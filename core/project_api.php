<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: project_api.php,v 1.2 2002-08-25 20:26:03 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Project API
	###########################################################################

	# --------------------
	# check to see if project exists by id
	# if it doesn't exist then redirect to the main page
	# otherwise let execution continue undisturbed
	function project_ensure_exists( $p_project_id ) {
		global $g_mantis_project_table;

		$c_project_id = (integer)$p_project_id;

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_project_table ".
				"WHERE id='$c_project_id'";
		$result = db_query( $query );
		if ( 0 == db_result( $result, 0, 0 ) ) {
			print_header_redirect( 'main_page.php' );
		}
	}
	# --------------------
	# check to see if project exists by name
	function project_is_name_unique( $p_name ) {
		global $g_mantis_project_table;

		$query ="SELECT COUNT(*) ".
				"FROM $g_mantis_project_table ".
				"WHERE name='$p_name'";
		$result = db_query( $query );

		if ( 0 == db_result( $result ) ) {
			return true;
		} else {
			return false;
		}
	}
?>