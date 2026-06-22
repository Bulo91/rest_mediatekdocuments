<h1>Présentation de l'API</h1>

Ce projet est une évolution du projet d'origine disponible à l'adresse :<br>
https://github.com/CNED-SLAM/rest_chocolatein<br>
Le README du dépôt d'origine présente le fonctionnement initial de l'API (structure des fichiers, rôle de chaque composant, principes d'exploitation).<br>
Cette API, écrite en PHP, permet d'exécuter des requêtes SQL sur la base de données **mediatek86** (SGBDR MySQL).<br>
Elle répond aux demandes de l'application client **MediaTekDocuments** :<br>
https://github.com/CNED-SLAM/MediaTekDocuments

<h1>Fonctionnalités ajoutées</h1>

Par rapport au dépôt d'origine, les évolutions suivantes ont été apportées :

<ul>
   <li><strong>sécurisation des accès</strong> : authentification Basic et gestion des identifiants via le fichier <code>.env</code> ;</li>
   <li><strong>gestion des commandes</strong> : consultation, ajout et suppression des commandes de documents et de revues ;</li>
   <li><strong>gestion des exemplaires</strong> : consultation, modification et suppression des exemplaires de revues ;</li>
   <li><strong>gestion des abonnements</strong> : suivi des abonnements aux revues ;</li>
   <li><strong>alertes sur les abonnements proches de leur expiration</strong> : remontée des abonnements arrivant à échéance ;</li>
   <li><strong>déploiement de l'API sur un hébergement distant</strong> : mise en ligne sur AwardSpace ;</li>
   <li><strong>sauvegarde automatique de la base de données</strong> : scripts de backup et planification via cron-job.org.</li>
</ul>

<h1>Technologies utilisées</h1>

<ul>
   <li>PHP</li>
   <li>MySQL</li>
   <li>API REST</li>
   <li>JSON</li>
   <li>phpDocumentor</li>
</ul>

<h1>Installation locale</h1>

Pour tester l'API REST en local, voici le mode opératoire :

<h2>1. Installation de WampServer</h2>

<ul>
   <li>Télécharger et installer <a href="https://www.wampserver.com/">WampServer</a> (ou un environnement équivalent : Apache, PHP, MySQL).</li>
   <li>Vérifier que les services Apache et MySQL sont démarrés (icône WampServer verte).</li>
   <li>Installer également <a href="https://www.postman.com/">Postman</a> pour les tests de l'API, et un IDE (NetBeans, VS Code, etc.) pour analyser le code.</li>
</ul>

<h2>2. Récupération et installation du projet</h2>

<ul>
   <li>Cloner ou télécharger le dépôt, puis placer le dossier dans le répertoire <code>www</code> de WampServer (par exemple : <code>c:\wamp64\www\rest_mediatekdocuments</code>).</li>
   <li>Si Composer n'est pas installé, le télécharger depuis <a href="https://getcomposer.org/">getcomposer.org</a>, puis exécuter <code>composer install</code> à la racine du projet pour recréer le dossier <code>vendor</code>.</li>
</ul>

<h2>3. Importation du script SQL</h2>

<ul>
   <li>Ouvrir <strong>phpMyAdmin</strong> (via le menu WampServer).</li>
   <li>Créer une base de données nommée <code>mediatek86</code>.</li>
   <li>Importer et exécuter le script <code>mediatek86.sql</code> situé à la racine du projet afin de créer les tables et insérer les données initiales.</li>
</ul>

<h2>4. Configuration du fichier .env</h2>

Le fichier <code>src/.env</code> contient les paramètres sensibles d'accès à la base de données et d'authentification à l'API. Pour un usage en local, adapter les valeurs suivantes :<br>
<pre>
BDD_SERVER=localhost
BDD_PORT=3306
BDD_BD=mediatek86
BDD_LOGIN=root
BDD_PWD=

AUTHENTIFICATION=basic
AUTH_USER=mediatkuser
AUTH_PW=mediatkpwd
</pre>

> **Important :** ne jamais versionner de mots de passe réels. Le fichier <code>.env</code> ne doit pas être exposé publiquement.

<h2>5. Démarrage du serveur</h2>

<ul>
   <li>Démarrer WampServer (Apache et MySQL actifs).</li>
   <li>L'API est alors accessible à l'adresse : <strong>http://localhost/rest_mediatekdocuments/</strong></li>
</ul>

<h1>Déploiement</h1>

L'API est déployée sur un hébergement web gratuit **AwardSpace**, accessible à l'adresse :<br>
<strong>https://mediatekdocuments.myartsonline.com/</strong>

<h2>Transfert des fichiers (FTP avec FileZilla)</h2>

<ul>
   <li>Installer <a href="https://filezilla-project.org/">FileZilla Client</a>.</li>
   <li>Se connecter au serveur AwardSpace avec les identifiants FTP fournis par l'hébergeur (hôte, utilisateur, mot de passe, port).</li>
   <li>Transférer l'ensemble des fichiers du projet vers le répertoire web distant (<code>www</code> ou équivalent).</li>
   <li>Adapter le fichier <code>src/.env</code> sur le serveur avec les paramètres de connexion MySQL AwardSpace (serveur, base, identifiants).</li>
   <li>Placer les scripts de sauvegarde (<code>backup.sh</code>, <code>backup.php</code>) dans le dossier <code>savebdd</code> sur le serveur distant.</li>
</ul>

<h2>Tests avec Postman</h2>

<ul>
   <li>Configurer l'URL de base de l'API distante dans Postman.</li>
   <li>Activer l'authentification Basic (onglet <strong>Authorization</strong>, type <strong>Basic Auth</strong>) avec les identifiants définis dans le fichier <code>.env</code>.</li>
   <li>Exécuter les requêtes de la collection Postman pour valider le bon fonctionnement de l'API en production.</li>
</ul>

<h1>Sauvegardes automatiques</h1>

La sauvegarde de la base de données est automatisée grâce aux éléments suivants :

<h2>backup.sh</h2>

Script shell exécuté sur le serveur Linux d'AwardSpace. Il utilise <code>mysqldump</code> pour exporter la base de données, compresse le résultat avec <code>gzip</code> et enregistre le fichier dans le dossier <code>savebdd</code>.

<h2>backup.php</h2>

Script PHP accessible via HTTP qui lance <code>backup.sh</code> à distance. Il affiche le code de retour et la sortie du script, ce qui permet de vérifier le bon déroulement de la sauvegarde.

<h2>Dossier savebdd</h2>

Ce dossier contient les scripts de sauvegarde et les fichiers générés. Voir également le fichier <a href="savebdd/README.md">savebdd/README.md</a> pour le détail du fonctionnement et de la restauration.

<h2>Planification avec cron-job.org</h2>

Le service externe <a href="https://cron-job.org/">cron-job.org</a> appelle périodiquement l'URL de <code>backup.php</code> sur le serveur distant, ce qui déclenche automatiquement la sauvegarde sans intervention manuelle.

<h2>Fichiers générés (.sql.gz)</h2>

Les sauvegardes sont enregistrées sous la forme :<br>
<pre>bddbackup_YYYY-MM-DD.sql.gz</pre>

Ces fichiers ne sont pas versionnés dans Git. Pour restaurer une sauvegarde : télécharger le fichier, le décompresser, puis importer le fichier <code>.sql</code> résultant dans phpMyAdmin.

<h1>Utilisation de l'API</h1>

Adresse de l'API (en local) : <strong>http://localhost/rest_mediatekdocuments/</strong><br>
Les requêtes s'effectuent en ajoutant des informations dans l'URL et, selon les besoins, dans le corps de la requête (body).

<h2>Récupérer un contenu (SELECT) — GET</h2>

<strong>Méthode HTTP : GET</strong><br>
<code>http://localhost/rest_mediatekdocuments/table/champs</code> (<code>champs</code> optionnel)

<ul>
   <li><code>table</code> : nom de la table (caractères alphanumériques et <code>_</code>).</li>
   <li><code>champs</code> (optionnel) : liste des champs nom/valeur servant à la recherche, au format JSON.</li>
</ul>

<h2>Insérer (INSERT) — POST</h2>

<strong>Méthode HTTP : POST</strong><br>
<code>http://localhost/rest_mediatekdocuments/table</code>

Dans le body (Postman : onglet <strong>Body</strong>, option <strong>x-www-form-urlencoded</strong>) :

<ul>
   <li>Key : <code>champs</code></li>
   <li>Value : liste des champs nom/valeur pour l'insertion, au format JSON</li>
</ul>

<h2>Modifier (UPDATE) — PUT</h2>

<strong>Méthode HTTP : PUT</strong><br>
<code>http://localhost/rest_mediatekdocuments/table/id</code> (<code>id</code> optionnel)

<ul>
   <li><code>table</code> : nom de la table.</li>
   <li><code>id</code> (optionnel) : identifiant de la ligne à modifier.</li>
</ul>

Dans le body (Postman : onglet <strong>Body</strong>, option <strong>x-www-form-urlencoded</strong>) :

<ul>
   <li>Key : <code>champs</code></li>
   <li>Value : liste des champs nom/valeur pour la modification, au format JSON</li>
</ul>

<h2>Supprimer (DELETE) — DELETE</h2>

<strong>Méthode HTTP : DELETE</strong><br>
<code>http://localhost/rest_mediatekdocuments/table/champs</code> (<code>champs</code> optionnel)

<ul>
   <li><code>table</code> : nom de la table.</li>
   <li><code>champs</code> (optionnel) : liste des champs nom/valeur déterminant les lignes à supprimer, au format JSON.</li>
</ul>

<h2>Authentification Basic</h2>

Toute requête vers l'API doit inclure une authentification HTTP Basic. Dans Postman : onglet <strong>Authorization</strong>, type <strong>Basic Auth</strong>, puis renseigner le nom d'utilisateur et le mot de passe définis dans le fichier <code>src/.env</code> (<code>AUTH_USER</code> et <code>AUTH_PW</code>).

Les exemples d'utilisation détaillés (requêtes sur les livres, DVD, revues, commandes, exemplaires, abonnements, etc.) sont disponibles dans la <strong>collection Postman</strong> fournie avec le projet.

<h1>Documentation technique</h1>

La documentation du code source, générée avec <strong>phpDocumentor</strong>, est disponible dans le dépôt à l'adresse suivante :<br>
<a href="docs/api/index.html">docs/api/index.html</a><br>
Elle décrit les classes, méthodes et paramètres de l'API (notamment <code>MyAccessBDD</code>, <code>Controle</code>, <code>Connexion</code>, etc.).

<h1>Auteur</h1>

<strong>Bulent KURT</strong> – BTS SIO SLAM
