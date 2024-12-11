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
require_api( 'utility_api.php' );

auth_ensure_user_authenticated();

$f_type					= gpc_get_int( 'type', -1 );
$f_source_query_id		= gpc_get_int( 'source_query_id', -1 );
$f_isset_temporary		= gpc_isset( 'temporary' );
$f_make_temporary		= gpc_get_bool( 'temporary' );
$f_isset_new_key		= gpc_isset( 'new' );
$f_force_new_key		= gpc_get_bool( 'new' );
$f_project_id			= gpc_get_int( 'set_project_id', -1 );

# flags to redirect after changing the filter
# 'print' will redirect to print_all_bug_page.php
# 'summary' will redirect to summary_page.php
# otherwise, the default redirect is to view_all_bug_page.php
$f_print				= gpc_get_bool( 'print' );
$f_summary				= gpc_get_bool( 'summary' );

# Get the filter in use
$t_setting_arr = current_user_get_bug_filter();

# If there is an explicit "temporary" parameter true/false, will force the new filter
# to be termporary (true) or persistent (false), according to its value.
# If the parameter is not present, the filter will be kept the same as original.
if( $f_isset_temporary ) {
	# when only changing the temporary status of a filter and no action is specified
	# we assume not to reset current filter
	if( $f_type == -1 ) {
		# use type 2 wich keeps current filter values
		$f_type = FILTER_ACTION_PARSE_ADD;
	}
	$t_temp_filter = $f_make_temporary;
} else {
	$t_temp_filter = filter_is_temporary( $t_setting_arr );
}

if( $f_type == -1 && $f_isset_new_key ) {
	# use an action that keeps current filter values
	$f_type = FILTER_ACTION_PARSE_ADD;
}

if( $f_type == -1 ) {
	print_header_redirect( 'view_all_bug_page.php' );
}

# -1 is a special case stored query: it means we want to reset our filter
if( ( $f_type == FILTER_ACTION_LOAD ) && ( $f_source_query_id == -1 ) ) {
	$f_type = FILTER_ACTION_RESET;
}

# If user can't use persistent filters, force the creation of a temporary filter
if( !filter_user_can_use_persistent( auth_get_current_user_id() ) ) {
	$t_temp_filter = true;
}

$t_previous_temporary_key = filter_get_temporary_key( $t_setting_arr );
$t_force_new_key = $t_temp_filter && $f_force_new_key;

# Clear the source query id.  Since we have entered new filter criteria.
if( isset( $t_setting_arr['_source_query_id'] ) ) {
	unset( $t_setting_arr['_source_query_id'] );
}

switch( $f_type ) {
	# Apply a new empty filter
	case FILTER_ACTION_RESET:
		log_event( LOG_FILTERING, 'view_all_set.php: New filter' );
		$t_setting_arr = array();
		break;

	# Read new filter parameters. (filter_gpc_get reads a new set of parameters)
	# Parameter that are not submitted, will be reset to defaults.
	case FILTER_ACTION_PARSE_NEW:
		log_event( LOG_FILTERING, 'view_all_set.php: Parse a new filter' );
		$t_setting_arr = filter_gpc_get();
		break;

	# Read and update filter parameters (filter_gpc_get is called over current filter)
	# Parameter that are not submitted, will not be modified
	case FILTER_ACTION_PARSE_ADD:
		log_event( LOG_FILTERING, 'view_all_set.php: Parse incremental filter values' );
		$t_setting_arr = filter_gpc_get( $t_setting_arr );
		break;

	# Fetch a stored filter from database
	case FILTER_ACTION_LOAD:
		log_event( LOG_FILTERING, 'view_all_set.php: Load stored filter' );

		$t_setting_arr = filter_get( $f_source_query_id, null );
		if( null === $t_setting_arr ) {
			# couldn't get the filter, if we were trying to use the filter, clear it and reload
			error_proceed_url( 'view_all_set.php?type=' . FILTER_ACTION_RESET );
			trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
			exit;
		} else {
			$t_setting_arr['_source_query_id'] = $f_source_query_id;
		}
		break;

	# Generalise the filter
	case FILTER_ACTION_GENERALIZE:
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

	default:
		# does nothing. catch all case
		log_event( LOG_FILTERING, 'view_all_set.php: default - do nothing' );
		break;
}

$t_setting_arr = filter_ensure_valid_filter( $t_setting_arr );

# If only using a temporary filter, don't store it in the database
if( !$t_temp_filter ) {
	# get project if it was specified
	$t_project_id = ( $f_project_id >= 0 ) ? $f_project_id : null;
	# Store the filter in the database as the current filter for the project
	filter_set_project_filter( $t_setting_arr, $t_project_id );
}

# evaluate redirect
if( $f_print ) {
	$t_redirect_url = 'print_all_bug_page.php';
} elseif( $f_summary ) {
	$t_redirect_url = 'summary_page.php';
} else {
	$t_redirect_url = 'view_all_bug_page.php';
}

if( $t_temp_filter ) {
	# keeping the $t_previous_temporary_key, and using it to save back the filter
	# The key inside the filter array may have been deleted as part of some actions
	# Note, if we reset the key here, a new filter will be created after each filter change.
	# This adds a lot of orphaned filters to session store, but would allow consistency
	# through browser back button, for example.
	if( $t_force_new_key ) {
		$t_previous_temporary_key = null;
		unset( $t_setting_arr['_temporary_key'] );
	}
	$t_temporary_key = filter_temporary_set( $t_setting_arr, $t_previous_temporary_key );
	$t_redirect_url = $t_redirect_url . '?' . filter_get_temporary_key_param( $t_temporary_key );
}
print_header_redirect( $t_redirect_url );
