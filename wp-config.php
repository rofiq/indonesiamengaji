<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'indonesiamengaji');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'GEfUo.KjRe6(FsS(MJ:{><MO44,8Ar~btSQZczc3a`1Yb97`##Xhm~~s]LS)O^?;');
define('SECURE_AUTH_KEY',  'iz^c<pvb7/ v+b;yx<Z~lbpH&SWSR7QV+]2zf_%b}W!O6<S2hYLv9y]D?=+xuc{x');
define('LOGGED_IN_KEY',    'kh-,/%I^8w_wFSh&vH&]CA.#VwB_Q+&+7YK`=.yt<l|((-6E9$>@d!k!([b*{x5N');
define('NONCE_KEY',        'gF_:c7[it|hV*o{CT(@c3@,7pa/[2T9`~H RxG4y/R5,+hI([+A@2!r5[l!.8?%v');
define('AUTH_SALT',        '#DCci.zc]AmwE9oOKOTyhvUMNBzcACN70/s`]Y`:6KjR_C$O9r@,.uYa<:dttVK)');
define('SECURE_AUTH_SALT', 'o^Ua=v![tb)Nrs0ByE%VP;<[K@3SbY 1-Y|1E^PW%yk&!qJK{RzMj+U ?CgR!(c2');
define('LOGGED_IN_SALT',   'AvM=`)kjnWlAVPczZj.],=T?D!cj|^;zEB@%_/gQBh-{GFh.3@R{62%^V8EhRaOk');
define('NONCE_SALT',       'wjuqsvHO9yME3?G:oE*x-7{$lZVhc;f0{!EOQP2&@.q:pO<~@*n4yTt_>;0jh}A8');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
