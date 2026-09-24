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
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses logging_api.php
 * @uses print_api.php
 * @uses relationship_api.php
 * @uses string_api.php
 * @uses user_api.php
 *
 * @noinspection HtmlFormInputWithoutLabel, PhpUnused
 */

use Mantis\Exceptions\ClientException;
use Mantis\Exceptions\StateException;

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
require_api( 'icon_api.php' );
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
 *      print_filter_[filter_name]
 *
 *      Where [filter_name] is the same as the "name" of the form element for
 *      that filter. This naming convention is depended upon by the controller
 *      at the end of the script.
 *
 * @todo print functions should be abstracted.  Many of these functions
 *      are virtually identical except for the property name.
 *      Perhaps this code could be made simpler by refactoring into a
 *      class to avoid all those calls to global (which are pretty ugly)
 *      These functions could also be shared by view_filters_page.php
 */

/**
 * Returns HTML for each filter field, to be used in filter form.
 *
 * $p_filter_target is a field name to match any of "the print_filter_..."
 * functions, excluding those related to custom fields and plugin fields. When
 * $p_show_options is enabled, the form inputs are returned to allow selection,
 * if the option is disabled, returns the current value and a hidden input for
 * that value.
 *
 * @param array   $p_filter        Filter array
 * @param string  $p_filter_target Filter field name
 * @param boolean $p_show_inputs   True to return a visible form input or false
 *                                 for a text value.
 *
 * @return string The html content for the field requested
 *
 * @throws StateException if there is no matching print_filter_... function
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
		throw new StateException(
			"No function to populate the target",
			ERROR_FILTER_NOT_FOUND,
			array( $p_filter_target )
		);
	}
}


/**
 * Return the input modifier to be used for advanced filters.
 * @param array $p_filter	Filter array to use
 * @return string
 */
