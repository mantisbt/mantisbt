<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### Check to see if signup is allowed
	if ( $g_allow_signup == 0 ) {
		header( "Status: 302 moved" );
		header( "Location: $g_login_page" );
		exit;
	}

	### Check for a properly formatted email with valid MX record
	$result = 0;
	if ( !is_valid_email( $f_email ) ) {
		PRINT "$f_email $s_invalid_email<p>";
		PRINT "<a href=\"$g_signup_page\">$s_proceed</a>";
		exit;
	}

	### Check for duplicate username
	$query = "SELECT username
		FROM $g_mantis_user_table
		WHERE username='$f_username'";
    $result = db_query( $query );
    if ( db_num_rows( $result ) > 0 ) {
    	PRINT "$f_username $s_duplicate_username<p>";
		PRINT "<a href=\"$g_signup_page\">$s_proceed</a>";
    	exit;
    }

	### Passed our checks.  Insert into DB then send email.

	### Create random password
	$t_password = create_random_password( $f_email );

	### Use a default access level
	### create the almost unique string for each user then insert into the table
	$t_cookie_string = create_cookie_string( $f_email );
	$t_password2 = crypt( $t_password );
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

	### Create preferences for the user
	$t_user_id = db_insert_id();
    $query = "INSERT
    		INTO $g_mantis_user_pref_table
    		(id, user_id, advanced_report, advanced_view)
    		VALUES
    		(null, '$t_user_id',
    		$g_default_advanced_report, $g_default_advanced_view)";
    $result = db_query($query);

	### Send notification email
	email_signup( $t_user_id, $t_password );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<? print_top_page( $g_top_include_page ) ?>

<p>
<div align="center">
<?
	if ( $result ) {						### SUCCESS
		PRINT "[$f_username - $f_email] $$s_password_emailed_msg<p>$s_no_reponse_msg<p>";
	} else {								### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_login_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>