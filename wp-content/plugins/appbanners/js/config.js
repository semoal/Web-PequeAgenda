jQuery(function() {
    appBannersConfig = appBannersConfig || {};
    jQuery.smartbanner(appBannersConfig);

    jQuery('.sb-close').click(function(event) {
        jQuery('#smartbanner').css('display', 'none');
    });
});

