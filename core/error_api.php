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
 * Error API
 *
 * @package CoreAPI
 * @subpackage ErrorAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_api.php
 * @uses database_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

$g_error_parameters = array();
$g_error_handled = false;
$g_error_proceed_url = null;
$g_error_send_page_header = true;

# Make sure we always capture User-defined errors regardless of ini settings
# These can be disabled in config_inc.php, see $g_display_errors
error_reporting( error_reporting() | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE );

set_error_handler( 'error_handler' );

/**
 * Default error handler
 *
 * This handler will not receive E_ERROR, E_PARSE, E_CORE_*, or E_COMPILE_*
 *  errors.
 *
 * E_USER_* are triggered by us and will contain an error constant in $p_error
 * The others, being system errors, will come with a string in $p_error
 *
 * @access private
 * @param int p_type contains the level of the error raised, as an integer.
 * @param string p_error contains the error message, as a string.
 * @param string p_file contains the filename that the error was raised in, as a string.
 * @param int p_line contains the line number the error was raised at, as an integer.
 * @param array p_context to the active symbol table at the point the error occurred (optional)
 * @uses lang_api.php
 * @uses config_api.php
 * @uses compress_api.php
 * @uses database_api.php (optional)
 * @uses html_api.php (optional)
 */
function error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
	global $g_error_parameters, $g_error_handled;
	global $g_error_send_page_header;

	# check if errors were disabled with @ somewhere in this call chain
	if( 0 == error_reporting() ) {
		return;
	}

	$t_lang_pushed = false;

	$t_db_connected = false;
	if( function_exists( 'db_is_connected' ) ) {
		if( db_is_connected() ) {
			$t_db_connected = true;
		}
	}
	$t_html_api = false;
	if( function_exists( 'html_end' ) ) {
		$t_html_api = true;
	}

	# flush any language overrides to return to user's natural default
	if( $t_db_connected ) {
		lang_push( lang_get_default() );
		$t_lang_pushed = true;
	}

	$t_method_array = config_get_global( 'display_errors' );
	if( isset( $t_method_array[$p_type] ) ) {
		$t_method = $t_method_array[$p_type];
	} else {
		if( isset( $t_method_array[E_ALL] ) ) {
			$t_method = $t_method_array[E_ALL];
		} else {
			$t_method = 'none';
		}
	}

	# build an appropriate error string
	switch( $p_type ) {
		case E_WARNING:
			$t_error_type = 'SYSTEM WARNING';
			$t_error_description = "'$p_error' in '$p_file' line $p_line";
			break;
		case E_NOTICE:
			$t_error_type = 'SYSTEM NOTICE';
			$t_error_description = "'$p_error' in '$p_file' line $p_line";
			break;
		case E_USER_ERROR:
			$t_error_type = "APPLICATION ERROR #$p_error";
			$t_error_description = error_string( $p_error );
			if( $t_method == DISPLAY_ERROR_INLINE ) {
				$t_error_description .= "\n" . error_string( ERROR_DISPLAY_USER_ERROR_INLINE );
			}
			break;
		case E_USER_WARNING:
			$t_error_type = "APPLICATION WARNING #$p_error";
			$t_error_description = error_string( $p_error );
			break;
		case E_USER_NOTICE:

			# used for debugging
			$t_error_type = 'DEBUG';
			$t_error_description = $p_error;
			break;
		default:

			# shouldn't happen, just display the error just in case
			$t_error_type = '';
			$t_error_description = $p_error;
	}

	$t_error_description = nl2br( $t_error_description );

	if( php_sapi_name() == 'cli' ) {
		echo $t_error_type . " " . $t_error_description . "\n";
		exit();
	}

	switch( $t_method ) {
		case DISPLAY_ERROR_HALT:
			# disable any further event callbacks
			if ( function_exists( 'event_clear_callbacks' ) ) {
				event_clear_callbacks();
			}

			$t_oblen = ob_get_length();
			if( $t_oblen > 0 ) {
				$t_old_contents = ob_get_contents();
				if( !error_handled() ) {
					# Retrieve the previously output header
					if( false !== preg_match_all( '|^(.*)(</head>.*$)|is', $t_old_contents, $result ) ) {
						$t_old_headers = $result[1][0];
						unset( $t_old_contents );
					}
				}
			}

			# We need to ensure compression is off - otherwise the compression headers are output.
			compress_disable();

			# then clean the buffer, leaving output buffering on.
			if( $t_oblen > 0 ) {
				ob_clean();
			}

			# don't send the page header information if it has already been sent
			if( $g_error_send_page_header ) {
				if( $t_html_api ) {
					html_page_top1();
					if( $p_error != ERROR_DB_QUERY_FAILED && $t_db_connected == true ) {
						html_page_top2();
					} else {
						html_page_top2a();
					}
				} else {
					echo '<html><head><title>', $t_error_type, '</title></head><body>';
				}
			} else {
				# Output the previously sent headers, if defined
				if( isset( $t_old_headers ) ) {
					echo $t_old_headers, "\n";
					html_page_top2();
				}
			}

			echo '<div id="error-msg">';
			echo '<div class="error-type">' . $t_error_type . '</div>';
			echo '<div class="error-description">', $t_error_description, '</div>';

			echo '<div class="error-info">';
			if( null === $g_error_proceed_url ) {
				echo lang_get( 'error_no_proceed' );
			} else {
				echo '<a href="', $g_error_proceed_url, '">', lang_get( 'proceed' ), '</a>';
			}
			echo '</div>';

			if( ON == config_get_global( 'show_detailed_errors' ) ) {
				echo '<div class="error-details">';
				error_print_details( $p_file, $p_line, $p_context );
				echo '</div>';
				echo '<div class="error-trace">';
				error_print_stack_trace();
				echo '</div>';
			}
			echo '</div>';

			if( isset( $t_old_contents ) ) {
				echo '<div class="warning">Previous non-fatal errors occurred.  Page contents follow.</div>';
				echo '<div id="old-contents">';
				echo $t_old_contents;
				echo '</div>';
			}

			if( $t_html_api ) {
				if( $p_error != ERROR_DB_QUERY_FAILED && $t_db_connected == true ) {
					html_page_bottom();
				} else {
					html_body_end();
					html_end();
				}
			} else {
				echo '</body></html>', "\n";
			}
			exit();
		case DISPLAY_ERROR_INLINE:
			echo '<div class="error-inline">', $t_error_type, ': ', $t_error_description, '</div>';
			$g_error_handled = true;
			break;
		default:
			# do nothing - note we treat this as we've not handled an error, so any redirects go through.
		}

		if( $t_lang_pushed ) {
			lang_pop();
	}

	$g_error_parameters = array();
	$g_error_proceed_url = null;
}

