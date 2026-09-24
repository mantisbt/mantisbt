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
 * @uses custom_field_api.php
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
require_api( 'custom_field_api.php' );
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
gpc_make_array( FILTER_PROPERTY_PROJECTION );
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
gpc_make_array( FILTER_PROPERTY_NOTE_USER_ID );

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
$t_my_filter[FILTER_PROPERTY_PROJECTION] = gpc_get_string_array( FILTER_PROPERTY_PROJECTION, $t_meta_filter_any_array );
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
$t_my_filter[FILTER_PROPERTY_TAG_STRING] = gpc_get_string( FILTER_PROPERTY_TAG_STRING, '' );
$t_my_filter[FILTER_PROPERTY_TAG_SELECT] = gpc_get_int( FILTER_PROPERTY_TAG_SELECT, 0 );
$t_my_filter[FILTER_PROPERTY_NOTE_USER_ID] = gpc_get_string_array( FILTER_PROPERTY_NOTE_USER_ID, $t_meta_filter_any_array );

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

# Relative date descriptors, as encoded by filter_get_url(). An endpoint that
# carries one keeps sliding: filter_resolve_relative_dates() recomputes it at
# query time, and the absolute parts read above are only the snapshot taken when
# the link was built.
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] =
	filter_gpc_get_relative_descriptor( FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE );
$t_my_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] =
	filter_gpc_get_relative_descriptor( FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE] =
	filter_gpc_get_relative_descriptor( FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE );
$t_my_filter[FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE] =
	filter_gpc_get_relative_descriptor( FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE );

# Both endpoints of a built-in date field are in play, so a link that carries one
# of them and not the other is completed from the defaults rather than left as a
# field that mixes a relative endpoint with a fixed one.
foreach( array(
		array( FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE, FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE ),
		array( FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE, FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE ),
	) as $t_pair ) {
	if( empty( $t_my_filter[$t_pair[0]] ) && empty( $t_my_filter[$t_pair[1]] ) ) {
		continue;
	}

	foreach( filter_relative_descriptors_fill(
			array( $t_pair[0] => $t_my_filter[$t_pair[0]], $t_pair[1] => $t_my_filter[$t_pair[1]] )
		) as $t_property => $t_descriptor ) {
		$t_my_filter[$t_property] = $t_descriptor;
	}
}

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
		$t_custom_field_id = mb_substr( $t_var_name, 13 );
		# Only the plain "custom_field_<id>" form carries a value. A date custom
		# field spells its filter out in suffixed parameters instead (_control,
		# _relative, the descriptor parts); those are read below, and keying the
		# filter on them would leave entries no custom field id matches.
		if( ctype_digit( $t_custom_field_id ) ) {
			$t_custom_fields[$t_custom_field_id] = $t_var_value;
		}
	}
}

$t_my_filter['custom_fields'] = $t_custom_fields;

# Date custom fields, which encode the operator and either fixed dates or
# relative descriptors in suffixed parameters. Read through the same helper the
# filter form uses, so a permalink round-trips whichever mode it was built in.
foreach( custom_field_get_ids() as $t_cfid ) {
	if( custom_field_type( $t_cfid ) != CUSTOM_FIELD_TYPE_DATE
		|| !gpc_isset( 'custom_field_' . $t_cfid . '_control' ) ) {
		continue;
	}

	list( $t_values, $t_relative ) = filter_gpc_get_custom_field_date( $t_cfid );
	$t_my_filter['custom_fields'][$t_cfid] = $t_values;
	if( null !== $t_relative ) {
		$t_my_filter[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE][$t_cfid] = $t_relative;
	}
}

# Handle class-based filters defined by plugins
$t_plugin_filters = filter_get_plugin_filters();
foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
    switch( $t_filter_object->type ) {
        case FILTER_TYPE_STRING:
            $t_my_filter[$t_field_name] = gpc_get_string( $t_field_name, '' );
            break;
        case FILTER_TYPE_INT:
            $t_my_filter[$t_field_name] = gpc_get_int( $t_field_name, 0 );
            break;
        case FILTER_TYPE_BOOLEAN:
            $t_my_filter[$t_field_name] = gpc_get_bool( $t_field_name );
            break;
        case FILTER_TYPE_MULTI_STRING:
            $t_my_filter[$t_field_name] = gpc_get_string_array( $t_field_name );
            break;
        case FILTER_TYPE_MULTI_INT:
            $t_my_filter[$t_field_name] = gpc_get_int_array( $t_field_name );
            break;
    }
}

# Must use advanced filter so that the project_id is applied and multiple
# selections are handled.
$t_my_filter['_view_type'] = FILTER_VIEW_TYPE_ADVANCED;

$t_setting_arr = filter_ensure_valid_filter( $t_my_filter );

# A temporary filter is not serialized, so it does not pass the mode check that
# every persisted filter does. A hand-edited URL is the one way to reach here
# with a date field half relative and half fixed, which is a filter the form
# cannot render - reject it as the client error it is rather than show it.
filter_ensure_consistent_relative_mode( $t_setting_arr );

# set the filter for use, for current user
# Note: This will overwrite the filter in use/default for current project and user.
$t_temporary_key = filter_temporary_set( $t_setting_arr );

# redirect to print_all or view_all page
if( $f_print ) {
	$t_redirect_url = 'print_all_bug_page.php';
} else {
	$t_redirect_url = 'view_all_bug_page.php';
}
$t_redirect_url .= '?' . filter_get_temporary_key_param( $t_temporary_key );

print_header_redirect( $t_redirect_url );
