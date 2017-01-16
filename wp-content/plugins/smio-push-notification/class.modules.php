<?php

class smpush_modules extends smpush_controller {

  public static $wpdateformat;

  public function __construct() {
    parent::__construct();
  }
  
  public static function widget() {
    register_widget('smpush_widget');
  }
  
  public static function archive() {
    global $wpdb;
    self::load_jsplugins();
    $pageurl = admin_url().'admin.php?page=smpush_archive';
    $pagname = 'smpush_archive';
    if (isset($_GET['delete'])) {
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_archive WHERE id='$_GET[id]'");
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_archive_reports WHERE msgid='$_GET[id]'");
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE sendoptions='$_GET[id]'");
      exit;
    }
    if (isset($_GET['empty'])) {
      $wpdb->query("TRUNCATE ".$wpdb->prefix."push_archive");
      $wpdb->query("TRUNCATE ".$wpdb->prefix."push_archive_reports");
      $wpdb->query("TRUNCATE ".$wpdb->prefix."push_cron_queue");
      wp_redirect($pageurl);
    }
    elseif (!empty($_GET['apply'])) {
      if (!empty($_GET['doaction'])) {
        $doaction = $_GET['doaction'];
      } elseif (!empty($_GET['doaction2'])) {
        $doaction = $_GET['doaction2'];
      }
      $ids = implode(',', $_GET['archive']);
      if ($doaction == 'delete') {
        $wpdb->query("DELETE FROM ".$wpdb->prefix."push_archive WHERE id IN($ids)");
        $wpdb->query("DELETE FROM ".$wpdb->prefix."push_archive_reports WHERE msgid IN($ids)");
        parent::update_counters();
      }
      wp_redirect($pageurl);
    }
    elseif (isset($_GET['action']) && $_GET['action'] == 'reports') {
      self::$wpdateformat = get_option('date_format').' '.get_option('time_format');
      $sql = self::Paging("SELECT * FROM ".$wpdb->prefix."push_archive_reports WHERE msgid='$_GET[msgid]' ORDER BY report_time DESC", $wpdb);
      $reports = $wpdb->get_results($sql);
      $paging_args = array(
      'base' => preg_replace('/&?callpage=([0-9]+)/', '', $_SERVER['REQUEST_URI']).'%_%',
      'format' => '&callpage=%#%',
      'total' => self::$paging['pages'],
      'current' => self::$paging['page'],
      'show_all' => false,
      'end_size' => 3,
      'mid_size' => 2,
      'prev_next' => true,
      'prev_text' => __('« Previous'),
      'next_text' => __('Next »')
      );
      include(smpush_dir.'/pages/archive_reports.php');
    }
    else {
      self::$wpdateformat = get_option('date_format').' '.get_option('time_format');
      $where = array();
      $order = 'ORDER BY id DESC';
      if (!empty($_GET['query'])) {
        $where[] = "message LIKE '%$_GET[query]%'";
        $order = '';
      }
      if (count($where) > 0) {
        $where = 'WHERE '.implode(' AND ', $where);
      } else {
        $where = '';
      }
      $sql = self::Paging("SELECT * FROM ".$wpdb->prefix."push_archive $where $order", $wpdb);
      $archives = $wpdb->get_results($sql);
      $paging_args = array(
      'base' => preg_replace('/&?callpage=([0-9]+)/', '', $_SERVER['REQUEST_URI']).'%_%',
      'format' => '&callpage=%#%',
      'total' => self::$paging['pages'],
      'current' => self::$paging['page'],
      'show_all' => false,
      'end_size' => 3,
      'mid_size' => 2,
      'prev_next' => true,
      'prev_text' => __('« Previous'),
      'next_text' => __('Next »')
      );
      include(smpush_dir.'/pages/archive_manage.php');
    }
  }

