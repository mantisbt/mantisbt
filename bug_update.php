<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Revision: 1.36 $
	# $Author: jfitzell $
	# $Date: 2002-10-20 23:59:48 $
	#
	# $Id: bug_update.php,v 1.36 2002-10-20 23:59:48 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Update bug data then redirect to the appropriate viewing page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_bug_id );
	check_access( $g_update_bug_threshold );

	$c_bug_id = (integer)$f_bug_id;

	bug_ensure_exists( $f_bug_id );

	# extract current extended information into history variables
	$row = bug_get_extended_row( $f_bug_id );
	extract( $row, EXTR_PREFIX_ALL, 'h' );

	# if bug is private, make sure user can view private bugs
	# use the db view state rather than the new one to check
	access_bug_check( $f_bug_id, $h_view_state );

	# set variable to be valid if necessary
	check_varset( $f_duplicate_id, '' );
	check_varset( $f_category, '' );

    if ( ( $f_handler_id != 0 ) AND ( NEW_ == $f_status ) AND ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
        $f_status = ASSIGNED;
    }

	# prevent warnings
	check_varset( $f_os,         $h_os );
	check_varset( $f_os_build,   $h_os_build );
	check_varset( $f_platform,   $h_platform );
	check_varset( $f_version,    $h_version );
	check_varset( $f_build,      $h_build );
	check_varset( $f_eta,        $h_eta );
	check_varset( $f_projection, $h_projection );
	check_varset( $f_resolution, $h_resolution );
	check_varset( $f_os_build,   $h_os_build );

	if ( !isset( $f_steps_to_reproduce ) ) {
		$c_steps_to_reproduce = $h_steps_to_reproduce;
	} else {
		$c_steps_to_reproduce = string_prepare_textarea( $f_steps_to_reproduce );
	}

	# prepare strings
	$c_os 						= string_prepare_text( $f_os );
	$c_os_build 				= string_prepare_text( $f_os_build );
	$c_platform					= string_prepare_text( $f_platform );
	$c_version 					= string_prepare_text( $f_version );
	$c_build 					= string_prepare_text( $f_build );
	$c_summary					= string_prepare_text( $f_summary );
	$c_description 				= string_prepare_textarea( $f_description );
	$c_additional_information 	= string_prepare_textarea( $f_additional_information );

	$c_category					= addslashes($f_category);
	$c_status					= (integer)$f_status;
	$c_severity					= (integer)$f_severity;
	$c_resolution				= (integer)$f_resolution;
	$c_projection				= (integer)$f_projection;
	$c_eta						= (integer)$f_eta;
	$c_priority					= (integer)$f_priority;
	$c_reproducibility			= (integer)$f_reproducibility;
	$c_duplicate_id				= (integer)$f_duplicate_id;
	$c_handler_id				= (integer)$f_handler_id;
	$c_reporter_id              = (integer)$f_reporter_id;
	$c_view_state				= (integer)$f_view_state;

	$h_description 				= string_prepare_textarea( $h_description );
	$h_steps_to_reproduce		= string_prepare_textarea( $h_steps_to_reproduce );
	$h_additional_information	= string_prepare_textarea( $h_additional_information );

	# Update all fields
    $query = "UPDATE $g_mantis_bug_table
    		SET category='$c_category',
    			severity='$c_severity',
    			reproducibility='$c_reproducibility',
				priority='$c_priority',
				status='$c_status',
				projection='$c_projection',
				duplicate_id='$c_duplicate_id',
				resolution='$c_resolution',
				handler_id='$c_handler_id',
				reporter_id='$c_reporter_id',
				eta='$c_eta',
				summary='$c_summary',
				os='$c_os',
				os_build='$c_os_build',
				platform='$c_platform',
				build='$c_build',
				version='$c_version',
				view_state='$c_view_state'
    		WHERE id='$c_bug_id'";
   	$result = db_query($query);

	# These fields are not changed as often as the assigned person, priority, status, etc.
	if ( ( $c_description != $h_description ) || ( $c_steps_to_reproduce != $h_steps_to_reproduce ) || ( $c_additional_information != $h_additional_information ) ) {
	    $query = "UPDATE $g_mantis_bug_text_table ".
					"SET description='$c_description', ".
					"steps_to_reproduce='$c_steps_to_reproduce', ".
					"additional_information='$c_additional_information' ".
					"WHERE id='$h_bug_text_id'";
	   	$result = db_query($query);
	}

	# log changes
	$t_user_id = current_user_get_field( 'id' );
	history_log_event_direct( $c_bug_id, 'category',        $h_category, $f_category, $t_user_id );
	history_log_event_direct( $c_bug_id, 'severity',        $h_severity, $c_severity, $t_user_id );
	history_log_event_direct( $c_bug_id, 'reproducibility', $h_reproducibility, $c_reproducibility, $t_user_id );
	history_log_event_direct( $c_bug_id, 'priority',        $h_priority, $c_priority, $t_user_id );
	history_log_event_direct( $c_bug_id, 'status',          $h_status, $c_status, $t_user_id );
	history_log_event_direct( $c_bug_id, 'projection',      $h_projection, $f_projection, $t_user_id );
	history_log_event_direct( $c_bug_id, 'duplicate_id',    $h_duplicate_id, $c_duplicate_id, $t_user_id );
	history_log_event_direct( $c_bug_id, 'resolution',      $h_resolution, $c_resolution, $t_user_id );
	history_log_event_direct( $c_bug_id, 'handler_id',      $h_handler_id, $c_handler_id, $t_user_id );
	history_log_event_direct( $c_bug_id, 'reporter_id',     $h_reporter_id, $c_reporter_id, $t_user_id );
	history_log_event_direct( $c_bug_id, 'eta',             $h_eta, $c_eta, $t_user_id );
	history_log_event_direct( $c_bug_id, 'summary',         $h_summary, $f_summary, $t_user_id );
	history_log_event_direct( $c_bug_id, 'os',              $h_os, $f_os, $t_user_id );
	history_log_event_direct( $c_bug_id, 'os_build',        $h_os_build, $f_os_build, $t_user_id );
	history_log_event_direct( $c_bug_id, 'platform',        $h_platform, $f_platform, $t_user_id );
	history_log_event_direct( $c_bug_id, 'build',           $h_build, $f_build, $t_user_id );
	history_log_event_direct( $c_bug_id, 'version',         $h_version, $f_version, $t_user_id );
	history_log_event_direct( $c_bug_id, 'view_state',      $h_view_state, $c_view_state, $t_user_id );

	if ( $h_description != $c_description ) {
		history_log_event_special( $c_bug_id, DESCRIPTION_UPDATED );
	}
	if ( $h_steps_to_reproduce != $c_steps_to_reproduce ) {
		history_log_event_special( $c_bug_id, STEP_TO_REPRODUCE_UPDATED );
	}
	if ( $h_additional_information != $c_additional_information ) {
		history_log_event_special( $c_bug_id, ADDITIONAL_INFO_UPDATED );
	}

	# add a bugnote if there was one
	check_varset( $f_private, false );
	check_varset( $f_bugnote_text, '' );

	$f_bugnote_text = trim( $f_bugnote_text );
	if ( !empty( $f_bugnote_text ) ) {
		$result = bugnote_add( $f_bug_id, $f_bugnote_text, (bool)$f_private );
	}

	# updated the last_updated date
	$result = bug_update_date( $f_bug_id );

	# If we should notify and it's in feedback state then send an email
	switch ( $f_status ) {
		case NEW_:		# This will be used in the case where auto-assign = OFF, in this case the bug can be 
						# assigned/unassigned while the status is NEW.
						# @@@ In case of unassigned, the e-mail will still say ASSIGNED, but it will be shown
						# that the handler is empty + history ( old_handler => @null@ ).
						if ( $f_handler_id != $f_old_handler_id ) {
							email_assign( $f_bug_id );
						}
						break;
		case FEEDBACK:	if ( $f_status!= $f_old_status ) {
   							email_feedback( $f_bug_id );
   						}
						break;
		case ASSIGNED:	if ( ( $f_handler_id != $f_old_handler_id ) OR ( $f_status != $f_old_status ) ) {
							email_assign( $f_bug_id );
			   			}
						break;
		case RESOLVED:	email_resolved( $f_bug_id );
						break;
		case CLOSED:	email_close( $f_bug_id );
						break;
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $f_bug_id );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>