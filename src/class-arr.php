<?php
/**
 * The Array helpers.
 *
 * @since      1.0.0
 * @package    HuuHaDev
 * @subpackage HuuHaDev\Helpers
 * @author     HuuHaDev <admin@huuhadev.com>
 */

namespace HuuHaDev\Helpers;

use ArrayAccess;

/**
 * Arr class.
 */
class Arr {

	/**
	 * Insert the given element after the given key in the array
	 *
	 * Sample usage:
	 *
	 * given
	 *
	 * array( 'item_1' => 'foo', 'item_2' => 'bar' )
	 *
	 * array_insert_after( $array, 'item_1', array( 'item_1.5' => 'w00t' ) )
	 *
	 * becomes
	 *
	 * array( 'item_1' => 'foo', 'item_1.5' => 'w00t', 'item_2' => 'bar' )
	 *
	 * @since 2.2.0
	 * @param array $array array to insert the given element into
	 * @param string $insert_key key to insert given element after
	 * @param array $element element to insert into array
	 * @return array
	 */
	public static function array_insert_after( Array $array, $insert_key, Array $element ) {

		$new_array = array();

		foreach ( $array as $key => $value ) {

			$new_array[ $key ] = $value;

			if ( $insert_key == $key ) {

				foreach ( $element as $k => $v ) {
					$new_array[ $k ] = $v;
				}
			}
		}

		return $new_array;
	}


	/**
	 * Convert array into XML by recursively generating child elements
	 *
	 * First instantiate a new XML writer object:
	 *
	 * $xml = new XMLWriter();
	 *
	 * Open in memory (alternatively you can use a local URI for file output)
	 *
	 * $xml->openMemory();
	 *
	 * Then start the document
	 *
	 * $xml->startDocument( '1.0', 'UTF-8' );
	 *
	 * Don't forget to end the document and output the memory
	 *
	 * $xml->endDocument();
	 *
	 * $your_xml_string = $xml->outputMemory();
	 *
	 * @since 2.2.0
	 * @param \XMLWriter $xml_writer XML writer instance
	 * @param string|array $element_key name for element, e.g. <per_page>
	 * @param string|array $element_value value for element, e.g. 100
	 * @return string generated XML
	 */
	public static function array_to_xml( $xml_writer, $element_key, $element_value = array() ) {

		if ( is_array( $element_value ) ) {

			// handle attributes
			if ( '@attributes' === $element_key ) {
				foreach ( $element_value as $attribute_key => $attribute_value ) {

					$xml_writer->startAttribute( $attribute_key );
					$xml_writer->text( $attribute_value );
					$xml_writer->endAttribute();
				}
				return;
			}

			// handle multi-elements (e.g. multiple <Order> elements)
			if ( is_numeric( key( $element_value ) ) ) {

				// recursively generate child elements
				foreach ( $element_value as $child_element_key => $child_element_value ) {

					$xml_writer->startElement( $element_key );

					foreach ( $child_element_value as $sibling_element_key => $sibling_element_value ) {
						self::array_to_xml( $xml_writer, $sibling_element_key, $sibling_element_value );
					}

					$xml_writer->endElement();
				}

			} else {

				// start root element
				$xml_writer->startElement( $element_key );

				// recursively generate child elements
				foreach ( $element_value as $child_element_key => $child_element_value ) {
					self::array_to_xml( $xml_writer, $child_element_key, $child_element_value );
				}

				// end root element
				$xml_writer->endElement();
			}

		} else {

			// handle single elements
			if ( '@value' == $element_key ) {

				$xml_writer->text( $element_value );

			} else {

				// wrap element in CDATA tags if it contains illegal characters
				if ( false !== strpos( $element_value, '<' ) || false !== strpos( $element_value, '>' ) ) {

					$xml_writer->startElement( $element_key );
					$xml_writer->writeCdata( $element_value );
					$xml_writer->endElement();

				} else {

					$xml_writer->writeElement( $element_key, $element_value );
				}

			}

			return;
		}
	}


	/** Number helper functions *******************************************/


