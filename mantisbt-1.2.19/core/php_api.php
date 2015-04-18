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
 * Functions to help in backwards compatibility of PHP versions, etc.
 * @package CoreAPI
 * @subpackage PHPCompatibilityAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Constant for our minimum required PHP version
 */
define( 'PHP_MIN_VERSION', '5.1.0' );

# cache array of comparisons
$g_cached_version = array();

/**
 * Determine if PHP is running in CLI or CGI mode and return the mode.
 * @return int PHP mode
 */
function php_mode() {
	static $s_mode = null;

	if ( is_null( $s_mode ) ) {
		# Check to see if this is CLI mode or CGI mode
		if ( isset( $_SERVER['SERVER_ADDR'] )
			|| isset( $_SERVER['LOCAL_ADDR'] )
			|| isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$s_mode = PHP_CGI;
		} else {
			$s_mode = PHP_CLI;
		}
	}

	return $s_mode;
}

# Returns true if the current PHP version is higher than the one
#  specified in the given string
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

# Enforce our minimum requirements
if( !php_version_at_least( PHP_MIN_VERSION ) ) {
	@ob_end_clean();
	echo '<b>FATAL ERROR: Your version of PHP is too old.  MantisBT requires PHP version ' . PHP_MIN_VERSION . ' or newer</b><br />Your version of PHP is version ' . phpversion();
	die();
}

# Check for function because it is deprecated in PHP 5.3 and removed in PHP 6
if ( function_exists( 'set_magic_quotes_runtime' ) ) {
	@set_magic_quotes_runtime( false );
}

# Added in PHP 5.2.0
if ( !function_exists( 'memory_get_peak_usage') ) {
	function memory_get_peak_usage() {
		return memory_get_usage();
	}
}

# If mb_* not defined, define it to map to standard methods.
if ( !function_exists( 'mb_substr' ) ) {
	function mb_substr( $p_text, $p_index, $p_size ) {
		return utf8_substr( $p_text, $p_index, $p_size );
	}
}
