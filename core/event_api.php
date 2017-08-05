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
 * Event API
 *
 * @package CoreAPI
 * @subpackage EventAPI
 * @author John Reese
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses constant_inc.php
 * @uses error_api.php
 * @uses events_inc.php
 * @uses plugin_api.php
 */

require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'events_inc.php' );
require_api( 'plugin_api.php' );

# @global array $g_event_cache
$g_event_cache = array();

/**
 * Declare an event of a given type.
 * Will do nothing if event already exists.
 * @param string  $p_name Event name.
 * @param integer $p_type Event type.
 * @access public
 * @return void
 */
function event_declare( $p_name, $p_type = EVENT_TYPE_DEFAULT ) {
	global $g_event_cache;

	if( !isset( $g_event_cache[$p_name] ) ) {
		$g_event_cache[$p_name] = array(
			'type' => $p_type,
			'callbacks' => array(),
		);
	}
}

/**
 * Convenience function for declare multiple events.
 * @param array $p_events Events.
 * @access public
 * @return void
 */
function event_declare_many( array $p_events ) {
	foreach( $p_events as $t_name => $t_type ) {
		event_declare( $t_name, $t_type );
	}
}

/**
 * Hook a callback function to a given event.
 * A plugin's basename must be specified for proper handling of plugin callbacks.
 * @param string         $p_name     Event name.
 * @param string         $p_callback Callback function.
 * @param integer|string $p_plugin   Plugin basename.
 * @access public
 * @return null
 */
function event_hook( $p_name, $p_callback, $p_plugin = 0 ) {
	global $g_event_cache;

	if( !isset( $g_event_cache[$p_name] ) ) {
		error_parameters( $p_name );
		trigger_error( ERROR_EVENT_UNDECLARED, WARNING );
		return null;
	}

	$g_event_cache[$p_name]['callbacks'][$p_plugin][] = $p_callback;
}

/**
 * Hook multiple callback functions to multiple events.
 * @param array          $p_hooks  Event name/callback pairs.
 * @param integer|string $p_plugin Plugin basename.
 * @access public
 * @return void
 */
function event_hook_many( array $p_hooks, $p_plugin = 0 ) {
	foreach( $p_hooks as $t_name => $t_callbacks ) {
		if( !is_array( $t_callbacks ) ) {
			event_hook( $t_name, $t_callbacks, $p_plugin );
			continue;
		}

		foreach( $t_callbacks as $t_callback ) {
			event_hook( $t_name, $t_callback, $p_plugin );
		}
	}
}

/**
 * In the case of errors that halt execution, it is useful to
 * clear the list of event callbacks so that no other callbacks
 * are executed while the error message is being displayed.
 * @return void
 */
function event_clear_callbacks() {
	global $g_event_cache;

	foreach( $g_event_cache as $t_name => $t_event_info ) {
		$g_event_cache[$t_name]['callbacks'] = array();
	}
}

/**
 * Signal an event to execute and handle callbacks as necessary.
 * @param string  $p_name           Event name.
 * @param mixed   $p_params         Event parameters.
 * @param mixed   $p_params_dynamic Event parameters Dynamic.
 * @param integer $p_type           Event type override.
 * @return mixed Null if event undeclared, appropriate return value otherwise
 * @access public
 */
function event_signal( $p_name, $p_params = null, $p_params_dynamic = null, $p_type = null ) {
	global $g_event_cache;

	if( !isset( $g_event_cache[$p_name] ) ) {
		error_parameters( $p_name );
		trigger_error( ERROR_EVENT_UNDECLARED, WARNING );
		return null;
	}

	if( is_null( $p_type ) ) {
		$t_type = $g_event_cache[$p_name]['type'];
	} else {
		$t_type = $p_type;
	}
	$t_callbacks = $g_event_cache[$p_name]['callbacks'];

	switch( $t_type ) {
		case EVENT_TYPE_EXECUTE:
			event_type_execute( $p_name, $t_callbacks, $p_params );
			return null;
		case EVENT_TYPE_OUTPUT:
			event_type_output( $p_name, $t_callbacks, $p_params );
			return null;
		case EVENT_TYPE_CHAIN:
			if( !is_array( $p_params_dynamic ) ) {
				$p_params_dynamic = array(
					$p_params_dynamic,
				);
			}
			return event_type_chain( $p_name, $t_callbacks, $p_params, $p_params_dynamic );
		case EVENT_TYPE_FIRST:
			return event_type_first( $p_name, $t_callbacks, $p_params );
		default:
			return event_type_default( $p_name, $t_callbacks, $p_params );
	}
}

/**
 * Executes a plugin's callback function for a given event.
 * @param string $p_event    Event name.
 * @param string $p_callback Callback name.
 * @param string $p_plugin   Plugin basename.
 * @param mixed  $p_params   Parameters for event callback.
 * @return mixed null if callback not found, value from callback otherwise
 * @access public
 */
