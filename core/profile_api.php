<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: profile_api.php,v 1.2 2002-10-20 22:52:52 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Profile API
	###########################################################################

	#===================================
	# Boolean queries and ensures
	#===================================


	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Create a new profile for the user, return the ID of the new profile
	function profile_create( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description ) {
		$c_user_id		= db_prepare_string( $p_user_id );
		$c_platform		= db_prepare_string( $p_platform );
		$c_os			= db_prepare_string( $p_os );
		$c_os_build		= db_prepare_string( $p_os_build );
		$c_description	= db_prepare_string( $p_description );

		user_ensure_unprotected( $p_user_id );

		$t_user_profile_table = config_get( 'mantis_user_profile_table' );

		# Add profile
		$query = "INSERT
				  INTO $t_user_profile_table
				    ( id, user_id, platform, os, os_build, description )
				  VALUES
				    ( null, '$c_user_id', '$c_platform', '$c_os', '$c_os_build', '$c_description' )";
		db_query( $query );

		return db_insert_id();
	}

	# --------------------
	# Delete a profile for the user
	#
	# Note that although profile IDs are currently globally unique, the existing
	#  code included the user_id in the query and I have chosen to keep that for
	#  this API as it hides the details of id implementation from users of the API
	function profile_delete( $p_user_id, $p_profile_id ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_profile_id	= db_prepare_int( $p_profile_id );

		user_ensure_unprotected( $p_user_id );

		$t_user_profile_table = config_get( 'mantis_user_profile_table' );

		# Delete the profile
		$query = "DELETE
				  FROM $t_user_profile_table
				  WHERE id='$c_profile_id' AND user_id='$c_user_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Update a profile for the user
	function profile_update( $p_user_id, $p_profile_id, $p_platform, $p_os, $p_os_build, $p_description ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_profile_id	= db_prepare_int( $p_profile_id );
		$c_platform		= db_prepare_string( $p_platform );
		$c_os			= db_prepare_string( $p_os );
		$c_os_build		= db_prepare_string( $p_os_build );
		$c_description	= db_prepare_string( $p_description );

		user_ensure_unprotected( $p_user_id );

		$t_user_profile_table = config_get( 'mantis_user_profile_table' );

		# Add item
		$query = "UPDATE $t_user_profile_table
				  SET platform='$c_platform', os='$c_os',
					os_build='$c_os_build', description='$c_description'
				  WHERE id='$c_profile_id' AND user_id='$c_user_id'";
		$result = db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	#===================================
	# Data Access
	#===================================

	# --------------------
	# Return a profile row from the database
	function profile_get_row( $p_user_id, $p_profile_id ) {
		$c_user_id		= db_prepare_int( $p_user_id );
		$c_profile_id	= db_prepare_int( $p_profile_id );

		$t_user_profile_table = config_get( 'mantis_user_profile_table' );

		$query = "SELECT *
				  FROM $t_user_profile_table
				  WHERE id='$c_profile_id' AND user_id='$c_user_id'";
	    $result = db_query( $query );

		return db_fetch_array( $result );
	}

	# --------------------
	# Return an array containing all rows for a given user
	function profile_get_all_rows( $p_user_id ) {
		$c_user_id		= db_prepare_int( $p_user_id );

		$t_user_profile_table = config_get( 'mantis_user_profile_table' );

		$query = "SELECT *
				  FROM $t_user_profile_table
				  WHERE user_id='$c_user_id'";
	    $result = db_query( $query );

		$t_rows = array();
		$t_row_count = db_num_rows( $result );

		for ( $i=0 ; $i < $t_row_count ; $i++ ) {
			array_push( $t_rows, db_fetch_array( $result ) );
		}

		return $t_rows;	
	}

	#===================================
	# Data Modification
	#===================================

?>
