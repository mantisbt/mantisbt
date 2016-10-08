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
 * Filter print helper functions API
 *
 * @package CoreAPI
 * @subpackage FilterAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses logging_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'logging_api.php' );
require_api( 'print_api.php' );
require_api( 'relationship_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );


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
 * Return the input modifier to be used when a filter is of type "advanced"
 * @param type $p_filter	Filter array to use
 * @return string
 */
function filter_select_modifier( $p_filter ) {
	if( 'advanced' == $p_filter['_view_type'] ) {
		return ' multiple="multiple" size="10"';
	} else {
		return '';
	}
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array	$p_filter	Filter array
 * @return void
 */
function print_filter_values_reporter_id( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * Print the reporter field
 * @return void
 */
function print_filter_reporter_id() {
	global $g_filter;
	?>
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_REPORTER_ID;?>[]">
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_user_monitor( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * Print the user monitor field
 * @return void
 */
function print_filter_user_monitor() {
	global $g_filter;
	?>
	<!-- Monitored by -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_MONITOR_USER_ID;?>[]">
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_handler_id( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print the handler field
 * @return void
 */
function print_filter_handler_id() {
	global $g_filter, $f_view_type;
	?>
		<!-- Handler -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_HANDLER_ID;?>[]">
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_category( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print the category field
 * @return void
 */
function print_filter_show_category() {
	global $g_filter;
	?>
		<!-- Category -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_CATEGORY_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_CATEGORY_ID], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_category_filter_option_list( $g_filter[FILTER_PROPERTY_CATEGORY_ID] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_platform( $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_PLATFORM, $p_filter[FILTER_PROPERTY_PLATFORM] );
}

/**
 * print the platform field
 * @return void
 */
function print_filter_platform() {
	global $g_filter;

	?>
		<!-- Platform -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_PLATFORM;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_PLATFORM], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php
				log_event( LOG_FILTERING, 'Platform = ' . var_export( $g_filter[FILTER_PROPERTY_PLATFORM], true ) );
	print_platform_option_list( $g_filter[FILTER_PROPERTY_PLATFORM] );
	?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_os( $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_OS, $p_filter[FILTER_PROPERTY_OS] );
}

/**
 * print the os field
 * @return void
 */
function print_filter_os() {
	global $g_filter;

	?>
		<!-- OS -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_OS;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_OS], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_option_list( $g_filter[FILTER_PROPERTY_OS] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_os_build( $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_OS_BUILD, $p_filter[FILTER_PROPERTY_OS_BUILD] );
}

/**
 * print the os build field
 * @return void
 */
function print_filter_os_build() {
	global $g_filter;

	?>
		<!-- OS Build -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_OS_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_OS_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_build_option_list( $g_filter[FILTER_PROPERTY_OS_BUILD] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_severity( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print the severity field
 * @return void
 */
function print_filter_show_severity() {
	global $g_filter;
	?><!-- Severity -->
			<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_SEVERITY;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_SEVERITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'severity', $g_filter[FILTER_PROPERTY_SEVERITY] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_resolution( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print resolution field
 * @return void
 */
function print_filter_show_resolution() {
	global $g_filter;
	?><!-- Resolution -->
			<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_RESOLUTION;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_RESOLUTION], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'resolution', $g_filter[FILTER_PROPERTY_RESOLUTION] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_status( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print status field
 * @return void
 */
function print_filter_show_status() {
	global $g_filter;
	?>	<!-- Status -->
			<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_STATUS;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_STATUS], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'status', $g_filter[FILTER_PROPERTY_STATUS] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_hide_status( $p_filter ) {
	$t_filter = $p_filter;
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

/**
 * print hide status field
 * @return void
 */
function print_filter_hide_status() {
	global $g_filter;
	?><!-- Hide Status -->
			<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_HIDE_STATUS;?>[]">
				<option value="<?php echo META_FILTER_NONE?>">[<?php echo lang_get( 'none' )?>]</option>
				<?php print_enum_string_option_list( 'status', $g_filter[FILTER_PROPERTY_HIDE_STATUS] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_build( $p_filter ) {
	$t_filter = $p_filter;
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
}
/**
 * print build field
 * @return void
 */
function print_filter_show_build() {
	global $g_filter;
	?><!-- Build -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_build_option_list( $g_filter[FILTER_PROPERTY_BUILD] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_version( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print version field
 * @return void
 */
function print_filter_show_version() {
	global $g_filter;
	?><!-- Version -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $g_filter[FILTER_PROPERTY_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_fixed_in_version( $p_filter ) {
	$t_filter = $p_filter;
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
}
/**
 * print fixed in version field
 * @return void
 */
function print_filter_show_fixed_in_version() {
	global $g_filter;
	?><!-- Fixed in Version -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_FIXED_IN_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $g_filter[FILTER_PROPERTY_FIXED_IN_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_target_version( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print target version field
 * @return void
 */
function print_filter_show_target_version() {
	global $g_filter;
	?><!-- Fixed in Version -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_TARGET_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $g_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $g_filter[FILTER_PROPERTY_TARGET_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_priority( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print priority field
 * @return void
 */
function print_filter_show_priority() {
	global $g_filter;
	?><!-- Priority -->
	<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_PRIORITY;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_PRIORITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_enum_string_option_list( 'priority', $g_filter[FILTER_PROPERTY_PRIORITY] )?>
	</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_profile( $p_filter ) {
	$t_filter = $p_filter;
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
}
/**
 * print profile field
 * @return void
 */
function print_filter_show_profile() {
	global $g_filter;
	?><!-- Profile -->
		<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_PROFILE_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $g_filter[FILTER_PROPERTY_PROFILE_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_profile_option_list_for_project( helper_get_current_project(), $g_filter[FILTER_PROPERTY_PROFILE_ID] );?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_per_page( $p_filter ) {
	$t_filter = $p_filter;
	echo ( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] == 0 ) ? lang_get( 'all' ) : string_display_line( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] );
	echo '<input type="hidden" name="', FILTER_PROPERTY_ISSUES_PER_PAGE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] ), '" />';
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_view_state( $p_filter ) {
	$t_filter = $p_filter;
	if( VS_PUBLIC === $t_filter[FILTER_PROPERTY_VIEW_STATE] ) {
		echo lang_get( 'public' );
	} else if( VS_PRIVATE === $t_filter[FILTER_PROPERTY_VIEW_STATE] ) {
		echo lang_get( 'private' );
	} else {
		echo lang_get( 'any' );
		$t_filter[FILTER_PROPERTY_VIEW_STATE] = META_FILTER_ANY;
	}
	echo '<input type="hidden" name="', FILTER_PROPERTY_VIEW_STATE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_VIEW_STATE] ), '" />';
}

/**
 * print view state field
 * @return void
 */
function print_filter_view_state() {
	global $g_filter;
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_sticky_issues( $p_filter ) {
	$t_filter = $p_filter;
	$t_sticky_filter_state = gpc_string_to_bool( $t_filter[FILTER_PROPERTY_STICKY] );
	print( $t_sticky_filter_state ? lang_get( 'yes' ) : lang_get( 'no' ) );
	?>
	<input type="hidden" name="<?php
		echo FILTER_PROPERTY_STICKY; ?>" value="<?php
		echo $t_sticky_filter_state ? 'on' : 'off'; ?>" />
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_highlight_changed( $p_filter ) {
	$t_filter = $p_filter;
	echo $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED];
		?>
		<input type="hidden"
			name="<?php echo FILTER_PROPERTY_HIGHLIGHT_CHANGED; ?>"
			value="<?php echo string_attribute( $t_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED] ); ?>">
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_do_filter_by_date( $p_filter ) {
	$t_filter = $p_filter;
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

		if( ON != $g_filter[FILTER_PROPERTY_FILTER_BY_DATE] ) {
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_relationship_type( $p_filter ) {
	$t_filter = $p_filter;
	echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_TYPE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] ), '" />';
	echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_BUG, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] ), '" />';
	$c_rel_type = $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
	$c_rel_bug = $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG];
	if( -1 == $c_rel_type || 0 == $c_rel_bug ) {
		echo lang_get( 'any' );
	} else {
		echo relationship_get_description_for_history( $c_rel_type ) . ' ' . $c_rel_bug;
	}
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_tag_string( $p_filter ) {
	$t_filter = $p_filter;
	$t_tag_string = $t_filter[FILTER_PROPERTY_TAG_STRING];
	if( $t_filter[FILTER_PROPERTY_TAG_SELECT] != 0 && tag_exists( $t_filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
		$t_tag_string .= ( is_blank( $t_tag_string ) ? '' : config_get( 'tag_separator' ) );
		$t_tag_string .= tag_get_field( $t_filter[FILTER_PROPERTY_TAG_SELECT], 'name' );
	}
	echo string_html_entities( $t_tag_string );
	echo '<input type="hidden" name="', FILTER_PROPERTY_TAG_STRING, '" value="', string_attribute( $t_tag_string ), '" />';
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param type $p_filter	Filter array
 */
function print_filter_values_note_user_id( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * print note reporter field
 * @return void
 */
function print_filter_note_user_id() {
	global $g_filter, $f_view_type;
	?>
	<!-- BUGNOTE REPORTER -->
	<select<?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_NOTE_USER_ID;?>[]">
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter			Filter array
 * @param string $p_field_name		Field name
 * @param object $p_filter_object	Filter object
 * @return void
 */
function print_filter_values_plugin_field( $p_filter, $p_field_name, $p_filter_object ) {
	$t_filter = $p_filter;
	if( !isset( $p_filter[$p_field_name] ) ) {
		echo lang_get( 'any' );
	} else {
		$t_value = $p_filter[$p_field_name];
		switch( $p_filter_object->type ) {
			case FILTER_TYPE_STRING:
			case FILTER_TYPE_INT:
				if( filter_field_is_any( $t_value ) ) {
					echo lang_get( 'any' );
				} else {
					echo string_display_line( $t_value );
				}
				echo '<input type="hidden" name="' . string_attribute( $p_field_name ) . '" value="' . string_attribute( $t_value ) . '">';
				break;

			case FILTER_TYPE_BOOLEAN:
				echo string_display_line( $p_filter_object->display( (bool)$t_value ) );
				echo '<input type="hidden" name="' . string_attribute( $p_field_name ) . '" value="' . (bool)$t_value . '">';
				break;

			case FILTER_TYPE_MULTI_STRING:
			case FILTER_TYPE_MULTI_INT:
				if( !is_array( $t_value ) ) {
					$t_value = array( $t_value );
				}
				$t_strings = array();
				foreach( $t_value as $t_current ) {
					if( filter_field_is_any( $t_current ) ) {
						$t_strings[] = lang_get( 'any' );
					} else {
						$t_strings[] = string_display_line( $p_filter_object->display( $t_current ) );
					}
					echo '<input type="hidden" name="' . string_attribute( $p_field_name ) . '[]" value="' . string_attribute( $t_current ) . '">';
				}
				echo implode( '<br>', $t_strings );
				break;
		}
	}
}

/**
 * Print plugin filter fields as defined by MantisFilter objects.
 * @param string $p_field_name    Field name.
 * @param object $p_filter_object Filter object.
 * @return void
 */
function print_filter_plugin_field( $p_field_name, $p_filter_object ) {
	global $g_filter;

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
			echo '<select' . filter_select_modifier( $g_filter ) . ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' name="',
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
			echo '<select' . filter_select_modifier( $g_filter ) . ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' name="',
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
 * Print the current value of custom field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array to use
 * @param integer $p_field_id	Custom field id
 * @return void
 */
function print_filter_values_custom_field( $p_filter, $p_field_id ) {
	if( CUSTOM_FIELD_TYPE_DATE == custom_field_type( $p_field_id ) ) {
		print_filter_values_custom_field_date( $p_filter, $p_field_id );
		return;
	}

	if( isset( $p_filter['custom_fields'][$p_field_id] ) ) {
		$t_values = $p_filter['custom_fields'][$p_field_id];
	} else {
		$t_values = array();
	}
	$t_strings = array();
	$t_inputs = array();

	if( filter_field_is_any( $t_values ) ) {
		$t_strings[] = lang_get( 'any' );
	} else {
		foreach( $t_values as $t_val ) {
			$t_val = stripslashes( $t_val );
			if( filter_field_is_none( $t_val ) ) {
				$t_strings[] = lang_get( 'none' );
			} else {
				$t_strings[] = $t_val;
			}
			$t_inputs[] = '<input type="hidden" name="custom_field_' . $p_field_id . '[]" value="' . string_attribute( $t_val ) . '" />';
		}
	}

	echo implode( '<br>', $t_strings );
	echo implode( '', $t_inputs );
}

/**
 * Print the current value of this filter field (for a date type field), as visible string,
 * and as a hidden form input.
 * @param array $p_filter	Filter array to use
 * @param integer $p_field_id	Custom field id
 * @return void
 */
function print_filter_values_custom_field_date( $p_filter, $p_field_id ) {
	$t_short_date_format = config_get( 'short_date_format' );
	if( !isset( $p_filter['custom_fields'][$p_field_id][1] ) ) {
		$p_filter['custom_fields'][$p_field_id][1] = 0;
	}
	$t_start = date( $t_short_date_format, $p_filter['custom_fields'][$p_field_id][1] );

	if( !isset( $p_filter['custom_fields'][$p_field_id][2] ) ) {
		$p_filter['custom_fields'][$p_field_id][2] = 0;
	}
	$t_end = date( $t_short_date_format, $p_filter['custom_fields'][$p_field_id][2] );
	switch( $p_filter['custom_fields'][$p_field_id][0] ) {
		case CUSTOM_FIELD_DATE_ANY:
			echo lang_get( 'any' );
			break;
		case CUSTOM_FIELD_DATE_NONE:
			echo lang_get( 'none' );
			break;
		case CUSTOM_FIELD_DATE_BETWEEN:
			echo lang_get( 'between_date' ) . '<br>';
			echo $t_start . '<br>' . $t_end;
			break;
		case CUSTOM_FIELD_DATE_ONORBEFORE:
			echo lang_get( 'on_or_before_date' ) . '<br>';
			echo $t_end;
			break;
		case CUSTOM_FIELD_DATE_BEFORE:
			echo lang_get( 'before_date' ) . '<br>';
			echo $t_end;
			break;
		case CUSTOM_FIELD_DATE_ON:
			echo lang_get( 'on_date' ) . '<br>';
			echo $t_start;
			break;
		case CUSTOM_FIELD_DATE_AFTER:
			echo lang_get( 'after_date' ) . '<br>';
			echo $t_start;
			break;
		case CUSTOM_FIELD_DATE_ONORAFTER:
			echo lang_get( 'on_or_after_date' ) . '<br>';
			echo $t_start;
			break;
	}
	# print hidden inputs
	$tcf = $p_filter['custom_fields'][$p_field_id];
	echo '<input type="hidden" name="custom_field_' . $p_field_id . '_control" value="' . $p_filter['custom_fields'][$p_field_id][0] . '">';
	echo '<input type="hidden" name="custom_field_' . $p_field_id . '_start_timestamp" value="' . $p_filter['custom_fields'][$p_field_id][1] . '">';
	echo '<input type="hidden" name="custom_field_' . $p_field_id . '_end_timestamp" value="' . $p_filter['custom_fields'][$p_field_id][2] . '">';
}


/**
 * Print custom field input list.
 * This function does not validates permissions
 * @global array $g_filter
 * @param integer $p_field_id	Custom field id
 * @return void
 */
function print_filter_custom_field( $p_field_id ) {
	global $g_filter;

	$t_cfdef = custom_field_get_definition( $p_field_id );

	switch( $t_cfdef['type'] ) {
		case CUSTOM_FIELD_TYPE_DATE:
			print_filter_custom_field_date( $p_field_id );
			break;

		case CUSTOM_FIELD_TYPE_TEXTAREA:
			echo '<input type="text" name="custom_field_', $p_field_id, '" size="10" value="" >';
			break;

		default:
			echo '<select' . filter_select_modifier( $g_filter ) . ' name="custom_field_' . $p_field_id . '[]">';
			# Option META_FILTER_ANY
			echo '<option value="' . META_FILTER_ANY . '"';
			check_selected( $g_filter['custom_fields'][$p_field_id], META_FILTER_ANY, false );
			echo '>[' . lang_get( 'any' ) . ']</option>';
			# don't show META_FILTER_NONE for enumerated types as it's not possible for them to be blank
			if( !in_array( $t_cfdef['type'], array( CUSTOM_FIELD_TYPE_ENUM, CUSTOM_FIELD_TYPE_LIST, CUSTOM_FIELD_TYPE_MULTILIST ) ) ) {
				echo '<option value="' . META_FILTER_NONE . '"';
				check_selected( $g_filter['custom_fields'][$p_field_id], META_FILTER_NONE, false );
				echo '>[' . lang_get( 'none' ) . ']</option>';
			}
			# Print possible values
			$t_values = custom_field_distinct_values( $t_cfdef );
			if( is_array( $t_values ) ){
				$t_max_length = config_get( 'max_dropdown_length' );
				foreach( $t_values as $t_val ) {
					if( filter_field_is_any($t_val) || filter_field_is_none( $t_val ) ) {
						continue;
					}
					echo '<option value="' . string_attribute( $t_val ) . '"';
					if( isset( $g_filter['custom_fields'][$p_field_id] ) ) {
						check_selected( $g_filter['custom_fields'][$p_field_id], $t_val, false );
					}
					echo '>' . string_attribute( string_shorten( $t_val, $t_max_length ) ) . '</option>';
				}
			}
			echo '</select>';
			break;
	}
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_sort( $p_filter ) {
	$t_filter = $p_filter;
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
 * @global array $g_filter
 * @param integer $p_field_id  Custom field identifier.
 * @return void
 */
function print_filter_custom_field_date( $p_field_id ) {
	global $g_filter;
	$t_cfdef = custom_field_get_definition( $p_field_id );
	$t_values = custom_field_distinct_values( $t_cfdef );

	# Resort the values so there ordered numerically, they are sorted as strings otherwise which
	# may be wrong for dates before early 2001.
	if( is_array( $t_values ) ) {
		array_multisort( $t_values, SORT_NUMERIC, SORT_ASC );
	}

	$t_sel_start_year = null;
	$t_sel_end_year = null;
	if( isset( $t_values[0] ) ) {
		$t_sel_start_year = date( 'Y', $t_values[0] );
	}
	$t_count = count( $t_values );
	if( isset( $t_values[$t_count - 1] ) ) {
		$t_sel_end_year = date( 'Y', $t_values[$t_count - 1] );
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
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_project_id( $p_filter ) {
	$t_filter = $p_filter;
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
}

/**
 * Print project field
 * @return void
 */
function print_filter_project_id() {
	global $g_filter;
	?>
		<!-- Project -->
		<select <?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_PROJECT_ID;?>[]">
			<option value="<?php echo META_FILTER_CURRENT ?>"
				<?php check_selected( $g_filter[FILTER_PROPERTY_PROJECT_ID], META_FILTER_CURRENT );?>>
				[<?php echo lang_get( 'current' )?>]
			</option>
			<?php print_project_option_list( $g_filter[FILTER_PROPERTY_PROJECT_ID] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_match_type( $p_filter ) {
	$t_filter = $p_filter;
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
	<?php
}

/**
 * Print filter match type selector
 * @return void
 */
function print_filter_match_type() {
	global $g_filter;
?>
		<!-- Project -->
		<select <?php echo filter_select_modifier( $g_filter ) ?> name="<?php echo FILTER_PROPERTY_MATCH_TYPE;?>">
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
