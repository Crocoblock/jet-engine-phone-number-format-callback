<?php
/**
 * Plugin Name: JetEngine - Converts phone number format
 * Plugin URI: #
 * Description: Adds a new callback to the Dynamic Field widget that allows you to convert a phone number to a phone number mask format.
 * Version:     1.1.1
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

add_filter( 'jet-engine/listings/allowed-callbacks', 'jet_engine_add_pnf_callback', 10, 2 );
add_filter( 'jet-engine/listing/dynamic-field/callback-args', 'jet_engine_add_pnf_callback_args', 10, 3 );
add_filter( 'jet-engine/listings/allowed-callbacks-args', 'jet_engine_add_pnf_callback_controls' );

function jet_engine_add_pnf_callback( $callbacks ) {
	$callbacks['jet_engine_pnf'] = __( 'Phone number format', 'jet-engine-phone-number-format-callback' );

	return $callbacks;
}

function jet_engine_pnf_get_pattern_from_str( $pattern, $str ) {
	if ( preg_match_all( $pattern, $str, $matches ) ) {
		return implode( $matches[0] );
	}

	return '';
}

function jet_engine_pnf( $value, $mask = '+9 (999) 999-9999' ) {
	if ( empty( $value ) ) {
		return $value;
	}

	$mask               = preg_replace( '/\d/', '%n', $mask );
	$patterns_from_mask = jet_engine_pnf_get_pattern_from_str( '/%n/', $mask );
	$numbers_from_str   = jet_engine_pnf_get_pattern_from_str( '/\d+/', $value );
	$arr_of_pattern     = preg_replace( '/%n/', '/%n/', str_split( $patterns_from_mask, 2 ) );
	$arr_of_replacement = str_split( $numbers_from_str );
	$result             = preg_replace( $arr_of_pattern, $arr_of_replacement, $mask, 1 );

	return $result ? $result : $value;
}

function jet_engine_add_pnf_callback_args( $args, $callback, $settings = array() ) {
	if ( 'jet_engine_pnf' === $callback ) {
		$args[] = isset( $settings['phone_number_format'] ) ? $settings['phone_number_format'] : '+9 (999) 999-9999';
	}

	return $args;
}

function jet_engine_add_pnf_callback_controls( $args = array() ) {
	$args['phone_number_format'] = array(
		'label'     => __( 'Enter mask', 'jet-engine-phone-number-format-callback' ),
		'type'      => 'text',
		'default'   => '+9 (999) 999-9999',
		'condition' => array(
			'dynamic_field_filter' => 'yes',
			'filter_callback'      => array( 'jet_engine_pnf' ),
		),
	);

	return $args;
}
