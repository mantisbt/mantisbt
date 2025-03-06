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
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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
	LOG_EMAIL_VERBOSE => 'MAIL_VERBOSE',
	LOG_FILTERING => 'FILTER',
	LOG_AJAX => 'AJAX',
	LOG_LDAP => 'LDAP',
	LOG_DATABASE => 'DB',
	LOG_WEBSERVICE => 'WEBSERVICE',
	LOG_PLUGIN => 'PLUGIN',
);

/**
 * Log an event
 * @param integer          $p_level Valid debug log level.
 * @param string|array,... $p_msg   Either a string, or an array structured as (string,execution time).
 * @return void
 */
function log_event( $p_level, $p_msg ) {
	global $g_log_levels;

	# check to see if logging is enabled
	$t_sys_log = config_get_global( 'log_level' );

	if( 0 == ( $t_sys_log & $p_level ) ) {
		return;
	}

	if( is_array( $p_msg ) ) {
		$t_event = $p_msg;
		$t_msg = var_export( $p_msg, true );
	} else {
		if( func_num_args() > 2 ) {
			$t_args = func_get_args();
			array_shift( $t_args ); # skip level
			array_shift( $t_args ); # skip message
			$p_msg = vsprintf( $p_msg, $t_args );
		}
		$t_event = array( $p_msg, 0 );
		$t_msg = $p_msg;
	}

	$t_caller = log_get_caller( $p_level );

	$t_now = date( config_get_global( 'complete_date_format' ) );
	$t_level = $g_log_levels[$p_level];

	# Prevent recursion from plugins hooked on EVENT_LOG
	static $s_event_log_called = false;
	# Checking existence of event_signal function is required, because Logging
	# API is loaded before Event API.
	if( !$s_event_log_called && function_exists( 'event_signal' ) ) {
		$t_plugin_event = '[' . $t_level . '] ' . $t_msg;
		$s_event_log_called = true;
		event_signal( 'EVENT_LOG', array( $t_plugin_event ) );
		$s_event_log_called = false;
	}

	$t_log_destination = config_get_global( 'log_destination' );

	if( is_blank( $t_log_destination ) ) {
		$t_destination = '';
	} else {
		$t_result = explode( ':', $t_log_destination, 2 );
		$t_destination = $t_result[0];
		if( isset( $t_result[1] ) ) {
			$t_modifiers = $t_result[1];
		}
	}

	$t_php_event = $t_now . ' ' . $t_level . ' ' . $t_caller . ' ' . $t_msg;

	switch( $t_destination ) {
		case 'none':
			break;
		case 'file':
			if( isset( $t_modifiers ) ) {
				static $s_log_writable = null;

				if( $s_log_writable ) {
					error_log( $t_php_event . PHP_EOL, 3, $t_modifiers );
				} elseif( $s_log_writable === null ) {
					# Try to log the event, suppress errors in case the file is not writable
					$s_log_writable = @error_log( $t_php_event . PHP_EOL, 3, $t_modifiers );

					if( !$s_log_writable ) {
						# Display a one-time warning message and write it to PHP system log as well.
						# Note: to ensure the error is shown regardless of $g_display_error settings,
						# we manually set the message and log it with error_log_delayed(), which will
						# cause it to be displayed at page bottom.
						error_parameters( $t_modifiers );
						$t_message = error_string( ERROR_LOGFILE_NOT_WRITABLE );
						error_log_delayed( $t_message );
						error_log( 'MantisBT - ' . htmlspecialchars_decode( $t_message ) );
					}
				}
			}
			break;
		case 'page':
			global $g_log_events;
			$g_log_events[] = array( time(), $p_level, $t_event, $t_caller);
			break;
		default:
			# use default PHP error log settings
			error_log( $t_php_event . PHP_EOL );
			break;
	}

	# If running from command line, echo log event to stdout
	if( $t_destination != 'none' && php_sapi_name() == 'cli' ) {
		echo $t_php_event . PHP_EOL;
	}
}

