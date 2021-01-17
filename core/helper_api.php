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
 * Helper API
 *
 * @package CoreAPI
 * @subpackage HelperAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses error_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'error_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'user_api.php' );
require_api( 'user_pref_api.php' );
require_api( 'utility_api.php' );

use Mantis\Exceptions\ClientException;

/**
 * alternate classes for table rows
 * If no index is given, continue alternating based on the last index given
 * @param int $p_index
 * @param string $p_odd_class default: row-1
 * @param string $p_even_class default: row-2
 * @return string
 */
function helper_alternate_class( $p_index = null, $p_odd_class = 'row-1', $p_even_class = 'row-2' ) {
	static $t_index = 1;

	if( null !== $p_index ) {
		$t_index = $p_index;
	}

	error_parameters( __FUNCTION__, 'CSS' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	if( 1 == $t_index++ % 2 ) {
		return "class=\"$p_odd_class\"";
	} else {
		return "class=\"$p_even_class\"";
	}
}

/**
 * Transpose a bidimensional array
 *
 * e.g. array('a'=>array('k1'=>1,'k2'=>2),'b'=>array('k1'=>3,'k2'=>4))
 * becomes array('k1'=>array('a'=>1,'b'=>3),'k2'=>array('a'=>2,'b'=>4))
 *
 * @param array $p_array The array to transpose.
 * @return array|mixed transposed array or $p_array if not 2-dimensional array
 */
function helper_array_transpose( array $p_array ) {
	$t_out = array();
	foreach( $p_array as $t_key => $t_sub ) {
		if( !is_array( $t_sub ) ) {
			# This function can only handle bidimensional arrays
			trigger_error( ERROR_GENERIC, ERROR );
		}

		foreach( $t_sub as $t_subkey => $t_value ) {
			$t_out[$t_subkey][$t_key] = $t_value;
		}
	}
	return $t_out;
}

/**
 * get the color string for the given status, user and project
 * @param integer      $p_status        Status value.
 * @param integer|null $p_user          User id, defaults to null (all users).
 * @param integer|null $p_project       Project id, defaults to null (all projects).
 * @param string       $p_default_color Fallback color in case status is not found (defaults to white).
 * @return string
 */
function get_status_color( $p_status, $p_user = null, $p_project = null, $p_default_color = '#ffffff' ) {
	$t_status_enum = config_get( 'status_enum_string', null, $p_user, $p_project );
	$t_status_colors = config_get( 'status_colors', null, $p_user, $p_project );
	$t_status_label = MantisEnum::getLabel( $t_status_enum, $p_status );

	if( isset( $t_status_colors[$t_status_label] ) ) {
		return $t_status_colors[$t_status_label];
	}
	return $p_default_color;
}

/**
 * get the status percentages
 * @return array key is the status value, value is the percentage of bugs for the status
 */
function get_percentage_by_status() {
	$t_project_id = helper_get_current_project();
	$t_user_id = auth_get_current_user_id();

	# checking if it's a per project statistic or all projects
	$t_specific_where = helper_project_specific_where( $t_project_id, $t_user_id );

	$t_query = 'SELECT status, COUNT(*) AS num
				FROM {bug}
				WHERE ' . $t_specific_where;
	if( !access_has_project_level( config_get( 'private_bug_threshold' ) ) ) {
		$t_query .= ' AND view_state < ' . VS_PRIVATE;
	}
	$t_query .= ' GROUP BY status';
	$t_result = db_query( $t_query );

	$t_status_count_array = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_status_count_array[$t_row['status']] = $t_row['num'];
	}
	$t_bug_count = array_sum( $t_status_count_array );
	foreach( $t_status_count_array as $t_status=>$t_value ) {
		$t_status_count_array[$t_status] = round( ( $t_value / $t_bug_count ) * 100 );
	}

	return $t_status_count_array;
}

