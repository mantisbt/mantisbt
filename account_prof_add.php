<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prof_add.php,v 1.27 2005-02-25 00:23:48 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# This file adds a new profile and redirects to account_proj_menu_page.php
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'profile_api.php' );
?>
<?php
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php
	$f_platform		= gpc_get_string( 'platform' );
	$f_os			= gpc_get_string( 'os' );
	$f_os_build		= gpc_get_string( 'os_build' );
	$f_description	= gpc_get_string( 'description' );

	$t_user_id		= gpc_get_int( 'user_id' );
	if ( ALL_USERS != $t_user_id ) {
		$t_user_id = auth_get_current_user_id();
	}

	if ( ALL_USERS == $t_user_id ) {
		access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
	} else {
		access_ensure_global_level( config_get( 'add_profile_threshold' ) );
	}

	profile_create( $t_user_id, $f_platform, $f_os, $f_os_build, $f_description );

	if ( ALL_USERS == $t_user_id ) {
		print_header_redirect( 'manage_prof_menu_page.php' );
	} else {
		print_header_redirect( 'account_prof_menu_page.php' );
	}
?>
