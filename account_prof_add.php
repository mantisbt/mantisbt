<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This file adds a new profile and redirects to account_proj_menu_page.php
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( REPORTER );

	# protected account check
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}

	$f_platform		= gpc_get_string( 'f_platform' );
	$f_os			= gpc_get_string( 'f_os' );
	$f_os_build		= gpc_get_string( 'f_os_build' );
	$f_description	= gpc_get_string( 'f_description' );

	profile_create( auth_get_current_user_id(), $f_platform, $f_os, $f_os_build, $f_description );

	print_header_redirect( 'account_prof_menu_page.php' );
?>