function event_callback( $p_event, $p_callback, $p_plugin, $p_params = null ) {
	$t_value = null;
	if( !is_array( $p_params ) ) {
		$p_params = array(
			$p_params,
		);
	}

	if( $p_plugin !== 0 ) {
		global $g_plugin_cache;

		plugin_push_current( $p_plugin );

		if( method_exists( $g_plugin_cache[$p_plugin], $p_callback ) ) {
			$t_value = call_user_func_array( array( $g_plugin_cache[$p_plugin], $p_callback ), array_merge( array( $p_event ), $p_params ) );
		}

		plugin_pop_current();
	} else {
		if( function_exists( $p_callback ) ) {
			$t_value = call_user_func_array( $p_callback, array_merge( array( $p_event ), $p_params ) );
		}
	}

	return $t_value;
}

/**
 * Process an execute event type.
 * All callbacks will be called with parameters, and their
 * return values will be ignored.
 * @param string $p_event     Event name.
 * @param array  $p_callbacks Array of callback function/plugin base name key/value pairs.
 * @param array  $p_params    Callback parameters.
 * @return void
 * @access public
 */
function event_type_execute( $p_event, array $p_callbacks, $p_params = null ) {
	foreach( $p_callbacks as $t_plugin => $t_callbacks ) {
		foreach( $t_callbacks as $t_callback ) {
			event_callback( $p_event, $t_callback, $t_plugin, $p_params );
		}
	}
}

/**
 * Process an output event type.
 * All callbacks will be called with the given parameters, and their
 * return values will be echoed to the client, separated by a given string.
 * If there are no callbacks, then nothing will be sent as output.
 * @param string $p_event     Event name.
 * @param array  $p_callbacks Array of callback function/plugin base name key/value pairs.
 * @param mixed  $p_params    Parameters to event callback (array, or single object)
 * @param mixed  $p_format    Output separator (if single string) or indexed array of pre, mid, and post strings.
 * @access public
 * @return void
 */
function event_type_output( $p_event, array $p_callbacks, $p_params = null, $p_format = null ) {
	$t_prefix = '';
	$t_separator = '';
	$t_postfix = '';

	if( is_array( $p_format ) ) {
		switch( count( $p_format ) ) {
			case 3:
				$t_postfix = $p_format[2];
			case 2:
				$t_separator = $p_format[1];
			case 1:
				$t_prefix = $p_format[0];
		}
	} else {
		$t_separator = $p_format;
	}

	$t_output = array();
	foreach( $p_callbacks as $t_plugin => $t_callbacks ) {
		foreach( $t_callbacks as $t_callback ) {
			$t_output[] = event_callback( $p_event, $t_callback, $t_plugin, $p_params );
		}
	}
	if( count( $p_callbacks ) > 0 ) {
		echo $t_prefix, implode( $t_separator, $t_output ), $t_postfix;
	}
}

/**
 * Process a chained event type.
 * The first callback with be called with the given input.  All following
 * callbacks will be called with the previous's output as its input.  The
 * final callback's return value will be returned to the event origin.
 * @param string $p_event     Event name.
 * @param array  $p_callbacks Array of callback function/plugin basename key/value pairs.
 * @param mixed  $p_input     Input data.
 * @param array  $p_params    Parameters.
 * @return mixed Output data
 * @access public
 */
function event_type_chain( $p_event, array $p_callbacks, $p_input, $p_params = null ) {
	$t_output = $p_input;

	foreach( $p_callbacks as $t_plugin => $t_callbacks ) {
		foreach( $t_callbacks as $t_callback ) {
			if( !is_array( $t_output ) ) {
				$t_output = array(
					$t_output,
				);
			}

			$t_params = array_merge( $t_output, $p_params );
			$t_output = event_callback( $p_event, $t_callback, $t_plugin, $t_params );
		}
	}
	return $t_output;
}

/**
 * Process a first-return event.
 * Callbacks will be called with the given parameters until a callback
 * returns a non-null value; at this point, no other callbacks will be
 * processed, and the return value be passed back to the event origin.
 * @param string $p_event     Event name.
 * @param array  $p_callbacks Array of callback function/plugin basename key/value pairs.
 * @param mixed  $p_params    Parameters passed to callbacks.
 * @return mixed|null The first non-null callback result, or null otherwise
 * @access public
 */
function event_type_first( $p_event, array $p_callbacks, $p_params ) {
	$t_output = null;

	foreach( $p_callbacks as $t_plugin => $t_callbacks ) {
		foreach( $t_callbacks as $t_callback ) {
			$t_output = event_callback( $p_event, $t_callback, $t_plugin, $p_params );

			if( !is_null( $t_output ) ) {
				return $t_output;
			}
		}
	}

	return null;
}

/**
 * Process a default event type.
 * All callbacks will be called with the given data parameters.  The
 * return value of each callback will be appended to an array with the callback's
 * basename as the key.  This array will then be returned to the event origin.
 * @param string $p_event     Event name.
 * @param array  $p_callbacks Array of callback function/plugin basename key/value pairs.
 * @param mixed  $p_data      Data.
 * @return array Array of callback/return key/value pairs
 * @access public
 */
function event_type_default( $p_event, array $p_callbacks, $p_data ) {
	$t_output = array();
	foreach( $p_callbacks as $t_plugin => $t_callbacks ) {
		foreach( $t_callbacks as $t_callback ) {
			$t_output[$t_plugin][$t_callback] = event_callback( $p_event, $t_callback, $t_plugin, $p_data );
		}
	}
	return $t_output;
}
