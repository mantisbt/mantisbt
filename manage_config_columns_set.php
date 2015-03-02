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
 * Set Columns Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );

form_security_validate( 'manage_config_columns_set' );

$f_project_id = gpc_get_int( 'project_id' );
$f_view_issues_columns = gpc_get_string( 'view_issues_columns' );
$f_print_issues_columns = gpc_get_string( 'print_issues_columns' );
$f_csv_columns = gpc_get_string( 'csv_columns' );
$f_excel_columns = gpc_get_string( 'excel_columns' );
$f_form_page = gpc_get_string( 'form_page' );

if( $f_project_id != ALL_PROJECTS ) {
	project_ensure_exists( $f_project_id );
}

$g_project_override = $f_project_id;
$t_project_id = $f_project_id;

$t_account_page = $f_form_page === 'account';

if( $f_project_id == ALL_PROJECTS ) {
	if( !$t_account_page ) {
		# From manage page, only admins can set global defaults for ALL_PROJECT
		if( !current_user_is_administrator() ) {
			access_denied();
		}
	}
} else {
	if( $t_account_page ) {
		access_ensure_project_level( config_get( 'view_bug_threshold' ), $f_project_id );
	} else {
		access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
	}
}

# For Account Column Customization, use current user.
# For Manage Column Customization, use no user.
if( $t_account_page ) {
	$t_user_id = auth_get_current_user_id();
} else {
	$t_user_id = NO_USER;
}

$t_all_columns = columns_get_all();

$t_view_issues_columns = columns_string_to_array( $f_view_issues_columns );
columns_ensure_valid( 'view_issues', $t_view_issues_columns, $t_all_columns );

$t_print_issues_columns = columns_string_to_array( $f_print_issues_columns );
columns_ensure_valid( 'print_issues', $t_print_issues_columns, $t_all_columns );

$t_csv_columns = columns_string_to_array( $f_csv_columns );
columns_ensure_valid( 'csv', $t_csv_columns, $t_all_columns );

$t_excel_columns = columns_string_to_array( $f_excel_columns );
columns_ensure_valid( 'excel', $t_excel_columns, $t_all_columns );

if( json_encode( config_get( 'view_issues_page_columns', '', $t_user_id, $t_project_id ) ) !== json_encode( $t_view_issues_columns ) ) {
	config_set( 'view_issues_page_columns', $t_view_issues_columns, $t_user_id, $t_project_id );
}
if( json_encode( config_get( 'print_issues_page_columns', '', $t_user_id, $t_project_id ) ) !== json_encode( $t_print_issues_columns ) ) {
	config_set( 'print_issues_page_columns', $t_print_issues_columns, $t_user_id, $t_project_id );
}
if( json_encode( config_get( 'csv_columns', '', $t_user_id, $t_project_id ) ) !== json_encode( $t_csv_columns ) ) {
	config_set( 'csv_columns', $t_csv_columns, $t_user_id, $t_project_id );
}
if( json_encode( config_get( 'excel_columns', '', $t_user_id, $t_project_id ) ) !== json_encode( $t_excel_columns ) ) {
	config_set( 'excel_columns', $t_excel_columns, $t_user_id, $t_project_id );
}

form_security_purge( 'manage_config_columns_set' );

$t_redirect_url = $t_account_page ? 'account_manage_columns_page.php' : 'manage_config_columns_page.php';
html_page_top( null, $t_redirect_url );

html_operation_successful( $t_redirect_url );

html_page_bottom();
