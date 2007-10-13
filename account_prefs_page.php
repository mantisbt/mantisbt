<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: account_prefs_page.php,v 1.19.22.1 2007-10-13 22:32:03 giallu Exp $
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

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );

	#============ Parameters ============
	# (none)

	#============ Permissions ============
	auth_ensure_user_authenticated();

	current_user_ensure_unprotected();
?>
<?php
	include( 'account_prefs_inc.php' );

	html_page_top1( lang_get( 'change_preferences_link' ) );
	html_page_top2();

	edit_account_prefs();

	html_page_bottom1( __FILE__ );
?>
