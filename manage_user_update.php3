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

	if ( !access_level_check_greater( "administrator" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### update action
	if ( $f_action=="update" ) {
		if ( $f_protected=="on" ) {
		    $query = "UPDATE $g_mantis_user_table
		    		SET username='$f_username', email='$f_email',
		    			protected='$f_protected'
		    		WHERE id='$f_id'";
		}
		else {
		    $query = "UPDATE $g_mantis_user_table
		    		SET username='$f_username', email='$f_email',
		    			access_level='$f_access_level', enabled='$f_enabled',
		    			protected='$f_protected'
		    		WHERE id='$f_id'";
		}

	    $result = mysql_query( $query );
	}
	### password is blank password
	else if ( $f_action=="reset" ) {
	    $query = "UPDATE $g_mantis_user_table
	    		SET password='4nPtPLdAFdoxA'
	    		WHERE id='$f_id'";
	    $result = mysql_query( $query );
	}
	### delete account
	else if ( $f_action=="delete" ) {
	    if ( $f_protected!="on" ) {
	    	$query = "DELETE
	    			FROM $g_mantis_user_table
	    			WHERE id='$f_id'";
	    }
	    $result = mysql_query( $query );
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_manage_page, $g_wait_time );
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
	if ( $result ) {
		PRINT "Account successfully updated...<p>";
		if ( $f_protected=="on" ) {
			PRINT "Account protected. Access level and enabled protected.<p>";
		}
	}
	else {
		PRINT "ERROR DETECTED: Report this sql statement to <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
		echo $query;
	}
?>
<p>
<a href="<? echo $g_manage_page ?>">Click here to proceed</a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>