/**
 * Given a enumeration string and number, return the appropriate string for the
 * specified user/project
 * @param string       $p_enum_name An enumeration string name.
 * @param integer      $p_val       An enumeration string value.
 * @param integer|null $p_user      A user identifier, defaults to null (all users).
 * @param integer|null $p_project   A project identifier, defaults to null (all projects).
 * @return string
 */
function get_enum_element( $p_enum_name, $p_val, $p_user = null, $p_project = null ) {
	$t_config_var = config_get( $p_enum_name . '_enum_string', null, $p_user, $p_project );
	$t_string_var = lang_get( $p_enum_name . '_enum_string' );

	return MantisEnum::getLocalizedLabel( $t_config_var, $t_string_var, $p_val );
}

/**
 * Compares the 2 specified variables, returns true if equal, false if not.
 * With strict type checking, will trigger an error if the types of the compared
 * variables don't match.
 * This helper function is used by {@link check_checked()} and {@link check_selected()}
 * @param mixed   $p_var1   The variable to compare.
 * @param mixed   $p_var2   The second variable to compare.
 * @param boolean $p_strict Set to true for strict type checking, false for loose.
 * @return boolean
 */
function helper_check_variables_equal( $p_var1, $p_var2, $p_strict ) {
	if( $p_strict ) {
		if( gettype( $p_var1 ) !== gettype( $p_var2 ) ) {
			# Reaching this point is a a sign that you need to check the types
			# of the parameters passed to this function. They should match.
			trigger_error( ERROR_TYPE_MISMATCH, ERROR );
		}

		# We need to be careful when comparing an array of
		# version number strings (["1.0", "1.1", "1.10"]) to
		# a selected version number of "1.10". If a ==
		# comparison were to be used, PHP would treat
		# "1.1" and "1.10" as being the same as the strings
		# would be converted to numerals before being compared
		# as numerals.
		#
		# This is further complicated by filter dropdowns
		# containing a mixture of string and integer values.
		# The following "meta filter values" exist as integer
		# values in dropdowns:
		#   META_FILTER_MYSELF = -1
		#   META_FILTER_NONE = -2
		#   META_FILTER_CURRENT = -3
		#   META_FILTER_ANY = 0
		#
		# For these reasons, a === comparison is required.

		return $p_var1 === $p_var2;
	} else {
		return $p_var1 == $p_var2;
	}
}

/**
 * Attach a "checked" attribute to a HTML element if $p_var === $p_val or
 * a {value within an array passed via $p_var} === $p_val.
 *
 * If the second parameter is not given, the first parameter is compared to
 * the boolean value true.
 *
 * @param mixed   $p_var    The variable to compare.
 * @param mixed   $p_val    The value to compare $p_var with.
 * @param boolean $p_strict Set to false to bypass strict type checking (defaults to true).
 * @return void
 */
function check_checked( $p_var, $p_val = true, $p_strict = true ) {
	if( is_array( $p_var ) ) {
		foreach( $p_var as $t_this_var ) {
			if( helper_check_variables_equal( $t_this_var, $p_val, $p_strict ) ) {
				echo ' checked="checked"';
				return;
			}
		}
	} else {
		if( helper_check_variables_equal( $p_var, $p_val, $p_strict ) ) {
			echo ' checked="checked"';
			return;
		}
	}
}

/**
 * Attach a "selected" attribute to a HTML element if $p_var === $p_val or
 * a {value within an array passed via $p_var} === $p_val.
 *
 * If the second parameter is not given, the first parameter is compared to
 * the boolean value true.
 *
 * @param mixed   $p_var    The variable to compare.
 * @param mixed   $p_val    The value to compare $p_var with.
 * @param boolean $p_strict Set to false to bypass strict type checking (defaults to true).
 * @return void
 */
function check_selected( $p_var, $p_val = true, $p_strict = true ) {
	if( is_array( $p_var ) ) {
		foreach ( $p_var as $t_this_var ) {
			if( helper_check_variables_equal( $t_this_var, $p_val, $p_strict ) ) {
				echo ' selected="selected"';
				return;
			}
		}
	} else {
		if( helper_check_variables_equal( $p_var, $p_val, $p_strict ) ) {
			echo ' selected="selected"';
		}
	}
}

