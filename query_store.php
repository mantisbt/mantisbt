<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	require_once( 'core.php' );
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'compress_api.php' );
	require_once( $t_core_path.'filter_api.php' );
	require_once( $t_core_path.'current_user_api.php' );
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'string_api.php' );
	require_once( $t_core_path.'date_api.php' );

	auth_ensure_user_authenticated();
	compress_enable();

	$f_query_name = gpc_get_string( 'query_name' );
	$f_is_public = gpc_get_bool( 'is_public' );
	$f_all_projects = gpc_get_bool( 'all_projects' );
	
	$t_query_redirect_url = 'query_store_page.php';
	
	# We can't have a blank name
	if ( is_blank( $f_query_name ) ) {	
		$t_query_redirect_url = $t_query_redirect_url . '?error_msg=' 
			. urlencode( lang_get( 'query_blank_name' ) );
		print_header_redirect( $t_query_redirect_url );
	}

	# Check and make sure they don't already have a
	# query with the same name
	$t_query_arr = filter_db_get_available_queries();
	foreach( $t_query_arr as $t_id => $t_name )	{
		if ( $f_query_name == $t_name ) {
			$t_query_redirect_url = $t_query_redirect_url . '?error_msg=' 
				. urlencode( lang_get( 'query_dupe_name' ) );
			print_header_redirect( $t_query_redirect_url );
			exit;
		}
	}

	$t_project_id = helper_get_current_project();
	if ( $f_all_projects ) {
		$t_project_id = 0;
	}
	 
	$t_filter_string = filter_db_get_filter( gpc_get_cookie( config_get( 'view_all_cookie' ), '' ) );

	$t_new_row_id = filter_db_set_for_current_user($t_project_id, $f_is_public, 
													$f_query_name, $t_filter_string);
		
	if ( $t_new_row_id == -1 ) {	
		$t_query_redirect_url = $t_query_redirect_url . '?error_msg=' 
			. urlencode( lang_get( 'query_store_error' ) );
		print_header_redirect( $t_query_redirect_url );
	} else {	
		print_header_redirect( 'view_all_bug_page.php' );
	}
?>
