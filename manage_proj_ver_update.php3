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
	$f_version 		= urldecode( $f_version );
	$f_orig_version = urldecode( $f_orig_version );
	$f_date_order = urldecode( $f_date_order );

	$result = 0;
	$query = "";
	# check for duplicate
	if ( !is_duplicate_version( $f_version, $f_project_id, $f_date_order ) ) {
		# update version
		$query = "UPDATE $g_mantis_project_version_table
				SET version='$f_version', date_order='$f_date_order'
				WHERE version='$f_orig_version' AND project_id='$f_project_id'";
		$result = db_query( $query );

		$query = "SELECT id, date_submitted, last_updated
				FROM $g_mantis_bug_table
	    		WHERE version='$f_version'";
	   	$result = db_query( $query );
	   	$bug_count = db_num_rows( $result );

		# update version
		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );

			$t_bug_id 			= $row["id"];
			$t_date_submitted 	= $row["date_submitted"];
			$t_last_updated 	= $row["last_updated"];

			$query2 = "UPDATE $g_mantis_bug_table
					SET version='$f_version', date_submitted='$t_date_submitted',
						last_updated='$t_last_updated'
					WHERE id='$t_bug_id'";
			$result2 = db_query( $query2 );
		}
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
		PRINT "$s_version_updated_msg<p>";
	} else if ( is_duplicate_version( $f_version, $f_project_id, $f_date_order )) {
		PRINT "$s_duplicate_version<p>";
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