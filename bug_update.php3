<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Update bug data then redirect to the appropriate viewing page
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( UPDATER );
	check_bug_exists( $f_id );

	### set variable to be valid if necessary
	if ( !isset( $f_duplicate_id ) ) {
		$f_duplicate_id = "";
	}

	### grab the bug_text_id
    $query = "SELECT bug_text_id
    		FROM $g_mantis_bug_table
    		WHERE id='$f_id'";
    $result = db_query( $query );
    $t_bug_text_id = db_result( $result, 0, 0 );

	### prevent warnings
	if (!isset( $f_os )) {
		$f_os = "";
	}
	if (!isset( $f_os_build )) {
		$f_os_build = "";
	}
	if (!isset( $f_platform )) {
		$f_platform = "";
	}
	if (!isset( $f_version )) {
		$f_version = "";
	}

	### prepare strings
	$f_os 						= string_prepare_text( $f_os );
	$f_os_build 				= string_prepare_text( $f_os_build );
	$f_platform					= string_prepare_text( $f_platform );
	$f_version 					= string_prepare_text( $f_version );
	$f_summary					= string_prepare_text( $f_summary );
	$f_description 				= string_prepare_textarea( $f_description );
	$f_steps_to_reproduce 		= string_prepare_textarea( $f_steps_to_reproduce );
	$f_additional_information 	= string_prepare_textarea( $f_additional_information );

    if ( ( $f_handler_id != 0 ) AND ( $f_status == NEW_ ) ) {
        $f_status = ASSIGNED;
    }

	### Update all fields
    $query = "UPDATE $g_mantis_bug_table
    		SET category='$f_category', severity='$f_severity',
    			reproducibility='$f_reproducibility',
				priority='$f_priority', status='$f_status',
				projection='$f_projection', duplicate_id='$f_duplicate_id',
				resolution='$f_resolution', handler_id='$f_handler_id',
				eta='$f_eta', summary='$f_summary'
    		WHERE id='$f_id'";
   	$result = db_query($query);

    $query = "UPDATE $g_mantis_bug_text_table
    		SET description='$f_description',
				steps_to_reproduce='$f_steps_to_reproduce',
				additional_information='$f_additional_information'
    		WHERE id='$t_bug_text_id'";
   	$result = db_query($query);

	### If we should notify and it's in feedback state then send an email
	switch ( $f_status ) {
		case FEEDBACK:	if ( $f_status!= $f_old_status ) {
   							email_feedback( $f_id );
   						}
						break;
		case ASSIGNED:	if ( ( $f_handler_id != $f_old_handler_id ) OR ( $f_status!= $f_old_status ) ) {
			   				email_assign( $f_id );
			   			}
						break;
		case RESOLVED:	email_resolved( $f_id );
						break;
		case CLOSED:	email_close( $f_id );
						break;
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_update_redirect_url( $f_id );

	if (( $g_quick_proceed == 1 )&&( $result )) {
		print_header_redirect( $t_redirect_url );
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url, $g_wait_time );
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
		PRINT "$s_bug_updated_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>
