<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php include( "core_API.php" ) ?>
<?php
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	# Check to see if signup is allowed
	if ( OFF == $g_allow_signup ) {
		print_header_redirect( $g_login_page );
		exit;
	}

	# check for empty username
	$f_username = trim( $f_username );
	if ( empty( $f_username ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}
  $f_username = addslashes($f_username);

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
	if ( !signup_user( $f_username, $f_email ) ) {
		PRINT "$s_account_create_fail<p>";
		PRINT "<a href=\"$g_signup_page\">$s_proceed</a>";
		exit;
	}
?>
<?php print_page_top1() ?>
<?php
	print_head_bottom();
	print_body_top();
	print_header( $g_page_title );
	print_top_page( $g_top_include_page );
?>

<p>
<div align="center">
<?php
	if ( $result ) {						# SUCCESS
		PRINT "[$f_username - $f_email] $s_password_emailed_msg<p>$s_no_reponse_msg<p>";
	} else {								# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_login_page, $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>