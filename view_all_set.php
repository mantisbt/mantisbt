<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: view_all_set.php,v 1.37 2004-08-06 15:38:38 jlatour Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_type					= gpc_get_int( 'type', -1 );
	$f_view_type			= gpc_get_string( 'view_type', 'simple' );
	$f_source_query_id		= gpc_get_int( 'source_query_id', -1 );
	$f_print				= gpc_get_bool( 'print' );
	$f_temp_filter			= gpc_get_bool( 'temporary' );

	# these are all possibly multiple selections for advanced filtering
	$f_show_category = array();
	if ( is_array( gpc_get( 'show_category', null ) ) ) {
		$f_show_category = gpc_get_string_array( 'show_category', 'any' );
	} else {
		$f_show_category = gpc_get_string( 'show_category', 'any' );
		$f_show_category = array( $f_show_category );
	}

	$f_show_severity = array();
	if ( is_array( gpc_get( 'show_severity', null ) ) ) {
		$f_show_severity = gpc_get_string_array( 'show_severity', 'any' );
	} else {
		$f_show_severity = gpc_get_string( 'show_severity', 'any' );
		$f_show_severity = array( $f_show_severity );
	}

	$f_show_status = array();
	if ( is_array( gpc_get( 'show_status', null ) ) ) {
		$f_show_status = gpc_get_string_array( 'show_status', 'any' );
	} else {
		$f_show_status = gpc_get_string( 'show_status', 'any' );
		$f_show_status = array( $f_show_status );
	}

	$f_hide_status = array();
	if ( is_array( gpc_get( 'hide_status', null ) ) ) {
		$f_hide_status = gpc_get_string_array( 'hide_status', 'none' );
	} else {
		$f_hide_status = gpc_get_string( 'hide_status', 'none' );
		$f_hide_status = array( $f_hide_status );
	}

	$f_reporter_id = array();
	if ( is_array( gpc_get( 'reporter_id', null ) ) ) {
		$f_reporter_id = gpc_get_string_array( 'reporter_id', 'any' );
	} else {
		$f_reporter_id = gpc_get_string( 'reporter_id', 'any' );
		$f_reporter_id = array( $f_reporter_id );
	}

	$f_handler_id = array();
	if ( is_array( gpc_get( 'handler_id', null ) ) ) {
		$f_handler_id = gpc_get_string_array( 'handler_id', 'any' );
	} else {
		$f_handler_id = gpc_get_string( 'handler_id', 'any' );
		$f_handler_id = array( $f_handler_id );
	}

	$f_show_resolution = array();
	if ( is_array( gpc_get( 'show_resolution', null ) ) ) {
		$f_show_resolution = gpc_get_string_array( 'show_resolution', 'any' );
	} else {
		$f_show_resolution = gpc_get_string( 'show_resolution', 'any' );
		$f_show_resolution = array( $f_show_resolution );
	}

	$f_show_build = array();
	if ( is_array( gpc_get( 'show_build', null ) ) ) {
		$f_show_build = gpc_get_string_array( 'show_build', 'any' );
	} else {
		$f_show_build = gpc_get_string( 'show_build', 'any' );
		$f_show_build = array( $f_show_build );
	}

	$f_show_version = array();
	if ( is_array( gpc_get( 'show_version', null ) ) ) {
		$f_show_version = gpc_get_string_array( 'show_version', 'any' );
	} else {
		$f_show_version = gpc_get_string( 'show_version', 'any' );
		$f_show_version = array( $f_show_version );
	}

	$f_fixed_in_version = array();
	if ( is_array( gpc_get( 'fixed_in_version', null ) ) ) {
		$f_fixed_in_version = gpc_get_string_array( 'fixed_in_version', 'any' );
	} else {
		$f_fixed_in_version = gpc_get_string( 'fixed_in_version', 'any' );
		$f_fixed_in_version = array( $f_fixed_in_version );
	}

	$f_user_monitor = array();
	if ( is_array( gpc_get( 'user_monitor', null ) ) ) {
		$f_user_monitor = gpc_get_string_array( 'user_monitor', 'any' );
	} else {
		$f_user_monitor = gpc_get_string( 'user_monitor', 'any' );
		$f_user_monitor = array( $f_user_monitor );
	}

	# these are only single values, even when doing advanced filtering
	$f_per_page				= gpc_get_int( 'per_page', -1 );
	$f_highlight_changed	= gpc_get_string( 'highlight_changed', config_get( 'default_show_changed' ) );
	# sort direction
	$f_sort					= gpc_get_string( 'sort', 'last_updated' );
	$f_dir					= gpc_get_string( 'dir', 'DESC' );
	# date values
	$f_start_month			= gpc_get_string( 'start_month', date( 'm' ) );
	$f_end_month			= gpc_get_string( 'end_month', date( 'm' ) );
	$f_start_day			= gpc_get_string( 'start_day', 1 );
	$f_end_day				= gpc_get_string( 'end_day', date( 'd' ) );
	$f_start_year			= gpc_get_string( 'start_year', date( 'Y' ) );
	$f_end_year				= gpc_get_string( 'end_year', date( 'Y' ) );
	$f_search				= gpc_get_string( 'search', '' );
	$f_and_not_assigned		= gpc_get_bool( 'and_not_assigned' );
	$f_do_filter_by_date	= gpc_get_bool( 'do_filter_by_date' );
	$f_view_state			= gpc_get_string( 'view_state', 'any' );

	$t_custom_fields 		= custom_field_get_ids();
	$f_custom_fields_data 	= array();
	if ( is_array( $t_custom_fields ) && ( sizeof( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			if ( is_array( gpc_get( 'custom_field_' . $t_cfid, null ) ) ) {
				$f_custom_fields_data[$t_cfid] = gpc_get_string_array( 'custom_field_' . $t_cfid, 'any' );
			} else {
				$f_custom_fields_data[$t_cfid] = gpc_get_string( 'custom_field_' . $t_cfid, 'any' );
				$f_custom_fields_data[$t_cfid] = array( $f_custom_fields_data[$t_cfid] );
			}
		}
	}

	if ( $f_temp_filter ) {
		$f_type = 1;
	}
	
	if ( $f_and_not_assigned ) {
		$f_and_not_assigned = 'on';
	}

	if ( $f_do_filter_by_date ) {
		$f_do_filter_by_date = 'on';
	}

	if ( $f_type < 0 ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}

	$t_hide_status_default = config_get( 'hide_status_default' );
	
	# show bugs per page
	if ( $f_per_page < 1 ) {
		$f_per_page = config_get( 'default_limit_view' );
	}

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
*/
	# Set new filter values.  These are stored in a cookie
	$t_view_all_cookie_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
	$t_view_all_cookie = filter_db_get_filter( $t_view_all_cookie_id );
	$t_old_setting_arr	= explode( '#', $t_view_all_cookie, 2 );
	
	$t_setting_arr = array();
	
	# If we're not going to reset the cookie, make sure it's valid
	if ( $f_type != 0 ) {
		if ( ( $t_old_setting_arr[0] == 'v1' ) ||
			 ( $t_old_setting_arr[0] == 'v2' ) || 
			 ( $t_old_setting_arr[0] == 'v3' ) || 
			 ( $t_old_setting_arr[0] == 'v4' ) ) {
			gpc_clear_cookie( 'view_all_cookie' );
			print_header_redirect( 'view_all_set.php?type=0' );
		}
		if ( isset( $t_old_setting_arr[1] ) ) {
			$t_setting_arr = unserialize( $t_old_setting_arr[1] );
		}
	
		if ( isset($t_setting_arr['highlight_changed']) ) {
			check_varset( $f_highlight_changed, $t_setting_arr['highlight_changed'] );
		} else {
			check_varset( $f_highlight_changed, config_get( 'default_show_changed' ) );
		}
	}

	$t_cookie_version = config_get( 'cookie_version' );
	$t_default_show_changed = config_get( 'default_show_changed' );

	switch ( $f_type ) {
		# New cookie
		case '0':
				$t_setting_arr = array();

				break;
		# Update filters
		case '1':
				$t_setting_arr['_version'] = $t_cookie_version;
				$t_setting_arr['_view_type'] = $f_view_type;
				$t_setting_arr['show_category'] = $f_show_category;
				$t_setting_arr['show_severity'] = $f_show_severity;
				$t_setting_arr['show_status'] = $f_show_status;
				$t_setting_arr['per_page'] = $f_per_page;
				$t_setting_arr['highlight_changed'] = $f_highlight_changed;
				$t_setting_arr['reporter_id'] = $f_reporter_id;
				$t_setting_arr['handler_id'] = $f_handler_id;
				$t_setting_arr['sort'] = $f_sort;
				$t_setting_arr['dir'] = $f_dir;
				$t_setting_arr['start_month'] = $f_start_month;
				$t_setting_arr['start_day'] = $f_start_day;
				$t_setting_arr['start_year'] = $f_start_year;
				$t_setting_arr['end_month'] = $f_end_month;
				$t_setting_arr['end_day'] = $f_end_day;
				$t_setting_arr['end_year'] = $f_end_year;
				$t_setting_arr['search'] = $f_search;
				$t_setting_arr['hide_status'] = $f_hide_status;
				$t_setting_arr['and_not_assigned'] = $f_and_not_assigned;
				$t_setting_arr['show_resolution'] = $f_show_resolution;
				$t_setting_arr['show_build'] = $f_show_build;
				$t_setting_arr['show_version'] = $f_show_version;
				$t_setting_arr['do_filter_by_date'] = $f_do_filter_by_date;
				$t_setting_arr['fixed_in_version'] = $f_fixed_in_version;
				$t_setting_arr['user_monitor'] = $f_user_monitor;
				$t_setting_arr['view_state'] = $f_view_state;
				$t_setting_arr['custom_fields'] = $f_custom_fields_data;

				break;
		# Set the sort order and direction
		case '2':
				# We only need to set those fields that we are overriding
				$t_setting_arr['sort'] = $f_sort;
				$t_setting_arr['dir'] = $f_dir;

				break;
		# This is when we want to copy another query from the
		# database over the top of our current one
		case '3':
			$t_filter_string = filter_db_get_filter( $f_source_query_id );
			# If we can use the query that we've requested,
			# grab it. We will overwrite the current one at the
			# bottom of this page
			if ( $t_filter_string != null ) {
				$t_cookie_detail = explode( '#', $t_filter_string, 2 );
				$t_setting_arr = unserialize( $t_cookie_detail[1] );

				break;
			}
		# Generalise the filter
		case '4':
				$t_setting_arr['show_category']	= array( "any" );
				$t_setting_arr['reporter_id'] 	= array( "any" );
				$t_setting_arr['handler_id'] 	= array( "any" );
				$t_setting_arr['show_build'] 	= array( "any" );
				$t_setting_arr['show_version'] 	= array( "any" );
				$t_setting_arr['fixed_in_version']	= array( "any" );
				$t_setting_arr['user_monitor'] 		= array( "any" );

				$t_custom_fields 		= custom_field_get_ids();
				$t_custom_fields_data 	= array();
				if ( is_array( $t_custom_fields ) && ( sizeof( $t_custom_fields ) > 0 ) ) {
					foreach( $t_custom_fields as $t_cfid ) {
						$t_custom_fields_data[$t_cfid] =  array( "any" );
					}
				}
				$t_setting_arr['custom_fields'] = $t_custom_fields_data;

				break;
		# Just set the search string value
		case '5':
				$t_setting_arr['search'] = $f_search;

				break;
		# does nothing. catch all case
		default:
				break;
	}

	$t_setting_arr = filter_ensure_valid_filter( $t_setting_arr );

	$t_settings_serialized = serialize( $t_setting_arr );
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
		$t_redirect_url = $t_redirect_url . '?filter=' . $t_settings_serialized;
		# @@@ jlatour: Why this translation, instead of using urlencode?
		$t_trans = array( '"' => '%22', ';' => '%3B', '%' => '%25', ' ' => '%20' );
		$t_redirect_url = strtr( $t_redirect_url, $t_trans );
		html_meta_redirect( $t_redirect_url, 0 );
	} else {
		print_header_redirect( $t_redirect_url );
	}
?>