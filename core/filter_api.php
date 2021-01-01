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
 * @uses session_api.php
 * @uses string_api.php
 * @uses tag_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 * @uses filter_form_api.php
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
require_api( 'session_api.php' );
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );
require_api( 'filter_form_api.php' );

# @global array $g_filter	Filter array for the filter in use through view_all_bug_page
# This gets initialized on filter load
# @TODO cproensa	We should move towards not relying on this variable, as we reuse filter logic
# to allow operating on other filter different that the one in use for view_all_bug_page.
# For example: manage and edit stored filters.
$g_filter = null;


# ==========================================================================
# CACHING
# ==========================================================================
# We cache filter requests to reduce the number of SQL queries

# @global array $g_cache_filter_db_rows
# indexed by filter_id, contains the filter rows as read from db table
$g_cache_filter_db_rows = array();

# @global array $g_cache_filter_subquery
# indexed by a hash of the filter array, contains a prebuilt BugFilterQuery object
$g_cache_filter_subquery = array();

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
					foreach( $t_plugin_filter_array as $t_filter_item ) {
						if( is_object( $t_filter_item ) && $t_filter_item instanceof MantisFilter ) {
							$t_filter_object = $t_filter_item;
						} elseif( class_exists( $t_filter_item ) && is_subclass_of( $t_filter_item, 'MantisFilter' ) ) {
							$t_filter_object = new $t_filter_item();
						} else {
							continue;
						}
						$t_filter_name = mb_strtolower( $t_plugin . '_' . $t_filter_object->field );
						$s_field_array[$t_filter_name] = $t_filter_object;
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

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ) ) {
		$t_query[] = filter_encode_field_and_value(
			FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED,
			$p_custom_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ? 'on' : 'off' );

		# The start and end dates are only applicable if filter by date is set.
		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_DATE_SUBMITTED_START_DAY, $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_DATE_SUBMITTED_END_DAY, $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH, $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH, $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR, $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR, $p_custom_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] );
		}
	}

	if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ) ) {
		$t_query[] = filter_encode_field_and_value(
			FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE,
			$p_custom_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ? 'on' : 'off' );

		# The start and end dates are only applicable if filter by date is set.
		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_LAST_UPDATED_START_DAY, $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_LAST_UPDATED_END_DAY, $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_LAST_UPDATED_START_MONTH, $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_LAST_UPDATED_END_MONTH, $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_LAST_UPDATED_START_YEAR, $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] );
		}

		if( !filter_field_is_any( $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] ) ) {
			$t_query[] = filter_encode_field_and_value( FILTER_PROPERTY_LAST_UPDATED_END_YEAR, $p_custom_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] );
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
		$t_query_str = implode( '&', $t_query );
		$t_url = config_get_global( 'path' ) . 'search.php?' . $t_query_str;
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

	return implode( '&', $t_query_array );
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
 * Make sure the filter array contains all the fields. If any field is missing,
 * create it with a default value.
 * @param array $p_filter_arr Input filter array
 * @return array Processed filter array
 */
function filter_ensure_fields( array $p_filter_arr ) {
	# Fill missing filter properties with defaults
	if( isset( $p_filter_arr['_view_type'] ) ) {
		$t_filter_default = filter_get_default_array( $p_filter_arr['_view_type'] );
	} else {
		$t_filter_default = filter_get_default_array();
	}

	foreach( $t_filter_default as $t_key => $t_default_value ) {
		if( !isset( $p_filter_arr[$t_key] ) ) {
			$p_filter_arr[$t_key] = $t_default_value;
		}
	}

	# Veryfy custom fields
	foreach( $t_filter_default['custom_fields'] as $t_cfid => $t_cf_data ) {
		if( !isset( $p_filter_arr['custom_fields'][$t_cfid] ) ) {
			$p_filter_arr['custom_fields'][$t_cfid] = $t_cf_data;
		}
	}

	return $p_filter_arr;
}

/**
 * A wrapper to compare filter version syntax
 * Note: Currently, filter versions have this syntax: "vN",  * where N is an integer number.
 * @param string $p_version1    First version number
 * @param string $p_version2    Second version number
 * @param string $p_operator    Comparison test, if provided. As expected by version_compare()
 * @return mixed	As returned by version_compare()
 */
function filter_version_compare( $p_version1, $p_version2, $p_operator = null ) {
	return version_compare( $p_version1, $p_version2, $p_operator );
}

/**
 * Upgrade a filter array to the current filter structure, by converting properties
 * that have changed from previous filter versions
 * @param array $p_filter	Filter array to upgrade
 * @return array	Updgraded filter array
 */
function filter_version_upgrade( array $p_filter ) {
	# This is a stub for future version upgrades

	# After conversions are made, update filter value to current version
	$p_filter['_version'] = FILTER_VERSION;
	return $p_filter;
}

/**
 * Make sure that our filters are entirely correct and complete (it is possible that they are not).
 * We need to do this to cover cases where we don't have complete control over the filters given.
 * @param array $p_filter_arr	A filter array
 * @return array	Validated filter array
 */
function filter_ensure_valid_filter( array $p_filter_arr ) {
	if( !isset( $p_filter_arr['_version'] ) ) {
		$p_filter_arr['_version'] = FILTER_VERSION;
	}

	if( filter_version_compare( $p_filter_arr['_version'], FILTER_VERSION, '<' ) ) {
		$p_filter_arr = filter_version_upgrade( $p_filter_arr );
	}

	$p_filter_arr = filter_ensure_fields( $p_filter_arr );

	$t_config_view_filters = config_get( 'view_filters' );
	$t_view_type = $p_filter_arr['_view_type'];
	if( ADVANCED_ONLY == $t_config_view_filters ) {
		$t_view_type = FILTER_VIEW_TYPE_ADVANCED;
	}
	if( SIMPLE_ONLY == $t_config_view_filters ) {
		$t_view_type = FILTER_VIEW_TYPE_SIMPLE;
	}
	if( !in_array( $t_view_type, array( FILTER_VIEW_TYPE_SIMPLE, FILTER_VIEW_TYPE_ADVANCED ) ) ) {
		$t_view_type = filter_get_default_view_type();
	}
	$p_filter_arr['_view_type'] = $t_view_type;

	$t_sort_fields = explode( ',', $p_filter_arr[FILTER_PROPERTY_SORT_FIELD_NAME] );
	$t_dir_fields = explode( ',', $p_filter_arr[FILTER_PROPERTY_SORT_DIRECTION] );
	# both arrays should be equal length, just in case
	$t_sort_fields_count = min( count( $t_sort_fields ), count( $t_dir_fields ) );

	# clean up sort fields, remove invalid columns
	$t_new_sort_array = array();
	$t_new_dir_array = array();
	$t_all_columns = columns_get_all_active_columns();
	for( $ix = 0; $ix < $t_sort_fields_count; $ix++ ) {
		if( isset( $t_sort_fields[$ix] ) ) {
			$t_column = $t_sort_fields[$ix];
			# check that the column name exist
			if( !in_array( $t_column, $t_all_columns ) ) {
				continue;
			}
			# check that it has not been already used
			if( in_array( $t_column, $t_new_sort_array ) ) {
				continue;
			}
			# check that it is sortable
			if( !column_is_sortable( $t_column ) ) {
				continue;
			}
			$t_new_sort_array[] = $t_column;

			# if there is no dir field, set a dummy value
			if( isset( $t_dir_fields[$ix] ) ) {
				$t_dir = $t_dir_fields[$ix];
			} else {
				$t_dir = '';
			}
			# normalize sort_dir value
			$t_dir = ( $t_dir == 'ASC' ) ? 'ASC' : 'DESC';
			$t_new_dir_array[] = $t_dir;
		}
	}

	if( count( $t_new_sort_array ) > 0 ) {
		$p_filter_arr[FILTER_PROPERTY_SORT_FIELD_NAME] = implode( ',', $t_new_sort_array );
		$p_filter_arr[FILTER_PROPERTY_SORT_DIRECTION] = implode( ',', $t_new_dir_array );
	} else {
		$p_filter_arr[FILTER_PROPERTY_SORT_FIELD_NAME] = filter_get_default_property( FILTER_PROPERTY_SORT_FIELD_NAME, $t_view_type );
		$p_filter_arr[FILTER_PROPERTY_SORT_DIRECTION] = filter_get_default_property( FILTER_PROPERTY_SORT_DIRECTION, $t_view_type );
	}

	# Validate types for values.

	# helper function to validate types
	$t_function_validate_type = function( $p_value, $p_type ) {
		$t_value = stripslashes( $p_value );
		if( ( $t_value === 'any' ) || ( $t_value === '[any]' ) ) {
			$t_value = META_FILTER_ANY;
		}
		if( ( $t_value === 'none' ) || ( $t_value === '[none]' ) ) {
			$t_value = META_FILTER_NONE;
		}
		# Ensure the filter property has the right type - see #20087
		switch( $p_type ) {
			case 'string' :
			case 'int' :
				settype( $t_value, $p_type );
				break;
		}
		return $t_value;
	};

	# Validate properties that must not be arrays
	$t_single_value_list = array(
		FILTER_PROPERTY_VIEW_STATE => 'int',
		FILTER_PROPERTY_RELATIONSHIP_TYPE => 'int',
		FILTER_PROPERTY_RELATIONSHIP_BUG => 'int',
	);
	foreach( $t_single_value_list as $t_field_name => $t_field_type ) {
		$t_value = $p_filter_arr[$t_field_name];
		if( is_array( $t_value ) ) {
			if( count( $t_value ) > 0 ) {
				$p_filter_arr[$t_field_name] = reset( $t_value );
			} else {
				$p_filter_arr[$t_field_name] = filter_get_default_property( $t_field_name, $t_view_type );
			}
		}
		$p_filter_arr[$t_field_name] = $t_function_validate_type( $p_filter_arr[$t_field_name], $t_field_type );
	}

	# Validate properties that must be arrays, and the type of its elements
	$t_array_values_list = array(
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
		FILTER_PROPERTY_PLATFORM => 'string',
		FILTER_PROPERTY_OS => 'string',
		FILTER_PROPERTY_OS_BUILD => 'string',
		FILTER_PROPERTY_PROJECT_ID => 'int'
	);
	foreach( $t_array_values_list as $t_multi_field_name => $t_multi_field_type ) {
		if( !is_array( $p_filter_arr[$t_multi_field_name] ) ) {
			$p_filter_arr[$t_multi_field_name] = array(
				$p_filter_arr[$t_multi_field_name],
			);
		}
		$t_checked_array = array();
		foreach( $p_filter_arr[$t_multi_field_name] as $t_filter_value ) {
			$t_checked_array[] = $t_function_validate_type( $t_filter_value, $t_multi_field_type );
		}
		$p_filter_arr[$t_multi_field_name] = $t_checked_array;
	}

	$t_custom_fields = custom_field_get_ids();
	if( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			if( isset( $p_filter_arr['custom_fields'][$t_cfid]) ) {
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

	# If view_type is advanced, and hide_status is present, modify status array
	# to remove hidden status. This may happen after switching from simple to advanced.
	# Then, remove hide_status property, as it does not apply to advanced filter
	if( $p_filter_arr['_view_type'] == FILTER_VIEW_TYPE_ADVANCED
			&& !filter_field_is_none( $p_filter_arr[FILTER_PROPERTY_HIDE_STATUS] ) ) {
		if( filter_field_is_any( $p_filter_arr[FILTER_PROPERTY_STATUS] ) ) {
			$t_selected_status_array = MantisEnum::getValues( config_get( 'status_enum_string' ) );
		} else {
			$t_selected_status_array = $p_filter_arr[FILTER_PROPERTY_STATUS];
		}
		$t_hide_status = $p_filter_arr[FILTER_PROPERTY_HIDE_STATUS][0];
		$t_new_status_array = array();
		foreach( $t_selected_status_array as $t_status ) {
			if( $t_status < $t_hide_status ) {
				$t_new_status_array[] = $t_status;
			}
		}
		# If there is no status left, reset the status property to "any"
		if( empty( $t_new_status_array ) ) {
			$t_new_status_array[] = META_FILTER_ANY;
		}
		$p_filter_arr[FILTER_PROPERTY_STATUS] = $t_new_status_array;
		$p_filter_arr[FILTER_PROPERTY_HIDE_STATUS] = META_FILTER_NONE;
	}

	#If view_type is simple, resolve conflicts between show_status and hide_status
	if( $p_filter_arr['_view_type'] == FILTER_VIEW_TYPE_SIMPLE
			&& !filter_field_is_none( $p_filter_arr[FILTER_PROPERTY_HIDE_STATUS] ) ) {
		# get array of hidden status ids
		$t_all_status = MantisEnum::getValues( config_get( 'status_enum_string' ) );
		$t_hidden_status = $p_filter_arr[FILTER_PROPERTY_HIDE_STATUS][0];
		$t_hidden_status_array = array();
		foreach( $t_all_status as $t_status ) {
			if( $t_status >= $t_hidden_status ) {
				$t_hidden_status_array[] = $t_status;
			}
		}
		# remove hidden status from show_status property array
		# note that this will keep the "any" meta value, if present
		$t_show_status_array = array_diff( $p_filter_arr[FILTER_PROPERTY_STATUS], $t_hidden_status_array );
		# If there is no status left, reset the status property previous values, and remove hide_status
		if( empty( $t_show_status_array ) ) {
			$t_show_status_array = $p_filter_arr[FILTER_PROPERTY_STATUS];
			$p_filter_arr[FILTER_PROPERTY_HIDE_STATUS] = META_FILTER_NONE;
		}
		$p_filter_arr[FILTER_PROPERTY_STATUS] = $t_show_status_array;
	}

	# validate relationship fields
	if( !(
		$p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_BUG] > 0
		|| $p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_BUG] == META_FILTER_ANY
		|| $p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_BUG] == META_FILTER_NONE
		) ) {
		$p_filter_arr[FILTER_PROPERTY_RELATIONSHIP_BUG] = filter_get_default_property( FILTER_PROPERTY_RELATIONSHIP_BUG, $t_view_type );
	}

	# all of our filter values are now guaranteed to be there, and correct.
	return $p_filter_arr;
}

