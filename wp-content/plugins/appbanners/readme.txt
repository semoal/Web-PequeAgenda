=== AppBanners ===
Contributors: mattpramschufer 
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=mattpram%40gmail%2ecom
Tags: iOS App Banner, Android App Banner, Market App, MS App Banner
Requires at least: 4.0
Tested up to: 4.5.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ability to promote iOS, Android and MS Applications with an App Banner similar to iOS6 App Banner.

== Description ==

Marketing an iOS App, Android App or MS App within your Wordpress site never got easier.  This plugin will allow you to put in your App IDs and automatically generate the proper meta tags to utilize Apple's App Banner as specified  <a href="http://developer.apple.com/library/ios/#documentation/AppleApplications/Reference/SafariWebContent/PromotingAppswithAppBanners/PromotingAppswithAppBanners.html">here</a>.

For older versions of iOS (prior to version 6.0) a jQuery alternative will pop up in similar fashion to the Apple one.  Android devices are supported with links to the Google Play Store.  Windows devices are supported with links to the MS App Store.

This plugin utilizes the SmartBanner jQuery plugin https://github.com/jasny/jquery.smartbanner 

== Installation ==

1. Activate the plugin through the `Plugins` menu in WordPress
1. Go to 'Settings->App Banners' and enter in your:
* Apple App Store App ID (http://linkmaker.itunes.apple.com/us/), 
* Google Play App ID (http://developer.android.com/distribute/googleplay/promote/linking.html)
* For Microsoft Apps: msApplication-ID is found under Package name in your app manifest, and msApplication-PackageFamilyName is found under Package family name in your app manifest
* Author
* App Title
* Price

== Frequently Asked Questions ==

= Why is the AppBanner not showing up? =
First thing you should do is load the non minified version of the javascript file by editing line:46 of appBanners.php
 Change this
 `		wp_register_script( 'app-banners-scripts', plugins_url( '/lib/smartbanner/jquery.smartbanner.min.js', __FILE__ ), array( 'jquery' ), false, true );
 `
 To this
 `		wp_register_script( 'app-banners-scripts', plugins_url( '/lib/smartbanner/jquery.smartbanner.js', __FILE__ ), array( 'jquery' ), false, true );
 `

This will then output in the console logging as to why the App Banner is not showing for you.

= I have an iOS device running the latest operating system and I can not see the app banner... What gives? =
Well unfortunately this is not an issue with the AppBanners plugin.  For all iOS devices after iOS 6 Apple introduces their own Smart Banner.
You can read about it <a href="https://developer.apple.com/library/ios/documentation/AppleApplications/Reference/SafariWebContent/PromotingAppswithAppBanners/PromotingAppswithAppBanners.html" target="_blank">here</a>

We rely on the operating system to read the META tag which AppBanners plugin injects into your site to display the native iOS App Banner.  There are so many variables as to when and why Apple shows the banner and sometimes does not.

= Is there a way to track the number of clicks to the install/open button on the App Banners Plugin with Javascript? =
Yes.  There is a couple of ways.  The most preferred way would be to simply add the following code to your theme's scripts file or a place where you can add Javascript to your theme.  The other option is to add directly to your functions.php file of your theme.

**Javscript Version**
`      jQuery(document).ready(function ($) {
         $('body').on('click', '.sb-button', function(){
            ga('send', {
               'hitType': 'event',          // Required.
               'eventCategory': 'App Banners',   // Required.
               'eventAction': 'click',      // Required.
               'eventLabel': 'Clicked App Banner' //Optional
            });
         });
      });`

**Functions.php Version**
`//Start App Banner Click Tracking
 add_action('wp_head','appbanners_track_clicks_js');

 function appbanners_track_clicks_js() {    ?>
    <script>

       jQuery(document).ready(function ($) {
          $('body').on('click', '.sb-button', function(){
             ga('send', {
                'hitType': 'event',          // Required.
                'eventCategory': 'App Banners',   // Required.
                'eventAction': 'click',      // Required.
                'eventLabel': 'Clicked App Banner' //Optional
             });
          });
       });

    </script>
    <?php
 }
 //End App Banner Click Tracking`


== Screenshots ==

1. Settings Screen
2. Apple App Banner
3. Android App Banner

== Changelog ==

= 1.5.14 =
 * Fixed issue with double smart banners appearing on some devices
 * Fixed issue with Gloss effect always showing even if it was set to false.

= 1.5.13 =
 * Added in the ability to input which selector the smartbanner pushes down from.
 * Updated to latest version of jQuery Smartbanner plugin
 * Tested with Wordpress 4.5.2 to confirm working.

= 1.5.12 =
 * Added in the ability to input which selector the smartbanner gets appended to.  Thanks to ericbow for submitting the patch.  If no input selector is specified it will default to <body>

= 1.5.11 =
 * Added in the ability to specify custom URL for button.  If you do not want to specify a url, leave it blank and it will default to app store.

= 1.5.10 =
 * Added in checkbox to allow control over outputting META VIEWPORT tag on site.
 * Big shout out to e2.robert for taking the time to submitting the patch!

= 1.5.9 =
 * Reworked javascript set cookie function.  As there was a bug if you set the app banner to always show.
 * Tested with Wordpress 4.3

= 1.5.8 =
 * Completed redid the way I generate the javascript config file for plugin.  Not utilizing wp_localize_script().
 * Updated to latest version of SmartBanners JS - https://github.com/jasny/jquery.smartbanner
 * Added in extra functionality to detect Facebook and Twitter in iOS webview. Thanks so much to asadowski10 for the snippet!
 * Minified all JS and CSS files, but included unminified versions in case folks need to tweak.

= 1.5.7 =
* Ensured that script files are properly injected into the footer by utilizing the 5th flag of wp_register_script(). Thanks e2robert! .

= 1.5.6 =
* Sorry for so many errors, this should be last fix for the time being.

= 1.5.5 =
* Forced JS output via php headers, thanks again Tim.

= 1.5.4 =
* Wrapped app banners options in jQuery(document).ready() function.

= 1.5.3 =
* Fixed dependency issue with last update.  Move generated settings into a PHP file which is enqueued after jQuery is ready.

= 1.5.2 =
* Fixed issue with injecting javascript into the header instead of the footer.  Thanks Tim for the heads up on that.

= 1.5.1 =
* Fixed issue with passing strings to Javascript instead of integers.  Thanks @michael78au for the heads up.

= 1.5 =
* Fixed issue with close button on Android
* Fixed issue with if user set the banner to always show, it would still set a cookie
* Updated code to only load tags for apps that have app ids filled in.

= 1.4 =
* Updated to latest version of jQuery Smartbanner plugin
* Added in support for Windows Devices
* Various code clean up

= 1.3 =
* Ensured compatibility with WP 4.0
* Added in string escaping for fields to account for quotes and single quotes

= 1.2.1 =
* Hotfix for Android App ID typo in version 1.2

= 1.2 =
* Updated to Wordpress 3.9
* Added in additional options to setting panel

= 1.1 =
* Added number of days to keep banner hidden after closing
* Added number of days to keep banner hidden after clicking view button
* Added ability to change text on button
* Added in Settings link on Plugin Screen

= 1.1 =
* Initial Release