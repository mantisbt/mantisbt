<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: version_api.php,v 1.3 2002-08-27 04:26:43 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Version API
	###########################################################################

	# --------------------
	# checks to see if the version is a duplicate
	# we do it this way because each different project can have the same category names
	# The old version name is excluded from the search for duplicates since a version
	# can re-take its name.  It is also useful when changing the case of a version name.
	# For example, "version" -> "Version".
	function is_duplicate_version( $p_project_id, $p_version, $p_date_order='0', $p_old_version = '' ) {
		global $g_mantis_project_version_table;

		$c_project_id	= (integer)$p_project_id;
		$c_version		= addslashes($p_version);

		$query = "SELECT COUNT(*)
				FROM $g_mantis_project_version_table
				WHERE project_id='$c_project_id' AND
				version='$c_version'";

		if ( strlen($p_old_version) != 0 ) {
			$c_old_version = addslashes($p_old_version);
			$query = $query . " AND version <> '$c_old_version'";
		}

		if ( strcmp($p_date_order, '0') != 0) {
			$c_date_order	= addslashes($p_date_order);
			$query = $query . " AND	date_order='$c_date_order'";
		}

		$result = db_query( $query );
		$version_count =  db_result( $result, 0, 0 );

		return ( $version_count > 0 );
	}
	# --------------------
	function version_add( $p_project_id, $p_version ) {
		global $g_mantis_project_version_table;

		$c_project_id	= (integer)$p_project_id;
		$c_version		= addslashes($p_version);

		$query = "INSERT
				INTO $g_mantis_project_version_table
				( project_id, version, date_order )
				VALUES
				( '$c_project_id', '$c_version', NOW() )";
		return db_query( $query );
	}
	# --------------------
	function version_update( $p_project_id, $p_version, $p_date_order, $p_orig_version ) {
		global $g_mantis_project_version_table;

		$c_project_id	= (integer)$p_project_id;
		$c_version		= addslashes($p_version);
		$c_date_order	= addslashes($p_date_order);
		$c_orig_version	= addslashes($p_orig_version);

		$query = "UPDATE $g_mantis_project_version_table
				SET version='$c_version',
					date_order='$c_date_order'
				WHERE version='$c_orig_version'
					  AND project_id='$c_project_id'";
		return db_query( $query );
	}
	# --------------------
	function version_delete( $p_project_id, $p_version ) {
		global $g_mantis_project_version_table;

		$c_project_id	= (integer)$p_project_id;
		$c_version		= addslashes($p_version);

		$query = "DELETE
				FROM $g_mantis_project_version_table
				WHERE project_id='$c_project_id' AND
					  version='$c_version'";
		return db_query( $query );
	}
	# --------------------
	# return all categories for the specified project id
	function version_get_all( $p_project_id ) {
		global $g_mantis_project_version_table;

		$c_project_id = (integer)$p_project_id;

		$query = "SELECT version, date_order
				FROM $g_mantis_project_version_table
				WHERE project_id='$c_project_id'
				ORDER BY date_order DESC";
		return db_query( $query );
	}
	# --------------------
	# delete all versions associated with a project
	function version_delete_all( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_version_table = config_get( 'mantis_project_version_table' );

		$query = "DELETE
				  FROM $t_project_version_table
				  WHERE project_id='$c_project_id'";

		db_query( $query );

		# db_query() errors on failure so:
		return true;
	}
?>
