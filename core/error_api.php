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
$g_errors_delayed = array();
$g_error_handled = false;
$g_error_proceed_url = null;
$g_error_send_page_header = true;

# Make sure we always capture User-defined errors regardless of ini settings
# These can be disabled in config_inc.php, see $g_display_errors
error_reporting( error_reporting() | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_USER_DEPRECATED );

global $g_bypass_error_handler;
if( !$g_bypass_error_handler ) {
	set_error_handler( 'error_handler' );
	set_exception_handler( 'error_exception_handler' );
}

/**
 * @global Exception $g_exception
 */
$g_exception = null;

/**
 * Unhandled exception handler
 *
 * @param \Mantis\Exceptions\MantisException|Exception|Error $p_exception The exception to handle
 * @return void
 */
function error_exception_handler( $p_exception ) {
	global $g_exception;

	# As per PHP documentation, null may be received to reset handler to default state.
	if( $p_exception === null ) {
		$g_exception = null;
		return;
	}

	$g_exception = $p_exception;

	if( is_a( $p_exception, 'Mantis\Exceptions\MantisException' ) ) {
		$t_params = $p_exception->getParams();
		if( !empty( $t_params ) ) {
			call_user_func_array( 'error_parameters', $t_params );
		}

		trigger_error( $p_exception->getCode(), ERROR );

		# It is not expected to get here, but just in case!
		return;
	}

	# trigger a generic error
	trigger_error( ERROR_PHP, ERROR );
}

/**
 * Get error stack based on last exception
 *
 * @param Exception|null $p_exception The exception to print stack trace for.  Null will check last seen exception.
 * @return array The stack trace as an array
 */
function error_stack_trace( $p_exception = null ) {
	if( $p_exception === null ) {
		global $g_exception;
		$p_exception = $g_exception;
	}

	if ( $p_exception === null ) {
		# The reported stack trace should begin with the function call where the
		# error was triggered, so we remove the internal error handler calls
		# (this function, its parent and the error handler itself).
		$t_stack = array_slice( debug_backtrace(), 3 );
	} else {
		$t_stack = $p_exception->getTrace();
	}

	return $t_stack;
}

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
 * @param integer $p_type    Contains the level of the error raised, as an integer.
 * @param string  $p_error   Contains the error message, as a string.
 * @param string  $p_file    Contains the filename that the error was raised in, as a string.
 * @param integer $p_line    Contains the line number the error was raised at, as an integer.
 * @return void
 * @uses lang_api.php
 * @uses config_api.php
 * @uses compress_api.php
 * @uses database_api.php (optional)
 * @uses html_api.php (optional)
 */
