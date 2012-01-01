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

	auth_ensure_user_authenticated();

	$f_type					= gpc_get_int( 'type', -1 );
	$f_source_query_id		= gpc_get_int( 'source_query_id', -1 );
	$f_print				= gpc_get_bool( 'print' );
	$f_temp_filter			= gpc_get_bool( 'temporary' );

	# validate filter type
	$f_default_view_type = 'simple';
	if ( ADVANCED_DEFAULT == config_get( 'view_filters' ) ) {
		$f_default_view_type = 'advanced';
	}

	$f_view_type = gpc_get_string( 'view_type', $f_default_view_type );
	if ( ADVANCED_ONLY == config_get( 'view_filters' ) ) {
		$f_view_type = 'advanced';
	}
	if ( SIMPLE_ONLY == config_get( 'view_filters' ) ) {
		$f_view_type = 'simple';
	}
	if ( !in_array( $f_view_type, array( 'simple', 'advanced' ) ) ) {
		$f_view_type = $f_default_view_type;
	}

	# these are all possibly multiple selections for advanced filtering
	$f_show_category = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_CATEGORY, null ) ) ) {
		$f_show_category = gpc_get_string_array( FILTER_PROPERTY_CATEGORY, META_FILTER_ANY );
	} else {
		$f_show_category = gpc_get_string( FILTER_PROPERTY_CATEGORY, META_FILTER_ANY );
		$f_show_category = array( $f_show_category );
	}

	$f_platform = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_PLATFORM, null ) ) ) {
		$f_platform = gpc_get_string_array( FILTER_PROPERTY_PLATFORM, META_FILTER_ANY );
	} else {
		$f_platform = gpc_get_string( FILTER_PROPERTY_PLATFORM, META_FILTER_ANY );
		$f_platform = array( $f_platform );
	}

	$f_os = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_OS, null ) ) ) {
		$f_os = gpc_get_string_array( FILTER_PROPERTY_OS, META_FILTER_ANY );
	} else {
		$f_os = gpc_get_string( FILTER_PROPERTY_OS, META_FILTER_ANY );
		$f_os = array( $f_os );
	}

	$f_os_build = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_OS_BUILD, null ) ) ) {
		$f_os_build = gpc_get_string_array( FILTER_PROPERTY_OS_BUILD, META_FILTER_ANY );
	} else {
		$f_os_build = gpc_get_string( FILTER_PROPERTY_OS_BUILD, META_FILTER_ANY );
		$f_os_build = array( $f_os_build );
	}

	$f_show_severity = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_SEVERITY_ID, null ) ) ) {
		$f_show_severity = gpc_get_string_array( FILTER_PROPERTY_SEVERITY_ID, META_FILTER_ANY );
	} else {
		$f_show_severity = gpc_get_string( FILTER_PROPERTY_SEVERITY_ID, META_FILTER_ANY );
		$f_show_severity = array( $f_show_severity );
	}

	$f_show_status = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_STATUS_ID, null ) ) ) {
		$f_show_status = gpc_get_string_array( FILTER_PROPERTY_STATUS_ID, META_FILTER_ANY );
	} else {
		$f_show_status = gpc_get_string( FILTER_PROPERTY_STATUS_ID, META_FILTER_ANY );
		$f_show_status = array( $f_show_status );
	}

	$f_hide_status = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_HIDE_STATUS_ID, null ) ) ) {
		$f_hide_status = gpc_get_string_array( FILTER_PROPERTY_HIDE_STATUS_ID, META_FILTER_NONE );
	} else {
		$f_hide_status = gpc_get_string( FILTER_PROPERTY_HIDE_STATUS_ID, META_FILTER_NONE );
		$f_hide_status = array( $f_hide_status );
	}

	$f_reporter_id = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_REPORTER_ID, null ) ) ) {
		$f_reporter_id = gpc_get_string_array( FILTER_PROPERTY_REPORTER_ID, META_FILTER_ANY );
	} else {
		$f_reporter_id = gpc_get_string( FILTER_PROPERTY_REPORTER_ID, META_FILTER_ANY );
		$f_reporter_id = array( $f_reporter_id );
	}

	$f_handler_id = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_HANDLER_ID, null ) ) ) {
		$f_handler_id = gpc_get_string_array( FILTER_PROPERTY_HANDLER_ID, META_FILTER_ANY );
	} else {
		$f_handler_id = gpc_get_string( FILTER_PROPERTY_HANDLER_ID, META_FILTER_ANY );
		$f_handler_id = array( $f_handler_id );
	}

	$f_project_id = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_PROJECT_ID, null ) ) ) {
		$f_project_id = gpc_get_int_array( FILTER_PROPERTY_PROJECT_ID, META_FILTER_CURRENT );
	} else {
		$f_project_id = gpc_get_int( FILTER_PROPERTY_PROJECT_ID, META_FILTER_CURRENT );
		$f_project_id = array( $f_project_id );
	}

	$f_show_resolution = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_RESOLUTION_ID, null ) ) ) {
		$f_show_resolution = gpc_get_string_array( FILTER_PROPERTY_RESOLUTION_ID, META_FILTER_ANY );
	} else {
		$f_show_resolution = gpc_get_string( FILTER_PROPERTY_RESOLUTION_ID, META_FILTER_ANY );
		$f_show_resolution = array( $f_show_resolution );
	}

	$f_show_build = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_PRODUCT_BUILD, null ) ) ) {
		$f_show_build = gpc_get_string_array( FILTER_PROPERTY_PRODUCT_BUILD, META_FILTER_ANY );
	} else {
		$f_show_build = gpc_get_string( FILTER_PROPERTY_PRODUCT_BUILD, META_FILTER_ANY );
		$f_show_build = array( $f_show_build );
	}

	$f_show_version = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_PRODUCT_VERSION, null ) ) ) {
		$f_show_version = gpc_get_string_array( FILTER_PROPERTY_PRODUCT_VERSION, META_FILTER_ANY );
	} else {
		$f_show_version = gpc_get_string( FILTER_PROPERTY_PRODUCT_VERSION, META_FILTER_ANY );
		$f_show_version = array( $f_show_version );
	}

	$f_fixed_in_version = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_FIXED_IN_VERSION, null ) ) ) {
		$f_fixed_in_version = gpc_get_string_array( FILTER_PROPERTY_FIXED_IN_VERSION, META_FILTER_ANY );
	} else {
		$f_fixed_in_version = gpc_get_string( FILTER_PROPERTY_FIXED_IN_VERSION, META_FILTER_ANY );
		$f_fixed_in_version = array( $f_fixed_in_version );
	}

	$f_target_version = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_TARGET_VERSION, null ) ) ) {
		$f_target_version = gpc_get_string_array( FILTER_PROPERTY_TARGET_VERSION, META_FILTER_ANY );
	} else {
		$f_target_version = gpc_get_string( FILTER_PROPERTY_TARGET_VERSION, META_FILTER_ANY );
		$f_target_version = array( $f_target_version );
	}

	$f_show_profile = array();
	if ( is_array( gpc_get( 'show_profile', null ) ) ) {
		$f_show_profile = gpc_get_string_array( 'show_profile', META_FILTER_ANY );
	} else {
		$f_show_profile = gpc_get_string( 'show_profile', META_FILTER_ANY );
		$f_show_profile = array( $f_show_profile );
	}

	$f_show_priority = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_PRIORITY_ID, null ) ) ) {
		$f_show_priority = gpc_get_string_array( FILTER_PROPERTY_PRIORITY_ID, META_FILTER_ANY );
	} else {
		$f_show_priority = gpc_get_string( FILTER_PROPERTY_PRIORITY_ID, META_FILTER_ANY );
		$f_show_priority = array( $f_show_priority );
	}

	$f_user_monitor = array();
	if ( is_array( gpc_get( FILTER_PROPERTY_MONITOR_USER_ID, null ) ) ) {
		$f_user_monitor = gpc_get_string_array( FILTER_PROPERTY_MONITOR_USER_ID, META_FILTER_ANY );
	} else {
		$f_user_monitor = gpc_get_string( FILTER_PROPERTY_MONITOR_USER_ID, META_FILTER_ANY );
		$f_user_monitor = array( $f_user_monitor );
	}

	$f_note_user_id = array();
    if ( is_array( gpc_get( FILTER_PROPERTY_NOTE_USER_ID, null ) ) ) {
        $f_note_user_id = gpc_get_string_array( FILTER_PROPERTY_NOTE_USER_ID, META_FILTER_ANY );
    } else {
        $f_note_user_id = gpc_get_string( FILTER_PROPERTY_NOTE_USER_ID, META_FILTER_ANY );
        $f_note_user_id = array( $f_note_user_id );
    }

	# these are only single values, even when doing advanced filtering
	$f_per_page				= gpc_get_int( FILTER_PROPERTY_ISSUES_PER_PAGE, -1 );
	$f_highlight_changed	= gpc_get_int( FILTER_PROPERTY_HIGHLIGHT_CHANGED, config_get( 'default_show_changed' ) );
	$f_sticky_issues		= gpc_get_bool( FILTER_PROPERTY_SHOW_STICKY_ISSUES );
	# sort direction
	$f_sort_d				= gpc_get_string( FILTER_PROPERTY_SORT_FIELD_NAME, '' );
	$f_dir_d				= gpc_get_string( FILTER_PROPERTY_SORT_DIRECTION, '' );
	$f_sort_0				= gpc_get_string( FILTER_PROPERTY_SORT_FIELD_NAME . '_0', 'last_updated' );
	$f_dir_0				= gpc_get_string( FILTER_PROPERTY_SORT_DIRECTION . '_0', 'DESC' );
	$f_sort_1				= gpc_get_string( FILTER_PROPERTY_SORT_FIELD_NAME . '_1', '' );
	$f_dir_1				= gpc_get_string( FILTER_PROPERTY_SORT_DIRECTION . '_1', '' );

	# date values
	$f_start_month			= gpc_get_int( FILTER_PROPERTY_START_MONTH, date( 'm' ) );
	$f_end_month			= gpc_get_int( FILTER_PROPERTY_END_MONTH, date( 'm' ) );
	$f_start_day			= gpc_get_int( FILTER_PROPERTY_START_DAY, 1 );
	$f_end_day				= gpc_get_int( FILTER_PROPERTY_END_DAY, date( 'd' ) );
	$f_start_year			= gpc_get_int( FILTER_PROPERTY_START_YEAR, date( 'Y' ) );
	$f_end_year				= gpc_get_int( FILTER_PROPERTY_END_YEAR, date( 'Y' ) );
	$f_search				= gpc_get_string( FILTER_PROPERTY_FREE_TEXT, '' );
	$f_and_not_assigned		= gpc_get_bool( FILTER_PROPERTY_NOT_ASSIGNED );
	$f_do_filter_by_date	= gpc_get_bool( FILTER_PROPERTY_FILTER_BY_DATE );
	$f_view_state			= gpc_get_int( FILTER_PROPERTY_VIEW_STATE_ID, META_FILTER_ANY );

	$f_tag_string			= gpc_get_string( FILTER_PROPERTY_TAG_STRING, '' );
	$f_tag_select			= gpc_get_int( FILTER_PROPERTY_TAG_SELECT, '0' );

	# plugin filter updates
	$t_plugin_filters = filter_get_plugin_filters();
	$f_filter_input = array();

	foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
		switch( $t_filter_object->type ) {
			case FILTER_TYPE_STRING:
				$f_filter_input[ $t_field_name ] = gpc_get_string( $t_field_name, $t_filter_object->default );
				break;

			case FILTER_TYPE_INT:
				$f_filter_input[ $t_field_name ] = gpc_get_int( $t_field_name, $t_filter_object->default );
				break;

			case FILTER_TYPE_BOOLEAN:
				$f_filter_input[ $t_field_name ] = gpc_get_bool( $t_field_name, OFF );
				break;

			case FILTER_TYPE_MULTI_STRING:
				$f_filter_input[ $t_field_name ] = gpc_get_string_array( $t_field_name, $t_filter_object->default );
				break;

			case FILTER_TYPE_MULTI_INT:
				$f_filter_input[ $t_field_name ] = gpc_get_int_array( $t_field_name, $t_filter_object->default );
				break;
		}
	}

	# custom field updates
	$t_custom_fields 		= custom_field_get_ids(); /** @todo (thraxisp) This should really be the linked ids, but we don't know the project */
	$f_custom_fields_data 	= array();
	if ( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			if (custom_field_type( $t_cfid ) == CUSTOM_FIELD_TYPE_DATE) {
				$t_control = gpc_get_string( 'custom_field_' . $t_cfid . '_control', null);

				$t_year = gpc_get_int( 'custom_field_' . $t_cfid . '_start_year', null);
				$t_month = gpc_get_int( 'custom_field_' . $t_cfid . '_start_month', null);
				$t_day = gpc_get_int( 'custom_field_' . $t_cfid . '_start_day', null);
				$t_start_date = mktime(0, 0, 0, $t_month, $t_day, $t_year);

				$t_year = gpc_get_int( 'custom_field_' . $t_cfid . '_end_year', null);
				$t_month = gpc_get_int( 'custom_field_' . $t_cfid . '_end_month', null);
				$t_day = gpc_get_int( 'custom_field_' . $t_cfid . '_end_day', null);
				$t_end_date = mktime(0, 0, 0, $t_month, $t_day, $t_year);

				$f_custom_fields_data[$t_cfid] = array();
				$f_custom_fields_data[$t_cfid][0] = $t_control;
				$t_start = 1;
				$t_end = 1;
				$t_one_day = 86399;

				switch ($t_control)
				{
				case CUSTOM_FIELD_DATE_ANY:
				case CUSTOM_FIELD_DATE_NONE:
					break ;
				case CUSTOM_FIELD_DATE_BETWEEN:
					$t_start = $t_start_date;
					$t_end = $t_end_date + $t_one_day - 1;
					break ;
				case CUSTOM_FIELD_DATE_ONORBEFORE:
					$t_end = $t_start_date + $t_one_day - 1;
					break;
				case CUSTOM_FIELD_DATE_BEFORE:
					$t_end = $t_start_date;
					break ;
				case CUSTOM_FIELD_DATE_ON:
					$t_start = $t_start_date;
					$t_end = $t_start_date + $t_one_day - 1;
					break;
				case CUSTOM_FIELD_DATE_AFTER:
					$t_start = $t_start_date + $t_one_day - 1;
					$t_end = 2147483647; // Some time in 2038, max value of a signed int.
					break;
				case CUSTOM_FIELD_DATE_ONORAFTER:
					$t_start = $t_start_date;
					$t_end = 2147483647; // Some time in 2038, max value of a signed int.
					break;
				}
				$f_custom_fields_data[$t_cfid][1] = $t_start;
				$f_custom_fields_data[$t_cfid][2] = $t_end;
			} else {
				if ( is_array( gpc_get( 'custom_field_' . $t_cfid, null ) ) ) {
					$f_custom_fields_data[$t_cfid] = gpc_get_string_array( 'custom_field_' . $t_cfid, META_FILTER_ANY );
				} else {
					$f_custom_fields_data[$t_cfid] = gpc_get_string( 'custom_field_' . $t_cfid, META_FILTER_ANY );
					$f_custom_fields_data[$t_cfid] = array( $f_custom_fields_data[$t_cfid] );
				}
			}
		}
	}

	$f_relationship_type = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_TYPE, -1 );
	$f_relationship_bug = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_BUG, 0 );

	if ( $f_temp_filter ) {
		$f_type = 1;
	}

	if ( $f_and_not_assigned ) {
		$f_and_not_assigned = 'on';
	}

	if ( $f_do_filter_by_date ) {
		$f_do_filter_by_date = 'on';
	}

	if ( $f_sticky_issues ) {
		$f_sticky_issues = 'on';
	} else {
		$f_sticky_issues = 'off';
	}

	if ( $f_type < 0 ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}

	$t_hide_status_default = config_get( 'hide_status_default' );

	# show bugs per page
	if ( $f_per_page < 0 ) {
		$f_per_page = config_get( 'default_limit_view' );
	}

	# combine sort settings
	#  (f_sort overrides f_sort_1 if set to keep old sorting code working in view_all_bug_inc)
	$f_sort = ( ( $f_sort_d != "" ) ? $f_sort_d : $f_sort_0 ) . ( ( $f_sort_1 != "" ) ? "," . $f_sort_1 : "" );
	$f_dir = ( ( $f_dir_d != "" ) ? $f_dir_d : $f_dir_0 ) . ( ( $f_dir_1 != "" ) ? "," . $f_dir_1 : "" );

	# -1 is a special case stored query: it means we want to reset our filter
	if ( ( $f_type == 3 ) && ( $f_source_query_id == -1 ) ) {
		$f_type = 0;
	}

