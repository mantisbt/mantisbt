#!/usr/local/bin/php -q
<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: send_emails.php,v 1.1 2006-08-14 08:32:57 vboctor Exp $
	# --------------------------------------------------------

	global $g_bypass_headers;
	$g_bypass_headers = 1;

	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'email_api.php' );

	# Make sure this script doesn't run via the webserver
	# @@@ This is a hack to detect php-cgi, there must be a better way.
	if ( isset( $_SERVER['SERVER_PORT'] ) ) {
		echo "send_emails.php is not allowed to run through the webserver.\n";
		exit( 1 );
	}

	echo "Sending emails...\n";
	email_send_all();
	echo "Done.\n";

	exit( 0 );
?>