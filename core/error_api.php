<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: error_api.php,v 1.23 2003-04-23 22:17:22 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Error API
	###########################################################################

	# set up error_handler() as the new default error handling function
	set_error_handler( 'error_handler' );

	#########################################
	# SECURITY NOTE: these globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on
	#
	$g_error_parameters = array();
	$g_error_handled = false;
	$g_error_proceed_url = null;

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
		
		# check if errors were disabled with @ somewhere in this call chain
		if ( 0 == error_reporting() ) {
			return;
		}

		$t_short_file = basename( $p_file );
		$t_method = 'none';

		# build an appropriate error string
		switch ( $p_type ) {
			case E_WARNING:
				$t_error_type = 'SYSTEM WARNING';
				$t_error_description = $p_error;
				if ( ON == config_get( 'show_warnings' ) ) {
					$t_method = 'inline';
				}
				break;
			case E_NOTICE:
				$t_error_type = 'SYSTEM NOTICE';
				$t_error_description = $p_error;
				if ( ON == config_get( 'show_notices' ) ) {
					$t_method = 'inline';
				}
				break;
			case E_USER_ERROR:
				$t_error_type = "APPLICATION ERROR #$p_error";
				$t_error_description = error_string( $p_error );
				$t_method = 'halt';
				break;
			case E_USER_WARNING:
				$t_error_type = "APPLICATION WARNING #$p_error";
				$t_error_description = error_string( $p_error );
				if ( ON == config_get( 'show_warnings' ) ) {
					$t_method = 'inline';
				}
				break;
			case E_USER_NOTICE:
				# used for debugging
				$t_error_type = 'DEBUG';
				$t_error_description = $p_error;
				if ( ON == config_get( 'show_notices' ) ) {
					$t_method = 'inline';
				}
				break;
			default:
				#shouldn't happen, just display the error just in case
				$t_error_type = '';
				$t_error_description = $p_error;
		}

		$t_error_description = nl2br( htmlentities( $t_error_description ) );

		if ( 'halt' == $t_method ) {
			$t_old_contents = ob_get_contents();
			# ob_end_clean() still sems to call the output handler which
			#  outputs the headers indicating compression. If we had
			#  PHP > 4.2.0 we could use ob_clean() instead but as it is
			#  we need to disable compression.
			compress_disable();
			ob_end_clean();

			html_page_top1();
			html_page_top2();

			echo '<br /><div align="center"><table class="width50" cellspacing="1">';
			echo "<tr><td class=\"form-title\">$t_error_type</td></tr>";
			echo "<tr><td><p class=\"center\" style=\"color:red\">$t_error_description</p></td></tr>";
			
			echo '<tr><td><p class="center">';
			if ( null === $g_error_proceed_url ) {
				echo lang_get( 'error_no_proceed' );
			} else {
				echo "<a href=\"$g_error_proceed_url\">" . lang_get( 'proceed' ) . '</a>';
			}
			echo '</p></td></tr>';
			
			# @@@ temp until we get parameterized errors
			echo '<tr><td>';
			for ( $i = 0 ; $i < sizeof( $g_error_parameters ) ; $i = $i + 1 ) {
				echo $g_error_parameters[$i].'<br />';
			}
			echo '</td></tr>';

			if ( ON == config_get( 'show_detailed_errors' ) ) {
				echo '<tr><td>';
				error_print_details( $p_file, $p_line, $p_context );
				echo '</td></tr>';
				echo '<tr><td>';
				error_print_stack_trace();
				echo '</td></tr>';
			}
			echo '</table></div>';

			if ( $g_error_handled ) {
				echo '<p>Previous non-fatal errors occurred.  Page contents follow.</p>';

				echo '<div style="border: solid 1px black;padding: 4px">';
				echo $t_old_contents;
				echo '</div>';
			}

			html_page_bottom1();
			exit();
		} else if ( 'inline' == $t_method ) {
			echo "<p style=\"color:red\">$t_error_type: $t_error_description</p>";
			# @@@ temp until we get parameterized errors
			for ( $i = 0 ; $i < sizeof( $g_error_parameters ) ; $i = $i + 1 ) {
				echo $g_error_parameters[$i].'<br />';
			}
		} else {
			# do nothing
		}

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
					<td>Full path: <?php echo htmlentities( $p_file ) ?></td>
				</tr>
				<tr>
					<td>Line: <?php echo $p_line ?></td>
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
		echo '<table class="width100"><tr><th>Variable</th><th>Value</th><th>Type</th></tr>';

		# print normal variables
		foreach ( $p_context as $t_var => $t_val ) {
			if ( !is_array( $t_val ) && !is_object( $t_val ) ) {
				echo "<tr><td>$t_var</td><td>" . htmlentities( (string)$t_val ) . '</td><td>' . gettype( $t_val ) . "</td></tr>\n";
			}
		}

		# print arrays
		foreach ( $p_context as $t_var => $t_val ) {
			if ( is_array( $t_val ) && ( $t_var != 'GLOBALS' ) ) {
			    echo "<tr><td colspan=\"3\" align=\"left\"><br /><strong>$t_var</strong></td></tr>";
				echo "<tr><td colspan=\"3\">";
				error_print_context( $t_val );
				echo "</td></tr>";
			}
		}

		echo '</table>';
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

			echo '<center><table class="width75">';

			for ( $i = 0 ; $i < sizeof( $t_stack ) ; $i = $i + 1 ) {
				echo '<tr ' . helper_alternate_class( $i ) . '"><td>' . htmlentities( $t_stack[$i] ) . '</td></tr>';
			}

			echo '</table></center>';
		} else if ( php_version_at_least( '4.3' ) ) {
			$t_stack = debug_backtrace();

			array_shift( $t_stack ); #remove the call to this function from the stack trace
			array_shift( $t_stack ); #remove the call to the error handler from the stack trace

			echo '<center><table class="width75">';
			echo '<tr><th>Filename</th><th>Line</th><th>Function</th><th>Args</th></tr>';

			foreach ( $t_stack as $t_frame ) {
				echo '<tr ' . helper_alternate_class() . '">';
				echo '<td>' . htmlentities( $t_frame['file'] ) . "</td><td>$t_frame[line]</td><td>$t_frame[function]</td>";

				$t_args = array();
				if ( isset( $t_frame['args'] ) ) {
					foreach( $t_frame['args'] as $t_value ) {
						$t_args[] = error_build_parameter_string( $t_value );
					}
				}

				echo '<td>( ' . htmlentities( implode( $t_args, ', ' ) ) . ' )</td></tr>';
			}

			echo '</table></center>';
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
		$MANTIS_ERROR = lang_get( 'MANTIS_ERROR' );
		
		return $MANTIS_ERROR[$p_error];
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
