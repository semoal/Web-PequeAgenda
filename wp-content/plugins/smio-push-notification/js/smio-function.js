/*!
 * Copyright (c) 2013 Smart IO Labs
 * Project repository: http://smartiolabs.com
 * license: Is not allowed to use any part of the code.
 */
var $ = jQuery;
var smpush_currcount=0, smpush_percent=0, smpush_google_open = 1, smpush_wp_open = 1, smpush_wp10_open = 1, smpush_bb_open = 1, smpush_chrome_open = 1, smpush_safari_open = 1, smpush_firefox_open = 1, smpush_firstrun = 1, smpush_feedback_open = 1, smpush_feedback_google = 1, smpush_feedback_chrome = 1, smpush_feedback_safari = 1;
var smpush_pro_currcount=0, smpush_pro_percent=0, smpush_lastid=0, smpush_resum_timer;
$(document).ready(function() {
  $("#smpush_model_select").change(function(){
    $('.smpush_apidesc').hide();
    $('.smpush_method_'+$(this).val()).show();
  });
  $('.smpushCloseTB').click(function(){
    smpushHideTable($(this).closest("div.metabox-holder").attr("data-smpush-counter"));
  });
  $('#smio-submit').click(function(){
    var form = $(this).parents('form');
    if(!validateForm(form))
      return false;
  });
  $('#push-token-list td span').click(function(){
    $(this).attr('style', 'height:auto');
  });
  $('#search-submit').click(function(){
    $("#smpush-noheader-value").remove();
  });
  $('#post-query-submit').click(function(event){
    $("#smpush-noheader-value").remove();
  });
  $('.smpush-applytoall').click(function(event){
    if(!confirm(smpush_jslang.applytoall)){
      event.preventDefault();
      return;
    }
  });
  $('.smio-delete').click(function(event){
    var confirmtxt = $(this).attr("data-confirm");
    if(typeof confirmtxt == "undefined"){
      confirmtxt = smpush_jslang.deleteconfirm;
    }
    if (!confirm(confirmtxt)){
      event.preventDefault();
    }
  });
  $('#smpush-calculate-btn').click(function(){
    var options = {
    url:           $('#smpush_histform').attr("action")+'&calculate=1&noheader=1',
    beforeSubmit:  function(){$('.smpush_calculate_process').show()},
    success:       function(responseText, statusText){
      responseText = JSON.parse(responseText);
      $('#smpush-calculate-span-ios').html(responseText["ios"]);
      $('#smpush-calculate-span-android').html(responseText["android"]);
      $('#smpush-calculate-span-wp').html(responseText["wp"]);
      $('#smpush-calculate-span-wp10').html(responseText["wp10"]);
      $('#smpush-calculate-span-bb').html(responseText["bb"]);
      $('#smpush-calculate-span-chrome').html(responseText["chrome"]);
      $('#smpush-calculate-span-safari').html(responseText["safari"]);
      $('#smpush-calculate-span-firefox').html(responseText["firefox"]);
      $('#smpush-calculate-span-ios').fadeIn();
      $('#smpush-calculate-span-android').fadeIn();
      $('#smpush-calculate-span-wp').fadeIn();
      $('#smpush-calculate-span-bb').fadeIn();
      $('#smpush-calculate-span-chrome').fadeIn();
      $('#smpush-calculate-span-safari').fadeIn();
      $('#smpush-calculate-span-firefox').fadeIn();
      $('.smpush_calculate_process').hide();
    }
    };
    $('#smpush_histform').ajaxSubmit(options);
  });
  $('#smpush-clear-hisbtn').click(function(){
    var options = {
    url:           $('#smpush_histform').attr("action")+'&clearhistory=1&noheader=1',
    beforeSubmit:  function(){$('.smpush_process').show()},
    success:       function(responseText, statusText){if(responseText!=1){console.log(responseText);}else{$('.smpush_process').hide();}}
    };
    $('#smpush_histform').ajaxSubmit(options);
  });
  $('#smpush-save-hisbtn').click(function(){
    var options = {
    url:           $('#smpush_histform').attr("action")+'&savehistory=1&noheader=1',
    beforeSubmit:  function(){$('.smpush_process').show()},
    success:       function(responseText, statusText){if(responseText!=1){console.log(responseText);}else{$('.smpush_process').hide();}}
    };
    $('#smpush_histform').ajaxSubmit(options);
  });
  $('.smpush-payload').change(function(){
   if($(this).val() == "multi"){
     $(".smpush-payload-normal").hide();
     $(".smpush-payload-multi").show();
   }
   else{
     $(".smpush-payload-multi").hide();
     $(".smpush-payload-normal").show();
   }
  });
  $('.and_smpush-payload').change(function(){
   if($(this).val() == "multi"){
     $(".and_smpush-payload-normal").hide();
     $(".and_smpush-payload-multi").show();
   }
   else{
     $(".and_smpush-payload-multi").hide();
     $(".and_smpush-payload-normal").show();
   }
  });
  
  var smpush_upload_field; 
  jQuery('.smpush_upload_file_btn').click(function() {
    smpush_upload_field = jQuery(this).attr('data-container');
    formfield = jQuery('.'+smpush_upload_field).attr('name');
    tb_show('', 'media-upload.php?type=image&TB_iframe=1');
    return false;
  });
  window.send_to_editor = function(html) {
    imgurl = jQuery('img', html).attr('src');
    jQuery('.'+smpush_upload_field).val(imgurl);
    tb_remove();
  }
});

