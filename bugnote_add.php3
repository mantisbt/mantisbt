<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# Insert the bugnote into the database then redirect to the bug page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( REPORTER );
	check_bug_exists( $f_id );

	# get user information
	$u_id = get_current_user_field( "id " );

	$f_bugnote_text = string_prepare_textarea( $f_bugnote_text );
	# insert bugnote text
	$query = "INSERT
			INTO $g_mantis_bugnote_text_table
			( id, note )
			VALUES
			( null, '$f_bugnote_text' )";
	$result = db_query( $query );

	# retrieve bugnote text id number
	$t_bugnote_text_id = db_insert_id();

	# insert bugnote info
	$query = "INSERT
			INTO $g_mantis_bugnote_table
			( id, bug_id, reporter_id, bugnote_text_id, date_submitted, last_modified )
			VALUES
			( null, '$f_id', '$u_id','$t_bugnote_text_id',NOW(), NOW() )";
	$result = db_query( $query );

	$query = "SELECT date_submitted
			FROM $g_mantis_bug_table
    		WHERE id='$f_id'";
   	$result = db_query( $query );
   	$t_date_submitted = db_result( $result, 0, 0 );

	# update bug last updated
	$query = "UPDATE $g_mantis_bug_table
    		SET date_submitted='$t_date_submitted', last_updated=NOW()
    		WHERE id='$f_id'";
   	$result = db_query($query);

   	# notify reporter and handler
   	if ( get_bug_field( "status", $f_id ) == FEEDBACK ) {
   		if ( get_bug_field( "resolution", $f_id ) == REOPENED ) {
   			email_reopen( $f_id );
   		} else {
   			email_feedback( $f_id );
   		}
   	} else if ( get_bug_field( "status", $f_id ) == RESOLVED ) {
   		email_resolved( $f_id );
   	} else {
   		email_bugnote_add( $f_id );
   	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( ( ON == $g_quick_proceed )&&( $result ) ) {
		print_header_redirect( $t_redirect_url );
	}
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