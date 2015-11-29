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
 * @uses authentication_api.php
 * @uses crypto_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'crypto_api.php' );

/**
 * Create an API token
 *
 * @param $p_token_name The name (description) identifying what the token is going to be used for.
 * @param integer $p_user_id The user id, or null for logged in user.
 * @return string The plain token.
 */
function api_token_create( $p_token_name, $p_user_id = null ) {
	if( is_blank( $p_token_name ) ) {
		error_parameters( lang_get( 'token_name' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_token_name = trim( $p_token_name );

	if( $p_user_id === null ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = (int)$p_user_id;
	}

	$t_plain_token = crypto_generate_uri_safe_nonce( 32 );
	$t_hash = api_token_hash( $t_plain_token );
	$t_date_created = db_now();

	$t_query = 'INSERT INTO {api_token}
					( user_id, name, hash, date_created )
					VALUES ( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query( $t_query, array( $t_user_id, (string)$t_token_name, $t_hash, $t_date_created ) );

	return $t_plain_token;
}

/**
 * Calculate the hash for the token.
 * @param $p_token The plain token.
 * @return string The one way hash for the token
 */
function api_token_hash( $p_token ) {
	# We ignore spaces in tokens.  We inject spaces for users for readability only.
	return sha1( str_replace( ' ', '', $p_token ) );
}

/**
 * Format the token for readability by having it displayed in groups of 4 letters with space between them.
 * The token will be valid if supplied with or without spaces.
 *
 * @param $p_token The token.
 * @return string The formatted token.
 */
function api_token_format( $p_token ) {
	$t_formatted_token = '';

	$t_len = strlen( $p_token );

	for ( $i = 0; $i < $t_len; $i++ ) {
		if ( $i > 0 && ( $i % 4 ) == 0 ) {
			$t_formatted_token .= ' ';
		}

		$t_formatted_token .= $p_token[$i];
	}

	return $t_formatted_token;
}

/**
 * Validate a plain token for the specified user.
 * @param $p_user_id The user id.
 * @param $p_token The plain token.
 * @return bool true valid, false otherwise.
 */
function api_token_validate( $p_user_id, $p_token ) {
	$t_encrypted_token = api_token_hash( $p_token );

	$t_query = 'SELECT * FROM {api_token} WHERE user_id=' . db_param() . ' AND hash=' . db_param();
	$t_result = db_query( $t_query, array( $p_user_id, $t_encrypted_token ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		api_token_touch( $t_row['id'] );
		return true;
	}

	return false;
}

function api_token_get_all( $p_user_id ) {
	$t_query = 'SELECT * FROM {api_token} WHERE user_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_user_id ) );

	$t_rows = array();
	while ( ( $t_row = db_fetch_array( $t_result ) ) !== false )
	{
		$t_rows[] = $t_row;
	}

	return $t_rows;
}

/**
 * Updates the last used timestamp for the api token.
 *
 * @param $p_api_token_id The token id.
 */
function api_token_touch( $p_api_token_id ) {
	$t_date_used = db_now();

	$t_query = 'UPDATE {api_token} SET date_used=' . db_param() . ' WHERE id=' . db_param();

	db_query( $t_query, array( $t_date_used, $p_api_token_id ) );
}

/**
 * Revokes the api token with the specified id
 * @param $p_api_token_id The API token id.
 * @param $p_user_id The user id or null for logged in user.
 */
function api_token_revoke( $p_api_token_id, $p_user_id = null ) {
	if( $p_user_id === null ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = (int)$p_user_id;
	}

	$t_query = 'DELETE FROM {api_token} WHERE id=' . db_param() . ' AND user_id = ' . db_param();
	db_query( $t_query, array( $p_api_token_id, $t_user_id ) );
}

/**
 * Gets the max length for the API token name.
 * @return int Maximum length for API token name.
 */
function api_token_name_max_length() {
	return 128;
}