function filter_select_modifier( array $p_filter ) {
	if( FILTER_VIEW_TYPE_ADVANCED == $p_filter['_view_type'] ) {
		return ' multiple="multiple" size="10"';
	} else {
		return '';
	}
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_name );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print the reporter field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_reporter_id( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_REPORTER_ID;?>[]">
		<?php
	# if current user is a reporter, and limited_reporters is set to ON, only display that name
	if( access_has_limited_view() ) {
		$t_id = auth_get_current_user_id();
		$t_username = user_get_name( $t_id );
		$t_display_name = string_attribute( $t_username );
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
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_user_monitor( array $p_filter ) {
	$t_filter = $p_filter;
	$t_output = '';
	$t_any_found = false;
	$t_none_found = false;
	if( count( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] ) == 0 ) {
		echo lang_get( 'any' );
	} else {
		$t_first_flag = true;
		foreach( $t_filter[FILTER_PROPERTY_MONITOR_USER_ID] as $t_current ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_MONITOR_USER_ID, '[]" value="', string_attribute( $t_current ), '" />';
			$t_this_name = '';
			if( filter_field_is_any( $t_current ) ) {
				$t_any_found = true;
			} else if( filter_field_is_none( $t_current ) ) {
				$t_none_found = true;
			} else if( filter_field_is_myself( $t_current ) ) {
				if( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
					$t_this_name = '[' . lang_get( 'myself' ) . ']';
				} else {
					$t_any_found = true;
				}
			} else {
				$t_this_name = user_get_name( $t_current );
			}
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_name );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else if( $t_none_found ) {
			echo lang_get( 'none' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print the user monitor field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_user_monitor( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
	<!-- Monitored by -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_MONITOR_USER_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_MONITOR_USER_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_MONITOR_USER_ID], META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php
				if( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
		echo '<option value="' . META_FILTER_MYSELF . '" ';
		check_selected( $p_filter[FILTER_PROPERTY_MONITOR_USER_ID], META_FILTER_MYSELF );
		echo '>[' . lang_get( 'myself' ) . ']</option>';
	}
	$t_threshold = config_get( 'show_monitor_list_threshold' );

	if( access_has_project_level( $t_threshold ) ) {
		print_user_option_list( $p_filter[FILTER_PROPERTY_MONITOR_USER_ID], null, config_get( 'monitor_bug_threshold' ) );
	}
	?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_name );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print the handler field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_handler_id( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Handler -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_HANDLER_ID;?>[]">
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
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_show_category( array $p_filter ) {
	$t_filter = $p_filter;
	$t_output = '';
	$t_any_found = false;
	$t_none_found = false;
	if( count( $t_filter[FILTER_PROPERTY_CATEGORY_ID] ) == 0 ) {
		echo lang_get( 'any' );
	} else {
		$t_first_flag = true;
		foreach( $t_filter[FILTER_PROPERTY_CATEGORY_ID] as $t_current ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_CATEGORY_ID, '[]" value="', string_attribute( $t_current ), '" />';
			$t_this_string = '';
			if( filter_field_is_any( $t_current ) ) {
				$t_any_found = true;
			} elseif( filter_field_is_none( $t_current ) ) {
				$t_none_found = true;
			} else {
				$t_this_string = $t_current;
			}
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} elseif( $t_none_found ) {
			echo lang_get( 'none' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print the category field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_category( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Category -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_CATEGORY_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_CATEGORY_ID], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_CATEGORY_ID], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_category_filter_option_list( $p_filter[FILTER_PROPERTY_CATEGORY_ID] )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_platform( array $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_PLATFORM, $p_filter[FILTER_PROPERTY_PLATFORM] );
}

/**
 * Print the platform field.
 *
 * @param array|null $p_filter
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_platform( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Platform -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PLATFORM;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_PLATFORM], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php
				log_event( LOG_FILTERING, 'Platform = ' . var_export( $p_filter[FILTER_PROPERTY_PLATFORM], true ) );
	print_platform_option_list( $p_filter[FILTER_PROPERTY_PLATFORM] );
	?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_os( array $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_OS, $p_filter[FILTER_PROPERTY_OS] );
}

/**
 * Print the os field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_os( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- OS -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_OS;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_OS], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_option_list( $p_filter[FILTER_PROPERTY_OS] )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_os_build( array $p_filter ) {
	print_multivalue_field( FILTER_PROPERTY_OS_BUILD, $p_filter[FILTER_PROPERTY_OS_BUILD] );
}

/**
 * Print the os build field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_os_build( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- OS Build -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_OS_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_OS_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_os_build_option_list( $p_filter[FILTER_PROPERTY_OS_BUILD] )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print the severity field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_severity( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Severity -->
			<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_SEVERITY;?>[]">
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
function print_filter_values_show_reproducibility( array $p_filter ) {
	$t_filter = $p_filter;
	$t_output = '';
	$t_any_found = false;
	if( count( $t_filter[FILTER_PROPERTY_REPRODUCIBILITY] ) == 0 ) {
		echo lang_get( 'any' );
	} else {
		$t_first_flag = true;
		foreach( $t_filter[FILTER_PROPERTY_REPRODUCIBILITY] as $t_current ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_REPRODUCIBILITY, '[]" value="', string_attribute( $t_current ), '" />';
			$t_this_string = '';
			if( filter_field_is_any( $t_current ) ) {
				$t_any_found = true;
			} else {
				$t_this_string = get_enum_element( 'reproducibility', $t_current );
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
 * print the reproducibility field
 * @global array $g_filter
 * @param array|null $p_filter Filter array
 * @return void
 */
function print_filter_show_reproducibility( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Reproducibility -->
	<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_REPRODUCIBILITY;?>[]">
		<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_REPRODUCIBILITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
		<?php print_enum_string_option_list( 'reproducibility', $p_filter[FILTER_PROPERTY_REPRODUCIBILITY] )?>
	</select>
	<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print resolution field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_resolution( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Resolution -->
			<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_RESOLUTION;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_RESOLUTION], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'resolution', $p_filter[FILTER_PROPERTY_RESOLUTION] )?>
			</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print status field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_status( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>	<!-- Status -->
			<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_STATUS;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_STATUS], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'status', $p_filter[FILTER_PROPERTY_STATUS] )?>
			</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		$t_hide_status_post = '';
		if( count( $t_filter[FILTER_PROPERTY_HIDE_STATUS] ) == 1 ) {
			$t_hide_status_post = ' (' . lang_get( 'and_above' ) . ')';
		}
		if( $t_none_found ) {
			echo lang_get( 'none' );
		} else {
			echo $t_output . string_attribute( $t_hide_status_post );
		}
	}
}

/**
 * Print hide status field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_hide_status( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Hide Status -->
			<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_HIDE_STATUS;?>[]">
				<option value="<?php echo META_FILTER_NONE?>">[<?php echo lang_get( 'none' )?>]</option>
				<?php print_enum_string_option_list( 'status', $p_filter[FILTER_PROPERTY_HIDE_STATUS] )?>
			</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print build field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_build( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Build -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_BUILD;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_BUILD], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_build_option_list( $p_filter[FILTER_PROPERTY_BUILD] )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print version field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_version( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	$t_projects = filter_get_included_projects( $p_filter );
	?><!-- Version -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $p_filter[FILTER_PROPERTY_VERSION], $t_projects, VERSION_ALL, false )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print fixed in version field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_fixed_in_version( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	$t_projects = filter_get_included_projects( $p_filter );
	?><!-- Fixed in Version -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_FIXED_IN_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_FIXED_IN_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $p_filter[FILTER_PROPERTY_FIXED_IN_VERSION], $t_projects, VERSION_ALL, false )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print target version field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_target_version( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	$t_projects = filter_get_included_projects( $p_filter );
	?><!-- Fixed in Version -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_TARGET_VERSION;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<option value="<?php echo META_FILTER_NONE?>"<?php check_selected( $p_filter[FILTER_PROPERTY_TARGET_VERSION], (string)META_FILTER_NONE );?>>[<?php echo lang_get( 'none' )?>]</option>
			<?php print_version_option_list( $p_filter[FILTER_PROPERTY_TARGET_VERSION], $t_projects, VERSION_ALL, false )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print priority field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_priority( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Priority -->
	<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PRIORITY;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_PRIORITY], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_enum_string_option_list( 'priority', $p_filter[FILTER_PROPERTY_PRIORITY] )?>
	</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 * @throws ClientException
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
				$t_profile = new ProfileData( $t_current );
				$t_this_string = $t_profile->get_name();
			}
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print profile field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_profile( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Profile -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PROFILE_ID;?>[]">
			<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_PROFILE_ID], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
			<?php print_profile_option_list_for_project( helper_get_current_project(), $p_filter[FILTER_PROPERTY_PROFILE_ID] );?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_per_page( array $p_filter ) {
	$t_filter = $p_filter;
	echo ( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] == 0 ) ? lang_get( 'all' ) : string_attribute( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] );
	echo '<input type="hidden" name="', FILTER_PROPERTY_ISSUES_PER_PAGE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_ISSUES_PER_PAGE] ), '" />';
}

/**
 * Print issues per page field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_per_page( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Number of bugs per page -->
		<input class="input-xs" type="text" name="<?php echo FILTER_PROPERTY_ISSUES_PER_PAGE;?>" size="3" maxlength="7" value="<?php echo $p_filter[FILTER_PROPERTY_ISSUES_PER_PAGE]?>" />
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
 * Print view state field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_view_state( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- View Status -->
		<select class="input-xs" name="<?php echo FILTER_PROPERTY_VIEW_STATE;?>">
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
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_sticky_issues( array $p_filter ) {
	$t_filter = $p_filter;
	$t_sticky_filter_state = gpc_string_to_bool( $t_filter[FILTER_PROPERTY_STICKY] );
	print( $t_sticky_filter_state ? lang_get( 'yes' ) : lang_get( 'no' ) );
	?>
	<input type="hidden" name="<?php
		echo FILTER_PROPERTY_STICKY; ?>" value="<?php
		echo $t_sticky_filter_state ? ON : OFF; ?>" />
	<?php
}

/**
 * Print sticky issues field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_sticky_issues( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Show or hide sticky bugs -->
			<input type="hidden" name="<?php echo FILTER_PROPERTY_STICKY ?>" value="<?php echo OFF ?>">
			<label>
				<input class="input-xs ace" type="checkbox" name="<?php echo FILTER_PROPERTY_STICKY;?>"
					<?php check_checked( gpc_string_to_bool( $p_filter[FILTER_PROPERTY_STICKY] ), true );?>
				/>
				<span class="lbl"></span>
			</label>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
 * Print highlight changed field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_highlight_changed( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Highlight changed bugs -->
			<input class="input-xs" type="text" name="<?php echo FILTER_PROPERTY_HIGHLIGHT_CHANGED;?>" size="3" maxlength="7" value="<?php echo $p_filter[FILTER_PROPERTY_HIGHLIGHT_CHANGED]?>" />
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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

		$t_start_rel = !empty( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] ) ? $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] : null;
		$t_end_rel = !empty( $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] ) ? $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] : null;
		if( null !== $t_start_rel || null !== $t_end_rel ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED, '_relative" value="on" />';
			print_filter_hidden_relative_inputs( FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE, $t_start_rel );
			print_filter_hidden_relative_inputs( FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE, $t_end_rel );
			echo ' ', filter_relative_date_icon();
		}

		if( null !== $t_start_rel ) {
			echo ' ', filter_relative_date_display( $t_start_rel );
		} else {
			echo ' ', filter_format_short_date( mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] ) );
		}

		echo ' - ';

		if( null !== $t_end_rel ) {
			echo filter_relative_date_display( $t_end_rel );
		} else {
			echo filter_format_short_date( mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY], $t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] ) );
		}
	} else {
		echo lang_get( 'no' );
	}
}

/**
 * Print the two control rows shared by the built-in date filters: the checkbox
 * that turns the field on, and the select that chooses fixed or relative dates.
 *
 * These are separate concerns and are deliberately separate controls. The
 * checkbox is the field's on/off switch and keeps the name these filters have
 * always submitted, so an unchecked field still means "do not filter by this
 * date". The select only says which kind of bounds the field uses; it defaults
 * to fixed and never turns the field on by itself.
 *
 * The checkbox carries the name these filters have always submitted, and is
 * paired with a hidden input of the same name so an unchecked box still posts
 * a value. That is what keeps a field "off": in static mode every field is
 * rendered and submitted, so without the checkbox an untouched date field
 * would turn its filter on. In dynamic mode an unexpanded field submits
 * nothing at all and the stored value carries through instead.
 *
 * @param string  $p_enable_field  Filter property holding the field's on/off flag.
 * @param string  $p_label         Lang string key for the checkbox label.
 * @param boolean $p_enabled       Whether the field is currently filtering.
 * @param boolean $p_relative_mode Whether the field is in relative mode.
 * @return void
 */
function print_filter_date_type_row( $p_enable_field, $p_label, $p_enabled, $p_relative_mode ) {
	# Fixed is the default; a stored relative descriptor is what selects relative.
	$t_type = $p_relative_mode ? FILTER_DATE_TYPE_RELATIVE : FILTER_DATE_TYPE_FIXED;
	$t_options = array(
		FILTER_DATE_TYPE_FIXED => lang_get( 'date_type_fixed' ),
		FILTER_DATE_TYPE_RELATIVE => lang_get( 'date_type_relative' ),
	);
?>
		<tr>
			<td colspan="2">
				<input type="hidden" name="<?php echo $p_enable_field ?>" value="<?php echo OFF ?>" />
				<label>
					<input class="input-xs ace js_rd_enable" type="checkbox"
						name="<?php echo $p_enable_field ?>"
						<?php check_checked( $p_enabled, true ) ?> />
					<span class="lbl padding-6 small"><?php echo lang_get( $p_label ) ?></span>
				</label>
			</td>
		</tr>
		<tr>
			<td><?php echo lang_get( 'date_type_label' ) ?></td>
			<td>
				<select class="input-xs js_rd_type" name="<?php echo $p_enable_field ?>_type">
<?php	foreach( $t_options as $t_value => $t_label ) { ?>
					<option value="<?php echo $t_value ?>"<?php check_selected( $t_type, $t_value ) ?>><?php echo string_attribute( $t_label ) ?></option>
<?php	} ?>
				</select>
			</td>
		</tr>
<?php
}

/**
 * Print filter by date fields.
 *
 * @param boolean    $p_hide_checkbox Hide data filter checkbox.
 * @param array|null $p_filter        Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_do_filter_by_date( $p_hide_checkbox = false, ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}

	# Relative mode is encoded by the presence of a descriptor for either endpoint.
	$t_start_relative = !empty( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] ) ? $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE] : null;
	$t_end_relative = !empty( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] ) ? $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE] : null;
	$t_relative_mode = ( null !== $t_start_relative ) || ( null !== $t_end_relative );
	$t_enabled = gpc_string_to_bool( $p_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] );

	# Callers that hide the mode controls get the fixed rows and nothing else, so
	# the field stays fixed-mode whatever the filter holds - honouring a descriptor
	# there would hide the fixed rows and leave the table with no date inputs.
	if( $p_hide_checkbox ) {
		$t_relative_mode = false;
	}

	# Only the selected mode's rows are shown; the other mode's rows are hidden
	# rather than merely disabled, which keeps the field compact. The initial
	# state is rendered here because the filter form is also inserted by AJAX
	# (return_dynamic_filters.php), where no change event fires for the
	# mode handler in common.js to react to.
	#
	# Visibility tracks the mode, and the mode defaults to fixed, so a field that
	# is not filtering still shows its fixed date inputs, disabled, as these
	# filters have always done. Whether the inputs are enabled is a separate
	# question, answered by the enable checkbox.
	$t_fixed_row_style = $t_relative_mode ? ' style="display:none"' : '';
	$t_relative_row_style = $t_relative_mode ? '' : ' style="display:none"';
	$t_menu_disabled = ( $t_enabled && !$t_relative_mode ) ? '' : ' disabled="disabled" ';
	$t_relative_disabled = ( $t_enabled && $t_relative_mode ) ? '' : ' disabled="disabled" ';

	# Without the mode controls the field is fixed-mode only, and its inputs are
	# the whole field, so they are always live.
	if( $p_hide_checkbox ) {
		$t_menu_disabled = '';
	}
?>
		<table>
<?php
	if( !$p_hide_checkbox ) {
		print_filter_date_type_row(
			FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED,
			'use_date_filters',
			$t_enabled,
			$t_relative_mode );
	}
?>

		<!-- Start date -->
		<tr class="js_rd_row_fixed"<?php echo $t_fixed_row_style ?>>
			<td>
			<?php echo lang_get( 'start_date_label' )?>
			</td>
			<td class="nowrap">
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
	foreach( $t_chars as $t_char ) {
		if( strcasecmp( $t_char, 'M' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_DATE_SUBMITTED_START_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] );
			print "</select>\n";
		}
	}
	?>
			</td>
		</tr>
		<!-- End date -->
		<tr class="js_rd_row_fixed"<?php echo $t_fixed_row_style ?>>
			<td>
			<?php echo lang_get( 'end_date_label' )?>
			</td>
			<td>
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
	foreach( $t_chars as $t_char ) {
		if( strcasecmp( $t_char, 'M' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_DATE_SUBMITTED_END_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] );
			print "</select>\n";
		}
	}
	?>
			</td>
		</tr>
<?php
	if( !$p_hide_checkbox ) {
?>
		<!-- Relative start date -->
		<tr class="js_rd_row_relative"<?php echo $t_relative_row_style ?>>
			<td>
			<?php echo lang_get( 'start_date_label' )?>
			</td>
			<td class="nowrap">
			<?php print_filter_relative_date_inputs( FILTER_PROPERTY_DATE_SUBMITTED_START_RELATIVE, $t_relative_disabled, $t_start_relative ); ?>
			</td>
		</tr>
		<!-- Relative end date -->
		<tr class="js_rd_row_relative"<?php echo $t_relative_row_style ?>>
			<td>
			<?php echo lang_get( 'end_date_label' )?>
			</td>
			<td>
			<?php print_filter_relative_date_inputs( FILTER_PROPERTY_DATE_SUBMITTED_END_RELATIVE, $t_relative_disabled, $t_end_relative ); ?>
			</td>
		</tr>
<?php
	}
?>
		</table>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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

		$t_start_rel = !empty( $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE] ) ? $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE] : null;
		$t_end_rel = !empty( $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE] ) ? $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE] : null;
		if( null !== $t_start_rel || null !== $t_end_rel ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE, '_relative" value="on" />';
			print_filter_hidden_relative_inputs( FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE, $t_start_rel );
			print_filter_hidden_relative_inputs( FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE, $t_end_rel );
			echo ' ', filter_relative_date_icon();
		}

		if( null !== $t_start_rel ) {
			echo ' ', filter_relative_date_display( $t_start_rel );
		} else {
			echo ' ', filter_format_short_date( mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH], $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY], $t_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] ) );
		}

		echo ' - ';

		if( null !== $t_end_rel ) {
			echo filter_relative_date_display( $t_end_rel );
		} else {
			echo filter_format_short_date( mktime( 0, 0, 0, $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH], $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY], $t_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] ) );
		}
	} else {
		echo lang_get( 'no' );
	}
}

/**
 * Print the relative-date input cluster (anchor + signed offset + unit) for a
 * single date filter endpoint.
 *
 * These inputs capture a relative expression such as "today - 7 days" as a
 * counterpart to the fixed year/month/day selects. The submitted values are
 * assembled into a relative date descriptor by filter_gpc_get_relative_descriptor().
 * For the built-in date fields each control carries the js_rd_relative class, by
 * which the mode handler in common.js enables or disables the group as the date
 * type select changes. Custom date fields reuse these inputs but are driven by
 * their own handler, which selects them by name prefix because the operator
 * governs the two endpoints separately.
 *
 * @param string     $p_name_prefix Form-field name prefix for this endpoint.
 * @param string     $p_disabled    Disabled attribute markup (or empty string).
 * @param array|null $p_descriptor  Stored descriptor to pre-select, or null for defaults.
 * @return void
 */
function print_filter_relative_date_inputs( $p_name_prefix, $p_disabled = '', ?array $p_descriptor = null ) {
	if( null === $p_descriptor ) {
		$p_descriptor = filter_relative_descriptor_default( $p_name_prefix );
	}
	$t_parts = filter_relative_descriptor_to_parts( $p_descriptor );
	# Taken from filter_relative_date_anchors() so the form cannot offer an anchor
	# the resolver does not know; each label is the anchor's own lang string.
	$t_anchors = array();
	foreach( array_keys( filter_relative_date_anchors() ) as $t_anchor ) {
		$t_anchors[$t_anchor] = lang_get( 'relative_date_anchor_' . $t_anchor );
	}
	# As for the anchors: the set comes from filter_relative_date_units(), keyed by
	# the code the form field carries. The lang strings are the plural of the unit
	# name - relative_date_unit_days, _weeks, _months, _years.
	$t_units = array();
	foreach( filter_relative_date_units() as $t_unit => $t_code ) {
		$t_units[$t_code] = lang_get( 'relative_date_unit_' . $t_unit . 's' );
	}
?>
			<select class="input-xs js_rd_relative" name="<?php echo $p_name_prefix ?>_anchor"<?php echo $p_disabled ?>>
<?php	foreach( $t_anchors as $t_value => $t_label ) { ?>
				<option value="<?php echo $t_value ?>"<?php check_selected( $t_parts['anchor'], $t_value ) ?>><?php echo string_attribute( $t_label ) ?></option>
<?php	} ?>
			</select>
			<select class="input-xs js_rd_relative" name="<?php echo $p_name_prefix ?>_sign"<?php echo $p_disabled ?>>
				<option value="-"<?php check_selected( $t_parts['sign'], '-' ) ?>>&minus;</option>
				<option value="+"<?php check_selected( $t_parts['sign'], '+' ) ?>>+</option>
			</select>
			<input type="number" class="input-xs js_rd_relative" name="<?php echo $p_name_prefix ?>_num" value="<?php echo (int)$t_parts['num'] ?>" min="0" max="<?php echo FILTER_RELATIVE_DATE_MAX_OFFSET ?>" style="width: 56px"<?php echo $p_disabled ?> />
			<select class="input-xs js_rd_relative" name="<?php echo $p_name_prefix ?>_unit"<?php echo $p_disabled ?>>
<?php	foreach( $t_units as $t_value => $t_label ) { ?>
				<option value="<?php echo $t_value ?>"<?php check_selected( $t_parts['unit'], $t_value ) ?>><?php echo string_attribute( $t_label ) ?></option>
<?php	} ?>
			</select>
<?php
}

/**
 * Format a timestamp with the configured 'short_date_format', using the
 * localized month name. Only the M/D/Y placeholders of the format are honoured
 * (any separators are dropped), matching how the built-in date filters have
 * always rendered their endpoints in the read-only filter view.
 *
 * @param integer $p_timestamp Unix timestamp.
 * @return string Formatted date, e.g. "2026 July 23".
 */
function filter_format_short_date( $p_timestamp ) {
	$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
	$t_parts = array();
	foreach( $t_chars as $t_char ) {
		if( strcasecmp( $t_char, 'M' ) == 0 ) {
			$t_parts[] = lang_get( 'month_' . strtolower( date( 'F', $p_timestamp ) ) );
		} elseif( strcasecmp( $t_char, 'D' ) == 0 ) {
			$t_parts[] = date( 'd', $p_timestamp );
		} elseif( strcasecmp( $t_char, 'Y' ) == 0 ) {
			$t_parts[] = date( 'Y', $p_timestamp );
		}
	}
	return implode( ' ', $t_parts );
}

/**
 * Describe a relative date descriptor in words, in the order the form lays out
 * its inputs: "start of month -1 month". An offset of zero is the anchor alone,
 * "today"; an offset from today is the offset alone, "-7 days".
 *
 * @param array $p_descriptor Relative date descriptor.
 * @return string Plain-text description, not HTML-escaped.
 */
function filter_relative_descriptor_label( array $p_descriptor ) {
	$t_descriptor = filter_relative_descriptor_normalize( $p_descriptor );
	$t_anchor = lang_get( 'relative_date_anchor_' . $t_descriptor['anchor'] );

	$t_offset = $t_descriptor['offset'];
	if( 0 === $t_offset ) {
		return $t_anchor;
	}

	$t_count = abs( $t_offset );
	$t_step = ( $t_offset < 0 ? '-' : '+' ) . $t_count . ' '
		. lang_get( 'relative_date_unit_' . $t_descriptor['unit'] . ( 1 === $t_count ? '' : 's' ) );

	return 'today' === $t_descriptor['anchor'] ? $t_step : $t_anchor . ' ' . $t_step;
}

/**
 * Render a relative date endpoint for the read-only filter view as the date it
 * resolves to as of now, e.g. "2026 July 23", with the expression behind it,
 * e.g. "-7 days", in a tooltip. The field as a whole is marked relative by
 * filter_relative_date_icon(), printed before its dates.
 *
 * The date is always resolved from the descriptor here, never read from the
 * filter's year/month/day (or timestamp) slots: those are only refreshed when
 * the filter is submitted, so for a saved filter loaded later they hold the
 * date it had when it was saved, not the one the query will actually use.
 *
 * @param array       $p_descriptor  Relative date descriptor.
 * @param string|null $p_date_format Date format for the resolved date; null
 *                                   uses the localized short-date rendering of
 *                                   filter_format_short_date().
 * @return string HTML for the endpoint.
 */
function filter_relative_date_display( array $p_descriptor, $p_date_format = null ) {
	$t_timestamp = filter_relative_descriptor_to_date( $p_descriptor );
	$t_date = ( null === $p_date_format )
		? filter_format_short_date( $t_timestamp )
		: date( $p_date_format, $t_timestamp );

	# Styled like the dotted underline MantisBT already gives hover details
	# elsewhere, such as the resolution behind a related issue's status.
	return '<span class="relative-date" title="' . string_attribute( filter_relative_descriptor_label( $p_descriptor ) ) . '">'
		. string_html_specialchars( $t_date )
		. '</span>';
}

/**
 * The icon that marks a date field in the read-only filter view as relative,
 * printed once before its dates.
 *
 * @return string HTML for the icon.
 */
function filter_relative_date_icon() {
	return icon_get( 'fa-repeat', 'grey' );
}

/**
 * Emit a relative date descriptor as hidden form inputs (one per part), so the
 * read-only filter view re-posts the descriptor and preserves relative mode.
 *
 * @param string     $p_prefix     Field-name prefix for this endpoint.
 * @param array|null $p_descriptor Relative date descriptor, or null.
 * @return void
 */
function print_filter_hidden_relative_inputs( $p_prefix, $p_descriptor ) {
	if( empty( $p_descriptor ) || !is_array( $p_descriptor ) ) {
		return;
	}
	foreach( filter_relative_descriptor_to_parts( $p_descriptor ) as $t_key => $t_value ) {
		echo '<input type="hidden" name="', string_attribute( $p_prefix . '_' . $t_key ), '" value="', string_attribute( $t_value ), '" />';
	}
}

/**
 * Print filter by last update date fields.
 *
 * @param boolean    $p_hide_checkbox Hide data filter checkbox.
 * @param array|null $p_filter        Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_do_filter_by_last_updated_date( $p_hide_checkbox = false, ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}

	# Relative mode is encoded by the presence of a descriptor for either endpoint.
	$t_start_relative = !empty( $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE] ) ? $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE] : null;
	$t_end_relative = !empty( $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE] ) ? $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE] : null;
	$t_relative_mode = ( null !== $t_start_relative ) || ( null !== $t_end_relative );
	$t_enabled = gpc_string_to_bool( $p_filter[FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE] );

	# See print_filter_do_filter_by_date(): hiding the mode controls also means
	# fixed mode only, so the table cannot render with no date inputs at all.
	if( $p_hide_checkbox ) {
		$t_relative_mode = false;
	}

	# See print_filter_do_filter_by_date() for why the inactive mode's rows are
	# hidden here rather than only disabled, why visibility tracks the mode
	# rather than whether the field is enabled, and why the mode select is a
	# separate control from the enable checkbox.
	$t_fixed_row_style = $t_relative_mode ? ' style="display:none"' : '';
	$t_relative_row_style = $t_relative_mode ? '' : ' style="display:none"';
	$t_menu_disabled = ( $t_enabled && !$t_relative_mode ) ? '' : ' disabled="disabled" ';
	$t_relative_disabled = ( $t_enabled && $t_relative_mode ) ? '' : ' disabled="disabled" ';
	if( $p_hide_checkbox ) {
		$t_menu_disabled = '';
	}
?>
		<table>
<?php
	if( !$p_hide_checkbox ) {
		print_filter_date_type_row(
			FILTER_PROPERTY_FILTER_BY_LAST_UPDATED_DATE,
			'use_last_updated_date_filters',
			$t_enabled,
			$t_relative_mode );
	}
?>

		<!-- Start date -->
		<tr class="js_rd_row_fixed"<?php echo $t_fixed_row_style ?>>
			<td>
			<?php echo lang_get( 'start_date_label' )?>
			</td>
			<td class="nowrap">
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
	foreach( $t_chars as $t_char ) {
		if( strcasecmp( $t_char, 'M' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_LAST_UPDATED_START_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_LAST_UPDATED_START_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_LAST_UPDATED_START_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_START_YEAR] );
			print "</select>\n";
		}
	}
	?>
			</td>
		</tr>
		<!-- End date -->
		<tr class="js_rd_row_fixed"<?php echo $t_fixed_row_style ?>>
			<td>
			<?php echo lang_get( 'end_date_label' )?>
			</td>
			<td>
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
	foreach( $t_chars as $t_char ) {
		if( strcasecmp( $t_char, 'M' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_LAST_UPDATED_END_MONTH, '"', $t_menu_disabled, '>';
			print_month_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_MONTH] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'D' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_LAST_UPDATED_END_DAY, '"', $t_menu_disabled, '>';
			print_day_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_DAY] );
			print "</select>\n";
		}
		if( strcasecmp( $t_char, 'Y' ) == 0 ) {
			echo '<select class="input-xs js_rd_fixed" name="', FILTER_PROPERTY_LAST_UPDATED_END_YEAR, '"', $t_menu_disabled, '>';
			print_year_option_list( $p_filter[FILTER_PROPERTY_LAST_UPDATED_END_YEAR] );
			print "</select>\n";
		}
	}
	?>
			</td>
		</tr>
<?php
	if( !$p_hide_checkbox ) {
?>
		<!-- Relative start date -->
		<tr class="js_rd_row_relative"<?php echo $t_relative_row_style ?>>
			<td>
			<?php echo lang_get( 'start_date_label' )?>
			</td>
			<td class="nowrap">
			<?php print_filter_relative_date_inputs( FILTER_PROPERTY_LAST_UPDATED_START_RELATIVE, $t_relative_disabled, $t_start_relative ); ?>
			</td>
		</tr>
		<!-- Relative end date -->
		<tr class="js_rd_row_relative"<?php echo $t_relative_row_style ?>>
			<td>
			<?php echo lang_get( 'end_date_label' )?>
			</td>
			<td>
			<?php print_filter_relative_date_inputs( FILTER_PROPERTY_LAST_UPDATED_END_RELATIVE, $t_relative_disabled, $t_end_relative ); ?>
			</td>
		</tr>
<?php
	}
?>
		</table>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 * @throws ClientException
 */
function print_filter_values_relationship_type( array $p_filter ) {
	$t_filter = $p_filter;
	echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_TYPE, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE] ), '" />';
	echo '<input type="hidden" name="', FILTER_PROPERTY_RELATIONSHIP_BUG, '" value="', string_attribute( $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG] ), '" />';
	$c_rel_type = $t_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
	$c_rel_bug = $t_filter[FILTER_PROPERTY_RELATIONSHIP_BUG];
	if( BUG_REL_ANY == $c_rel_type ) {
		switch ( $c_rel_bug ) {
			case META_FILTER_NONE:
				echo lang_get( 'none' );
				break;
			case META_FILTER_ANY:
				echo lang_get( 'any' );
				break;
			default:
				echo lang_get( 'any' ),' ' , lang_get( 'with' ), ' ', $c_rel_bug;
		}
	} elseif( BUG_REL_NONE == $c_rel_type ) {
		echo lang_get( 'none' );
		switch ( $c_rel_bug ) {
			case META_FILTER_NONE:
			case META_FILTER_ANY:
				break;
			default:
				echo ' ', lang_get( 'with' ), ' ', $c_rel_bug;
		}
	} else {
		echo relationship_get_description_for_history( $c_rel_type ) . ' ';
		switch ( $c_rel_bug ) {
			case META_FILTER_NONE:
				echo lang_get( 'none' );
				break;
			case META_FILTER_ANY:
				echo lang_get( 'any' );
				break;
			default:
				echo $c_rel_bug;
		}
	}
}

/**
 * Print relationship fields.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_relationship_type( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	$c_reltype_value = $p_filter[FILTER_PROPERTY_RELATIONSHIP_TYPE];
	print_relationship_list_box( $c_reltype_value, 'relationship_type', true, true, "input-xs" );
	echo '<input class="input-xs" type="text" name="', FILTER_PROPERTY_RELATIONSHIP_BUG, '" size="5" maxlength="10" value="', $p_filter[FILTER_PROPERTY_RELATIONSHIP_BUG], '" />';
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
 * Print tag fields.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_tag_string( ?array $p_filter = null ) {
	global $g_filter;
	if( !access_has_project_level( config_get( 'tag_view_threshold' ) ) ) {
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
		<input class="input-xs" type="text" name="<?php echo FILTER_PROPERTY_TAG_STRING;?>" id="<?php echo FILTER_PROPERTY_TAG_STRING;?>" size="25" value="<?php echo string_attribute( $t_tag_string )?>" />
		<select class="input-xs" <?php echo helper_get_tab_index()?> name="<?php echo FILTER_PROPERTY_TAG_SELECT;?>" id="<?php echo FILTER_PROPERTY_TAG_SELECT;?>">
			<?php print_tag_option_list();?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
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
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_name );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print note reporter field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_note_user_id( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
	<!-- BUGNOTE REPORTER -->
	<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_NOTE_USER_ID;?>[]">
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
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array  $p_filter        Filter array
 * @param string $p_field_name    Field name
 * @param object $p_filter_object Filter object
 *
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
					echo string_attribute( $t_value );
				}
				echo '<input type="hidden" name="' . string_attribute( $p_field_name ) . '" value="' . string_attribute( $t_value ) . '">';
				break;

			case FILTER_TYPE_BOOLEAN:
				echo string_attribute( $p_filter_object->display( (bool)$t_value ) );
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
						$t_strings[] = string_attribute( $p_filter_object->display( $t_current ) );
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
 *
 * @param string     $p_field_name    Field name.
 * @param object     $p_filter_object Filter object.
 * @param array|null $p_filter        Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_plugin_field( $p_field_name, $p_filter_object, ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}

	$t_size = (int)$p_filter_object->size;

	switch( $p_filter_object->type ) {
		case FILTER_TYPE_STRING:
			echo '<input class="input-xs" type="text" name="', string_attribute( $p_field_name ), '"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' value="',
				string_attribute( $p_filter[$p_field_name] ), '"/>';
			break;

		case FILTER_TYPE_INT:
			echo '<input class="input-xs" type="text" name="', string_attribute( $p_field_name ), '"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' ), ' value="',
				(int)$p_filter[$p_field_name], '"/>';
			break;

		case FILTER_TYPE_BOOLEAN:
			echo '<input name="', string_attribute( $p_field_name ), '" type="hidden" value="', OFF ,'"/>';
			echo '<label>';
			echo '<input class="input-xs ace" name="', string_attribute( $p_field_name ), '" type="checkbox"',
				( $t_size > 0 ? ' size="' . $t_size . '"' : '' );
			check_checked( (bool)$p_filter[$p_field_name] );
			echo '"/>';
			echo '<span class="lbl"></span>';
			echo '</label>';
			break;

		case FILTER_TYPE_MULTI_STRING:
			echo '<select class="input-xs" ' . filter_select_modifier( $p_filter ) . ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ),
				' name="', string_attribute( $p_field_name ), '[]">', '<option value="', META_FILTER_ANY, '"';
			check_selected( $p_filter[$p_field_name], (string)META_FILTER_ANY );
			echo '>[', lang_get( 'any' ), ']</option>';

			foreach( $p_filter_object->options() as $t_option_value => $t_option_name ) {
				echo '<option value="', string_attribute( $t_option_value ), '" ';
				check_selected( $p_filter[$p_field_name], $t_option_value, false );
				echo '>', string_attribute( $t_option_name ), '</option>';
			}

			echo '</select>';
			break;

		case FILTER_TYPE_MULTI_INT:
			echo '<select class="input-xs"' . filter_select_modifier( $p_filter ) . ( $t_size > 0 ? ' size="' . $t_size . '"' : '' ),
				' name="', string_attribute( $p_field_name ), '[]">', '<option value="', META_FILTER_ANY, '"';
			check_selected( $p_filter[$p_field_name], META_FILTER_ANY );
			echo '>[', lang_get( 'any' ), ']</option>';

			foreach( $p_filter_object->options() as $t_option_value => $t_option_name ) {
				echo '<option value="', (int)$t_option_value, '" ';
				check_selected( $p_filter[$p_field_name], (int)$t_option_value );
				echo '>', string_attribute( $t_option_name ), '</option>';
			}

			echo '</select>';
			break;
	}
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 * @param integer $p_field_id Custom field id
 *
 * @return void
 */
function print_filter_values_custom_field( array $p_filter, $p_field_id ) {
	if( CUSTOM_FIELD_TYPE_DATE == custom_field_type( $p_field_id ) ) {
		print_filter_values_custom_field_date( $p_filter, $p_field_id );
		return;
	}

	$t_values = $p_filter['custom_fields'][$p_field_id] ?? array();
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
				$t_strings[] = string_attribute( $t_val );
			}
			$t_inputs[] = '<input type="hidden" name="custom_field_' . $p_field_id . '[]" value="' . string_attribute( $t_val ) . '" />';
		}
	}

	echo implode( '<br>', $t_strings );
	echo implode( '', $t_inputs );
}

