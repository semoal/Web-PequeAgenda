<?php

class smpush_controller extends smpush_helper{
  public static $apisetting;
  public static $defconnection;
  public static $pushdb;
  public static $history;

  public function __construct(){
    $this->get_api_setting();
    $this->set_def_connection();
    $this->cron_setup();
    $this->add_rewrite_rules();
    if(self::$defconnection['dbtype'] == 'remote'){
      self::$pushdb = new wpdb(self::$defconnection['dbuser'], self::$defconnection['dbpass'], self::$defconnection['dbname'], self::$defconnection['dbhost']);
      if(!self::$pushdb){
        $this->output(0, __('Connecting with the remote push notification database is failed', 'smpush-plugin-lang'));
      }
    }
    else{
      global $wpdb;
      self::$pushdb = $wpdb;
    }
    self::$pushdb->hide_errors();
  }

  public function set_def_connection(){
    global $wpdb;
    self::$defconnection = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'", 'ARRAY_A');
  }

  public static function parse_query($query){
    if(preg_match_all("/{([a-zA-Z0-9_]+)}/", $query, $matches)){
      foreach($matches[1] AS $match){
        if($match == 'ios_name' OR $match == 'android_name' OR $match == 'wp_name' OR $match == 'wp10_name' OR $match == 'bb_name' OR $match == 'chrome_name' OR $match == 'safari_name' OR $match == 'firefox_name')
          $query = str_replace('{'.$match.'}', self::$defconnection[$match] , $query);
        elseif($match == 'tbname_temp')
          $query = str_replace('{'.$match.'}', '`'.self::$defconnection['tbname'].'_temp'.'`' , $query);
        else
          $query = str_replace('{'.$match.'}', '`'.self::$defconnection[$match].'`' , $query);
      }
    }
    $query = str_replace('{wp_prefix}', SMPUSHTBPRE, $query);
    return $query;
  }

  public static function setting(){
    if($_POST){
      self::saveOptions();
    }
    else{
      global $wpdb;
      $connections = $wpdb->get_results("SELECT id,title FROM ".$wpdb->prefix."push_connection ORDER BY id ASC");
      wp_enqueue_script('media-upload');
      wp_enqueue_script('thickbox');
      wp_enqueue_script('jquery');
      wp_enqueue_style('thickbox');
      self::loadpage('setting', 1, $connections);
    }
  }

  public static function documentation(){
    include(smpush_dir.'/class.documentation.php');
    self::load_jsplugins();
    $document = new smpush_documentation();
    $document = $document->build();
    $smpushexurl['auth_key'] = (self::$apisetting['complex_auth']==1)?md5(date('m/d/Y').self::$apisetting['auth_key'].date('H:i')):self::$apisetting['auth_key'];
    $smpushexurl['push_basename'] = get_bloginfo('url') .'/'.self::$apisetting['push_basename'];
    include(smpush_dir.'/pages/documentation.php');
  }

  public static function loadpage($template, $noheader=0, $params=0){
    self::load_jsplugins();
    $noheader = ($noheader == 0)?'':'&noheader=1';
    $page_url = admin_url().'admin.php?page=smpush_'.$template.$noheader;
    include(smpush_dir.'/pages/'.$template.'.php');
  }

  public static function load_jsplugins(){
    wp_enqueue_style('smpush-style');
    if(is_rtl()){
      wp_enqueue_style('smpush-rtl');
    }
    wp_enqueue_script('smpush-mainscript');
    wp_enqueue_script('smpush-plugins');
  }

