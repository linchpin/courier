<?php
/**
 * WordPress SEO Integrations
 */

namespace CourierNotices\Controller\Integrations;

/**
 * Class WP_SEO
 *
 * @package CourierNotices\Controller
 */
class WP_SEO {

	/**
	 * Register our hooks
	 * Exclude notice related post types and taxonomies from the sitemap regardless of options.
	 *
	 * @since 1.1
	 */
	public function register_actions() {
		add_filter( 'wpseo_sitemap_exclude_post_type', array( $this, 'exclude_courier_notice_from_search' ), 10, 2 );
		add_filter( 'wpseo_sitemap_exclude_taxonomy', array( $this, 'exclude_courier_notice_taxonomies' ), 10, 2 );
	}

	/**
	 * Exclude Taxonomy From Yoast SEO Sitemap
	 *
	 * @since 1.0
	 *
	 * @param $value
	 * @param $taxonomy
	 *
	 * @return bool
	 */
	public function exclude_courier_notice_taxonomies( $value, $taxonomy ) {
		$taxonomy_to_exclude = array( 'courier_type', 'courier_scope', 'courier_placement', 'courier_status' );

		if ( in_array( $taxonomy, $taxonomy_to_exclude, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Exclude Courier Notices from WordPress SEO Sitemaps
	 *
	 * @since 1.0
	 *
	 * @param bool   $value     Default false.
	 * @param string $post_type Post type name.
	 *
	 * @return bool
	 */
	public function exclude_courier_notice_from_search( $value, $post_type ) {
		if ( 'courier_notice' === $post_type ) {
			return true;
		}

		return false;
	}
}
