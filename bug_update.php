<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_update.php,v 1.39 2002-11-27 02:45:20 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Update bug data then redirect to the appropriate viewing page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id = gpc_get_int( 'f_bug_id' );

	project_access_check( $f_bug_id );
	check_access( config_get( 'update_bug_threshold' ) );
	bug_ensure_exists( $f_bug_id );

	# if bug is private, make sure user can view private bugs
	# use the db view state rather than the new one to check
	access_bug_check( $f_bug_id );

	# extract current extended information
	$t_bug_data = bug_get( $f_bug_id, true );

	$t_bug_data->reporter_id		= gpc_get_int( 'f_reporter_id', $t_bug_data->reporter_id );
	$t_bug_data->handler_id			= gpc_get_int( 'f_handler_id', $t_bug_data->handler_id );
	$t_bug_data->duplicate_id		= gpc_get_int( 'f_duplicate_id', $t_bug_data->duplicate_id );
	$t_bug_data->priority			= gpc_get_int( 'f_priority', $t_bug_data->priority );
	$t_bug_data->severity			= gpc_get_int( 'f_severity', $t_bug_data->severity );
	$t_bug_data->reproducibility	= gpc_get_int( 'f_reproducibility', $t_bug_data->reproducibility );
	$t_bug_data->status				= gpc_get_int( 'f_status', $t_bug_data->status );
	$t_bug_data->resolution			= gpc_get_int( 'f_resolution', $t_bug_data->resolution );
	$t_bug_data->projection			= gpc_get_int( 'f_projection', $t_bug_data->projection );
	$t_bug_data->category			= gpc_get_string( 'f_category', $t_bug_data->category );
	$t_bug_data->eta				= gpc_get_int( 'f_eta', $t_bug_data->eta );
	$t_bug_data->os					= gpc_get_string( 'f_os', $t_bug_data->os );
	$t_bug_data->os_build			= gpc_get_string( 'f_os_build', $t_bug_data->os_build );
	$t_bug_data->platform			= gpc_get_string( 'f_platform', $t_bug_data->platform );
	$t_bug_data->version			= gpc_get_string( 'f_version', $t_bug_data->version );
	$t_bug_data->build				= gpc_get_string( 'f_build', $t_bug_data->build );
	$t_bug_data->view_state			= gpc_get_int( 'f_view_state', $t_bug_data->view_state );
	$t_bug_data->summary			= gpc_get_string( 'f_summary', $t_bug_data->summary );

	$t_bug_data->description		= gpc_get_string( 'f_description', $t_bug_data->description );
	$t_bug_data->steps_to_reproduce	= gpc_get_string( 'f_steps_to_reproduce', $t_bug_data->steps_to_reproduce );
	$t_bug_data->additional_information	= gpc_get_string( 'f_additional_information', $t_bug_data->additional_information );

	$f_private						= gpc_get_bool( 'f_private' );
	$f_bugnote_text					= gpc_get_string( 'f_bugnote_text', '' );

	# Handle auto-assigning
    if ( ( NEW_ == $t_bug_data->status ) 
	  && ( 0 != $t_bug_data->handler_id )
	  && ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
        $t_bug_data->status = ASSIGNED;
    }

	bug_update( $f_bug_id, $t_bug_data, true );

	$f_bugnote_text = trim( $f_bugnote_text );
	if ( !is_blank( $f_bugnote_text ) ) {
		bugnote_add( $f_bug_id, $f_bugnote_text, $f_private );
	}

	print_header_redirect_view( $f_bug_id );
?>