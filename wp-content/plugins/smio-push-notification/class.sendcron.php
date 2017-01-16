<?php

class smpush_cronsend extends smpush_controller {
  private static $startTime;
  private static $totalSent;
  private static $iosCounter;
  private static $andCounter;
  private static $wpCounter;
  private static $wp10Counter;
  private static $bbCounter;
  private static $chCounter;
  private static $saCounter;
  private static $fiCounter;
  private static $iosDelIDS;
  private static $andDelIDS;
  private static $wpDelIDS;
  private static $wp10DelIDS;
  private static $bbDelIDS;
  private static $chDelIDS;
  private static $saDelIDS;
  private static $fiDelIDS;
  private static $iosDevices;
  private static $andDevices;
  private static $wpDevices;
  private static $wp10Devices;
  private static $bbDevices;
  private static $chDevices;
  private static $saDevices;
  private static $fiDevices;
  private static $tempunique;
  private static $sendoptions;

  public function __construct() {
    parent::__construct();
  }

  public static function destruct() {
    global $wpdb;
    $wpdb->get_var("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE sendoptions='".self::$tempunique."'");
    $wpdb->update($wpdb->prefix.'push_archive', array('endtime' => date('Y-m-d H:i:s')), array('id' => self::$tempunique));
    smpush_sendpush::connectFeedback(0, true);
    delete_transient('smpush_cron_stats');
  }
  
  public static function finishQueue() {
    if(self::$totalSent > 0){
      global $wpdb;
      smpush_sendpush::updateStats('totalsend', self::$totalSent, true);
      $report = smpush_sendpush::updateStats('all', 0, true);
      $wpdb->update($wpdb->prefix.'push_archive', array('endtime' => date('Y-m-d H:i:s')), array('id' => self::$tempunique));
      $wpdb->insert($wpdb->prefix.'push_archive_reports', array('msgid' => self::$tempunique, 'report_time' => current_time('timestamp'), 'report' => serialize($report)));
      smpush_sendpush::updateStats('', 0, true);
      self::$totalSent = 0;
    }
  }

