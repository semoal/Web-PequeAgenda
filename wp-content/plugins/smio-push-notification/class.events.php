<?php

class smpush_events extends smpush_controller{
  private static $post;
  private static $sendToDevices;
  private static $sendToType;
  private static $desktopLinkOpen = false;
  
  public function __construct(){
    parent::__construct();
  }
  
  private static function eventVerifyConditions($conditions){
    foreach($conditions['attri'] as $key => $value){
      $param = $conditions['attri'][$key];
      $sign = $conditions['sign'][$key];
      $value = $conditions['value'][$key];
      if(preg_match('/^meta_/', $param)){
        $param = preg_replace('/^meta_/', '', $param);
        if(isset(self::$post->meta_keys[$param][0])){
          $param = self::$post->meta_keys[$param][0];
        }
        else{
          return false;
        }
      }
      elseif(isset(self::$post->$param)){
        $param = self::$post->$param;
      }
      else{
        return false;
      }
      if(strtolower($value) == 'now()'){
        $param = strtotime($param);
        $value = time();
      }
      elseif(strtolower($value) == 'date()'){
        $param = strtotime($param);
        $value = time();
      }
      switch($sign){
        case '>':
          if($param <= $value)return false;
          break;
        case '>=':
          if($param < $value)return false;
          break;
        case '<':
          if($param >= $value)return false;
          break;
        case '<=':
          if($param > $value)return false;
          break;
        case '=':
          if($param != $value)return false;
          break;
        case 'NOT =':
          if($param == $value)return false;
          break;
        case 'IN':
          $haystack = explode(',', $value);
          $haystack = array_map('trim', $haystack);
          if(!in_array($param, $haystack))return false;
          break;
        case 'NOT IN':
          $haystack = explode(',', $value);
          $haystack = array_map('trim', $haystack);
          if(in_array($param, $haystack))return false;
          break;
      }
    }
  }
  
