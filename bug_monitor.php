<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_monitor.php,v 1.25 2003-02-20 07:30:04 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# This file turns monitoring on or off for a bug for the current user
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id	= gpc_get_int( 'bug_id' );
	$f_action	= gpc_get_string( 'action' );

	access_ensure_bug_level( config_get( 'monitor_bug_threshold' ), $f_bug_id );

	if ( 'delete' == $f_action ) {
		bug_unmonitor( $f_bug_id, auth_get_current_user_id() );
	} else { # should be 'add' but we have to account for other values
		bug_monitor( $f_bug_id, auth_get_current_user_id() );
	}

	print_successful_redirect_to_bug( $f_bug_id );
?>
