<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( ADMINISTRATOR );

	# Either generate a random password and email it if emailing is enabled.
	# Otherwise make a blank one.

	# Go with random password and email it to the user
    if ( OFF == $f_protected ) {
		if ( ON == $g_allow_signup ) {
			# Create random password
			$t_password = create_random_password( $f_email );

			# create the almost unique string for each user then insert into the table
			$t_password2 = process_plain_password( $t_password );
		    $query = "UPDATE $g_mantis_user_table
		    		SET password='$t_password2'
		    		WHERE id='$f_id'";
		    $result = db_query( $query );

			# Send notification email
			email_reset( $f_id, $t_password );
		} else { # use blank password, no emailing
			switch ( $g_login_method ) {
				case CRYPT: $t_password = "4nPtPLdAFdoxA";
							break;
				case PLAIN: $t_password = "";
							break;
				case MD5:	$t_password = "d41d8cd98f00b204e9800998ecf8427e";
							break;
				default:	$t_password = "";
			}
			# password is blank password
		    $query = "UPDATE $g_mantis_user_table
		    		SET password='$t_password'
		    		WHERE id='$f_id'";
		    $result = db_query( $query );
		}
	}

	$t_redirect_url = $g_manage_page;
?>
<? print_page_top1() ?>
<?
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<? print_page_top2() ?>

<p>
<div align="center">
<?
	if ( ON == $f_protected ) {				# PROTECTED
		PRINT "$s_account_reset_protected_msg<p>";
	} else if ( $result ) {					# SUCCESS
		if ( ON == $g_allow_signup ) {
			PRINT "$s_account_reset_msg<p>";
		} else {
			PRINT "$s_account_reset_msg2<p>";
		}
	} else {								# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, $s_proceed );
?>
</div>

<? print_page_bot1( __FILE__ ) ?>