function smpush_delete_service(id){
  if(!confirm(smpush_jslang.deleteconfirm)){
    return;
  }
  $('.smpush_service_'+id+'_loading').show();
  $.get(smpush_pageurl, {'noheader':1, 'delete': 1, 'id': id}
  ,function(data){
    $('.smpush_service_'+id+'_loading').hide();
    $('#smpush-service-tab-'+id).hide(600, function() {
      $('#smpush-service-tab-'+id).remove("push-alternate");
    });
  });
}

function smpush_open_service(id, actiontype, action, newwidth){
  if(actiontype == 1){
    if(confirm(smpush_jslang.savechangesconfirm)){
      $('#smpush_jform').ajaxSubmit();
    }
  }
  else if(actiontype == 2){
    if(typeof(newwidth) == "undefined"){
      var newwidth = 55;
    }
    $(".smpush-canhide").hide();
    $("#col-left").css("width", newwidth+"%");
  }
  if(typeof(newwidth) != "undefined"){
    $(".smpush-canhide").hide();
    $("#col-left").css("width", newwidth+"%");
  }
  $(".smpush_form_ajax").show();
  $('.smpush-service-tab').removeClass("push-alternate");
  $('#smpush-service-tab-'+id).addClass("push-alternate");
  $('.smpush_service_'+id+'_loading').show();
  $.get(smpush_pageurl, {'noheader':1, 'action': action, 'id': id}
  ,function(data){
    $('.smpush_form_ajax').html(data);
    var smpush_form_options = {
        beforeSubmit:  function(){$('.smpush_process').show()},
        success:       function(responseText, statusText){
          if(responseText != 1){
            $(".smpush_process").hide();
            alert(responseText['message']);
          }
          else{
            $(".smpush_process").hide();
            $(".smpush_form_ajax").fadeOut("fast", function(){
              $('.smpush_form_ajax').html('');
              if(actiontype == 2){
                $("#col-left").css("width", "100%");
                $(".smpush-canhide").show();
              }
              if(id != -1){
                $("html, body").animate({scrollTop: $('#smpush-service-tab-'+id).offset().top-100}, "slow");
              }
            });
          }
        }
    };
    $('#smpush_jform').ajaxForm(smpush_form_options);
    $('#smio-submit').click(function(){
      var form = $(this).parents('form');
      if (!validateForm(form)) return false;
    });
    $('.smpush_service_'+id+'_loading').hide();
    if(id != -1)$("html, body").animate({scrollTop: 0}, "slow");
  });
}

