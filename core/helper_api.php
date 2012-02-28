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
 * Misc. Helper Functions
 * @package CoreAPI
 * @subpackage HelperAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * alternate color function
 * If no index is given, continue alternating based on the last index given
 * @param int $p_index
 * @param string $p_odd_color
 * @param string $p_even_color
 * @return string
 */
function helper_alternate_colors( $p_index, $p_odd_color, $p_even_color ) {
	static $t_index = 1;

	if( null !== $p_index ) {
		$t_index = $p_index;
	}

	if( 1 == $t_index++ % 2 ) {
		return $p_odd_color;
	} else {
		return $p_even_color;
	}
}

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

	if( 1 == $t_index++ % 2 ) {
		return "class=\"$p_odd_class\"";
	} else {
		return "class=\"$p_even_class\"";
	}
}

/**
 * get the color string for the given status, user and project
 * @param int $p_status
 * @param int|null $p_user user id, defaults to null (all users)
 * @param int|null $p_project project id, defaults to null (all projects)
 * @return string
 */
function get_status_color( $p_status, $p_user = null, $p_project = null ) {
	$t_status_label = MantisEnum::getLabel( config_get( 'status_enum_string', null, $p_user, $p_project ), $p_status );
	$t_status_colors = config_get( 'status_colors', null, $p_user, $p_project );
	$t_color = '#ffffff';

	if ( isset( $t_status_colors[$t_status_label] ) ) {
		$t_color = $t_status_colors[$t_status_label];
	}

	return $t_color;
}

/**
 * Given a enum string and num, return the appropriate string for the
 * specified user/project
 * @param string $p_enum_name
 * @param int $p_val
 * @param int|null $p_user user id, defaults to null (all users)
 * @param int|null $p_project project id, defaults to null (all projects)
 * @return string
 */
function get_enum_element( $p_enum_name, $p_val, $p_user = null, $p_project = null ) {
	$config_var = config_get( $p_enum_name . '_enum_string', null, $p_user, $p_project );
	$string_var = lang_get( $p_enum_name . '_enum_string' );

	return MantisEnum::getLocalizedLabel( $config_var, $string_var, $p_val );
}

/**
 * If $p_var is not an array and is equal to $p_val then we PRINT SELECTED.
 * If $p_var is an array, then if any member is equal to $p_val we PRINT SELECTED.
 * This is used when we want to know if a variable indicated a certain
 * option element is selected
 *
 * If the second parameter is not given, the first parameter is compared
 *  to the boolean value true
 * @param mixed $p_var
 * @param mixed $p_val
 * @return null
 */
function check_selected( $p_var, $p_val = true ) {
	if( is_array( $p_var ) ) {
		foreach( $p_var as $t_this_var ) {

			# catch the case where one entry is 0 and the other is a string.
			if( is_string( $t_this_var ) && is_string( $p_val ) ) {
				if( $t_this_var === $p_val ) {
					echo ' selected="selected" ';
					return;
				}
			}
			else if( $t_this_var == $p_val ) {
				echo ' selected="selected" ';
				return;
			}
		}
	} else {
		if( is_string( $p_var ) && is_string( $p_val ) ) {
			if( $p_var === $p_val ) {
				echo ' selected="selected" ';
				return;
			}
		}
		else if( $p_var == $p_val ) {
			echo ' selected="selected" ';
			return;
		}
	}
}

/**
 * If $p_var and $p_val are equal to each other then we PRINT CHECKED
 * This is used when we want to know if a variable indicated a certain
 * element is checked
 *
 * If the second parameter is not given, the first parameter is compared
 * to the boolean value true
 * @param mixed $p_var
 * @param mixed $p_val
 * @return null
 */
function check_checked( $p_var, $p_val = true ) {
	if( $p_var == $p_val ) {
		echo ' checked="checked" ';
	}
}

/**
 * Set up PHP for a long process execution
 * The script timeout is set based on the value of the long_process_timeout config option.
 * $p_ignore_abort specified whether to ignore user aborts by hitting
 * the Stop button (the default is not to ignore user aborts)
 * @param bool $p_ignore_abort
 * @return int
 */
function helper_begin_long_process( $p_ignore_abort = false ) {
	$t_timeout = config_get( 'long_process_timeout' );

	# silent errors or warnings reported when safe_mode is ON.
	@set_time_limit( $t_timeout );

	ignore_user_abort( $p_ignore_abort );
	return $t_timeout;
}

# this allows pages to override the current project settings.
#  This typically applies to the view bug pages where the "current"
#  project as used by the filters, etc, does not match the bug being viewed.
$g_project_override = null;
$g_cache_current_project = null;

/**
 * Return the current project id as stored in a cookie
 *  If no cookie exists, the user's default project is returned
 * @return int
 */
