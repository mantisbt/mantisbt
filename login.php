<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: login.php,v 1.35 2004-08-05 17:58:47 jlatour Exp $
	# --------------------------------------------------------
?>
<?php

	# Check login then redirect to main_page.php or to login_page.php

	require_once( 'core.php' );

	$f_username		= gpc_get_string( 'username', '' );
	$f_password		= gpc_get_string( 'password', '' );
	$f_perm_login	= gpc_get_bool( 'perm_login' );
	$f_return		= gpc_get_string( 'return', config_get( 'default_home_page' ) );
	$f_from			= gpc_get_string( 'from', '' );

	if ( BASIC_AUTH == config_get( 'login_method' ) ) {
		$f_username = $_SERVER['REMOTE_USER'];
		$f_password = $_SERVER['PHP_AUTH_PW'];
 	}

	if ( HTTP_AUTH == config_get( 'login_method' ) ) {
		if ( !auth_http_is_logout_pending() )
		{
			if ( isset( $_SERVER['PHP_AUTH_USER'] ) )
				$f_username = $_SERVER['PHP_AUTH_USER'];
			if ( isset( $_SERVER['PHP_AUTH_PW'] ) )
				$f_password = $_SERVER['PHP_AUTH_PW'];
		} else {
			auth_http_set_logout_pending( false );
			auth_http_prompt();
			break;
		}
	}

	if ( auth_attempt_login( $f_username, $f_password, $f_perm_login ) ) {
		$t_redirect_url = 'login_cookie_test.php?return=' . urlencode( $f_return );
	} else {
		$t_redirect_url = 'login_page.php?error=1';

		if ( HTTP_AUTH == config_get( 'login_method' ) ) {
			auth_http_prompt();
			exit;
		}
	}

	print_header_redirect( $t_redirect_url );
?>
