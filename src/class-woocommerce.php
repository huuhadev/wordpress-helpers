<?php
/**
 * The Woocommerce helpers.
 *
 * @since      1.0.0
 * @package    HuuHaDev
 * @subpackage HuuHaDev\Helpers
 * @author     HuuHaDev <admin@huuhadev.com>
 */

namespace HuuHaDev\Helpers;

/**
 * Woocommervce class.
 */
class Woocommerce {

	/**
	 * Get order line items (products) in a neatly-formatted array of objects
	 * with properties:
	 *
	 * + id - item ID
	 * + name - item name, usually product title, processed through htmlentities()
	 * + description - formatted item meta (e.g. Size: Medium, Color: blue), processed through htmlentities()
	 * + quantity - item quantity
	 * + item_total - item total (line total divided by quantity, excluding tax & rounded)
	 * + line_total - line item total (excluding tax & rounded)
	 * + meta - formatted item meta array
	 * + product - item product or null if getting product from item failed
	 * + item - raw item array
	 *
	 * @since 3.0.0
	 * @param \WC_Order $order
	 * @return array
	 */
	public static function get_order_line_items( $order ) {

		$line_items = array();

		foreach ( $order->get_items() as $id => $item ) {

			$line_item = new stdClass();

			$product = $order->get_product_from_item( $item );

			$item_desc = array();

			// add SKU to description if available
			if ( is_callable( array( $product, 'get_sku' ) ) && $product->get_sku() ) {
				$item_desc[] = sprintf( 'SKU: %s', $product->get_sku() );
			}

			// get meta + format it
			$item_meta = new WC_Order_Item_Meta( $item );

			$item_meta = $item_meta->get_formatted();

			if ( ! empty( $item_meta ) ) {

				foreach ( $item_meta as $meta ) {
					$item_desc[] = sprintf( '%s: %s', $meta['label'], $meta['value'] );
				}
			}

			$item_desc = implode( ', ', $item_desc );

			$line_item->id          = $id;
			$line_item->name        = htmlentities( $item['name'], ENT_QUOTES, 'UTF-8', false );
			$line_item->description = htmlentities( $item_desc, ENT_QUOTES, 'UTF-8', false );
			$line_item->quantity    = $item['qty'];
			$line_item->item_total  = isset( $item['recurring_line_total'] ) ? $item['recurring_line_total'] : $order->get_item_total( $item );
			$line_item->line_total  = $order->get_line_total( $item );
			$line_item->meta        = $item_meta;
			$line_item->product     = is_object( $product ) ? $product : null;
			$line_item->item        = $item;

			$line_items[] = $line_item;
		}

		return $line_items;
	}


	/**
	 * Safely get and trim data from $_POST
	 *
	 * @since 3.0.0
	 * @param string $key array key to get from $_POST array
	 * @return string value from $_POST or blank string if $_POST[ $key ] is not set
	 */
	public static function get_post( $key ) {

		if ( isset( $_POST[ $key ] ) ) {
			return trim( $_POST[ $key ] );
		}

		return '';
	}


	/**
	 * Safely get and trim data from $_REQUEST
	 *
	 * @since 3.0.0
	 * @param string $key array key to get from $_REQUEST array
	 * @return string value from $_REQUEST or blank string if $_REQUEST[ $key ] is not set
	 */
	public static function get_request( $key ) {

		if ( isset( $_REQUEST[ $key ] ) ) {
			return trim( $_REQUEST[ $key ] );
		}

		return '';
	}


	/**
	 * Get the count of notices added, either for all notices (default) or for one
	 * particular notice type specified by $notice_type.
	 *
	 * WC notice functions are not available in the admin
	 *
	 * @since 3.0.2
	 * @param string $notice_type The name of the notice type - either error, success or notice. [optional]
	 * @return int
	 */
	public static function wc_notice_count( $notice_type = '' ) {

		if ( function_exists( 'wc_notice_count' ) ) {
			return wc_notice_count( $notice_type );
		}

		return 0;
	}


