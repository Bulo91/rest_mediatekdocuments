# Sauvegarde de la base de données

Ce dossier contient les fichiers permettant d'automatiser la sauvegarde de la base de données de l'API MediaTekDocuments.

## Fichiers

- `backup.sh` : script Linux utilisant mysqldump pour créer une sauvegarde compressée de la base de données.
- `backup.php` : script PHP permettant de lancer la sauvegarde à distance.

## Automatisation

La sauvegarde est déclenchée automatiquement grâce au service externe **cron-job.org**, qui appelle périodiquement le fichier `backup.php`.

## Fichiers générés

Les sauvegardes sont enregistrées sous la forme :

- `bddbackup_YYYY-MM-DD.sql.gz`

Ces fichiers ne sont pas versionnés dans Git.

## Restauration

Pour restaurer la base de données :

1. Télécharger le fichier `.sql.gz`.
2. Le décompresser.
3. Importer le fichier `.sql` dans phpMyAdmin.
