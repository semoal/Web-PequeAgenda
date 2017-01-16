<form action="<?php echo $pageurl;?>&noheader=1" method="post" id="smpush_jform" class="validate">
<input type="hidden" name="id" value="<?php echo $event['id'];?>">
   <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content" class="edit-form-section">
         <div id="namediv" class="stuffbox">
            <h3><label><?php echo (empty($event['title']))? __('Add New Event', 'smpush-plugin-lang') : $event['title'];?></label></h3>
            <div class="inside">
               <table class="form-table">
                <tbody>
                  <tr valign="top">
                     <td class="first"><?php echo __('Title', 'smpush-plugin-lang')?></td>
                     <td class="form-required">
                     <input name="title" type="text" size="60" value="<?php echo $event['title'];?>" aria-required="true">
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Event Type', 'smpush-plugin-lang')?></td>
                     <td>
                     <select name="event_type">
                        <option value="publish"><?php echo __('Published for first time', 'smpush-plugin-lang')?></option>
                        <option value="approve" <?php if($event['event_type'] == 'approve'){?>selected="selected"<?php }?>><?php echo __('Get approval to publish', 'smpush-plugin-lang')?></option>
                        <option value="update" <?php if($event['event_type'] == 'update'){?>selected="selected"<?php }?>><?php echo __('Get new changes or updates', 'smpush-plugin-lang')?></option>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Post Type', 'smpush-plugin-lang')?></td>
                     <td class="form-required">
                     <select name="event_post_type" class="smpushPostType" aria-required="true">
                       <option value=""><?php echo __('Select Post Type', 'smpush-plugin-lang')?></option>
                      <?php foreach(get_post_types('', 'names') as $post_type): ?>
                        <option value="<?php echo $post_type?>" <?php if($event['post_type'] == $post_type){?>selected="selected"<?php }?>><?php echo $post_type?></option>
                      <?php endforeach; ?>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Push Message', 'smpush-plugin-lang')?></td>
                     <td>
                       <select id="smpushPostAttri" class="smpushPostAttriSelector" style="width:150px;float:left;margin:0 5px 10px 0">
                          <option value=""><?php echo __('Choose Attribute', 'smpush-plugin-lang')?></option>
                        </select>
                       <select id="smpushPostAttriFormat" style="float:left;margin:0 5px 10px 0">
                          <option value="regular"><?php echo __('Select Format', 'smpush-plugin-lang')?></option>
                          <option value="CapitalizeFirst">CapitalizeFirst</option>
                          <option value="CapitalizeAllFirst">CapitalizeAllFirst</option>
                          <option value="UPPERCASE">UPPERCASE</option>
                          <option value="lowercase">lowercase</option>
                          <option value="datetime">Date Time</option>
                          <option value="date">Date</option>
                          <option value="regular">Regular</option>
                        </select>
                       <select id="smpushPostAttriFunction" style="float:left;margin:0 5px 10px 0">
                          <option value=""><?php echo __('Pass To Function', 'smpush-plugin-lang')?></option>
                          <optgroup label="<?php echo __('Post Functions', 'smpush-plugin-lang')?>">
                            <option value="post_title"><?php echo __('Title', 'smpush-plugin-lang')?></option>
                            <option value="post_permalink"><?php echo __('Permalink', 'smpush-plugin-lang')?></option>
                            <option value="post_date"><?php echo __('Publish Date', 'smpush-plugin-lang')?></option>
                            <option value="post_mod_date"><?php echo __('Last Update Date', 'smpush-plugin-lang')?></option>
                            <option value="post_categories"><?php echo __('Categories', 'smpush-plugin-lang')?></option>
                            <option value="post_tags"><?php echo __('Tags', 'smpush-plugin-lang')?></option>
                          </optgroup>
                          <optgroup label="<?php echo __('User Functions', 'smpush-plugin-lang')?>">
                            <option value="user_title"><?php echo __('Display Name', 'smpush-plugin-lang')?></option>
                            <option value="user_email"><?php echo __('Email', 'smpush-plugin-lang')?></option>
                            <option value="user_name"><?php echo __('Username', 'smpush-plugin-lang')?></option>
                          </optgroup>
                        </select>
                       <input type="text" id="smpushPostAttriDefault" size="15" placeholder="<?php echo __('Default Value', 'smpush-plugin-lang')?>" style="float:left;margin:0 5px 10px 0">
                       <input type="button" class="smpushInsertAtrri button button-primary" style="float:left;margin:0 5px 5px 0" value="<?php echo __('Insert', 'smpush-plugin-lang')?>">
                       <br class="clear">
                       <textarea name="message" rows="6" cols="40" style="width:95%" id="smpushEventMessage" aria-required="true"><?php echo $event['message'];?></textarea>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Ignore Case', 'smpush-plugin-lang')?></td>
                     <td>
                       <label><input name="ignore" type="checkbox" <?php if($event['ignore'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Ignore sending the message if one of its variables is empty or equal zero value .', 'smpush-plugin-lang')?></label>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Conditions', 'smpush-plugin-lang')?></td>
                     <td class="smpushEventConditions">
                       <?php if(!empty($event['conditions'])): foreach($event['conditions']['attri'] as $key => $condition): ?>
                       <p class="smpush-clear">
                          <input name="conditions[attri][]" value="<?php echo $event['conditions']['attri'][$key] ?>" type="text" size="15" style="float:left;margin:0 5px">
                          <input name="conditions[sign][]" value="<?php echo $event['conditions']['sign'][$key] ?>" type="text" size="15" style="float:left;margin:0 5px">
                          <input name="conditions[value][]" value="<?php echo $event['conditions']['value'][$key] ?>" type="text" size="25" style="float:left;margin:0 5px">
                          <input type="button" class="button button-primary" onclick="$(this).closest('p').remove();" style="float:left;margin:0 5px" value="<?php echo __('Remove', 'smpush-plugin-lang')?>">
                       </p>
                       <?php endforeach; endif; ?>
                       <div class="smpush-clear">
                          <select name="conditions[attri][]" class="smpushPostAttriSelector" style="width:150px;float:left;margin:0 5px">
                             <option value=""><?php echo __('Choose Attribute', 'smpush-plugin-lang')?></option>
                           </select>
                         <select name="conditions[sign][]" style="width:120px;float:left;margin:0 5px 5px 0" onchange="smpushUpdateValueField(this)">
                            <option data-placeholder="<?php echo __('Write Value', 'smpush-plugin-lang')?>" value=""><?php echo __('Choose Sign', 'smpush-plugin-lang')?></option>
                             <option data-placeholder="<?php echo __('e.g. 1 or Now() or Date()', 'smpush-plugin-lang')?>">></option>
                             <option data-placeholder="<?php echo __('e.g. 1 or Now() or Date()', 'smpush-plugin-lang')?>">>=</option>
                             <option data-placeholder="<?php echo __('e.g. 1 or Now() or Date()', 'smpush-plugin-lang')?>"><</option>
                             <option data-placeholder="<?php echo __('e.g. 1 or Now() or Date()', 'smpush-plugin-lang')?>"><=</option>
                             <option data-placeholder="<?php echo __('e.g. 1 or Now() or Date()', 'smpush-plugin-lang')?>">=</option>
                             <option data-placeholder="<?php echo __('e.g. 1 or Now() or Date()', 'smpush-plugin-lang')?>">NOT =</option>
                             <option data-placeholder="<?php echo __('e.g. 1 , value , value , 3', 'smpush-plugin-lang')?>">IN</option>
                             <option data-placeholder="<?php echo __('e.g. 1 , value , value , 3', 'smpush-plugin-lang')?>">NOT IN</option>
                           </select>
                         <input name="conditions[value][]" class="smpushPostAttriSelectorValue" type="text" size="25" placeholder="<?php echo __('Write Value', 'smpush-plugin-lang')?>" style="float:left;margin:0 5px">
                          <input type="button" class="button button-primary" onclick="smpushEventAddRow(this)" style="float:left;margin:0 5px" value="<?php echo __('AND', 'smpush-plugin-lang')?>">
                          <input type="button" class="button button-primary" onclick="smpushEventDelRow(this)" style="float:left;margin:0 5px" value="<?php echo __('Remove', 'smpush-plugin-lang')?>">
                       </div>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Notify Segment', 'smpush-plugin-lang')?></td>
                     <td>
                       <select name="notify_segment" onchange="if(this.value == 'custom'){$('.smpush_userid_field').show();}else{$('.smpush_userid_field').hide();}">
                         <option value="all"><?php echo __('All Registered Users', 'smpush-plugin-lang')?></option>
                         <option value="post_owner" <?php if($event['notify_segment'] == 'post_owner'):?>selected="selected"<?php endif;?>><?php echo __('User that published the post', 'smpush-plugin-lang')?></option>
                         <option value="post_commenters" <?php if($event['notify_segment'] == 'post_commenters'):?>selected="selected"<?php endif;?>><?php echo __('Users add a comment in the post', 'smpush-plugin-lang')?></option>
                         <option value="custom" <?php if($event['notify_segment'] == 'custom'):?>selected="selected"<?php endif;?>><?php echo __('Specify a user ID attribute', 'smpush-plugin-lang')?></option>
                        </select>
                     </td>
                  </tr>
                  <tr valign="top" class="smpush_userid_field" <?php if($event['notify_segment'] != 'custom'):?>style="display:none"<?php endif;?>>
                     <td class="first"><?php echo __('User ID Device', 'smpush-plugin-lang')?></td>
                     <td class="form-required">
                       <input name="userid_field" value="<?php echo $event['userid_field'] ?>" type="text" id="smpushUserAttriValue" size="22">
                       <select class="smpushPostAttriSelector" style="width:150px;" onchange="$('#smpushUserAttriValue').val($(this).val())">
                           <option value=""><?php echo __('Choose Attribute', 'smpush-plugin-lang')?></option>
                        </select>
                       <p class="description"><?php echo __('Select the userID attribute to send the device that is related to this userID value only .', 'smpush-plugin-lang')?></p>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Desktop Push Link', 'smpush-plugin-lang')?></td>
                     <td>
                       <label><input name="desktop_link" type="checkbox" <?php if($event['desktop_link'] == 1) { ?>checked="checked"<?php } ?>> <?php echo __('Enable open the post link when click on desktop push notification', 'smpush-plugin-lang')?></label>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first"><?php echo __('Status', 'smpush-plugin-lang')?></td>
                     <td>
                      <input name="status" type="checkbox" <?php if($event['status'] == 1) { ?>checked="checked"<?php } ?>>
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
<script type="text/javascript">
jQuery(document).ready(function() {
 $(".smpushInsertAtrri").click(function(){
   if($("#smpushPostAttri").val() == ""){
     return;
   }
   var insert = "{$"+$("#smpushPostAttri").val();
   if($("#smpushPostAttriFormat").val() != ""){
     insert += "|"+$("#smpushPostAttriFormat").val();
   }
   if($("#smpushPostAttriDefault").val() != ""){
     var defValue = $("#smpushPostAttriDefault").val();
     defValue = defValue.replace("{", "");
     defValue = defValue.replace("}", "");
     defValue = defValue.replace("$", "");
     insert += "|"+defValue;
   }
   else{
     insert += "|null";
   }
   if($("#smpushPostAttriFunction").val() != ""){
     insert += "|"+$("#smpushPostAttriFunction").val();
   }
   insert += "}";
   smpushInsertAtCaret("smpushEventMessage", insert);
 });
 $(".smpushPostType").change(function(){
   if($(this).val() == ""){
     return;
   }
   $(".smpush_service_-1_loading").show();
   $.get("<?php echo $pageurl?>&loadAttri=1", {"noheader": 1, "smpush_post_type" : $(this).val()}, function(data){
     $(".smpush_service_-1_loading").hide();
     if(data == 0){
       alert(smpush_jslang.event_no_post);
       return;
     }
     $(".smpushPostAttriSelector").html(data);
   });
 });
 if($(".smpushPostType").val() != ""){
   $(".smpushPostType").trigger("change");
 }
});
</script>