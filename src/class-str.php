<?php
/**
 * The String helpers.
 *
 * @since      1.0.0
 * @package    HuuHaDev
 * @subpackage HuuHaDev\Helpers
 * @author     HuuHaDev <admin@huuhadev.com>
 */

namespace HuuHaDev\Helpers;

/**
 * Str class.
 */
class Str {

	/** encoding used for mb_*() string functions */
	const MB_ENCODING = 'UTF-8';

	/**
	 * Returns true if the haystack string starts with needle
	 *
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public static function str_starts_with( $haystack, $needle ) {

		if ( self::multibyte_loaded() ) {

			if ( '' === $needle ) {
				return true;
			}

			return 0 === mb_strpos( $haystack, $needle, 0, self::MB_ENCODING );

		} else {

			$needle = self::str_to_ascii( $needle );

			if ( '' === $needle ) {
				return true;
			}

			return 0 === strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
		}
	}


	/**
	 * Return true if the haystack string ends with needle
	 *
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public static function str_ends_with( $haystack, $needle ) {

		if ( '' === $needle ) {
			return true;
		}

		if ( self::multibyte_loaded() ) {

			return mb_substr( $haystack, -mb_strlen( $needle, self::MB_ENCODING ), null, self::MB_ENCODING ) === $needle;

		} else {

			$haystack = self::str_to_ascii( $haystack );
			$needle   = self::str_to_ascii( $needle );

			return substr( $haystack, -strlen( $needle ) ) === $needle;
		}
	}


	/**
	 * Returns true if the needle exists in haystack
	 *
	 * Note: case-sensitive
	 *
	 * @since 2.2.0
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	public static function str_exists( $haystack, $needle ) {

		if ( self::multibyte_loaded() ) {

			if ( '' === $needle ) {
				return false;
			}

			return false !== mb_strpos( $haystack, $needle, 0, self::MB_ENCODING );

		} else {

			$needle = self::str_to_ascii( $needle );

			if ( '' === $needle ) {
				return false;
			}

			return false !== strpos( self::str_to_ascii( $haystack ), self::str_to_ascii( $needle ) );
		}
	}


	/**
	 * Truncates a given $string after a given $length if string is longer than
	 * $length. The last characters will be replaced with the $omission string
	 * for a total length not exceeding $length
	 *
	 * @since 2.2.0
	 * @param string $string text to truncate
	 * @param int $length total desired length of string, including omission
	 * @param string $omission omission text, defaults to '...'
	 * @return string
	 */
	public static function str_truncate( $string, $length, $omission = '...' ) {

		if ( self::multibyte_loaded() ) {

			// bail if string doesn't need to be truncated
			if ( mb_strlen( $string, self::MB_ENCODING ) <= $length ) {
				return $string;
			}

			$length -= mb_strlen( $omission, self::MB_ENCODING );

			return mb_substr( $string, 0, $length, self::MB_ENCODING ) . $omission;

		} else {

			$string = self::str_to_ascii( $string );

			// bail if string doesn't need to be truncated
			if ( strlen( $string ) <= $length ) {
				return $string;
			}

			$length -= strlen( $omission );

			return substr( $string, 0, $length ) . $omission;
		}
	}


	/**
	 * Returns a string with all non-ASCII characters removed. This is useful
	 * for any string functions that expect only ASCII chars and can't
	 * safely handle UTF-8. Note this only allows ASCII chars in the range
	 * 33-126 (newlines/carriage returns are stripped)
	 *
	 * @since 2.2.0
	 * @param string $string string to make ASCII
	 * @return string
	 */
	public static function str_to_ascii( $string ) {

		// strip ASCII chars 32 and under
		$string = filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW );

