<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Reset prefs to defaults then redirect to account_prefs_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# get protected state
	$t_protected = get_current_user_field( 'protected' );

	# protected account check
	if ( ON == $t_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	# get user id
	$t_user_id = get_current_user_field( 'id' );

	## reset to defaults
	$query = "UPDATE $g_mantis_user_pref_table
			SET default_project='0000000',
				advanced_report='$g_default_advanced_report',
				advanced_view='$g_default_advanced_view',
				advanced_update='$g_default_advanced_update',
				refresh_delay='$g_default_refresh_delay',
				redirect_delay='$g_default_redirect_delay',
				email_on_new='$g_default_email_on_new',
				email_on_assigned='$g_default_email_on_assigned',
				email_on_feedback='$g_default_email_on_feedback',
				email_on_resolved='$g_default_email_on_resolved',
				email_on_closed='$g_default_email_on_closed',
				email_on_reopened='$g_default_email_on_reopened',
				email_on_bugnote='$g_default_email_on_bugnote',
				email_on_status='$g_default_email_on_status',
				email_on_priority='$g_default_email_on_priority',
				language='$g_default_language'
			WHERE user_id='$t_user_id'";
	$result = db_query( $query );

	$t_redirect_url = 'account_prefs_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
