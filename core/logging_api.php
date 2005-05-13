<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: logging_api.php,v 1.1 2005-05-13 22:02:55 thraxisp Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );
	
	$g_log_levels = array(
		LOG_EMAIL => 'mail',
		LOG_EMAIL_RECIPIENT => 'mail_recipient'
	);

	###########################################################################
	# Logging api
	#  This is used to log system events other than bug related history
	###########################################################################
	
	function log_event( $p_level, $p_msg ) {
		global $g_log_levels;
	
		# check to see if logging is enabled
		$t_sys_log = config_get_global( 'log_level' );
		if ( 0 == ( $t_sys_log & $p_level ) ) {
			return;
		}
		
		$t_now = date( config_get( 'complete_date_format' ) );
		$t_level = $g_log_levels[$p_level];
		
		list( $t_destination, $t_modifiers) = split( ':', config_get_global( 'log_destination' ), 2 );
		switch ( $t_destination ) {
			case 'file':
				error_log( $t_now . ' ' . $t_level . ' ' . $p_msg . "\n", 3, $t_modifiers );
				break;
			default:
				break;
		}
	}
	
?>