	/**
	 * Add and store a notice.
	 *
	 * WC notice functions are not available in the admin
	 *
	 * @since 3.0.2
	 * @param string $message The text to display in the notice.
	 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
	 */
	public static function wc_add_notice( $message, $notice_type = 'success' ) {

		if ( function_exists( 'wc_add_notice' ) ) {
			wc_add_notice( $message, $notice_type );
		}
	}


	/**
	 * Print a single notice immediately
	 *
	 * WC notice functions are not available in the admin
	 *
	 * @since 3.0.2
	 * @param string $message The text to display in the notice.
	 * @param string $notice_type The singular name of the notice type - either error, success or notice. [optional]
	 */
	public static function wc_print_notice( $message, $notice_type = 'success' ) {

		if ( function_exists( 'wc_print_notice' ) ) {
			wc_print_notice( $message, $notice_type );
		}
	}


	/**
	 * Gets the full URL to the log file for a given $handle
	 *
	 * @since 4.0.0
	 * @param string $handle log handle
	 * @return string URL to the WC log file identified by $handle
	 */
	public static function get_wc_log_file_url( $handle ) {
		return admin_url( sprintf( 'admin.php?page=wc-status&tab=logs&log_file=%s-%s-log', $handle, sanitize_file_name( wp_hash( $handle ) ) ) );
	}


	/** JavaScript helper functions ***************************************/