/**
 * Get a filter array with default values
 * Optional view type parameter is used to initialize some fields properly,
 * as some may differ in the default content.
 * @param string $p_view_type	FILTER_VIEW_TYPE_SIMPLE or FILTER_VIEW_TYPE_ADVANCED
 * @return array Filter array with default values
 */
function filter_get_default_array( $p_view_type = null ) {
	static $t_cache_default_array = array();

	$t_default_view_type = filter_get_default_view_type();
	if( !in_array( $p_view_type, array( FILTER_VIEW_TYPE_SIMPLE, FILTER_VIEW_TYPE_ADVANCED ) ) ) {
		$p_view_type = $t_default_view_type;
	}

	# this function is called multiple times from filter api so return a cached value if possible
	if( isset( $t_cache_default_array[$p_view_type] ) ) {
		return $t_cache_default_array[$p_view_type];
	}

	$t_default_show_changed = config_get( 'default_show_changed' );
	$t_meta_filter_any_array = array( META_FILTER_ANY );

	$t_config_view_filters = config_get( 'view_filters' );
	if( ADVANCED_ONLY == $t_config_view_filters ) {
		$t_view_type = FILTER_VIEW_TYPE_ADVANCED;
	} elseif( SIMPLE_ONLY == $t_config_view_filters ) {
		$t_view_type = FILTER_VIEW_TYPE_SIMPLE;
	} else {
		$t_view_type = $p_view_type;
	}

	if( $t_view_type == FILTER_VIEW_TYPE_SIMPLE ) {
		$t_hide_status_default = config_get( 'hide_status_default' );
	} else {
		$t_hide_status_default = META_FILTER_NONE;
	}

	$t_filter = array(
		'_version' => FILTER_VERSION,
		'_view_type' => $t_view_type,
		FILTER_PROPERTY_CATEGORY_ID => $t_meta_filter_any_array,
		FILTER_PROPERTY_SEVERITY => $t_meta_filter_any_array,
		FILTER_PROPERTY_STATUS => $t_meta_filter_any_array,
		FILTER_PROPERTY_HIGHLIGHT_CHANGED => $t_default_show_changed,
		FILTER_PROPERTY_REPORTER_ID => $t_meta_filter_any_array,
		FILTER_PROPERTY_HANDLER_ID => $t_meta_filter_any_array,
		FILTER_PROPERTY_PROJECT_ID => array( META_FILTER_CURRENT ),
		FILTER_PROPERTY_RESOLUTION => $t_meta_filter_any_array,
		FILTER_PROPERTY_BUILD => $t_meta_filter_any_array,
		FILTER_PROPERTY_VERSION => $t_meta_filter_any_array,
		FILTER_PROPERTY_HIDE_STATUS => array( $t_hide_status_default ),
		FILTER_PROPERTY_MONITOR_USER_ID => $t_meta_filter_any_array,
		FILTER_PROPERTY_SORT_FIELD_NAME => 'last_updated',
		FILTER_PROPERTY_SORT_DIRECTION => 'DESC',
		FILTER_PROPERTY_ISSUES_PER_PAGE => config_get( 'default_limit_view' ),
		FILTER_PROPERTY_MATCH_TYPE => FILTER_MATCH_ALL,
		FILTER_PROPERTY_PLATFORM => $t_meta_filter_any_array,
		FILTER_PROPERTY_OS => $t_meta_filter_any_array,
		FILTER_PROPERTY_OS_BUILD => $t_meta_filter_any_array,
		FILTER_PROPERTY_FIXED_IN_VERSION => $t_meta_filter_any_array,
		FILTER_PROPERTY_TARGET_VERSION => $t_meta_filter_any_array,
		FILTER_PROPERTY_PROFILE_ID => $t_meta_filter_any_array,
		FILTER_PROPERTY_PRIORITY => $t_meta_filter_any_array,
		FILTER_PROPERTY_NOTE_USER_ID => $t_meta_filter_any_array,
		FILTER_PROPERTY_STICKY => gpc_string_to_bool( config_get( 'show_sticky_issues' ) ),
		FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED => false,
		FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH => date( 'm' ),
		FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH => date( 'm' ),
		FILTER_PROPERTY_DATE_SUBMITTED_START_DAY => 1,
		FILTER_PROPERTY_DATE_SUBMITTED_END_DAY => date( 'd' ),
		FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR => date( 'Y' ),
		FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR => date( 'Y' ),
		FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE => false,
		FILTER_PROPERTY_LAST_UPDATED_START_MONTH => date( 'm' ),
		FILTER_PROPERTY_LAST_UPDATED_END_MONTH => date( 'm' ),
		FILTER_PROPERTY_LAST_UPDATED_START_DAY => 1,
		FILTER_PROPERTY_LAST_UPDATED_END_DAY => date( 'd' ),
		FILTER_PROPERTY_LAST_UPDATED_START_YEAR => date( 'Y' ),
		FILTER_PROPERTY_LAST_UPDATED_END_YEAR => date( 'Y' ),
		FILTER_PROPERTY_SEARCH => '',
		FILTER_PROPERTY_VIEW_STATE => META_FILTER_ANY,
		FILTER_PROPERTY_TAG_STRING => '',
		FILTER_PROPERTY_TAG_SELECT => 0,
		FILTER_PROPERTY_RELATIONSHIP_TYPE => BUG_REL_ANY,
		FILTER_PROPERTY_RELATIONSHIP_BUG => META_FILTER_ANY,
	);

	# initialize plugin filters
	$t_plugin_filters = filter_get_plugin_filters();
	foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
			switch( $t_filter_object->type ) {
				case FILTER_TYPE_STRING:
					$t_filter[$t_field_name] = $t_filter_object->default;
					break;
				case FILTER_TYPE_INT:
					$t_filter[$t_field_name] = (int)$t_filter_object->default;
					break;
				case FILTER_TYPE_BOOLEAN:
					$t_filter[$t_field_name] = (bool)$t_filter_object->default;
					break;
				case FILTER_TYPE_MULTI_STRING:
					$t_filter[$t_field_name] = array( (string)META_FILTER_ANY );
					break;
				case FILTER_TYPE_MULTI_INT:
					$t_filter[$t_field_name] = array( META_FILTER_ANY );
					break;
				default:
					$t_filter[$t_field_name] = (string)META_FILTER_ANY;
			}

		if( !$t_filter_object->validate( $t_filter[$t_field_name] ) ) {
			$t_filter[$t_field_name] = $t_filter_object->default;
		}
	}

	$t_custom_fields = custom_field_get_ids();
	# @@@ (thraxisp) This should really be the linked ids, but we don't know the project
	$f_custom_fields_data = array();
	if( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			$f_custom_fields_data[$t_cfid] = array( (string)META_FILTER_ANY );
		}
	}
	$t_filter['custom_fields'] = $f_custom_fields_data;

	$t_cache_default_array[$p_view_type] = $t_filter;
	return $t_filter;
}

/**
 * Returns the default view type for filters
 * @return string Default view type
 */
function filter_get_default_view_type() {
	if( ADVANCED_DEFAULT == config_get( 'view_filters' ) ) {
		return FILTER_VIEW_TYPE_ADVANCED;
	} else {
		return FILTER_VIEW_TYPE_SIMPLE;
	}
}

/**
 * Returns the default value for a filter property.
 * Relies on filter_get_default_array() to get a defaulted filter.
 * @param string $p_filter_property The requested filter property name
 * @param string $p_view_type Optional, view type for the defaulted filter (simple/advanced)
 * @return mixed The property default value, or null if it doesn't exist
 */
function filter_get_default_property( $p_filter_property, $p_view_type = null ) {
	$t_default_array = filter_get_default_array( $p_view_type );
	if( isset( $t_default_array[$p_filter_property] ) ) {
		return $t_default_array[$p_filter_property];
	} else {
		return null;
	}
}

