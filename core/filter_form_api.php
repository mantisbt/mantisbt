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
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
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
require_api( 'current_user_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
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
 * Returns HTML for each filter field, to be used in filter form.
 * $p_filter_target is a field name to match any of "the print_filter_..." functions,
 * excluding those related to custom fields and plugin fields.
 * When $p_show_options is enabled, the form inputs are returned to allow selection,
 * if the option is disabled, returns the current value and a hidden input for that value.
 * @param array $p_filter Filter array
 * @param string $p_filter_target Filter field name
 * @param boolean $p_show_inputs Whether to return a visible form input or a text value.
 * @return string The html content for the field requested
 */
function filter_form_get_input( array $p_filter, $p_filter_target, $p_show_inputs = true ) {
	if( $p_show_inputs ) {
		$t_function_prefix = 'print_filter_';
	} else {
		$t_function_prefix = 'print_filter_values_';
	}
	$t_params = array( $p_filter );
	$t_function_name = $t_function_prefix . $p_filter_target;

	# override non standard calls
	switch( $p_filter_target ) {
		case 'do_filter_by_date':
		case 'do_filter_by_last_updated_date':
			if( $p_show_inputs ) {
				$t_params = array( false, $p_filter );
			}
			break;
	}

	if( function_exists( $t_function_name ) ) {
		ob_start();
		call_user_func_array( $t_function_name, $t_params );
		return ob_get_clean();
	} else {
		# error - no function to populate the target (e.g., print_filter_foo)
		error_parameters( $p_filter_target );
		trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
		return false;
	}
}


/**
 * Return the input modifier to be used when a filter is of type "advanced"
 * @param array $p_filter	Filter array to use
 * @return string
 */
function filter_select_modifier( array $p_filter ) {
	if( 'advanced' == $p_filter['_view_type'] ) {
		return ' multiple="multiple" size="10"';
	} else {
		return '';
	}
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_values_reporter_id( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_reporter_id( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_REPORTER_ID;?>[]">
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
		<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_REPORTER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
		<?php
			if( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
				echo '<option value="' . META_FILTER_MYSELF . '" ';
				check_selected( $p_filter[FILTER_PROPERTY_REPORTER_ID], META_FILTER_MYSELF );
				echo '>[' . lang_get( 'myself' ) . ']</option>';
			}
		print_reporter_option_list( $p_filter[FILTER_PROPERTY_REPORTER_ID] );
	}?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_user_monitor( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_user_monitor( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
	<!-- Monitored by -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_MONITOR_USER_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_MONITOR_USER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php
				if( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
		echo '<option value="' . META_FILTER_MYSELF . '" ';
		check_selected( $p_filter[FILTER_PROPERTY_MONITOR_USER_ID], META_FILTER_MYSELF );
		echo '>[' . lang_get( 'myself' ) . ']</option>';
	}
	$t_threshold = config_get( 'show_monitor_list_threshold' );
	$t_has_project_level = access_has_project_level( $t_threshold );

	if( $t_has_project_level ) {
		print_reporter_option_list( $p_filter[FILTER_PROPERTY_MONITOR_USER_ID] );
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
function print_filter_values_handler_id( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_handler_id( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Handler -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_HANDLER_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_HANDLER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php if( access_has_project_level( config_get( 'view_handler_threshold' ) ) ) {?>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_HANDLER_ID], META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php
				if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
			echo '<option value="' . META_FILTER_MYSELF . '" ';
			check_selected( $p_filter[FILTER_PROPERTY_HANDLER_ID], META_FILTER_MYSELF );
			echo '>[' . lang_get( 'myself' ) . ']</option>';
		}

		print_assign_to_option_list( $p_filter[FILTER_PROPERTY_HANDLER_ID] );
	}?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_category( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_category( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Category -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_CATEGORY_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_CATEGORY_ID], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_category_filter_option_list( $p_filter[FILTER_PROPERTY_CATEGORY_ID] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_platform( array $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_PLATFORM, $p_filter[FILTER_PROPERTY_PLATFORM] );
}

/**
 * print the platform field
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_platform( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Platform -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PLATFORM;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_PLATFORM], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php
				log_event( LOG_FILTERING, 'Platform = ' . var_export( $p_filter[FILTER_PROPERTY_PLATFORM], true ) );
	print_platform_option_list( $p_filter[FILTER_PROPERTY_PLATFORM] );
	?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_os( array $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_OS, $p_filter[FILTER_PROPERTY_OS] );
}

/**
 * print the os field
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_os( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- OS -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_OS;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_OS], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_option_list( $p_filter[FILTER_PROPERTY_OS] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_os_build( array $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_OS_BUILD, $p_filter[FILTER_PROPERTY_OS_BUILD] );
}

/**
 * print the os build field
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_os_build( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- OS Build -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_OS_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_OS_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_build_option_list( $p_filter[FILTER_PROPERTY_OS_BUILD] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_severity( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_severity( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Severity -->
			<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_SEVERITY;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_SEVERITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'severity', $p_filter[FILTER_PROPERTY_SEVERITY] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_resolution( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_resolution( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Resolution -->
			<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_RESOLUTION;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_RESOLUTION], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'resolution', $p_filter[FILTER_PROPERTY_RESOLUTION] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_status( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_status( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>	<!-- Status -->
			<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_STATUS;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_STATUS], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'status', $p_filter[FILTER_PROPERTY_STATUS] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_hide_status( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_hide_status( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Hide Status -->
			<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_HIDE_STATUS;?>[]">
				<option value="<?php echo META_FILTER_NONE?>">[<?php echo lang_get( 'none' )?>]</option>
				<?php print_enum_string_option_list( 'status', $p_filter[FILTER_PROPERTY_HIDE_STATUS] )?>
			</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_build( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_build( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Build -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_build_option_list( $p_filter[FILTER_PROPERTY_BUILD] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_version( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_version( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Version -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $p_filter[FILTER_PROPERTY_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_fixed_in_version( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_fixed_in_version( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Fixed in Version -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_FIXED_IN_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $p_filter[FILTER_PROPERTY_FIXED_IN_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_target_version( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_target_version( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Fixed in Version -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_TARGET_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $p_filter[FILTER_PROPERTY_TARGET_VERSION], null, VERSION_ALL, false, true )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_priority( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_priority( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Priority -->
	<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PRIORITY;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_PRIORITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_enum_string_option_list( 'priority', $p_filter[FILTER_PROPERTY_PRIORITY] )?>
	</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_show_profile( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_profile( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Profile -->
		<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PROFILE_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_PROFILE_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_profile_option_list_for_project( helper_get_current_project(), $p_filter[FILTER_PROPERTY_PROFILE_ID] );?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_per_page( array $p_filter ) {
	$t_filter = $p_filter;
	echo ( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] == 0 ) ? lang_get( 'all' ) : string_display_line( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] );
	echo '<input type="hidden" name="', FILTER_PROPERTY_ISSUES_PER_PAGE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] ), '" />';
}

/**
 * print issues per page field
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_per_page( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Number of bugs per page -->
		<input type="text" name="<?php echo FILTER_PROPERTY_ISSUES_PER_PAGE;?>" size="3" maxlength="7" value="<?php echo $p_filter[FILTER_PROPERTY_ISSUES_PER_PAGE]?>" />
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_view_state( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_view_state( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- View Status -->
		<select name="<?php echo FILTER_PROPERTY_VIEW_STATE;?>">
			<?php
			echo '<option value="' . META_FILTER_ANY . '"';
	check_selected( $p_filter[FILTER_PROPERTY_VIEW_STATE], META_FILTER_ANY );
	echo '>[' . lang_get( 'any' ) . ']</option>';
	echo '<option value="' . VS_PUBLIC . '"';
	check_selected( $p_filter[FILTER_PROPERTY_VIEW_STATE], VS_PUBLIC );
	echo '>' . lang_get( 'public' ) . '</option>';
	echo '<option value="' . VS_PRIVATE . '"';
	check_selected( $p_filter[FILTER_PROPERTY_VIEW_STATE], VS_PRIVATE );
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
function print_filter_values_sticky_issues( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_sticky_issues( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Show or hide sticky bugs -->
			<input type="hidden" name="<?php echo FILTER_PROPERTY_STICKY ?>" value="<?php echo OFF ?>">
			<input type="checkbox" name="<?php echo FILTER_PROPERTY_STICKY;?>"<?php check_checked( gpc_string_to_bool( $p_filter[FILTER_PROPERTY_STICKY] ), true );?> />
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_highlight_changed( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_highlight_changed( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Highlight changed bugs -->
			<input type="text" name="<?php echo FILTER_PROPERTY_HIGHLIGHT_CHANGED;?>" size="3" maxlength="7" value="<?php echo $p_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED]?>" />
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_do_filter_by_date( array $p_filter ) {
	$t_filter = $p_filter;
	if( 'on' == $t_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ) {
		echo '<input type="hidden" name="', FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_DATE_SUBMITTED_START_DAY, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_DATE_SUBMITTED_END_DAY, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] ), '" />';

		$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
		$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] );
		foreach( $t_chars as $t_char ) {
			if( strcasecmp( $t_char, 'M' ) == 0 ) {
				echo ' ';
				echo lang_get( 'month_' . strtolower ( date( 'F', $t_time ) ) );
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

		$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] );
		foreach( $t_chars as $t_char ) {
			if( strcasecmp( $t_char, 'M' ) == 0 ) {
				echo ' ';
				echo lang_get( 'month_' . strtolower ( date( 'F', $t_time ) ) );
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
 * @global array $g_filter
 * @param boolean $p_hide_checkbox Hide data filter checkbox.
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_do_filter_by_date( $p_hide_checkbox = false, array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
?>
		<table cellspacing="0" cellpadding="0" class="js_switch_date_inputs_container">
<?php
	$t_menu_disabled =  '';
	if( !$p_hide_checkbox ) {
?>
		<tr>
			<td colspan="2">
				<input type="hidden" name="<?php echo FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED ?>" value="<?php echo OFF ?>" />
				<label>
					<input type="checkbox" id="use_date_filters" class="js_switch_date_inputs_trigger"
						name="<?php echo FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED ?>"
						<?php check_checked( gpc_string_to_bool( $p_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ), true ) ?> />
					<?php echo lang_get( 'use_date_filters' )?>
				</label>
			</td>
		</tr>
<?php

		if( ON != $p_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] ) {
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
			echo '<select name="', FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_DATE_SUBMITTED_START_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] );
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
			echo '<select name="', FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_DATE_SUBMITTED_END_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] );
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
function print_filter_values_do_filter_by_last_updated_date( array $p_filter ) {
	$t_filter = $p_filter;
	if( 'on' == $t_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ) {
		echo '<input type="hidden" name="', FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_LAST_UPDATED_START_MONTH, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_LAST_UPDATED_START_DAY, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_LAST_UPDATED_START_YEAR, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_LAST_UPDATED_END_MONTH, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_LAST_UPDATED_END_DAY, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_LAST_UPDATED_END_YEAR, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] ), '" />';

		$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
		$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH], $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY], $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] );
		foreach( $t_chars as $t_char ) {
			if( strcasecmp( $t_char, 'M' ) == 0 ) {
				echo ' ';
				echo lang_get( 'month_' . strtolower (date( 'F', $t_time ) ) );
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

		$t_time = mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH], $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY], $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] );
		foreach( $t_chars as $t_char ) {
			if( strcasecmp( $t_char, 'M' ) == 0 ) {
				echo ' ';
				echo lang_get( 'month_' . strtolower ( date( 'F', $t_time ) ) );
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
 * Print filter by last update date fields
 * @global array $g_filter
 * @param boolean $p_hide_checkbox Hide data filter checkbox.
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_do_filter_by_last_updated_date( $p_hide_checkbox = false, array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
?>
		<table cellspacing="0" cellpadding="0" class="js_switch_date_inputs_container">
<?php
	$t_menu_disabled =  '';
	if( !$p_hide_checkbox ) {
?>
		<tr>
			<td colspan="2">
				<input type="hidden" name="<?php echo FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE ?>" value="<?php echo OFF ?>" />
				<label>
					<input type="checkbox" id="use_last_updated_date_filters" class="js_switch_date_inputs_trigger"
						name="<?php echo FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE ?>"
						<?php check_checked( gpc_string_to_bool( $p_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ), true ) ?> />
					<?php echo lang_get( 'use_last_updated_date_filters' )?>
				</label>
			</td>
		</tr>
<?php

		if( ON != $p_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] ) {
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
			echo '<select name="', FILTER_PROPERTY_LAST_UPDATED_START_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_LAST_UPDATED_START_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_LAST_UPDATED_START_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] );
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
			echo '<select name="', FILTER_PROPERTY_LAST_UPDATED_END_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_LAST_UPDATED_END_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select name="', FILTER_PROPERTY_LAST_UPDATED_END_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] );
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
function print_filter_values_relationship_type( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_relationship_type( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	$c_reltype_value = $p_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
	if( !$c_reltype_value ) {
		$c_reltype_value = -1;
	}
	relationship_list_box( $c_reltype_value, 'relationship_type', true );
	echo '<input type="text" name="', FILTER_PROPERTY_RELATIONSHIP_BUG, '" size="5" maxlength="10" value="', $p_filter[FILTER_PROPERTY_RELATIONSHIP_BUG], '" />';
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_tag_string( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_tag_string( array $p_filter = null ) {
	global $g_filter;
	if( !access_has_global_level( config_get( 'tag_view_threshold' ) ) ) {
		return;
	}
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	$t_tag_string = $p_filter[FILTER_PROPERTY_TAG_STRING];
	if( $p_filter[FILTER_PROPERTY_TAG_SELECT] != 0 && tag_exists( $p_filter[FILTER_PROPERTY_TAG_SELECT] ) ) {
		$t_tag_string .= ( is_blank( $t_tag_string ) ? '' : config_get( 'tag_separator' ) );
		$t_tag_string .= tag_get_field( $p_filter[FILTER_PROPERTY_TAG_SELECT], 'name' );
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
 * @param array $p_filter	Filter array
 */
function print_filter_values_note_user_id( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_note_user_id( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
	<!-- BUGNOTE REPORTER -->
	<select<?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_NOTE_USER_ID;?>[]">
		<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_NOTE_USER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
		<?php if( access_has_project_level( config_get( 'view_handler_threshold' ) ) ) {?>
		<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_NOTE_USER_ID], META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
		<?php
			if( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
				echo '<option value="' . META_FILTER_MYSELF . '"';
				check_selected( $p_filter[FILTER_PROPERTY_NOTE_USER_ID], META_FILTER_MYSELF );
				echo '>[' . lang_get( 'myself' ) . ']</option>';
			}

			print_note_option_list( $p_filter[FILTER_PROPERTY_NOTE_USER_ID] );
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
function print_filter_values_plugin_field( array $p_filter, $p_field_name, $p_filter_object ) {
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
 * @global array $g_filter
 * @param string $p_field_name    Field name.
 * @param object $p_filter_object Filter object.
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_plugin_field( $p_field_name, $p_filter_object, array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}

	$t_size = (int)$p_filter_object->size;

	switch( $p_filter_object->type ) {
		case FILTER_TYPE_STRING:
			echo '<input name="', string_attribute( $p_field_name ), '"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' value="',
				string_attribute( $p_filter[$p_field_name] ), '"/>';
			break;

		case FILTER_TYPE_INT:
			echo '<input name="', string_attribute( $p_field_name ), '"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' value="',
				(int)$p_filter[$p_field_name], '"/>';
			break;

		case FILTER_TYPE_BOOLEAN:
			echo '<input name="', string_attribute( $p_field_name ), '" type="hidden" value="', OFF ,'"/>';
			echo '<input name="', string_attribute( $p_field_name ), '" type="checkbox"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), check_checked( (bool)$p_filter[$p_field_name] ) , '"/>';
			break;

		case FILTER_TYPE_MULTI_STRING:
			echo '<select' . filter_select_modifier( $p_filter ) . ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' name="',
				string_attribute( $p_field_name ), '[]">', '<option value="', META_FILTER_ANY, '"',
				check_selected( $p_filter[$p_field_name], (string)META_FILTER_ANY ), '>[', lang_get( 'any' ), ']</option>';

			foreach( $p_filter_object->options() as $t_option_value => $t_option_name ) {
				echo '<option value="', string_attribute( $t_option_value ), '" ',
					check_selected( $p_filter[$p_field_name], $t_option_value, false ), '>',
					string_display_line( $t_option_name ), '</option>';
			}

			echo '</select>';
			break;

		case FILTER_TYPE_MULTI_INT:
			echo '<select' . filter_select_modifier( $p_filter ) . ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' name="',
				string_attribute( $p_field_name ), '[]">', '<option value="', META_FILTER_ANY, '"',
				check_selected( $p_filter[$p_field_name], META_FILTER_ANY ), '>[', lang_get( 'any' ), ']</option>';

			foreach( $p_filter_object->options() as $t_option_value => $t_option_name ) {
				echo '<option value="', (int)$t_option_value, '" ',
					check_selected( $p_filter[$p_field_name], (int)$t_option_value ), '>',
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
function print_filter_values_custom_field( array $p_filter, $p_field_id ) {
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
function print_filter_values_custom_field_date( array $p_filter, $p_field_id ) {
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
	$t_cf = $p_filter['custom_fields'][$p_field_id];
	echo '<input type="hidden" name="custom_field_' . $p_field_id . '_control" value="' . $t_cf[0] . '">';
	echo '<input type="hidden" name="custom_field_' . $p_field_id . '_start_timestamp" value="' . $t_cf[1] . '">';
	echo '<input type="hidden" name="custom_field_' . $p_field_id . '_end_timestamp" value="' . $t_cf[2] . '">';
}


/**
 * Print custom field input list.
 * This function does not validates permissions
 * @global array $g_filter
 * @param integer $p_field_id	Custom field id
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_custom_field( $p_field_id, array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}

	$t_cfdef = custom_field_get_definition( $p_field_id );

	switch( $t_cfdef['type'] ) {
		case CUSTOM_FIELD_TYPE_DATE:
			print_filter_custom_field_date( $p_field_id, $p_filter );
			break;

		case CUSTOM_FIELD_TYPE_TEXTAREA:
			echo '<input type="text" name="custom_field_', $p_field_id, '" size="10" value="" >';
			break;

		default:
			echo '<select' . filter_select_modifier( $p_filter ) . ' name="custom_field_' . $p_field_id . '[]">';
			# Option META_FILTER_ANY
			echo '<option value="' . META_FILTER_ANY . '"';
			check_selected( $p_filter['custom_fields'][$p_field_id], META_FILTER_ANY, false );
			echo '>[' . lang_get( 'any' ) . ']</option>';
			# don't show META_FILTER_NONE for enumerated types as it's not possible for them to be blank
			if( !in_array( $t_cfdef['type'], array( CUSTOM_FIELD_TYPE_ENUM, CUSTOM_FIELD_TYPE_LIST, CUSTOM_FIELD_TYPE_MULTILIST ) ) ) {
				echo '<option value="' . META_FILTER_NONE . '"';
				check_selected( $p_filter['custom_fields'][$p_field_id], META_FILTER_NONE, false );
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
					if( isset( $p_filter['custom_fields'][$p_field_id] ) ) {
						check_selected( $p_filter['custom_fields'][$p_field_id], $t_val, false );
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
function print_filter_values_show_sort( array $p_filter ) {
	$p_sort_properties = filter_get_visible_sort_properties_array( $p_filter );
	$t_sort_fields = $p_sort_properties[FILTER_PROPERTY_SORT_FIELD_NAME];
	$t_dir_fields = $p_sort_properties[FILTER_PROPERTY_SORT_DIRECTION];

	# @TODO cproensa: this could be a constant, or conffig.
	$t_max_displayed_sort = 2;

	$t_count = count( $t_sort_fields );
	for( $i = 0; $i < $t_count; $i++ ) {
		# Only show the first sort columns
		if( $i< $t_max_displayed_sort ) {
			if( $i > 0 ) {
				echo ', ';
			}
			$t_sort = $t_sort_fields[$i];
			if(column_is_custom_field( $t_sort ) ) {
				$t_field_name = string_display( lang_get_defaulted( column_get_custom_field_name( $t_sort ) ) );
			} else {
				$t_field_name = string_get_field_name( $t_sort );
			}
			echo $t_field_name . ' ' . lang_get( 'bugnote_order_' . utf8_strtolower( $t_dir_fields[$i] ) );
		} elseif ( $i == $t_max_displayed_sort ) {
			echo ', ...';
		}
		# All sort columns are placed in hidden fields
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_FIELD_NAME, '[]" value="', string_attribute( $t_sort_fields[$i] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_DIRECTION, '[]" value="', string_attribute( $t_dir_fields[$i] ), '" />';
	}
}

/**
 * Print sort fields
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_show_sort( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}

	# get visible columns, and filter out those that ar not sortable
	$t_visible_columns = array_filter( helper_get_columns_to_view(), 'column_is_sortable' );

	$t_shown_fields[''] = '';
	foreach( $t_visible_columns as $t_column ) {
		if(column_is_custom_field( $t_column ) ) {
			$t_field_name = string_display( lang_get_defaulted( column_get_custom_field_name( $t_column ) ) );
		} else {
			$t_field_name = string_get_field_name( $t_column );
		}
		$t_shown_fields[$t_column] = $t_field_name;
	}
	$t_shown_dirs[''] = '';
	$t_shown_dirs['ASC'] = lang_get( 'bugnote_order_asc' );
	$t_shown_dirs['DESC'] = lang_get( 'bugnote_order_desc' );

	# get values from filter structure
	$p_sort_properties = filter_get_visible_sort_properties_array( $p_filter );
	$t_sort_fields = $p_sort_properties[FILTER_PROPERTY_SORT_FIELD_NAME];
	$t_dir_fields = $p_sort_properties[FILTER_PROPERTY_SORT_DIRECTION];

	# @TODO cproensa: this could be a constant, or conffig.
	$t_max_inputs_sort = 3;

	$t_print_select_inputs =
		function( $p_sort_val ='', $p_dir_val ='' ) use ( $t_shown_fields, $t_shown_dirs ) {
			echo '<select name="', FILTER_PROPERTY_SORT_FIELD_NAME, '[]">';
			foreach( $t_shown_fields as $t_key => $t_val ) {
				echo '<option value="' . $t_key . '"';
				check_selected( $t_key, $p_sort_val );
				echo '>' . $t_val . '</option>';
			}
			echo '</select>';
			echo '<select name="', FILTER_PROPERTY_SORT_DIRECTION, '[]">';
			foreach( $t_shown_dirs as $t_key => $t_val ) {
				echo '<option value="' . $t_key . '"';
				check_selected( $t_key, $p_dir_val );
				echo '>' . $t_val . '</option>';
			}
			echo '</select>';
		};

	# if there are fields to display, show the dropdowns
	if( count( $t_visible_columns ) > 0 ) {
		$t_field_count = count( $t_sort_fields );
		$t_count = min( $t_field_count, $t_max_inputs_sort );
		for( $i = 0; $i < $t_count; $i++ ) {
			if( $i > 0 ) {
				echo ', ';
			}
			$t_print_select_inputs( $t_sort_fields[$i], $t_dir_fields[$i] );
		}
		# If we can have more inputs displayed, print one more as empty.
		if( $t_field_count < $t_max_inputs_sort ) {
			echo ', ';
			$t_print_select_inputs();
		}
	} else {
		echo lang_get_defaulted( 'last_updated' ) . lang_get( 'bugnote_order_desc' );
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_FIELD_NAME, '_array[]" value="last_updated" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_DIRECTION, '_array[]" value="DESC" />';
	}
}

/**
 * Print custom field date fields
 * @global array $g_filter
 * @param integer $p_field_id  Custom field identifier.
 * @param array $p_filter 	Filter array
 * @return void
 */
function print_filter_custom_field_date( $p_field_id, array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
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

	if( isset( $p_filter['custom_fields'][$p_field_id][1] ) ) {
		$t_start_time = $p_filter['custom_fields'][$p_field_id][1];
	} else {
		$t_start_time = 0;
	}

	if( isset( $p_filter['custom_fields'][$p_field_id][2] ) ) {
		$t_end_time = $p_filter['custom_fields'][$p_field_id][2];
	} else {
		$t_end_time = 0;
	}

	$t_start_disable = true;
	$t_end_disable = true;

	# if $p_filter['custom_fields'][$p_field_id][0] is not set (ie no filter),
	# we will drop through the following switch and use the default values
	# above, so no need to check if stuff is set or not.
	switch( $p_filter['custom_fields'][$p_field_id][0] ) {
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
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ANY );
	echo '>' . lang_get( 'any' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_NONE . '"';
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_NONE );
	echo '>' . lang_get( 'none' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_BETWEEN . '"';
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_BETWEEN );
	echo '>' . lang_get( 'between_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_ONORBEFORE . '"';
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ONORBEFORE );
	echo '>' . lang_get( 'on_or_before_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_BEFORE . '"';
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_BEFORE );
	echo '>' . lang_get( 'before_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_ON . '"';
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ON );
	echo '>' . lang_get( 'on_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_AFTER . '"';
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_AFTER );
	echo '>' . lang_get( 'after_date' ) . '</option>' . "\n";
	echo '<option value="' . CUSTOM_FIELD_DATE_ONORAFTER . '"';
	check_selected( (int)$p_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ONORAFTER );
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
function print_filter_values_project_id( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_project_id( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Project -->
		<select <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PROJECT_ID;?>[]">
			<option value="<?php echo META_FILTER_CURRENT ?>"
				<?php check_selected( $p_filter[FILTER_PROPERTY_PROJECT_ID], META_FILTER_CURRENT );?>>
				[<?php echo lang_get( 'current' )?>]
			</option>
			<?php print_project_option_list( $p_filter[FILTER_PROPERTY_PROJECT_ID] )?>
		</select>
		<?php
}

/**
 * Print the current value of this filter field, as visible string, and as a hidden form input.
 * @param array $p_filter	Filter array
 * @return void
 */
function print_filter_values_match_type( array $p_filter ) {
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
 * @global array $g_filter
 * @param array $p_filter Filter array
 * @return void
 */
function print_filter_match_type( array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
?>
		<!-- Project -->
		<select <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_MATCH_TYPE;?>">
			<option value="<?php echo FILTER_MATCH_ALL?>" <?php check_selected( $p_filter[FILTER_PROPERTY_MATCH_TYPE], FILTER_MATCH_ALL );?>>[<?php echo lang_get( 'filter_match_all' )?>]</option>
			<option value="<?php echo FILTER_MATCH_ANY?>" <?php check_selected( $p_filter[FILTER_PROPERTY_MATCH_TYPE], FILTER_MATCH_ANY );?>>[<?php echo lang_get( 'filter_match_any' )?>]</option>
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


/**
 * Draw the table cells to view and edit a filter. This will usually be part of a form.
 * This method only prints the cells, not the table definition, or any other form element
 * outside of that.
 * A filter array is provided, to populate the fields.
 * The form will use javascript to show dinamic completion of fields (unless the
 * parameter $p_static is provided).
 * A page name can be provided to be used as a fallback script when javascript is
 * not available on the cliuent, and the form was rendered with dynamic fields.
 * By default, the fallback is the current page.
 *
 * @param array $p_filter	Filter array to show.
 * @param boolean $p_for_screen	Type of output
 * @param boolean $p_static	Wheter to print a static form (no dynamic fields)
 * @param string $p_static_fallback_page	Page name to use as javascript fallback
 * @return void
 */
function filter_form_draw_inputs( $p_filter, $p_for_screen = true, $p_static = false, $p_static_fallback_page = null ) {

	$t_filter = filter_ensure_valid_filter( $p_filter );
	$t_view_type = $t_filter['_view_type'];
	$t_source_query_id = isset( $t_filter['_source_query_id'] ) ? (int)$t_filter['_source_query_id'] : -1;

	# If it's a stored filter, linked to a secific project, use that project_id to render available fields
	if( $t_source_query_id > 0 ) {
		$t_project_id = (int)filter_get_field( $t_source_query_id, 'project_id' );
		if( ALL_PROJECTS == $t_project_id ) {
			# If all_projects, the filter can be used at any project, select the current project id
			$t_project_id = helper_get_current_project();
		} else if( $t_project_id < 0 ) {
			# If filter is an unnamed filter, project id is stored as negative value.
			$t_project_id = -1 * $t_project_id;
		}
	} else {
		$t_project_id = helper_get_current_project();
	}

	if( null === $p_static_fallback_page ) {
		$p_static_fallback_page = $_SERVER['PHP_SELF'];
	}
	$t_filters_url = $p_static_fallback_page;
	$t_get_params = $_GET;
	$t_get_params['for_screen'] = $p_for_screen;
	$t_get_params['static'] = ON;
	$t_get_params['view_type'] = ( 'advanced' == $t_view_type ) ? 'advanced' : 'simple';
	$t_filters_url .= '?' . http_build_query( $t_get_params );

	$t_show_product_version =  version_should_show_product_version( $t_project_id );
	$t_show_build = $t_show_product_version && ( config_get( 'enable_product_build' ) == ON );

	# overload handler_id setting if user isn't supposed to see them (ref #6189)
	if( !access_has_any_project( config_get( 'view_handler_threshold' ) ) ) {
		$t_filter[FILTER_PROPERTY_HANDLER_ID] = array(
			META_FILTER_ANY,
		);
	}

	if ( config_get( 'use_dynamic_filters' ) ) {
		$t_dynamic_filter_expander_class = ' class="dynamic-filter-expander"';
	} else {
		$t_dynamic_filter_expander_class = '';
	}

	$get_field_header = function ( $p_id, $p_label ) use ( $t_filters_url, $p_static, $t_filter, $t_source_query_id, $t_dynamic_filter_expander_class ) {
		if( $p_static) {
			return $p_label;
		} else {
			if( $t_source_query_id > 0 ) {
				$t_data_filter_id = ' data-filter_id="' . $t_source_query_id . '"';
			} else {
				$t_data_filter_id = '';
			}
			return '<a href="' . $t_filters_url . '" id="' . $p_id . '"' . $t_dynamic_filter_expander_class . $t_data_filter_id . '>' . $p_label . '</a>';
		}
	};


	$t_filter_cols = max( 8, config_get( 'filter_custom_fields_per_row' ) );
	$t_show_inputs = $p_static;

	#
	# Build the field items
	# Use different sections to keep some separation among each group of fields
	# When a section starts, its fields start in a new row.

	$t_row1 = new FilterBoxGridLayout( $t_filter_cols , FilterBoxGridLayout::ORIENTATION_VERTICAL );

	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'reporter_id_filter', lang_get( 'reporter' ) ),
			filter_form_get_input( $t_filter, 'reporter_id', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'reporter_id_filter_target' /* content id */
			));
	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'handler_id_filter', lang_get( 'assigned_to' ) ),
			filter_form_get_input( $t_filter, 'handler_id', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'handler_id_filter_target' /* content id */
			));
	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'user_monitor_filter', lang_get( 'monitored_by' ) ),
			filter_form_get_input( $t_filter, 'user_monitor', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'user_monitor_filter_target' /* content id */
			));
	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'note_user_id_filter', lang_get( 'note_user_id' ) ),
			filter_form_get_input( $t_filter, 'note_user_id', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'note_user_id_filter_target' /* content id */
			));
	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'show_priority_filter', lang_get( 'priority' ) ),
			filter_form_get_input( $t_filter, 'show_priority', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'show_priority_filter_target' /* content id */
			));
	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'show_severity_filter', lang_get( 'severity' ) ),
			filter_form_get_input( $t_filter, 'show_severity', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'show_severity_filter_target' /* content id */
			));
	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'view_state_filter', lang_get( 'view_status' ) ),
			filter_form_get_input( $t_filter, 'view_state', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'view_state_filter_target' /* content id */
			));
	$t_row1->add_item( new TableFieldsItem(
			$get_field_header( 'sticky_issues_filter', lang_get( 'sticky' ) ),
			filter_form_get_input( $t_filter, 'sticky_issues', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'sticky_issues_filter_target' /* content id */
			));

	$t_row2 = new FilterBoxGridLayout( $t_filter_cols , FilterBoxGridLayout::ORIENTATION_VERTICAL );

	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'show_category_filter', lang_get( 'category' ) ),
			filter_form_get_input( $t_filter, 'show_category', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'show_category_filter_target' /* content id */
			));
	if( 'simple' == $t_view_type ) {
		$t_row2->add_item( new TableFieldsItem(
				$get_field_header( 'hide_status_filter', lang_get( 'hide_status' ) ),
				filter_form_get_input( $t_filter, 'hide_status', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'hide_status_filter_target' /* content id */
				));
	}
	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'show_status_filter', lang_get( 'status' ) ),
			filter_form_get_input( $t_filter, 'show_status', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'show_status_filter_target' /* content id */
			));
	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'show_resolution_filter', lang_get( 'resolution' ) ),
			filter_form_get_input( $t_filter, 'show_resolution', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'show_resolution_filter_target' /* content id */
			));
	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'do_filter_by_date_filter', lang_get( 'use_date_filters' ) ),
			filter_form_get_input( $t_filter, 'do_filter_by_date', $t_show_inputs ),
			2 /* colspan */,
			null /* class */,
			'do_filter_by_date_filter_target' /* content id */
			));
	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'do_filter_by_last_updated_date_filter', lang_get( 'use_last_updated_date_filters' ) ),
			filter_form_get_input( $t_filter, 'do_filter_by_last_updated_date', $t_show_inputs ),
			2 /* colspan */,
			null /* class */,
			'do_filter_by_last_updated_date_filter_target' /* content id */
			));
	if( 'advanced' == $t_view_type ) {
		$t_row2->add_item( new TableFieldsItem(
				$get_field_header( 'project_id_filter', lang_get( 'email_project' ) ),
				filter_form_get_input( $t_filter, 'project_id', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'project_id_filter_target' /* content id */
				));
	}

	$t_row3 = new FilterBoxGridLayout( $t_filter_cols , FilterBoxGridLayout::ORIENTATION_VERTICAL );

	if( ON == config_get( 'enable_profiles' ) ) {
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'show_profile_filter', lang_get( 'profile' ) ),
				filter_form_get_input( $t_filter, 'show_profile', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'show_profile_filter_target' /* content id */
				));
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'platform_filter', lang_get( 'platform' ) ),
				filter_form_get_input( $t_filter, 'platform', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'platform_filter_target' /* content id */
				));
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'os_filter', lang_get( 'os' ) ),
				filter_form_get_input( $t_filter, 'os', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'os_filter_target' /* content id */
				));
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'os_build_filter', lang_get( 'os_version' ) ),
				filter_form_get_input( $t_filter, 'os_build', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'os_build_filter_target' /* content id */
				));
	}
	if( $t_show_build ) {
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'show_build_filter', lang_get( 'product_build' ) ),
				filter_form_get_input( $t_filter, 'show_build', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'show_build_filter_target' /* content id */
				));
	}
	if( $t_show_product_version ) {
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'show_version_filter', lang_get( 'product_version' ) ),
				filter_form_get_input( $t_filter, 'show_version', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'show_version_filter_target' /* content id */
				));
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'show_fixed_in_version_filter', lang_get( 'fixed_in_version' ) ),
				filter_form_get_input( $t_filter, 'show_fixed_in_version', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'show_fixed_in_version_filter_target' /* content id */
				));
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'show_target_version_filter', lang_get( 'target_version' ) ),
				filter_form_get_input( $t_filter, 'show_target_version', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'show_target_version_filter_target' /* content id */
				));
	}
	$t_row3->add_item( new TableFieldsItem(
			$get_field_header( 'relationship_type_filter', lang_get( 'bug_relationships' ) ),
			filter_form_get_input( $t_filter, 'relationship_type', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'relationship_type_filter_target' /* content id */
			));
	if( access_has_global_level( config_get( 'tag_view_threshold' ) ) ) {
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'tag_string_filter', lang_get( 'tags' ) ),
				filter_form_get_input( $t_filter, 'tag_string', $t_show_inputs ),
				3 /* colspan */,
				null /* class */,
				'tag_string_filter_target' /* content id */
				));
	}

	# plugin filters & custom fields

	$t_row_extra = new FilterBoxGridLayout( $t_filter_cols , FilterBoxGridLayout::ORIENTATION_VERTICAL );

	$t_plugin_filters = filter_get_plugin_filters();
	foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
		$t_colspan = (int)$t_filter_object->colspan;
		$t_header = $get_field_header( string_attribute( $t_field_name ) . '_filter', string_display_line( $t_filter_object->title ) );
		ob_start();
		if( $p_static ) {
			print_filter_plugin_field( $t_field_name, $t_filter_object, $t_filter );
		} else {
			print_filter_values_plugin_field( $t_filter, $t_field_name, $t_filter_object );
		}
		$t_content = ob_get_clean();

		$t_row_extra->add_item( new TableFieldsItem(
				$t_header,
				$t_content,
				$t_colspan,
				null /* class */,
				string_attribute( $t_field_name ) . '_filter_target' /* content id */
				));
	}

	if( ON == config_get( 'filter_by_custom_fields' ) ) {
		$t_custom_fields = custom_field_get_linked_ids( $t_project_id );
		$t_accessible_custom_fields = array();
		foreach( $t_custom_fields as $t_cfid ) {
			$t_cfdef = custom_field_get_definition( $t_cfid );
			if( $t_cfdef['access_level_r'] <= current_user_get_access_level() && $t_cfdef['filter_by'] ) {
				$t_accessible_custom_fields[] = $t_cfdef;
			}
		}

		if( !empty( $t_accessible_custom_fields ) ) {
			foreach( $t_accessible_custom_fields as $t_cfdef ) {
				$t_header = $get_field_header( 'custom_field_' . $t_cfdef['id'] . '_filter', string_display_line( lang_get_defaulted( $t_cfdef['name'] ) ) );
				ob_start();
				if( $p_static ) {
					print_filter_custom_field( $t_cfdef['id'], $t_filter );
				} else {
					print_filter_values_custom_field( $t_filter, $t_cfdef['id'] );
				}
				$t_content = ob_get_clean();

				$t_row_extra->add_item( new TableFieldsItem(
						$t_header,
						$t_content,
						1 /* colspan */,
						null /* class */,
						'custom_field_' . $t_cfdef['id'] . '_filter_target' /* content id */
						));
			}
		}
	}

	# Section: last fields, horizontal orientation

	$t_section_last = new FilterBoxGridLayout( $t_filter_cols , FilterBoxGridLayout::ORIENTATION_HORIZONTAL );

	$t_section_last->add_item( new TableFieldsItem(
			$get_field_header( 'per_page_filter', lang_get( 'show' ) ),
			filter_form_get_input( $t_filter, 'per_page', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'per_page_filter_target' /* content id */
			));
	$t_section_last->add_item( new TableFieldsItem(
			$get_field_header( 'show_sort_filter', lang_get( 'sort' ) ),
			filter_form_get_input( $t_filter, 'show_sort', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'show_sort_filter_target' /* content id */
			));
	$t_section_last->add_item( new TableFieldsItem(
			$get_field_header( 'match_type_filter', lang_get( 'filter_match_type' ) ),
			filter_form_get_input( $t_filter, 'match_type', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'match_type_filter_target' /* content id */
			));
	$t_section_last->add_item( new TableFieldsItem(
			$get_field_header( 'highlight_changed_filter', lang_get( 'changed' ) ),
			filter_form_get_input( $t_filter, 'highlight_changed', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'highlight_changed_filter_target' /* content id */
			));

	?>
	<table class="table table-bordered table-condensed2">
		<?php $t_row1->render() ?>
		<?php $t_row2->render() ?>
		<?php $t_row3->render() ?>
		<?php $t_row_extra->render() ?>
		<tr class="spacer"></tr>
		<?php $t_section_last->render() ?>
	</table>
	<?php
}


/**
 * Class that extends TableGridLayout and implements the specific HTML output needed for the
 * filter form table
 */
class FilterBoxGridLayout extends TableGridLayout {

	/**
	 * Prints HTML code for TD cell representing the Item header
	 * @param TableFieldsItem $p_item Item to display
	 * @param integer $p_colspan Colspan attribute for cell
	 */
	protected function render_td_item_header( TableFieldsItem $p_item, $p_colspan ) {
		echo '<td class="small-caption category ' . $p_item->attr_class . '"';
		if( $p_colspan > 1) {
			echo ' colspan="' . $p_colspan . '"';
		}
		if( $p_item->header_attr_id ) {
			echo ' id="' . $p_item->header_attr_id . '"';
		}
		echo '>';
		echo $p_item->header;
		echo '</td>';
	}

	/**
	 * Prints HTML code for TD cell representing the Item content
	 * @param TableFieldsItem $p_item Item to display
	 * @param integer $p_colspan Colspan attribute for cell
	 */
	protected function render_td_item_content( TableFieldsItem $p_item, $p_colspan ) {
		echo '<td class="small-caption ' . $p_item->attr_class . '"';
		if( $p_colspan > 1) {
			echo ' colspan="' . $p_colspan . '"';
		}
		if( $p_item->content_attr_id ) {
			echo ' id="' . $p_item->content_attr_id . '"';
		}
		echo '>';
		echo $p_item->content;
		echo '</td>';
	}

	/**
	 * Prints HTML code for an empty TD cell, of header type
	 * @param integer $p_colspan Colspan attribute for cell
	 */
	protected function render_td_empty_header( $p_colspan ) {
		echo '<td class="small-caption category"';
		if( $p_colspan > 1) {
			echo ' colspan="' . $p_colspan . '"';
		}
		echo '>';
		echo '&nbsp;';
		echo '</td>';
	}
}