/**
 * If $p_val is true then we PRINT DISABLED to prevent selection of the
 * current option list item
 *
 * @param boolean $p_val Whether to disable the current option value.
 * @return void
 */
function check_disabled( $p_val = true ) {
	if( $p_val ) {
		echo ' disabled="disabled" ';
	}
}

/**
 * Set up PHP for a long process execution
 * The script timeout is set based on the value of the long_process_timeout config option.
 * $p_ignore_abort specified whether to ignore user aborts by hitting
 * the Stop button (the default is not to ignore user aborts)
 * @param boolean $p_ignore_abort Whether to ignore user aborts from the web browser.
 * @return integer
 */
function helper_begin_long_process( $p_ignore_abort = false ) {
	$t_timeout = config_get_global( 'long_process_timeout' );

	# silent errors or warnings reported when safe_mode is ON.
	@set_time_limit( $t_timeout );

	ignore_user_abort( $p_ignore_abort );
	return $t_timeout;
}

# this allows pages to override the current project settings.
# This typically applies to the view bug pages where the "current"
# project as used by the filters, etc, does not match the bug being viewed.
$g_project_override = null;
$g_cache_current_project = null;

/**
 * Return the current project id as stored in a cookie
 * If no cookie exists, the user's default project is returned
 * @return integer
 */
function helper_get_current_project() {
	global $g_project_override, $g_cache_current_project;

	if( $g_project_override !== null ) {
		return $g_project_override;
	}

	if( $g_cache_current_project === null ) {
		$t_cookie_name = config_get_global( 'project_cookie' );

		$t_project_id = gpc_get_cookie( $t_cookie_name, null );

		if( null === $t_project_id ) {
			$t_pref = user_pref_get( auth_get_current_user_id(), ALL_PROJECTS );
			$t_project_id = $t_pref->default_project;
		} else {
			$t_project_id = explode( ';', $t_project_id );
			$t_project_id = $t_project_id[count( $t_project_id ) - 1];
		}

		if( !project_exists( $t_project_id ) || ( 0 == project_get_field( $t_project_id, 'enabled' ) ) || !access_has_project_level( config_get( 'view_bug_threshold', null, null, $t_project_id ), $t_project_id ) ) {
			$t_project_id = ALL_PROJECTS;
		}
		$g_cache_current_project = (int)$t_project_id;
	}
	return $g_cache_current_project;
}

/**
 * Return the current project id as stored in a cookie, in an Array
 * If no cookie exists, the user's default project is returned
 * If the current project is a subproject, the return value will include
 * any parent projects
 * @return array
 */
function helper_get_current_project_trace() {
	$t_cookie_name = config_get_global( 'project_cookie' );

	$t_project_id = gpc_get_cookie( $t_cookie_name, null );

	if( null === $t_project_id ) {
		$t_bottom = current_user_get_pref( 'default_project' );
		$t_parent = $t_bottom;
		$t_project_id = array(
			$t_bottom,
		);

		while( true ) {
			$t_parent = project_hierarchy_get_parent( $t_parent );
			if( 0 == $t_parent ) {
				break;
			}
			array_unshift( $t_project_id, $t_parent );
		}

	} else {
		$t_project_id = explode( ';', $t_project_id );
		$t_bottom = $t_project_id[count( $t_project_id ) - 1];
	}

	if( !project_exists( $t_bottom ) || ( 0 == project_get_field( $t_bottom, 'enabled' ) ) || !access_has_project_level( config_get( 'view_bug_threshold', null, null, $t_bottom ), $t_bottom ) ) {
		$t_project_id = array(
			ALL_PROJECTS,
		);
	}

	return $t_project_id;
}

/**
 * Set the current project id (stored in a cookie)
 * @param integer $p_project_id A valid project identifier.
 * @return boolean always true
 */
