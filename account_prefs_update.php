<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Updates prefs then redirect to account_prefs_page.php3
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# get protected state
	$t_protected = get_current_user_field( "protected" );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	# Clean input

	if ( !isset( $f_advanced_report ) || ( ON == $f_advanced_report ) ) {
		$c_advanced_report = 0;
	} else {
		$c_advanced_report = 1;
	}

	if ( !isset( $f_advanced_view ) || ( ON == $f_advanced_view ) ) {
		$c_advanced_view = 0;
	} else {
		$c_advanced_view = 1;
	}

	if ( !isset( $f_advanced_update ) || ( ON == $f_advanced_update ) ) {
		$c_advanced_update = 0;
	} else {
		$c_advanced_update = 1;
	}

	if ( !isset( $f_email_on_new ) || ( ON == $f_email_on_new ) ) {
		$c_email_on_new = 0;
	} else {
		$c_email_on_new = 1;
	}

	if ( !isset( $f_email_on_assigned ) || ( ON == $f_email_on_assigned ) ) {
		$c_email_on_assigned = 0;
	} else {
		$c_email_on_assigned = 1;
	}

	if ( !isset( $f_email_on_feedback ) || ( ON == $f_email_on_feedback ) ) {
		$c_email_on_feedback = 0;
	} else {
		$c_email_on_feedback = 1;
	}

	if ( !isset( $f_email_on_resolved ) || ( ON == $f_email_on_resolved ) ) {
		$c_email_on_resolved = 0;
	} else {
		$c_email_on_resolved = 1;
	}

	if ( !isset( $f_email_on_closed ) || ( ON == $f_email_on_closed ) ) {
		$c_email_on_closed = 0;
	} else {
		$c_email_on_closed = 1;
	}

	if ( !isset( $f_email_on_reopened ) || ( ON == $f_email_on_reopened ) ) {
		$c_email_on_reopened = 0;
	} else {
		$c_email_on_reopened = 1;
	}

	if ( !isset( $f_email_on_bugnote ) || ( ON == $f_email_on_bugnote ) ) {
		$c_email_on_bugnote = 0;
	} else {
		$c_email_on_bugnote = 1;
	}

	if ( !isset( $f_email_on_status ) || ( ON == $f_email_on_status ) ) {
		$c_email_on_status = 0;
	} else {
		$c_email_on_status = 1;
	}

	if ( !isset( $f_email_on_priority ) || ( ON == $f_email_on_priority ) ) {
		$c_email_on_priority = 0;
	} else {
		$c_email_on_priority = 1;
	}

	$c_project_id		= (integer)$f_project_id;
	$c_language			= addslashes($f_language);
	$c_redirect_delay	= (integer)$f_redirect_delay;

	# make sure the delay isn't too low
	if (( $g_min_refresh_delay > $f_refresh_delay )&&
		( $f_refresh_delay != 0 )) {
		$f_refresh_delay = $g_min_refresh_delay;
	}
	$c_refresh_delay = (integer)$f_refresh_delay;

	# get user id
	$t_user_id = get_current_user_field( "id" );

	# update preferences
	$query = "UPDATE $g_mantis_user_pref_table
			SET default_project='$c_project_id',
				advanced_report='$c_advanced_report',
				advanced_view='$c_advanced_view',
				advanced_update='$c_advanced_update',
				refresh_delay='$c_refresh_delay',
				redirect_delay='$c_redirect_delay',
				email_on_new='$c_email_on_new',
				email_on_assigned='$c_email_on_assigned',
				email_on_feedback='$c_email_on_feedback',
				email_on_resolved='$c_email_on_resolved',
				email_on_closed='$c_email_on_closed',
				email_on_reopened='$c_email_on_reopened',
				email_on_bugnote='$c_email_on_bugnote',
				email_on_status='$c_email_on_status',
				email_on_priority='$c_email_on_priority',
				language='$c_language'
			WHERE user_id='$t_user_id'";
	$result = db_query( $query );

	$t_redirect_url = $g_account_prefs_page;
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>