  public static function saveOptions(){
    if(smpush_env == 'demo'){
      echo 1;
      die();
    }
    $newsetting = array();
    foreach($_POST AS $key=>$value){
      if(!in_array($key, array('submit'))){
        $newsetting[$key] = $value;
        unset(self::$apisetting[$key]);
      }
    }
    $checkbox = array(
    'bb_notify_friends',
    'bb_notify_messages',
    'bb_notify_activity',
    'bb_notify_activity_admins_only',
    'bb_notify_xprofile',
    'desktop_status',
    'desktop_debug',
    'desktop_chrome_status',
    'desktop_firefox_status',
    'desktop_safari_status',
    'desktop_modal',
    'desktop_logged_only',
    'auto_geo',
    'complex_auth',
    'apple_sandbox',
    'wp_authed',
    'bb_dev_env',
    'android_titanium_payload',
    'android_corona_payload',
    'ios_titanium_payload',
    'e_post_chantocats',
    'e_apprpost',
    'e_appcomment',
    'e_newcomment',
    'e_usercomuser',
    'e_postupdated',
    'e_newpost',
    'stop_summarize'
    );
    foreach($checkbox AS $inptname){
      if(!isset($_POST[$inptname])){
        self::$apisetting[$inptname] = 0;
      }
    }
    $upload_dir = wp_upload_dir();
    $cert_upload_path = $upload_dir['basedir'].'/certifications';
    if(! file_exists($cert_upload_path)){
      if(! mkdir($cert_upload_path)){
        die(__('can not create a directory to save the certifications files under uploads directory .', 'smpush-plugin-lang'));
      }
    }
    if(!empty($_FILES['apple_cert_upload']['tmp_name'])){
      if(strtolower(substr($_FILES['apple_cert_upload']['name'], strrpos($_FILES['apple_cert_upload']['name'], '.') + 1)) == 'pem'){
        $target_path = $cert_upload_path.'/cert_connection_'.time().'_'.$newsetting['def_connection'].'.pem';
        if(move_uploaded_file($_FILES['apple_cert_upload']['tmp_name'], $target_path)){
          unset(self::$apisetting['apple_cert_path']);
          $newsetting['apple_cert_path'] = addslashes($target_path);
        }
      }
    }
    if(!empty($_FILES['wp_cert']['tmp_name'])){
      $ext = strtolower(substr($_FILES['wp_cert']['name'], strrpos($_FILES['wp_cert']['name'], '.') + 1));
      $target_path = $cert_upload_path.'/wp_cert_connection_'.time().'_'.$newsetting['def_connection'].'.'.$ext;
      if(move_uploaded_file($_FILES['wp_cert']['tmp_name'], $target_path)){
        unset(self::$apisetting['wp_cert']);
        $newsetting['wp_cert'] = addslashes($target_path);
      }
    }
    if(!empty($_FILES['wp_pem']['tmp_name'])){
      $ext = strtolower(substr($_FILES['wp_pem']['name'], strrpos($_FILES['wp_pem']['name'], '.') + 1));
      $target_path = $cert_upload_path.'/wp_pem_connection_'.time().'_'.$newsetting['def_connection'].'.'.$ext;
      if(move_uploaded_file($_FILES['wp_pem']['tmp_name'], $target_path)){
        unset(self::$apisetting['wp_pem']);
        $newsetting['wp_pem'] = addslashes($target_path);
      }
    }
    if(!empty($_FILES['wp_cainfo']['tmp_name'])){
      $ext = strtolower(substr($_FILES['wp_cainfo']['name'], strrpos($_FILES['wp_cainfo']['name'], '.') + 1));
      $target_path = $cert_upload_path.'/wp_cainfo_connection_'.time().'_'.$newsetting['def_connection'].'.'.$ext;
      if(move_uploaded_file($_FILES['wp_cainfo']['tmp_name'], $target_path)){
        unset(self::$apisetting['wp_cainfo']);
        $newsetting['wp_cainfo'] = addslashes($target_path);
      }
    }
    if(!empty($_FILES['safari_cert_upload']['tmp_name'])){
      $ext = strtolower(substr($_FILES['safari_cert_upload']['name'], strrpos($_FILES['safari_cert_upload']['name'], '.') + 1));
      $target_path = $cert_upload_path.'/safari_cert_connection_'.time().'_'.$newsetting['def_connection'].'.'.$ext;
      if(move_uploaded_file($_FILES['safari_cert_upload']['tmp_name'], $target_path)){
        unset(self::$apisetting['safari_cert_path']);
        $newsetting['safari_cert_path'] = addslashes($target_path);
      }
    }
    if(!empty($_FILES['safari_certp12_upload']['tmp_name'])){
      $ext = strtolower(substr($_FILES['safari_certp12_upload']['name'], strrpos($_FILES['safari_certp12_upload']['name'], '.') + 1));
      $target_path = $cert_upload_path.'/safari_certp12_connection_'.time().'_'.$newsetting['def_connection'].'.'.$ext;
      if(move_uploaded_file($_FILES['safari_certp12_upload']['tmp_name'], $target_path)){
        unset(self::$apisetting['safari_certp12_path']);
        $newsetting['safari_certp12_path'] = addslashes($target_path);
      }
    }
    unset(self::$apisetting['safari_pack_path']);
    self::$apisetting = array_map('addslashes', self::$apisetting);
    self::$apisetting = array_merge($newsetting, self::$apisetting);
    update_option('smpush_options', self::$apisetting);
    echo 1;
    die();
  }