/**
 * Print the filter field's current value (for a date type field) as a visible
 * string and a hidden form input.
 *
 * @param array $p_filter   Filter array to use
 * @param int   $p_field_id	Custom field id
 *
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

	# Relative mode: descriptors replace the displayed dates with the dates they
	# resolve to now, each carrying its expression as a tooltip. The
	# stored timestamps are not used, both because they go stale (see
	# filter_relative_date_display()) and because they carry the operator's
	# day-boundary offset; resolving the descriptor yields the plain calendar date
	# the boundary was derived from.
	$t_relative = isset( $p_filter[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE][$p_field_id] )
		? $p_filter[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE][$p_field_id]
		: null;
	$t_start_rel = ( is_array( $t_relative ) && !empty( $t_relative['start'] ) ) ? $t_relative['start'] : null;
	$t_end_rel = ( is_array( $t_relative ) && !empty( $t_relative['end'] ) ) ? $t_relative['end'] : null;
	$t_relative_mode = ( null !== $t_start_rel ) || ( null !== $t_end_rel );
	if( null !== $t_start_rel ) {
		$t_start = filter_relative_date_display( $t_start_rel, $t_short_date_format );
	}
	if( null !== $t_end_rel ) {
		$t_end = filter_relative_date_display( $t_end_rel, $t_short_date_format );
	}
	# Single-endpoint operators display the 'start' endpoint when relative.
	$t_single = ( null !== $t_start_rel ) ? $t_start : $t_end;
	# A relative field is marked once, before its dates.
	$t_icon = $t_relative_mode ? filter_relative_date_icon() . ' ' : '';

	switch( $p_filter['custom_fields'][$p_field_id][0] ) {
		case CUSTOM_FIELD_DATE_ANY:
			echo lang_get( 'any' );
			break;
		case CUSTOM_FIELD_DATE_NONE:
			echo lang_get( 'none' );
			break;
		case CUSTOM_FIELD_DATE_BETWEEN:
			echo lang_get( 'between_date' ) . '<br>';
			echo $t_icon, $t_start . '<br>' . $t_end;
			break;
		case CUSTOM_FIELD_DATE_ONORBEFORE:
			echo lang_get( 'on_or_before_date' ) . '<br>';
			echo $t_icon, $t_single;
			break;
		case CUSTOM_FIELD_DATE_BEFORE:
			echo lang_get( 'before_date' ) . '<br>';
			echo $t_icon, $t_single;
			break;
		case CUSTOM_FIELD_DATE_ON:
			echo lang_get( 'on_date' ) . '<br>';
			echo $t_icon, $t_start;
			break;
		case CUSTOM_FIELD_DATE_AFTER:
			echo lang_get( 'after_date' ) . '<br>';
			echo $t_icon, $t_start;
			break;
		case CUSTOM_FIELD_DATE_ONORAFTER:
			echo lang_get( 'on_or_after_date' ) . '<br>';
			echo $t_icon, $t_start;
			break;
	}
	# print hidden inputs
	$t_cf = $p_filter['custom_fields'][$p_field_id];
	echo '<input type="hidden" name="custom_field_' . $p_field_id . '_control" value="' . $t_cf[0] . '">';
	if( $t_relative_mode ) {
		echo '<input type="hidden" name="custom_field_' . $p_field_id . '_relative" value="on">';
		print_filter_hidden_relative_inputs( 'custom_field_' . $p_field_id . '_start_relative', $t_start_rel );
		print_filter_hidden_relative_inputs( 'custom_field_' . $p_field_id . '_end_relative', $t_end_rel );
	} else {
		echo '<input type="hidden" name="custom_field_' . $p_field_id . '_start_timestamp" value="' . $t_cf[1] . '">';
		echo '<input type="hidden" name="custom_field_' . $p_field_id . '_end_timestamp" value="' . $t_cf[2] . '">';
	}
}


/**
 * Print custom field input list.
 *
 * This function does not validate permissions
 *
 * @param integer    $p_field_id Custom field id
 * @param array|null $p_filter   Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_custom_field( $p_field_id, ?array $p_filter = null ) {
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
			echo '<input class="input-xs" type="text" name="custom_field_',
				string_html_specialchars( $p_field_id ),
				'" size="10" value="" >';
			break;

		default:
			echo '<select class="input-xs" ' . filter_select_modifier( $p_filter )
				. ' name="custom_field_' . string_html_specialchars( $p_field_id ) . '[]">';
			# Option META_FILTER_ANY
			echo '<option value="' . META_FILTER_ANY . '"';
			check_selected( $p_filter['custom_fields'][$p_field_id], META_FILTER_ANY, false );
			echo '>[' . lang_get( 'any' ) . ']</option>';
			# don't show META_FILTER_NONE for enumerated types as it's not possible for them to be blank
			if( !in_array( $t_cfdef['type'], array( CUSTOM_FIELD_TYPE_ENUM, CUSTOM_FIELD_TYPE_LIST ) ) ) {
				echo '<option value="' . META_FILTER_NONE . '"';
				check_selected( $p_filter['custom_fields'][$p_field_id], META_FILTER_NONE, false );
				echo '>[' . lang_get( 'none' ) . ']</option>';
			}
			# Print possible values
			$t_included_projects = filter_get_included_projects( $p_filter );
			$t_values = custom_field_distinct_values( $t_cfdef, $t_included_projects );
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
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_show_sort( array $p_filter ) {
	$p_sort_properties = filter_get_visible_sort_properties_array( $p_filter );
	$t_sort_fields = $p_sort_properties[FILTER_PROPERTY_SORT_FIELD_NAME];
	$t_dir_fields = $p_sort_properties[FILTER_PROPERTY_SORT_DIRECTION];

	# @TODO cproensa: this could be a constant, or config.
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
				$t_field_name = string_attribute( lang_get_defaulted( column_get_custom_field_name( $t_sort ) ) );
			} else {
				$t_field_name = string_get_field_name( $t_sort );
			}
			echo $t_field_name . ' ' . lang_get( 'bugnote_order_' . mb_strtolower( $t_dir_fields[$i] ) );
		} elseif ( $i == $t_max_displayed_sort ) {
			echo ', ...';
		}
		# All sort columns are placed in hidden fields
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_FIELD_NAME, '[]" value="', string_attribute( $t_sort_fields[$i] ), '" />';
		echo '<input type="hidden" name="', FILTER_PROPERTY_SORT_DIRECTION, '[]" value="', string_attribute( $t_dir_fields[$i] ), '" />';
	}
}

/**
 * Print sort fields.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_show_sort( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}

	# get visible columns, and filter out those that ar not sortable
	$t_visible_columns = array_filter( helper_get_columns_to_view(), 'column_is_sortable' );

	$t_shown_fields[''] = '';
	foreach( $t_visible_columns as $t_column ) {
		if(column_is_custom_field( $t_column ) ) {
			$t_field_name = string_attribute( lang_get_defaulted( column_get_custom_field_name( $t_column ) ) );
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

	# @TODO cproensa: this could be a constant, or config.
	$t_max_inputs_sort = 3;

	$t_print_select_inputs =
		function( $p_sort_val ='', $p_dir_val ='' ) use ( $t_shown_fields, $t_shown_dirs ) {
			echo '<select class="input-xs" name="', FILTER_PROPERTY_SORT_FIELD_NAME, '[]">';
			foreach( $t_shown_fields as $t_key => $t_val ) {
				echo '<option value="' . $t_key . '"';
				check_selected( $t_key, $p_sort_val );
				echo '>' . $t_val . '</option>';
			}
			echo '</select>';
			echo '<select class="input-xs" name="', FILTER_PROPERTY_SORT_DIRECTION, '[]">';
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
 * Print custom field date fields.
 *
 * @param integer    $p_field_id Custom field identifier.
 * @param array|null $p_filter   Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_custom_field_date( $p_field_id, ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	$t_cfdef = custom_field_get_definition( $p_field_id );
	$t_included_projects = filter_get_included_projects( $p_filter );
	$t_values = custom_field_distinct_values( $t_cfdef, $t_included_projects );

	$t_sel_start_year = null;
	$t_sel_end_year = null;
	if( is_array( $t_values ) && !empty( $t_values ) ) {
		# Sort the values so they are ordered numerically
		# (otherwise they are treated as strings, which may be wrong for dates before early 2001)
		array_multisort( $t_values, SORT_NUMERIC, SORT_ASC );
		$t_sel_start_year = date( 'Y', reset( $t_values ) );
		$t_sel_end_year = date( 'Y', end( $t_values ) );
	}

	$t_start = date( 'U' );

	# Default to today in filters
	$t_end = $t_start;

	$t_start_time = $p_filter['custom_fields'][$p_field_id][1] ?? 0;
	$t_end_time = $p_filter['custom_fields'][$p_field_id][2] ?? 0;

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

	# Relative mode is encoded by the presence of a descriptor for this field.
	# Fixed mode is the default. The operator (control) determines which
	# endpoints are active; the mode determines which set of inputs (fixed
	# year/month/day vs relative anchor/offset/unit) is enabled for them.
	$t_relative = isset( $p_filter[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE][$p_field_id] )
		? $p_filter[FILTER_PROPERTY_CUSTOM_FIELDS_RELATIVE][$p_field_id]
		: null;
	$t_start_desc = ( is_array( $t_relative ) && !empty( $t_relative['start'] ) ) ? $t_relative['start'] : null;
	$t_end_desc = ( is_array( $t_relative ) && !empty( $t_relative['end'] ) ) ? $t_relative['end'] : null;
	$t_relative_mode = ( null !== $t_start_desc ) || ( null !== $t_end_desc );

	# Compose mode with the operator's active-endpoint mask.
	$t_fixed_start_disable = $t_relative_mode ? true : $t_start_disable;
	$t_fixed_end_disable = $t_relative_mode ? true : $t_end_disable;
	$t_rel_start_disabled_attr = ( !$t_relative_mode || $t_start_disable ) ? ' disabled="disabled" ' : '';
	$t_rel_end_disabled_attr = ( !$t_relative_mode || $t_end_disable ) ? ' disabled="disabled" ' : '';

	# Only the selected mode's rows are shown, as for the built-in date fields.
	# The operator still governs which endpoints within the visible mode are
	# enabled, so a row can be visible but disabled.
	$t_fixed_row_style = $t_relative_mode ? ' style="display:none"' : '';
	$t_relative_row_style = $t_relative_mode ? '' : ' style="display:none"';

	$t_cf_name = 'custom_field_' . string_html_specialchars( $p_field_id );

	echo '<table><tr><td>' . "\n";
	echo '<select class="input-xs" size="1" name="' . $t_cf_name . '_control">' . "\n";
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

	# Fixed / relative mode select, matching the built-in date filters. Here the
	# operator select above is the field's on/off switch (ANY means "do not
	# filter"), so this only chooses the kind of bounds. Fixed is the default.
	echo "</td></tr>\n<tr><td>";
	echo '<select class="input-xs js_cf_date_mode" name="' . $t_cf_name . '_relative">' . "\n";
	echo '<option value="' . OFF . '"';
	check_selected( $t_relative_mode, false );
	echo '>' . lang_get( 'date_type_fixed' ) . '</option>' . "\n";
	echo '<option value="' . ON . '"';
	check_selected( $t_relative_mode, true );
	echo '>' . lang_get( 'date_type_relative' ) . '</option>' . "\n";
	echo '</select>' . "\n";

	echo '</td></tr>' . "\n" . '<tr class="js_cf_row_fixed"' . $t_fixed_row_style . '><td>';

	print_date_selection_set( $t_cf_name . '_start', config_get( 'short_date_format' ), $t_start, $t_fixed_start_disable, false, $t_sel_start_year, $t_sel_end_year, "input-xs" );
	print '</td></tr>' . "\n" . '<tr class="js_cf_row_fixed"' . $t_fixed_row_style . '><td>';
	print_date_selection_set( $t_cf_name . '_end', config_get( 'short_date_format' ), $t_end, $t_fixed_end_disable, false, $t_sel_start_year, $t_sel_end_year, "input-xs" );
	print '</td></tr>' . "\n" . '<tr class="js_cf_row_relative"' . $t_relative_row_style . '><td>';
	print_filter_relative_date_inputs( $t_cf_name . '_start_relative', $t_rel_start_disabled_attr, $t_start_desc );
	print '</td></tr>' . "\n" . '<tr class="js_cf_row_relative"' . $t_relative_row_style . '><td>';
	print_filter_relative_date_inputs( $t_cf_name . '_end_relative', $t_rel_end_disabled_attr, $t_end_desc );
	print "</td></tr>\n</table>";
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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

			if( META_FILTER_CURRENT == $t_current ) {
				$t_this_name = '[' . lang_get( 'current' ) . ']';
			} else {
				$t_this_name = project_get_name( $t_current, false );
			}
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_name );
		}
		echo $t_output;
	}
}

/**
 * Print project field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_project_id( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?>
		<!-- Project -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PROJECT_ID;?>[]">
			<option value="<?php echo META_FILTER_CURRENT ?>"
				<?php check_selected( $p_filter[FILTER_PROPERTY_PROJECT_ID], META_FILTER_CURRENT );?>>
				[<?php echo lang_get( 'current' )?>]
			</option>
			<?php print_project_option_list( $p_filter[FILTER_PROPERTY_PROJECT_ID] )?>
		</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_projection( array $p_filter ) {
	$t_filter = $p_filter;
	$t_output = '';
	$t_any_found = false;
	if( count( $t_filter[FILTER_PROPERTY_PROJECTION] ) == 0 ) {
		echo lang_get( 'any' );
	} else {
		$t_first_flag = true;
		foreach( $t_filter[FILTER_PROPERTY_PROJECTION] as $t_current ) {
			echo '<input type="hidden" name="', FILTER_PROPERTY_PROJECTION, '[]" value="', string_attribute( $t_current ), '" />';
			$t_this_string = '';
			if( filter_field_is_any( $t_current ) ) {
				$t_any_found = true;
			} else {
				$t_this_string = get_enum_element( 'projection', $t_current );
			}
			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}
			$t_output .= string_attribute( $t_this_string );
		}
		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}

/**
 * Print projection field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_projection( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	?><!-- Projection -->
			<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_PROJECTION;?>[]">
				<option value="<?php echo META_FILTER_ANY?>"<?php check_selected( $p_filter[FILTER_PROPERTY_PROJECTION], META_FILTER_ANY );?>>[<?php echo lang_get( 'any' )?>]</option>
				<?php print_enum_string_option_list( 'projection', $p_filter[FILTER_PROPERTY_PROJECTION] )?>
			</select>
		<?php
}

/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
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
 * Print filter match type selector.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_match_type( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
?>
		<!-- Project -->
		<select class="input-xs" <?php echo filter_select_modifier( $p_filter ) ?> name="<?php echo FILTER_PROPERTY_MATCH_TYPE;?>">
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
				$t_this_string = string_attribute( $t_current );
			}

			if( !$t_first_flag ) {
				$t_output .= '<br />';
			} else {
				$t_first_flag = false;
			}

			$t_output .= $t_this_string;
		}

		if( $t_any_found ) {
			echo lang_get( 'any' );
		} else {
			echo $t_output;
		}
	}
}


/**
 * Draw the table cells to view and edit a filter.
 *
 * This will usually be part of a form. This method only prints the cells, not
 * the table definition, or any other form element outside of that.
 * A filter array is provided, to populate the fields.
 * The form will use javascript to show dynamic completion of fields (unless the
 * parameter $p_static is provided).
 * A page name can be provided to be used as a fallback script when javascript is
 * not available on the client, and the form was rendered with dynamic fields.
 * By default, the fallback is the current page.
 *
 * @param array   $p_filter               Filter array to show.
 * @param boolean $p_for_screen           Type of output
 * @param boolean $p_static               Whether to print a static form (no
 *                                        dynamic fields)
 * @param string  $p_static_fallback_page Page name to use as javascript
 *                                        fallback
 * @param boolean $p_show_search          Whether to render the search field
 *                                        inside the general fields area. If
 *                                        false, the text search should be
 *                                        managed externally.
 * @return void
 * @throws StateException
 */
