<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( 'core_API.php' ) ?>
<?php login_cookie_check() ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	# check for empty username
	$f_username = trim( $f_username );
	if ( empty( $f_username ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}
	$c_username = addslashes($f_username);

	# Check for duplicate username
	$query = "SELECT username
		FROM $g_mantis_user_table
		WHERE username='$c_username'";
    $result = db_query( $query );
    if ( db_num_rows( $result ) > 0 ) {
    	PRINT "$f_username $s_duplicate_username<p>";
		PRINT "<a href=\"manage_create_user_page.php\">$s_proceed</a>";
    	exit;
    }

	if ( $f_password != $f_password_verify ) {
		echo 'ERROR: passwords do not match';
		exit;
	}

	if ( !isset( $f_protected ) ) {
		$c_protected = 0;
	} else {
		$c_protected = 1;
	}

	if ( !isset( $f_enabled ) ) {
		$c_enabled = 0;
	} else {
		$c_enabled = 1;
	}

	# create the almost unique string for each user then insert into the table
	$t_cookie_string	= create_cookie_string();
	$t_password			= process_plain_password( $f_password );
	$c_email			= addslashes($f_email);
	$c_access_level		= (integer)$f_access_level;

    $query = "INSERT
    		INTO $g_mantis_user_table
    		( id, username, email, password, date_created, last_visit,
    		access_level, enabled, protected, cookie_string )
			VALUES
			( null, '$c_username', '$c_email', '$t_password', NOW(), NOW(),
			'$c_access_level', '$c_enabled', '$c_protected', '$t_cookie_string')";
    $result = db_query( $query );

   	# Use this for MS SQL: SELECT @@IDENTITY AS 'id'
	$t_user_id = db_insert_id();

	# Create preferences

    $query = "INSERT
    		INTO $g_mantis_user_pref_table
    		(id, user_id, project_id,
    		advanced_report, advanced_view, advanced_update,
    		refresh_delay, redirect_delay,
    		email_on_new, email_on_assigned,
    		email_on_feedback, email_on_resolved,
    		email_on_closed, email_on_reopened,
    		email_on_bugnote, email_on_status,
    		email_on_priority, language)
    		VALUES
    		(null, '$t_user_id', '0000000',
    		'$g_default_advanced_report', '$g_default_advanced_view', '$g_default_advanced_update',
    		'$g_default_refresh_delay', '$g_default_redirect_delay',
    		'$g_default_email_on_new', '$g_default_email_on_assigned',
    		'$g_default_email_on_feedback', '$g_default_email_on_resolved',
    		'$g_default_email_on_closed', '$g_default_email_on_reopened',
    		'$g_default_email_on_bugnote', '$g_default_email_on_status',
    		'$g_default_email_on_priority', '$g_default_language')";
    $result = db_query($query);

    $t_redirect_url = 'manage_page.php';
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	if ( $result ) {				# SUCCESS
		$f_access_level = get_enum_element( 'access_levels', $f_access_level );
		PRINT "$s_created_user_part1 <span class=\"bold\">$f_username</span> $s_created_user_part2 <span class=\"bold\">$f_access_level</span><p>";
	} else {						# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>