function helper_set_current_project( $p_project_id ) {
	global $g_cache_current_project;

	$t_project_cookie_name = config_get_global( 'project_cookie' );

	$g_cache_current_project = $p_project_id;
	gpc_set_cookie( $t_project_cookie_name, $p_project_id, true );

	return true;
}

/**
 * Clear all known user preference cookies
 * @return void
 */
function helper_clear_pref_cookies() {
	gpc_clear_cookie( config_get_global( 'project_cookie' ) );
	gpc_clear_cookie( config_get( 'manage_users_cookie' ) );
	gpc_clear_cookie( config_get_global( 'manage_config_cookie' ) );
}

/**
 * Check whether the user has confirmed this action.
 *
 * If the user has not confirmed the action, generate a page which asks the user to confirm and
 * then submits a form back to the current page with all the GET and POST data and an additional
 * field called _confirmed to indicate that confirmation has been done.
 * @param string $p_message      Confirmation message to display to the end user.
 * @param string $p_button_label Button label to display to the end user.
 * @return boolean
 */
function helper_ensure_confirmed( $p_message, $p_button_label ) {
	if( true == gpc_get_bool( '_confirmed' ) ) {
		return true;
	}

	layout_page_header();
	layout_page_begin();

	echo '<div class="col-md-12 col-xs-12">';
	echo '<div class="space-10"></div>';
	echo '<div class="alert alert-warning center">';
	echo '<p class="bigger-110">';
	echo "\n" . $p_message . "\n";
	echo '</p>';
	echo '<div class="space-10"></div>';

	echo '<form method="post" class="center" action="">' . "\n";
	# CSRF protection not required here - user needs to confirm action
	# before the form is accepted.
	print_hidden_inputs( $_POST );
	print_hidden_inputs( $_GET );

	echo '<input type="hidden" name="_confirmed" value="1" />' , "\n";
	echo '<input type="submit" class="btn btn-primary btn-white btn-round" value="' . $p_button_label . '" />';
	echo "\n</form>\n";

	echo '<div class="space-10"></div>';
	echo '</div></div>';

	layout_page_end();
	exit;
}

/**
 * Call custom function.
 *
 * $p_function - Name of function to call (eg: do_stuff).  The function will call custom_function_override_do_stuff()
 *		if found, otherwise, will call custom_function_default_do_stuff().
 * $p_args_array - Parameters to function as an array
 * @param string $p_function   Custom function name.
 * @param array  $p_args_array An array of arguments to pass to the custom function.
 * @return mixed
 */
function helper_call_custom_function( $p_function, array $p_args_array ) {
	$t_function = 'custom_function_override_' . $p_function;

	if( !function_exists( $t_function ) ) {
		$t_function = 'custom_function_default_' . $p_function;
	}

	return call_user_func_array( $t_function, $p_args_array );
}

/**
 * return string to use in db queries containing projects of given user
 * @param integer $p_project_id A valid project identifier.
 * @param integer $p_user_id    A valid user identifier.
 * @return string
 */
function helper_project_specific_where( $p_project_id, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_project_ids = user_get_all_accessible_projects( $p_user_id, $p_project_id );

	if( 0 == count( $t_project_ids ) ) {
		$t_project_filter = ' 1<>1';
	} else if( 1 == count( $t_project_ids ) ) {
		$t_project_filter = ' project_id=' . reset( $t_project_ids );
	} else {
		$t_project_filter = ' project_id IN (' . implode( ',', $t_project_ids ) . ')';
	}

	return $t_project_filter;
}

/**
 * Get array of columns for given target
 * @param integer $p_columns_target Target view for the columns.
 * @param boolean $p_viewable_only  Whether to return viewable columns only.
 * @param integer $p_user_id        A valid user identifier.
 * @return array
 */
