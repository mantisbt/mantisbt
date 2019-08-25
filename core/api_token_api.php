<?php
# MantisBT - A PHP based bugtracking system

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
 * API Token API
 *
 * @package CoreAPI
 * @subpackage ApiTokenAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses crypto_api.php
 */

require_api( 'crypto_api.php' );

/**
 * Checks if specified user can create API tokens.
 * @param integer|null $p_user_id User id or null for current logged in user.
 * @return bool true: can create tokens, false: otherwise.
 */
function api_token_can_create( $p_user_id = null ) {
	$t_user_id = is_null( $p_user_id ) ? auth_get_current_user_id() : $p_user_id;
	return !user_is_protected( $t_user_id );
}

/**
 * Create an API token
 *
 * @param string $p_token_name The name (description) identifying what the token is going to be used for.
 * @param integer $p_user_id The user id.
 * @return string The plain token.
 * @access public
 */
function api_token_create( $p_token_name, $p_user_id ) {
	if( is_blank( $p_token_name ) ) {
		error_parameters( lang_get( 'api_token_name' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_token_name = trim( $p_token_name );
	if( mb_strlen( $t_token_name ) > DB_FIELD_SIZE_API_TOKEN_NAME ) {
		error_parameters( lang_get( 'api_token_name' ), DB_FIELD_SIZE_API_TOKEN_NAME );
		trigger_error( ERROR_FIELD_TOO_LONG, ERROR );
	}

	api_token_name_ensure_unique( $t_token_name, $p_user_id );

	$t_plain_token = crypto_generate_uri_safe_nonce( API_TOKEN_LENGTH );
	$t_hash = api_token_hash( $t_plain_token );
	$t_date_created = db_now();

	db_param_push();
	$t_query = 'INSERT INTO {api_token}
					( user_id, name, hash, date_created )
					VALUES ( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query( $t_query, array( $p_user_id, (string)$t_token_name, $t_hash, $t_date_created ) );

	return $t_plain_token;
}

/**
 * Calculate the hash for the token.
 * @param string $p_token The plain token.
 * @return string The one way hash for the token
 * @access public
 */
function api_token_hash( $p_token ) {
	return hash( 'sha256', $p_token );
}

/**
 * Checks that the specified token name is unique to the user.
 *
 * @param string $p_token_name The token name.
 * @param string $p_user_id    The user id.
 *
 * @return bool True if unique, False if token already exists
 */
function api_token_name_is_unique( $p_token_name, $p_user_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {api_token} WHERE user_id=' . db_param() . ' AND name=' . db_param();
	$t_result = db_query( $t_query, array( $p_user_id, $p_token_name ) );

	$t_row = db_fetch_array( $t_result );

	return $t_row === false;
}

/**
 * Ensure that the specified token name is unique to the user, otherwise,
 * prompt the user with an error.
 *
 * @param string $p_token_name The token name.
 * @param string $p_user_id The user id.
 */
function api_token_name_ensure_unique( $p_token_name, $p_user_id ) {
	if ( !api_token_name_is_unique( $p_token_name, $p_user_id ) ) {
		error_parameters( $p_token_name );
		trigger_error( ERROR_API_TOKEN_NAME_NOT_UNIQUE, ERROR );
	}
}

/**
 * Get user information given an API token.
 *
 * @param string $p_token The plain token.
 * @return int|bool user id or false if no match found.
 * @access public
 */
function api_token_get_user( $p_token ) {
	# If the supplied token doesn't look like a valid one, then fail the check w/o doing db lookups.
	# This is likely called from code that supports both tokens and passwords.
	if( is_blank( $p_token ) || mb_strlen( $p_token ) != API_TOKEN_LENGTH ) {
		return false;
	}

	$t_encrypted_token = api_token_hash( $p_token );

	db_param_push();

	# TODO: add an index on just the API token hash
	$t_query = 'SELECT * FROM {api_token} WHERE hash=' . db_param();
	$t_result = db_query( $t_query, array( $t_encrypted_token ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		api_token_touch( $t_row['id'] );
		return $t_row['user_id'];
	}

	return false;
}

/**
 * Validate a plain token for the specified user.
 * @param string $p_username The user name.
 * @param string $p_token The plain token.
 * @return boolean true valid username and token, false otherwise.
 * @access public
 */
function api_token_validate( $p_username, $p_token ) {
	# If the supplied token doesn't look like a valid one, then fail the check w/o doing db lookups.
	# This is likely called from code that supports both tokens and passwords.
	if( is_blank( $p_token ) || mb_strlen( $p_token ) != API_TOKEN_LENGTH ) {
		return false;
	}

	$t_user_id = user_get_id_by_name( $p_username );

	# If user is not found in the database, they don't have api tokens, we won't bother with worrying about
	# auto-creation scenario here.
	if( $t_user_id === false ) {
		return false;
	}

	$t_encrypted_token = api_token_hash( $p_token );

	db_param_push();
	$t_query = 'SELECT * FROM {api_token} WHERE user_id=' . db_param() . ' AND hash=' . db_param();
	$t_result = db_query( $t_query, array( $t_user_id, $t_encrypted_token ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		api_token_touch( $t_row['id'] );
		return true;
	}

	return false;
}

/**
 * Get all API tokens associated with the specified user.
 * @param integer $p_user_id The user id.
 * @return array Array of API token rows for owned by the user, can be empty.
 * @access public
 */
function api_token_get_all( $p_user_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {api_token} WHERE user_id=' . db_param() . ' ORDER BY date_used DESC, date_created ASC';
	$t_result = db_query( $t_query, array( $p_user_id ) );

	$t_rows = array();
	while ( ( $t_row = db_fetch_array( $t_result ) ) !== false )
	{
		$t_rows[] = $t_row;
	}

	return $t_rows;
}

/**
 * Determines whether the specified token has ever been used.
 * @param array $p_token token to check
 * @return bool True if used
 * @access public
 */
function api_token_is_used( array $p_token ) {
	return (int)$p_token['date_used'] > 1;
}

/**
 * Updates the last used timestamp for the api token.
 *
 * @param integer $p_api_token_id The token id.
 * @return void
 * @access public
 */
function api_token_touch( $p_api_token_id ) {
	$t_date_used = db_now();

	db_param_push();
	$t_query = 'UPDATE {api_token} SET date_used=' . db_param() . ' WHERE id=' . db_param();

	db_query( $t_query, array( $t_date_used, $p_api_token_id ) );
}

/**
 * Revokes the api token with the specified id
 * @param integer $p_api_token_id The API token id.
 * @param integer $p_user_id The user id.
 * @return void
 * @access public
 */
function api_token_revoke( $p_api_token_id, $p_user_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {api_token} WHERE id=' . db_param() . ' AND user_id = ' . db_param();
	db_query( $t_query, array( $p_api_token_id, $p_user_id ) );
}