function SMPUSH_ProccessQueue(baseurl, allcount, increration){
  if(allcount == 0){
    $("#smpush_progressinfo").append("<p class='error'>"+smpush_jslang.no_tokens_msg+"</p>");
    return;
  }
  if(smpush_pro_currcount == 0){
    $("#smpush_progressinfo").append("<p>"+smpush_jslang.start_queuing+" "+allcount+" "+smpush_jslang.token_in_queue+"</p>");
  }

  $.getJSON(baseurl+'admin.php?page=smpush_send_notification', {'noheader':1, 'lastid':smpush_lastid, 'increration':increration}
  ,function(data){
    if(typeof(data) === "undefined" || data === null){
      $("#smpush_progressinfo").append("<p class='error'>"+smpush_jslang.escaped_reconnect+"</p>");
      smpush_resum_timer = setTimeout(function(){SMPUSH_ProccessQueue(baseurl, allcount, increration)}, 2000);
      return;
    }

    if(data.respond != 0){
      smpush_pro_currcount = smpush_pro_currcount+increration;
      smpush_pro_percent = Math.floor(((smpush_pro_currcount)/allcount)*100);
      $("#smpush_progressbar").progressbar("value", smpush_pro_percent);
      $(".smpush_progress_label").text(smpush_pro_percent+'%');
    }

    if(data.respond == 1){
      smpush_lastid = data.message;
      SMPUSH_ProccessQueue(baseurl, allcount, increration);
    }
    else if(data.respond == -1){
      $("#smpush_progressbar").progressbar("value", 100);
      $(".smpush_progress_label").text(smpush_jslang.completed);
      $("#smpush_progressinfo").append('<p>'+data.message+' '+smpush_jslang.message_queuing_completed+'</p>');
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == -2){
      $("#smpush_progressbar").progressbar("value", 100);
      $(".smpush_progress_label").text(smpush_jslang.completed);
      $("#smpush_progressinfo").append('<p>'+data.message+' '+smpush_jslang.message_queuing_scheduling+'</p>');
      $("#smpush_progressinfo").append('<p>'+smpush_jslang.completed+'...</p>');
      $("#cancel_push").val(smpush_jslang.exit_and_back);
    }
    else if(data.respond == 0) $("#smpush_progressinfo").append(data.message);
    else $("#smpush_progressinfo").append('<p class="error">'+smpush_jslang.error_refresh+'</p>');
  }).fail(function(error) {
    console.log(error.responseText);
    $("#smpush_progressinfo").append("<p class='error'>"+smpush_jslang.escaped_reconnect+"</p>");
    smpush_resum_timer = setTimeout(function(){SMPUSH_ProccessQueue(baseurl, allcount, increration)}, 2000);
  });
}