function filter_form_draw_inputs( $p_filter, $p_for_screen = true, $p_static = false, $p_static_fallback_page = null, $p_show_search = true ) {

	$t_filter = filter_ensure_valid_filter( $p_filter );
	$t_view_type = $t_filter['_view_type'];
	$t_source_query_id = isset( $t_filter['_source_query_id'] ) ? (int)$t_filter['_source_query_id'] : -1;

	# If it's a stored filter, linked to a specific project, use that project_id to render available fields
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

	$t_filter_projects = filter_get_included_projects( $t_filter, $t_project_id );

	if( null === $p_static_fallback_page ) {
		$p_static_fallback_page = $_SERVER['SCRIPT_NAME'];
	}
	$t_filters_url = helper_mantis_url( $p_static_fallback_page );
	$t_get_params = $_GET;
	$t_get_params['for_screen'] = $p_for_screen;
	$t_get_params['static'] = ON;
	$t_get_params['view_type'] = ( FILTER_VIEW_TYPE_ADVANCED == $t_view_type )
		? FILTER_VIEW_TYPE_ADVANCED
		: FILTER_VIEW_TYPE_SIMPLE;
	$t_filters_url = helper_url_combine( $t_filters_url, $t_get_params );

	$t_show_product_version =  version_should_show_product_version( $t_filter_projects );
	$t_show_build = $t_show_product_version && ( config_get( 'enable_product_build' ) == ON );

	# overload handler_id setting if user isn't supposed to see them (ref #6189)
	if( !access_has_any_project_level( 'view_handler_threshold' ) ) {
		$t_filter[FILTER_PROPERTY_HANDLER_ID] = array(
			META_FILTER_ANY,
		);
	}

	if ( config_get( 'use_dynamic_filters' ) ) {
		$t_dynamic_filter_expander_class = ' class="dynamic-filter-expander"';
	} else {
		$t_dynamic_filter_expander_class = '';
	}

	$get_field_header = function ( $p_id, $p_label, $p_dynamic = true ) use ( $t_filters_url, $p_static, $t_filter, $t_source_query_id, $t_dynamic_filter_expander_class ) {
		if( $p_static || !$p_dynamic ) {
			return $p_label;
		} else {
			if( filter_is_temporary( $t_filter ) ) {
				$t_data_filter_id = ' data-filter="' . filter_get_temporary_key( $t_filter ) . '"';
			} elseif ( isset( $t_filter['_filter_id'] ) ) {
				$t_data_filter_id = ' data-filter_id="' . $t_filter['_filter_id'] . '"';
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

	$t_row1 = new FilterBoxGridLayout( $t_filter_cols , TableGridLayout::ORIENTATION_VERTICAL );

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
        $get_field_header( 'show_reproducibility_filter', lang_get( 'reproducibility' ) ),
        filter_form_get_input( $t_filter, 'show_reproducibility', $t_show_inputs ),
        1 /* colspan */,
        null /* class */,
        'show_reproducibility_filter_target' /* content id */
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

	$t_row2 = new FilterBoxGridLayout( $t_filter_cols , TableGridLayout::ORIENTATION_VERTICAL );

	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'show_category_filter', lang_get( 'category' ) ),
			filter_form_get_input( $t_filter, 'show_category', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'show_category_filter_target' /* content id */
			));
	if( FILTER_VIEW_TYPE_SIMPLE == $t_view_type ) {
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
	if( ON == config_get( 'enable_projection' ) ) {
		$t_row2->add_item( new TableFieldsItem(
				$get_field_header( 'projection_filter', lang_get( 'projection' ) ),
				filter_form_get_input( $t_filter, 'projection', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'projection_filter_target' /* content id */
				));
	}
	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'do_filter_by_date_filter', lang_get( 'use_date_filters' ) ),
			filter_form_get_input( $t_filter, 'do_filter_by_date', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'do_filter_by_date_filter_target' /* content id */
			));
	$t_row2->add_item( new TableFieldsItem(
			$get_field_header( 'do_filter_by_last_updated_date_filter', lang_get( 'use_last_updated_date_filters' ) ),
			filter_form_get_input( $t_filter, 'do_filter_by_last_updated_date', $t_show_inputs ),
			1 /* colspan */,
			null /* class */,
			'do_filter_by_last_updated_date_filter_target' /* content id */
			));
	if( FILTER_VIEW_TYPE_ADVANCED == $t_view_type ) {
		$t_row2->add_item( new TableFieldsItem(
				$get_field_header( 'project_id_filter', lang_get( 'email_project' ) ),
				filter_form_get_input( $t_filter, 'project_id', $t_show_inputs ),
				1 /* colspan */,
				null /* class */,
				'project_id_filter_target' /* content id */
				));
	}

	$t_row3 = new FilterBoxGridLayout( $t_filter_cols , TableGridLayout::ORIENTATION_VERTICAL );

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
				$get_field_header( 'os_build_filter', lang_get( 'os_build' ) ),
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
	if( access_has_project_level( config_get( 'tag_view_threshold' ) ) ) {
		$t_row3->add_item( new TableFieldsItem(
				$get_field_header( 'tag_string_filter', lang_get( 'tags' ) ),
				filter_form_get_input( $t_filter, 'tag_string', $t_show_inputs ),
				3 /* colspan */,
				null /* class */,
				'tag_string_filter_target' /* content id */
				));
	}

	# plugin filters & custom fields

	$t_row_extra = new FilterBoxGridLayout( $t_filter_cols , TableGridLayout::ORIENTATION_VERTICAL );

	$t_plugin_filters = filter_get_plugin_filters();
	foreach( $t_plugin_filters as $t_field_name => $t_filter_object ) {
		$t_colspan = (int)$t_filter_object->colspan;
		$t_header = $get_field_header( string_attribute( $t_field_name ) . '_filter', string_attribute( $t_filter_object->title ) );
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
		$t_filter_included_projects = filter_get_included_projects( $t_filter );
		$t_custom_fields = custom_field_get_linked_ids( $t_filter_included_projects );
		$t_accessible_custom_fields = array();
		foreach( $t_custom_fields as $t_cfid ) {
			$t_cfdef = custom_field_get_definition( $t_cfid );
			$t_projects_to_check = array_intersect( $t_filter_included_projects, custom_field_get_project_ids( $t_cfid ) );
			if( $t_cfdef['filter_by']
				&& access_has_any_project_level( (int)$t_cfdef['access_level_r'], $t_projects_to_check ) ) {
				$t_accessible_custom_fields[] = $t_cfdef;
			}
		}

		if( !empty( $t_accessible_custom_fields ) ) {
			foreach( $t_accessible_custom_fields as $t_cfdef ) {
				$t_header = $get_field_header( 'custom_field_' . $t_cfdef['id'] . '_filter', string_attribute( lang_get_defaulted( $t_cfdef['name'] ) ) );
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

	$t_section_last = new FilterBoxGridLayout( $t_filter_cols , TableGridLayout::ORIENTATION_HORIZONTAL );

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

	if( $p_show_search ) {
		$t_section_search = new FilterBoxGridLayout( $t_filter_cols , TableGridLayout::ORIENTATION_HORIZONTAL );

		$t_section_search->add_item( new TableFieldsItem(
				$get_field_header( 'search_filter', lang_get( 'search' ), false /* don't expand this field */ ),
				filter_form_get_input( $t_filter, 'search', $t_show_inputs ),
				$t_filter_cols /* colspan */,
				'bigger-120' /* class */,
				'search_filter_target' /* content id */
				));
	}

	?>
	<table class="table table-bordered table-condensed2 filters">
		<?php
		$t_row1->render();
		$t_row2->render();
		$t_row3->render();
		$t_row_extra->render();
		$t_section_last->render_spacer();
		$t_section_last->render();
		if( $p_show_search ) {
			$t_section_search->render_spacer();
			$t_section_search->render();
		}
		?>
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


/**
 * Print the filter field's current value as a visible string and a hidden form input.
 *
 * @param array $p_filter Filter array
 *
 * @return void
 */
function print_filter_values_search( array $p_filter ) {
	# always show the search text input
	print_filter_search( $p_filter );
}

/**
 * Print search field.
 *
 * @param array|null $p_filter Filter array
 *
 * @return void
 * @global array     $g_filter
 */
function print_filter_search( ?array $p_filter = null ) {
	global $g_filter;
	if( null === $p_filter ) {
		$p_filter = $g_filter;
	}
	echo '<input type="text" id="filter-search-txt" class="input-sm" size="48" name="', FILTER_PROPERTY_SEARCH, '"
		placeholder="' . lang_get( 'search' ) . '" value="', string_attribute( $p_filter[FILTER_PROPERTY_SEARCH] ), '" />';
}
