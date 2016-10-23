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
 * Search
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'print_api.php' );

auth_ensure_user_authenticated();

$f_print = gpc_get_bool( 'print' );

gpc_make_array( FILTER_PROPERTY_CATEGORY_ID );
gpc_make_array( FILTER_PROPERTY_SEVERITY );
gpc_make_array( FILTER_PROPERTY_STATUS );
gpc_make_array( FILTER_PROPERTY_REPORTER_ID );
gpc_make_array( FILTER_PROPERTY_HANDLER_ID );
gpc_make_array( FILTER_PROPERTY_PROJECT_ID );
gpc_make_array( FILTER_PROPERTY_RESOLUTION );
gpc_make_array( FILTER_PROPERTY_BUILD );
gpc_make_array( FILTER_PROPERTY_VERSION );
gpc_make_array( FILTER_PROPERTY_FIXED_IN_VERSION );
gpc_make_array( FILTER_PROPERTY_TARGET_VERSION );
gpc_make_array( FILTER_PROPERTY_PROFILE_ID );
gpc_make_array( FILTER_PROPERTY_PLATFORM );
gpc_make_array( FILTER_PROPERTY_OS );
gpc_make_array( FILTER_PROPERTY_OS_BUILD );
gpc_make_array( FILTER_PROPERTY_PRIORITY );
gpc_make_array( FILTER_PROPERTY_MONITOR_USER_ID );
gpc_make_array( FILTER_PROPERTY_VIEW_STATE );

$t_my_filter = filter_get_default();

# gpc_get_*_array functions expect 2nd param to be an array
$t_meta_filter_any_array = array( META_FILTER_ANY );

$t_my_filter[FILTER_PROPERTY_SEARCH] = gpc_get_string( FILTER_PROPERTY_SEARCH, '' );
$t_my_filter[FILTER_PROPERTY_CATEGORY_ID] = gpc_get_string_array( FILTER_PROPERTY_CATEGORY_ID, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_REPORTER_ID] = gpc_get_string_array( FILTER_PROPERTY_REPORTER_ID, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_HANDLER_ID] = gpc_get_string_array( FILTER_PROPERTY_HANDLER_ID, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_SEVERITY] = gpc_get_string_array( FILTER_PROPERTY_SEVERITY, $t_meta_filter_any_array );

$t_my_filter[FILTER_PROPERTY_STATUS] = gpc_get_string_array( FILTER_PROPERTY_STATUS, $t_meta_filter_any_array );

$t_my_filter[FILTER_PROPERTY_PROJECT_ID] = gpc_get_string_array( FILTER_PROPERTY_PROJECT_ID, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_RESOLUTION] = gpc_get_string_array( FILTER_PROPERTY_RESOLUTION, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_BUILD] = gpc_get_string_array( FILTER_PROPERTY_BUILD, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_FIXED_IN_VERSION] = gpc_get_string_array( FILTER_PROPERTY_FIXED_IN_VERSION, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_TARGET_VERSION] = gpc_get_string_array( FILTER_PROPERTY_TARGET_VERSION, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_PRIORITY] = gpc_get_string_array( FILTER_PROPERTY_PRIORITY, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_MONITOR_USER_ID] = gpc_get_string_array( FILTER_PROPERTY_MONITOR_USER_ID, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_PROFILE_ID] = gpc_get_string_array( FILTER_PROPERTY_PROFILE_ID, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_PLATFORM] = gpc_get_string_array( FILTER_PROPERTY_PLATFORM, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_OS] = gpc_get_string_array( FILTER_PROPERTY_OS, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_OS_BUILD] = gpc_get_string_array( FILTER_PROPERTY_OS_BUILD, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_VIEW_STATE] = gpc_get_string_array( FILTER_PROPERTY_VIEW_STATE, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_VERSION] = gpc_get_string_array( FILTER_PROPERTY_VERSION, $t_meta_filter_any_array );
$t_my_filter[FILTER_PROPERTY_MATCH_TYPE] = gpc_get_int( FILTER_PROPERTY_MATCH_TYPE, FILTER_MATCH_ALL );

# Filtering by Date
# Creation Date
$t_my_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] = gpc_get_bool( FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED );
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] = gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] = gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_START_DAY, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] = gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] = gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] = gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_END_DAY, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] = gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR, META_FILTER_ANY );
# Last Update Date
$t_my_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] = gpc_get_bool( FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] = gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_START_MONTH, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] = gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_START_DAY, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] = gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_START_YEAR, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] = gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_END_MONTH, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] = gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_END_DAY, META_FILTER_ANY );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] = gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_END_YEAR, META_FILTER_ANY );

$t_my_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_TYPE, -1 );
$t_my_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_BUG, 0 );

$t_my_filter[FILTER_PROPERTY_HIDE_STATUS] = gpc_get_int( FILTER_PROPERTY_HIDE_STATUS, config_get( 'hide_status_default' ) );
$t_my_filter[FILTER_PROPERTY_STICKY] = gpc_get_bool( FILTER_PROPERTY_STICKY, config_get( 'show_sticky_issues' ) );

$t_my_filter[FILTER_PROPERTY_SORT_FIELD_NAME] = gpc_get_string( FILTER_PROPERTY_SORT_FIELD_NAME, '' );
$t_my_filter[FILTER_PROPERTY_SORT_DIRECTION] = gpc_get_string( FILTER_PROPERTY_SORT_DIRECTION, '' );
$t_my_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] = gpc_get_int( FILTER_PROPERTY_ISSUES_PER_PAGE, config_get( 'default_limit_view' ) );

$t_highlight_changed = gpc_get_int( FILTER_PROPERTY_HIGHLIGHT_CHANGED, -1 );
if( $t_highlight_changed != -1 ) {
	$t_my_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] = $t_highlight_changed;
}

# Handle custom fields.
$t_custom_fields = array();
foreach( $_GET as $t_var_name => $t_var_value ) {
	if( strpos( $t_var_name, 'custom_field_' ) === 0 ) {
		$t_custom_field_id = utf8_substr( $t_var_name, 13 );
		$t_custom_fields[$t_custom_field_id] = $t_var_value;
	}
}

$t_my_filter['custom_fields'] = $t_custom_fields;

# Must use advanced filter so that the project_id is applied and multiple
# selections are handled.
$t_my_filter['_view_type'] = 'advanced';

$t_setting_arr = filter_ensure_valid_filter( $t_my_filter );

$t_settings_serialized = json_encode( $t_setting_arr );
$t_settings_string = FILTER_VERSION . '#' . $t_settings_serialized;

# Store the filter string in the database: its the current filter, so some values won't change
$t_project_id = helper_get_current_project();
$t_project_id = ( $t_project_id * -1 );
$t_row_id = filter_db_set_for_current_user( $t_project_id, false, '', $t_settings_string );

# set cookie values
gpc_set_cookie( config_get( 'view_all_cookie' ), $t_row_id, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );

# redirect to print_all or view_all page
if( $f_print ) {
	$t_redirect_url = 'print_all_bug_page.php';
} else {
	$t_redirect_url = 'view_all_bug_page.php';
}

print_header_redirect( $t_redirect_url );
