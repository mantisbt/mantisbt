<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_cat_copy.php,v 1.21 2005-02-27 15:33:01 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'category_api.php' );
?>
<?php
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
		trigger_error( ERROR_CATEGORY_NO_ACTION, ERROR );
	}

	$rows = category_get_all_rows( $t_src_project_id );

	foreach ( $rows as $row ) {
		$t_category = $row['category'];

		if ( category_is_unique( $t_dst_project_id, $t_category ) ) {
			category_add( $t_dst_project_id, $t_category );
		}
	}

	print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
?>
