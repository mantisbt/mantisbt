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
 * Filter API
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses collapse_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses date_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses logging_api.php
 * @uses print_api.php
 * @uses profile_api.php
 * @uses project_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'collapse_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'database_api.php' );
require_api( 'date_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'logging_api.php' );
require_api( 'print_api.php' );
require_api( 'profile_api.php' );
require_api( 'project_api.php' );
require_api( 'relationship_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

$g_filter = null;

/**
 * Initialize the filter API with the current filter.
 * @param array $p_filter The filter to set as the current filter.
 */
function filter_init( $p_filter ) {
	global $g_filter;
	$g_filter = $p_filter;
}

/**
 * Allow plugins to define a set of class-based filters, and register/load
 * them here to be used by the rest of filter_api.
 * @return array Mapping of field name to filter object
 */
function filter_get_plugin_filters() {
	static $s_field_array = null;

	if( is_null( $s_field_array ) ) {
		$s_field_array = array();

		$t_all_plugin_filters = event_signal( 'EVENT_FILTER_FIELDS' );
		foreach( $t_all_plugin_filters as $t_plugin => $t_plugin_filters ) {
			foreach( $t_plugin_filters as $t_callback => $t_plugin_filter_array ) {
				if( is_array( $t_plugin_filter_array ) ) {
					foreach( $t_plugin_filter_array as $t_filter_class ) {
						if( class_exists( $t_filter_class ) && is_subclass_of( $t_filter_class, 'MantisFilter' ) ) {
							$t_filter_object = new $t_filter_class();
							$t_field_name = $t_plugin . '_' . $t_filter_object->field;
							$s_field_array[$t_field_name] = $t_filter_object;
						}
					}
				}
			}
		}
	}

	return $s_field_array;
}

/**
 * Get a permanent link for the current active filter.  The results of using these fields by other users
 * can be inconsistent with the original results due to fields like "Myself", "Current Project",
 * and due to access level.
 * @param array $p_custom_filter Array containing a custom filter definition.
 * @return string the search.php?xxxx or an empty string if no criteria applied.
 */
function filter_get_url( array $p_custom_filter ) {
	$t_query = array();

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_PROJECT_ID] ) ) {
		$t_project_id = $p_custom_filter[FILTER_PROPERTY_PROJECT_ID];

		if( count( $t_project_id ) == 1 && $t_project_id[0] == META_FILTER_CURRENT ) {
			$t_project_id = array(
				helper_get_current_project(),
			);
		}

		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_PROJECT_ID, $t_project_id );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_SEARCH] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_SEARCH, $p_custom_filter[FILTER_PROPERTY_SEARCH] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_CATEGORY_ID] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_CATEGORY_ID, $p_custom_filter[FILTER_PROPERTY_CATEGORY_ID] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_REPORTER_ID] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_REPORTER_ID, $p_custom_filter[FILTER_PROPERTY_REPORTER_ID] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_STATUS] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_STATUS, $p_custom_filter[FILTER_PROPERTY_STATUS] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_MONITOR_USER_ID] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_MONITOR_USER_ID, $p_custom_filter[FILTER_PROPERTY_MONITOR_USER_ID] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_HANDLER_ID] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_HANDLER_ID, $p_custom_filter[FILTER_PROPERTY_HANDLER_ID] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_NOTE_USER_ID] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_NOTE_USER_ID, $p_custom_filter[FILTER_PROPERTY_NOTE_USER_ID] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_SEVERITY] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_SEVERITY, $p_custom_filter[FILTER_PROPERTY_SEVERITY] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_RESOLUTION] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_RESOLUTION, $p_custom_filter[FILTER_PROPERTY_RESOLUTION] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_PRIORITY] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_PRIORITY, $p_custom_filter[FILTER_PROPERTY_PRIORITY] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_VIEW_STATE] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_VIEW_STATE, $p_custom_filter[FILTER_PROPERTY_VIEW_STATE] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_STICKY] ) ) {
		$t_query[] = filter_encode_field_and_value(
			FILTER_PROPERTY_STICKY,
			$p_custom_filter[FILTER_PROPERTY_STICKY] ? 'on' : 'off' );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_VERSION] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_VERSION, $p_custom_filter[FILTER_PROPERTY_VERSION] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_BUILD] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_BUILD, $p_custom_filter[FILTER_PROPERTY_BUILD] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_FIXED_IN_VERSION] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_FIXED_IN_VERSION, $p_custom_filter[FILTER_PROPERTY_FIXED_IN_VERSION] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_TARGET_VERSION] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_TARGET_VERSION, $p_custom_filter[FILTER_PROPERTY_TARGET_VERSION] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_SORT_FIELD_NAME] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_SORT_FIELD_NAME, $p_custom_filter[FILTER_PROPERTY_SORT_FIELD_NAME] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_SORT_DIRECTION] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_SORT_DIRECTION, $p_custom_filter[FILTER_PROPERTY_SORT_DIRECTION] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] ) ) {
		if( $p_custom_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] != config_get( 'default_limit_view' ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_ISSUES_PER_PAGE, $p_custom_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] );
		}
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] ) ) {
		if( $p_custom_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] != config_get( 'default_show_changed' ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_HIGHLIGHT_CHANGED, $p_custom_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] );
		}
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_HIDE_STATUS] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_HIDE_STATUS, $p_custom_filter[FILTER_PROPERTY_HIDE_STATUS] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_FILTER_BY_DATE] ) ) {
		$t_query[] = filter_encode_field_and_value(
			FILTER_PROPERTY_FILTER_BY_DATE,
			$p_custom_filter[FILTER_PROPERTY_FILTER_BY_DATE] ? 'on' : 'off' );

		# The start and end dates are only applicable if filter by date is set.
		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_START_DAY] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_START_DAY, $p_custom_filter[FILTER_PROPERTY_START_DAY] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_END_DAY] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_END_DAY, $p_custom_filter[FILTER_PROPERTY_END_DAY] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_START_MONTH] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_START_MONTH, $p_custom_filter[FILTER_PROPERTY_START_MONTH] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_END_MONTH] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_END_MONTH, $p_custom_filter[FILTER_PROPERTY_END_MONTH] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_START_YEAR] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_START_YEAR, $p_custom_filter[FILTER_PROPERTY_START_YEAR] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_END_YEAR] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_END_YEAR, $p_custom_filter[FILTER_PROPERTY_END_YEAR] );
		}
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] ) ) {
		if( $p_custom_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] != -1 ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_RELATIONSHIP_TYPE, $p_custom_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] );
		}
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_RELATIONSHIP_BUG, $p_custom_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_PLATFORM] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_PLATFORM, $p_custom_filter[FILTER_PROPERTY_PLATFORM] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_OS] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_OS, $p_custom_filter[FILTER_PROPERTY_OS] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_OS_BUILD] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_OS_BUILD, $p_custom_filter[FILTER_PROPERTY_OS_BUILD] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_TAG_STRING] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_TAG_STRING, $p_custom_filter[FILTER_PROPERTY_TAG_STRING] );
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
		$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_TAG_SELECT, $p_custom_filter[FILTER_PROPERTY_TAG_SELECT] );
	}

	$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_MATCH_TYPE, $p_custom_filter[FILTER_PROPERTY_MATCH_TYPE] );

	if( isset( $p_custom_filter['custom_fields'] ) ) {
		foreach( $p_custom_filter['custom_fields'] as $t_custom_field_id => $t_custom_field_values ) {
			if( !filter_field_is_any( $t_custom_field_values ) ) {
				$t_query[] = filter_encode_field_and_value( 'custom_field_' . $t_custom_field_id, $t_custom_field_values );
			}
		}
	}

	# Allow plugins to add filter fields
	$t_plugin_filter_array = filter_get_plugin_filters();
	foreach( $t_plugin_filter_array as $t_field_name => $t_filter_object ) {
		if( !filter_field_is_any( $p_custom_filter[$t_field_name] ) ) {
			$t_query[] = filter_encode_field_and_value( $t_field_name, $p_custom_filter[$t_field_name], $t_filter_object->type );
		}
	}

	if( count( $t_query ) > 0 ) {
		$t_query_str = implode( $t_query, '&' );
		$t_url = config_get( 'path' ) . 'search.php?' . $t_query_str;
	} else {
		$t_url = '';
	}

	return $t_url;
}

/**
 * Encodes a field and it's value for the filter URL.  This handles the URL encoding and arrays.
 * @param string  $p_field_name  The field name.
 * @param string  $p_field_value The field value (can be an array).
 * @param integer $p_field_type  Field Type e.g. FILTER_TYPE_MULTI_STRING.
 * @return string url encoded string
 */
function filter_encode_field_and_value( $p_field_name, $p_field_value, $p_field_type = null ) {
	$t_query_array = array();
	if( is_array( $p_field_value ) ) {
		$t_count = count( $p_field_value );
		if( $t_count > 1 || $p_field_type == FILTER_TYPE_MULTI_STRING || $p_field_type == FILTER_TYPE_MULTI_INT ) {
			foreach( $p_field_value as $t_value ) {
				$t_query_array[] = urlencode( $p_field_name . '[]' ) . '=' . urlencode( $t_value );
			}
		} else if( $t_count == 1 ) {
			$t_query_array[] = urlencode( $p_field_name ) . '=' . urlencode( $p_field_value[0] );
		}
	} else {
		$t_query_array[] = urlencode( $p_field_name ) . '=' . urlencode( $p_field_value );
	}

	return implode( $t_query_array, '&' );
}

/**
 * Checks the supplied value to see if it is an ANY value.
 * @param string $p_field_value The value to check.
 * @return boolean true for "ANY" values and false for others.  "ANY" means filter criteria not active.
 */
function filter_field_is_any( $p_field_value ) {
	if( is_array( $p_field_value ) ) {
		if( count( $p_field_value ) == 0 ) {
			return true;
		}

		foreach( $p_field_value as $t_value ) {
			if( ( META_FILTER_ANY == $t_value ) && ( is_numeric( $t_value ) ) ) {
				return true;
			}
		}
	} else {
		if( is_string( $p_field_value ) && is_blank( $p_field_value ) ) {
			return true;
		}

		if( is_bool( $p_field_value ) && !$p_field_value ) {
			return true;
		}

		if( ( META_FILTER_ANY == $p_field_value ) && ( is_numeric( $p_field_value ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Checks the supplied value to see if it is a NONE value.
 * @param string $p_field_value The value to check.
 * @return boolean true for "NONE" values and false for others.
 * @todo is a check for these necessary?  if( ( $t_filter_value === 'none' ) || ( $t_filter_value === '[none]' ) )
 */
function filter_field_is_none( $p_field_value ) {
	if( is_array( $p_field_value ) ) {
		foreach( $p_field_value as $t_value ) {
			if( ( META_FILTER_NONE == $t_value ) && ( is_numeric( $t_value ) ) ) {
				return true;
			}
		}
	} else {
		if( is_string( $p_field_value ) && is_blank( $p_field_value ) ) {
			return false;
		}

		if( ( META_FILTER_NONE == $p_field_value ) && ( is_numeric( $p_field_value ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 *  Checks the supplied value to see if it is a MYSELF value.
 * @param string $p_field_value The value to check.
 * @return boolean true for "MYSELF" values and false for others.
 */
function filter_field_is_myself( $p_field_value ) {
	return( META_FILTER_MYSELF == $p_field_value ? true : false );
}

/**
 * Filter per page
 * @param array   $p_filter   Filter.
 * @param integer $p_count    Count.
 * @param integer $p_per_page Per page.
 * @return integer
 */
function filter_per_page( array $p_filter, $p_count, $p_per_page ) {
	$p_per_page = (( null == $p_per_page ) ? (int)$p_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] : $p_per_page );
	$p_per_page = (( 0 == $p_per_page || -1 == $p_per_page ) ? $p_count : $p_per_page );

	return (int)abs( $p_per_page );
}

/**
 *  Use $p_count and $p_per_page to determine how many pages to split this list up into.
 *  For the sake of consistency have at least one page, even if it is empty.
 * @param integer $p_count    Count.
 * @param integer $p_per_page Per page.
 * @return integer page count
 */
function filter_page_count( $p_count, $p_per_page ) {
	$t_page_count = ceil( $p_count / $p_per_page );
	if( $t_page_count < 1 ) {
		$t_page_count = 1;
	}
	return $t_page_count;
}

/**
 * Checks to make sure $p_page_number isn't past the last page.
 * and that $p_page_number isn't before the first page
 * @param integer $p_page_number Page number.
 * @param integer $p_page_count  Page count.
 * @return integer
 */
function filter_valid_page_number( $p_page_number, $p_page_count ) {
	if( $p_page_number > $p_page_count ) {
		$p_page_number = $p_page_count;
	}

	if( $p_page_number < 1 ) {
		$p_page_number = 1;
	}
	return $p_page_number;
}

/**
 * Figure out the offset into the db query, offset is which record to start querying from
 * @param integer $p_page_number Page number.
 * @param integer $p_per_page    Per page.
 * @return integer
 */
function filter_offset( $p_page_number, $p_per_page ) {
	return(( (int)$p_page_number -1 ) * (int)$p_per_page );
}

/**
 * Make sure that our filters are entirely correct and complete (it is possible that they are not).
 * We need to do this to cover cases where we don't have complete control over the filters given.
 * @param array $p_filter_arr A Filter definition.
 * @return array
 * @todo function needs to be abstracted
 */
function filter_ensure_valid_filter( array $p_filter_arr ) {
	# extend current filter to add information passed via POST
	if( !isset( $p_filter_arr['_version'] ) ) {
		$p_filter_arr['_version'] = FILTER_VERSION;
	}
	$t_cookie_vers = (int)substr( $p_filter_arr['_version'], 1 );
	if( substr( FILTER_VERSION, 1 ) > $t_cookie_vers ) {
		# if the version is old, update it
		$p_filter_arr['_version'] = FILTER_VERSION;
	}

	# Filter view type - ensure it's either 'simple' or 'advanced' (prevent XSS)
	if( !isset( $p_filter_arr['_view_type'] ) ) {
		$p_filter_arr['_view_type'] = gpc_get_string( 'view_type', 'simple' );
	}
	if( $p_filter_arr['_view_type'] !== 'advanced' ) {
		$p_filter_arr['_view_type'] = 'simple';
	}

	if( !isset( $p_filter_arr[FILTER_PROPERTY_ISSUES_PER_PAGE] ) ) {
		$p_filter_arr[FILTER_PROPERTY_ISSUES_PER_PAGE] = gpc_get_int( FILTER_PROPERTY_ISSUES_PER_PAGE, config_get( 'default_limit_view' ) );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_HIGHLIGHT_CHANGED] ) ) {
		$p_filter_arr[FILTER_PROPERTY_HIGHLIGHT_CHANGED] = config_get( 'default_show_changed' );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_STICKY] ) ) {
		$p_filter_arr[FILTER_PROPERTY_STICKY] = gpc_string_to_bool( config_get( 'show_sticky_issues' ) );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_SORT_FIELD_NAME] ) ) {
		$p_filter_arr[FILTER_PROPERTY_SORT_FIELD_NAME] = 'last_updated';
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_SORT_DIRECTION] ) ) {
		$p_filter_arr[FILTER_PROPERTY_SORT_DIRECTION] = 'DESC';
	}

	if( !isset( $p_filter_arr[FILTER_PROPERTY_PLATFORM] ) ) {
		$p_filter_arr[FILTER_PROPERTY_PLATFORM] = array(
			0 => (string)META_FILTER_ANY,
		);
	}

	if( !isset( $p_filter_arr[FILTER_PROPERTY_OS] ) ) {
		$p_filter_arr[FILTER_PROPERTY_OS] = array(
			0 => (string)META_FILTER_ANY,
		);
	}

	if( !isset( $p_filter_arr[FILTER_PROPERTY_OS_BUILD] ) ) {
		$p_filter_arr[FILTER_PROPERTY_OS_BUILD] = array(
			0 => (string)META_FILTER_ANY,
		);
	}

	if( !isset( $p_filter_arr[FILTER_PROPERTY_PROJECT_ID] ) ) {
		$p_filter_arr[FILTER_PROPERTY_PROJECT_ID] = array(
			0 => META_FILTER_CURRENT,
		);
	}

	if( !isset( $p_filter_arr[FILTER_PROPERTY_START_MONTH] ) ) {
		$p_filter_arr[FILTER_PROPERTY_START_MONTH] = gpc_get_string( FILTER_PROPERTY_START_MONTH, date( 'm' ) );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_START_DAY] ) ) {
		$p_filter_arr[FILTER_PROPERTY_START_DAY] = gpc_get_string( FILTER_PROPERTY_START_DAY, 1 );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_START_YEAR] ) ) {
		$p_filter_arr[FILTER_PROPERTY_START_YEAR] = gpc_get_string( FILTER_PROPERTY_START_YEAR, date( 'Y' ) );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_END_MONTH] ) ) {
		$p_filter_arr[FILTER_PROPERTY_END_MONTH] = gpc_get_string( FILTER_PROPERTY_END_MONTH, date( 'm' ) );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_END_DAY] ) ) {
		$p_filter_arr[FILTER_PROPERTY_END_DAY] = gpc_get_string( FILTER_PROPERTY_END_DAY, date( 'd' ) );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_END_YEAR] ) ) {
		$p_filter_arr[FILTER_PROPERTY_END_YEAR] = gpc_get_string( FILTER_PROPERTY_END_YEAR, date( 'Y' ) );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_SEARCH] ) ) {
		$p_filter_arr[FILTER_PROPERTY_SEARCH] = '';
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_FILTER_BY_DATE] ) ) {
		$p_filter_arr[FILTER_PROPERTY_FILTER_BY_DATE] = gpc_get_bool( FILTER_PROPERTY_FILTER_BY_DATE, false );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_VIEW_STATE] ) ) {
		$p_filter_arr[FILTER_PROPERTY_VIEW_STATE] = gpc_get( FILTER_PROPERTY_VIEW_STATE, META_FILTER_ANY );
	} else if( filter_field_is_any( $p_filter_arr[FILTER_PROPERTY_VIEW_STATE] ) ) {
		$p_filter_arr[FILTER_PROPERTY_VIEW_STATE] = META_FILTER_ANY;
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_TYPE] ) ) {
		$p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_TYPE] = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_TYPE, -1 );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_BUG] ) ) {
		$p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_BUG] = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_BUG, 0 );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_TAG_STRING] ) ) {
		$p_filter_arr[FILTER_PROPERTY_TAG_STRING] = gpc_get_string( FILTER_PROPERTY_TAG_STRING, '' );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_TAG_SELECT] ) ) {
		$p_filter_arr[FILTER_PROPERTY_TAG_SELECT] = gpc_get_string( FILTER_PROPERTY_TAG_SELECT, '' );
	}
	if( !isset( $p_filter_arr[FILTER_PROPERTY_MATCH_TYPE] ) ) {
		$p_filter_arr[FILTER_PROPERTY_MATCH_TYPE] = gpc_get_int( FILTER_PROPERTY_MATCH_TYPE, FILTER_MATCH_ALL );
	} else {
		settype( $p_filter_arr[FILTER_PROPERTY_MATCH_TYPE], 'int' );
	}

	# initialize plugin filters
	$t_plugin_filters = filter_get_plugin_filters();
	foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
		if( !isset( $p_filter_arr[$t_field_name] ) ) {
			switch( $t_filter_object->type ) {
				case FILTER_TYPE_STRING:
					$p_filter_arr[$t_field_name] = gpc_get_string( $t_field_name, $t_filter_object->default );
					break;

				case FILTER_TYPE_INT:
					$p_filter_arr[$t_field_name] = gpc_get_int( $t_field_name, (int)$t_filter_object->default );
					break;

				case FILTER_TYPE_BOOLEAN:
					$p_filter_arr[$t_field_name] = gpc_get_bool( $t_field_name, (bool)$t_filter_object->default );
					break;

				case FILTER_TYPE_MULTI_STRING:
					$p_filter_arr[$t_field_name] = gpc_get_string_array( $t_field_name, array( 0 => (string)META_FILTER_ANY ) );
					break;

				case FILTER_TYPE_MULTI_INT:
					$p_filter_arr[$t_field_name] = gpc_get_int_array( $t_field_name, array( 0 => META_FILTER_ANY ) );
					break;

				default:
					$p_filter_arr[$t_field_name] = (string)META_FILTER_ANY;
			}
		}

		if( ! $t_filter_object->validate( $p_filter_arr[$t_field_name] ) ) {
			$p_filter_arr[$t_field_name] = $t_filter_object->default;
		}
	}

	$t_custom_fields = custom_field_get_ids();

	# @@@ (thraxisp) This should really be the linked ids, but we don't know the project
	$f_custom_fields_data = array();
	if( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			if( is_array( gpc_get( 'custom_field_' . $t_cfid, null ) ) ) {
				$f_custom_fields_data[$t_cfid] = gpc_get_string_array( 'custom_field_' . $t_cfid, array( META_FILTER_ANY ) );
			} else {
				$f_custom_fields_data[$t_cfid] = gpc_get_string( 'custom_field_' . $t_cfid, (string)META_FILTER_ANY );
				$f_custom_fields_data[$t_cfid] = array(
					$f_custom_fields_data[$t_cfid],
				);
			}
		}
	}

	# validate sorting
	$t_fields = helper_get_columns_to_view();
	$t_n_fields = count( $t_fields );
	for( $i = 0;$i < $t_n_fields;$i++ ) {
		if( isset( $t_fields[$i] ) && in_array( $t_fields[$i], array( 'selection', 'edit', 'bugnotes_count', 'attachment_count' ) ) ) {
			unset( $t_fields[$i] );
		}
	}

	# Make sure array is no longer than 2 elements
	$t_sort_fields = explode( ',', $p_filter_arr['sort'] );
	if( count( $t_sort_fields ) > 2 ) {
		$t_sort_fields = array_slice( $t_sort_fields, 0, 2 );
	}

	# Make sure array is no longer than 2 elements
	$t_dir_fields = explode( ',', $p_filter_arr['dir'] );
	if( count( $t_dir_fields ) > 2 ) {
		$t_dir_fields = array_slice( $t_dir_fields, 0, 2 );
	}

	# Validate the max of two segments for $t_sort_fields and $t_dir_fields
	for( $i = 0;$i < 2;$i++ ) {
		if( isset( $t_sort_fields[$i] ) ) {
			$t_drop = false;
			$t_sort = $t_sort_fields[$i];
			if( strpos( $t_sort, 'custom_' ) === 0 ) {
				if( false === custom_field_get_id_from_name( utf8_substr( $t_sort, utf8_strlen( 'custom_' ) ) ) ) {
					$t_drop = true;
				}
			} else {
				if( !in_array( $t_sort, $t_fields ) ) {
					$t_drop = true;
				}
			}
			if( !in_array( $t_dir_fields[$i], array( 'ASC', 'DESC' ) ) ) {
				$t_drop = true;
			}
			if( $t_drop ) {
				unset( $t_sort_fields[$i] );
				unset( $t_dir_fields[$i] );
			}
		}
	}
	if( count( $t_sort_fields ) > 0 ) {
		$p_filter_arr['sort'] = implode( ',', $t_sort_fields );
		$p_filter_arr['dir'] = implode( ',', $t_dir_fields );
	} else {
		$p_filter_arr['sort'] = 'last_updated';
		$p_filter_arr['dir'] = 'DESC';
	}

	# validate or filter junk from other fields
	$t_multi_select_list = array(
		FILTER_PROPERTY_CATEGORY_ID => 'string',
		FILTER_PROPERTY_SEVERITY => 'int',
		FILTER_PROPERTY_STATUS => 'int',
		FILTER_PROPERTY_REPORTER_ID => 'int',
		FILTER_PROPERTY_HANDLER_ID => 'int',
		FILTER_PROPERTY_NOTE_USER_ID => 'int',
		FILTER_PROPERTY_RESOLUTION => 'int',
		FILTER_PROPERTY_PRIORITY => 'int',
		FILTER_PROPERTY_BUILD => 'string',
		FILTER_PROPERTY_VERSION => 'string',
		FILTER_PROPERTY_HIDE_STATUS => 'int',
		FILTER_PROPERTY_FIXED_IN_VERSION => 'string',
		FILTER_PROPERTY_TARGET_VERSION => 'string',
		FILTER_PROPERTY_MONITOR_USER_ID => 'int',
		FILTER_PROPERTY_PROFILE_ID => 'int',
	);
	foreach( $t_multi_select_list as $t_multi_field_name => $t_multi_field_type ) {
		if( !isset( $p_filter_arr[$t_multi_field_name] ) ) {
			if( FILTER_PROPERTY_HIDE_STATUS == $t_multi_field_name ) {
				$p_filter_arr[$t_multi_field_name] = array(
					config_get( 'hide_status_default' ),
				);
			} else if( 'custom_fields' == $t_multi_field_name ) {
				$p_filter_arr[$t_multi_field_name] = array(
					$f_custom_fields_data,
				);
			} else {
				$t_val = META_FILTER_ANY;
				# Ensure the filter property has the right type - see #20087
				settype( $t_val, $t_multi_field_type );
				$p_filter_arr[$t_multi_field_name] = array( $t_val );
			}
		} else {
			if( !is_array( $p_filter_arr[$t_multi_field_name] ) ) {
				$p_filter_arr[$t_multi_field_name] = array(
					$p_filter_arr[$t_multi_field_name],
				);
			}
			$t_checked_array = array();
			foreach( $p_filter_arr[$t_multi_field_name] as $t_filter_value ) {
				$t_filter_value = stripslashes( $t_filter_value );
				if( ( $t_filter_value === 'any' ) || ( $t_filter_value === '[any]' ) ) {
					$t_filter_value = META_FILTER_ANY;
				}
				if( ( $t_filter_value === 'none' ) || ( $t_filter_value === '[none]' ) ) {
					$t_filter_value = META_FILTER_NONE;
				}
				# Ensure the filter property has the right type - see #20087
				switch( $t_multi_field_type ) {
					case 'string' :
					case 'int' :
						settype( $t_filter_value, $t_multi_field_type );
						break;
				}
				$t_checked_array[] = $t_filter_value;
			}
			$p_filter_arr[$t_multi_field_name] = $t_checked_array;
		}
	}

	if( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			if( !isset( $p_filter_arr['custom_fields'][$t_cfid] ) ) {
				$p_filter_arr['custom_fields'][$t_cfid] = array(
					META_FILTER_ANY,
				);
			} else {
				if( !is_array( $p_filter_arr['custom_fields'][$t_cfid] ) ) {
					$p_filter_arr['custom_fields'][$t_cfid] = array(
						$p_filter_arr['custom_fields'][$t_cfid],
					);
				}
				$t_checked_array = array();
				foreach( $p_filter_arr['custom_fields'][$t_cfid] as $t_filter_value ) {
					$t_filter_value = stripslashes( $t_filter_value );
					if( ( $t_filter_value === 'any' ) || ( $t_filter_value === '[any]' ) ) {
						$t_filter_value = META_FILTER_ANY;
					}
					$t_checked_array[] = $t_filter_value;
				}
				$p_filter_arr['custom_fields'][$t_cfid] = $t_checked_array;
			}
		}
	}

	# all of our filter values are now guaranteed to be there, and correct.
	return $p_filter_arr;
}

