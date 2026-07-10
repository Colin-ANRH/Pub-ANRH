# Pub-ANRH

Site WordPress **ANRH Publications** — vitrine, catalogue produits et demandes de devis (sans vente en ligne).

| Environnement | URL | Branche / déploiement |
|---------------|-----|------------------------|
| **Local** | `http://localhost:8080/ANRPUB/` | développement manuel (XAMPP) |
| **Staging** | `https://pub.anrh.fr` | `master` → GitHub Actions (FTP OVH) |
| **Production** | _à définir_ | — |

---

## Stack

- **CMS** : WordPress 6.x
- **Thème** : `anrhpub_theme` (custom)
- **PHP** : 8.2+ (OVH) / 8.2 (local XAMPP)
- **Base de données** : MySQL 8.x
- **Hébergement staging** : OVH mutualisé
- **DNS** : Gandi (`anrh.fr`)
- **CI/CD** : GitHub Actions → FTP

---

## Structure du dépôt

```
.
├── .github/workflows/          # Pipelines de déploiement
│   ├── deploy-staging.yml      # Déploiement rapide (thème + config)
│   └── deploy-staging-full.yml # Déploiement complet WordPress (manuel)
├── deploy-ovh/                 # Fichiers de config pour le staging OVH
│   ├── .htaccess
│   ├── wp-config.template.php
│   └── build-wp-config.php
├── wp-content/
│   ├── mu-plugins/
│   │   └── anrh-staging-gate.php   # Verrou d'accès staging (à retirer en prod)
│   └── themes/
│       └── anrhpub_theme/          # Thème principal du projet
└── wp-config.php               # Local uniquement (non versionné)
```

Le dépôt contient une installation WordPress complète. Les parties **maintenues par l'équipe** sont principalement le thème `anrhpub_theme`, le mu-plugin de staging et les workflows de déploiement.

---

## Prérequis locaux

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL)
- PHP 8.2+
- Git

### Installation locale

1. Cloner le dépôt :
   ```bash
   git clone https://github.com/Colin-ANRH/Pub-ANRH.git
   ```
2. Copier le projet dans `C:\xampp\htdocs\ANRPUB\` (ou configurer un virtual host).
3. Créer la base MySQL `anrhpub_db` et importer un dump si nécessaire.
4. Copier `wp-config-sample.php` → `wp-config.php` et renseigner les identifiants locaux :
   ```php
   define( 'DB_NAME', 'anrhpub_db' );
   define( 'DB_USER', 'root' );
   define( 'DB_PASSWORD', '' );
   define( 'DB_HOST', 'localhost' );
   define( 'WP_ENVIRONMENT_TYPE', 'local' );
   ```
5. Démarrer Apache et MySQL (XAMPP).
6. Ouvrir : **http://localhost:8080/ANRPUB/**

> Le verrou staging (`anrh-staging-gate`) est **désactivé en local** (`WP_ENVIRONMENT_TYPE = local`).

---

## Déploiement staging (OVH)

### Automatique — à chaque push sur `master`

Workflow : **Deploy staging OVH (pub.anrh.fr)**

Déploie via FTP :
- thème `anrhpub_theme`
- `wp-config.php` (généré)
- `.htaccess` OVH
- mu-plugin staging
- fichiers PHP racine

### Manuel — déploiement complet WordPress

Workflow : **Deploy staging OVH — complet (1ère fois)**

À lancer depuis l'onglet **Actions** de GitHub (première installation ou réinitialisation serveur).

### Secrets GitHub requis

Configurer dans **Settings → Secrets and variables → Actions** :

| Secret | Description |
|--------|-------------|
| `OVH_FTP_SERVER` | Serveur FTP OVH (`ftp.clusterXXX.hosting.ovh.net`) |
| `OVH_FTP_USERNAME` | Identifiant FTP |
| `OVH_FTP_PASSWORD` | Mot de passe FTP |
| `OVH_DB_PASSWORD` | Mot de passe MySQL (`anrservipubanrh`) |
| `STAGING_GATE_USER` | Identifiant du verrou staging |
| `STAGING_GATE_PASSWORD` | Mot de passe du verrou staging |
| `DEPLOY_UNZIP_TOKEN` | Token pour déploiement complet (archive zip) |

---

## Infrastructure staging

| Élément | Valeur |
|---------|--------|
| **Domaine** | `pub.anrh.fr` |
| **Dossier FTP OVH** | `pub.anrh.fr/` (à la racine FTP, **pas** dans `www/`) |
| **Base MySQL** | `anrservipubanrh` |
| **Serveur MySQL** | `anrservipubanrh.mysql.db` |

### DNS (Gandi — zone `anrh.fr`)

| Type | Nom | Valeur |
|------|-----|--------|
| A | `pub` | `213.186.33.17` |
| A | `www.pub` | `213.186.33.17` |
| AAAA | `pub` | `2001:41d0:1:1b00:213:186:33:17` |
| AAAA | `www.pub` | `2001:41d0:1:1b00:213:186:33:17` |
| TXT | `ovhcontrol` | _(valeur fournie par OVH)_ |

### SSL

Activer **Let's Encrypt** dans l'espace client OVH pour `pub.anrh.fr` et `www.pub.anrh.fr`, puis forcer la redirection HTTP → HTTPS.

---

## Verrou staging

Le site staging est protégé par un **modal identifiant / mot de passe** et exclu des moteurs de recherche (`noindex`, `robots.txt`).

**Déconnexion :** `https://pub.anrh.fr/?anrh_staging_logout=1`

### Retirer le verrou à la mise en production

1. Supprimer `wp-content/mu-plugins/anrh-staging-gate.php`
2. Retirer du `wp-config.php` :
   ```php
   define( 'ANRH_STAGING_GATE', ... );
   define( 'ANRH_STAGING_USER', ... );
   define( 'ANRH_STAGING_PASSWORD', ... );
   ```
3. Passer `WP_ENVIRONMENT_TYPE` à `'production'`

---

## Fichiers exclus du dépôt

- `wp-config.php` (secrets locaux)
- exports SQL (`export*.sql`)
- sauvegardes UpdraftPlus (`wp-content/updraft/`)

Ne jamais committer de mots de passe, clés API ou dumps de base de données.

---

## Équipe

**ANRH Peyruis** — [anrh.fr](https://www.anrh.fr)

---

## Licence

Thème et code custom : usage interne ANRH.  
WordPress et plugins tiers : licences respectives (GPL, etc.).
