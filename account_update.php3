<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### This page updates a user's information
	### If an account is protected then changes are forbidden
	### The page gets redirected back to account_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	$f_id 			= get_current_user_field( "id" );
	$f_protected 	= get_current_user_field( "protected" );

	### If an account is protected then no one can change the information
	### This is useful for shared accounts or for demo purposes
	$result = 0;
	if ( $f_protected==0 ) {

		### Update everything except password
	    $query = "UPDATE $g_mantis_user_table
	    		SET username='$f_username', email='$f_email'
	    		WHERE id='$f_id'";
		$result = db_query( $query );

		### Update password if the two match and are not empty
		if (( !empty( $f_password ) )&&( $f_password==$f_password_confirm )) {
			$t_password = process_plain_password( $f_password );
			$query = "UPDATE $g_mantis_user_table
					SET password='$t_password'
					WHERE id='$f_id'";
			$result = db_query( $query );
		}
	} ### end if protected
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
<? print_top_page( $g_top_include_page ) ?>

<? print_menu( $g_menu_include_file ) ?>

<p>
<div align="center">
<?
	if ( $f_protected==1 ) {				### PROTECTED
		PRINT "$s_account_protected_msg<p>";
	} else if ( $result ) {					### SUCCESS
		PRINT "$s_account_updated_msg<p>";
	} else {								### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_account_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>