function helper_get_columns_to_view( $p_columns_target = COLUMNS_TARGET_VIEW_PAGE, $p_viewable_only = true, $p_user_id = null ) {
	$t_columns = helper_call_custom_function( 'get_columns_to_view', array( $p_columns_target, $p_user_id ) );

	# Fix column names for custom field columns that may be stored as lowercase in configuration. See issue #17367
	# If the system was working fine with lowercase names, then database is case-insensitive, eg: mysql
	# Fix by forcing a search with current name to get the id, then get the actual name by looking up this id
	foreach( $t_columns as &$t_column_name ) {
		$t_cf_name = column_get_custom_field_name( $t_column_name );
		if( $t_cf_name ) {
			$t_cf_id = custom_field_get_id_from_name( $t_cf_name );
			$t_column_name = column_get_custom_field_column_name( $t_cf_id );
		}
	}

	if( !$p_viewable_only ) {
		return $t_columns;
	}

	$t_keys_to_remove = array();

	if( $p_columns_target == COLUMNS_TARGET_CSV_PAGE || $p_columns_target == COLUMNS_TARGET_EXCEL_PAGE ) {
		$t_keys_to_remove[] = 'selection';
		$t_keys_to_remove[] = 'edit';
		$t_keys_to_remove[] = 'overdue';
	}

	$t_current_project_id = helper_get_current_project();

	if( $t_current_project_id != ALL_PROJECTS && !access_has_project_level( config_get( 'view_handler_threshold' ), $t_current_project_id ) ) {
		$t_keys_to_remove[] = 'handler_id';
	}

	if( $t_current_project_id != ALL_PROJECTS && !access_has_project_level( config_get( 'roadmap_view_threshold' ), $t_current_project_id ) ) {
		$t_keys_to_remove[] = 'target_version';
	}

	foreach( $t_keys_to_remove as $t_key_to_remove ) {
		$t_keys = array_keys( $t_columns, $t_key_to_remove );

		foreach( $t_keys as $t_key ) {
			unset( $t_columns[$t_key] );
		}
	}

	# get the array values to remove gaps in the array which causes issue
	# if the array is accessed using an index.
	return array_values( $t_columns );
}

/**
 * if all projects selected, default to <prefix><username><suffix><extension>, otherwise default to
 * <prefix><projectname><suffix><extension>.
 * @param string $p_extension_with_dot File name Extension.
 * @param string $p_prefix             File name Prefix.
 * @param string $p_suffix             File name suffix.
 * @return string
 */
function helper_get_default_export_filename( $p_extension_with_dot, $p_prefix = '', $p_suffix = '' ) {
	$t_filename = $p_prefix;

	$t_current_project_id = helper_get_current_project();

	if( ALL_PROJECTS == $t_current_project_id ) {
		$t_filename .= user_get_name( auth_get_current_user_id() );
	} else {
		$t_filename .= project_get_field( $t_current_project_id, 'name' );
	}

	return $t_filename . $p_suffix . $p_extension_with_dot;
}

/**
 * returns a tab index value and increments it by one.  This is used to give sequential tab index on a form.
 * @return integer
 */
function helper_get_tab_index_value() {
	static $s_tab_index = 0;
	return ++$s_tab_index;
}

/**
 * returns a tab index and increments internal state by 1.  This is used to give sequential tab index on
 * a form.  For example, this function returns: tabindex="1"
 * @return string
 */
function helper_get_tab_index() {
	return 'tabindex="' . helper_get_tab_index_value() . '"';
}

/**
 * returns a boolean indicating whether SQL queries executed should be shown or not.
 * @return boolean
 */
function helper_log_to_page() {
	# Check is authenticated before checking access level, otherwise user gets
	# redirected to login_page.php.  See #8461.
	return config_get_global( 'log_destination' ) === 'page' && auth_is_user_authenticated() && access_has_global_level( config_get( 'show_log_threshold' ) );
}

/**
 * Return a URL relative to the web root, compatible with other applications
 * @param string $p_url A relative URL to a page within Mantis.
 * @return string
 */
function helper_mantis_url( $p_url ) {
	if( is_blank( $p_url ) ) {
		return $p_url;
	}

	# Return URL as-is if it already starts with short path
	$t_short_path = config_get_global( 'short_path' );
	if( strpos( $p_url, $t_short_path ) === 0 ) {
		return $p_url;
	}

	return $t_short_path . $p_url;
}

