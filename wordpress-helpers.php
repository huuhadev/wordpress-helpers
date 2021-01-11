<?php
/**
 * WordPress Helpers
 *
 * @package      HuuHaDev\Helpers
 * @copyright    Copyright (C) 2018, HuuHaDev - info@huuhadev.com
 * @link         https://huuhadev.com
 * @since        1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Helpers
 * Version:           1.0.5
 * Plugin URI:        https://huuhadev.com/plugins/wordpress-helpers/
 * Description:       Collection of utilities required during development of a plugin or theme for WordPress. Build for developers by developers.
 * Author:            HuuHaDev
 * Author URI:        https://huuhadev.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.0
 * Tested up to:      5.5.3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class Autoloading.
 */
include dirname( __FILE__ ) . '/vendor/autoload.php';
