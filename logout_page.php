<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Removes all the cookies and then redirect to $g_logout_redirect_page
?>
<?php include( "core_API.php" ); ?>
<?php
	# delete cookies then redirect to $g_logout_redirect_page
	setcookie( $g_string_cookie );
	setcookie( $g_project_cookie );
	setcookie( $g_view_all_cookie );
	setcookie( $g_manage_cookie );

	print_header_redirect( $g_logout_redirect_page );
?>