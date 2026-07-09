<?php
require dirname( __DIR__, 4 ) . '/wp-load.php';

delete_option( 'anrhpub_images_version' );
anrhpub_seed_product_images();
update_option( 'anrhpub_images_version', ANRHPUB_IMAGES_VERSION );

$total  = 0;
$thumbs = 0;
$query  = new WP_Query( array( 'post_type' => 'anr_product', 'posts_per_page' => 20 ) );

while ( $query->have_posts() ) {
	$query->the_post();
	$total++;
	if ( has_post_thumbnail() ) {
		$thumbs++;
	}
}

echo "Images: {$thumbs}/{$total}\n";
