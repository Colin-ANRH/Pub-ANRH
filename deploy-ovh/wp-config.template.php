<?php
/**
 * Template wp-config — staging OVH pub.anrh.fr
 * Utilisé par deploy-ovh/build-wp-config.php (GitHub Actions).
 */

define( 'DB_NAME', 'anrservipubanrh' );
define( 'DB_USER', 'anrservipubanrh' );
define( 'DB_PASSWORD', 'VOTRE_MDP_OVH' );
define( 'DB_HOST', 'anrservipubanrh.mysql.db' );

define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         '/Im|XCd4Jd{!%FiuC]N]O V!m;Y{TkZ_$lAO7ZOvAo<8>rGo8wQeV3e{.jl#O;p6' );
define( 'SECURE_AUTH_KEY',  'a4*9RpO5D4Y]Pf/Xovc|6yyz~kzJp!]_W.P(7IbN}HQ$vm;:jX!@:N`lAI?tNTQQ' );
define( 'LOGGED_IN_KEY',    'Uw:a}W$rAOQtF-voK/kPWV_8$-tIp Zd}x@_#Fb0+YIC+2&0@[.0Tv@t:saa1+@}' );
define( 'NONCE_KEY',        'u^%LlVwH%V)(rWOvRv@,-Auv}ataYen)iKZkQvXs>MCy03p]l_Q#60vsL/th7E$2' );
define( 'AUTH_SALT',        '^}l[HVM*2; $%bi(%Qiw%vrd)q5p%#E2cGqmX>;O&_@fi>G:Sd@A7E[RD*1B?(pJ' );
define( 'SECURE_AUTH_SALT', ']E%wHvQ{(Fzh1fD~>76S+nnDQ%yi&yeug%[I6/, 7j23Ik-/XSQrh2+:.=$(E^Bi' );
define( 'LOGGED_IN_SALT',   'Q,P=aX[/MZxP*LvT#wij(uqb&J?)UBQFP:bP>a(HZcnfWswnVRo(P+F7d!& p$=u' );
define( 'NONCE_SALT',       ',6jMsYvCv<zN$pAZo0 ay+LXK)sfNx?f(lni~[Zk<0PNh*fAa8RG{;16hcsBeX,z' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );
define( 'WP_ENVIRONMENT_TYPE', 'staging' );

// Verrou staging — retirer à la mise en production.
define( 'ANRH_STAGING_GATE', VOTRE_STAGING_GATE_ENABLED );
define( 'ANRH_STAGING_USER', 'VOTRE_STAGING_USER' );
define( 'ANRH_STAGING_PASSWORD', 'VOTRE_STAGING_PASSWORD' );

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
