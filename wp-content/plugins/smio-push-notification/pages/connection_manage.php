<div class="wrap">
   <div id="smpush-icon-connmanage" class="icon32"><br></div>
   <h2><?php echo __('Manage Connections', 'smpush-plugin-lang')?><a href="javascript:" onclick="smpush_open_service(-1)" class="add-new-h2"><?php echo __('New Connection', 'smpush-plugin-lang')?></a><img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_-1_loading" style="display:none" /></h2>
   <div id="col-container">
      <div id="col-left" style="width: 45%;margin-top: 10px;">
         <div class="col-wrap">
             <table class="wp-list-table widefat fixed tags" cellspacing="0">
                <thead>
                   <tr>
                      <th scope="col" class="manage-column"><span><?php echo __('Title', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Description', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column" style="width:70px"><span><?php echo __('Count', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories" style="width:75px"><span></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                      <th scope="col" class="manage-column"><span><?php echo __('Title', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Description', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column"><span><?php echo __('Count', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories"><span></span></th>
                   </tr>
                </tfoot>
                <tbody id="the-list" data-wp-lists="list:tag">
                <?php if($connections){$counter = 0;foreach($connections AS $connection){$counter++;?>
                   <tr id="smpush-service-tab-<?php echo $connection->id;?>" class="smpush-service-tab <?php if($counter%2 == 0){echo 'alternate';}?>">
                      <td class="name column-name"><strong><?php echo $connection->title;?></strong><br />
                      <div class="row-actions">
                      <?php if($connection->id != self::$apisetting['def_connection']){?>
                      <span class="delete"><a class="smio-delete" href="<?php echo $pageurl;?>&delete=1&noheader=1&id=<?php echo $connection->id;?>">Delete</a></span>
                      <?php }?>
                      </div>
                      </td>
                      <td class="description column-description"><?php echo $connection->description;?></td>
                      <td class="description column-description"><?php echo $connection->counter;?></td>
                      <td class="description column-categories">
                      <input type="button" class="button action smpush-open-btn" value="Edit" onclick="smpush_open_service(<?php echo $connection->id;?>)" />
                      <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_<?php echo $connection->id;?>_loading" style="display:none" />
                      </td>
                   </tr>
                <?php }}?>
                </tbody>
             </table>
             <br class="clear">
            <div class="form-wrap">
            <p><strong><?php echo __('Note', 'smpush-plugin-lang')?>:</strong><br /><?php echo __('You can select the default connection from setting page', 'smpush-plugin-lang')?></p>
            </div>
         </div>
      </div>
      <div id="col-right" class="smpush_form_ajax" style="width: 55%"></div>
   </div>
</div>
<script type="text/javascript">
var smpush_pageurl = '<?php echo $pageurl;?>';
jQuery(document).ready(function() {
    smpush_open_service(-1);
});
</script>