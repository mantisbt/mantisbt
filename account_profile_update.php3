<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	if ( !access_level_check_greater( "reporter" ) ) {
		### need to replace with access error page
		header( "Location: $g_logout_page" );
		exit;
	}

	### " character poses problem when editting so let's just convert them
	$f_platform		= string_safe( str_replace( "\"", "'", $f_platform ) );
	$f_os			= string_safe( str_replace( "\"", "'", $f_os ) );
	$f_os_build		= string_safe( str_replace( "\"", "'", $f_os_build ) );
	$f_description	= string_safe( $f_description );

	### Add item
	$query = "UPDATE $g_mantis_user_profile_table
    		SET platform='$f_platform', os='$f_os',
    			os_build='$f_os_build', description='$f_description'
    		WHERE id='$f_id'";
    $result = mysql_query( $query );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_account_profile_manage_page, $g_wait_time );
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
		PRINT "Profile updated...<p>";
	}
	### FAILURE
	else {
		PRINT "ERROR DETECTED: Report this sql statement to <a href=\"<? echo $g_administrator_email ?>\">administrator</a><p>";
	}
?>
<p>
<a href="<? echo $g_account_profile_manage_page ?>">Click here to proceed</a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>