<?php

class WindowsPhonePushNotification extends smpush_sendpush {

  private static $notif_url = '';

  /**
   * Toast notifications are system-wide notifications that do not disrupt the user workflow or require intervention to resolve. They are displayed at the top of the screen for ten seconds before disappearing. If the toast notification is tapped, the application that sent the toast notification will launch. A toast notification can be dismissed with a flick.
   * Two text elements of a toast notification can be updated:
   * Title. A bolded string that displays immediately after the application icon.
   * Sub-title. A non-bolded string that displays immediately after the Title.
   */
  public static function push_toast($notif_url, $title, $params = array(), $delay = 0, $message_id = NULL) {
    self::$notif_url = $notif_url;

    $toastMessage = '<?xml version="1.0" encoding="utf-8"?>'.
    '<wp:Notification xmlns:wp="WPNotification">'.
    '<wp:Toast>'.
    '<wp:Text1>test</wp:Text1>'.
    "<wp:Text2>".htmlspecialchars($title).'</wp:Text2>';
    if(!empty($params)){
      $urlparam = array();
      foreach($params as $param => $value){
        $urlparam[] = $param.'='.htmlspecialchars($value);
      }
      $toastMessage .= '<wp:Param>/page1.xaml?'.implode('&amp;', $urlparam).'</wp:Param>';
    }
    $toastMessage .= '</wp:Toast>'.
    '</wp:Notification>';

    return self::push('toast', $delay + 2, $message_id, $toastMessage);
  }

  /**
   * A Tile displays in the Start screen if the end user has pinned it. Three elements of the Tile can be updated:
   * @background_url : You can use a local resource or remote resource for the background image of a Tile.
   * @title : The Title must fit a single line of text and should not be wider than the actual Tile. If this value is not set, the already-existing Title will display in the Tile.
   * @count. an integer value from 1 to 99. If not set in the push notification or set to any other integer value, the current Count value will continue to display. 
   */
  public static function push_tile($notif_url, $background_url, $title, $count, $delay = 0, $message_id = NULL) {
    self::$notif_url = $notif_url;

    $msg = "<?xml version=\"1.0\" encoding=\"utf-8\"?>".
    "<wp:Notification xmlns:wp=\"WPNotification\">".
    "<wp:Tile>".
    "<wp:BackgroundImage>".htmlspecialchars($background_url)."</wp:BackgroundImage>".
    "<wp:Count>$count</wp:Count>".
    "<wp:Title>".htmlspecialchars($title)."</wp:Title>".
    "</wp:Tile>".
    "</wp:Notification>";

    return self::push('token', $delay + 1, $message_id, $msg);
  }

  /**
   * If you do not wish to update the Tile or send a toast notification, you can instead send raw information to your application using a raw notification. If your application is not currently running, the raw notification is discarded on the Microsoft Push Notification Service and is not delivered to the device. The payload of a raw notification has a maximum size of 1 KB.
   */
  public static function push_raw($notif_url, $data, $delay = 0, $message_id = NULL) {
    self::$notif_url = $notif_url;
    return self::push(NULL, $delay + 3, $message_id, $data);
  }

  /**
   * @target : type of notification
   * @delay : immediate, in 450sec or in 900sec
   * @message_id : The optional custom header X-MessageID uniquely identifies a notification message. If it is present, the same value is returned in the notification response. It must be a string that contains a UUID
   */
  private static function push($target, $delay, $message_id, $toastMessage) {
    $sendedheaders = array(
    'Content-Type: text/xml',
    'X-NotificationClass: '.$delay
    );
    if ($message_id != NULL)
      $sendedheaders[] = 'X-MessageID: '.$message_id;
    if ($target != NULL)
      $sendedheaders[] = 'X-WindowsPhone-Target: '.$target;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $sendedheaders);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $toastMessage);
    curl_setopt($ch, CURLOPT_URL, self::$notif_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(self::$apisetting['wp_authed'] == 1){
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '2');
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '1');
      curl_setopt($ch, CURLOPT_CAINFO,  self::$apisetting['wp_cainfo']);
      curl_setopt($ch, CURLOPT_SSLCERT, self::$apisetting['wp_cert']);
      curl_setopt($ch, CURLOPT_SSLKEY, self::$apisetting['wp_pem']);
    }
    $response = curl_exec($ch);
    $curl_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($curl_status == 503){
      return false;
    }
    if($curl_status == 401){
      return false;
    }

    $result = array();
    if(!empty($response)){
      foreach (explode("\n", $response) as $line) {
        $tab = explode(":", $line, 2);
        if (count($tab) == 2)
          $result[$tab[0]] = trim($tab[1]);
      }
    }
    return $result;
  }

}