/**
 *  Get the standard filter that is to be used when no filter was previously saved.
 *  When creating specific filters, this can be used as a basis for the filter, where
 *  specific entries can be overridden.
 * @return mixed
 */
function filter_get_default() {
	$t_hide_status_default = config_get( 'hide_status_default' );
	$t_default_show_changed = config_get( 'default_show_changed' );

	$t_filter = array(
		FILTER_PROPERTY_CATEGORY_ID => array(
			'0' => (string)META_FILTER_ANY,
		),
		FILTER_PROPERTY_SEVERITY => array(
			'0' => META_FILTER_ANY,
		),
		FILTER_PROPERTY_STATUS => array(
			'0' => META_FILTER_ANY,
		),
		FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
		FILTER_PROPERTY_REPORTER_ID => array(
			'0' => META_FILTER_ANY,
		),
		FILTER_PROPERTY_HANDLER_ID => array(
			'0' => META_FILTER_ANY,
		),
		FILTER_PROPERTY_PROJECT_ID => array(
			'0' => META_FILTER_CURRENT,
		),
		FILTER_PROPERTY_RESOLUTION => array(
			'0' => META_FILTER_ANY,
		),
		FILTER_PROPERTY_BUILD => array(
			'0' => (string)META_FILTER_ANY,
		),
		FILTER_PROPERTY_VERSION => array(
			'0' => (string)META_FILTER_ANY,
		),
		FILTER_PROPERTY_HIDE_STATUS => array(
			'0' => $t_hide_status_default,
		),
		FILTER_PROPERTY_MONITOR_USER_ID => array(
			'0' => META_FILTER_ANY,
		),
		FILTER_PROPERTY_SORT_FIELD_NAME => 'last_updated',
		FILTER_PROPERTY_SORT_DIRECTION => 'DESC',
		FILTER_PROPERTY_ISSUES_PER_PAGE => config_get( 'default_limit_view' ),
		FILTER_PROPERTY_MATCH_TYPE => FILTER_MATCH_ALL
	);

	return filter_ensure_valid_filter( $t_filter );
}

/**
 * Deserialize filter string
 * @param string $p_serialized_filter Serialized filter string.
 * @return mixed $t_filter array
 * @see filter_ensure_valid_filter
 */
function filter_deserialize( $p_serialized_filter ) {
	if( is_blank( $p_serialized_filter ) ) {
		return false;
	}

	# check to see if new cookie is needed
	$t_setting_arr = explode( '#', $p_serialized_filter, 2 );
	if( ( $t_setting_arr[0] == 'v1' ) || ( $t_setting_arr[0] == 'v2' ) || ( $t_setting_arr[0] == 'v3' ) || ( $t_setting_arr[0] == 'v4' ) ) {
		# these versions can't be salvaged, they are too old to update
		return false;
	}

	# We shouldn't need to do this anymore, as filters from v5 onwards should cope with changing
	# filter indices dynamically
	$t_filter_array = array();
	if( isset( $t_setting_arr[1] ) ) {
		$t_filter_array = json_decode( $t_setting_arr[1], true );
	} else {
		return false;
	}
	if( $t_filter_array['_version'] != FILTER_VERSION ) {
		# if the version is not new enough, update it using defaults
		return filter_ensure_valid_filter( $t_filter_array );
	}

	return $t_filter_array;
}

/**
 * Check if the filter cookie exists and is of the correct version.
 * @return boolean
 */
function filter_is_cookie_valid() {
	$t_view_all_cookie_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
	$t_view_all_cookie = filter_db_get_filter( $t_view_all_cookie_id );

	# check to see if the cookie does not exist
	if( is_blank( $t_view_all_cookie ) ) {
		return false;
	}

	# check to see if new cookie is needed
	$t_setting_arr = explode( '#', $t_view_all_cookie, 2 );
	if( ( $t_setting_arr[0] == 'v1' ) || ( $t_setting_arr[0] == 'v2' ) || ( $t_setting_arr[0] == 'v3' ) || ( $t_setting_arr[0] == 'v4' ) ) {
		return false;
	}

	# We shouldn't need to do this anymore, as filters from v5 onwards should cope with changing
	# filter indices dynamically
	$t_filter_cookie_arr = array();
	if( isset( $t_setting_arr[1] ) ) {
		$t_filter_cookie_arr = json_decode( $t_setting_arr[1], true );
	} else {
		return false;
	}
	if( $t_filter_cookie_arr['_version'] != FILTER_VERSION ) {
		return false;
	}

	return true;
}

/**
 * Get the array fields specified by $p_filter_id
 * using the cached row if it's available
 * @param integer $p_filter_id A filter identifier to look up in the database.
 * @return array a filter row
 */
function filter_get_row( $p_filter_id ) {
	return filter_cache_row( $p_filter_id );
}

/**
 * Get the value of the filter field specified by filter id and field name
 * @param integer $p_filter_id  A filter identifier to look up in the database.
 * @param string  $p_field_name Name of the filter field to retrieve.
 * @return string
 */
