<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Update bug data then redirect to the appropriate viewing page
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	project_access_check( $f_id );
	check_access( UPDATER );

	# extract current extended information into history variables
	$result = get_bug_row_ex ( $f_id );
	if ( 0 == db_num_rows( $result ) ) {
		# speed is not an issue in this case, so re-use code
		check_bug_exists( $f_id );
	}

	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'h' );

	$c_id = (integer)$f_id;

	# set variable to be valid if necessary
	check_varset( $f_duplicate_id, '' );
	check_varset( $f_category, '' );

    if ( ( $f_handler_id != 0 ) AND ( NEW_ == $f_status ) AND ( ON == $g_auto_set_status_to_assigned ) ) {
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
	$c_status					= (integer)$f_status;
	$c_duplicate_id				= (integer)$f_duplicate_id;
	$c_handler_id				= (integer)$f_handler_id;
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
				eta='$c_eta',
				summary='$c_summary',
				os='$c_os',
				os_build='$c_os_build',
				platform='$c_platform',
				build='$c_build',
				version='$c_version',
				view_state='$c_view_state'
    		WHERE id='$c_id'";
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
	$t_user_id = get_current_user_field( 'id' );
	history_log_event_direct( $c_id, 'category',        $h_category, $c_category, $t_user_id );
	history_log_event_direct( $c_id, 'severity',        $h_severity, $c_severity, $t_user_id );
	history_log_event_direct( $c_id, 'reproducibility', $h_reproducibility, $c_reproducibility, $t_user_id );
	history_log_event_direct( $c_id, 'priority',        $h_priority, $c_priority, $t_user_id );
	history_log_event_direct( $c_id, 'status',          $h_status, $c_status, $t_user_id );
	history_log_event_direct( $c_id, 'projection',      $h_projection, $c_projection, $t_user_id );
	history_log_event_direct( $c_id, 'duplicate_id',    $h_duplicate_id, $c_duplicate_id, $t_user_id );
	history_log_event_direct( $c_id, 'resolution',      $h_resolution, $c_resolution, $t_user_id );
	history_log_event_direct( $c_id, 'handler_id',      $h_handler_id, $c_handler_id, $t_user_id );
	history_log_event_direct( $c_id, 'eta',             $h_eta, $c_eta, $t_user_id );
	history_log_event_direct( $c_id, 'summary',         $h_summary, $c_summary, $t_user_id );
	history_log_event_direct( $c_id, 'os',              $h_os, $c_os, $t_user_id );
	history_log_event_direct( $c_id, 'os_build',        $h_os_build, $c_os_build, $t_user_id );
	history_log_event_direct( $c_id, 'platform',        $h_platform, $c_platform, $t_user_id );
	history_log_event_direct( $c_id, 'build',           $h_build, $c_build, $t_user_id );
	history_log_event_direct( $c_id, 'version',         $h_version, $c_version, $t_user_id );
	history_log_event_direct( $c_id, 'view_state',      $h_view_state, $c_view_state, $t_user_id );

	if ( $h_description != $c_description ) {
		history_log_event_special( $c_id, DESCRIPTION_UPDATED );
	}
	if ( $h_steps_to_reproduce != $c_steps_to_reproduce ) {
		history_log_event_special( $c_id, STEP_TO_REPRODUCE_UPDATED );
	}
	if ( $h_additional_information != $c_additional_information ) {
		history_log_event_special( $c_id, ADDITIONAL_INFO_UPDATED );
	}

	# updated the last_updated date
	$result = bug_date_update( $f_id );

	# If we should notify and it's in feedback state then send an email
	switch ( $f_status ) {
		case FEEDBACK:	if ( $f_status!= $f_old_status ) {
   							email_feedback( $f_id );
   						}
						break;
		case ASSIGNED:	if ( ( $f_handler_id != $f_old_handler_id ) OR ( $f_status!= $f_old_status ) ) {
			   				email_assign( $f_id );
			   			}
						break;
		case RESOLVED:	email_resolved( $f_id );
						break;
		case CLOSED:	email_close( $f_id );
						break;
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
