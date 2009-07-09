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
 * Compression API
 *
 * @package CoreAPI
 * @subpackage CompressionAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Starts the buffering/compression (only if the compression option is ON)
 * This variable is used internally.  It is not used for configuration
 * @global bool $g_compression_started
 */
$g_compression_started = false;

/**
 * Check if compression should be enabled.
 * @return bool
 * @access public
 */
function compress_handler_is_enabled() {
	global $g_compress_html, $g_use_iis;

	if( defined( 'COMPRESSION_DISABLED' ) ) {
		return false;
	}

	// Dont use config_get here so only dependency is on consant.inc.php in this module
	if( ON == $g_compress_html ) {
		if( !extension_loaded( 'zlib' ) ) {
			return false;
		}
	
		if ( ini_get( 'zlib.output_compression' ) ) {
			/* zlib output compression is enabled */
			return false;
		}
		
		if( php_version_at_least( '5.2.10' ) && ini_get('output_handler') == '' ) {
			ini_set('zlib.output_compression', true);
			// do it transparently
			return false;
		}
		
		if ( OFF == $g_use_iis ) {
			// disable compression when using IIS because of issue #2953. for windows compression, use zlib.output_compression in php.ini or a later version of php
			return false;
		}
		return ( 'ob_gzhandler' != ini_get( 'output_handler' ) );
	}
}

/**
 * Start compression handler if required
 * @return null
 * @access public
 */
function compress_start_handler() {
	if( compress_handler_is_enabled() ) {
		# Before doing anything else, start output buffering so we don't prevent
		#  headers from being sent if there's a blank line in an included file
		ob_start( 'compress_handler' );
	}
}

/**
 * Output Buffering handler that either compresses the buffer or just.
 * returns it, depending on the return value of compress_handler_is_enabled()
 * @param string $p_buffer
 * @param int $p_mode
 * @return string
 * @access public
 */
function compress_handler( & $p_buffer, $p_mode ) {
	global $g_compression_started;
	if( $g_compression_started && compress_handler_is_enabled() ) {
		return ob_gzhandler( $p_buffer, $p_mode );
	} else {
		return $p_buffer;
	}
}

/**
 * Enable output buffering with compression.
 * @return null
 * @access public
 */
function compress_enable() {
	global $g_compression_started;

	$g_compression_started = true;
}

/**
 * Disable output buffering with compression.
 * @return null
 * @access public
 */
function compress_disable() {
	global $g_compression_started;

	$g_compression_started = false;
}
