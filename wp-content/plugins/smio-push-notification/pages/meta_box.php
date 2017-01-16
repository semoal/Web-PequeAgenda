<table>
  <tbody>
    <tr valign="middle">
      <td>
        <label class="selectit"><input type="checkbox" name="smpush_mute" checked="checked"> <?php echo __('Mute notification for this post', 'smpush-plugin-lang')?></label>
      </td>
    </tr>
    <tr valign="middle">
      <td>
        <h2 style="padding:12px 0"><?php echo __('Specific Channels', 'smpush-plugin-lang')?></h2>
        <ul class="categorychecklist form-no-clear" style="margin: 0">
          <li><label class="selectit"><input type="checkbox" name="smpush_all_users" checked="checked"> <?php echo __('All Users', 'smpush-plugin-lang')?></label></li>
          <?php foreach ($channels as $channel): ?>
            <li><label class="selectit"><input value="<?php echo $channel->id; ?>" type="checkbox" name="smpush_channels[]" disabled="disabled"> <?php echo $channel->title; ?> (<?php echo $channel->count; ?>)</label></li>
          <?php endforeach; ?>
        </ul>
      </td>
    </tr>
  </tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function() {
  jQuery('input[name="smpush_all_users"]').click(function(){
    if(jQuery(this)[0].checked){
      jQuery('input[name="smpush_channels[]"]').attr("disabled", "disabled");
    }
    else{
      jQuery('input[name="smpush_channels[]"]').removeAttr("disabled");
    }
  });
});
</script>