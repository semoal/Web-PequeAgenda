<?php

class smpush_helper {
  public $ParseOutput;
  public $curl_status;
  public static $returnValue;
  public static $staticResult;
  public static $paging = array(
  'stillmore' => 0,
  'perpage' => 0,
  'callpage' => 0,
  'next' => 0,
  'previous' => 0,
  'pages' => 0,
  'result' => 0
  );

  public function __construct(){}
  
  public function buildCurl($url, $ssl = false, $postfields = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    if ($ssl !== false) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
      curl_setopt($ch, CURLOPT_CAINFO, smiopubap_lib.'/cacert.pem');
    } else {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    if(!empty($postfields)){
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    }
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    $result = curl_exec($ch);
    $this->curl_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $result;
  }

  public static function touch_time( $edit = false, $tab_index = 0, $multi = 0 ) {
    global $wp_locale;
    $tab_index_attribute = '';
    if ( (int) $tab_index > 0 )
    $tab_index_attribute = " tabindex=\"$tab_index\"";

    $time_adj = current_time('timestamp');
    $jj = ($edit) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
    $mm = ($edit) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
    $aa = ($edit) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
    $hh = ($edit) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
    $mn = ($edit) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
    $ss = ($edit) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );

    $cur_jj = gmdate( 'd', $time_adj );
    $cur_mm = gmdate( 'm', $time_adj );
    $cur_aa = gmdate( 'Y', $time_adj );
    $cur_hh = gmdate( 'H', $time_adj );
    $cur_mn = gmdate( 'i', $time_adj );

