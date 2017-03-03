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
require_api( 'string_api.php' );
require_api( 'tag_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );
require_api( 'filter_form_api.php' );

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
					foreach( $t_plugin_filter_array as $t_filter_item ) {
						if( is_object( $t_filter_item ) && $t_filter_item instanceof MantisFilter ) {
							$t_filter_object = $t_filter_item;
						} elseif( class_exists( $t_filter_item ) && is_subclass_of( $t_filter_item, 'MantisFilter' ) ) {
							$t_filter_object = new $t_filter_item();
						} else {
							continue;
						}
						$t_filter_name = utf8_strtolower( $t_plugin . '_' . $t_filter_object->field );
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
 * Make sure that our filters are entirely correct and complete (it is possible that they are not).
 * We need to do this to cover cases where we don't have complete control over the filters given.
 * @param array $p_filter_arr A Filter definition.
 * @return array
 * @todo function needs to be abstracted
 */
function filter_ensure_valid_filter( array $p_filter_arr ) {
	if( !isset( $p_filter_arr['_version'] ) ) {
		$p_filter_arr['_version'] = FILTER_VERSION;
	}
	$t_cookie_vers = (int)substr( $p_filter_arr['_version'], 1 );
	$t_current_version = (int)substr( FILTER_VERSION, 1 );
	if( $t_current_version > $t_cookie_vers ) {
		# if the version is old, update it
		$p_filter_arr['_version'] = FILTER_VERSION;
	}

	$p_filter_arr = filter_ensure_fields( $p_filter_arr );

	$t_config_view_filters = config_get( 'view_filters' );
	$t_view_type = $p_filter_arr['_view_type'];
	if( ADVANCED_ONLY == $t_config_view_filters ) {
		$t_view_type = 'advanced';
	}
	if( SIMPLE_ONLY == $t_config_view_filters ) {
		$t_view_type = 'simple';
	}
	if( !in_array( $t_view_type, array( 'simple', 'advanced' ) ) ) {
		$t_view_type = filter_get_default_view_type();
	}
	$p_filter_arr['_view_type'] = $t_view_type;

	$t_sort_fields = explode( ',', $p_filter_arr[FILTER_PROPERTY_SORT_FIELD_NAME] );
	$t_dir_fields = explode( ',', $p_filter_arr[FILTER_PROPERTY_SORT_DIRECTION] );
	# both arrays should be equal lenght, just in case
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
		FILTER_PROPERTY_PLATFORM => 'string',
		FILTER_PROPERTY_OS => 'string',
		FILTER_PROPERTY_OS_BUILD => 'string',
		FILTER_PROPERTY_PROJECT_ID => 'int'
	);
	foreach( $t_multi_select_list as $t_multi_field_name => $t_multi_field_type ) {
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
	if( $p_filter_arr['_view_type'] == 'advanced'
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
	if( $p_filter_arr['_view_type'] == 'simple'
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

	# all of our filter values are now guaranteed to be there, and correct.
	return $p_filter_arr;
}

/**
 * Get a filter array with default values
 * Optional view type parameter is used to initialize some fields properly,
 * as some may differ in the default content.
 * @param string $p_view_type	"simple" or "advanced"
 * @return array Filter array with default values
 */
function filter_get_default_array( $p_view_type = null ) {
	static $t_cache_default_array = array();

	$t_default_view_type = filter_get_default_view_type();
	if( !in_array( $p_view_type, array( 'simple', 'advanced' ) ) ) {
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
		$t_view_type = 'advanced';
	} elseif( SIMPLE_ONLY == $t_config_view_filters ) {
		$t_view_type = 'simple';
	} else {
		$t_view_type = $p_view_type;
	}

	if( $t_view_type == 'simple' ) {
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
		FILTER_PROPERTY_RELATIONSHIP_BUG => 0,
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
		return 'advanced';
	} else {
		return 'simple';
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
 * Creates a serialized filter with the correct format
 * @param array $p_filter_array Filter array to be serialized
 * @return string Serialized filter string
 */
function filter_serialize( $p_filter_array ) {
	$t_cookie_version = FILTER_VERSION;
	$t_settings_serialized = json_encode( $p_filter_array );
	$t_settings_string = $t_cookie_version . '#' . $t_settings_serialized;
	return $t_settings_string;
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

	$p_query_clauses['order'] = array();

	# Get only the visible, and sortable, column properties
	# @TODO cproensa: this defaults to COLUMNS_TARGET_VIEW_PAGE
	# are we sure that filters are only used with the column set for view page?
	$p_sort_properties = filter_get_visible_sort_properties_array( $p_filter );
	$t_sort_fields = $p_sort_properties[FILTER_PROPERTY_SORT_FIELD_NAME];
	$t_dir_fields = $p_sort_properties[FILTER_PROPERTY_SORT_DIRECTION];

	if( gpc_string_to_bool( $p_filter[FILTER_PROPERTY_STICKY] ) && ( null !== $p_show_sticky ) ) {
		$p_query_clauses['order'][] = '{bug}.sticky DESC';
	}

	$t_count = count( $t_sort_fields );
	for( $i = 0; $i < $t_count; $i++ ) {
		$c_sort = $t_sort_fields[$i];
		$c_dir = 'DESC' == $t_dir_fields[$i] ? 'DESC' : 'ASC';

		# if sorting by a custom field
		if( column_is_custom_field( $c_sort ) ) {
			$t_custom_field = column_get_custom_field_name( $c_sort );
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
		} else if( column_is_plugin_column( $c_sort ) ) {
			$t_plugin_columns = columns_get_plugin_columns();
			$t_column_object = $t_plugin_columns[$c_sort];

			$t_clauses = $t_column_object->sortquery( $c_dir );
			if( is_array( $t_clauses ) ) {
				if( isset( $t_clauses['join'] ) ) {
					$p_query_clauses['join'][] = $t_clauses['join'];
				}
				if( isset( $t_clauses['order'] ) ) {
					$p_query_clauses['order'][] = $t_clauses['order'];
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
 * @param boolean $p_pop_param      Whether to pop DB params from the stack
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
	$t_rows = array();
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
 * @param boolean $p_pop_param        Whether to pop DB params from the stack
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

	# creation date filter
	if( ( 'on' == $t_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] )
			) {
		$t_start_string = $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] . '-' . $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] . '-' . $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] . ' 00:00:00';
		$t_end_string = $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] . '-' . $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] . '-' . $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] . ' 23:59:59';

		$t_where_params[] = strtotime( $t_start_string );
		$t_where_params[] = strtotime( $t_end_string );
		array_push( $t_project_where_clauses, '({bug}.date_submitted BETWEEN ' . db_param() . ' AND ' . db_param() . ' )' );
	}


	# last update date filter
	if( ( 'on' == $t_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] )
			&& is_numeric( $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] )
			) {
		$t_start_string = $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] . '-' . $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] . '-' . $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] . ' 00:00:00';
		$t_end_string = $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] . '-' . $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] . '-' . $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] . ' 23:59:59';

		$t_where_params[] = strtotime( $t_start_string );
		$t_where_params[] = strtotime( $t_end_string );
		array_push( $t_project_where_clauses, '({bug}.last_updated BETWEEN ' . db_param() . ' AND ' . db_param() . ' )' );
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
	$t_desired_statuses = $t_filter[FILTER_PROPERTY_STATUS];

	# simple filtering: restrict by the hide status value if present
	if( 'simple' == $t_filter['_view_type'] ) {
		if( isset( $t_filter[FILTER_PROPERTY_HIDE_STATUS][0] ) && !filter_field_is_none( $t_filter[FILTER_PROPERTY_HIDE_STATUS][0] ) ) {
			$t_selected_status_array = $t_filter[FILTER_PROPERTY_STATUS];
			# if we have metavalue for "any", expand to all status, to filter them
			if( filter_field_is_any( $t_selected_status_array ) ) {
				$t_selected_status_array = MantisEnum::getValues( config_get( 'status_enum_string' ) );
			}
			$t_hide_status = $t_filter[FILTER_PROPERTY_HIDE_STATUS][0];
			# Filter out status that must be hidden
			$t_desired_statuses = array();
			foreach( $t_selected_status_array as $t_this_status ) {
				if( $t_hide_status > $t_this_status ) {
					$t_desired_statuses[] = $t_this_status;
				}
			}
		}
	}
	# advanced filtering: ignore hide_status, do nothing.

	# if show_status is "any", empty the array, to not include any condition on status.
	if( filter_field_is_any( $t_desired_statuses ) ) {
			$t_desired_statuses = array();
	}

	if( count( $t_desired_statuses ) > 0 ) {
		$t_clauses = array();

		foreach( $t_desired_statuses as $t_filter_member ) {
			$t_clauses[] = (int)$t_filter_member;
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
				# Note: no need to test negative values, '-' sign has been removed
				if( $t_search_term <= DB_MAX_INT ) {
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
 * Mainly based on filter_draw_selection_area2() but adds the support for the collapsible
 * filter display.
 * @param integer $p_page_number Page number.
 * @param boolean $p_for_screen  Whether output is for screen view.
 * @return void
 * @see filter_draw_selection_area2
 */
function filter_draw_selection_area( $p_page_number, $p_for_screen = true ) {
	echo '<div class="col-md-12 col-xs-12">';
	filter_draw_selection_area2( $p_page_number, $p_for_screen, true );
	echo '</div>';
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
	$t_page_number = (int)$p_page_number;

	$t_view_type = $t_filter['_view_type'];

	$t_action = 'view_all_set.php?f=3';
	if( $p_for_screen == false ) {
		$t_action = 'view_all_set.php';
	}
	if( $p_expanded ) {
		# in expanded form, all field are sent
		$t_view_all_set_type = 1;
	} else {
		# in condensed form, only the search field is sent, to be added over current filter values.
		$t_view_all_set_type = 5;
	}
	?>

	<div class="filter-box">
		<form method="post" name="filters<?php echo $t_form_name_suffix?>" id="filters_form<?php echo $t_form_name_suffix?>" action="<?php echo $t_action;?>">
		<?php # CSRF protection not required here - form does not result in modifications ?>
		<input type="hidden" name="type" value="<?php echo $t_view_all_set_type ?>" />
		<?php
			if( $p_for_screen == false ) {
		echo '<input type="hidden" name="print" value="1" />';
		echo '<input type="hidden" name="offset" value="0" />';
	}
	?>
		<input type="hidden" name="page_number" value="<?php echo $t_page_number?>" />
		<input type="hidden" name="view_type" value="<?php echo $t_view_type?>" />
	<?php
	$t_stored_queries_arr = filter_db_get_available_queries();
	if( $p_expanded ) {
		$t_collapse_block = is_collapsed( 'filter' );
		$t_block_css = $t_collapse_block ? 'collapsed' : '';
		$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
		?>

		<div id="filter" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-filter"></i>
				<?php echo lang_get( 'filters' ) ?>
			</h4>
			<div class="widget-toolbar">
				<?php
					$f_switch_view_link = (config_get('use_dynamic_filters')) ? 'view_all_set.php?type=6&amp;view_type=' : 'view_filters_page.php?view_type=';
					$t_view_filters = config_get('view_filters');

					if( ( ( SIMPLE_ONLY != $t_view_filters ) && ( ADVANCED_ONLY != $t_view_filters ) ) ||
						access_has_project_level( config_get( 'create_permalink_threshold' ) ) ||
						count( $t_stored_queries_arr ) > 0 ) { ?>
					<div class="widget-menu">
						<a href="#" data-action="settings" data-toggle="dropdown">
							<i class="ace-icon fa fa-bars bigger-125"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-right dropdown-yellow dropdown-caret dropdown-closer">
							<?php
							if( ( SIMPLE_ONLY != $t_view_filters ) && ( ADVANCED_ONLY != $t_view_filters ) ) {
								echo '<li>';
								if( 'advanced' == $t_view_type ) {
									echo '<a href="' . $f_switch_view_link, 'simple"><i class="ace-icon fa fa-toggle-off"></i>&#160;&#160;' . lang_get('simple_filters') . '</a>';
								} else {
									echo '<a href="' . $f_switch_view_link, 'advanced"><i class="ace-icon fa fa-toggle-on"></i>&#160;&#160;' . lang_get('advanced_filters') . '</a>';
								}
								echo '</li>';
							}
							if( access_has_project_level( config_get( 'create_permalink_threshold' ) ) ) {
								echo '<li>';
								echo '<a href="permalink_page.php?url=' . urlencode( filter_get_url( $t_filter ) ) . '">';
								echo '<i class="ace-icon fa fa-link"></i>&#160;&#160;' . lang_get( 'create_filter_link' );
								echo '</a>';
								echo '</li>';
							}
							if( count( $t_stored_queries_arr ) > 0 ) {
								echo '<li>';
								echo '<a href="manage_filter_page.php">';
								echo '<i class="ace-icon fa fa-wrench"></i>&#160;&#160;' . lang_get( 'open_queries' );
								echo '</a>';
								echo '</li>';
							} ?>
						</ul>
					</div>
				<?php } ?>
				<a id="filter-toggle" data-action="collapse" href="#">
					<i class="1 ace-icon fa bigger-125 <?php echo $t_block_icon ?>"></i>
				</a>
			</div>
			<?php if( count( $t_stored_queries_arr ) > 0 ) { ?>
				<div id="filter-bar-queries" class="widget-toolbar hidden-xs" style="display: <?php echo $t_collapse_block ? 'block' : 'none' ?>">
					<div class="widget-menu margin-left-8 margin-right-8">
						<select id="filter-bar-query-id" class="input-xs">
							<option value="-1"></option>
							<option value="-1"><?php echo '[' . lang_get( 'reset_query' ) . ']'?></option>
							<?php
							$t_source_query_id = isset( $t_filter['_source_query_id'] ) ? (int)$t_filter['_source_query_id'] : -1;
							foreach( $t_stored_queries_arr as $t_query_id => $t_query_name ) {
								echo '<option value="' . $t_query_id . '" ';
								check_selected( $t_query_id, $t_source_query_id );
								echo '>' . string_display_line( $t_query_name ) . '</option>';
							}
							?>
						</select>
					</div>
				</div>
			<?php } ?>
			<div id="filter-bar-search" class="widget-toolbar no-border" style="display: <?php echo $t_collapse_block ? 'block' : 'none' ?>">
				<div class="widget-menu margin-left-8 margin-right-8">
					<input id="filter-bar-search-txt" type="text" size="16" class="input-xs"
						   placeholder="<?php echo lang_get( 'search' ) ?>"
						   value="<?php echo string_attribute( $t_filter[FILTER_PROPERTY_SEARCH] ); ?>" />
					<button id="filter-bar-search-btn" type="submit" name="filter" class="btn btn-primary btn-white btn-round btn-xs"
							title="<?php echo lang_get( 'filter_button' ) ?>">
						<i class="ace-icon fa fa-search"></i>
					</button>
				</div>
			</div>
		</div>

		<div class="widget-body">
		<div class="widget-main no-padding">

		<div class="table-responsive">

		<?php
		filter_form_draw_inputs( $t_filter, $p_for_screen, false, 'view_filters_page.php' );
		?>

		</div>
		</div>
		<?php
	}

	echo '<div class="widget-toolbox padding-8 clearfix">';
	echo '<div class="btn-toolbar pull-left">';

	# expanded
	echo '<div class="form-inline">';
	echo '<input type="text" id="filter-search-txt" class="input-sm" size="16" name="', FILTER_PROPERTY_SEARCH, '"
		placeholder="' . lang_get( 'search' ) . '" value="', string_attribute( $t_filter[FILTER_PROPERTY_SEARCH] ), '" />';
	?>
	<input type="submit" class="btn btn-primary btn-sm btn-white btn-round no-float" name="filter" value="<?php echo lang_get( 'filter_button' )?>" />
	</div>
	<?php

	echo '</form></div>';
	echo '<div class="btn-toolbar pull-right">';
	echo '<div class="btn-group">';

	if( access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) { ?>
		<form class="form-inline pull-left" method="post" name="save_query" action="query_store_page.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="submit" name="save_query_button" class="btn btn-primary btn-white btn-sm btn-round"
				value="<?php echo lang_get( 'save_query' )?>" />
		</form>
	<?php
	}
	if( count( $t_stored_queries_arr ) > 0 ) { ?>
		<form id="filter-queries-form" class="form-inline pull-left padding-left-8"  method="get" name="list_queries<?php echo $t_form_name_suffix;?>" action="view_all_set.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="hidden" name="type" value="3" />
			<select name="source_query_id">
				<option value="-1"></option>
				<option value="-1"><?php echo '[' . lang_get( 'reset_query' ) . ']'?></option>
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
	<?php
	} else { ?>
		<form class="form-inline pull-left" method="get" name="reset_query" action="view_all_set.php">
			<?php # CSRF protection not required here - form does not result in modifications ?>
			<input type="hidden" name="type" value="3" />
			<input type="hidden" name="source_query_id" value="-1" />
			<input type="submit" name="reset_query_button" class="btn btn-primary btn-white btn-sm btn-round" value="<?php echo lang_get( 'reset_query' )?>" />
		</form>
	<?php
	}
	?>


	</div>
	</div>
	</div>
	</div>
	</div>
	</div>
<?php
}


# ==========================================================================
# CACHING
# ==========================================================================

# We cache filter requests to reduce the number of SQL queries
# @global array $g_cache_filter
# @global array $g_cache_filter_db_filters
$g_cache_filter = array();
$g_cache_filter_db_filters = array();

function filter_cache_rows( array $p_filter_ids ) {
	global $g_cache_filter;

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
		$g_cache_filter[$t_row['id']] = $t_row;
		unset( $t_ids_not_found[$t_row['id']] );
	}
	foreach( $t_ids_not_found as $t_id ) {
		$g_cache_filter[$t_id] = false;
	}
}

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

	if( !isset( $g_cache_filter[$p_filter_id] ) ) {
		filter_cache_rows( array($p_filter_id) );
	}

	$t_row = $g_cache_filter[$p_filter_id];
	if( $p_trigger_errors && !$t_row ) {
		error_parameters( $p_filter_id );
		trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
	}

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

	if( !filter_is_accessible( $c_filter_id, $p_user_id ) ) {
		return null;
	}

	if( isset( $g_cache_filter_db_filters[$c_filter_id] ) ) {
		if( $g_cache_filter_db_filters[$c_filter_id] === false ) {
			return null;
		}
		return $g_cache_filter_db_filters[$c_filter_id];
	}

	$t_filter_row = filter_cache_row( $c_filter_id, /* trigger_errors */ false );
	if( $t_filter_row ) {
		$g_cache_filter_db_filters[$c_filter_id] = $t_filter_row['filter_string'];
	} else {
		$g_cache_filter_db_filters[$c_filter_id] = false;
	}

	return $g_cache_filter_db_filters[$c_filter_id];
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
 * @return boolean
 */
function filter_db_can_delete_filter( $p_filter_id ) {
	$c_filter_id = (int)$p_filter_id;
	$t_user_id = auth_get_current_user_id();

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
function filter_db_get_queries( $p_project_id = null, $p_user_id = null, $p_public = null ) {
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
 * Create a filter for getting issues modified in the last N days
 * @param integer $p_days Number of days counting from today
 * @param array $p_filter Add the filter conditions over this filter array. Return a new one if null
 * @return array Filter array
 */
function filter_create_recently_modified( $p_days, $p_filter = null ) {
	if( null === $p_filter ) {
		$p_filter = filter_get_default();
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
	return $p_filter;
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
	$f_view_type = gpc_get_string( 'view_type', null );

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
		# this parameter has to be pushed in fron t of current sort set
		$f_dir_add = gpc_get_string( FILTER_PROPERTY_SORT_DIRECTION . '_add', '' );
		# Plain concatenation. Empty fields, or extra commas will be cleaned by ensure_valid_filter
		$f_sort = $f_sort_add . ',' . $t_filter[FILTER_PROPERTY_SORT_FIELD_NAME];
		$f_dir = $f_dir_add . ',' . $t_filter[FILTER_PROPERTY_SORT_DIRECTION];
	} else {
		# use the defaluts
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
	$f_custom_fields_data 	= array();
	if( is_array( $t_custom_fields ) && ( count( $t_custom_fields ) > 0 ) ) {
		foreach( $t_custom_fields as $t_cfid ) {
			if( custom_field_type( $t_cfid ) == CUSTOM_FIELD_TYPE_DATE ) {
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
	$f_relationship_bug = gpc_get_int( FILTER_PROPERTY_RELATIONSHIP_BUG, $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] );

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
	$t_filter_row = filter_cache_row( $p_filter_id, /* trigger_errors */ false );
	if( $t_filter_row ) {
		return !empty( $t_filter_row['name'] ) && $t_filter_row['project_id'] >= 0;
	}
	return false;
}

/**
 * Returns true if the filter is accesible by the user, which happens when the user
 * is the owner of the filter, or the filter is public.
 * @param integer $p_filter_id	Filter id
 * @param integer $p_user_id	User id
 * @return boolean	true if the filter is accesible by the user
 */
function filter_is_accessible( $p_filter_id, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}
	$t_filter_row = filter_cache_row( $p_filter_id, /* trigger_errors */ false );
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