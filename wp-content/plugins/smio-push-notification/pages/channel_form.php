<form action="<?php echo $pageurl;?>&noheader=1" method="post" id="smpush_jform" class="validate">
<input type="hidden" name="id" value="<?php echo $channel['id'];?>">
   <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content" class="edit-form-section">
         <div id="namediv" class="stuffbox">
            <h3><label><?php echo (empty($channel['title']))? __('Add New Channel', 'smpush-plugin-lang') : $channel['title'];?></label></h3>
            <div class="inside">
               <table class="form-table">
                <tbody>
                  <tr valign="top" class="form-required">
                     <td class="first"><?php echo __('Title', 'smpush-plugin-lang')?></td>
                     <td>
                     <input name="title" type="text" size="40" value="<?php echo $channel['title'];?>" aria-required="true">
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Description', 'smpush-plugin-lang')?></td>
                     <td>
                     <textarea name="description" rows="5" cols="40"><?php echo $channel['description'];?></textarea>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Privacy', 'smpush-plugin-lang')?></td>
                     <td>
                     <select name="privacy">
                        <option value="0"><?php echo __('Public', 'smpush-plugin-lang')?></option>
                        <option value="1" <?php if($channel['private'] == 1){?>selected="selected"<?php }?>><?php echo __('Private', 'smpush-plugin-lang')?></option>
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