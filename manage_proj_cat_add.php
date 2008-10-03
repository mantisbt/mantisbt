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
	# $Id: manage_proj_cat_add.php,v 1.33.2.1 2007-10-13 22:33:30 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'category_api.php' );

	form_security_validate( 'manage_proj_cat_add' );

	auth_reauthenticate();

	$f_project_id	= gpc_get_int( 'project_id' );
	$f_category		= gpc_get_string( 'category' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( is_blank( $f_category ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$t_categories = explode( '|', $f_category );
	$t_category_count = count( $t_categories );

	foreach ( $t_categories as $t_category ) {
		if ( is_blank( $t_category ) ) {
			continue;
		}

		$t_category = trim( $t_category );
		if ( category_is_unique( $f_project_id, $t_category ) ) {
			category_add( $f_project_id, $t_category );
		} else if ( 1 == $t_category_count ) {
			# We only error out on duplicates when a single value was
			#  given.  If multiple values were given, we just add the
			#  ones we can.  The others already exist so it isn't really
			#  an error.

			trigger_error( ERROR_CATEGORY_DUPLICATE, ERROR );
		}
	}

	form_security_purge( 'manage_proj_cat_add' );

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;

	print_header_redirect( $t_redirect_url );
?>
