<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	# delete account
    if ( $f_protected!="on" ) {
	    # Remove aaccount
    	$query = "DELETE
    			FROM $g_mantis_user_table
    			WHERE id='$f_id'";
	    $result = db_query( $query );

	    # Remove associated profiles
	    $query = "DELETE
	    		FROM $g_mantis_user_profile_table
	    		WHERE user_id='$f_id'";
	    $result = db_query( $query );

		# Remove associated preferences
    	$query = "DELETE
    			FROM $g_mantis_user_pref_table
    			WHERE user_id='$f_id'";
    	$result = db_query( $query );

    	$query = "DELETE
    			FROM $g_mantis_project_user_list_table
	    		WHERE user_id='$f_id'";
	    $result = db_query( $query );
    }

    $t_redirect_url = $g_manage_page;
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url, $g_wait_time );
	}
?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( "on" == $f_protected ) {				# PROTECTED
		PRINT "$s_account_delete_protected_msg<p>";
	} else if ( $result ) {						# SUCCESS
		PRINT "$s_operation_successful<p>";
	} else {									# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>