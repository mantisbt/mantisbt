<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Delete the bug, bugtext, bugnote, and bugtexts selected
	# Redirects to view_all_bug_page.php3
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( DEVELOPER );
	check_bug_exists( $f_id );

	# Delete the bug entry
	$query = "DELETE
			FROM $g_mantis_bug_table
			WHERE id='$f_id'";
	$result = db_query($query);

	# Delete the corresponding bug text
	$query = "DELETE
			FROM $g_mantis_bug_text_table
			WHERE id='$f_bug_text_id'";
	$result = db_query($query);

	# Delete the bugnote text items
	$query = "SELECT bugnote_text_id
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$f_id'";
	$result = db_query($query);
	$bugnote_count = db_num_rows( $result );
	for ($i=0;$i<$bugnote_count;$i++){
		$row = db_fetch_array( $result );
		$t_bugnote_text_id = $row["bugnote_text_id"];

		# Delete the corresponding bugnote texts
		$query = "DELETE
				FROM $g_mantis_bugnote_text_table
				WHERE id='$t_bugnote_text_id'";
		$result2 = db_query( $query );
	}

	# Delete the corresponding bugnotes
	$query = "DELETE
			FROM $g_mantis_bugnote_table
			WHERE bug_id='$f_id'";
	$result = db_query($query);

	if ( DISK == $g_file_upload_method ) {
		# Delete files from disk
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

	# Delete the corresponding files
	$query = "DELETE
		FROM $g_mantis_bug_file_table
		WHERE bug_id='$f_id'";
	$result = db_query($query);

	$t_redirect_url = $g_view_all_bug_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>