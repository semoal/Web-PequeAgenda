<?php

class smpush_event_manager extends smpush_controller {

  public function __construct() {
    parent::__construct();
  }

  public static function page() {
    global $wpdb;
    self::load_jsplugins();
    $pageurl = admin_url().'admin.php?page=smpush_events';
    if ($_POST) {
      if(smpush_env == 'demo'){
        echo 1;
        exit;
      }
      if (empty($_POST['title']) || empty($_POST['message']) || empty($_POST['event_post_type'])) {
        self::jsonPrint(0, __('All fields are required.', 'smpush-plugin-lang'));
      }
      $conditions = array();
      foreach($_POST['conditions']['attri'] as $key => $value){
        if(!empty($_POST['conditions']['attri'][$key]) && !empty($_POST['conditions']['sign'][$key])){
          $conditions['attri'][$key] = $_POST['conditions']['attri'][$key];
          $conditions['sign'][$key] = $_POST['conditions']['sign'][$key];
          $conditions['value'][$key] = $_POST['conditions']['value'][$key];
        }
      }
      $data = array();
      $data['title'] = $_POST['title'];
      $data['event_type'] = $_POST['event_type'];
      $data['post_type'] = $_POST['event_post_type'];
      $data['message'] = $_POST['message'];
      $data['notify_segment'] = $_POST['notify_segment'];
      $data['userid_field'] = $_POST['userid_field'];
      $data['conditions'] = (empty($conditions))? '' : serialize($conditions);
      $data['desktop_link'] = (isset($_POST['desktop_link']))? 1 : 0;
      $data['status'] = (isset($_POST['status']))? 1 : 0;
      $data['ignore'] = (isset($_POST['ignore']))? 1 : 0;
      if (!empty($_POST['id'])) {
        $wpdb->update($wpdb->prefix.'push_events', $data, array('id' => $_POST['id']));
      } else {
        $wpdb->insert($wpdb->prefix.'push_events', $data);
      }
      echo 1;
      exit;
    }
    elseif (isset($_GET['loadAttri'])) {
      $post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE post_type='$_GET[smpush_post_type]' LIMIT 0,1", 'ARRAY_A');
      if(empty($post)){
        echo 0;
        exit;
      }
      $selcOptions = '<option value="">'.__('Choose Attribute', 'smpush-plugin-lang').'</option><optgroup label="'.__('Post Attributes', 'smpush-plugin-lang').'">';
      foreach($post as $column => $value){
        $selcOptions .= '<option>'.$column.'</option>';
      }
      $selcOptions .= '</optgroup>';
      $postIDs = $wpdb->get_row("SELECT GROUP_CONCAT(ID SEPARATOR ',') AS ids FROM $wpdb->posts WHERE post_type='$_GET[smpush_post_type]' ORDER BY ID DESC LIMIT 0,1000", 'ARRAY_A');
      $metakeys = $wpdb->get_results("SELECT DISTINCT(meta_key) AS meta_key FROM $wpdb->postmeta WHERE post_id IN($postIDs[ids])", 'ARRAY_A');
      $selcOptions .= '<optgroup label="'.__('Postmeta Attributes', 'smpush-plugin-lang').'">';
      foreach($metakeys as $metakey){
        $selcOptions .= '<option value="meta_'.$metakey['meta_key'].'">'.$metakey['meta_key'].'</option>';
      }
      $selcOptions .= '</optgroup>';
      echo $selcOptions;
      exit;
    }
    elseif (isset($_GET['delete'])) {
      if(smpush_env == 'demo'){
        echo 1;
        exit;
      }
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_events WHERE id='$_GET[id]'");
      wp_redirect($pageurl);
    }
    elseif (isset($_GET['id'])) {
      if ($_GET['id'] == -1) {
        $event = array('id' => 0, 'title' => '', 'event_type' => '1', 'post_type' => '', 'message' => '', 'notify_segment' => 'all', 'userid_field' => '', 'desktop_link' => 1, 'status' => 1, 'ignore' => 0);
      }
      else {
        $event = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."push_events WHERE id='$_GET[id]'", 'ARRAY_A');
        $event['conditions'] = unserialize($event['conditions']);
        $event = stripslashes_deep($event);
      }
      include(smpush_dir.'/pages/event_form.php');
      exit;
    }
    else {
      $events = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_events ORDER BY id ASC");
      include(smpush_dir.'/pages/event_manage.php');
    }
  }
  
}