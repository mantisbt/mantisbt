<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?
	### This file adds a new profile and redirects to account_proj_menu_page.php3
?>
<? include( "core_API.php" ) ?>
<? login_cookie_check() ?>
<?
	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );
	check_access( REPORTER );

	### " character poses problem when editting so let's just convert them
	$f_platform		= string_prepare_text( $f_platform );
	$f_os			= string_prepare_text( $f_os );
	$f_os_build		= string_prepare_text( $f_os_build );
	$f_description	= string_prepare_textarea( $f_description );

	### get user id
	$t_user_id = get_current_user_field( "id" );

	### Add profile
	$query = "INSERT
			INTO $g_mantis_user_profile_table
    		( id, user_id, platform, os, os_build, description )
			VALUES
			( null, '$t_user_id', '$f_platform', '$f_os', '$f_os_build', '$f_description' )";
    $result = db_query( $query );
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	if ( $result ) {
		print_meta_redirect( $g_account_profile_menu_page, $g_wait_time );
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
	if ( $result ) {					### SUCCESS
		PRINT "$s_profile_added_msg<p>";
	} else {							### FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $g_account_profile_menu_page, $s_proceed );
?>
</div>

<? print_bottom_page( $g_bottom_include_page ) ?>
<? print_footer(__FILE__) ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>