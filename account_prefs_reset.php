<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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
	# $Id: account_prefs_reset.php,v 1.27.2.1 2007-10-13 22:32:05 giallu Exp $
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
	# helper_ensure_post();

	auth_ensure_user_authenticated();

	user_ensure_unprotected( $f_user_id );

	user_pref_set_default( $f_user_id );

	print_header_redirect( $f_redirect_url, true, true );
?>
