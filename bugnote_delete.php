<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bugnote_delete.php,v 1.31 2003-02-15 10:25:16 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Remove the bugnote and bugnote text and redirect back to
	# the viewing page
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );
	require_once( $t_core_path.'bugnote_api.php' );
?>
<?php
	$f_bugnote_id = gpc_get_int( 'bugnote_id' );

	access_ensure_bugnote_level( config_get( 'delete_bugnote_threshold' ), $f_bugnote_id );

	helper_ensure_confirmed( lang_get( 'delete_bugnote_sure_msg' ),
							 lang_get( 'delete_bugnote_button' ) );

	bugnote_delete( $f_bugnote_id );

	$t_bug_id = bugnote_get_field( $f_bugnote_id, 'bug_id' );

	print_header_redirect( string_get_bug_view_url( $t_bug_id ) . '#bugnotes' );
?>
