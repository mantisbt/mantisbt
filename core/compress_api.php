<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: compress_api.php,v 1.5 2003-01-03 03:24:24 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Compression API
	#
	# Starts the buffering/compression (only if the compression option is ON
	# and PHP version 4.0.4 or above is used).  This method should be called 
	# after all possible re-directs and access level checks.
	###########################################################################

	# ----------------
	# Check if compression should be enabled.
	function compress_is_enabled() {
		return ( ( ON == config_get( 'compress_html' ) ) && php_version_at_least('4.0.4') );
	}
	# ----------------
	# Start output buffering with compression.
	function compress_start() {
		if  ( compress_is_enabled() ) {
			ob_implicit_flush( 0 );
			ob_start( 'ob_gzhandler' );
		}
	}
	# ----------------
	# Stop buffering and flush buffer contents.
	function compress_stop() {
		if  ( compress_is_enabled() ) {
			ob_end_flush();
			ob_implicit_flush();
		}
	}
	# ----------------
?>