<div class="wrap" id="smpush-dashboard">
<div id="smpush-icon-push" class="icon32"><br></div>
<h2><?php echo get_admin_page_title();?></h2>
<form action="<?php echo $pageurl;?>" method="post">
  <div id="col-container">
  <div class="metabox-holder">
     <div class="postbox-container" style="width:60%;">
        <div class="meta-box-sortables">
           <div class="postbox">
              <div class="handlediv" title="<?php echo __('Click to toggle', 'smpush-plugin-lang')?>"><br></div>
              <h3><label><?php echo __('Sending Options', 'smpush-plugin-lang')?></label></h3>
              <div class="inside">
                 <table class="form-table">
                    <tbody>
                       <tr valign="top">
                          <td class="first"><?php echo __('Message', 'smpush-plugin-lang')?></td>
                          <td><textarea name="message" cols="40" rows="6" class="large-text"><?php echo __('Test push notification functionality !', 'smpush-plugin-lang')?></textarea></td>
                       </tr>
                       <tr valign="top">
                          <td class="first">iOS <?php echo __('Device Token', 'smpush-plugin-lang')?></td>
                          <td>
                             <input name="ios_token" type="text" class="regular-text" />
                          </td>
                       </tr>
                       <tr valign="top">
                          <td class="first">Android <?php echo __('Device Token', 'smpush-plugin-lang')?></td>
                          <td>
                             <input name="android_token" type="text" class="regular-text" />
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
     <div class="postbox-container" style="width:60%;">
         <div class="postbox">
             <table class="form-table" style="margin-top: 0;">
                <tbody>
                   <tr valign="top">
                      <td>
                        <input type="submit" class="button button-primary" value="<?php echo __('Send a test message', 'smpush-plugin-lang')?>">
                      </td>
                   </tr>
                </tbody>
             </table>
         </div>
     </div>
  </div>
  </div>
</form>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
 if(typeof postboxes !== 'undefined')
   postboxes.add_postbox_toggles( 'dashboard_page_stats' );
});
</script>