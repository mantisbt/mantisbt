<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: xmlhttprequest.php,v 1.2 2007-04-20 08:28:22 vboctor Exp $
	# --------------------------------------------------------

	# This is the first page a user sees when they login to the bugtracker
	# News is displayed which can notify users of any important changes

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'logging_api.php' );
	require_once( $t_core_path . 'xmlhttprequest_api.php' );

	auth_ensure_user_authenticated();

	$f_entrypoint = gpc_get_string( 'entrypoint' );

	$t_function = 'xmlhttprequest_' . $f_entrypoint;
	if ( function_exists( $t_function ) ) {
		log_event( LOG_AJAX, "Calling {$t_function}..." );
		call_user_func( $t_function );
	} else {
		log_event( LOG_AJAX, "Unknown function for entry point = " . $t_function );
		echo 'unknown entry point';
	}
?>
