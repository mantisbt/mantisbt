<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( $f_protected!="on" ) {		### only if not protected
		if ( $f_action=="update" ) {
		    $query = "UPDATE $g_mantis_user_table
		    		SET username='$f_username', email='$f_email'
		    		WHERE id='$f_id'";
			$result = mysql_query( $query );

			### lets change the password
			if ( !empty( $f_password ) ) {
				if ( $f_password==$f_password_confirm ) {
					$t_password = crypt( $f_password );
					$query = "UPDATE $g_mantis_user_table
							SET password='$t_password'
							WHERE id='$f_id'";
					$result = mysql_query( $query );
		    	}
			}
		}
		else if ( $f_action=="delete" ) {
		    $query = "DELETE
		    		FROM $g_mantis_user_table
		    		WHERE id='$f_id'";
		    $result = mysql_query( $query );
		    header( "Location: $g_logout_page" );
		}
		else {
			echo "ERROR: INVALID ACTION";
		}
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_account_page, $g_wait_time );
	}
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
<?
	### SUCCESS
	if ( $result ) {
		PRINT "Your account has been successfully updated...<p>";
		if ( $f_protected=="on" ) {
			PRINT "Account protected. Cannot change some settings.<p>";
		}
	}
	### FAILURE
	else {
		PRINT "ERROR!!!  An error has occured.  Email the <a href=\"mailto:$g_administrator_email\">administrator</a> with this query:<p>";
		echo $query;
	}
?>
<p>
<a href="<? echo $g_account_page ?>">Click here to proceed</a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>