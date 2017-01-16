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
define('DB_NAME', 'db648369029');

/** MySQL database username */
define('DB_USER', 'dbo648369029');

/** MySQL database password */
define('DB_PASSWORD', '1q2w3e4r5t6y');

/** MySQL hostname */
define('DB_HOST', 'db648369029.db.1and1.com');

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
define('AUTH_KEY',         'LE-:x&69@vwCe3EhlO&yW=K-|@~-=c?z$}yljAs3NkD 7IH2nx{fuj7rJek<__]S');
define('SECURE_AUTH_KEY',  'yLbl_|b-hOReXs!X&6aE6]a}?;~|n )Y:nO-!CtHL7$&v|6r|Ul<V/Ff@Lq>X@^5');
define('LOGGED_IN_KEY',    'naL _AZ%!di,#l_gN?AY<!Mb@SyvNPbI76r6c@5@CFFu_]:+a2v>(|,RfHOSOAV.');
define('NONCE_KEY',        'InZK*ppWFT_K<#lsV&lCp2j9#Tlv}GbhIez1]>~LOqiX]EJ,(:,R5=}FY,]H&h$$');
define('AUTH_SALT',        'Uxm&Fn8!]`OKk^c}7<@k9@8Iq.|hDB]0=Z%U%EBvc:mgheF=G#MU@|)F29q9qT2B');
define('SECURE_AUTH_SALT', 'e}(G<ltht8%C^U$X$f5y*q9YE((W`]b`i)0oRpU8sn-.r^cA|BI@eUxeKa{2Q4fs');
define('LOGGED_IN_SALT',   'fYk6IJxM.v~bD@j-w<x?`3G#<z5kUVhA>di|}_bi]kHe>Lth$~?|da?3O;/&@kzg');
define('NONCE_SALT',       'dg/9b2mUs*}5( GO 6T@3+++AM2tac;_y4Ey4WV$V^{:^eqof185+>7I^rfx{|ft');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'retokids_';

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

define('FS_METHOD', 'direct');