/**
 * Print logging api output to bottom of html page
 * @return void
 */
function log_print_to_page() {
	if( !helper_log_to_page() ) {
		return;
	}

	global $g_log_events, $g_log_levels, $g_email_shutdown_processing;

	if( $g_email_shutdown_processing ) {
		email_send_all();
	}

	$t_total_query_execution_time = 0;
	$t_unique_queries = array();
	$t_total_queries_count = 0;

	$t_icon = icon_get( 'fa-flag-o', 'ace-icon' );
	$t_section_title = lang_get( 'debug_log' );
	echo <<<HTML

		<!-- Mantis Debug Log Output -->
		<div class="space-10"></div>
		<div class="row">
			<div class="col-xs-12">
				<div class="widget-box widget-color-red">
					<div class="widget-header widget-header-small">
						<h4 class="widget-title lighter">
							$t_icon 
							$t_section_title
						</h4>
					</div>
					<div class="widget-body">

		HTML;

	if( $g_log_events ) {
		# Table header
		$t_columns = [
			'log_page_number',
			'log_page_time',
			'log_page_caller',
			'log_page_event',
		];
		echo <<<HTML
						<div class="widget-main no-padding">
							<div class="table-responsive">
								<table id="log-event-list" class="table table-bordered table-condensed table-striped">
									<thead>
										<tr class="small-caption">

		HTML;
		foreach( $t_columns as $t_column ) {
			echo '<th>' . lang_get( $t_column ) . '</th>' . PHP_EOL;
		}
		echo <<<HTML
						</tr>
					</thead>
					<tbody>

		HTML;

		$t_print_row_format = '<tr class="%s">' . PHP_EOL
			. str_repeat( "\t<td>%s</td>\n", 4 )
			. '</tr>' . PHP_EOL;
		$t_count = [];
		foreach( array_keys( $g_log_levels ) as $t_level ) {
			$t_count[$t_level] = 0;
		}
		foreach( $g_log_events as $t_log_event ) {
			$t_level = $t_log_event[1];
			$t_count[$t_level]++;

			$t_class = 'small';
			if( $t_level == LOG_DATABASE ) {
				# Compute Statistics for DB queries
				if( !in_array( $t_log_event[2][0], $t_unique_queries ) ) {
					$t_unique_queries[] = $t_log_event[2][0];
				} else {
					$t_class .= ' duplicate-query';
				}
				$t_total_query_execution_time += $t_log_event[2][1];
				$t_total_queries_count++;
			}

			# Print row
			printf( $t_print_row_format,
				$t_class,
				$g_log_levels[$t_level] . '-' . $t_count[$t_log_event[1]],
				$t_log_event[2][1] ?: '',
				string_html_specialchars( $t_log_event[3] ),
				string_html_specialchars( $t_log_event[2][0] )
			);
		} # foreach $g_log_events

		# Print statistics
		$t_statistics = [
			'total_queries_executed' => $t_total_queries_count,
			'unique_queries_executed' => count( $t_unique_queries ),
			'total_query_execution_time' => $t_total_query_execution_time,
		];
		foreach( $t_statistics as $t_stat => $t_value ) {
			if( $t_value ) {
				printf($t_print_row_format,
				'small',
					$g_log_levels[LOG_DATABASE],
					'',
					'',
					sprintf( lang_get( $t_stat ), $t_value )
				);
			}
		}

		echo <<<HTML
					</tbody>
				</table>
			</div>
		</div>

		HTML;
	}

	echo <<<HTML
				</div>
			</div>
		</div>
	</div>
	<div class="space-10"></div>
	<!--END Mantis Debug Log Output-->


	HTML;
}

/**
 * Builds a string with information from the call backtrace of current logging action
 * The output format is, where available:
 *    {plugin} {file}:{line} {class}[::|->]{function}
 *
 * Some of the actual backtrace is removed to get more informative line to the user.
 * The log type is used to selectively remove some internal call traces.
 *
 * @param integer $p_level	Log level type constant
 * @return string	Output string with caller information
 */
