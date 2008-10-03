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
	# $Id: manage_user_proj_add.php,v 1.22.2.1 2007-10-13 22:33:56 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	form_security_validate('manage_user_proj_add');

	auth_reauthenticate();

	$f_user_id		= gpc_get_int( 'user_id' );
	$f_access_level	= gpc_get_int( 'access_level' );
	$f_project_id	= gpc_get_int_array( 'project_id', array() );
	$t_manage_user_threshold = config_get( 'manage_user_threshold' );

	foreach ( $f_project_id as $t_proj_id ) {
		if ( access_has_project_level( $t_manage_user_threshold, $t_proj_id ) ) {
			project_add_user( $t_proj_id, $f_user_id, $f_access_level );
		}
	}

	form_security_purge('manage_user_proj_add');

	print_header_redirect( 'manage_user_edit_page.php?user_id=' . $f_user_id );
?>
