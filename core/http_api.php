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
 * HTTP API
 *
 * Provides functions to manage HTTP response headers.
 *
 * @package CoreAPI
 * @subpackage HTTPAPI
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 */

require_api( 'config_api.php' );

/**
 * The Content-Security-Policy settings array.  Use http_csp_add() to update it.
 * @var array
 */
$g_csp = array();

/**
 * Check to see if the client is using Microsoft Internet Explorer so we can
 * enable quirks and hacky non-standards-compliant workarounds.
 * @return boolean True if Internet Explorer is detected as the user agent
 */
function is_browser_internet_explorer() {
	$t_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'none';

	if( strpos( $t_user_agent, 'MSIE' ) ) {
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

	if( strpos( $t_user_agent, 'Chrome/' ) ) {
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
 * @param string  $p_filename Filename.
 * @param boolean $p_inline   Display file inline (optional, default = treat as attachment).
 * @return void
 */
function http_content_disposition_header( $p_filename, $p_inline = false ) {
	if( !headers_sent() ) {
		$t_encoded_filename = rawurlencode( $p_filename );
		$t_disposition = '';
		if( !$p_inline ) {
			$t_disposition = 'attachment;';
		}
		if( is_browser_internet_explorer() || is_browser_chrome() ) {
			# Internet Explorer does not support RFC2231 however it does
			# incorrectly decode URL encoded filenames and we can use this to
			# get UTF8 filenames to work with the file download dialog. Chrome
			# behaves in the same was as Internet Explorer in this respect.
			# See http://greenbytes.de/tech/tc2231/#attwithfnrawpctenclong
			header( 'Content-Disposition:' . $t_disposition . ' filename="' . $t_encoded_filename . '"' );
		} else {
			# For most other browsers, we can use this technique:
			# http://greenbytes.de/tech/tc2231/#attfnboth2
			header( 'Content-Disposition:' . $t_disposition . ' filename*=UTF-8\'\'' . $t_encoded_filename . '; filename="' . $t_encoded_filename . '"' );
		}
	}
}

/**
 * Set caching headers that will allow or prevent browser caching.
 * Use an explicit true/false value for the parameter to force the caching behavior.
 * Otherwise, use null or omit the oparameter to use default behavior.
 * The default behavior is to not cache, or the specified in the option $g_allow_browser_cache
 * @param null|boolean $p_allow_caching Force or not caching. Omitting or null, will use default behavior.
 * @return void
 */
function http_caching_headers( $p_allow_caching = null ) {
	global $g_allow_browser_cache;

	if( headers_sent() ) {
		return;
	}

	if( $p_allow_caching === null ) {
		$t_allow_caching = ( isset( $g_allow_browser_cache ) && ON == $g_allow_browser_cache );
	} else {
		$t_allow_caching = $p_allow_caching;
	}

	if( $t_allow_caching ) {
			if( is_browser_internet_explorer() ) {
				header( 'Cache-Control: private, proxy-revalidate' );
			} else {
				header( 'Cache-Control: private, must-revalidate' );
			}
			# set an expire time to +10 days
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time()+864000 ) );
	} else {
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

	}
}

/**
 * Set content-type headers.
 * @return void
 */
function http_content_headers() {
	if( !headers_sent() ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		# Don't let Internet Explorer second-guess our content-type, as per
		# http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
		header( 'X-Content-Type-Options: nosniff' );
	}
}

/**
 * Add a Content-Security-Policy directive.
 *
 * @param  string $p_type  The directive type, e.g. style-src, script-src.
 * @param  string $p_value The directive value, e.g. 'self', https://ajax.googleapis.com
 * @return void
 */
function http_csp_add( $p_type, $p_value ) {
	global $g_csp;

	if ( $g_csp === null ) {
		# Development error, headers already emitted.
		trigger_error( ERROR_GENERIC, ERROR );
	}

	if ( isset( $g_csp[$p_type] ) ) {
		if ( !in_array( $p_value, $g_csp[$p_type] ) ) {
			$g_csp[$p_type][] = $p_value;
		}
	} else {
		$g_csp[$p_type] = array( $p_value );
	}
}

/**
 * Constructs the value of the CSP header.
 * @return string CSP header value.
 */
function http_csp_value() {
	global $g_csp;

	if ( $g_csp === null ) {
		# Development error, headers already emitted.
		trigger_error( ERROR_GENERIC, ERROR );
	}

	$t_csp_value = '';

	foreach ( $g_csp as $t_key => $t_values ) {
		$t_csp_value .= $t_key . ' ' . implode( ' ', $t_values ) . '; ';
	}

	$t_csp_value = trim( $t_csp_value, '; ' );

	return $t_csp_value;
}

/**
 * Send header for Content-Security-Policy.
 * @return void
 */
function http_csp_emit_header() {
	header( 'Content-Security-Policy: ' . http_csp_value() );

	global $g_csp;
	$g_csp = null;
}

/**
 * Set security headers (frame busting, clickjacking/XSS/CSRF protection).
 * @return void
 */
function http_security_headers() {
	if( !headers_sent() ) {
		header( 'X-Frame-Options: DENY' );

		# Define Content Security Policy
		http_csp_add( 'default-src', "'self'" );
		http_csp_add( 'frame-ancestors', "'none'" );
		http_csp_add( 'style-src', "'self'" );
		http_csp_add( 'style-src', "'unsafe-inline'" );
		http_csp_add( 'script-src', "'self'" );
		http_csp_add( 'img-src', "'self'" );
		http_csp_add( 'img-src', "'self' data:" );

		# White list the CDN urls (if enabled)
		if ( config_get_global( 'cdn_enabled' ) == ON ) {
			http_csp_add( 'style-src', 'ajax.googleapis.com' );
			http_csp_add( 'style-src', 'stackpath.bootstrapcdn.com' );
			http_csp_add( 'style-src', 'fonts.googleapis.com' );
			http_csp_add( 'style-src', 'cdnjs.cloudflare.com' );

			http_csp_add( 'font-src', 'fonts.gstatic.com' );
			http_csp_add( 'font-src', 'stackpath.bootstrapcdn.com' );

			http_csp_add( 'script-src', 'ajax.googleapis.com' );
			http_csp_add( 'script-src', 'stackpath.bootstrapcdn.com' );
			http_csp_add( 'script-src', 'cdnjs.cloudflare.com' );

			http_csp_add( 'img-src', 'ajax.googleapis.com' );

		}

		http_csp_emit_header();
	}
}

/**
 * Load and set any custom headers defined by the site configuration.
 * @return void
 */
function http_custom_headers() {
	if( !headers_sent() ) {
		# send user-defined headers
		foreach( config_get_global( 'custom_headers' ) as $t_header ) {
			header( $t_header );
		}
	}
}

/**
 * Set all headers used by a normal page load.
 * @return void
 */
function http_all_headers() {
	global $g_bypass_headers;

	if( !$g_bypass_headers && !headers_sent() ) {
		http_content_headers();
		http_caching_headers();
		http_security_headers();
		http_custom_headers();
	}
}

if( !function_exists( 'http_build_url' ) ) {
/**
 * Builds an URL from its components
 * This is basically the reverse of parse_url(), a minimalistic implementation
 * of pecl_http extension's http_build_url() function.
 * @param array $p_url The URL components (as results from parse_url())
 * @return string
 */
function http_build_url( array $p_url ) {
	return
		  ( isset( $p_url['scheme'] )   ? $p_url['scheme'] . '://' : '' )
		. ( isset( $p_url['user'] )
			? $p_url['user'] . ( isset( $p_url['pass'] ) ? ':' . $p_url['pass'] : '' ) .'@'
			: ''
		  )
		. ( isset( $p_url['host'] )     ? $p_url['host'] : '' )
		. ( isset( $p_url['port'] )     ? ':' . $p_url['port'] : '' )
		. ( isset( $p_url['path'] )     ? $p_url['path'] : '' )
		. ( isset( $p_url['query'] )    ? '?' . $p_url['query'] : '' )
		. ( isset( $p_url['fragment'] ) ? '#' . $p_url['fragment'] : '' )
	;
}
}