function log_get_caller( $p_level = null ) {
	$t_full_backtrace = debug_backtrace();
	$t_backtrace = $t_full_backtrace;
	$t_root_path = dirname( __DIR__ ) . DIRECTORY_SEPARATOR;

	# Remove top trace, as it's this function
	unset( $t_backtrace[0] );

	# Remove trace step from the include in plugin.php
	# if any log is triggered from the body of a plugin page, we don't want
	# to show the caller function "include()" from plugin.php
	$t_last = end( $t_backtrace );
	$t_last_key = key( $t_backtrace );
	if( isset( $t_last['function'] )
		&& $t_last['function'] == 'include'
		&& $t_last['file'] == $t_root_path . 'plugin.php'
		) {
		unset( $t_backtrace[$t_last_key] );
		unset( $t_full_backtrace[$t_last_key] );
	}

	# Iterate over the backtrace and clean up some steps to show a cleaner info
	foreach( $t_backtrace as $t_index => $t_step ) {

		# Remove special cases of steps for each log type
		switch( $p_level ) {
			# For plugin logs, we want to hide the plugin api calls
			case LOG_PLUGIN:
				if( isset( $t_step['function'] ) ) {
					# Remove trace step executed for plugin_log_event()
					if( $t_step['function'] == 'plugin_log_event'
						&& $t_full_backtrace[$t_index-1]['file'] == $t_root_path . 'core/plugin_api.php'
						) {
						unset( $t_backtrace[$t_index-1] );
						continue 2; # next foreach
					}
				}
				break;

			# For database logs, we want to hide all the intermediate db api calls
			case LOG_DATABASE:
				# Remove trace steps that are executed inside DbQuery class, or inherited classes
				if( isset( $t_step['class'] ) && (
					$t_step['class'] == 'DbQuery'
					|| is_subclass_of( $t_step['class'], 'DbQuery', true )
					) ) {
					unset( $t_backtrace[$t_index-1] );
					continue 2; # next foreach
				}
				if( isset( $t_step['function'] ) ) {
					# Remove trace step executed for db_query() or db_query_bound()
					if( ( $t_step['function'] == 'db_query' || $t_step['function'] == 'db_query_bound' )
						&& $t_full_backtrace[$t_index-1]['file'] == $t_root_path . 'core/database_api.php'
						) {
						unset( $t_backtrace[$t_index-1] );
						continue 2; # next foreach
					}
				}
				break;
		}

		# shortcut break the loop as soon as there is a step that has not been deleted.
		reset( $t_backtrace );
		$t_first_key = key( $t_backtrace );
		# note, we are deleting steps at <current index> - 1
		# this section is only reached when no deletion has been performed
		if( $t_index > $t_first_key ) {
			break;
		}
	}

	# At this point, first step in the cleaned backtrace is the one we want to show
	$t_step = reset( $t_backtrace);
	$t_step_key = key( $t_backtrace );
	$t_caller_file = basename( $t_step['file'] );
	$t_caller_line = $t_step['line'];
	$t_caller_function = '';
	$t_caller_class = '';
	$t_caller_plugin = ( LOG_PLUGIN == $p_level ) ? plugin_get_current() . ' ' : '';
	# Get the function that called this, from the next backtrace step, if it exists
	if( isset( $t_full_backtrace[$t_step_key+1] ) ) {
		$t_caller_function = $t_full_backtrace[$t_step_key+1]['function'] . '()';
		if( isset( $t_full_backtrace[$t_step_key+1]['class'] ) ) {
			$t_caller_class = $t_full_backtrace[$t_step_key+1]['class'];
			$t_caller_class .= $t_full_backtrace[$t_step_key+1]['type'];
		}
	}

	$t_caller = $t_caller_plugin . $t_caller_file . ':' . $t_caller_line . ' '
			. $t_caller_class . $t_caller_function;
	return $t_caller;
}
