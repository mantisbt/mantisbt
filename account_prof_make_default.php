<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Make the specified profile the default
	# Redirect to account_prof_menu_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	# protected account check
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	$f_id	= gpc_get_int( 'f_id' );

	current_user_set_pref( 'default_profile', $f_id );

	print_header_redirect( 'account_prof_menu_page.php' );
?>