  private static function eventManager($event_type, $postid, $channelIDs){
    global $wpdb;
    $events = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_events WHERE event_type='$event_type' AND post_type='".self::$post->post_type."' AND status='1'", 'ARRAY_A');
    if($events){
      foreach($events as $event){
        $event = stripslashes_deep($event);
        $message = $event['message'];
        self::$desktopLinkOpen = $event['desktop_link'];
        $conditions = unserialize($event['conditions']);
        if(!empty($conditions['attri'])){
          $continue = self::eventVerifyConditions($conditions);
          if($continue === false){
            continue;
          }
        }
        if($event['notify_segment'] == 'custom'){
          $userid_field = $event['userid_field'];
          if(preg_match('/^meta_/', $userid_field)){
            $userid_field = preg_replace('/^meta_/', '', $userid_field);
            if(!empty(self::$post->meta_keys[$userid_field][0])){
              self::$sendToDevices = self::$post->meta_keys[$userid_field][0];
              self::$sendToType = 'userid';
            }
          }
          elseif(!empty(self::$post->$userid_field)){
            self::$sendToDevices = self::$post->$userid_field;
            self::$sendToType = 'userid';
          }
          else{
            continue;
          }
        }
        if(preg_match_all('/\{\$([^}]+)\}/', $message, $matches)){
          foreach($matches[1] as $dynparam){
            $params = explode('|', $dynparam);
            $dynparam = $params[0];
            $expression = '{$'.$dynparam;
            $replace = '';
            if(preg_match('/^meta_/', $dynparam)){
              $dynparamfix = preg_replace('/^meta_/', '', $dynparam);
              if(!empty(self::$post->meta_keys[$dynparamfix][0])){
                $replace = self::$post->meta_keys[$dynparamfix][0];
              }
              elseif($event['ignore'] == 1){
                continue;
              }
            }
            elseif(!empty(self::$post->$dynparam)){
              $replace = self::$post->$dynparam;
            }
            elseif($event['ignore'] == 1){
              continue;
            }
            if(isset($params[1])){
              $expression .= '|'.$params[1];
              switch ($params[1]){
                case 'CapitalizeFirst':
                  $replace = ucfirst($replace);
                  break;
                case 'CapitalizeAllFirst':
                  $replace = ucwords($replace);
                  break;
                case 'UPPERCASE':
                  $replace = strtoupper($replace);
                  break;
                case 'lowercase':
                  $replace = strtolower($replace);
                  break;
                case 'datetime':
                  $replace = date(get_option('date_format').' '.get_option('time_format'), strtotime($replace));
                  break;
                case 'date':
                  $replace = date(get_option('date_format'), strtotime($replace));
                  break;
                case 'regular':
                  break;
                default :
                  $replace = $params[1];
                  break;
              }
            }
            if(isset($params[3])){
              $expression .= '|'.$params[3];
              switch ($params[3]){
                case 'post_title':
                  $post = get_post($replace);
                  $replace = $post->post_title;
                  break;
                case 'post_permalink':
                  $replace = get_permalink($replace);
                  break;
                case 'post_date':
                  $post = get_post($replace);
                  $replace = date(get_option('date_format'), strtotime($post->post_date));
                  break;
                case 'post_mod_date':
                  $post = get_post($replace);
                  $replace = date(get_option('date_format'), strtotime($post->post_modified));
                  break;
                case 'post_categories':
                  $cats = wp_get_post_categories($replace, array('fields' => 'names'));
                  $replace = implode(', ', $cats);
                  break;
                case 'post_tags':
                  $posttags = get_the_tags($replace);
                  if($posttags) {
                    $tags = array();
                    foreach($posttags as $tag) {
                      $tags[] = $tag->name; 
                    }
                    $replace = implode(', ', $tags);
                  }
                  break;
                case 'user_title':
                  $user_info = get_userdata($replace);
                  $replace = $user_info->display_name;
                  break;
                case 'user_email':
                  $user_info = get_userdata($replace);
                  $replace = $user_info->user_email;
                  break;
                case 'user_name':
                  $user_info = get_userdata($replace);
                  $replace = $user_info->user_login;
                  break;
              }
            }
            if(isset($params[2]) && $replace == ''){
              $expression .= '|'.$params[2];
              if($params[2] == 'null'){
                $replace = '';
              }
              else{
                $replace = $params[2];
              }
            }
            elseif(isset($params[2]) && $params[2] == 'null'){
              $expression .= '|null';
            }
            $expression .= '}';
            $message = str_replace($expression, self::ShortString($replace, 100), $message);
          }
        }
        switch($event['notify_segment']){
          case 'all':
            if(self::$apisetting['e_post_chantocats'] == 1){
              self::$sendToDevices = self::PushUsersInPostCat($postid);
              self::$sendToType = 'tokenid';
            }
            else{
              self::$sendToDevices = 'all';
              self::$sendToType = 'all';
            }
            break;
          case 'post_owner':
            self::$sendToDevices = self::UsersRelatedPost($postid);
            self::$sendToType = 'userid';
            break;
          case 'post_commenters':
            self::$sendToDevices = self::UsersRelatedPost($postid, true);
            self::$sendToType = 'userid';
            break;
        }
        self::eventSendQueuedMessage($message, $postid, $event_type, $channelIDs);
      }
    }
  }
  
  public static function eventSendQueuedMessage($message, $postid, $event_type, $channelIDs){
    if(self::$sendToDevices !== false){
      
      switch ($event_type){
        case 'publish':
          $filter = 'newpost';
          break;
        case 'approve':
          $filter = 'apprpost';
          break;
        case 'update':
          $filter = 'postupdated';
          break;
      }
      
      $message = apply_filters('smpush_events_'.$filter.'_message', $message, $postid);
      $postid = apply_filters('smpush_events_'.$filter.'_payload', $postid, $message);

      $cronsetting = array();
      $cronsetting['desktop_title'] = get_bloginfo('name');
      $cronsetting['desktop_link'] = (empty(self::$desktopLinkOpen))? ''  : get_permalink($postid);
      $cronsetting = apply_filters('smpush_events_'.$filter.'_settings', $cronsetting, $message, $postid);

      smpush_sendpush::SendCronPush(self::$sendToDevices, $message, $postid, self::$sendToType, $cronsetting, 0 , $channelIDs);
    }
  }
  
