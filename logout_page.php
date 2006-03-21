<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: logout_page.php,v 1.18 2006-03-21 12:12:00 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	auth_logout();

	if ( HTTP_AUTH == config_get( 'login_method' ) ) {
		auth_http_set_logout_pending( true );
	}

	print_header_redirect( config_get( 'logout_redirect_page' ), /* die */ true, /* sanitize */ false );
?>
