<?php

use Facebook\Facebook;

/**
 * Admin class for Back-End stuff
 */
class FBPFWP_Admin {

	/** @var array $options Array for plugin options */ 
	private $options = array();

	/** @var object $fb Facebook API Object */
	private $fb;

	/** @var array $fbOptions Array with relevant options for Facebook API */
	private $fbOptions = array(
		'appId'               => false,
		'appSecret'           => false,
		'defaultGraphVersion' => 'v3.0',
		'pageId'              => false,
		'accessToken'         => false,
		'permissions'         => [ 'manage_pages', 'publish_pages' ],
		'callback'            => false,
		'loginUrl'            => false
	);

	/**
	 * Construct class for hooks
	 */
	public function __construct() {
		add_action( 'admin_init',                  array( $this, 'admin_init' ) );
		add_action( 'admin_menu',                  array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts',       array( $this, 'load_stylesnscripts' ) );
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_fb_checkbox' ) );
		add_action( 'save_post',                   array( $this, 'save_fb_state' ) );
		add_action( 'before_delete_post',          array( $this, 'delete_fb_post' ) );

		add_action( 'load-options-general.php',    array( $this, 'prevent_editor_access' ) );
		add_action( 'load-options-writing.php',    array( $this, 'prevent_editor_access' ) );
		add_action( 'load-options-reading.php',    array( $this, 'prevent_editor_access' ) );
		add_action( 'load-options-discussion.php', array( $this, 'prevent_editor_access' ) );
		add_action( 'load-options-media.php',      array( $this, 'prevent_editor_access' ) );
		add_action( 'load-options-permalink.php',  array( $this, 'prevent_editor_access' ) );
		add_action( 'load-options.php',            array( $this, 'prevent_editor_access' ) );
	}

	/**
	 * Initialize admin area
	 */
	public function admin_init() {
		if ( ! current_user_can( 'fbpfwp_manage_options' ) ) return; 

		$this->options = get_option( 'fbpfwp_options', true );

		$this->init_fb();
	}

	/**
	 * Add settings page
	 */
	public function register_settings() {
		register_setting( 'fbpfwp_options', 'fbpfwp_options', array( $this, 'sanitze_options' ) );
		add_menu_page( 'Facebook Pages for WordPress', 'Facebook Pages for WordPress', 'fbpfwp_manage_options', 'fbpfwp', array( $this, 'render_settings_page' ) );
	}

	/**
	 * Validate options
	 */
	public function sanitize_options( $input ) {
		return $input;
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		require_once 'templates/options.php';
	}

	/**
	 * Load styles and scripts
	 */
	public function load_stylesnscripts() {
		wp_enqueue_style( 'fbpfwp-admin', plugin_dir_url( __FILE__ ) . 'css/fbpfwp-admin.css' );
	}

	/**
	 * Add Facebook publish checkbox to publish box for posts
	 */
	public function add_fb_checkbox() {
		if ( ! $this->fbOptions['accessToken'] ) return;

		$postId = get_the_ID();

		if ( get_post_type( $postId ) != 'post' ) {
			return;
		}

		$checked = get_post_meta( $postId, 'fbpfwp_publish', true );
		include_once 'templates/fb-checkbox.php';
	}

	/**
	 * Prevent users with cap "fbpfwp_not_access_options" to access options pages.
	 * They need the role "manage_options" so authorize Facebook.
	 */
	public function prevent_editor_access() {
		if ( current_user_can( 'fbpfwp_not_access_options' ) ) {
			wp_die( 'Sorry, you are not allowed to change these options.' );
			exit();
		}
	}

	/**
	 * Save state of Facebook publish checkbox
	 */
	public function save_fb_state() {
		$postId = get_the_ID();

		if ( isset ( $_POST['fbpfwp_publish'] ) ) {
			update_post_meta( $postId, 'fbpfwp_publish', $_POST['fbpfwp_publish'] );

			$this->upsert_fb_state( $postId );
		} else {
			$this->delete_fb_post( $postId );

			update_post_meta( $postId, 'fbpfwp_publish', 'off' );
			delete_post_meta( $postId, 'fbpfwp_id' );
		}
	}

	/**
	 * Initialize Facebook API and check for login callback
	 */
	private function init_fb() {
		session_start();

		if ( ! empty( $this->options['app_id'] )       ) $this->fbOptions['appId'] = $this->options['app_id'];
		if ( ! empty( $this->options['app_secret'] )   ) $this->fbOptions['appSecret'] = $this->options['app_secret'];
		if ( ! empty( $this->options['page_id']      ) ) $this->fbOptions['pageId'] = $this->options['page_id'];
		if ( ! empty( $this->options['access_token'] ) ) $this->fbOptions['accessToken'] = $this->options['access_token'];

		if ( isset( $this->options['app_id'] ) && isset( $this->options['app_secret'] ) ) {
			$this->fb = new Facebook([
				'app_id' => $this->fbOptions['appId'],
				'app_secret' => $this->fbOptions['appSecret'],
				'default_graph_version' => $this->fbOptions['defaultGraphVersion'],
			]);
		
			$helper = $this->fb->getRedirectLoginHelper();

			try {
				if ( ! empty( $this->options['access_token'] ) ) {
					$accessToken = $this->options['access_token'];
				} else {
					$accessToken = $helper->getAccessToken();
				}
			} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}

			if ( isset( $accessToken ) ) {
				if ( empty( $this->options['access_token'] ) ) {
					$oAuth2Client = $this->fb->getOAuth2Client();
					$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken( (string) $accessToken );

					try {
						$response = $this->fb->get( '/' . $this->fbOptions['pageId'] . '?fields=access_token', (string) $longLivedAccessToken );
						$pageToken = $response->getDecodedBody()['access_token'];
					} catch ( Exception $e ) {
						echo 'Facebook SDK returned an error: ' . $e->getMessage();
						exit;
					}

					$this->options['access_token'] = $pageToken;
					$this->fbOptions['accessToken'] = $pageToken;

					update_option( 'fbpfwp_options', $this->options );
				}

				$this->fb->setDefaultAccessToken( $this->options['access_token'] );
			} else {
				$this->fbOptions['callback'] = admin_url( 'options-general.php?page=fbpfwp' );
				$this->fbOptions['loginUrl'] = $helper->getLoginUrl($this->fbOptions['callback'], $this->fbOptions['permissions']);	
			}
		}
	}

