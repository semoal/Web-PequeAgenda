<?php

class smpush_localization {

  public static function load_textdomain() {
    load_plugin_textdomain('smpush-plugin-lang', false, basename(smpush_dir).'/language');
  }
  
  public static function javascript() {
    wp_localize_script('smpush-mainscript', 'smpush_jslang', array(
    'event_no_post' => __('Please create at least one post of this post type to enable creating a push event for it', 'smpush-plugin-lang'),
    'applytoall' => __('Action will be applied to all results, continue?', 'smpush-plugin-lang'),
    'deleteconfirm' => __('Are you sure you want to continue?', 'smpush-plugin-lang'),
    'savechangesconfirm' => __('Do you want to save current changes?', 'smpush-plugin-lang'),
    'no_tokens_msg' => __('There is no tokens accept your choices', 'smpush-plugin-lang'),
    'start_queuing' => __('Start queuing', 'smpush-plugin-lang'),
    'token_in_queue' => __('token in the queue table', 'smpush-plugin-lang'),
    'escaped_reconnect' => __('Escaped and try reconnect...', 'smpush-plugin-lang'),
    'completed' => __('Completed', 'smpush-plugin-lang'),
    'message_queuing_completed' => __('message queuing completed and start now in sending process', 'smpush-plugin-lang'),
    'message_queuing_scheduling' => __('message queued successfully for scheduling send', 'smpush-plugin-lang'),
    'exit_and_back' => __('Exit and return back', 'smpush-plugin-lang'),
    'error_refresh' => __('Error ocurred refresh the page', 'smpush-plugin-lang'),
    'start_feedback' => __('Start feedback service', 'smpush-plugin-lang'),
    ));
  }

}
