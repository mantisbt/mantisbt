<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Delete the bug, bugtext, bugnote, and bugtexts selected
	### Redirects to view_all_bug_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( DEVELOPER );
	check_bug_exists( $f_id );

	### Delete the bug entry
	$query = "DELETE
			FROM $g_mantis_bug_table
			WHERE id='$f_id'";
	$result = db_query($query);

	### Delete the corresponding bug text
	$query = "DELETE
			FROM $g_mantis_bug_text_table
			WHERE id='$f_bug_text_id'";
	$result = db_query($query);

	### Delete the bugnote text items
	$query = "SELECT bugnote_text_id
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$f_id'";
	$result = db_query($query);
	$bugnote_count = db_num_rows( $result );
	for ($i=0;$i<$bugnote_count;$i++){
		$row = db_fetch_array( $result );
		$t_bugnote_text_id = $row["bugnote_text_id"];

		### Delete the corresponding bugnote texts
		$query = "DELETE
				FROM $g_mantis_bugnote_text_table
				WHERE id='$t_bugnote_text_id'";
		$result2 = db_query( $query );
	}

	### Delete the corresponding bugnotes
	$query = "DELETE
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$f_id'";
	$result = db_query($query);

	if ( $g_store_file_to==1 ) {
		### Delete files from disk
		$query = "SELECT diskfile
			FROM $g_mantis_bug_file_table
			WHERE bug_id='$f_id'";
		$result = db_query($query);
		$file_count = db_num_rows( $result );

		# there may be more than one file
		for ($i=0;$i<$file_count;$i++){
			$row = db_fetch_array( $result );
			$t_diskfile = $row["diskfile"];

			# use this instead of delete;
			# in windows replace with system("del $t_diskfile");
			unlink( $t_diskfile );
		}
	}

	### Delete the corresponding files
	$query = "DELETE
		FROM $g_mantis_bug_file_table
		WHERE bug_id='$f_id'";
	$result = db_query($query);
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_view_all_bug_page, $g_wait_time );
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
	if ( $result ) {					### SUCCESS
		PRINT "$s_bug_deleted_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_view_all_bug_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>