	/**
	 * Enhanced search JavaScript (Select2)
	 *
	 * Enqueues JavaScript required for AJAX search with Select2.
	 *
	 * Example usage:
	 *    <input type="hidden" class="sv-wc-enhanced-search" name="category_ids" data-multiple="true" style="min-width: 300px;"
	 *       data-action="wc_cart_notices_json_search_product_categories"
	 *       data-nonce="<?php echo wp_create_nonce( 'search-categories' ); ?>"
	 *       data-request_data = "<?php echo esc_attr( json_encode( array( 'field_name' => 'something_exciting', 'default' => 'default_label' ) ) ) ?>"
	 *       data-placeholder="<?php esc_attr_e( 'Search for a category&hellip;', 'wc-cart-notices' ) ?>"
	 *       data-allow_clear="true"
	 *       data-selected="<?php
	 *          $json_ids    = array();
	 *          if ( isset( $notice->data['categories'] ) ) {
	 *             foreach ( $notice->data['categories'] as $value => $title ) {
	 *                $json_ids[ esc_attr( $value ) ] = esc_html( $title );
	 *             }
	 *          }
	 *          echo esc_attr( json_encode( $json_ids ) );
	 *       ?>"
	 *       value="<?php echo implode( ',', array_keys( $json_ids ) ); ?>" />
	 *
	 * - `data-selected` can be a json encoded associative array like Array( 'key' => 'value' )
	 * - `value` should be a comma-separated list of selected keys
	 * - `data-request_data` can be used to pass any additional data to the AJAX request
	 *
	 * @codeCoverageIgnore no need to unit test this since it's mostly JS
	 * @since 3.1.0
	 */
	public static function render_select2_ajax() {

		if ( ! did_action( 'sv_wc_select2_ajax_rendered' ) ) {

			$javascript = "( function(){
					if ( ! $().select2 ) return;
				";

			// ensure localized strings are used
			$javascript .= "
					function getEnhancedSelectFormatString() {

						if ( 'undefined' !== typeof wc_select_params ) {
							wc_enhanced_select_params = wc_select_params;
						}

						if ( 'undefined' === typeof wc_enhanced_select_params ) {
							return {};
						}

						var formatString = {
							formatMatches: function( matches ) {
								if ( 1 === matches ) {
									return wc_enhanced_select_params.i18n_matches_1;
								}

								return wc_enhanced_select_params.i18n_matches_n.replace( '%qty%', matches );
							},
							formatNoMatches: function() {
								return wc_enhanced_select_params.i18n_no_matches;
							},
							formatAjaxError: function( jqXHR, textStatus, errorThrown ) {
								return wc_enhanced_select_params.i18n_ajax_error;
							},
							formatInputTooShort: function( input, min ) {
								var number = min - input.length;

								if ( 1 === number ) {
									return wc_enhanced_select_params.i18n_input_too_short_1
								}

								return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', number );
							},
							formatInputTooLong: function( input, max ) {
								var number = input.length - max;

								if ( 1 === number ) {
									return wc_enhanced_select_params.i18n_input_too_long_1
								}

								return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', number );
							},
							formatSelectionTooBig: function( limit ) {
								if ( 1 === limit ) {
									return wc_enhanced_select_params.i18n_selection_too_long_1;
								}

								return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', number );
							},
							formatLoadMore: function( pageNumber ) {
								return wc_enhanced_select_params.i18n_load_more;
							},
							formatSearching: function() {
								return wc_enhanced_select_params.i18n_searching;
							}
						};

						return formatString;
					}
				";

			// add Select2 ajax call
			$javascript .= "
					$( ':input.sv-wc-enhanced-search' ).filter( ':not(.enhanced)' ).each( function() {
						var select2_args = {
							allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
							placeholder: $( this ).data( 'placeholder' ),
							minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
							escapeMarkup: function( m ) {
								return m;
							},
							ajax: {
								url:         '" . admin_url( 'admin-ajax.php' ) . "',
								dataType:    'json',
								quietMillis: 250,
								data: function( term, page ) {
									return {
										term:         term,
										request_data: $( this ).data( 'request_data' ) ? $( this ).data( 'request_data' ) : {},
										action:       $( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
										security:     $( this ).data( 'nonce' )
									};
								},
								results: function( data, page ) {
									var terms = [];
									if ( data ) {
										$.each( data, function( id, text ) {
											terms.push( { id: id, text: text } );
										});
									}
									return { results: terms };
								},
								cache: true
							}
						};
						if ( $( this ).data( 'multiple' ) === true ) {
							select2_args.multiple = true;
							select2_args.initSelection = function( element, callback ) {
								var data     = $.parseJSON( element.attr( 'data-selected' ) );
								var selected = [];

								$( element.val().split( ',' ) ).each( function( i, val ) {
									selected.push( { id: val, text: data[ val ] } );
								});
								return callback( selected );
							};
							select2_args.formatSelection = function( data ) {
								return '<div class=\"selected-option\" data-id=\"' + data.id + '\">' + data.text + '</div>';
							};
						} else {
							select2_args.multiple = false;
							select2_args.initSelection = function( element, callback ) {
								var data = {id: element.val(), text: element.attr( 'data-selected' )};
								return callback( data );
							};
						}

						select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

						$( this ).select2( select2_args ).addClass( 'enhanced' );
					});
				";

			$javascript .= "} )();";

			wc_enqueue_js( $javascript );

			/**
			 * WC Select2 Ajax Rendered Action.
			 *
			 * Fired when an Ajax select2 is rendered.
			 *
			 * @since 3.1.0
			 */
			do_action( 'sv_wc_select2_ajax_rendered' );
		}
	}


	/** Framework translation functions ***********************************/


	/**
	 * Gettext `__()` wrapper for framework-translated strings
	 *
	 * Warning! This function should only be used if an existing
	 * translation from the framework is to be used. It should
	 * never be called for plugin-specific or untranslated strings!
	 * Untranslated = not registered via string literal.
	 *
	 * @since 4.1.0
	 * @param string $text
	 * @return string translated text
	 */
	public static function f__( $text ) {

		return __( $text, 'woocommerce-plugin-framework' );
	}


	/**
	 * Gettext `_e()` wrapper for framework-translated strings
	 *
	 * Warning! This function should only be used if an existing
	 * translation from the framework is to be used. It should
	 * never be called for plugin-specific or untranslated strings!
	 * Untranslated = not registered via string literal.
	 *
	 * @since 4.1.0
	 * @param string $text
	 */
	public static function f_e( $text ) {

		_e( $text, 'woocommerce-plugin-framework' );
	}


	/**
	 * Gettext `_x()` wrapper for framework-translated strings
	 *
	 * Warning! This function should only be used if an existing
	 * translation from the framework is to be used. It should
	 * never be called for plugin-specific or untranslated strings!
	 * Untranslated = not registered via string literal.
	 *
	 * @since 4.1.0
	 * @param string $text
	 * @return string translated text
	 */
	public static function f_x( $text, $context ) {

		return _x( $text, $context, 'woocommerce-plugin-framework' );
	}


	/** Misc functions ****************************************************/


	/**
	 * Convert a 2-character country code into its 3-character equivalent, or
	 * vice-versa, e.g.
	 *
	 * 1) given USA, returns US
	 * 2) given US, returns USA
	 *
	 * @since 4.2.0
	 * @param string $code ISO-3166-alpha-2 or ISO-3166-alpha-3 country code
	 * @return string country code
	 */
	public static function convert_country_code( $code ) {

		// ISO 3166-alpha-2 => ISO 3166-alpha3
		$countries = array(
			'AF' => 'AFG', 'AL' => 'ALB', 'DZ' => 'DZA', 'AD' => 'AND', 'AO' => 'AGO',
			'AG' => 'ATG', 'AR' => 'ARG', 'AM' => 'ARM', 'AU' => 'AUS', 'AT' => 'AUT',
			'AZ' => 'AZE', 'BS' => 'BHS', 'BH' => 'BHR', 'BD' => 'BGD', 'BB' => 'BRB',
			'BY' => 'BLR', 'BE' => 'BEL', 'BZ' => 'BLZ', 'BJ' => 'BEN', 'BT' => 'BTN',
			'BO' => 'BOL', 'BA' => 'BIH', 'BW' => 'BWA', 'BR' => 'BRA', 'BN' => 'BRN',
			'BG' => 'BGR', 'BF' => 'BFA', 'BI' => 'BDI', 'KH' => 'KHM', 'CM' => 'CMR',
			'CA' => 'CAN', 'CV' => 'CPV', 'CF' => 'CAF', 'TD' => 'TCD', 'CL' => 'CHL',
			'CN' => 'CHN', 'CO' => 'COL', 'KM' => 'COM', 'CD' => 'COD', 'CG' => 'COG',
			'CR' => 'CRI', 'CI' => 'CIV', 'HR' => 'HRV', 'CU' => 'CUB', 'CY' => 'CYP',
			'CZ' => 'CZE', 'DK' => 'DNK', 'DJ' => 'DJI', 'DM' => 'DMA', 'DO' => 'DOM',
			'EC' => 'ECU', 'EG' => 'EGY', 'SV' => 'SLV', 'GQ' => 'GNQ', 'ER' => 'ERI',
			'EE' => 'EST', 'ET' => 'ETH', 'FJ' => 'FJI', 'FI' => 'FIN', 'FR' => 'FRA',
			'GA' => 'GAB', 'GM' => 'GMB', 'GE' => 'GEO', 'DE' => 'DEU', 'GH' => 'GHA',
			'GR' => 'GRC', 'GD' => 'GRD', 'GT' => 'GTM', 'GN' => 'GIN', 'GW' => 'GNB',
			'GY' => 'GUY', 'HT' => 'HTI', 'HN' => 'HND', 'HU' => 'HUN', 'IS' => 'ISL',
			'IN' => 'IND', 'ID' => 'IDN', 'IR' => 'IRN', 'IQ' => 'IRQ', 'IE' => 'IRL',
			'IL' => 'ISR', 'IT' => 'ITA', 'JM' => 'JAM', 'JP' => 'JPN', 'JO' => 'JOR',
			'KZ' => 'KAZ', 'KE' => 'KEN', 'KI' => 'KIR', 'KP' => 'PRK', 'KR' => 'KOR',
			'KW' => 'KWT', 'KG' => 'KGZ', 'LA' => 'LAO', 'LV' => 'LVA', 'LB' => 'LBN',
			'LS' => 'LSO', 'LR' => 'LBR', 'LY' => 'LBY', 'LI' => 'LIE', 'LT' => 'LTU',
			'LU' => 'LUX', 'MK' => 'MKD', 'MG' => 'MDG', 'MW' => 'MWI', 'MY' => 'MYS',
			'MV' => 'MDV', 'ML' => 'MLI', 'MT' => 'MLT', 'MH' => 'MHL', 'MR' => 'MRT',
			'MU' => 'MUS', 'MX' => 'MEX', 'FM' => 'FSM', 'MD' => 'MDA', 'MC' => 'MCO',
			'MN' => 'MNG', 'ME' => 'MNE', 'MA' => 'MAR', 'MZ' => 'MOZ', 'MM' => 'MMR',
			'NA' => 'NAM', 'NR' => 'NRU', 'NP' => 'NPL', 'NL' => 'NLD', 'NZ' => 'NZL',
			'NI' => 'NIC', 'NE' => 'NER', 'NG' => 'NGA', 'NO' => 'NOR', 'OM' => 'OMN',
			'PK' => 'PAK', 'PW' => 'PLW', 'PA' => 'PAN', 'PG' => 'PNG', 'PY' => 'PRY',
			'PE' => 'PER', 'PH' => 'PHL', 'PL' => 'POL', 'PT' => 'PRT', 'QA' => 'QAT',
			'RO' => 'ROU', 'RU' => 'RUS', 'RW' => 'RWA', 'KN' => 'KNA', 'LC' => 'LCA',
			'VC' => 'VCT', 'WS' => 'WSM', 'SM' => 'SMR', 'ST' => 'STP', 'SA' => 'SAU',
			'SN' => 'SEN', 'RS' => 'SRB', 'SC' => 'SYC', 'SL' => 'SLE', 'SG' => 'SGP',
			'SK' => 'SVK', 'SI' => 'SVN', 'SB' => 'SLB', 'SO' => 'SOM', 'ZA' => 'ZAF',
			'ES' => 'ESP', 'LK' => 'LKA', 'SD' => 'SDN', 'SR' => 'SUR', 'SZ' => 'SWZ',
			'SE' => 'SWE', 'CH' => 'CHE', 'SY' => 'SYR', 'TJ' => 'TJK', 'TZ' => 'TZA',
			'TH' => 'THA', 'TL' => 'TLS', 'TG' => 'TGO', 'TO' => 'TON', 'TT' => 'TTO',
			'TN' => 'TUN', 'TR' => 'TUR', 'TM' => 'TKM', 'TV' => 'TUV', 'UG' => 'UGA',
			'UA' => 'UKR', 'AE' => 'ARE', 'GB' => 'GBR', 'US' => 'USA', 'UY' => 'URY',
			'UZ' => 'UZB', 'VU' => 'VUT', 'VA' => 'VAT', 'VE' => 'VEN', 'VN' => 'VNM',
			'YE' => 'YEM', 'ZM' => 'ZMB', 'ZW' => 'ZWE', 'TW' => 'TWN', 'CX' => 'CXR',
			'CC' => 'CCK', 'HM' => 'HMD', 'NF' => 'NFK', 'NC' => 'NCL', 'PF' => 'PYF',
			'YT' => 'MYT', 'GP' => 'GLP', 'PM' => 'SPM', 'WF' => 'WLF', 'TF' => 'ATF',
			'BV' => 'BVT', 'CK' => 'COK', 'NU' => 'NIU', 'TK' => 'TKL', 'GG' => 'GGY',
			'IM' => 'IMN', 'JE' => 'JEY', 'AI' => 'AIA', 'BM' => 'BMU', 'IO' => 'IOT',
			'VG' => 'VGB', 'KY' => 'CYM', 'FK' => 'FLK', 'GI' => 'GIB', 'MS' => 'MSR',
			'PN' => 'PCN', 'SH' => 'SHN', 'GS' => 'SGS', 'TC' => 'TCA', 'MP' => 'MNP',
			'PR' => 'PRI', 'AS' => 'ASM', 'UM' => 'UMI', 'GU' => 'GUM', 'VI' => 'VIR',
			'HK' => 'HKG', 'MO' => 'MAC', 'FO' => 'FRO', 'GL' => 'GRL', 'GF' => 'GUF',
			'MQ' => 'MTQ', 'RE' => 'REU', 'AX' => 'ALA', 'AW' => 'ABW', 'AN' => 'ANT',
			'SJ' => 'SJM', 'AC' => 'ASC', 'TA' => 'TAA', 'AQ' => 'ATA',
		);

		if ( 3 === strlen( $code ) ) {
			$countries = array_flip( $countries );
		}

		return isset( $countries[ $code ] ) ? $countries[ $code ] : $code;
	}

}
