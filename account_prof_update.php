<?php
# MantisBT - A PHP based bugtracking system

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
 * This page updates the users profile information then redirects to
 * account_prof_menu_page.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 * @uses profile_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'print_api.php' );
require_api( 'profile_api.php' );

if( !config_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

$t_form_name = 'account_prof_update';
form_security_validate( $t_form_name );

auth_ensure_user_authenticated();
current_user_ensure_unprotected();

$f_action = gpc_get_string( 'action' );
$f_redirect_page = gpc_get_string( 'redirect', 'account_prof_menu_page.php' );

if( $f_action != 'add' ) {
	$f_profile_id = gpc_get_int( 'profile_id' );
}

switch( $f_action ) {
	case 'add':
		$f_platform		= gpc_get_string( 'platform' );
		$f_os			= gpc_get_string( 'os' );
		$f_os_build		= gpc_get_string( 'os_build' );
		$f_description	= gpc_get_string( 'description' );
		$t_user_id		= gpc_get_int( 'user_id' );

		if( ALL_USERS == $t_user_id ) {
			access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
		} else {
			access_ensure_global_level( config_get( 'add_profile_threshold' ) );
			$t_user_id = auth_get_current_user_id();
		}

		profile_create( $t_user_id, $f_platform, $f_os, $f_os_build, $f_description );
		break;

	case 'update':
		if( profile_is_global( $f_profile_id ) ) {
			access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
			$t_user_id = ALL_USERS;
		} else {
			$t_user_id = auth_get_current_user_id();
		}

		$f_platform = gpc_get_string( 'platform' );
		$f_os = gpc_get_string( 'os' );
		$f_os_build = gpc_get_string( 'os_build' );
		$f_description = gpc_get_string( 'description' );

		profile_update( $t_user_id, $f_profile_id, $f_platform, $f_os, $f_os_build, $f_description );
		break;

	case 'delete':
		if( profile_is_global( $f_profile_id ) ) {
			access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
			$t_user_id = ALL_USERS;
		} else {
			$t_user_id = auth_get_current_user_id();
		}

		helper_ensure_confirmed(
			sprintf( lang_get( 'delete_profile_confirm_msg' ),
				string_attribute( profile_get_name( $f_profile_id ) )
			),
			lang_get( 'delete_profile' )
		);

		profile_delete( $t_user_id, $f_profile_id );
		break;

	case 'make_default':
		current_user_set_pref( 'default_profile', $f_profile_id );
		break;
}

form_security_purge( $t_form_name );

layout_page_header( null, $f_redirect_page );
layout_page_begin();
html_operation_successful( $f_redirect_page );
layout_page_end();

