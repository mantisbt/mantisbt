<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# <SQLI>
	# This page allows the close / suppress / others mass treatments, and display the adequate page
?>


<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php

# the queries
function updateBugLite($p_id, $p_status, $p_request) {
	global $g_mantis_bug_table;

	$t_handler_id = get_current_user_field( 'id' );
	$t_query='';

	switch ($p_status) {
		
		case 'MOVED' : 
			$t_query = "project_id='$p_request'";
			break;
				
		case CLOSED :
			$t_query="status='$p_status'";
			break ;

		case ASSIGNED :
			$t_handler_id = $p_request ;
			$t_query="status='$p_status'";
			break ;

		case RESOLVED :
			$t_query=" status='$p_status', resolution='$p_request'";
			break ;

		case 'UP_PRIOR' :
			$t_query="priority='$p_request'";
			break ;

		case 'UP_STATUS' :
			$t_query="status='$p_request'";
			break ;

	}
	# Update fields
	$query = "UPDATE $g_mantis_bug_table ".
    		"SET handler_id='$t_handler_id', $t_query ".
			"WHERE id='$p_id'";

   	$result = db_query($query);

	# update bug last updated
	bug_date_update($p_id);

   	# notify reporter and handler
	# currently desactivated
	/*switch ( $p_status ) {

		case ASSIGNED:	email_assign( $p_id );
			   			break;
		case RESOLVED:	email_resolved( $p_id );
						break;
		case CLOSED:	email_close( $p_id );
						break;
	}*/

}//updateBug 


if ( $f_actionconfirmed=='1' ) {	
	
	foreach($f_bug_arr as $value) {

		# get the id and the bug_text_id parameters
		# the bug_text_id is used only for the delete function
		$t_id_arr=explode( ',', $value );

		switch ( $f_action ) {
		
		case 'CLOSE':
			updateBugLite($t_id_arr[0],CLOSED,'');
			break;

		case 'DELETE':
			deleteBug($t_id_arr[0],$t_id_arr[1]);
			break;
			
		case 'MOVE':
			updateBugLite($t_id_arr[0],'MOVED',$f_project_id);
			break;
	
		case 'ASSIGN':
			updateBugLite($t_id_arr[0],ASSIGNED,$f_assign);
			break;

		case 'RESOLVE':
			updateBugLite($t_id_arr[0],RESOLVED,$f_resolution);
			break;
		
		case 'UP_PRIOR':
			updateBugLite($t_id_arr[0],'UP_PRIOR',$f_priority);
			break;

		case 'UP_STATUS':
			updateBugLite($t_id_arr[0],'UP_STATUS',$f_status);
			break;
		}
	}

	print_meta_redirect( 'view_all_bug_page.php',0);
} 
?>