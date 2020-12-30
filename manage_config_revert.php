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
 * Manage configuration
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
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 */


require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );

form_security_validate( 'manage_config_revert' );

auth_reauthenticate();

$f_project_id = gpc_get_int( 'project', 0 );
$f_revert = gpc_get_string( 'revert', '' );
$f_return = gpc_get_string( 'return' );

$t_access = true;
$t_revert_vars = explode( ',', $f_revert );
array_walk( $t_revert_vars, 'trim' );
foreach ( $t_revert_vars as $t_revert ) {
	$t_access &= access_has_project_level( config_get_access( $t_revert ), $f_project_id );
}

if( !$t_access ) {
	access_denied();
}

if( '' != $f_revert ) {
	# Confirm with the user
	helper_ensure_confirmed(
		sprintf( lang_get( 'config_delete_sure' ),
			string_html_specialchars( implode( ', ', $t_revert_vars ) ),
			string_attribute( project_get_name( $f_project_id ) )
		),
		lang_get( 'delete_config_button' ) );

	foreach ( $t_revert_vars as $t_revert ) {
		config_delete( $t_revert, ALL_USERS, $f_project_id );
	}
}

form_security_purge( 'manage_config_revert' );

$t_redirect_url = $f_return;

layout_page_header( null, $t_redirect_url );

layout_page_begin();

html_operation_successful( $t_redirect_url );

layout_page_end();
