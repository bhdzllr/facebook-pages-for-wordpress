<?php

/**
 * Peeker class to allow Facebook crawlers access to scheduled posts.
 */
class FBPFWP_Peeker {

	private $fbBots = [
		'facebookexternalhit/1.1 (+https://www.facebook.com/externalhit_uatext.php)',
		'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
		'facebookexternalhit/1.1',
		'Facebot'
	];

	private $originalPosts;

	/**
	 * Construct class for hooks
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Add hook for Facebook Bots
	 */
	public function init() {
		if ( ! $this->is_facebook_bot() ) {
			return;
		}

		add_filter( 'posts_results', array( $this, 'make_scheduled_posts_public' ), null, 2 );
	}

	/**
	 * Check if crawler is a Facebook bot
	 */
	private function is_facebook_bot() {
		return in_array( $_SERVER[ 'HTTP_USER_AGENT' ], $this->fbBots );
	}

	/**
	 * Add hook for public posts for Facebook bots
	 */
	public function make_scheduled_posts_public( $posts, $query ) {
		if ( sizeof( $posts ) != 1 ) {
			return $posts;
		}

		$status = get_post_status_object( get_post_status( $posts[0] ) );

		if ( $status->public ) {
			return $posts;
		}

		$this->originalPosts = $posts;

		add_filter( 'the_posts', array( $this, 'override_private' ), null, 2 );
	}

	/**
	 * Return private post vor Facebook bot
	 */
	public function override_private( $posts, $query ) {
		remove_filter( 'the_posts', [ $this, 'override_private' ], null, 2 );

		return $this->originalPosts;
	}

}
