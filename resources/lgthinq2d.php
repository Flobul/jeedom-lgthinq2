#!/usr/
<?php

require_once dirname(__FILE__) . '/../core/class/lgthinq2.class.php';

log::add('lgthinq2', 'info', __('DÉMON MQTT : ', __FILE__) . __('Activation du service MQTT LGThinQ', __FILE__));

lgthinq2::getTokenIsExpired();

$fileCertAmazonCA = dirname(__FILE__) . '/../../../data/AmazonRootCA1.pem';
$fileCertClient = dirname(__FILE__) . '/../../../data/certificatePem.pem';
$fileCertClientKey = dirname(__FILE__) . '/../../../data/pass.pem';

try {

    // GET LG MQTT SERVER
    $headers = lgthinq2::defaultGwHeaders();
    $curlMqtt = curl_init();
    curl_setopt_array($curlMqtt, array(
        CURLOPT_URL => lgthinq2::LGTHINQ_MQTT_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $headers
    ));
    $rep = curl_exec($curlMqtt);
    $gatewayHttpCode = curl_getinfo($curlMqtt, CURLINFO_HTTP_CODE);
    $gatewayCurlError = curl_error($curlMqtt);
    curl_close($curlMqtt);
    if ($rep === false || $gatewayHttpCode >= 400) {
        throw new RuntimeException(
            __('Impossible de récupérer la route MQTT LG ThinQ', __FILE__) . ' (HTTP ' . $gatewayHttpCode .
            ($gatewayCurlError !== '' ? ', cURL : ' . $gatewayCurlError : '') . ').'
        );
    }
    $gatewayRes = json_decode($rep, true);
    if (!$gatewayRes || !isset($gatewayRes['resultCode'], $gatewayRes['result'])) {
        throw new RuntimeException(__('Réponse invalide reçue pour la route MQTT LG ThinQ.', __FILE__));
    }
    if ($gatewayRes['resultCode'] != '0000') {
        if ($gatewayRes['resultCode'] == '0110') {
            event::add('jeedom::alert', array(
                'level' => 'success',
                'page' => 'lgthinq2',
                'message' => __('Les conditions générales ont changé, merci de vous rendre dans l’application pour les accepter.', __FILE__),
            ));
        }
        throw new RuntimeException(__('La route MQTT LG ThinQ a renvoyé le code', __FILE__) . ' ' . $gatewayRes['resultCode'] . '.');
    }
    if (empty($gatewayRes['result']['apiServer']) || empty($gatewayRes['result']['mqttServer'])) {
        throw new RuntimeException(__('La route LG ThinQ ne contient pas les serveurs API et MQTT attendus.', __FILE__));
    }
    log::add('lgthinq2', 'debug', __('DÉMON MQTT : ', __FILE__) . __('API SERVER ', __FILE__) . $gatewayRes['result']['apiServer']);
    //config::save('LGE_API_SERVER', $gatewayRes['result']['apiServer'], 'lgthinq2');
    //config::save('LG_MQTT_SERVER', $gatewayRes['result']['mqttServer'], 'lgthinq2');

    // Generate the private key and certificate signing request locally. The old
    // Azure helper was a single point of failure and exposed the private key to
    // a third-party service.
    if (!extension_loaded('openssl')) {
        throw new RuntimeException(__("L'extension PHP OpenSSL est requise pour initialiser MQTT.", __FILE__));
    }
    $opensslConfig = array(
        'digest_alg' => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    );
    $privateKey = openssl_pkey_new($opensslConfig);
    if ($privateKey === false) {
        throw new RuntimeException(__('OpenSSL ne parvient pas à générer la clé privée MQTT.', __FILE__));
    }
    $certificateRequest = openssl_csr_new(array(
        'commonName' => 'AWS IoT Certificate',
        'organizationName' => 'Amazon',
    ), $privateKey, $opensslConfig);
    if ($certificateRequest === false) {
        throw new RuntimeException(__('OpenSSL ne parvient pas à générer la demande de certificat MQTT.', __FILE__));
    }
    if (!openssl_pkey_export($privateKey, $privateKeyPem, null, $opensslConfig)) {
        throw new RuntimeException(__('OpenSSL ne parvient pas à exporter la clé privée MQTT.', __FILE__));
    }
    if (!openssl_csr_export($certificateRequest, $csrPem)) {
        throw new RuntimeException(__('OpenSSL ne parvient pas à exporter la demande de certificat MQTT.', __FILE__));
    }
    if (file_put_contents($fileCertClientKey, $privateKeyPem, LOCK_EX) === false) {
        throw new RuntimeException(__("Impossible d'écrire la clé privée MQTT dans", __FILE__) . ' ' . $fileCertClientKey . '.');
    }
    chmod($fileCertClientKey, 0600);
    log::add('lgthinq2', 'debug', __('DÉMON MQTT : ', __FILE__) . 'Clé privée et CSR générés localement');

    // GET CLIENT INIT REQUEST
    $repCli = lgthinq2::postData(lgthinq2::LGTHINQ2_SERV_URL . 'service/users/client', '', lgthinq2::defaultDevicesHeaders());
    log::add('lgthinq2', 'debug', __('DÉMON MQTT : ', __FILE__) . __('CLIENT ', __FILE__) . $repCli);
    $cliRes = json_decode($repCli, true);
    if (!$cliRes || !isset($cliRes['resultCode']) || $cliRes['resultCode'] == '0102') {
        lgthinq2::login();
    }
    if ((!$cliRes || !isset($cliRes['resultCode']) || $cliRes['resultCode'] == '0110') && config::byKey('authorize_terms', 'lgthinq2', false) == true) {
        lgthinq2::terms();
    }

    // GET PRIVATE CLIENT CERTIFICATE
    $csr = preg_replace('/-----BEGIN (?:NEW )?CERTIFICATE REQUEST-----|-----END (?:NEW )?CERTIFICATE REQUEST-----|\s+/', '', $csrPem);
    //log::add('lgthinq2', 'debug', __('DÉMON MQTT : ', __FILE__) . __('CSR ', __FILE__) . $csr);
    $repCer = lgthinq2::postData(lgthinq2::LGTHINQ2_SERV_URL . 'service/users/client/certificate', json_encode(array('csr' => $csr)), lgthinq2::defaultDevicesHeaders());
    $cerRes = json_decode($repCer, true);
    if (!$cerRes || !isset($cerRes['resultCode'], $cerRes['result']) || $cerRes['resultCode'] != '0000') {
        throw new RuntimeException(
            __('Impossible de récupérer le certificat MQTT LG ThinQ', __FILE__) . ' (' . __('code', __FILE__) . ' ' .
            ($cerRes['resultCode'] ?? __('réponse invalide', __FILE__)) . ').'
        );
    }
    if (empty($cerRes['result']['certificatePem']) || empty($cerRes['result']['subscriptions'])) {
        throw new RuntimeException(__('La réponse LG ThinQ ne contient pas le certificat ou les abonnements MQTT attendus.', __FILE__));
    }
    log::add('lgthinq2', 'debug', __('DÉMON MQTT : ', __FILE__) . __('CER ', __FILE__) . 'Certificat reçu, ' . count($cerRes['result']['subscriptions']) . ' abonnement(s)');
    //config::save('MQTT_CER_PEM', $cerRes['result']['certificatePem'], 'lgthinq2');
    if (file_put_contents($fileCertClient, $cerRes['result']['certificatePem'], LOCK_EX) === false) {
        throw new RuntimeException(__("Impossible d'écrire le certificat MQTT dans", __FILE__) . ' ' . $fileCertClient . '.');
    }
    chmod($fileCertClient, 0600);
    //config::save('MQTT_SUB', $cerRes['result']['subscriptions'][0], 'lgthinq2');

    // GET AMAZON ROOT CA
    if (!is_file($fileCertAmazonCA)) {
        shell_exec('sudo wget --quiet -O ' . $fileCertAmazonCA . ' ' . lgthinq2::LGTHINQ_MQTT_CER);
        log::add('lgthinq2', 'debug', __('DÉMON MQTT : ', __FILE__) . __('CERTCA ', __FILE__) .  $fileCertAmazonCA);
    }

    // INIT MQTT CONNECTION
    $mqttUri = parse_url($gatewayRes['result']['mqttServer']);
    $mqtt = new \PhpMqtt\Client\MqttClient(str_replace('.iot.', '-ats.iot.', $mqttUri['host']), $mqttUri['port'], lgthinq2::getClientId());

    $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
        ->setKeepAliveInterval(60)
        ->setConnectTimeout(10)
        ->setReconnectAutomatically(true)
        ->setUseTls(true)
        ->setTlsCertificateAuthorityFile($fileCertAmazonCA)
        ->setTlsClientCertificateFile($fileCertClient)
        ->setTlsClientCertificateKeyFile($fileCertClientKey);

    $mqtt->connect($connectionSettings, false);
    log::add('lgthinq2', 'info', __('DÉMON MQTT : ', __FILE__) . __('connecté', __FILE__));
    foreach ($cerRes['result']['subscriptions'] as $subscription) {
        $mqtt->subscribe($subscription, function (string $topic, string $message, bool $retained) {
            $json = json_decode($message, true);
            foreach (eqLogic::byType('lgthinq2') as $lgthinq2) {
                if ($lgthinq2->getLogicalId() == $json['deviceId']) {
                    $timestamp = null;
                    if (isset($json['data']['state']['reported'])) {
                        $deviceTypeConfigFile = lgthinq2::loadConfigFile($json['deviceId']);
                        if (!is_object($lgthinq2->getCmd('info', 'online'))) {
                            $lgthinq2->checkAndCreateCmdFromConfigFile($deviceTypeConfigFile, 'online');
                        }
                        if (isset($json['data']['state']['reported']['timestamp'])) {
                            $timestamp = date('Y-m-d H:i:s', ($json['data']['state']['reported']['timestamp']/1000));
                        }
                        $lgthinq2->checkAndUpdateCmd('online', $json['data']['state']['reported']['online'], $timestamp);
                        $refState = lgthinq2::deviceTypeConstantsState($lgthinq2->getConfiguration('deviceType'));
                        if ($refState) {
                            $data = $json['data']['state']['reported'][$refState];
                        } else {
                            $data = $json['data']['state']['reported'];
                        }
                        foreach ($data as $refStateId => $refStateValue) {
                            if (!is_object($lgthinq2->getCmd('info', $refStateId))) {
                                $lgthinq2->checkAndCreateCmdFromConfigFile($deviceTypeConfigFile, $refStateId);
                            }
                            $lgthinq2->checkValueAndUpdateCmd($refStateId, $refStateValue, $timestamp);
                            log::add('lgthinq2', 'info', __('DÉMON MQTT : ', __FILE__) . $lgthinq2->getName() . __(' commande mise à jour : ', __FILE__) . $refStateId . __(' à la valeur : ', __FILE__) .$refStateValue . __(' au temps : ', __FILE__) . $timestamp);
                        }
                    }
                }
            }
        }, 1);
    }

    $mqtt->loop(true, true, 60);

    $mqtt->disconnect();

} catch (Throwable $e) {
    log::add(
        'lgthinq2',
        'error',
        __('DÉMON MQTT : ', __FILE__) . __('Erreur rencontrée ', __FILE__) . $e->getMessage() .
        ' [' . basename($e->getFile()) . ':' . $e->getLine() . ']'
    );
}

?>
