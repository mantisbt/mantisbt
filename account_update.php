<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page updates a user's information
	# If an account is protected then changes are forbidden
	# The page gets redirected back to account_page.php3
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	$f_id 			= get_current_user_field( 'id' );
	$f_protected 	= get_current_user_field( 'protected' );

	# protected account check
	if ( ON == $f_protected ) {
		print_mantis_error( ERROR_PROTECTED_ACCOUNT );
	}

	# If an account is protected then no one can change the information
	# This is useful for shared accounts or for demo purposes
	$result = 0;
	if ( OFF == $f_protected ) {

		$c_username	= addslashes($f_username);
		$c_email	= addslashes($f_email);
		$c_id		= (integer)$f_id;

		# Update everything except password
	    $query = "UPDATE $g_mantis_user_table
	    		SET username='$c_username', email='$c_email'
	    		WHERE id='$c_id'";
		$result = db_query( $query );

		# Update password if the two match and are not empty
		if (( !empty( $f_password ) )&&( $f_password == $f_password_confirm )) {
			$t_password = process_plain_password( $f_password );
			$query = "UPDATE $g_mantis_user_table
					SET password='$t_password'
					WHERE id='$c_id'";
			$result = db_query( $query );
		}
	} # end if protected

	$t_redirect_url = 'account_page.php';
	if ( $result ) {
		print_header_redirect( $t_redirect_url );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>