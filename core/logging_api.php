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
 * Logging API
 *
 * Provides functionality to log system events other than bug related history.
 *
 * @package CoreAPI
 * @subpackage LoggingAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses event_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'event_api.php' );
require_api( 'utility_api.php' );

$g_log_levels = array(
	LOG_EMAIL => 'MAIL',
	LOG_EMAIL_RECIPIENT => 'RECIPIENT',
	LOG_FILTERING => 'FILTER',
	LOG_AJAX => 'AJAX',
	LOG_LDAP => 'LDAP',
	LOG_DATABASE => 'DB',
	LOG_SOAP => 'SOAP'
);

/**
 * Log an event
 * @param int $p_level Valid debug log level
 * @param string|array $p_msg Either a string, or an array structured as (string,execution time)
 * @param object $p_backtrace [Optional] debug_backtrace() stack to use 
 * @return null
 */
function log_event( $p_level, $p_msg, $p_backtrace = null ) {
	global $g_log_levels;

	# check to see if logging is enabled
	$t_sys_log = config_get_global( 'log_level' );

	if ( 0 == ( $t_sys_log & $p_level ) ) {
		return;
	}

	if( is_array( $p_msg ) ) {
		$t_event = $p_msg;
		$s_msg = var_export( $p_msg, true );
	} else {
		$t_event = array( $p_msg, 0 );
		$s_msg = $p_msg;
	}

	if( $p_backtrace === null ) {
		$t_backtrace = debug_backtrace();
	} else {
		$t_backtrace = $p_backtrace;
	}
	$t_caller = basename( $t_backtrace[0]['file'] );
	$t_caller .= ":" . $t_backtrace[0]['line'];

	# Is this called from another function?
	if( isset( $t_backtrace[1] ) ) {
		$t_caller .= ' ' . $t_backtrace[1]['function'] . '()';
	} else {
		# or from a script directly?
		$t_caller .= ' ' . $_SERVER['SCRIPT_NAME'];
	}

	$t_now = date( config_get_global( 'complete_date_format' ) );
	$t_level = $g_log_levels[$p_level];

	$t_plugin_event = '[' . $t_level . '] ' . $p_msg;
	if( function_exists( 'event_signal' ) )
		event_signal( 'EVENT_LOG', array( $t_plugin_event ) );

	$t_log_destination = config_get_global( 'log_destination' );

	if ( is_blank( $t_log_destination ) ) {
		$t_destination = '';
	} else {
		@list( $t_destination, $t_modifiers ) = explode( ':', $t_log_destination, 2 );
	}

	switch( $t_destination ) {
		case 'none':
			break;
		case 'file':
			$t_php_event = $t_now . ' ' . $t_level . ' ' . $s_msg;
			error_log( $t_php_event . PHP_EOL, 3, $t_modifiers );
			break;
		case 'page':
			global $g_log_events;
			$g_log_events[] = array( time(), $p_level, $t_event, $t_caller);
			break;
		case 'firebug':
			if( !class_exists( 'FirePHP' ) ) {
				if( file_exists( config_get_global( 'library_path' ) . 'FirePHPCore' . DIRECTORY_SEPARATOR . 'FirePHP.class.php' ) ) {
					require_lib( 'FirePHPCore' . DIRECTORY_SEPARATOR . 'FirePHP.class.php' );
				}
			}
			if( class_exists( 'FirePHP' ) ) {
				static $firephp;
				if( $firephp === null ) {
					$firephp = FirePHP::getInstance(true);
				}
				$t_php_event = $t_now . ' ' . $t_level . ' ' . $s_msg;
				$firephp->log( $p_msg, $t_php_event );
				return;
			}
			// if firebug is not available, fall through
		default:
			# use default PHP error log settings
			$t_php_event = $t_now . ' ' . $t_level . ' ' . $s_msg;
			error_log( $t_php_event . PHP_EOL );
			break;
	}
}

