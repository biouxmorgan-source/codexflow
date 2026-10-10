# Mettre SagaWyn en ligne

Ce guide suit le cahier d'hébergement : un serveur **Hetzner Cloud CX23** géré par **Laravel Forge** (offre Hobby), les e-mails par **Brevo** (gratuit) et le HTTPS par **Let's Encrypt** (gratuit, géré par Forge). Il faut compter environ 20 € HT par mois et une soirée pour la première mise en ligne.

Deux règles pour toute la suite :

- Les mots de passe et les clés (base, Brevo, Reverb, VAPID, Stripe) se saisissent **uniquement dans Forge**, jamais dans le dépôt ni dans une discussion.
- À chaque étape, `php artisan sagawyn:check` dit ce qui reste à régler. Dans Forge, on lance une commande depuis l'onglet **Commands** du site.

Les noms de menus de Forge et Hetzner changent de temps en temps. Si un nom ne correspond pas, cherche l'intitulé le plus proche.

## 1. Le nom de domaine

1. Achète le domaine chez un registraire (OVH, Gandi, Infomaniak…), par exemple `sagawyn.com`.
2. Ne touche pas encore aux DNS : l'adresse du serveur arrive à l'étape 4.

## 2. Les comptes

1. **Hetzner** : crée un compte sur https://console.hetzner.cloud, puis un projet « SagaWyn ». Dans *Security → API tokens*, crée un jeton en **lecture et écriture** et garde-le pour l'étape 3.
2. **Forge** : crée un compte sur https://forge.laravel.com et choisis l'offre Hobby. Relie ton compte GitHub (il faut accès au dépôt `biouxmorgan-source/codexflow`).
3. **Brevo** : crée un compte gratuit sur https://www.brevo.com. Dans *Expéditeurs, domaines et IP dédiées*, ajoute ton domaine. Brevo affiche des enregistrements DNS (DKIM, DMARC) à ajouter chez ton registraire : fais-le, sinon les e-mails finissent en indésirables.

## 3. Le serveur

1. Dans Forge, ajoute Hetzner comme fournisseur en collant le jeton de l'étape 2.
2. Crée un serveur :
   - type **App Server** ;
   - région en Allemagne ou en Finlande (Nuremberg, Falkenstein ou Helsinki) ;
   - taille **CX23** ;
   - **PHP 8.3** ;
   - base **PostgreSQL 16**, nommée `sagawyn`.
