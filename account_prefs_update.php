<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prefs_update.php,v 1.23 2002-12-30 09:44:44 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Updates prefs then redirect to account_prefs_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_user_id					= gpc_get_int( 'user_id' );
	$f_redirect_url				= gpc_get_string( 'redirect_url' );

	$t_prefs = user_pref_get( $f_user_id );

	$t_prefs->redirect_delay	= gpc_get_int( 'redirect_delay' );
	$t_prefs->refresh_delay		= gpc_get_int( 'refresh_delay' );
	$t_prefs->default_project	= gpc_get_int( 'default_project' );

	$t_prefs->language			= gpc_get_string( 'language' );

	$t_prefs->advanced_report	= gpc_get_bool( 'advanced_report' );
	$t_prefs->advanced_view		= gpc_get_bool( 'advanced_view' );
	$t_prefs->advanced_update	= gpc_get_bool( 'advanced_update' );
	$t_prefs->email_on_new		= gpc_get_bool( 'email_on_new' );
	$t_prefs->email_on_assigned	= gpc_get_bool( 'email_on_assigned' );
	$t_prefs->email_on_feedback	= gpc_get_bool( 'email_on_feedback' );
	$t_prefs->email_on_resolved	= gpc_get_bool( 'email_on_resolved' );
	$t_prefs->email_on_closed	= gpc_get_bool( 'email_on_closed' );
	$t_prefs->email_on_reopened	= gpc_get_bool( 'email_on_reopened' );
	$t_prefs->email_on_bugnote	= gpc_get_bool( 'email_on_bugnote' );
	$t_prefs->email_on_status	= gpc_get_bool( 'email_on_status' );
	$t_prefs->email_on_priority	= gpc_get_bool( 'email_on_priority' );

	# prevent users from changing other user's accounts
	if ( $f_user_id != auth_get_current_user_id() ) {
		check_access( ADMINISTRATOR );
	}

	# make sure the delay isn't too low
	if (( config_get( 'min_refresh_delay' ) > $t_prefs->refresh_delay )&&
		( $t_prefs->refresh_delay != 0 )) {
		$t_prefs->refresh_delay = config_get( 'min_refresh_delay' );
	}

	user_pref_set( $f_user_id, $t_prefs );

	print_page_top1();
	print_meta_redirect( $f_redirect_url );
	print_page_top2();
	echo '<br /><div align="center">';

	echo lang_get( 'operation_successful' );

	echo '<br />';
	print_bracket_link( $f_redirect_url, lang_get( 'proceed' ) );
	echo '<br /></div>';
	print_page_bot1( __FILE__ );
?>