  public static function loadHistory($field, $index=false){
    if($index === false){
      if(isset(self::$history[$field])){
        if(is_array(self::$history[$field])){
          return array_map('stripslashes', self::$history[$field]);
        }
        else{
          return stripslashes(self::$history[$field]);
        }
      }
    }
    else{
      if(isset(self::$history[$field][$index])){
        return stripslashes(self::$history[$field][$index]);
      }
    }
    return '';
  }

  public function build_menus(){
    add_menu_page('Settings', __('Push Notification', 'smpush-plugin-lang'), 'delete_pages', 'smpush_setting', array('smpush_controller', 'setting'), 'div', 4);
    add_submenu_page('smpush_setting', __('Send Push Notification', 'smpush-plugin-lang'), __('Sending Dashboard', 'smpush-plugin-lang'), 'delete_pages', 'smpush_send_notification', array('smpush_sendpush', 'send_notification'));
    add_submenu_page('smpush_setting', __('Message Archive', 'smpush-plugin-lang'), __('Message Archive', 'smpush-plugin-lang'), 'delete_pages', 'smpush_archive', array('smpush_modules', 'archive'));
    add_submenu_page('smpush_setting', __('Event Manager', 'smpush-plugin-lang'), __('Event Manager', 'smpush-plugin-lang'), 'delete_pages', 'smpush_events', array('smpush_event_manager', 'page'));
    add_submenu_page('smpush_setting', __('Manage Connections', 'smpush-plugin-lang'), __('Manage Connections', 'smpush-plugin-lang'), 'delete_pages', 'smpush_connections', array('smpush_modules', 'connections'));
    add_submenu_page('smpush_setting', __('Manage Device Token', 'smpush-plugin-lang'), __('Manage Device Token', 'smpush-plugin-lang'), 'delete_pages', 'smpush_tokens', array('smpush_modules', 'tokens'));
    add_submenu_page('smpush_setting', __('Push Notification Channels', 'smpush-plugin-lang'), __('Manage Channels', 'smpush-plugin-lang'), 'delete_pages', 'smpush_channel', array('smpush_modules', 'push_channel'));
    add_submenu_page('smpush_setting', __('Test Dashboard', 'smpush-plugin-lang'), __('Test Dashboard', 'smpush-plugin-lang'), 'delete_pages', 'smpush_test_sending', array('smpush_modules', 'testing'));
    add_submenu_page('smpush_setting', __('Developer Documentation', 'smpush-plugin-lang'), __('Documentation', 'smpush-plugin-lang'), 'delete_pages', 'smpush_documentation', array('smpush_controller', 'documentation'));
    add_submenu_page('smpush_setting', __('System Auto Update', 'smpush-plugin-lang'), __('Auto Update', 'smpush-plugin-lang'), 'delete_pages', 'smpush_autoupdate', array('smpush_autoupdate', 'auto_update'));
    add_submenu_page(NULL, __('Sending Push Notification', 'smpush-plugin-lang'), __('Sending Push Notification', 'smpush-plugin-lang'), 'delete_pages', 'smpush_send_process', array('smpush_sendpush', 'send_process'));
    add_submenu_page(NULL, __('Queue Push', 'smpush-plugin-lang'), __('Queue Push', 'smpush-plugin-lang'), 'delete_pages', 'smpush_runqueue', array('smpush_sendpush', 'RunQueue'));
    add_submenu_page(NULL, __('Cancel Queue Push', 'smpush-plugin-lang'), __('Cancel Queue Push', 'smpush-plugin-lang'), 'delete_pages', 'smpush_cancelqueue', array('smpush_sendpush', 'smpush_cancelqueue'));
    add_submenu_page(NULL, __('Active invalid tokens', 'smpush-plugin-lang'), __('Active invalid tokens', 'smpush-plugin-lang'), 'delete_pages', 'smpush_active_tokens', array('smpush_sendpush', 'activateTokens'));
    add_submenu_page(NULL, __('Watch real-time GPS', 'smpush-plugin-lang'), __('Watch real-time GPS', 'smpush-plugin-lang'), 'delete_pages', 'smpush_realtime_gps', array('smpush_sendpush', 'gpsRealtime'));
  }

