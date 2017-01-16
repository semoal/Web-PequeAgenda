<?php

class smpush_sendpush extends smpush_controller {

  public static $cronSendOperation = false;
  
  const TIME_BINARY_SIZE = 4;
  const TOKEN_LENGTH_BINARY_SIZE = 2;
  const DEVICE_BINARY_SIZE = 32;
  const ERROR_RESPONSE_SIZE = 6;
  const ERROR_RESPONSE_COMMAND = 8;
  const STATUS_CODE_INTERNAL_ERROR = 999;
  
  protected static $_aErrorResponseMessages = array(
  0 => 'No errors encountered',
  1 => 'Processing error',
  2 => 'Missing device token',
  3 => 'Missing topic',
  4 => 'Missing payload',
  5 => 'Invalid token size',
  6 => 'Invalid topic size',
  7 => 'Invalid payload size',
  8 => 'Invalid token',
  self::STATUS_CODE_INTERNAL_ERROR => 'Internal error'
  );
  
  protected static $apnsErrors = array(
  'BadCollapseId' => 'The collapse identifier exceeds the maximum allowed size',
  'BadDeviceToken' => 'The specified device token was bad. Verify that the request contains a valid token and that the token matches the environment.',
  'BadExpirationDate' => 'The apns-expiration value is bad.',
  'BadMessageId' => 'The apns-id value is bad.',
  'BadPriority' => 'The apns-priority value is bad.',
  'BadTopic' => 'The APP ID is invalid. Please enter the correct APP ID in your application settings.',
  'DeviceTokenNotForTopic' => 'The device token does not match the specified topic.',
  'DuplicateHeaders' => 'One or more headers were repeated.',
  'IdleTimeout' => 'Idle time out.',
  'MissingDeviceToken' => 'The device token is not specified in the request :path. Verify that the :path header contains the device token.',
  'MissingTopic' => 'The APP ID is invalid. Please enter the correct APP ID in your application settings.',
  'PayloadEmpty' => 'The message payload was empty',
  'TopicDisallowed' => 'Pushing to this topic is not allowed',
  'BadCertificate' => 'The certificate or password phrase is wrong. Please resubmit the right info in the application settings.',
  'BadCertificateEnvironment' => 'The client certificate is for the wrong environment. Enbale/Disable the sandbox option in the application settings.',
  'ExpiredProviderToken' => 'The provider token is stale and a new token should be generated',
  'Forbidden' => 'The specified action is not allowed',
  'InvalidProviderToken' => 'The provider token is not valid or the token signature could not be verified',
  'MissingProviderToken' => 'No provider certificate was used to connect to APNs and Authorization header was missing or no provider token was specified',
  'BadPath' => 'The request contained a bad :path value',
  'MethodNotAllowed' => 'The specified :method was not POST',
  'Unregistered' => 'The device token is inactive for the specified topic.',
  'PayloadTooLarge' => 'The message payload was too large. Please minify your message text or custom payloads.',
  'TooManyProviderTokenUpdates' => 'The provider token is being updated too often',
  'TooManyRequests' => 'Too many requests were made consecutively to the same device token',
  'InternalServerError' => 'An internal server error occurred',
  'ServiceUnavailable' => 'The service is unavailable',
  'Shutdown' => 'The server is shutting down'
  );
  
  protected static $sendoptions = array(
  'msgid' => '',
  'message' => '',
  'iostestmode' => 0,
  'feedback' => 0,
  'expire' => 0,
  'ios_slide' => '',
  'ios_badge' => 0,
  'ios_sound' => 'default',
  'ios_cavailable' => 0,
  'ios_launchimg' => '',
  'wp10_img' => '',
  'extra_type' => '',
  'extravalue' => '',
  'and_extra_type' => '',
  'and_extravalue' => '',
  'wp_extra_type' => '',
  'wp_extravalue' => '',
  'wp10_extra_type' => '',
  'wp10_extravalue' => '',
  'bb_extra_type' => '',
  'bb_extravalue' => ''
  );

  public function __construct() {
    parent::__construct();
    global $_wp_using_ext_object_cache;
    $_wp_using_ext_object_cache = null;
  }

  private static function archiveMsgLog($message, $sendtime, $sendtype, $options, $desktop_notification = array('chrome','safari','firefox')) {
    global $wpdb;
    $sendtime = date('Y-m-d H:i:s', $sendtime);
    if(!empty($desktop_notification)){
      $desktop_notification = implode(',', $desktop_notification);
    }
    else{
      $desktop_notification = '';
    }
    $wpdb->insert($wpdb->prefix.'push_archive', array('message' => $message, 'starttime' => $sendtime, 'send_type' => $sendtype, 'desktop' => $desktop_notification, 'options' => serialize($options)));
    return $wpdb->insert_id;
  }
  
  public static function gpsRealtime() {
    if(isset($_GET['smpushlive'])){
      if(!empty($_GET['lastupdate'])){
        $lasttime = $_GET['lastupdate'];
      }
      else{
        $lasttime = time()-3600;
      }
      $devices = self::$pushdb->get_results(self::parse_query("SELECT {type_name} AS devicetype,{info_name} AS deviceinfo,{latidude_name} AS latidude,{longitude_name} AS longitude FROM {tbname} WHERE {gpstime_name}>'$lasttime' AND {active_name}='1'"), 'ARRAY_A');
      if($devices){
        $devices[0]['lastupdate'] = (string)time();
        echo json_encode($devices);
      }
      else{
        echo time();
      }
      exit;
    }
    self::loadpage('realtime_gps', 1, array());
  }

  public static function SendPushMessage($device_token, $device_type, $message, $sendsetting = array(), $sendtime = 0) {
    global $wpdb;
    $token = array();
    $token[0]['token'] = $device_token;
    self::$sendoptions['message'] = $message;
    self::$sendoptions = array_merge(self::$sendoptions, $sendsetting);

    if ($sendtime == 0) {
      $sendtime = current_time('timestamp');
    }
    
    $msgid = self::archiveMsgLog($message, $sendtime, 'cronsend', self::$sendoptions);
    $crondata = array(
    'token' => $device_token,
    'device_type' => $device_type,
    'sendtime' => $sendtime,
    'sendoptions' => $msgid
    );
    $wpdb->insert($wpdb->prefix.'push_cron_queue', $crondata);
  }

  public static function SendCronPush($ids, $message, $extravalue, $gettype = 'userid', $sendsetting = array(), $sendtime = 0, $channel_filter_ids=false, $gps_loc_filter=false) {
    global $wpdb;
    
    $inner = $select = $where = '';
    if(!empty($channel_filter_ids)){
      $defconid = self::$apisetting['def_connection'];
      $tablename = $wpdb->prefix.'push_relation';
      $inner = "INNER JOIN $tablename ON($tablename.channel_id IN($channel_filter_ids) AND $tablename.connection_id='$defconid' AND $tablename.token_id={tbname}.{id_name})";
    }
    
    if(!empty($gps_loc_filter['latidude']) AND ! empty($gps_loc_filter['longitude']) AND ! empty($gps_loc_filter['radius'])) {
      $select = ",(3959*acos(cos(radians($gps_loc_filter[latidude]))*cos(radians({tbname}.{latidude_name}))*cos(radians({tbname}.{longitude_name})-radians($gps_loc_filter[longitude]))+sin(radians($gps_loc_filter[latidude]))*sin(radians({tbname}.{latidude_name})))) AS geodistance";
      if(!empty($gps_loc_filter['gps_expire'])){
        $where = " AND {tbname}.{gpstime_name}>".(time()-($gps_loc_filter['gps_expire']*3600));
      }
      $order = 'HAVING geodistance<='.$gps_loc_filter['radius'].' ORDER BY {tbname}.{id_name} ASC';
    }
    else{
      $order = 'GROUP BY {tbname}.{id_name}';
    }
    
    if ($ids == 'all') {
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type $select FROM {tbname} $inner WHERE {tbname}.{active_name}='1' $where $order"));
    }
    elseif ($gettype == 'userid') {
      if(is_array($ids)){
        $ids = implode(',', $ids);
      }
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type $select FROM {tbname} $inner WHERE {tbname}.userid IN($ids) AND {tbname}.{active_name}='1' $where $order"));
    }
    elseif ($gettype == 'tokenid') {
      $ids = implode(',', $ids);
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type $select FROM {tbname} $inner WHERE {tbname}.{id_name} IN($ids) AND {tbname}.{active_name}='1' $where $order"));
    }
    elseif ($gettype == 'channel') {
      $defconid = self::$apisetting['def_connection'];
      $tablename = $wpdb->prefix.'push_relation';
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type $select FROM $tablename
      INNER JOIN {tbname} ON({tbname}.{id_name}=$tablename.token_id AND {tbname}.{active_name}='1')
      WHERE $tablename.channel_id IN($ids) AND $tablename.connection_id='$defconid' $where $order"));
    }
    if (!$queue) return false;
    self::$sendoptions['message'] = $message;

    if (!empty($sendsetting)) {
      self::$sendoptions = array_merge(self::$sendoptions, $sendsetting);
    }
    if (!empty($extravalue)) {
      if (is_array($extravalue)) {
        self::$sendoptions['extra_type'] = 'json';
        self::$sendoptions['extravalue'] = (phpversion() >= 5.4) ? json_encode($extravalue, JSON_UNESCAPED_UNICODE) : json_encode($extravalue);
      }
      else {
        self::$sendoptions['extra_type'] = 'normal';
        self::$sendoptions['extravalue'] = $extravalue;
      }
    }
    
    if ($sendtime == 0) {
      $sendtime = current_time('timestamp');
    }

    $msgid = self::archiveMsgLog($message, $sendtime, 'cronsend', self::$sendoptions);
    foreach ($queue AS $queueone) {
      $crondata = array(
      'token' => $queueone->device_token,
      'device_type' => $queueone->device_type,
      'sendtime' => $sendtime,
      'sendoptions' => $msgid
      );
      $wpdb->insert($wpdb->prefix.'push_cron_queue', $crondata);
    }
  }

  public static function activateTokens() {
    self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='1'"));
    self::update_counters();
    wp_redirect(admin_url().'admin.php?page=smpush_send_notification');
  }