/**
 * convert a duration string in "[h]h:mm" to an integer (minutes)
 * @param string $p_hhmm A string in [h]h:mm format to convert.
 * @param string $p_field The field name.
 * @return integer
 */
function helper_duration_to_minutes( $p_hhmm, $p_field = 'hhmm' ) {
	if( is_blank( $p_hhmm ) ) {
		return 0;
	}

	$t_a = explode( ':', $p_hhmm );
	$t_min = 0;

	# time can be composed of max 3 parts (hh:mm:ss)
	if( count( $t_a ) > 3 ) {
		throw new ClientException(
			sprintf( "Invalid value '%s' for field '%s'.", $p_hhmm, $p_field ),
			ERROR_INVALID_FIELD_VALUE,
			array( $p_field )
		);
	}

	$t_count = count( $t_a );
	for( $i = 0;$i < $t_count;$i++ ) {
		# all time parts should be integers and non-negative.
		if( !is_numeric( $t_a[$i] ) || ( (integer)$t_a[$i] < 0 ) ) {
			throw new ClientException(
				sprintf( "Invalid value '%s' for field '%s'.", $p_hhmm, $p_field ),
				ERROR_INVALID_FIELD_VALUE,
				array( $p_field )
			);
		}

		# minutes and seconds are not allowed to exceed 59.
		if( ( $i > 0 ) && ( $t_a[$i] > 59 ) ) {
			throw new ClientException(
				sprintf( "Invalid value '%s' for field '%s'.", $p_hhmm, $p_field ),
				ERROR_INVALID_FIELD_VALUE,
				array( $p_field )
			);
		}
	}

	switch( $t_count ) {
		case 1:
			$t_min = (integer)$t_a[0];
			break;
		case 2:
			$t_min = (integer)$t_a[0] * 60 + (integer)$t_a[1];
			break;
		case 3:
			# if seconds included, approximate it to minutes
			$t_min = (integer)$t_a[0] * 60 + (integer)$t_a[1];

			if( (integer)$t_a[2] >= 30 ) {
				$t_min++;
			}
			break;
	}

	return (int)$t_min;
}

/**
 * Global shutdown functions registration
 * Registers shutdown functions
 * @return void
 */
function shutdown_functions_register() {
	register_shutdown_function( 'email_shutdown_function' );
}

/**
 * Filter a set of strings by finding strings that start with a case-insensitive prefix.
 * @param array  $p_set    An array of strings to search through.
 * @param string $p_prefix The prefix to filter by.
 * @return array An array of strings which match the supplied prefix.
 */
function helper_filter_by_prefix( array $p_set, $p_prefix ) {
	$t_matches = array();
	foreach ( $p_set as $p_item ) {
		if( mb_strtolower( mb_substr( $p_item, 0, mb_strlen( $p_prefix ) ) ) === mb_strtolower( $p_prefix ) ) {
			$t_matches[] = $p_item;
		}
	}
	return $t_matches;
}

/**
 * Combine a Mantis page with a query string.  This handles the case where the page is a native
 * page or a plugin page.
 * @param string $p_page The page (relative or full)
 * @param string $p_query_string The query string
 * @return string The combined url.
 */
function helper_url_combine( $p_page, $p_query_string ) {
	$t_url = $p_page;

	if( !is_blank( $p_query_string ) ) {
		if( stripos( $p_page, '?' ) !== false ) {
			$t_url .= '&' . $p_query_string;
		} else {
			$t_url .= '?' . $p_query_string;
		}
	}

	return $t_url;
}

/**
 * Generate a hash to be used with dynamically generated content that is expected
 * to be cached by the browser. This hash can be used to differentiate the generated
 * content when it may be different based on some runtime attributes like: current user,
 * project or language.
 * An optional custom string can be provided to be added to the hash, for additional
 * differentiating criteria, but this string must be already prepared by the caller.
 *
 * @param array $p_runtime_attrs    Array of attributes to be calculated from current session.
 *                                  possible values: 'user', 'project', 'lang'
 * @param string $p_custom_string   Additional string provided by the caller
 * @return string                   A hashed md5 string
 */
