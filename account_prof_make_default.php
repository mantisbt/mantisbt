<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prof_make_default.php,v 1.26 2005-02-12 20:01:03 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# Make the specified profile the default
	# Redirect to account_prof_menu_page.php
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php
	$f_profile_id	= gpc_get_int( 'profile_id' );

	current_user_set_pref( 'default_profile', $f_profile_id );

	print_header_redirect( 'account_prof_menu_page.php' );
?>
