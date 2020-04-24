<?php
/**
 * Plugin Name: Facebook Pages for WordPress
 * Plugin URI:  http://github.com/bhdzllr
 * Description: Post from WordPress to Facebook Page (needs Facebook App on https://developers.facebook.com/)
 * Version:     1.0.0
 * Author:      bhdzllr
 * Author URI:  http://twitter.com/bhdzllr
 */

error_reporting(E_ALL);

// Abort if file is called directly.
if ( ! defined( 'WPINC' ) ) exit;

require_once 'vendor/autoload.php';

/**
 * Facebook Pages for WordPress Main Class
 */
class FBPFWP_Main {

	/** @var FBPFWP_Main|null Class instance */
	private static $instance;

	/**
	 * Constructor
	 */
	private function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, 'uninstall' );

		require_once 'fbpfwp-admin.php';
		require_once 'fbpfwp-peeker.php';

		new FBPFWP_Admin();
		new FBPFWP_Peeker();
	}

	/**
	 * Return instance if exist, else create one
	 *
	 * @return FBPFWP_Main class
	 */
	public static function getInstance() {
		if ( ! isset( self::$instance ) )
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Forbid clone from outside via `__clone()`
	 */
	private function __clone() {}
	
	/**
	 * Forbid deserialization from outside via `__wakeup()`
	 */
	private function __wakeup() {}

	/**
	 * Plugin activation
	 */
	public function activate() {
		$roleAdministrator = get_role( 'administrator' );
		$roleAdministrator->add_cap( 'fbpfwp_manage_options' );

		$roleEditor = get_role( 'editor' );
		$roleEditor->add_cap( 'fbpfwp_manage_options' );
		$roleEditor->add_cap( 'fbpfwp_not_access_options' );
		$roleEditor->add_cap( 'manage_options' );
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		$roleAdministrator = get_role( 'administrator' );
		$roleAdministrator->remove_cap( 'fbpfwp_manage_options' );

		$roleEditor = get_role( 'editor' );
		$roleEditor->remove_cap( 'fbpfwp_manage_options' );
		$roleEditor->remove_cap( 'fbpfwp_not_access_options' );
		$roleEditor->remove_cap( 'manage_options' ); 
	}

	/**
	 * Plugin uninstall
	 */
	public function uninstall() {
		delete_option( 'fbpfwp_options' );
	}

}

$fbpfwp = FBPFWP_Main::getInstance(); // Run plugin
