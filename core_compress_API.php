<?php
	# Starts the buffering/compression (only if the compression option is ON)
	# This method should be called after all possible re-directs and access
	# level checks.
	# ----------------
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