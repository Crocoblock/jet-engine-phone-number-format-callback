<?php
/**
 * Plugin Name: JetEngine - Converts phone number format
 * Plugin URI: #
 * Description: Adds a new callback to the Dynamic Field widget that allows you to convert a phone number to a phone number mask format.
 * Version:     1.1.2
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function jet_engine_pnf( $value, $mask = '+9 (999) 999-9999' ) {
	return \Jet_Engine_Phone_Number_Format::get_pnf_by_mask( $value, $mask );
}

class Jet_Engine_Phone_Number_Format {
	public function __construct() {
		add_filter( 'jet-engine/listings/allowed-callbacks', array( $this, 'add_pnf_callback' ), 10, 2 );
		add_filter( 'jet-engine/listing/dynamic-field/callback-args', array(
			$this,
			'add_pnf_callback_args'
		), 10, 3 );
		add_filter( 'jet-engine/listings/allowed-callbacks-args', array(
			$this,
			'add_pnf_callback_controls'
		) );
	}

	public function add_pnf_callback( $callbacks ) {
		$callbacks['jet_engine_pnf'] = __( 'Phone number format', 'jet-engine-phone-number-format-callback' );

		return $callbacks;
	}

	public static function get_pnf_by_mask( $value, $mask = '+9 (999) 999-9999' ) {
		if ( empty( $value ) || empty( $mask ) ) {
			return $value;
		}

		// Change each number to a pattern from the incoming mask. Example: +%n (%n%n%n) %n%n%n-%n%n%n%n
		$mask = preg_replace( '/\d/', '%n', $mask );
		// Get only patterns from the mask. Example: '%n%n%n%n...'
		$patterns_from_mask = self::get_pattern_from_str( '/%n/', $mask );
		// Get all numbers from input string. Example: input value '+12-34-56-78-90'; output value '1234...'
		$numbers_from_str = self::get_pattern_from_str( '/\d+/', $value );
		// Get both arrays by mask and by numbers
		// Example: '%n%n%n%n...' => ['%n','%n','%n','%n',....]
		$arr_of_pattern = preg_replace( '/%n/', '/%n/', str_split( $patterns_from_mask, 2 ) );
		// Example: '1234...' => ['1','2','3','4',....]
		$arr_of_replacement = str_split( $numbers_from_str );
		// Replace character by character (by mask +%n (%n%n%n) %n%n%n-%n%n%n%n)
		$result = preg_replace( $arr_of_pattern, $arr_of_replacement, $mask, 1 );

		return $result ? $result : $value;
	}

	// Retrieves all values from a string that match a pattern.
	public static function get_pattern_from_str( $pattern, $str ) {
		if ( preg_match_all( $pattern, $str, $matches ) ) {
			return implode( $matches[0] );
		}

		return '';
	}

	public function add_pnf_callback_args( $args, $callback, $settings = array() ) {
		if ( 'jet_engine_pnf' === $callback ) {
			$args[] = isset( $settings['phone_number_format'] ) ? $settings['phone_number_format'] : '+9 (999) 999-9999';
		}

		return $args;
	}

	public function add_pnf_callback_controls( $args = array() ) {
		$args['phone_number_format'] = array(
			'label'       => __( 'Enter mask', 'jet-engine-phone-number-format-callback' ),
			'type'        => 'text',
			'default'     => '+9 (999) 999-9999',
			'description' => __( 'Masking definitions - numeric [0-9]', 'jet-engine-phone-number-format-callback' ),
			'condition'   => array(
				'dynamic_field_filter' => 'yes',
				'filter_callback'      => array( 'jet_engine_pnf' ),
			),
		);

		return $args;
	}
}

new Jet_Engine_Phone_Number_Format();