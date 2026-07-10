-- À exécuter dans phpMyAdmin sur la base anrservipubanrh si besoin
UPDATE wp_options SET option_value = 'https://pub.anrh.fr' WHERE option_name = 'home';
UPDATE wp_options SET option_value = 'https://pub.anrh.fr' WHERE option_name = 'siteurl';

-- Thème actif (évite l'erreur « twentytwentyfive n'existe pas »)
UPDATE wp_options SET option_value = 'anrhpub_theme' WHERE option_name IN ('template', 'stylesheet');

SELECT option_name, option_value FROM wp_options WHERE option_name IN ('home', 'siteurl', 'template', 'stylesheet');
