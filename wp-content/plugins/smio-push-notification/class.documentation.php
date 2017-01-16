<?php

class smpush_documentation {

  public function __construct(){}

  public function build(){
    $request = array();
    $request['push'] = array(
    'savetoken' => 'Add or update a device token subscription',
    'deletetoken' => 'Delete a device token subscription permanently',
    'send_notification' => 'Send push notification message',
    'channels_subscribe' => 'Edit the device subscription in channels',
    'device_channels' => 'Get a list of channels and whichever device subscribed',
    'get_archive' => 'Get a list of archived messages',
    'cron_job' => 'Service to start and run a cron job for sending the scheduled messages',
    'get_channels' => 'Get the list of all channels',
    'add_channel' => 'Add new channel',
    'update_channel' => 'Update a channel',
    'delete_channel' => 'Delete a channel',
    );
    foreach($request AS $key=>$value){
      foreach($value AS $model=>$title){
        $api[$model] = $this->$model();
      }
    }
    $document['api'] = $api;
    $document['links'] = $request;
    $document['group'] = array(
    'push' => 'Push Notification'
    );
    return $document;
  }

  public function params(){
    $api['params']['device_token'] = array(
    'description' => 'Device token value get from mobile device API. Called PIN for BlackBerry 10 and Notify URL for Windows Phone 8',
    'type' => 'string',
    'required' => false
    );
    $api['params']['device_type'] = array(
    'description' => 'Support IOS, Android, Windows Phone 8 and Blackberry 10 devices',
    'type' => 'Choose(ios, android, wp, bb)',
    'required' => false
    );
    $api['params']['active'] = array(
    'description' => 'Device susbcription status',
    'type' => 'boolean(1,0) default is 1',
    'required' => false
    );
    $api['params']['channels_id'] = array(
    'description' => 'IDS of channels to subscribe in, seperated by (,) like 1,2,3',
    'type' => 'int',
    'requiredtxt' => 'If there are no channels ID sent, the subscription will go to the default channel'
    );
    $api['params']['latitude'] = array(
    'description' => 'Latitude point for the device GPS location',
    'type' => 'string',
    'required' => false
    );
    $api['params']['longitude'] = array(
    'description' => 'Longitude point for the device GPS location',
    'type' => 'string',
    'required' => false
    );
    return $api;
  }

  public function savetoken(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $hertapi = $this->params();
    $api['params']['device_token'] = $hertapi['params']['device_token'];
    $api['params']['device_type'] = $hertapi['params']['device_type'];
    $api['params']['active'] = $hertapi['params']['active'];
    $api['params']['channels_id'] = $hertapi['params']['channels_id'];
    $api['params']['latitude'] = $hertapi['params']['latitude'];
    $api['params']['longitude'] = $hertapi['params']['longitude'];
    $api['params']['device_token']['required'] = true;
    $api['params']['device_type']['required'] = true;
    $api['params']['device_info'] = array(
    'description' => 'Send the device information like device name, version and model',
    'type' => '(text)',
    'required' => false
    );
    $api['params']['user_id'] = array(
    'description' => 'Wordpress User ID to link with the device token data',
    'type' => 'int',
    'required' => false
    );
    $api['note'] = 'If the device token already exists system will update the device token data with any changes in device_info or user_id parameters';
    $api['example'] = 'savetoken/?{api_key}device_token=84dc67b0cd5915439509ce48830e659d2ee79966ecbb29b14918ff8865229c7b&amp;device_type=ios&amp;channels_id=1,2&amp;device_info=Name:John Adams, Model:, Version:';
    $api['errors'] = array('Connect with remote push database is failed');
    return $api;
  }
  
  public function deletetoken(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $hertapi = $this->params();
    $api['params']['device_token'] = $hertapi['params']['device_token'];
    $api['params']['device_type'] = $hertapi['params']['device_type'];
    $api['params']['device_token']['required'] = false;
    $api['params']['device_type']['required'] = false;
    $api['params']['user_id'] = array(
    'description' => 'User ID that linked with any device information in the database.',
    'type' => '(int)',
    'requiredtxt' => 'Required if you did not send the device token and type'
    );
    $api['example'] = 'deletetoken/?{api_key}device_token=84dc67b0cd5915439509ce48830e659d2ee79966ecbb29b14918ff8865229c7b&amp;device_type=ios';
    return $api;
  }

