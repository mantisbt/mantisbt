<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prof_make_default.php,v 1.20 2003-01-23 23:02:49 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# Make the specified profile the default
	# Redirect to account_prof_menu_page.php
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'current_user_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_profile_id	= gpc_get_int( 'profile_id' );

	current_user_set_pref( 'default_profile', $f_profile_id );

	print_header_redirect( 'account_prof_menu_page.php' );
?>
