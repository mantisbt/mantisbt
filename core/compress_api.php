<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: compress_api.php,v 1.2 2002-08-25 08:14:59 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Compression API
	#
	# Starts the buffering/compression (only if the compression option is ON)
	# This method should be called after all possible re-directs and access
	# level checks.
	###########################################################################

	function start_compression() {
		global $g_compress_html;

		if ( ON == $g_compress_html ) {
			ob_implicit_flush( 0 );
			ob_start( 'ob_gzhandler' );
		}
	}
	# ----------------
	# Stop buffering and flush buffer contents.
	function stop_compression() {
		global $g_compress_html;

		if ( ON == $g_compress_html ) {
			ob_end_flush();
			ob_implicit_flush();
		}
	}
	# ----------------
?>