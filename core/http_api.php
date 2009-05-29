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
 * API for managing HTTP response headers and transactions.
 * @package CoreAPI
 * @subpackage HTTPAPI
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Set caching headers that will allow or prevent browser caching.
 * @param boolean Allow caching
 */
function http_caching_headers( $p_allow_caching=false ) {
	global $g_allow_browser_cache;

	// Basic browser detection
	$t_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'none';

	$t_browser_name = 'Normal';
	if ( strpos( $t_user_agent, 'MSIE' ) ) {
		$t_browser_name = 'IE';
	}

	// Headers to prevent caching
	//  with option to bypass if running from script
	if ( !headers_sent() ) {
		if ( $p_allow_caching || ( isset( $g_allow_browser_cache ) && ON == $g_allow_browser_cache ) ) {
			switch ( $t_browser_name ) {
			case 'IE':
				header( 'Cache-Control: private, proxy-revalidate' );
				break;
			default:
				header( 'Cache-Control: private, must-revalidate' );
				break;
			}
		} else {
			header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		}

		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
	}
}

/**
 * Set content-type headers.
 */
function http_content_headers() {
	if ( !headers_sent() ) {
		header( 'Content-type: text/html;charset=utf-8' );
	}
}

/**
 * Load and set any custom headers defined by the site configuration.
 */
function http_custom_headers() {
	if ( !headers_sent() ) {
		// send user-defined headers
		foreach( config_get_global( 'custom_headers' ) as $t_header ) {
			header( $t_header );
		}
	}
}

/**
 * Set all headers used by a normal page load.
 */
function http_all_headers() {
	global $g_bypass_headers;

	if ( !$g_bypass_headers && !headers_sent() ) {
		http_content_headers();
		http_caching_headers();
		http_custom_headers();
	}
}
