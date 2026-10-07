<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cms_nhomg' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3307' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',         'hh57={Qp:-I@=8OmCU{j|]?T!UVVcfmL)^=C|!|j#J3R6=_$TXW~nX46bmtS4+k7' );
define( 'SECURE_AUTH_KEY',  '0Enyt<oMY}MH9L=tgc?Z[QmZ`?@<+g$Jiluy3{xv|:I}IikYb?<w}x!YN =F)aqs' );
define( 'LOGGED_IN_KEY',    'Ure{ pDdgcFVyQ7$&TcOAS`cz&<A|qHPwlPii,a#Uk$xtV<_[Az+}bXKg`sRB{)F' );
define( 'NONCE_KEY',        'H^dLp}g!hOL{INh_!1#2[k;~y8T[u>|+:(H-wY/+C?&btk1[v!)~Ak2:r=oY:!R?' );
define( 'AUTH_SALT',        'UM7S~7h(G8VP46^%; xJ8XMLYWbm-N$My#34`,t56}yP.l7~9ATmm,2;{1.Sa|nJ' );
define( 'SECURE_AUTH_SALT', 'N=x2;AwH!f%dOt|k.DVe$<|XJv|%~]eXlI5hAGI4@XTtNt7kytLV@XW0]hc;Krtj' );
define( 'LOGGED_IN_SALT',   'Ja(!aUZ$#]x??tCPh#Hf7RAc}0P?p A9<=$:g!EAUPj76va@?w,x<BxjW?e+-6=?' );
define( 'NONCE_SALT',       '~wwEV*dx4.DEvxLA6D4Zu/tX;g0~W,y|4B5%o[I{Vl(fT@lAij{LS)5((6Q,=0K;' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
