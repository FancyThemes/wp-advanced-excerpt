<?php

// Do not use outside the Loop!
function the_advanced_excerpt( $args = '', $get = false ) {
	global $advanced_excerpt;
	if ( !empty( $args ) && !is_array( $args ) ) {
		$args = wp_parse_args( $args );

		// Parse query style parameters
		if ( isset( $args['ellipsis'] ) ) {
			$args['ellipsis'] = urldecode( $args['ellipsis'] );
		}

		if ( isset( $args['allowed_tags'] ) ) {
			$args['allowed_tags'] = preg_split( '/[\s,]+/', $args['allowed_tags'] );
		}

		if ( isset( $args['exclude_tags'] ) ) {
			$args['exclude_tags'] = preg_split( '/[\s,]+/', $args['exclude_tags'] );
		}
	}

	// convert legacy arg use_words to it's udpated equivalent
	if ( isset( $args['use_words'] ) ) {
		$args['length_type'] = ( 1 == $args['use_words'] ) ? 'words' : 'characters';
		unset( $args['use_words'] );
	}

	// convert legacy args finish_word & finish_sentence to their udpated equivalents
	if ( isset( $args['finish_word'] ) || isset( $args['finish_sentence'] ) ) {

		$defaults = array(
			'finish_word' => 0,
			'finish_sentence' => 0
		);

		$args = wp_parse_args( $args, $defaults );

		if ( 0 == $args['finish_word'] && 0 == $args['finish_sentence'] ) {
			$args['finish'] = 'exact';
		} else if ( 1 == $args['finish_word'] && 1 == $args['finish_sentence'] ) {
			$args['finish'] = 'sentence';
		} else if ( 0 == $args['finish_word'] && 1 == $args['finish_sentence'] ) {
			$args['finish'] = 'sentence';
		} else {
			$args['finish'] = 'word';
		}

		unset( $args['finish_word'] );
		unset( $args['finish_sentence'] );
	}

	if ( ! empty( $args['allowed_tags'] ) || ! empty( $args['exclude_tags'] ) ) {
		if ( isset( $args['allowed_tags'] ) && ! in_array( '_all', (array) $args['allowed_tags'] ) ) {
			$args['allowed_tags_option'] = 'remove_all_tags_except';
		} else if ( ! isset( $args['allowed_tags'] ) ) {
			$args['allowed_tags_option'] = 'remove_all_tags_except';
		}
	}

	// Set temporary options
	$advanced_excerpt->options = wp_parse_args( $args, $advanced_excerpt->options );

	// Ensure our filter is hooked, regardless of the page type
	if ( ! has_filter( 'get_the_excerpt', array( $advanced_excerpt, 'filter_excerpt' ) ) ) {
		remove_all_filters( 'get_the_excerpt' );
		remove_all_filters( 'the_excerpt' );
		add_filter( 'get_the_excerpt', array( $advanced_excerpt, 'filter_excerpt' ) );
	}

	if ( $get ) {
		return get_the_excerpt();
	} else {
		the_excerpt();
	}

	// Reset the options back to their original state
	$advanced_excerpt->load_options();
}