function error_handler( $p_type, $p_error, $p_file, $p_line ) {
	global $g_error_parameters, $g_error_handled, $g_error_proceed_url;
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

	$t_show_detailed_errors = config_get_global( 'show_detailed_errors' ) == ON;

	# Force errors to use HALT method.
	if( $p_type == E_USER_ERROR || $p_type == E_ERROR || $p_type == E_RECOVERABLE_ERROR ) {
		$t_method = DISPLAY_ERROR_HALT;
	}

	# build an appropriate error string
	$t_error_location = 'in \'' . $p_file .'\' line ' . $p_line;
	$t_error_description = '\'' . $p_error . '\' ' . $t_error_location;
	switch( $p_type ) {
		case E_WARNING:
			$t_error_type = 'SYSTEM WARNING';
			break;
		case E_NOTICE:
			$t_error_type = 'SYSTEM NOTICE';
			break;
		case E_STRICT:
			$t_error_type = 'STRICT NOTICE';
			break;
		case E_RECOVERABLE_ERROR:
			# This should generally be considered fatal (like E_ERROR)
			$t_error_type = 'SYSTEM ERROR';
			break;
		case E_DEPRECATED:
			$t_error_type = 'DEPRECATED';
			break;
		case E_USER_ERROR:
			if( $p_error == ERROR_PHP ) {
				$t_error_type = 'INTERNAL APPLICATION ERROR';

				global $g_exception;
				$t_error_description = $g_exception->getMessage();
				$t_error_to_log = $t_error_description . "\n" . error_stack_trace_as_string();
				error_log( $t_error_to_log );

				# If show detailed errors is OFF hide PHP exceptions since they sometimes
				# include file path.
				if( !$t_show_detailed_errors ) {
					$t_error_description = '';
				}
			} else {
				$t_error_type = 'APPLICATION ERROR #' . $p_error;
				$t_error_description = error_string( $p_error );
			}
			break;
		case E_USER_WARNING:
			$t_error_type = 'APPLICATION WARNING #' . $p_error;
			$t_error_description = error_string( $p_error ) . ' (' . $t_error_location . ')';
			break;
		case E_USER_NOTICE:
			# used for debugging
			$t_error_type = 'DEBUG';
			break;
		case E_USER_DEPRECATED:
			# Get details about the error, to facilitate debugging with a more
			# useful message including filename and line number.
			$t_stack = error_stack_trace();
			$t_caller = $t_stack[0];

			$t_error_type = 'WARNING';
			$t_error_description =  error_string( $p_error )
				. ' (in ' . $t_caller['file']
				. ' line ' . $t_caller['line'] . ')';

			if( $t_method == DISPLAY_ERROR_INLINE && php_sapi_name() != 'cli' ) {
				error_log_delayed( $t_error_description );
				$g_error_handled = true;
				return;
			}
			break;
		default:
			# shouldn't happen, just display the error just in case
			$t_error_type = 'UNHANDLED ERROR TYPE (' .
				'<a href="http://php.net/errorfunc.constants">' . $p_type. '</a>)';
			$t_error_description = $p_error . ' (' . $t_error_location . ')';
	}

	$t_error_description = nl2br( $t_error_description );

	if( php_sapi_name() == 'cli' ) {
		if( DISPLAY_ERROR_NONE != $t_method ) {
			echo $t_error_type . ': ' . $t_error_description . "\n";

			if( $t_show_detailed_errors ) {
				echo "\n";
				error_print_stack_trace();
			}
		}
		if( DISPLAY_ERROR_HALT == $t_method ) {
			exit(1);
		}
	} else {
		switch( $t_method ) {
			case DISPLAY_ERROR_HALT:
				# disable any further event callbacks
				if( function_exists( 'event_clear_callbacks' ) ) {
					event_clear_callbacks();
				}

				$t_oblen = ob_get_length();
				if( $t_oblen > 0 ) {
					$t_old_contents = ob_get_contents();
					if( !error_handled() ) {
						# Retrieve the previously output header
						if( false !== preg_match_all( '|^(.*)(</head>.*$)|is', $t_old_contents, $t_result ) &&
							isset( $t_result[1] ) && isset( $t_result[1][0] ) ) {
							$t_old_headers = $t_result[1][0];
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

				# If HTML error output was disabled, set an error header and stop
				if( defined( 'DISABLE_INLINE_ERROR_REPORTING' ) ) {
					# @TODO Have a mapping for mantis error codes to appropriate HTTP error codes
					header( ' ', true, 400 );
					exit(1);
				}

				# don't send the page header information if it has already been sent
				if( $g_error_send_page_header ) {
					if( $t_html_api ) {
						layout_page_header();
						if( $p_error != ERROR_DB_QUERY_FAILED && $t_db_connected == true ) {
							if( auth_is_user_authenticated() ) {
								layout_page_begin();
							} else {
								layout_navbar();
								layout_main_container_begin();
							}
						}
					} else {
						layout_page_header( $t_error_type );
					}
				} else {
					# Output the previously sent headers, if defined
					if( isset( $t_old_headers ) ) {
						echo $t_old_headers, "\n";
						layout_page_header_end();
						layout_page_begin();
					}
				}

				echo '<div class="col-md-12 col-xs-12">';
				echo '<div class="space-20"></div>', "\n";
				echo '<div class="alert alert-danger">', "\n";

				echo '<p class="bold">' . $t_error_type . '</p>', "\n";
				echo '<p>', $t_error_description, '</p>', "\n";

				echo '<div class="error-info">';
				if( null === $g_error_proceed_url ) {
					echo lang_get( 'error_no_proceed' );
				} else {
					echo '<a href="', $g_error_proceed_url, '">', lang_get( 'proceed' ), '</a>';
				}
				echo '</div>', "\n";

				if( $t_show_detailed_errors ) {
					error_print_details( $p_file, $p_line );
					error_print_stack_trace();
				}
				echo '</div></div>';

				if( isset( $t_old_contents ) ) {
					echo '<div class="col-md-12 col-xs-12">';
					echo '<div class="space-20"></div>';
					echo '<div class="alert alert-warning">';
					echo '<div class="warning">Previous non-fatal errors occurred.  Page contents follow.</div>';
					echo '<p>';
					echo $t_old_contents;
					echo '</p></div></div>';
				}

				if( $t_html_api ) {
					if( $p_error != ERROR_DB_QUERY_FAILED && $t_db_connected == true ) {
						if( auth_is_user_authenticated() ) {
							layout_page_end();
						} else {
							layout_main_container_end();
							layout_footer();
						}
					} else {
						layout_body_javascript();
						html_body_end();
						html_end();
					}
				} else {
					echo '</body></html>', "\n";
				}
				exit(1);

			case DISPLAY_ERROR_INLINE:
				if( !defined( 'DISABLE_INLINE_ERROR_REPORTING' ) ) {
					echo '<div class="alert alert-warning">', $t_error_type, ': ', $t_error_description, '</div>';
				}
				$g_error_handled = true;
				break;

			default:
				# do nothing - note we treat this as we've not handled an error, so any redirects go through.
		}
	}

	if( $t_lang_pushed ) {
		lang_pop();
	}

	$g_error_parameters = array();
	$g_error_proceed_url = null;
}


/**
 * Error handler to convert PHP errors to Exceptions.
 * This is used to temporarily override the default error handler, when it is
 * required to catch a PHP error (e.g. when unserializing data in install
 * helper functions).
 * @param integer $p_type    Level of the error raised.
 * @param string  $p_error   Error message.
 * @param string  $p_file    Filename that the error was raised in.
 * @param integer $p_line    Line number the error was raised at.
 * @throws ErrorException
 */
function error_convert_to_exception( $p_type, $p_error, $p_file, $p_line ) {
	throw new ErrorException( $p_error, 0, $p_type, $p_file, $p_line );
}

/**
 * Enqueues an error message for later display.
 * @see error_print_delayed()
 *
 * @param string $p_message Error message
 *
 * @return void
 */
function error_log_delayed( $p_message ) {
	global $g_errors_delayed;
	$g_errors_delayed[] = $p_message;
}

/**
 * Prints messages from the delayed errors queue.
 * The error handler enqueues deprecation warnings that would be printed inline,
 * to avoid display issues when they are triggered within html tags. Only unique
 * messages are printed.
 * @return void
 */
function error_print_delayed() {
	global $g_errors_delayed;

	if( !empty( $g_errors_delayed ) ) {
		echo '<div class="space-10 clearfix"></div>', "\n";
		echo '<div id="delayed-errors" class="alert alert-warning">';
		foreach( array_unique( $g_errors_delayed ) as $t_error ) {
			echo "\n" . '<div class="error-inline">', $t_error, '</div>';
		}
		echo "\n" . '</div>';

		$g_errors_delayed = array();
	}
}

/**
 * Print out the error details
 * @param string  $p_file    File error occurred in.
 * @param integer $p_line    Line number error occurred on.
 * @return void
 */
function error_print_details( $p_file, $p_line ) {
?>
	<div class="error-details">
		<hr>
		<h2>Detailed error information</h2>
		<ul>
			<li>Full path:
				<span class="code">
					<?php echo htmlentities( $p_file, ENT_COMPAT, 'UTF-8' );?>
				</span>
			</li>
			<li>Line number:
				<span class="code"><?php echo $p_line ?></span>
			</li>
		</ul>
	</div>
<?php
}


/**
 * Get the stack trace as a string that can be logged or echoed to CLI output.
 *
 * @param Exception|null $p_exception The exception to print stack trace for.  Null will check last seen exception.
 * @return string multi-line printout of stack trace.
 */
function error_stack_trace_as_string( $p_exception = null ) {
	$t_stack = error_stack_trace( $p_exception );
	$t_output = '';

	foreach( $t_stack as $t_frame ) {
		$t_output .= ( isset( $t_frame['file'] ) ? $t_frame['file'] : '-' ) . ': ' .
			( isset( $t_frame['line'] ) ? $t_frame['line'] : '-' ) . ': ' .
			( isset( $t_frame['class'] ) ? $t_frame['class'] : '-' ) . ' - ' .
			( isset( $t_frame['type'] ) ? $t_frame['type'] : '-' ) . ' - ' .
			( isset( $t_frame['function'] ) ? $t_frame['function'] : '-' );

		$t_args = array();
		if( isset( $t_frame['args'] ) && !empty( $t_frame['args'] ) ) {
			foreach( $t_frame['args'] as $t_value ) {
				$t_args[] = error_build_parameter_string( $t_value );
			}

			$t_output .= '( ' . implode( $t_args, ', ' ) . " )\n";
		} else {
			$t_output .= "()\n";
		}
	}

	return $t_output;
}

/**
 * Print out a stack trace
 *
 * @param Exception|null $p_exception The exception to print stack trace for.  Null will check last seen exception.
 */
function error_print_stack_trace( $p_exception = null ) {
	if( php_sapi_name() == 'cli' ) {
		echo error_stack_trace_as_string( $p_exception );
		return;
	}
?>
	<h3>Stack trace</h3>
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-condensed">
				<tr>
					<th>#</th>
					<th>Filename</th>
					<th>Line</th>
					<th>Class</th>
					<th>Type</th>
					<th>Function</th>
					<th>Args</th>
				</tr>
<?php
	$t_stack = error_stack_trace( $p_exception );

	foreach( $t_stack as $t_id => $t_frame ) {
		if( isset( $t_frame['args'] ) && !empty( $t_frame['args'] ) ) {
			$t_args = array();
			foreach( $t_frame['args'] as $t_value ) {
				$t_args[] = error_build_parameter_string( $t_value );
			}
		} else {
			$t_args = array('-');
		}

		printf(
			"<tr>\n" . str_repeat( "<td>%s</td>\n", 7 ) . "</tr>\n",
			$t_id,
			isset( $t_frame['file'] ) ? htmlentities( $t_frame['file'], ENT_COMPAT, 'UTF-8' ) : '-',
			isset( $t_frame['line'] ) ? $t_frame['line'] : '-',
			isset( $t_frame['class'] ) ? $t_frame['class'] : '-',
			isset( $t_frame['type'] ) ? $t_frame['type'] : '-',
			isset( $t_frame['function'] ) ? $t_frame['function'] : '-',
			htmlentities( implode( $t_args, ', ' ), ENT_COMPAT, 'UTF-8' )
		);

	}
	echo '</table>', '</div>';
}

/**
 * Build a string describing the parameters to a function
 * @param string|array|object $p_param    Parameter.
 * @param boolean             $p_showtype Default true.
 * @param integer             $p_depth    Default 0.
 * @return string
 */
function error_build_parameter_string( $p_param, $p_showtype = true, $p_depth = 0 ) {
	if( $p_depth++ > 10 ) {
		return '<strong>***Nesting Level Too Deep***</strong>';
	}

	if( is_array( $p_param ) ) {
		$t_results = array();

		foreach( $p_param as $t_key => $t_value ) {
			$t_results[] = '[' . error_build_parameter_string( $t_key, false, $p_depth ) . '] => ' . error_build_parameter_string( $t_value, false, $p_depth );
		}

		return '<array> { ' . implode( $t_results, ', ' ) . ' }';
	} else if( is_object( $p_param ) ) {
		$t_results = array();

		$t_class_name = get_class( $p_param );
		$t_inst_vars = get_object_vars( $p_param );

		foreach( $t_inst_vars as $t_name => $t_value ) {
			$t_results[] = '[' . $t_name . '] => ' . error_build_parameter_string( $t_value, false, $p_depth );
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
 * @param integer $p_error Error string to localize.
 * @return string
 * @access public
 */
function error_string( $p_error ) {
	global $g_error_parameters;

	$t_lang = null;
	while( true ) {
		$t_err_msg = lang_get( 'MANTIS_ERROR', $t_lang );
		if( array_key_exists( $p_error, $t_err_msg ) ) {
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
	$t_string = vsprintf( $t_error, array_merge( $g_error_parameters, $t_padding ) );
	return preg_replace( '/&amp;(#[0-9]+|[a-z]+);/i', '&$1;', @htmlspecialchars( $t_string, ENT_COMPAT, 'UTF-8' ) );
}

/**
 * Check if we have handled an error during this page
 * Return true if an error has been handled, false otherwise
 * @return boolean
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
 * @return void
 */
function error_parameters() {
	global $g_error_parameters;

	$g_error_parameters = func_get_args();
}

/**
 * Set a URL to give to the user to proceed after viewing the error
 * @access public
 * @param string $p_url URL given to user after viewing the error.
 * @return void
 */
function error_proceed_url( $p_url ) {
	global $g_error_proceed_url;

	$g_error_proceed_url = $p_url;
}

