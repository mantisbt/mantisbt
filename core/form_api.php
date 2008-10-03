<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2008 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form API for handling tasks necessary to form security and validation.
 * Security methods are targetted to work with both GET and POST form types,
 * and should allow multiple simultaneous edits of the form to be submitted.
 *
 * @package FormAPI
 * @uses session_api.php
 */

/**
 * Generate a random security token, prefixed by date, store it in the
 * user's session, and then return the string to be used as a form element
 * element with the security token as the value.
 * @param string Form name
 * @return string Security token string
 */
function form_security_token( $p_form_name ) {
	$t_tokens = session_get( 'form_security_tokens', array() );

	# Create a new array for the form name if necessary
	if ( !isset( $t_tokens[ $p_form_name ] ) || !is_array( $t_tokens[ $p_form_name ] ) ) {
		$t_tokens[ $p_form_name ] = array();
	}

	# Generate a random security token prefixed by date.
	# mt_rand() returns an int between 0 and RAND_MAX as extra entropy
	$t_date = date( 'Ymd-' );
	$t_string = $t_date . sha1( time() . mt_rand() );

	# Add the token to the user's session
	$t_tokens[ $p_form_name ][] = $t_string;
	session_set( 'form_security_tokens', $t_tokens );

	# The token string
	return $t_string;
}

/**
 * Get a hidden form element containing a generated form security token.
 * @param string Form name
 * @return string Hidden form element to output
 */
function form_security_field( $p_form_name ) {
	$t_string = form_security_token( $p_form_name );

	# Create the form element HTML string for the security token
	$t_form_token = $p_form_name . '_token';
	$t_element = '<input type="hidden" name="%s" value="%s"/>';
	$t_element = sprintf( $t_element, $t_form_token, $t_string );

	return $t_element;
}

/**
 * Get a URL parameter containing a generated form security token.
 * @param string Form name
 * @return string Hidden form element to output
 */
function form_security_param( $p_form_name ) {
	$t_string = form_security_token( $p_form_name );

	# Create the GET parameter to be used in a URL for a secure link
	$t_form_token = $p_form_name . '_token';
	$t_param = '&%s=%s';
	$t_param = sprintf( $t_param, $t_form_token, $t_string );

	return $t_param;
}

/**
 * Validate the security token for the given form name based on tokens
 * stored in the user's session.  While checking stored tokens, any that
 * are more than 3 days old will be purged.
 * @param string Form name
 * @return boolean Form is valid
 */
function form_security_validate( $p_form_name ) {
	$t_tokens = session_get( 'form_security_tokens', array() );

	# Short-circuit if we don't have any tokens for the given form name
	if ( !isset( $t_tokens[ $p_form_name ] )
		|| !is_array( $t_tokens[ $p_form_name ] )
		|| count( $t_tokens[ $p_form_name ] ) < 1 ) {

		trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
		return false;
	}

	# Get the form input
	$t_form_token = $p_form_name . '_token';
	$t_input = gpc_get_string( $t_form_token, '' );

	# No form input
	if ( '' == $t_input ) {
		trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
		return false;
	}

	# Generate a date string of three days ago
	$t_date = date( 'Ymd', time() - (3 * 24 * 60 * 60) );

	# Check all stored security tokens
	$t_valid = false;
	foreach( $t_tokens[ $p_form_name ] as $t_token ) {
		$t_token_date = substr( $t_token, 0, 8 );

		# Newer than three days, check for match
		if ( $t_date < $t_token_date && $t_token == $t_input ) {
			$t_valid = true;
		}
	}

	if( !$t_valid ) {
		trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
	}

	return $t_valid;
}

/**
 * Purge form security tokens that are older than 3 days, or used
 * for form validation.
 * @param string Form name
 */
function form_security_purge( $p_form_name ) {
	$t_tokens = session_get( 'form_security_tokens', array() );

	# Short-circuit if we don't have any tokens for the given form name
	if ( !isset( $t_tokens[ $p_form_name ] )
		|| !is_array( $t_tokens[ $p_form_name ] )
		|| count( $t_tokens[ $p_form_name ] ) < 1 ) {

		return;
	}

	# Get the form input
	$t_form_token = $p_form_name . '_token';
	$t_input = gpc_get_string( $t_form_token, '' );

	# Generate a date string of three days ago
	$t_date = date( 'Ymd', time() - (3 * 24 * 60 * 60) );

	# Check all stored security tokens, purging old ones as necessary
	$t_tokens_kept = array();
	$t_valid = false;
	foreach( $t_tokens[ $p_form_name ] as $t_token ) {
		$t_token_date = substr( $t_token, 0, 8 );

		# Newer than three days, check for match, keep otherwise
		if ( $t_date < $t_token_date && $t_token != $t_input ) {
			$t_tokens_kept[] = $t_token;
		}
	}

	# Store only the unpurged tokens in the session
	$t_tokens[ $p_form_name ] = $t_tokens_kept;
	session_set( 'form_security_tokens', $t_tokens );

	return;
}

