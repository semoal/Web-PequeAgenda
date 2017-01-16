<?php
/*
Plugin Name: Push Notification System
Plugin URI: https://smartiolabs.com/product/push-notification-system
Description: Provides a complete solution to send desktop and mobile push notification messages to platforms iOS, Android, Chrome, Safari, Firefox, Windows Phone 8, Windows 10 and BlackBerry 10.
Author: Smart IO Labs
Version: 5.9.1
Author URI: https://smartiolabs.com
*/

define('smpush_dir', plugin_dir_path(__FILE__));
define('smpush_imgpath', plugins_url('/images', __FILE__));
define('smpush_csspath', plugins_url('/css', __FILE__));
define('smpush_jspath', plugins_url('/js', __FILE__));
define('SMPUSHVERSION', 5.91);
define('smpush_env', 'production');//debug, production, demo

@date_default_timezone_set(get_option('timezone_string'));

include(smpush_dir.'/class.helper.php');
include(smpush_dir.'/class.controller.php');
include(smpush_dir.'/class.sendpush.php');
include(smpush_dir.'/class.windowsphone.php');
include(smpush_dir.'/class.universal.windows.php');
include(smpush_dir.'/class.blackberry.php');
include(smpush_dir.'/class.sendcron.php');
include(smpush_dir.'/class.events.php');
include(smpush_dir.'/class.widget.php');
include(smpush_dir.'/class.modules.php');
include(smpush_dir.'/class.api.php');
include(smpush_dir.'/class.autoupdate.php');
require(smpush_dir.'/class.geolocation.php');
require(smpush_dir.'/class.browserpush.php');
require(smpush_dir.'/class.build.profile.php');
require(smpush_dir.'/class.localization.php');
require(smpush_dir.'/class.event.manager.php');

register_activation_hook(__FILE__, 'smpush_install');
register_uninstall_hook(__FILE__, 'smpush_uninstall');

add_action('init', 'smpush_start');
add_action('wpmu_new_blog', 'smpush_new_blog_installed', 99, 6);
add_filter('cron_schedules', array('smpush_controller', 'register_cron'));

//Push notification for custom events
add_action('transition_post_status', array('smpush_events', 'queue_event'), 99, 3);
add_action('wp_insert_comment', array('smpush_events', 'new_comment'), 99, 2);
add_action('comment_unapproved_to_approved', array('smpush_events', 'comment_approved'));
add_action('add_meta_boxes', array('smpush_events', 'build_meta_box'));
add_action('widgets_init', array('smpush_modules', 'widget'));
add_action('plugins_loaded', array('smpush_localization', 'load_textdomain'));
add_action('bp_notification_after_save', array('smpush_events', 'buddy_notifications'), 99, 1);
add_action('bp_activity_after_save', array('smpush_events', 'buddy_activity'), 99, 1);

function smpush_start(){
  global $wpdb;
  define('SMPUSHTBPRE', $wpdb->prefix);
  $smpush_controller = new smpush_controller();

  $smpush_version = get_option('smpush_version');
  if($smpush_version != SMPUSHVERSION){
    smpush_upgrade($smpush_version);
  }

  add_action('template_redirect', array($smpush_controller, 'start_fetch_method'));
  add_action('deleted_user', array('smpush_api', 'delete_relw_app'));
  add_action('admin_menu', array($smpush_controller, 'build_menus'));
  add_action('admin_enqueue_scripts', 'smpush_scripts');
  add_action('admin_enqueue_scripts', array('smpush_localization', 'javascript'));
  add_action('wp_enqueue_scripts', 'smpush_frontend_scripts');
  add_action('smpush_cron_fewdays', array($smpush_controller, 'check_update_notify'));
  add_action('smpush_update_counters', array('smpush_controller', 'update_all_counters'));
  add_action('wp_head', array('smpush_browser_push', 'start_all_lisenter'), 0);

  add_filter('query_vars', array($smpush_controller, 'register_vars'));
}

function smpush_frontend_scripts(){
  wp_register_style('smpush-sweetalert-plugin-css', smpush_csspath.'/sweetalert.css', array(), SMPUSHVERSION);
  wp_enqueue_style('smpush-sweetalert-plugin-css');
  wp_register_script('smpush-sweetalert-plugin-script', smpush_jspath.'/sweetalert.min.js', array('jquery'), SMPUSHVERSION);
  wp_enqueue_script('smpush-sweetalert-plugin-script');
}