  public static function queue_event($new_status, $old_status, $post){
    if(isset($_POST['smpush_mute'])){
      return;
    }
    global $wpdb;
    $wpdb->delete($wpdb->prefix.'push_events_queue', array('post_id' => $post->ID));
    $event = array();
    $event['post_id'] = $post->ID;
    $event['new_status'] = $new_status;
    $event['old_status'] = $old_status;
    $event['post'] = serialize($_POST);
    $wpdb->insert($wpdb->prefix.'push_events_queue', $event);
  }
  
  public static function post_status_change($new_status, $old_status, $postid, $post){
    self::$post = get_post($postid);
    if(empty(self::$post)){
      return false;
    }
    self::$post->meta_keys = get_post_meta($postid);
    $channelIDs = '';
    if(!isset($post['smpush_all_users']) && !empty($post['smpush_channels'])){
      $channelIDs = implode(',', $post['smpush_channels']);
    }
    
    if(!empty($new_status) && !empty($old_status)){
      if($new_status == 'publish' && $old_status != $new_status){
        $message = self::eventManager('publish', $postid, $channelIDs);
        $message = self::eventManager('approve', $postid, $channelIDs);
      }
      else{
        $message = self::eventManager('update', $postid, $channelIDs);
      }
    }
  }
  
  private static function processNotifBody($type, $subject){
    $type = $type.'_body';
    $message = str_replace(array('{subject}','{comment}'), $subject, stripslashes(self::$apisetting[$type]));
    return $message;
  }

  public static function comment_approved($nowcomment){
    if(self::$apisetting['e_appcomment'] == 1){
      $subject = self::ShortString($nowcomment->comment_content, 60);
      $message = self::processNotifBody('e_appcomment', $subject);
      $postid = $nowcomment->comment_post_ID;
      $commentid = $nowcomment->comment_ID;
      
      $message = apply_filters('smpush_events_approvecomment_message', $message, $postid, $commentid);
      $payload = apply_filters('smpush_events_approvecomment_payload', $postid, $message, $commentid);
      
      $cronsetting = array();
      $post = get_post($postid);
      $cronsetting['desktop_title'] = $post->post_title;
      $cronsetting['desktop_link'] = get_permalink($postid);
      $cronsetting = apply_filters('smpush_events_approvecomment_settings', $cronsetting, $message, $postid);
      
      smpush_sendpush::SendCronPush(array(0=>$nowcomment->user_id), $message, $payload, 'userid', $cronsetting);
    }
    self::new_comment($nowcomment->comment_ID, $nowcomment);
  }

