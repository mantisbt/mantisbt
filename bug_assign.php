<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

    # This module is based on bug_update.php3 and provides a quick method
    # for assigning a call to the currently signed on user.
    # Copyright (C) 2001  Steve Davies - steved@ihug.co.nz

?>
<?php
	# Assign bug to user then redirect to viewing page
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	project_access_check( $f_id );
	check_access( DEVELOPER );
	check_bug_exists( $f_id );

    $t_ass_val = ASSIGNED;

    # get user id
    $t_handler_id = get_current_user_field( "id" );
    $query = "UPDATE $g_mantis_bug_table
            SET handler_id='$t_handler_id', status='$t_ass_val'
			WHERE id='$f_id'";
    $result = db_query($query);

	# updated the last_updated date
	$result = bug_date_update( $f_id );

	# send assigned to email
	email_assign( $f_id );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $f_id, 1 );
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>