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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'compress_api.php' );
	require_once( 'filter_api.php' );

	auth_ensure_user_authenticated();

	$f_print = gpc_get_bool( 'print' );

	gpc_make_array( FILTER_SEARCH_CATEGORY );
	gpc_make_array( FILTER_SEARCH_SEVERITY_ID );
	gpc_make_array( FILTER_SEARCH_STATUS_ID );
	gpc_make_array( FILTER_SEARCH_REPORTER_ID );
	gpc_make_array( FILTER_SEARCH_HANDLER_ID );
	gpc_make_array( FILTER_SEARCH_PROJECT_ID );
	gpc_make_array( FILTER_SEARCH_RESOLUTION_ID );
	gpc_make_array( FILTER_SEARCH_PRODUCT_BUILD );
	gpc_make_array( FILTER_SEARCH_PRODUCT_VERSION );
	gpc_make_array( FILTER_SEARCH_FIXED_IN_VERSION );
	gpc_make_array( FILTER_SEARCH_TARGET_VERSION );
	gpc_make_array( FILTER_SEARCH_PROFILE );
	gpc_make_array( FILTER_SEARCH_PLATFORM );
	gpc_make_array( FILTER_SEARCH_OS );
	gpc_make_array( FILTER_SEARCH_OS_BUILD );
	gpc_make_array( FILTER_SEARCH_PRIORITY_ID );
	gpc_make_array( FILTER_SEARCH_MONITOR_USER_ID );
	gpc_make_array( FILTER_SEARCH_VIEW_STATE_ID );

	$my_filter = filter_get_default();
	$my_filter[FILTER_PROPERTY_FREE_TEXT] = gpc_get_string( FILTER_SEARCH_FREE_TEXT, '' );
	$my_filter[FILTER_PROPERTY_CATEGORY] = gpc_get_string_array( FILTER_SEARCH_CATEGORY, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_REPORTER_ID] = gpc_get_string_array( FILTER_SEARCH_REPORTER_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_HANDLER_ID] = gpc_get_string_array( FILTER_SEARCH_HANDLER_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_SEVERITY_ID] = gpc_get_string_array( FILTER_SEARCH_SEVERITY_ID, META_FILTER_ANY );

	$my_filter[FILTER_PROPERTY_STATUS_ID] = gpc_get_string_array( FILTER_SEARCH_STATUS_ID, META_FILTER_ANY );

	$my_filter[FILTER_PROPERTY_PROJECT_ID] = gpc_get_string_array( FILTER_SEARCH_PROJECT_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_RESOLUTION_ID] = gpc_get_string_array( FILTER_SEARCH_RESOLUTION_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PRODUCT_BUILD] = gpc_get_string_array( FILTER_SEARCH_PRODUCT_BUILD, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_FIXED_IN_VERSION] = gpc_get_string_array( FILTER_SEARCH_FIXED_IN_VERSION, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_TARGET_VERSION] = gpc_get_string_array( FILTER_SEARCH_TARGET_VERSION, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PRIORITY_ID] = gpc_get_string_array( FILTER_SEARCH_PRIORITY_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_MONITOR_USER_ID] = gpc_get_string_array( FILTER_SEARCH_MONITOR_USER_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PROFILE] = gpc_get_string_array( FILTER_SEARCH_PROFILE, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PLATFORM] = gpc_get_string_array( FILTER_SEARCH_PLATFORM, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_OS] = gpc_get_string_array( FILTER_SEARCH_OS, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_OS_BUILD] = gpc_get_string_array( FILTER_SEARCH_OS_BUILD, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_VIEW_STATE_ID] = gpc_get_string_array( FILTER_SEARCH_VIEW_STATE_ID, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_PRODUCT_VERSION] = gpc_get_string_array( FILTER_SEARCH_PRODUCT_VERSION, META_FILTER_ANY );

	// Filtering by Date
	$my_filter[FILTER_PROPERTY_FILTER_BY_DATE] = gpc_get_bool( FILTER_SEARCH_FILTER_BY_DATE );
	$my_filter[FILTER_PROPERTY_START_MONTH] = gpc_get_int( FILTER_SEARCH_START_MONTH, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_START_DAY] = gpc_get_int( FILTER_SEARCH_START_DAY, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_START_YEAR] = gpc_get_int( FILTER_SEARCH_START_YEAR, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_END_MONTH] = gpc_get_int( FILTER_SEARCH_END_MONTH, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_END_DAY] = gpc_get_int( FILTER_SEARCH_END_DAY, META_FILTER_ANY );
	$my_filter[FILTER_PROPERTY_END_YEAR] = gpc_get_int( FILTER_SEARCH_END_YEAR, META_FILTER_ANY );

	$my_filter[FILTER_PROPERTY_NOT_ASSIGNED] = gpc_get_bool( FILTER_SEARCH_NOT_ASSIGNED );

	$my_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] = gpc_get_int( FILTER_SEARCH_RELATIONSHIP_TYPE, -1 );
	$my_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] = gpc_get_int( FILTER_SEARCH_RELATIONSHIP_BUG, 0 );

	$my_filter[FILTER_PROPERTY_HIDE_STATUS_ID] = gpc_get_int( FILTER_SEARCH_HIDE_STATUS_ID, config_get( 'hide_status_default' ) );
	$my_filter[FILTER_PROPERTY_SHOW_STICKY_ISSUES] = gpc_get_bool( FILTER_SEARCH_SHOW_STICKY_ISSUES, config_get( 'show_sticky_issues' ) );

	$my_filter[FILTER_PROPERTY_SORT_FIELD_NAME] = gpc_get_string( FILTER_SEARCH_SORT_FIELD_NAME, '' );
	$my_filter[FILTER_PROPERTY_SORT_DIRECTION] = gpc_get_string( FILTER_SEARCH_SORT_DIRECTION, '' );
	$my_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] = gpc_get_int( FILTER_SEARCH_ISSUES_PER_PAGE, config_get( 'default_limit_view' ) );

	$t_highlight_changed = gpc_get_int( FILTER_SEARCH_HIGHLIGHT_CHANGED, -1 );
	if ( $t_highlight_changed != -1 ) {
		$my_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] = $t_highlight_changed;
	}

	# Handle custom fields.
	$t_custom_fields = array();
	foreach( $_GET as $t_var_name => $t_var_value ) {
		if ( strpos( $t_var_name, 'custom_field_' ) === 0 ) {
			$t_custom_field_id = utf8_substr( $t_var_name, 13 );
			$t_custom_fields[$t_custom_field_id] = $t_var_value;
		}
	}

	$my_filter['custom_fields'] = $t_custom_fields;

	# Must use advanced filter so that the project_id is applied and multiple
	# selections are handled.
	$my_filter['_view_type'] = 'advanced';

	$tc_setting_arr = filter_ensure_valid_filter( $my_filter );

	$t_settings_serialized = serialize( $tc_setting_arr );
	$t_settings_string = config_get( 'cookie_version' ) . '#' . $t_settings_serialized;

	# Store the filter string in the database: its the current filter, so some values won't change
	$t_project_id = helper_get_current_project();
	$t_project_id = ( $t_project_id * -1 );
	$t_row_id = filter_db_set_for_current_user( $t_project_id, false, '', $t_settings_string );

	# set cookie values
	gpc_set_cookie( config_get( 'view_all_cookie' ), $t_row_id, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );

	# redirect to print_all or view_all page
	if ( $f_print ) {
		$t_redirect_url = 'print_all_bug_page.php';
	} else {
		$t_redirect_url = 'view_all_bug_page.php';
	}

	print_header_redirect( $t_redirect_url );
