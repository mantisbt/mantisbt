<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: tokens_api.php,v 1.4 2005-02-12 20:01:18 jlatour Exp $
	# --------------------------------------------------------

	### TOKENS API ###

	# This implements temporary storage of strings.
	# DB schema: id, type, owner, timestamp, value

	# TODO
	# 1. add constant for user token types TOKEN_USER. users can define token_my_type = token_user, token_other = token_user + 1 etc
	#    TOKEN_USER = 1000
	# 2. Implement Token_touch
	# 3. Test token_ensure_owner
	# 4. Add index on type + owner to DB
	# 5. remove 'timestamp' from dbschema?
	# 6. Replace generic errors
	# 7. add an 'expiry' param to token_add
	# 8. rework ts_purge_expired not to be called on every get. Maybe call it if token is found to be expired.
	# 9. return 'default param' from token_add is token not found

	# --------------------
	function token_ensure_owner( $p_token_id, $p_owner_id ) {
		$c_token_id = db_prepare_int( $p_token_id );
		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		$query = "SELECT owner
				          	FROM $t_tokens_table
				          	WHERE id='$c_token_id'";
		$result = db_query( $query );

		if( db_result( $result ) != $p_owner_id ) {
			trigger_error( ERROR_GENERIC, ERROR );
		}

		return true;
	}

	# --------------------
	function token_touch( $p_token_id, $p_expiry_delay ) {
	}

	# --------------------
	function token_delete_by_owner( $p_owner_owner ) {
		$c_token_owner = db_prepare_int( $p_token_owner );

		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		# Remove
		$query = "DELETE FROM $t_tokens_table
		          	WHERE owner='$c_token_owner'";
		db_query( $query );

		return true;
	}

	# --------------------
	function token_delete_by_type( $p_token_type ) {
		$c_token_type = db_prepare_int( $p_token_type );

		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		# Remove
		$query = "DELETE FROM $t_tokens_table
		          	WHERE type='$c_token_type'";
		db_query( $query );

		return true;
	}

	# --------------------
	function token_delete_by_type_owner( $p_token_type, $p_token_owner ) {
		$c_token_type = db_prepare_int( $p_token_type );
		$c_token_owner = db_prepare_int( $p_token_owner );

		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		# Remove
		$query = "DELETE FROM $t_tokens_table
		          	WHERE type='$c_token_type' and owner='$c_token_owner'";
		db_query( $query );

		return true;
	}

	# --------------------
	function token_exists( $p_token_id ) {
		$c_token_id   	= db_prepare_int( $p_token_id );
		$t_tokens_table	= config_get( 'mantis_tokens_table' );

		$query 	= "SELECT id
		          	FROM $t_tokens_table
		          	WHERE id='$c_token_id'";
		$result	= db_query( $query, 1 );

		return( 1 == db_result( $result ) );
	}

	# --------------------
	function token_ensure_exists( $p_token_id ) {
		if ( !token_exists( $p_token_id ) ) {
			trigger_error( ERROR_GENERIC, ERROR );
		}

		return true;
	}

	# --------------------
	function token_add( $p_token_value, $p_token_type = TOKEN_UNKNOWN, $p_token_owner = null ) {
		$c_token_type = db_prepare_int( $p_token_type );
		$c_token_value = db_prepare_string ( $p_token_value );

		if( $p_token_owner == null ) {
			$c_token_owner = auth_get_current_user_id();
		} else {
			$c_token_owner = db_prepare_int( $p_token_owner );
		}
		$t_tokens_table	= config_get( 'mantis_tokens_table' );
		# insert
		$query = "INSERT INTO $t_tokens_table
		          		( type, owner, timestamp, value )
		          	 VALUES
		          		( $c_token_type, $c_token_owner, " . db_now(). ",'$c_token_value' )";
		db_query( $query );
		return db_insert_id( $t_tokens_table );
	}
	# --------------------
	# This method does not generate an error if the token does not exist,
	# e.g. if we try to delete an expired token
	function token_delete( $p_token_id ) {
		$c_token_id = db_prepare_int( $p_token_id );

		$t_tokens_table	= config_get( 'mantis_tokens_table' );
		# Remove
		$query = "DELETE FROM $t_tokens_table
		          	WHERE id='$c_token_id'";
		db_query( $query, 1 );
		return true;
	}
	# --------------------
	function token_get_value( $p_token_id ) {
		$c_token_id = db_prepare_int( $p_token_id );
		$c_token_owner = auth_get_current_user_id();
		$t_tokens_table	= config_get( 'mantis_tokens_table' );
		token_purge_expired();
		token_ensure_owner( $c_token_id, $c_token_owner ) ;
		$query = "SELECT value
		          	FROM $t_tokens_table
		          	WHERE id='$c_token_id'";
		$result = db_query( $query );
		return db_result( $result );
	}
	# --------------------
	function token_purge_expired( $p_token_type = NULL ) {
		$t_tokens_table	= config_get( 'mantis_tokens_table' );
		# Remove
		$query = "DELETE FROM $t_tokens_table WHERE ";
		if ( !is_null( $p_token_type ) ) {
			$c_token_type = db_prepare_int( $p_token_type );
			$query .= " type='$c_token_type' AND ";
		}
		$query .= db_helper_compare_days( db_now(), 'timestamp', ">= '1'" );
		db_query( $query );
		return true;
	}
?>
