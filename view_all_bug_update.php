<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page allows the close / suppress / others mass treatments, and display the adequate page
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	
	$f_action			= gpc_get_string( 'f_action' );
	$f_actionconfirmed	= gpc_get_bool( 'f_actionconfirmed' );
	$f_bug_arr			= gpc_get_string_array( 'f_bug_arr', array() );

# the queries
function updateBugLite($p_id, $p_status, $p_request) {
	$t_handler_id = auth_get_current_user_id();
	$t_query='';
	$result = 1;

	# history treatment
	# extract current extended information into history variables
	$result = get_bug_row_ex ( $p_id );
	if ( 0 == db_num_rows( $result ) ) {
		# speed is not an issue in this case, so re-use code
		bug_ensure_exists( $p_id );
	}
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, 'h' );

	switch ($p_status) {

		case 'MOVED' :
			check_access( config_get( 'bug_move_access_level' ) );
			$t_query = "project_id='$p_request'";
			break;

		case CLOSED :
			check_access( config_get( 'close_bug_threshold' ) );
			$t_query="status='$p_status'";
			break ;

		case ASSIGNED :
			check_access( config_get( 'update_bug_threshold' ) );
			// @@@ Check that $p_request has access to handle a bug.
			$t_handler_id = $p_request ;
			$t_query="handler_id='$t_handler_id', status='$p_status'";
			break ;

		case RESOLVED :
			check_access( config_get( 'handle_bug_threshold' ) );
			$t_query=" status='$p_status', resolution='$p_request'";
			break ;

		case 'UP_PRIOR' :
			check_access( config_get( 'update_bug_threshold' ) );
			$t_query="priority='$p_request'";
			break ;

		case 'UP_STATUS' :
			check_access( config_get( 'update_bug_threshold' ) );
			$t_query="handler_id='$t_handler_id', status='$p_request'";
			break ;
	}
	# Update fields
	$query = "UPDATE ".config_get( 'mantis_bug_table' )."
    		  SET $t_query
			  WHERE id='$p_id'";

   	$result = db_query($query);

	if ( !$result ) {
		print_mantis_error( ERROR_GENERIC );
	}

	# history logging should be done after the field updates
	switch ($p_status) {

		case 'MOVED' :
			history_log_event_direct( $p_id, 'project_id', $h_project_id, $p_request, $t_handler_id );
			break;

		case CLOSED :
			history_log_event_direct( $p_id, 'status', $h_status, $p_status, $t_handler_id );
			break ;

		case ASSIGNED :
			history_log_event_direct( $p_id, 'handler_id', $h_handler_id, $p_request, $t_handler_id );
			history_log_event_direct( $p_id, 'status', $h_status, $p_status, $t_handler_id );
			break ;

		case RESOLVED :
			history_log_event_direct( $p_id, 'resolution', $h_resolution, $p_request, $t_handler_id );
			history_log_event_direct( $p_id, 'status', $h_status, $p_status, $t_handler_id );
			break ;

		case 'UP_PRIOR' :
			history_log_event_direct( $p_id, 'priority', $h_priority, $p_request, $t_handler_id );
			break ;

		case 'UP_STATUS' :
			history_log_event_direct( $p_id, 'status', $h_status, $p_request, $t_handler_id );
			break ;
	}

	# update bug last updated
	bug_date_update($p_id);

   	# notify reporter and handler
	switch ( $p_status ) {

		case ASSIGNED:	email_assign( $p_id );
			   			break;
		case RESOLVED:	email_resolved( $p_id );
						break;
		case CLOSED:	email_close( $p_id );
						break;
	}

}//updateBug

# We check to see if the variable exists to avoid warnings
if ( $f_actionconfirmed ) {

	foreach($f_bug_arr as $value) {

		# get the id and the bug_text_id parameters
		# the bug_text_id is used only for the delete function
		$t_id_arr=explode( ',', $value );

		switch ( $f_action ) {

		case 'CLOSE':
			updateBugLite($t_id_arr[0],CLOSED,'');
			break;

		case 'DELETE':
			bug_delete($t_id_arr[0],$t_id_arr[1]);
			break;

		case 'MOVE':
			$f_project_id = gpc_get_int( 'f_project_id' );
			updateBugLite($t_id_arr[0],'MOVED',$f_project_id);
			break;

		case 'ASSIGN':
			$f_assign = gpc_get_int( 'f_assign' );
			updateBugLite($t_id_arr[0],ASSIGNED,$f_assign);
			break;

		case 'RESOLVE':
			$f_resolution = gpc_get_int( 'f_resolution' );
			updateBugLite($t_id_arr[0],RESOLVED,$f_resolution);
			break;

		case 'UP_PRIOR':
			$f_priority = gpc_get_int( 'f_priority' );
			updateBugLite($t_id_arr[0],'UP_PRIOR',$f_priority);
			break;

		case 'UP_STATUS':
			$f_status = gpc_get_int( 'f_status' );
			updateBugLite($t_id_arr[0],'UP_STATUS',$f_status);
			break;
		}
	}

	print_meta_redirect( 'view_all_bug_page.php',0);
}
?>
