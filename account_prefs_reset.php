<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prefs_reset.php,v 1.26 2005-02-12 20:01:03 jlatour Exp $
	# --------------------------------------------------------

	# CALLERS
	#	This page is called from:
	#	- account_prefs_inc.php

	# EXPECTED BEHAVIOUR
	#	- Reset the user's preferences to default values
	#	- Redirect to account_prefs_page.php or another page, if given

	# CALLS
	#	This page conditionally redirects upon completion

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- User must not be protected

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'user_pref_api.php' );

	#============ Parameters ============
	$f_user_id = gpc_get_int( 'user_id' );
	$f_redirect_url	= gpc_get_string( 'redirect_url', 'account_prefs_page.php' );

	#============ Permissions ============
	auth_ensure_user_authenticated();

	user_ensure_unprotected( $f_user_id );

	user_pref_set_default( $f_user_id );

	print_header_redirect( $f_redirect_url );
?>
