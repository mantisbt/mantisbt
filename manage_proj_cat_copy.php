<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'category_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_project_threshold' ) );

	$f_project_id		= gpc_get_int( 'project_id' );
	$f_other_project_id	= gpc_get_int( 'other_project_id' );
	$f_copy_from		= gpc_get_bool( 'copy_from' );
	$f_copy_to			= gpc_get_bool( 'copy_to' );

	if ( $f_copy_from ) {
	  $t_src_project_id = $f_other_project_id;
	  $t_dst_project_id = $f_project_id;
	} else if ( $f_copy_to ) {
	  $t_src_project_id = $f_project_id;
	  $t_dst_project_id = $f_other_project_id;
	} else {
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$result = category_get_all( $t_src_project_id );
	$category_count = db_num_rows( $result );
	for ($i=0;$i<$category_count;$i++) {
		$row = db_fetch_array( $result );
		$t_category = $row['category'];
		$t_category = addslashes( $t_category );

		if ( !is_duplicate_category( $t_dst_project_id, $t_category ) ) {
			category_add( $t_dst_project_id, $t_category );
		}
	}

	$t_redirect_url = 'manage_proj_edit_page.php?project_id='.$f_project_id;

	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
