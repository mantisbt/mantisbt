<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# This page updates a user's information
	# If an account is protected then changes are forbidden
	# The page gets redirected back to account_page.php3
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	$f_id 			= get_current_user_field( "id" );
	$f_protected 	= get_current_user_field( "protected" );

	# If an account is protected then no one can change the information
	# This is useful for shared accounts or for demo purposes
	$result = 0;
	if ( OFF == $f_protected ) {

		# Update everything except password
	    $query = "UPDATE $g_mantis_user_table
	    		SET username='$f_username', email='$f_email'
	    		WHERE id='$f_id'";
		$result = db_query( $query );

		# Update password if the two match and are not empty
		if (( !empty( $f_password ) )&&( $f_password == $f_password_confirm )) {
			$t_password = process_plain_password( $f_password );
			$query = "UPDATE $g_mantis_user_table
					SET password='$t_password'
					WHERE id='$f_id'";
			$result = db_query( $query );
		}
	} # end if protected

	$t_redirect_url = $g_account_page;
?>
<?php print_page_top1() ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?
	if ( ON == $f_protected ) {				# PROTECTED
		PRINT "$s_account_protected_msg<p>";
	} else if ( $result ) {					# SUCCESS
		PRINT "$s_operation_successful<p>";
	} else {								# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_account_page, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>