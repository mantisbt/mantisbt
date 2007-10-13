<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: php_api.php,v 1.19.2.1 2007-10-13 22:35:38 giallu Exp $
	# --------------------------------------------------------

	### PHP Compatibility API ###

	# Functions to help in backwards compatibility of PHP versions, etc.

	# Constant for our minimum required PHP version
	define( 'PHP_MIN_VERSION', '4.3.0' );

	# cache array of comparisons
	$g_cached_version = array();

	# --------------------
	# Returns true if the current PHP version is higher than the one
	#  specified in the given string
	function php_version_at_least( $p_version_string ) {
		global $g_cached_version;

		if ( isset( $g_cached_version[$p_version_string] ) ) {
			return $g_cached_version[$p_version_string];
		}

		$t_curver = array_pad( explode( '.', phpversion() ), 3, 0 );
		$t_minver = array_pad( explode( '.', $p_version_string ), 3, 0 );

		for ($i = 0 ; $i < 3 ; $i = $i + 1 ) {
			$t_cur = (int)$t_curver[$i];
			$t_min = (int)$t_minver[$i];

			if ( $t_cur < $t_min ) {
				$g_cached_version[$p_version_string] = false;
				return false;
			} else if ( $t_cur > $t_min ) {
				$g_cached_version[$p_version_string] = true;
				return true;
			}
		}

		# if we get here, the versions must match exactly so:
		$g_cached_version[$p_version_string] = true;
		return true;
	}

	# --------------------
	# Enforce our minimum requirements
	if ( !php_version_at_least( PHP_MIN_VERSION ) ) {
		@ob_end_clean();
		PRINT '<b>FATAL ERROR: Your version of PHP is too old.  Mantis requires PHP version ' . PHP_MIN_VERSION . ' or newer</b><br />Your version of PHP is version ' . phpversion();
		die();
	}

	# --------------------
	ini_set('magic_quotes_runtime', 0);

	# --------------------
	# file_put_contents is normally in PEAR
	if (!function_exists('file_put_contents')) {
		function file_put_contents($filename, $data) {
			if (($h = fopen($filename, 'w')) === false) {
				return false;
			}
			if (($bytes = @fwrite($h, $data)) === false) {
				return false;
			}
			fclose($h);
			return $bytes;
		}
	}

	# --------------------
	# vsprintf is normally in PEAR
	if ( !function_exists( 'vsprintf' ) ) {
		function vsprintf( $format, $args ) {
			array_unshift( $args, $format );
			return call_user_func_array( 'sprintf', $args );
		}
	}

?>
