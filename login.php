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
	 * Check login then redirect to main_page.php or to login_page.php
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * Mantis Core API's
	  */
	require_once( 'core.php' );

	$f_username		= gpc_get_string( 'username', '' );
	$f_password		= gpc_get_string( 'password', '' );
	$f_perm_login	= gpc_get_bool( 'perm_login' );
	$f_return		= gpc_get_string( 'return', config_get( 'default_home_page' ) );
	$f_from			= gpc_get_string( 'from', '' );
	
	$f_username = auth_prepare_username($f_username);
	$f_password = auth_prepare_password($f_password);

	if ( auth_attempt_login( $f_username, $f_password, $f_perm_login ) ) {
		$t_redirect_url = 'login_cookie_test.php?return=' . string_sanitize_url( $f_return );
	} else {
		$t_redirect_url = 'login_page.php?return=' . string_sanitize_url( $f_return ) . '&error=1';

		if ( HTTP_AUTH == config_get( 'login_method' ) ) {
			auth_http_prompt();
			exit;
		}
	}

	print_header_redirect( $t_redirect_url );
?>