function smpush_scripts(){
  wp_register_script('smpush-progbarscript', smpush_jspath.'/jquery.progressbar.js', array('jquery'), SMPUSHVERSION);
  wp_register_script('smpush-mainscript', smpush_jspath.'/smio-function.js', array('jquery'), SMPUSHVERSION);
  wp_register_script('smpush-plugins', smpush_jspath.'/smio-plugins.js', array('jquery'), SMPUSHVERSION);
  wp_register_script('smpush-select2-js', smpush_jspath.'/select2.min.js', array('jquery'), SMPUSHVERSION);
  wp_register_style('smpush-mainstyle', smpush_csspath.'/autoload-style.css', array(), SMPUSHVERSION);
  wp_register_style('smpush-style', smpush_csspath.'/smio-style.css', array(), SMPUSHVERSION);
  wp_register_style('smpush-progbarstyle', smpush_csspath.'/smio-progressbar.css', array(), SMPUSHVERSION);
  wp_register_style('smpush-select2-style', smpush_csspath.'/select2.min.css', array(), SMPUSHVERSION);
  wp_enqueue_style('smpush-mainstyle');
  if(is_rtl()){
    wp_register_style('smpush-rtl', smpush_csspath.'/smio-style-rtl.css', array(), SMPUSHVERSION);
  }
  if(get_bloginfo('version') > 3.7){
    wp_register_style('smpush-fix38', smpush_csspath.'/autoload-style38.css', array(), SMPUSHVERSION);
    wp_enqueue_style('smpush-fix38');
  }
}

function smpush_new_blog_installed($blog_id, $user_id, $domain, $path, $site_id, $meta) {
  smpush_install($blog_id);
}

