<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# Remove the bugnote and bugnote text and redirect back to
	# the viewing page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( REPORTER );

	# $f_bug_id is the bug id
	# @@@ WHat does this do?
	check_bugnote_exists( $f_id );

	# grab the bugnote text id
	$query = "SELECT bugnote_text_id
			FROM $g_mantis_bugnote_table
			WHERE id='$f_bugnote_id'";
	$result = db_query( $query );
	$t_bugnote_text_id = db_result( $result, 0, 0 );

	# Remove the bugnote
	$query = "DELETE
			FROM $g_mantis_bugnote_table
			WHERE id='$f_bugnote_id'";
	$result = db_query($query);

	# Remove the bugnote text
	$query = "DELETE
			FROM $g_mantis_bugnote_text_table
			WHERE id='$t_bugnote_text_id'";
	$result = db_query($query);

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id );
?>
<? print_page_top1() ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<? print_page_top2() ?>

<? print_proceed( $result, $query, $t_redirect_url ) ?>

<? print_page_bot1( __FILE__ ) ?>