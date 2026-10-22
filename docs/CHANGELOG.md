# Changelog

## Non publié

## Version 1.05.0 (14/10/2026)

- Ajout de workflows CI et de scripts pour le front-end, le PHP, la sécurité et la gestion des badges.
- Mise en place de scripts Python pour la restauration des langues d’internationalisation, l’analyse des secrets et la validation des fichiers i18n.
- Ajout de scripts Bash pour l’exécution des vérifications ESLint, PHPStan et Prettier.
- Création de workflows GitHub Actions pour la vérification du front-end, de la syntaxe PHP, de la structure Jeedom, des audits de sécurité et de la gestion des badges de la boutique.
- Mise en place de tests unitaires pour la génération des badges de la boutique et des fichiers README.
- Ajout de fichiers CSS pour les modaux de configuration et d’état dans le plugin LGThinQ.
- Mise à jour du fichier .gitignore pour exclure les caches des tests locaux et des outils de CI.

## Version 1.04.02 (14/08/2026)

- Ajout de la gestion des erreurs pour la récupération des routes MQTT et génération locale de clés privées et CSR avec OpenSSL.

## Version 1.04.01 (19/07/2026)

- Suppression des logs de débogage dans les fichiers PHP et HTML

## Version 1.04.00 (30/06/2026)

- Amélioration de la gestion des processus.
- Ajout d'une vérification des tokens d'accès.

## Version 1.04 (05/07/2025)

- Correction de la valeur envoyée lors de l'envoie d'une commande action/other créée manuellement.
- Correction du lien du CSS.
- Ajout d'un champ en rouge, pour les commande other si updateLGCmdToValue n'est pas vide (il faut alors le vider)

## Version 1.03 (13/05/2025)

- Affiche si une modification est en cours sur la page de configuration du plugin.

## Version 1.02 (12/05/2025)

- Correction de la dernière valeur du jour en historique (consommation eau/électricité).
- Ajout de commandes action pour lave-linge séchant en thinq2.

## Version 1.01 (23/04/2025)

- Correction listValueSelected : retrait des options du core pour intégrer le tout dans le plugin.

## Version 1.00 (20/04/2025)

- Correction des valeurs des commandes info (select/binaire) sur le dashboard.
- Ajout de conversion inversée pour les valeurs clé/valeur des commandes select.
- Corrections mineures PHP.

## Version 0.99 (17/04/2025)

- Ajout de commandes actions pour les réfrigérateurs en thinq1
- Correction de warning PHP

## Version 0.98 (14/04/2025)

- Correction des commandes dupliquées lorsque le nom est "N/A".

## Version 0.97 (16/12/2024)

- Correction de valeurs des commandes binaires ON/OFF.

## Version 0.96 (15/12/2024)

- Ajout d'un bouton pour valider des conditions manuellement.
- Correction de la fonction de validation des conditions.
- Ajout d'une notification quand de nouvelles conditions sont à valider.

## Version 0.95 (07/12/2024)

- Correction de la synchronisation générale

## Version 0.94 (02/12/2024)

- baisse du nombre de déconnections pour appareils thinq1 pollMonitor.
- Correction du status online pour appareils thinq1 pollMonitor.

## Version 0.93 (28/11/2024)

- Correction warning PHP.

## Version 0.92 (27/11/2024)

- Correction uri offset.

## Version 0.91 (27/11/2024)

- Ajout de vérifications des fichiers de langues.

## Version 0.89 (01/08/2024)

- Mise à jour des logos.
- Ajout d'un point de statut de connexion sur la vignette eqLogic.
- Optimisation de l'affichage de asTable.

## Version 0.88 (28/07/2024)

- Ajouter de nouveaux appareils.
- Ajouter de nouveaux logos.
- Ajout de constantes pour les clés header identiques.
- Ajouter d'un cron5 pour obtenir la consommation d'eau et d'électricité, le nombre de portes ouvertes, la pollution de l'air… (thinq2 ok, thinq1 à tester)

## Version 0.87 (25/07/2024)

- Correction du rechargement des commandes.
- Correction de la santé jeedom : délai d'expiration inversé.

## Version 0.86 (12/07/2024) beta

- Ajout du require composer "php-mqtt".
- Ajout d'auto-acceptation des conditions LG.

## Version 0.85 (30/06/2024) beta

- Retrait de jQuery et bootbox. => vanillaJS
- Ajout d'informations au passage de la souris sur les équipements.
- Correction de l'erreur lors d'une synchronisation.
- Ajout de log pour erreur de requête postData.

## Version 0.84 (19/05/2024) beta

- Correction des fichiers de langues contenant des caractères non reconnus.
- Amélioration de fonctions de traductions.

## Version 0.83 (18/05/2024) beta

- Correction de l'envoi des commandes pour anciens appareils en lgthinq1.
- Correction de la suppression des commandes lors de synchro.

## Version 0.82 (16/05/2024) beta

- Correction des traduction des clés pour les anciens appareils en thinq1.
- Correction de la commande "Connecté" pour les anciens appareils en thinq1.
- Correction du widget des commande binaries qui passaient en alerte (réception d'une valeur différente de 0 ou 1)

## Version 0.81 (15/05/2024) beta

- Correction de l'affichage du widget dashboard.
- Ajout compatibilité des appareils thinq1 binary.

## Version 0.40 (13/02/2024) beta

- Ajout de PHPDoc et traductions.

## Version 0.37 (04/12/2023) beta

- Ajout d'une liste déroulante des langues de connexion LG.
- Ajout de la possibilité de se connecter depuis un compte LG de n'importe quel pays.

## Version 0.10 (11/10/2023) beta

- Première version.
