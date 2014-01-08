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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'columns_api.php' );
	require_once( 'gpc_api.php' );

	form_security_validate( 'manage_config_columns_set' );

	# @@@ access_ensure_project_level( config_get( 'manage_project_threshold' ) );

	$f_project_id = gpc_get_int( 'project_id' );
	$f_view_issues_columns = gpc_get_string( 'view_issues_columns' );
	$f_print_issues_columns = gpc_get_string( 'print_issues_columns' );
	$f_csv_columns = gpc_get_string( 'csv_columns' );
	$f_excel_columns = gpc_get_string( 'excel_columns' );
	$f_update_columns_for_current_project = gpc_get_bool( 'update_columns_for_current_project' );
	$f_update_columns_as_my_default = gpc_get_bool( 'update_columns_as_my_default' );
	$f_update_columns_as_global_default = gpc_get_bool( 'update_columns_as_global_default' );
	$f_form_page = gpc_get_string( 'form_page' );

	# only admins can set global defaults.for ALL_PROJECT
	if ( $f_update_columns_as_global_default && $f_project_id == ALL_PROJECTS && !current_user_is_administrator() ) {
		access_denied();
	}

	# only MANAGERS can set global defaults.for a project
	if ( $f_update_columns_as_global_default && $f_project_id != ALL_PROJECTS ) {
		access_ensure_project_level( MANAGER, $f_project_id );
	}

	# user should only be able to set columns for a project that is accessible.
	if ( $f_update_columns_for_current_project && $f_project_id != ALL_PROJECTS ) {
		access_ensure_project_level( VIEWER, $f_project_id );
	}

	if ( $f_update_columns_as_my_default || $f_update_columns_as_global_default ) {
		$t_project_id = ALL_PROJECTS;
	} else {
		$t_project_id = $f_project_id;
		project_ensure_exists( $t_project_id );
	}

	# Calculate the user id to set the configuration for.
	if ( $f_update_columns_as_my_default || $f_update_columns_for_current_project ) {
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

	if ( serialize( config_get( 'view_issues_page_columns', '', $t_user_id, $t_project_id ) ) !== serialize( $t_view_issues_columns ) ) {
		config_set( 'view_issues_page_columns', $t_view_issues_columns, $t_user_id, $t_project_id );
	}
	if ( serialize( config_get( 'print_issues_page_columns', '', $t_user_id, $t_project_id ) ) !== serialize( $t_print_issues_columns ) ) {
		config_set( 'print_issues_page_columns', $t_print_issues_columns, $t_user_id, $t_project_id );
	}
	if ( serialize( config_get( 'csv_columns', '', $t_user_id, $t_project_id ) ) !== serialize( $t_csv_columns ) ) {
		config_set( 'csv_columns', $t_csv_columns, $t_user_id, $t_project_id );
	}
	if ( serialize( config_get( 'excel_columns', '', $t_user_id, $t_project_id ) ) !== serialize( $t_excel_columns ) ) {
		config_set( 'excel_columns', $t_excel_columns, $t_user_id, $t_project_id );
	}

	form_security_purge( 'manage_config_columns_set' );
?>
<br />
<div align="center">
<?php
	$t_redirect_url = $f_form_page === 'account' ? 'account_manage_columns_page.php' : 'manage_config_columns_page.php';
	html_page_top( null, $t_redirect_url );
	echo '<br />';
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php
	html_page_bottom();
