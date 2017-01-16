<?php

class smpush_browser_push extends smpush_controller {

  public function __construct() {
    parent::__construct();
  }

  private static function safari() {
    $output = '
<script>

var devicetype = "safari";

window.onload = function() {
  if ("safari" in window && "pushNotification" in window.safari) {
    smpush_bootstrap_init();
    
    var pushButton = jQuery(".smpush-push-permission-button");
    pushButton.removeAttr("disabled");
    
    if(smpush_getCookie("smpush_safari_device_token") != ""){
      pushButton.html("'.addslashes(self::$apisetting['desktop_btn_unsubs_text']).'");
    }
    else{
      pushButton.html("'.addslashes(self::$apisetting['desktop_btn_subs_text']).'");
      smpush_link_user();
    }
    
    pushButton.click(function() {
      var permissionData = window.safari.pushNotification.permission("'.self::$apisetting['safari_web_id'].'");
      checkRemotePermission(permissionData);
    });
    jQuery(".smpush-push-subscriptions-button").click(function() {
      var permissionData = window.safari.pushNotification.permission("'.self::$apisetting['safari_web_id'].'");
      checkRemotePermission(permissionData);
    });
    
    if("'.self::$apisetting['desktop_modal'].'" == "0"){
      document.getElementsByClassName("smpush-push-permission-button")[0].click();
    }    
  }
};
 
var checkRemotePermission = function (permissionData) {
  if (permissionData.permission === "default") {
    window.safari.pushNotification.requestPermission(
        "'.get_bloginfo('url') .'/'.self::$apisetting['push_basename'].'/safari",
        "'.self::$apisetting['safari_web_id'].'",
        {},
        checkRemotePermission
    );
  }
  else if (permissionData.permission === "denied") {
    smpush_endpoint_unsubscribe(smpush_getCookie("smpush_safari_device_token"));
    smpush_setCookie("smpush_desktop_request", "true", 10);
    smpush_setCookie("smpush_safari_device_token", "false", -1);
    smpush_setCookie("smpush_device_token", "false", -1);
  }
  else if (permissionData.permission === "granted") {
    if(smpush_getCookie("smpush_safari_device_token") != ""){
      smpush_endpoint_unsubscribe(smpush_getCookie("smpush_safari_device_token"));
      smpush_setCookie("smpush_desktop_request", "true", 10);
      smpush_setCookie("smpush_safari_device_token", "false", -1);
      smpush_setCookie("smpush_device_token", "false", -1);
      pushButton.attr("disabled","disabled");
      jQuery(".smpush-push-subscriptions-button").attr("disabled","disabled");
      jQuery(".smpush-push-subscriptions-button").html("'.self::$apisetting['desktop_modal_saved_text'].'");
    }
    else{
      smpush_setCookie("smpush_safari_device_token", permissionData.deviceToken, 365);
      smpush_endpoint_subscribe(permissionData.deviceToken);
      pushButton.attr("disabled","disabled");
      jQuery(".smpush-push-subscriptions-button").attr("disabled","disabled");
      jQuery(".smpush-push-subscriptions-button").html("'.self::$apisetting['desktop_modal_saved_text'].'");
    }
  }
};

';
    $output .= self::bootstrap();
    $output .= '</script>';
    echo preg_replace('/\s+/', ' ', $output);
  }
  
