<div class="wrap">
   <div id="smpush-icon-tokens" class="icon32"><br></div>
   <h2><?php echo __('Manage Device Token', 'smpush-plugin-lang')?>
   <a href="<?php echo $pageurl;?>&remove_duplicates=1&noheader=1" class="add-new-h2 smio-delete" data-confirm="<?php echo __('It is highly recommended to take a backup from the table before start, Continue', 'smpush-plugin-lang')?>?"><?php echo __('Remove Duplicates', 'smpush-plugin-lang')?></a>
   <a href="javascript:" onclick="smpush_open_service(-1,2)" class="add-new-h2"><?php echo __('Add New Device', 'smpush-plugin-lang')?></a><img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_-1_loading" style="display:none" />
   </h2>
   <div id="col-container">
      <div id="col-left" style="width: 100%">
      <form action="<?php echo $pageurl;?>" method="get">
      <input type="hidden" name="page" value="<?php echo $pagname;?>" />
      <input type="hidden" name="noheader" value="1" id="smpush-noheader-value" />
         <div class="col-wrap">
          <p class="search-box smpush-canhide">
              <label class="screen-reader-text"><?php echo __('Search Devices:', 'smpush-plugin-lang')?></label>
              <input type="search" name="query" value="<?php echo (!empty($_GET['query']))?$_GET['query']:'';?>">
              <input type="submit" id="search-submit" class="button" value="<?php echo __('Search Devices', 'smpush-plugin-lang')?>">
           </p>
          <div class="tablenav top">
      		<div class="alignleft actions bulkactions smpush-canhide">
                <select name="doaction">
                  <option value="0"><?php echo __('Bulk Actions', 'smpush-plugin-lang')?></option>
                  <option value="activate"><?php echo __('Activate', 'smpush-plugin-lang')?></option>
                  <option value="deactivate"><?php echo __('Deactivate', 'smpush-plugin-lang')?></option>
                  <option value="delete"><?php echo __('Delete', 'smpush-plugin-lang')?></option>
                </select>
                <input type="submit" name="apply" class="button action" value="<?php echo __('Apply', 'smpush-plugin-lang')?>">
                <input type="submit" name="applytoall" class="smpush-applytoall button action" value="<?php echo __('Apply to all', 'smpush-plugin-lang')?>">
        	</div>
            <div class="alignleft actions smpush-canhide">
              <select name="device_type">
                <option value="0">Show all types</option>
                <option value="<?php echo $types_name->ios_name;?>">iOS</option>
                <option value="<?php echo $types_name->android_name;?>" <?php if($_GET['device_type'] == $types_name->android_name){?>selected="selected"<?php }?>>Android</option>
                <option value="<?php echo $types_name->wp_name;?>" <?php if($_GET['device_type'] == $types_name->wp_name){?>selected="selected"<?php }?>>Windows Phone</option>
                <option value="<?php echo $types_name->wp10_name;?>" <?php if($_GET['device_type'] == $types_name->wp10_name){?>selected="selected"<?php }?>>Windows 10</option>
                <option value="<?php echo $types_name->bb_name;?>" <?php if($_GET['device_type'] == $types_name->bb_name){?>selected="selected"<?php }?>>BlackBerry</option>
                <option value="<?php echo $types_name->chrome_name;?>" <?php if($_GET['device_type'] == $types_name->chrome_name){?>selected="selected"<?php }?>>Chrome</option>
                <option value="<?php echo $types_name->safari_name;?>" <?php if($_GET['device_type'] == $types_name->safari_name){?>selected="selected"<?php }?>>Safari</option>
                <option value="<?php echo $types_name->firefox_name;?>" <?php if($_GET['device_type'] == $types_name->firefox_name){?>selected="selected"<?php }?>>Firefox</option>
              </select>
              <select name="status">
              <option value="0">Show all status</option>
              <option value="1" <?php if($_GET['status'] == 1) echo 'selected="selected"';?>><?php echo __('Active', 'smpush-plugin-lang')?></option>
              <option value="2" <?php if($_GET['status'] == 2) echo 'selected="selected"';?>><?php echo __('Inactive', 'smpush-plugin-lang')?></option>
              </select>
              <?php if($types_name->dbtype == 'localhost'){?>
              <select name="channel_id" class="postform">
              <option value="0"><?php echo __('View all channels', 'smpush-plugin-lang')?></option>
              <?php foreach($channels as $channel){?>
              <option value="<?php echo $channel->id;?>" <?php if($_GET['channel_id'] == $channel->id) echo 'selected="selected"';?>><?php echo $channel->title;?></option>
              <?php }?>
              </select>
              <?php }?>
              <input type="text" name="userid" placeholder="User ID" value="<?php echo (!empty($_GET['userid']))?$_GET['userid']:'';?>">
              <input type="submit" id="post-query-submit" class="button" value="<?php echo __('Filter', 'smpush-plugin-lang')?>">
            </div>
            <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo self::$paging['result'];?> <?php echo __('items', 'smpush-plugin-lang')?></span></div>
        	<br class="clear">
        	</div>
             <table class="wp-list-table widefat fixed tags" cellspacing="0" <?php if(get_bloginfo('version') < 3.8){?>style="table-layout: auto"<?php }?>>
                <thead>
                   <tr>
                      <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select All', 'smpush-plugin-lang')?></label><input id="cb-select-all-1" type="checkbox"></th>
                      <th scope="col" class="manage-column" style="width:110px"><span><?php echo __('ID', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span><?php echo __('Device Token', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Device Type', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('User', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span><?php echo __('Information', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories smpush-center" style="width:75px"><?php echo __('Active', 'smpush-plugin-lang')?><span></span></th>
                      <th scope="col" class="manage-column column-categories" style="width:155px"><?php echo __('Action', 'smpush-plugin-lang')?><span></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                      <th scope="col" id="cb" class="manage-column column-cb check-column"><label class="screen-reader-text" for="cb-select-all-1"><?php echo __('Select All', 'smpush-plugin-lang')?></label><input id="cb-select-all-1" type="checkbox"></th>
                      <th scope="col" class="manage-column" style="width:110px"><span><?php echo __('ID', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span><?php echo __('Device Token', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Device Type', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('User', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-canhide"><span><?php echo __('Information', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column column-categories smpush-center"><?php echo __('Active', 'smpush-plugin-lang')?><span></span></th>
                      <th scope="col" class="manage-column column-categories"><?php echo __('Action', 'smpush-plugin-lang')?><span></span></th>
                   </tr>
                </tfoot>
                <tbody id="push-token-list">
                <?php if($tokens){$counter = 0;foreach($tokens AS $token){$counter++;?>
                   <tr id="smpush-service-tab-<?php echo $token->id;?>" class="smpush-service-tab <?php if($counter%2 == 0){echo 'alternate';}?>">
                      <th scope="row" class="check-column">
                        <label class="screen-reader-text"></label>
                        <input type="checkbox" name="device[]" value="<?php echo $token->id;?>">
                        <div class="locked-indicator"></div>
                      </th>
                      <td class="name column-name"><strong><?php echo $token->id;?></strong></td>
                      <td class="name column-name smpush-canhide"><span><?php echo $token->device_token;?></span></td>
                      <td class="name column-name smpush-center"><?php echo $token->device_type;?></td>
                      <td class="name column-name smpush-center"><?php if(!empty($token->user))echo $token->user;?></td>
                      <td class="name column-name smpush-canhide"><span><?php echo $token->information;?></span></td>
                      <td class="description column-comments smpush-center"><?php echo ($token->active == 1)? __('Yes', 'smpush-plugin-lang') : __('No', 'smpush-plugin-lang');?></td>
                      <td class="description column-categories">
                      <input type="button" class="button action smpush-open-btn" value="<?php echo __('Edit', 'smpush-plugin-lang')?>" onclick="smpush_open_service(<?php echo $token->id;?>,2)" />
                      <input type="button" class="button action smpush-open-btn" value="<?php echo __('Delete', 'smpush-plugin-lang')?>" onclick="smpush_delete_service(<?php echo $token->id;?>)" />
                      <img src="<?php echo smpush_imgpath.'/wpspin_light.gif';?>" alt="" class="smpush_service_<?php echo $token->id;?>_loading" style="display:none" />
                      </td>
                   </tr>
                <?php }}else{?>
                <tr class="no-items"><td class="colspanchange" colspan="7"><?php echo __('No items found.', 'smpush-plugin-lang')?></td></tr>
                <?php }?>
                </tbody>
             </table>
             <div class="tablenav bottom">
        		<div class="alignleft actions bulkactions">
                <select name="doaction2">
                  <option value="0"><?php echo __('Bulk Actions', 'smpush-plugin-lang')?></option>
                  <option value="activate"><?php echo __('Activate', 'smpush-plugin-lang')?></option>
                  <option value="deactivate"><?php echo __('Deactivate', 'smpush-plugin-lang')?></option>
                  <option value="delete"><?php echo __('Delete', 'smpush-plugin-lang')?></option>
                </select>
                <input type="submit" name="apply" class="button action" value="<?php echo __('Apply', 'smpush-plugin-lang')?>">
                <input type="submit" name="applytoall" class="smpush-applytoall button action" value="<?php echo __('Apply to all', 'smpush-plugin-lang')?>">
            	</div>
                <div class="tablenav-pages"><span class="displaying-num"><?php echo self::$paging['result'];?> <?php echo __('items', 'smpush-plugin-lang')?></span>
                  <span class="pagination-links">
                  <?php echo paginate_links($paging_args);?>
                  </span>
                </div>
            	<br class="clear">
             </div>
         </div>
      </form>
      </div>
      <div id="col-right" class="smpush_form_ajax" style="width: 45%;display:none;"></div>
   </div>
</div>
<script type="text/javascript">
var smpush_pageurl = '<?php echo $pageurl;?>';
</script>