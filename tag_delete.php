<?php
# Mantis - a php based bugtracking system

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

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	/**
	 * requires tag_api
	 */
	require_once( $t_core_path . 'tag_api.php' );

	form_security_validate( 'tag_delete' );

	access_ensure_global_level( config_get( 'tag_edit_threshold' ) );

	$f_tag_id = gpc_get_int( 'tag_id' );
	$t_tag_row = tag_get( $f_tag_id );

	helper_ensure_confirmed( lang_get( 'tag_delete_message' ), lang_get( 'tag_delete_button' ) );

	tag_delete( $f_tag_id );

	form_security_purge( 'tag_delete' );
	
	print_successful_redirect( config_get( 'default_home_page' ) );