  private static function bootstrap() {
    return '
function smpush_debug(object) {
  if('.self::$apisetting['desktop_debug'].' == 1){
    console.log(object);
  }
}

function smpush_endpoint_subscribe(subscriptionId) {
  if(subscriptionId == ""){
    return false;
  }
  smpush_setCookie("smpush_desktop_request", "true", 365);
  smpush_setCookie("smpush_device_token", subscriptionId, 365);
  
  var subsChannels = [];
  jQuery("input.smpush_desktop_channels_subs:checked").each(function(index) {
    subsChannels.push(jQuery(this).val());
  });
  subsChannels = subsChannels.join(",");
  
  if(jQuery(".smpush-push-subscriptions-button").length > 0){
    var apiService = "channels_subscribe";
  }
  else{
    var apiService = "savetoken";
  }
  
  jQuery.ajax({
    method: "POST",
    url: "'.get_bloginfo('url') .'/index.php?smpushcontrol="+apiService,
    data: { device_token: subscriptionId, device_type: devicetype, active: 1, user_id: '.get_current_user_id().', channels_id: subsChannels }
  })
  .done(function( msg ) {
    jQuery(".smpush-push-subscriptions-button").attr("disabled","disabled");
    jQuery(".smpush-push-subscriptions-button").html("'.self::$apisetting['desktop_modal_saved_text'].'");
    smpush_debug("Data Sent");
  });
}

function smpush_endpoint_unsubscribe(subscriptionId) {
  jQuery.ajax({
    method: "POST",
    url: "'.get_bloginfo('url') .'/index.php?smpushcontrol=deletetoken",
    data: { device_token: subscriptionId, device_type: devicetype}
  })
  .done(function( msg ) {
    smpush_debug("Data Sent");
    smpush_setCookie("smpush_linked_user", "false", -1);
    smpush_setCookie("smpush_safari_device_token", "false", -1);
    smpush_setCookie("smpush_device_token", "false", -1);
    smpush_setCookie("smpush_desktop_request", "false", -1);
  });
}

function smpush_bootstrap_init(){
  var pushSupported = false;
  
  if("safari" in window && "pushNotification" in window.safari){
    pushSupported = true;
  }
  if (typeof(ServiceWorkerRegistration) != "undefined" && ("showNotification" in ServiceWorkerRegistration.prototype)) {
    pushSupported = true;
  }
  
  if(! pushSupported){
    console.log("Browser not support push notification");
    return;
  }
  
  if(smpush_getCookie("smpush_desktop_request") != "true"){
    if("'.self::$apisetting['desktop_modal'].'" == "1"){
      swal({
        title: "'.addslashes(self::$apisetting['desktop_modal_title']).'",
        text: \''.addslashes(self::$apisetting['desktop_modal_message']).'<br /><br /><button class="smpush-push-permission-button" disabled>'.addslashes(self::$apisetting['desktop_btn_subs_text']).'</button>\',
        html: true,
        showConfirmButton: false,
        allowOutsideClick: true,
        showCancelButton: true,
        cancelButtonText: "'.addslashes(self::$apisetting['desktop_modal_cancel_text']).'",
      });
      jQuery(".showSweetAlert button.cancel").click(function(){
        smpush_setCookie("smpush_desktop_request", "true", 3);
      });
    }
    else{
      jQuery("body").append("<button class=\"smpush-push-permission-button\" style=\"display:none\" disabled>'.addslashes(self::$apisetting['desktop_btn_subs_text']).'</button>");
    }
  }
}

function smpush_link_user() {
  if(jQuery(".smpush-push-subscriptions-button").length > 0 && smpush_getCookie("smpush_linked_user") == ""){
    jQuery(".smpush-push-subscriptions-button").trigger("click");
    smpush_setCookie("smpush_linked_user", "true", 30);
  }
}

function smpush_setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function smpush_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(";");
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==" "){
          c = c.substring(1);
        }
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}
';
  }
  
  private static function chrome($type) {
    $output = '
<link rel="manifest" href="'.get_bloginfo('url') .'/?smpushprofile=manifest">
<script>
"use strict";

var smpush_isPushEnabled = false;

if("'.$type.'" == "chrome"){
  var devicetype = "chrome";
}
else{
  var devicetype = "firefox";
}

function smpush_endpointWorkaround(endpoint){
	var device_id = "";
	if(endpoint.indexOf("mozilla") > -1){
        device_id = endpoint.split("/")[endpoint.split("/").length-1]; 
    }
	else if(endpoint.indexOf("send/") > -1){
		device_id = endpoint.slice(endpoint.search("send/")+5);
	}
    else{
      console.log(endpoint);
      console.log("error while getting device_id from endpoint");
      alert("error while getting device_id from endpoint");
      window.close();
    }
    console.log(device_id);
	return device_id;
}

function smpush_sendSubscriptionToServer(subscription) {
  var subscriptionId = smpush_endpointWorkaround(subscription.endpoint);
  smpush_debug(subscriptionId);
  smpush_endpoint_subscribe(subscriptionId);
}

function smpush_unsubscribe() {
  smpush_setCookie("smpush_desktop_request", "true", 10);
  var pushButton = jQuery(".smpush-push-permission-button");
  pushButton.attr("disabled","disabled");

  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    serviceWorkerRegistration.pushManager.getSubscription().then(
      function(pushSubscription) {
        if (!pushSubscription) {
          smpush_isPushEnabled = false;
          pushButton.removeAttr("disabled");
          pushButton.html("'.addslashes(self::$apisetting['desktop_btn_subs_text']).'");
          return;
        }
        
        var subscriptionId = smpush_endpointWorkaround(pushSubscription.endpoint);
        smpush_debug(subscriptionId);
        smpush_endpoint_unsubscribe(subscriptionId);

        pushSubscription.unsubscribe().then(function() {
          pushButton.removeAttr("disabled");
          pushButton.html("'.addslashes(self::$apisetting['desktop_btn_subs_text']).'");
          smpush_isPushEnabled = false;
        }).catch(function(e) {
          smpush_debug("Unsubscription error: ", e);
          pushButton.removeAttr("disabled");
        });
      }).catch(function(e) {
        smpush_debug("Error thrown while unsubscribing from push messaging.", e);
      });
  });
}