		// strip ASCII chars 127 and higher
		return filter_var( $string, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH );
	}


	/**
	 * Return a string with insane UTF-8 characters removed, like invisible
	 * characters, unused code points, and other weirdness. It should
	 * accept the common types of characters defined in Unicode.
	 *
	 * The following are allowed characters:
	 *
	 * p{L} - any kind of letter from any language
	 * p{Mn} - a character intended to be combined with another character without taking up extra space (e.g. accents, umlauts, etc.)
	 * p{Mc} - a character intended to be combined with another character that takes up extra space (vowel signs in many Eastern languages)
	 * p{Nd} - a digit zero through nine in any script except ideographic scripts
	 * p{Zs} - a whitespace character that is invisible, but does take up space
	 * p{P} - any kind of punctuation character
	 * p{Sm} - any mathematical symbol
	 * p{Sc} - any currency sign
	 *
	 * pattern definitions from http://www.regular-expressions.info/unicode.html
	 *
	 * @since 4.0.0
	 * @param string $string
	 * @return mixed
	 */
	public static function str_to_sane_utf8( $string ) {

		$sane_string = preg_replace( '/[^\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Zs}\p{P}\p{Sm}\p{Sc}]/u', '', $string );

		// preg_replace with the /u modifier can return null or false on failure
		return ( is_null( $sane_string ) || false === $sane_string ) ? $string : $sane_string;
	}


	/**
	 * Helper method to check if the multibyte extension is loaded, which
	 * indicates it's safe to use the mb_*() string methods
	 *
	 * @since 2.2.0
	 * @return bool
	 */
	protected static function multibyte_loaded() {

		return extension_loaded( 'mbstring' );
	}

	/**
	 * Validates whether the passed variable is a empty string.
	 *
	 * @param mixed $variable The variable to validate.
	 *
	 * @return bool Whether or not the passed value is a non-empty string.
	 */
	public static function is_empty( $variable ) {
		return empty( $variable ) || ! is_string( $variable );
	}

	/**
	 * Validates whether the passed variable is a non-empty string.
	 *
	 * @param mixed $variable The variable to validate.
	 *
	 * @return bool Whether or not the passed value is a non-empty string.
	 */
	public static function is_non_empty( $variable ) {
		return is_string( $variable ) && '' !== $variable;
	}

	/**
	 * Check if the string contains the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function contains( $needle, $haystack ) {
		return self::is_non_empty( $needle ) ? strpos( $haystack, $needle ) !== false : false;
	}

	/**
	 * Check if the string begins with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function starts_with( $needle, $haystack ) {
		return '' === $needle || substr( $haystack, 0, strlen( $needle ) ) === (string) $needle;
	}

	/**
	 * Check if the string end with the given value.
	 *
	 * @param string $needle   The sub-string to search for.
	 * @param string $haystack The string to search.
	 *
	 * @return bool
	 */
	public static function ends_with( $needle, $haystack ) {
		return '' === $needle || substr( $haystack, -strlen( $needle ) ) === (string) $needle;
	}

	/**
	 * Check the string for desired comparison.
	 *
	 * @param string $needle     The sub-string to search for.
	 * @param string $haystack   The string to search.
	 * @param string $comparison The type of comparison.
	 *
	 * @return bool
	 */
	public static function comparison( $needle, $haystack, $comparison = '' ) {

		$hash = array(
			'regex'    => 'preg_match',
			'end'      => array( __CLASS__, 'ends_with' ),
			'start'    => array( __CLASS__, 'starts_with' ),
			'contains' => array( __CLASS__, 'contains' ),
		);

		if ( $comparison && isset( $hash[ $comparison ] ) ) {
			return call_user_func( $hash[ $comparison ], $needle, $haystack );
		}

		// Exact.
		return $needle === $haystack;
	}

	/**
	 * Convert string to array with defined seprator.
	 *
	 * @param string $str String to convert.
	 * @param string $sep Seprator.
	 *
	 * @return bool|array
	 */
	public static function to_arr( $str, $sep = ',' ) {
		$parts = explode( $sep, trim( $str ) );

		return empty( $parts ) ? false : $parts;
	}

	/**
	 * Convert string to array, weed out empty elements and whitespaces.
	 *
	 * @param string $str         User-defined list.
	 * @param string $sep_pattern Separator pattern for regex.
	 *
	 * @return array
	 */
	public static function to_arr_no_empty( $str, $sep_pattern = '\r\n|[\r\n]' ) {
		$array = empty( $str ) ? array() : preg_split( '/' . $sep_pattern . '/', $str, -1, PREG_SPLIT_NO_EMPTY );
		$array = array_filter( array_map( 'trim', $array ) );

		return $array;
	}

	/**
	 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
	 *
	 * @param string $size The size.
	 *
	 * @return int
	 */
	public static function let_to_num( $size ) {
		$char = substr( $size, -1 );
		$ret  = substr( $size, 0, -1 );

		// @codingStandardsIgnoreStart
		switch ( strtoupper( $char ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}
		// @codingStandardsIgnoreEnd

		return $ret;
	}

	/**
	 * Convert a number to K, M, B, etc.
	 *
	 * @param int|double $number Number which to convert to pretty string.
	 *
	 * @return string
	 */
	public static function human_number( $number ) {

		if ( ! is_numeric( $number ) ) {
			return 0;
		}

		$negative = '';
		if ( abs( $number ) != $number ) {
			$negative = '-';
			$number   = abs( $number );
		}

		if ( $number < 1000 ) {
			return $negative ? -1 * $number : $number;
		}

		$unit  = intval( log( $number, 1000 ) );
		$units = array( '', 'K', 'M', 'B', 'T', 'Q' );

		if ( array_key_exists( $unit, $units ) ) {
			return sprintf( '%s%s%s', $negative, rtrim( number_format( $number / pow( 1000, $unit ), 1 ), '.0' ), $units[ $unit ] );
		}

		return $number;
	}

	/**
	 * Truncate text for given length.
	 *
	 * @param {string} $str    Text to truncate.
	 * @param {number} $length Length to truncate for.
	 *
	 * @return {string} Truncated text.
	 */
	public static function truncate( $str, $length = 110 ) {
		$str     = wp_strip_all_tags( $str, true );
		$excerpt = mb_substr( $str, 0, $length );

		// Remove part of an entity at the end.
		$excerpt = preg_replace( '/&[^;\s]{0,6}$/', '', $excerpt );
		if ( $str !== $excerpt ) {
			$strrpos = function_exists( 'mb_strrpos' ) ? 'mb_strrpos' : 'strrpos';
			$excerpt = mb_substr( $str, 0, $strrpos( trim( $excerpt ), ' ' ) );
		}

		return $excerpt;
	}
}
