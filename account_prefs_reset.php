<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prefs_reset.php,v 1.14 2002-10-18 23:36:51 jfitzell Exp $
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
	#	- The user's account must not be protected

	require_once( 'core.php' );

	#============ Parameters ============
	$f_redirect_url	= gpc_get_string( 'f_redirect_url', 'account_prefs_page.php' );

	#============ Permissions ============
	login_cookie_check();

	current_user_ensure_unprotected();
?>
<?php
	# delete and then recreate user prefs
	user_pref_delete( auth_get_current_user_id() );
	user_pref_create( auth_get_current_user_id() );

	print_header_redirect( $f_redirect_url );
?>
