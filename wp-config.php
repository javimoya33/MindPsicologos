<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wcepoyncomu2022' );

/** MySQL database username */
define( 'DB_USER', 'wcepoyncomu2022' );

/** MySQL database password */
define( 'DB_PASSWORD', 'loB3lpic79BHe25' );

/** MySQL hostname */
define( 'DB_HOST', 'wcepoyncomu2022.mysql.db' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

define('WP_HOME','https://www.mindpsicologos.com/');
define('WP_SITEURL','https://www.mindpsicologos.com/');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '@Nh}vm3t%Hg^[5E(,{8JH,L=Kvngg)AcfbU=LwH{wa#p]Dnc+:b $RtGF<A~3;4j' );
define( 'SECURE_AUTH_KEY',  'pi6lcBB :Q>-1D2#J6eQPUNTz>5>2w>hJ9n$3&v0ngJyDE]SL8my|+$1Uc-T! 5y' );
define( 'LOGGED_IN_KEY',    '#9]FV?dzU|!R>L~5+fXd*aLBz(lqZHeR[4p)J!-Xsw0=~FV[&)9S`1<Y~{g8Q]#@' );
define( 'NONCE_KEY',        'XDqVc,!0+X0o.?TMy{uz&q;Yq_+@6byg9G|r,<!=Kctww5KhO4p06:xPb)dzorNF' );
define( 'AUTH_SALT',        'nL_/ESNooCL62l>~{(Pfj/Hta/_H`Q&I1QjZ&?w=G;L]#=zz#<J}CX?:tJ?#DJ=z' );
define( 'SECURE_AUTH_SALT', '~~qorbbF<J|YVu%_<WEsKc#.@m|+]PmA~ZBHCycW9 lOw3FOi;]g;HFmYKO#Zc;C' );
define( 'LOGGED_IN_SALT',   'n-z-Bkghb$:BA~.5|U4xzOi$P2A(4T`pk{Df`qpRU!F{>Cs+H=,/6e>c9^kB9t0<' );
define( 'NONCE_SALT',       'ce]fAaCh>EBQIk`]Ls!L$nmg`cT/}n/x_]Xwh!=`yA-2=a2hakDOJGYd-Kr7#QdT' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'mind_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
define( 'WP_MEMORY_LIMIT', '96M' );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
