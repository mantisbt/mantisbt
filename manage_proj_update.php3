<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );

	if ( !isset( $f_enabled ) ) {
		$f_enabled = 0;
	} else {
		$f_enabled = 1;
	}

	$f_name 		= string_prepare_textarea( $f_name );
	$f_description 	= string_prepare_textarea( $f_description );

	# Make sure file path has trailing slash
	if ( $f_file_path[strlen($f_file_path)-1] != "/" ) {
		$f_file_path = $f_file_path."/";
	}

	### Update entry
	$query = "UPDATE $g_mantis_project_table
			SET name='$f_name',
				status='$f_status',
				enabled='$f_enabled',
				view_state='$f_view_state',
				file_path='$f_file_path',
				access_min='$f_access_min',
				description='$f_description'
    		WHERE id='$f_project_id'";
    $result = db_query( $query );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_manage_project_menu_page, $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $result ) {				### SUCCESS
		PRINT "$s_project_updated_msg<p>";
	} else {						### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_manage_project_menu_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>