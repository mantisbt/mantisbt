<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_monitor.php,v 1.19 2003-01-23 23:02:54 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	# This file turns monitoring on or off for a bug for the current user
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'bug_api.php' );
	require_once( $g_core_path . 'project_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id	= gpc_get_int( 'bug_id' );
	$f_action	= gpc_get_string( 'action' );

	project_access_check( $f_bug_id );
	bug_ensure_exists( $f_bug_id );

	$t_view_state = bug_get_field( $f_bug_id, 'view_state' );

	$t_threshold = config_get( 'monitor_bug_threshold' );
	if ( PRIVATE == $t_view_state ) {
		$t_threshold = max( config_get( 'private_bug_threshold' ) , $t_threshold );
	}

	check_access( $t_threshold );

	if ( 'delete' == $f_action ) {
		bug_unmonitor( $f_bug_id, auth_get_current_user_id() );
	} else { # should be 'add' but we have to account for other values
		bug_monitor( $f_bug_id, auth_get_current_user_id() );
	}

	print_header_redirect_view( $f_bug_id );
?>
