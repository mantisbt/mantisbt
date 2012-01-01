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
 * API for simplifying remote URL operations
 *
 * @package CoreAPI
 * @subpackage URLAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Retrieve the contents of a remote URL.
 * First tries using built-in PHP modules, then
 * attempts system calls as last resort.
 * @param string URL
 * @return string URL contents
 */
function url_get( $p_url ) {

	# Generic PHP call
	if( ini_get_bool( 'allow_url_fopen' ) ) {
		return @file_get_contents( $p_url );
	}

	# Use the PHP cURL extension
	if( function_exists( 'curl_init' ) ) {
		$t_curl = curl_init( $p_url );
		curl_setopt( $t_curl, CURLOPT_RETURNTRANSFER, true );

		$t_data = curl_exec( $t_curl );
		curl_close( $t_curl );

		return $t_data;
	}

	# Last resort system call
	$t_url = escapeshellarg( $p_url );
	return shell_exec( 'curl ' . $t_url );
}