  public static function register_cron($schedules){
    $schedules['smpush_few_days'] = array(
      'interval' => 259200,
      'display' => __('Once every 3 days')
    );
    return $schedules;
  }

  public function cron_setup(){
    if(!wp_next_scheduled('smpush_update_counters')){
      wp_schedule_event(mktime(3,0,0,date('m'),date('d'),date('Y')), 'daily', 'smpush_update_counters');
	}
    if(! wp_next_scheduled('smpush_cron_fewdays')){
      wp_schedule_event(mktime(15,0,0,date('m'),date('d'),date('Y')), 'smpush_few_days', 'smpush_cron_fewdays');
	}
    if(get_transient('smpush_update_notice') !== false){
      add_action('admin_notices', array('smpush_controller', 'update_notice'));
    }
  }

  public function check_update_notify(){
    if(function_exists('curl_init')){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "https://smartiolabs.com/update/push_notification");
      curl_setopt($ch, CURLOPT_REFERER, 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
      $data = json_decode(curl_exec($ch));
      curl_close($ch);
      if($data !== NULL){
        if($data->version > SMPUSHVERSION){
          set_transient('smpush_update_notice', $data, 60);
        }
      }
    }
  }

  public static function update_notice(){
    $data = get_transient('smpush_update_notice');
    delete_transient('smpush_update_notice');
    echo '<div class="update-nag"><p><a href="'.$data->link.'" target="_blank">'.$data->plugin.' '.$data->version.'</a> '.__('is available! Please update your system using the', 'smpush-plugin-lang').' <a href="'.admin_url().'admin.php?page=smpush_autoupdate">'.__('auto update page', 'smpush-plugin-lang').'</a>.</p></div>';
  }

  public static function update_counters(){
    global $wpdb;
    $defconid = self::$apisetting['def_connection'];
    $counter = self::$pushdb->get_var(self::parse_query("SELECT COUNT({id_name}) FROM {tbname} WHERE {active_name}='1'"));
    $wpdb->query("UPDATE ".$wpdb->prefix."push_connection SET `counter`='$counter' WHERE id='$defconid'");
    
    $wpdb->query("DELETE FROM ".$wpdb->prefix."push_desktop_messages WHERE timepost<NOW()-INTERVAL 10 DAY");
  }

  public static function update_all_counters(){
    global $wpdb;
    self::update_counters();
    $channels = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."push_channels");
    if($channels){
      foreach($channels as $channel){
        $count = $wpdb->get_var("SELECT COUNT(token_id) FROM ".$wpdb->prefix."push_relation WHERE channel_id='$channel->id'");
        $wpdb->query("UPDATE ".$wpdb->prefix."push_channels SET `count`='$count' WHERE id='$channel->id'");
      }
    }
  }

  public function get_option($index){
    return self::$apisetting[$index];
  }

  public function get_api_setting(){
    self::$apisetting = get_option('smpush_options');
    self::$apisetting = array_map('stripslashes', self::$apisetting);
  }

  public function add_mime_types($mime_types){
    $mime_types['crt'] = 'application/x-x509-user-cert';
    $mime_types['cer'] = 'application/pkix-cert';
    $mime_types['pem'] = 'application/x-pem-file';
    $mime_types['pfx'] = 'application/x-pkcs12';
    $mime_types['p12'] = 'application/x-pkcs12';
    $mime_types['csr'] = 'application/pkcs10';
    return $mime_types;
  }
  
  //for future use
  public function add_rewrite_rules(){
    $apiname = self::$apisetting['push_basename'];
    add_rewrite_rule($apiname.'/?$', 'index.php?smpushcontrol=debug', 'top');
    add_rewrite_rule($apiname.'/(.+)$', 'index.php?smpushcontrol=$matches[1]', 'top');
  }

  public function start_fetch_method(){
    $profile = get_query_var('smpushprofile');
    $method = get_query_var('smpushcontrol');
    if(!empty($method)){
      if(strpos($method, 'safari/v') !== false){
        new smpush_api('safari', true, $method);
      }
      else{
        new smpush_api($method);
      }
    }
    if(!empty($_GET['smpushprofile'])){
      new smpush_build_profile($_GET['smpushprofile']);
    }
  }

  public function register_vars($vars){
      $vars[] = 'smpushcontrol';
      return $vars;
  }

}