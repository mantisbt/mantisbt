<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: account_prefs_page.php,v 1.10 2002-10-01 20:28:19 jfitzell Exp $
	# --------------------------------------------------------

	# CALLERS
	#	This page is called from:
	#	- print_account_menu()
	#	- header redirects from account_*.php

	# EXPECTED BEHAVIOUR
	#	- Display the user's current preferences
	#	- Allow the user to edit the preferences
	#	- Provide the option of saving changes or resetting to default values

	# CALLS
	#	This page calls the following pages:
	#	- acount_prefs_update.php  (to save changes)
	#	- account_prefs_reset.php  (to reset preferences to default values)

	# RESTRICTIONS & PERMISSIONS
	#	- User must be authenticated
	#	- The user's account must not be protected

	require_once( 'core.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	login_cookie_check();

	current_user_ensure_unprotected();
?>
<?php
	include( 'account_prefs_inc.php' );

	print_page_top1();
	print_page_top2();

	edit_account_prefs();

	print_page_bot1( __FILE__ );
?>
