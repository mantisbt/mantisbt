<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Updates prefs then redirect to account_prefs_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_user_id				= gpc_get_int( 'f_user_id' );
	$f_project_id			= gpc_get_int( 'f_project_id' );
	$f_redirect_delay		= gpc_get_int( 'f_redirect_delay' );
	$f_refresh_delay		= gpc_get_int( 'f_refresh_delay' );
	$f_language				= gpc_get_string( 'f_language' );
	$f_redirect_url			= gpc_get_string( 'f_redirect_url' );

	$f_advanced_report		= gpc_get_bool( 'f_advanced_report' );
	$f_advanced_view		= gpc_get_bool( 'f_advanced_view' );
	$f_advanced_update		= gpc_get_bool( 'f_advanced_update' );
	$f_email_on_new			= gpc_get_bool( 'f_email_on_new' );
	$f_email_on_assigned	= gpc_get_bool( 'f_email_on_assigned' );
	$f_email_on_feedback	= gpc_get_bool( 'f_email_on_feedback' );
	$f_email_on_resolved	= gpc_get_bool( 'f_email_on_resolved' );
	$f_email_on_closed		= gpc_get_bool( 'f_email_on_closed' );
	$f_email_on_reopened	= gpc_get_bool( 'f_email_on_reopened' );
	$f_email_on_bugnote		= gpc_get_bool( 'f_email_on_bugnote' );
	$f_email_on_status		= gpc_get_bool( 'f_email_on_status' );
	$f_email_on_priority	= gpc_get_bool( 'f_email_on_priority' );

	# protected account check
	if ( ON == user_get_field( $f_user_id, 'protected' ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	# prevent users from changing other user's accounts
	if ( $f_user_id != auth_get_current_user_id() ) {
		check_access( ADMINISTRATOR );
	}

	# make sure the delay isn't too low
	if (( config_get( 'min_refresh_delay' ) > $f_refresh_delay )&&
		( $f_refresh_delay != 0 )) {
		$f_refresh_delay = config_get( 'min_refresh_delay' );
	}

	$c_user_id			= db_prepare_int( $f_user_id );
	$c_project_id		= db_prepare_int( $f_project_id );
	$c_redirect_delay	= db_prepare_int( $f_redirect_delay );
	$c_refresh_delay	= db_prepare_int( $f_refresh_delay );
	$c_language			= db_prepare_string( $f_language );

	$c_advanced_report		= db_prepare_bool( $f_advanced_report );
	$c_advanced_view		= db_prepare_bool( $f_advanced_view );
	$c_advanced_update		= db_prepare_bool( $f_advanced_update );
	$c_email_on_new			= db_prepare_bool( $f_email_on_new );
	$c_email_on_assigned	= db_prepare_bool( $f_email_on_assigned );
	$c_email_on_feedback	= db_prepare_bool( $f_email_on_feedback );
	$c_email_on_resolved	= db_prepare_bool( $f_email_on_resolved );
	$c_email_on_closed		= db_prepare_bool( $f_email_on_closed );
	$c_email_on_reopened	= db_prepare_bool( $f_email_on_reopened );
	$c_email_on_bugnote		= db_prepare_bool( $f_email_on_bugnote );
	$c_email_on_status		= db_prepare_bool( $f_email_on_status );
	$c_email_on_priority	= db_prepare_bool( $f_email_on_priority );

	$t_user_pref_table = config_get( 'mantis_user_pref_table' );

	# update preferences
	$query = "UPDATE $t_user_pref_table
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
			WHERE user_id='$c_user_id'";
	$result = db_query( $query );

	print_page_top1();
	print_meta_redirect( $f_redirect_url );
	print_page_top2();
	echo '<br /><div align="center">';

	if ( $result ) {
		echo lang_get( 'operation_successful' );
	} else {
		echo $MANTIS_ERROR[ERROR_GENERIC];
	}

	echo '<br />';
	print_bracket_link( $f_redirect_url, lang_get( 'proceed' ) );
	echo '<br /></div>';
	print_page_bot1( __FILE__ );
?>
