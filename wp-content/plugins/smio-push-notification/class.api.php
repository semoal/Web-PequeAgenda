<?php

class smpush_api extends smpush_controller{
  public $counter = 0;
  public $dateformat;
  public $queryorder;
  protected $carry;

  public function __construct($method, $returnValue=false, $carry = ''){
    $auth_key = $this->get_option('auth_key');
    $this->ParseOutput = true;
    $this->carry = $carry;
    self::$returnValue = $returnValue;
    $samedomain = false;
    if(!empty($carry)){
      $samedomain = true;
    }
    if(!empty($_SERVER['HTTP_REFERER']) && parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) == $_SERVER['HTTP_HOST']){
      $samedomain = true;
    }
    if(!$samedomain && !empty($auth_key) && isset($auth_key)){
      if($this->get_option('complex_auth') == 1){
        $auth_keys = array();
        $minutenow = date('i');
        $minuteafter = ($minutenow+1 > 59)? 0 : $minutenow+1;
        $minutebefore = ($minutenow-1 < 0)? 59 : $minutenow-1;
        $auth_keys[] = md5(date('m/d/Y').$auth_key.date('H').$minutenow);
        $auth_keys[] = md5(date('m/d/Y').$auth_key.date('H').$minuteafter);
        $auth_keys[] = md5(date('m/d/Y').$auth_key.date('H').$minutebefore);
      }
      else{
        $auth_keys = array($auth_key);
      }
      if(!empty($_REQUEST['auth_key'])){
        $input_auth_key = $_REQUEST['auth_key'];
      }
      else{
        $input_auth_key = $this->checkReqHeader('auth_key');
      }
      if(!in_array($input_auth_key, $auth_keys))
        return $this->output(0, __('Authentication failed: Authentication key is required to proceed', 'smpush-plugin-lang'));
    }
    if(!isset($_REQUEST['orderby'])){
      $_REQUEST['orderby'] = '';
    }
    if(isset($_REQUEST['order'])){
      if(strtolower($_REQUEST['order']) == 'asc')
          $this->queryorder = 'ASC';
      elseif(strtolower($_REQUEST['order']) == 'desc')
          $this->queryorder = 'DESC';
      else
          $this->queryorder = false;
    }
    if(!empty($_REQUEST['device_token'])){
      $_REQUEST['device_token'] = urldecode($_REQUEST['device_token']);
    }
    if(method_exists($this, $method))
        $this->$method();
    else
        return $this->output(0, __('You called unavailable method', 'smpush-plugin-lang').' `'.$method.'`');
  }

  public function cron_job(){
    smpush_cronsend::cronStart();
  }

  public function send_notification(){
    $this->CheckParams(array('message'));
    $_REQUEST = array_map('urldecode', $_REQUEST);
    $setting = array();
    if(!empty($_REQUEST['expire'])){
      $setting['expire'] = $_REQUEST['expire'];
    }
    if(!empty($_REQUEST['ios_slide'])){
      $setting['ios_slide'] = stripslashes($_REQUEST['ios_slide']);
    }
    if(!empty($_REQUEST['ios_badge'])){
      $setting['ios_badge'] = $_REQUEST['ios_badge'];
    }
    if(!empty($_REQUEST['ios_sound'])){
      $setting['ios_sound'] = $_REQUEST['ios_sound'];
    }
    if(!empty($_REQUEST['ios_cavailable'])){
      $setting['ios_cavailable'] = $_REQUEST['ios_cavailable'];
    }
    if(!empty($_REQUEST['ios_launchimg'])){
      $setting['ios_launchimg'] = stripslashes($_REQUEST['ios_launchimg']);
    }
    if(!empty($_REQUEST['customparams'])){
      $setting['extra_type'] = 'json';
      $setting['extravalue'] = stripslashes($_REQUEST['customparams']);
    }
    if(!empty($_REQUEST['android_customparams'])){
      $setting['and_extra_type'] = 'json';
      $setting['and_extravalue'] = stripslashes($_REQUEST['android_customparams']);
    }
    if(!empty($_REQUEST['wp_customparams'])){
      $setting['wp_extra_type'] = 'json';
      $setting['wp_extravalue'] = stripslashes($_REQUEST['wp_customparams']);
    }
    if(!empty($_REQUEST['bb_customparams'])){
      $setting['bb_extra_type'] = 'json';
      $setting['bb_extravalue'] = stripslashes($_REQUEST['bb_customparams']);
    }
    if(!empty($_REQUEST['desktop_link'])){
      $setting['desktop_link'] = stripslashes($_REQUEST['desktop_link']);
    }
    if(!empty($_REQUEST['desktop_title'])){
      $setting['desktop_title'] = stripslashes($_REQUEST['desktop_title']);
    }
    if(!empty($_REQUEST['desktop_icon'])){
      $setting['desktop_icon'] = stripslashes($_REQUEST['desktop_icon']);
    }
    if(!empty($_REQUEST['sendtime'])){
      $sendtime = strtotime(stripslashes($_REQUEST['sendtime']), current_time('timestamp'));
    }
    else{
      $sendtime = 0;
    }
    if(!empty($_REQUEST['latidude']) AND ! empty($_REQUEST['longitude']) AND ! empty($_REQUEST['radius'])) {
      $gps_loc_filter = array();
      $gps_loc_filter['latidude'] = $_REQUEST['latidude'];
      $gps_loc_filter['longitude'] = $_REQUEST['longitude'];
      $gps_loc_filter['radius'] = $_REQUEST['radius'];
      if(!empty($_REQUEST['gps_expire'])){
        $gps_loc_filter['gps_expire'] = $_REQUEST['gps_expire'];
      }
    }
    else{
      $gps_loc_filter = false;
    }
    
    if(!empty($_REQUEST['device_token'])){
      $this->CheckParams(array('device_token','device_type'));
      $tokenid = self::$pushdb->get_var(self::parse_query("SELECT {id_name} FROM {tbname} WHERE {md5token_name}='".md5($_REQUEST['device_token'])."' AND {type_name}='$_REQUEST[device_type]'"));
      smpush_sendpush::SendCronPush(array($tokenid), $_REQUEST['message'], '', 'tokenid', $setting, $sendtime, false, $gps_loc_filter);
      $this->output(1, __('Message sent successfully', 'smpush-plugin-lang'));
    }
    elseif(!empty($_REQUEST['user_id'])){
      $tokeninfo = self::$pushdb->get_row(self::parse_query("SELECT {token_name} AS device_token,{type_name} AS device_type FROM {tbname} WHERE userid='$_REQUEST[user_id]' AND {active_name}='1'"));
      if($tokeninfo){
        smpush_sendpush::SendCronPush($_REQUEST['user_id'], $_REQUEST['message'], '', 'userid', $setting, $sendtime, false, $gps_loc_filter);
        $this->output(1, __('Message sent successfully', 'smpush-plugin-lang'));
      }
      else{
        $this->output(0, __('Did not find data about this user or the user is inactive', 'smpush-plugin-lang'));
      }
    }
    elseif(!empty($_REQUEST['channel'])){
      if($_REQUEST['channel'] == 'all'){
        smpush_sendpush::SendCronPush('all', $_REQUEST['message'], '', '', $setting, $sendtime, false, $gps_loc_filter);
      }
      else{
        smpush_sendpush::SendCronPush($_REQUEST['channel'], $_REQUEST['message'], '', 'channel', $setting, $sendtime, false, $gps_loc_filter);
      }
      $this->output(1, __('Message sent successfully', 'smpush-plugin-lang'));
    }
    elseif(!empty($_REQUEST['latidude'])) {
      $this->CheckParams(array('longitude','radius'));
      smpush_sendpush::SendCronPush('all', $_REQUEST['message'], '', '', $setting, $sendtime, false, $gps_loc_filter);
      $this->output(1, __('Message sent successfully', 'smpush-plugin-lang'));
    }
    $this->output(1, __('Wrong parameters', 'smpush-plugin-lang'));
  }

  public function savetoken($printout=true){
    $this->CheckParams(array('device_token','device_type'));
    if(empty($_REQUEST['device_info'])){
      $_REQUEST['device_info'] = '';
    }
    if(!isset($_REQUEST['active'])){
      $_REQUEST['active'] = 1;
    }
    if(!empty($_REQUEST['latitude'])){
      $_REQUEST['latidude'] = $_REQUEST['latitude'];
    }
    if(empty($_REQUEST['latidude']) OR empty($_REQUEST['longitude'])){
      $_REQUEST['latidude'] = '0';
      $_REQUEST['longitude'] = '0';
      $locationinfo = smpush_geoloc::get_location_info();
      if($locationinfo !== false){
        $_REQUEST['latidude'] = $locationinfo['latitude'];
        $_REQUEST['longitude'] = $locationinfo['longitude'];
      }
    }
    global $wpdb;
    $device_type = $_REQUEST['device_type'];
    $types_name = $wpdb->get_row("SELECT ios_name,android_name,wp_name,bb_name,chrome_name,safari_name,firefox_name,wp10_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'", ARRAY_A);
    $types_name = array_flip($types_name);
    if(!isset($types_name[$device_type])){
      $supported_types = implode(' , ', array_flip($types_name));
      $this->output(0, __('Wrong device type value. System supports the following device types', 'smpush-plugin-lang').' '.$supported_types);
    }
    $tokenid = self::$pushdb->get_var(self::parse_query("SELECT {id_name} FROM {tbname} WHERE {md5token_name}='".md5($_REQUEST['device_token'])."' AND {type_name}='$_REQUEST[device_type]'"));
    if($tokenid > 0){
      self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='$_REQUEST[active]',{info_name}='$_REQUEST[device_info]',{latidude_name}='$_REQUEST[latidude]',{longitude_name}='$_REQUEST[longitude]',{gpstime_name}='".time()."' WHERE {id_name}='$tokenid'"));
      if(!empty($_REQUEST['user_id'])){
        self::$pushdb->query(self::parse_query("UPDATE {tbname} SET userid='$_REQUEST[user_id]' WHERE {id_name}='$tokenid'"));
        if(isset($_REQUEST['channels_id'])){
          update_user_meta($_REQUEST['user_id'], 'smpush_subscribed_channels', $_REQUEST['channels_id']);
        }
      }
      if(!$printout) return $tokenid;
      return $this->output(1, __('Token saved successfully', 'smpush-plugin-lang'));
    }
    self::$pushdb->query(self::parse_query("INSERT INTO {tbname} ({token_name},{md5token_name},{type_name},{info_name},{active_name},{latidude_name},{longitude_name},{gpstime_name}) VALUES ('$_REQUEST[device_token]','".md5($_REQUEST['device_token'])."','$device_type','$_REQUEST[device_info]','$_REQUEST[active]','$_REQUEST[latidude]','$_REQUEST[longitude]','".time()."')"));
    $tokenid = self::$pushdb->insert_id;
    if($tokenid === false){
      return $this->output(0, __('Push database connection error', 'smpush-plugin-lang'));
    }
    if(!empty($_REQUEST['user_id'])){
      self::$pushdb->query(self::parse_query("UPDATE {tbname} SET userid='$_REQUEST[user_id]' WHERE {id_name}='$tokenid'"));
      if(isset($_REQUEST['channels_id'])){
        update_user_meta($_REQUEST['user_id'], 'smpush_subscribed_channels', $_REQUEST['channels_id']);
      }
    }
    $defconid = self::$apisetting['def_connection'];
    self::$pushdb->query(self::parse_query("UPDATE ".SMPUSHTBPRE."push_connection SET counter=counter+1 WHERE id='$defconid'"));
    if(isset($_REQUEST['channels_id']) && !empty($_REQUEST['user_id'])){
      self::updateUserChannels($_REQUEST['user_id'], $_REQUEST['channels_id']);
    }
    elseif(!empty($_REQUEST['channels_id'])){
      $chids = explode(',', $_REQUEST['channels_id']);
      foreach($chids AS $chid){
        $wpdb->query("INSERT INTO ".SMPUSHTBPRE."push_relation (channel_id,token_id,connection_id) VALUES ('$chid','$tokenid','$defconid')");
      }
      $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`+1 WHERE id IN($_REQUEST[channels_id])");
    }
    else{
      $defchid = $wpdb->get_var("SELECT id FROM ".SMPUSHTBPRE."push_channels WHERE `default`='1'");
      $wpdb->query("INSERT INTO ".SMPUSHTBPRE."push_relation (channel_id,token_id,connection_id) VALUES ('$defchid','$tokenid','$defconid')");
      $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`+1 WHERE id='$defchid'");
    }
    if(!$printout) return $tokenid;
    return $this->output(1, __('Token saved successfully', 'smpush-plugin-lang'));
  }
  
  public function deletetoken(){
    if(!empty($_REQUEST['user_id'])){
      $tokens = self::$pushdb->get_results(self::parse_query("SELECT {id_name} AS tokenid FROM {tbname} WHERE userid='$_REQUEST[user_id]'"));
      if($tokens){
        foreach($tokens as $token){
          self::$pushdb->query(self::parse_query("DELETE FROM {tbname} WHERE {id_name}='$token->tokenid'"));
          self::$pushdb->query("DELETE FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$token->tokenid'");
        }
      }
      return $this->output(1, __('Token subscription deleted successfully', 'smpush-plugin-lang'));
    }
    else{
      $this->CheckParams(array('device_token','device_type'));
      $tokenid = self::$pushdb->get_var(self::parse_query("SELECT {id_name} FROM {tbname} WHERE {md5token_name}='".md5($_REQUEST['device_token'])."' AND {type_name}='$_REQUEST[device_type]'"));
      if(!empty($tokenid)){
        self::$pushdb->query(self::parse_query("DELETE FROM {tbname} WHERE {id_name}='$tokenid'"));
        self::$pushdb->query("DELETE FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$tokenid'");
      }
      return $this->output(1, __('Token subscription deleted successfully', 'smpush-plugin-lang'));
    }
  }

  public function channels_subscribe(){
    if(!empty($_REQUEST['user_id']) && (empty($_REQUEST['device_token']) || empty($_REQUEST['device_type']))){
      $tokenid = self::$pushdb->get_var(self::parse_query("SELECT {id_name} AS tokenid FROM {tbname} WHERE userid='$_REQUEST[user_id]' ORDER BY {id_name} ASC LIMIT 0,1"));
    }
    else{
      $tokenid = $this->savetoken(false);
    }
    if(isset($_REQUEST['channels_id']) && !empty($_REQUEST['user_id'])){
      self::updateUserChannels($_REQUEST['user_id'], $_REQUEST['channels_id']);
    }
    elseif(isset($_REQUEST['channels_id'])){
      self::editSubscribedChannels($tokenid, $_REQUEST['channels_id']);
    }
    return $this->output(1, __('Subscription saved successfully', 'smpush-plugin-lang'));
  }

  public static function updateUserChannels($userid, $newchannels){
    global $wpdb;
    $tokens = $wpdb->get_results(self::parse_query("SELECT {id_name} AS tokenid FROM {tbname} WHERE userid='$userid'"));
    if($tokens){
      foreach($tokens AS $token){
        self::editSubscribedChannels($token->tokenid, $newchannels);
      }
    }
  }
  
  public static function editSubscribedChannels($tokenid, $newchannels){
    global $wpdb;
    $defconid = self::$apisetting['def_connection'];
    $subschans = $wpdb->get_results("SELECT channel_id FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$tokenid' AND connection_id='$defconid'");
    if($subschans){
      foreach($subschans AS $subschan){
        $chids[] = $subschan->channel_id;
      }
      $chids = implode(',', $chids);
      $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`-1 WHERE id IN($chids)");
    }
    $wpdb->query("DELETE FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$tokenid' AND connection_id='$defconid'");
    if(!empty($newchannels)){
      $chids = explode(',', $newchannels);
      foreach($chids AS $chid){
        $wpdb->query("INSERT INTO ".SMPUSHTBPRE."push_relation (channel_id,token_id,connection_id) VALUES ('$chid','$tokenid','$defconid')");
      }
      $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`+1 WHERE id IN($newchannels)");
    }
  }

  public function safari(){
    if(strpos($this->carry, '/devices/') !== false){
      preg_match('/devices\/([a-zA-Z0-9]+)\/registrations/', $this->carry, $matches);
      $deviceToken = $matches[1];
      if(empty($deviceToken)){
        die();
      }
      $_REQUEST['device_token'] = $deviceToken;
      $_REQUEST['device_type'] = 'safari';
      if($_SERVER['REQUEST_METHOD'] == "POST"){
        $_REQUEST['active'] = '1';
        $this->savetoken();
      }
      elseif($_SERVER['REQUEST_METHOD'] == "DELETE"){
        $_REQUEST['active'] = '0';
        $this->savetoken();
      }
    }
    elseif(strpos($this->carry, '/pushPackages/') !== false){
      if(empty(self::$apisetting['safari_pack_path']) || !file_exists(self::$apisetting['safari_pack_path'])){
        $packpath = $this->buildSafariPackFile(self::$apisetting);
        self::$apisetting['safari_pack_path'] = $packpath;
        self::$apisetting = array_map('addslashes', self::$apisetting);
        update_option('smpush_options', self::$apisetting);
      }
      else{
        $packpath = self::$apisetting['safari_pack_path'];
      }
      header('Content-type: application/zip');
      header('Content-Disposition: attachment; filename="package.zip"');
      header('Pragma: public');
      header('Expires: 0');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Cache-Control: public');
      header('Content-Type: text/plain; charset=utf-8');
      header('Content-Transfer-Encoding: binary');
      ob_end_flush();
      if(function_exists('file_get_contents')){
        echo file_get_contents($packpath);
      }
      elseif(function_exists('fopen')){
        $handle = fopen($packpath, 'r');
        $content = fread($handle, filesize($packpath));
        fclose($handle);
        echo $content;
      }
      die;
    }
    elseif(strpos($this->carry, '/log') !== false){
      $body = file_get_contents('php://input');
      $body = json_decode($body, true);
      if(!empty($body['logs'])){
        global $wpdb;
        foreach($body['logs'] as $error => $log){
          $wpdb->insert($wpdb->prefix.'push_archive', array('send_type' => 'feedback', 'message' => $log, 'starttime' => date('Y-m-d H:i:s', current_time('timestamp'))));
        }
      }
    }
    $this->output(1, __('Success', 'smpush-plugin-lang'));
  }
  
  public function get_archive(){
    global $wpdb;
    $order = 'DESC';
    $where = '';
    $push_archiveTB = $wpdb->prefix.'push_archive';
    if(!empty($_REQUEST['order'])){
      if(strtolower($_REQUEST['order']) == 'asc') $order = 'ASC';
      else $order = 'DESC';
    }
    if(!empty($_REQUEST['platform'])){
      if($_REQUEST['platform'] == 'chrome'){
        $where = "AND $push_archiveTB.desktop LIKE '%chrome%'";
      }
      elseif($_REQUEST['platform'] == 'firefox'){
        $where = "AND $push_archiveTB.desktop LIKE '%firefox%'";
      }
      elseif($_REQUEST['platform'] == 'safari'){
        $where = "AND $push_archiveTB.desktop LIKE '%safari%'";
      }
      else{
        die();
      }
    }
    if(!empty($_REQUEST['deviceID'])){
      $sql = "SELECT $push_archiveTB.id,$push_archiveTB.message,$push_archiveTB.starttime,$push_archiveTB.options FROM ".$wpdb->prefix."push_desktop_messages
      INNER JOIN $push_archiveTB ON($push_archiveTB.id=".$wpdb->prefix."push_desktop_messages.msgid)
      WHERE ".$wpdb->prefix."push_desktop_messages.token='".md5($_REQUEST['deviceID'])."' AND ".$wpdb->prefix."push_desktop_messages.type='$_REQUEST[platform]' $where ORDER BY ".$wpdb->prefix."push_desktop_messages.timepost ASC LIMIT 0,6";
      $gets = $wpdb->get_results($sql, 'ARRAY_A');
      if(!$gets) return $this->output(1, array());
      if($gets){
        $wpdb->query("DELETE FROM ".$wpdb->prefix."push_desktop_messages WHERE token='".md5($_REQUEST['deviceID'])."' AND type='$_REQUEST[platform]'");
      }
    }
    else{
      $sql = "SELECT id,message,starttime,options FROM ".$wpdb->prefix."push_archive WHERE send_type IN('sendnow','cronsend') $where ORDER BY id ".$order;
      $sql = $this->Paging($sql, $wpdb);
      $gets = $wpdb->get_results($sql, 'ARRAY_A');
      if(!$gets) return $this->output(0, __('No result found', 'smpush-plugin-lang'));
    }
    $messages = array();
    foreach ($gets as $get){
      $message = array();
      $message['id'] = $get['id'];
      $message['message'] = html_entity_decode(preg_replace("/U\+([0-9A-F]{4,5})/", "&#x\\1;", self::cleanString($get['message'])), ENT_NOQUOTES, 'UTF-8');
      $message['starttime'] = $get['starttime'];
      $options = unserialize($get['options']);
      $message['title'] = (!empty($options['desktop_title']))? html_entity_decode(preg_replace("/U\+([0-9A-F]{4,5})/", "&#x\\1;", self::cleanString($options['desktop_title'])), ENT_NOQUOTES, 'UTF-8') : '';
      $message['link'] = (!empty($options['desktop_link']))? self::cleanString($options['desktop_link']) : '';
      $message['icon'] = (!empty($options['desktop_icon']))? self::cleanString($options['desktop_icon']) : '';
      $messages[] = $message;
    }
    $this->output(1, $messages);
  }
  
  public function device_channels(){
    global $wpdb;
    if(!empty($_REQUEST['user_id']) && (empty($_REQUEST['device_token']) || empty($_REQUEST['device_type']))){
      $tokenid = self::$pushdb->get_var(self::parse_query("SELECT {id_name} AS tokenid FROM {tbname} WHERE userid='$_REQUEST[user_id]' ORDER BY {id_name} ASC LIMIT 0,1"));
    }
    else{
      $tokenid = $this->savetoken(false);
    }
    $defconid = self::$apisetting['def_connection'];
    $subschans = $wpdb->get_results("SELECT channel_id FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$tokenid' AND connection_id='$defconid'");
    if($subschans){
      foreach($subschans AS $subschan){
        $chids[] = $subschan->channel_id;
      }
    }
    else $chids = array();
    $this->get_channels($chids);
  }

  public function get_channels($chids=false){
    global $wpdb;
    if($_REQUEST['orderby'] == 'subscribers')
        $orderby = 'push_channels.`count`';
    elseif($_REQUEST['orderby'] == 'name')
        $orderby = 'push_channels.title';
    elseif($_REQUEST['orderby'] == 'date')
        $orderby = 'push_channels.id';
    else
        $orderby = 'push_channels.id';
    $arg = array(
    'where' => array('push_channels.private'=>0),
    'orderby' => $orderby,
    'order' => ($this->queryorder) ? $this->queryorder:'ASC'
    );
    $sql = "SELECT * FROM ".$wpdb->prefix."push_channels {where} {order}";
    $sql = $this->queryBuild($sql, $arg);
    $channels = $wpdb->get_results($sql, 'ARRAY_A');
    if($channels){
      if($chids !== false){
        foreach($channels AS $channel){
          if(in_array($channel['id'], $chids))
            $channel['subscribed'] = 'yes';
          else
            $channel['subscribed'] = 'no';
          $get[] = $channel;
        }
        return $this->output(1, $get);
      }
      return $this->output(1, $channels);
    }
    else{
      return $this->output(0, __('No result found', 'smpush-plugin-lang'));
    }
  }
  
  public function add_channel(){
    $this->CheckParams(array('title','private'));
    global $wpdb;
    if(!empty($_REQUEST['unique'])){
      $bool = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."push_channels WHERE title='$_REQUEST[title]'");
      if($bool){
        $this->output(0, __('This channel name is taken', 'smpush-plugin-lang'));
      }
    }
    $data = array();
    $data['title'] = $_REQUEST['title'];
    $data['description'] = (!empty($_REQUEST['description']))? $_REQUEST['description'] : '';
    $data['private'] = (!empty($_REQUEST['private']))? 1 : 0;
    $data['default'] = 0;
    $data['count'] = 0;
    $wpdb->insert($wpdb->prefix.'push_channels', $data);
    $this->output($wpdb->insert_id, __('Channel added successfully', 'smpush-plugin-lang'));
  }
  
  public function update_channel(){
    $this->CheckParams(array('id','title','private'));
    global $wpdb;
    if(!empty($_REQUEST['unique'])){
      $bool = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."push_channels WHERE title='$_REQUEST[title]' AND id!='$_REQUEST[id]'");
      if($bool){
        $this->output(0, __('This channel name is taken', 'smpush-plugin-lang'));
      }
    }
    $data = array();
    $data['title'] = $_REQUEST['title'];
    $data['description'] = (!empty($_REQUEST['description']))? $_REQUEST['description'] : '';
    $data['private'] = (!empty($_REQUEST['private']))? 1 : 0;
    $wpdb->update($wpdb->prefix.'push_channels', $data, array('id' => $_REQUEST['id']));
    $this->output(1, __('Channel updated successfully', 'smpush-plugin-lang'));
  }
  
  public function delete_channel(){
    $this->CheckParams(array('id'));
    global $wpdb;
    $wpdb->delete($wpdb->prefix.'push_channels', array('id' => $_REQUEST['id']));
    $wpdb->delete($wpdb->prefix.'push_relation', array('channel_id' => $_REQUEST['id'], 'connection_id' => self::$apisetting['def_connection']));
    $this->output(1, __('Channel deleted successfully', 'smpush-plugin-lang'));
  }

  public function debug(){
    $this->output(1, __('Push notification system is active now and work under version', 'smpush-plugin-lang').' '.get_option('smpush_version'));
  }

  public static function delete_relw_app($user_id){
    global $wpdb;
    $wpdb->delete(SMPUSHTBPRE.'push_tokens', array('userid' => $user_id));
  }

}