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
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( UPDATER );
	check_bug_exists( $f_id );
	$c_id = (integer)$f_id;

	# set variable to be valid if necessary
	if ( !isset( $f_duplicate_id ) ) {
		$f_duplicate_id = '';
	}

	# grab the bug_text_id
	$query = "SELECT bug_text_id
				FROM $g_mantis_bug_table
				WHERE id='$c_id'";
	$result = db_query( $query );
	$t_bug_text_id = db_result( $result, 0, 0 );

    if ( ( $f_handler_id != 0 ) AND ( NEW_ == $f_status ) ) {
        $f_status = ASSIGNED;
    }

	# prevent warnings
	if ( !isset( $f_os ) ) {
		$f_os = get_bug_field( $f_id, 'os' );
	}
	if ( !isset( $f_os_build ) ) {
		$f_os_build = get_bug_field( $f_id, 'os_build' );
	}
	if ( !isset( $f_platform ) ) {
		$f_platform = get_bug_field( $f_id, 'platform' );
	}
	if ( !isset( $f_version ) ) {
		$f_version = get_bug_field( $f_id, 'version' );
	}
	if ( !isset( $f_build ) ) {
		$f_build = get_bug_field( $f_id, 'build' );
	}
	if ( !isset( $f_eta ) ) {
		$f_eta = get_bug_field( $f_id, 'eta' );
	}
	if ( !isset( $f_projection ) ) {
		$f_projection = get_bug_field( $f_id, 'projection' );
	}
	if ( !isset( $f_resolution ) ) {
		$f_resolution = get_bug_field( $f_id, 'resolution' );
	}
	if ( !isset( $f_steps_to_reproduce ) ) {
		$c_steps_to_reproduce = get_bug_text_field( $f_id, 'steps_to_reproduce' );
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

	$c_status			= (integer)$f_status;
	$c_category			= addslashes($f_category);
	$c_severity			= (integer)$f_severity;
	$c_resolution		= (integer)$f_resolution;
	$c_projection		= (integer)$f_projection;
	$c_eta				= (integer)$f_eta;
	$c_priority			= (integer)$f_priority;
	$c_reproducibility	= (integer)$f_reproducibility;
	$c_status			= (integer)$f_status;
	$c_duplicate_id		= (integer)$f_duplicate_id;
	$c_handler_id		= (integer)$f_handler_id;
	$c_view_state		= (integer)$f_view_state;

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

    $query = "UPDATE $g_mantis_bug_text_table
    		SET description='$c_description',
				steps_to_reproduce='$c_steps_to_reproduce',
				additional_information='$c_additional_information'
    		WHERE id='$t_bug_text_id'";
   	$result = db_query($query);

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
