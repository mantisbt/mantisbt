<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# Check to see if signup is allowed
	if ( OFF == $g_allow_signup ) {
		print_header_redirect( $g_login_page );
		exit;
	}

	# Check for a properly formatted email with valid MX record
	$result = 0;
	if ( !is_valid_email( $f_email ) ) {
		PRINT "$f_email $s_invalid_email<p>";
		PRINT "<a href=\"$g_signup_page\">$s_proceed</a>";
		exit;
	}

	# Check for duplicate username
	$query = "SELECT username
		FROM $g_mantis_user_table
		WHERE username='$f_username'";
    $result = db_query( $query );
    if ( db_num_rows( $result ) > 0 ) {
    	PRINT "$f_username $s_duplicate_username<p>";
		PRINT "<a href=\"$g_signup_page\">$s_proceed</a>";
    	exit;
    }

	# Passed our checks.  Insert into DB then send email.

	# Create random password
	$t_password = create_random_password( $f_email );

	# Use a default access level
	# create the almost unique string for each user then insert into the table
	$t_cookie_string = create_cookie_string( $f_email );
	$t_password2 = process_plain_password( $t_password );
    $query = "INSERT
    		INTO $g_mantis_user_table
    		( id, username, email, password, date_created, last_visit,
    		enabled, protected, access_level, login_count, cookie_string )
			VALUES
			( null, '$f_username', '$f_email', '$t_password2', NOW(), NOW(),
			1, 0, $g_default_new_account_access_level, 0, '$t_cookie_string')";
    $result = db_query( $query );
    if ( !$result ) {
    	PRINT "$s_account_create_fail<p>";
		PRINT "<a href=\"$g_signup_page\">$s_proceed</a>";
    	exit;
    }

	# Create preferences for the user
	$t_user_id = db_insert_id();
    $query = "INSERT
    		INTO $g_mantis_user_pref_table
    		(id, user_id, advanced_report, advanced_view, advanced_update,
    		refresh_delay, redirect_delay,
    		email_on_new, email_on_assigned,
    		email_on_feedback, email_on_resolved,
    		email_on_closed, email_on_reopened,
    		email_on_bugnote, email_on_status,
    		email_on_priority, language)
    		VALUES
    		(null, '$t_user_id', '$g_default_advanced_report',
    		'$g_default_advanced_view', '$g_default_advanced_update',
    		'$g_default_refresh_delay', '$g_default_redirect_delay',
    		'$g_default_email_on_new', '$g_default_email_on_assigned',
    		'$g_default_email_on_feedback', '$g_default_email_on_resolved',
    		'$g_default_email_on_closed', '$g_default_email_on_reopened',
    		'$g_default_email_on_bugnote', '$g_default_email_on_status',
    		'$g_default_email_on_priority', '$g_default_language')";
    $result = db_query($query);

	# Send notification email
	email_signup( $t_user_id, $t_password );
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>


<p>
<div align="center">
<?
	if ( $result ) {						# SUCCESS
		PRINT "[$f_username - $f_email] $s_password_emailed_msg<p>$s_no_reponse_msg<p>";
	} else {								# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_login_page, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>