function helper_get_current_project() {
	global $g_project_override, $g_cache_current_project;

	if( $g_project_override !== null ) {
		return $g_project_override;
	}

	if( $g_cache_current_project === null ) {
		$t_cookie_name = config_get( 'project_cookie' );

		$t_project_id = gpc_get_cookie( $t_cookie_name, null );

		if( null === $t_project_id ) {
			$t_pref = user_pref_get( auth_get_current_user_id(), ALL_PROJECTS, false );
			$t_project_id = $t_pref->default_project;
		} else {
			$t_project_id = explode( ';', $t_project_id );
			$t_project_id = $t_project_id[count( $t_project_id ) - 1];
		}

		if( !project_exists( $t_project_id ) || ( 0 == project_get_field( $t_project_id, 'enabled' ) ) || !access_has_project_level( VIEWER, $t_project_id ) ) {
			$t_project_id = ALL_PROJECTS;
		}
		$g_cache_current_project = (int) $t_project_id;
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
	$t_cookie_name = config_get( 'project_cookie' );

	$t_project_id = gpc_get_cookie( $t_cookie_name, null );

	if( null === $t_project_id ) {
		$t_bottom = current_user_get_pref( 'default_project' );
		$t_parent = $t_bottom;
		$t_project_id = Array(
			$t_bottom,
		);

		while( true ) {
			$t_parent = project_hierarchy_get_parent( $t_parent );
			if( 0 == $t_parent ) {
				break;
			}
			array_unshift($t_project_id, $t_parent);
		}

	} else {
		$t_project_id = explode( ';', $t_project_id );
		$t_bottom = $t_project_id[count( $t_project_id ) - 1];
	}

	if( !project_exists( $t_bottom ) || ( 0 == project_get_field( $t_bottom, 'enabled' ) ) || !access_has_project_level( VIEWER, $t_bottom ) ) {
		$t_project_id = Array(
			ALL_PROJECTS,
		);
	}

	return $t_project_id;
}

/**
 * Set the current project id (stored in a cookie)
 * @param int $p_project_id
 * @return bool always true
 */
function helper_set_current_project( $p_project_id ) {
	$t_project_cookie_name = config_get( 'project_cookie' );

	gpc_set_cookie( $t_project_cookie_name, $p_project_id, true );

	return true;
}

/**
 * Clear all known user preference cookies
 * @return null
 */
function helper_clear_pref_cookies() {
	gpc_clear_cookie( config_get( 'project_cookie' ) );
	gpc_clear_cookie( config_get( 'manage_cookie' ) );
}

/**
 * Check whether the user has confirmed this action.
 *
 * If the user has not confirmed the action, generate a page which asks
 * the user to confirm and then submits a form back to the current page
 * with all the GET and POST data and an additional field called _confirmed
 * to indicate that confirmation has been done.
 * @param string $p_message
 * @param string $p_button_label
 * @return bool
 * @todo improve this formatting - to only be about 50% of the screen width so that it doesn't become hard to read.
 */
function helper_ensure_confirmed( $p_message, $p_button_label ) {
	if( true == gpc_get_bool( '_confirmed' ) ) {
		return true;
	}

	html_page_top();

	echo "<br />\n<div align=\"center\">\n";
	print_hr();
	echo "\n$p_message\n";

	echo '<form method="post" action="' . string_attribute( form_action_self() ) . "\">\n";
	# CSRF protection not required here - user needs to confirm action
	# before the form is accepted.
	print_hidden_inputs( gpc_strip_slashes( $_POST ) );
	print_hidden_inputs( gpc_strip_slashes( $_GET ) );

	echo "<input type=\"hidden\" name=\"_confirmed\" value=\"1\" />\n";
	echo '<br /><br /><input type="submit" class="button" value="' . $p_button_label . '" />';
	echo "\n</form>\n";

	print_hr();
	echo "</div>\n";
	html_page_bottom();
	exit;
}

/**
 * Call custom function.
 *
 * $p_function - Name of function to call (eg: do_stuff).  The function will call custom_function_override_do_stuff()
 *		if found, otherwise, will call custom_function_default_do_stuff().
 * $p_args_array - Parameters to function as an array
 * @param string $p_function
 * @param array $p_args_array
 * @return mixed
 */
function helper_call_custom_function( $p_function, $p_args_array ) {
	$t_function = 'custom_function_override_' . $p_function;

	if( !function_exists( $t_function ) ) {
		$t_function = 'custom_function_default_' . $p_function;
	}

	return call_user_func_array( $t_function, $p_args_array );
}

/**
 * return string to use in db queries containing projects of given user
 * @param int $p_project_id
 * @param int $p_user_id
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
		$t_project_filter = ' project_id=' . $t_project_ids[0];
	} else {
		$t_project_filter = ' project_id IN (' . join( ',', $t_project_ids ) . ')';
	}

	return $t_project_filter;
}

/**
 *
 * @param int $p_columns_target
 * @param bool $p_viewable_only
 * @param int $p_user_id
 * @return array
 */
function helper_get_columns_to_view( $p_columns_target = COLUMNS_TARGET_VIEW_PAGE, $p_viewable_only = true, $p_user_id = null ) {
	$t_columns = helper_call_custom_function( 'get_columns_to_view', array( $p_columns_target, $p_user_id ) );

	# An associative array that is used to map older column names to newer ones.  This is useful when a column is renamed in the database
	# or if the value is replaced with a foreign key.
	$t_columns_map = array(
		'category' => 'category_id',
	);

	foreach( $t_columns as $t_key => $t_value ) {
		if( isset( $t_columns_map[$t_value] ) ) {
			$t_columns[$t_key] = $t_columns_map[$t_value];
		}
	}

	if( !$p_viewable_only ) {
		return $t_columns;
	}

	$t_keys_to_remove = array();

	if( $p_columns_target == COLUMNS_TARGET_CSV_PAGE || $p_columns_target == COLUMNS_TARGET_EXCEL_PAGE ) {
		$t_keys_to_remove[] = 'selection';
		$t_keys_to_remove[] = 'edit';
		$t_keys_to_remove[] = 'bugnotes_count';
		$t_keys_to_remove[] = 'attachment_count';
		$t_keys_to_remove[] = 'overdue';
	}

	$t_enable_sponsorship = config_get( 'enable_sponsorship' );
	if( OFF == $t_enable_sponsorship ) {
		$t_keys_to_remove[] = 'sponsorship_total';
	}

	if( $p_columns_target == COLUMNS_TARGET_CSV_PAGE || $p_columns_target == COLUMNS_TARGET_EXCEL_PAGE ) {
		$t_keys_to_remove[] = 'attachment_count';
	}

	$t_keys_to_remove[] = 'duplicate_id';

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
 * @param string $p_extension_with_dot
 * @param string $p_prefix
 * @param string $p_suffix
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
 * @return int
 */
function helper_get_tab_index_value() {
	static $tab_index = 0;
	return ++$tab_index;
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
 * @return bool
 */
function helper_show_queries() {
	# Check is authenticated before checking access level, otherwise user gets
	# redirected to login_page.php.  See #8461.
	return ON == config_get( 'show_queries_count' ) && auth_is_user_authenticated() && access_has_global_level( config_get( 'show_queries_threshold' ) );
}

/**
 * Return a URL relative to the web root, compatible with other applications
 * @param string $p_url
 * @return string
 */
function helper_mantis_url( $p_url ) {
	if( is_blank( $p_url ) ) {
		return $p_url;
	}
	return config_get_global( 'short_path' ) . $p_url;
}

/**
 * convert a duration string in "[h]h:mm" to an integer (minutes)
 * @param string $p_hhmm
 * @return int
 */
function helper_duration_to_minutes( $p_hhmm ) {
	if( is_blank( $p_hhmm ) ) {
		return 0;
	}

	$t_a = explode( ':', $p_hhmm );
	$t_min = 0;

	// time can be composed of max 3 parts (hh:mm:ss)
	if( count( $t_a ) > 3 ) {
		error_parameters( 'p_hhmm', $p_hhmm );
		trigger_error( ERROR_CONFIG_OPT_INVALID, ERROR );
	}

	$t_count = count( $t_a );
	for( $i = 0;$i < $t_count;$i++ ) {
		// all time parts should be integers and non-negative.
		if( !is_numeric( $t_a[$i] ) || ( (integer) $t_a[$i] < 0 ) ) {
			error_parameters( 'p_hhmm', $p_hhmm );
			trigger_error( ERROR_CONFIG_OPT_INVALID, ERROR );
		}

		// minutes and seconds are not allowed to exceed 59.
		if(( $i > 0 ) && ( $t_a[$i] > 59 ) ) {
			error_parameters( 'p_hhmm', $p_hhmm );
			trigger_error( ERROR_CONFIG_OPT_INVALID, ERROR );
		}
	}

	switch( $t_count ) {
		case 1:
			$t_min = (integer) $t_a[0];
			break;
		case 2:
			$t_min = (integer) $t_a[0] * 60 + (integer) $t_a[1];
			break;
		case 3:
			// if seconds included, approxiate it to minutes
			$t_min = (integer) $t_a[0] * 60 + (integer) $t_a[1];

			if( (integer) $t_a[2] >= 30 ) {
				$t_min++;
			}
			break;
	}

	return (int) $t_min;
}
