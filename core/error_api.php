<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: error_api.php,v 1.36 2004-10-24 14:12:47 vboctor Exp $
	# --------------------------------------------------------

	### Error API ###

	# set up error_handler() as the new default error handling function
	set_error_handler( 'error_handler' );

	#########################################
	# SECURITY NOTE: these globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_error_parameters		= array();
	$g_error_handled		= false;
	$g_error_proceed_url	= null;

	# ---------------
	# Default error handler
	#
	# This handler will not receive E_ERROR, E_PARSE, E_CORE_*, or E_COMPILE_*
	#  errors.
	#
	# E_USER_* are triggered by us and will contain an error constant in $p_error
	# The others, being system errors, will come with a string in $p_error
	#
	function error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
		global $g_error_parameters, $g_error_handled, $g_error_proceed_url;
		global $g_lang_overrides;

		# check if errors were disabled with @ somewhere in this call chain
		if ( 0 == error_reporting() ) {
			return;
		}

		# flush any language overrides to return to user's natural default
		lang_push( config_get ( 'default_language' ) );
		
		$t_short_file	= basename( $p_file );
		$t_method_array = config_get( 'display_errors' );
		if ( isset( $t_method_array[$p_type] ) ) {
			$t_method = $t_method_array[$p_type];
		}else{
			$t_method		= 'none';
		}

		# build an appropriate error string
		switch ( $p_type ) {
			case E_WARNING:
				$t_error_type = 'SYSTEM WARNING';
				$t_error_description = $p_error;
				break;
			case E_NOTICE:
				$t_error_type = 'SYSTEM NOTICE';
				$t_error_description = $p_error;
				break;
			case E_USER_ERROR:
				$t_error_type = "APPLICATION ERROR #$p_error";
				$t_error_description = error_string( $p_error );
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
				#shouldn't happen, just display the error just in case
				$t_error_type = '';
				$t_error_description = $p_error;
		}

		$t_error_description = nl2br( $t_error_description );

		if ( 'halt' == $t_method ) {
			$t_old_contents = ob_get_contents();
			# ob_end_clean() still sems to call the output handler which
			#  outputs the headers indicating compression. If we had
			#  PHP > 4.2.0 we could use ob_clean() instead but as it is
			#  we need to disable compression.
			compress_disable();

			if ( ob_get_length() ) {
				ob_end_clean();
			}

			html_page_top1();
			html_page_top2();

			PRINT '<br /><div align="center"><table class="width50" cellspacing="1">';
			PRINT "<tr><td class=\"form-title\">$t_error_type</td></tr>";
			PRINT "<tr><td><p class=\"center\" style=\"color:red\">$t_error_description</p></td></tr>";

			PRINT '<tr><td><p class="center">';
			if ( null === $g_error_proceed_url ) {
				PRINT lang_get( 'error_no_proceed' );
			} else {
				PRINT "<a href=\"$g_error_proceed_url\">" . lang_get( 'proceed' ) . '</a>';
			}
			PRINT '</p></td></tr>';

			if ( ON == config_get( 'show_detailed_errors' ) ) {
				PRINT '<tr><td>';
				error_print_details( $p_file, $p_line, $p_context );
				PRINT '</td></tr>';
				PRINT '<tr><td>';
				error_print_stack_trace();
				PRINT '</td></tr>';
			}
			PRINT '</table></div>';

			if ( $g_error_handled ) {
				PRINT '<p>Previous non-fatal errors occurred.  Page contents follow.</p>';

				PRINT '<div style="border: solid 1px black;padding: 4px">';
				PRINT $t_old_contents;
				PRINT '</div>';
			}

			html_page_bottom1();
			exit();
		} else if ( 'inline' == $t_method ) {
			PRINT "<p style=\"color:red\">$t_error_type: $t_error_description</p>";
		} else {
			# do nothing
		}

		lang_pop();
		$g_error_parameters = array();
		$g_error_handled = true;
		$g_error_proceed_url = null;
	}

	# ---------------
	# Print out the error details including context
	function error_print_details( $p_file, $p_line, $p_context ) {
?>
		<center>
			<table class="width75">
				<tr>
					<td>Full path: <?php PRINT htmlentities( $p_file ) ?></td>
				</tr>
				<tr>
					<td>Line: <?php PRINT $p_line ?></td>
				</tr>
				<tr>
					<td>
						<?php error_print_context( $p_context ) ?>
					</td>
				</tr>
			</table>
		</center>
<?php
	}

	# ---------------
	# Print out the variable context given
	function error_print_context( $p_context ) {
		PRINT '<table class="width100"><tr><th>Variable</th><th>Value</th><th>Type</th></tr>';

		# print normal variables
		foreach ( $p_context as $t_var => $t_val ) {
			if ( !is_array( $t_val ) && !is_object( $t_val ) ) {
				$t_val = htmlentities( (string)$t_val );
				$t_type = gettype( $t_val );

				# Mask Passwords
				if ( strpos( $t_var, 'password' ) !== false ) {
					$t_val = '**********';
				}

				PRINT "<tr><td>$t_var</td><td>$t_val</td><td>$t_type</td></tr>\n";
			}
		}

		# print arrays
		foreach ( $p_context as $t_var => $t_val ) {
			if ( is_array( $t_val ) && ( $t_var != 'GLOBALS' ) ) {
			    PRINT "<tr><td colspan=\"3\" align=\"left\"><br /><strong>$t_var</strong></td></tr>";
				PRINT "<tr><td colspan=\"3\">";
				error_print_context( $t_val );
				PRINT "</td></tr>";
			}
		}

		PRINT '</table>';
	}

	# ---------------
	# Print out a stack trace if PHP provides the facility or xdebug is present
	function error_print_stack_trace() {
		if ( extension_loaded( 'xdebug' ) ) { #check for xdebug presence
			$t_stack = xdebug_get_function_stack();

			# reverse the array in a separate line of code so the
			#  array_reverse() call doesn't appear in the stack
			$t_stack = array_reverse( $t_stack );
			array_shift( $t_stack ); #remove the call to this function from the stack trace

			PRINT '<center><table class="width75">';

			foreach ( $t_stack as $t_frame ) {
				PRINT '<tr ' . helper_alternate_class() . '>';
				PRINT '<td>' . htmlentities( $t_frame['file'] ) . '</td><td>' . $t_frame['line'] . '</td><td>' . $t_frame['function'] . '</td>';

				$t_args = array();
				if ( isset( $t_frame['params'] ) ) {
					foreach( $t_frame['params'] as $t_value ) {
						$t_args[] = error_build_parameter_string( $t_value );
					}
				}

				PRINT '<td>( ' . htmlentities( implode( $t_args, ', ' ) ) . ' )</td></tr>';
			}
			PRINT '</table></center>';
		} else if ( php_version_at_least( '4.3' ) ) {
			$t_stack = debug_backtrace();

			array_shift( $t_stack ); #remove the call to this function from the stack trace
			array_shift( $t_stack ); #remove the call to the error handler from the stack trace

			PRINT '<center><table class="width75">';
			PRINT '<tr><th>Filename</th><th>Line</th><th>Function</th><th>Args</th></tr>';

			foreach ( $t_stack as $t_frame ) {
				PRINT '<tr ' . helper_alternate_class() . '>';
				PRINT '<td>' . htmlentities( $t_frame['file'] ) . '</td><td>' . $t_frame['line'] . '</td><td>' . $t_frame['function'] . '</td>';

				$t_args = array();
				if ( isset( $t_frame['args'] ) ) {
					foreach( $t_frame['args'] as $t_value ) {
						$t_args[] = error_build_parameter_string( $t_value );
					}
				}

				PRINT '<td>( ' . htmlentities( implode( $t_args, ', ' ) ) . ' )</td></tr>';
			}

			PRINT '</table></center>';
		}
	}

	# ---------------
	# Build a string describing the parameters to a function
	function error_build_parameter_string( $p_param ) {
		if ( is_array( $p_param ) ) {
			$t_results = array();

			foreach ( $p_param as $t_key => $t_value ) {
				$t_results[] =	'[' . error_build_parameter_string( $t_key ) . ']' .
								' => ' . error_build_parameter_string( $t_value );
			}

			return '{ ' . implode( $t_results, ', ' ) . ' }';
		} else if ( is_bool( $p_param ) ) {
			if ( $p_param ) {
				return 'true';
			} else {
				return 'false';
			}
		} else if ( is_float( $p_param ) || is_int( $p_param ) ) {
			return $p_param;
		} else if ( is_null( $p_param ) ) {
			return 'null';
		} else if ( is_object( $p_param ) ) {
			$t_results = array();

			$t_class_name = get_class( $p_param );
			$t_inst_vars = get_object_vars( $p_param );

			foreach ( $t_inst_vars as $t_name => $t_value ) {
				$t_results[] =	"[$t_name]" .
								' => ' . error_build_parameter_string( $t_value );
			}

			return 'Object <$t_class_name> ( ' . implode( $t_results, ', ' ) . ' )';
		} else if ( is_string( $p_param ) ) {
			return "'$p_param'";
		}
	}

	# ---------------
	# Return an error string (in the current language) for the given error
	function error_string( $p_error ) {
		global $g_error_parameters;

		$MANTIS_ERROR = lang_get( 'MANTIS_ERROR' );

		# We pad the parameter array to make sure that we don't get errors if
		#  the caller didn't give enough parameters for the error string
		$t_padding = array_pad( array(), 10, '' );

		$t_error = $MANTIS_ERROR[$p_error];

		return call_user_func_array( 'sprintf', array_merge( array( $t_error ), $g_error_parameters, $t_padding ) );
	}

	# ---------------
	# Check if we have handled an error during this page
	# Return true if an error has been handled, false otherwise
	function error_handled() {
		global $g_error_handled;

		return ( true == $g_error_handled );
	}

	# ---------------
	# Set additional info parameters to be used when displaying the next error
	# This function takes a variable number of parameters
	#
	# When writing internationalized error strings, note that you can change the
	#  order of parameters in the string.  See the PHP manual page for the
	#  sprintf() function for more details.
	function error_parameters() {
		global $g_error_parameters;

		$g_error_parameters = func_get_args();
	}

	# ---------------
	# Set a url to give to the user to proceed after viewing the error
	function error_proceed_url( $p_url ) {
		global $g_error_proceed_url;

		$g_error_proceed_url = $p_url;
	}
?>