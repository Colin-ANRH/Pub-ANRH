# Migration PrestaShop → ANRPUB (v2)

Script : `tools/migrate-prestashop.php` (version **2.0.0**)

## 1) Prérequis

- Dump MySQL PrestaShop importé en local (ou accès distant)
- WordPress ANRPUB fonctionnel (Apache + `mysqli` pour `wp-load.php`)
- PHP CLI avec extensions `pdo_mysql` et `mysqli`
- Images produits accessibles depuis `SOURCE_BASE_URL` (sideload HTTP)

## 2) Configuration

Éditer les constantes en tête du script :

| Constante | Description |
|-----------|-------------|
| `SOURCE_DB_*` | Connexion base PrestaShop |
| `SOURCE_DB_PREFIX` | Préfixe tables (`ps_`) |
| `SOURCE_LANG_ID` | Langue FR (souvent `1`) |
| `SOURCE_SHOP_ID` | Boutique (souvent `1`) |
| `SOURCE_BASE_URL` | Ancien site, ex. `https://anr-pub.fr` |
| `HTTP_TOKEN` | Token si exécution navigateur |
| `ORDER_STATUS_MAP` | IDs `ps_order_state` → statuts WP |

## 3) Diagnostic (obligatoire avant import)

```bash
php wp-content/themes/anrhpub_theme/tools/migrate-prestashop.php --run=1 --step=diagnose
```

Affiche les comptages par table et la liste des **statuts commande Presta** avec le mapping actuel — ajuster `ORDER_STATUS_MAP` si besoin.

## 4) Dry-run puis import

```bash
php wp-content/themes/anrhpub_theme/tools/migrate-prestashop.php --run=1 --step=all --dry-run=1
php wp-content/themes/anrhpub_theme/tools/migrate-prestashop.php --run=1 --step=all
```

### Étapes unitaires

| Étape | Contenu |
|-------|---------|
| `diagnose` | Comptages + statuts commande |
| `categories` | → taxonomie `anr_category` |
| `colors` | Attributs « Couleur » → `anr_color` |
| `products` | Produits complets (voir ci-dessous) |
| `clients` | Comptes + adresses + SIRET/société |
| `newsletter` | → CPT newsletter (helper thème) |
| `orders` | Commandes + adresse livraison |
| `credits` | Avoirs `ps_order_slip` → `anr_credit` |
| `cms` | Pages CMS Presta → pages WP |
| `quotes` | Paniers client → brouillons `anr_quote` |
| `redirects` | Export CSV des URLs anciennes |
| `all` | Tout dans l’ordre + export redirections |

## 5) Détail de l’import v2

### Produits (`anr_product`)

- Titre, slug, description courte/longue
- Référence, prix HT, badge promo si `on_sale`
- **Toutes** les catégories (`category_product`)
- Caractéristiques Presta → meta `anr_details` (liste HTML)
- Stock global (`anr_stock_status`, `anr_stock_qty`) + stock par couleur (`anr_product_color_stock`)
- Image de couverture + galerie (`anr_product_gallery`)
- Mapping persistant : option `anrhpub_prestashop_migration_map`

### Clients

- Rôle `anr_client`, compte `approved` si actif Presta
- Société, SIRET (si colonnes présentes)
- **Adresses** → meta `anrhpub_addresses` + adresse de livraison par défaut
- Mot de passe : **non récupérable** — meta `anrhpub_must_reset_password` + email « mot de passe oublié » après migration

### Commandes (`anr_order`)

- Numéro, client, statut, total HT, lignes JSON
- Snapshot adresse livraison (`anr_delivery_address`)
- Lien Presta : meta `anrhpub_ps_order_id`

### Avoirs (`anr_credit`)

- Depuis `ps_order_slip`, liés à la commande WP migrée

### Pages CMS

- Contenu Presta → pages WordPress (slug `link_rewrite`)

### Paniers → devis

- Chaque panier avec client et lignes → devis **brouillon** (historique indicatif, pas un devis validé Presta)

### Redirections 301

- Chemins anciens enregistrés dans l’option `anrhpub_prestashop_url_redirects`
- Après import : `step=redirects` ou `all` génère  
  `wp-content/uploads/anrhpub-prestashop-redirects.csv`  
  Importable dans le plugin **Redirection** (colonne ancienne URL → nouvelle URL).

## 6) Exécution HTTP (staging uniquement)

```
/wp-content/themes/anrhpub_theme/tools/migrate-prestashop.php?run=1&token=VOTRE_TOKEN&step=diagnose
```

Changer `HTTP_TOKEN` avant toute utilisation. **Ne pas laisser ce fichier accessible en production** sans protection (IP, Basic Auth, suppression après migration).

## 7) Reprise / idempotence

Le script peut être relancé : les IDs Presta → WP sont mémorisés. Les enregistrements existants (même référence, même email, même n° commande) sont mis à jour plutôt que dupliqués.

Pour repartir de zéro : supprimer l’option `anrhpub_prestashop_migration_map` (et éventuellement les contenus WP importés).

## 8) Limites connues

- Fiches techniques PDF : non extraites automatiquement (à reposer à la main ou module export Presta)
- Tarifs spécifiques / grilles B2B Presta : non importés (grilles ANRPUB à reconfigurer)
- Messages / fil de discussion devis Presta : non migrés
- Schéma Presta variable selon version/modules — utiliser `diagnose` et adapter les requêtes si une table manque
- Paniers → devis : conversion indicative (tous les paniers historiques, pas seulement les devis officiels)

## 9) Checklist post-migration

1. Vérifier comptages admin (produits, clients, commandes, avoirs)
2. Tester 5 fiches produit (images, stock, couleurs)
3. Connexion client test + « Mot de passe oublié »
4. Importer le CSV redirections
5. UpdraftPlus : sauvegarde hebdo activée sur le nouveau site
6. Couper l’accès public à `migrate-prestashop.php`
