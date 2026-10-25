<?php
/**
 * Profile box integration tests.
 */
class Profile_Box_Test extends WP_UnitTestCase {

	/**
	 * @var Inc2734\WP_Profile_Box\Bootstrap
	 */
	private $profile_box;

	public function set_up() {
		parent::set_up();
		$this->profile_box = new Inc2734\WP_Profile_Box\Bootstrap();
	}

	public function tear_down() {
		remove_filter( 'init', array( $this->profile_box, '_init' ) );
		remove_filter( 'user_contactmethods', array( $this->profile_box, '_add_detail_url_field' ) );
		remove_filter( 'user_contactmethods', array( $this->profile_box, '_add_sns_account_fields' ) );
		remove_shortcode( 'wp_profile_box' );
		wp_reset_postdata();
		parent::tear_down();
	}

	public function test_registers_contact_fields_without_removing_existing_fields() {
		$methods = wp_get_user_contact_methods();

		foreach ( array( 'detail', 'twitter', 'x', 'facebook', 'threads', 'instagram', 'pinterest', 'youtube', 'linkedin', 'wordpress', 'tumblr', 'amazon', 'line' ) as $key ) {
			$this->assertArrayHasKey( $key, $methods );
		}

		$methods = $this->profile_box->_add_detail_url_field( array( 'existing' => 'Existing' ) );
		$this->assertSame( 'Existing', $methods['existing'] );
	}

	public function test_shortcode_displays_profile_information_and_links() {
		$user_id = self::factory()->user->create(
			array(
				'display_name' => 'Profile Author',
				'description'  => "First paragraph.\n\nSecond paragraph.",
				'user_url'     => 'https://example.com/',
			)
		);
		update_user_meta( $user_id, 'detail', 'https://example.com/about/?a=1&b=2' );
		update_user_meta( $user_id, 'x', 'https://x.com/example' );

		$html = do_shortcode( '[wp_profile_box user_id="' . $user_id . '" title="About the author"]' );

		$this->assertStringContainsString( '<h2 class="wp-profile-box__title">About the author</h2>', $html );
		$this->assertStringContainsString( 'Profile Author', $html );
		$this->assertStringContainsString( '<p>First paragraph.</p>', $html );
		$this->assertStringContainsString( '<p>Second paragraph.</p>', $html );
		$this->assertStringContainsString( 'href="' . esc_url( 'https://example.com/about/?a=1&b=2' ) . '"', $html );
		$this->assertStringContainsString( 'href="' . esc_url( get_author_posts_url( $user_id ) ) . '"', $html );
		$this->assertStringContainsString( 'href="https://example.com/"', $html );
		$this->assertStringContainsString( 'href="https://x.com/example"', $html );
		$this->assertStringNotContainsString( 'wp-profile-box__sns-accounts-item--twitter', $html );
	}

	public function test_omits_optional_title_detail_and_social_accounts() {
		$user_id = self::factory()->user->create();
		$html    = do_shortcode( '[wp_profile_box user_id="' . $user_id . '" title=""]' );

		$this->assertStringNotContainsString( 'wp-profile-box__title', $html );
		$this->assertStringNotContainsString( 'wp-profile-box__detail-btn', $html );
		$this->assertStringNotContainsString( 'wp-profile-box__sns-accounts"', $html );
		$this->assertStringContainsString( 'wp-profile-box__archives-btn', $html );
	}

	public function test_uses_current_post_author_by_default() {
		$user_id = self::factory()->user->create( array( 'display_name' => 'Current Post Author' ) );
		$post_id = self::factory()->post->create( array( 'post_author' => $user_id ) );
		$this->go_to( get_permalink( $post_id ) );
		the_post();

		$html = do_shortcode( '[wp_profile_box]' );

		$this->assertStringContainsString( 'Current Post Author', $html );
		$this->assertStringContainsString( '<h2 class="wp-profile-box__title">Bio</h2>', $html );
	}

	public function test_filters_archive_link_by_current_post_type() {
		$user_id = self::factory()->user->create();
		$post_id = self::factory()->post->create( array( 'post_author' => $user_id ) );
		$this->go_to( get_permalink( $post_id ) );
		the_post();

		$html = do_shortcode( '[wp_profile_box user_id="' . $user_id . '" in_same_post_type="1"]' );

		$this->assertStringContainsString( 'href="' . esc_url( add_query_arg( 'post_type', 'post', get_author_posts_url( $user_id ) ) ) . '"', $html );
	}

	public function test_escapes_title_and_rejects_unsafe_links() {
		$user_id = self::factory()->user->create();
		update_user_meta( $user_id, 'detail', 'javascript:alert(1)' );
		update_user_meta( $user_id, 'x', 'javascript:alert(2)' );
		$html = $this->profile_box->_shortcode(
			array(
				'user_id' => $user_id,
				'title'   => '<script>alert("title")</script>',
			)
		);

		$this->assertStringContainsString( esc_html( '<script>alert("title")</script>' ), $html );
		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringNotContainsString( 'javascript:', $html );
	}

	public function test_supports_custom_social_account_filter() {
		$user_id = self::factory()->user->create();
		update_user_meta( $user_id, 'custom_social', 'https://example.com/social/' );
		$filter = static function ( $accounts ) {
			$accounts['custom_social'] = 'Custom Social';
			return $accounts;
		};
		add_filter( 'inc2734_wp_profile_box_sns_accounts', $filter );

		try {
			$this->assertArrayHasKey( 'custom_social', wp_get_user_contact_methods() );
			$html = do_shortcode( '[wp_profile_box user_id="' . $user_id . '"]' );
			$this->assertStringContainsString( 'wp-profile-box__sns-accounts-item--custom_social', $html );
			$this->assertStringContainsString( 'href="https://example.com/social/" target="_blank">Custom Social</a>', $html );
		} finally {
			remove_filter( 'inc2734_wp_profile_box_sns_accounts', $filter );
		}
	}

	public function test_supports_avatar_size_filter() {
		$user_id = self::factory()->user->create();
		$filter  = static function () {
			return 48;
		};
		add_filter( 'inc2734_wp_profile_box_avatar_size', $filter );

		try {
			$html = do_shortcode( '[wp_profile_box user_id="' . $user_id . '"]' );
			$this->assertStringContainsString( 'avatar-48', $html );
		} finally {
			remove_filter( 'inc2734_wp_profile_box_avatar_size', $filter );
		}
	}
}
