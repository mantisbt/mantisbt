<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Check login then redirect to main_page.php3 or to login_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php
	$f_username		= gpc_get_string( 'username', '' );
	$f_password		= gpc_get_string( 'password', '' );
	$f_perm_login	= gpc_get_bool( 'perm_login' );
	$f_return		= gpc_get_string( 'return', 'main_page.php' );

	if ( BASIC_AUTH == config_get( 'login_method' ) ) {
		$f_username = $_SERVER['REMOTE_USER'];
		$f_password = $_SERVER['PHP_AUTH_PW'];
 	}

	if ( auth_attempt_login( $f_username, $f_password, $f_perm_login ) ) {
		$t_redirect_url = 'login_cookie_test.php?return=' . $f_return;
	} else {
		$t_redirect_url = 'login_page.php?error=1';
	}

	print_header_redirect( $t_redirect_url );
?>
