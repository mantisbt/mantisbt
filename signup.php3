<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( $g_allow_signup == "0" ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

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
	$t_password = create_random_password( $p_email );

	### create the almost unique string for each user then insert into the table
	$t_cookie_string = create_cookie_string( $f_email );
	$t_password2 = crypt( $t_password );
    $query = "INSERT
    		INTO $g_mantis_user_table
    		( id, username, email, password, date_created, last_visit,
    		access_level, enabled, protected, cookie_string )
			VALUES
			( null, '$f_username', '$f_email', '$t_password2', NOW(), NOW(),
			'reporter', 'on', '', '$t_cookie_string')";
    $result = db_query( $query );
    if ( !$result ) {
    	PRINT "$s_account_create_fail<p>";
		PRINT "<a href=\"$g_signup_page\">$s_proceed</a>";
    	exit;
    }

   	### Use this for MS SQL: SELECT @@IDENTITY AS 'id'
	$query = "select LAST_INSERT_ID()";
	$result = db_query( $query );
	if ( $result ) {
		$t_user_id = db_result( $result, 0, 0 );
	}

	### Create user profile
	$query = "INSERT INTO $g_mantis_user_profile_table
    		( id, user_id, platform, os, os_build, description, default_profile )
    		VALUES
			( null, '$t_user_id', '$f_platform', '$f_os', '$f_os_build', '$f_description', '' )";
    $result = db_query( $query );

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
<div align=center>
<?
	### SUCCESS
	if ( $result ) {
		PRINT "$f_username - $f_email was successfully added.<p>Wait a few minutes and check your email for your password.  If you do not respond within a week your account may be deleted.";
	}
	### FAILURE
	else {
		PRINT "$s_sql_error_detected <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
	}
?>
<p>
<a href="<? echo $g_login_page ?>"><? echo $s_proceed ?></a>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>