/**
 *  Get the standard filter that is to be used when no filter was previously saved.
 *  When creating specific filters, this can be used as a basis for the filter, where
 *  specific entries can be overridden.
 * @return mixed
 */
function filter_get_default() {
	# Create empty array, validation will fill it with defaults
	$t_filter = array();
	return filter_ensure_valid_filter( $t_filter );
}

/**
 * Deserialize filter string
 * Expected strings have this format: "<version>#<json string>" where:
 * - <version> is the versio number of the filter structure used. See constant FILTER_VERSION
 * - # is a separator
 * - <json string> is the json encoded filter array.
 * @param string $p_serialized_filter Serialized filter string.
 * @return mixed $t_filter array
 * @see filter_ensure_valid_filter
 */
function filter_deserialize( $p_serialized_filter ) {
	if( is_blank( $p_serialized_filter ) ) {
		return false;
	}

	#@TODO cproensa, we could accept a pure json array, without version prefix
	# in this case, the filter version field inside the array is to be used
	# and if not present, set the current filter version

	# check filter version mark
	$t_setting_arr = explode( '#', $p_serialized_filter, 2 );
	$t_version_string = $t_setting_arr[0];
	if( in_array( $t_version_string, array( 'v1', 'v2', 'v3', 'v4' ) ) ) {
		# these versions can't be salvaged, they are too old to update
		return false;
	} elseif( in_array( $t_version_string, array( 'v5', 'v6', 'v7', 'v8' ) ) ) {
		# filters from v5 onwards should cope with changing filter indices dynamically
		$t_filter_array = unserialize( $t_setting_arr[1] );
	} else {
		# filters from v9 onwards are stored as json
		$t_filter_array = json_decode( $t_setting_arr[1], /* assoc array */ true );
	}

	# If the unserialez data is not an array, the some error happened, eg, invalid format
	if( !is_array( $t_filter_array ) ) {
		return false;
	}

	# Set the filter version that was loaded in the array
	$t_filter_array['_version'] = $t_setting_arr[0];

	# If upgrade in filter content is needed, it will be done in filter_ensure_valid_filter()
	return filter_ensure_valid_filter( $t_filter_array );
}

/**
 * Creates a serialized filter with the correct format
 * @param array $p_filter_array Filter array to be serialized
 * @return string Serialized filter string
 */
function filter_serialize( $p_filter_array ) {
	$t_cookie_version = FILTER_VERSION;
	$p_filter_array = filter_clean_runtime_properties( $p_filter_array );
	$t_settings_serialized = json_encode( $p_filter_array );
	$t_settings_string = $t_cookie_version . '#' . $t_settings_serialized;
	return $t_settings_string;
}

/**
 * Get the filter db row $p_filter_id
 * using the cached row if it's available
 * @global array $g_cache_filter_db_rows
 * @param integer $p_filter_id      A filter identifier to look up in the database.
 * @return array|boolean	The row of filter data as stored in db table, or false if does not exist
 */
function filter_get_row( $p_filter_id ) {
	global $g_cache_filter_db_rows;

	if( !isset( $g_cache_filter_db_rows[$p_filter_id] ) ) {
		filter_cache_rows( array($p_filter_id) );
	}

	$t_row = $g_cache_filter_db_rows[$p_filter_id];
	return $t_row;
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
		$t_filter = filter_ensure_valid_filter( $p_custom_filter );
	}

	# build a filter query, here for counting results
	$t_filter_query = new BugFilterQuery(
			$t_filter,
			array(
				'query_type' => BugFilterQuery::QUERY_TYPE_LIST,
				'project_id' => $p_project_id,
				'user_id' => $p_user_id,
				'use_sticky' => $p_show_sticky
				)
			);
	$p_bug_count = $t_filter_query->get_bug_count();
	if( 0 == $p_bug_count ) {
		return array();
	}

	# Calculate pagination
	$p_per_page = filter_per_page( $t_filter, $p_bug_count, $p_per_page );
	$p_page_count = filter_page_count( $p_bug_count, $p_per_page );
	$p_page_number = filter_valid_page_number( $p_page_number, $p_page_count );
	$t_offset = filter_offset( $p_page_number, $p_per_page );

	$t_filter_query->set_limit( $p_per_page );
	$t_filter_query->set_offset( $t_offset );
	# Execute query
	$t_rows = $t_filter_query->fetch_all();
	$t_bug_id_array = array_column( $t_rows, 'id' );

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
 * Cache the filter results with bugnote stats for later use
 * @param array $p_rows             Results of the filter query.
 * @param array $p_id_array_lastmod Array of bug ids.
 * @return array
 */
function filter_cache_result( array $p_rows, array $p_id_array_lastmod ) {
	$t_stats = bug_get_bugnote_stats_array( $p_id_array_lastmod );
	$t_rows = array();
	foreach( $p_rows as $t_row ) {
		if( array_key_exists( $t_row['id'], $t_stats ) ) {
			$t_rows[] = bug_row_to_object( bug_cache_database_result( $t_row, $t_stats[$t_row['id']] ) );
		} else {
			$t_rows[] = bug_row_to_object( bug_cache_database_result( $t_row ) );
		}
	}
	return $t_rows;
}

/**
 * Prints the filter selection area for both the bug list view screen and
 * the bug list print screen. This function was an attempt to make it easier to
 * add new filters and rearrange them on screen for both pages.
 * @return void
 */
function filter_draw_selection_area() {
	$t_form_name_suffix = '_open';

	$t_filter = current_user_get_bug_filter();
	$t_filter = filter_ensure_valid_filter( $t_filter === false ? array() : $t_filter );

	$t_view_type = $t_filter['_view_type'];

	?>
	<div class="col-md-12 col-xs-12">
	<div class="filter-box">

	<?php
	$t_stored_queries_arr = filter_db_get_available_queries();
	$t_is_temporary = filter_is_temporary( $t_filter );
	$t_tmp_filter_param = $t_is_temporary ? '&filter=' . filter_get_temporary_key( $t_filter ) : '';
	$t_can_persist = filter_user_can_use_persistent( auth_get_current_user_id() );

	$t_collapse_block = is_collapsed( 'filter' );
	$t_block_css = $t_collapse_block ? 'collapsed' : '';
	$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

	# further use of this icon must be inlined to avoid spaces in rendered html
	$t_temporary_icon_html = ( $t_is_temporary && $t_can_persist ) ?
		icon_get( 'fa-clock-o', 'fa-xs-top' )
		: '';
	$t_url_reset_filter = 'view_all_set.php?type=' . FILTER_ACTION_RESET;
	$t_url_persist_filter = 'view_all_set.php?temporary=n' . $t_tmp_filter_param . '&set_project_id=' . helper_get_current_project();
	?>

		<div id="filter" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
<?php
				print_icon(
					'fa-filter',
					'ace-icon',
					$t_temporary_icon_html ? lang_get( 'temporary_filter' ) : '',
					$t_temporary_icon_html
				);
				echo lang_get( 'filters' )
?>
			</h4>

			<div class="widget-toolbar">
				<?php
					$t_view_filters = config_get('view_filters');

					if( ( ( SIMPLE_ONLY != $t_view_filters ) && ( ADVANCED_ONLY != $t_view_filters ) ) ||
						access_has_project_level( config_get( 'create_permalink_threshold' ) ) ||
						count( $t_stored_queries_arr ) > 0 ) { ?>
					<div class="widget-menu">
						<a href="#" data-action="settings" data-toggle="dropdown">
							<?php print_icon( 'fa-bars', 'ace-icon bigger-125'); ?>
						</a>
						<ul class="dropdown-menu dropdown-menu-right dropdown-yellow dropdown-caret dropdown-closer">
							<?php
							$t_url = config_get( 'use_dynamic_filters' )
								? 'view_all_set.php?type=' . FILTER_ACTION_PARSE_ADD . $t_tmp_filter_param . '&view_type='
								: 'view_filters_page.php?view_type=';
							filter_print_view_type_toggle( $t_url, $t_filter['_view_type'] );

							if( access_has_project_level( config_get( 'create_permalink_threshold' ) ) ) {
								# Add CSRF protection, see #22702
								$t_permalink_url = urlencode( filter_get_url( $t_filter ) )
									. form_security_param( 'permalink' );
								echo '<li>';
								echo '<a href="permalink_page.php?url=' . $t_permalink_url . '">';
								print_icon( 'fa-link', 'ace-icon' );
								echo '&#160;&#160;' . lang_get( 'create_filter_link' );
								echo '</a>';
								echo '</li>';
							}
							if( count( $t_stored_queries_arr ) > 0 ) {
								echo '<li>';
								echo '<a href="manage_filter_page.php">';
								print_icon( 'fa-wrench', 'ace-icon' );
								echo '&#160;&#160;' . lang_get( 'open_queries' );
								echo '</a>';
								echo '</li>';
							}
							if( $t_is_temporary && $t_can_persist ) {
								echo '<li>';
								echo '<a href="' . $t_url_persist_filter . '">';
								print_icon( 'fa-thumb-tack', 'ace-icon' );
								echo '&#160;&#160;' . lang_get( 'set_as_persistent_filter' );
								echo '</a>';
								echo '</li>';
							}
							?>
						</ul>
					</div>
				<?php } ?>
				<a id="filter-toggle" data-action="collapse" href="#">
					<?php print_icon( $t_block_icon, '1 ace-icon bigger-125' ); ?>
				</a>
			</div>
			<div id="filter-bar-queries" class="widget-toolbar no-border" style="display: <?php echo $t_collapse_block ? 'block' : 'none' ?>">
				<div class="widget-menu margin-left-8">
				<?php
				if( $t_is_temporary && $t_can_persist ) {
				?>
					<a class="btn btn-primary btn-white btn-round btn-xs"
					   title="<?php echo lang_get( 'set_as_persistent_filter' ) ?>"
					   href="<?php echo $t_url_persist_filter ?>">
						<?php print_icon( 'fa-thumb-tack', 'ace-icon' ); ?>
					</a>
				<?php
				}
				?>
					<a class="btn btn-primary btn-white btn-round btn-xs"
					   title="<?php echo lang_get( 'reset_query' ) ?>"
					   href="<?php echo $t_url_reset_filter ?>">
						<?php print_icon( 'fa-times', 'ace-icon' ); ?>
					</a>
				</div>
				<?php if( count( $t_stored_queries_arr ) > 0 ) { ?>
				<div class="widget-menu hidden-xs">
					<form method="post" action="view_all_set.php">
						<input type="hidden" name="type" value="<?php echo FILTER_ACTION_LOAD ?>" />
						<select id="filter-bar-query-id" class="input-xs">
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
					</form>
				</div>
				<?php } ?>
				<div class="widget-menu margin-right-8">

					<form method="post" action="view_all_set.php">
						<input type="hidden" name="type" value="<?php echo FILTER_ACTION_PARSE_ADD ?>" />
						<input id="filter-bar-search-txt" type="text" size="16" class="input-xs"
							   placeholder="<?php echo lang_get( 'search' ) ?>"
							   name="<?php echo FILTER_PROPERTY_SEARCH ?>"
							   value="<?php echo string_attribute( $t_filter[FILTER_PROPERTY_SEARCH] ); ?>" />
						<button id="filter-bar-search-btn" type="submit" name="filter_submit" class="btn btn-primary btn-white btn-round btn-xs"
								title="<?php echo lang_get( 'filter_button' ) ?>">
							<?php print_icon( 'fa-search', 'ace-icon' ); ?>
						</button>
					</form>

				</div>
			</div>
		</div>

		<div class="widget-body">
			<div class="widget-toolbox padding-4 clearfix">
				<div class="btn-toolbar">
					<div class="form-inline">
						<div class="btn-group pull-left">
	<?php
	# Top left toolbar for buttons

	$t_url_reset_filter = 'view_all_set.php?type=' . FILTER_ACTION_RESET;
	if( $t_is_temporary && $t_can_persist ) {
	?>
							<a class="btn btn-sm btn-primary btn-white btn-round" href="<?php echo $t_url_persist_filter ?>">
								<?php print_icon( 'fa-thumb-tack', 'ace-icon' ); ?>
								<?php echo lang_get( 'persist' ) ?>
							</a>
	<?php
	}
	?>
							<a class="btn btn-sm btn-primary btn-white btn-round" href="<?php echo $t_url_reset_filter ?>">
								<?php print_icon( 'fa-times', 'ace-icon' ); ?>
								<?php echo lang_get( 'reset' ) ?>
							</a>

	<?php
	if( access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) {
		$t_url_save_filter = 'query_store_page.php';
		if( filter_is_temporary( $t_filter ) ) {
			$t_url_save_filter .= '?filter=' . filter_get_temporary_key( $t_filter );
		}
	?>
							<a class="btn btn-sm btn-primary btn-white btn-round" href="<?php echo $t_url_save_filter ?>">
								<?php print_icon( 'fa-floppy-o', 'ace-icon' ); ?>
								<?php echo lang_get( 'save' ) ?>
							</a>
	<?php
	}
	?>
						</div>

	<?php
	if( count( $t_stored_queries_arr ) > 0 ) { ?>
						<form id="filter-queries-form" class="form-inline pull-left padding-left-8"  method="get" name="list_queries<?php echo $t_form_name_suffix;?>" action="view_all_set.php">
							<?php # CSRF protection not required here - form does not result in modifications?>
							<input type="hidden" name="type" value="<?php echo FILTER_ACTION_LOAD ?>" />
							<label><?php echo lang_get( 'load' ) ?>
								<select class="input-s" name="source_query_id">
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
							</label>
						</form>
	<?php
	}
	?>
					</div>
				</div>
			</div>

			<form method="post" name="filters<?php echo $t_form_name_suffix?>" id="filters_form<?php echo $t_form_name_suffix?>" action="view_all_set.php">
				<?php # CSRF protection not required here - form does not result in modifications ?>
				<input type="hidden" name="type" value="<?php echo FILTER_ACTION_PARSE_NEW ?>" />
				<?php
				if( filter_is_temporary( $t_filter ) ) {
					echo '<input type="hidden" name="filter" value="' . filter_get_temporary_key( $t_filter ) . '" />';
				}
				?>
				<input type="hidden" name="view_type" value="<?php echo $t_view_type?>" />

			<div class="widget-main no-padding">
				<div class="table-responsive">
					<?php
					filter_form_draw_inputs( $t_filter, true, false, 'view_filters_page.php', false /* don't show search */ );
					?>
				</div>
			</div>

			<div class="widget-toolbox padding-8 clearfix">
				<div class="btn-toolbar pull-left">
					<div class="form-inline">
						<?php echo '<input type="text" id="filter-search-txt" class="input-sm" size="16" name="', FILTER_PROPERTY_SEARCH, '"'
							, ' placeholder="' . lang_get( 'search' ) . '" value="', string_attribute( $t_filter[FILTER_PROPERTY_SEARCH] ), '" />';
						?>
						<input type="submit" class="btn btn-primary btn-sm btn-white btn-round no-float" name="filter_submit" value="<?php echo lang_get( 'filter_button' )?>" />
					</div>
				</div>
			</div>

			</form>
		</div>
		</div>
	</div>
	</div>
<?php
}

