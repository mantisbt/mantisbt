<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# Set an existing bugnote private or public.
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# make sure the user accessing the note is valid and has proper access
	$t_bugnote_user_id	= get_bugnote_field( $f_bugnote_id, 'reporter_id' );
	$t_id				= get_bugnote_field( $f_bugnote_id, 'bug_id' );
	$t_user_id			= get_current_user_field( 'id' );
	$c_bugnote_id = (integer)$f_bugnote_id;

	project_access_check( $t_id );

	if ( get_bug_field( $t_id, 'status' ) < RESOLVED ) {
		if (( access_level_check_greater_or_equal( ADMINISTRATOR ) ) ||
			( $t_bugnote_user_id == $t_user_id )) {
			# do nothing
		} elseif ( access_level_check_greater_or_equal( $g_private_bugnote_threshold ) ) {
			# do nothing
		} else {
			print_header_redirect( 'logout_page.php' );
		}
	} else {
		print_header_redirect( 'logout_page.php' );
	}

	if ( 1 == $f_private ) {
		$c_view_state = PRIVATE;
	} else {
		$c_view_state = PUBLIC;
	}

	# update view_state
	$query = "UPDATE $g_mantis_bugnote_table
				SET view_state='$c_view_state'
				WHERE id='$c_bugnote_id'";
	$result = db_query( $query );

	# Determine which view page to redirect back to.
	$t_redirect_url = get_view_redirect_url( $t_id, 1 ) . '#bugnotes';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>