<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	# Delete account, remove cookies, and redirect user to logout redirect page
	# If the account is protected this fails.
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# get protected state
	$t_protected = get_current_user_field( "protected" );

	# If an account is protected then no one can change the information
	# This is useful for shared accounts or for demo purposes
	$result = 0;
	if ( 0 == $t_protected ) {

		# get user id
		$t_user_id = get_current_user_field( "id" );

	    # Remove account
    	$query = "DELETE
    			FROM $g_mantis_user_table
    			WHERE id='$t_user_id'";
	    $result = db_query( $query );

	    # Remove associated profiles
	    $query = "DELETE
	    		FROM $g_mantis_user_profile_table
	    		WHERE user_id='$t_user_id'";
	    $result = db_query( $query );

		# Remove associated preferences
    	$query = "DELETE
    			FROM $g_mantis_user_pref_table
    			WHERE user_id='$t_user_id'";
    	$result = db_query( $query );

    	$query = "DELETE
    			FROM $g_mantis_project_user_list_table
	    		WHERE user_id='$f_id'";
	    $result = db_query( $query );

		# delete cookies
		setcookie( $g_string_cookie );
		setcookie( $g_project_cookie );
		setcookie( $g_view_all_cookie );
	} # end if protected
?>
<? print_page_top1() ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_logout_redirect_page );
	}
?>
<? print_page_top2() ?>

<p>
<div align="center">
<?
	if ( ON == $t_protected ) {				# PROTECTED
		PRINT "$s_account_protected_msg<p>";
		print_bracket_link( $g_account_page, $s_go_back );
	} else if ( $result ) {					# SUCCESS
		PRINT "$s_operation_successful<p>";
		print_bracket_link( $g_logout_redirect_page, $s_proceed );
	} else {								# FAILURE
		print_sql_error( $query );
	}
?>
</div>

<? print_page_bot1( __FILE__ ) ?>