function log_print_to_page() {
	if ( config_get_global( 'log_destination' ) === 'page' && auth_is_user_authenticated() && access_has_global_level( config_get( 'show_log_threshold' ) ) ) {
		global $g_log_events, $g_log_levels;
		echo "\n\n<!--Mantis Debug Log Output-->";
		echo "<hr />\n";
		echo "<table id=\"log-event-list\">\n";
		echo "\t<thead>\n";
		echo "\t\t<tr>\n";
		echo "\t\t\t<th>" . lang_get( 'log_page_number' ) . "</th>\n";
		echo "\t\t\t<th>" . lang_get( 'log_page_time' ) . "</th>\n";
		echo "\t\t\t<th>" . lang_get( 'log_page_caller' ) . "</th>\n";
		echo "\t\t\t<th>" . lang_get( 'log_page_event' ) . "</th>\n";
		echo "\t\t</tr>\n";
		echo "\t</thead>\n";
		echo "\t<tbody>\n";

		$t_unique_queries_count = 0;
		$t_total_query_execution_time = 0;
		$t_unique_queries = array();
		$t_total_queries_count = 0;
		$t_total_event_count = count( $g_log_events );

		if( $t_total_event_count == 0 ) {
			echo "\t</tbody>\n\t</table>\n";
			echo "<!--END Mantis Debug Log Output-->\n\n";
			return;
		}

		for ( $i = 0; $i < $t_total_event_count; $i++ ) {
			if( $g_log_events[$i][1] == LOG_DATABASE ) {
				if( !in_array( $g_log_events[$i][2][0], $t_unique_queries ) ) {
					$t_unique_queries_count++;
					$g_log_events[$i][2][2] = false;
					array_push( $t_unique_queries, $g_log_events[$i][2][0] );
				} else {
					$g_log_events[$i][2][2] = true;
				}
				$t_total_query_execution_time += $g_log_events[$i][2][1];
			}
		}

		$t_count = array();
		foreach( $g_log_events as $t_log_event ) {
			$t_level = $g_log_levels[$t_log_event[1]];
			$t_count[$t_log_event[1]]++;
			switch ( $t_log_event[1] ) {
				case LOG_DATABASE:
					$t_total_queries_count++;
					$t_query_duplicate_class = '';
					if ( $t_log_event[2][2] ) {
						$t_query_duplicate_class = ' class="duplicate-query"';
					}
					echo "\t\t<tr$t_query_duplicate_class><td>" . $t_level . '-' . $t_count[$t_log_event[1]] . "</td><td>" . $t_log_event[2][1] . "</td><td>" . string_html_specialchars ( $t_log_event[3] ) . "</td><td>" . string_html_specialchars( $t_log_event[2][0] ) . "</td></tr>\n";
					break;
				default:
					echo "\t\t<tr><td>" . $t_level . '-' . $t_count[$t_log_event[1]] . "</td><td>" . $t_log_event[2][1] . "</td><td>" . string_html_specialchars ( $t_log_event[3] ) . "</td><td>" . string_html_specialchars( $t_log_event[2][0] ) . "</td></tr>\n";
			}
		}

		# output any summary data
		if ( $t_unique_queries_count != 0 ) {
			$t_unique_queries_executed = sprintf( lang_get( 'unique_queries_executed' ), $t_unique_queries_count );
			echo "\t\t<tr><td>" . $g_log_levels[LOG_DATABASE] . '</td><td colspan="3">' . $t_unique_queries_executed . "</td></tr>\n";
		}
		if ( $t_total_queries_count != 0 ) {
			$t_total_queries_executed = sprintf( lang_get( 'total_queries_executed' ), $t_total_queries_count );
			echo "\t\t<tr><td>" . $g_log_levels[LOG_DATABASE] . '</td><td colspan="3">' . $t_total_queries_executed . "</td></tr>\n";
		}
		if ( $t_total_query_execution_time != 0 ) {
			$t_total_query_time = sprintf( lang_get( 'total_query_execution_time' ), $t_total_query_execution_time );
			echo "\t\t<tr><td>" . $g_log_levels[LOG_DATABASE] . '</td><td colspan="3">' . $t_total_query_time . "</td></tr>\n";
		}
		echo "\t</tbody>\n\t</table>\n";
	}
	echo "<!--END Mantis Debug Log Output-->\n\n";
}
