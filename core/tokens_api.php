<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: tokens_api.php,v 1.11 2007-10-28 01:06:38 prichards Exp $
	# --------------------------------------------------------

	# This implements temporary storage of strings.
	# DB schema: id, type, owner, timestamp, value

	# Set up global for token_purge_expired_once()
	$g_tokens_purged = false;

	/**
	 * Check if a token exists.
	 * @param integer Token ID
	 * @return boolean True if token exists
	 */
	function token_exists( $p_token_id ) {
		$c_token_id   	= db_prepare_int( $p_token_id );
		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		$t_query 	= "SELECT id
		          	FROM $t_tokens_table
		          	WHERE id=" . db_param(0);
		$t_result	= db_query_bound( $t_query, Array( $c_token_id ), 1 );

		return( 1 == db_num_rows( $t_result ) );
	}

	/**
	 * Make sure a token exists.
	 * @param integer Token ID
	 * @return boolean True if token exists
	 */
	function token_ensure_exists( $p_token_id ) {
		if ( !token_exists( $p_token_id ) ) {
			trigger_error( ERROR_TOKEN_NOT_FOUND, ERROR );
		}

		return true;
	}

	# High-level CRUD Usage

	/**
	 * Get a token's information
	 * @param integer Token type
	 * @param integer User ID
	 * @return array Token row
	 */
	function token_get( $p_type, $p_user_id = null ) {
		token_purge_expired_once();

		$c_type = db_prepare_int( $p_type );
		$c_user_id = db_prepare_int( $p_user_id == null ? auth_get_current_user_id() : $p_user_id );

		$t_tokens_table = config_get( 'mantis_tokens_table' );

		$t_query = "SELECT * FROM $t_tokens_table 
					WHERE type=" . db_param(0) . " AND owner=" . db_param(1);
		$t_result = db_query_bound( $t_query, Array( $c_type, $c_user_id ) );

		if ( db_num_rows( $t_result ) > 0 ) {
			return db_fetch_array( $t_result );
		}

		return null;
	}

	/**
	 * Get a token's value or null if not found
	 * @param integer Token type
	 * @param integer User ID (null for current user)
	 * @return array Token row
	 */
	function token_get_value( $p_type, $p_user_id = null ) {
		$t_token = token_get( $p_type, $p_user_id );

		if ( null !== $t_token ) {
			return $t_token['value'];
		}

		return null;
	}

	/**
	 * Create or update a token's value and expiration
	 * @param integer Token type
	 * @param string Token value
	 * @param integer Token expiration in seconds
	 * @param integer User ID
	 * @return integer Token ID
	 */
	function token_set( $p_type, $p_value, $p_expiry = TOKEN_EXPIRY, $p_user_id = null ) {
		$t_token = token_get( $p_type, $p_user_id );
		if ( $t_token === null ) {
			return token_create( $p_type, $p_value, $p_expiry, $p_user_id );
		}
 
		token_update( $t_token['id'], $p_value, $p_expiry );
		return $t_token['id'];
	}

	/**
	 * Touch a token to update its expiration time.
	 * @param integer Token ID
	 * @param integer Token expiration in seconds
	 * @return always true	 
	 */
	function token_touch( $p_token_id, $p_expiry = TOKEN_EXPIRY ) {
		token_ensure_exists( $p_token_id );

		$c_token_id = db_prepare_int( $p_token_id );
		$c_token_expiry = db_timestamp( db_date( time() + $p_expiry ) );
		$t_tokens_table = config_get( 'mantis_tokens_table' );

		$t_query = "UPDATE $t_tokens_table
					SET expiry=$c_token_expiry
					WHERE id='$c_token_id'";
		db_query( $t_query, 1 );

		return true;
	}

	/**
	 * Delete a token.
	 * @param integer Token type
	 * @param integer User ID or null for current logged in user.
	 * @return always true	 
	 */
	function token_delete( $p_type, $p_user_id = null ) {
		$c_type = db_prepare_int( $p_type );
		$c_user_id = db_prepare_int( $p_user_id == null ? auth_get_current_user_id() : $p_user_id );

		$t_tokens_table = config_get( 'mantis_tokens_table' );

		$t_query = "DELETE FROM $t_tokens_table 
					WHERE type='$c_type' AND owner='$c_user_id'";
		db_query( $t_query );

		return true;
	}

	/**
	 * Delete all tokens owned by a specified user.
	 * @param integer User ID or null for current logged in user.
	 * @return always true	 
	 */
	function token_delete_by_owner( $p_user_id = null ) {
		if( $p_user_id == null ) {
			$c_user_id = auth_get_current_user_id();
		} else {
			$c_user_id = db_prepare_int( $p_user_id );
		}

		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		# Remove
		$t_query = "DELETE FROM $t_tokens_table
		          	WHERE owner='$c_user_id'";
		db_query( $t_query );

		return true;
	}

	# Low-level CRUD, not for general use

	/**
	 * Create a token.
	 * @param integer Token type
	 * @param string Token value
	 * @param integer Token expiration in seconds
	 * @param integer User ID
	 * @return integer Token ID
	 */
	function token_create( $p_type, $p_value, $p_expiry = TOKEN_EXPIRY, $p_user_id = null ) {
		$c_type = db_prepare_int( $p_type );
		$c_value = db_prepare_string( $p_value );
		$c_timestamp = db_now();
		$c_expiry = db_timestamp( db_date(time() + $p_expiry) );
		$c_user_id = db_prepare_int( $p_user_id == null ? auth_get_current_user_id() : $p_user_id );

		$t_tokens_table = config_get( 'mantis_tokens_table' );

		$t_query = "INSERT INTO $t_tokens_table
					( type, value, timestamp, expiry, owner )
					VALUES ( '$c_type', '$c_value', $c_timestamp, $c_expiry, '$c_user_id' )";
		db_query( $t_query );
		return db_insert_id( $t_tokens_table );
	}

	/**
	 * Update a token
	 * @param integer Token ID
	 * @param string Token value
	 * @param integer Token expiration in seconds
	 * @return always true.	 
	 */
	function token_update( $p_token_id, $p_value, $p_expiry = TOKEN_EXPIRY ) {
		token_ensure_exists( $p_token_id );
		$c_token_id = db_prepare_int( $p_token_id );
		$c_value = db_prepare_string( $p_value );
		$c_expiry = db_timestamp( db_date(time() + $p_expiry) );

		$t_tokens_table = config_get( 'mantis_tokens_table' );

		$t_query = "UPDATE $t_tokens_table 
					SET value='$c_value', expiry=$c_expiry
					WHERE id=$c_token_id";
		db_query( $t_query, 1 );

		return true;
	}

	/**
	 * Delete all tokens of a specified type.
	 * @param integer Token Type
	 * @return always true.	 
	 */
	function token_delete_by_type( $p_token_type ) {
		$c_token_type = db_prepare_int( $p_token_type );

		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		# Remove
		$t_query = "DELETE FROM $t_tokens_table
		          	WHERE type='$c_token_type'";
		db_query( $t_query );

		return true;
	}

	/**
	 * Purge all expired tokens.
	 * @param integer Token type
	 * @return always true.	 
	 */
	function token_purge_expired( $p_token_type = null ) {
		global $g_tokens_purged;

		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		$t_query = "DELETE FROM $t_tokens_table WHERE ";
		if ( !is_null( $p_token_type ) ) {
			$c_token_type = db_prepare_int( $p_token_type );
			$t_query .= " type='$c_token_type' AND ";
		}

		$t_query .= db_now() . ' > expiry';
		db_query( $t_query );

		$g_tokens_purged = true;

		return true;
	}

	/**
	 * Purge all expired tokens only once per session.
	 * @param integer Token type
	 */
	function token_purge_expired_once( $p_token_type = null ) {
		global $g_tokens_purged;
		if ( !$g_tokens_purged ) {
			token_purge_expired();
		}
	}