	/**
	 * Format a number with 2 decimal points, using a period for the decimal
	 * separator and no thousands separator.
	 *
	 * Commonly used for payment gateways which require amounts in this format.
	 *
	 * @since 3.0.0
	 * @param float $number
	 * @return string
	 */
	public static function number_format( $number ) {

		return number_format( (float) $number, 2, '.', '' );
	}

	/**
	 * Determine whether the given value is array accessible.
	 *
	 * @param mixed $value Value to check.
	 *
	 * @return bool
	 */
	public static function accessible( $value ) {
		return is_array( $value ) || $value instanceof ArrayAccess;
	}

	/**
	 * Determine if the given key exists in the provided array.
	 *
	 * @param ArrayAccess|array $array Array to check key in.
	 * @param string|int        $key   Key to check for.
	 *
	 * @return bool
	 */
	public static function exists( $array, $key ) {
		if ( $array instanceof ArrayAccess ) {
			// @codeCoverageIgnoreStart
			return $array->offsetExists( $key );
			// @codeCoverageIgnoreEnd
		}

		return array_key_exists( $key, $array );
	}

	/**
	 * Check whether an array or [[\Traversable]] contains an element.
	 *
	 * This method does the same as the PHP function [in_array()](https://secure.php.net/manual/en/function.in-array.php)
	 * but additionally works for objects that implement the [[\Traversable]] interface.
	 *
	 * @throws \InvalidArgumentException If `$array` is neither traversable nor an array.
	 *
	 * @param array|\Traversable $array  The set of values to search.
	 * @param mixed              $search The value to look for.
	 * @param bool               $strict Whether to enable strict (`===`) comparison.
	 *
	 * @return bool `true` if `$search` was found in `$array`, `false` otherwise.
	 */
	public static function includes( $array, $search, $strict = true ) {
		if ( $array instanceof \Traversable ) {
			return self::includes_traversable( $array, $search, $strict );
		}

		$is_array = is_array( $array );
		if ( ! $is_array ) {
			throw new \InvalidArgumentException( 'Argument $array must be an array or implement Traversable' );
		}

		return $is_array ? in_array( $search, $array, $strict ) : false; // phpcs:ignore
	}

	/**
	 * Check Traversable contains an element.
	 *
	 * @param \Traversable $array  The set of values to search.
	 * @param mixed        $search The value to look for.
	 * @param bool         $strict Whether to enable strict (`===`) comparison.
	 *
	 * @return bool `true` if `$search` was found in `$array`, `false` otherwise.
	 */
	private static function includes_traversable( $array, $search, $strict = true ) {
		foreach ( $array as $value ) {
			if ( ( $strict && $search === $value ) || $search == $value ) { // phpcs:ignore
				return true;
			}
		}

		return false;
	}

	/**
	 * Insert a single array item inside another array at a set position
	 *
	 * @param array $array    Array to modify. Is passed by reference, and no return is needed.
	 * @param array $new      New array to insert.
	 * @param int   $position Position in the main array to insert the new array.
	 */
	public static function insert( &$array, $new, $position ) {
		$before = array_slice( $array, 0, $position - 1 );
		$after  = array_diff_key( $array, $before );
		$array  = array_merge( $before, $new, $after );
	}

	/**
	 * Push an item onto the beginning of an array.
	 *
	 * @param array $array Array to add.
	 * @param mixed $value Value to add.
	 * @param mixed $key   Add with this key.
	 */
	public static function prepend( &$array, $value, $key = null ) {
		if ( is_null( $key ) ) {
			array_unshift( $array, $value );
			return;
		}

		$array = [ $key => $value ] + $array;
	}

	/**
	 * Update array add or delete value
	 *
	 * @param array $array Array to modify. Is passed by reference, and no return is needed.
	 * @param array $value Value to add or delete.
	 */
	public static function add_delete_value( &$array, $value ) {
		if ( ( $key = array_search( $value, $array ) ) !== false ) { // @codingStandardsIgnoreLine
			unset( $array[ $key ] );
			return;
		}

		$array[] = $value;
	}
}