function filter_get_field( $p_filter_id, $p_field_name ) {
	$t_row = filter_get_row( $p_filter_id );

	if( isset( $t_row[$p_field_name] ) ) {
		return $t_row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * Add sort parameters to the query clauses
 * @param array   &$p_filter       Filter to sort.
 * @param boolean $p_show_sticky   Whether to show sticky items.
 * @param array   $p_query_clauses Array of query clauses.
 * @return array $p_query_clauses
 */
function filter_get_query_sort_data( array &$p_filter, $p_show_sticky, array $p_query_clauses ) {
	# if sort is blank then default the sort and direction.  This is to fix the
	# symptoms of #3953.  Note that even if the main problem is fixed, we may
	# have to keep this code for a while to handle filters saved with this blank field.
	if( is_blank( $p_filter[FILTER_PROPERTY_SORT_FIELD_NAME] ) ) {
		$p_filter[FILTER_PROPERTY_SORT_FIELD_NAME] = 'last_updated';
		$p_filter[FILTER_PROPERTY_SORT_DIRECTION] = 'DESC';
	}

	$p_query_clauses['order'] = array();
	$t_sort_fields = explode( ',', $p_filter[FILTER_PROPERTY_SORT_FIELD_NAME] );
	$t_dir_fields = explode( ',', $p_filter[FILTER_PROPERTY_SORT_DIRECTION] );

	$t_plugin_columns = columns_get_plugin_columns();

	if( gpc_string_to_bool( $p_filter[FILTER_PROPERTY_STICKY] ) && ( null !== $p_show_sticky ) ) {
		$p_query_clauses['order'][] = '{bug}.sticky DESC';
	}

	$t_count = count( $t_sort_fields );
	for( $i = 0;$i < $t_count;$i++ ) {
		$c_sort = $t_sort_fields[$i];
		$c_dir = 'DESC' == $t_dir_fields[$i] ? 'DESC' : 'ASC';

		if( !in_array( $t_sort_fields[$i], array_slice( $t_sort_fields, $i + 1 ) ) ) {
			# if sorting by a custom field
			if( strpos( $c_sort, 'custom_' ) === 0 ) {
				$t_custom_field = utf8_substr( $c_sort, utf8_strlen( 'custom_' ) );
				$t_custom_field_id = custom_field_get_id_from_name( $t_custom_field );
				$t_def = custom_field_get_definition( $t_custom_field_id );
				$t_value_field = ( $t_def['type'] == CUSTOM_FIELD_TYPE_TEXTAREA ? 'text' : 'value' );
				$c_cf_alias = 'custom_field_' . $t_custom_field_id;

				# Distinguish filter table aliases from sort table aliases (see #19670)
				$t_cf_table_alias = 'cf_sort_' . $t_custom_field_id;
				$t_cf_select = $t_cf_table_alias . '.' . $t_value_field . ' ' . $c_cf_alias;

				# check to be sure this field wasn't already added to the query.
				if( !in_array( $t_cf_select, $p_query_clauses['select'] ) ) {
					$p_query_clauses['select'][] = $t_cf_select;
					$p_query_clauses['join'][] = 'LEFT JOIN {custom_field_string} ' . $t_cf_table_alias . ' ON
												{bug}.id = ' . $t_cf_table_alias . '.bug_id AND ' . $t_cf_table_alias . '.field_id = ' . $t_custom_field_id;
				}

				$p_query_clauses['order'][] = $c_cf_alias . ' ' . $c_dir;

			# if sorting by plugin columns
			} else if( isset( $t_plugin_columns[$t_sort_fields[$i]] ) ) {
				$t_column_object = $t_plugin_columns[$t_sort_fields[$i]];

				if( $t_column_object->sortable ) {
					$t_clauses = $t_column_object->sortquery( $c_dir );

					if( is_array( $t_clauses ) ) {
						if( isset( $t_clauses['join'] ) ) {
							$p_query_clauses['join'][] = $t_clauses['join'];
						}
						if( isset( $t_clauses['order'] ) ) {
							$p_query_clauses['order'][] = $t_clauses['order'];
						}
					}
				}

			# standard column
			} else {
				$t_sort_col = '{bug}.' . $c_sort;

				# when sorting by due_date, always display undefined dates last
				if( 'due_date' == $c_sort && 'ASC' == $c_dir ) {
					$t_sort_due_date = $t_sort_col . ' = 1';
					$p_query_clauses['select'][] = $t_sort_due_date;
					$t_sort_col = $t_sort_due_date . ', ' . $t_sort_col;
				}

				$p_query_clauses['order'][] = $t_sort_col . ' ' .$c_dir;
			}
		}
	}

	# add basic sorting if necessary
	if( !in_array( 'last_updated', $t_sort_fields ) ) {
		$p_query_clauses['order'][] = '{bug}.last_updated DESC';
	}
	if( !in_array( 'date_submitted', $t_sort_fields ) ) {
		$p_query_clauses['order'][] = '{bug}.date_submitted DESC';
	}

	return $p_query_clauses;
}

/**
 * Remove any duplicate values in certain elements of query_clauses
 * Do not loop over query clauses as some keys may contain valid duplicate values.
 * We basically want unique values for just the base query elements select, from, and join
 * 'where' and 'where_values' key should not have duplicates as that is handled earlier and applying
 * array_unique here could cause problems with the query.
 * @param array $p_query_clauses Array of query clauses.
 * @return array
 */
function filter_unique_query_clauses( array $p_query_clauses ) {
	$p_query_clauses['select'] = array_unique( $p_query_clauses['select'] );
	$p_query_clauses['from'] = array_unique( $p_query_clauses['from'] );
	$p_query_clauses['join'] = array_unique( $p_query_clauses['join'] );
	return $p_query_clauses;
}

/**
 * Build a query with the query clauses array, query for bug count and return the result
 *
 * Note: The parameter $p_pop_param can be used as 'false' to keep db_params in the stack,
 * if the same query clauses object is reused for several queries. In that case a db_param_pop()
 * should be used manually when required.
 * This is the case when "filter_get_bug_count" is used followed by "filter_get_bug_rows_result"
 * @param array $p_query_clauses Array of query clauses.
 * @param type $p_pop_param      Whether to pop DB params from the stack
 * @return integer
 */
function filter_get_bug_count( array $p_query_clauses, $p_pop_param = true ) {
	# If query caluses is an empty array, the query can't be created
	if( empty( $p_query_clauses ) ) {
		if( $p_pop_param ) {
			# reset the db_param stack, this woould have been done by db_query if executed
			db_param_pop();
		}
		return 0;
	}
	$p_query_clauses = filter_unique_query_clauses( $p_query_clauses );
	$t_select_string = 'SELECT Count( DISTINCT {bug}.id ) as idcnt ';
	$t_from_string = ' FROM ' . implode( ', ', $p_query_clauses['from'] );
	$t_join_string = (( count( $p_query_clauses['join'] ) > 0 ) ? implode( ' ', $p_query_clauses['join'] ) : '' );
	$t_where_string = count( $p_query_clauses['project_where'] ) > 0 ? 'WHERE '. implode( ' AND ', $p_query_clauses['project_where'] ) : '';
	if( count( $p_query_clauses['where'] ) > 0 ) {
		$t_where_string .= ' AND ( ';
		$t_where_string .= implode( $p_query_clauses['operator'], $p_query_clauses['where'] );
		$t_where_string .= ' ) ';
	}
	$t_result = db_query(
			$t_select_string . ' ' . $t_from_string . ' ' . $t_join_string . ' ' . $t_where_string,
			$p_query_clauses['where_values'],
			/* limit */ -1, /* offset */ -1,
			$p_pop_param );
	return db_result( $t_result );
}

/**
 * Get set of bug rows from given filter
 * @todo Had to make all these parameters required because we can't use call-time pass by reference anymore.
 * I really preferred not having to pass all the params in if you didn't want to, but I wanted to get
 * rid of the errors for now.  If we can think of a better way later (maybe return an object) that would be great.
 *
 * @param integer &$p_page_number  Page number of the page you want to see (set to the actual page on return).
 * @param integer &$p_per_page     The number of bugs to see per page (set to actual on return)
 *                                 -1   indicates you want to see all bugs
 *                                 null indicates you want to use the value specified in the filter.
 * @param integer &$p_page_count   You don't need to give a value here, the number of pages will be stored here on return.
 * @param integer &$p_bug_count    You don't need to give a value here, the number of bugs will be stored here on return.
 * @param mixed   $p_custom_filter Custom Filter to use.
 * @param integer $p_project_id    Project id to use in filtering.
 * @param integer $p_user_id       User id to use as current user when filtering.
 * @param boolean $p_show_sticky   True/false - get sticky issues only.
 * @return boolean|array
 */
function filter_get_bug_rows( &$p_page_number, &$p_per_page, &$p_page_count, &$p_bug_count, $p_custom_filter = null, $p_project_id = null, $p_user_id = null, $p_show_sticky = null ) {
	# assigning to $p_* for this function writes the values back in case the caller wants to know

	if( $p_custom_filter === null ) {
		$t_filter = filter_get_bug_rows_filter( $p_project_id, $p_user_id );
	} else {
		$t_filter = $p_custom_filter;
	}

	# Get the query clauses
	$t_query_clauses = filter_get_bug_rows_query_clauses( $t_filter, $p_project_id, $p_user_id, $p_show_sticky );

	# Get the total number of bugs that meet the criteria.
	# Keep the db_params in stack for next query
	$p_bug_count = filter_get_bug_count( $t_query_clauses, /* pop_params */ false );
	if( 0 == $p_bug_count ) {
		# reset the db_param stack that was initialized by "filter_get_bug_rows_query_clauses()"
		db_param_pop();
		return array();
	}

	# Calculate pagination
	$p_per_page = filter_per_page( $t_filter, $p_bug_count, $p_per_page );
	$p_page_count = filter_page_count( $p_bug_count, $p_per_page );
	$p_page_number = filter_valid_page_number( $p_page_number, $p_page_count );
	$t_offset = filter_offset( $p_page_number, $p_per_page );
	# Execute query
	$t_result = filter_get_bug_rows_result( $t_query_clauses, $p_per_page, $t_offset );

	# Read results into rows array
	$t_bug_id_array = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_bug_id_array[] = (int)$t_row['id'];
		$t_rows[] = $t_row;
	}

	# Return the processed rows: cache data, convert to bug objects
	return filter_cache_result( $t_rows, $t_bug_id_array );
}

/**
 * Get the filter defined by user and project.
 * @param integer $p_project_id    Project id to use in filtering.
 * @param integer $p_user_id       User id to use as current user when filtering.
 * @return array
 */
function filter_get_bug_rows_filter( $p_project_id = null, $p_user_id = null ) {
	$t_current_user_id = auth_get_current_user_id();

	if( $p_user_id === null || $p_user_id === 0 ) {
		$t_user_id = $t_current_user_id;
	} else {
		$t_user_id = $p_user_id;
	}

	if( null === $p_project_id ) {
		# @@@ If project_id is not specified, then use the project id(s) in the filter if set, otherwise, use current project.
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	if( $t_user_id == $t_current_user_id ) {
		$t_filter = current_user_get_bug_filter();
	} else {
		$t_filter = user_get_bug_filter( $t_user_id, $t_project_id );
	}

	# if filter isn't return above, create a new filter from an empty array.
	if( false === $t_filter ) {
		$t_filter = array();
	}
	return $t_filter;
}

/**
 * Creates a sql query with the supplied filter query clauses, and returns the unprocessed result set opbject
 *
 * Note: The parameter $p_pop_param can be used as 'false' to keep db_params in the stack,
 * if the same query clauses object is reused for several queries. In that case a db_param_pop()
 * should be used manually when required.
 * This is the case when "filter_get_bug_count" is used followed by "filter_get_bug_rows_result"
 * @param array   $p_query_clauses Array of query clauses
 * @param integer $p_count         The number of rows to return
 *                                 -1 or null indicates default query (no limits)
 * @param integer $p_offset        Offset query results for paging (number of rows)
 *                                 -1 or null indicates default query (no offset)
 * @param type $p_pop_param        Whether to pop DB params from the stack
 * @return IteratorAggregate|boolean adodb result set or false if the query failed.
 */
function filter_get_bug_rows_result( array $p_query_clauses, $p_count = null, $p_offset = null, $p_pop_param = true ) {
	# if the query can't be formed, there are no results
	if( empty( $p_query_clauses ) ) {
		if( $p_pop_param ) {
			# reset the db_param stack, this woould have been done by db_query if executed
			db_param_pop();
		}
		return db_empty_result();
	}

	if( null === $p_count ) {
		$t_count = -1;
	} else {
		$t_count = $p_count;
	}
	if( null === $p_offset ) {
		$t_offset = -1;
	} else {
		$t_offset = $p_offset;
	}
	$t_query_clauses = $p_query_clauses;
	$t_select_string = 'SELECT DISTINCT ' . implode( ', ', $t_query_clauses['select'] );
	$t_from_string = ' FROM ' . implode( ', ', $t_query_clauses['from'] );
	$t_order_string = ' ORDER BY ' . implode( ', ', $t_query_clauses['order'] );
	$t_join_string = count( $t_query_clauses['join'] ) > 0 ? implode( ' ', $t_query_clauses['join'] ) : ' ';
	$t_where_string = ' WHERE '. implode( ' AND ', $t_query_clauses['project_where'] );
	if( count( $t_query_clauses['where'] ) > 0 ) {
		$t_where_string .= ' AND ( ';
		$t_where_string .= implode( $t_query_clauses['operator'], $t_query_clauses['where'] );
		$t_where_string .= ' ) ';
	}

	$t_result = db_query(
		$t_select_string . $t_from_string . $t_join_string . $t_where_string . $t_order_string,
		$t_query_clauses['where_values'],
		$t_count,
		$t_offset,
		$p_pop_param
	);
	return $t_result;
}

/**
 * Creates an array of formatted query clauses, based on the supplied
 * filter and parameters.
 * Note: this function executes db_param_push():
 *  - If the returned query is not executed, db_param_pop() should be executed
 *    to clean up the parameter stack
 *  - If the final query adds db_param() outside of this function,
 *    they must be added after this function is called.
 * @param array   $p_filter       Filter array object
 * @param integer $p_project_id   Project id to use in filtering.
 * @param integer $p_user_id      User id to use as current user when filtering.
 * @param boolean $p_show_sticky  True/false - get sticky issues only.
 * @return array
 */
function filter_get_bug_rows_query_clauses( array $p_filter, $p_project_id = null, $p_user_id = null, $p_show_sticky = null ) {
	log_event( LOG_FILTERING, 'START NEW FILTER QUERY' );

	$t_limit_reporters = config_get( 'limit_reporters' );
	$t_report_bug_threshold = config_get( 'report_bug_threshold' );
	$t_where_param_count = 0;

	$t_current_user_id = auth_get_current_user_id();

	if( $p_user_id === null || $p_user_id === 0 ) {
		$t_user_id = $t_current_user_id;
	} else {
		$t_user_id = $p_user_id;
	}

	$c_user_id = (int)$t_user_id;

	if( null === $p_project_id ) {
		# @@@ If project_id is not specified, then use the project id(s) in the filter if set, otherwise, use current project.
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	$t_filter = filter_ensure_valid_filter( $p_filter );

	$t_view_type = $t_filter['_view_type'];

	db_param_push();

	# project query clauses must be AND-ed always, irrespective of how the filter
	# clauses are requested by the user ( all matching -> AND, any matching -> OR )
	$t_where_clauses = array();

	$t_project_where_clauses =  array( '{project}.enabled = ' . db_param() );
	$t_where_params = array(
		1,
	);
	$t_select_clauses = array(
		'{bug}.*',
	);

	$t_from_clauses = array(
		'{bug}',
	);

	$t_join_clauses = array(
		' JOIN {project} ON {project}.id = {bug}.project_id',
	);

	# normalize the project filtering into an array $t_project_ids
	if( 'simple' == $t_view_type ) {
		log_event( LOG_FILTERING, 'Simple Filter' );
		$t_project_ids = array(
			$t_project_id,
		);
		$t_include_sub_projects = true;
	} else {
		log_event( LOG_FILTERING, 'Advanced Filter' );
		if( !is_array( $t_filter[FILTER_PROPERTY_PROJECT_ID] ) ) {
			$t_project_ids = array(
				(int)$t_filter[FILTER_PROPERTY_PROJECT_ID],
			);
		} else {
			$t_project_ids = array_map( 'intval', $t_filter[FILTER_PROPERTY_PROJECT_ID] );
		}

		$t_include_sub_projects = (( count( $t_project_ids ) == 1 ) && ( ( $t_project_ids[0] == META_FILTER_CURRENT ) || ( $t_project_ids[0] == ALL_PROJECTS ) ) );
	}

	log_event( LOG_FILTERING, 'project_ids = @P' . implode( ', @P', $t_project_ids ) );
	log_event( LOG_FILTERING, 'include sub-projects = ' . ( $t_include_sub_projects ? '1' : '0' ) );

	# if the array has ALL_PROJECTS, then reset the array to only contain ALL_PROJECTS.
	# replace META_FILTER_CURRENT with the actualy current project id.
	$t_all_projects_found = false;
	$t_new_project_ids = array();
	foreach( $t_project_ids as $t_pid ) {
		if( $t_pid == META_FILTER_CURRENT ) {
			$t_pid = $t_project_id;
		}

		if( $t_pid == ALL_PROJECTS ) {
			$t_all_projects_found = true;
			log_event( LOG_FILTERING, 'all projects selected' );
			break;
		}

		# filter out inaccessible projects.
		if( !project_exists( $t_pid ) || !access_has_project_level( config_get( 'view_bug_threshold', null, $t_user_id, $t_pid ), $t_pid, $t_user_id ) ) {
			log_event( LOG_FILTERING, 'Invalid or inaccessible project: ' . $t_pid );
			continue;
		}

		$t_new_project_ids[] = $t_pid;
	}

	$t_projects_query_required = true;
	if( $t_all_projects_found ) {
		if( user_is_administrator( $t_user_id ) ) {
			log_event( LOG_FILTERING, 'all projects + administrator, hence no project filter.' );
			$t_projects_query_required = false;
		} else {
			$t_project_ids = user_get_accessible_projects( $t_user_id );
		}
	} else {
		$t_project_ids = $t_new_project_ids;
	}

	if( $t_projects_query_required ) {
		# expand project ids to include sub-projects
		if( $t_include_sub_projects ) {
			$t_top_project_ids = $t_project_ids;

			foreach( $t_top_project_ids as $t_pid ) {
				log_event( LOG_FILTERING, 'Getting sub-projects for project id @P' . $t_pid );
				$t_subproject_ids = user_get_all_accessible_subprojects( $t_user_id, $t_pid );
				if( !$t_subproject_ids ) {
					continue;
				}
				$t_project_ids = array_merge( $t_project_ids, $t_subproject_ids );
			}

			$t_project_ids = array_unique( $t_project_ids );
		}

		# if no projects are accessible, then return an empty array.
		if( count( $t_project_ids ) == 0 ) {
			log_event( LOG_FILTERING, 'no accessible projects' );
			return array();
		}

		log_event( LOG_FILTERING, 'project_ids after including sub-projects = @P' . implode( ', @P', $t_project_ids ) );

		# this array is to be populated with project ids for which we only want to show public issues.  This is due to the limited
		# access of the current user.
		$t_public_only_project_ids = array();

		# this array is populated with project ids that the current user has full access to.
		$t_private_and_public_project_ids = array();
		$t_limited_projects = array();

		foreach( $t_project_ids as $t_pid ) {
			# limit reporters to visible projects
			if( ( ON === $t_limit_reporters ) && ( !access_has_project_level( access_threshold_min_level( config_get( 'report_bug_threshold', null, $t_user_id, $t_pid ) ) + 1, $t_pid, $t_user_id ) ) ) {
				array_push( $t_limited_projects, '({bug}.project_id=' . $t_pid . ' AND ({bug}.reporter_id=' . $t_user_id . ') )' );
			} else {
				$t_access_required_to_view_private_bugs = config_get( 'private_bug_threshold', null, null, $t_pid );
				if( access_has_project_level( $t_access_required_to_view_private_bugs, $t_pid, $t_user_id ) ) {
					$t_private_and_public_project_ids[] = $t_pid;
				} else {
					$t_public_only_project_ids[] = $t_pid;
				}
			}
		}

		log_event( LOG_FILTERING, 'project_ids (with public/private access) = @P' . implode( ', @P', $t_private_and_public_project_ids ) );
		log_event( LOG_FILTERING, 'project_ids (with public access) = @P' . implode( ', @P', $t_public_only_project_ids ) );

		$t_count_private_and_public_project_ids = count( $t_private_and_public_project_ids );
		if( $t_count_private_and_public_project_ids == 1 ) {
			$t_private_and_public_query = '( {bug}.project_id = ' . $t_private_and_public_project_ids[0] . ' )';
		} else if( $t_count_private_and_public_project_ids > 1 ) {
			$t_private_and_public_query = '( {bug}.project_id in (' . implode( ', ', $t_private_and_public_project_ids ) . ') )';
		} else {
			$t_private_and_public_query = null;
		}

		$t_count_public_only_project_ids = count( $t_public_only_project_ids );
		$t_public_view_state_check = '( ( {bug}.view_state = ' . VS_PUBLIC . ' ) OR ( {bug}.reporter_id = ' . $t_user_id . ') )';
		if( $t_count_public_only_project_ids == 1 ) {
			$t_public_only_query = '( ( {bug}.project_id = ' . $t_public_only_project_ids[0] . ' ) AND ' . $t_public_view_state_check . ')';
		} else if( $t_count_public_only_project_ids > 1 ) {
			$t_public_only_query = '( ( {bug}.project_id in (' . implode( ', ', $t_public_only_project_ids ) . ') ) AND ' . $t_public_view_state_check . ')';
		} else {
			$t_public_only_query = null;
		}

		# both queries can't be null, so we either have one of them or both.

		if( $t_private_and_public_query === null ) {
			$t_project_query = $t_public_only_query;
		} else if( $t_public_only_query === null ) {
			$t_project_query = $t_private_and_public_query;
		} else {
			$t_project_query = '( ' . $t_public_only_query . ' OR ' . $t_private_and_public_query . ' )';
		}

		if( !empty( $t_limited_projects ) ) {
			foreach( $t_limited_projects as $t_string ) {
				if( $t_project_query ==  "" ) {
					$t_project_query = " ( $t_string ) ";
				} else {
					$t_project_query = " ( $t_project_query OR ( $t_string ) )";
				}
			}
		}
		log_event( LOG_FILTERING, 'project query = ' . $t_project_query );
		array_push( $t_project_where_clauses, $t_project_query );
	}

	# date filter
	if( ( 'on' == $t_filter[FILTER_PROPERTY_FILTER_BY_DATE] ) && is_numeric( $t_filter[FILTER_PROPERTY_START_MONTH] ) && is_numeric( $t_filter[FILTER_PROPERTY_START_DAY] ) && is_numeric( $t_filter[FILTER_PROPERTY_START_YEAR] ) && is_numeric( $t_filter[FILTER_PROPERTY_END_MONTH] ) && is_numeric( $t_filter[FILTER_PROPERTY_END_DAY] ) && is_numeric( $t_filter[FILTER_PROPERTY_END_YEAR] ) ) {

		$t_start_string = $t_filter[FILTER_PROPERTY_START_YEAR] . '-' . $t_filter[FILTER_PROPERTY_START_MONTH] . '-' . $t_filter[FILTER_PROPERTY_START_DAY] . ' 00:00:00';
		$t_end_string = $t_filter[FILTER_PROPERTY_END_YEAR] . '-' . $t_filter[FILTER_PROPERTY_END_MONTH] . '-' . $t_filter[FILTER_PROPERTY_END_DAY] . ' 23:59:59';

		$t_where_params[] = strtotime( $t_start_string );
		$t_where_params[] = strtotime( $t_end_string );
		array_push( $t_project_where_clauses, '({bug}.date_submitted BETWEEN ' . db_param() . ' AND ' . db_param() . ' )' );
	}

	# view state
	$t_view_state = (int)$t_filter[FILTER_PROPERTY_VIEW_STATE];
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_VIEW_STATE] ) ) {
		$t_view_state_query = '({bug}.view_state=' . db_param() . ')';
		log_event( LOG_FILTERING, 'view_state query = ' . $t_view_state_query );
		$t_where_params[] = $t_view_state;
		array_push( $t_where_clauses, $t_view_state_query );
	} else {
		log_event( LOG_FILTERING, 'no view_state query' );
	}

	# reporter
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_REPORTER_ID] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_REPORTER_ID] as $t_filter_member ) {
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '0' );
			} else {
				$c_reporter_id = (int)$t_filter_member;
				if( filter_field_is_myself( $c_reporter_id ) ) {
					array_push( $t_clauses, $c_user_id );
				} else {
					array_push( $t_clauses, $c_reporter_id );
				}
			}
		}

		if( 1 < count( $t_clauses ) ) {
			$t_reporter_query = '( {bug}.reporter_id in (' . implode( ', ', $t_clauses ) . ') )';
		} else {
			$t_reporter_query = '( {bug}.reporter_id=' . $t_clauses[0] . ' )';
		}

		log_event( LOG_FILTERING, 'reporter query = ' . $t_reporter_query );
		array_push( $t_where_clauses, $t_reporter_query );
	} else {
		log_event( LOG_FILTERING, 'no reporter query' );
	}

	# handler
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_HANDLER_ID] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_HANDLER_ID] as $t_filter_member ) {
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, 0 );
			} else {
				$c_handler_id = (int)$t_filter_member;
				if( filter_field_is_myself( $c_handler_id ) ) {
					array_push( $t_clauses, $c_user_id );
				} else {
					array_push( $t_clauses, $c_handler_id );
				}
			}
		}

		if( 1 < count( $t_clauses ) ) {
			$t_handler_query = '( {bug}.handler_id in (' . implode( ', ', $t_clauses ) . ') )';
		} else {
			$t_handler_query = '( {bug}.handler_id=' . $t_clauses[0] . ' )';
		}

		log_event( LOG_FILTERING, 'handler query = ' . $t_handler_query );
		array_push( $t_where_clauses, $t_handler_query );
	} else {
		log_event( LOG_FILTERING, 'no handler query' );
	}

	# category
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_CATEGORY_ID] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_CATEGORY_ID] as $t_filter_member ) {
			if( !filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, $t_filter_member );
			}
		}

		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.category_id in ( SELECT id FROM {category} WHERE name in (' . implode( ', ', $t_where_tmp ) . ') ) )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.category_id in ( SELECT id FROM {category} WHERE name=' . db_param() . ') )' );
		}
	}

	# severity
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_SEVERITY] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_SEVERITY] as $t_filter_member ) {
			$c_show_severity = (int)$t_filter_member;
			array_push( $t_clauses, $c_show_severity );
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.severity in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.severity=' . db_param() . ' )' );
		}
	}

	# show / hide status
	# take a list of all available statuses then remove the ones that we want hidden, then make sure
	# the ones we want shown are still available
	$t_desired_statuses = array();
	$t_available_statuses = MantisEnum::getValues( config_get( 'status_enum_string' ) );

	if( 'simple' == $t_filter['_view_type'] ) {
		# simple filtering: if showing any, restrict by the hide status value, otherwise ignore the hide
		$t_this_status = $t_filter[FILTER_PROPERTY_STATUS][0];
		$t_this_hide_status = isset( $t_filter[FILTER_PROPERTY_HIDE_STATUS][0] )
			? $t_filter[FILTER_PROPERTY_HIDE_STATUS][0]
			: null;

		if( filter_field_is_any( $t_this_status ) ) {
			foreach( $t_available_statuses as $t_this_available_status ) {
				if( $t_this_hide_status > $t_this_available_status ) {
					$t_desired_statuses[] = $t_this_available_status;
				}
			}
		} else {
			$t_desired_statuses[] = $t_this_status;
		}
	} else {
		# advanced filtering: ignore the hide
		if( filter_field_is_any( $t_filter[FILTER_PROPERTY_STATUS] ) ) {
			$t_desired_statuses = array();
		} else {
			foreach( $t_filter[FILTER_PROPERTY_STATUS] as $t_this_status ) {
				$t_desired_statuses[] = $t_this_status;
			}
		}
	}

	if( count( $t_desired_statuses ) > 0 ) {
		$t_clauses = array();

		foreach( $t_desired_statuses as $t_filter_member ) {
			$c_show_status = (int)$t_filter_member;
			array_push( $t_clauses, $c_show_status );
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.status in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.status=' . db_param() . ' )' );
		}
	}

	# resolution
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_RESOLUTION] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_RESOLUTION] as $t_filter_member ) {
			$c_show_resolution = (int)$t_filter_member;
			array_push( $t_clauses, $c_show_resolution );
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.resolution in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.resolution=' . db_param() . ' )' );
		}
	}

	# priority
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_PRIORITY] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_PRIORITY] as $t_filter_member ) {
			$c_show_priority = (int)$t_filter_member;
			array_push( $t_clauses, $c_show_priority );
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.priority in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.priority=' . db_param() . ' )' );
		}
	}

	# product build
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_BUILD] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_BUILD] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				$c_show_build = $t_filter_member;
				array_push( $t_clauses, $c_show_build );
			}
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.build in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.build=' . db_param() . ' )' );
		}
	}

	# product version
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_VERSION] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_VERSION] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				$c_show_version = $t_filter_member;
				array_push( $t_clauses, $c_show_version );
			}
		}

		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.version in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.version=' . db_param() . ' )' );
		}
	}

	# profile
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_PROFILE_ID] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_PROFILE_ID] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '0' );
			} else {
				$c_show_profile = (int)$t_filter_member;
				array_push( $t_clauses, $c_show_profile );
			}
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.profile_id in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.profile_id=' . db_param() . ' )' );
		}
	}

	# platform
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_PLATFORM] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_PLATFORM] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				$c_platform = $t_filter_member;
				array_push( $t_clauses, $c_platform );
			}
		}

		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.platform in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.platform = ' . db_param() . ' )' );
		}
	}

	# Operating System (os)
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_OS] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_OS] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				$c_os = $t_filter_member;
				array_push( $t_clauses, $c_os );
			}
		}

		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.os in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.os = ' . db_param() . ' )' );
		}
	}

	# Operating System Build (os_build)
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_OS_BUILD] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_OS_BUILD] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				$c_os_build = $t_filter_member;
				array_push( $t_clauses, $c_os_build );
			}
		}

		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.os_build in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.os_build = ' . db_param() . ' )' );
		}
	}

	# fixed in version
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_FIXED_IN_VERSION] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_FIXED_IN_VERSION] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				$c_fixed_in_version = $t_filter_member;
				array_push( $t_clauses, $c_fixed_in_version );
			}
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.fixed_in_version in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.fixed_in_version=' . db_param() . ' )' );
		}
	}

	# target version
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_TARGET_VERSION] ) ) {
		$t_clauses = array();

		foreach( $t_filter[FILTER_PROPERTY_TARGET_VERSION] as $t_filter_member ) {
			$t_filter_member = stripslashes( $t_filter_member );
			if( filter_field_is_none( $t_filter_member ) ) {
				array_push( $t_clauses, '' );
			} else {
				$c_target_version = $t_filter_member;
				array_push( $t_clauses, $c_target_version );
			}
		}

		# echo var_dump( $t_clauses ); exit;
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( {bug}.target_version in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( {bug}.target_version=' . db_param() . ' )' );
		}
	}

	# users monitoring a bug
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] ) ) {
		$t_clauses = array();
		$t_table_name = 'user_monitor';
		array_push( $t_join_clauses, 'LEFT JOIN {bug_monitor} ' . $t_table_name . ' ON ' . $t_table_name . '.bug_id = {bug}.id' );

		foreach( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] as $t_filter_member ) {
			$c_user_monitor = (int)$t_filter_member;
			if( filter_field_is_myself( $c_user_monitor ) ) {
				array_push( $t_clauses, $c_user_id );
			} else {
				array_push( $t_clauses, $c_user_monitor );
			}
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( ' . $t_table_name . '.user_id in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( ' . $t_table_name . '.user_id=' . db_param() . ' )' );
		}
	}

	# bug relationship
	$t_any_found = false;
	$c_rel_type = $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
	$c_rel_bug = $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG];
	if( -1 == $c_rel_type || 0 == $c_rel_bug ) {
		$t_any_found = true;
	}
	if( !$t_any_found ) {
		# use the complementary type
		$t_comp_type = relationship_get_complementary_type( $c_rel_type );
		$t_clauses = array();
		$t_table_dst = 'rel_dst';
		$t_table_src = 'rel_src';
		array_push( $t_join_clauses, 'LEFT JOIN {bug_relationship} ' . $t_table_dst . ' ON ' . $t_table_dst . '.destination_bug_id = {bug}.id' );
		array_push( $t_join_clauses, 'LEFT JOIN {bug_relationship} ' . $t_table_src . ' ON ' . $t_table_src . '.source_bug_id = {bug}.id' );

		# get reverse relationships
		$t_where_params[] = $t_comp_type;
		$t_where_params[] = $c_rel_bug;
		$t_where_params[] = $c_rel_type;
		$t_where_params[] = $c_rel_bug;
		array_push( $t_clauses, '(' . $t_table_dst . '.relationship_type=' . db_param() . ' AND ' . $t_table_dst . '.source_bug_id=' . db_param() . ')' );
		array_push( $t_clauses, '(' . $t_table_src . '.relationship_type=' . db_param() . ' AND ' . $t_table_src . '.destination_bug_id=' . db_param() . ')' );
		array_push( $t_where_clauses, '(' . implode( ' OR ', $t_clauses ) . ')' );
	}

	# tags
	$c_tag_string = trim( $t_filter[FILTER_PROPERTY_TAG_STRING] );
	$c_tag_select = trim( $t_filter[FILTER_PROPERTY_TAG_SELECT] );
	if( is_blank( $c_tag_string ) && !is_blank( $c_tag_select ) && $c_tag_select != 0 && tag_exists( $c_tag_select ) ) {
		$t_tag = tag_get( $c_tag_select );
		$c_tag_string = $t_tag['name'];
	}

	if( !is_blank( $c_tag_string ) ) {
		$t_tags = tag_parse_filters( $c_tag_string );

		if( count( $t_tags ) ) {

			$t_tags_all = array();
			$t_tags_any = array();
			$t_tags_none = array();

			foreach( $t_tags as $t_tag_row ) {
				switch( $t_tag_row['filter'] ) {
					case 1:
						$t_tags_all[] = $t_tag_row;
						break;
					case 0:
						$t_tags_any[] = $t_tag_row;
						break;
					case -1:
						$t_tags_none[] = $t_tag_row;
						break;
				}
			}

			if( 0 < $t_filter[FILTER_PROPERTY_TAG_SELECT] && tag_exists( $t_filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
				$t_tags_any[] = tag_get( $t_filter[FILTER_PROPERTY_TAG_SELECT] );
			}

			if( count( $t_tags_all ) ) {
				$t_clauses = array();
				foreach( $t_tags_all as $t_tag_row ) {
					array_push( $t_clauses, '{bug}.id IN ( SELECT bug_id FROM {bug_tag} WHERE {bug_tag}.tag_id = ' . $t_tag_row['id'] . ')' );
				}
				array_push( $t_where_clauses, '(' . implode( ' AND ', $t_clauses ) . ')' );
			}

			if( count( $t_tags_any ) ) {
				$t_clauses = array();
				foreach( $t_tags_any as $t_tag_row ) {
					array_push( $t_clauses, '{bug_tag}.tag_id = ' . $t_tag_row['id'] );
				}
				array_push( $t_where_clauses, '{bug}.id IN ( SELECT bug_id FROM {bug_tag} WHERE ( ' . implode( ' OR ', $t_clauses ) . ') )' );
			}

			if( count( $t_tags_none ) ) {
				$t_clauses = array();
				foreach( $t_tags_none as $t_tag_row ) {
					array_push( $t_clauses, '{bug_tag}.tag_id = ' . $t_tag_row['id'] );
				}
				array_push( $t_where_clauses, '{bug}.id NOT IN ( SELECT bug_id FROM {bug_tag} WHERE ( ' . implode( ' OR ', $t_clauses ) . ') )' );
			}
		}
	}

	# note user id
	if( !filter_field_is_any( $t_filter[FILTER_PROPERTY_NOTE_USER_ID] ) ) {
		$t_bugnote_table_alias = 'mbnt';
		$t_clauses = array();
		array_push( $t_join_clauses, 'LEFT JOIN {bugnote} ' . $t_bugnote_table_alias . ' ON {bug}.id = ' . $t_bugnote_table_alias . '.bug_id' );

		foreach( $t_filter[FILTER_PROPERTY_NOTE_USER_ID] as $t_filter_member ) {
			$c_note_user_id = (int)$t_filter_member;
			if( filter_field_is_myself( $c_note_user_id ) ) {
				array_push( $t_clauses, $c_user_id );
			} else {
				array_push( $t_clauses, $c_note_user_id );
			}
		}
		if( 1 < count( $t_clauses ) ) {
			$t_where_tmp = array();
			foreach( $t_clauses as $t_clause ) {
				$t_where_tmp[] = db_param();
				$t_where_params[] = $t_clause;
			}
			array_push( $t_where_clauses, '( ' . $t_bugnote_table_alias . '.reporter_id in (' . implode( ', ', $t_where_tmp ) . ') )' );
		} else {
			$t_where_params[] = $t_clauses[0];
			array_push( $t_where_clauses, '( ' . $t_bugnote_table_alias . '.reporter_id=' . db_param() . ' )' );
		}
	}

	# plugin filters
	$t_plugin_filters = filter_get_plugin_filters();
	foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
		if( !filter_field_is_any( $t_filter[$t_field_name] ) || $t_filter_object->type == FILTER_TYPE_BOOLEAN ) {
			$t_filter_query = $t_filter_object->query( $t_filter[$t_field_name] );
			if( is_array( $t_filter_query ) ) {
				if( isset( $t_filter_query['join'] ) ) {
					array_push( $t_join_clauses, $t_filter_query['join'] );
				}
				if( isset( $t_filter_query['where'] ) ) {
					array_push( $t_where_clauses, $t_filter_query['where'] );
				}
				if( isset( $t_filter_query['params'] ) && is_array( $t_filter_query['params'] ) ) {
					$t_where_params = array_merge( $t_where_params, $t_filter_query['params'] );
				}
			}
		}
	}

	# custom field filters
	if( ON == config_get( 'filter_by_custom_fields' ) ) {
		# custom field filtering
		# @@@ At the moment this gets the linked fields relating to the current project
		#     It should get the ones relating to the project in the filter or all projects
		#     if multiple projects.
		$t_custom_fields = custom_field_get_linked_ids( $t_project_id );

		foreach( $t_custom_fields as $t_cfid ) {
			$t_field_info = custom_field_cache_row( $t_cfid, true );
			if( !$t_field_info['filter_by'] ) {
				continue;

				# skip this custom field it shouldn't be filterable
			}

			$t_field = $t_filter['custom_fields'][$t_cfid];
			$t_custom_where_clause = '';

			# Ignore all custom filters that are not set, or that are set to '' or "any"
			if( !filter_field_is_any( $t_field ) ) {
				$t_def = custom_field_get_definition( $t_cfid );
				$t_table_name = '{custom_field_string}_' . $t_cfid;

				# We need to filter each joined table or the result query will explode in dimensions
				# Each custom field will result in a exponential growth like Number_of_Issues^Number_of_Custom_Fields
				# and only after this process ends (if it is able to) the result query will be filtered
				# by the WHERE clause and by the DISTINCT clause
				$t_cf_join_clause = 'LEFT JOIN {custom_field_string} ' . $t_table_name . ' ON {bug}.id = ' . $t_table_name . '.bug_id AND ' . $t_table_name . '.field_id = ' . $t_cfid;

				if( $t_def['type'] == CUSTOM_FIELD_TYPE_DATE ) {
					# Define the value field with type cast to integer
					$t_value_field = 'CAST(COALESCE(NULLIF(' . $t_table_name . '.value, \'\'), \'0\') AS DECIMAL)';
					switch( $t_field[0] ) {
						# Closing parenthesis intentionally omitted, will be added later on
						case CUSTOM_FIELD_DATE_ANY:
							break;
						case CUSTOM_FIELD_DATE_NONE:
							array_push( $t_join_clauses, $t_cf_join_clause );
							$t_custom_where_clause = '( ' . $t_table_name . '.bug_id is null OR ' . $t_value_field . ' = 0 ';
							break;
						case CUSTOM_FIELD_DATE_BEFORE:
							array_push( $t_join_clauses, $t_cf_join_clause );
							$t_custom_where_clause = '( ' . $t_value_field . ' != 0 AND ' . $t_value_field . ' < ' . $t_field[2];
							break;
						case CUSTOM_FIELD_DATE_AFTER:
							array_push( $t_join_clauses, $t_cf_join_clause );
							$t_custom_where_clause = '( ' . $t_value_field . ' > ' . ( $t_field[1] + 1 );
							break;
						default:
							array_push( $t_join_clauses, $t_cf_join_clause );
							$t_custom_where_clause = '( ' . $t_value_field . ' BETWEEN ' . $t_field[1] . ' AND ' . $t_field[2];
							break;
					}
				} else {
					array_push( $t_join_clauses, $t_cf_join_clause );

					$t_filter_array = array();
					foreach( $t_field as $t_filter_member ) {
						$t_filter_member = stripslashes( $t_filter_member );
						if( filter_field_is_none( $t_filter_member ) ) {
							# coerce filter value if selecting META_FILTER_NONE so it will match empty fields
							$t_filter_member = '';

							# but also add those _not_ present in the custom field string table
							array_push( $t_filter_array, '{bug}.id NOT IN (SELECT bug_id FROM {custom_field_string} WHERE field_id=' . $t_cfid . ')' );
						}

						switch( $t_def['type'] ) {
							case CUSTOM_FIELD_TYPE_CHECKBOX:
							case CUSTOM_FIELD_TYPE_MULTILIST:
								$t_where_params[] = '%|' . $t_filter_member . '|%';
								array_push( $t_filter_array, db_helper_like( $t_table_name . '.value' ) );
								break;
							case CUSTOM_FIELD_TYPE_TEXTAREA:
								$t_where_params[] = '%' . $t_filter_member . '%';
								array_push( $t_filter_array, db_helper_like( $t_table_name . '.text' ) );
								break;
							default:
								$t_where_params[] = $t_filter_member;
								array_push( $t_filter_array, $t_table_name . '.value = ' . db_param() );
						}
					}
					$t_custom_where_clause .= '(' . implode( ' OR ', $t_filter_array );
				}
				if( !is_blank( $t_custom_where_clause ) ) {
					array_push( $t_where_clauses, $t_custom_where_clause . ')' );
				}
			}
		}
	}

	# Text search
	if( !is_blank( $t_filter[FILTER_PROPERTY_SEARCH] ) ) {
		# break up search terms by spacing or quoting
		preg_match_all( "/-?([^'\"\s]+|\"[^\"]+\"|'[^']+')/", $t_filter[FILTER_PROPERTY_SEARCH], $t_matches, PREG_SET_ORDER );

		# organize terms without quoting, paying attention to negation
		$t_search_terms = array();
		foreach( $t_matches as $t_match ) {
			$t_search_terms[trim( $t_match[1], "\'\"" )] = ( $t_match[0][0] == '-' );
		}

		# build a big where-clause and param list for all search terms, including negations
		$t_first = true;
		$t_textsearch_where_clause = '( ';
		foreach( $t_search_terms as $t_search_term => $t_negate ) {
			if( !$t_first ) {
				$t_textsearch_where_clause .= ' AND ';
			}

			if( $t_negate ) {
				$t_textsearch_where_clause .= 'NOT ';
			}

			$c_search = '%' . $t_search_term . '%';
			$t_textsearch_where_clause .= '( ' . db_helper_like( '{bug}.summary' ) .
				' OR ' . db_helper_like( '{bug_text}.description' ) .
				' OR ' . db_helper_like( '{bug_text}.steps_to_reproduce' ) .
				' OR ' . db_helper_like( '{bug_text}.additional_information' ) .
				' OR ' . db_helper_like( '{bugnote_text}.note' );

			$t_where_params[] = $c_search;
			$t_where_params[] = $c_search;
			$t_where_params[] = $c_search;
			$t_where_params[] = $c_search;
			$t_where_params[] = $c_search;

			if( is_numeric( $t_search_term ) ) {
				# PostgreSQL on 64-bit OS hack (see #14014)
				if( PHP_INT_MAX > 0x7FFFFFFF && db_is_pgsql() ) {
					$t_search_max = 0x7FFFFFFF;
				} else {
					$t_search_max = PHP_INT_MAX;
				}
				# Note: no need to test negative values, '-' sign has been removed
				if( $t_search_term <= $t_search_max ) {
					$c_search_int = (int)$t_search_term;
					$t_textsearch_where_clause .= ' OR {bug}.id = ' . db_param();
					$t_textsearch_where_clause .= ' OR {bugnote}.id = ' . db_param();
					$t_where_params[] = $c_search_int;
					$t_where_params[] = $c_search_int;
				}
			}

			$t_textsearch_where_clause .= ' )';
			$t_first = false;
		}
		$t_textsearch_where_clause .= ' )';

		# add text query elements to arrays
		if( !$t_first ) {
			$t_join_clauses[] = 'JOIN {bug_text} ON {bug}.bug_text_id = {bug_text}.id';
			$t_join_clauses[] = 'LEFT JOIN {bugnote} ON {bug}.id = {bugnote}.bug_id';
			# Outer join required otherwise we don't retrieve issues without notes
			$t_join_clauses[] = 'LEFT JOIN {bugnote_text} ON {bugnote}.bugnote_text_id = {bugnote_text}.id';
			$t_where_clauses[] = $t_textsearch_where_clause;
		}
	}

	# End text search

	# Determine join operator
	if( $t_filter[FILTER_PROPERTY_MATCH_TYPE] == FILTER_MATCH_ANY ) {
		$t_join_operator = ' OR ';
	} else {
		$t_join_operator = ' AND ';
	}

	log_event( LOG_FILTERING, 'Join operator : ' . $t_join_operator );

	$t_query_clauses['select'] = $t_select_clauses;
	$t_query_clauses['from'] = $t_from_clauses;
	$t_query_clauses['join'] = $t_join_clauses;
	$t_query_clauses['where'] = $t_where_clauses;
	$t_query_clauses['where_values'] = $t_where_params;
	$t_query_clauses['project_where'] = $t_project_where_clauses;
	$t_query_clauses['operator'] = $t_join_operator;
	$t_query_clauses = filter_get_query_sort_data( $t_filter, $p_show_sticky, $t_query_clauses );

	$t_query_clauses = filter_unique_query_clauses( $t_query_clauses );
	return $t_query_clauses;
}

