<div class="wrap" id="smpush-dashboard">
   <div id="smpush-icon-push" class="icon32"><br></div>
   <h2><?php echo get_admin_page_title();?>
     <a href="<?php echo admin_url();?>admin.php?page=smpush_active_tokens&noheader=1" data-confirm="<?php echo __('Are you sure you want to activate all invalid device tokens', 'smpush-plugin-lang')?>?" class="smio-delete add-new-h2"><?php echo __('Active All Tokens', 'smpush-plugin-lang')?></a>
     <a href="javascript:" class="add-new-h2" onclick="smpushResetHistoryTables()"><?php echo __('Reset Table Views', 'smpush-plugin-lang')?></a>
   </h2>
   <form action="<?php echo $page_url;?>" method="post" id="smpush_histform">
      <input type="hidden" name="latidude" id="smio_latidude" />
      <input type="hidden" name="longitude" id="smio_longitude" />
      <div id="col-container">
         <div id="col-left" style="width: 60%">
            <div class="metabox-holder" data-smpush-counter="1">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                        <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('Message', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Message', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <textarea name="message" cols="50" rows="10" id="smpush-message" class="large-text"><?php echo self::loadHistory('message')?></textarea>
                                       <p class="description"><?php echo __('Reference for unicode smileys codes', 'smpush-plugin-lang')?> <a href="http://apps.timwhitlock.info/emoji/tables/unicode" target="_blank"><?php echo __('click here', 'smpush-plugin-lang')?></a></p>
                                    </td>
                                 </tr>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Expire Time', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="expire" value="<?php echo self::loadHistory('expire')?>" type="number" size="10" step="1" /> <?php echo __('Hour', 'smpush-plugin-lang')?>
                                       <p class="description"><?php echo __('Time in hours from now to keep the message alive', 'smpush-plugin-lang')?></p>
                                       <p class="description"><?php echo __('Leave it empty to set to the longest default time', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="metabox-holder" data-smpush-counter="2">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('GEO-fence settings', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('GPS Last Update', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="gps_expire" value="<?php $gps_expire = self::loadHistory('gps_expire');echo (empty($gps_expire))? '1' : self::loadHistory('gps_expire');?>" type="number" size="10" step="1" /> <?php echo __('Hour', 'smpush-plugin-lang')?>
                                       <p class="description"><?php echo __('Set its value to 0 for ignoring the last update time', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top">
                                    <td colspan="2">
                                       <div id="smio_gmap_search">
                                          <input id="smio_gmap_address" class="smio_gmap_input" type="text" placeholder="<?php echo __('Enter the search address...', 'smpush-plugin-lang')?>" />
                                          <input name="radius" id="smio_gmap_radius" class="smio_gmap_input" type="number" step="1" placeholder="<?php echo __('Radius in miles', 'smpush-plugin-lang')?>" style="width:150px" />
                                       </div>
                                       <div id="smio-gmap"></div>
                                       <br /><a href="<?php echo admin_url();?>admin.php?page=smpush_realtime_gps&noheader=1&width=800&height=700" class="button button-primary thickbox"><?php echo __('Watch Real-time GPS', 'smpush-plugin-lang')?></a>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
           <div id="smpush-calculate-dashboard" class="metabox-holder" data-smpush-counter="3">
               <div class="postbox-container" style="width:100%;">
                  <div class="postbox">
                    <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                     <table class="form-table" style="margin-top: 0;">
                        <tbody>
                           <tr valign="top">
                              <td>
                                <h4 class="heading">iOS</h4>
                                <p class="nothing"><span id="smpush-calculate-span-ios">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                              <td>
                                <h4 class="heading">Android</h4>
                                <p class="nothing"><span id="smpush-calculate-span-android">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                              <td>
                                <h4 class="heading">Chrome</h4>
                                <p class="nothing"><span id="smpush-calculate-span-chrome">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                              <td>
                                <h4 class="heading">Safari</h4>
                                <p class="nothing"><span id="smpush-calculate-span-safari">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                              <td rowspan="2">
                                 <input type="button" id="smpush-calculate-btn" class="button" value="<?php echo __('Calculate Devices', 'smpush-plugin-lang')?>">
                                 <img src="<?php echo smpush_imgpath;?>/wpspin_light.gif" class="smpush_calculate_process" alt="" />
                              </td>
                           </tr>
                           <tr valign="top">
                             <td>
                                <h4 class="heading">Firefox</h4>
                                <p class="nothing"><span id="smpush-calculate-span-firefox">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                              <td>
                                <h4 class="heading">Windows Phone</h4>
                                <p class="nothing"><span id="smpush-calculate-span-wp">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                              <td>
                                <h4 class="heading">BlackBerry</h4>
                                <p class="nothing"><span id="smpush-calculate-span-bb">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                              <td>
                                <h4 class="heading">Windows 10</h4>
                                <p class="nothing"><span id="smpush-calculate-span-wp10">0</span> <?php echo __('Device', 'smpush-plugin-lang')?></p>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
            <div class="metabox-holder" data-smpush-counter="4">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('Send Settings', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Send Time', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <div id="timestampdiv"><?php self::touch_time(); ?></div>
                                       <p class="description"><?php echo __('You must first add a cron job in your Cpanel for the scheduled sending, A tutorial', 'smpush-plugin-lang')?> <a href="https://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank"><?php echo __('here', 'smpush-plugin-lang')?></a></p>
                                    </td>
                                 </tr>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Device type', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="type[]" multiple="multiple" class="smpush_select2" style="width:100%;display:none">
                                          <option value="ios" <?php $chanhistory=self::loadHistory('type');if(!empty($chanhistory)){if(in_array('ios', $chanhistory)){echo 'selected="selected"';}}?>>iOS</option>
                                          <option value="android" <?php if(!empty($chanhistory)){if(in_array('android', $chanhistory)){echo 'selected="selected"';}}?>>Android</option>
                                          <option value="wp" <?php if(!empty($chanhistory)){if(in_array('wp', $chanhistory)){echo 'selected="selected"';}}?>>Windows Phone 8</option>
                                          <option value="wp10" <?php if(!empty($chanhistory)){if(in_array('wp10', $chanhistory)){echo 'selected="selected"';}}?>>Windows 10</option>
                                          <option value="bb" <?php if(!empty($chanhistory)){if(in_array('bb', $chanhistory)){echo 'selected="selected"';}}?>>BlackBerry</option>
                                          <option value="chrome" <?php if(!empty($chanhistory)){if(in_array('chrome', $chanhistory)){echo 'selected="selected"';}}?>>Chrome</option>
                                          <option value="safari" <?php if(!empty($chanhistory)){if(in_array('safari', $chanhistory)){echo 'selected="selected"';}}?>>Safari</option>
                                          <option value="firefox" <?php if(!empty($chanhistory)){if(in_array('firefox', $chanhistory)){echo 'selected="selected"';}}?>>Firefox</option>
                                       </select>
                                    </td>
                                 </tr>
                                 <?php if($params['dbtype'] == 'localhost'){?>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('In channels (AND)', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="inchannels_and[]" multiple="multiple" class="smpush_select2" style="width:100%;display:none">
                                          <?php $chanhistory=self::loadHistory('inchannels_and');foreach($params['channels'] AS $channel){?>
                                          <option value="<?php echo $channel->id;?>" <?php if(!empty($chanhistory)){if(in_array($channel->id, $chanhistory)){echo 'selected="selected"';}}?>><?php echo $channel->title;?> (<?php echo $channel->count;?>)</option>
                                          <?php }?>
                                       </select>
                                       <p class="description"><?php echo __('Users subscribed in channels with AND relation', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('In channels (OR)', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="inchannels_or[]" multiple="multiple" class="smpush_select2" style="width:100%;display:none">
                                          <?php $chanhistory=self::loadHistory('inchannels_or');foreach($params['channels'] AS $channel){?>
                                          <option value="<?php echo $channel->id;?>" <?php if(!empty($chanhistory)){if(in_array($channel->id, $chanhistory)){echo 'selected="selected"';}}?>><?php echo $channel->title;?> (<?php echo $channel->count;?>)</option>
                                          <?php }?>
                                       </select>
                                       <p class="description"><?php echo __('Users subscribed in channels with OR relation', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Not in channels (AND)', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="notchannels_and[]" multiple="multiple" class="smpush_select2" style="width:100%;display:none">
                                          <?php $chanhistory=self::loadHistory('notchannels_and');foreach($params['channels'] AS $channel){?>
                                          <option value="<?php echo $channel->id;?>" <?php if(!empty($chanhistory)){if(in_array($channel->id, $chanhistory)){echo 'selected="selected"';}}?>><?php echo $channel->title;?> (<?php echo $channel->count;?>)</option>
                                          <?php }?>
                                       </select>
                                       <p class="description"><?php echo __('Users not subscribed in channels with AND relation', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Not in channels (OR)', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="notchannels_or[]" multiple="multiple" class="smpush_select2" style="width:100%;display:none">
                                          <?php $chanhistory=self::loadHistory('notchannels_or');foreach($params['channels'] AS $channel){?>
                                          <option value="<?php echo $channel->id;?>" <?php if(!empty($chanhistory)){if(in_array($channel->id, $chanhistory)){echo 'selected="selected"';}}?>><?php echo $channel->title;?> (<?php echo $channel->count;?>)</option>
                                          <?php }?>
                                       </select>
                                       <p class="description"><?php echo __('Users not subscribed in channels with OR relation', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <?php }?>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Feedback Service', 'smpush-plugin-lang')?></td>
                                    <td><label><input name="feedback" type="checkbox" <?php if(self::loadHistory('feedback') != ''){echo 'checked="checked"';}?> /> <?php echo __('Enable feedback will find and deactivate the invalid devices tokens', 'smpush-plugin-lang')?></label></td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="metabox-holder">
               <div class="postbox-container" style="width:100%;">
                  <div class="postbox">
                     <table class="form-table" style="margin-top: 0;">
                        <tbody>
                           <tr valign="top">
                              <td>
                                 <input type="submit" name="sendnow" class="button button-primary" value="<?php echo __('Start Sending Now', 'smpush-plugin-lang')?>">
                                 <input type="submit" name="cronsend" class="button button-primary" value="<?php echo __('Automatic Scheduled Sending', 'smpush-plugin-lang')?>">
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
         <div id="col-right" style="width:39%">
           <div class="metabox-holder" data-smpush-counter="6">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('Desktop Adjustments', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Link To Open', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="desktop_link" type="url" size="30" value="<?php echo self::loadHistory('desktop_link')?>" />
                                       <p class="description"><?php echo __('Open link when user clicks on notification message', 'smpush-plugin-lang')?></p>
                                       <p class="description"><?php echo __('Leave it empty to make the notification body unclickable', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Title', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="desktop_title" type="text" size="30" value="<?php echo self::loadHistory('desktop_title')?>" />
                                    </td>
                                 </tr>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Icon', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input class="smpush_upload_field_deskicon" type="url" size="20" name="desktop_icon" value="<?php echo self::loadHistory('desktop_icon'); ?>" />
                                        <input class="smpush_upload_file_btn button action" data-container="smpush_upload_field_deskicon" type="button" value="<?php echo __('Select File', 'smpush-plugin-lang')?>" />
                                        <p class="description"><?php echo __('Choose an icon in a standard size 192x192 px', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="metabox-holder" data-smpush-counter="7">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('Message Payload', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Type', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="extra_type" class="smpush-payload">
                                          <option value="multi"><?php echo __('Multi Values', 'smpush-plugin-lang')?></option>
                                          <option value="normal" <?php if(self::loadHistory('extra_type') == 'normal'){echo 'selected="selected"';}?>><?php echo __('Normal Text', 'smpush-plugin-lang')?></option>
                                          <option value="json" <?php if(self::loadHistory('extra_type') == 'json'){echo 'selected="selected"';}?>><?php echo __('JSON', 'smpush-plugin-lang')?></option>
                                       </select>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="smpush-payload-multi" <?php if(self::loadHistory('extra_type') != 'multi' && self::loadHistory('extra_type') != ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="key[]" value="<?php echo self::loadHistory('key', 0)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>" size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('value', 0)?>" name="value[]" type="text" size="20" /><br />
                                       <input name="key[]" value="<?php echo self::loadHistory('key', 1)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('value', 1)?>" name="value[]" type="text" size="20" /><br />
                                       <input name="key[]" value="<?php echo self::loadHistory('key', 2)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('value', 2)?>" name="value[]" type="text" size="20" /><br />
                                       <input name="key[]" value="<?php echo self::loadHistory('key', 3)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('value', 3)?>" name="value[]" type="text" size="20" /><br />
                                       <input name="key[]" value="<?php echo self::loadHistory('key', 4)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('value', 4)?>" name="value[]" type="text" size="20" /><br />
                                       <input name="key[]" value="<?php echo self::loadHistory('key', 5)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('value', 5)?>" name="value[]" type="text" size="20" />
                                       <p class="description"><?php echo __('Keys with empty values will ignore.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="smpush-payload-normal" <?php if(self::loadHistory('extra_type') == 'multi' || self::loadHistory('extra_type') == ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                      <textarea name="extra" class="regular-text" style="width:95%;height:80px"><?php echo self::loadHistory('extra')?></textarea>
                                       <p class="description"><?php echo __('Send with push message as name `relatedvalue`', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="metabox-holder" data-smpush-counter="8">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('Customize Android Payload', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Type', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="and_extra_type" class="and_smpush-payload">
                                          <option value="multi"><?php echo __('Multi Values', 'smpush-plugin-lang')?></option>
                                          <option value="normal" <?php if(self::loadHistory('and_extra_type') == 'normal'){echo 'selected="selected"';}?>><?php echo __('Normal Text', 'smpush-plugin-lang')?></option>
                                          <option value="json" <?php if(self::loadHistory('and_extra_type') == 'json'){echo 'selected="selected"';}?>><?php echo __('JSON', 'smpush-plugin-lang')?></option>
                                       </select>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="and_smpush-payload-multi" <?php if(self::loadHistory('and_extra_type') != 'multi' && self::loadHistory('and_extra_type') != ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 0)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('and_value', 0)?>" name="and_value[]" type="text" size="20" /><br />
                                       <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 1)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('and_value', 1)?>" name="and_value[]" type="text" size="20" /><br />
                                       <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 2)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('and_value', 2)?>" name="and_value[]" type="text" size="20" /><br />
                                       <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 3)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('and_value', 3)?>" name="and_value[]" type="text" size="20" /><br />
                                       <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 4)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('and_value', 4)?>" name="and_value[]" type="text" size="20" /><br />
                                       <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 5)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('and_value', 5)?>" name="and_value[]" type="text" size="20" />
                                       <p class="description"><?php echo __('Keys with empty values will ignore.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="and_smpush-payload-normal" <?php if(self::loadHistory('and_extra_type') == 'multi' || self::loadHistory('and_extra_type') == ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <textarea name="and_extra" class="regular-text" style="width:95%;height:80px"><?php echo self::loadHistory('and_extra')?></textarea>
                                       <p class="description"><?php echo __('Send with push message as name `relatedvalue`', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
           <div class="metabox-holder" data-smpush-counter="9">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('Customize Windows Phone 8 Payload', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Type', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="wp_extra_type" class="wp_smpush-payload">
                                          <option value="multi"><?php echo __('Multi Values', 'smpush-plugin-lang')?></option>
                                          <option value="normal" <?php if(self::loadHistory('wp_extra_type') == 'normal'){echo 'selected="selected"';}?>><?php echo __('Normal Text', 'smpush-plugin-lang')?></option>
                                          <option value="json" <?php if(self::loadHistory('wp_extra_type') == 'json'){echo 'selected="selected"';}?>><?php echo __('JSON', 'smpush-plugin-lang')?></option>
                                       </select>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="wp_smpush-payload-multi" <?php if(self::loadHistory('wp_extra_type') != 'multi' && self::loadHistory('wp_extra_type') != ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="wp_key[]" value="<?php echo self::loadHistory('wp_key', 0)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp_value', 0)?>" name="wp_value[]" type="text" size="20" /><br />
                                       <input name="wp_key[]" value="<?php echo self::loadHistory('wp_key', 1)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp_value', 1)?>" name="wp_value[]" type="text" size="20" /><br />
                                       <input name="wp_key[]" value="<?php echo self::loadHistory('wp_key', 2)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp_value', 2)?>" name="wp_value[]" type="text" size="20" /><br />
                                       <input name="wp_key[]" value="<?php echo self::loadHistory('wp_key', 3)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp_value', 3)?>" name="wp_value[]" type="text" size="20" /><br />
                                       <input name="wp_key[]" value="<?php echo self::loadHistory('wp_key', 4)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp_value', 4)?>" name="wp_value[]" type="text" size="20" /><br />
                                       <input name="wp_key[]" value="<?php echo self::loadHistory('wp_key', 5)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp_value', 5)?>" name="wp_value[]" type="text" size="20" />
                                       <p class="description"><?php echo __('Keys with empty values will ignore.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="wp_smpush-payload-normal" <?php if(self::loadHistory('wp_extra_type') == 'multi' || self::loadHistory('wp_extra_type') == ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <textarea name="wp_extra" class="regular-text" style="width:95%;height:80px"><?php echo self::loadHistory('wp_extra')?></textarea>
                                       <p class="description"><?php echo __('Send with push message as name `relatedvalue`', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
           <div class="metabox-holder" data-smpush-counter="10">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('Customize Windows 10 Payload', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="top">
                                    <td class="first"><?php echo __('Type', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <select name="wp10_extra_type" class="wp10_smpush-payload">
                                          <option value="multi"><?php echo __('Multi Values', 'smpush-plugin-lang')?></option>
                                          <option value="normal" <?php if(self::loadHistory('wp10_extra_type') == 'normal'){echo 'selected="selected"';}?>><?php echo __('Normal Text', 'smpush-plugin-lang')?></option>
                                          <option value="json" <?php if(self::loadHistory('wp10_extra_type') == 'json'){echo 'selected="selected"';}?>><?php echo __('JSON', 'smpush-plugin-lang')?></option>
                                       </select>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="wp10_smpush-payload-multi" <?php if(self::loadHistory('wp10_extra_type') != 'multi' && self::loadHistory('wp10_extra_type') != ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="wp10_key[]" value="<?php echo self::loadHistory('wp10_key', 0)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp10_value', 0)?>" name="wp10_value[]" type="text" size="20" /><br />
                                       <input name="wp10_key[]" value="<?php echo self::loadHistory('wp10_key', 1)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp10_value', 1)?>" name="wp10_value[]" type="text" size="20" /><br />
                                       <input name="wp10_key[]" value="<?php echo self::loadHistory('wp10_key', 2)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp10_value', 2)?>" name="wp10_value[]" type="text" size="20" /><br />
                                       <input name="wp10_key[]" value="<?php echo self::loadHistory('wp10_key', 3)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp10_value', 3)?>" name="wp10_value[]" type="text" size="20" /><br />
                                       <input name="wp10_key[]" value="<?php echo self::loadHistory('wp10_key', 4)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp10_value', 4)?>" name="wp10_value[]" type="text" size="20" /><br />
                                       <input name="wp10_key[]" value="<?php echo self::loadHistory('wp10_key', 5)?>" type="text" placeholder="<?php echo __('key', 'smpush-plugin-lang')?>"  size="10" /> <input placeholder="<?php echo __('value', 'smpush-plugin-lang')?>" value="<?php echo self::loadHistory('wp10_value', 5)?>" name="wp10_value[]" type="text" size="20" />
                                       <p class="description"><?php echo __('Keys with empty values will ignore.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="top" class="wp10_smpush-payload-normal" <?php if(self::loadHistory('wp10_extra_type') == 'multi' || self::loadHistory('wp10_extra_type') == ''){echo 'style="display:none;"';}?>>
                                    <td class="first"><?php echo __('Payload', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <textarea name="wp10_extra" class="regular-text" style="width:95%;height:80px"><?php echo self::loadHistory('wp10_extra')?></textarea>
                                       <p class="description"><?php echo __('Send with push message as name `relatedvalue`', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="middle">
                                    <td class="first">Image</td>
                                    <td>
                                      <input name="wp10_img" type="url" value="<?php echo self::loadHistory('wp10_img')?>" size="35" />
                                       <p class="description"><?php echo __('Image link to appear beside the subject of push message .', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="metabox-holder" data-smpush-counter="11">
               <div class="postbox-container" style="width:100%;">
                  <div class="meta-box-sortables">
                     <div class="postbox">
                       <img src="<?php echo smpush_imgpath; ?>/close.png" class="smpushCloseTB" style="display:none" />
                        <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
                        <h3><label><?php echo __('iOS Adjustments', 'smpush-plugin-lang')?></label></h3>
                        <div class="inside">
                           <table class="form-table">
                              <tbody>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Lock Key', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="ios_slide" type="text" value="<?php echo self::loadHistory('ios_slide')?>" />
                                       <p class="description"><?php echo __('Change (view) sentence in (Slide to view)', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Badge', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="ios_badge" type="text" value="<?php echo self::loadHistory('ios_badge')?>" />
                                       <p class="description"><?php echo __('The number to display as the badge of the application icon.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Sound', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="ios_sound" type="text" value="<?php echo (self::loadHistory('ios_sound') == '')?'default':self::loadHistory('ios_sound');?>" />
                                       <p class="description"><?php echo __('The name of a sound file in the application bundle.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Content Available', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="ios_cavailable" type="text" value="<?php echo self::loadHistory('ios_cavailable')?>" />
                                       <p class="description"><?php echo __('Provide this key with a value of 1 to indicate that new content is available.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                                 <tr valign="middle">
                                    <td class="first"><?php echo __('Launch Image', 'smpush-plugin-lang')?></td>
                                    <td>
                                       <input name="ios_launchimg" type="text" value="<?php echo self::loadHistory('ios_launchimg')?>" />
                                       <p class="description"><?php echo __('The filename of an image file in the application bundle.', 'smpush-plugin-lang')?></p>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="metabox-holder">
               <div class="postbox-container" style="width:100%;">
                  <div class="postbox">
                     <table class="form-table" style="margin-top: 0;">
                        <tbody>
                           <tr valign="top">
                              <td>
                                 <input type="button" id="smpush-save-hisbtn" class="button" value="<?php echo __('Save Current Setting', 'smpush-plugin-lang')?>">
                                 <input type="button" id="smpush-clear-hisbtn" class="button" value="<?php echo __('Clear History', 'smpush-plugin-lang')?>">
                                 <img src="<?php echo smpush_imgpath;?>/wpspin_light.gif" class="smpush_process" alt="" />
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
  smpushHideHistoryTables();
  $(".smpush_select2").select2({tags: true})
 if(typeof postboxes !== 'undefined')
   postboxes.add_postbox_toggles( 'dashboard_page_stats');
});
</script>