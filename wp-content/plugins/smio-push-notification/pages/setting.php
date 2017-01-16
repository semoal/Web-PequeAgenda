<div class="wrap">
  <div id="smpush-icon-devsetting" class="icon32"><br></div>
  <h2><?php echo __('Push Notification Settings', 'smpush-plugin-lang')?></h2>

  <div id="col-container" class="smpush-settings-page">
    <form action="<?php echo $page_url; ?>" method="post" id="smpush_jform" class="validate">
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><span><?php echo __('General Settings', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Authentication Key', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="auth_key" type="text" value="<?php echo self::$apisetting['auth_key']; ?>" size="50" class="regular-text">
                        <p class="description"><?php echo __('Send this key with any request with a parameter called <code>auth_key</code> to prevent access to API from outside .', 'smpush-plugin-lang')?></p>
                        <p class="description"><?php echo __('Also you can send this key in the header of each request in a parameter called <code>auth_key</code> for more security .', 'smpush-plugin-lang')?></p>
                        <p class="description"><?php echo __('Leave it empty to disable this feature .', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <?php if (self::$apisetting['complex_auth'] == 1) { ?>
                    <tr valign="top">
                      <td class="first">Complex Authentication</td>
                      <td>
                        <label><input name="complex_auth" type="checkbox" value="1" <?php if (self::$apisetting['complex_auth'] == 1) { ?>checked="checked"<?php } ?>> Put the authentication key into an encrypted string</label>
                        <p class="description">The encrypted string will be in the following format <a href="http://en.wikipedia.org/wiki/MD5" target="_blank">MD5</a>(Date in m/d/y - Your auth key - Time in H:m)</p>
                        <p class="description">For example <a href="http://en.wikipedia.org/wiki/MD5" target="_blank">MD5</a>(<?php echo date('m/d/Y').self::$apisetting['auth_key'].date('H:i'); ?>)</p>
                      </td>
                    </tr>
                    <?php } ?>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('API Base Name', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="push_basename" type="text" value="<?php echo self::$apisetting['push_basename']; ?>" class="regular-text">
                        <p class="description"><span><code><?php echo get_bloginfo('url') ; ?>/</code><abbr>API_BASE_NAME<code>/</code></abbr></span></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Default Connection', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <select name="def_connection" class="postform">
                          <?php foreach ($params AS $connection) { ?>
                            <option value="<?php echo $connection->id; ?>" <?php if ($connection->id == self::$apisetting['def_connection']) { ?>selected=""<?php } ?>><?php echo $connection->title; ?></option>
                          <?php } ?>
                        </select>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Auto Geolocation', 'smpush-plugin-lang')?></td>
                      <td><label><input name="auto_geo" type="checkbox" value="1" <?php if (self::$apisetting['auto_geo'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable auto collecting the device location from its connection point if system does not receive the location parameters (Not 100% Accurate)', 'smpush-plugin-lang')?></label></td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Geolocation Provider', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <select name="geo_provider" onchange="if (this.value == 'db-ip.com' || this.value == 'telize.com') { $('.smio_dbip_apikey').show(); } else { $('.smio_dbip_apikey').hide(); }">
                          <option value="db-ip.com" <?php if (self::$apisetting['geo_provider'] == 'db-ip.com') { ?>selected="selected"<?php } ?>>db-ip.com</option>
                          <option value="telize.com" <?php if (self::$apisetting['geo_provider'] == 'telize.com') { ?>selected="selected"<?php } ?>>telize.com</option>
                          <option value="ip-api.com" <?php if (self::$apisetting['geo_provider'] == 'ip-api.com') { ?>selected="selected"<?php } ?>>ip-api.com [Free]</option>
                        </select>
                      </td>
                    </tr>
                    <tr valign="top" class="smio_dbip_apikey" <?php if (self::$apisetting['geo_provider'] != 'db-ip.com' && self::$apisetting['geo_provider'] != 'telize.com') { ?>style="display:none;"<?php } ?>>
                      <td class="first"><label>db-ip.com <?php echo __('API Key', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="db_ip_apikey" type="text" value="<?php echo self::$apisetting['db_ip_apikey']; ?>" class="regular-text" size="50">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Google Maps API Key', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="gmaps_apikey" type="text" value="<?php echo self::$apisetting['gmaps_apikey']; ?>" class="regular-text" size="50">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Apple API Version', 'smpush-plugin-lang')?></td>
                      <td>
                        <label><input name="apple_api_ver" type="radio" value="http2" <?php if (self::$apisetting['apple_api_ver'] == 'http2') { ?>checked="checked"<?php } ?>> <?php echo __('New Apple API version uses new HTTP/2 protocol [Recommended]', 'smpush-plugin-lang')?></label><br />
                        <label><input name="apple_api_ver" type="radio" value="ssl" <?php if (self::$apisetting['apple_api_ver'] == 'ssl') { ?>checked="checked"<?php } ?>> <?php echo __('Old Apple API version uses SSL connection', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/apple.png" alt="" /> <span><?php echo __('Apple Connection Settings', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td class="first"><?php echo __('Certification Type', 'smpush-plugin-lang')?></td>
                      <td><label><input name="apple_sandbox" type="checkbox" value="1" <?php if (self::$apisetting['apple_sandbox'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable Apple sandbox server for development certification type', 'smpush-plugin-lang')?></label></td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Password Phrase', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="apple_passphrase" type="text" value="<?php echo self::$apisetting['apple_passphrase']; ?>" class="regular-text">
                        <p class="description"><?php echo __('Apple password phrase for sending push notification.', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('App ID', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="apple_appid" type="text" value="<?php echo self::$apisetting['apple_appid']; ?>" class="regular-text">
                        <p class="description"><?php echo __('App ID under App IDs page in Identifiers block.', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Certification .PEM File', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="apple_cert_path" type="text" value="<?php echo self::$apisetting['apple_cert_path']; ?>" size="60" class="regular-text">
                        <input name="apple_cert_upload" type="file">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Message Truncate', 'smpush-plugin-lang')?></td>
                      <td><label><input name="stop_summarize" type="checkbox" value="1" <?php if (self::$apisetting['stop_summarize'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Disable truncate iOS push message if exceeds the allowed payload', 'smpush-plugin-lang')?></label></td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/android.png" alt="" /> <span><?php echo __('Android Connection Settings', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('API Key', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="google_apikey" type="text" value="<?php echo self::$apisetting['google_apikey']; ?>" class="regular-text" size="50">
                        <p class="description"><?php echo __('Google API key for sending Android push notification.', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/wp.png" alt="" /> <span><?php echo __('Windows Phone 8 Settings', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td class="first"><?php echo __('Authenticated', 'smpush-plugin-lang')?></td>
                      <td><label><input name="wp_authed" type="checkbox" value="1" <?php if (self::$apisetting['wp_authed'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Windows Phone 8 authenticated apps have no limit quota for sending daily.', 'smpush-plugin-lang')?></label></td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Certificate File', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="wp_cert" type="file" class="regular-text"><?php if (!empty(self::$apisetting['wp_cert'])): ?> <img title="Uploaded" src="<?php echo smpush_imgpath; ?>/valid.png" alt="" /><?php endif; ?>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Private key', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="wp_pem" type="file" class="regular-text"><?php if (!empty(self::$apisetting['wp_pem'])): ?> <img title="Uploaded" src="<?php echo smpush_imgpath; ?>/valid.png" alt="" /><?php endif; ?>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('CA Info', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="wp_cainfo" type="file" class="regular-text"><?php if (!empty(self::$apisetting['wp_cainfo'])): ?> <img title="Uploaded" src="<?php echo smpush_imgpath; ?>/valid.png" alt="" /><?php endif; ?>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/wp.png" alt="" /> <span><?php echo __('Universal Windows 10 Settings', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Package SID', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="wp10_pack_sid" type="text" size="80" value="<?php echo self::$apisetting['wp10_pack_sid']?>" placeholder="e.g. ms-app://S-1-15-2-2972962901-2322836549-3722629029" class="regular-text">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Client Secret', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="wp10_client_secret" type="text" size="60" value="<?php echo self::$apisetting['wp10_client_secret']?>" placeholder="e.g. Vex8L9WOFZuj95euaLrvSH7XyoDhLJc7" class="regular-text">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/blackberry.png" alt="" /> <span><?php echo __('BlackBerry Connection Settings', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td class="first"><?php echo __('Development Mode', 'smpush-plugin-lang')?></td>
                      <td><label><input name="bb_dev_env" type="checkbox" value="1" <?php if (self::$apisetting['bb_dev_env'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable development mode', 'smpush-plugin-lang')?></label></td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Application ID', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="bb_appid" type="text" value="<?php echo self::$apisetting['bb_appid']; ?>" class="regular-text" size="50">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Password', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="bb_password" type="text" value="<?php echo self::$apisetting['bb_password']; ?>" class="regular-text">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('CPID', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="bb_cpid" type="text" value="<?php echo self::$apisetting['bb_cpid']; ?>" class="regular-text">
                        <p class="description"><?php echo __('Content Provider ID is provided by BlackBerry in the email you received.', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/appcelerator.png" alt="" /> <span><?php echo __('Titanium Compatibility', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="ios_titanium_payload" type="checkbox" value="1" <?php if (self::$apisetting['ios_titanium_payload'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __("Make iOS's payload compatible with Titanium platform", 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="android_titanium_payload" type="checkbox" value="1" <?php if (self::$apisetting['android_titanium_payload'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __("Make Android's payload compatible with Titanium platform", 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/corona.png" alt="" /> <span><?php echo __('Corona Compatibility', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="android_corona_payload" type="checkbox" value="1" <?php if (self::$apisetting['android_corona_payload'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Make the message structure compatible with Corona platform', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/desktop.png" alt="" /> <span><?php echo __('Desktop Notifications', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="desktop_status" type="checkbox" value="1" <?php if (self::$apisetting['desktop_status'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable desktop push notification listeners', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="desktop_debug" type="checkbox" value="1" <?php if (self::$apisetting['desktop_debug'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable desktop push notification debug mode', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="desktop_modal" type="checkbox" value="1" <?php if (self::$apisetting['desktop_modal'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Show modal box to request from visitor to allow notification for your site', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="desktop_logged_only" type="checkbox" value="1" <?php if (self::$apisetting['desktop_logged_only'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable desktop push notification for logged users only', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Modal Head Title', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" name="desktop_modal_title" value="<?php echo self::$apisetting['desktop_modal_title']; ?>" class="regular-text" size="40" />
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Modal Message', 'smpush-plugin-lang')?></td>
                      <td>
                        <textarea name="desktop_modal_message" rows="8" cols="70" class="regular-text"><?php echo self::$apisetting['desktop_modal_message']; ?></textarea>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Subscribe Button Text', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" name="desktop_btn_subs_text" value="<?php echo self::$apisetting['desktop_btn_subs_text']; ?>" class="regular-text" size="40" />
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Unsubscribe Button Text', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" name="desktop_btn_unsubs_text" value="<?php echo self::$apisetting['desktop_btn_unsubs_text']; ?>" class="regular-text" size="40" />
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Ignore Button Text', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" name="desktop_modal_cancel_text" value="<?php echo self::$apisetting['desktop_modal_cancel_text']; ?>" class="regular-text" size="20" />
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Saved Button Text', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" name="desktop_modal_saved_text" value="<?php echo self::$apisetting['desktop_modal_saved_text']; ?>" class="regular-text" size="20" />
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Default Icon', 'smpush-plugin-lang')?></td>
                      <td>
                        <input class="smpush_upload_field_deskicon" type="url" size="50" name="desktop_deficon" value="<?php echo self::$apisetting['desktop_deficon']; ?>" />
                        <input class="smpush_upload_file_btn button action" data-container="smpush_upload_field_deskicon" type="button" value="<?php echo __('Select File', 'smpush-plugin-lang')?>" />
                        <p class="description"><?php echo __('Choose an icon in a standard size 192x192 px', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/chrome.png" alt="" /> <span>Chrome</span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="desktop_chrome_status" type="checkbox" value="1" <?php if (self::$apisetting['desktop_chrome_status'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable desktop push notification listener for Chrome browser', 'smpush-plugin-lang')?></label>
                        <p class="description">
                          <?php echo __('Chrome push notification requires your site working under <code>HTTPS</code> protocol .', 'smpush-plugin-lang')?>
                          <a href="https://www.namecheap.com/security/ssl-certificates/single-domain.aspx?aff=101337" target="_blank"><?php echo __('Buy one for $9 only', 'smpush-plugin-lang')?></a>
                        </p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('API Key', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" name="chrome_apikey" value="<?php echo self::$apisetting['chrome_apikey']; ?>" class="regular-text" size="50" />
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Project Number', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" name="chrome_projectid" value="<?php echo self::$apisetting['chrome_projectid']; ?>" class="regular-text" size="30" />
                        <p class="description"><?php echo __('For how to get API key and project number', 'smpush-plugin-lang')?> <a href="https://smartiolabs.com/blog/61/get-api-key-sender-id-fcm-push-notification-firebase/" target="_blank"><?php echo __('click here', 'smpush-plugin-lang')?></a></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/firefox.png" alt="" /> <span>Firefox</span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="desktop_firefox_status" type="checkbox" value="1" <?php if (self::$apisetting['desktop_firefox_status'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable desktop push notification listener for Firefox browser', 'smpush-plugin-lang')?></label>
                        <p class="description">
                          <?php echo __('Firefox push notification requires your site working under <code>HTTPS</code> protocol .', 'smpush-plugin-lang')?>
                          <a href="https://www.namecheap.com/security/ssl-certificates/single-domain.aspx?aff=101337" target="_blank"><?php echo __('Buy one for $9 only', 'smpush-plugin-lang')?></a>
                        </p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/safari.png" alt="" /> <span>Safari</span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="desktop_safari_status" type="checkbox" value="1" <?php if (self::$apisetting['desktop_safari_status'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable desktop push notification listener for Safari browser', 'smpush-plugin-lang')?></label>
                        <p class="description">
                          <?php echo __('Safari push notification requires your site working under <code>HTTPS</code> protocol .', 'smpush-plugin-lang')?>
                          <a href="https://www.namecheap.com/security/ssl-certificates/single-domain.aspx?aff=101337" target="_blank"><?php echo __('Buy one for $9 only', 'smpush-plugin-lang')?></a>
                        </p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Certification .PEM File', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" size="50" name="safari_cert_path" value="<?php echo self::$apisetting['safari_cert_path']; ?>" />
                        <input type="file" name="safari_cert_upload" />
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Certification .P12 File', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" size="50" name="safari_certp12_path" value="<?php echo self::$apisetting['safari_certp12_path']; ?>" />
                        <input type="file" name="safari_certp12_upload" />
                        <p class="description"><?php echo __('We provide a paid service to generate your certificates for $10 only', 'smpush-plugin-lang')?> <a href="https://smartiolabs.com/support" target="_blank"><?php echo __('request now', 'smpush-plugin-lang')?></a></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><label><?php echo __('Password Phrase', 'smpush-plugin-lang')?></label></td>
                      <td>
                        <input name="safari_passphrase" type="text" value="<?php echo self::$apisetting['safari_passphrase']; ?>" class="regular-text">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Website Push ID', 'smpush-plugin-lang')?></td>
                      <td>
                        <input type="text" size="30" name="safari_web_id" placeholder="e.g. web.com.example.domain" value="<?php echo self::$apisetting['safari_web_id']; ?>" />
                        <p class="description"><?php echo __('The Website Push ID, as specified in your registration with the Member Center.', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td class="first"><?php echo __('Push Icon', 'smpush-plugin-lang')?></td>
                      <td>
                        <input class="smpush_upload_field_safariicon" type="url" size="50" name="safari_icon" value="<?php echo self::$apisetting['safari_icon']; ?>" />
                        <input class="smpush_upload_file_btn button action" data-container="smpush_upload_field_safariicon" type="button" value="<?php echo __('Select File', 'smpush-plugin-lang')?>" />
                        <p class="description"><?php echo __('Choose an icon in a standard size 256x256 px', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/events.png" alt="" /> <span><?php echo __('Push Notification Events', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="e_post_chantocats" type="checkbox" value="1" <?php if (self::$apisetting['e_post_chantocats'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Notify only members which subscribed in a channel name equivalent with the post category name', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="e_appcomment" type="checkbox" value="1" <?php if (self::$apisetting['e_appcomment'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Notify user when administrator approved on his comment', 'smpush-plugin-lang')?></label>
                        <input name="e_appcomment_body" type="text" value='<?php echo self::$apisetting['e_appcomment_body']; ?>' class="regular-text" size="80">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="e_newcomment" type="checkbox" value="1" <?php if (self::$apisetting['e_newcomment'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Notify author when added new comment on his post', 'smpush-plugin-lang')?></label>
                        <input name="e_newcomment_body" type="text" value='<?php echo self::$apisetting['e_newcomment_body']; ?>' class="regular-text" size="80">
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                        <label><input name="e_usercomuser" type="checkbox" value="1" <?php if (self::$apisetting['e_usercomuser'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Notify user when someone comment on his comment', 'smpush-plugin-lang')?></label>
                        <input name="e_usercomuser_body" type="text" value='<?php echo self::$apisetting['e_usercomuser_body']; ?>' class="regular-text" size="80">
                        <br class="clear">
                        <p class="description"><?php echo __('Notice: System will replace {subject},{comment} words with the subject of topic or comment content.', 'smpush-plugin-lang')?></p>
                        <p class="description"><?php echo __('Notice: System will send the topic ID with the push notification message as name `relatedvalue`.', 'smpush-plugin-lang')?></p>
                        <p class="description"><?php echo __('Notice: To use this feature first please enable the cron-job service, Look', 'smpush-plugin-lang')?> <a href="http://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank"><?php echo __('here', 'smpush-plugin-lang')?></a> <?php echo __('for further information', 'smpush-plugin-lang')?></p>                      </td>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="col-left">
        <div id="post-body" class="metabox-holder columns-2">
          <div>
            <div id="namediv" class="stuffbox">
              <h3><label><img src="<?php echo smpush_imgpath; ?>/buddypress.png" alt="" /> <span><?php echo __('BuddyPress Events', 'smpush-plugin-lang')?></span></label></h3>
              <div class="inside">
                <table class="form-table">
                  <tbody>
                    <tr valign="top">
                      <td>
                        <label><input name="bb_notify_friends" type="checkbox" value="1" <?php if (self::$apisetting['bb_notify_friends'] == 1) { ?>checked="checked"<?php } ?>>&nbsp;
                        <?php echo __('Enable user receive push notification for friends component', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td>
                        <label><input name="bb_notify_messages" type="checkbox" value="1" <?php if (self::$apisetting['bb_notify_messages'] == 1) { ?>checked="checked"<?php } ?>>&nbsp;
                        <?php echo __('Enable user receive push notification for messages component', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td>
                        <label><input name="bb_notify_activity" type="checkbox" value="1" <?php if (self::$apisetting['bb_notify_activity'] == 1) { ?>checked="checked"<?php } ?>>&nbsp;
                        <?php echo __('Enable user receive push notification for activity component', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td>
                        <label><input name="bb_notify_activity_admins_only" type="checkbox" value="1" <?php if (self::$apisetting['bb_notify_activity_admins_only'] == 1) { ?>checked="checked"<?php } ?>>&nbsp;
                        <?php echo __('Send push notifications for group activities to administrators only', 'smpush-plugin-lang')?></label>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td>
                        <label><input name="bb_notify_xprofile" type="checkbox" value="1" <?php if (self::$apisetting['bb_notify_xprofile'] == 1) { ?>checked="checked"<?php } ?>>&nbsp;
                        <?php echo __('Enable user receive push notification for xprofile component', 'smpush-plugin-lang')?></label>
                        <p class="description"><?php echo __('Notice: To use this feature first please enable the cron-job service, Look', 'smpush-plugin-lang')?> <a href="https://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank"><?php echo __('here', 'smpush-plugin-lang')?></a> <?php echo __('for further information', 'smpush-plugin-lang')?></p>
                      </td>
                    </tr>
                    <tr valign="top">
                      <td colspan="2">
                          <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo __('Save All Settings', 'smpush-plugin-lang')?>">
                          <img src="<?php echo smpush_imgpath; ?>/wpspin_light.gif" class="smpush_process" alt="" />
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
</div>