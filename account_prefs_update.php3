<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### Updates prefs then redirect to account_prefs_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### A bunch of existance checks; necessary to prevent warnings

	if ( !isset( $f_advanced_report ) ) {
		$f_advanced_report = 0;
	} else {
		$f_advanced_report = 1;
	}

	if ( !isset( $f_advanced_view ) ) {
		$f_advanced_view = 0;
	} else {
		$f_advanced_view = 1;
	}

	if ( !isset( $f_advanced_update ) ) {
		$f_advanced_update = 0;
	} else {
		$f_advanced_update = 1;
	}

	if ( !isset( $f_email_on_new ) ) {
		$f_email_on_new = 0;
	} else {
		$f_email_on_new = 1;
	}

	if ( !isset( $f_email_on_assigned ) ) {
		$f_email_on_assigned = 0;
	} else {
		$f_email_on_assigned = 1;
	}

	if ( !isset( $f_email_on_feedback ) ) {
		$f_email_on_feedback = 0;
	} else {
		$f_email_on_feedback = 1;
	}

	if ( !isset( $f_email_on_resolved ) ) {
		$f_email_on_resolved = 0;
	} else {
		$f_email_on_resolved = 1;
	}

	if ( !isset( $f_email_on_closed ) ) {
		$f_email_on_closed = 0;
	} else {
		$f_email_on_closed = 1;
	}

	if ( !isset( $f_email_on_reopened ) ) {
		$f_email_on_reopened = 0;
	} else {
		$f_email_on_reopened = 1;
	}

	if ( !isset( $f_email_on_bugnote ) ) {
		$f_email_on_bugnote = 0;
	} else {
		$f_email_on_bugnote = 1;
	}

	if ( !isset( $f_email_on_status ) ) {
		$f_email_on_status = 0;
	} else {
		$f_email_on_status = 1;
	}

	if ( !isset( $f_email_on_priority ) ) {
		$f_email_on_priority = 0;
	} else {
		$f_email_on_priority = 1;
	}

	### make sure the delay isn't too low
	if (( $g_min_refresh_delay > $f_refresh_delay )&&
		( $f_refresh_delay != 0 )) {
		$f_refresh_delay = $g_min_refresh_delay;
	}

	### get user id
	$t_user_id = get_current_user_field( "id" );

	### update preferences
	$query = "UPDATE $g_mantis_user_pref_table
			SET default_project='$f_project_id',
				advanced_report='$f_advanced_report',
				advanced_view='$f_advanced_view',
				advanced_update='$f_advanced_update',
				refresh_delay='$f_refresh_delay',
				redirect_delay='$f_redirect_delay',
				email_on_new='$f_email_on_new',
				email_on_assigned='$f_email_on_assigned',
				email_on_feedback='$f_email_on_feedback',
				email_on_resolved='$f_email_on_resolved',
				email_on_closed='$f_email_on_closed',
				email_on_reopened='$f_email_on_reopened',
				email_on_bugnote='$f_email_on_bugnote',
				email_on_status='$f_email_on_status',
				email_on_priority='$f_email_on_priority',
				language='$f_language'
			WHERE user_id='$t_user_id'";
	$result = db_query( $query );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_account_prefs_page, $g_wait_time );
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
		PRINT "$s_prefs_updated_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_account_prefs_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>