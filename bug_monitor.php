<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This file sets the bug to the chosen resolved state then gives the
	# user the opportunity to enter a reason for the closure
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
    $query = "SELECT view_state
    		FROM $g_mantis_bug_table
    		WHERE id='$f_id'";
    $result = db_query( $query );
	$row = db_fetch_array( $result );
	extract( $row, EXTR_PREFIX_ALL, "v" );
	if ( PRIVATE == $v_view_state ) {
		check_access( $g_private_bug_threshold );
	}
	else {
		check_access( $g_monitor_bug_threshold );
	}
	check_bug_exists( $f_id );

	# get user information
	$u_id = get_current_user_field( "id " );

	if ( "add" == $f_action ) {
	# Make sure we aren't already monitoring this bug
 		$query = "SELECT *
 		   	FROM $g_mantis_bug_monitor_table
 		   	WHERE bug_id='$f_id' AND user_id='$u_id'";
 		$result = db_query( $query );
		$t_num_rows = db_num_rows( $result );

		if ( $t_num_rows == 0 ) {
			# Insert monitoring record
   		 $query = "INSERT
					INTO $g_mantis_bug_monitor_table
					( user_id, bug_id )
					VALUES
					( '$u_id', '$f_id' )";
   			$result = db_query($query);
		}

	} elseif ( "delete" == $f_action ) {

		# Delete monitoring record
   	 $query = "DELETE
				FROM $g_mantis_bug_monitor_table
				WHERE user_id = '$u_id' AND bug_id = '$f_id'";
   		$result = db_query($query);
	}

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
