<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: logout_page.php,v 1.15 2004-05-25 23:43:48 int2str Exp $
	# --------------------------------------------------------
?>
<?php
	# Removes all the cookies and then redirect to the page specified in
	#  the config option logout_redirect_page

	require_once( 'core.php' );

	auth_logout();

	if ( HTTP_AUTH == config_get( 'login_method' ) ) {
		auth_http_logout();
	}

	print_header_redirect( config_get( 'logout_redirect_page' ) );
?>