/**
 * Print out the error details including context
 * @param string $p_file
 * @param int $p_line
 * @param string $p_context
 * @return null
 */
function error_print_details( $p_file, $p_line, $p_context ) {
	?>
		<table class="width90">
			<tr>
				<td>Full path: <?php echo htmlentities( $p_file, ENT_COMPAT, 'UTF-8' );?></td>
			</tr>
			<tr>
				<td>Line: <?php echo $p_line?></td>
			</tr>
			<tr>
				<td>
					<?php error_print_context( $p_context )?>
				</td>
			</tr>
		</table>
<?php
}

/**
 * Print out the variable context given
 * @param string $p_context
 * @return null
 */
function error_print_context( $p_context ) {
	if( !is_array( $p_context ) ) {
		return;
	}

	echo '<table class="width100" style="table-layout:fixed;"><tr><th>Variable</th><th>Value</th><th>Type</th></tr>';

	# print normal variables
	foreach( $p_context as $t_var => $t_val ) {
		if( !is_array( $t_val ) && !is_object( $t_val ) ) {
			$t_type = gettype( $t_val );
			$t_val = htmlentities( (string) $t_val, ENT_COMPAT, 'UTF-8' );

			# Mask Passwords
			if( strpos( $t_var, 'password' ) !== false ) {
				$t_val = '**********';
			}

			echo '<tr><td style="width=20%; word-wrap:break-word; overflow:auto;">', $t_var,
				'</td><td style="width=70%; word-wrap:break-word; overflow:auto;">', $t_val,
				'</td><td style="width=10%;">', $t_type, '</td></tr>', "\n";
		}
	}

	# print arrays
	foreach( $p_context as $t_var => $t_val ) {
		if( is_array( $t_val ) && ( $t_var != 'GLOBALS' ) ) {
			echo '<tr><td colspan="3" style="word-wrap:break-word"><br /><strong>', $t_var, '</strong></td></tr>';
			echo '<tr><td colspan="3">';
			error_print_context( $t_val );
			echo '</td></tr>';
		}
	}

	echo '</table>';
}

/**
 * Print out a stack trace
 * @return null
 * @uses error_alternate_class
 */
