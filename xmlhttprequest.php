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
	 * This is the first page a user sees when they login to the bugtracker
	 * News is displayed which can notify users of any important changes
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'logging_api.php' );
	require_once( $t_core_path . 'xmlhttprequest_api.php' );

	auth_ensure_user_authenticated();

	$f_entrypoint = gpc_get_string( 'entrypoint' );

	$t_function = 'xmlhttprequest_' . $f_entrypoint;
	if ( function_exists( $t_function ) ) {
		log_event( LOG_AJAX, "Calling {$t_function}..." );
		call_user_func( $t_function );
	} else {
		log_event( LOG_AJAX, "Unknown function for entry point = " . $t_function );
		echo 'unknown entry point';
	}
