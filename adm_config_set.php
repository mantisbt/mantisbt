<?php
# MantisBT - a php based bugtracking system

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
	 * This page stores the reported bug
	 *
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	form_security_validate( 'adm_config_set' );

	$f_user_id       = gpc_get_int( 'user_id' );
	$f_project_id    = gpc_get_int( 'project_id' );
	$f_config_option = gpc_get_string( 'config_option' );
	$f_type          = gpc_get_string( 'type' );
	$f_value         = gpc_get_string( 'value' );

	if ( is_blank( $f_config_option ) ) {
		error_parameters( 'config_option' );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if ( $f_project_id == ALL_PROJECTS ) {
		access_ensure_global_level( config_get('set_configuration_threshold' ) );
	} else {
		project_ensure_exists( $f_project_id );
		access_ensure_project_level( config_get('set_configuration_threshold' ), $f_project_id );
	}

	# make sure that configuration option specified is a valid one.
	$t_not_found_value = '***CONFIG OPTION NOT FOUND***';
	if ( config_get_global( $f_config_option, $t_not_found_value ) === $t_not_found_value ) {
		error_parameters( $f_config_option );
		trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, ERROR );
	}

	# make sure that configuration option specified can be stored in the database
	if ( !config_can_set_in_database( $f_config_option ) ) {
		error_parameters( $f_config_option );
		trigger_error( ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB, ERROR );
	}

	# For 'default', behavior is based on the global variable's type
	if( $f_type == CONFIG_TYPE_DEFAULT ) {
		$t_config_global_value = config_get_global( $f_config_option );
		if( is_string( $t_config_global_value ) ) {
			$t_type = CONFIG_TYPE_STRING;
		} else if( is_int( $t_config_global_value ) ) {
			$t_type = CONFIG_TYPE_INT;
		} else if( is_float( $t_config_global_value ) ) {
			$t_type = CONFIG_TYPE_FLOAT;
		} else {
			# note that we consider bool and float as complex.
			# We use ON/OFF for bools which map to numeric.
			$t_type = CONFIG_TYPE_COMPLEX;
		}
	} else {
		$t_type = $f_type;
	}

	switch( $t_type ) {
		case CONFIG_TYPE_STRING:
			$t_value = $f_value;
			break;
		case CONFIG_TYPE_INT:
			$t_value = (integer) constant_replace( trim( $f_value ) );
			break;
		case CONFIG_TYPE_FLOAT:
			$t_value = (float) constant_replace( trim( $f_value ) );
			break;
		case CONFIG_TYPE_COMPLEX:
		default:
			$t_value = process_complex_value( $f_value );
			break;
	}

	config_set( $f_config_option, $t_value, $f_user_id, $f_project_id );

	form_security_purge( 'adm_config_set' );

	print_successful_redirect( 'adm_config_report.php' );


	/**
	 * Helper function to recursively process complex types
	 * We support the following kind of variables here:
	 * 1. constant values (like the ON/OFF switches): they are defined as constants mapping to numeric values
	 * 2. simple arrays with the form: array( a, b, c, d )
	 * 3. associative arrays with the form: array( a=>1, b=>2, c=>3, d=>4 )
	 * 4. multi-dimensional arrays
	 * commas and '=>' within strings are handled
	 *
	 * @param string $p_value Complex value to process
	 * @return parsed variable
	 */
	function process_complex_value( $p_value, $p_trimquotes = false ) {
		static $s_regex_array = null;
		static $s_regex_string = null;
		static $s_regex_element = null;

		$t_value = trim( $p_value );

		# Parsing regex initialization
		if( is_null( $s_regex_array ) ) {
			$s_regex_array = '^array[\s]*\((.*)\)$';
			$s_regex_string =
				# unquoted string (word)
				'[\w]+' . '|' .
				# single-quoted string
				"'(?:[^'\\\\]|\\\\.)*'" . '|' .
				# double-quoted string
				'"(?:[^"\\\\]|\\\\.)*"';
			# The following complex regex will parse individual array elements,
			# taking into consideration sub-arrays, associative arrays and single,
			# double and un-quoted strings
			# @TODO dregad reverse pattern logic for sub-array to avoid match on array(xxx)=>array(xxx)
			$s_regex_element = '('
				# Main sub-pattern - match one of
				. '(' .
						# sub-array: ungreedy, no-case match ignoring nested parenthesis
						'(?:(?iU:array\s*(?:\\((?:(?>[^()]+)|(?1))*\\))))' . '|' .
						$s_regex_string
				. ')'
				# Optional pattern for associative array, back-referencing the
				# above main pattern
				. '(?:\s*=>\s*(?2))?' .
				')';
		}

		if( preg_match( "/$s_regex_array/s", $t_value, $t_match ) === 1 ) {
			# It's an array - process each element
			$t_processed = array();

			if( preg_match_all( "/$s_regex_element/", $t_match[1], $t_elements ) ) {
				foreach( $t_elements[0] as $key => $element ) {
					if( !trim( $element ) ) {
						# Empty element - skip it
						continue;
					}
					# Check if element is associative array
					preg_match_all( "/($s_regex_string)\s*=>\s*(.*)/", $element, $t_split );
					if( !empty( $t_split[0] ) ) {
						# associative array
						$t_new_key = constant_replace( trim( $t_split[1][0], " \t\n\r\0\x0B\"'" ) );
						$t_new_value = process_complex_value( $t_split[2][0], true );
						$t_processed[$t_new_key] = $t_new_value;
					} else {
						# regular array
						$t_new_value = process_complex_value( $element );
						$t_processed[$key] = $t_new_value;
					}
				}
			}
			return $t_processed;
		} else {
			# Scalar value
			if( $p_trimquotes ) {
				$t_value = trim( $t_value, " \t\n\r\0\x0B\"'" );
			}
			return constant_replace( $t_value );
		}
	}

	/**
	 * Split by commas, but ignore commas that are within quotes or parenthesis.
	 * Ignoring commas within parenthesis helps allow for multi-dimensional arrays.
	 * @param $p_string string to split
	 * @return array
	 */
	function special_split ( $p_string ) {
		$t_values = array();
		$t_array_element = "";
		$t_paren_level = 0;
		$t_inside_quote = False;
		$t_escape_next = False;

		foreach( str_split( trim( $p_string ) ) as $character ) {
			if( $t_escape_next ) {
				$t_array_element .= $character;
				$t_escape_next = False;
			} else if( $character == "," && $t_paren_level==0 && !$t_inside_quote ) {
				array_push( $t_values, $t_array_element );
				$t_array_element = "";
			} else {
				if( $character == "(" && !$t_inside_quote ) {
					$t_paren_level ++;
				} else if( $character == ")" && !$t_inside_quote ) {
					$t_paren_level --;
				} else if( $character == "'" ) {
					$t_inside_quote = !$t_inside_quote;
				} else if( $character == "\\" ) {
					# escape character
					$t_escape_next = true;
					# keep the escape if the string will be going through another recursion
					if( $t_paren_level > 0 ) {
						$t_array_element .= $character;
					}
					continue;
				}
				$t_array_element .= $character;
			}
		}
		array_push( $t_values, $t_array_element );
		return $t_values;
	}


	/**
	 * Check if the passed string is a constant and returns its value
	 * if yes, or the string itself if not
	 * @param $p_name string to check
	 * @return mixed|string value of constant $p_name, or $p_name itself
	 */
	function constant_replace( $p_name ) {
		if( is_string( $p_name ) && defined( $p_name ) ) {
			# we have a constant
			return constant( $p_name );
		}
		return $p_name;
	}
