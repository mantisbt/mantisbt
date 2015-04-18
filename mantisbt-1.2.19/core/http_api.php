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
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Checks to see if script was queried through the HTTPS protocol
 * @return boolean True if protocol is HTTPS
 */
function http_is_protocol_https() {
	return !empty( $_SERVER['HTTPS'] ) && ( utf8_strtolower( $_SERVER['HTTPS'] ) != 'off' );
}

/**
 * Check to see if the client is using Microsoft Internet Explorer so we can
 * enable quirks and hacky non-standards-compliant workarounds.
 * @return boolean True if Internet Explorer is detected as the user agent
 */
function is_browser_internet_explorer() {
	$t_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'none';

	if ( strpos( $t_user_agent, 'MSIE' ) ) {
		return true;
	}

	return false;
}

/**
 * Checks to see if the client is using Google Chrome so we can enable quirks
 * and hacky non-standards-compliant workarounds.
 * @return boolean True if Chrome is detected as the user agent
 */
function is_browser_chrome() {
	$t_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'none';

	if ( strpos( $t_user_agent, 'Chrome/' ) ) {
		return true;
	}

	return false;
}

/**
 * Send a Content-Disposition header. This is more complex than it sounds
 * because only a few browsers properly support RFC2231. For those browsers
 * which are behind the times or are otherwise broken, we need to use
 * some hacky workarounds to get them to work 'nicely' with attachments and
 * inline files. See http://greenbytes.de/tech/tc2231/ for full reasoning.
 * @param string Filename
 * @param boolean Display file inline (optional, default = treat as attachment)
 */
function http_content_disposition_header( $p_filename, $p_inline = false ) {
	if ( !headers_sent() ) {
		$t_encoded_filename = rawurlencode( $p_filename );
		$t_disposition = '';
		if ( !$p_inline ) {
			$t_disposition = 'attachment;';
		}
		if ( is_browser_internet_explorer() || is_browser_chrome() ) {
			// Internet Explorer does not support RFC2231 however it does
			// incorrectly decode URL encoded filenames and we can use this to
			// get UTF8 filenames to work with the file download dialog. Chrome
			// behaves in the same was as Internet Explorer in this respect.
			// See http://greenbytes.de/tech/tc2231/#attwithfnrawpctenclong
			header( 'Content-Disposition:' . $t_disposition . ' filename="' . $t_encoded_filename . '"' );
		} else {
			// For most other browsers, we can use this technique:
			// http://greenbytes.de/tech/tc2231/#attfnboth2
			header( 'Content-Disposition:' . $t_disposition . ' filename*=UTF-8\'\'' . $t_encoded_filename . '; filename="' . $t_encoded_filename . '"' );
		}
	}
}

/**
 * Set caching headers that will allow or prevent browser caching.
 * @param boolean Allow caching
 */
function http_caching_headers( $p_allow_caching=false ) {
	global $g_allow_browser_cache;

	// Headers to prevent caching
	// with option to bypass if running from script
	if ( !headers_sent() ) {
		if ( $p_allow_caching || ( isset( $g_allow_browser_cache ) && ON == $g_allow_browser_cache ) ) {
			if ( is_browser_internet_explorer() ) {
				header( 'Cache-Control: private, proxy-revalidate' );
			} else {
				header( 'Cache-Control: private, must-revalidate' );
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
		header( 'Content-Type: text/html; charset=utf-8' );
		# Don't let Internet Explorer second-guess our content-type, as per
		# http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
		header( 'X-Content-Type-Options: nosniff' );
	}
}

/**
 * Set security headers (frame busting, clickjacking/XSS/CSRF protection).
 */
function http_security_headers() {
	if ( !headers_sent() ) {
		header( 'X-Frame-Options: DENY' );
		$t_avatar_img_allow = '';
		if ( config_get_global( 'show_avatar' ) ) {
			if ( http_is_protocol_https() ) {
				$t_avatar_img_allow = "; img-src 'self' https://secure.gravatar.com:443";
			} else {
				$t_avatar_img_allow = "; img-src 'self' http://www.gravatar.com:80";
			}
		}
		header( "X-Content-Security-Policy: allow 'self'; options inline-script eval-script$t_avatar_img_allow; frame-ancestors 'none'" );
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
		http_security_headers();
		http_custom_headers();
	}
}
