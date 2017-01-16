<div class="wrap">
   <div id="smpush-icon-chanmanage" class="icon32"><br></div>
   <h2><?php echo __('Manage Events', 'smpush-plugin-lang')?><a href="javascript:" onclick="smpush_open_service(-1,2,'',30)" class="add-new-h2"><?php echo __('New Event', 'smpush-plugin-lang')?></a>
   <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_-1_loading" style="display:none" />
   </h2>
   <div id="col-container">
      <div id="col-left" style="width: 100%;margin-top: 10px;">
         <div class="col-wrap">
             <table class="wp-list-table widefat fixed tags" cellspacing="0">
                <thead>
                   <tr>
                      <th scope="col" class="manage-column" style="width:35%"><span><?php echo __('Title', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Event Type', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center smpush-canhide"><span><?php echo __('Post Type', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center smpush-canhide"><span><?php echo __('Status', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories" style="width:100px"><span></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                      <th scope="col" class="manage-column"><span><?php echo __('Title', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Event Type', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center smpush-canhide"><span><?php echo __('Post Type', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center smpush-canhide"><span><?php echo __('Status', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories"><span></span></th>
                   </tr>
                </tfoot>
                <tbody id="the-list" data-wp-lists="list:tag">
                <?php if($events){$counter = 0;foreach($events AS $event){$counter++;?>
                   <tr id="smpush-service-tab-<?php echo $event->id;?>" class="smpush-service-tab <?php if($counter%2 == 0){echo 'alternate';}?>">
                      <td class="name column-name"><strong><?php echo $event->title;?></strong></td>
                      <td class="description column-description smpush-center"><?php echo $event->event_type;?></td>
                      <td class="description column-description smpush-center smpush-canhide"><?php echo $event->post_type;?></td>
                      <td class="description column-description smpush-center smpush-canhide"><?php echo ($event->status == 1)? __('Active', 'smpush-plugin-lang') : __('Inactive', 'smpush-plugin-lang');?></td>
                      <td class="description column-categories">
                      <input type="button" class="button action smpush-open-btn" value="<?php echo __('Edit', 'smpush-plugin-lang')?>" onclick="smpush_open_service(<?php echo $event->id;?>,'','',30)" />
                      <input type="button" class="button action smpush-open-btn" value="<?php echo __('Delete', 'smpush-plugin-lang')?>" onclick="smpush_delete_service(<?php echo $event->id;?>)" style="margin-top:4px" />
                      <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_<?php echo $event->id;?>_loading" style="display:none" />
                      </td>
                   </tr>
                <?php }}else{?>
                <tr class="no-items"><td class="colspanchange" colspan="5"><?php echo __('No items found.', 'smpush-plugin-lang')?></td></tr>
                <?php }?>
                </tbody>
             </table>
             <br class="clear">
             <p class="description smpush-canhide" style="color:red"><?php echo __('Notice: To use this feature first please enable the cron-job service, Look', 'smpush-plugin-lang')?> <a href="https://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank"><?php echo __('here', 'smpush-plugin-lang')?></a> <?php echo __('for further information', 'smpush-plugin-lang')?></p>
         </div>
      </div>
      <div id="col-right" class="smpush_form_ajax" style="width: 70%;display:none"></div>
   </div>
</div>
<script type="text/javascript">
var smpush_pageurl = '<?php echo $pageurl;?>';
</script>