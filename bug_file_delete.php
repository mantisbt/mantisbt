<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_file_delete.php,v 1.28 2004-10-05 14:59:08 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	# Delete a file from a bug and then view the bug
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php
	$f_file_id = gpc_get_int( 'file_id' );

	$t_bug_id = file_get_field( $f_file_id, 'bug_id' );

	access_ensure_bug_level( config_get( 'update_bug_threshold' ), $t_bug_id );

	file_delete( $f_file_id, 'bug' );

	print_header_redirect_view( $t_bug_id );
?>