  public static function new_comment($commid, $nowcomment){
    global $wpdb;
    if($nowcomment->comment_approved == 1){
      if(self::$apisetting['e_usercomuser'] == 1){
        if($nowcomment->comment_parent > 0){
          $comment = $wpdb->get_row("SELECT comment_post_ID,user_id FROM ".$wpdb->prefix."comments WHERE comment_ID='".$nowcomment->comment_parent."' AND user_id>0", 'ARRAY_A');
          if(!$comment) return false;
          $commentcount = $wpdb->get_var("SELECT COUNT(comment_ID) AS commcount FROM ".$wpdb->prefix."comments WHERE comment_parent='".$nowcomment->comment_parent."' AND comment_approved='1'");
          if($commentcount>0 AND ($commentcount==1 OR $commentcount%5==0)){
            $subject = self::ShortString($nowcomment->comment_content, 60);
            $message = self::processNotifBody('e_usercomuser', $subject);
            $postid = $comment['comment_post_ID'];
            $commentid = $comment['comment_ID'];

            $message = apply_filters('smpush_events_user_reply_touser_message', $message, $postid, $commentid);
            $payload = apply_filters('smpush_events_user_reply_touser_payload', $postid, $message, $commentid);
            
            $cronsetting = array();
            $post = get_post($postid);
            $cronsetting['desktop_title'] = $post->post_title;
            $cronsetting['desktop_link'] = get_permalink($postid);
            $cronsetting = apply_filters('smpush_events_user_reply_touser_settings', $cronsetting, $message, $postid);

            smpush_sendpush::SendCronPush(array(0=>$comment['user_id']), $message, $payload, 'userid', $cronsetting);
          }
        }
      }
      if(self::$apisetting['e_newcomment'] == 1){
        $postid = $nowcomment->comment_post_ID;
        $commentid = $nowcomment->comment_ID;
        $commentcount = $wpdb->get_var("SELECT COUNT(comment_ID) AS commcount FROM ".$wpdb->prefix."comments WHERE comment_post_ID='$postid' AND comment_approved='1'");
        if($commentcount>0 AND ($commentcount==1 OR $commentcount%10==0)){
          $post = $wpdb->get_row("SELECT post_title,post_author,guid FROM ".$wpdb->prefix."posts WHERE ID='$postid'", 'ARRAY_A');
          $subject = self::ShortString($post['post_title'], 60);
          $message = self::processNotifBody('e_newcomment', $subject);

          $message = apply_filters('smpush_events_newcomment_message', $message, $postid, $commentid);
          $payload = apply_filters('smpush_events_newcomment_payload', $postid, $message, $commentid);
          
          $cronsetting = array();
          $cronsetting['desktop_title'] = $post['post_title'];
          $cronsetting['desktop_link'] = $post['guid'];
          $cronsetting = apply_filters('smpush_events_newcomment_settings', $cronsetting, $message, $postid);
          
          smpush_sendpush::SendCronPush(array(0=>$post['post_author']), $message, $payload, 'userid', $cronsetting);
        }
      }
    }
  }

