<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### This page stores the reported bug and then redirects to view_all_bug_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( UPDATER );

	# We check to see if the variable exists to avoid warnings
	$result = 1;
	if ( isset( $f_bug_arr ) ) {
		$t_count = count( $f_bug_arr );
		for ( $i=0; $i < $t_count; $i++ ) {
			$t_new_id = $f_bug_arr[$i];
			$query = "UPDATE $g_mantis_bug_table
					SET project_id='$f_project_id'
					WHERE id='$t_new_id'";
			$result = db_query( $query );

			if ( !$result ) {
				break;
			}
		}
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_view_all_bug_page, $g_wait_time );
	}
?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( !$result ) {				# MYSQL ERROR
		print_sql_error( $query );
	} else {						# SUCCESS
		PRINT "$s_bugs_moved_msg<p>";
		print_bracket_link( $g_view_all_bug_page, $s_view_bugs_link );
	}
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>