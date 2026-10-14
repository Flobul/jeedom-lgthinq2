<?php
/* This file is part of Jeedom. */
if (!isConnect('admin')) {
    throw new Exception(__('401 - Accès non autorisé', __FILE__));
}
$eqLogics = lgthinq2::byType('lgthinq2');
$enabledCount = 0;
$onlineCount = 0;
$thinq2Count = 0;
foreach ($eqLogics as $healthEqLogic) {
    if ($healthEqLogic->getIsEnable()) $enabledCount++;
    if ($healthEqLogic->getConfiguration('platformType') === 'thinq2') $thinq2Count++;
    $online = $healthEqLogic->getCmd('info', 'online');
    if (is_object($online) && (bool) $online->execCmd()) $onlineCount++;
}
?>
<?php include_file('desktop', 'health', 'css', 'lgthinq2'); ?>

<div id="lgthinq2Health">
    <header class="lgthinq2-health-hero">
        <span class="lgthinq2-health-hero-icon"><i class="fas fa-heartbeat"></i></span>
        <div><span class="lgthinq2-health-eyebrow">{{Diagnostic du plugin}}</span><h2>{{Santé LG ThinQ 2}}</h2><p>{{État des appareils LG, plateforme utilisée et dernières communications.}}</p></div>
    </header>

    <section class="lgthinq2-health-summary" aria-label="{{Résumé de la santé LG ThinQ 2}}">
        <article><span class="lgthinq2-health-summary-icon"><i class="fas fa-home"></i></span><div><span>{{Appareils}}</span><strong><?php echo count($eqLogics); ?></strong><small>{{configuré(s)}}</small></div></article>
        <article><span class="lgthinq2-health-summary-icon"><i class="fas fa-power-off"></i></span><div><span>{{Actifs}}</span><strong><?php echo (int) $enabledCount; ?></strong><small><?php echo max(0, count($eqLogics) - $enabledCount); ?> {{désactivé(s)}}</small></div></article>
        <article><span class="lgthinq2-health-summary-icon"><i class="fas fa-wifi"></i></span><div><span>{{Connectés}}</span><strong><?php echo (int) $onlineCount; ?></strong><small>{{signalés en ligne}}</small></div></article>
        <article><span class="lgthinq2-health-summary-icon"><i class="fas fa-cloud"></i></span><div><span>{{Plateforme ThinQ 2}}</span><strong><?php echo (int) $thinq2Count; ?></strong><small><?php echo max(0, count($eqLogics) - $thinq2Count); ?> {{ThinQ 1}}</small></div></article>
    </section>

    <section class="lgthinq2-health-panel">
        <div class="lgthinq2-health-panel-header"><i class="fas fa-list"></i><h3>{{Équipements LG}}</h3></div>
        <div class="table-responsive">
            <table class="table table-condensed table-striped tablesorter" id="table_healthlgthinq2">
                <thead><tr><th>{{Appareil}}</th><th>{{Type}}</th><th>{{Plateforme}}</th><th>{{Statut}}</th><th>{{Connecté}}</th><th>{{Dernière communication}}</th><th>{{Date création}}</th></tr></thead>
                <tbody>
                    <?php foreach ($eqLogics as $eqLogic) {
                        $platform = (string) $eqLogic->getConfiguration('platformType');
                        $online = $eqLogic->getCmd('info', 'online');
                        $isOnline = is_object($online) && (bool) $online->execCmd();
                        $type = (string) lgthinq2::deviceTypeConstants($eqLogic->getConfiguration('deviceType'));
                    ?>
                        <tr<?php echo $eqLogic->getIsEnable() ? '' : ' class="lgthinq2-health-disabled"'; ?>>
                            <td><a href="<?php echo htmlspecialchars($eqLogic->getLinkToConfiguration(), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($eqLogic->getHumanName(true), ENT_QUOTES, 'UTF-8'); ?></a></td>
                            <td><?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="label label-<?php echo $platform === 'thinq1' ? 'info' : 'success'; ?>"><?php echo htmlspecialchars($platform !== '' ? $platform : 'N/A', ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><span class="label label-<?php echo $eqLogic->getStatus('state') === 'nok' ? 'danger' : 'success'; ?>"><?php echo $eqLogic->getStatus('state') === 'nok' ? '{{NOK}}' : '{{OK}}'; ?></span></td>
                            <td><span class="label label-<?php echo $isOnline ? 'success' : 'danger'; ?>"><?php echo $isOnline ? '{{OUI}}' : '{{NON}}'; ?></span></td>
                            <td><?php echo htmlspecialchars((string) $eqLogic->getStatus('lastCommunication'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string) $eqLogic->getConfiguration('createtime'), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (count($eqLogics) === 0) { ?><tr><td colspan="7" class="text-center text-muted">{{Aucun appareil LG ThinQ}}</td></tr><?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