  public static function tokens() {
    global $wpdb;
    self::load_jsplugins();
    $pageurl = admin_url().'admin.php?page=smpush_tokens';
    $pagname = 'smpush_tokens';
    if (!empty($_POST['device_type'])) {
      if (empty($_POST['device_token'])) {
        self::jsonPrint(0, __('Field `Device Token` is required.', 'smpush-plugin-lang'));
      }
      if ($_POST['id'] > 0) {
        self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {id_name}='$_POST[id]',{token_name}='$_POST[device_token]',{md5token_name}='".md5($_REQUEST['device_token'])."',{type_name}='$_POST[device_type]',{info_name}='$_POST[information]',{active_name}='$_POST[active]',{latidude_name}='$_POST[latidude]',{longitude_name}='$_POST[longitude]',{gpstime_name}='".time()."' WHERE {id_name}='$_POST[id]'"));
        $tokenid = $_POST['id'];
      } else {
        self::$pushdb->query(self::parse_query("INSERT INTO {tbname} ({token_name},{md5token_name},{type_name},{info_name},{active_name},{latidude_name},{longitude_name},{gpstime_name}) VALUES ('$_POST[device_token]','".md5($_REQUEST['device_token'])."','$_POST[device_type]','$_POST[information]','$_POST[active]','$_POST[latidude]','$_POST[longitude]','".time()."')"));
        $tokenid = self::$pushdb->insert_id;
        $wpdb->query("UPDATE ".$wpdb->prefix."push_connection SET counter=counter+1 WHERE id='".self::$apisetting['def_connection']."'");
      }
      if (!empty($_POST['channels'])) {
        $_POST['channels'] = implode(',', $_POST['channels']);
        smpush_api::editSubscribedChannels($tokenid, $_POST['channels']);
      }
      echo 1;
      exit;
    }
    elseif (isset($_GET['remove_duplicates'])) {
      self::$pushdb->query(self::parse_query("CREATE TABLE {tbname_temp} AS SELECT * FROM {tbname} GROUP BY {md5token_name}"));
      if (empty(self::$pushdb->last_error)) {
        self::$pushdb->query(self::parse_query("ALTER TABLE {tbname_temp} ADD PRIMARY KEY({id_name})"));
        self::$pushdb->query(self::parse_query("ALTER TABLE {tbname_temp} CHANGE {id_name} {id_name} INT(11) NOT NULL AUTO_INCREMENT"));
        self::$pushdb->query(self::parse_query("ALTER TABLE  {tbname_temp} ADD INDEX ({md5token_name})"));
        self::$pushdb->query(self::parse_query("DROP TABLE {tbname}"));
        self::$pushdb->query(self::parse_query("RENAME TABLE {tbname_temp} TO {tbname}"));
        parent::update_counters();
        wp_redirect($pageurl);
      }
      else {
        wp_die(__('An error has occurred, the system stopped and rolled back the changes.', 'smpush-plugin-lang'));
      }
    }
    elseif (isset($_GET['delete'])) {
      self::$pushdb->query(self::parse_query("DELETE FROM {tbname} WHERE {id_name}='$_GET[id]'"));
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_relation WHERE token_id='$_GET[id]' AND connection_id='".self::$apisetting['def_connection']."'");
      parent::update_counters();
      exit;
    }
    elseif (isset($_GET['id'])) {
      $channels = $wpdb->get_results("SELECT id,title FROM ".$wpdb->prefix."push_channels ORDER BY title ASC");
      $types_name = $wpdb->get_row("SELECT ios_name,android_name,wp_name,bb_name,chrome_name,safari_name,firefox_name,wp10_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
      if ($_GET['id'] == -1) {
        $token = array('id' => 0, 'device_token' => '', 'device_type' => '', 'information' => '', 'latidude' => '', 'longitude' => '', 'channels' => array(), 'active' => 1);
      }
      else {
        $subschannels = $wpdb->get_results("SELECT channel_id FROM ".$wpdb->prefix."push_relation WHERE token_id='$_GET[id]' AND connection_id='".self::$apisetting['def_connection']."'");
        $token = self::$pushdb->get_row(self::parse_query("SELECT {id_name} AS id,{token_name} AS device_token,{type_name} AS device_type,{info_name} AS information,{active_name} AS active,{latidude_name} AS latidude,{longitude_name} AS longitude FROM {tbname} WHERE {id_name}='$_GET[id]'"), 'ARRAY_A');
        $token = array_map('stripslashes', $token);
        $token['channels'] = array();
        if ($subschannels) {
          foreach ($subschannels as $subschannel) {
            $token['channels'][] = $subschannel->channel_id;
          }
        }
      }
      include(smpush_dir.'/pages/token_form.php');
      exit;
    }
    else {
      $types_name = $wpdb->get_row("SELECT dbtype,ios_name,android_name,wp_name,bb_name,chrome_name,safari_name,firefox_name,wp10_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
      $channels = $wpdb->get_results("SELECT id,title FROM ".$wpdb->prefix."push_channels ORDER BY title ASC");
      $where = array();
      $inner = '';
      $order = 'ORDER BY {tbname}.{id_name} DESC';
      if (!empty($_GET['query'])) {
        $where[] = "({tbname}.{token_name}='$_GET[query]' OR {tbname}.{info_name} LIKE '%$_GET[query]%')";
        $order = '';
      }
      if (!empty($_GET['device_type'])) {
        $where[] = "{tbname}.{type_name}='$_GET[device_type]'";
      }
      else {
        $_GET['device_type'] = '';
      }
      if (!empty($_GET['userid'])) {
        $where[] = "{tbname}.userid='$_GET[userid]'";
      }
      else {
        $_GET['userid'] = '';
      }
      if (!empty($_GET['status'])) {
        if ($_GET['status'] == 2)
          $status = 0;
        else
          $status = 1;
        $where[] = "{tbname}.{active_name}='$status'";
      }
      else {
        $_GET['status'] = '-1';
      }
      if (!empty($_GET['channel_id'])) {
        $table = $wpdb->prefix.'push_relation';
        $inner = "INNER JOIN $table ON($table.token_id={tbname}.{id_name} AND $table.connection_id='".self::$apisetting['def_connection']."')";
        $where[] = "$table.channel_id='$_GET[channel_id]'";
        $order = 'GROUP BY {tbname}.{id_name} DESC';
      } else {
        $_GET['channel_id'] = '';
      }
      if (count($where) > 0) {
        $where = 'WHERE '.implode(' AND ', $where);
      } else {
        $where = '';
      }
      if (!empty($_GET['apply'])) {
        if (!empty($_GET['doaction'])) {
          $doaction = $_GET['doaction'];
        } elseif (!empty($_GET['doaction2'])) {
          $doaction = $_GET['doaction2'];
        }
        $ids = implode(',', $_GET['device']);
        if ($doaction == 'activate') {
          self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='1' WHERE {id_name} IN($ids)"));
        } elseif ($doaction == 'deactivate') {
          self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {id_name} IN($ids)"));
        } elseif ($doaction == 'delete') {
          self::$pushdb->query(self::parse_query("DELETE FROM {tbname} WHERE {id_name} IN($ids)"));
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_relation WHERE token_id IN($ids) AND connection_id='".self::$apisetting['def_connection']."'");
          parent::update_counters();
        }
        wp_redirect($pageurl);
      } elseif (!empty($_GET['applytoall'])) {
        if (!empty($_GET['doaction'])) {
          $doaction = $_GET['doaction'];
        } elseif (!empty($_GET['doaction2'])) {
          $doaction = $_GET['doaction2'];
        }
        $tokens = self::$pushdb->get_results(self::parse_query("SELECT {tbname}.{id_name} AS id FROM {tbname} $inner $where GROUP BY {tbname}.{id_name}"));
        if ($tokens) {
          foreach ($tokens as $token) {
            if ($doaction == 'activate') {
              self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='1' WHERE {id_name}='".$token->id."'"));
            } elseif ($doaction == 'deactivate') {
              self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {id_name}='".$token->id."'"));
            } elseif ($doaction == 'delete') {
              self::$pushdb->query(self::parse_query("DELETE FROM {tbname} WHERE {id_name}='".$token->id."'"));
              $wpdb->query("DELETE FROM ".$wpdb->prefix."push_relation WHERE token_id='".$token->id."' AND connection_id='".self::$apisetting['def_connection']."'");
              parent::update_counters();
            }
          }
        }
        wp_redirect($pageurl);
      }
      $sql = self::Paging(self::parse_query("SELECT {tbname}.{id_name} AS id,{tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type
      ,{tbname}.{info_name} AS information,{tbname}.{active_name} AS active,$wpdb->users.user_login AS user FROM {tbname}
      LEFT JOIN $wpdb->users ON($wpdb->users.ID={tbname}.userid)
      $inner $where $order"), self::$pushdb);
      $tokens = self::$pushdb->get_results($sql);
      $paging_args = array(
      'base' => preg_replace('/&?callpage=([0-9]+)/', '', $_SERVER['REQUEST_URI']).'%_%',
      'format' => '&callpage=%#%',
      'total' => self::$paging['pages'],
      'current' => self::$paging['page'],
      'show_all' => false,
      'end_size' => 3,
      'mid_size' => 2,
      'prev_next' => true,
      'prev_text' => __('« Previous'),
      'next_text' => __('Next »')
      );
      include(smpush_dir.'/pages/token_manage.php');
    }
  }

  public static function push_channel() {
    global $wpdb;
    self::load_jsplugins();
    $pageurl = admin_url().'admin.php?page=smpush_channel';
    if ($_POST) {
      if (empty($_POST['title'])) {
        self::jsonPrint(0, __('Field title is required.', 'smpush-plugin-lang'));
      }
      if ($_POST['privacy'] == 1)
        $privacy = 1;
      else
        $privacy = 0;
      if ($_POST['id'] > 0) {
        $wpdb->update($wpdb->prefix.'push_channels', array('title' => $_POST['title'], 'description' => $_POST['description'], 'private' => $privacy), array('id' => $_POST['id']));
      } else {
        $wpdb->insert($wpdb->prefix.'push_channels', array('title' => $_POST['title'], 'description' => $_POST['description'], 'private' => $privacy));
      }
      echo 1;
      exit;
    } elseif (isset($_GET['update_counters'])) {
      parent::update_all_counters();
      wp_redirect($pageurl);
    } elseif (isset($_GET['delete'])) {
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_channels WHERE id='$_GET[id]'");
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_relation WHERE channel_id='$_GET[id]'");
      wp_redirect($pageurl);
    } elseif (isset($_GET['default'])) {
      $wpdb->query("UPDATE ".$wpdb->prefix."push_channels SET `default`='0'");
      $wpdb->update($wpdb->prefix.'push_channels', array('default' => 1), array('id' => $_GET['id']));
      wp_redirect($pageurl);
    } elseif (isset($_GET['id'])) {
      if ($_GET['id'] == -1) {
        $channel = array('id' => 0, 'title' => '', 'description' => '', 'private' => '0');
      } else {
        $channel = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."push_channels WHERE id='$_GET[id]'", 'ARRAY_A');
        $channel = array_map('stripslashes', $channel);
      }
      include(smpush_dir.'/pages/channel_form.php');
      exit;
    } else {
      $channels = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_channels ORDER BY id DESC");
      include(smpush_dir.'/pages/channel_manage.php');
    }
  }

  public static function testing() {
    global $wpdb;
    self::load_jsplugins();
    $pageurl = admin_url().'admin.php?page=smpush_test_sending';
    if ($_POST) {
      $message = $_POST['message'];
      $applelog = 'No device token';
      $googlelog = 'No device token';
      if (!empty($_POST['ios_token'])) {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', self::$apisetting['apple_cert_path']);
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$apisetting['apple_passphrase']);
        if (self::$apisetting['apple_sandbox'] == 1) {
          $appleserver = 'tls://gateway.sandbox.push.apple.com:2195';
        } else {
          $appleserver = 'tls://gateway.push.apple.com:2195';
        }
        @$fp = stream_socket_client($appleserver, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp) {
          if (empty($errstr))
            $errstr = __('Apple Certification error or problem with Password phrase', 'smpush-plugin-lang');
          if ($err == 111)
            $errstr .= __(' - Contact your host to enable outgoing ports', 'smpush-plugin-lang');
          $applelog = __('Failed to connect', 'smpush-plugin-lang').": $err $errstr".PHP_EOL;
        }
        else {
          $message = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $message), ENT_NOQUOTES, 'UTF-8');
          $body['aps'] = array('alert' => $message, 'sound' => 'default');
          $payload = json_encode($body);
          @$msg = chr(0).pack('n', 32).pack('H*', $_POST['ios_token']).pack('n', strlen($payload)).$payload;
          $resj = fwrite($fp, $msg, strlen($msg));
          fclose($fp);
          $applelog = __('Connected successfully with Apple server', 'smpush-plugin-lang')."\n".__('If you did not receive any message please check again your certification file and mobile code', 'smpush-plugin-lang');
        }
      }
      if (!empty($_POST['android_token'])) {
        if (function_exists('curl_init')) {
          $url = 'https://fcm.googleapis.com/fcm/send';
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
          $fields = array('registration_ids' => array($_POST['android_token']), 'data' => $data);
          $headers = array('Authorization: key='.self::$apisetting['google_apikey'], 'Content-Type: application/json');
          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $url);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
          $result = curl_exec($ch);
          if ($result === false) {
            $googlelog = 'Curl failed: '.curl_error($ch);
          } else {
            $googlelog = $result;
          }
          curl_close($ch);
        } else {
          $googlelog = __('CURL Library is not support in your host', 'smpush-plugin-lang');
        }
      }
      echo '<h3>'.__('Apple Response', 'smpush-plugin-lang').'</h3><p><pre class="smpush_pre">'.$applelog.'</pre></p><h3>'.__('Google Response', 'smpush-plugin-lang').'</h3><p><pre class="smpush_pre">'.$googlelog.'</pre></p>';
    } else {
      include(smpush_dir.'/pages/test_sending.php');
    }
  }

  public static function connections() {
    global $wpdb;
    self::load_jsplugins();
    $pageurl = admin_url().'admin.php?page=smpush_connections';
    if ($_POST) {
      extract($_POST);
      if (empty($title)) {
        self::jsonPrint(0, __('Field title is required.', 'smpush-plugin-lang'));
      }
      if ($type == 'remote') {
        if (function_exists('mysqli_connect')) {
          @$testlink = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
          if ($testlink->connect_errno) {
            self::jsonPrint(0, __('Could not connect with remote database error', 'smpush-plugin-lang').': '.$testlink->connect_error);
          }
        } else {
          @$testlink = mysql_connect($dbhost, $dbuser, $dbpass, $dbname);
          if (!$testlink) {
            self::jsonPrint(0, __('Could not connect with remote database error', 'smpush-plugin-lang').': '.mysql_error());
          }
        }
        @$pushdb = new wpdb($dbuser, $dbpass, $dbname, $dbhost);
      } else {
        $pushdb = $wpdb;
      }
      $pushdb->hide_errors();
      $_tbname = str_replace('{wp_prefix}', $wpdb->prefix, $tbname);
      $count = $pushdb->get_var("SELECT COUNT($id_name) FROM `$_tbname`");
      if ($count === null) {
        self::jsonPrint(0, __('Table or ID name column is wrong', 'smpush-plugin-lang'));
      }
      $test = $pushdb->get_row("SELECT COUNT($token_name) FROM `$_tbname` LIMIT 0,1");
      if ($test === null) {
        self::jsonPrint(0, __('Device Token column is wrong', 'smpush-plugin-lang'));
      }
      $test = $pushdb->get_row("SELECT COUNT($md5token_name) FROM `$_tbname` LIMIT 0,1");
      if ($test === null) {
        self::jsonPrint(0, __('MD5 Device Token column is wrong', 'smpush-plugin-lang'));
      }
      $test = $pushdb->get_row("SELECT COUNT($id_name) FROM `$_tbname` WHERE `$type_name`='$ios_name' OR `$type_name`='$android_name' OR `$type_name`='$wp_name' OR `$type_name`='$wp10_name' OR `$type_name`='$bb_name' OR `$type_name`='$chrome_name' OR `$type_name`='$safari_name' OR `$type_name`='$firefox_name' LIMIT 0,1");
      if ($test === null) {
        self::jsonPrint(0, __('Type column or Device type values is wrong', 'smpush-plugin-lang'));
      }
      if (!empty($info_name)) {
        $test = $pushdb->get_row("SELECT COUNT($info_name) FROM `$_tbname` LIMIT 0,1");
        if ($test === null) {
          self::jsonPrint(0, __('Information column name is wrong', 'smpush-plugin-lang'));
        }
      } else {
        $info_name = 'information';
        $pushdb->query("ALTER TABLE `$_tbname` ADD `$info_name` TINYTEXT NOT NULL");
      }
      if (!empty($active_name)) {
        $test = $pushdb->get_row("SELECT COUNT($active_name) FROM `$_tbname` LIMIT 0,1");
        if ($test === null) {
          self::jsonPrint(0, __('Active column name is wrong', 'smpush-plugin-lang'));
        }
      } else {
        $active_name = 'active';
        $pushdb->query("ALTER TABLE `$_tbname` ADD `$active_name` BOOLEAN NOT NULL");
        $pushdb->query("UPDATE `$_tbname` SET `$active_name`='1'");
      }
      if (!empty($latidude_name)) {
        $test = $pushdb->get_row("SELECT COUNT($latidude_name) FROM `$_tbname` LIMIT 0,1");
        if ($test === null) {
          self::jsonPrint(0, __('Latidude column name is wrong', 'smpush-plugin-lang'));
        }
      } else {
        $latidude_name = 'latidude';
        $pushdb->query("ALTER TABLE `$_tbname` ADD `$latidude_name` DECIMAL(10, 8) NOT NULL");
      }
      if (!empty($longitude_name)) {
        $test = $pushdb->get_row("SELECT COUNT($longitude_name) FROM `$_tbname` LIMIT 0,1");
        if ($test === null) {
          self::jsonPrint(0, __('Longitude column name is wrong', 'smpush-plugin-lang'));
        }
      } else {
        $longitude_name = 'longitude';
        $pushdb->query("ALTER TABLE `$_tbname` ADD `$longitude_name` DECIMAL(11, 8) NOT NULL");
      }
      if (!empty($gpstime_name)) {
        $test = $pushdb->get_row("SELECT COUNT($gpstime_name) FROM `$_tbname` LIMIT 0,1");
        if ($test === null) {
          self::jsonPrint(0, __('GPS update time column name is wrong', 'smpush-plugin-lang'));
        }
      } else {
        $gpstime_name = 'gps_time_update';
        $pushdb->query("ALTER TABLE `$_tbname` ADD `$gpstime_name` VARCHAR(15) NOT NULL");
      }
      $data = array(
      'title' => $title,
      'description' => $description,
      'dbtype' => $type,
      'dbhost' => $dbhost,
      'dbname' => $dbname,
      'dbuser' => $dbuser,
      'dbpass' => $dbpass,
      'tbname' => $tbname,
      'id_name' => $id_name,
      'token_name' => $token_name,
      'md5token_name' => $md5token_name,
      'type_name' => $type_name,
      'ios_name' => $ios_name,
      'android_name' => $android_name,
      'wp_name' => $wp_name,
      'wp10_name' => $wp10_name,
      'bb_name' => $bb_name,
      'chrome_name' => $chrome_name,
      'safari_name' => $safari_name,
      'firefox_name' => $firefox_name,
      'info_name' => $info_name,
      'active_name' => $active_name,
      'latidude_name' => $latidude_name,
      'longitude_name' => $longitude_name,
      'gpstime_name' => $gpstime_name,
      'counter' => $count
      );
      if ($id > 0) {
        $wpdb->update($wpdb->prefix.'push_connection', $data, array('id' => $id));
      } else {
        $wpdb->insert($wpdb->prefix.'push_connection', $data);
      }
      echo 1;
      exit;
    } elseif (isset($_GET['delete'])) {
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_connection WHERE id='$_GET[id]'");
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_relation WHERE connection_id='$_GET[id]'");
      wp_redirect($pageurl);
    } elseif (isset($_GET['id'])) {
      if ($_GET['id'] == -1) {
        $connection = array('id' => 0, 'title' => '', 'description' => '', 'dbtype' => '', 'dbhost' => '', 'dbname' => '', 'dbuser' => '', 'dbpass' => '', 'tbname' => '', 'token_name' => '', 'md5token_name' => '', 'type_name' => '', 'ios_name' => '', 'android_name' => '', 'bb_name' => '', 'wp_name' => '', 'wp10_name' => '', 'chrome_name' => '', 'safari_name' => '', 'firefox_name' => '', 'id_name' => '', 'info_name' => '', 'active_name' => '', 'latidude_name' => '', 'longitude_name' => '', 'gpstime_name' => '');
      } else {
        $connection = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."push_connection WHERE id='$_GET[id]'", 'ARRAY_A');
        $connection = array_map('stripslashes', $connection);
      }
      include(smpush_dir.'/pages/connection_form.php');
      exit;
    } else {
      $connections = $wpdb->get_results("SELECT id,title,description,counter FROM ".$wpdb->prefix."push_connection ORDER BY id DESC");
      include(smpush_dir.'/pages/connection_manage.php');
    }
  }

}