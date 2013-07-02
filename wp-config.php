<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'weddingsite');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '!Hxdr5x41*~>BQh;+<LlVlag.CL{Brc_5ehKmZ:$#i4>N<*Dt{4XSyI3+TOitE(_');
define('SECURE_AUTH_KEY',  'm*pD3->7h^v+])7Q1S=#bNeT0W(Tvty|S-eV84yvg~Qp+S7t$V|-^s.|+?,zChQ<');
define('LOGGED_IN_KEY',    'D,@5AZM/J|bKK&]eyvBS~N-VZOEs{JEP2#R`f_;0WWR7xj9!>JADXd&(zZ+K4r6P');
define('NONCE_KEY',        '6X Z}7/<gac4V-|lYY{QWUBFLsxw~-;xGD8~EAX4|*jgf)B]0(~n2beUFjg~osH ');
define('AUTH_SALT',        'B$lR|og3(P1f&]0`{VKY}K|+=U5ov58S+Z*;emQpC(i]r>&;KuF2twXxOz^wc63w');
define('SECURE_AUTH_SALT', '9M/Ip|>9]0IsMYX0ew!l2o[Z2:~*WAlQMz3;(rvNOq5`hM`JX$7^h9mQe=d0xYKN');
define('LOGGED_IN_SALT',   '$-`.g0G&pfZM=ai,C6K+gv<UF8LNApvjcbnpD|I3zia45:+&/HRq#/S+hSlXE?|G');
define('NONCE_SALT',       '/ez*yP+{(nT>EW=C?t*qvZ%@X)([/l>Fov&J//L@WbN.xCy!T=C*.EyT4Fi)a%+{');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* Multisite */
define('WP_ALLOW_MULTISITE', true);
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'localhost');
define('PATH_CURRENT_SITE', '/~eric/weddingsite/web/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