	/**
	 * Publish to Facebook
	 */
	private function upsert_fb_state( $postId ) {
		$post = get_post( $postId );
		$format = get_post_format( $postId );
		$data = [];

		if ( empty( $post->post_excerpt ) ) {
			// $excerpt = wp_trim_words( get_the_content(), 55 );
			$excerpt = $post->post_content;
			$excerpt = strip_tags( $excerpt );
			$excerpt = strip_shortcodes( $excerpt );
			$excerpt = trim( $excerpt );
		} else {
			$excerpt = strip_tags( $post->post_excerpt );
			$excerpt = str_replace( "", "'", $excerpt );
			$excerpt = trim( $excerpt );
		}

		$data['message'] = $excerpt;
		$data['link'] = get_permalink( $postId );

		if ( $format == 'aside' ) unset( $data['link'] );

		// 'appsecret_proof' => hash_hmac(
		// 	'sha256',
		// 	$this->fbOptions['accessToken'],
		// 	$this->fbOptions['appSecret']
		// )

		if ( $post->post_status === 'publish' || $post->post_status === 'future' ) {
			$fbId = get_post_meta( $postId, 'fbpfwp_id', true );

			if ( $fbId ) { // Update post
				if ( $post->post_status === 'future') {
					$data['scheduled_publish_time'] = mysql2date( 'U', $post->post_date_gmt, false );
				} elseif ( $post->post_status === 'publish' ) {
					$data['is_published'] = true;
					unset( $data['scheduled_publish_time'] );
				}

				try {
					$response = $this->fb->post( '/' . $fbId, $data, $this->fbOptions['accessToken'] );
				} catch ( Exception $e ) {
					echo $e->getMessage();
					exit;
				}
			} elseif ( $post->post_status === 'future' ) { // New post scheduled
				$data['published'] = false;
				$data['scheduled_publish_time'] = mysql2date( 'U', $post->post_date_gmt, false );

				try {
					$response = $this->fb->post( '/' . $this->fbOptions['pageId'] . '/feed', $data, $this->fbOptions['accessToken'] );
					update_post_meta( $postId, 'fbpfwp_id', $response->getDecodedBody()['id'] );
				} catch ( Exception $e ) {
					echo $e->getMessage();
					exit;
				}
			} elseif ( $post->post_status === 'publish' ) { // New post instantly
				try {
					$response = $this->fb->post( '/' . $this->fbOptions['pageId'] . '/feed', $data, $this->fbOptions['accessToken'] );
					update_post_meta( $postId, 'fbpfwp_id', $response->getDecodedBody()['id'] );
				} catch ( Exception $e ) {
					echo $e->getMessage();
					exit;
				}
			}		
		}
	}

	/**
	 * Delete post from Facebook.
	 */
	public function delete_fb_post( $postId ) {
		$fbId = get_post_meta( $postId, 'fbpfwp_id', true );
		if ( ! $fbId ) return;

		try {
			$response = $this->fb->delete( '/' . $fbId, array(), $this->fbOptions['accessToken'] );
		} catch ( Facebook\Exceptions\FacebookResponseException $e ) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch ( Facebook\Exceptions\FacebookSDKException $e ) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		} catch ( Exception $e ) {
			// echo $e->getMessage();
			// exit;

			return; // This allows to delete the Facebook Id on the post if there are problems
		}
	}

}
