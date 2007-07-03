<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_ver_copy.php,v 1.1 2007-07-03 03:57:07 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'version_api.php' );

	$f_project_id		= gpc_get_int( 'project_id' );
	$f_other_project_id	= gpc_get_int( 'other_project_id' );
	$f_copy_from		= gpc_get_bool( 'copy_from' );
	$f_copy_to			= gpc_get_bool( 'copy_to' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_other_project_id );

	if ( $f_copy_from ) {
		$t_src_project_id = $f_other_project_id;
		$t_dst_project_id = $f_project_id;
	} else if ( $f_copy_to ) {
		$t_src_project_id = $f_project_id;
		$t_dst_project_id = $f_other_project_id;
	} else {
		trigger_error( ERROR_VERSION_NO_ACTION, ERROR );
	}

	$t_rows = version_get_all_rows( $t_src_project_id );

	foreach ( $t_rows as $t_row ) {
		if ( version_is_unique( $t_version, $t_dst_project_id ) ) {
			version_add( $t_dst_project_id, $t_row['version'], $t_row['released'], $t_row['description'], $t_row['date_order'] );
		}
	}

	print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
?>