  public function send_notification(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $hertapi = $this->params();
    $api['params']['device_token'] = $hertapi['params']['device_token'];
    $api['params']['device_type'] = $hertapi['params']['device_type'];
    $api['params']['device_token']['required'] = false;
    $api['params']['device_type']['required'] = false;
    $api['params']['user_id'] = array(
    'description' => 'User ID or IDs linked with a device information saved in the database. Send like this 22 or group of IDs 22,23,24',
    'type' => '(int)',
    'requiredtxt' => 'Required if you did not send the device token and type'
    );
    $api['params']['channel'] = array(
    'description' => 'Send to all users subscribed in specified channels by send its ID like 1 or 1,2,4 or send to all users by sending word `all` in this parameter',
    'type' => 'string',
    'requiredtxt' => 'Required if you did not send the device token and type or user_id'
    );
    $api['params']['sendtime'] = array(
    'description' => 'Send the message in a scheduled time like 01/15/'.date('Y').' 13:30:00',
    'type' => 'datetime(m/d/Y H:i:s)',
    'required' => false
    );
    $api['params']['message'] = array(
    'description' => 'The text message to send',
    'type' => 'text',
    'required' => true
    );
    $api['params']['latitude'] = array(
    'description' => 'Latitude point for the device GPS location filter',
    'type' => 'string',
    'required' => false
    );
    $api['params']['longitude'] = array(
    'description' => 'Longitude point for the device GPS location filter',
    'type' => 'string',
    'required' => false
    );
    $api['params']['radius'] = array(
    'description' => 'Radius size in miles from the GPS latitude and longitude point to draw the selected area to get the devices inside',
    'type' => 'int',
    'required' => false
    );
    $api['params']['gps_expire'] = array(
    'description' => 'Expire time that device last time send its location to server in hours',
    'type' => 'int',
    'required' => false
    );
    $api['params']['desktop_title'] = array(
    'description' => 'Desktop push notification title',
    'type' => 'text',
    'required' => false
    );
    $api['params']['desktop_icon'] = array(
    'description' => 'Desktop push notification icon to appear beside message area',
    'type' => 'URL',
    'required' => false
    );
    $api['params']['desktop_link'] = array(
    'description' => 'Desktop push notification link to open',
    'type' => 'URL',
    'required' => false
    );
    $api['params']['expire'] = array(
    'description' => 'Time in hours from now to keep the message alive',
    'type' => 'string',
    'required' => false
    );
    $api['params']['ios_slide'] = array(
    'description' => 'iOS: Change (view) sentence in (Slide to view)',
    'type' => 'string',
    'required' => false
    );
    $api['params']['ios_badge'] = array(
    'description' => 'iOS: The number to display as the badge of the application icon',
    'type' => 'string',
    'required' => false
    );
    $api['params']['ios_sound'] = array(
    'description' => 'iOS: The name of a sound file in the application bundle',
    'type' => 'string',
    'required' => false
    );
    $api['params']['ios_cavailable'] = array(
    'description' => 'iOS: Provide this key with a value of 1 to indicate that new content is available',
    'type' => 'string',
    'required' => false
    );
    $api['params']['ios_launchimg'] = array(
    'description' => 'iOS: The filename of an image file in the application bundle',
    'type' => 'string',
    'required' => false
    );
    $api['params']['customparams'] = array(
    'description' => 'Send custom parameters with the message in a JSON format. For example: {"param1":"value1","param2":"value2","param3":"value3"}',
    'type' => 'string',
    'required' => false
    );
    $api['params']['android_customparams'] = array(
    'description' => 'Android: Send custom parameters with the message in a JSON format. For example: {"param1":"value1","param2":"value2","param3":"value3"}',
    'type' => 'string',
    'required' => false
    );
    $api['params']['wp_customparams'] = array(
    'description' => 'Windows Phone: Send custom parameters with the message in a JSON format. For example: {"param1":"value1","param2":"value2","param3":"value3"}',
    'type' => 'string',
    'required' => false
    );
    $api['params']['bb_customparams'] = array(
    'description' => 'BlackBerry: Send custom parameters with the message in a JSON format. For example: {"param1":"value1","param2":"value2","param3":"value3"}',
    'type' => 'string',
    'required' => false
    );
    $api['example'] = 'send_notification/?{api_key}user_id=1&amp;message=Test custom push notification message';
    $api['errors'] = array('Did not find data about this user or the user is inactive');
    $api['note'] = 'To use the scheduled sending feature you must first enable the cron-job service, Look <a href="http://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank">here</a> for further information';
    $api['note'] .= '<br />It is recommended to send this request as POST';
    return $api;
  }

