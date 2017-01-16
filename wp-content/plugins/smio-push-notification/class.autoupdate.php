<?php

class smpush_autoupdate extends smpush_controller {

  public static $wpdateformat;

  public function __construct() {
    parent::__construct();
  }
  
  public static function auto_update() {
    $helper = new smpush_helper();
    $content = '';
    if(!empty($_POST['startupdate'])){
      if(smpush_env == 'demo'){
        $content .= 'update feature is not allowed in the demo version';
      }
      if(empty(self::$apisetting['purchase_code'])){
        $content .= '<p>failed : enter your private purchase code to proceed in the updating process</p>';
      }
      if(!function_exists('rmdir')){
        $content .= '<p>failed : function rmdir() is not supported in your server</p>';
      }
      if(!function_exists('unlink')){
        $content .= '<p>failed : function unlink() is not supported in your server</p>';
      }
      if(!function_exists('fopen')){
        $content .= '<p>failed : function fopen() is not supported in your server</p>';
      }
      if(chmod(smpush_dir, 0777) === false){
        $content .= '<p>failed : directory wp-content does not have permission 0777</p>';
      }
      if(!class_exists('ZipArchive')){
        $content .= '<p>failed : ZipArchive library is not supported in your server</p>';
      }
      if(empty($content)){
        $lastupdate = json_decode($helper->buildCurl('https://smartiolabs.com/update/push_notification'), true);
        $updateData = $helper->buildCurl('https://smartiolabs.com/download', false, array('purchase_code' => self::$apisetting['purchase_code']));
        if($helper->curl_status == 200){
          $localzipfile = smpush_dir.'/../smiopush_update_package.zip';
          @unlink($localzipfile);
          $handle = fopen($localzipfile, 'w');
          fwrite($handle, $updateData);
          fclose($handle);
          if(md5_file($localzipfile) == $lastupdate['md5_hash']){
            $zip = new ZipArchive;
            $ziphandle = $zip->open($localzipfile);
            if ($ziphandle === TRUE) {
              self::delTree(smpush_dir);
              $zip->extractTo(smpush_dir.'/..');
              $zip->close();
              @unlink($localzipfile);
              $content = '<p>'.__('your system has been successfully upgraded to the latest version', 'smpush-plugin-lang').' '.$lastupdate['version'].'</p>';
            }
            else {
              @unlink($localzipfile);
              $content = '<p>'.__('Something happens while downloading the update package...Please try again later', 'smpush-plugin-lang').'</p>';
            }
          }
          else{
            @unlink($localzipfile);
            $content = '<p>'.__('Something happens while downloading the update package...Please try again later', 'smpush-plugin-lang').'</p>';
          }
        }
        else{
          $content = $updateData;
        }
      }
      include(smpush_dir.'/pages/auto_update.php');
      exit();
    }
    if(!empty($_POST['save'])){
      self::$apisetting['purchase_code'] = $_POST['purchase_code'];
      update_option('smpush_options', self::$apisetting);
    }
    $lastupdate = json_decode($helper->buildCurl('https://smartiolabs.com/update/push_notification'));
    if($lastupdate !== NULL){
      $content = '<p>'.__('System current version', 'smpush-plugin-lang').' : '.SMPUSHVERSION.'</p><p>'.__('System last version', 'smpush-plugin-lang').' : '.$lastupdate->version.'</p>';
    }
    else{
      $content = '<p>'.__('System current version', 'smpush-plugin-lang').' : '.SMPUSHVERSION.'</p><p>'.__('System last version : failed to connect</p>', 'smpush-plugin-lang');
    }
    $content .= '<form action="" method="post">
      <input name="purchase_code" type="text" size="50" value="'.self::$apisetting['purchase_code'].'" />
      <input type="submit" name="save" class="button button-primary" value="'.__('Save Changes', 'smpush-plugin-lang').'">';
    if(empty(self::$apisetting['purchase_code'])){
      $content .= '<p class="howto">'.__('For how to get your private purchase code', 'smpush-plugin-lang').' <a href="http://smartiolabs.com/blog/52/where-is-my-purchase-code/" target="_blank">'.__('click here', 'smpush-plugin-lang').'</a></p>';
      $content .= '<p><input type="submit" name="startupdate" class="button button-primary" value="'.__('Start System Update', 'smpush-plugin-lang').'" disabled="disabled"></p>';
    }
    elseif(!empty($lastupdate->version) && $lastupdate->version > SMPUSHVERSION){
      $content .= '<p><input type="submit" name="startupdate" class="button button-primary" value="'.__('Start System Update', 'smpush-plugin-lang').'"></p>';
    }
    $content .= '</form>';
    include(smpush_dir.'/pages/auto_update.php');
  }
  
  private static function delTree($dirPath) {
    if (!is_dir($dirPath)) {
      return;
    }
    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
      $dirPath .= '/';
    }
    $files = glob($dirPath.'*', GLOB_MARK);
    foreach ($files as $file) {
      if (is_dir($file)) {
        self::delTree($file);
      } else {
        unlink($file);
      }
    }
    rmdir($dirPath);
    return true;
  }

}