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
 * This API handles the ob_gzhandler in php.
 *
 * @package CoreAPI
 * @subpackage CompressionAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Starts the buffering/compression (only if the compression option is ON)
 * This variable is used internally.  It is not used for configuration
 * @global bool $g_compression_started
 */
$g_compression_started = false;

/**
 * Check if compression handler (ob_gzhandler) should be enabled. Note: this should not be used
 * as an indicator of whether output received by a client will be compressed, only whether an
 * output handler is used to compress output.
 * @return bool
 * @access public
 */
function compress_handler_is_enabled() {
	global $g_compress_html, $g_use_iis;

	// indicates compression should be disabled for a page. Note: php.ini may still enable zlib.output_compression.
	// it may be possible to turn this off through the use of ini_set within that specific page.
	if ( defined( 'COMPRESSION_DISABLED' ) ) {
		return false;
	}

	// Dont use config_get here so only dependency is on consant.inc.php in this module
	// We only actively compress html if global configuration compress_html is set.
	if ( ON == $g_compress_html ) {
		// both compression handlers require zlib module to be loaded
		if( !extension_loaded( 'zlib' ) ) {
			return false;
		}
	
		if ( ini_get( 'zlib.output_compression' ) ) {
			/* zlib output compression is already enabled - we can't load the gzip output handler */
			return false;
		}
		
		// Since php 5.2.10, it's possible to set zlib.output_compression via ini_set.
		// This method is preferred over ob_gzhandler
		if( php_version_at_least( '5.2.10' ) && ini_get( 'output_handler' ) == '' && function_exists( 'ini_set' ) ) {
			ini_set( 'zlib.output_compression', true );
			// do it transparently
			return false;
		}
		
		if ( OFF == $g_use_iis ) {
			// disable compression when using IIS because of issue #2953. 
			// For windows compression, use zlib.output_compression in php.ini or a later version of php
			return false;
		}
		// if php.ini does not already use ob_gzhandler by default, return true.
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
	} else if ( ini_get_bool( 'zlib.output_compression' ) == true ) {
		if( defined( 'COMPRESSION_DISABLED' ) ) {
			return;
		}
		ob_start();
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
