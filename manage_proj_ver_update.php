<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
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

	$t_redirect_url = $g_manage_project_edit_page."?f_project_id=".$f_project_id;
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( $result ) {				# SUCCESS
		PRINT "$s_operation_successful<p>";
	} else if ( is_duplicate_version( $f_version, $f_project_id, $f_date_order )) {
		PRINT $MANTIS_ERROR[ERROR_DUPLICATE_VERSION]."<p>";
	} else {						# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>