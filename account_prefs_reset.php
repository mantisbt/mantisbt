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
	$f_user_id				= gpc_get_int( 'f_user_id' );
	$f_redirect_url			= gpc_get_string( 'f_redirect_url', 'account_prefs_page.php' );

	# protected account check
	if ( user_is_protected( $f_user_id ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	# prevent users from changing other user's accounts
	if ( $f_user_id != auth_get_current_user_id() ) {
		check_access( ADMINISTRATOR );
	}

	# delete and then recreate user prefs
	user_delete_prefs( $f_user_id );
	user_create_prefs( $f_user_id );

	print_header_redirect( $f_redirect_url );
?>
