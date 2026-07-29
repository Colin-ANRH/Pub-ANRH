-- Vérifie / corrige les URLs de la base STAGING (anrservistgpub).
UPDATE wp_options SET option_value = 'https://staging-pub.anrh.fr' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'https://staging-pub.anrh.fr' WHERE option_name = 'siteurl';

-- Contrôle
SELECT option_name, option_value FROM wp_options WHERE option_name IN ('home', 'siteurl', 'template', 'stylesheet');
