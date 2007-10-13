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
	# $Id: account_prof_delete.php,v 1.27.22.1 2007-10-13 22:32:12 giallu Exp $
	# --------------------------------------------------------
?>
<?php
	# The specified profile is deleted and the user is redirected to
	# account_prof_menu_page.php3
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
	$f_profile_id	= gpc_get_int( 'profile_id' );

	if ( profile_is_global( $f_profile_id ) ) {
		access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );

		profile_delete( ALL_USERS, $f_profile_id );
		print_header_redirect( 'manage_prof_menu_page.php' );
	} else {
		profile_delete( auth_get_current_user_id(), $f_profile_id );
		print_header_redirect( 'account_prof_menu_page.php' );
	}
?>
