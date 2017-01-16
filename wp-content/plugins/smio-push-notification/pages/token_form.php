<form action="<?php echo $pageurl;?>&noheader=1" method="post" id="smpush_jform" class="validate">
<input type="hidden" name="id" value="<?php echo $token['id'];?>">
   <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content" class="edit-form-section">
         <div id="namediv" class="stuffbox">
            <h3><label><?php echo ($token['id']==0)? __('Add New Device', 'smpush-plugin-lang') : __('Edit Device Info', 'smpush-plugin-lang') ;?></label></h3>
            <div class="inside">
               <table class="form-table">
                <tbody>
                  <tr valign="top" class="form-required">
                     <td class="first"><?php echo __('Device Token', 'smpush-plugin-lang')?></td>
                     <td>
                     <textarea name="device_token" rows="5" cols="40" aria-required="true"><?php echo $token['device_token'];?></textarea>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Information', 'smpush-plugin-lang')?></td>
                     <td>
                     <textarea name="information" rows="5" cols="40"><?php echo $token['information'];?></textarea>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Type', 'smpush-plugin-lang')?></td>
                     <td>
                     <select name="device_type">
                        <option value="<?php echo $types_name->ios_name;?>">iOS</option>
                        <option value="<?php echo $types_name->android_name;?>" <?php if($token['device_type'] == $types_name->android_name){?>selected="selected"<?php }?>>Android</option>
                        <option value="<?php echo $types_name->wp_name;?>" <?php if($token['device_type'] == $types_name->wp_name){?>selected="selected"<?php }?>>Windows Phone</option>
                        <option value="<?php echo $types_name->wp10_name;?>" <?php if($token['device_type'] == $types_name->wp10_name){?>selected="selected"<?php }?>>Windows 10</option>
                        <option value="<?php echo $types_name->bb_name;?>" <?php if($token['device_type'] == $types_name->bb_name){?>selected="selected"<?php }?>>BlackBerry</option>
                        <option value="<?php echo $types_name->chrome_name;?>" <?php if($token['device_type'] == $types_name->chrome_name){?>selected="selected"<?php }?>>Chrome</option>
                        <option value="<?php echo $types_name->safari_name;?>" <?php if($token['device_type'] == $types_name->safari_name){?>selected="selected"<?php }?>>Safari</option>
                        <option value="<?php echo $types_name->firefox_name;?>" <?php if($token['device_type'] == $types_name->firefox_name){?>selected="selected"<?php }?>>Firefox</option>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Channels', 'smpush-plugin-lang')?></td>
                     <td>
                     <select name="channels[]" multiple>
                     <?php foreach($channels as $channel){?>
                        <option value="<?php echo $channel->id;?>" <?php if(in_array($channel->id, $token['channels'])){?>selected="selected"<?php }?>><?php echo $channel->title;?></option>
                     <?php }?>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Latidude Point', 'smpush-plugin-lang')?></td>
                     <td><input name="latidude" value="<?php echo $token['latidude'];?>" type="text" size="40"></td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Longitude Point', 'smpush-plugin-lang')?></td>
                     <td><input name="longitude" value="<?php echo $token['longitude'];?>" type="text" size="40"></td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Active', 'smpush-plugin-lang')?></td>
                     <td>
                     <select name="active">
                        <option value="1"><?php echo __('Yes', 'smpush-plugin-lang')?></option>
                        <option value="0" <?php if($token['active'] == 0){?>selected="selected"<?php }?>><?php echo __('No', 'smpush-plugin-lang')?></option>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                    <td colspan="2"><input type="submit" name="submit" id="smio-submit" class="button button-primary" style="width: 120px;" value="<?php echo __('Save Changes', 'smpush-plugin-lang')?>">
                    <img src="<?php echo smpush_imgpath;?>/wpspin_light.gif" class="smpush_process" alt="" /></td>
                 </tr>
                </tbody>
              </table>
            </div>
         </div>
      </div>
   </div>
</form>