function SMPUSH_RunQueue(baseurl, allcount){
  $.getJSON(baseurl+'admin.php?page=smpush_runqueue', {'noheader':1, 'getcount':0, 'firstrun':smpush_firstrun, 'google_notify':smpush_google_open, 'wp_notify':smpush_wp_open, 'wp10_notify':smpush_wp10_open, 'bb_notify':smpush_bb_open, 'chrome_notify':smpush_chrome_open, 'safari_notify':smpush_safari_open, 'firefox_notify':smpush_firefox_open, 'feedback_open':smpush_feedback_open, 'feedback_google':smpush_feedback_google, 'feedback_chrome':smpush_feedback_chrome, 'feedback_safari':smpush_feedback_safari}
  ,function(data){
    smpush_firstrun = 0;
    if(typeof(data) === "undefined" || data === null){
      $("#smpush_progressinfo").append("<p class='error'>"+smpush_jslang.escaped_reconnect+"</p>");
      smpush_resum_timer = setTimeout(function(){SMPUSH_RunQueue(baseurl, allcount)}, 3000);
      return;
    }

    if(data.respond != 0){
      if(allcount == -1){
        $(".smpush_progress_label").text(smpush_jslang.start_feedback);
      }
      else{
        smpush_percent = Math.floor((smpush_currcount/allcount)*100);
        $("#smpush_progressbar").progressbar("value", smpush_percent);
        $(".smpush_progress_label").text(smpush_percent+'%');
        if(smpush_percent >= 100){
          $(".smpush_progress_label").text(smpush_jslang.start_feedback);
        }
      }
    }

    if(data.respond == 1){
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == -1){
      $("#smpush_progressbar").progressbar("value", 100);
      $(".smpush_progress_label").text(smpush_jslang.completed);
      $("#smpush_progressinfo").append(data.message);
      $("#smpush_progressinfo").append('<p>'+smpush_jslang.completed+'...</p>');
      $("#cancel_push").val(smpush_jslang.exit_and_back);
    }
    else if(data.respond == 2){
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      if(data.result.message != ""){
        $("#smpush_progressinfo").append(data.result.message);
      }
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 3){
      smpush_google_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == "wp_server_reponse"){
      smpush_wp_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == "wp10_server_reponse"){
      smpush_wp10_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == "bb_server_reponse"){
      smpush_bb_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == "chrome_server_reponse"){
      smpush_chrome_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == "safari_server_reponse"){
      smpush_safari_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == "firefox_server_reponse"){
      smpush_firefox_open = 0;
      if(data.result.all_count > 0){
        smpush_currcount = allcount-data.result.all_count;
      }
      $("#smpush_progressinfo").append(data.result.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 4){
      smpush_feedback_open = 0;
      $("#smpush_progressinfo").append(data.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 5){
      smpush_feedback_google = 0;
      $("#smpush_progressinfo").append(data.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 6){
      smpush_feedback_chrome = 0;
      $("#smpush_progressinfo").append(data.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 7){
      smpush_feedback_safari = 0;
      $("#smpush_progressinfo").append(data.message);
      SMPUSH_RunQueue(baseurl, allcount);
    }
    else if(data.respond == 0) $("#smpush_progressinfo").append(data.message);
    else $("#smpush_progressinfo").append('<p class="error">'+smpush_jslang.error_refresh+'</p>');
  }).fail(function(error) {
    console.log(error.responseText);
    $("#smpush_progressinfo").append("<p class='error'>"+smpush_jslang.escaped_reconnect+"</p>");
    smpush_resum_timer = setTimeout(function(){SMPUSH_RunQueue(baseurl, allcount)}, 3000);
  });
}

function smpushEventDelRow(button) {
  if($(".smpushEventConditions div").length == 1){
    return;
  }
  $(button).closest("div").remove();
}

function smpushUpdateValueField(select) {
  var value = $(select).find(':selected').attr('data-placeholder');
  $(select).closest("div").find(".smpushPostAttriSelectorValue").attr("placeholder", value);
}

function smpushEventAddRow(button) {
  var newRow = "<div class='smpush-clear'>"+$(button).closest("div").html()+"</div>";
  $(".smpushEventConditions").append(newRow);
  $(".smpushEventConditions div:last").find("select").val("");
  $(".smpushEventConditions div:last").find("input[type='text']").val("");
}

function smpushInsertAtCaret(areaId, text) {
  var txtarea = document.getElementById(areaId);
  var scrollPos = txtarea.scrollTop;
  var strPos = 0;
  var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? "ff" : (document.selection ? "ie" : false ) );
  if (br == "ie") { 
      txtarea.focus();
      var range = document.selection.createRange();
      range.moveStart ('character', -txtarea.value.length);
      strPos = range.text.length;
  }
  else if (br == "ff") strPos = txtarea.selectionStart;

  var front = (txtarea.value).substring(0,strPos);  
  var back = (txtarea.value).substring(strPos,txtarea.value.length); 
  txtarea.value=front+text+back;
  strPos = strPos + text.length;
  if (br == "ie") { 
      txtarea.focus();
      var range = document.selection.createRange();
      range.moveStart ('character', -txtarea.value.length);
      range.moveStart ('character', strPos);
      range.moveEnd ('character', 0);
      range.select();
  }
  else if (br == "ff") {
      txtarea.selectionStart = strPos;
      txtarea.selectionEnd = strPos;
      txtarea.focus();
  }
  txtarea.scrollTop = scrollPos;
}

function smpush_back_setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function smpush_back_getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(";");
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==" "){
          c = c.substring(1);
        }
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

function smpushHideTable(tbnum){
  $(".metabox-holder[data-smpush-counter='"+tbnum+"']").hide();
  var smpushTablesHistory = smpush_back_getCookie("smpushTablesHistory");
  smpushTablesHistory += ","+tbnum;
  smpush_back_setCookie("smpushTablesHistory", smpushTablesHistory, 30);
}

function smpushResetHistoryTables(){
  $(".metabox-holder").show();
  smpush_back_setCookie("smpushTablesHistory", "", -1);
}

function smpushHideHistoryTables(){
  var smpushTablesHistory = smpush_back_getCookie("smpushTablesHistory");
  if(smpushTablesHistory != ""){
    smpushTablesHistory = smpushTablesHistory.split(",");
    for(var i=0;i<=smpushTablesHistory.length;i++){
      $(".metabox-holder[data-smpush-counter='"+smpushTablesHistory[i]+"']").hide();
    }
  }
}