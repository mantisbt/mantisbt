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