  public static function writeLog($log) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'push_archive', array('send_type' => 'feedback', 'message' => $log, 'starttime' => self::$startTime, 'endtime' => date('Y-m-d H:i:s')));
  }

  public static function resetIOS() {
    self::$iosDevices = array();
    self::$iosDelIDS = array();
    self::$iosCounter = 0;
  }

  public static function resetAND() {
    self::$andDevices = array();
    self::$andDelIDS = array();
    self::$andCounter = 0;
  }
  
  public static function resetWP() {
    self::$wpDevices = array();
    self::$wpDelIDS = array();
    self::$wpCounter = 0;
  }
  
  public static function resetWP10() {
    self::$wp10Devices = array();
    self::$wp10DelIDS = array();
    self::$wp10Counter = 0;
  }
  
  public static function resetBB() {
    self::$bbDevices = array();
    self::$bbDelIDS = array();
    self::$bbCounter = 0;
  }
  
  public static function resetCH() {
    self::$chDevices = array();
    self::$chDelIDS = array();
    self::$chCounter = 0;
  }
  
  public static function resetSA() {
    self::$saDevices = array();
    self::$saDelIDS = array();
    self::$saCounter = 0;
  }
  
  public static function resetFI() {
    self::$fiDevices = array();
    self::$fiDelIDS = array();
    self::$fiCounter = 0;
  }

  public static function runEventQueue() {
    global $wpdb;
    $events = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_events_queue ORDER BY id DESC");
    if($events){
      $eventManager = new smpush_events();
      foreach($events as $event){
        $wpdb->query("DELETE FROM ".$wpdb->prefix."push_events_queue WHERE id='$event->id'");
        $eventManager::post_status_change($event->new_status, $event->old_status, $event->post_id, unserialize($event->post));
      }
    }
  }
  
  public static function cronStart() {
    @set_time_limit(0);
    global $wpdb;
    self::runEventQueue();
    register_shutdown_function(array('smpush_cronsend', 'destruct'));
    self::$startTime = date('Y-m-d H:i:s');
    self::$totalSent = 0;
    self::$tempunique = '';
    self::resetIOS();
    self::resetAND();
    self::resetWP();
    self::resetWP10();
    self::resetBB();
    self::resetCH();
    self::resetSA();
    self::resetFI();
    $TIMENOW = time();
    if(!session_id()) {
      session_start();
    }
    smpush_sendpush::updateStats('', 0, true);
    $types_name = $wpdb->get_row("SELECT ios_name,android_name,wp_name,bb_name,chrome_name,safari_name,firefox_name,wp10_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
    $queue = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_cron_queue WHERE $TIMENOW>sendtime ORDER BY sendoptions ASC");
    if($queue) {
      foreach($queue AS $queueone) {
        if(empty(self::$tempunique)){
          self::$tempunique = $queueone->sendoptions;
        }
        if(self::$tempunique != $queueone->sendoptions){
          if(self::$iosCounter > 0)
            self::sendPushCron('ios');
          if(self::$andCounter > 0)
            self::sendPushCron('android');
          if(self::$wpCounter > 0)
            self::sendPushCron('wp');
          if(self::$wp10Counter > 0)
            self::sendPushCron('wp10');
          if(self::$bbCounter > 0)
            self::sendPushCron('bb');
          if(self::$chCounter > 0)
            self::sendPushCron('chrome');
          if(self::$saCounter > 0)
            self::sendPushCron('safari');
          if(self::$fiCounter > 0)
            self::sendPushCron('firefox');
          self::finishQueue();
          self::$tempunique = $queueone->sendoptions;
        }
        if(self::$iosCounter >= 1000){
          self::sendPushCron('ios');
        }
        if(self::$andCounter >= 1000){
          self::sendPushCron('android');
        }
        if(self::$wpCounter >= 1000){
          self::sendPushCron('wp');
        }
        if(self::$wp10Counter >= 1000){
          self::sendPushCron('wp10');
        }
        if(self::$bbCounter >= 1000){
          self::sendPushCron('bb');
        }
        if(self::$chCounter >= 1000){
          self::sendPushCron('chrome');
        }
        if(self::$saCounter >= 1000){
          self::sendPushCron('safari');
        }
        if(self::$fiCounter >= 1000){
          self::sendPushCron('firefox');
        }
        if($queueone->device_type == $types_name->ios_name) {
          self::$iosDelIDS[] = $queueone->id;
          self::$iosDevices[self::$iosCounter]['token'] = $queueone->token;
          self::$iosDevices[self::$iosCounter]['id'] = $queueone->id;
          self::$iosCounter++;
        }
        elseif($queueone->device_type == $types_name->android_name) {
          self::$andDelIDS[] = $queueone->id;
          self::$andDevices['token'][self::$andCounter] = $queueone->token;
          self::$andDevices['id'][self::$andCounter] = $queueone->id;
          self::$andCounter++;
        }
        elseif($queueone->device_type == $types_name->wp_name) {
          self::$wpDelIDS[] = $queueone->id;
          self::$wpDevices['token'][self::$wpCounter] = $queueone->token;
          self::$wpDevices['id'][self::$wpCounter] = $queueone->id;
          self::$wpCounter++;
        }
        elseif($queueone->device_type == $types_name->wp10_name) {
          self::$wp10DelIDS[] = $queueone->id;
          self::$wp10Devices['token'][self::$wp10Counter] = $queueone->token;
          self::$wp10Devices['id'][self::$wp10Counter] = $queueone->id;
          self::$wp10Counter++;
        }
        elseif($queueone->device_type == $types_name->bb_name) {
          self::$bbDelIDS[] = $queueone->id;
          self::$bbDevices['token'][self::$bbCounter] = $queueone->token;
          self::$bbDevices['id'][self::$bbCounter] = $queueone->id;
          self::$bbCounter++;
        }
        elseif($queueone->device_type == $types_name->chrome_name) {
          self::$chDelIDS[] = $queueone->id;
          self::$chDevices['token'][self::$chCounter] = $queueone->token;
          self::$chDevices['id'][self::$chCounter] = $queueone->id;
          self::$chCounter++;
        }
        elseif($queueone->device_type == $types_name->safari_name) {
          self::$saDelIDS[] = $queueone->id;
          self::$saDevices['token'][self::$saCounter] = $queueone->token;
          self::$saDevices['id'][self::$saCounter] = $queueone->id;
          self::$saCounter++;
        }
        elseif($queueone->device_type == $types_name->firefox_name) {
          self::$fiDelIDS[] = $queueone->id;
          self::$fiDevices['token'][self::$fiCounter] = $queueone->token;
          self::$fiDevices['id'][self::$fiCounter] = $queueone->id;
          self::$fiCounter++;
        }
        else{
          continue;
        }
        self::$totalSent++;
      }
      if(self::$iosCounter > 0){
        self::sendPushCron('ios');
      }
      if(self::$andCounter > 0){
        self::sendPushCron('android');
      }
      if(self::$wpCounter > 0){
        self::sendPushCron('wp');
      }
      if(self::$wp10Counter > 0){
        self::sendPushCron('wp10');
      }
      if(self::$bbCounter > 0){
        self::sendPushCron('bb');
      }
      if(self::$chCounter > 0){
        self::sendPushCron('chrome');
      }
      if(self::$saCounter > 0){
        self::sendPushCron('safari');
      }
      if(self::$fiCounter > 0){
        self::sendPushCron('firefox');
      }
    }
    self::finishQueue();
    die();
  }

  public static function sendPushCron($type) {
    global $wpdb;
    self::$sendoptions = unserialize($wpdb->get_var("SELECT options FROM ".$wpdb->prefix."push_archive WHERE id='".self::$tempunique."'"));
    if(empty(self::$sendoptions)){
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE sendoptions='".self::$tempunique."'");
      self::writeLog(__('System did not find the related data for message', 'smpush-plugin-lang').' #'.self::$tempunique.' : '.__('operation cancelled', 'smpush-plugin-lang'));
      die();
    }
    self::$sendoptions['msgid'] = self::$tempunique;
    if($type == 'ios'){
      $DelIDS = implode(',', self::$iosDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$iosDevices, 'ios', self::$sendoptions, true, 0, true);
      self::resetIOS();
    }
    elseif($type == 'android'){
      $DelIDS = implode(',', self::$andDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$andDevices, 'android', self::$sendoptions, true, 0, true);
      self::resetAND();
    }
    elseif($type == 'wp'){
      $DelIDS = implode(',', self::$wpDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$wpDevices, 'wp', self::$sendoptions, true, 0, true);
      self::resetWP();
    }
    elseif($type == 'wp10'){
      $DelIDS = implode(',', self::$wp10DelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$wp10Devices, 'wp10', self::$sendoptions, true, 0, true);
      self::resetWP10();
    }
    elseif($type == 'bb'){
      $DelIDS = implode(',', self::$bbDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$bbDevices, 'bb', self::$sendoptions, true, 0, true);
      self::resetBB();
    }
    elseif($type == 'chrome'){
      $DelIDS = implode(',', self::$chDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$chDevices, 'chrome', self::$sendoptions, true, 0, true);
      self::resetCH();
    }
    elseif($type == 'safari'){
      $DelIDS = implode(',', self::$saDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$saDevices, 'safari', self::$sendoptions, true, 0, true);
      self::resetSA();
    }
    elseif($type == 'firefox'){
      $DelIDS = implode(',', self::$fiDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$fiDevices, 'firefox', self::$sendoptions, true, 0, true);
      self::resetFI();
    }
  }

}