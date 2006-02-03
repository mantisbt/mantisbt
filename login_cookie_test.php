<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: login_cookie_test.php,v 1.8.22.1 2005-12-05 12:14:47 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# Check to see if cookies are working
?>
<?php require_once( 'core.php' ) ?>
<?php
	$f_return = gpc_get_string( 'return', config_get( 'default_home_page' ) );

	$c_return = string_prepare_header( $f_return );

	if ( auth_is_user_authenticated() ) {
		$t_redirect_url = $c_return;
	} else {
		$t_redirect_url = 'login_page.php?cookie_error=1';
	}

	print_header_redirect( $t_redirect_url );
?>