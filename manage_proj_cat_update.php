<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( MANAGER );
	$f_category = urldecode( $f_category );
	$f_orig_category = urldecode( $f_orig_category );

	$result = 0;
	$query = "";
	# check for duplicate
	if ( !is_duplicate_category( $f_category, $f_project_id ) ) {
		# update category
		$query = "UPDATE $g_mantis_project_category_table
				SET category='$f_category'
				WHERE category='$f_orig_category' AND project_id='$f_project_id'";
		$result = db_query( $query );

		$query = "SELECT id, date_submitted, last_updated
				FROM $g_mantis_bug_table
	    		WHERE category='$f_orig_category'";
	   	$result = db_query( $query );
	   	$bug_count = db_num_rows( $result );

		# update version in each corresponding bug
		for ($i=0;$i<$bug_count;$i++) {
			$row = db_fetch_array( $result );
			$t_bug_id = $row["id"];
			$t_date_submitted = $row["date_submitted"];
			$t_last_updated = $row["last_updated"];

			$query2 = "UPDATE $g_mantis_bug_table
					SET category='$f_category', date_submitted='$t_date_submitted',
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
	if ( $result ) {					# SUCCESS
		PRINT "$s_operation_successful<p>";
	} else if ( is_duplicate_category( $f_category, $f_project_id )) {
		PRINT $MANTIS_ERROR[ERROR_DUPLICATE_CATEGORY]."<p>";
	} else {							# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>