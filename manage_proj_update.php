<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_update.php,v 1.27 2007-09-25 23:52:12 nuclear_eclipse Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	auth_reauthenticate();

	$f_project_id 	= gpc_get_int( 'project_id' );
	$f_name 		= gpc_get_string( 'name' );
	$f_description 	= gpc_get_string( 'description' );
	$f_status 		= gpc_get_int( 'status' );
	$f_view_state 	= gpc_get_int( 'view_state' );
	$f_file_path 	= gpc_get_string( 'file_path', '' );
	$f_enabled	 	= gpc_get_bool( 'enabled' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	project_update( $f_project_id, $f_name, $f_description, $f_status, $f_view_state, $f_file_path, $f_enabled );

	print_header_redirect( 'manage_proj_page.php' );
?>