/**
 *  Cache the filter results with bugnote stats for later use
 * @param array $p_rows             Results of the filter query.
 * @param array $p_id_array_lastmod Array of bug ids.
 * @return array
 */
function filter_cache_result( array $p_rows, array $p_id_array_lastmod ) {
	$t_stats = bug_get_bugnote_stats_array( $p_id_array_lastmod );
	$t_rows = array();
	foreach( $p_rows as $t_row ) {
		$b = $t_stats[$t_row['id']];
		if( array_key_exists( $t_row['id'], $t_stats ) ) {
			$t_rows[] = bug_row_to_object( bug_cache_database_result( $t_row, $t_stats[$t_row['id']] ) );
		} else {
			$t_rows[] = bug_row_to_object( bug_cache_database_result( $t_row ) );
		}
	}
	return $t_rows;
}

/**
 * Mainly based on filter_draw_selection_area2() but adds the support for the collapsible
 * filter display.
 * @param integer $p_page_number Page number.
 * @param boolean $p_for_screen  Whether output is for screen view.
 * @return void
 * @see filter_draw_selection_area2
 */
function filter_draw_selection_area( $p_page_number, $p_for_screen = true ) {
	collapse_open( 'filter' );
	filter_draw_selection_area2( $p_page_number, $p_for_screen, true );
	collapse_closed( 'filter' );
	filter_draw_selection_area2( $p_page_number, $p_for_screen, false );
	collapse_end( 'filter' );
}

/**
 * Prints the filter selection area for both the bug list view screen and
 * the bug list print screen. This function was an attempt to make it easier to
 * add new filters and rearrange them on screen for both pages.
 * @param integer $p_page_number Page number.
 * @param boolean $p_for_screen  Whether output is for screen view.
 * @param boolean $p_expanded    Whether to display expanded.
 * @return void
 */
