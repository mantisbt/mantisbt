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
	 * login_anon.php logs a user in anonymously without having to enter a username
	 * or password.
	 *
	 * Depends on two global configuration variables:
	 * allow_anonymous_login - bool which must be true to allow anonymous login.
	 * anonymous_account - name of account to login with.
	 *
	 * TODO:
	 * Check how manage account is impacted.
 	 * Might be extended to allow redirects for bug links etc.
 	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	$f_return = gpc_get_string( 'return', '' );

	$t_anonymous_account = config_get( 'anonymous_account' );

	if ( $f_return !== '' ) {
		$t_return = string_url( string_sanitize_url( $f_return ) );
		print_header_redirect( "login.php?username=$t_anonymous_account&perm_login=false&return=$t_return" );
	} else {
		print_header_redirect( "login.php?username=$t_anonymous_account&perm_login=false" );
	}
