<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );
	$f_version = urldecode( $f_version );

	# delete version
	$query = "DELETE
			FROM $g_mantis_project_version_table
			WHERE project_id='$f_project_id' AND version='$f_version'";
	$result = db_query( $query );

	$query = "SELECT id, bug_text_id
			FROM $g_mantis_bug_table
			WHERE project_id='$f_project_id' AND version='$f_version'";
	$result = db_query( $query );
	$bug_count = db_num_rows( $result );

	for ($i=0;$i<$bug_count;$i++) {
		$row = db_fetch_array( $result );
		$t_bug_id = $row["id"];
		$t_bug_text_id = $row["bug_text_id"];

		# Delete the bug text
		$query2 = "DELETE
				FROM $g_mantis_bug_text_table
	    		WHERE id='$t_bug_text_id'";
	    $result2 = db_query( $query2 );

		# select bugnotes to delete
		$query3 = "SELECT id, bugnote_text_id
				FROM $g_mantis_bugnote_table
	    		WHERE bug_id='$t_bug_id'";
	    $result3 = db_query( $query3 );
	    $bugnote_count = db_num_rows( $result3 );

		for ($j=0;$j<$bugnote_count;$j++) {
			$row2 = db_fetch_array( $result3 );
			$t_bugnote_id = $row2["id"];
			$t_bugnote_text_id = $row2["bugnote_text_id"];

			# Delete the bugnotes
			$query = "DELETE
					FROM $g_mantis_bugnote_table
		    		WHERE id='$t_bugnote_id'";
		    $result = db_query( $query );

			# Delete the bugnote texts
			$query4 = "DELETE
					FROM $g_mantis_bugnote_text_table
		    		WHERE id='$t_bugnote_text_id'";
		    $result4 = db_query( $query4 );
		}
	}

	$query = "DELETE
			FROM $g_mantis_bug_table
			WHERE project_id='$f_project_id' AND version='$f_version'";
    $result = db_query( $query );

    $t_redirect_url = $g_manage_project_edit_page."?f_project_id=".$f_project_id;
?>
<?php print_page_top1() ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<?php print_proceed( $result, $query, $t_redirect_url ) ?>

<?php print_page_bot1( __FILE__ ) ?>