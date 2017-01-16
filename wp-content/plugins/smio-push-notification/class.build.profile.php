<?php

class smpush_build_profile extends smpush_controller {
  protected static $platform;


  public function __construct($method) {
    parent::__construct();
    
    include_once(smpush_dir.'/class.browser.detect.php');
    $detector = new smpush_Browser();
    $detector->Browser();
    switch ($detector->getBrowser()){
      case 'Chrome':
        self::$platform = 'chrome';
        break;
      case 'Firefox':
        self::$platform = 'firefox';
        break;
    }
    self::$method();
    die();
  }

  private static function manifest() {
    header('Content-Type: application/json');
    $json = array();
    $json['name'] = get_bloginfo('name');
    $json['short_name'] = get_bloginfo('name');
    $json['icons'][0] = array(
    'src' => self::$apisetting['desktop_deficon'],
    'sizes' => '192x192',
    );
    $json['start_url'] = '/index.html?homescreen=1';
    $json['display'] = 'standalone';
    $json['gcm_sender_id'] = self::$apisetting['chrome_projectid'];
    $json['//'] = 'gcm_user_visible_only is only needed until Chrome 44 is in stable ';
    $json['gcm_user_visible_only'] = true;
    echo json_encode($json);
  }
  
  private static function service_worker() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header('Content-Type: application/javascript');
echo '

"use strict";

function getDeviceID(endpoint){
	var device_id = "";
	if(endpoint.indexOf("mozilla") > -1){
        device_id = endpoint.split("/")[endpoint.split("/").length-1]; 
    }
	else{
		device_id = endpoint.slice(endpoint.search("send/")+5);
	}
    console.log(endpoint);
    console.log(device_id);
	return device_id;
}

function handle_notification(t, n){
    return self.registration.showNotification(t, n);
}

self.addEventListener("push", function(event) {
  console.log("Received a push message");
  var title = "'.get_bloginfo('name').'";
  var message = "";
  var icon = "'.self::$apisetting['desktop_deficon'].'";
  var notificationTag = "/";
  
  event.waitUntil(self.registration.pushManager.getSubscription().then(function(o) {
    fetch("'.get_bloginfo('url').'/index.php?smpushcontrol=get_archive&orderby=date&order=desc&platform='.self::$platform.'&deviceID="+getDeviceID(o.endpoint),{headers:{"Cache-Control": "no-store, no-cache, must-revalidate, max-age=0"}}
    ).then(function(response) {
      if (response.status !== 200) {
        console.log("Looks like there was a problem. Status Code: " + response.status);
        throw new Error();
      }
      return response.json().then(function(json) {
		  var nlist=[];
		  var notificationcontent="";
		for(var i=0;i<=json["result"].length-1;i++){
			notificationcontent = {
				body: (json["result"][i]["message"] == "")? message : json["result"][i]["message"],
				tag: (json["result"][i]["link"] == "")? notificationTag : json["result"][i]["link"],
				icon: (json["result"][i]["icon"] == "")? icon : json["result"][i]["icon"],
                requireInteraction: (json["result"][i]["requireInteraction"] == "")? false : true
			};
			nlist.push(handle_notification(json["result"][i]["title"], notificationcontent));
		}
		return Promise.all(nlist);
      });
    })
    })
  );
});

self.addEventListener("notificationclick", function (event) {
  event.notification.close();
  if(event.notification.tag == ""){
    return;
  }
  event.waitUntil(clients.matchAll({
    type: "window"
  }).then(function (clientList) {
    for (var i = 0; i < clientList.length; i++) {
      var client = clientList[i];
      if (client.url === event.notification.tag && "focus" in client) {
        return client.focus();
      }
    }
    if (clients.openWindow) {
      return clients.openWindow(event.notification.tag);
    }
  }));
});

';
  }
  
}