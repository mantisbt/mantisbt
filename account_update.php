<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php
	# This page updates a user's information
	# If an account is protected then changes are forbidden
	# The page gets redirected back to account_page.php3
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_email			= gpc_get_string( 'f_email', '' );
	$f_password			= gpc_get_string( 'f_password', '' );
	$f_password_confirm	= gpc_get_string( 'f_password_confirm', '' );

	$f_email = email_append_domain( $f_email );

	$t_id 			= auth_get_current_user_id();

	$t_user_table	= config_get( 'mantis_user_table' );

	$result = 0;
	# protected account check
	# If an account is protected then no one can change the information
	# This is useful for shared accounts or for demo purposes
	if ( current_user_is_protected() ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	} else {
		email_ensure_valid( $f_email );

		$c_email	= db_prepare_string( $f_email );

		# Update email
	    $query = "UPDATE $t_user_table
	    		SET email='$c_email'
	    		WHERE id='$t_id'";
		$result = db_query( $query );

		# Update password if the two match and are not empty
		#@@@ display an error if the passwords don't match?
		if (( !empty( $f_password ) )&&( $f_password == $f_password_confirm )) {
			$t_password = process_plain_password( $f_password );
			$query = "UPDATE $t_user_table
					SET password='$t_password'
					WHERE id='$t_id'";
			$result = db_query( $query );
		}

	} # end if protected

	if ( $result ) {
		print_header_redirect( 'account_page.php' );
	} else {
		print_mantis_error( ERROR_GENERIC );
	}
?>
