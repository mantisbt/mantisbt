<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( ADMINISTRATOR );
	$c_id = (integer)$f_id;
	$f_protected = user_get_field( $f_id, 'protected' );

	# Either generate a random password and email it if emailing is enabled.
	# Otherwise make a blank one.

	# Go with random password and email it to the user
    if ( OFF == $f_protected ) {
		if ( ON == $g_send_reset_password ) {
			# Create random password
			$t_password = auth_generate_random_password( $f_email );

			# create the almost unique string for each user then insert into the table
			$t_password2 = auth_process_plain_password( $t_password );
		    $query = "UPDATE $g_mantis_user_table
		    		SET password='$t_password2'
		    		WHERE id='$c_id'";
		    $result = db_query( $query );

			# Send notification email
			email_reset( $f_id, $t_password );
		} else { # use blank password, no emailing
			switch ( $g_login_method ) {
				case CRYPT: $t_password = '4nPtPLdAFdoxA';
							break;
				case PLAIN: $t_password = '';
							break;
				case MD5:	$t_password = 'd41d8cd98f00b204e9800998ecf8427e';
							break;
				default:	$t_password = '';
			}
			# password is blank password
		    $query = "UPDATE $g_mantis_user_table
		    		SET password='$t_password'
		    		WHERE id='$c_id'";
		    $result = db_query( $query );
		}
	}

	$t_redirect_url = 'manage_page.php';
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	if ( ON == $f_protected ) {				# PROTECTED
		echo lang_get( 'account_reset_protected_msg' ).'<br />';
	} else if ( $result ) {					# SUCCESS
		if ( ON == $g_send_reset_password ) {
			echo lang_get( 'account_reset_msg' ).'<br />';
		} else {
			echo lang_get( 'account_reset_msg2' ).'<br />';
		}
	} else {								# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
