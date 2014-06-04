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

# cache array of comparisons
$g_cached_version = array();

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
 * @param string $p_version_string version string to compare
 * @return bool
 */
function php_version_at_least( $p_version_string ) {
	global $g_cached_version;

	if( isset( $g_cached_version[$p_version_string] ) ) {
		return $g_cached_version[$p_version_string];
	}

	$t_curver = array_pad( explode( '.', phpversion() ), 3, 0 );
	$t_minver = array_pad( explode( '.', $p_version_string ), 3, 0 );

	for( $i = 0;$i < 3;$i = $i + 1 ) {
		$t_cur = (int) $t_curver[$i];
		$t_min = (int) $t_minver[$i];

		if( $t_cur < $t_min ) {
			$g_cached_version[$p_version_string] = false;
			return false;
		}
		else if( $t_cur > $t_min ) {
			$g_cached_version[$p_version_string] = true;
			return true;
		}
	}

	# if we get here, the versions must match exactly so:
	$g_cached_version[$p_version_string] = true;
	return true;
}

# If mb_* not defined, define it to map to standard methods.
if( !function_exists( 'mb_substr' ) ) {
	/**
	 * Map mb_substr to utf8_substr if mb extension is not found
	 * @param string $p_text text string
	 * @param int $p_index start position
	 * @param int $p_size size
	 * @return string
	 */
	function mb_substr( $p_text, $p_index, $p_size ) {
		return utf8_substr( $p_text, $p_index, $p_size );
	}
}
