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
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Retrieve the contents of a remote URL.
 * First tries using built-in PHP modules (OpenSSL and cURL), then attempts
 * system call as last resort.
 * @param string URL
 * @return null|string URL contents (NULL in case of errors)
 */
function url_get( $p_url ) {

	# Generic PHP call
	if( ini_get_bool( 'allow_url_fopen' ) ) {
		$t_data = @file_get_contents( $p_url );

		if( $t_data !== false ) {
			return $t_data;
		}
		# If the call failed (e.g. due to lack of https wrapper)
		# we fall through to attempt retrieving URL with another method
	}

	# Use the PHP cURL extension
	if( function_exists( 'curl_init' ) ) {
		$t_curl = curl_init( $p_url );

		# cURL options
		$t_curl_opt[CURLOPT_RETURNTRANSFER] = true;

		# @todo It may be useful to provide users a way to define additional
		# custom options for curl module, e.g. proxy settings and authentication.
		# This could be stored in a global config option.

		# Default User Agent (Mantis version + php curl extension version)
		$t_vers = curl_version();
		$t_curl_opt[CURLOPT_USERAGENT] =
			'mantisbt/' . MANTIS_VERSION . ' php-curl/' . $t_vers['version'];

		# Set the options
		curl_setopt_array( $t_curl, $t_curl_opt );

		# Retrieve data
		$t_data = curl_exec( $t_curl );
		curl_close( $t_curl );

		if( $t_data !== false ) {
			return $t_data;
		}
	}

	# Last resort system call
	$t_url = escapeshellarg( $p_url );
	return shell_exec( 'curl ' . $t_url );
}
