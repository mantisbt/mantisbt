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
 * Set cookie for View all bugs page
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
 * @uses custom_field_api.php
 * @uses error_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses logging_api.php
 * @uses print_api.php
 * @uses tokens_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'error_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'logging_api.php' );
require_api( 'print_api.php' );
require_api( 'tokens_api.php' );
require_api( 'utility_api.php' );

auth_ensure_user_authenticated();

$f_type					= gpc_get_int( 'type', -1 );
$f_source_query_id		= gpc_get_int( 'source_query_id', -1 );
$f_print				= gpc_get_bool( 'print' );
$f_temp_filter			= gpc_get_bool( 'temporary' );

if( $f_temp_filter ) {
	$f_type = 1;
}

if( $f_type < 0 ) {
	print_header_redirect( 'view_all_bug_page.php' );
}

# -1 is a special case stored query: it means we want to reset our filter
if( ( $f_type == 3 ) && ( $f_source_query_id == -1 ) ) {
	$f_type = 0;
}

#   array contents
#   --------------
#	 0: version
#	 1: $f_show_category
#	 2: $f_show_severity
#	 3: $f_show_status
#	 4: $f_per_page
#	 5: $f_highlight_changed
#	 6: $f_hide_closed
#	 7: $f_reporter_id
#	 8: $f_handler_id
#	 9: $f_sort
#	10: $f_dir
#	11: $f_start_month
#	12: $f_start_day
#	13: $f_start_year
#	14: $f_end_month
#	15: $f_end_day
#	16: $f_end_year
#	17: $f_search
#	18: $f_hide_resolved
#	19: $f_show_resolution
#	20: $f_show_build
#	21: $f_show_version
#	22: $f_do_filter_by_date
#	23: $f_custom_field
#	24: $f_relationship_type
# 	25: $f_relationship_bug
# 	26: $f_show_profile

# Set new filter values.  These are stored in a cookie
$t_view_all_cookie_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
$t_view_all_cookie = filter_db_get_filter( $t_view_all_cookie_id );

# process the cookie if it exists, it may be blank in a new install
if( !is_blank( $t_view_all_cookie ) ) {
	$t_setting_arr = filter_deserialize( $t_view_all_cookie );
	if( false === $t_setting_arr ) {
		# couldn't deserialize, if we were trying to use the filter, clear it and reload
		# for ftype = 0, 1, or 3, we are going to re-write the filter anyways
		if( !in_array( $f_type, array( 0, 1, 3 ) ) ) {
			gpc_clear_cookie( 'view_all_cookie' );
			error_proceed_url( 'view_all_set.php?type=0' );
			trigger_error( ERROR_FILTER_TOO_OLD, ERROR );
			exit; # stop here
		}
	} else {
		$t_setting_arr = filter_ensure_valid_filter( $t_setting_arr );
	}
} else {
	# no cookie found, set it
	$f_type = 1;
}

# Clear the source query id.  Since we have entered new filter criteria.
$t_setting_arr['_source_query_id'] = '';
switch( $f_type ) {
	# New cookie
	case '0':
		log_event( LOG_FILTERING, 'view_all_set.php: New cookie' );
		$t_setting_arr = array();
		break;
	# Update filters. (filter_gpc_get reads a new set of parameters)
	case '1':
		$t_setting_arr = filter_gpc_get();
		break;
	# Set the sort order and direction (filter_gpc_get is called over current filter)
	case '2':
		log_event( LOG_FILTERING, 'view_all_set.php: Set the sort order and direction.' );
		$t_setting_arr = filter_gpc_get( $t_setting_arr );

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
		if( false === $t_setting_arr ) {
			# couldn't deserialize, if we were trying to use the filter, clear it and reload
			gpc_clear_cookie( 'view_all_cookie' );
			error_proceed_url( 'view_all_set.php?type=0' );
			trigger_error( ERROR_FILTER_TOO_OLD, ERROR );
			exit; # stop here
		} else {
			$t_setting_arr = filter_ensure_valid_filter( $t_setting_arr );
		}
		# Store the source query id to select the correct filter in the drop down.
		$t_setting_arr['_source_query_id'] = $f_source_query_id;
		break;
	case '4':
		# Generalise the filter
		log_event( LOG_FILTERING, 'view_all_set.php: Generalise the filter' );

		$t_setting_arr[FILTER_PROPERTY_CATEGORY_ID]			= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_REPORTER_ID] 		= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_HANDLER_ID] 			= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_BUILD] 				= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_VERSION] 			= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_PRIORITY]			= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_FIXED_IN_VERSION]	= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_TARGET_VERSION]		= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_MONITOR_USER_ID] 	= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_NOTE_USER_ID]  		= array( META_FILTER_ANY );
		$t_setting_arr[FILTER_PROPERTY_RELATIONSHIP_TYPE] = -1;
		$t_setting_arr[FILTER_PROPERTY_RELATIONSHIP_BUG] 	= 0;

		$t_custom_fields 		= custom_field_get_ids(); # @@@ (thraxisp) This should really be the linked ids, but we don't know the project
		$t_custom_fields_data 	= array();
		if( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
			foreach( $t_custom_fields as $t_cfid ) {
				$t_custom_fields_data[$t_cfid] =  array( META_FILTER_ANY );
			}
		}
		$t_setting_arr['custom_fields'] = $t_custom_fields_data;

		break;
	case '5':
		# Just set the search string value (filter_gpc_get is called over current filter)
		log_event( LOG_FILTERING, 'view_all_set.php: Search Text' );
		$t_setting_arr = filter_gpc_get( $t_setting_arr );
		break;
	case '6':
		# Just set the view_state (simple / advanced) value. (filter_gpc_get is called over current filter)
		log_event( LOG_FILTERING, 'view_all_set.php: View state (simple/advanced)' );
		$t_setting_arr = filter_gpc_get( $t_setting_arr );

		break;
	default:
		# does nothing. catch all case
		log_event( LOG_FILTERING, 'view_all_set.php: default - do nothing' );
		break;
}

$t_setting_arr = filter_ensure_valid_filter( $t_setting_arr );

$t_settings_string = filter_serialize( $t_setting_arr );

# If only using a temporary filter, don't store it in the database
if( !$f_temp_filter ) {
	# Store the filter string in the database: its the current filter, so some values won't change
	$t_project_id = helper_get_current_project();
	$t_project_id = ( $t_project_id * -1 );
	$t_row_id = filter_db_set_for_current_user( $t_project_id, false, '', $t_settings_string );

	# set cookie values
	gpc_set_cookie( config_get( 'view_all_cookie' ), $t_row_id, time()+config_get( 'cookie_time_length' ), config_get( 'cookie_path' ) );
}

# redirect to print_all or view_all page
if( $f_print ) {
	$t_redirect_url = 'print_all_bug_page.php';
} else {
	$t_redirect_url = 'view_all_bug_page.php';
}

if( $f_temp_filter ) {
	$t_token_id = token_set( TOKEN_FILTER, json_encode( $t_setting_arr ) );
	$t_redirect_url = $t_redirect_url . '?filter=' . $t_token_id;
}
print_header_redirect( $t_redirect_url );