function filter_draw_selection_area2( $p_page_number, $p_for_screen = true, $p_expanded = true ) {
	$t_form_name_suffix = $p_expanded ? '_open' : '_closed';

	$t_filter = current_user_get_bug_filter();
	$t_filter = filter_ensure_valid_filter( $t_filter === false ? array() : $t_filter );
	$t_project_id = helper_get_current_project();
	$t_page_number = (int)$p_page_number;

	$t_view_type = $t_filter['_view_type'];

	$t_tdclass = 'small-caption';
	$t_trclass = 'row-category2';
	$t_action = 'view_all_set.php?f=3';

	if( $p_for_screen == false ) {
		$t_tdclass = 'print';
		$t_trclass = '';
		$t_action = 'view_all_set.php';
	}
	?>

	<div class="filter-box">
		<form method="post" name="filters<?php echo $t_form_name_suffix?>" id="filters_form<?php echo $t_form_name_suffix?>" action="<?php echo $t_action;?>">
		<?php # CSRF protection not required here - form does not result in modifications ?>
		<input type="hidden" name="type" value="1" />
		<?php
			if( $p_for_screen == false ) {
		echo '<input type="hidden" name="print" value="1" />';
		echo '<input type="hidden" name="offset" value="0" />';
	}
	?>
		<input type="hidden" name="page_number" value="<?php echo $t_page_number?>" />
		<input type="hidden" name="view_type" value="<?php echo $t_view_type?>" />
		<?php
		$t_filter_cols = config_get( 'filter_custom_fields_per_row' );
	if( $p_expanded ) {
		?>
		<table width="100%" cellspacing="1">
		<?php
		$t_custom_cols = $t_filter_cols;

		$t_current_user_access_level = current_user_get_access_level();
		$t_accessible_custom_fields_ids = array();
		$t_accessible_custom_fields_names = array();
		$t_accessible_custom_fields_values = array();
		$t_num_custom_rows = 0;
		$t_per_row = 0;

		if( ON == config_get( 'filter_by_custom_fields' ) ) {
			$t_custom_fields = custom_field_get_linked_ids( $t_project_id );

			foreach( $t_custom_fields as $t_cfid ) {
				$t_field_info = custom_field_cache_row( $t_cfid, true );
				if( $t_field_info['access_level_r'] <= $t_current_user_access_level && $t_field_info['filter_by'] ) {
					$t_accessible_custom_fields_ids[] = $t_cfid;
					$t_accessible_custom_fields_names[] = $t_field_info['name'];
					$t_accessible_custom_fields_types[] = $t_field_info['type'];
					$t_accessible_custom_fields_values[] = custom_field_distinct_values( $t_field_info );
				}
			}

			if( count( $t_accessible_custom_fields_ids ) > 0 ) {
				$t_per_row = config_get( 'filter_custom_fields_per_row' );
				$t_num_custom_rows = ceil( count( $t_accessible_custom_fields_ids ) / $t_per_row );
			}
		}

		$t_filters_url = 'view_filters_page.php?for_screen=' . $p_for_screen;
		if( 'advanced' == $t_view_type ) {
			$t_filters_url = $t_filters_url . '&amp;view_type=advanced';
		}
		$t_filters_url = $t_filters_url . '&amp;target_field=';

		$t_show_product_version =  version_should_show_product_version( $t_project_id );
		$t_show_build = $t_show_product_version && ( config_get( 'enable_product_build' ) == ON );

		# overload handler_id setting if user isn't supposed to see them (ref #6189)
		if( !access_has_any_project( config_get( 'view_handler_threshold' ) ) ) {
			$t_filter[FILTER_PROPERTY_HANDLER_ID] = array(
				META_FILTER_ANY,
			);
		}

		$t_dynamic_filter_expander_class = ( config_get( 'use_dynamic_filters' ) ) ? ' class="dynamic-filter-expander"' : '';
		?>

		<tr <?php echo 'class="' . $t_trclass . '"';?>>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_REPORTER_ID . '[]';?>" id="reporter_id_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'reporter_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_MONITOR_USER_ID . '[]';?>" id="user_monitor_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'monitored_by_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_HANDLER_ID . '[]';?>" id="handler_id_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'assigned_to_label' )?></a>
			</td>
			<td colspan="2" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_CATEGORY_ID . '[]';?>" id="show_category_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'category_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_SEVERITY . '[]';?>" id="show_severity_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'severity_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_RESOLUTION . '[]';?>" id="show_resolution_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'resolution_label' )?></a>
			</td>
			<td class="small-caption">
				<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_PROFILE_ID . '[]';?>" id="show_profile_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'profile_label' )?></a>
				<?php } ?>
			</td>
			<?php if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
		</tr>

		<tr class="row-1">
			<td class="small-caption" id="reporter_id_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_REPORTER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_REPORTER_ID] as $t_current ) {
				$t_this_name = '';
				echo '<input type="hidden" name="', FILTER_PROPERTY_REPORTER_ID, '[]" value="', string_attribute( $t_current ), '" />';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else if( filter_field_is_none( $t_current ) ) {
					$t_this_name = lang_get( 'none' );
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_name );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<td class="small-caption" id="user_monitor_filter_target">
		<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_MONITOR_USER_ID, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_name = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_name );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo string_display( $t_output );
			}
		}
		?>
			</td>
			<td class="small-caption" id="handler_id_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_HANDLER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_HANDLER_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_HANDLER_ID, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_name = '';
				if( filter_field_is_none( $t_current ) ) {
					$t_this_name = lang_get( 'none' );
				} else if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_name );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo string_display( $t_output );
			}
		}
		?>
			</td>
			<td colspan="2" class="small-caption" id="show_category_filter_target">
		<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_CATEGORY_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_CATEGORY_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_CATEGORY_ID, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = $t_current;
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_string );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<td class="small-caption" id="show_severity_filter_target">
		<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_SEVERITY] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_SEVERITY] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_SEVERITY, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'severity', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_string );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<td class="small-caption" id="show_resolution_filter_target">
		<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_RESOLUTION] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_RESOLUTION] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_RESOLUTION, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'resolution', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_string );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
			<td class="small-caption" id="show_profile_filter_target">
		<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_PROFILE_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_PROFILE_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_PROFILE_ID, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_profile = profile_get_row_direct( $t_current );
					$t_this_string = $t_profile['platform'] . ' ' . $t_profile['os'] . ' ' . $t_profile['os_build'];
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_string );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<?php } else { ?>
				<td></td>
			<?php }
				if( $t_filter_cols > 8 ) {
					echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
				}?>
		</tr>
		<tr <?php echo 'class="' . $t_trclass . '"';?>>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_STATUS . '[]';?>" id="show_status_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'status_label' )?></a>
			</td>
			<td class="small-caption">
				<?php if( 'simple' == $t_view_type ) {?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_HIDE_STATUS . '[]';?>" id="hide_status_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'hide_status_label' )?></a>
				<?php } ?>
			</td>
			<td class="small-caption">
			<?php if( $t_show_build ) { ?>
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_BUILD . '[]';?>" id="show_build_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'product_build_label' )?></a>
			<?php } ?>
			</td>
			<?php if( $t_show_product_version ) {?>
			<td colspan="2" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_VERSION . '[]';?>" id="show_version_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'product_version_label' )?></a>
			</td>
			<td colspan="1" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_FIXED_IN_VERSION . '[]';?>" id="show_fixed_in_version_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'fixed_in_version_label' )?></a>
			</td>
			<?php } else {?>
			<td colspan="2" class="small-caption">
				&#160;
			</td>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
			<?php } ?>
			<td colspan="1" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_PRIORITY . '[]';?>" id="show_priority_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'priority_label' )?></a>
			</td>
			<?php if( $t_show_product_version ) {?>
			<td colspan="1" class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_TARGET_VERSION . '[]';?>" id="show_target_version_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'target_version_label' )?></a>
			</td>
			<?php
			} else {?>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
			<?php
			}
			if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 7 ) . '">&#160;</td>';
		}?>
		</tr>

		<tr class="row-1">
			<td class="small-caption" id="show_status_filter_target">
		<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_STATUS] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_STATUS] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_STATUS, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'status', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_string );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
			<td class="small-caption" id="hide_status_filter_target">
		<?php
		if( 'simple' == $t_view_type ) {
			$t_output = '';
			$t_none_found = false;
			if( count( $t_filter[FILTER_PROPERTY_HIDE_STATUS] ) == 0 ) {
				echo lang_get( 'none' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_HIDE_STATUS] as $t_current ) {
					echo '<input type="hidden" name="', FILTER_PROPERTY_HIDE_STATUS, '[]" value="', string_attribute( $t_current ), '" />';
					$t_this_string = '';
					if( filter_field_is_none( $t_current ) ) {
						$t_none_found = true;
					} else {
						$t_this_string = get_enum_element( 'status', $t_current );
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . string_display_line( $t_this_string );
				}
				$t_hide_status_post = '';
				if( count( $t_filter[FILTER_PROPERTY_HIDE_STATUS] ) == 1 ) {
					$t_hide_status_post = ' (' . lang_get( 'and_above' ) . ')';
				}
				if( true == $t_none_found ) {
					echo lang_get( 'none' );
				} else {
					echo $t_output . string_display_line( $t_hide_status_post );
				}
			}
		}
		?>
			</td>
		<?php
		if( $t_show_build ) {
		?>
			<td class="small-caption" id="show_build_filter_target">
		<?php
			$t_output = '';
			$t_any_found = false;
			if( count( $t_filter[FILTER_PROPERTY_BUILD] ) == 0 ) {
				echo lang_get( 'any' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_BUILD] as $t_current ) {
					$t_current = stripslashes( $t_current );
					echo '<input type="hidden" name="', FILTER_PROPERTY_BUILD, '[]" value="', string_attribute( $t_current ), '" />';
					$t_this_string = '';
					if( filter_field_is_any( $t_current ) ) {
						$t_any_found = true;
					} else if( filter_field_is_none( $t_current ) ) {
						$t_this_string = lang_get( 'none' );
					} else {
						$t_this_string = $t_current;
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . string_display_line( $t_this_string );
				}
				if( true == $t_any_found ) {
					echo lang_get( 'any' );
				} else {
					echo $t_output;
				}
			}
			?>
			</td>
			<?php } else { ?>
			<td class="small-caption"></td>
			<?php }
				if( $t_show_product_version ) {
			?>
			<td colspan="2" class="small-caption" id="show_version_filter_target">
							<?php
								$t_output = '';
			$t_any_found = false;
			if( count( $t_filter[FILTER_PROPERTY_VERSION] ) == 0 ) {
				echo lang_get( 'any' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_VERSION] as $t_current ) {
					$t_current = stripslashes( $t_current );
					echo '<input type="hidden" name="', FILTER_PROPERTY_VERSION, '[]" value="', string_attribute( $t_current ), '" />';
					$t_this_string = '';
					if( filter_field_is_any( $t_current ) ) {
						$t_any_found = true;
					} else if( filter_field_is_none( $t_current ) ) {
						$t_this_string = lang_get( 'none' );
					} else {
						$t_this_string = $t_current;
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . string_display_line( $t_this_string );
				}
				if( true == $t_any_found ) {
					echo lang_get( 'any' );
				} else {
					echo $t_output;
				}
			}
			?>
			</td>
			<td colspan="1" class="small-caption" id="show_fixed_in_version_filter_target">
							<?php
								$t_output = '';
			$t_any_found = false;
			if( count( $t_filter[FILTER_PROPERTY_FIXED_IN_VERSION] ) == 0 ) {
				echo lang_get( 'any' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_FIXED_IN_VERSION] as $t_current ) {
					$t_current = stripslashes( $t_current );
					echo '<input type="hidden" name="', FILTER_PROPERTY_FIXED_IN_VERSION, '[]" value="', string_attribute( $t_current ), '" />';
					$t_this_string = '';
					if( filter_field_is_any( $t_current ) ) {
						$t_any_found = true;
					} else if( filter_field_is_none( $t_current ) ) {
						$t_this_string = lang_get( 'none' );
					} else {
						$t_this_string = $t_current;
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . string_display_line( $t_this_string );
				}
				if( true == $t_any_found ) {
					echo lang_get( 'any' );
				} else {
					echo $t_output;
				}
			}
			?>
			</td>
		<?php } else { ?>
			<td colspan="2" class="small-caption">
				&#160;
			</td>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
		<?php } ?>
			<td colspan="1" class="small-caption" id="show_priority_filter_target">
		<?php
		$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_PRIORITY] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_PRIORITY] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_PRIORITY, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else {
					$t_this_string = get_enum_element( 'priority', $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_string );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
		</td>
		<?php if( $t_show_product_version ) { ?>
		<td colspan="1" class="small-caption" id="show_target_version_filter_target">
							<?php
								$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_TARGET_VERSION] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_TARGET_VERSION] as $t_current ) {
				$t_current = stripslashes( $t_current );
				echo '<input type="hidden" name="', FILTER_PROPERTY_TARGET_VERSION, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_string = '';
				if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_none( $t_current ) ) {
					$t_this_string = lang_get( 'none' );
				} else {
					$t_this_string = $t_current;
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_string );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>
		<?php } else { ?>
			<td colspan="1" class="small-caption">
				&#160;
			</td>
		<?php }

		if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 7 ) . '">&#160;</td>';
		}?>

		</tr>

		<tr <?php echo 'class="' . $t_trclass . '"';?>>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_ISSUES_PER_PAGE;?>" id="per_page_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'show_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_VIEW_STATE;?>" id="view_state_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'view_status_label' )?></a>
			</td>
			<td class="small-caption">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_STICKY;?>" id="sticky_issues_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'sticky_label' )?></a>
			</td>
			<td class="small-caption" colspan="2">
			</td>
			<td class="small-caption" >
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_FILTER_BY_DATE;?>" id="do_filter_by_date_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'use_date_filters_label' )?></a>
			</td>
			<td class="small-caption" colspan="2">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_RELATIONSHIP_TYPE;?>" id="relationship_type_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'bug_relationships_label' )?></a>
			</td>
			<?php if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
		</tr>
		<tr class="row-1">
			<td class="small-caption" id="per_page_filter_target">
				<?php
					echo( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] == 0 ) ? lang_get( 'all' ) : string_display_line( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] );
		echo '<input type="hidden" name="', FILTER_PROPERTY_ISSUES_PER_PAGE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] ), '" />';
		?>
			</td>
			<td class="small-caption" id="view_state_filter_target">
				<?php
				if( VS_PUBLIC === $t_filter[FILTER_PROPERTY_VIEW_STATE] ) {
			echo lang_get( 'public' );
		} else if( VS_PRIVATE === $t_filter[FILTER_PROPERTY_VIEW_STATE] ) {
			echo lang_get( 'private' );
		} else {
			echo lang_get( 'any' );
			$t_filter[FILTER_PROPERTY_VIEW_STATE] = META_FILTER_ANY;
		}
		echo '<input type="hidden" name="', FILTER_PROPERTY_VIEW_STATE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_VIEW_STATE] ), '" />';
		?>
			</td>
			<td class="small-caption" id="sticky_issues_filter_target">
				<?php
					$t_sticky_filter_state = gpc_string_to_bool( $t_filter[FILTER_PROPERTY_STICKY] );
					print( $t_sticky_filter_state ? lang_get( 'yes' ) : lang_get( 'no' ) );
				?>
				<input type="hidden" name="<?php
					echo FILTER_PROPERTY_STICKY; ?>" value="<?php
					echo $t_sticky_filter_state ? 'on' : 'off'; ?>" />
			</td>
			<td class="small-caption" colspan="2">&#160;
			</td>
			<td class="small-caption" id="do_filter_by_date_filter_target">
		<?php
		if( 'on' == $t_filter[FILTER_PROPERTY_FILTER_BY_DATE] ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_FILTER_BY_DATE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_FILTER_BY_DATE] ), '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_START_MONTH, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_START_MONTH] ), '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_START_DAY, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_START_DAY] ), '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_START_YEAR, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_START_YEAR] ), '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_END_MONTH, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_END_MONTH] ), '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_END_DAY, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_END_DAY] ), '" />';
			echo '<input type="hidden" name="', FILTER_PROPERTY_END_YEAR, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_END_YEAR] ), '" />';

			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
			$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_START_MONTH], $t_filter[FILTER_PROPERTY_START_DAY], $t_filter[FILTER_PROPERTY_START_YEAR] );
			foreach( $t_chars as $t_char ) {
				if( strcasecmp( $t_char, 'M' ) == 0 ) {
					echo ' ';
					echo date( 'F', $t_time );
				}
				if( strcasecmp( $t_char, 'D' ) == 0 ) {
					echo ' ';
					echo date( 'd', $t_time );
				}
				if( strcasecmp( $t_char, 'Y' ) == 0 ) {
					echo ' ';
					echo date( 'Y', $t_time );
				}
			}

			echo ' - ';

			$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_END_MONTH], $t_filter[FILTER_PROPERTY_END_DAY], $t_filter[FILTER_PROPERTY_END_YEAR] );
			foreach( $t_chars as $t_char ) {
				if( strcasecmp( $t_char, 'M' ) == 0 ) {
					echo ' ';
					echo date( 'F', $t_time );
				}
				if( strcasecmp( $t_char, 'D' ) == 0 ) {
					echo ' ';
					echo date( 'd', $t_time );
				}
				if( strcasecmp( $t_char, 'Y' ) == 0 ) {
					echo ' ';
					echo date( 'Y', $t_time );
				}
			}
		} else {
			echo lang_get( 'no' );
		}
		?>
			</td>

			<td class="small-caption" colspan="2" id="relationship_type_filter_target">
		<?php
		echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_TYPE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_BUG, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] ), '" />';
		$c_rel_type = $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
		$c_rel_bug = $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG];
		if( -1 == $c_rel_type || 0 == $c_rel_bug ) {
			echo lang_get( 'any' );
		} else {
			echo relationship_get_description_for_history( $c_rel_type ) . ' ' . $c_rel_bug;
		}

		?>
			</td>
			<?php if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
		</tr>
		<tr <?php echo 'class="' . $t_trclass . '"';?>>
			<td class="small-caption">
				<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_PLATFORM;?>" id="platform_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'platform_label' )?></a>
				<?php } ?>
			</td>
			<td class="small-caption">
				<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_OS;?>" id="os_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'os_label' )?></a>
				<?php } ?>
			</td>
			<td class="small-caption">
				<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
					<a href="<?php echo $t_filters_url . FILTER_PROPERTY_OS_BUILD;?>" id="os_build_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'os_version_label' )?></a>
				<?php } ?>
			</td>
			<td class="small-caption" colspan="5">
				<?php if( access_has_global_level( config_get( 'tag_view_threshold' ) ) ) { ?>
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_TAG_STRING;?>" id="tag_string_filter"<?php echo $t_dynamic_filter_expander_class ?>><?php echo lang_get( 'tags_label' )?></a>
				<?php } ?>
			</td>
			<?php if( $t_filter_cols > 8 ) {
			echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 8 ) . '">&#160;</td>';
		}?>
		</tr>
		<tr class="row-1">
			<?php if( ON == config_get( 'enable_profiles' ) ) { ?>
			<td class="small-caption" id="platform_filter_target">
				<?php print_multivalue_field( FILTER_PROPERTY_PLATFORM, $t_filter[FILTER_PROPERTY_PLATFORM] ); ?>
			</td>
			<td class="small-caption" id="os_filter_target">
				<?php print_multivalue_field( FILTER_PROPERTY_OS, $t_filter[FILTER_PROPERTY_OS] ); ?>
			</td>
			<td class="small-caption" id="os_build_filter_target">
				<?php print_multivalue_field( FILTER_PROPERTY_OS_BUILD, $t_filter[FILTER_PROPERTY_OS_BUILD] ); ?>
			</td>
			<?php } else {?>
				<td colspan="3">&#160;</td>
			<?php } ?>

			<td class="small-caption" id="tag_string_filter_target" colspan="5">
				<?php
					$t_tag_string = $t_filter[FILTER_PROPERTY_TAG_STRING];
		if( $t_filter[FILTER_PROPERTY_TAG_SELECT] != 0 && tag_exists( $t_filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
			$t_tag_string .= ( is_blank( $t_tag_string ) ? '' : config_get( 'tag_separator' ) );
			$t_tag_string .= tag_get_field( $t_filter[FILTER_PROPERTY_TAG_SELECT], 'name' );
		}
		echo string_html_entities( $t_tag_string );
		echo '<input type="hidden" name="', FILTER_PROPERTY_TAG_STRING, '" value="', string_attribute( $t_tag_string ), '" />';
		?>
			</td>
		</tr>
		<?php

		# get plugin filters
		$t_plugin_filters = filter_get_plugin_filters();
		$t_plugin_filter_links = array();
		$t_plugin_filter_fields = array();
		$t_column_count_by_row = array();
		$t_row = 0;
		$t_column_count_by_row[$t_row] = 0;
		foreach( $t_plugin_filters as $t_field_name=>$t_filter_object ) {
			# be sure the colspan is an integer
			$t_colspan = (int)$t_filter_object->colspan;

			# prevent silliness.
			if( $t_colspan < 0 ) {
				$t_colspan = abs( $t_colspan );
			} else if( $t_colspan > $t_filter_cols ) {
				$t_colspan = $t_filter_cols;
			} else if( $t_colspan == 0 ) {
				$t_colspan = 1;
			}
			# the row may already have elements in it. find out.
			$t_columns_available = $t_filter_cols - $t_column_count_by_row[$t_row];
			if( $t_columns_available == 0 ) {
				$t_row++;
				$t_column_count_by_row[$t_row] = 0;
			}

			# see if there is room in the current row
			if( $t_columns_available >= $t_colspan ) {
				$t_assigned_row = $t_row;
				$t_column_count_by_row[$t_row] += $t_colspan;
			} else {
				$t_is_assigned = false;
				# find a row with space
				foreach( $t_column_count_by_row as $t_row_num=>$t_col_count ) {
					if( $t_colspan <= ( $t_filter_cols - $t_col_count ) ) {
						$t_assigned_row = $t_row_num;
						$t_column_count_by_row[$t_row_num] += $t_colspan;
						$t_is_assigned = true;
						break;
					}
				}
				if( !$t_is_assigned ) {
					# no space was found in existing rows. Add a new row for it.
					$t_assigned_row = count( $t_plugin_filter_links );
					$t_column_count_by_row[$t_assigned_row] = $t_colspan;
				}
			}
			$t_colspan_attr = ( $t_colspan > 1 ? 'colspan="' . $t_colspan . '" ' : '' );
			$t_plugin_filter_links[$t_assigned_row][] = '<td ' . $t_colspan_attr . 'class="small-caption"> <a href="' . $t_filters_url . string_attribute( $t_field_name ) .
				'" id="' . string_attribute( $t_field_name ) . '_filter"' . $t_dynamic_filter_expander_class . '>' . string_display_line( $t_filter_object->title ) . '</a> </td>';
			$t_values = '<td ' . $t_colspan_attr . 'class="small-caption" id="' . string_attribute( $t_field_name ) . '_filter_target"> ';

			if( !isset( $t_filter[$t_field_name] ) ) {
				$t_values .= lang_get( 'any' );
			} else {
				switch( $t_filter_object->type ) {
					case FILTER_TYPE_STRING:
					case FILTER_TYPE_INT:
						if( filter_field_is_any( $t_filter[$t_field_name] ) ) {
							$t_values .= lang_get( 'any' );
						} else {
							$t_values .= string_display_line( $t_filter[$t_field_name] );
						}
						$t_values .= '<input type="hidden" name="' . string_attribute( $t_field_name ) . '" value="' . string_attribute( $t_filter[$t_field_name] ) . '"/>';
						break;

					case FILTER_TYPE_BOOLEAN:
						$t_values .= string_display_line( $t_filter_object->display( (bool)$t_filter[$t_field_name] ) );
						$t_values .= '<input type="hidden" name="' . string_attribute( $t_field_name ) . '" value="' . (bool)$t_filter[$t_field_name] . '"/>';
						break;

					case FILTER_TYPE_MULTI_STRING:
					case FILTER_TYPE_MULTI_INT:
						$t_first = true;
						$t_output = '';

						if( !is_array( $t_filter[$t_field_name] ) ) {
							$t_filter[$t_field_name] = array( $t_filter[$t_field_name] );
						}

						foreach( $t_filter[$t_field_name] as $t_current ) {
							if( filter_field_is_any( $t_current ) ) {
								$t_output .= lang_get( 'any' );
							} else {
								$t_output .= ( $t_first ? '' : '<br/>' ) . string_display_line( $t_filter_object->display( $t_current ) );
								$t_first = false;
							}
							$t_values .= '<input type="hidden" name="' . string_attribute( $t_field_name ) . '[]" value="' . string_attribute( $t_current ) . '"/>';
						}

						$t_values .= $t_output;
						break;
				}
			}

			$t_values .= '</td>';

			$t_plugin_filter_fields[$t_assigned_row][] = $t_values;
		}

		$t_row_count = count( $t_plugin_filter_links );
		for( $i=0; $i<$t_row_count; $i++ ) {
			if( $t_column_count_by_row[$i] < $t_filter_cols ) {
				$t_plugin_filter_links[$i][] = '<td class="small-caption" colspan="' . ( $t_filter_cols - $t_column_count_by_row[$i] ) . '">&#160;</td>';
				$t_plugin_filter_fields[$i][] = '<td class="small-caption" colspan="' . ( $t_filter_cols - $t_column_count_by_row[$i] ) . '">&#160;</td>';
			}
			$t_links_row = "\n\t\t" . join( "\n\t\t", $t_plugin_filter_links[$i] );
			$t_values_row = "\n\t\t" . join( "\n\t\t", $t_plugin_filter_fields[$i] );
			echo "\n\t" . '<tr class="', $t_trclass, '">', $t_links_row, "\n\t</tr>";
			echo "\n\t" . '<tr class="row-1">', $t_values_row, "\n\t</tr>\n\t";
		}

		if( ON == config_get( 'filter_by_custom_fields' ) ) {

			# -- Custom Field Searching --

			if( count( $t_accessible_custom_fields_ids ) > 0 ) {
				$t_per_row = config_get( 'filter_custom_fields_per_row' );
				$t_num_fields = count( $t_accessible_custom_fields_ids );
				$t_row_idx = 0;
				$t_col_idx = 0;

				$t_fields = '';
				$t_values = '';

				for( $i = 0;$i < $t_num_fields;$i++ ) {
					if( $t_col_idx == 0 ) {
						$t_fields = '<tr class="' . $t_trclass . '">';
						$t_values = '<tr class="row-1">';
					}

					if( isset( $t_accessible_custom_fields_names[$i] ) ) {
						$t_fields .= '<td class="small-caption"> ';
						$t_fields .= '<a href="' . $t_filters_url . 'custom_field_' . $t_accessible_custom_fields_ids[$i] . '[]" id="custom_field_' . $t_accessible_custom_fields_ids[$i] . '_filter"' . $t_dynamic_filter_expander_class . '>';
						$t_fields .= string_display_line( lang_get_defaulted( $t_accessible_custom_fields_names[$i] ) );
						$t_fields .= '</a> </td> ';
					}
					$t_output = '';
					$t_any_found = false;

					$t_values .= '<td class="small-caption" id="custom_field_' . $t_accessible_custom_fields_ids[$i] . '_filter_target"> ';
					if( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] ) ) {
						$t_values .= lang_get( 'any' );
					} else {
						if( $t_accessible_custom_fields_types[$i] == CUSTOM_FIELD_TYPE_DATE ) {
							$t_short_date_format = config_get( 'short_date_format' );
							if( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] ) ) {
								$t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] = 0;
							}
							$t_start = date( $t_short_date_format, $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] );

							if( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] ) ) {
								$t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] = 0;
							}
							$t_end = date( $t_short_date_format, $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] );
							switch( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][0] ) {
								case CUSTOM_FIELD_DATE_ANY:
									$t_values .= lang_get( 'any' );
									break;
								case CUSTOM_FIELD_DATE_NONE:
									$t_values .= lang_get( 'none' );
									break;
								case CUSTOM_FIELD_DATE_BETWEEN:
									$t_values .= lang_get( 'between_date' ) . '<br />';
									$t_values .= $t_start . '<br />' . $t_end;
									break;
								case CUSTOM_FIELD_DATE_ONORBEFORE:
									$t_values .= lang_get( 'on_or_before_date' ) . '<br />';
									$t_values .= $t_end;
									break;
								case CUSTOM_FIELD_DATE_BEFORE:
									$t_values .= lang_get( 'before_date' ) . '<br />';
									$t_values .= $t_end;
									break;
								case CUSTOM_FIELD_DATE_ON:
									$t_values .= lang_get( 'on_date' ) . '<br />';
									$t_values .= $t_start;
									break;
								case CUSTOM_FIELD_DATE_AFTER:
									$t_values .= lang_get( 'after_date' ) . '<br />';
									$t_values .= $t_start;
									break;
								case CUSTOM_FIELD_DATE_ONORAFTER:
									$t_values .= lang_get( 'on_or_after_date' ) . '<br />';
									$t_values .= $t_start;
									break;
							}
						} else {
							$t_first_flag = true;
							foreach( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] as $t_current ) {
								$t_current = stripslashes( $t_current );
								$t_this_string = '';
								if( filter_field_is_any( $t_current ) ) {
									$t_any_found = true;
								} else if( filter_field_is_none( $t_current ) ) {
									$t_this_string = lang_get( 'none' );
								} else {
									$t_this_string = $t_current;
								}

								if( $t_first_flag != true ) {
									$t_output = $t_output . '<br />';
								} else {
									$t_first_flag = false;
								}

								$t_output = $t_output . string_display_line( $t_this_string );
								$t_values .= '<input type="hidden" name="custom_field_' . $t_accessible_custom_fields_ids[$i] . '[]" value="' . string_attribute( $t_current ) . '" />';
							}
						}

						if( true == $t_any_found ) {
							$t_values .= lang_get( 'any' );
						} else {
							$t_values .= $t_output;
						}
					}
					$t_values .= ' </td>';

					$t_col_idx++;

					if( $t_col_idx == $t_per_row ) {
						if( $t_filter_cols > $t_per_row ) {
							$t_fields .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
							$t_values .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
						}

						$t_fields .= '</tr>' . "\n";
						$t_values .= '</tr>' . "\n";

						echo $t_fields;
						echo $t_values;

						$t_col_idx = 0;
						$t_row_idx++;
					}
				}

				if( $t_col_idx > 0 ) {
					if( $t_col_idx < $t_per_row ) {
						$t_fields .= '<td colspan="' . ( $t_per_row - $t_col_idx ) . '">&#160;</td> ';
						$t_values .= '<td colspan="' . ( $t_per_row - $t_col_idx ) . '">&#160;</td> ';
					}

					if( $t_filter_cols > $t_per_row ) {
						$t_fields .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
						$t_values .= '<td colspan="' . ( $t_filter_cols - $t_per_row ) . '">&#160;</td> ';
					}

					$t_fields .= '</tr>' . "\n";
					$t_values .= '</tr>' . "\n";

					echo $t_fields;
					echo $t_values;
				}
			}
		}
		?>
		<tr class="row-1">
			<td class="small-caption category2">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_NOTE_USER_ID;?>" id="note_user_id_filter"<?php echo $t_dynamic_filter_expander_class ?>>
					<?php echo lang_get( 'note_user_id_label' )?>
				</a>
			</td>
			<td class="small-caption" id="note_user_id_filter_target">
				<?php
					$t_output = '';
		$t_any_found = false;
		if( count( $t_filter[FILTER_PROPERTY_NOTE_USER_ID] ) == 0 ) {
			echo lang_get( 'any' );
		} else {
			$t_first_flag = true;
			foreach( $t_filter[FILTER_PROPERTY_NOTE_USER_ID] as $t_current ) {
				echo '<input type="hidden" name="', FILTER_PROPERTY_NOTE_USER_ID, '[]" value="', string_attribute( $t_current ), '" />';
				$t_this_name = '';
				if( filter_field_is_none( $t_current ) ) {
					$t_this_name = lang_get( 'none' );
				} else if( filter_field_is_any( $t_current ) ) {
					$t_any_found = true;
				} else if( filter_field_is_myself( $t_current ) ) {
					if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
						$t_this_name = '[' . lang_get( 'myself' ) . ']';
					} else {
						$t_any_found = true;
					}
				} else {
					$t_this_name = user_get_name( $t_current );
				}
				if( $t_first_flag != true ) {
					$t_output = $t_output . '<br />';
				} else {
					$t_first_flag = false;
				}
				$t_output = $t_output . string_display_line( $t_this_name );
			}
			if( true == $t_any_found ) {
				echo lang_get( 'any' );
			} else {
				echo $t_output;
			}
		}
		?>
			</td>

			<!-- Sort by -->
			<td class="small-caption category2">
				<a href="<?php echo $t_filters_url . 'show_sort';?>" id="show_sort_filter"<?php echo $t_dynamic_filter_expander_class ?>>
					<?php echo lang_get( 'sort_label' )?>
				</a>
			</td>
			<td class="small-caption" id="show_sort_filter_target">
				<?php
					$t_sort_fields = explode( ',', $t_filter[FILTER_PROPERTY_SORT_FIELD_NAME] );
		$t_dir_fields = explode( ',', $t_filter[FILTER_PROPERTY_SORT_DIRECTION] );

		for( $i = 0;$i < 2;$i++ ) {
			if( isset( $t_sort_fields[$i] ) ) {
				if( 0 < $i ) {
					echo ', ';
				}
				$t_sort = $t_sort_fields[$i];
				if( strpos( $t_sort, 'custom_' ) === 0 ) {
					$t_field_name = string_display( lang_get_defaulted( utf8_substr( $t_sort, utf8_strlen( 'custom_' ) ) ) );
				} else {
					$t_field_name = string_get_field_name( $t_sort );
				}

				echo $t_field_name . ' ' . lang_get( 'bugnote_order_' . utf8_strtolower( $t_dir_fields[$i] ) );
				echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_FIELD_NAME, '_', $i, '" value="', string_attribute( $t_sort_fields[$i] ), '" />';
				echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_DIRECTION, '_', $i, '" value="', string_attribute( $t_dir_fields[$i] ), '" />';
			}
		}