function filter_cache_rows( array $p_filter_ids ) {
	global $g_cache_filter_db_rows;

	if( empty( $p_filter_ids ) ) {
		return;
	}
	$t_ids_not_found = array();
	$t_params = array();
	$t_sql_params = array();
	db_param_push();
	foreach( $p_filter_ids as $t_id ) {
		$t_sql_params[] = db_param();
		$t_params[] = (int)$t_id;
		$t_ids_not_found[$t_id] = $t_id;
	}
	$t_query = 'SELECT * FROM {filters} WHERE id IN ('
			. implode( ',', $t_sql_params ) . ')';
	$t_result = db_query( $t_query, $t_params );
	while( $t_row = db_fetch_array( $t_result ) ) {
		$g_cache_filter_db_rows[$t_row['id']] = $t_row;
		unset( $t_ids_not_found[$t_row['id']] );
	}
	foreach( $t_ids_not_found as $t_id ) {
		$g_cache_filter_db_rows[$t_id] = false;
	}
}

/**
 * Clear the filter cache (or just the given id if specified)
 * @param integer $p_filter_id Filter id.
 * @return boolean
 */
function filter_clear_cache( $p_filter_id = null ) {
	global $g_cache_filter_db_rows;

	if( null === $p_filter_id ) {
		$g_cache_filter_db_rows = array();
	} else {
		unset( $g_cache_filter_db_rows[(int)$p_filter_id] );
	}

	return true;
}

/**
 * Update a filter identified by its id
 * Some parameters are optional and only will be updated if provided
 * Note that values are not validated
 * @param int $p_filter_id Filter id
 * @param string $p_filter_string Filter string in custom serialized format
 * @param int $p_project_id
 * @param bool $p_is_public
 * @param string $p_name
 */
function filter_db_update_filter( $p_filter_id, $p_filter_string, $p_project_id = null, $p_is_public = null, $p_name = null ) {
	db_param_push();
	$t_params = array();
	$t_query = 'UPDATE {filters} SET filter_string=' . db_param();
	$t_params[] = $p_filter_string;
	if( null !== $p_project_id ) {
		$t_query .= ', project_id=' . db_param();
		$t_params[] = (int)$p_project_id;
	}
	if( null !== $p_is_public ) {
		$t_query .= ', is_public=' . db_param();
		$t_params[] = (bool)$p_is_public;
	}
	if( null !== $p_name ) {
		$t_query .= ', name=' . db_param();
		$t_params[] = $p_name;
	}
	$t_query .= ' WHERE id=' . db_param();
	$t_params[] = (int)$p_filter_id;
	db_query( $t_query, $t_params );
}

/**
 * Add a filter to the database.
 * This function does not perform any validation on access or inserted data
 *
 * @param string $p_filter_string  Filter string in filter-serialized format
 * @param integer $p_user_id      User id owner of the filter
 * @param integer $p_project_id   Project id associated to the filter
 * @param string $p_name          Name of the filter
 * @param boolean $p_is_public    Boolean flag to set the filter public
 * @return integer	The id of the created row
 */
function filter_db_create_filter( $p_filter_string, $p_user_id, $p_project_id, $p_name, $p_is_public ) {
	$c_project_id = (int)$p_project_id;
	$c_user_id = $p_user_id;
	$c_is_public = (bool)$p_is_public;

	db_param_push();
	$t_query = 'INSERT INTO {filters} ( user_id, project_id, is_public, name, filter_string )'
			. ' VALUES ( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	$t_params = array( $c_user_id, $c_project_id, $c_is_public, $p_name, $p_filter_string );
	db_query( $t_query, $t_params );

	return db_insert_id( db_get_table( 'filters' ) );
}

/**
 * Updates the default filter for a project and user.
 * We only can have one filter of this kind, per project and user.
 * These special filters are saved in database with a negative project id
 * to differentiate from standard named filters.
 *
 * Note: currently this filter is how the current filter in use is persisted
 * This means: the last used filter settings, for each project, are saved here.
 * @TODO cproensa, theres some suggestions to clean this up:
 * - working filters should not be tracked in database, at least not as unique per user
 * - include a UI functionality to allow setting/clearing these default filters
 * - ideally, the storage should be cleaner: either separated from standard filters
 *     or use a proper field in the table, instead of relying on the negative project id
 *
 * @param array $p_filter        Filter array
 * @param integer $p_project_id  Project id
 * @param integer $p_user_id     User id
 * @return integer	The filter id that was updated or created
 */
function filter_set_project_filter( array $p_filter, $p_project_id = null, $p_user_id = null ) {
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

	$p_filter_string = filter_serialize( $p_filter );
	# Check if a row already exists
	$t_id = filter_db_get_project_current( $t_project_id, $p_user_id );
	if( $t_id ) {
		# A row already esxists
		filter_db_update_filter( $t_id, $p_filter_string );
	} else {
		# Must create a row
		$t_db_project_id = -1 * $t_project_id;
		$t_id = filter_db_create_filter( $p_filter_string, $t_user_id, $t_db_project_id, '', false );
	}
	return $t_id;
}

/**
 * This function returns the filter string that is tied to the unique id parameter. If the user
 * does not have permission to see this filter, the function returns null
 * @param integer $p_filter_id A Filter identifier.
 * @param integer $p_user_id   A valid user identifier.
 * @return mixed
 */
function filter_db_get_filter_string( $p_filter_id, $p_user_id = null ) {
	$c_filter_id = (int)$p_filter_id;

	if( !filter_is_accessible( $c_filter_id, $p_user_id ) ) {
		return null;
	}

	$t_filter_row = filter_get_row( $c_filter_id );
	return $t_filter_row['filter_string'];
}

/**
 * get current filter for given project and user
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A valid user identifier.
 * @return integer
 */
function filter_db_get_project_current( $p_project_id = null, $p_user_id = null ) {
	if( null === $p_project_id ) {
		$c_project_id = helper_get_current_project();
	} else {
		$c_project_id = (int)$p_project_id;
	}
	if( null === $p_user_id ) {
		$c_user_id = auth_get_current_user_id();
	} else {
		$c_user_id = (int)$p_user_id;
	}

	# we store current filters for each project with a special project index
	$t_filter_project_id = $c_project_id * -1;

	db_param_push();
	$t_query = 'SELECT id FROM {filters} WHERE user_id = ' . db_param()
			. ' AND project_id = ' . db_param() . ' AND name = ' . db_param();
	$t_result = db_query( $t_query, array( $c_user_id, $t_filter_project_id, '' ) );

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

	$t_filter_row = filter_get_row( $c_filter_id );
	if( !$t_filter_row ) {
		return null;
	}

	if( $t_filter_row['user_id'] != auth_get_current_user_id() ) {
		if( $t_filter_row['is_public'] != true ) {
			return null;
		}
	}

	return $t_filter_row['name'];
}

