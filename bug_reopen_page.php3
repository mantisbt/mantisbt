<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# Reopen the bug, set status to feedback and give the user the opportunity
	# to input a bugnote
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( $g_reopen_bug_threshold );
	check_bug_exists( $f_id );

	# Update fields
	$t_fee_val = FEEDBACK;
	$t_reop = REOPENED;
    $query = "UPDATE $g_mantis_bug_table
    		SET status='$t_fee_val',
				resolution='$t_reop'
    		WHERE id='$f_id'";
   	$result = db_query($query);

   	email_reopen( $f_id );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?
	if ( $result ) {					# SUCCESS
		PRINT "$s_operation_successful<p>";
	} else {							# FAILURE
		print_sql_error( $query );
	}
?>

<?php include( $g_view_bug_inc ) ?>
<?php include( $g_bugnote_include_file ) ?>

<?php print_page_bot1( __FILE__ ) ?>