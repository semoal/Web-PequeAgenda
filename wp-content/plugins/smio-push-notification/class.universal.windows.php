<?php

class UniversalWindows10 extends smpush_sendpush {

  private static $notif = '';
  private static $access_token = '';
  private static $error = '';

  public static function getAccessTokenWP10() {
    if(!empty(self::$access_token)){
      return;
    }
    $str = 'grant_type=client_credentials&client_id='.urlencode(self::$apisetting['wp10_pack_sid']).'&client_secret='.urlencode(self::$apisetting['wp10_client_secret']).'&scope=notify.windows.com';
    $url = 'https://login.live.com/accesstoken.srf';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);                       
    $output = json_decode($output);
    if(isset($output->error)){
      self::$error = $output->error_description;
      return;
    }
    self::$access_token = $output->access_token;
  }
  
  public static function pushToastWP10($notif_url, $title, $params = array(), $image) {
    self::$notif = $notif_url;
    self::getAccessTokenWP10();
    
    if(!empty(self::$error)){
      return self::$error;
    }

    $toastMessage = '<toast>'.
    '<visual>'.
    '<binding template="ToastImageAndText04">';
    if(!empty($image)){
      $toastMessage .= '<image id="1" src="'.stripslashes($image).'"/>';
    }
    $toastMessage .= '<text id="1">'.htmlspecialchars($title).'</text>';
    if(!empty($params)){
      $idcounter = 2;
      foreach($params as $param => $value){
        $toastMessage .= '<text id="'.$idcounter.'">'.htmlspecialchars($value).'</text>';
        $idcounter++;
      }
    }
    $toastMessage .= '</binding>'.
    '</visual>'.
    '</toast>';
    
    return self::posTileWP10($notif_url, $toastMessage);
  }
  
  public static function posTileWP10($uri, $xml_data, $type = 'wns/toast', $tileTag = '') {
    $headers = array('Content-Type: text/xml', "Content-Length: ".strlen($xml_data), "X-WNS-Type: $type", "Authorization: Bearer ".self::$access_token);
    if (!empty($tileTag)) {
      array_push($headers, "X-WNS-Tag: $tileTag");
    }
    $f = fopen(smpush_dir.'/request.txt', 'w');
    $ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_STDERR, $f);
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($f);
    @unlink(smpush_dir.'/request.txt');

    if ($code == 200) {
      return true;
    }
    elseif ($code == 401) {
      self::$access_token = '';
      return self::posTileWP10($uri, $xml_data, $type, $tileTag);
    }
    elseif ($code == 410 || $code == 404) {
      return false;
    }
    else {
      return true;
    }
  }

}