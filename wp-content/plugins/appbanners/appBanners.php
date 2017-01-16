<?php

/*
  Plugin Name: App Banners
  Plugin URI: www.emoxie.com
  Description: Ability to promote iOS, Android and MS Applications with an App Banner similar to iOS6 App Banner.  Utilizes jQuery Smart Banner by Arnold Daniels <arnold@jasny.net>
  Version: 1.5.14
  Author: E-Moxie
  Author URI: www.emoxie.com
 */

if ( ! class_exists( 'AppBanners' ) ) :

	class AppBanners {

		/**
		 * Initialization function
		 */
		public static function init() {
			add_action( 'wp_enqueue_scripts', 'AppBanners_enqueue_scripts' );
			add_action( 'wp_head', 'AppBanners_Meta' );
			add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), 'AppBanners_settings_link' );

			/**
			 * If logged into administration area call the Admin functions of the AppBanners
			 */

			if ( is_admin() ) {
				require_once dirname( __FILE__ ) . '/appBanners-admin.php';
				App_Banners_Admin::init();
			}
		}

	}

	/*
	 * Scripts to be enqueued into Wordpress.  Making sure that jquery is added as a dependency
	 * for SmartBanner.js
	 */

	function AppBanners_enqueue_scripts() {
		wp_register_style( 'app-banners-styles', plugins_url( '/lib/smartbanner/jquery.smartbanner.min.css', __FILE__ ) );
		wp_enqueue_style( 'app-banners-styles' );

		//Script files are placed in Footer
		wp_register_script( 'app-banners-scripts', plugins_url( '/lib/smartbanner/jquery.smartbanner.min.js', __FILE__ ), array( 'jquery' ), false, true );
		wp_enqueue_script( 'app-banners-scripts' );

		wp_register_script( 'app-banners-custom-scripts', plugins_url( '/js/config.min.js', __FILE__ ), array( 'jquery' ), false, true );
		wp_localize_script( 'app-banners-custom-scripts', 'appBannersConfig', AppBanners_config() );

		wp_enqueue_script( 'app-banners-custom-scripts' );

	}


	function AppBanners_config() {

		$author           = htmlspecialchars( get_option( 'APP_BANNERS_author' ), ENT_QUOTES );
		$price            = get_option( 'APP_BANNERS_price' );
		$title            = htmlspecialchars( get_option( 'APP_BANNERS_title' ), ENT_QUOTES );
		$icon             = get_option( 'APP_BANNERS_icon' );
		$button           = htmlspecialchars( get_option( 'APP_BANNERS_button' ), ENT_QUOTES );
		$url              = get_option( 'APP_BANNERS_url' );
		$daysHidden       = (int) get_option( 'APP_BANNERS_daysHidden' );
		$daysReminder     = (int) get_option( 'APP_BANNERS_daysReminder' );
		$speedOut         = (int) get_option( 'APP_BANNERS_speedOut' );
		$speedIn          = (int) get_option( 'APP_BANNERS_speedIn' );
		$iconGloss        = get_option( 'APP_BANNERS_iconGloss' );

		if($iconGloss == 'true'){
			$iconGloss = (bool) 1;
		} else {
			$iconGloss = (bool) 0;
		}

		$inAppStore       = htmlspecialchars( get_option( 'APP_BANNERS_inAppStore' ), ENT_QUOTES );
		$inGooglePlay     = htmlspecialchars( get_option( 'APP_BANNERS_inGooglePlay' ), ENT_QUOTES );
		$appStoreLanguage = get_option( 'APP_BANNERS_appStoreLanguage' );
		$appendToSelector = get_option( 'APP_BANNERS_appendToSelector', 'body' );
		$pushSelector     = get_option( 'APP_BANNERS_pushSelector', 'html' );
		$printViewPort    = get_option( 'APP_BANNERS_printViewPort' );

		$options = array(
			'title'            => $title,
			'author'           => $author,
			'price'            => $price,
			'appStoreLanguage' => $appStoreLanguage,
			'inAppStore'       => $inAppStore,
			'inGooglePlay'     => $inGooglePlay,
			'inAmazonAppStore' => 'In the Amazon Appstore',
			'inWindowsStore'   => 'In the Windows Store',
			'GooglePlayParams' => null,
			'icon'             => $icon,
			'iconGloss'        => $iconGloss,
			'url'              => $url,
			'button'           => $button,
			'scale'            => 'auto',
			'speedIn'          => $speedIn,
			'speedOut'         => $speedOut,
			'daysHidden'       => $daysHidden,
			'daysReminder'     => $daysReminder,
			'force'            => null,
			'hideOnInstall'    => true,
			'layer'            => false,
			'iOSUniversalApp'  => true,
			'appendToSelector' => $appendToSelector,
			'printViewPort'    => $printViewPort,
			'pushSelector'     => $pushSelector

		);

		return $options;
	}


	/*
	 * Function to inject the default app banner meta tags into the head of the
	 * site.  Utilizing wp_head action.
	 */
	function AppBanners_Meta() {
		$appleID                  = get_option( 'APP_BANNERS_apple_id' );
		$androidID                = get_option( 'APP_BANNERS_android_id' );
		$author                   = get_option( 'APP_BANNERS_author' );
		$msApplicationID          = get_option( 'APP_BANNERS_ms_application_id' );
		$msApplicationPackageName = get_option( 'APP_BANNERS_ms_application_package_name' );
		$printViewPort            = get_option( 'APP_BANNERS_printViewPort' );

		if ( $appleID ) {
			echo '<meta name="apple-itunes-app" content="app-id=' . $appleID . '">' . PHP_EOL;
		}
		if ( $androidID ) {
			echo '<meta name="google-play-app" content="app-id=' . $androidID . '">' . PHP_EOL;
		}
		if ( $msApplicationID ) {
			echo '<meta name="msApplication-ID" content="' . $msApplicationID . '"/>' . PHP_EOL;
		}
		if ( $msApplicationPackageName ) {
			echo '<meta name="msApplication-PackageFamilyName" content="' . $msApplicationPackageName . '"/>' . PHP_EOL;
		}
		if ( $author ) {
			echo '<meta name="author" content="' . $author . '">' . PHP_EOL;
		}
		if ( $printViewPort ) {
			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
		}
	}


	/**
	 * Add in Settings link to plugin details.
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	function AppBanners_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=app-banners-plugin-options_options">Settings</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}


	AppBanners::init();


endif;
