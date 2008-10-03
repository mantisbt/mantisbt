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
	# $Id: manage_proj_user_add.php,v 1.5.2.1 2007-10-13 22:33:43 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	form_security_validate( 'manage_proj_user_add' );

	auth_reauthenticate();

	$f_project_id	= gpc_get_int( 'project_id' );
	$f_user_id		= gpc_get_int_array( 'user_id', array() );
	$f_access_level	= gpc_get_int( 'access_level' );

	# We should check both since we are in the project section and an
	#  admin might raise the first threshold and not realize they need
	#  to raise the second
	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	access_ensure_project_level( config_get( 'project_user_threshold' ), $f_project_id );

	# Add user(s) to the current project
	foreach( $f_user_id as $t_user_id ) {
		project_add_user( $f_project_id, $t_user_id, $f_access_level );
	}

	form_security_purge( 'manage_proj_user_add' );

	print_header_redirect( 'manage_proj_edit_page.php?project_id=' . $f_project_id );
?>
