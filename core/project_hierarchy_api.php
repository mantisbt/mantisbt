<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2005  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: project_hierarchy_api.php,v 1.2 2005-02-27 21:19:31 prichards Exp $
	# --------------------------------------------------------

	### Project Hierarchy API ###

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	# --------------------
	function project_hierarchy_add( $p_child_id, $p_parent_id ) {
		if ( in_array( $p_parent_id, project_hierarchy_get_all_subprojects( $p_child_id ) ) ) {
			trigger_error( ERROR_PROJECT_RECURSIVE_HIERARCHY, ERROR );
		}

		$t_project_hierarchy_table = config_get( 'mantis_project_hierarchy_table' );

		$c_child_id  = db_prepare_int( $p_child_id  );
		$c_parent_id = db_prepare_int( $p_parent_id );

		$query = "INSERT INTO $t_project_hierarchy_table
		                ( child_id, parent_id )
						VALUES
						( $c_child_id, $c_parent_id )";

		db_query($query);
	}

	# --------------------
	function project_hierarchy_remove( $p_child_id, $p_parent_id ) {
		$t_project_hierarchy_table = config_get( 'mantis_project_hierarchy_table' );

		$c_child_id  = db_prepare_int( $p_child_id  );
		$c_parent_id = db_prepare_int( $p_parent_id );

		$query = "DELETE FROM $t_project_hierarchy_table
		                WHERE child_id = $c_child_id
						AND parent_id = $c_parent_id";

		db_query($query);
	}

	# --------------------
	function project_hierarchy_remove_all( $p_project_id ) {
		$t_project_hierarchy_table = config_get( 'mantis_project_hierarchy_table' );

		$c_project_id = db_prepare_int( $p_project_id );

		$query = "DELETE FROM $t_project_hierarchy_table
		                WHERE child_id = $c_project_id
						  OR parent_id = $c_project_id";

		db_query($query);
	}

	# --------------------
	function project_hierarchy_is_toplevel( $p_project_id ) {
		$t_project_hierarchy_table = config_get( 'mantis_project_hierarchy_table' );

		$c_project_id = db_prepare_int( $p_project_id );

		$query = "SELECT * FROM $t_project_hierarchy_table
		                WHERE child_id = $c_project_id";

		$result = db_query($query);

		return ( db_num_rows( $result ) == 0 );
	}

	# --------------------
	function project_hierarchy_get_subprojects( $p_project_id ) {
		$c_project_id = db_prepare_int( $p_project_id );

		$t_project_table			= config_get( 'mantis_project_table' );
		$t_project_hierarchy_table	= config_get( 'mantis_project_hierarchy_table' );

		$query = "SELECT DISTINCT( p.id ), p.name
				  FROM $t_project_table p
				  LEFT JOIN $t_project_hierarchy_table ph
				    ON ph.child_id = p.id
				  WHERE p.enabled = 1 AND
				    ph.parent_id = $c_project_id
				  ORDER BY p.name";

		$result = db_query( $query );
		$row_count = db_num_rows( $result );

		$t_projects = array();

		for ( $i=0 ; $i < $row_count ; $i++ ) {
			$row = db_fetch_array( $result );

			array_push( $t_projects, $row['id'] );
		}

		return $t_projects;
	}

	# --------------------
	function project_hierarchy_get_all_subprojects( $p_project_id ) {
		$t_todo        = project_hierarchy_get_subprojects( $p_project_id );
		$t_subprojects = Array();

		while ( $t_todo ) {
			$t_elem = array_shift( $t_todo );
			if ( !in_array( $t_elem, $t_subprojects ) ) {
				array_push( $t_subprojects, $t_elem );
				$t_todo = array_merge( $t_todo, project_hierarchy_get_subprojects( $t_elem ) );
			}
		}

		return $t_subprojects;
	}
?>
