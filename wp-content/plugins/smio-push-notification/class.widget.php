<?php

class smpush_widget extends WP_Widget {

  function __construct() {
    parent::__construct(false, __('Push Notification Subscription', 'smpush-plugin-lang'));
  }

  function widget($args, $instance) {
    if($instance['logged_only'] == 1 && !is_user_logged_in()){
      return;
    }
    $enableSaveChannelBTN = false;
    $settings = get_option('smpush_options');
    if($instance['show_channels'] == 1 && is_user_logged_in()){
      global $wpdb;
      $channels = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'push_channels ORDER BY title ASC');
      $subschannels = get_user_meta(get_current_user_id(), 'smpush_subscribed_channels', true);
      if($subschannels !== false){
        $enableSaveChannelBTN = true;
      }
      if(empty($subschannels)){
        $subschannels = array();
      }
      else{
        $subschannels = explode(',', $subschannels);
      }
    }
    if(empty($subschannels)){
      $subschannels = array();
    }
    include(smpush_dir.'/pages/widget.php');
  }

  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['container'] = strip_tags($new_instance['container']);
    $instance['container_class'] = strip_tags($new_instance['container_class']);
    $instance['custom_css'] = strip_tags($new_instance['custom_css']);
    $instance['head_title'] = strip_tags($new_instance['head_title']);
    $instance['message'] = strip_tags($new_instance['message']);
    $instance['save_channels_btn'] = strip_tags($new_instance['save_channels_btn']);
    if(isset($new_instance['show_channels'])){
      $instance['show_channels'] = 1;
    }
    else{
      $instance['show_channels'] = 0;
    }
    if(isset($new_instance['logged_only'])){
      $instance['logged_only'] = 1;
    }
    else{
      $instance['logged_only'] = 0;
    }
    return $instance;
  }

  function form($instance) {
    if (empty($instance)) {
      $instance = array();
    }
    $defaults = array(
    'container' => '',
    'container_class' => '',
    'custom_css' => '',
    'head_title' => __('Get Notified Of New Posts', 'smpush-plugin-lang'),
    'message' => __('Turn on desktop push notification', 'smpush-plugin-lang'),
    'save_channels_btn' => __('Update Subscriptions', 'smpush-plugin-lang'),
    'show_channels' => 0,
    'logged_only' => 0,
    );
    $instance = array_merge($defaults, $instance);
    $container = $instance['container'];
    $container_class = $instance['container_class'];
    $custom_css = $instance['custom_css'];
    $head_title = $instance['head_title'];
    $message = $instance['message'];
    $save_channels_btn = $instance['save_channels_btn'];
    $show_channels = $instance['show_channels'];
    $logged_only = $instance['logged_only'];
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('container'); ?>"><?php echo __('Container Tag', 'smpush-plugin-lang')?>:</label>
      <input class="widefat" type="text" id="<?php echo $this->get_field_id('container'); ?>" placeholder="e.g. aside, section or div" name="<?php echo $this->get_field_name('container'); ?>" value="<?php echo esc_attr($container); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('container_class'); ?>"><?php echo __('Container CSS Class Name', 'smpush-plugin-lang')?>:</label>
      <input class="widefat" type="text" id="<?php echo $this->get_field_id('container_class'); ?>" name="<?php echo $this->get_field_name('container_class'); ?>" value="<?php echo esc_attr($container_class); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('head_title'); ?>"><?php echo __('Head Title', 'smpush-plugin-lang')?>:</label>
      <input class="widefat" type="text" id="<?php echo $this->get_field_id('head_title'); ?>" name="<?php echo $this->get_field_name('head_title'); ?>" value="<?php echo esc_attr($head_title); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('message'); ?>"><?php echo __('Message', 'smpush-plugin-lang')?>:</label>
      <input class="widefat" type="text" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>" value="<?php echo esc_attr($message); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('save_channels_btn'); ?>"><?php echo __('Save Channels Button Text', 'smpush-plugin-lang')?>:</label>
      <input class="widefat" type="text" id="<?php echo $this->get_field_id('save_channels_btn'); ?>" name="<?php echo $this->get_field_name('save_channels_btn'); ?>" value="<?php echo esc_attr($save_channels_btn); ?>">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('show_channels'); ?>">
        <input class="widefat" type="checkbox" id="<?php echo $this->get_field_id('show_channels'); ?>" value="1" name="<?php echo $this->get_field_name('show_channels'); ?>" <?php if($show_channels == 1): ?>checked="checked"<?php endif;?> /> <?php echo __('Show channels subscription if user is logged', 'smpush-plugin-lang')?>
      </label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('logged_only'); ?>">
        <input class="widefat" type="checkbox" id="<?php echo $this->get_field_id('logged_only'); ?>" value="1" name="<?php echo $this->get_field_name('logged_only'); ?>" <?php if($logged_only == 1): ?>checked="checked"<?php endif;?> /> <?php echo __('Show this widget for logged users only', 'smpush-plugin-lang')?>
      </label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('custom_css'); ?>"><?php echo __('Custom CSS', 'smpush-plugin-lang')?>:</label>
      <textarea class="widefat" rows="8" id="<?php echo $this->get_field_id('custom_css'); ?>" placeholder="<?php echo __('Write CSS code to customise this widget design', 'smpush-plugin-lang')?>" name="<?php echo $this->get_field_name('custom_css'); ?>"><?php echo esc_attr($custom_css)?></textarea>
    </p>
    <?php
  }

}
