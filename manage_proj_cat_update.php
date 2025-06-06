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
 * Update Project Categories
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'utility_api.php' );

form_security_validate( 'manage_proj_cat_update' );

auth_reauthenticate();

$f_category_id     = gpc_get_int( 'category_id' );
$f_name            = trim( gpc_get_string( 'name' ) );
$f_assigned_to     = gpc_get_int( 'assigned_to', 0 );
# Underlying DB column is integer, but we use it as bool since we only have 2 states
$f_status          = (int)gpc_get_bool( 'status', CATEGORY_STATUS_DISABLED );

if( is_blank( $f_name ) ) {
	error_parameters( 'name' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$t_row = category_get_row( $f_category_id );
$t_old_name = $t_row['name'];
$t_project_id = $t_row['project_id'];

access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_project_id );

# check for duplicate
if( mb_strtolower( $f_name ) != mb_strtolower( $t_old_name ) ) {
	category_ensure_unique( $t_project_id, $f_name );
}

category_update( $f_category_id, $f_name, $f_assigned_to, $f_status );

form_security_purge( 'manage_proj_cat_update' );

if( $t_project_id == ALL_PROJECTS ) {
	$t_redirect_url = 'manage_proj_page.php';
} else {
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $t_project_id . '#categories';
}

print_header_redirect( $t_redirect_url );
