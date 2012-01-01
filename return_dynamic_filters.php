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
	require_once( 'current_user_api.php' );
	require_once( 'bug_api.php' );
	require_once( 'string_api.php' );
	require_once( 'date_api.php' );

	auth_ensure_user_authenticated();

	compress_enable();

	global $t_filter;
	global $t_select_modifier;
	$t_filter = current_user_get_bug_filter();
	if( $t_filter === false ) {
		$t_filter = filter_get_default();
	}
	$t_project_id = helper_get_current_project();
	$t_current_user_access_level = current_user_get_access_level();
	$t_accessible_custom_fields_ids = array();
	$t_accessible_custom_fields_names = array();
	$t_accessible_custom_fields_types = array();
	$t_accessible_custom_fields_values = array();
	$t_filter_cols = 7;
	$t_custom_cols = 1;
	$t_custom_rows = 0;

	if ( ON == config_get( 'filter_by_custom_fields' ) ) {
		$t_custom_cols = config_get( 'filter_custom_fields_per_row' );
		$t_custom_fields = custom_field_get_linked_ids( $t_project_id );

		foreach ( $t_custom_fields as $t_cfid ) {
			$t_field_info = custom_field_cache_row( $t_cfid, true );
			if ( $t_field_info['access_level_r'] <= $t_current_user_access_level ) {
				$t_accessible_custom_fields_ids[] = $t_cfid;
				$t_accessible_custom_fields_names[] = $t_field_info['name'];
				$t_accessible_custom_fields_types[] = $t_field_info['type'];
				$t_accessible_custom_fields_values[] = custom_field_distinct_values( $t_field_info, $t_project_id );
			}
		}

		if ( count( $t_accessible_custom_fields_ids ) > 0 ) {
			$t_per_row = config_get( 'filter_custom_fields_per_row' );
			$t_custom_rows = ceil( count( $t_accessible_custom_fields_ids ) / $t_per_row );
		}
	}

	$f_for_screen = gpc_get_bool( 'for_screen', true );

	$t_sort = $t_filter[ FILTER_PROPERTY_SORT_FIELD_NAME ];
	$t_dir = $t_filter[ FILTER_PROPERTY_SORT_DIRECTION ];
	$t_action  = "view_all_set.php?f=3";

	if ( $f_for_screen == false ) {
		$t_action  = "view_all_set.php";
	}

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

	$t_select_modifier = '';
	if ( 'advanced' == $f_view_type ) {
		$t_select_modifier = 'multiple="multiple" size="10" ';
	}

	/**
	 * Prepend headers to the dynamic filter forms that are sent as the response from this page.
	 */
	function return_dynamic_filters_prepend_headers() {
		if ( !headers_sent() ) {
			header( 'Content-Type: text/html; charset=utf-8' );
		}
	}

	$f_filter_target = gpc_get_string( 'filter_target' );
	$t_function_name = 'print_filter_' . utf8_substr( $f_filter_target, 0, -7 );
	if ( function_exists( $t_function_name ) ) {
		return_dynamic_filters_prepend_headers();
		call_user_func( $t_function_name );
	} else if ( 'custom_field' == utf8_substr( $f_filter_target, 0, 12 ) ) {
		# custom function
		$t_custom_id = utf8_substr( $f_filter_target, 13, -7 );
		return_dynamic_filters_prepend_headers();
		print_filter_custom_field( $t_custom_id );
	} else {
		$t_plugin_filters = filter_get_plugin_filters();
		$t_found = false;
		foreach ( $t_plugin_filters as $t_field_name => $t_filter_object ) {
			if ( $t_field_name . '_filter' == $f_filter_target ) {
				return_dynamic_filters_prepend_headers();
				print_filter_plugin_field( $t_field_name, $t_filter_object );
				$t_found = true;
				break;
			}
		}

		if ( !$t_found ) {
			# error - no function to populate the target (e.g., print_filter_foo)
			error_parameters( $f_filter_target );
			trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
		}
	}