?>
			</td>
<?php
		if( 'advanced' == $t_view_type ) {
?>
			<!-- Project -->
			<td class="small-caption category2" colspan="2">
				<a href="<?php echo $t_filters_url . FILTER_PROPERTY_PROJECT_ID;?>" id="project_id_filter"<?php echo $t_dynamic_filter_expander_class ?>>
					<?php echo lang_get( 'email_project_label' )?>
				</a>
			</td>
			<td class="small-caption" id="project_id_filter_target">
<?php
			$t_output = '';
			if( !is_array( $t_filter[FILTER_PROPERTY_PROJECT_ID] ) ) {
				$t_filter[FILTER_PROPERTY_PROJECT_ID] = array(
					$t_filter[FILTER_PROPERTY_PROJECT_ID],
				);
			}
			if( count( $t_filter[FILTER_PROPERTY_PROJECT_ID] ) == 0 ) {
				echo lang_get( 'current' );
			} else {
				$t_first_flag = true;
				foreach( $t_filter[FILTER_PROPERTY_PROJECT_ID] as $t_current ) {
					echo '<input type="hidden" name="', FILTER_PROPERTY_PROJECT_ID, '[]" value="', string_attribute( $t_current ), '" />';
					$t_this_name = '';
					if( META_FILTER_CURRENT == $t_current ) {
						$t_this_name = '[' . lang_get( 'current' ) . ']';
					} else {
						$t_this_name = project_get_name( $t_current, false );
					}
					if( $t_first_flag != true ) {
						$t_output = $t_output . '<br />';
					} else {
						$t_first_flag = false;
					}
					$t_output = $t_output . string_display_line( $t_this_name );
				}
				echo $t_output;
			}
?>
			</td>
<?php
			if( $t_filter_cols > 6 ) {
				echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 5 ) . '">&#160;</td>';
			}
		} else {
			if( $t_filter_cols > 3 ) {
				echo '<td class="small-caption" colspan="' . ( $t_filter_cols - 2 ) . '">&#160;</td>';
			}
		}
?>
		</tr>

		<!-- Match Type -->
		<tr class="row-1">
			<td class="small-caption category2">
				<a id="match_type_filter"
					href="<?php echo $t_filters_url . FILTER_PROPERTY_MATCH_TYPE;?>"
					<?php echo $t_dynamic_filter_expander_class; ?>>
					<?php echo lang_get( 'filter_match_type_label' )?>
				</a>
			</td>
			<td class="small-caption" id="match_type_filter_target">
			<?php
				switch( $t_filter[FILTER_PROPERTY_MATCH_TYPE] ) {
					case FILTER_MATCH_ANY:
						echo lang_get( 'filter_match_any' );
						break;
					case FILTER_MATCH_ALL:
					default:
						echo lang_get( 'filter_match_all' );
						break;
				}
			?>
			<input type="hidden" name="match_type" value="<?php echo $t_filter[FILTER_PROPERTY_MATCH_TYPE] ?>"/>
			</td>

			<td class="small-caption category2">
				<a id="highlight_changed_filter"
					href="<?php echo $t_filters_url . FILTER_PROPERTY_HIGHLIGHT_CHANGED; ?>"
					<?php echo $t_dynamic_filter_expander_class; ?>>
					<?php echo lang_get( 'changed_label' )?>
				</a>
			</td>
			<td class="small-caption" valign="top" id="highlight_changed_filter_target">
				<?php echo $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED]; ?>
				<input type="hidden"
					name="<?php echo FILTER_PROPERTY_HIGHLIGHT_CHANGED; ?>"
					value="<?php echo string_attribute( $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] ); ?>"
				/>
			</td>

			<td colspan="4">&#160;</td>
		</tr>
	</table>
		<?php
	}

	# expanded
	collapse_icon( 'filter' );
	echo '&nbsp;'; # This is a hack to ensure the div is high enough
	echo '<div class="search-box">';
	echo '<label>';
	echo lang_get( 'search' ) . '&#160;';
	echo '<input type="text" size="16" name="', FILTER_PROPERTY_SEARCH, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_SEARCH] ), '" />';
	echo '</label>';
	echo '</div>';
	?>
	<div class="submit-query"><input type="submit" name="filter" value="<?php echo lang_get( 'filter_button' )?>" /></div>
	</form>
	<?php
	$t_stored_queries_arr = filter_db_get_available_queries();

	if( access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) { ?>
	<div class="save-query">
		<form method="post" name="save_query" action="query_store_page.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="submit" name="save_query_button" class="button-small" value="<?php echo lang_get( 'save_query' )?>" />
		</form>
	</div><?php
	}
	if( count( $t_stored_queries_arr ) > 0 ) { ?>
	<div class="manage-queries">
		<form method="post" name="open_queries" action="query_view_page.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="submit" name="switch_to_query_button" class="button-small" value="<?php echo lang_get( 'open_queries' )?>" />
		</form>
	</div>
	<div class="stored-queries">
		<form method="get" name="list_queries<?php echo $t_form_name_suffix;?>" action="view_all_set.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="hidden" name="type" value="3" />
			<select name="source_query_id">
				<option value="-1"><?php echo '[' . lang_get( 'reset_query' ) . ']'?></option>
				<option value="-1"></option>
				<?php
				$t_source_query_id = isset( $t_filter['_source_query_id'] ) ? (int)$t_filter['_source_query_id'] : -1;
				foreach( $t_stored_queries_arr as $t_query_id => $t_query_name ) {
					echo '<option value="' . $t_query_id . '" ';
					check_selected( $t_query_id, $t_source_query_id );
					echo '>' . string_display_line( $t_query_name ) . '</option>';
				}
				?>
			</select>
			<input type="submit" name="switch_to_query_button" class="button-small" value="<?php echo lang_get( 'use_query' )?>" />
		</form>
	</div> <?php
	} else { ?>
	<div class="reset-query">
		<form method="get" name="reset_query" action="view_all_set.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="hidden" name="type" value="3" />
			<input type="hidden" name="source_query_id" value="-1" />
			<input type="submit" name="reset_query_button" class="button-small" value="<?php echo lang_get( 'reset_query' )?>" />
		</form>
	</div><?php
	}
	?>

	<div class="filter-links">
		<?php
		if( access_has_project_level( config_get( 'create_permalink_threshold' ) ) ) {
			?>
			<form method="get" action="permalink_page.php">
				<?php # CSRF protection not required here - form does not result in modifications ?>
				<input type="hidden" name="url" value="<?php echo urlencode( filter_get_url( $t_filter ) ) ?>" />
				<input type="submit" name="reset_query_button" class="button-small" value="<?php echo lang_get( 'create_filter_link' ) ?>" />
			</form>
			<?php
		}

		$t_view_filters = config_get( 'view_filters' );
		if( ( SIMPLE_ONLY != $t_view_filters ) && ( ADVANCED_ONLY != $t_view_filters ) ) {
			?>
			<form method="get" action="view_all_set.php">
				<?php # CSRF protection not required here - form does not result in modifications ?>
				<input type="hidden" name="type" value="<?php echo config_get( 'use_dynamic_filters' ) ? '6' : '' ?>" />
				<input type="hidden" name="view_type" value="<?php echo 'advanced' == $t_view_type ? 'simple' : 'advanced' ?>" />
				<input type="submit" name="reset_query_button" class="button-small" value="<?php echo 'advanced' == $t_view_type ? lang_get( 'simple_filters' ) : lang_get( 'advanced_filters' ) ?>" />
			</form>
			<?php
		}
		?>
	</div>

	</div>
	<br />
<?php
}

/**
 * @internal The following functions each print out filter field inputs.
 *      They are derived from view_filters_page.php
 *      The functions follow a strict naming convention:
 *
 * 		print_filter_[filter_name]
 *
 *      Where [filter_name] is the same as the "name" of the form element for
 *      that filter. This naming convention is depended upon by the controller
 *      at the end of the script.
 *
 * @todo print functions should be abstracted.  Many of these functions
 *      are virtually identical except for the property name.
 *      Perhaps this code could be made simpler by refactoring into a
 *      class so as to avoid all those calls to global(which are pretty ugly)
 *      These functions could also be shared by view_filters_page.php
 */

/**
 * Print the reporter field
 * @return void
 */