/**
 * Check if the current user has permissions to delete the stored query
 * @param integer $p_filter_id Filter id.
 * @param integer|null User id or null for logged in user.
 * @return boolean
 */
function filter_db_can_delete_filter( $p_filter_id, $p_user_id = null ) {
	$c_filter_id = (int)$p_filter_id;
	$t_user_id = $p_user_id != null ? $p_user_id : auth_get_current_user_id();

	# Administrators can delete any filter
	if( user_is_administrator( $t_user_id ) ) {
		return true;
	}

	$t_filter_row = filter_get_row( $c_filter_id );
	if( $t_filter_row
		&& $t_filter_row['user_id'] == $t_user_id
		&& $t_filter_row['project_id'] >= 0	) {
		return true;
	} else {
		return false;
	}
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
 * Returns stored filters based on a combination of linked project, owner user and public status.
 * Any parameter can be defaulted to null, to get all results for that field
 * @param integer $p_project_id		Project id linked to the filter
 * @param integer $p_user_id		User id, meaning filter owner
 * @param boolean $p_public			Public flag for filter
 * @return array	Array of filter ids and names
 */
function filter_db_get_named_filters( $p_project_id = null, $p_user_id = null, $p_public = null ) {
	db_param_push();
	$t_params = array();
	$t_query = 'SELECT id, name FROM {filters} WHERE project_id >= ' . db_param();
	$t_params[] = 0;

	# build where clauses
	if( null !== $p_project_id ) {
		$t_query .= ' AND project_id = ' . db_param();
		$t_params[] = (int)$p_project_id;
	}
	if( null !== $p_user_id ) {
		$t_query .= ' AND user_id = ' . db_param();
		$t_params[] = (int)$p_user_id;
	}
	if( null !== $p_public ) {
		$t_query .= ' AND is_public = ' . db_param();
		# cast $p_public to strict true/false values
		$t_params[] = $p_public ? true : false;
	}

	$t_result = db_query( $t_query, $t_params );

	$t_query_arr = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_query_arr[$t_row['id']] = $t_row['name'];
	}
	return $t_query_arr;
}

/**
 * Get the list of available filters.
 *
 * @param integer|null $p_project_id A valid project identifier or null for current project.
 * @param integer|null $p_user_id    A valid user identifier or null for logged in user.
 * @param boolean $p_filter_by_project Only return filters associated with specified project id or All Projects, otherwise return all filters for user.
 * @param boolean $p_return_names_only true: return names of filters, false: return structures with filter header information.
 * @return array Array of filters.
 */
function filter_db_get_available_queries( $p_project_id = null, $p_user_id = null, $p_filter_by_project = true, $p_return_names_only = true ) {
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
		return array();
	}

	# Get the list of available queries. By sorting such that public queries are
	# first, we can override any query that has the same name as a private query
	# with that private one
	db_param_push();

	if( $p_filter_by_project ) {
		$t_query = 'SELECT * FROM {filters}
			WHERE (project_id = ' . db_param() . '
				OR project_id = 0)
			AND name != \'\'
			AND (is_public = ' . db_param() . '
				OR user_id = ' . db_param() . ')
			ORDER BY is_public DESC, name ASC';

		$t_result = db_query( $t_query, array( $t_project_id, true, $t_user_id ) );
	} else {
		$t_project_ids = user_get_all_accessible_projects( $t_user_id );
		$t_project_ids[] = ALL_PROJECTS;

		$t_query = 'SELECT * FROM {filters}
			WHERE project_id in (' . implode( ',', $t_project_ids ) . ')
			AND name != \'\'
			AND (is_public = ' . db_param() . '
				OR user_id = ' . db_param() . ')
			ORDER BY is_public DESC, name ASC';

		$t_result = db_query( $t_query, array( true, $t_user_id ) );
	}
	
	$t_filters = array();

	# first build the id=>name array
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_filters[$t_row['id']] = $t_row['name'];
	}
	filter_cache_rows( array_keys( $t_filters ) );

	if( $p_return_names_only ) {
		asort( $t_filters );
		return $t_filters;
	}

	# build an extended array of name=>{filter data}
	$t_filter_data = array();
	foreach( $t_filters as $t_filter_id => $t_filter_name ) {
		$t_row = array();
		$t_filter_obj = filter_get( $t_filter_id );
		if( !$t_filter_obj ) {
			continue;
		}

		$t_row = filter_get_row( $t_filter_id );
		$t_row['criteria'] = $t_filter_obj;
		$t_row['url'] = filter_get_url( $t_filter_obj );
		$t_filter_data[$t_filter_name] = $t_row;
	}
	return $t_filter_data;
}

/**
 * Check that the given filter name does not exceed the maximum filter length
 * @param string $p_name Filter name.
 * @return boolean true when under max_length (64) and false when over
 */
function filter_name_valid_length( $p_name ) {
	if( mb_strlen( $p_name ) > 64 ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Create a filter for getting issues modified in the last N days.
 * @param integer $p_days Number of days counting from today
 * @param array $p_filter Add the filter conditions over this filter array. Return a new one if null
 * @return array Filter array
 */
function filter_create_recently_modified( $p_days, $p_filter = null ) {
	if( null === $p_filter ) {
		$p_filter = filter_get_default();
		# This filter overrides default "hide status" property
		$p_filter[FILTER_PROPERTY_HIDE_STATUS] = META_FILTER_NONE;
	}
	$c_days = (int)$p_days;
	$p_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] = true;
	$t_date = new DateTime('today');
	$p_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] = $t_date->format( 'j' );
	$p_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] = $t_date->format( 'n' );
	$p_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] = $t_date->format( 'Y' );
	$t_date->modify( '-' . $c_days . ' days' );
	$p_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] = $t_date->format( 'j' );
	$p_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] = $t_date->format( 'n' );
	$p_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] = $t_date->format( 'Y' );
	return filter_ensure_valid_filter( $p_filter );
}

/**
 * Create a filter for getting any issues without restrictions
 * @return mixed A valid filter.
 */
