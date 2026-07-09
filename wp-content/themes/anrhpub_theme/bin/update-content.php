<?php
require dirname( __DIR__, 4 ) . '/wp-load.php';

delete_option( 'anrhpub_content_version' );
anrhpub_maybe_update_page_content();

echo "Contenu pages mis a jour (ANRH).\n";