function print_filter_reporter_id() {
	global $g_select_modifier, $g_filter;
	?>
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_REPORTER_ID;?>[]">
		<?php
	# if current user is a reporter, and limited reports set to ON, only display that name
	# @@@ thraxisp - access_has_project_level checks greater than or equal to,
	#   this assumed that there aren't any holes above REPORTER where the limit would apply
	#
	if( ( ON === config_get( 'limit_reporters' ) ) && ( !access_has_project_level( access_threshold_min_level( config_get( 'report_bug_threshold' ) ) + 1 ) ) ) {
		$t_id = auth_get_current_user_id();
		$t_username = user_get_field( $t_id, 'username' );
		$t_realname = user_get_field( $t_id, 'realname' );
		$t_display_name = string_attribute( $t_username );
		if( ( isset( $t_realname ) ) && ( $t_realname > '' ) && ( ON == config_get( 'show_realname' ) ) ) {
			$t_display_name = string_attribute( $t_realname );
		}
		echo '<option value="' . $t_id . '" selected="selected">' . $t_display_name . '</option>';
	} else {
		?>
		<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_REPORTER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
		<?php
			if( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
				echo '<option value="' . META_FILTER_MYSELF . '" ';
				check_selected( $g_filter[FILTER_PROPERTY_REPORTER_ID], META_FILTER_MYSELF );
				echo '>[' . lang_get( 'myself' ) . ']</option>';
			}
		print_reporter_option_list( $g_filter[FILTER_PROPERTY_REPORTER_ID] );
	}?>
		</select>
		<?php
}

/**
 * Print the user monitor field
 * @return void
 */
function print_filter_user_monitor() {
	global $g_select_modifier, $g_filter;
	?>
	<!-- Monitored by -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_MONITOR_USER_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_MONITOR_USER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php
				if( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
		echo '<option value="' . META_FILTER_MYSELF . '" ';
		check_selected( $g_filter[FILTER_PROPERTY_MONITOR_USER_ID], META_FILTER_MYSELF );
		echo '>[' . lang_get( 'myself' ) . ']</option>';
	}
	$t_threshold = config_get( 'show_monitor_list_threshold' );
	$t_has_project_level = access_has_project_level( $t_threshold );

	if( $t_has_project_level ) {
		print_reporter_option_list( $g_filter[FILTER_PROPERTY_MONITOR_USER_ID] );
	}
	?>
		</select>
		<?php
}

/**
 * print the handler field
 * @return void
 */
function print_filter_handler_id() {
	global $g_select_modifier, $g_filter, $f_view_type;
	?>
		<!-- Handler -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_HANDLER_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_HANDLER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php if( access_has_project_level( config_get( 'view_handler_threshold' ) ) ) {?>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_HANDLER_ID], META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php
				if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
			echo '<option value="' . META_FILTER_MYSELF . '" ';
			check_selected( $g_filter[FILTER_PROPERTY_HANDLER_ID], META_FILTER_MYSELF );
			echo '>[' . lang_get( 'myself' ) . ']</option>';
		}

		print_assign_to_option_list( $g_filter[FILTER_PROPERTY_HANDLER_ID] );
	}?>
		</select>
		<?php
}

/**
 * print the category field
 * @return void
 */
function print_filter_show_category() {
	global $g_select_modifier, $g_filter;
	?>
		<!-- Category -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_CATEGORY_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_CATEGORY_ID], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_category_filter_option_list( $g_filter[FILTER_PROPERTY_CATEGORY_ID] )?>
		</select>
		<?php
}

/**
 * print the platform field
 * @return void
 */
function print_filter_platform() {
	global $g_select_modifier, $g_filter;

	?>
		<!-- Platform -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_PLATFORM;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_PLATFORM], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php
				log_event( LOG_FILTERING, 'Platform = ' . var_export( $g_filter[FILTER_PROPERTY_PLATFORM], true ) );
	print_platform_option_list( $g_filter[FILTER_PROPERTY_PLATFORM] );
	?>
		</select>
		<?php
}

/**
 * print the os field
 * @return void
 */
function print_filter_os() {
	global $g_select_modifier, $g_filter;

	?>
		<!-- OS -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_OS;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_OS], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_option_list( $g_filter[FILTER_PROPERTY_OS] )?>
		</select>
		<?php
}

/**
 * print the os build field
 * @return void
 */
function print_filter_os_build() {
	global $g_select_modifier, $g_filter;

	?>
		<!-- OS Build -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_OS_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_OS_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_build_option_list( $g_filter[FILTER_PROPERTY_OS_BUILD] )?>
		</select>
		<?php
}

/**
 * print the severity field
 * @return void
 */
function print_filter_show_severity() {
	global $g_select_modifier, $g_filter;
	?><!-- Severity -->
			<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_SEVERITY;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_SEVERITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'severity', $g_filter[FILTER_PROPERTY_SEVERITY] )?>
			</select>
		<?php
}

/**
 * print resolution field
 * @return void
 */
function print_filter_show_resolution() {
	global $g_select_modifier, $g_filter;
	?><!-- Resolution -->
			<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_RESOLUTION;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_RESOLUTION], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'resolution', $g_filter[FILTER_PROPERTY_RESOLUTION] )?>
			</select>
		<?php
}

/**
 * print status field
 * @return void
 */
function print_filter_show_status() {
	global $g_select_modifier, $g_filter;
	?>	<!-- Status -->
			<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_STATUS;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_STATUS], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'status', $g_filter[FILTER_PROPERTY_STATUS] )?>
			</select>
		<?php
}

/**
 * print hide status field
 * @return void
 */
function print_filter_hide_status() {
	global $g_select_modifier, $g_filter;
	?><!-- Hide Status -->
			<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_HIDE_STATUS;?>[]">
				<option value="<?php echo META_FILTER_NONE?>">[<?php echo lang_get( 'none' )?>]</option>
				<?php print_enum_string_option_list( 'status', $g_filter[FILTER_PROPERTY_HIDE_STATUS] )?>
			</select>
		<?php
}

/**
 * print build field
 * @return void
 */
function print_filter_show_build() {
	global $g_select_modifier, $g_filter;
	?><!-- Build -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_build_option_list( $g_filter[FILTER_PROPERTY_BUILD] )?>
		</select>
		<?php
}

/**
 * print version field
 * @return void
 */