function smpush_install($blog_id = false){
  if($blog_id !== false){
    switch_to_blog($blog_id);
  }
  if(get_option('smpush_version') > 0){
    if($blog_id !== false){
      restore_current_blog();
    }
    return;
  }
  smpush_uninstall_code();
  global $wpdb, $wp_rewrite;
  $wpdb->hide_errors();
  require_once(ABSPATH.'wp-admin/includes/upgrade.php');
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `send_type` enum('sendnow','cronsend','feedback') NOT NULL,
  `message` mediumtext NOT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endtime` datetime DEFAULT NULL,
  `options` text,
  `desktop` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_archive_reports` (
  `msgid` int(11) NOT NULL,
  `report_time` varchar(15) NOT NULL,
  `report` text NOT NULL,
  KEY `msgid` (`msgid`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_feedback` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `tokens` longtext NOT NULL,
  `feedback` longtext NOT NULL,
  `device_type` set('ios','android','ios_invalid','chrome','firefox') NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `platid` int(11) NOT NULL,
  `action` varchar(10) NOT NULL,
  `stat` int(11) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `device_type` varchar(10) NOT NULL,
  `feedback` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `device_type` (`device_type`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_cron_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `device_type` varchar(10) NOT NULL,
  `sendtime` varchar(50) NOT NULL,
  `sendoptions` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sendtime` (`sendtime`),
  KEY `device_type` (`device_type`),
  KEY `sendoptions` (`sendoptions`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `md5device_token` varchar(32) NOT NULL,
  `device_type` VARCHAR(10) NOT NULL,
  `information` TINYTEXT NOT NULL,
  `latidude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(11, 8) NOT NULL,
  `gps_time_update` VARCHAR(15) NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`md5device_token`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` mediumtext NOT NULL,
  `private` tinyint(1) NOT NULL,
  `default` tinyint(1) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_relation` (
  `channel_id` int(11) NOT NULL,
  `token_id` int(11) NOT NULL,
  KEY `channel_id` (`channel_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_events` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(200) NOT NULL,
    `event_type` varchar(50) NOT NULL,
    `post_type` varchar(50) NOT NULL,
    `message` text NOT NULL,
    `notify_segment` varchar(50) NOT NULL,
    `userid_field` varchar(100) NOT NULL,
    `conditions` text NOT NULL,
    `desktop_link` BOOLEAN NOT NULL,
    `ignore` tinyint(1) NOT NULL,
    `status` tinyint(1) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_events_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `post_id` int(11) NOT NULL,
    `old_status` varchar(50) NOT NULL,
    `new_status` varchar(50) NOT NULL,
    `post` mediumtext NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_relation` ADD  `connection_id` INT NOT NULL");
  $wpdb->query("UPDATE `".$wpdb->prefix."push_relation` SET `connection_id`='1'");
  $chancount = $wpdb->get_var("SELECT id FROM `".$wpdb->prefix."push_channels` WHERE id='1'");
  if(!$chancount){
    $wpdb->query("INSERT INTO `".$wpdb->prefix."push_channels` (`id`, `title`, `private`, `default`) VALUES (1, '".__('Main Channel', 'smpush-plugin-lang')."', 0, 1);");
  }
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_connection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` mediumtext NOT NULL,
  `dbtype` enum('localhost','remote') NOT NULL,
  `dbhost` varchar(50) NOT NULL DEFAULT 'localhost',
  `dbname` varchar(50) NOT NULL,
  `dbuser` varchar(50) NOT NULL,
  `dbpass` varchar(50) NOT NULL,
  `tbname` varchar(50) NOT NULL,
  `id_name` varchar(50) NOT NULL,
  `token_name` varchar(50) NOT NULL,
  `md5token_name` varchar(50) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `ios_name` varchar(20) NOT NULL,
  `android_name` varchar(20) NOT NULL,
  `wp_name` varchar(20) NOT NULL,
  `bb_name` varchar(20) NOT NULL,
  `chrome_name` varchar(20) NOT NULL,
  `safari_name` varchar(20) NOT NULL,
  `firefox_name` varchar(20) NOT NULL,
  `wp10_name` varchar(20) NOT NULL,
  `info_name` VARCHAR(50) NOT NULL,
  `latidude_name` VARCHAR(50) NOT NULL,
  `longitude_name` VARCHAR(50) NOT NULL,
  `gpstime_name` VARCHAR(50) NOT NULL,
  `active_name` VARCHAR( 20 ) NOT NULL,
  `counter` int(11) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_desktop_messages` (
    `msgid` int(11) NOT NULL,
    `token` varchar(32) NOT NULL,
    `type` varchar(10) NOT NULL,
    `timepost` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  
  $wpdb->query("INSERT INTO `".$wpdb->prefix."push_connection` VALUES (1, '".__('Default Connection', 'smpush-plugin-lang')."', '".__('Plugin default connection', 'smpush-plugin-lang')."', 'localhost', '', '', '', '', '{wp_prefix}push_tokens', 'id', 'device_token', 'md5device_token', 'device_type', 'ios', 'android', 'wp', 'bb', 'chrome', 'safari', 'firefox', 'wp10', 'information', 'latidude', 'longitude', 'gps_time_update', 'active', 0)");
  $wpdb->insert($wpdb->prefix.'push_events', array('title' => __('Notify all members when administrator published a new post', 'smpush-plugin-lang'), 'event_type' => 'publish', 'post_type' => 'post', 'message' => __('We have published a new topic `{$post_title}`', 'smpush-plugin-lang'), 'notify_segment' => 'all', 'status' => 1));
  $wpdb->insert($wpdb->prefix.'push_events', array('title' => __('Notify author when administrator approved and published his post', 'smpush-plugin-lang'), 'event_type' => 'approve', 'post_type' => 'post', 'message' => __('Your post `{$post_title}` is approved and published', 'smpush-plugin-lang'), 'notify_segment' => 'post_owner', 'status' => 1));
  $wpdb->insert($wpdb->prefix.'push_events', array('title' => __('Notify all users subscribed in a post when has got a new update', 'smpush-plugin-lang'), 'event_type' => 'update', 'post_type' => 'post', 'message' => __('The post you subscribed in `{$post_title}` got new updates', 'smpush-plugin-lang'), 'notify_segment' => 'post_commenters', 'status' => 1));
    
  $setting = array(
  'auth_key' => '',
  'complex_auth' => 0,
  'push_basename' => 'push',
  'def_connection' => 1,
  'apple_sandbox' => 0,
  'stop_summarize' => 0,
  'gmaps_apikey' => '',
  'apple_passphrase' => '',
  'apple_cert_path' => '',
  'apple_appid' => '',
  'apple_api_ver' => 'http2',
  'google_apikey' => '',
  'chrome_apikey' => '',
  'desktop_status' => '0',
  'desktop_debug' => '0',
  'desktop_modal' => '0',
  'desktop_logged_only' => '0',
  'desktop_modal_title' => __('Keep me posted', 'smpush-plugin-lang'),
  'desktop_modal_message' => __('Give us a permission to receive push notification messages and we will keep you posted !', 'smpush-plugin-lang'),
  'safari_web_id' => '',
  'desktop_btn_subs_text' => __('Enable Push Messages', 'smpush-plugin-lang'),
  'desktop_btn_unsubs_text' => __('Disable Push Messages', 'smpush-plugin-lang'),
  'desktop_modal_cancel_text' => __('Ignore', 'smpush-plugin-lang'),
  'desktop_modal_saved_text' => __('Saved', 'smpush-plugin-lang'),
  'desktop_deficon' => '',
  'desktop_chrome_status' => '0',
  'chrome_projectid' => '',
  'desktop_firefox_status' => '0',
  'desktop_safari_status' => '0',
  'safari_cert_path' => '',
  'safari_certp12_path' => '',
  'safari_icon' => '',
  'safari_passphrase' => '',
  'ios_titanium_payload' => 0,
  'android_titanium_payload' => 0,
  'purchase_code' => '',
  'wp_authed' => '0',
  'wp_cert' => '',
  'wp_pem' => '',
  'wp10_pack_sid' => '',
  'wp10_client_secret' => '',
  'wp_cainfo' => '',
  'bb_appid' => '',
  'bb_password' => '',
  'bb_cpid' => '',
  'bb_dev_env' => 0,
  'android_corona_payload' => 0,
  'geo_provider' => 'ip-api.com',
  'db_ip_apikey' => '',
  'auto_geo' => 1,
  'e_post_chantocats' => 0,
  'e_appcomment' => 0,
  'e_newcomment' => 0,
  'e_usercomuser' => 0,
  'e_appcomment_body' => __('Your comment "{comment}" is approved and published now', 'smpush-plugin-lang'),
  'e_newcomment_body' => __('Your post "{subject}" have new comments, Keep in touch with your readers', 'smpush-plugin-lang'),
  'e_usercomuser_body' => __('Someone reply on your comment "{comment}"', 'smpush-plugin-lang'),
  'bb_notify_friends' => 0,
  'bb_notify_messages' => 0,
  'bb_notify_activity' => 0,
  'bb_notify_activity_admins_only' => 0,
  'bb_notify_xprofile' => 0,
  );
  add_option('smpush_options', $setting);
  add_option('smpush_version', SMPUSHVERSION);
  add_option('smpush_instant_send', array());
  add_option('smpush_cron_stats', array());
  add_option('smpush_stats', array());
  add_option('smpush_history', '');
  
  $wp_rewrite->flush_rules(false);
  
  if($blog_id !== false){
    restore_current_blog();
  }
}

function smpush_upgrade($version){
  require_once(ABSPATH.'wp-admin/includes/upgrade.php');
  global $wpdb;
  $wpdb->hide_errors();
  if($version < 2.0){
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_feedback` (
    `id` tinyint(4) NOT NULL AUTO_INCREMENT,
    `tokens` longtext NOT NULL,
    `feedback` longtext NOT NULL,
    `device_type` set('ios','android','ios_invalid') NOT NULL,
    PRIMARY KEY (`id`)
    )";
    dbDelta($sql);
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_queue` ADD `expire` SMALLINT NOT NULL ,
    ADD `ios_slide` VARCHAR( 40 ) NOT NULL ,
    ADD `feedback` BOOLEAN NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_tokens` ADD `information` TINYTEXT NOT NULL,
    ADD `active` BOOLEAN NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_queue` CHANGE `device_type` `device_type` VARCHAR( 10 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_connection` ADD `info_name` VARCHAR(50) NOT NULL AFTER `android_name`,
    ADD `active_name` VARCHAR(20) NOT NULL AFTER `info_name`");
    $wpdb->query("UPDATE `".$wpdb->prefix."push_connection` SET active_name='active',info_name='information' WHERE id='1'");
    $wpdb->query("UPDATE `".$wpdb->prefix."push_tokens` SET `active`='1'");
    $version = 2.0;
  }
  if($version == 2.0){
    $version = 2.1;
  }
  if($version == 2.1){
    $version = 2.2;
  }
  if($version == 2.2){
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_queue`");
    $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_queue` DROP  `extravalue` ,
    DROP  `extra_type` ,
    DROP  `expire` ,
    DROP  `ios_slide`");
    $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_queue` ADD  `options` MEDIUMTEXT NOT NULL");
    $version = 2.3;
  }
  if($version == 2.3){
    $setting = get_option('smpush_options');
    update_option('smpush_options', unserialize($setting));
    $version = 2.4;
  }
  if($version == 2.4){
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_archive` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `message` mediumtext NOT NULL,
    `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `endtime` datetime DEFAULT NULL,
    `report` mediumtext NOT NULL,
    PRIMARY KEY (`id`)
    )";
    dbDelta($sql);
    $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_queue` DROP  `message` ,DROP  `options`");
    add_option('smpush_history', '');
    $version = 2.5;
  }
  if($version == 2.5){
    $version = 2.6;
  }
  if($version == 2.6){
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_cron_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `token` varchar(255) NOT NULL,
    `device_type` varchar(10) NOT NULL,
    `sendtime` varchar(50) NOT NULL,
    `sendoptions` varchar(50) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sendtime` (`sendtime`),
    KEY `device_type` (`device_type`)
    )";
    dbDelta($sql);
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_queue` ADD INDEX (`device_type`)");
    $setting = get_option('smpush_options');
    $setting['e_apprpost'] = 0;
    $setting['e_appcomment'] = 0;
    $setting['e_newcomment'] = 0;
    $setting['e_usercomuser'] = 0;
    $setting['e_postupdated'] = 0;
    $setting['e_newpost'] = 0;
    $setting['e_apprpost_body'] = __('Your post "{subject}" is approved and published', 'smpush-plugin-lang');
    $setting['e_appcomment_body'] = __('Your comment "{comment}" is approved and published now', 'smpush-plugin-lang');
    $setting['e_newcomment_body'] = __('Your post "{subject}" have new comments, Keep in touch with your readers', 'smpush-plugin-lang');
    $setting['e_usercomuser_body'] = __('Someone reply on your comment "{comment}"', 'smpush-plugin-lang');
    $setting['e_postupdated_body'] = __('The post you subscribed in "{subject}" got updated', 'smpush-plugin-lang');
    $setting['e_newpost_body'] = __('We have published a new topic "{subject}"', 'smpush-plugin-lang');
    update_option('smpush_options', $setting);
    $version = 3;
  }
  if($version == 3){
    $version = 3.1;
  }
  if($version == 3.1){
    $version = 3.2;
  }
  if($version == 3.2){
    $version = 3.3;
  }
  if($version == 3.3){
    $setting = get_option('smpush_options');
    $setting['ios_titanium_payload'] = 0;
    $setting['android_titanium_payload'] = 0;
    update_option('smpush_options', $setting);
    $version = 3.4;
  }
  if($version == 3.4){
    $version = 3.5;
  }
  if($version == 3.5){
    $setting = get_option('smpush_options');
    $setting['complex_auth'] = 0;
    update_option('smpush_options', $setting);
    $version = 3.6;
  }
  if($version == 3.6){
    $setting = get_option('smpush_options');
    $setting['e_post_chantocats'] = 0;
    update_option('smpush_options', $setting);
    $version = 3.7;
  }
  if($version == 3.7){
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_archive` ADD  `transient` VARCHAR( 50 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_cron_queue` ADD INDEX (`sendoptions`)");
    $version = 3.8;
  }
  if($version == 3.8){
    $version = 3.9;
  }
  if($version == 3.9){
    $version = 3.91;
  }
  if($version == 3.91){
    $version = 3.92;
  }
  if($version == 3.92){
    $version = 3.93;
  }
  if($version == 3.93){
    $version = 3.94;
  }
  if($version == 3.94){
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_connection` ADD `latidude_name` varchar(50) NOT NULL AFTER `info_name`, ADD `longitude_name` varchar(50) NOT NULL AFTER `latidude_name`, ADD `gpstime_name` varchar(50) NOT NULL AFTER `longitude_name`;");
    $wpdb->update($wpdb->prefix.'push_connection', array('latidude_name' => 'latidude', 'longitude_name' => 'longitude', 'gpstime_name' => 'gps_time_update'), array('tbname' => '{wp_prefix}push_tokens'));
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_tokens` ADD `latidude` DECIMAL(10, 8) NOT NULL AFTER `information`, ADD `longitude` DECIMAL(11, 8) NOT NULL AFTER `latidude`, ADD `gps_time_update` VARCHAR(15) NOT NULL AFTER `longitude`;");
    $setting = get_option('smpush_options');
    $setting['stop_summarize'] = 0;
    $setting['geo_provider'] = 'telize.com';
    $setting['db_ip_apikey'] = '';
    $setting['auto_geo'] = 1;
    update_option('smpush_options', $setting);
    $version = 4.0;
  }
  if($version == 4.0){
    $version = 4.1;
  }
  if($version == 4.1){
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_tokens` CHANGE `device_type` `device_type` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_connection` ADD `wp_name` VARCHAR(20) NOT NULL AFTER `android_name`, ADD `bb_name` VARCHAR(20) NOT NULL AFTER `wp_name`");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_connection` ADD `md5token_name` VARCHAR(50) NOT NULL AFTER `token_name`");
    $wpdb->update($wpdb->prefix.'push_connection', array('wp_name' => 'wp', 'bb_name' => 'bb', 'md5token_name' => 'md5device_token'), array('tbname' => '{wp_prefix}push_tokens'));
    $setting = get_option('smpush_options');
    $setting['wp_authed'] = '0';
    $setting['wp_cert'] = '';
    $setting['wp_pem'] = '';
    $setting['wp_cainfo'] = '';
    $setting['bb_appid'] = '';
    $setting['bb_password'] = '';
    $setting['bb_cpid'] = '';
    $setting['bb_dev_env'] = 0;
    $setting['android_corona_payload'] = 0;
    $setting['purchase_code'] = '';
    update_option('smpush_options', $setting);
    smpush_move_certs();
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_tokens` ADD `md5device_token` VARCHAR(32) NOT NULL AFTER `device_token`");
    $wpdb->query('UPDATE `'.$wpdb->prefix.'push_tokens` SET `md5device_token`=MD5(`device_token`)');
    $wpdb->query('ALTER TABLE '.$wpdb->prefix.'push_tokens DROP INDEX device_token');
    $wpdb->query('ALTER TABLE '.$wpdb->prefix.'push_tokens ADD INDEX(`md5device_token`)');
    $version = 4.2;
  }
  if($version == 4.2){
    $version = 4.3;
  }
  if($version == 4.3){
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_cron_queue` CHANGE `sendoptions` `sendoptions` INT NOT NULL;");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_feedback` CHANGE `device_type` `device_type` SET('ios','android','ios_invalid','chrome','firefox')");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_archive` DROP `report`,DROP `transient`");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_archive` ADD `options` TEXT NULL DEFAULT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_archive` ADD `send_type` ENUM('sendnow','cronsend','feedback') NOT NULL AFTER `id`;");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_archive` ADD `desktop` VARCHAR(50) NOT NULL");
    $wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_statistics` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` date NOT NULL,
      `platid` int(11) NOT NULL,
      `action` varchar(10) NOT NULL,
      `stat` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    $wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_archive_reports` (
      `msgid` int(11) NOT NULL,
      `report_time` varchar(15) NOT NULL,
      `report` text NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_connection` ADD `chrome_name` VARCHAR(20) NOT NULL AFTER `bb_name`, ADD `safari_name` VARCHAR(20) NOT NULL AFTER `chrome_name`, ADD `firefox_name` VARCHAR(20) NOT NULL AFTER `safari_name`");
    $wpdb->update($wpdb->prefix.'push_connection', array('chrome_name' => 'chrome', 'safari_name' => 'safari', 'firefox_name' => 'firefox'), array('tbname' => '{wp_prefix}push_tokens'));
    add_option('smpush_instant_send', array());
    add_option('smpush_cron_stats', array());
    add_option('smpush_stats', array());
    
    $setting = get_option('smpush_options');
    $setting['chrome_apikey'] = '';
    $setting['desktop_status'] = '0';
    $setting['desktop_modal'] = '0';
    $setting['desktop_modal_title'] = __('Keep me posted', 'smpush-plugin-lang');
    $setting['desktop_modal_message'] = __('Give us a permission to receive push notification messages and we will keep you posted !', 'smpush-plugin-lang');
    $setting['desktop_deficon'] = '';
    $setting['desktop_chrome_status'] = '0';
    $setting['chrome_projectid'] = '';
    $setting['desktop_firefox_status'] = '0';
    $setting['desktop_safari_status'] = '0';
    $setting['safari_cert_path'] = '';
    $setting['safari_passphrase'] = '';
    $setting['safari_web_id'] = '';
    $setting['desktop_btn_subs_text'] = __('Enable Push Messages', 'smpush-plugin-lang');
    $setting['desktop_btn_unsubs_text'] = __('Disable Push Messages', 'smpush-plugin-lang');
    update_option('smpush_options', $setting);
    
    $version = 5.0;
  }
  if($version == 5.0){
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_archive_reports` ADD INDEX(`msgid`)");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_connection` ADD `wp10_name` VARCHAR(20) NOT NULL AFTER `firefox_name`");
    $wpdb->update($wpdb->prefix.'push_connection', array('wp10_name' => 'wp10'), array('tbname' => '{wp_prefix}push_tokens'));

    $setting = get_option('smpush_options');
    $setting['desktop_modal_cancel_text'] = __('Ignore', 'smpush-plugin-lang');
    $setting['wp10_pack_sid'] = '';
    $setting['wp10_client_secret'] = '';
    $setting['safari_certp12_path'] = '';
    $setting['safari_icon'] = '';
    if($setting['geo_provider'] == 'telize.com'){
      $setting['geo_provider'] = 'ip-api.com';
    }
    update_option('smpush_options', $setting);
    $version = 5.1;
  }
  if($version == 5.1){
    $version = 5.2;
  }
  if($version == 5.2){
    $wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_events_queue` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `old_status` varchar(50) NOT NULL,
      `new_status` varchar(50) NOT NULL,
      `post` mediumtext NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    $wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_events` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(200) NOT NULL,
      `event_type` varchar(50) NOT NULL,
      `post_type` varchar(50) NOT NULL,
      `message` text NOT NULL,
      `notify_segment` varchar(50) NOT NULL,
      `userid_field` varchar(100) NOT NULL,
      `conditions` text NOT NULL,
      `desktop_link` BOOLEAN NOT NULL,
      `ignore` tinyint(1) NOT NULL,
      `status` tinyint(1) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
    $setting = get_option('smpush_options');
    $wpdb->insert($wpdb->prefix.'push_events', array('title' => __('Notify all members when administrator published a new post', 'smpush-plugin-lang'), 'event_type' => 'publish', 'post_type' => 'post', 'message' => str_replace('{subject}', '{$post_title}', $setting['e_newpost_body']), 'notify_segment' => 'all', 'status' => $setting['e_newpost']));
    $wpdb->insert($wpdb->prefix.'push_events', array('title' => __('Notify author when administrator approved and published his post', 'smpush-plugin-lang'), 'event_type' => 'approve', 'post_type' => 'post', 'message' => str_replace('{subject}', '{$post_title}', $setting['e_apprpost_body']), 'notify_segment' => 'post_owner', 'status' => $setting['e_apprpost']));
    $wpdb->insert($wpdb->prefix.'push_events', array('title' => __('Notify all users subscribed in a post when has got a new update', 'smpush-plugin-lang'), 'event_type' => 'update', 'post_type' => 'post', 'message' => str_replace('{subject}', '{$post_title}', $setting['e_postupdated_body']), 'notify_segment' => 'post_commenters', 'status' => $setting['e_postupdated']));
    unset($setting['e_newpost']);
    unset($setting['e_newpost_body']);
    unset($setting['e_apprpost']);
    unset($setting['e_apprpost_body']);
    unset($setting['e_postupdated']);
    unset($setting['e_postupdated_body']);
    $setting['bb_notify_friends'] = 0;
    $setting['bb_notify_messages'] = 0;
    $setting['bb_notify_activity'] = 0;
    $setting['bb_notify_xprofile'] = 0;
    update_option('smpush_options', $setting);
    $version = 5.3;
  }
  if($version == 5.3){
    $setting = get_option('smpush_options');
    $setting['bb_notify_activity_admins_only'] = 1;
    update_option('smpush_options', $setting);
    $version = 5.4;
  }
  if($version == 5.4){
    $wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_desktop_messages` (
      `msgid` int(11) NOT NULL,
      `token` varchar(32) NOT NULL,
      `type` varchar(10) NOT NULL,
      `timepost` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    
    $setting = get_option('smpush_options');
    $setting['desktop_debug'] = 0;
    update_option('smpush_options', $setting);
    $version = 5.5;
  }
  if($version == 5.5){
    $version = 5.6;
  }
  if($version <= 5.6){
    $setting = get_option('smpush_options');
    $setting['gmaps_apikey'] = '';
    update_option('smpush_options', $setting);
    $version = 5.7;
  }
  if($version <= 5.7){
    $version = 5.8;
  }
  if($version <= 5.8){
    $setting = get_option('smpush_options');
    $setting['desktop_logged_only'] = 0;
    $setting['apple_appid'] = '';
    $setting['desktop_modal_saved_text'] = __('Saved', 'smpush-plugin-lang');
    update_option('smpush_options', $setting);
    $version = 5.9;
  }
  if($version <= 5.9){
    $setting = get_option('smpush_options');
    $setting['apple_api_ver'] = 'http2';
    update_option('smpush_options', $setting);
    $version = 5.91;
  }
  update_option('smpush_version', $version);
}

function smpush_move_certs(){
  global $wpdb;
  if(is_multisite()){
    $blogs = $wpdb->get_results("SELECT blog_id FROM $wpdb->blogs");
    if($blogs){
      foreach($blogs as $blog){
        switch_to_blog($blog->blog_id);
        smpush_move_certs_onesite();
      }
      restore_current_blog();
    }
  }
  else{
    smpush_move_certs_onesite();
  }
}

function smpush_move_certs_onesite(){
  $upload_dir = wp_upload_dir();
  if(! file_exists($upload_dir['basedir'].'/certifications')){
    @mkdir($upload_dir['basedir'].'/certifications');
  }
  $settings = get_option('smpush_options');
  if(empty($settings['apple_cert_path'])){
    return;
  }
  $settings['apple_cert_path'] = stripslashes($settings['apple_cert_path']);
  $target_path = $upload_dir['basedir'].'/certifications/'.basename($settings['apple_cert_path']);
  @rename($settings['apple_cert_path'], $target_path);
  $settings['apple_cert_path'] = addslashes($target_path);
  update_option('smpush_options', $settings);
}

function smpush_uninstall(){
  global $wpdb;
  if(is_multisite()){
    $blogs = $wpdb->get_results("SELECT blog_id FROM $wpdb->blogs");
    if($blogs){
      foreach($blogs as $blog){
        switch_to_blog($blog->blog_id);
        smpush_uninstall_code();
      }
      restore_current_blog();
    }
  }
  else{
    smpush_uninstall_code();
  }
}

function smpush_uninstall_code(){
  global $wpdb;
  global $wp_rewrite;
  $wpdb->hide_errors();
  $wp_rewrite->flush_rules();
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_queue`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_tokens`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_channels`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_relation`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_connection`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_feedback`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_archive`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_cron_queue`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_archive_reports`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_statistics`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_events`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_events_queue`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_desktop_messages`");
  delete_option('smpush_options');
  delete_option('smpush_version');
  delete_option('smpush_history');
  delete_option('smpush_instant_send');
  delete_option('smpush_cron_stats');
  delete_option('smpush_stats');
  wp_clear_scheduled_hook('smpush_update_counters');
  wp_clear_scheduled_hook('smpush_cron_fewdays');
}