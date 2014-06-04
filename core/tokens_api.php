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
 * Tokens API
 *
 * This implements temporary storage of strings.
 * DB schema: id, type, owner, timestamp, value
 *
 * @package CoreAPI
 * @subpackage TokensAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );

# Set up global for token_purge_expired_once()
$g_tokens_purged = false;

/**
 * Check if a token exists.
 * @param int $p_token_id Token ID
 * @return bool True if token exists
 */
function token_exists( $p_token_id ) {
	$t_tokens_table = db_get_table( 'tokens' );
	$t_query = "SELECT id FROM $t_tokens_table WHERE id=" . db_param();
	$t_result = db_query_bound( $t_query, array( $p_token_id ), 1 );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		return true;
	}
	return false;
}

/**
 * Make sure a token exists.
 * @param int $p_token_id Token ID
 * @return bool True if token exists
 */
function token_ensure_exists( $p_token_id ) {
	if( !token_exists( $p_token_id ) ) {
		trigger_error( ERROR_TOKEN_NOT_FOUND, ERROR );
	}

	return true;
}

/**
 * Get a token's information
 * @param int $p_type Token type
 * @param int $p_user_id User ID
 * @return array Token row
 */
function token_get( $p_type, $p_user_id = null ) {
	token_purge_expired_once();

	$c_type = (int)$p_type;
	$c_user_id = (int)( $p_user_id == null ? auth_get_current_user_id() : $p_user_id );

	$t_tokens_table = db_get_table( 'tokens' );

	$t_query = "SELECT * FROM $t_tokens_table
					WHERE type=" . db_param() . " AND owner=" . db_param();
	$t_result = db_query_bound( $t_query, array( $c_type, $c_user_id ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		return $t_row;
	}

	return null;
}

/**
 * Get a token's value or null if not found
 * @param int $p_type Token type
 * @param int $p_user_id User ID (null for current user)
 * @return array Token row
 */
function token_get_value( $p_type, $p_user_id = null ) {
	$t_token = token_get( $p_type, $p_user_id );

	if( null !== $t_token ) {
		return $t_token['value'];
	}

	return null;
}

/**
 * Create or update a token's value and expiration
 * @param int $p_type Token type
 * @param string $p_value Token value
 * @param int $p_expiry Token expiration in seconds
 * @param int $p_user_id User ID
 * @return int Token ID
 */
function token_set( $p_type, $p_value, $p_expiry = TOKEN_EXPIRY, $p_user_id = null ) {
	$t_token = token_get( $p_type, $p_user_id );
	if( $t_token === null ) {
		return token_create( $p_type, $p_value, $p_expiry, $p_user_id );
	}

	token_update( $t_token['id'], $p_value, $p_expiry );
	return $t_token['id'];
}

/**
 * Touch a token to update its expiration time.
 * @param int $p_token_id Token ID
 * @param int $p_expiry Token expiration in seconds
 */
function token_touch( $p_token_id, $p_expiry = TOKEN_EXPIRY ) {
	token_ensure_exists( $p_token_id );

	$c_token_expiry = time() + $p_expiry;
	$t_tokens_table = db_get_table( 'tokens' );
	$t_query = "UPDATE $t_tokens_table SET expiry=" . db_param() . " WHERE id=" . db_param();
	db_query_bound( $t_query, array( $c_token_expiry, $p_token_id ) );
}

/**
 * Delete a token.
 * @param int $p_type Token type
 * @param int $p_user_id User ID or null for current logged in user.
 */
function token_delete( $p_type, $p_user_id = null ) {
	$c_user_id = db_prepare_int( $p_user_id == null ? auth_get_current_user_id() : $p_user_id );

	$t_tokens_table = db_get_table( 'tokens' );
	$t_query = "DELETE FROM $t_tokens_table WHERE type=" . db_param() . " AND owner=" . db_param();
	db_query_bound( $t_query, array( $p_type, $c_user_id ) );
}

/**
 * Delete all tokens owned by a specified user.
 * @param int $p_user_id User ID or null for current logged in user.
 */
function token_delete_by_owner( $p_user_id = null ) {
	if( $p_user_id == null ) {
		$c_user_id = auth_get_current_user_id();
	} else {
		$c_user_id = (int)$p_user_id;
	}

	# Remove
	$t_tokens_table = db_get_table( 'tokens' );
	$t_query = "DELETE FROM $t_tokens_table WHERE owner=" . db_param();
	db_query_bound( $t_query, array( $c_user_id ) );
}

/**
 * Create a token.
 * @param int $p_type Token type
 * @param string $p_value Token value
 * @param int $p_expiry Token expiration in seconds
 * @param int $p_user_id User ID
 * @return int Token ID
 */
function token_create( $p_type, $p_value, $p_expiry = TOKEN_EXPIRY, $p_user_id = null ) {
	$c_type = (int)$p_type;
	$c_timestamp = db_now();
	$c_expiry = time() + $p_expiry;
	$c_user_id = (int)( $p_user_id == null ? auth_get_current_user_id() : $p_user_id );

	$t_tokens_table = db_get_table( 'tokens' );

	$t_query = "INSERT INTO $t_tokens_table
					( type, value, timestamp, expiry, owner )
					VALUES ( " . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query_bound( $t_query, array( $c_type, (string)$p_value, $c_timestamp, $c_expiry, $c_user_id ) );
	return db_insert_id( $t_tokens_table );
}

/**
 * Update a token
 * @param int $p_token_id Token ID
 * @param string $p_value Token value
 * @param int $p_expiry Token expiration in seconds
 * @return bool always true.
 */
function token_update( $p_token_id, $p_value, $p_expiry = TOKEN_EXPIRY ) {
	token_ensure_exists( $p_token_id );
	$c_token_id = (int)$p_token_id;
	$c_expiry = time() + $p_expiry;

	$t_tokens_table = db_get_table( 'tokens' );
	$t_query = "UPDATE $t_tokens_table
					SET value=" . db_param() . ", expiry=" . db_param() . "
					WHERE id=" . db_param();
	db_query_bound( $t_query, array( (string)$p_value, $c_expiry, $c_token_id ) );

	return true;
}

/**
 * Delete all tokens of a specified type.
 * @param int $p_token_type Token Type
 * @return bool always true.
 */
function token_delete_by_type( $p_token_type ) {

	# Remove
	$t_tokens_table = db_get_table( 'tokens' );
	$t_query = "DELETE FROM $t_tokens_table WHERE type=" . db_param();
	db_query_bound( $t_query, array( $p_token_type ) );

	return true;
}

/**
 * Purge all expired tokens.
 * @param int $p_token_type Token type
 * @return bool always true.
 */
function token_purge_expired( $p_token_type = null ) {
	global $g_tokens_purged;

	$t_tokens_table = db_get_table( 'tokens' );

	$t_query = "DELETE FROM $t_tokens_table WHERE " . db_param() . " > expiry";
	if( !is_null( $p_token_type ) ) {
		$c_token_type = db_prepare_int( $p_token_type );
		$t_query .= " AND type=" . db_param();
		db_query_bound( $t_query, array( db_now(), $c_token_type ) );
	} else {
		db_query_bound( $t_query, array( db_now() ) );
	}

	$g_tokens_purged = true;

	return true;
}

/**
 * Purge all expired tokens only once per session.
 */
function token_purge_expired_once() {
	global $g_tokens_purged;
	if( !$g_tokens_purged ) {
		token_purge_expired();
	}
}
