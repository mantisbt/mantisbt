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

	$result = 0;
	### check for empty case or duplicate
	if ( !empty( $f_version )&&( !is_duplicate_version( $f_version, $f_project_id ) ) ) {
		### insert version
		$query = "INSERT
				INTO $g_mantis_project_version_table
				( project_id, version, date_order )
				VALUES
				( '$f_project_id', '$f_version', NOW() )";
		$result = db_query( $query );
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( "$g_manage_project_edit_page?f_project_id=$f_project_id", $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>
f<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $result ) {					### SUCCESS
		PRINT "$s_version_added_msg<p>";
	} else if ( is_duplicate_version( $f_version, $f_project_id )) {
		PRINT "$s_duplicate_version";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_manage_project_edit_page."?f_project_id=".$f_project_id, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>