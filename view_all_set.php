<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: view_all_set.php,v 1.22 2004-03-24 00:30:29 narcissus Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_type					= gpc_get_int( 'type', -1 );
	$f_source_query_id		= gpc_get_int( 'source_query_id', -1 );
	$f_print				= gpc_get_bool( 'print' );

	$f_show_category		= gpc_get_string( 'show_category', '' );
	$f_show_severity		= gpc_get_string( 'show_severity', '' );
	$f_show_status			= gpc_get_string( 'show_status', '' );
	$f_per_page				= gpc_get_int( 'per_page', -1 );
	$f_highlight_changed	= gpc_get_string( 'highlight_changed', config_get( 'default_show_changed' ) );
	$f_hide_closed			= gpc_get_bool( 'hide_closed' );
	$f_hide_resolved			= gpc_get_bool( 'hide_resolved' );
	$f_reporter_id			= gpc_get_string( 'reporter_id', '' );
	$f_handler_id			= gpc_get_string( 'handler_id', '' );
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
	$f_show_resolution		= gpc_get_string( 'show_resolution', 'any' );
	$f_show_build			= gpc_get_string( 'show_build', 'any' );
	$f_show_version			= gpc_get_string( 'show_version', 'any' );
	$f_do_filter_by_date	= gpc_get_bool( 'do_filter_by_date' );

	$t_custom_fields 		= custom_field_get_ids();
	$f_custom_fields_data 	= array();
	if ( is_array( $t_custom_fields ) && ( sizeof( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			$f_custom_fields_data[$t_cfid] =  gpc_get_string( 'custom_field_' . $t_cfid, 'any' );
		}
	}

	if ( $f_hide_closed ) {
		$f_hide_closed = 'on';
	}

	if ( $f_hide_resolved ) {
		$f_hide_resolved = 'on';
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

	if ( ON == config_get( 'hide_closed_default' ) ) {
		$t_hide_closed_default = 'on';
	} else {
		$t_hide_closed_default = '';
	}

	# show bugs per page
	if ( $f_per_page < 0 ) {
		$f_per_page = config_get( 'default_limit_view' );
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
				$t_setting_arr['_version'] = $t_cookie_version;
				$t_setting_arr['show_category'] = "any";
				$t_setting_arr['show_severity'] = "any";
				$t_setting_arr['show_status'] = "any";
				$t_setting_arr['per_page'] = $f_per_page;
				$t_setting_arr['highlight_changed'] = $t_default_show_changed;
				$t_setting_arr['hide_closed'] = $t_hide_closed_default;
				$t_setting_arr['reporter_id'] = "any";
				$t_setting_arr['handler_id'] = "any";
				$t_setting_arr['sort'] = "last_updated";
				$t_setting_arr['dir'] = "DESC";
				$t_setting_arr['start_month'] = $f_start_month;
				$t_setting_arr['start_day'] = $f_start_day;
				$t_setting_arr['start_year'] = $f_start_year;
				$t_setting_arr['end_month'] = $f_end_month;
				$t_setting_arr['end_day'] = $f_end_day;
				$t_setting_arr['end_year'] = $f_end_year;
				$t_setting_arr['search'] = $f_search;
				$t_setting_arr['hide_resolved'] = $f_hide_resolved;
				$t_setting_arr['and_not_assigned'] = $f_and_not_assigned;
				$t_setting_arr['show_resolution'] = $f_show_resolution;
				$t_setting_arr['show_build'] = $f_show_build;
				$t_setting_arr['show_version'] = $f_show_version;
				$t_setting_arr['do_filter_by_date'] = $f_do_filter_by_date;
				$t_setting_arr['custom_fields'] = $f_custom_fields_data;

				break;
		# Update filters
		case '1':
				$t_setting_arr['_version'] = $t_cookie_version;
				$t_setting_arr['show_category'] = $f_show_category;
				$t_setting_arr['show_severity'] = $f_show_severity;
				$t_setting_arr['show_status'] = $f_show_status;
				$t_setting_arr['per_page'] = $f_per_page;
				$t_setting_arr['highlight_changed'] = $t_default_show_changed;
				$t_setting_arr['hide_closed'] = $f_hide_closed;
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
				$t_setting_arr['hide_resolved'] = $f_hide_resolved;
				$t_setting_arr['and_not_assigned'] = $f_and_not_assigned;
				$t_setting_arr['show_resolution'] = $f_show_resolution;
				$t_setting_arr['show_build'] = $f_show_build;
				$t_setting_arr['show_version'] = $f_show_version;
				$t_setting_arr['do_filter_by_date'] = $f_do_filter_by_date;
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
			
		# does nothing. catch all case
		default:
				break;
	}

	$t_settings_serialized = serialize( $t_setting_arr );
	$t_settings_string = $t_cookie_version . '#' . $t_settings_serialized;

	# Store the filter string in the database: its the current filter, so some values won't change
	$t_row_id = filter_db_set_for_current_user( -1, false, '', $t_settings_string );

	# set cookie values
	setcookie( config_get( 'view_all_cookie' ), $t_row_id, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );

	# redirect to print_all or view_all page
	if ( $f_print ) {
		$t_redirect_url = 'print_all_bug_page.php';
	} else {
		$t_redirect_url = 'view_all_bug_page.php';
	}

	print_header_redirect( $t_redirect_url );
?>
