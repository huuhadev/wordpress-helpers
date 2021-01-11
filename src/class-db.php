<?php
/**
 * The database helpers.
 *
 * @since      1.0.0
 * @package    HuuHaDev
 * @subpackage HuuHaDev\Helpers
 * @author     HuuHaDev <admin@huuhadev.com>
 */

namespace HuuHaDev\Helpers;

/**
 * DB class.
 */
class DB {

	/**
	 * Check if table exists in db or not.
	 *
	 * @param string $table_name Table name to check for existance.
	 *
	 * @return bool
	 */
	public static function check_table_exists( $table_name ) {
		global $wpdb;

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . $table_name ) ) ) === $wpdb->prefix . $table_name ) {
			return true;
		}

		return false;
	}
}