function filter_create_any() {
	$t_filter = filter_get_default();

	$t_filter[FILTER_PROPERTY_HIDE_STATUS] = META_FILTER_NONE;

	return filter_ensure_valid_filter( $t_filter );
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

/**
 * Performs the reading of parameters from get/post.
 * If a filter array is passed as parameter, the read parameters will be appended,
 * or everride existing ones.
 * If no filter array is used as parameter, a default one will be used.
 * @param array $p_filter An existing filter array
 * @return array The resulting filter array
 */
function filter_gpc_get( array $p_filter = null ) {
	# Get or copy the view_type first as it's needed to get proper defaults
	$f_view_type = gpc_get_string( 'view_type', null );
	if( null === $f_view_type && is_array( $p_filter ) && isset( $p_filter['_view_type'] ) ) {
		$f_view_type = $p_filter['_view_type'];
	}

	if( null === $p_filter ) {
		$t_filter = filter_get_default_array( $f_view_type );
	} else {
		$t_filter = filter_ensure_fields( $p_filter );
	}

	# these are all possibly multiple selections for advanced filtering
	# If a single value is provided, it will be normalized to an array with 'filter_ensure_valid_filter()'

	$f_show_category = gpc_get( FILTER_PROPERTY_CATEGORY_ID, $t_filter[FILTER_PROPERTY_CATEGORY_ID] );
	$f_platform = gpc_get( FILTER_PROPERTY_PLATFORM, $t_filter[FILTER_PROPERTY_PLATFORM] );
	$f_os = gpc_get( FILTER_PROPERTY_OS, $t_filter[FILTER_PROPERTY_OS] );
	$f_os_build = gpc_get( FILTER_PROPERTY_OS_BUILD, $t_filter[FILTER_PROPERTY_OS_BUILD] );
	$f_show_severity = gpc_get( FILTER_PROPERTY_SEVERITY, $t_filter[FILTER_PROPERTY_SEVERITY] );
	$f_show_status = gpc_get( FILTER_PROPERTY_STATUS, $t_filter[FILTER_PROPERTY_STATUS] );
	$f_hide_status = gpc_get( FILTER_PROPERTY_HIDE_STATUS, $t_filter[FILTER_PROPERTY_HIDE_STATUS] );
	$f_reporter_id = gpc_get( FILTER_PROPERTY_REPORTER_ID, $t_filter[FILTER_PROPERTY_REPORTER_ID] );
	$f_handler_id = gpc_get( FILTER_PROPERTY_HANDLER_ID, $t_filter[FILTER_PROPERTY_HANDLER_ID] );
	$f_project_id = gpc_get( FILTER_PROPERTY_PROJECT_ID, $t_filter[FILTER_PROPERTY_PROJECT_ID] );
	$f_show_resolution = gpc_get( FILTER_PROPERTY_RESOLUTION, $t_filter[FILTER_PROPERTY_RESOLUTION] );
	$f_show_build = gpc_get( FILTER_PROPERTY_BUILD, $t_filter[FILTER_PROPERTY_BUILD] );
	$f_show_version = gpc_get( FILTER_PROPERTY_VERSION, $t_filter[FILTER_PROPERTY_VERSION] );
	$f_fixed_in_version = gpc_get( FILTER_PROPERTY_FIXED_IN_VERSION, $t_filter[FILTER_PROPERTY_FIXED_IN_VERSION] );
	$f_target_version = gpc_get( FILTER_PROPERTY_TARGET_VERSION, $t_filter[FILTER_PROPERTY_TARGET_VERSION] );
	$f_show_profile = gpc_get( FILTER_PROPERTY_PROFILE_ID, $t_filter[FILTER_PROPERTY_PROFILE_ID] );
	$f_show_priority = gpc_get( FILTER_PROPERTY_PRIORITY, $t_filter[FILTER_PROPERTY_PRIORITY] );
	$f_user_monitor = gpc_get( FILTER_PROPERTY_MONITOR_USER_ID, $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] );
	$f_note_user_id = gpc_get( FILTER_PROPERTY_NOTE_USER_ID, $t_filter[FILTER_PROPERTY_NOTE_USER_ID] );

	$f_match_type = gpc_get_int( FILTER_PROPERTY_MATCH_TYPE, $t_filter[FILTER_PROPERTY_MATCH_TYPE] );

	# these are only single values, even when doing advanced filtering
	$f_per_page = gpc_get_int( FILTER_PROPERTY_ISSUES_PER_PAGE, $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] );
	$f_highlight_changed = gpc_get_int( FILTER_PROPERTY_HIGHLIGHT_CHANGED, $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] );
	$f_sticky_issues = gpc_get_bool( FILTER_PROPERTY_STICKY, $t_filter[FILTER_PROPERTY_STICKY] );

	# This sort parameter is a set of comma separated values, and can be an array of parameters.
	# sort="c1,c2" as used by permalinks
	# sort[]="c1" sort[]="c2" as used by filter form
	gpc_make_array( FILTER_PROPERTY_SORT_FIELD_NAME );
	$f_sort_array = gpc_get_string_array( FILTER_PROPERTY_SORT_FIELD_NAME, array() );

	# This sort parameter is an incremental column addition to current sort set.
	# Only one column/dir, which is added to the front.
	$f_sort_add = gpc_get_string( FILTER_PROPERTY_SORT_FIELD_NAME . '_add', null );

	if( !empty( $f_sort_array ) ) {
		gpc_make_array( FILTER_PROPERTY_SORT_DIRECTION );
		$f_dir_array = gpc_get_string_array( FILTER_PROPERTY_SORT_DIRECTION, array() );
		$t_new_sort_array = array();
		$t_new_dir_array = array();
		# evaluate each parameter, checks that "dir" may be omitted in order to avoid shifting subsequent parameters
		$t_count = count( $f_sort_array );
		for( $ix = 0; $ix < $t_count; $ix++ ) {
			$t_param_columns = explode( ',', $f_sort_array[$ix] );
			if( isset( $f_dir_array[$ix] ) ) {
				$t_param_dirs = explode( ',', $f_dir_array[$ix] );
			} else {
				$t_param_dirs = array();
			}
			# fill the gaps with dummy string, they will be defaulted by ensure_valid_filter
			if( count( $t_param_dirs ) < count( $t_param_columns ) ) {
				$t_param_dirs = array_pad( $t_param_dirs, count( $t_param_columns ), '' );
			}
			$t_new_sort_array = array_merge( $t_new_sort_array, $t_param_columns );
			$t_new_dir_array = array_merge( $t_new_dir_array, $t_param_dirs );
		}
		$f_sort = implode( ',', $t_new_sort_array );
		$f_dir = implode( ',', $t_new_dir_array );
	} elseif( null !== $f_sort_add ) {
		# this parameter has to be pushed in front of current sort set
		$f_dir_add = gpc_get_string( FILTER_PROPERTY_SORT_DIRECTION . '_add', '' );
		# Plain concatenation. Empty fields, or extra commas will be cleaned by ensure_valid_filter
		$f_sort = $f_sort_add . ',' . $t_filter[FILTER_PROPERTY_SORT_FIELD_NAME];
		$f_dir = $f_dir_add . ',' . $t_filter[FILTER_PROPERTY_SORT_DIRECTION];
	} else {
		# use the defaults
		$f_sort = $t_filter[FILTER_PROPERTY_SORT_FIELD_NAME];
		$f_dir = $t_filter[FILTER_PROPERTY_SORT_DIRECTION];
	}

	# date values
	# creation date
	$f_do_filter_by_date	= gpc_get_bool( FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED, $t_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] );
	$f_start_month			= gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] );
	$f_end_month			= gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] );
	$f_start_day			= gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_START_DAY, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] );
	$f_end_day				= gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_END_DAY, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] );
	$f_start_year			= gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] );
	$f_end_year				= gpc_get_int( FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] );
	# last_updated date values
	$f_do_filter_by_last_updated_date	= gpc_get_bool( FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE, $t_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] );
	$f_last_updated_start_month			= gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_START_MONTH, $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] );
	$f_last_updated_end_month			= gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_END_MONTH, $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] );
	$f_last_updated_start_day			= gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_START_DAY, $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] );
	$f_last_updated_end_day				= gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_END_DAY, $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] );
	$f_last_updated_start_year			= gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_START_YEAR, $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] );
	$f_last_updated_end_year			= gpc_get_int( FILTER_PROPERTY_LAST_UPDATED_END_YEAR, $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] );

	$f_search				= gpc_get_string( FILTER_PROPERTY_SEARCH, $t_filter[FILTER_PROPERTY_SEARCH] );
	$f_view_state			= gpc_get_int( FILTER_PROPERTY_VIEW_STATE, $t_filter[FILTER_PROPERTY_VIEW_STATE] );

	$f_tag_string			= gpc_get_string( FILTER_PROPERTY_TAG_STRING, $t_filter[FILTER_PROPERTY_TAG_STRING] );
	$f_tag_select			= gpc_get_int( FILTER_PROPERTY_TAG_SELECT, $t_filter[FILTER_PROPERTY_TAG_SELECT] );

	# plugin filter updates
	$t_plugin_filters = filter_get_plugin_filters();
	$t_filter_input = array();

	foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
		switch( $t_filter_object->type ) {
			case FILTER_TYPE_STRING:
				$t_filter_input[$t_field_name] = gpc_get_string( $t_field_name, $t_filter[$t_field_name] );
				break;

			case FILTER_TYPE_INT:
				$t_filter_input[$t_field_name] = gpc_get_int( $t_field_name, $t_filter[$t_field_name] );
				break;

			case FILTER_TYPE_BOOLEAN:
				$t_filter_input[$t_field_name] = gpc_get_bool( $t_field_name, $t_filter[$t_field_name]);
				break;

			case FILTER_TYPE_MULTI_STRING:
				$t_filter_input[$t_field_name] = gpc_get_string_array( $t_field_name, $t_filter[$t_field_name] );
				break;

			case FILTER_TYPE_MULTI_INT:
				$t_filter_input[$t_field_name] = gpc_get_int_array( $t_field_name, $t_filter[$t_field_name] );
				break;
		}
	}

	# custom field updates
	$t_custom_fields 		= custom_field_get_ids(); # @todo (thraxisp) This should really be the linked ids, but we don't know the project
	$f_custom_fields_data 	= $t_filter['custom_fields'];
	if( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			if( custom_field_type( $t_cfid ) == CUSTOM_FIELD_TYPE_DATE ) {

				# check if gpc parameters are present, otherwise skip parsing.
				if( !gpc_isset( 'custom_field_' . $t_cfid . '_control' ) ) {
					continue;
				}

				$f_custom_fields_data[$t_cfid] = array();

				# Get date control property
				$t_control = gpc_get_string( 'custom_field_' . $t_cfid . '_control', null );
				$f_custom_fields_data[$t_cfid][0] = $t_control;

				$t_one_day = 86399;
				# Get start date. If there is a timestamp input provided, use it,
				# otherwise, look for individual date parts
				$f_start_date = gpc_get( 'custom_field_' . $t_cfid . '_start_timestamp', null );
				if( null !== $f_start_date ) {
					$t_start_date = (int)$f_start_date;
					$t_start = $t_start_date;
				} else {
					$t_year = gpc_get_int( 'custom_field_' . $t_cfid . '_start_year', null );
					$t_month = gpc_get_int( 'custom_field_' . $t_cfid . '_start_month', null );
					$t_day = gpc_get_int( 'custom_field_' . $t_cfid . '_start_day', null );
					$t_start_date = mktime( 0, 0, 0, $t_month, $t_day, $t_year );
					# calculate correct timestamps
					$t_start = 1;
					switch( $t_control ) {
						case CUSTOM_FIELD_DATE_ANY:
						case CUSTOM_FIELD_DATE_NONE:
						case CUSTOM_FIELD_DATE_ONORBEFORE:
						case CUSTOM_FIELD_DATE_BEFORE:
							break ;
						case CUSTOM_FIELD_DATE_BETWEEN:
							$t_start = $t_start_date;
							break ;
						case CUSTOM_FIELD_DATE_ON:
							$t_start = $t_start_date;
							break;
						case CUSTOM_FIELD_DATE_AFTER:
							$t_start = $t_start_date + $t_one_day - 1;
							break;
						case CUSTOM_FIELD_DATE_ONORAFTER:
							$t_start = $t_start_date;
							break;
					}
				}
				$f_custom_fields_data[$t_cfid][1] = $t_start;

				# Get end date. If there is a timestamp input provided, use it,
				# otherwise, look for individual date parts
				$f_end_date = gpc_get( 'custom_field_' . $t_cfid . '_end_timestamp', null );
				if( null !== $f_end_date ) {
					$t_end_date = (int)$f_end_date;
					$t_end = $t_end_date;
				} else {
					$t_year = gpc_get_int( 'custom_field_' . $t_cfid . '_end_year', null );
					$t_month = gpc_get_int( 'custom_field_' . $t_cfid . '_end_month', null );
					$t_day = gpc_get_int( 'custom_field_' . $t_cfid . '_end_day', null );
					$t_end_date = mktime( 0, 0, 0, $t_month, $t_day, $t_year );
					# calculate correct timestamps
					$t_end = 1;
					switch( $t_control ) {
						case CUSTOM_FIELD_DATE_ANY:
						case CUSTOM_FIELD_DATE_NONE:
							break ;
						case CUSTOM_FIELD_DATE_BETWEEN:
							$t_end = $t_end_date + $t_one_day - 1;
							break ;
						case CUSTOM_FIELD_DATE_ONORBEFORE:
							$t_end = $t_start_date + $t_one_day - 1;
							break;
						case CUSTOM_FIELD_DATE_BEFORE:
							$t_end = $t_start_date;
							break ;
						case CUSTOM_FIELD_DATE_ON:
							$t_end = $t_start_date + $t_one_day - 1;
							break;
						case CUSTOM_FIELD_DATE_AFTER:
							$t_end = 2147483647; # Some time in 2038, max value of a signed int.
							break;
						case CUSTOM_FIELD_DATE_ONORAFTER:
							$t_end = 2147483647; # Some time in 2038, max value of a signed int.
							break;
					}
				}
				$f_custom_fields_data[$t_cfid][2] = $t_end;

			} else {

				# check if gpc parameters are present, otherwise skip parsing.
				if( !gpc_isset( 'custom_field_' . $t_cfid ) ) {
					continue;
				}

				if( is_array( gpc_get( 'custom_field_' . $t_cfid, null ) ) ) {
					$f_custom_fields_data[$t_cfid] = gpc_get_string_array( 'custom_field_' . $t_cfid, array( META_FILTER_ANY ) );
				} else {
					$f_custom_fields_data[$t_cfid] = gpc_get_string( 'custom_field_' . $t_cfid, META_FILTER_ANY );
					$f_custom_fields_data[$t_cfid] = array( $f_custom_fields_data[$t_cfid] );
				}
			}
		}
	}

	$f_relationship_type = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_TYPE, $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] );
	$f_relationship_bug = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_BUG, $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] );

	log_event( LOG_FILTERING, 'filter_gpc_get: Update filters' );
	$t_filter_input['_version'] 								= FILTER_VERSION;
	$t_filter_input['_view_type'] 							= $f_view_type;
	$t_filter_input[FILTER_PROPERTY_CATEGORY_ID] 			= $f_show_category;
	$t_filter_input[FILTER_PROPERTY_SEVERITY] 				= $f_show_severity;
	$t_filter_input[FILTER_PROPERTY_STATUS] 					= $f_show_status;
	$t_filter_input[FILTER_PROPERTY_ISSUES_PER_PAGE] 		= $f_per_page;
	$t_filter_input[FILTER_PROPERTY_HIGHLIGHT_CHANGED] 		= $f_highlight_changed;
	$t_filter_input[FILTER_PROPERTY_REPORTER_ID] 			= $f_reporter_id;
	$t_filter_input[FILTER_PROPERTY_HANDLER_ID] 				= $f_handler_id;
	$t_filter_input[FILTER_PROPERTY_PROJECT_ID] 				= $f_project_id;
	$t_filter_input[FILTER_PROPERTY_SORT_FIELD_NAME] 		= $f_sort;
	$t_filter_input[FILTER_PROPERTY_SORT_DIRECTION] 			= $f_dir;
	$t_filter_input[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] 			= $f_do_filter_by_date;
	$t_filter_input[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] 			= $f_start_month;
	$t_filter_input[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] 				= $f_start_day;
	$t_filter_input[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] 				= $f_start_year;
	$t_filter_input[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] 				= $f_end_month;
	$t_filter_input[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] 				= $f_end_day;
	$t_filter_input[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] 				= $f_end_year;
	$t_filter_input[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] = $f_do_filter_by_last_updated_date;
	$t_filter_input[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] 	= $f_last_updated_start_month;
	$t_filter_input[FILTER_PROPERTY_LAST_UPDATED_START_DAY] 	= $f_last_updated_start_day;
	$t_filter_input[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] 	= $f_last_updated_start_year;
	$t_filter_input[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] 	= $f_last_updated_end_month;
	$t_filter_input[FILTER_PROPERTY_LAST_UPDATED_END_DAY] 		= $f_last_updated_end_day;
	$t_filter_input[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] 		= $f_last_updated_end_year;
	$t_filter_input[FILTER_PROPERTY_SEARCH] 					= $f_search;
	$t_filter_input[FILTER_PROPERTY_HIDE_STATUS] 			= $f_hide_status;
	$t_filter_input[FILTER_PROPERTY_RESOLUTION] 				= $f_show_resolution;
	$t_filter_input[FILTER_PROPERTY_BUILD] 					= $f_show_build;
	$t_filter_input[FILTER_PROPERTY_VERSION] 				= $f_show_version;
	$t_filter_input[FILTER_PROPERTY_FIXED_IN_VERSION] 		= $f_fixed_in_version;
	$t_filter_input[FILTER_PROPERTY_TARGET_VERSION] 			= $f_target_version;
	$t_filter_input[FILTER_PROPERTY_PRIORITY] 				= $f_show_priority;
	$t_filter_input[FILTER_PROPERTY_MONITOR_USER_ID] 		= $f_user_monitor;
	$t_filter_input[FILTER_PROPERTY_VIEW_STATE] 				= $f_view_state;
	$t_filter_input['custom_fields'] 						= $f_custom_fields_data;
	$t_filter_input[FILTER_PROPERTY_STICKY] 					= $f_sticky_issues;
	$t_filter_input[FILTER_PROPERTY_RELATIONSHIP_TYPE] 		= $f_relationship_type;
	$t_filter_input[FILTER_PROPERTY_RELATIONSHIP_BUG] 		= $f_relationship_bug;
	$t_filter_input[FILTER_PROPERTY_PROFILE_ID] 				= $f_show_profile;
	$t_filter_input[FILTER_PROPERTY_PLATFORM] 				= $f_platform;
	$t_filter_input[FILTER_PROPERTY_OS] 						= $f_os;
	$t_filter_input[FILTER_PROPERTY_OS_BUILD] 				= $f_os_build;
	$t_filter_input[FILTER_PROPERTY_TAG_STRING] 				= $f_tag_string;
	$t_filter_input[FILTER_PROPERTY_TAG_SELECT] 				= $f_tag_select;
	$t_filter_input[FILTER_PROPERTY_NOTE_USER_ID] 			= $f_note_user_id;
	$t_filter_input[FILTER_PROPERTY_MATCH_TYPE] 				= $f_match_type;

	# copy runtime properties, if present
	if( isset( $t_filter['_temporary_key'] ) ) {
		$t_filter_input['_temporary_key'] = $t_filter['_temporary_key'];
	}
	if( isset( $t_filter['_filter_id'] ) ) {
		$t_filter_input['_filter_id'] = $t_filter['_filter_id'];
	}
	# Don't copy cached subquery '_subquery' property

	return filter_ensure_valid_filter( $t_filter_input );
}

