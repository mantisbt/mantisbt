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
	$duplicate = is_duplicate_category( $f_category, $f_project_id );
	### check for empty case or duplicate
	if ( !empty( $f_category )&& !$duplicate ) {
		### insert category
		$query = "INSERT
				INTO $g_mantis_project_category_table
				( project_id, category )
				VALUES
				( '$f_project_id', '$f_category' )";
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
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $result ) {				### SUCCESS
		PRINT "$s_category_added_msg<p>";
	} else if ( $duplicate ) {		### DUPLICATE
		PRINT "$s_duplicate_category<p>";
	} else {						### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_manage_project_edit_page."?f_project_id=".$f_project_id, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>