    $month = "<select " . ( $multi ? '' : 'id="mm" ' ) . "name=\"mm\"$tab_index_attribute>\n";
    for ( $i = 1; $i < 13; $i = $i +1 ) {
    $monthnum = zeroise($i, 2);
    $month .= "\t\t\t" . '<option value="' . $monthnum . '"';
    if ( $i == $mm )
    $month .= ' selected="selected"';
    /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
    $month .= '>' . sprintf( __( '%1$s-%2$s' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
    }
    $month .= '</select>';

    $day = '<input type="text" ' . ( $multi ? '' : 'id="jj" ' ) . 'name="jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
    $year = '<input type="text" ' . ( $multi ? '' : 'id="aa" ' ) . 'name="aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" />';
    $hour = '<input type="text" ' . ( $multi ? '' : 'id="hh" ' ) . 'name="hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';
    $minute = '<input type="text" ' . ( $multi ? '' : 'id="mn" ' ) . 'name="mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" />';

    echo '<div class="timestamp-wrap">';
    /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
    printf( __( '%1$s %2$s, %3$s @ %4$s : %5$s' ), $month, $day, $year, $hour, $minute );

    echo '</div><input type="hidden" id="ss" name="ss" value="' . $ss . '" />';

    if ( $multi ) return;

    echo "\n\n";
    foreach ( array('mm', 'jj', 'aa', 'hh', 'mn') as $timeunit ) {
      echo '<input type="hidden" id="hidden_' . $timeunit . '" name="hidden_' . $timeunit . '" value="' . $$timeunit . '" />' . "\n";
      $cur_timeunit = 'cur_' . $timeunit;
      echo '<input type="hidden" id="'. $cur_timeunit . '" name="'. $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
    }
  }

  public static function Paging($sql, $db){
  	if(isset($_REQUEST['perpage'])) $limit = $_REQUEST['perpage'];
  	else $limit = 20;
  	if(isset($_REQUEST['callpage'])) $currentpage = $_REQUEST['callpage'];
  	else $currentpage = 1;

    if(preg_match('/group by ([a-zA-Z0-9`*(),._\n\r]+)\s?/i', $sql, $match)){
      $cselect = 'DISTINCT('.$match[1].')';
      $countsql = preg_replace('/group by ([a-zA-Z0-9`*(),._\n\r\s]+)\s?/i', '', $sql);
    }
    else{
      $cselect = '*';
      $countsql = $sql;
    }
    $countsql = preg_replace('/select ([a-zA-Z0-9`*(),._\n\r\s]+) from/i', 'SELECT COUNT('.$cselect.') FROM', $countsql);
    $count = $db->get_var($countsql);
    if($db->num_rows > 1)
        $count = $db->num_rows;
    if($count == 0)
        return;
  	$pages = $count/$limit;
  	$pages = ceil($pages);

  	if($currentpage < $pages)
  		self::$paging['stillmore'] = 1;
  	else{
  		$currentpage = $pages;
  		self::$paging['stillmore'] = 0;
  	}
  	if($currentpage == 1){
  		self::$paging['previous'] = 0;
  		self::$paging['next'] = $currentpage+1;
  	}
  	elseif($currentpage == $pages){
  		self::$paging['previous'] = $currentpage-1;
  		self::$paging['next'] = 0;
  	}
  	else{
  		self::$paging['previous'] = $currentpage-1;
  		self::$paging['next'] = $currentpage+1;
  	}

    self::$paging['result'] = $count;
    self::$paging['pages'] = $pages;
    self::$paging['perpage'] = $limit;
    self::$paging['page'] = $currentpage;

  	if($currentpage > 0) $currentpage--;
  	$from = $currentpage*$limit;
  	return $sql." LIMIT $from,$limit";
  }

  public function output($respond, $result){
    if(!$this->ParseOutput){
      $this->ParseOutput = true;
      if(is_array($result))
        return $result;
      else
        return array();
    }
    self::jsonPrint($respond, $result);
  }

  public static function jsonPrint($respond, $result){
    $json = array();
  	if(is_array($result)){
  		$json['respond'] = $respond;
        $json['paging'] = self::$paging;
        $json['message'] = '';
        $json['result'] = $result;
  	}
  	else{
  		$json['respond'] = $respond;
        $json['paging'] = self::$paging;
  		$json['message'] = $result;
        $json['result'] = array();
  	}
    if(self::$returnValue == 'cronjob'){
      if($respond == 0){
        smpush_cronsend::writeLog($json['message']);
        die();
      }
      else{
        return;
      }
    }
    elseif(self::$returnValue){
      self::$staticResult = array('respond'=>$respond, 'result'=>$result);
      return true;
    }
    header('Content-Type: application/json');
  	echo json_encode($json);
  	die();
  }

  public function fetchPrintResult(){
    return self::$staticResult;
  }

  public function queryBuild($sql, $arg){
    if(isset($arg['like'])){
      foreach($arg['like'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index LIKE '$value'";
    }
    if(isset($arg['in'])){
      foreach($arg['in'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index IN ($value)";
    }
    if(isset($arg['notin'])){
      foreach($arg['notin'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index NOT IN ($value)";
    }
    if(isset($arg['between'])){
      foreach($arg['between'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index='$value' BETWEEN $value[0] AND $value[1]";
    }
    if(isset($arg['date'])){
      foreach($arg['date'] AS $tb=>$value){
        foreach($value AS $index=>$key)
            $where[] = "$key[index](".SMPUSHTBPRE."$tb)='$key[value]'";
      }
    }
    if(isset($arg['where'])){
      foreach($arg['where'] AS $index=>$value)
        $where[] = SMPUSHTBPRE."$index='$value'";
    }
    if(isset($where))
        $where = 'WHERE '.implode(' AND ', $where);
    else
        $where = '';
    if(isset($arg['orderby']))
        $order = 'ORDER BY '.SMPUSHTBPRE.$arg['orderby'].' '.$arg['order'];
    else
        $order = '';
    return str_replace(array('{where}','{order}'), array($where, $order), $sql);
  }

  public function checkReqHeader($detect){
    $return = false;
    if (!function_exists('apache_request_headers') && !function_exists('getallheaders')) {
      function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
          if (preg_match($rx_http, $key)) {
            $arh_key = preg_replace($rx_http, '', $key);
            $rx_matches = array();
            $rx_matches = explode('_', $arh_key);
            if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
              foreach ($rx_matches as $ak_key => $ak_val)
                $rx_matches[$ak_key] = ucfirst($ak_val);
              $arh_key = implode('-', $rx_matches);
            }
            $arh[$arh_key] = $val;
          }
        }
        return( $arh );
      }
    }
    if (function_exists('getallheaders')){
      foreach(getallheaders() as $name => $value){
        if($name == $detect){
          $return = $value;
        }
      }
    }
    elseif (function_exists('apache_request_headers')){
      foreach(apache_request_headers() as $name => $value){
        if($name == $detect){
          $return = $value;
        }
      }
    }
    if(empty($return) && !empty($_REQUEST[$name])){
      return $_REQUEST[$name];
    }
    return $return;
  }
  
  public function CheckParams($params, $or=false){
    if(! is_array($params)){
        $this->output(0, 'Parameters `'.$params.'` is required');
    }
    $indexes = '';
    foreach($params AS $param){
        if(!isset($_REQUEST[$param]) OR empty($_REQUEST[$param])){
            if($or) $indexes[] = $param;
            else $this->output(0, __('Parameter', 'smpush-plugin-lang').' `'.$param.'` '.__('is required, All required parameters are', 'smpush-plugin-lang').' `'.implode($params, '`,`').'`');
        }
        elseif($or) return;
    }
    if($or){
        $this->output(0, __('Parameters', 'smpush-plugin-lang').' `'.implode($params, '`,`').'` '.__('at least one of them is required', 'smpush-plugin-lang'));
    }
  }

  public static function cleanString($string){
    return trim(htmlspecialchars_decode(stripslashes($string)));
  }
  
  public static function ShortString($string, $charcount){
    $lenght = strlen($string);
    if($lenght > $charcount){
      $string = substr($string, 0, $charcount).'...';
      return $string;
    }
    else{
      return $string;
    }
  }
  
  public static function log($message){
    if(WP_DEBUG === true){
      if(is_array($message)){
        $message = json_encode($message);
      }
      $message = date('d/m/y H:i:s').' : '.$message;
      $message .= "\n==============================================";
      $message .= "\n";
      error_log($message, 3, ABSPATH.'/wp-content/debug.log');
    }
  }
  
  public function buildSafariPackFile($settings) {
    $tempfile = tempnam(sys_get_temp_dir(), '');
    if (file_exists($tempfile)) {
      unlink($tempfile);
    }
    mkdir($tempfile);
    if (is_dir($tempfile)) {
      $pack_folder = realpath($tempfile);
    }
    else{
      return '';
    }

    mkdir($pack_folder.'/icon.iconset');
    
    $image = wp_get_image_editor($settings['safari_icon']);
    $image->resize(16, 16, true);
    $image->save($pack_folder.'/icon.iconset/icon_16x16.png');
    
    $image = wp_get_image_editor($settings['safari_icon']);
    $image->resize(32, 32, true);
    $image->save($pack_folder.'/icon.iconset/icon_16x16@2x.png');
    
    $image = wp_get_image_editor($settings['safari_icon']);
    $image->resize(32, 32, true);
    $image->save($pack_folder.'/icon.iconset/icon_32x32.png');
    
    $image = wp_get_image_editor($settings['safari_icon']);
    $image->resize(64, 64, true);
    $image->save($pack_folder.'/icon.iconset/icon_32x32@2x.png');
    
    $image = wp_get_image_editor($settings['safari_icon']);
    $image->resize(128, 128, true);
    $image->save($pack_folder.'/icon.iconset/icon_128x128.png');
    
    $image = wp_get_image_editor($settings['safari_icon']);
    $image->resize(256, 256, true);
    $image->save($pack_folder.'/icon.iconset/icon_128x128@2x.png');

    $websitejson = array(
    'websiteName' => get_bloginfo('name'),
    'websitePushID' => $settings['safari_web_id'],
    'allowedDomains' => array('http://'.parse_url(get_bloginfo('url'), PHP_URL_HOST), 'https://'.parse_url(get_bloginfo('url'), PHP_URL_HOST)),
    'urlFormatString' => 'https://'.parse_url(get_bloginfo('url'), PHP_URL_HOST).'/%@',
    'authenticationToken' => md5(time()),
    'webServiceURL' => get_bloginfo('url').'/'.$settings['push_basename'].'/safari'
    );
    $this->storelocalfile($pack_folder.'/website.json', json_encode($websitejson));

    $manifest = array();
    $raw_files = array(
    'icon.iconset/icon_16x16.png',
    'icon.iconset/icon_16x16@2x.png',
    'icon.iconset/icon_32x32.png',
    'icon.iconset/icon_32x32@2x.png',
    'icon.iconset/icon_128x128.png',
    'icon.iconset/icon_128x128@2x.png',
    'website.json'
    );
    foreach ($raw_files as $raw_file) {
      $manifest[$raw_file] = sha1($this->readlocalfile($pack_folder.'/'.$raw_file));
    }
    $this->storelocalfile($pack_folder.'/manifest.json', json_encode($manifest));

    $pkcs12 = $this->readlocalfile($settings['safari_certp12_path']);
    $certs = array();
    if (!openssl_pkcs12_read($pkcs12, $certs, $settings['safari_passphrase'])) {
      error_log('wrong safari certification password');
    }
    $signature_path = $pack_folder.'/signature';
    // Sign the manifest.json file with the private key from the certificate
    $cert_data = openssl_x509_read($certs['cert']);
    $private_key = openssl_pkey_get_private($certs['pkey'], $settings['safari_passphrase']);
    openssl_pkcs7_sign($pack_folder.'/manifest.json', $signature_path, $cert_data, $private_key, array(), PKCS7_BINARY | PKCS7_DETACHED, smpush_dir.'/AppleWWDRCA.pem');
    // Convert the signature from PEM to DER
    $signature_pem = $this->readlocalfile($signature_path);
    $matches = array();
    if (!preg_match('~Content-Disposition:[^\n]+\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----~', $signature_pem, $matches)) {
      error_log('wrong safari certification type');
    }
    $signature_der = base64_decode($matches[1]);
    $this->storelocalfile($signature_path, $signature_der);

    $upload_dir = wp_upload_dir();
    $zip_path = $upload_dir['basedir'].'/certifications/safari_pack_connection_'.get_current_blog_id().'.zip';
    unlink($zip_path);
    
    $zip = new ZipArchive();
    if (!$zip->open($zip_path, ZIPARCHIVE::CREATE)) {
      error_log('Could not create '.$zip_path);
      return;
    }
    $raw_files[] = 'manifest.json';
    $raw_files[] = 'signature';
    foreach ($raw_files as $raw_file) {
      $zip->addFile($pack_folder.'/'.$raw_file, $raw_file);
    }
    $zip->close();
    return $zip_path;
  }
  
  public function readlocalfile($path) {
    if(function_exists('fopen') && function_exists('stream_get_contents')){
      $handler = fopen($path, 'rb');
      $content = stream_get_contents($handler);
      fclose($handler);
    }
    elseif(function_exists('readfile')){
      $content = readfile($path);
    }
	elseif(function_exists('file_get_contents')){
      $content = file_get_contents($path);
    }
    return $content;
  }
  
  public function storelocalfile($path, $contents) {
    if(function_exists('fopen')){
      $handle = fopen($path, 'w');
      fwrite($handle, $contents);
      fclose($handle);
    }
    elseif(function_exists('file_put_contents')){
      file_put_contents($path, $contents);
    }
  }

}