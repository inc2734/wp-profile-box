<?php
/**
 * @package inc2734/wp-profile-box
 * @author inc2734
 * @license GPL-2.0+
 */

namespace Inc2734\WP_Profile_Box;

class Bootstrap {

	/**
	 * Constructor.
	 */
	public function __construct() {
		load_textdomain( 'inc2734-wp-profile-box', __DIR__ . '/languages/' . get_locale() . '.mo' );

		add_filter( 'user_contactmethods', [ $this, '_add_detail_url_field' ] );
		add_filter( 'user_contactmethods', [ $this, '_add_sns_account_fields' ] );

		add_shortcode( 'wp_profile_box', [ $this, '_shortcode' ] );
	}

	/**
	 * Adds detail url field
	 *
	 * @param array $methods Array of contact method labels keyed by contact method.
	 * @return array
	 */
	public function _add_detail_url_field( $methods = [] ) {
		return array_merge(
			$methods,
			[
				'detail' => __( 'Detail page', 'inc2734-wp-profile-box' ),
			]
		);
	}

	/**
	 * Adds sns account fields.
	 *
	 * @param array $methods Array of contact method labels keyed by contact method.
	 * @return array
	 */
	public function _add_sns_account_fields( $methods = [] ) {
		$sns_accounts = $this->_sns_accounts();
		unset( $sns_accounts['url'] );
		$methods = array_merge( $methods, $sns_accounts );

		return $methods;
	}

	/**
	 * Adds SNS accounts settings.
	 *
	 * @return array
	 */
	protected function _sns_accounts() {
		return apply_filters(
			'inc2734_wp_profile_box_sns_accounts',
			[
				'url'       => __( 'Web Site', 'inc2734-wp-profile-box' ),
				'twitter'   => __( 'Twitter', 'inc2734-wp-profile-box' ),
				'facebook'  => __( 'Facebook', 'inc2734-wp-profile-box' ),
				'instagram' => __( 'Instagram', 'inc2734-wp-profile-box' ),
				'pinterest' => __( 'Pinterest', 'inc2734-wp-profile-box' ),
				'youtube'   => __( 'YouTube', 'inc2734-wp-profile-box' ),
				'linkedin'  => __( 'Linkedin', 'inc2734-wp-profile-box' ),
				'wordpress' => __( 'WordPress', 'inc2734-wp-profile-box' ),
				'tumblr'    => __( 'Tumblr', 'inc2734-wp-profile-box' ),
				'amazon'    => __( 'Amazon', 'inc2734-wp-profile-box' ),
				'line'      => __( 'LINE', 'inc2734-wp-profile-box' ),
			]
		);
	}

	/**
	 * Registers shortcode.
	 *
	 * @param array $attributes The shortcode attributes.
	 */
	public function _shortcode( $attributes = [] ) {
		$attributes = shortcode_atts(
			[
				'title'             => __( 'Bio', 'inc2734-wp-profile-box' ),
				'in_same_post_type' => false,
				'user_id'           => get_the_author_meta( 'ID' ),
			],
			$attributes
		);

		ob_start();
		?>
		<div class="wp-profile-box">
			<?php if ( ! empty( $attributes['title'] ) ) : ?>
				<h2 class="wp-profile-box__title"><?php echo esc_html( $attributes['title'] ); ?></h2>
			<?php endif; ?>

			<div class="wp-profile-box__container">
				<div class="wp-profile-box__figure">
					<?php echo get_avatar( $attributes['user_id'], apply_filters( 'inc2734_wp_profile_box_avatar_size', 96 ) ); ?>
				</div>
				<div class="wp-profile-box__body">
					<h3 class="wp-profile-box__name">
						<?php echo wp_kses_post( get_the_author_meta( 'display_name', $attributes['user_id'] ) ); ?>
					</h3>
					<div class="wp-profile-box__content">
						<?php echo wp_kses_post( wpautop( get_the_author_meta( 'description', $attributes['user_id'] ) ) ); ?>
					</div>

					<div class="wp-profile-box__buttons">
						<?php
						$detail_url = $this->_get_detail_page_url( $attributes['user_id'] );
						?>
						<?php if ( $detail_url ) : ?>
							<a class="wp-profile-box__detail-btn" href="<?php echo esc_url( $detail_url ); ?>">
								<?php esc_html_e( 'Detail page', 'inc2734-wp-profile-box' ); ?>
							</a>
						<?php endif; ?>

						<?php
						$author_posts_url = get_author_posts_url( $attributes['user_id'] );
						if ( $attributes['in_same_post_type'] ) {
							$author_posts_url_query_string = parse_url( $author_posts_url, PHP_URL_QUERY );
							if ( $author_posts_url_query_string ) {
								$author_posts_url = str_replace(
									'?' . $author_posts_url_query_string,
									'',
									$author_posts_url
								);
							}
							parse_str( $author_posts_url_query_string, $author_posts_url_query );
							$author_posts_url_query['post_type'] = get_post_type();
							$author_posts_url                   .= '?' . http_build_query( $author_posts_url_query, '', '&amp;' );
						}
						?>
						<a class="wp-profile-box__archives-btn" href="<?php echo esc_url( $author_posts_url ); ?>">
							<?php esc_html_e( 'Archives', 'inc2734-wp-profile-box' ); ?>
						</a>
					</div>

					<?php
					$sns_account_labels = $this->_sns_accounts();

					$sns_accounts = $this->_get_sns_accounts( $attributes['user_id'] );
					?>
					<?php if ( $sns_accounts ) : ?>
						<ul class="wp-profile-box__sns-accounts">
							<?php foreach ( $sns_accounts as $key => $url ) : ?>
								<li class="wp-profile-box__sns-accounts-item wp-profile-box__sns-accounts-item--<?php echo esc_attr( $key ); ?>"><a href="<?php echo esc_url( $url ); ?>" target="_blank"><?php echo wp_kses_post( $sns_account_labels[ $key ] ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Returns detail page URL.
	 *
	 * @param int $user_id User ID.
	 * @return string
	 */
	protected function _get_detail_page_url( $user_id ) {
		$detail_keys = $this->_add_detail_url_field();
		$detail_keys = array_keys( $detail_keys );
		foreach ( $detail_keys as $key ) {
			$detail_url = get_the_author_meta( $key, $user_id );
			if ( $detail_url ) {
				return $detail_url;
			}
		}
	}

	/**
	 * Returns SNS account URLs.
	 *
	 * @param int $user_id User ID.
	 * @return array
	 */
	protected function _get_sns_accounts( $user_id ) {
		$sns_accounts     = $this->_sns_accounts();
		$sns_account_keys = array_keys( $sns_accounts );

		$sns_accounts = [];

		foreach ( $sns_account_keys as $sns_account_key ) {
			$sns_account = get_the_author_meta( $sns_account_key, $user_id );
			if ( ! $sns_account ) {
				continue;
			}
			$sns_accounts[ $sns_account_key ] = $sns_account;
		}

		return $sns_accounts;
	}
}