/**
 * Returns the sort columns from a filter, with only those columns that are visible
 * according to $p_columns_target user's configuration, and valid for sorting.
 * Returns an array consisting of two respective properties of column names, and
 * sort direction, each one already exploded into an array.
 * Note: Filter array must be a valid filter
 * @param array $p_filter Original filter array.
 * @param integer $p_columns_target Target view for the columns.
 * @return array Array of filtered columns and order
 */
function filter_get_visible_sort_properties_array( array $p_filter, $p_columns_target = COLUMNS_TARGET_VIEW_PAGE ) {
	# get visible columns
	$t_visible_columns = helper_get_columns_to_view( $p_columns_target );
	# filter out those that ar not sortable
	$t_visible_columns = array_filter( $t_visible_columns, 'column_is_sortable' );

	$t_sort_fields = explode( ',', $p_filter[FILTER_PROPERTY_SORT_FIELD_NAME] );
	$t_dir_fields = explode( ',', $p_filter[FILTER_PROPERTY_SORT_DIRECTION] );
	$t_sort_array = array();
	$t_dir_array = array();
	$t_count = count( $t_sort_fields );
	for( $i = 0; $i < $t_count; $i++ ) {
		$c_sort = $t_sort_fields[$i];
		if( in_array( $c_sort, $t_visible_columns ) ) {
			$t_sort_array[] = $t_sort_fields[$i];
			$t_dir_array[] = $t_dir_fields[$i];
		}
	}
	return array(
		FILTER_PROPERTY_SORT_FIELD_NAME => $t_sort_array,
		FILTER_PROPERTY_SORT_DIRECTION => $t_dir_array
	);
}

/**
 * Returns true if the filter id is a named stored filter, which can be managed and edited.
 * Returns false if it's a temporary filter, or if the filter id does not exists
 * @param integer $p_filter_id
 * @return boolean
 */
function filter_is_named_filter( $p_filter_id ) {
	$t_filter_row = filter_get_row( $p_filter_id );
	if( $t_filter_row ) {
		return !empty( $t_filter_row['name'] ) && $t_filter_row['project_id'] >= 0;
	}
	return false;
}

/**
 * Returns true if the filter is accessible by the user, which happens when the user
 * is the owner of the filter, or the filter is public.
 * @param integer $p_filter_id	Filter id
 * @param integer $p_user_id	User id
 * @return boolean	true if the filter is accessible by the user
 */
function filter_is_accessible( $p_filter_id, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}
	$t_filter_row = filter_get_row( $p_filter_id );
	if( $t_filter_row ) {
		if( $t_filter_row['user_id'] == $t_user_id || $t_filter_row['is_public'] ) {
			# If the filter is a named filter, check the config options
			if( $t_filter_row['project_id'] >= 0
				&& !is_blank( $t_filter_row['name'] ) ) {
				return access_has_project_level( config_get( 'stored_query_use_threshold', null, $t_user_id, $t_filter_row['project_id'] ) );
			}
			# it it's a "current" filter, access is ok
			return true;
		}
	}
	return false;
}

/**
 * Prints the simple/advanced menu item toggle if needed
 * @param string $p_url       Target URL, must end with 'view_type='
 * @param string $p_view_type Filter view type (FILTER_VIEW_TYPE_SIMPLE or
 *                            FILTER_VIEW_TYPE_ADVANCED)
 */
function filter_print_view_type_toggle( $p_url, $p_view_type ) {
	$t_view_filters = config_get( 'view_filters' );
	if( $t_view_filters == SIMPLE_ONLY || $t_view_filters == ADVANCED_ONLY ) {
		return;
	}

	if( $p_view_type == FILTER_VIEW_TYPE_ADVANCED ) {
		$t_url = $p_url . FILTER_VIEW_TYPE_SIMPLE;
		$t_icon = 'fa-toggle-off';
		$t_lang_string = 'simple_filters';
	} else {
		$t_url = $p_url . FILTER_VIEW_TYPE_ADVANCED;
		$t_icon = 'fa-toggle-on';
		$t_lang_string = 'advanced_filters';
	}

	echo '<li>';
	printf( '<a href="%s">%s</i>&#160;&#160;%s</a>',
		$t_url,
		icon_get( $t_icon, 'ace-icon' ),
		lang_get( $t_lang_string )
	);
	echo '</li>';
}

/**
 * Returns an array of project ids which are included in the filter.
 * This array includes all individual projects/subprojects that are in the search scope.
 * If ALL_PROJECTS were included directly, or indirectly, and the parameter $p_return_all_projects
 * is set to true, the value ALL_PROJECTS will be returned. Otherwise the array will be expanded
 * to all actual accessible projects
 * @param array $p_filter                 Filter array
 * @param integer $p_project_id           Project id to use in filtering, if applicable by filter type
 * @param integer $p_user_id              User id to use as current user when filtering
 * @param boolean $p_return_all_projects  If true, return ALL_PROJECTS directly if found, instead of
 *                                         expanding to individual project ids
 * @return array|integer	Array of project ids, or ALL_PROJECTS if applicable.
 */
function filter_get_included_projects( array $p_filter, $p_project_id = null, $p_user_id = null, $p_return_all_projects = false ) {
	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}
	if( !$p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	$t_view_type = $p_filter['_view_type'];
	# normalize the project filtering into an array $t_project_ids
	if( FILTER_VIEW_TYPE_SIMPLE == $t_view_type ) {
		log_event( LOG_FILTERING, 'Simple Filter' );
		$t_project_ids = array( $t_project_id );
		$t_include_sub_projects = true;
	} else {
		log_event( LOG_FILTERING, 'Advanced Filter' );
		$t_project_ids = $p_filter[FILTER_PROPERTY_PROJECT_ID];
		$t_include_sub_projects = (( count( $t_project_ids ) == 1 ) && ( ( $t_project_ids[0] == META_FILTER_CURRENT ) || ( $t_project_ids[0] == ALL_PROJECTS ) ) );
	}

	log_event( LOG_FILTERING, 'project_ids = @P' . implode( ', @P', $t_project_ids ) );
	log_event( LOG_FILTERING, 'include sub-projects = ' . ( $t_include_sub_projects ? '1' : '0' ) );

	# if the array has ALL_PROJECTS, then reset the array to only contain ALL_PROJECTS.
	# replace META_FILTER_CURRENT with the actual current project id.

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

	# if not expanding ALL_PROJECTS, shortcut return directly
	if( $t_all_projects_found && $p_return_all_projects ) {
		return ALL_PROJECTS;
	}

	if( $t_all_projects_found ) {
		$t_project_ids = user_get_accessible_projects( $t_user_id );
	} else {
		$t_project_ids = $t_new_project_ids;
	}

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

	if( count( $t_project_ids ) ) {
		log_event( LOG_FILTERING, 'project_ids after including sub-projects = @P' . implode( ', @P', $t_project_ids ) );
	} else {
		log_event( LOG_FILTERING, 'no accessible projects' );
	}

	return $t_project_ids;
}