function helper_generate_cache_key( array $p_runtime_attrs = [], $p_custom_string = '' ) {
	# always add core version, to force reload of resources after an upgrade.
	$t_key = $p_custom_string . '+V' . MANTIS_VERSION;
	$t_user_auth = auth_is_user_authenticated();
	foreach( $p_runtime_attrs as $t_attr ) {
		switch( $t_attr ) {
			case 'user':
				$t_key .= '+U' . ( $t_user_auth ? auth_get_current_user_id() : META_FILTER_NONE );
				break;
			case 'project':
				$t_key .= '+P' . ( $t_user_auth ? helper_get_current_project() : META_FILTER_NONE );
				break;
			case 'lang':
				$t_key .= '+L' . lang_get_current();
				break;
			default:
				trigger_error( ERROR_GENERIC, ERROR );
		}
	}
	return md5( $t_key );
}

/**
 * Parse view state from provided array.
 *
 * @param array $p_view_state The view state array (typically would have an id, name or both).
 * @return integer view state id
 * @throws ClientException if view state is invalid or array is empty.
 */
function helper_parse_view_state( array $p_view_state ) {
	$t_view_state_enum = config_get( 'view_state_enum_string' );

	$t_view_state_id = VS_PUBLIC;
	if( isset( $p_view_state['id'] ) ) {
		$t_enum_by_ids = MantisEnum::getAssocArrayIndexedByValues( $t_view_state_enum );
		$t_view_state_id = (int)$p_view_state['id'];
		if( !isset( $t_enum_by_ids[$t_view_state_id] ) ) {
			throw new ClientException(
				sprintf( "Invalid view state id '%d'.", $t_view_state_id ),
				ERROR_INVALID_FIELD_VALUE,
				array( lang_get( 'view_state' ) ) );
		}
	} else if( isset( $p_view_state['name' ] ) ) {
		$t_enum_by_labels = MantisEnum::getAssocArrayIndexedByLabels( $t_view_state_enum );
		$t_name = $p_view_state['name'];
		if( !isset( $t_enum_by_labels[$t_name] ) ) {
			throw new ClientException(
				sprintf( "Invalid view state id '%d'.", $t_view_state_id ),
				ERROR_INVALID_FIELD_VALUE,
				array( lang_get( 'view_state' ) ) );
		}

		$t_view_state_id = $t_enum_by_labels[$t_name];
	} else {
		throw new ClientException(
			"Empty view state",
			ERROR_EMPTY_FIELD );
	}

	return $t_view_state_id;
}

/**
 * Parse numeric positive id.
 *
 * @param string $p_id The id to parse.
 * @param string $p_field_name The field name.
 * @return integer The parsed id.
 * @throws ClientException Id is not specified or invalid.
 */
function helper_parse_id( $p_id, $p_field_name ) {
	$t_id = trim( $p_id );
	if( !is_numeric( $t_id ) ) {
		if( empty( $t_id ) ) {
			throw new ClientException( "'$p_field_name' missing", ERROR_GPC_VAR_NOT_FOUND, array( $p_field_name ) );
		}

		throw new ClientException( "'$p_field_name' must be numeric", ERROR_INVALID_FIELD_VALUE, array( $p_field_name ) );
	}

	$t_id = (int)$t_id;
	if( $t_id < 1 ) {
		throw new ClientException( "'$p_field_name' must be >= 1", ERROR_INVALID_FIELD_VALUE, array( $p_field_name ) );
	}

	return $t_id;
}

/**
 * Parse issue id.
 *
 * @param string $p_issue_id The id to parse.
 * @param string $p_field_name The field name.
 * @return integer The issue id.
 * @throws ClientException Issue is not specified or invalid.
 */
function helper_parse_issue_id( $p_issue_id, $p_field_name = 'issue_id' ) {
	return helper_parse_id( $p_issue_id, $p_field_name );
}
