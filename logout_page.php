<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Removes all the cookies and then redirect to $g_logout_redirect_page
?>
<?php require_once( 'core.php' ); ?>
<?php
	# delete cookies then redirect to $g_logout_redirect_page
	auth_clear_cookies();

	setcookie( $g_project_cookie,	'', -1, $g_cookie_path );
	setcookie( $g_view_all_cookie,	'', -1, $g_cookie_path );
	setcookie( $g_manage_cookie,	'', -1, $g_cookie_path );

	print_header_redirect( $g_logout_redirect_page );
?>