function error_print_stack_trace() {
	echo '<table class="width90">';
	echo '<tr><th>Filename</th><th>Line</th><th></th><th></th><th>Function</th><th>Args</th></tr>';

	$t_stack = debug_backtrace();

	array_shift( $t_stack );

	# remove the call to this function from the stack trace
	array_shift( $t_stack );

	# remove the call to the error handler from the stack trace

	foreach( $t_stack as $t_frame ) {
		echo '<tr ', error_alternate_class(), '>';
		echo '<td>', ( isset( $t_frame['file'] ) ? htmlentities( $t_frame['file'], ENT_COMPAT, 'UTF-8' ) : '-' ), '</td><td>', ( isset( $t_frame['line'] ) ? $t_frame['line'] : '-' ), '</td><td>', ( isset( $t_frame['class'] ) ? $t_frame['class'] : '-' ), '</td><td>', ( isset( $t_frame['type'] ) ? $t_frame['type'] : '-' ), '</td><td>', ( isset( $t_frame['function'] ) ? $t_frame['function'] : '-' ), '</td>';

		$t_args = array();
		if( isset( $t_frame['args'] ) && !empty( $t_frame['args'] ) ) {
			foreach( $t_frame['args'] as $t_value ) {
				$t_args[] = error_build_parameter_string( $t_value );
			}
			echo '<td>( ', htmlentities( implode( $t_args, ', ' ), ENT_COMPAT, 'UTF-8' ), ' )</td></tr>';
		} else {
			echo '<td>-</td></tr>';
		}
	}
	echo '</table>';
}

/**
 * Build a string describing the parameters to a function
 * @param string $p_param
 * @param bool $p_showtype default true
 * @param int $p_depth default 0
 * @return string
 */
function error_build_parameter_string( $p_param, $p_showtype = true, $p_depth = 0 ) {
	if( $p_depth++ > 10 ) {
		return '<strong>***Nesting Level Too Deep***</strong>';
	}

	if( is_array( $p_param ) ) {
		$t_results = array();

		foreach( $p_param as $t_key => $t_value ) {
			$t_results[] = '[' . error_build_parameter_string( $t_key, false, $p_depth ) . ']' . ' => ' . error_build_parameter_string( $t_value, false, $p_depth );
		}

		return '<array> { ' . implode( $t_results, ', ' ) . ' }';
	}
	else if( is_object( $p_param ) ) {
		$t_results = array();

		$t_class_name = get_class( $p_param );
		$t_inst_vars = get_object_vars( $p_param );

		foreach( $t_inst_vars as $t_name => $t_value ) {
			$t_results[] = "[$t_name]" . ' => ' . error_build_parameter_string( $t_value, false, $p_depth );
		}

		return '<Object><' . $t_class_name . '> ( ' . implode( $t_results, ', ' ) . ' )';
	} else {
		if( $p_showtype ) {
			return '<' . gettype( $p_param ) . '>' . var_export( $p_param, true );
		} else {
			return var_export( $p_param, true );
		}
	}
}

/**
 * Return an error string (in the current language) for the given error.
 * @param int $p_error
 * @return string
 * @access public
 */
function error_string( $p_error ) {
	global $g_error_parameters;

	$t_lang = null;
	while( true ) {
		$t_err_msg = lang_get( 'MANTIS_ERROR', $t_lang );
		if( array_key_exists( $p_error, $t_err_msg) ) {
			$t_error = $t_err_msg[$p_error];
			break;
		} elseif( is_null( $t_lang ) ) {
			# Error string not found, fall back to English
			$t_lang = 'english';
		} else {
			# Error string not found
			$t_error = lang_get( 'missing_error_string' );
			# Prepend the error number
			array_unshift( $g_error_parameters, $p_error );
			break;
		}
	}

	# We pad the parameter array to make sure that we don't get errors if
	#  the caller didn't give enough parameters for the error string
	$t_padding = array_pad( array(), 10, '' );

	# ripped from string_api
	$t_string = vsprintf ( $t_error, array_merge( $g_error_parameters, $t_padding ) );
	return preg_replace( "/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", @htmlspecialchars( $t_string, ENT_COMPAT, 'UTF-8' ) );
}

/**
 * Check if we have handled an error during this page
 * Return true if an error has been handled, false otherwise
 * @return bool
 */
function error_handled() {
	global $g_error_handled;

	return( true == $g_error_handled );
}

/**
 * Set additional info parameters to be used when displaying the next error
 * This function takes a variable number of parameters
 *
 * When writing internationalized error strings, note that you can change the
 *  order of parameters in the string.  See the PHP manual page for the
 *  sprintf() function for more details.
 * @access public
 * @return null
 */
function error_parameters() {
	global $g_error_parameters;

	$g_error_parameters = func_get_args();
}

/**
 * Set a url to give to the user to proceed after viewing the error
 * @access public
 * @param string p_url url given to user after viewing the error
 * @return null
 */
function error_proceed_url( $p_url ) {
	global $g_error_proceed_url;

	$g_error_proceed_url = $p_url;
}

/**
 * Simple version of helper_alternate_class for use by error api only.
 * @access private
 * @return string representing css class
 */
function error_alternate_class() {
	static $t_errindex = 1;

	if( 1 == $t_errindex++ % 2 ) {
		return 'class="row-1"';
	} else {
		return 'class="row-2"';
	}
}