  private static function UserRelatedComment($commid){
    global $wpdb;
    $userid = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."comments WHERE comment_ID='$commid'");
    if(!$userid) return false;
    return $userid;
  }
  
  private static function PushUsersInPostCat($postid){
    global $wpdb;
    $ids = array();
    $channelids = array();
    $post_categories = wp_get_post_categories($postid);
    foreach($post_categories as $catobject){
      $category = get_category($catobject);
      $channelid = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."push_channels WHERE title LIKE '$category->name'");
      if($channelid){
        $channelids[] = $channelid;
      }
    }
    if(!empty($channelids)){
      $channelids = implode(',', $channelids);
      $tokenids = $wpdb->get_results("SELECT DISTINCT(token_id) FROM ".$wpdb->prefix."push_relation WHERE channel_id IN($channelids) AND connection_id='".self::$apisetting['def_connection']."'");
      if(!$tokenids) return false;
      foreach($tokenids AS $tokenid){
        $ids[] = $tokenid->token_id;
      }
    }
    return $ids;
  }

  private static function AllPushUsers(){
    $ids = array();
    $authorids = self::$pushdb->get_results(self::parse_query("SELECT userid FROM {tbname} WHERE userid>0 AND {active_name}='1'"));
    if(!$authorids) return false;
    foreach($authorids AS $authorid){
      $ids[] = $authorid->userid;
    }
    return $ids;
  }

  private static function UsersRelatedPost($postid, $allrealted=false){
    global $wpdb;
    $ids = array();
    $authorid = $wpdb->get_var("SELECT post_author FROM ".$wpdb->prefix."posts WHERE ID='$postid' AND post_status='publish' AND post_type='post' AND post_password=''");
    if(!$authorid) return false;
    $ids[] = $authorid;
    if($allrealted){
      $sql = "SELECT user_id FROM ".$wpdb->prefix."comments WHERE comment_post_ID='$postid' AND user_id>0 GROUP BY user_id";
      $gets = $wpdb->get_results($sql, 'ARRAY_A');
      if($gets){
        foreach($gets AS $get){
          $ids[] = $get['user_id'];
        }
      }
    }
    return $ids;
  }
  
  public static function buddy_activity($activity){
    if(self::$apisetting['bb_notify_activity'] == 0 || empty($activity->item_id)){
      return false;
    }
    global $wpdb;
    $bb_pages = get_option('bp-pages');
    $message = strip_tags($activity->action);

    $message = apply_filters('smpush_events_bb_message', $message, 'activity_'.$activity->id);
    $payload = apply_filters('smpush_events_bb_payload', 'activity_'.$activity->id, $message);
    $cronsetting = array();
    $cronsetting['desktop_title'] = '';
    $cronsetting['desktop_link'] = get_permalink($bb_pages['activity']);
    
    $user_id = array();
    if($activity->component == 'groups'){
      $cronsetting['desktop_title'] = $wpdb->get_var("SELECT name FROM ".$wpdb->prefix."bp_groups WHERE id='$activity->item_id'");
      $where = '';
      if(self::$apisetting['bb_notify_activity_admins_only'] == 1){
        $where = "AND is_admin='1'";
      }
      $admins = $wpdb->get_results("SELECT user_id FROM ".$wpdb->prefix."bp_groups_members WHERE group_id='$activity->item_id' AND is_confirmed='1' AND is_banned='0' $where");
      if($admins){
        foreach($admins as $admin){
          $user_id[] = $admin->user_id;
        }
      }
    }
    elseif($activity->type == 'activity_comment'){
      $commenters = $wpdb->get_results("SELECT user_id FROM ".$wpdb->prefix."bp_activity WHERE item_id='$activity->item_id' AND id<$activity->id");
      if($commenters){
        foreach($commenters as $commenter){
          $user_id[] = $commenter->user_id;
        }
      }
    }
    else{
      return false;
    }
    
    if(empty($user_id)){
      return false;
    }
    
    $cronsetting = apply_filters('smpush_events_bb_settings', $cronsetting, $message, 'activity_'.$activity->id);

    smpush_sendpush::SendCronPush($user_id, $message, $payload, 'userid', $cronsetting);
  }
  
  public static function buddy_notifications($notification){
    if(self::$apisetting['bb_notify_'.$notification->component_name] == 0){
      return false;
    }
    $bp = buddypress();
    $bp->notifications = new stdClass();
    $bp->notifications->query_loop = new stdClass();
    $bp->notifications->query_loop->notification = $notification;
    $notification_desc = bp_get_the_notification_description();

    $dom = new DOMDocument;
    $dom->loadHTML($notification_desc);
    $notification_link = $dom->getElementsByTagName('a');
    $notification_link = $notification_link[0]->getAttribute('href');
    $message = $dom->getElementsByTagName('a');
    $message = $message[0]->nodeValue;

    $message = apply_filters('smpush_events_bb_message', $message, $notification->id);
    $payload = apply_filters('smpush_events_bb_payload', $notification->id, $message);
    
    $user_info = get_userdata($notification->secondary_item_id);

    $cronsetting = array();
    $cronsetting['desktop_title'] = $user_info->display_name;
    $cronsetting['desktop_link'] = $notification_link;
    $cronsetting = apply_filters('smpush_events_bb_settings', $cronsetting, $message, $notification->id);

    smpush_sendpush::SendCronPush(array(0 => $notification->user_id), $message, $payload, 'userid', $cronsetting);
  }
  
  public static function meta_box_design($post){
    global $wpdb;
    $channels = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'push_channels ORDER BY title ASC');
    include(smpush_dir.'/pages/meta_box.php');
  }
  
  public static function build_meta_box(){
    add_meta_box('smpush-meta-box', 'Push Notification', array('smpush_events', 'meta_box_design'), null, 'side', 'high');
  }

}