/*   array contents
     --------------
	 0: version
	 1: $f_show_category
	 2: $f_show_severity
	 3: $f_show_status
	 4: $f_per_page
	 5: $f_highlight_changed
	 6: $f_hide_closed
	 7: $f_reporter_id
	 8: $f_handler_id
	 9: $f_sort
	10: $f_dir
	11: $f_start_month
	12: $f_start_day
	13: $f_start_year
	14: $f_end_month
	15: $f_end_day
	16: $f_end_year
	17: $f_search
	18: $f_hide_resolved
	19: $f_and_not_assigned
	20: $f_show_resolution
	21: $f_show_build
	22: $f_show_version
	23: $f_do_filter_by_date
	24: $f_custom_field
	25: $f_relationship_type
	26: $f_relationship_bug
	27: $f_show_profile

*/
	# Set new filter values.  These are stored in a cookie
	$t_view_all_cookie_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
	$t_view_all_cookie = filter_db_get_filter( $t_view_all_cookie_id );

	# process the cookie if it exists, it may be blank in a new install
	if ( !is_blank( $t_view_all_cookie ) ) {
		$t_setting_arr = filter_deserialize( $t_view_all_cookie );
		if ( false === $t_setting_arr ) {
			# couldn't deserialize, if we were trying to use the filter, clear it and reload
			# for ftype = 0, 1, or 3, we are going to re-write the filter anyways
			if ( !in_array( $f_type, array( 0, 1, 3 ) ) ) {
				gpc_clear_cookie( 'view_all_cookie' );
				error_proceed_url( 'view_all_set.php?type=0' );
				trigger_error( ERROR_FILTER_TOO_OLD, ERROR );
				exit; # stop here
			}
		}
	} else {
		# no cookie found, set it
		$f_type = 1;
	}

	$t_cookie_version = config_get( 'cookie_version' );
	$t_default_show_changed = config_get( 'default_show_changed' );

	switch ( $f_type ) {
		# New cookie
		case '0':
				log_event( LOG_FILTERING, 'view_all_set.php: New cookie' );
				$t_setting_arr = array();

				break;
		# Update filters
		case '1':
				log_event( LOG_FILTERING, 'view_all_set.php: Update filters' );
				$t_setting_arr['_version'] 								= $t_cookie_version;
				$t_setting_arr['_view_type'] 							= $f_view_type;
				$t_setting_arr[ FILTER_PROPERTY_CATEGORY ] 				= $f_show_category;
				$t_setting_arr[ FILTER_PROPERTY_SEVERITY_ID ] 			= $f_show_severity;
				$t_setting_arr[ FILTER_PROPERTY_STATUS_ID ] 			= $f_show_status;
				$t_setting_arr[ FILTER_PROPERTY_ISSUES_PER_PAGE ] 		= $f_per_page;
				$t_setting_arr[ FILTER_PROPERTY_HIGHLIGHT_CHANGED ] 	= $f_highlight_changed;
				$t_setting_arr[ FILTER_PROPERTY_REPORTER_ID ] 			= $f_reporter_id;
				$t_setting_arr[ FILTER_PROPERTY_HANDLER_ID ] 			= $f_handler_id;
				$t_setting_arr[ FILTER_PROPERTY_PROJECT_ID ] 			= $f_project_id;
				$t_setting_arr[ FILTER_PROPERTY_SORT_FIELD_NAME ] 		= $f_sort;
				$t_setting_arr[ FILTER_PROPERTY_SORT_DIRECTION ] 		= $f_dir;
				$t_setting_arr[ FILTER_PROPERTY_START_MONTH ] 			= $f_start_month;
				$t_setting_arr[ FILTER_PROPERTY_START_DAY ] 			= $f_start_day;
				$t_setting_arr[ FILTER_PROPERTY_START_YEAR ] 			= $f_start_year;
				$t_setting_arr[ FILTER_PROPERTY_END_MONTH ] 			= $f_end_month;
				$t_setting_arr[ FILTER_PROPERTY_END_DAY ] 				= $f_end_day;
				$t_setting_arr[ FILTER_PROPERTY_END_YEAR ] 				= $f_end_year;
				$t_setting_arr[ FILTER_PROPERTY_FREE_TEXT ] 			= $f_search;
				$t_setting_arr[ FILTER_PROPERTY_HIDE_STATUS_ID ] 		= $f_hide_status;
				$t_setting_arr[ FILTER_PROPERTY_NOT_ASSIGNED ] 			= $f_and_not_assigned;
				$t_setting_arr[ FILTER_PROPERTY_RESOLUTION_ID ] 		= $f_show_resolution;
				$t_setting_arr[ FILTER_PROPERTY_PRODUCT_BUILD ] 		= $f_show_build;
				$t_setting_arr[ FILTER_PROPERTY_PRODUCT_VERSION ] 		= $f_show_version;
				$t_setting_arr[ FILTER_PROPERTY_FILTER_BY_DATE ] 		= $f_do_filter_by_date;
				$t_setting_arr[ FILTER_PROPERTY_FIXED_IN_VERSION ] 		= $f_fixed_in_version;
				$t_setting_arr[ FILTER_PROPERTY_TARGET_VERSION ] 		= $f_target_version;
				$t_setting_arr[ FILTER_PROPERTY_PRIORITY_ID ] 			= $f_show_priority;
				$t_setting_arr[ FILTER_PROPERTY_MONITOR_USER_ID ] 		= $f_user_monitor;
				$t_setting_arr[ FILTER_PROPERTY_VIEW_STATE_ID ] 		= $f_view_state;
				$t_setting_arr['custom_fields'] 						= $f_custom_fields_data;
				$t_setting_arr[ FILTER_PROPERTY_SHOW_STICKY_ISSUES ] 	= $f_sticky_issues;
				$t_setting_arr[ FILTER_PROPERTY_RELATIONSHIP_TYPE ] 	= $f_relationship_type;
				$t_setting_arr[ FILTER_PROPERTY_RELATIONSHIP_BUG ] 		= $f_relationship_bug;
				$t_setting_arr['show_profile'] 							= $f_show_profile;
				$t_setting_arr[ FILTER_PROPERTY_PLATFORM ] 				= $f_platform;
				$t_setting_arr[ FILTER_PROPERTY_OS ] 					= $f_os;
				$t_setting_arr[ FILTER_PROPERTY_OS_BUILD ] 				= $f_os_build;
				$t_setting_arr[ FILTER_PROPERTY_TAG_STRING ] 			= $f_tag_string;
				$t_setting_arr[ FILTER_PROPERTY_TAG_SELECT ] 			= $f_tag_select;
				$t_setting_arr[ FILTER_PROPERTY_NOTE_USER_ID ] 			= $f_note_user_id;
				$t_setting_arr = array_merge( $t_setting_arr, $f_filter_input );
				break;
		# Set the sort order and direction
		case '2':
				log_event( LOG_FILTERING, 'view_all_set.php: Set the sort order and direction.' );

				# We only need to set those fields that we are overriding
				$t_setting_arr[ FILTER_PROPERTY_SORT_FIELD_NAME ] = $f_sort;
				$t_setting_arr[ FILTER_PROPERTY_SORT_DIRECTION ] = $f_dir;

				break;
		# This is when we want to copy another query from the
		# database over the top of our current one
		case '3':
				log_event( LOG_FILTERING, 'view_all_set.php: Copy another query from database' );

				$t_filter_string = filter_db_get_filter( $f_source_query_id );
				# If we can use the query that we've requested,
				# grab it. We will overwrite the current one at the
				# bottom of this page
				$t_setting_arr = filter_deserialize( $t_filter_string );
				if ( false === $t_setting_arr ) {
					# couldn't deserialize, if we were trying to use the filter, clear it and reload
					gpc_clear_cookie( 'view_all_cookie' );
					error_proceed_url( 'view_all_set.php?type=0' );
					trigger_error( ERROR_FILTER_TOO_OLD, ERROR );
					exit; # stop here
				}
				break;
		# Generalise the filter
		case '4':
				log_event( LOG_FILTERING, 'view_all_set.php: Generalise the filter' );

				$t_setting_arr[ FILTER_PROPERTY_CATEGORY ]			= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_REPORTER_ID ] 		= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_HANDLER_ID ] 		= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_PRODUCT_BUILD ] 	= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_PRODUCT_VERSION ] 	= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_PRIORITY_ID ]		= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_FIXED_IN_VERSION ]	= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_TARGET_VERSION ]	= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_MONITOR_USER_ID ] 	= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_NOTE_USER_ID ]  	= array( META_FILTER_ANY );
				$t_setting_arr[ FILTER_PROPERTY_RELATIONSHIP_TYPE ] = -1;
				$t_setting_arr[ FILTER_PROPERTY_RELATIONSHIP_BUG ] 	= 0;

				$t_custom_fields 		= custom_field_get_ids(); # @@@ (thraxisp) This should really be the linked ids, but we don't know the project
				$t_custom_fields_data 	= array();
				if ( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
					foreach( $t_custom_fields as $t_cfid ) {
						$t_custom_fields_data[$t_cfid] =  array( META_FILTER_ANY );
					}
				}
				$t_setting_arr['custom_fields'] = $t_custom_fields_data;

				break;
		# Just set the search string value
		case '5':
				log_event( LOG_FILTERING, 'view_all_set.php: Search Text' );
				$t_setting_arr[ FILTER_PROPERTY_FREE_TEXT ] = $f_search;

				break;
		# Just set the view_state (simple / advanced) value
		case '6':
				log_event( LOG_FILTERING, 'view_all_set.php: View state (simple/advanced)' );
				$t_setting_arr['_view_type'] = $f_view_type;

				break;
		# does nothing. catch all case
		default:
				log_event( LOG_FILTERING, 'view_all_set.php: default - do nothing' );
				break;
	}

	$tc_setting_arr = filter_ensure_valid_filter( $t_setting_arr );

	$t_settings_serialized = serialize( $tc_setting_arr );
	$t_settings_string = $t_cookie_version . '#' . $t_settings_serialized;

	# If only using a temporary filter, don't store it in the database
	if ( !$f_temp_filter ) {
		# Store the filter string in the database: its the current filter, so some values won't change
		$t_project_id = helper_get_current_project();
		$t_project_id = ( $t_project_id * -1 );
		$t_row_id = filter_db_set_for_current_user( $t_project_id, false, '', $t_settings_string );

		# set cookie values
		gpc_set_cookie( config_get( 'view_all_cookie' ), $t_row_id, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );
	}

	# redirect to print_all or view_all page
	if ( $f_print ) {
		$t_redirect_url = 'print_all_bug_page.php';
	} else {
		$t_redirect_url = 'view_all_bug_page.php';
	}

	if ( $f_temp_filter ) {
		$t_token_id = token_set( TOKEN_FILTER, $t_settings_serialized );
		$t_redirect_url = $t_redirect_url . '?filter=' . $t_token_id;
		html_meta_redirect( $t_redirect_url, 0 );
	} else {
		print_header_redirect( $t_redirect_url );
	}
