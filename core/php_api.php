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
 * PHP Compatibility API
 *
 * Provides functions to assist with backwards compatibility between PHP
 * versions.
 *
 * @package CoreAPI
 * @subpackage PHPCompatibilityAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Constant for our minimum required PHP version
 */
define( 'PHP_MIN_VERSION', '5.3.2' );

/**
 * Determine if PHP is running in CLI or CGI mode and return the mode.
 * @return int PHP mode
 */
function php_mode() {
	static $s_mode = null;

	if( is_null( $s_mode ) ) {
		# Check to see if this is CLI mode or CGI mode
		if( isset( $_SERVER['SERVER_ADDR'] )
			|| isset( $_SERVER['LOCAL_ADDR'] )
			|| isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$s_mode = PHP_CGI;
		} else {
			$s_mode = PHP_CLI;
		}
	}

	return $s_mode;
}

/**
 * Returns true if the current PHP version is higher than the one
 * specified in the given string
 * @param string $p_version_string Version string to compare.
 * @return boolean
 */
function php_version_at_least( $p_version_string ) {
	static $s_cached_version;

	if( isset( $s_cached_version[$p_version_string] ) ) {
		return $s_cached_version[$p_version_string];
	}

	$t_curver = array_pad( explode( '.', phpversion() ), 3, 0 );
	$t_minver = array_pad( explode( '.', $p_version_string ), 3, 0 );

	for( $i = 0;$i < 3;$i = $i + 1 ) {
		$t_cur = (int)$t_curver[$i];
		$t_min = (int)$t_minver[$i];

		if( $t_cur < $t_min ) {
			$s_cached_version[$p_version_string] = false;
			return false;
		} else if( $t_cur > $t_min ) {
			$s_cached_version[$p_version_string] = true;
			return true;
		}
	}

	# if we get here, the versions must match exactly so:
	$s_cached_version[$p_version_string] = true;
	return true;
}

/**
 * Define a multibyte/UTF-8 aware string padding function based on PHP's
 * str_pad function.
 * @param string  $p_input      Input string.
 * @param integer $p_pad_length Padding Length - Refers to the number of grapheme's in the string.
 * @param string  $p_pad_string Padding String.
 * @param integer $p_pad_type   Padding Type.
 * @return string
 */
function mb_str_pad($p_input, $p_pad_length, $p_pad_string = ' ', $p_pad_type = STR_PAD_RIGHT) {
	$t_input_length = mb_strlen( $p_input );
	if( $p_pad_length <= $t_input_length ) {
		return $p_input;
	}
	$t_pad_characters_required = $p_pad_length - $t_input_length;
	$t_pad_string_length = mb_strlen( $p_pad_string );
	$t_padded_string = $p_input;
	switch( $p_pad_type ) {
		case STR_PAD_RIGHT:
			$t_repetitions = ceil( $p_pad_length / $t_pad_string_length );
			$t_padded_string = mb_substr( $p_input . str_repeat( $p_pad_string, $t_repetitions ), 0, $p_pad_length );
			break;
		case STR_PAD_LEFT:
			$t_repetitions = ceil( $p_pad_length / $t_pad_string_length );
			$t_padded_string = mb_substr( str_repeat( $p_pad_string, $t_repetitions ), 0, $p_pad_length ) . $p_input;
			break;
		case STR_PAD_BOTH:
			$t_pad_amount_left = floor( $p_pad_length / 2 );
			$t_pad_amount_right = ceil( $p_pad_length / 2 );
			$t_repetitions_left = ceil( $t_pad_amount_left / $t_pad_string_length );
			$t_repetitions_right = ceil( $t_pad_amount_right / $t_pad_string_length );
			$t_padding_left = mb_substr( str_repeat( $p_pad_string, $t_repetitions_left ), 0, $t_pad_amount_left );
			$t_padding_right = mb_substr( str_repeat( $p_pad_string, $t_repetitions_right ), 0, $t_pad_amount_right );
			$t_padded_string = $t_padding_left . $p_input . $t_padding_right;
			break;
	}
	return $t_padded_string;
}

/**
 * function alias for utf8_strtoupper() for legacy support of plugins
 * @deprecated mb_strtoupper should be used in preference to this function.
 * @return string
 */
function utf8_strtoupper() {
  return call_user_func_array( 'mb_strtoupper', func_get_args() );
}

/**
 * function alias for utf8_strtolower() for legacy support of plugins
 * @deprecated mb_strtolower should be used in preference to this function.
 * @return string
 */
function utf8_strtolower() {
  return call_user_func_array( 'mb_strtolower', func_get_args() );
}

/**
 * function alias for utf8_strlen() for legacy support of plugins
 * @deprecated mb_strlen should be used in preference to this function.
 * @return integer
 */
function utf8_strlen() {
  return call_user_func_array( 'mb_strlen', func_get_args() );
}

/**
 * function alias for utf8_substr() for legacy support of plugins
 * @deprecated mb_substr should be used in preference to this function.
 * @return string
 */
function utf8_substr() {
  return call_user_func_array( 'mb_substr', func_get_args() );
}