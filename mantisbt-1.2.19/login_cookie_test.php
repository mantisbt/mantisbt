<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * Check to see if cookies are working
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	if ( auth_is_user_authenticated() ) {
		$f_return = gpc_get_string( 'return' );
		$c_return = string_prepare_header( $f_return );

		# If this is the first login for an instance, then redirect to create project page.
		# Use lack of projects as a hint for such scenario.
		if ( is_blank( $f_return ) || $f_return == 'index.php' ) {
			if ( current_user_is_administrator() && project_table_empty() ) {
				$c_return = 'manage_proj_create_page.php';
			}
		}

		$t_redirect_url = $c_return;
	} else {
		$t_redirect_url = 'login_page.php?cookie_error=1';
	}

	print_header_redirect( $t_redirect_url, true, true );
