<div class="wrap">
   <div id="smpush-icon-chanmanage" class="icon32"><br></div>
   <h2><?php echo __('Manage Channels', 'smpush-plugin-lang')?><a href="javascript:" onclick="smpush_open_service(-1)" class="add-new-h2"><?php echo __('New Channel', 'smpush-plugin-lang')?></a>
   <a href="<?php echo $pageurl;?>&update_counters=1&noheader=1" class="add-new-h2"><?php echo __('Update Counters', 'smpush-plugin-lang')?></a>
   <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_-1_loading" style="display:none" />
   </h2>
   <div id="col-container">
      <div id="col-left" style="width: 55%;margin-top: 10px;">
         <div class="col-wrap">
             <table class="wp-list-table widefat fixed tags" cellspacing="0">
                <thead>
                   <tr>
                      <th scope="col" class="manage-column column-posts" style="width:25px"><span><?php echo __('ID', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Title', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Description', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Privacy', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column" style="width:80px"><span><?php echo __('Subscribers', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories" style="width:75px"><span></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                      <th scope="col" class="manage-column column-posts"><span><?php echo __('ID', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Title', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Description', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Privacy', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Subscribers', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories"><span></span></th>
                   </tr>
                </tfoot>
                <tbody id="the-list" data-wp-lists="list:tag">
                <?php if($channels){$counter = 0;foreach($channels AS $channel){$counter++;?>
                   <tr id="smpush-service-tab-<?php echo $channel->id;?>" class="smpush-service-tab <?php if($counter%2 == 0){echo 'alternate';}?>">
                      <td class="name column-name"><?php echo $channel->id;?></td>
                      <td class="name column-name"><strong><?php if($channel->default == 1)echo '*';?><?php echo $channel->title;?></strong><br />
                      <div class="row-actions">
                      <?php if($channel->default == 0){?>
                      <span class="edit"><a href="<?php echo $pageurl;?>&default=1&noheader=1&id=<?php echo $channel->id;?>"><?php echo __('Default', 'smpush-plugin-lang')?></a></span>
                      <span class="delete"> | <a class="smio-delete" href="<?php echo $pageurl;?>&delete=1&noheader=1&id=<?php echo $channel->id;?>"><?php echo __('Delete', 'smpush-plugin-lang')?></a></span>
                      <?php }?>
                      </div>
                      </td>
                      <td class="description column-description"><?php echo $channel->description;?></td>
                      <td class="description column-description"><?php echo ($channel->private == 1)? __('Private', 'smpush-plugin-lang') : __('Public', 'smpush-plugin-lang');?></td>
                      <td class="description column-description"><?php echo $channel->count;?></td>
                      <td class="description column-categories">
                      <input type="button" class="button action smpush-open-btn" value="<?php echo __('Edit', 'smpush-plugin-lang')?>" onclick="smpush_open_service(<?php echo $channel->id;?>)" />
                      <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_<?php echo $channel->id;?>_loading" style="display:none" />
                      </td>
                   </tr>
                <?php }}else{?>
                <tr class="no-items"><td class="colspanchange" colspan="5"><?php echo __('No items found.', 'smpush-plugin-lang')?></td></tr>
                <?php }?>
                </tbody>
             </table>
             <br class="clear">
            <div class="form-wrap">
            <p><strong><?php echo __('Note', 'smpush-plugin-lang')?>:</strong><br><?php echo __('For how to subscribe,view or display push channels back to documentation page.', 'smpush-plugin-lang')?></p>
            </div>
         </div>
      </div>
      <div id="col-right" class="smpush_form_ajax" style="width: 45%"></div>
   </div>
</div>
<script type="text/javascript">
var smpush_pageurl = '<?php echo $pageurl;?>';
jQuery(document).ready(function() {
    smpush_open_service(-1);
});
</script>