<?php
/**
 * The Attachment helpers.
 *
 * @since      1.0.0
 * @package    HuuHaDev
 * @subpackage HuuHaDev\Helpers
 * @author     HuuHaDev <admin@huuhadev.com>
 */

namespace HuuHaDev\Helpers;

/**
 * Attachment class.
 */
class Attachment {

	/**
	 * Grabs an image alt text.
	 *
	 * @param int $attachment_id The attachment ID.
	 *
	 * @return string The image alt text.
	 */
	public static function get_alt_tag( $attachment_id ) {
		return (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	}

	/**
	 * Get the relative path of the image.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $img Image URL.
	 *
	 * @return string The expanded image URL.
	 */
	public static function get_relative_path( $img ) {
		if ( '/' !== $img[0] ) {
			return $img;
		}

		// If it's a relative URL, it's relative to the domain, not necessarily to the WordPress install, we
		// want to preserve domain name and URL scheme (http / https) though.
		$parsed_url = wp_parse_url( home_url() );

		return $parsed_url['scheme'] . '://' . $parsed_url['host'] . $img;
	}

	/**
	 * Find an attachment ID for a given URL.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $url The URL to find the attachment for.
	 *
	 * @return int The found attachment ID, or 0 if none was found.
	 */
	public static function get_by_url( $url ) {
		// Because get_by_url won't work on resized versions of images, we strip out the size part of an image URL.
		$url = preg_replace( '/(.*)-\d+x\d+\.(jpg|png|gif)$/', '$1.$2', $url );

		$id = function_exists( 'wpcom_vip_attachment_url_to_postid' ) ? wpcom_vip_attachment_url_to_postid( $url ) : self::url_to_postid( $url );

		return absint( $id );
	}

	/**
	 * Implements the attachment_url_to_postid with use of WP Cache.
	 *
	 * @codeCoverageIgnore
	 *
	 * @link https://dotlayer.com/20-wordpress-core-functions-that-dont-scale-and-how-to-work-around-it/
	 *
	 * @param string $url The attachment URL for which we want to know the Post ID.
	 *
	 * @return int The Post ID belonging to the attachment, 0 if not found.
	 */
	private static function url_to_postid( $url ) {
		$cache_key = sprintf( 'huuhadev_attachment_url_post_id_%s', md5( $url ) );

		// Set the ID based on the hashed url in the cache.
		$id = wp_cache_get( $cache_key );

		if ( 'not_found' === $id ) {
			return 0;
		}

		// ID is found in cache, return.
		if ( false !== $id ) {
			return $id;
		}

		// phpcs:ignore WordPress.VIP.RestrictedFunctions -- We use the WP COM version if we can, see above.
		$id = attachment_url_to_postid( $url );

		if ( empty( $id ) ) {
			wp_cache_set( $cache_key, 'not_found', 'default', ( 12 * HOUR_IN_SECONDS + mt_rand( 0, ( 4 * HOUR_IN_SECONDS ) ) ) );
			return 0;
		}

		// We have the Post ID, but it's not in the cache yet. We do that here and return.
		wp_cache_set( $cache_key, $id, 'default', ( 24 * HOUR_IN_SECONDS + mt_rand( 0, ( 12 * HOUR_IN_SECONDS ) ) ) );

		return $id;
	}
}
