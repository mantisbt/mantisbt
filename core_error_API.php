<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Revision: 1.1 $
	# $Author: jfitzell $
	# $Date: 2002-08-23 07:08:40 $
	#
	# $Id: core_error_API.php,v 1.1 2002-08-23 07:08:40 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Error API
	###########################################################################

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
		global $g_display_errors, $g_show_detailed_errors;

		$t_short_file = basename( $p_file );

		switch ( $p_type ) {
			case E_WARNING:
				$t_display = "SYSTEM WARNING: $p_error <br> ($t_short_file: line $p_line)";
				break;
			case E_NOTICE:
				$t_display = "SYSTEM NOTICE: $p_error <br> ($t_short_file: line $p_line)";
				break;
			case E_USER_ERROR:
				$t_display = "MANTIS ERROR #$p_error: " .
							error_string( $p_error ) .
							"<br>($t_short_file: line $p_line)";
				break;
			case E_USER_WARNING:
				$t_display = "MANTIS WARNING #$p_error: " .
							error_string( $p_error ) .
							"<br>($t_short_file: line $p_line)";
				break;
			case E_USER_NOTICE:
				$t_display = "MANTIS NOTICE #$p_error: " .
							error_string( $p_error ) .
							"<br>($t_short_file: line $p_line)";
				break;
			default:
				#shouldn't happen, just display the error just in case
				$t_display = $p_error;
		}

		if ( 'halt' == $g_display_errors[$p_type] ) {
			ob_end_clean();

			print_page_top1();
			print_page_top2a();

			echo "<p class=\"center\" style=\"color:red\">$t_display</p>";

			if ( $g_show_detailed_errors ) {
			?>
				<center>
					<table class="width75">
						<tr>
							<td>Full path: <?php echo $p_file ?></td>
						</tr>
						<tr>
							<td>Line: <?php echo $p_line ?></td>
						</tr>
						<tr>
							<td>
								<table class="width100">
									<tr>
										<th>Variable</th>
										<th>Value</th>
										<th>Type</th>
									</tr>
			<?php
				while ( list( $t_var, $t_val ) = each( $p_context ) ) {
					echo "<tr><td>$t_var</td><td>$t_val</td><td>" . gettype( $t_val ) . "</td></tr>\n";
				}
			?>
								</table>
							</td>
						</tr>
					</table>
				</center>
			<?php
			}

			die();
		} else if ( 'inline' == $g_display_errors[$p_type] ) {
			echo "<p style=\"color:red\">$t_display</p>";
		} else {
			# do nothing
		}
	}

	# set up our new error handler
	set_error_handler( 'error_handler' );

	# return an error string (in the current language) for the given error
	function error_string( $p_error ) {
		global $MANTIS_ERROR;

		return $MANTIS_ERROR[$p_error];
	}
?>