function print_filter_show_version() {
	global $g_select_modifier, $g_filter;
	?><!-- Version -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $g_filter[FILTER_PROPERTY_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * print fixed in version field
 * @return void
 */
function print_filter_show_fixed_in_version() {
	global $g_select_modifier, $g_filter;
	?><!-- Fixed in Version -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_FIXED_IN_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $g_filter[FILTER_PROPERTY_FIXED_IN_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * print target version field
 * @return void
 */
function print_filter_show_target_version() {
	global $g_select_modifier, $g_filter;
	?><!-- Fixed in Version -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_TARGET_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $g_filter[FILTER_PROPERTY_TARGET_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * print priority field
 * @return void
 */
function print_filter_show_priority() {
	global $g_select_modifier, $g_filter;
	?><!-- Priority -->
	<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_PRIORITY;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_PRIORITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_enum_string_option_list( 'priority', $g_filter[FILTER_PROPERTY_PRIORITY] )?>
	</select>
		<?php
}

/**
 * print profile field
 * @return void
 */
function print_filter_show_profile() {
	global $g_select_modifier, $g_filter;
	?><!-- Profile -->
		<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_PROFILE_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_PROFILE_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_profile_option_list_for_project( helper_get_current_project(), $g_filter[FILTER_PROPERTY_PROFILE_ID] );?>
		</select>
		<?php
}

/**
 * print issues per page field
 * @return void
 */
function print_filter_per_page() {
	global $g_filter;
	?><!-- Number of bugs per page -->
		<input type="text" name="<?php echo FILTER_PROPERTY_ISSUES_PER_PAGE;?>" size="3" maxlength="7" value="<?php echo $g_filter[FILTER_PROPERTY_ISSUES_PER_PAGE]?>" />
		<?php
}

/**
 * print view state field
 * @return void
 */
function print_filter_view_state() {
	global $g_select_modifier, $g_filter;
	?><!-- View Status -->
		<select name="<?php echo FILTER_PROPERTY_VIEW_STATE;?>">
			<?php
			echo '<option value="' . META_FILTER_ANY . '"';
	check_selected( $g_filter[FILTER_PROPERTY_VIEW_STATE], META_FILTER_ANY );
	echo '>[' . lang_get( 'any' ) . ']</option>';
	echo '<option value="' . VS_PUBLIC . '"';
	check_selected( $g_filter[FILTER_PROPERTY_VIEW_STATE], VS_PUBLIC );
	echo '>' . lang_get( 'public' ) . '</option>';
	echo '<option value="' . VS_PRIVATE . '"';
	check_selected( $g_filter[FILTER_PROPERTY_VIEW_STATE], VS_PRIVATE );
	echo '>' . lang_get( 'private' ) . '</option>';
	?>
		</select>
		<?php
}

/**
 * print sticky issues field
 * @return void
 */
function print_filter_sticky_issues() {
	global $g_filter;
	?><!-- Show or hide sticky bugs -->
			<input type="checkbox" name="<?php echo FILTER_PROPERTY_STICKY;?>"<?php check_checked( gpc_string_to_bool( $g_filter[FILTER_PROPERTY_STICKY] ), true );?> />
		<?php
}

/**
 * print highlight changed field
 * @return void
 */
function print_filter_highlight_changed() {
	global $g_filter;
	?><!-- Highlight changed bugs -->
			<input type="text" name="<?php echo FILTER_PROPERTY_HIGHLIGHT_CHANGED;?>" size="3" maxlength="7" value="<?php echo $g_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED]?>" />
		<?php
}

/**
 * Print filter by date fields
 * @param boolean $p_hide_checkbox Hide data filter checkbox.
 * @return void
 */
function print_filter_do_filter_by_date( $p_hide_checkbox = false ) {
	global $g_filter;
?>
		<table cellspacing="0" cellpadding="0">
<?php
	$t_menu_disabled =  '';
	if( !$p_hide_checkbox ) {
?>
		<tr>
			<td colspan="2">
				<label>
					<input type="checkbox" id="use_date_filters" name="<?php
						echo FILTER_PROPERTY_FILTER_BY_DATE ?>"<?php
						check_checked( gpc_string_to_bool( $g_filter[FILTER_PROPERTY_FILTER_BY_DATE] ), true ) ?> />
					<?php echo lang_get( 'use_date_filters' )?>
				</label>
			</td>
		</tr>
<?php

		if( 'on' !== $g_filter[FILTER_PROPERTY_FILTER_BY_DATE] ) {
			$t_menu_disabled = ' disabled="disabled" ';
		}
	}
?>

		<!-- Start date -->
		<tr>
			<td>
			<?php echo lang_get( 'start_date_label' )?>
			</td>
			<td class="nowrap">
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
	foreach( $t_chars as $t_char ) {
		if( strcasecmp( $t_char, 'M' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_START_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $g_filter[FILTER_PROPERTY_START_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_START_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $g_filter[FILTER_PROPERTY_START_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_START_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $g_filter[FILTER_PROPERTY_START_YEAR] );
			print "</select>\n";
		}
	}
	?>
			</td>
		</tr>
		<!-- End date -->
		<tr>
			<td>
			<?php echo lang_get( 'end_date_label' )?>
			</td>
			<td>
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
	foreach( $t_chars as $t_char ) {
		if( strcasecmp( $t_char, 'M' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_END_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $g_filter[FILTER_PROPERTY_END_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_END_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $g_filter[FILTER_PROPERTY_END_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_END_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $g_filter[FILTER_PROPERTY_END_YEAR] );
			print "</select>\n";
		}
	}
	?>
			</td>
		</tr>
		</table>
		<?php
}

/**
 * print relationship fields
 * @return void
 */
function print_filter_relationship_type() {
	global $g_filter;
	$c_reltype_value = $g_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
	if( !$c_reltype_value ) {
		$c_reltype_value = -1;
	}
	relationship_list_box( $c_reltype_value, 'relationship_type', true );
	echo '<input type="text" name="', FILTER_PROPERTY_RELATIONSHIP_BUG, '" size="5" maxlength="10" value="', $g_filter[FILTER_PROPERTY_RELATIONSHIP_BUG], '" />';
}

/**
 * print tag fields
 * @return void
 */
function print_filter_tag_string() {
	if( !access_has_global_level( config_get( 'tag_view_threshold' ) ) ) {
		return;
	}

	global $g_filter;
	$t_tag_string = $g_filter[FILTER_PROPERTY_TAG_STRING];
	if( $g_filter[FILTER_PROPERTY_TAG_SELECT] != 0 && tag_exists( $g_filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
		$t_tag_string .= ( is_blank( $t_tag_string ) ? '' : config_get( 'tag_separator' ) );
		$t_tag_string .= tag_get_field( $g_filter[FILTER_PROPERTY_TAG_SELECT], 'name' );
	}
	?>
		<input type="hidden" id="tag_separator" value="<?php echo config_get( 'tag_separator' )?>" />
		<input type="text" name="<?php echo FILTER_PROPERTY_TAG_STRING;?>" id="<?php echo FILTER_PROPERTY_TAG_STRING;?>" size="40" value="<?php echo string_attribute( $t_tag_string )?>" />
		<select <?php echo helper_get_tab_index()?> name="<?php echo FILTER_PROPERTY_TAG_SELECT;?>" id="<?php echo FILTER_PROPERTY_TAG_SELECT;?>">
			<?php print_tag_option_list();?>
		</select>
		<?php
}

/**
 * print note reporter field
 * @return void
 */
function print_filter_note_user_id() {
	global $g_select_modifier, $g_filter, $f_view_type;
	?>
	<!-- BUGNOTE REPORTER -->
	<select<?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_NOTE_USER_ID;?>[]">
		<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_NOTE_USER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
		<?php if( access_has_project_level( config_get( 'view_handler_threshold' ) ) ) {?>
		<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_NOTE_USER_ID], META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
		<?php
			if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
				echo '<option value="' . META_FILTER_MYSELF . '"';
				check_selected( $g_filter[FILTER_PROPERTY_NOTE_USER_ID], META_FILTER_MYSELF );
				echo '>[' . lang_get( 'myself' ) . ']</option>';
			}

			print_note_option_list( $g_filter[FILTER_PROPERTY_NOTE_USER_ID] );
		}
	?>
	</select>
	<?php
}


/**
 * Print plugin filter fields as defined by MantisFilter objects.
 * @param string $p_field_name    Field name.
 * @param object $p_filter_object Filter object.
 * @return void
 */
function print_filter_plugin_field( $p_field_name, $p_filter_object ) {
	global $g_select_modifier, $g_filter, $f_view_type;

	$t_size = (int)$p_filter_object->size;

	switch( $p_filter_object->type ) {
		case FILTER_TYPE_STRING:
			echo '<input name="', string_attribute( $p_field_name ), '"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' value="',
				string_attribute( $g_filter[$p_field_name] ), '"/>';
			break;

		case FILTER_TYPE_INT:
			echo '<input name="', string_attribute( $p_field_name ), '"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' value="',
				(int)$g_filter[$p_field_name], '"/>';
			break;

		case FILTER_TYPE_BOOLEAN:
			echo '<input name="', string_attribute( $p_field_name ), '" type="checkbox"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), check_checked( (bool)$g_filter[$p_field_name] ) , '"/>';
			break;

		case FILTER_TYPE_MULTI_STRING:
			echo '<select', $g_select_modifier, ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' name="',
				string_attribute( $p_field_name ), '[]">', '<option value="', META_FILTER_ANY, '"',
				check_selected( $g_filter[$p_field_name], (string)META_FILTER_ANY ), '>[', lang_get( 'any' ), ']</option>';

			foreach( $p_filter_object->options() as $t_option_value => $t_option_name ) {
				echo '<option value="', string_attribute( $t_option_value ), '" ',
					check_selected( $g_filter[$p_field_name], $t_option_value, false ), '>',
					string_display_line( $t_option_name ), '</option>';
			}

			echo '</select>';
			break;

		case FILTER_TYPE_MULTI_INT:
			echo '<select', $g_select_modifier, ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' name="',
				string_attribute( $p_field_name ), '[]">', '<option value="', META_FILTER_ANY, '"',
				check_selected( $g_filter[$p_field_name], META_FILTER_ANY ), '>[', lang_get( 'any' ), ']</option>';

			foreach( $p_filter_object->options() as $t_option_value => $t_option_name ) {
				echo '<option value="', (int)$t_option_value, '" ',
					check_selected( $g_filter[$p_field_name], (int)$t_option_value ), '>',
					string_display_line( $t_option_name ), '</option>';
			}

			echo '</select>';
			break;

	}
}

/**
 * print custom fields
 * @param integer $p_field_id Custom field identifier.
 * @return void
 */
function print_filter_custom_field( $p_field_id ) {
	global $g_filter, $t_accessible_custom_fields_names, $t_accessible_custom_fields_types, $t_accessible_custom_fields_values, $t_accessible_custom_fields_ids, $g_select_modifier;

	$j = array_search( $p_field_id, $t_accessible_custom_fields_ids );
	if( $j === null || $j === false ) {
		?>
			<span style="color:red;font-weight:bold;">
				unknown custom filter (custom <?php echo $p_field_id;?>)
			</span>
		<?php
	} else if( isset( $t_accessible_custom_fields_names[$j] ) ) {
		if( $t_accessible_custom_fields_types[$j] == CUSTOM_FIELD_TYPE_DATE ) {
			print_filter_custom_field_date( $j, $p_field_id );
		} else if( $t_accessible_custom_fields_types[$j] == CUSTOM_FIELD_TYPE_TEXTAREA ) {
			echo '<input type="text" name="custom_field_', $p_field_id, '" size="10" value="" />';
		} else {
			echo '<select' . $g_select_modifier . ' name="custom_field_' . $p_field_id . '[]">';
			echo '<option value="' . META_FILTER_ANY . '"';
			check_selected( $g_filter['custom_fields'][$p_field_id], META_FILTER_ANY, false );
			echo '>[' . lang_get( 'any' ) . ']</option>';

			# don't show META_FILTER_NONE for enumerated types as it's not possible for them to be blank
			if( !in_array( $t_accessible_custom_fields_types[$j], array( CUSTOM_FIELD_TYPE_ENUM, CUSTOM_FIELD_TYPE_LIST, CUSTOM_FIELD_TYPE_MULTILIST ) ) ) {
				echo '<option value="' . META_FILTER_NONE . '"';
				check_selected( $g_filter['custom_fields'][$p_field_id], META_FILTER_NONE, false );
				echo '>[' . lang_get( 'none' ) . ']</option>';
			}
			if( is_array( $t_accessible_custom_fields_values[$j] ) ) {
				$t_max_length = config_get( 'max_dropdown_length' );
				foreach( $t_accessible_custom_fields_values[$j] as $t_item ) {
					if( ( strtolower( $t_item ) !== META_FILTER_ANY ) && ( strtolower( $t_item ) !== META_FILTER_NONE ) ) {
						echo '<option value="' . string_attribute( $t_item ) . '"';
						if( isset( $g_filter['custom_fields'][$p_field_id] ) ) {
							check_selected( $g_filter['custom_fields'][$p_field_id], $t_item, false );
						}
						echo '>' . string_attribute( string_shorten( $t_item, $t_max_length ) ) . '</option>' . "\n";
					}
				}
			}
			echo '</select>';
		}
	}
}

/**
 * Print sort fields
 * @return void
 */
function print_filter_show_sort() {
	global $g_filter;

	# get all of the displayed fields for sort, then drop ones that
	#  are not appropriate and translate the rest
	$t_fields = helper_get_columns_to_view();
	$t_n_fields = count( $t_fields );
	$t_shown_fields[''] = '';
	for( $i = 0;$i < $t_n_fields;$i++ ) {
		if( !in_array( $t_fields[$i], array( 'selection', 'edit', 'bugnotes_count', 'attachment_count' ) ) ) {
			if( strpos( $t_fields[$i], 'custom_' ) === 0 ) {
				$t_field_name = string_display( lang_get_defaulted( utf8_substr( $t_fields[$i], utf8_strlen( 'custom_' ) ) ) );
			} else {
				$t_field_name = string_get_field_name( $t_fields[$i] );
			}
			$t_shown_fields[$t_fields[$i]] = $t_field_name;
		}
	}
	$t_shown_dirs[''] = '';
	$t_shown_dirs['ASC'] = lang_get( 'bugnote_order_asc' );
	$t_shown_dirs['DESC'] = lang_get( 'bugnote_order_desc' );

	# get default values from filter structure
	$t_sort_fields = explode( ',', $g_filter[FILTER_PROPERTY_SORT_FIELD_NAME] );
	$t_dir_fields = explode( ',', $g_filter[FILTER_PROPERTY_SORT_DIRECTION] );
	if( !isset( $t_sort_fields[1] ) ) {
		$t_sort_fields[1] = '';
		$t_dir_fields[1] = '';
	}

	# if there are fields to display, show the dropdowns
	if( count( $t_fields ) > 0 ) {
		# display a primary and secondary sort fields
		echo '<select name="', FILTER_PROPERTY_SORT_FIELD_NAME, '_0">';
		foreach( $t_shown_fields as $t_key => $t_val ) {
			echo '<option value="' . $t_key . '"';
			check_selected( $t_key, $t_sort_fields[0] );
			echo '>' . $t_val . '</option>';
		}
		echo '</select>';

		echo '<select name="', FILTER_PROPERTY_SORT_DIRECTION, '_0">';
		foreach( $t_shown_dirs as $t_key => $t_val ) {
			echo '<option value="' . $t_key . '"';
			check_selected( $t_key, $t_dir_fields[0] );
			echo '>' . $t_val . '</option>';
		}
		echo '</select>';

		echo ', ';

		# for secondary sort
		echo '<select name="', FILTER_PROPERTY_SORT_FIELD_NAME, '_1">';
		foreach( $t_shown_fields as $t_key => $t_val ) {
			echo '<option value="' . $t_key . '"';
			check_selected( $t_key, $t_sort_fields[1] );
			echo '>' . $t_val . '</option>';
		}
		echo '</select>';
		echo '<select name="', FILTER_PROPERTY_SORT_DIRECTION, '_1">';
		foreach( $t_shown_dirs as $t_key => $t_val ) {
			echo '<option value="' . $t_key . '"';
			check_selected( $t_key, $t_dir_fields[1] );
			echo '>' . $t_val . '</option>';
		}
		echo '</select>';
	} else {
		echo lang_get_defaulted( 'last_updated' ) . lang_get( 'bugnote_order_desc' );
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_FIELD_NAME, '_1" value="last_updated" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_DIRECTION, '_1" value="DESC" />';
	}
}

/**
 * Print custom field date fields
 * @param integer $p_field_num Field number.
 * @param integer $p_field_id  Field identifier.
 * @return void
 */
function print_filter_custom_field_date( $p_field_num, $p_field_id ) {
	global $g_filter, $t_accessible_custom_fields_values;

	# Resort the values so there ordered numerically, they are sorted as strings otherwise which
	# may be wrong for dates before early 2001.
	if( is_array( $t_accessible_custom_fields_values[$p_field_num] ) ) {
		array_multisort( $t_accessible_custom_fields_values[$p_field_num], SORT_NUMERIC, SORT_ASC );
	}

	$t_sel_start_year = null;
	$t_sel_end_year = null;
	if( isset( $t_accessible_custom_fields_values[$p_field_num][0] ) ) {
		$t_sel_start_year = date( 'Y', $t_accessible_custom_fields_values[$p_field_num][0] );
	}
	$t_count = count( $t_accessible_custom_fields_values[$p_field_num] );
	if( isset( $t_accessible_custom_fields_values[$p_field_num][$t_count - 1] ) ) {
		$t_sel_end_year = date( 'Y', $t_accessible_custom_fields_values[$p_field_num][$t_count - 1] );
	}

	$t_start = date( 'U' );

	# Default to today in filters..
	$t_end = $t_start;

	if( isset( $g_filter['custom_fields'][$p_field_id][1] ) ) {
		$t_start_time = $g_filter['custom_fields'][$p_field_id][1];
	} else {
		$t_start_time = 0;
	}

	if( isset( $g_filter['custom_fields'][$p_field_id][2] ) ) {
		$t_end_time = $g_filter['custom_fields'][$p_field_id][2];
	} else {
		$t_end_time = 0;
	}

	$t_start_disable = true;
	$t_end_disable = true;

	# if $g_filter['custom_fields'][$p_field_id][0] is not set (ie no filter),
	# we will drop through the following switch and use the default values
	# above, so no need to check if stuff is set or not.
	switch( $g_filter['custom_fields'][$p_field_id][0] ) {
		case CUSTOM_FIELD_DATE_ANY:
		case CUSTOM_FIELD_DATE_NONE:
			break;
		case CUSTOM_FIELD_DATE_BETWEEN:
			$t_start_disable = false;
			$t_end_disable = false;
			$t_start = $t_start_time;
			$t_end = $t_end_time;
			break;
		case CUSTOM_FIELD_DATE_ONORBEFORE:
		case CUSTOM_FIELD_DATE_BEFORE:
			$t_start_disable = false;
			$t_start = $t_end_time;
			break;
		case CUSTOM_FIELD_DATE_ON:
		case CUSTOM_FIELD_DATE_AFTER:
		case CUSTOM_FIELD_DATE_ONORAFTER:
			$t_start_disable = false;
			$t_start = $t_start_time;
			break;
	}

	echo '<table cellspacing="0" cellpadding="0"><tr><td>' . "\n";
	echo '<select size="1" name="custom_field_' . $p_field_id . '_control">' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_ANY . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ANY );
	echo '>' . lang_get( 'any' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_NONE . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_NONE );
	echo '>' . lang_get( 'none' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_BETWEEN . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_BETWEEN );
	echo '>' . lang_get( 'between_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_ONORBEFORE . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ONORBEFORE );
	echo '>' . lang_get( 'on_or_before_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_BEFORE . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_BEFORE );
	echo '>' . lang_get( 'before_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_ON . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ON );
	echo '>' . lang_get( 'on_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_AFTER . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_AFTER );
	echo '>' . lang_get( 'after_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_ONORAFTER . '"';
	check_selected( (int)$g_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ONORAFTER );
	echo '>' . lang_get( 'on_or_after_date' ) . '</option>' . "\n";
	echo '</select>' . "\n";

	echo "</td></tr>\n<tr><td>";

	print_date_selection_set( 'custom_field_' . $p_field_id . '_start', config_get( 'short_date_format' ), $t_start, $t_start_disable, false, $t_sel_start_year, $t_sel_end_year );
	print "</td></tr>\n<tr><td>";
	print_date_selection_set( 'custom_field_' . $p_field_id . '_end', config_get( 'short_date_format' ), $t_end, $t_end_disable, false, $t_sel_start_year, $t_sel_end_year );
	print "</td></tr>\n</table>";
}

/**
 * Print project field
 * @return void
 */
function print_filter_project_id() {
	global $g_select_modifier, $g_filter;
	?>
		<!-- Project -->
		<select <?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_PROJECT_ID;?>[]">
			<option value="<?php echo META_FILTER_CURRENT ?>"
				<?php check_selected( $g_filter[FILTER_PROPERTY_PROJECT_ID], META_FILTER_CURRENT );?>>
				[<?php echo lang_get( 'current' )?>]
			</option>
			<?php print_project_option_list( $g_filter[FILTER_PROPERTY_PROJECT_ID] )?>
		</select>
		<?php
}

/**
 * Print filter match type selector
 * @return void
 */
function print_filter_match_type() {
	global $g_select_modifier, $g_filter;
?>
		<!-- Project -->
		<select <?php echo $g_select_modifier;?> name="<?php echo FILTER_PROPERTY_MATCH_TYPE;?>">
			<option value="<?php echo FILTER_MATCH_ALL?>" <?php check_selected( $g_filter[FILTER_PROPERTY_MATCH_TYPE], FILTER_MATCH_ALL );?>>[<?php echo lang_get( 'filter_match_all' )?>]</option>
			<option value="<?php echo FILTER_MATCH_ANY?>" <?php check_selected( $g_filter[FILTER_PROPERTY_MATCH_TYPE], FILTER_MATCH_ANY );?>>[<?php echo lang_get( 'filter_match_any' )?>]</option>
		</select>
		<?php
}

/**
 * Prints a multi-value filter field.
 * @param string $p_field_name  Field name.
 * @param mixed  $p_field_value Field value.
 * @return void
 */
function print_multivalue_field( $p_field_name, $p_field_value ) {
	$t_output = '';
	$t_any_found = false;

	if( count( $p_field_value ) == 0 ) {
		echo lang_get( 'any' );
	} else {
		$t_first_flag = true;

		$t_field_value = is_array( $p_field_value ) ? $p_field_value : array( $p_field_value );

		foreach( $t_field_value as $t_current ) {
			$t_current = stripslashes( $t_current );
			?>
				<input type="hidden" name="<?php echo string_attribute( $p_field_name )?>[]" value="<?php echo string_attribute( $t_current );?>" />
				<?php
				$t_this_string = '';

			if( ( ( $t_current == META_FILTER_ANY ) && ( is_numeric( $t_current ) ) ) || ( is_blank( $t_current ) ) ) {
				$t_any_found = true;
			} else {
				$t_this_string = string_display( $t_current );
			}

			if( $t_first_flag != true ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}

			$t_output .= $t_this_string;
		}

		if( true == $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

# ==========================================================================
# CACHING
# ==========================================================================

# @internal SECURITY NOTE: cache globals are initialized here to prevent them
# being spoofed if register_globals is turned on.
# We cache filter requests to reduce the number of SQL queries
# @global array $g_cache_filter
# @global array $g_cache_filter_db_filters
$g_cache_filter = array();
$g_cache_filter_db_filters = array();

/**
 *  Cache a filter row if necessary and return the cached copy
 *  If the second parameter is true (default), trigger an error
 *  if the filter can't be found.  If the second parameter is
 *  false, return false if the filter can't be found.
 * @param integer $p_filter_id      A filter identifier to retrieve.
 * @param boolean $p_trigger_errors Whether to trigger an error if the filter is not found.
 * @return array|boolean
 */
function filter_cache_row( $p_filter_id, $p_trigger_errors = true ) {
	global $g_cache_filter;

	if( isset( $g_cache_filter[$p_filter_id] ) ) {
		return $g_cache_filter[$p_filter_id];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {filters} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_filter_id ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		if( $p_trigger_errors ) {
			error_parameters( $p_filter_id );
			trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	$g_cache_filter[$p_filter_id] = $t_row;

	return $t_row;
}

/**
 * Clear the filter cache (or just the given id if specified)
 * @param integer $p_filter_id Filter id.
 * @return boolean
 */
function filter_clear_cache( $p_filter_id = null ) {
	global $g_cache_filter;

	if( null === $p_filter_id ) {
		$g_cache_filter = array();
	} else {
		unset( $g_cache_filter[(int)$p_filter_id] );
	}

	return true;
}

/**
 * Add a filter to the database for the current user
 * @param integer $p_project_id    Project id.
 * @param boolean $p_is_public     Whether filter is public or private.
 * @param string  $p_name          Filter name.
 * @param string  $p_filter_string Filter string.
 * @return integer
 */
function filter_db_set_for_current_user( $p_project_id, $p_is_public, $p_name, $p_filter_string ) {
	$t_user_id = auth_get_current_user_id();
	$c_project_id = (int)$p_project_id;

	# check that the user can save non current filters (if required)
	if( ( ALL_PROJECTS <= $c_project_id ) && ( !is_blank( $p_name ) ) && ( !access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) ) {
		return -1;
	}

	# ensure that we're not making this filter public if we're not allowed
	if( !access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
		$p_is_public = false;
	}

	# Do I need to update or insert this value?
	db_param_push();
	$t_query = 'SELECT id FROM {filters}
					WHERE user_id=' . db_param() . '
					AND project_id=' . db_param() . '
					AND name=' . db_param();
	$t_result = db_query( $t_query, array( $t_user_id, $c_project_id, $p_name ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		db_param_push();
		$t_query = 'UPDATE {filters}
					  SET is_public=' . db_param() . ',
						filter_string=' . db_param() . '
					  WHERE id=' . db_param();
		db_query( $t_query, array( $p_is_public, $p_filter_string, $t_row['id'] ) );

		return $t_row['id'];
	} else {
		db_param_push();
		$t_query = 'INSERT INTO {filters}
						( user_id, project_id, is_public, name, filter_string )
					  VALUES
						( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
		db_query( $t_query, array( $t_user_id, $c_project_id, $p_is_public, $p_name, $p_filter_string ) );

		# Recall the query, we want the filter ID
		db_param_push();
		$t_query = 'SELECT id
						FROM {filters}
						WHERE user_id=' . db_param() . '
						AND project_id=' . db_param() . '
						AND name=' . db_param();
		$t_result = db_query( $t_query, array( $t_user_id, $c_project_id, $p_name ) );

		if( $t_row = db_fetch_array( $t_result ) ) {
			return $t_row['id'];
		}

		return -1;
	}
}

/**
 * This function returns the filter string that is tied to the unique id parameter. If the user
 * does not have permission to see this filter, the function returns null
 * @param integer $p_filter_id A Filter identifier.
 * @param integer $p_user_id   A valid user identifier.
 * @return mixed
 */
function filter_db_get_filter( $p_filter_id, $p_user_id = null ) {
	global $g_cache_filter_db_filters;
	$c_filter_id = (int)$p_filter_id;

	if( isset( $g_cache_filter_db_filters[$p_filter_id] ) ) {
		if( $g_cache_filter_db_filters[$p_filter_id] === false ) {
			return null;
		}
		return $g_cache_filter_db_filters[$p_filter_id];
	}

	if( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	db_param_push();
	$t_query = 'SELECT * FROM {filters} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $c_filter_id ) );

	if( $t_row = db_fetch_array( $t_result ) ) {
		if( $t_row['user_id'] != $t_user_id ) {
			if( $t_row['is_public'] != true ) {
				return null;
			}
		}

		# check that the user has access to non current filters
		if( ( ALL_PROJECTS <= $t_row['project_id'] ) && ( !is_blank( $t_row['name'] ) ) && ( !access_has_project_level( config_get( 'stored_query_use_threshold', null, $t_user_id, $t_row['project_id'] ) ) ) ) {
			return null;
		}

		$g_cache_filter_db_filters[$p_filter_id] = $t_row['filter_string'];
		return $t_row['filter_string'];
	} else {
		$g_cache_filter_db_filters[$p_filter_id] = false;
		return false;
	}
}

/**
 * get current filter for given project and user
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A valid user identifier.
 * @return integer
 */
function filter_db_get_project_current( $p_project_id, $p_user_id = null ) {
	$c_project_id = (int)$p_project_id;
	$c_project_id = $c_project_id * -1;

	if( null === $p_user_id ) {
		$c_user_id = auth_get_current_user_id();
	} else {
		$c_user_id = (int)$p_user_id;
	}

	# we store current filters for each project with a special project index
	db_param_push();
	$t_query = 'SELECT *
				  FROM {filters}
				  WHERE user_id=' . db_param() . '
					AND project_id=' . db_param() . '
					AND name=' . db_param();
	$t_result = db_query( $t_query, array( $c_user_id, $c_project_id, '' ) );

	if( $t_row = db_fetch_array( $t_result ) ) {
		return $t_row['id'];
	}

	return null;
}

/**
 * Query for the filter name using the filter id
 * @param integer $p_filter_id Filter id.
 * @return string
 */
function filter_db_get_name( $p_filter_id ) {
	$c_filter_id = (int)$p_filter_id;

	db_param_push();
	$t_query = 'SELECT * FROM {filters} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $c_filter_id ) );

	if( $t_row = db_fetch_array( $t_result ) ) {
		if( $t_row['user_id'] != auth_get_current_user_id() ) {
			if( $t_row['is_public'] != true ) {
				return null;
			}
		}

		return $t_row['name'];
	}

	return null;
}

/**
 * Check if the current user has permissions to delete the stored query
 * @param integer $p_filter_id Filter id.
 * @return boolean
 */
function filter_db_can_delete_filter( $p_filter_id ) {
	$c_filter_id = (int)$p_filter_id;
	$t_user_id = auth_get_current_user_id();

	# Administrators can delete any filter
	if( user_is_administrator( $t_user_id ) ) {
		return true;
	}

	db_param_push();
	$t_query = 'SELECT id
				  FROM {filters}
				  WHERE id=' . db_param() . '
				  AND user_id=' . db_param() . '
				  AND project_id!=' . db_param();
	$t_result = db_query( $t_query, array( $c_filter_id, $t_user_id, -1 ) );

	if( db_result( $t_result ) > 0 ) {
		return true;
	}

	return false;
}

/**
 * Delete the filter specified by $p_filter_id
 * @param integer $p_filter_id Filter identifier.
 * @return boolean
 */
function filter_db_delete_filter( $p_filter_id ) {
	$c_filter_id = (int)$p_filter_id;

	if( !filter_db_can_delete_filter( $c_filter_id ) ) {
		return false;
	}

	db_param_push();
	$t_query = 'DELETE FROM {filters} WHERE id=' . db_param();
	db_query( $t_query, array( $c_filter_id ) );

	return true;
}

/**
 * Delete all the unnamed filters
 * @return void
 */
function filter_db_delete_current_filters() {
	$t_all_id = ALL_PROJECTS;

	db_param_push();
	$t_query = 'DELETE FROM {filters} WHERE project_id<=' . db_param() . ' AND name=' . db_param();
	db_query( $t_query, array( $t_all_id, '' ) );
}

/**
 * Note: any changes made in this function should be reflected in
 * mci_filter_db_get_available_queries())
 * @param integer $p_project_id A valid project identifier.
 * @param integer $p_user_id    A valid user identifier.
 * @return mixed
 */
function filter_db_get_available_queries( $p_project_id = null, $p_user_id = null ) {
	$t_overall_query_arr = array();

	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = (int)$p_project_id;
	}

	if( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = (int)$p_user_id;
	}

	# If the user doesn't have access rights to stored queries, just return
	if( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
		return $t_overall_query_arr;
	}

	# Get the list of available queries. By sorting such that public queries are
	# first, we can override any query that has the same name as a private query
	# with that private one
	db_param_push();
	$t_query = 'SELECT * FROM {filters}
					WHERE (project_id=' . db_param() . '
						OR project_id=0)
					AND name!=\'\'
					AND (is_public = ' . db_param() . '
						OR user_id = ' . db_param() . ')
					ORDER BY is_public DESC, name ASC';
	$t_result = db_query( $t_query, array( $t_project_id, true, $t_user_id ) );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_overall_query_arr[$t_row['id']] = $t_row['name'];
	}

	$t_overall_query_arr = array_unique( $t_overall_query_arr );
	asort( $t_overall_query_arr );

	return $t_overall_query_arr;
}

/**
 * Check that the given filter name does not exceed the maximum filter length
 * @param string $p_name Filter name.
 * @return boolean true when under max_length (64) and false when over
 */
function filter_name_valid_length( $p_name ) {
	if( utf8_strlen( $p_name ) > 64 ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Create a filter for getting issues assigned to the specified project and user that
 * are not yet resolved.
 *
 * @param integer $p_project_id The project id or ALL_PROJECTS.
 * @param integer $p_user_id    The user id or 0 to get unassigned issues.
 * @return mixed valid filter.
 */
function filter_create_assigned_to_unresolved( $p_project_id, $p_user_id ) {
	$t_filter = filter_get_default();

	if( $p_user_id == 0 ) {
		$t_filter[FILTER_PROPERTY_HANDLER_ID] = array( '0' => META_FILTER_NONE );
	} else {
		$t_filter[FILTER_PROPERTY_HANDLER_ID] = array( '0' => $p_user_id );
	}

	$t_bug_resolved_status_threshold = config_get( 'bug_resolved_status_threshold', null, $p_user_id, $p_project_id );
	$t_filter[FILTER_PROPERTY_HIDE_STATUS] = array( '0' => $t_bug_resolved_status_threshold );

	if( $p_project_id != ALL_PROJECTS ) {
		$t_filter[FILTER_PROPERTY_PROJECT_ID] = array( '0' => $p_project_id );
	}

	return filter_ensure_valid_filter( $t_filter );
}

/**
 * Create a filter for getting issues reported by the specified project and user.
 * @param integer $p_project_id The project id or ALL_PROJECTS.
 * @param integer $p_user_id    A valid user identifier.
 * @return array a valid filter.
 */
function filter_create_reported_by( $p_project_id, $p_user_id ) {
	$t_filter = filter_get_default();
	$t_filter[FILTER_PROPERTY_REPORTER_ID] = array( '0' => $p_user_id );

	if( $p_project_id != ALL_PROJECTS ) {
		$t_filter[FILTER_PROPERTY_PROJECT_ID] = array( '0' => $p_project_id );
	}

	return filter_ensure_valid_filter( $t_filter );
}

/**
 * Create a filter for getting issues monitored by the specified project and user.
 * @param integer $p_project_id The project id or ALL_PROJECTS.
 * @param integer $p_user_id    The user id.
 * @return array a valid filter.
 */
function filter_create_monitored_by( $p_project_id, $p_user_id ) {
	$t_filter = filter_get_default();

	if( $p_user_id == 0 ) {
		$t_filter[FILTER_PROPERTY_MONITOR_USER_ID] = array( '0' => META_FILTER_NONE );
	} else {
		$t_filter[FILTER_PROPERTY_MONITOR_USER_ID] = array( '0' => $p_user_id );
	}

	if( $p_project_id != ALL_PROJECTS ) {
		$t_filter[FILTER_PROPERTY_PROJECT_ID] = array( '0' => $p_project_id );
	}

	return filter_ensure_valid_filter( $t_filter );
}
