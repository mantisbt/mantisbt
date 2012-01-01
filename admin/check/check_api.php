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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

$g_show_all = false;
$g_show_errors = false;

$g_failed_test = false;
$g_passed_test_with_warnings = false;

$g_alternate_row = 1;

$g_errors_temporarily_suppressed = false;
$g_errors_raised = array();

function check_init_error_handler() {
	set_error_handler( 'check_error_handler' );
	error_reporting( E_ALL );
}

function check_error_handler( $p_type, $p_error, $p_file, $p_line, $p_context ) {
	global $g_errors_raised;
	$g_errors_raised[] = array(
		'type' => $p_type,
		'error' => $p_error,
		'file' => $p_file,
		'line' => $p_line,
		'context' => $p_context
	);
}

function check_unhandled_errors_exist() {
	global $g_errors_raised;
	if ( count( $g_errors_raised ) > 0 ) {
		return true;
	}
	return false;
}

function check_print_error_rows() {
	global $g_show_errors, $g_errors_temporarily_suppressed, $g_errors_raised;
	if( !$g_show_errors || $g_errors_temporarily_suppressed ) {
		$g_errors_raised = array();
		return;
	}
	foreach( $g_errors_raised as $t_error ) {
		# build an appropriate error string
		switch( $t_error['type'] ) {
			case E_WARNING:
				$t_error_type = 'SYSTEM WARNING';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_NOTICE:
				$t_error_type = 'SYSTEM NOTICE';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			case E_USER_ERROR:
				$t_error_type = 'APPLICATION ERROR #' . $t_error['error'];
				$t_error_description = htmlentities( error_string( $t_error['error'] ) );
				break;
			case E_USER_WARNING:
				$t_error_type = 'APPLICATION WARNING #' . $t_error['error'];
				$t_error_description = htmlentities( error_string( $t_error['error'] ) );
				break;
			case E_USER_NOTICE:
				# used for debugging
				$t_error_type = 'DEBUG';
				$t_error_description = htmlentities( $t_error['error'] );
				break;
			default:
				# shouldn't happen, just display the error just in case
				$t_error_type = '';
				$t_error_description = htmlentities( $t_error['error'] );
		}
		echo "\t<tr>\n\t\t<td colspan=\"2\" class=\"error\">";
		echo "<strong>$t_error_type:</strong> $t_error_description<br />";
		echo '<em>Raised in file ' . htmlentities( $t_error['file'] ) . ' on line ' . htmlentities( $t_error['line'] ) . '</em>';
		echo "</td>\n\t</tr>\n";
	}
	$g_errors_raised = array();
}

function check_print_section_header_row( $p_heading ) {
?>
	<tr>
		<td colspan="2" class="thead2"><strong><?php echo $p_heading ?></strong></td>
	</tr>
<?php
}

function check_print_info_row( $p_description, $p_info = null ) {
	global $g_alternate_row, $g_show_all;
	if( !$g_show_all ) {
		return;
	}
	echo "\t<tr>\n\t\t<td class=\"description$g_alternate_row\">$p_description</td>\n";
	echo "\t\t<td class=\"info$g_alternate_row\">$p_info</td>\n\t</tr>\n";
	$g_alternate_row = $g_alternate_row === 1 ? 2 : 1;
}

function check_print_test_result( $p_result ) {
	global $g_alternate_row, $g_failed_test, $g_passed_test_with_warnings;
	switch ( $p_result ) {
		case BAD:
			echo "\t\t<td class=\"fail$g_alternate_row\">FAIL</td>\n";
			$g_failed_test = true;
			break;
		case GOOD:
			echo "\t\t<td class=\"pass$g_alternate_row\">PASS</td>\n";
			break;
		case WARN:
			echo "\t\t<td class=\"warn$g_alternate_row\">WARN</td>\n";
			$g_passed_test_with_warnings = true;
			break;
	}
}

function check_print_test_row( $p_description, $p_pass, $p_info = null ) {
	global $g_alternate_row, $g_show_all;
	if ( !$g_show_all && $p_pass ) {
		return $p_pass;
	}
	echo "\t<tr>\n\t\t<td class=\"description$g_alternate_row\">$p_description";
	if( $p_info !== null) {
		if( is_array( $p_info ) && isset( $p_info[$p_pass] ) ) {
			echo '<br /><em>' . $p_info[$p_pass] . '</em>';
		} else if( !is_array( $p_info ) ) {
			echo '<br /><em>' . $p_info . '</em>';
		}
	}
	echo "</td>\n";
	if( $p_pass && !check_unhandled_errors_exist() ) {
		check_print_test_result( GOOD );
	} else {
		check_print_test_result( BAD );
	}
	echo "\t</tr>\n";
	if( check_unhandled_errors_exist() ) {
		check_print_error_rows();
	}
	$g_alternate_row = $g_alternate_row === 1 ? 2 : 1;
	return $p_pass;
}

function check_print_test_warn_row( $p_description, $p_pass, $p_info = null ) {
	global $g_alternate_row, $g_show_all;
	if ( !$g_show_all && $p_pass ) {
		return $p_pass;
	}
	echo "\t<tr>\n\t\t<td class=\"description$g_alternate_row\">$p_description";
	if( $p_info !== null) {
		if( is_array( $p_info ) && isset( $p_info[$p_pass] ) ) {
			echo '<br /><em>' . $p_info[$p_pass] . '</em>';
		} else if( !is_array( $p_info ) ) {
			echo '<br /><em>' . $p_info . '</em>';
		}
	}
	echo "</td>\n";
	if( $p_pass && !check_unhandled_errors_exist() ) {
		check_print_test_result( GOOD );
	} else if( !check_unhandled_errors_exist() ) {
		check_print_test_result( WARN );
	} else {
		check_print_test_result( BAD );
	}
	echo "\t</tr>\n";
	if( check_unhandled_errors_exist() ) {
		check_print_error_rows();
	}
	$g_alternate_row = $g_alternate_row === 1 ? 2 : 1;
	return $p_pass;
}
