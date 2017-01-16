<style>.widefat td, .widefat th {padding: 8px 0;}</style>
<div class="wrap">
   <div id="smpush-icon-archive" class="icon32"><br></div>
   <h2><?php echo __('Reports', 'smpush-plugin-lang')?> <a href="<?php echo $pageurl;?>" class="add-new-h2"><?php echo __('Message Archive', 'smpush-plugin-lang')?></a></h2>
   <div id="col-container">
      <div id="col-left" style="width: 100%">
         <div class="col-wrap">
          <div class="tablenav top">
            <div class="tablenav-pages one-page"><span class="displaying-num"><?php echo self::$paging['result'];?> <?php echo __('items', 'smpush-plugin-lang')?></span></div>
        	<br class="clear">
        	</div>
             <table class="wp-list-table widefat fixed tags" cellspacing="0" <?php if(get_bloginfo('version') < 3.8){?>style="table-layout: auto"<?php }?>>
                <thead>
                   <tr>
                     <th scope="col" class="manage-column smpush-center" rowspan="2" style="width:150px"><span><?php echo __('Time', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span><?php echo __('Total', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>iOS</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Android</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Chrome</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Safari</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Firefox</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>WP 8</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>UW 10</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>BlackBerry 10</span></th>
                   </tr>
                   <tr>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                   </tr>
                </thead>
                <tfoot>
                   <tr>
                     <th scope="col" class="manage-column smpush-center" rowspan="2" style="width:150px"><span><?php echo __('Time', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Success', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center"><span><?php echo __('Fail', 'smpush-plugin-lang')?></span></th>
                   </tr>
                  <tr>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span><?php echo __('Total', 'smpush-plugin-lang')?></span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>iOS</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Android</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Chrome</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Safari</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>Firefox</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>WP 8</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>UW 10</span></th>
                      <th scope="col" class="manage-column smpush-center" colspan="2"><span>BlackBerry 10</span></th>
                   </tr>
                </tfoot>
                <tbody>
                <?php if($reports){$counter = 0;foreach($reports AS $sreport){$counter++;$report = unserialize($sreport->report);?>
                   <tr class="<?php if($counter%2 == 0){echo 'alternate';}?>">
                      <td class="name column-name smpush-center"><?php echo date(self::$wpdateformat, $sreport->report_time);?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['totalsend'])) ? '' : $report['totalsend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['totalfail'])) ? '' : $report['totalfail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['iossend'])) ? '' : $report['iossend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['iosfail'])) ? '' : $report['iosfail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['androidsend'])) ? '' : $report['androidsend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['androidfail'])) ? '' : $report['androidfail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['chromesend'])) ? '' : $report['chromesend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['chromefail'])) ? '' : $report['chromefail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['safarisend'])) ? '' : $report['safarisend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['safarifail'])) ? '' : $report['safarifail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['firefoxsend'])) ? '' : $report['firefoxsend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['firefoxfail'])) ? '' : $report['firefoxfail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['wpsend'])) ? '' : $report['wpsend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['wpfail'])) ? '' : $report['wpfail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['wp10send'])) ? '' : $report['wp10send'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['wp10fail'])) ? '' : $report['wp10fail'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['bbsend'])) ? '' : $report['bbsend'];?></td>
                      <td class="name column-name smpush-center"><?php echo (!isset($report['bbfail'])) ? '' : $report['bbfail'];?></td>
                   </tr>
                <?php }}else{?>
                <tr class="no-items"><td class="colspanchange smpush-center" colspan="17">No items found.</td></tr>
                <?php }?>
                </tbody>
             </table>
             <div class="tablenav bottom">
                <div class="tablenav-pages"><span class="displaying-num"><?php echo self::$paging['result'];?> <?php echo __('items', 'smpush-plugin-lang')?></span>
                  <span class="pagination-links">
                  <?php echo paginate_links($paging_args);?>
                  </span>
                </div>
            	<br class="clear">
             </div>
         </div>
      </div>
   </div>
</div>