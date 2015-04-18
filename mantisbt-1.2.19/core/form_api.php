<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form API for handling tasks necessary to form security and validation.
 * Security methods are targetted to work with both GET and POST form types,
 * and should allow multiple simultaneous edits of the form to be submitted.
 *
 * @package CoreAPI
 * @subpackage FormAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses session_api.php
 */

/**
 * Helper function to generate a form action value when forms are designed
 * to be submitted to the same url that's is currently being used, such as
 * helper_ensure_confirmed() or auth_reauthenticate().
 * @return string Form action value
 */
function form_action_self() {
	$t_self = trim( str_replace( "\0", '', $_SERVER['SCRIPT_NAME'] ) );
	return basename( $t_self );
}

/**
 * Generate a random security token, prefixed by date, store it in the
 * user's session, and then return the string to be used as a form element
 * element with the security token as the value.
 * @param string Form name
 * @return string Security token string
 */
function form_security_token( $p_form_name ) {
	if ( PHP_CLI == php_mode() || OFF == config_get_global( 'form_security_validation' ) ) {
		return '';
	}

	$t_tokens = session_get( 'form_security_tokens', array() );

	# Create a new array for the form name if necessary
	if( !isset( $t_tokens[$p_form_name] ) || !is_array( $t_tokens[$p_form_name] ) ) {
		$t_tokens[$p_form_name] = array();
	}

	# Generate a random security token prefixed by date.
	# mt_rand() returns an int between 0 and RAND_MAX as extra entropy
	$t_date = date( 'Ymd' );
	$t_string = $t_date . sha1( time() . mt_rand() );

	# Add the token to the user's session
	if ( !isset( $t_tokens[$p_form_name][$t_date] ) ) {
		$t_tokens[$p_form_name][$t_date] = array();
	}

	$t_tokens[$p_form_name][$t_date][$t_string] = true;
	session_set( 'form_security_tokens', $t_tokens );

	# The token string
	return $t_string;
}

/**
 * Get a hidden form element containing a generated form security token.
 * @param string $p_form_name Form name
 * @param string $p_security_token Optional security token, previously generated for the same form
 * @return string Hidden form element to output
 */
function form_security_field( $p_form_name, $p_security_token = null ) {
	if ( PHP_CLI == php_mode() || OFF == config_get_global( 'form_security_validation' ) ) {
		return '';
	}

	if( is_null( $p_security_token ) ) {
		$p_security_token = form_security_token( $p_form_name );
	}

	# Create the form element HTML string for the security token
	$t_form_token = $p_form_name . '_token';
	$t_element = sprintf(
		'<input type="hidden" name="%s" value="%s"/>',
		$t_form_token,
		$p_security_token
	);

	return $t_element;
}

/**
 * Get a URL parameter containing a generated form security token.
 * @param string Form name
 * @return string Hidden form element to output
 */
function form_security_param( $p_form_name ) {
	if ( PHP_CLI == php_mode() || OFF == config_get_global( 'form_security_validation' ) ) {
		return '';
	}

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
	if ( PHP_CLI == php_mode() || OFF == config_get_global( 'form_security_validation' ) ) {
		return true;
	}

	$t_tokens = session_get( 'form_security_tokens', array() );

	# Short-circuit if we don't have any tokens for the given form name
	if( !isset( $t_tokens[$p_form_name] ) || !is_array( $t_tokens[$p_form_name] ) || count( $t_tokens[$p_form_name] ) < 1 ) {

		trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
		return false;
	}

	# Get the form input
	$t_form_token = $p_form_name . '_token';
	$t_input = gpc_get_string( $t_form_token, '' );

	# No form input
	if( '' == $t_input ) {
		trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
		return false;
	}

	# Get the date claimed by the token
	$t_date = utf8_substr( $t_input, 0, 8 );

	# Check if the token exists
	if ( isset( $t_tokens[$p_form_name][$t_date][$t_input] ) ) {
		return true;
	}

	# Token does not exist
	trigger_error( ERROR_FORM_TOKEN_INVALID, ERROR );
	return false;
}

/**
 * Purge form security tokens that are older than 3 days, or used
 * for form validation.
 * @param string Form name
 */
function form_security_purge( $p_form_name ) {
	if ( PHP_CLI == php_mode() || OFF == config_get_global( 'form_security_validation' ) ) {
		return;
	}

	$t_tokens = session_get( 'form_security_tokens', array() );

	# Short-circuit if we don't have any tokens for the given form name
	if( !isset( $t_tokens[$p_form_name] ) || !is_array( $t_tokens[$p_form_name] ) || count( $t_tokens[$p_form_name] ) < 1 ) {
		return;
	}

	# Get the form input
	$t_form_token = $p_form_name . '_token';
	$t_input = gpc_get_string( $t_form_token, '' );

	# Get the date claimed by the token
	$t_date = utf8_substr( $t_input, 0, 8 );

	# Generate a date string of three days ago
	$t_purge_date = date( 'Ymd', time() - ( 3 * 24 * 60 * 60 ) );

	# Purge old token data, and the currently-used token
	unset( $t_tokens[$p_form_name][$t_date][$t_input] );

	foreach( $t_tokens as $t_form_name => $t_dates ) {
		foreach( $t_dates as $t_date => $t_date_tokens ) {
			if ( $t_date < $t_purge_date ) {
				unset( $t_tokens[$t_form_name][$t_date] );
			}
		}
	}

	session_set( 'form_security_tokens', $t_tokens );

	return;
}