  public function channels_subscribe(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $hertapi = $this->savetoken();
    $api['params'] = $hertapi['params'];
    $api['params']['user_id'] = array(
    'description' => 'User ID that linked with any device information in the database.',
    'type' => '(int)',
    'requiredtxt' => 'Required if you did not send the device token and type'
    );
    $api['params']['channels_id']['requiredtxt'] = '';
    $api['params']['channels_id']['required'] = true;
    $api['example'] = 'channels_subscribe/?{api_key}device_token=84dc67b0cd5915439509ce48830e659d2ee79966ecbb29b14918ff8865229c7b&device_type=ios&channels_id=1,2';
    $api['errors'] = array('Connect with remote push database is failed');
    return $api;
  }

  public function device_channels(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $hertapi = $this->params();
    $api['params']['device_token'] = $hertapi['params']['device_token'];
    $api['params']['device_type'] = $hertapi['params']['device_type'];
    $api['params']['latitude'] = $hertapi['params']['latitude'];
    $api['params']['longitude'] = $hertapi['params']['longitude'];
    $api['params']['device_token']['required'] = true;
    $api['params']['device_type']['required'] = true;
    $api['params']['user_id'] = array(
    'description' => 'User ID that linked with any device information in the database.',
    'type' => '(int)',
    'requiredtxt' => 'Required if you did not send the device token and type'
    );
    $hertapi = $this->get_channels();
    $api['order'] = $hertapi['order'];
    $api['example'] = 'device_channels/?{api_key}device_token=84dc67b0cd5915439509ce48830e659d2ee79966ecbb29b14918ff8865229c7b&device_type=ios';
    $api['errors'] = array('Connect with remote push database is failed','No result found');
    return $api;
  }

  public function get_channels(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $api['order']['date'] = array(
    'description' => '',
    'type' => 'ASC',
    'default' => true
    );
    $api['order']['name'] = array(
    'description' => '',
    'type' => '',
    'default' => false
    );
    $api['order']['subscribers'] = array(
    'description' => 'Count of subscribers in the channel',
    'type' => '',
    'default' => false
    );
    $api['example'] = 'get_channels/?{api_key}orderby=date&order=asc';
    $api['errors'] = array('No result found');
    return $api;
  }
  
  public function get_archive(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $api['order']['date'] = array(
    'description' => '',
    'type' => 'DESC',
    'default' => true
    );
    $api['example'] = 'get_archive/?{api_key}orderby=date&order=desc';
    $api['errors'] = array('No result found');
    return $api;
  }
  
  public function add_channel(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $api['params']['title'] = array(
    'description' => '',
    'type' => '(sring)',
    'required' => true
    );
    $api['params']['description'] = array(
    'description' => '',
    'type' => '(sring)',
    'required' => false
    );
    $api['params']['private'] = array(
    'description' => 'The channel privacy. Set 1 to make it private or 0 for public.',
    'type' => '(int) default (0)',
    'required' => true
    );
    $api['params']['unique'] = array(
    'description' => 'Enable this parameter by passing it as 1 to ensure that the channel title is not taken before.',
    'type' => '(int) default (0)',
    'required' => false
    );
    $api['example'] = 'add_channel/?{api_key}title=Games&private=0';
    $api['errors'] = array('This channel name is taken');
    return $api;
  }
  
  public function update_channel(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $api['params']['id'] = array(
    'description' => 'Channel ID to prepare it for updating.',
    'type' => '(int)',
    'required' => true
    );
    $api['params']['title'] = array(
    'description' => '',
    'type' => '(sring)',
    'required' => true
    );
    $api['params']['description'] = array(
    'description' => '',
    'type' => '(sring)',
    'required' => false
    );
    $api['params']['private'] = array(
    'description' => 'The channel privacy. Set 1 to make it private or 0 for public.',
    'type' => '(int) default (0)',
    'required' => true
    );
    $api['params']['unique'] = array(
    'description' => 'Enable this parameter by passing it as 1 to ensure that the channel title is not taken before.',
    'type' => '(int) default (0)',
    'required' => false
    );
    $api['example'] = 'update_channel/?{api_key}id=1&title=Games&private=0';
    $api['errors'] = array('This channel name is taken');
    return $api;
  }
  
  public function delete_channel(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $api['params']['id'] = array(
    'description' => 'Channel ID to prepare it for deleting.',
    'type' => '(int)',
    'required' => true
    );
    $api['example'] = 'delete_channel/?{api_key}id=1';
    return $api;
  }

  public function cron_job(){
    $api = array('params' => array(),'order' => array(),'note' => '','example' => '','errors' => array());
    $api['note'] = 'For how to add a cron job in your Cpanel please visit that tutorial <a href="http://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank">here</a>';
    $api['example'] = 'cron_job/?{api_key}';
    return $api;
  }

}