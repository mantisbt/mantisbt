<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	$t_status_flag = 0 ; #for colors

	if (isset($f_copy_from)) {
	  $f_src_project_id = $f_other_project_id;
	  $f_dst_project_id = $f_project_id;
	} else {
	  $f_src_project_id = $f_project_id;
	  $f_dst_project_id = $f_other_project_id;
	}

	$t_attribute_arr = attribute_get_all($f_parameter, $f_src_project_id );
	$att_arr_count = count($t_attribute_arr);

	# color treatments
	check_varset( $f_color, $g_background_color  ) ;

	for ($i=0;$i<$att_arr_count;$i++) {
		$t_attribute = $t_attribute_arr[$i];
		$t_attribute = addslashes( $t_attribute );

		if ( !is_duplicate_attribute( $f_parameter, $f_dst_project_id, $t_attribute ) ) {
			attribute_add( $f_parameter, $f_dst_project_id, $t_attribute );
			if ($f_parameter == $s_states) {
				$t_result2 = attribute_add( 'colors', $f_dst_project_id, $f_color); #colors set to background for the moment
			}
		}
	}
	//$t_redirect_url = 'manage_proj_edit_page.php?f_project_id='.$f_project_id;
	$t_redirect_url = 'manage_proj_menu_page.php';

	if ( $result ) {
		//print_header_redirect( $t_redirect_url );
		print_meta_redirect( $t_redirect_url ) ;
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