/**
 * Returns a filter array structure for the given filter_id
 * A default value can be provided to be used when the filter_id doesn't exists
 * or is not accessible
 *
 *  You may pass in any array as a default (including null) but if
 *  you pass in *no* default then an error will be triggered if the filter
 *  cannot be found
 *
 * @param integer $p_filter_id Filter id
 * @param array $p_default     A filter array to return when id is not found
 * @return array	A filter array
 */
function filter_get( $p_filter_id, array $p_default = null ) {
	# if no default was provided, we will trigger an error if not found
	$t_trigger_error = func_num_args() == 1;

	# This function checks for user access
	$t_filter_string = filter_db_get_filter_string( $p_filter_id );
	# If value is false, it either doesn't exists or is not accessible
	if( !$t_filter_string ) {
		if( $t_trigger_error ) {
			error_parameters( $p_filter_id );
			trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
		} else {
			return $p_default;
		}
	}
	$t_filter = filter_deserialize( $t_filter_string );
	# If the unserialez data is not an array, the some error happened, eg, invalid format
	if( !is_array( $t_filter ) ) {
		# Don't throw error, otherwise the user could not recover navigation easily
		return filter_get_default();
	}
	$t_filter = filter_clean_runtime_properties( $t_filter );
	$t_filter['_filter_id'] = $p_filter_id;

	$t_filter = filter_update_source_properties( $t_filter );

	return $t_filter;
}

/**
 * Return a standard filter
 * @param string $p_filter_name     The name of the filter
 * @param integer|null $p_user_id   A user id to build this filter. Null for current user
 * @param integer|null $p_project_id	 A project id to build this filter.  Null for current project
 * @return null|boolean|array       null filter not found, false invalid filter, otherwise the filter.
 */
function filter_standard_get( $p_filter_name, $p_user_id = null, $p_project_id = null ) {
	$p_filter_name = strtolower( $p_filter_name );

	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	if( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	switch( $p_filter_name ) {
		case FILTER_STANDARD_ANY:
			$t_filter = filter_create_any();
			break;
		case FILTER_STANDARD_ASSIGNED:
			$t_filter = filter_create_assigned_to_unresolved( $t_project_id, $t_user_id );
			break;
		case FILTER_STANDARD_UNASSIGNED:
			$t_filter = filter_create_assigned_to_unresolved( $t_project_id, NO_USER );
			break;
		case FILTER_STANDARD_REPORTED:
			$t_filter = filter_create_reported_by( $t_project_id, $t_user_id );
			break;
		case FILTER_STANDARD_MONITORED:
			$t_filter = filter_create_monitored_by( $t_project_id, $t_user_id );
			break;
		default:
			return null;
	}

	return $t_filter;
}

/**
 * Updates a filter's properties with those from another filter that is referenced
 * by it's source-id property.
 * This is used when an anonymous filter was created from a named filter. As long
 * as this anonymous filter is not modified, it must be keep in sync with the
 * referenced filter (source_id), because the source filter may have been modified
 * at a later time.
 * This is a side effect of always using anonymous filters even when selecting a
 * named filter to be applied as current.
 *
 * @param array $p_filter	Original filter array
 * @return array	Updated filter array
 */
function filter_update_source_properties( array $p_filter ) {
	# Check if the filter references a named filter
	# This property only makes sense, and should be available on unnamed filters
	if( isset( $p_filter['_filter_id'] ) ) {
		$t_filter_id = $p_filter['_filter_id'];
	} else {
		$t_filter_id = null;
	}
	if( isset( $p_filter['_source_query_id'] ) && $t_filter_id != $p_filter['_source_query_id'] ) {
		$t_source_query_id = $p_filter['_source_query_id'];
		# check if filter id is a proper named filter, and is accessible
		if( filter_is_named_filter( $t_source_query_id ) && filter_is_accessible( $t_source_query_id ) ){
			# replace filter with the referenced one
			$t_new_filter = filter_deserialize( filter_db_get_filter_string( $t_source_query_id ) );
			if( is_array( $t_new_filter ) ) {
				# update the referenced stored filter id for the new loaded filter
				$t_new_filter['_source_query_id'] = $t_source_query_id;
				$p_filter = filter_copy_runtime_properties( $t_new_filter, $p_filter );
			} else {
				# If the unserialez data is not an array, the some error happened, eg, invalid format
				unset( $p_filter['_source_query_id'] );
			}
		} else {
			# If the filter id is not valid, clean the referenced filter id
			unset( $p_filter['_source_query_id'] );
		}
	}
	return $p_filter;
}

/**
 * Returns a filter which is stored in session data, indexed by the provided key.
 * A default value can be provided to be used when the key doesn't exists
 *
 *  You may pass in any array as a default (including null) but if
 *  you pass in *no* default then an error will be triggered if the key
 *  cannot be found
 *
 * @param string $p_filter_key  Key to look up for in session data
 * @param mixed $p_default		A default value to return if key not found
 * @return array	A filter array.
 */
function filter_temporary_get( $p_filter_key, $p_default = null ) {
	# if no default was provided, we will trigger an error if not found
	$t_trigger_error = func_num_args() == 1;

	$t_session_filters = session_get( 'temporary_filters', array() );
	if( isset( $t_session_filters[$p_filter_key] ) ) {
		# setting here the key in the filter array only if the key exists
		# this validates against receiving garbage input as XSS attacks
		$t_filter = $t_session_filters[$p_filter_key];
		$t_filter['_temporary_key'] = $p_filter_key;
		return filter_ensure_valid_filter( $t_filter );
	} else {
		if( $t_trigger_error ) {
			error_parameters( $p_filter_key );
			trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
		} else {
			return $p_default;
		}
	}
}

/**
 * Saves a filter as a temporary filter in session data.
 * The filter will be updated or created, indexed by provided $p_filter_key,
 * If no key is provided, it will search in the filter property that holds
 * its key if it was loaded as a temporary filter.
 * If neither key is found, a new one will be created
 * @param array $p_filter     Filter array
 * @param string $p_filter_key  Key to update, or null
 * @return string	The key used for storing the filter.
 */
function filter_temporary_set( array $p_filter, $p_filter_key = null ) {
	if( null === $p_filter_key ) {
		$t_filter_key = filter_get_temporary_key( $p_filter );
		if( !$t_filter_key ) {
			$t_filter_key = uniqid();
		}
	} else {
		$t_filter_key = $p_filter_key;
	}

	$p_filter = filter_clean_runtime_properties( $p_filter );
	$t_session_filters = session_get( 'temporary_filters', array() );
	$t_session_filters[$t_filter_key] = $p_filter;
	session_set( 'temporary_filters', $t_session_filters );
	return $t_filter_key;
}

/**
 * Get the temporary key of the filter, if was loaded from temporary session store
 * Return null otherwise
 * @param array $p_filter	Filter array
 * @return string|null	Key associated with this filter, null if none
 */
function filter_get_temporary_key( array $p_filter ) {
	if( isset( $p_filter['_temporary_key'] ) ) {
		return $p_filter['_temporary_key'];
	} else {
		return null;
	}
}

/**
 * Returns true if the filter was loaded as temporary filter
 * @param array $p_filter	Filter array
 * @return boolean	Whether this filter is temporary
 */
function filter_is_temporary( array $p_filter ) {
	return isset( $p_filter['_temporary_key'] );
}

/**
 * Returns a string formatted as GET parameter, suitable for tracking a
 * temporary filter by its session key.
 * The parameter can be either:
 * - an existing key to be used directly, or
 * - a filter array, which can contain a property with the key.
 * If the provided filter does not contain the key property, the function
 * returns null.
 *
 * @param array|string $p_key_or_filter	Either a string key, or a filter array
 *
 * @return string|null	Formatted parameter string, or null
 */
function filter_get_temporary_key_param( $p_key_or_filter ) {
	if( is_array( $p_key_or_filter ) ) {
		$t_key = filter_get_temporary_key( $p_key_or_filter );
	} else {
		$t_key = $p_key_or_filter;
	}
	if( $t_key ) {
		return 'filter=' . $t_key;
	} else {
		return null;
	}
}

/**
 * Removes runtime properties that are should not be saved as part of the filter
 * Use this function before saving the filter.
 * @param array $p_filter	Filter array (passed as reference, it gets modified)
 * @return array	Modified filter array
 */
function filter_clean_runtime_properties( array $p_filter ) {
	if( isset( $p_filter['_temporary_key'] ) ) {
		unset( $p_filter['_temporary_key'] );
	}
	if( isset( $p_filter['_filter_id'] ) ) {
		unset( $p_filter['_filter_id'] );
	}
	if( isset( $p_filter['_subquery'] ) ) {
		unset( $p_filter['_subquery'] );
	}
	return $p_filter;
}

/**
 * Copy the runtime properties from one filter into another.
 * @param array $p_filter_to	Destination filter array
 * @param array $p_filter_from	Filter array from which properties are copied
 * @return array	Updated filter array
 */
function filter_copy_runtime_properties( array $p_filter_to, array $p_filter_from ) {
	if( isset( $p_filter_from['_temporary_key'] ) ) {
		$p_filter_to['_temporary_key'] = $p_filter_from['_temporary_key'];
	}
	if( isset( $p_filter_from['_filter_id'] ) ) {
		$p_filter_to['_filter_id'] = $p_filter_from['_filter_id'];
	}
	# we don't copy '_subquery' property, which is a cached subquery object,
	# and can be regenerated at demand

	return $p_filter_to;
}

/**
 * Return a cached BugFilterQuery object for the provided filter, configured and
 * ready to be used as a subquery for building other queries.
 * If the query is not in the cache, creates a new one and store it for later reuse.
 * Note: Query objects are indexed by a hash value over the serialized contents of the
 * filter array.
 *
 * Warning: Since the returned query is an object, it should not be modified in any way
 * that changes the expected behavior from the original filter array, as any further
 * reuse of this chached query will share the same instanced object.
 * If such a modification is needed over the query object, a clone should be used
 * instead, to avoid said side effects.
 *
 * @param array $p_filter	Filter array
 * @return BugFilterQuery	A query object for the filter
 */
function filter_cache_subquery( array $p_filter ) {
	global $g_cache_filter_subquery;

	$t_hash = md5( json_encode( $p_filter ) );
	if( !isset( $g_cache_filter_subquery[$t_hash] ) ) {
		$g_cache_filter_subquery[$t_hash] = new BugFilterQuery( $p_filter, BugFilterQuery::QUERY_TYPE_IDS );
	}

	return $g_cache_filter_subquery[$t_hash];
}
/**
 * Returns true if the user can use peristent filters, in contexts such as view_all_bug_page.
 * Persistent filters are remembered across sessions, and are not desirable when the user is
 * a shared user, eg: anonymous user
 * @param integer $p_user_id	A valid user identifier.
 * @return boolean true if the user can use persistent filters, false otherwise
 */
function filter_user_can_use_persistent( $p_user_id = null ) {
	return !user_is_anonymous( $p_user_id );
}
