<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: version_api.php,v 1.10 2004-04-08 02:42:27 prescience Exp $
	# --------------------------------------------------------

	###########################################################################
	# Version API
	###########################################################################

	#===================================
	# Boolean queries and ensures
	#===================================

	# --------------------
	# Check whether the version exists
	# Returns true if the version exists, false otherwise
	function version_exists( $p_project_id, $p_version ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_version		= db_prepare_string( $p_version );

		$t_project_version_table = config_get( 'mantis_project_version_table' );

		$query = "SELECT COUNT(*)
					FROM $t_project_version_table
					WHERE project_id='$c_project_id' AND
					version='$c_version'";

		$result = db_query( $query );
		$version_count =  db_result( $result );

		if ( 0 < $version_count ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Check whether the version name is unique
	# Returns true if the name is unique, false otherwise
	function version_is_unique( $p_project_id, $p_version ) {
		return ! version_exists( $p_project_id, $p_version );
	}

	# --------------------
	# Check whether the version exists in the project
	# Trigger an error if it does not
	function version_ensure_exists( $p_project_id, $p_version ) {
		if ( ! version_exists( $p_project_id, $p_version ) ) {
			trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
		}
	}

	# --------------------
	# Check whether the version is unique within a project
	# Trigger an error if it is not
	function version_ensure_unique( $p_project_id, $p_version ) {
		if ( ! version_is_unique( $p_project_id, $p_version ) ) {
			trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
		}
	}


	#===================================
	# Creation / Deletion / Updating
	#===================================

	# --------------------
	# Add a version to the project
	function version_add( $p_project_id, $p_version ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_version		= db_prepare_string( $p_version );

		version_ensure_unique( $p_project_id, $p_version );

		$t_project_version_table = config_get( 'mantis_project_version_table' );

		$query = "INSERT INTO $t_project_version_table
					( project_id, version, date_order )
				  VALUES
					( '$c_project_id', '$c_version', " . db_now() . ")";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Update the definition of a version
	function version_update( $p_project_id, $p_version, $p_new_version, $p_date_order ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_version		= db_prepare_string( $p_version );
		$c_new_version	= db_prepare_string( $p_new_version );
		$c_date_order	= db_prepare_string( $p_date_order );

		version_ensure_exists( $p_project_id, $p_version );

		# check for duplicates
		if ( ( strtolower( $p_version ) != strtolower( $p_new_version ) ) &&
			 !version_is_unique( $p_project_id, $p_new_version ) ) {
			trigger_error( ERROR_VERSION_DUPLICATE, ERROR );
		}

		$t_project_version_table	= config_get( 'mantis_project_version_table' );
		$t_bug_table				= config_get( 'mantis_bug_table' );

		$query = "UPDATE $t_project_version_table
				  SET version='$c_new_version',
					  date_order='$c_date_order'
				  WHERE version='$c_version' AND
				  		project_id='$c_project_id'";
		db_query( $query );

		if ( $p_version != $p_new_version ) {
			$query = "UPDATE $t_bug_table
					  SET version='$c_new_version'
					  WHERE version='$c_version'
						AND project_id='$c_project_id'";
			db_query( $query );
		}

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Remove a version from the project
	function version_remove( $p_project_id, $p_version, $p_new_version='' ) {
		$c_project_id	= db_prepare_int( $p_project_id );
		$c_version		= db_prepare_string( $p_version );
		$c_new_version	= db_prepare_string( $p_new_version );

		version_ensure_exists( $p_project_id, $p_version );

		$t_project_version_table	= config_get( 'mantis_project_version_table' );
		$t_bug_table				= config_get( 'mantis_bug_table' );

		$query = "DELETE
				  FROM $t_project_version_table
				  WHERE project_id='$c_project_id'
				    AND version='$c_version'";
		db_query( $query );

		$query = "UPDATE $t_bug_table
				  SET version='$c_new_version'
				  WHERE version='$c_version'
					AND project_id='$c_project_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}

	# --------------------
	# Remove all versions associated with a project
	function version_remove_all( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_version_table	= config_get( 'mantis_project_version_table' );
		$t_bug_table				= config_get( 'mantis_bug_table' );

		$query = "DELETE
				  FROM $t_project_version_table
	  			  WHERE project_id='$c_project_id'";

		db_query( $query );

		$query = "UPDATE $t_bug_table
				  SET version=''
				  WHERE project_id='$c_project_id'";
		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}


	#===================================
	# Data Access
	#===================================

	# --------------------
	# Return all versions for the specified project
	function version_get_all_rows( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_version_table = config_get( 'mantis_project_version_table' );

		$query = "SELECT version, date_order
				  FROM $t_project_version_table
				  WHERE project_id='$c_project_id'
				  ORDER BY date_order DESC";
		$result = db_query( $query );

		$count = db_num_rows( $result );

		$rows = array();

		for ( $i = 0 ; $i < $count ; $i++ ) {
			$row = db_fetch_array( $result );

			$rows[] = $row;
		}

		return $rows;
	}
?>