3. Forge affiche (et t'envoie par e-mail) le mot de passe *sudo* et celui de la base. **Note-les** dans ton gestionnaire de mots de passe.
4. Attends la fin de l'installation (une dizaine de minutes), puis note l'adresse IP du serveur.

## 4. Les DNS

Chez ton registraire, dans la zone DNS du domaine :

| Type | Nom | Valeur |
|------|-----|--------|
| A | `@` | l'adresse IPv4 du serveur |
| AAAA | `@` | l'adresse IPv6 du serveur (si Hetzner en donne une) |
| CNAME | `www` | `sagawyn.com.` |

La propagation prend de quelques minutes à quelques heures.

## 5. Le site

1. Dans Forge, sur le serveur, crée un site avec :
   - domaine : `sagawyn.com` ;
   - type de projet : Laravel ;
   - dépôt : `biouxmorgan-source/codexflow`, branche `main` ;
   - base de données : `sagawyn`.
2. Quand les DNS répondent, ouvre l'onglet **SSL** du site et demande un certificat **Let's Encrypt** pour `sagawyn.com` et `www.sagawyn.com`.

## 6. Le fichier .env

1. Ouvre l'onglet **Environment** du site.
2. Remplace tout son contenu par celui de `.env.production.example`, **en gardant** la ligne `APP_KEY=…` et le mot de passe de la base (`DB_PASSWORD`) que Forge a déjà remplis.
3. Remplace `sagawyn.example` par ton domaine (dans `APP_URL`, `REVERB_HOST` et `MAIL_FROM_ADDRESS`).
4. Remplis :
   - `MAIL_USERNAME` et `MAIL_PASSWORD` avec les identifiants **SMTP** de Brevo (*SMTP & API*) ;
   - `LEGAL_*` avec les mentions légales.
5. Les clés des notifications push : dans **Commands**, lance `php artisan webpush:vapid --show`, puis copie les deux clés dans `VAPID_PUBLIC_KEY` et `VAPID_PRIVATE_KEY`. Fais-le **une seule fois** : changer ces clés désabonne tous les appareils.
6. Si `APP_KEY` est vide, lance `php artisan key:generate --force` dans **Commands**. Ne la change plus jamais ensuite : les clés IA enregistrées par les comptes deviendraient illisibles.

## 7. Le temps réel, la file d'attente et les tâches de nuit

1. **Reverb** : dans l'onglet **Application** du site, active **Laravel Reverb**. Forge demande le nom d'hôte public (ton domaine) et le port (garde `8080`). Il crée le démon, configure le serveur web et remplit `REVERB_APP_ID`, `REVERB_APP_KEY` et `REVERB_APP_SECRET`. Vérifie ensuite dans **Environment** que `REVERB_HOST` est ton domaine, `REVERB_PORT=443` et `REVERB_SCHEME=https`.
2. **File d'attente** : dans l'onglet **Queue** (ou *Processes*) du site, ajoute un worker :
   - connexion `database` ;
   - nombre de tentatives `3` ;
   - délai `3` secondes.
3. **Planificateur** : dans l'onglet **Application**, active **Laravel Scheduler**. Il lance `php artisan schedule:run` chaque minute, ce qui fait tourner le ménage quotidien et la sauvegarde de 3 h 30.
4. **Extension GMP** (nécessaire aux notifications push) : si `sagawyn:check` la signale manquante, lance sur le serveur, dans **Recipes** et en tant que `root` :

   ```sh
   apt-get install -y php8.3-gmp && service php8.3-fpm restart
   ```

## 8. Le premier déploiement

1. Dans l'onglet **Deployments** du site, remplace le script par le contenu de `deploy/forge-deploy.sh`.
2. Active le **déploiement automatique** : chaque merge sur `main` mettra le site à jour.
3. Clique **Deploy now**, puis ouvre le journal du déploiement. Les dernières lignes sont le résultat de `sagawyn:check`. Il ne doit plus y avoir de ✗ ; les « ! » sont des conseils.

## 9. Les vérifications

1. Ouvre `https://sagawyn.com` et **crée ton compte en premier** : le premier compte devient administrateur.
2. Vérifie que la console d'administration n'affiche aucun avertissement en tête.
3. Fais « Mot de passe oublié » sur ton compte : l'e-mail doit arriver, aux couleurs de SagaWyn.
4. Ouvre une campagne dans deux navigateurs (MJ et joueur) et envoie un message : il doit apparaître sans recharger la page.
5. Installe l'application sur ton téléphone, active les notifications dans Préférences et déclenche une révélation : la notification doit arriver.
6. Teste `https://sagawyn.com/up` (réponse verte) et le partage du lien de la page d'accueil (aperçu avec image).
7. Si tu veux un contenu d'exemple : **Mes campagnes → Charger la démonstration**.

## 10. Les sauvegardes

SagaWyn en prévoit trois niveaux :

1. **Sauvegarde du serveur entier** par Hetzner. Dans la console Hetzner, sur le serveur, active **Backups** (environ +20 % du prix) : 7 copies quotidiennes.
2. **Sauvegarde de nuit** par SagaWyn (`BACKUP_ENABLED=true`). Chaque nuit à 3 h 30, elle copie la base et les fichiers envoyés dans `BACKUP_PATH` et garde 7 jours. Pour en lancer une à la main : `php artisan sagawyn:backup`.
3. **Copie hors du serveur**, indispensable si le serveur est perdu. Elle reste à mettre en place de ton côté. La solution simple est une **Hetzner Storage Box** BX11 (quelques euros par mois) :
   - Installe `rclone` sur le serveur et configure la Storage Box en SFTP (`rclone config`, en tant que `forge`).
   - Ajoute dans Forge une tâche planifiée (**Scheduler**, chaque jour à 4 h 30, utilisateur `forge`) :

     ```sh
     rclone sync /home/forge/backups/sagawyn storagebox:sagawyn
     ```

### Restaurer une sauvegarde

Dans un dossier daté de `BACKUP_PATH` :

```sh
php artisan down
pg_restore --clean --if-exists --no-owner -h 127.0.0.1 -U forge -d sagawyn database.dump
tar -xzf files.tar.gz -C storage/app/private
php artisan up
```

Lance ces commandes depuis le dossier du site, en remplaçant les chemins par ceux de la sauvegarde. Le mot de passe demandé est celui de la base.

## Mettre à jour, plus tard

- Chaque merge sur `main` redéploie le site si le déploiement automatique est actif. Sinon, clique **Deploy now**.
- Le numéro de version et la fenêtre « Quoi de neuf » suivent automatiquement.

## Si quelque chose ne va pas

1. Lance `php artisan sagawyn:check` dans **Commands** : chaque ✗ dit quoi corriger.
2. Lis le journal du jour dans `storage/logs/laravel-AAAA-MM-JJ.log` (onglet **Logs** du site dans Forge).
3. Consulte le journal du dernier déploiement dans **Deployments**.
4. Si une tâche est restée en échec : `php artisan queue:failed`, puis `php artisan queue:retry all`.
