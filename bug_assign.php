<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_assign.php,v 1.37 2004-02-11 22:16:28 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# Assign bug to user then redirect to viewing page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
?>
<?php
	$f_bug_id = gpc_get_int( 'bug_id' );
	$f_handler_id = gpc_get_int( 'handler_id', auth_get_current_user_id() );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );
	
	if ( $f_handler_id != NO_USER ) {
		access_ensure_bug_level( config_get( 'handle_bug_threshold' ), $f_bug_id, $f_handler_id );
	}

	bug_assign( $f_bug_id, $f_handler_id );

	print_successful_redirect_to_bug( $f_bug_id );
?>