  public static function smpush_cancelqueue() {
    global $wpdb;
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_queue`");
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_feedback`");
    delete_transient('smpush_post');
    delete_transient('smpush_resum');
    update_option('smpush_instant_send', array());
    self::updateStats();
    wp_redirect(admin_url().'admin.php?page=smpush_send_notification');
  }

  public static function send_process($resumsend, $allcount = 0, $increration = 0) {
    self::load_jsplugins();
    wp_enqueue_style('smpush-progbarstyle');
    wp_enqueue_script('smpush-progbarscript');
    include (smpush_dir.'/pages/send_process.php');
  }

  public static function send_notification() {
    global $wpdb;
    $resume_mode = false;
    if (!empty($_GET['calculate'])) {
      $stats = self::calculateDevices();
      echo json_encode($stats);
      exit;
    }
    if (!empty($_GET['savehistory'])) {
      update_option('smpush_history', $_POST);
      echo 1;
      exit;
    }
    if (!empty($_GET['clearhistory'])) {
      update_option('smpush_history', '');
      echo 1;
      exit;
    }
    if (get_transient('smpush_resum') !== false && !isset($_GET['lastid'], $_GET['increration'])) {
      $_POST = get_transient('smpush_post');
      $resume_mode = true;
    }
    if ($_POST) {
      if (isset($_POST['message'])) {
        $wpdb->query("TRUNCATE `".$wpdb->prefix."push_queue`");
        $wpdb->query("TRUNCATE `".$wpdb->prefix."push_feedback`");
        self::$sendoptions['message'] = $_POST['message'];
        $desktop_notification = array();
        $where = '';
        if(!empty($_POST['type'])){
          $_POST['type'] = array_flip($_POST['type']);
        }
        if(empty($_POST['type']) || (isset($_POST['type']['ios']) && isset($_POST['type']['android']) && isset($_POST['type']['wp']) && isset($_POST['type']['bb'])
           && isset($_POST['type']['chrome']) && isset($_POST['type']['safari']) && isset($_POST['type']['firefox']))){
          $_POST['type'] = array('all');
          $desktop_notification = array('chrome','safari','firefox');
          $where = '';
        }
        elseif(!empty($_POST['type'])){
          $_POST['type'] = array_flip($_POST['type']);
        }
        if (in_array('ios', $_POST['type'])) {
          $where .= " OR {tbname}.{type_name}='{ios_name}'";
        }
        if (in_array('android', $_POST['type'])) {
          $where .= " OR {tbname}.{type_name}='{android_name}'";
        }
        if (in_array('wp', $_POST['type'])) {
          $where .= " OR {tbname}.{type_name}='{wp_name}'";
        }
        if (in_array('wp10', $_POST['type'])) {
          $where .= " OR {tbname}.{type_name}='{wp10_name}'";
        }
        if (in_array('bb', $_POST['type'])) {
          $where .= " OR {tbname}.{type_name}='{bb_name}'";
        }
        if (in_array('chrome', $_POST['type'])) {
          array_push($desktop_notification, 'chrome');
          $where .= " OR {tbname}.{type_name}='{chrome_name}'";
        }
        if (in_array('safari', $_POST['type'])) {
          array_push($desktop_notification, 'safari');
          $where .= " OR {tbname}.{type_name}='{safari_name}'";
        }
        if (in_array('firefox', $_POST['type'])) {
          array_push($desktop_notification, 'firefox');
          $where .= " OR {tbname}.{type_name}='{firefox_name}'";
        }
        if(!empty($where)){
          $where = ' AND ('.ltrim($where, ' OR ').') ';
        }
        if (!empty($_POST['latidude']) AND ! empty($_POST['longitude']) AND ! empty($_POST['radius'])) {
          $select = ",(3959*acos(cos(radians($_POST[latidude]))*cos(radians({tbname}.{latidude_name}))*cos(radians({tbname}.{longitude_name})-radians($_POST[longitude]))+sin(radians($_POST[latidude]))*sin(radians({tbname}.{latidude_name})))) AS geodistance";
          if(!empty($_POST['gps_expire'])){
            $where .= " AND {tbname}.{gpstime_name}>".(time()-($_POST['gps_expire']*3600));
          }
          $order = 'HAVING geodistance<='.$_POST['radius'].' ORDER BY {tbname}.{id_name} ASC';
        }
        else{
          $select = '';
          $order = 'ORDER BY {tbname}.{id_name} ASC';
        }
        if (!empty($_POST['inchannels_and']) OR ! empty($_POST['inchannels_or']) OR ! empty($_POST['notchannels_and']) OR ! empty($_POST['notchannels_or'])) {
          $defconid = self::$apisetting['def_connection'];
          $tablename = $wpdb->prefix.'push_relation';
          //do not forget to change in calculateDevices() if you will make any changes in this query
          $smpush_query = self::parse_query("SELECT {tbname}.{id_name} AS token_id,{tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type,GROUP_CONCAT($tablename.`channel_id` SEPARATOR ',') AS channelids $select FROM {tbname}
          INNER JOIN $tablename ON($tablename.token_id={tbname}.{id_name} AND $tablename.connection_id='$defconid')
          WHERE {tbname}.{active_name}='1' $where AND {tbname}.{id_name}>[lastid] GROUP BY {tbname}.{id_name} $order LIMIT 0,[limit]");
          $alltokens = $wpdb->get_var(self::parse_query("SELECT  COUNT(DISTINCT({tbname}.{id_name})) FROM {tbname}
          INNER JOIN $tablename ON($tablename.token_id={tbname}.{id_name} AND $tablename.connection_id='$defconid')
          WHERE {tbname}.{active_name}='1' $where"));
        }
        else {
          //do not forget to change in calculateDevices() if you will make any changes in this query
          $smpush_query = self::parse_query("SELECT {id_name} AS token_id,{token_name} AS device_token,{type_name} AS device_type $select FROM {tbname} WHERE {active_name}='1' $where AND {id_name}>[lastid] $order LIMIT 0,[limit]");
          $alltokens = self::$pushdb->get_var(self::parse_query("SELECT COUNT({id_name}) FROM {tbname} WHERE {active_name}='1' $where"));
        }
        if ($alltokens === null) {
          wp_die('Please reconfig the default push notification database connection <a href="'.admin_url().'admin.php?page=smpush_connections">here</a>');
        }
        $feedback = (isset($_POST['feedback'])) ? 1 : 0;
        $iostestmode = (isset($_POST['iostestmode'])) ? 1 : 0;
        if (isset($_POST['feedback']) && (in_array('ios', $_POST['type']) OR in_array('all', $_POST['type']))) {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('device_type' => 'ios'));
        }
        $_POST['feedback'] = $feedback;
        $_POST['iostestmode'] = $iostestmode;
        if ($_POST['extra_type'] == 'multi') {
          $json = array();
          foreach ($_POST['key'] as $loop => $key) {
            if (!empty($key) && !empty($_POST['value'][$loop])) {
              $json[$key] = $_POST['value'][$loop];
            }
          }
          if (empty($json)) {
            $_POST['extra'] = '';
            $_POST['extra_type'] = '';
          } else {
            $_POST['extra'] = (phpversion() >= 5.4) ? json_encode($json, JSON_UNESCAPED_UNICODE) : json_encode($json);
            $_POST['extra_type'] = 'json';
          }
        }
        if ($_POST['and_extra_type'] == 'multi') {
          $json = array();
          foreach ($_POST['and_key'] as $loop => $key) {
            if (!empty($key) && !empty($_POST['and_value'][$loop])) {
              $json[$key] = $_POST['and_value'][$loop];
            }
          }
          if (empty($json)) {
            $_POST['and_extra'] = '';
            $_POST['and_extra_type'] = '';
          } else {
            $_POST['and_extra'] = (phpversion() >= 5.4) ? json_encode($json, JSON_UNESCAPED_UNICODE) : json_encode($json);
            $_POST['and_extra_type'] = 'json';
          }
        }
        $options = array(
        'message' => $_POST['message'],
        'iostestmode' => $_POST['iostestmode'],
        'feedback' => $_POST['feedback'],
        'expire' => $_POST['expire'],
        'desktop_link' => $_POST['desktop_link'],
        'desktop_title' => $_POST['desktop_title'],
        'desktop_icon' => $_POST['desktop_icon'],
        'ios_slide' => $_POST['ios_slide'],
        'ios_badge' => $_POST['ios_badge'],
        'ios_sound' => $_POST['ios_sound'],
        'ios_cavailable' => $_POST['ios_cavailable'],
        'ios_launchimg' => $_POST['ios_launchimg'],
        'wp10_img' => $_POST['wp10_img'],
        'extra_type' => $_POST['extra_type'],
        'extravalue' => $_POST['extra'],
        'and_extra_type' => $_POST['and_extra_type'],
        'and_extravalue' => $_POST['and_extra'],
        'wp_extra_type' => $_POST['wp_extra_type'],
        'wp_extravalue' => $_POST['wp_extra'],
        'wp10_extra_type' => $_POST['wp10_extra_type'],
        'wp10_extravalue' => $_POST['wp10_extra'],
        //'bb_extra_type' => $_POST['bb_extra_type'],
        //'bb_extravalue' => $_POST['bb_extra'],
        'inchannels_and' => (empty($_POST['inchannels_and']))?array():$_POST['inchannels_and'],
        'inchannels_or' => (empty($_POST['inchannels_or']))?array():$_POST['inchannels_or'],
        'notchannels_and' => (empty($_POST['notchannels_and']))?array():$_POST['notchannels_and'],
        'notchannels_or' => (empty($_POST['notchannels_or']))?array():$_POST['notchannels_or']
        );
        $sendtimeformat = $_POST['mm'].'/'.$_POST['jj'].'/'.$_POST['aa'].' '.$_POST['hh'].':'.$_POST['mn'].':00';
        $options['sendtime'] = strtotime($sendtimeformat, current_time('timestamp'));
        $options['sendtype'] = (isset($_POST['sendnow'])) ? 'sendnow' : 'cronsend';
        $options['query'] = $smpush_query;
        
        if(! $resume_mode){
          $msgid = self::archiveMsgLog($options['message'], $options['sendtime'], $options['sendtype'], $options, $desktop_notification);
          set_transient('smpush_post', $_POST, 43200);
          $handler_options = array(
          'token_counter' => 0,
          'lastid' => 0,
          'msgid' => $msgid,
          );
          update_option('smpush_instant_send', $handler_options);
        }
        
        if ($alltokens == 0)
          $increration = 0;
        else
          $increration = ceil($alltokens / 20);
        self::send_process(false, $alltokens, $increration, $feedback);
      }
      else {
        wp_redirect(admin_url().'admin.php?page=smpush_send_notification');
      }
    }
    elseif (isset($_GET['lastid'], $_GET['increration'])) {
      $handler_options = get_option('smpush_instant_send');
      if (empty($_GET['lastid']) && !empty($handler_options['lastid'])) {
        $_GET['lastid'] = $handler_options['lastid'];
      }
      $message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."push_archive WHERE id='$handler_options[msgid]'", ARRAY_A);
      $message_data['options'] = unserialize($message_data['options']);
      $smpush_query = $message_data['options']['query'];
      $options = $message_data['options'];
      $tokencounter = $handler_options['token_counter'];
      $query = str_replace(array('[lastid]', '[limit]'), array($_GET['lastid'], $_GET['increration']), $smpush_query);
      $tokens = self::$pushdb->get_results($query);
      if (!empty(self::$pushdb->last_error)) {
        self::jsonPrint(0, '<p class="error">'.__('Please reconfig the default push notification database connection', 'smpush-plugin-lang').'</p>');
      }
      if ($tokens) {
        if (!empty($options['inchannels_and']) OR ! empty($options['inchannels_or']) OR ! empty($options['notchannels_and']) OR ! empty($options['notchannels_or'])) {
          foreach ($tokens AS $token) {
            $lastid = $token->token_id;
            $token->channelids = explode(',', $token->channelids);
            //do not forget to change in calculateDevices() if you will make any changes in this query
            if (!empty($options['inchannels_and'])) {
              $intersect = array_intersect($token->channelids, $options['inchannels_and']);
              if (count($intersect) != count($options['inchannels_and'])) {
                continue;
              }
            }
            if (!empty($options['inchannels_or'])) {
              $bool = array_intersect($token->channelids, $options['inchannels_or']);
              if(empty($bool)){
                continue;
              }
            }
            if (!empty($options['notchannels_and'])) {
              $bool = array_intersect($token->channelids, $options['notchannels_and']);
              if(!empty($bool)){
                continue;
              }
            }
            if (!empty($options['notchannels_or'])) {
              $bool = array_diff($options['notchannels_or'], $token->channelids);
              if(empty($bool)){
                continue;
              }
            }

            if ($options['sendtype'] == 'sendnow') {
              $wpdb->insert($wpdb->prefix.'push_queue', array('token' => $token->device_token, 'device_type' => $token->device_type));
            }
            else {
              $wpdb->insert($wpdb->prefix.'push_cron_queue', array('token' => $token->device_token, 'device_type' => $token->device_type, 'sendtime' => $options['sendtime'], 'sendoptions' => $handler_options['msgid']));
            }
            $tokencounter++;
          }
        }
        else {
          if ($options['sendtype'] == 'sendnow') {
            foreach ($tokens AS $token) {
              $wpdb->insert($wpdb->prefix.'push_queue', array('token' => $token->device_token, 'device_type' => $token->device_type));
              $lastid = $token->token_id;
              $tokencounter++;
            }
          } else {
            foreach ($tokens AS $token) {
              $wpdb->insert($wpdb->prefix.'push_cron_queue', array('token' => $token->device_token, 'device_type' => $token->device_type, 'sendtime' => $options['sendtime'], 'sendoptions' => $handler_options['msgid']));
              $lastid = $token->token_id;
              $tokencounter++;
            }
          }
        }
        $handler_options['lastid'] = $lastid;
        $handler_options['token_counter'] = $tokencounter;
        update_option('smpush_instant_send', $handler_options);
        set_transient('smpush_resum', 1, 43200);
        self::jsonPrint(1, $lastid);
      }
      delete_transient('smpush_resum');
      delete_transient('smpush_post');
      if ($options['sendtype'] == 'sendnow') {
        self::$sendoptions['message'] = $options['message'];
        self::updateStats();
        self::updateStats('totalsend', $tokencounter);
        self::jsonPrint(-1, $tokencounter);
      }
      else {
        self::jsonPrint(-2, $tokencounter);
      }
    }
    else {
      $queuecount = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."push_queue");
      if ($queuecount > 0) {
        self::send_process(true, $queuecount);
      }
      else {
        $params = array();
        $params['all'] = self::$defconnection['counter'];
        $params['channels'] = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_channels ORDER BY id ASC");
        $params['dbtype'] = $wpdb->get_var("SELECT dbtype FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
        wp_register_script('smpush-gmap-source', 'https://maps.googleapis.com/maps/api/js?v=3.exp&key='.self::$apisetting['gmaps_apikey'], array('jquery'), SMPUSHVERSION);
        wp_register_script('smpush-gmap-js', smpush_jspath.'/gmap.js', array('jquery', 'smpush-gmap-source'), SMPUSHVERSION);
        wp_enqueue_script('postbox');
        wp_enqueue_script('smpush-gmap-js');
        wp_enqueue_script('smpush-select2-js');
        wp_enqueue_style('smpush-select2-style');
        add_thickbox();
        self::$history = get_option('smpush_history', $_POST);
        self::loadpage('send_notification', 0, $params);
      }
    }
  }

  private static function calculateDevices() {
    global $wpdb;
    $stats = array('ios' => 0, 'android' => 0,'wp' => 0,'wp10' => 0, 'bb' => 0, 'chrome' => 0, 'safari' => 0, 'firefox' => 0);
    $select = $order = $where = $gpswhere = '';
    $types_name = $wpdb->get_row("SELECT ios_name,android_name,wp_name,bb_name,chrome_name,safari_name,firefox_name,wp10_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
    if(!empty($_POST['type'])){
      $_POST['type'] = array_flip($_POST['type']);
    }
    if(empty($_POST['type']) || (isset($_POST['type']['ios']) && isset($_POST['type']['android']) && isset($_POST['type']['wp']) && isset($_POST['type']['wp10']) && isset($_POST['type']['bb'])
       && isset($_POST['type']['chrome']) && isset($_POST['type']['safari']) && isset($_POST['type']['firefox']))){
      $_POST['type'] = array('all');
      $where = '';
    }
    elseif(!empty($_POST['type'])){
      $_POST['type'] = array_flip($_POST['type']);
    }
    if (in_array('ios', $_POST['type'])) {
      $where .= " OR {tbname}.{type_name}='{ios_name}'";
    }
    if (in_array('android', $_POST['type'])) {
      $where .= " OR {tbname}.{type_name}='{android_name}'";
    }
    if (in_array('wp10', $_POST['type'])) {
      $where .= " OR {tbname}.{type_name}='{wp10_name}'";
    }
    if (in_array('bb', $_POST['type'])) {
      $where .= " OR {tbname}.{type_name}='{bb_name}'";
    }
    if (in_array('chrome', $_POST['type'])) {
      $where .= " OR {tbname}.{type_name}='{chrome_name}'";
    }
    if (in_array('safari', $_POST['type'])) {
      $where .= " OR {tbname}.{type_name}='{safari_name}'";
    }
    if (in_array('firefox', $_POST['type'])) {
      $where .= " OR {tbname}.{type_name}='{firefox_name}'";
    }
    if(!empty($where)){
      $where = ' AND ('.ltrim($where, ' OR ').') ';
    }
    if (!empty($_POST['latidude']) AND ! empty($_POST['longitude']) AND ! empty($_POST['radius'])) {
      $select = ",(3959*acos(cos(radians($_POST[latidude]))*cos(radians({tbname}.{latidude_name}))*cos(radians({tbname}.{longitude_name})-radians($_POST[longitude]))+sin(radians($_POST[latidude]))*sin(radians({tbname}.{latidude_name})))) AS geodistance";
      if(!empty($_POST['gps_expire'])){
        $gpswhere = " AND {tbname}.{gpstime_name}>".(time()-($_POST['gps_expire']*3600));
      }
      $order = 'HAVING geodistance<='.$_POST['radius'];
    }
    if (!empty($_POST['inchannels_and']) OR ! empty($_POST['inchannels_or']) OR ! empty($_POST['notchannels_and']) OR ! empty($_POST['notchannels_or'])) {
      $defconid = self::$apisetting['def_connection'];
      $tablename = $wpdb->prefix.'push_relation';
      $tokens = $wpdb->get_results(self::parse_query("SELECT {tbname}.{type_name} AS device_type,GROUP_CONCAT($tablename.`channel_id` SEPARATOR ',') AS channelids $select FROM {tbname}
      INNER JOIN $tablename ON($tablename.token_id={tbname}.{id_name} AND $tablename.connection_id='$defconid')
      WHERE {tbname}.{active_name}='1' $gpswhere $where GROUP BY {tbname}.{id_name} $order"));
      if($tokens){
        foreach ($tokens AS $token) {
          $token->channelids = explode(',', $token->channelids);
          if (!empty($_POST['inchannels_and'])) {
            $intersect = array_intersect($token->channelids, $_POST['inchannels_and']);
            if (count($intersect) != count($_POST['inchannels_and'])) {
              continue;
            }
          }
          if (!empty($_POST['inchannels_or'])) {
            $bool = array_intersect($token->channelids, $_POST['inchannels_or']);
            if(empty($bool)){
              continue;
            }
          }
          if (!empty($_POST['notchannels_and'])) {
            $bool = array_intersect($token->channelids, $_POST['notchannels_and']);
            if(!empty($bool)){
              continue;
            }
          }
          if (!empty($_POST['notchannels_or'])) {
            $bool = array_diff($_POST['notchannels_or'], $token->channelids);
            if(empty($bool)){
              continue;
            }
          }
          if($token->device_type == $types_name->ios_name){
            $stats['ios']++;
          }
          elseif($token->device_type == $types_name->android_name){
            $stats['android']++;
          }
          elseif($token->device_type == $types_name->wp_name){
            $stats['wp']++;
          }
          elseif($token->device_type == $types_name->wp10_name){
            $stats['wp10']++;
          }
          elseif($token->device_type == $types_name->bb_name){
            $stats['bb']++;
          }
          elseif($token->device_type == $types_name->chrome_name){
            $stats['chrome']++;
          }
          elseif($token->device_type == $types_name->safari_name){
            $stats['safari']++;
          }
          elseif($token->device_type == $types_name->firefox_name){
            $stats['firefox']++;
          }
        }
      }
    }
    else {
      if (in_array('ios', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS ios $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{ios_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['ios'] > 0){
          $stats['ios'] = $tokenstats['ios'];
        }
      }
      if (in_array('android', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS android $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{android_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['android'] > 0){
          $stats['android'] = $tokenstats['android'];
        }
      }
      if (in_array('wp', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS wp $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{wp_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['wp'] > 0){
          $stats['wp'] = $tokenstats['wp'];
        }
      }
      if (in_array('wp10', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS wp10 $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{wp10_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['wp10'] > 0){
          $stats['wp10'] = $tokenstats['wp10'];
        }
      }
      if (in_array('bb', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS bb $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{bb_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['bb'] > 0){
          $stats['bb'] = $tokenstats['bb'];
        }
      }
      if (in_array('chrome', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS chrome $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{chrome_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['chrome'] > 0){
          $stats['chrome'] = $tokenstats['chrome'];
        }
      }
      if (in_array('safari', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS safari $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{safari_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['safari'] > 0){
          $stats['safari'] = $tokenstats['safari'];
        }
      }
      if (in_array('firefox', $_POST['type']) OR in_array('all', $_POST['type'])) {
        $tokenstats = self::$pushdb->get_row(self::parse_query("SELECT COUNT(*) AS firefox $select FROM {tbname} WHERE {active_name}='1' AND {tbname}.{type_name}='{firefox_name}' $gpswhere $order"), 'ARRAY_A');
        if($tokenstats['firefox'] > 0){
          $stats['firefox'] = $tokenstats['firefox'];
        }
      }
    }
    return $stats;
  }
  
  public static function RunQueue() {
    global $wpdb;
    $iphone_devices = array();
    $android_devices = array();
    $wp_devices = array();
    $wp10_devices = array();
    $bb_devices = array();
    $chrome_devices = array();
    $safari_devices = array();
    $firefox_devices = array();
    $icounter = $acounter = $wcounter = $w10counter = $bcounter = $ccounter = $scounter = $fcounter = 0;
    
    $handler_options = get_option('smpush_instant_send');
    $message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."push_archive WHERE id='$handler_options[msgid]'", ARRAY_A);
    
    if($message_data['send_type'] == 'feedback'){
      self::connectFeedback(0);
      self::updateStats('all');
    }
    
    $options = unserialize($message_data['options']);
    $options['msgid'] = $handler_options['msgid'];
    
    $all_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."push_queue");
    $os_name = $wpdb->get_row("SELECT android_name,ios_name,wp_name,bb_name,chrome_name,safari_name,firefox_name,wp10_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
    if ($options['iostestmode'] == 1) {
      $limit = 1;
    }
    else {
      $limit = 1000;
    }
    $queue = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->ios_name' LIMIT 0,$limit");
    $queue2 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->android_name' LIMIT 0,$limit");
    $queue3 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->wp_name' LIMIT 0,$limit");
    $queue4 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->bb_name' LIMIT 0,$limit");
    $queue5 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->chrome_name' LIMIT 0,$limit");
    $queue6 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->safari_name' LIMIT 0,$limit");
    $queue7 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->firefox_name' LIMIT 0,$limit");
    $queue8 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->wp10_name' LIMIT 0,$limit");
    if (!$queue && !$queue2 && !$queue3 && !$queue4 && !$queue5 && !$queue6 && !$queue7 && !$queue8) {
      self::connectFeedback($all_count);
      self::updateStats('all');
    }
    foreach ($queue AS $queueone) {
      $iphone_devices[$icounter]['token'] = $queueone->token;
      $iphone_devices[$icounter]['id'] = $queueone->id;
      $icounter++;
    }
    foreach ($queue2 AS $queueone) {
      $android_devices['token'][$acounter] = $queueone->token;
      $android_devices['id'][$acounter] = $queueone->id;
      $acounter++;
    }
    foreach ($queue3 AS $queueone) {
      $wp_devices['token'][$wcounter] = $queueone->token;
      $wp_devices['id'][$wcounter] = $queueone->id;
      $wcounter++;
    }
    foreach ($queue4 AS $queueone) {
      $bb_devices['token'][$bcounter] = $queueone->token;
      $bb_devices['id'][$bcounter] = $queueone->id;
      $bcounter++;
    }
    foreach ($queue5 AS $queueone) {
      $chrome_devices['token'][$ccounter] = $queueone->token;
      $chrome_devices['id'][$ccounter] = $queueone->id;
      $ccounter++;
    }
    foreach ($queue6 AS $queueone) {
      $safari_devices[$scounter]['token'] = $queueone->token;
      $safari_devices[$scounter]['id'] = $queueone->id;
      $scounter++;
    }
    foreach ($queue7 AS $queueone) {
      $firefox_devices['token'][$fcounter] = $queueone->token;
      $firefox_devices['id'][$fcounter] = $queueone->id;
      $fcounter++;
    }
    foreach ($queue8 AS $queueone) {
      $wp10_devices['token'][$w10counter] = $queueone->token;
      $wp10_devices['id'][$w10counter] = $queueone->id;
      $w10counter++;
    }
    $message = $options['message'];
    if (!session_id()) {
      session_start();
    }
    if ($icounter > 0)
      self::connectPush($message, $iphone_devices, 'ios', $options, true, $all_count);
    if ($acounter > 0)
      self::connectPush($message, $android_devices, 'android', $options, true, $all_count);
    if ($wcounter > 0)
      self::connectPush($message, $wp_devices, 'wp', $options, true, $all_count);
    if ($bcounter > 0)
      self::connectPush($message, $bb_devices, 'bb', $options, true, $all_count);
    if ($ccounter > 0)
      self::connectPush($message, $chrome_devices, 'chrome', $options, true, $all_count);
    if ($scounter > 0)
      self::connectPush($message, $safari_devices, 'safari', $options, true, $all_count);
    if ($fcounter > 0)
      self::connectPush($message, $firefox_devices, 'firefox', $options, true, $all_count);
    if ($w10counter > 0)
      self::connectPush($message, $wp10_devices, 'wp10', $options, true, $all_count);
    self::jsonPrint(1, array('message' => '', 'all_count' => $all_count));
  }

  public static function connectPush($message, $device_token, $device_type, $options, $showerror = true, $all_count = 0, $cronjob = false) {
    global $wpdb;
    self::$cronSendOperation = $cronjob;
    if ($cronjob === true) {
      smpush_helper::$returnValue = 'cronjob';
    }
    $message = str_replace(array('"','\''), '`', self::cleanString($message));
    $sendCounter = 0;
    self::$sendoptions = $options;
    if ($device_type == 'ios' && self::$apisetting['apple_api_ver'] == 'http2') {
      $payload = self::getPayload($message);
      if (self::$sendoptions['expire'] > 0) {
        $expiry = current_time('timestamp') + (self::$sendoptions['expire'] * 3600);
      } else {
        $expiry = 0;
      }
      foreach ($device_token AS $key => $sDevice) {
        $sDevice['token'] = str_replace(array(' ', '-'), '', $sDevice['token']);
        if (isset($sDevice['id']) && $cronjob === false) {
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id='".$sDevice['id']."'");
        }
        unset($device_token[$key]);
        if (preg_match('~^[a-f0-9]{64}$~i', $sDevice['token'])) {
          if(smpush_env == 'debug'){
            $response = true;
            self::log('sent to: '.$sDevice['token']);
            self::log($payload);
          }
          else{
            $response = self::connectAPNS($sDevice['token'], $payload, 'ios');
          }
          if($response === false){
            if (self::$sendoptions['feedback'] == 1) {
              $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $sDevice['token'], 'device_type' => 'ios_invalid'));
            }
          }
          elseif($response === -1){
            self::updateStats('iosfail', 1);
          }
          elseif($response === true){
            //successfull message
          }
          else{
            return self::jsonPrint(0, '<p class="error">'.$response.'</p>');
          }
        }
        else {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $sDevice['token'], 'device_type' => 'ios_invalid'));
        }
        $sendCounter++;
      }
      self::updateStats('iossend', $sendCounter, $cronjob);
      if (!empty($_SESSION['smpush_firstrun'])) {
        $_SESSION['smpush_firstrun'] = 0;
        self::jsonPrint(2, array('message' => '<p>'.__('Connection With Apple server established successfully', 'smpush-plugin-lang').'</p>'.'<p>'.__('Apple server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'ios' && self::$apisetting['apple_api_ver'] == 'ssl') {
      $payload = self::getPayload($message);
      $ctx = stream_context_create();
      stream_context_set_option($ctx, 'ssl', 'local_cert', self::$apisetting['apple_cert_path']);
      stream_context_set_option($ctx, 'ssl', 'passphrase', self::$apisetting['apple_passphrase']);
      if (self::$apisetting['apple_sandbox'] == 1) {
        $appleserver = 'tls://gateway.sandbox.push.apple.com:2195';
      }
      else {
        $appleserver = 'tls://gateway.push.apple.com:2195';
      }
      @$fpssl = stream_socket_client($appleserver, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
      if (!$fpssl && $showerror) {
        if (empty($errstr))
          $errstr = __('Apple Certification error or problem with Password phrase', 'smpush-plugin-lang');
        if ($err == 111)
          $errstr .= __(' - Contact your host to enable outgoing ports', 'smpush-plugin-lang');
        elseif ($errstr == 'Connection timed out') {
          @fclose($fpssl);
          sleep(10);
          return self::jsonPrint(2, array('message' => '<p class="error">'.__('Connection timed out or your host blocked the outgoing port 2195...System trying reconnect now', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
        }
        self::jsonPrint(0, '<p class="error">'.__('Could not establish connection with SSL server', 'smpush-plugin-lang').': '.$errstr.'</p>');
      } elseif (!$fpssl)
        return;
      elseif (!empty($_GET['firstrun'])) {
        $_SESSION['smpush_firstrun'] = 1;
        @fclose($fpssl);
        self::jsonPrint(2, array('message' => '<p>'.__('Connection With Apple server established successfully', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
      if (self::$sendoptions['expire'] > 0) {
        $expiry = time() + (self::$sendoptions['expire'] * 3600);
      } else {
        $expiry = 0;
      }
      foreach ($device_token AS $key => $sDevice) {
        $sDevice['token'] = str_replace(array(' ', '-'), '', $sDevice['token']);
        if (isset($sDevice['id']) && $cronjob === false) {
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id='".$sDevice['id']."'");
        }
        unset($device_token[$key]);
        if (preg_match('~^[a-f0-9]{64}$~i', $sDevice['token'])) {
          if ($expiry > 0) {
            @$sslwrite = chr(1).pack("N", $sDevice['id']).pack("N", $expiry).pack("n", 32).pack('H*', $sDevice['token']).pack("n", strlen($payload)).$payload;
          } else {
            @$sslwrite = chr(0).pack('n', 32).pack('H*', $sDevice['token']).pack('n', strlen($payload)).$payload;
          }
          $sslwriteLen = strlen($sslwrite);
          if(smpush_env == 'debug'){
            $response = true;
            self::log('sent to: '.$sDevice['token']);
            self::log($payload);
          }
          elseif ($sslwriteLen !== (int) @fwrite($fpssl, $sslwrite)) {
            @fclose($fpssl);
            sleep(3);
            return self::jsonPrint(2, array('message' => '', 'all_count' => $all_count));
          }
          if (!empty($_SESSION['smpush_firstrun']) OR ( self::$sendoptions['iostestmode'] == 1 AND $cronjob === false)) {
            stream_set_blocking($fpssl, 0);
            stream_set_write_buffer($fpssl, 0);
            $read = array($fpssl);
            $null = NULL;
            $nChangedStreams = stream_select($read, $null, $null, 0, 1000000);
            if ($nChangedStreams !== false && $nChangedStreams > 0) {
              $status = @ord(fread($fpssl, 1));
              if (in_array($status, array(3, 4, 6, 7))) {
                @fclose($fpssl);
                self::jsonPrint(0, '<p class="error">'.__('Apple server error', 'smpush-plugin-lang').': '.self::$_aErrorResponseMessages[$status].'</p>');
              }
              if ($status == 8) {
                $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $sDevice['token'], 'device_type' => 'ios_invalid'));
                @fclose($fpssl);
                if (self::$sendoptions['iostestmode'] == 1) {
                  $_SESSION['smpush_firstrun'] = 0;
                  self::jsonPrint(2, array('message' => '<p>'.__('Apple server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
                } else {
                  self::connectPush($message, $device_token, $device_type, $options, true, $all_count, $cronjob);
                }
              }
            }
          }
          if (!empty($_SESSION['smpush_firstrun'])) {
            $_SESSION['smpush_firstrun'] = 0;
            @fclose($fpssl);
            self::jsonPrint(2, array('message' => '<p>'.__('Apple server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
          }
        } else {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $sDevice['token'], 'device_type' => 'ios_invalid'));
        }
      }
      @fclose($fpssl);
    }
    elseif ($device_type == 'safari' && self::$apisetting['apple_api_ver'] == 'http2') {
      $payload = array();
      $payload['aps']['alert'] = array(
        'title' => self::cleanString(self::$sendoptions['desktop_title']),
        'body' => $message
      );
      if(!empty(self::$sendoptions['ios_slide'])){
        $payload['aps']['alert']['action'] = self::$sendoptions['ios_slide'];
      }
      $payload['aps']['url-args'] = array(str_replace(get_bloginfo('url') , '', self::cleanString(self::$sendoptions['desktop_link'])));
      $payload = json_encode($payload);
      foreach ($device_token AS $key => $sDevice) {
        $sDevice['token'] = str_replace(array(' ', '-'), '', $sDevice['token']);
        if (isset($sDevice['id']) && $cronjob === false) {
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id='".$sDevice['id']."'");
        }
        if(!empty(self::$sendoptions['msgid'])){
          $wpdb->insert($wpdb->prefix.'push_desktop_messages', array('msgid' => self::$sendoptions['msgid'], 'token' => md5($sDevice['token']), 'type' => 'safari'));
        }
        unset($device_token[$key]);
        if(smpush_env == 'debug'){
          $response = true;
          self::log('sent to: '.$sDevice['token']);
          self::log($payload);
        }
        else{
          $response = self::connectAPNS($sDevice['token'], $payload, 'safari');
        }
        if($response === false){
          if (self::$sendoptions['feedback'] == 1) {
            $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $sDevice['token'], 'device_type' => 'safari_invalid'));
          }
        }
        elseif($response === -1){
          self::updateStats('safarifail', 1);
        }
        elseif($response === true){
          //successfull message
        }
        else{
          return self::jsonPrint(0, '<p class="error">'.$response.'</p>');
        }
        $sendCounter++;
      }
      self::updateStats('safarisend', $sendCounter, $cronjob);
      if (!empty($_GET['safari_notify'])) {
        self::jsonPrint('safari_server_reponse', array('message' => '<p>'.__('Connection With Safari server established successfully', 'smpush-plugin-lang').'</p><p>'.__('Safari server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'safari' && self::$apisetting['apple_api_ver'] == 'ssl') {
      $payload = array();
      $payload['aps']['alert'] = array(
        'title' => self::cleanString(self::$sendoptions['desktop_title']),
        'body' => $message
      );
      if(!empty(self::$sendoptions['ios_slide'])){
        $payload['aps']['alert']['action'] = self::$sendoptions['ios_slide'];
      }
      $payload['aps']['url-args'] = array(str_replace(get_bloginfo('url') , '', self::cleanString(self::$sendoptions['desktop_link'])));
      $payload = json_encode($payload);
      $ctx = stream_context_create();
      stream_context_set_option($ctx, 'ssl', 'local_cert', self::$apisetting['safari_cert_path']);
      stream_context_set_option($ctx, 'ssl', 'passphrase', self::$apisetting['safari_passphrase']);
      $appleserver = 'tls://gateway.push.apple.com:2195';
      @$fpssl = stream_socket_client($appleserver, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
      if (!$fpssl && $showerror) {
        if (empty($errstr))
          $errstr = __('Safari Certification error or problem with Password phrase', 'smpush-plugin-lang');
        if ($err == 111)
          $errstr .= __(' - Contact your host to enable outgoing ports', 'smpush-plugin-lang');
        elseif ($errstr == 'Connection timed out') {
          @fclose($fpssl);
          sleep(10);
          return self::jsonPrint(2, array('message' => '<p class="error">'.__('Connection timed out or your host blocked the outgoing port 2195...System trying reconnect now', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
        }
        self::jsonPrint(0, '<p class="error">'.__('Could not establish connection with Safari SSL server', 'smpush-plugin-lang').': '.$errstr.'</p>');
      }
      elseif (!$fpssl)return;
      foreach ($device_token AS $key => $sDevice) {
        $sDevice['token'] = str_replace(array(' ', '-'), '', $sDevice['token']);
        if (isset($sDevice['id']) && $cronjob === false) {
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id='".$sDevice['id']."'");
        }
        if(!empty(self::$sendoptions['msgid'])){
          $wpdb->insert($wpdb->prefix.'push_desktop_messages', array('msgid' => self::$sendoptions['msgid'], 'token' => md5($sDevice['token']), 'type' => 'safari'));
        }
        unset($device_token[$key]);
        @$sslwrite = chr(0).pack('n', 32).pack('H*', $sDevice['token']).pack('n', strlen($payload)).$payload;
        $sslwriteLen = strlen($sslwrite);
        if(smpush_env == 'debug'){
          $response = true;
          self::log('sent to: '.$sDevice['token']);
          self::log($payload);
        }
        elseif ($sslwriteLen !== (int) @fwrite($fpssl, $sslwrite)) {
          @fclose($fpssl);
          sleep(3);
          return self::jsonPrint(2, array('message' => '', 'all_count' => $all_count));
        }
      }
      fclose($fpssl);
      if (!empty($_GET['safari_notify'])) {
        self::jsonPrint('safari_server_reponse', array('message' => '<p>'.__('Connection With Safari server established successfully', 'smpush-plugin-lang').'</p><p>'.__('Safari server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'wp') {
      $payload = array();
      if (!empty(self::$sendoptions['wp_extravalue'])) {
        if (self::$sendoptions['wp_extra_type'] == 'normal') {
          $payload['relatedvalue'] = stripslashes(self::$sendoptions['wp_extravalue']);
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['wp_extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $payload[$key] = stripslashes($value);
            }
          }
        }
      }
      elseif (!empty(self::$sendoptions['extravalue'])) {
        if (self::$sendoptions['extra_type'] == 'normal') {
          $payload['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $payload[$key] = stripslashes($value);
            }
          }
        }
      }
      if (!function_exists('curl_init') && $showerror)
        self::jsonPrint(0, '<p class="error">'.__('Windows Phone: '.__('CURL Library is not support in your host', 'smpush-plugin-lang').'', 'smpush-plugin-lang').'</p>');
      elseif (!function_exists('curl_init'))
        return;
      
      foreach($device_token['token'] as $key => $token){
        if(smpush_env == 'debug'){
          self::log('sent to: '.$token);
          self::log($message);
          self::log($payload);
          $response = true;
        }
        else{
          $response = WindowsPhonePushNotification::push_toast($token, $message, $payload);
        }
        if (!empty($response['X-DeviceConnectionStatus']) && $response['X-DeviceConnectionStatus'] == 'Expired' && self::$sendoptions['feedback'] == 1) {
          self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($token)."'"));
          self::updateStats('wpfail', 1);
        }
        elseif ($response === false && $showerror) {
          self::jsonPrint(0, '<p class="error">'.__('Windows Phone push notification server not responding or unauthorized response', 'smpush-plugin-lang').'</p>');
        }
      }
      if (isset($device_token['id'])) {
        self::updateStats('wpsend', count($device_token['id']), $cronjob);
        if ($cronjob === false) {
          $ids = implode(',', $device_token['id']);
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id IN($ids)");
        }
      }
      if (!empty($_GET['wp_notify'])) {
        self::jsonPrint('wp_server_reponse', array('message' => '<p>'.__('Connection With Windows Phone server established successfully', 'smpush-plugin-lang').'</p><p>'.__('Windows Phone server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'wp10') {
      $payload = array();
      if (!empty(self::$sendoptions['wp10_extravalue'])) {
        if (self::$sendoptions['wp10_extra_type'] == 'normal') {
          $payload['relatedvalue'] = stripslashes(self::$sendoptions['wp10_extravalue']);
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['wp10_extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $payload[$key] = stripslashes($value);
            }
          }
        }
      }
      elseif (!empty(self::$sendoptions['extravalue'])) {
        if (self::$sendoptions['extra_type'] == 'normal') {
          $payload['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $payload[$key] = stripslashes($value);
            }
          }
        }
      }
      if (!function_exists('curl_init') && $showerror)
        self::jsonPrint(0, '<p class="error">Windows 10: '.__('CURL Library is not support in your host', 'smpush-plugin-lang').'</p>');
      elseif (!function_exists('curl_init'))
        return;
      
      foreach($device_token['token'] as $key => $token){
        if(smpush_env == 'debug'){
          self::log('sent to: '.$token);
          self::log($message);
          self::log($payload);
          $response = true;
        }
        else{
          $response = UniversalWindows10::pushToastWP10($token, $message, $payload, self::$sendoptions['wp10_img']);
        }
        if ($response === false) {
          if(self::$sendoptions['feedback'] == 1){
            self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($token)."'"));
            self::updateStats('wp10fail', 1);
          }
        }
        elseif ($response !== true && $showerror) {
          self::jsonPrint(0, '<p class="error">'.__('Windows 10 push notification server returns error', 'smpush-plugin-lang').': '.$response.'</p>');
        }
      }
      if (isset($device_token['id'])) {
        self::updateStats('wp10send', count($device_token['id']), $cronjob);
        if ($cronjob === false) {
          $ids = implode(',', $device_token['id']);
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id IN($ids)");
        }
      }
      if (!empty($_GET['wp10_notify'])) {
        self::jsonPrint('wp10_server_reponse', array('message' => '<p>'.__('Connection With Windows 10 server established successfully', 'smpush-plugin-lang').'</p><p>'.__('Windows 10 server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'bb') {
      if (!function_exists('curl_init') && $showerror)
        self::jsonPrint(0, '<p class="error">BlackBerry: '.__('CURL Library is not support in your host', 'smpush-plugin-lang').'</p>');
      elseif (!function_exists('curl_init'))
        return;
      
      $payload = array();
      $payload['message'] = $message;
      if (!empty(self::$sendoptions['bb_extravalue'])) {
        if (self::$sendoptions['bb_extra_type'] == 'normal') {
          $payload['relatedvalue'] = stripslashes(self::$sendoptions['bb_extravalue']);
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['bb_extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $payload[$key] = stripslashes($value);
            }
          }
        }
      }
      elseif (!empty(self::$sendoptions['extravalue'])) {
        if (self::$sendoptions['extra_type'] == 'normal') {
          $payload['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $payload[$key] = stripslashes($value);
            }
          }
        }
      }
      
      if(smpush_env == 'debug'){
        $response = true;
        self::log('sent to: '.$device_token['token']);
        self::log($payload);
      }
      else{
        $response = blackBerryPushNotification::pushMessage($device_token['token'], $payload, $showerror);
      }
      
      if (isset($device_token['id'])) {
        self::updateStats('bbsend', count($device_token['id']), $cronjob);
        if ($cronjob === false) {
          $ids = implode(',', $device_token['id']);
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id IN($ids)");
        }
        if (self::$sendoptions['feedback'] == 1) {
          //$wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => serialize($device_token['token']), 'feedback' => $response, 'device_type' => 'bb'));
        }
      }
      if (!empty($_GET['bb_notify'])) {
        self::jsonPrint('bb_server_reponse', array('message' => '<p>'.__('Connection With BlackBerry server established successfully', 'smpush-plugin-lang').'</p><p>'.__('BlackBerry server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'android') {
      $message = html_entity_decode(preg_replace("/U\+([0-9A-F]{4,5})/", "&#x\\1;", $message), ENT_NOQUOTES, 'UTF-8');
      $baseurl = 'https://fcm.googleapis.com/fcm/send';
      if (self::$apisetting['android_titanium_payload'] == 1) {
        $data = array();
        $data['payload']['android']['alert'] = $message;
      }
      elseif (self::$apisetting['android_corona_payload'] == 1) {
        $data = array();
        $data['alert'] = $message;
      }
      else {
        $data = array('message' => $message);
      }
      if (!empty(self::$sendoptions['and_extravalue'])) {
        if (self::$sendoptions['and_extra_type'] == 'normal') {
          if (self::$apisetting['android_titanium_payload'] == 1) {
            $data['payload']['android']['relatedvalue'] = stripslashes(self::$sendoptions['and_extravalue']);
          } else {
            $data['relatedvalue'] = stripslashes(self::$sendoptions['and_extravalue']);
          }
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['and_extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              if (self::$apisetting['android_titanium_payload'] == 1 && !in_array($key, array('title', 'icon', 'badge', 'sound', 'vibrate'))) {
                $data['payload']['android'][$key] = stripslashes($value);
              } elseif (self::$apisetting['android_titanium_payload'] == 1) {
                $data['payload'][$key] = stripslashes($value);
              } else {
                $data[$key] = stripslashes($value);
              }
            }
          }
        }
      }
      elseif (!empty(self::$sendoptions['extravalue'])) {
        if (self::$sendoptions['extra_type'] == 'normal') {
          if (self::$apisetting['android_titanium_payload'] == 1) {
            $data['payload']['android']['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
          } else {
            $data['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
          }
        } else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              if (self::$apisetting['android_titanium_payload'] == 1 && !in_array($key, array('title', 'icon', 'badge', 'sound', 'vibrate'))) {
                $data['payload']['android'][$key] = stripslashes($value);
              } elseif (self::$apisetting['android_titanium_payload'] == 1) {
                $data['payload'][$key] = stripslashes($value);
              } else {
                $data[$key] = stripslashes($value);
              }
            }
          }
        }
      }
      $fields = array('registration_ids' => $device_token['token'], 'data' => $data);
      if (self::$sendoptions['expire'] > 0) {
        $fields['time_to_live'] = self::$sendoptions['expire'] * 3600;
      }
      $headers = array('Authorization: key='.self::$apisetting['google_apikey'], 'Content-Type: application/json');
      if (!function_exists('curl_init') && $showerror)
        self::jsonPrint(0, '<p class="error">Google: '.__('CURL Library is not support in your host', 'smpush-plugin-lang').'</p>');
      elseif (!function_exists('curl_init'))
        return;
      $chandle = curl_init();
      curl_setopt($chandle, CURLOPT_URL, $baseurl);
      curl_setopt($chandle, CURLOPT_POST, true);
      curl_setopt($chandle, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($chandle, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($chandle, CURLOPT_SSL_VERIFYPEER, false);
      if(phpversion() >= 5.4){
        curl_setopt($chandle, CURLOPT_POSTFIELDS, json_encode($fields, JSON_UNESCAPED_UNICODE));
      }
      else{
        curl_setopt($chandle, CURLOPT_POSTFIELDS, json_encode($fields));
      }
      if(smpush_env == 'debug'){
        $result = true;
        $httpcode = 200;
        self::log($device_token['token']);
        self::log($data);
      }
      else{
        $result = curl_exec($chandle);
        $httpcode = curl_getinfo($chandle, CURLINFO_HTTP_CODE);
      }
      if ($result === FALSE && $showerror) {
        self::jsonPrint(0, '<p class="error">'.__('Google push notification server not responding', 'smpush-plugin-lang').'</p>');
      }
      elseif ($httpcode == 503 && $showerror) {
        self::jsonPrint(0, '<p class="error">'.__('Google push notification server not responding', 'smpush-plugin-lang').'</p>');
      }
      elseif ($httpcode == 401 && $showerror) {
        $result = json_decode($result);
        if (!empty($result->results[0]->error))
          self::jsonPrint(0, '<p class="error">'.$result->results[0]->error.'</p>');
        else
          self::jsonPrint(0, '<p class="error">'.__('Invalid Google API key', 'smpush-plugin-lang').'</p>');
      }
      if (isset($device_token['id'])) {
        self::updateStats('androidsend', count($device_token['id']), $cronjob);
        if ($cronjob === false) {
          $ids = implode(',', $device_token['id']);
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id IN($ids)");
        }
        if (self::$sendoptions['feedback'] == 1) {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => serialize($device_token['token']), 'feedback' => $result, 'device_type' => 'android'));
        }
      }
      curl_close($chandle);
      if (!empty($_GET['google_notify'])) {
        self::jsonPrint(3, array('message' => '<p>'.__('Connection With Google server established successfully', 'smpush-plugin-lang').'</p><p>'.__('Google server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'chrome') {
      $baseurl = 'https://fcm.googleapis.com/fcm/send';
      $data = array('message' => $message);
      if (!empty(self::$sendoptions['extravalue'])) {
        if (self::$sendoptions['extra_type'] == 'normal') {
          $data['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        }
        else {
          $extravalue = json_decode(stripslashes(self::$sendoptions['extravalue']));
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $data[$key] = stripslashes($value);
            }
          }
        }
      }
      $fields = array('registration_ids' => $device_token['token'], 'data' => $data);
      if (self::$sendoptions['expire'] > 0) {
        $fields['time_to_live'] = self::$sendoptions['expire'] * 3600;
      }
      $headers = array('Authorization: key='.self::$apisetting['chrome_apikey'], 'Content-Type: application/json');
      if (!function_exists('curl_init') && $showerror)
        self::jsonPrint(0, '<p class="error">Google: '.__('CURL Library is not support in your host', 'smpush-plugin-lang').'</p>');
      elseif (!function_exists('curl_init'))
        return;
      $chandle = curl_init();
      curl_setopt($chandle, CURLOPT_URL, $baseurl);
      curl_setopt($chandle, CURLOPT_POST, true);
      curl_setopt($chandle, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($chandle, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($chandle, CURLOPT_SSL_VERIFYPEER, false);
      if(phpversion() >= 5.4){
        curl_setopt($chandle, CURLOPT_POSTFIELDS, json_encode($fields, JSON_UNESCAPED_UNICODE));
      }
      else{
        curl_setopt($chandle, CURLOPT_POSTFIELDS, json_encode($fields));
      }
      if(smpush_env == 'debug'){
        $result = true;
        $httpcode = 200;
        self::log($device_token['token']);
        self::log($data);
      }
      else{
        $result = curl_exec($chandle);
        $httpcode = curl_getinfo($chandle, CURLINFO_HTTP_CODE);
      }
      if ($result === FALSE && $showerror) {
        self::jsonPrint(0, '<p class="error">'.__('Chrome push notification server not responding', 'smpush-plugin-lang').'</p>');
      }
      elseif ($httpcode == 503 && $showerror) {
        self::jsonPrint(0, '<p class="error">'.__('Chrome push notification server not responding', 'smpush-plugin-lang').'</p>');
      }
      elseif ($httpcode == 401 && $showerror) {
        $result = json_decode($result);
        if (!empty($result->results[0]->error))
          self::jsonPrint(0, '<p class="error">'.$result->results[0]->error.'</p>');
        else
          self::jsonPrint(0, '<p class="error">'.__('Invalid Chrome API key', 'smpush-plugin-lang').'</p>');
      }
      if (isset($device_token['id'])) {
        self::updateStats('chromesend', count($device_token['id']), $cronjob);
        if ($cronjob === false) {
          $ids = implode(',', $device_token['id']);
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id IN($ids)");
        }
        if (self::$sendoptions['feedback'] == 1) {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => serialize($device_token['token']), 'feedback' => $result, 'device_type' => 'chrome'));
        }
      }
      if(!empty(self::$sendoptions['msgid'])){
        foreach($device_token['token'] as $token){
          $wpdb->insert($wpdb->prefix.'push_desktop_messages', array('msgid' => self::$sendoptions['msgid'], 'token' => md5($token), 'type' => 'chrome'));
        }
      }
      curl_close($chandle);
      if (!empty($_GET['chrome_notify'])) {
        self::jsonPrint('chrome_server_reponse', array('message' => '<p>'.__('Connection With Chrome server established successfully', 'smpush-plugin-lang').'</p><p>'.__('Chrome server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
    elseif ($device_type == 'firefox') {
      $baseurl = 'https://updates.push.services.mozilla.com/wpush/v1/';
      if (!function_exists('curl_init') && $showerror)
        self::jsonPrint(0, '<p class="error">Firefox: '.__('CURL Library is not support in your host', 'smpush-plugin-lang').'</p>');
      elseif (!function_exists('curl_init'))
        return;
      foreach($device_token['token'] as $token){
        $chandle = curl_init();
        curl_setopt($chandle, CURLOPT_URL, $baseurl.$token);
        curl_setopt($chandle, CURLOPT_POST, TRUE);
        curl_setopt($chandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chandle, CURLOPT_SSL_VERIFYPEER, false);
        if(smpush_env == 'debug'){
          $result = true;
          $httpcode = 200;
          self::log('sent to: '.$token);
          self::log($data);
        }
        else{
          $result = curl_exec($chandle);
          $httpcode = curl_getinfo($chandle, CURLINFO_HTTP_CODE);
        }
        if (($httpcode == 404 || $httpcode == 410) && self::$sendoptions['feedback'] == 1) {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $token, 'device_type' => 'firefox'));
        }
        if(!empty(self::$sendoptions['msgid']) && $httpcode != 404){
          $wpdb->insert($wpdb->prefix.'push_desktop_messages', array('msgid' => self::$sendoptions['msgid'], 'token' => md5($token), 'type' => 'firefox'));
        }
        curl_close($chandle);
      }
      if (isset($device_token['id'])) {
        self::updateStats('firefoxsend', count($device_token['id']), $cronjob);
        if ($cronjob === false) {
          $ids = implode(',', $device_token['id']);
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id IN($ids)");
        }
      }
      if (!empty($_GET['firefox_notify'])) {
        self::jsonPrint('firefox_server_reponse', array('message' => '<p>'.__('Connection With Firefox server established successfully', 'smpush-plugin-lang').'</p><p>'.__('Firefox server accepts the payload and start working', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
      }
    }
  }

  public static function connectFeedback($all_count, $cronjob = false) {
    global $wpdb;
    self::$cronSendOperation = $cronjob;
    if ($cronjob === true) {
      smpush_helper::$returnValue = 'cronjob';
    }
    $fail = $androidfail = $chromefail = 0;
    $feedbacks = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_feedback");
    foreach ($feedbacks AS $feedback) {
      if ($feedback->device_type == 'ios_invalid') {
        self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($feedback->tokens)."'"));
        self::updateStats('iosfail', 1);
      }
      elseif ($feedback->device_type == 'safari_invalid') {
        self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($feedback->tokens)."'"));
        self::updateStats('safarifail', 1);
      }
      elseif ($feedback->device_type == 'android') {
        if (!empty($_GET['feedback_google'])) {
          self::jsonPrint(5, '<p>'.__('Start processing Google feedback queries', 'smpush-plugin-lang').'</p>');
        }
        $tokens = unserialize($feedback->tokens);
        $result = json_decode($feedback->feedback, true);
        foreach ($result['results'] AS $key => $status) {
          if (isset($status['error'])) {
            if ($status['error'] == 'InvalidRegistration' || $status['error'] == 'NotRegistered' || $status['error'] == 'MismatchSenderId') {
              self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($tokens[$key])."'"));
              $androidfail++;
            }
          }
        }
        self::updateStats('androidfail', $androidfail);
      }
      elseif ($feedback->device_type == 'chrome') {
        if (!empty($_GET['feedback_chrome'])) {
          self::jsonPrint(6, '<p>'.__('Start processing Chrome feedback queries', 'smpush-plugin-lang').'</p>');
        }
        $tokens = unserialize($feedback->tokens);
        $result = json_decode($feedback->feedback, true);
        foreach ($result['results'] AS $key => $status) {
          if (isset($status['error'])) {
            if ($status['error'] == 'InvalidRegistration' || $status['error'] == 'NotRegistered' || $status['error'] == 'MismatchSenderId') {
              self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($tokens[$key])."'"));
              $chromefail++;
            }
          }
        }
        self::updateStats('chromefail', $chromefail);
      }
      elseif ($feedback->device_type == 'firefox') {
        self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($feedback->tokens)."'"));
        self::updateStats('firefoxfail', 1);
      }
      elseif ($feedback->device_type == 'ios' && self::$apisetting['apple_api_ver'] == 'ssl' && !empty(self::$apisetting['apple_cert_path']) && !empty(self::$apisetting['apple_passphrase'])) {
        if (!empty($_GET['feedback_open'])) {
          self::jsonPrint(4, '<p>'.__('Start connection and reading with Apple feedback server, Maybe takes some time', 'smpush-plugin-lang').'</p>');
        }
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', self::$apisetting['apple_cert_path']);
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$apisetting['apple_passphrase']);
        if (self::$apisetting['apple_sandbox'] == 1) {
          $appleserver = 'tls://feedback.sandbox.push.apple.com:2196';
        } else {
          $appleserver = 'tls://feedback.push.apple.com:2196';
        }
        @$fpssl = stream_socket_client($appleserver, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fpssl) {
          if (empty($errstr))
            $errstr = __('Apple certification error or problem with Password phrase', 'smpush-plugin-lang');
          if ($err == 111)
            $errstr .= __(' - Contact your host to enable outgoing ports', 'smpush-plugin-lang');
          self::jsonPrint(0, '<p class="error">'.__('Could not establish connection with SSL server', 'smpush-plugin-lang').': '.$errstr.'</p>');
        }
        $nFeedbackTupleLen = self::TIME_BINARY_SIZE + self::TOKEN_LENGTH_BINARY_SIZE + self::DEVICE_BINARY_SIZE;
        $sBuffer = '';
        while (!feof($fpssl)) {
          $sBuffer .= $sCurrBuffer = fread($fpssl, 8192);
          $nCurrBufferLen = strlen($sCurrBuffer);
          unset($sCurrBuffer, $nCurrBufferLen);
          $nBufferLen = strlen($sBuffer);
          if ($nBufferLen >= $nFeedbackTupleLen) {
            $nFeedbackTuples = floor($nBufferLen / $nFeedbackTupleLen);
            for ($i = 0; $i < $nFeedbackTuples; $i++) {
              $sFeedbackTuple = substr($sBuffer, 0, $nFeedbackTupleLen);
              $sBuffer = substr($sBuffer, $nFeedbackTupleLen);
              $aFeedback = self::_parseBinaryTuple($sFeedbackTuple);
              self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {md5token_name}='".md5($aFeedback[deviceToken])."'"));
              $fail++;
              unset($aFeedback);
            }
          }
          $read = array($fpssl);
          $null = NULL;
          $nChangedStreams = stream_select($read, $null, $null, 0, 1000000);
          if ($nChangedStreams === false) {
            break;
          }
        }
        self::updateStats('iosfail', $fail);
        if ($fail > 0) {
          self::jsonPrint(2, array('message' => '<p>'.__('Reading from Apple feedback is finised, try to read again for more', 'smpush-plugin-lang').'</p>', 'all_count' => $all_count));
        }
      }
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_feedback WHERE id='".$feedback->id."'");
    }
  }

  protected static function _getPayload($message) {
    if (self::$apisetting['ios_titanium_payload'] == 1) {
      $aPayload['aps'] = array();
      $aPayload['aps']['alert'] = $message;
      if (!empty(self::$sendoptions['ios_sound'])) {
        $aPayload['aps']['sound'] = stripslashes(self::$sendoptions['ios_sound']);
      }
      if (!empty(self::$sendoptions['ios_badge'])) {
        $aPayload['aps']['badge'] = (int)self::$sendoptions['ios_badge'];
      }
      if (!empty(self::$sendoptions['extravalue'])) {
        if (self::$sendoptions['extra_type'] == 'normal') {
          $aPayload['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        } else {
          $extravalue = json_decode(self::$sendoptions['extravalue']);
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $aPayload[$key] = stripslashes($value);
            }
          }
        }
      }
    } else {
      $aPayload['aps'] = array();
      if (!empty(self::$sendoptions['ios_slide']) OR ! empty(self::$sendoptions['ios_launchimg'])) {
        $aPayload['aps']['alert']['body'] = $message;
        if (!empty(self::$sendoptions['ios_slide'])) {
          $aPayload['aps']['alert']['action-loc-key'] = stripslashes(self::$sendoptions['ios_slide']);
        }
        if (!empty(self::$sendoptions['ios_launchimg'])) {
          $aPayload['aps']['alert']['launch-image'] = stripslashes(self::$sendoptions['ios_launchimg']);
        }
      } else {
        $aPayload['aps']['alert'] = $message;
      }
      if (!empty(self::$sendoptions['ios_sound'])) {
        $aPayload['aps']['sound'] = stripslashes(self::$sendoptions['ios_sound']);
      }
      if (!empty(self::$sendoptions['ios_cavailable'])) {
        $aPayload['aps']['content-available'] = self::$sendoptions['ios_cavailable'];
      }
      if (!empty(self::$sendoptions['ios_badge'])) {
        $aPayload['aps']['badge'] = (int)self::$sendoptions['ios_badge'];
      }
      if (!empty(self::$sendoptions['extravalue'])) {
        if (self::$sendoptions['extra_type'] == 'normal') {
          if (self::$apisetting['android_corona_payload'] == 1) {
            $aPayload['aps']['custom'] = json_encode(array('relatedvalue' => stripslashes(self::$sendoptions['extravalue'])));
          }
          else{
            $aPayload['aps']['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
          }
        }
        elseif (self::$apisetting['android_corona_payload'] == 1) {
          $aPayload['aps']['custom'] = self::$sendoptions['extravalue'];
        }
        else {
          $extravalue = json_decode(self::$sendoptions['extravalue']);
          if ($extravalue) {
            foreach ($extravalue AS $key => $value) {
              $aPayload['aps'][$key] = stripslashes($value);
            }
          }
        }
      }
    }
    return $aPayload;
  }

  protected static function getPayload($message) {
    $message = html_entity_decode(preg_replace("/U\+([0-9A-F]{4,5})/", "&#x\\1;", $message), ENT_NOQUOTES, 'UTF-8');
    if (phpversion() < 5.3 OR self::$apisetting['stop_summarize'] == 1) {
      return json_encode(self::_getPayload($message));
    }
    @$sJSON = json_encode(self::_getPayload($message), defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
    if (!defined('JSON_UNESCAPED_UNICODE') && function_exists('mb_convert_encoding')) {
      $sJSON = preg_replace_callback('~\\\\u([0-9a-f]{4})~i', create_function('$aMatches', 'return mb_convert_encoding(pack("H*", $aMatches[1]), "UTF-8", "UTF-16");'), $sJSON);
    }
    $sJSONPayload = str_replace('"aps":[]', '"aps":{}', $sJSON);
    $nJSONPayloadLen = strlen($sJSONPayload);
    if ($nJSONPayloadLen > 256) {
      $nMaxTextLen = $nTextLen = strlen($message) - ($nJSONPayloadLen - 256);
      if ($nMaxTextLen > 0) {
        while (strlen($message = mb_substr($message, 0,  --$nTextLen, 'UTF-8')) > $nMaxTextLen);
        return self::getPayload($message);
      } else {
        self::jsonPrint(0, '<p class="error">Apple notification message is too long: '.$nJSONPayloadLen.' bytes. Maximum size is 256 bytes</p>');
      }
    }
    return $sJSONPayload;
  }
  
  protected static function connectAPNS($deviceToken, $payload, $platform) {
    if (!defined('CURL_HTTP_VERSION_2_0')) {
      define('CURL_HTTP_VERSION_2_0', 3);
    }
    if($platform == 'safari'){
      $cert = self::$apisetting['safari_cert_path'];
      $passphrase = self::$apisetting['safari_passphrase'];
      $appid = self::$apisetting['safari_web_id'];
      $serverAPNS = 'https://api.push.apple.com/3/device/';
    }
    else{
      $cert = self::$apisetting['apple_cert_path'];
      $passphrase = self::$apisetting['apple_passphrase'];
      $appid = self::$apisetting['apple_appid'];
      if (self::$apisetting['apple_sandbox'] == 1) {
        $serverAPNS = 'https://api.development.push.apple.com/3/device/';
      }
      else {
        $serverAPNS = 'https://api.push.apple.com/3/device/';
      }
    }
    $chandle = curl_init();
    curl_setopt($chandle, CURLOPT_URL, $serverAPNS.$deviceToken);
    curl_setopt($chandle, CURLOPT_PORT, 443);
    curl_setopt($chandle, CURLOPT_POST, true);
    curl_setopt($chandle, CURLOPT_HTTPHEADER, array('apns-topic: '.$appid));
    curl_setopt($chandle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chandle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($chandle, CURLOPT_SSLCERT, $cert);
    curl_setopt($chandle, CURLOPT_SSLCERTPASSWD, $passphrase);
    curl_setopt($chandle, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($chandle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
    $response = curl_exec($chandle);
    $httpcode = curl_getinfo($chandle, CURLINFO_HTTP_CODE);
    curl_close($chandle);

    if (!empty($response)) {
      $response = json_decode($response, true);
      if(isset(self::$apnsErrors[$response['reason']])){
        $failMSG = self::$apnsErrors[$response['reason']];
      }
      else{
        $failMSG = '';
      }
    }

    switch ($httpcode) {
      case 200:
        return true;
      case 400:
        if ($response['reason'] == 'BadDeviceToken' || $response['reason'] == 'DeviceTokenNotForTopic') {
          //invalid device token
          return false;
        } else {
          return($failMSG);
        }
        break;
      case 403:
        return($failMSG);
      case 404:
        return($failMSG);
      case 405:
        return($failMSG);
      case 410:
        //invalid device token
        return false;
      case 413:
        return($failMSG);
      case 429:
        //not received
        return -1;
      case 500:
        return($failMSG);
      case 503:
        return($failMSG);
      case 0:
        return('Server must be installed CURL version >= 7.46 and OpenSSL version >= 1.0.2e with HTTP/2 enabled');
    }
  }

  public static function updateStats($index = '', $value = 0, $cronjob = false) {
    if (self::$cronSendOperation === true OR $cronjob === true) {
      $transient = 'smpush_cron_stats';
    }
    else {
      $transient = 'smpush_stats';
    }
    if (empty($index)) {
      if (self::$cronSendOperation === false AND $cronjob === false AND ! empty(self::$sendoptions['message'])) {
        $handler_options = get_option('smpush_instant_send');
        $archiveid = $handler_options['msgid'];
      }
      else {
        $archiveid = 0;
      }
      $stats = array('totalsend' => 0, 'iossend' => 0, 'iosfail' => 0, 'androidsend' => 0, 'androidfail' => 0, 'wpsend' => 0, 'wpfail' => 0, 'wp10send' => 0, 'wp10fail' => 0, 'bbsend' => 0, 'bbfail' => 0, 'chromesend' => 0, 'chromefail' => 0, 'safarisend' => 0, 'safarifail' => 0, 'firefoxsend' => 0, 'firefoxfail' => 0, 'archiveid' => $archiveid);
      update_option($transient, $stats);
      return;
    }
    $stats = get_option($transient);
    if ($index == 'all') {
      $stats['totalfail'] = $stats['iosfail'] + $stats['androidfail'] + $stats['wpfail'] + $stats['wp10fail'] + $stats['bbfail'] + $stats['chromefail'] + $stats['safarifail'] + $stats['firefoxfail'];
      $archid = $stats['archiveid'];
      unset($stats['archiveid']);
      $result = self::printReport($stats);
      if (self::$cronSendOperation === true) {
        return $stats;
      }
      global $wpdb;
      $wpdb->update($wpdb->prefix.'push_archive', array('endtime' => date('Y-m-d H:i:s')), array('id' => $archid));
      $wpdb->insert($wpdb->prefix.'push_archive_reports', array('msgid' => $archid, 'report_time' => current_time('timestamp'), 'report' => serialize($stats)));
      return self::jsonPrint(-1, $result);
    }
    if ($index == 'totalsend') {
      if ($stats[$index] > 0)
        return;
    }
    $stats[$index] = $stats[$index] + $value;
    update_option($transient, $stats);
  }

  public static function printReport($stats) {
    if (isset($stats['error'])) {
      return '<p><strong>'.$stats['error'].'</strong></p>';
    }
    $result = '<p><strong>IOS '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['iossend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver or invalid tokens', 'smpush-plugin-lang').': '.$stats['iosfail'].' '.__('device token', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>Android '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['androidsend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Successful delivered', 'smpush-plugin-lang').': '.($stats['androidsend'] - $stats['androidfail']).' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver and invalid tokens', 'smpush-plugin-lang').': '.$stats['androidfail'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>Windows Phone '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['wpsend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Successful delivered', 'smpush-plugin-lang').': '.($stats['wpsend'] - $stats['wpfail']).' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver and invalid tokens', 'smpush-plugin-lang').': '.$stats['wpfail'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>Blackberry '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['bbsend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Successful delivered', 'smpush-plugin-lang').': '.($stats['bbsend'] - $stats['bbfail']).' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver and invalid tokens', 'smpush-plugin-lang').': '.$stats['bbfail'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>Windows 10 '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['wp10send'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Successful delivered', 'smpush-plugin-lang').': '.($stats['wp10send'] - $stats['wp10fail']).' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver and invalid tokens', 'smpush-plugin-lang').': '.$stats['wp10fail'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>Chrome '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['chromesend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Successful delivered', 'smpush-plugin-lang').': '.($stats['chromesend'] - $stats['chromefail']).' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver and invalid tokens', 'smpush-plugin-lang').': '.$stats['chromefail'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>Safari '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['safarisend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Successful delivered', 'smpush-plugin-lang').': '.($stats['safarisend'] - $stats['safarifail']).' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver and invalid tokens', 'smpush-plugin-lang').': '.$stats['safarifail'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>Firefox '.__('Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent messages', 'smpush-plugin-lang').': '.$stats['firefoxsend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Successful delivered', 'smpush-plugin-lang').': '.($stats['firefoxsend'] - $stats['firefoxfail']).' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver and invalid tokens', 'smpush-plugin-lang').': '.$stats['firefoxfail'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p><strong>'.__('Total Report', 'smpush-plugin-lang').':</strong></p>';
    $result .= '<p>'.__('Total sent', 'smpush-plugin-lang').': '.$stats['totalsend'].' '.__('message', 'smpush-plugin-lang').'</p>';
    $result .= '<p>'.__('Failure to deliver or invalid tokens', 'smpush-plugin-lang').': '.$stats['totalfail'].' '.__('device token', 'smpush-plugin-lang').'</p>';
    return $result;
  }

  public static function SendPush($ids, $message, $extravalue) {return;}
  
  protected static function _parseBinaryTuple($sBinaryTuple) {
    return unpack('Ntimestamp/ntokenLength/H*deviceToken', $sBinaryTuple);
  }
  
}