function smpush_subscribe() {
  var pushButton = jQuery(".smpush-push-permission-button");
  pushButton.attr("disabled","disabled");

  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})
      .then(function(subscription) {
        smpush_isPushEnabled = true;
        pushButton.html("'.addslashes(self::$apisetting['desktop_btn_unsubs_text']).'");
        pushButton.removeAttr("disabled");
        return smpush_sendSubscriptionToServer(subscription);
      })
      .catch(function(e) {
        if (Notification.permission === "denied") {
          smpush_debug("Permission for Notifications was denied");
          pushButton.attr("disabled","disabled");
          smpush_endpoint_unsubscribe(smpush_getCookie("smpush_device_token"));
        } else {
          smpush_debug(e);
          if(smpush_getCookie("smart_push_smio_allow_before") == ""){
            smpush_setCookie("smart_push_smio_allow_before", "true", 1);
            smpush_subscribe();
          }
          pushButton.html("'.addslashes(self::$apisetting['desktop_btn_subs_text']).'");
          pushButton.removeAttr("disabled");
        }
      });
  });
}

function smpush_initialiseState() {
  if (!("showNotification" in ServiceWorkerRegistration.prototype)) {
    smpush_debug("Notifications aren\'t supported.");
    return;
  }

  if (Notification.permission === "denied") {
    smpush_debug("The user has blocked notifications.");
    smpush_endpoint_unsubscribe(smpush_getCookie("smpush_device_token"));
    return;
  }

  if (!("PushManager" in window)) {
    smpush_debug("Push messaging isn\'t supported.");
    return;
  }

  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    serviceWorkerRegistration.pushManager.getSubscription()
      .then(function(subscription) {
        var pushButton = jQuery(".smpush-push-permission-button");
        pushButton.removeAttr("disabled");

        if (!subscription) {
          if("'.self::$apisetting['desktop_modal'].'" == "0"){
            document.getElementsByClassName("smpush-push-permission-button")[0].click();
          }
          return;
        }

        pushButton.html("'.addslashes(self::$apisetting['desktop_btn_unsubs_text']).'");
        smpush_isPushEnabled = true;
        
        smpush_link_user();
      })
      .catch(function(err) {
        smpush_debug("Error during getSubscription()", err);
      });
  });
}

window.addEventListener("load", function() {
  smpush_bootstrap_init();
  
  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register("'.get_bloginfo('url') .'/?smpushprofile=service_worker").then(smpush_initialiseState);
  } else {
    smpush_debug("Service workers aren\'t supported in this browser.");
  }
  
  if(jQuery(".smpush-push-permission-button").length < 1){
    return false;
  }
  
  var pushButton = jQuery(".smpush-push-permission-button");
  
  pushButton.click(function() {
    if (smpush_isPushEnabled) {
      smpush_unsubscribe();
    } else {
      smpush_subscribe();
    }
  });
  
  jQuery(".smpush-push-subscriptions-button").click(function() {
    smpush_subscribe();
  });
  
});
';
    $output .= self::bootstrap();
    $output .= '</script>';
    echo preg_replace('/\s+/', ' ', $output);
  }
  
  public static function start_all_lisenter() {
    if(self::$apisetting['desktop_logged_only'] == 1 && !is_user_logged_in()){
      return;
    }
    include(smpush_dir.'/class.browser.detect.php');
    $detector = new smpush_Browser();
    $detector->Browser();
    
    switch ($detector->getBrowser()){
      case 'Chrome':
        if($detector->getVersion() >= 42 && self::$apisetting['desktop_status'] == 1 && self::$apisetting['desktop_chrome_status'] == 1){
          self::chrome('chrome');
        }
        break;
      case 'Firefox':
        if($detector->getVersion() >= 44 && self::$apisetting['desktop_status'] == 1 && self::$apisetting['desktop_firefox_status'] == 1){
          self::chrome('firefox');
        }
        break;
      case 'Safari':
        if($detector->getVersion() >= 7 && self::$apisetting['desktop_status'] == 1 && self::$apisetting['desktop_safari_status'] == 1){
          self::safari();
        }
        break;
    }
  }
  
}