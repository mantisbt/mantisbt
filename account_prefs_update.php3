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

	### Some sensible defaults
	if ( empty( $f_limit_view ) ) {
		$f_limit_view = $g_default_limit_view;
	}
	if ( empty( $f_show_last ) ) {
		$f_show_last = $g_default_show_last;
	}

	if ( $f_action="update" ) {
		## update preferences
		$query = "UPDATE $g_mantis_user_pref_table
				SET advanced_report='$f_advanced_report',
					advanced_view='$f_advanced_view'
				WHERE id='$f_id'";
		$result = mysql_query( $query );
	}
	else if ( $f_action="reset" ) {
		## reset to defaults
		$query = "UPDATE $g_mantis_user_pref_table
				SET advanced_report='$g_default_advanced_report',
					advanced_view='$g_default_advanced_view'
				WHERE id='$f_id'";
		$result = mysql_query( $query );
	}
	else {
		echo "ERROR: invalid action";
	}
?>
<? print_html_top() ?>
<? print_head_top() ?>
<? print_title( $g_window_title ) ?>
<? print_css( $g_css_include_file ) ?>
<?
	print_meta_redirect( $g_account_prefs_page, $g_wait_time );
?>
<? include( $g_meta_include_file ) ?>
<? print_head_bottom() ?>
<? print_body_top() ?>
<? print_header( $g_page_title ) ?>
<p>
<? print_menu( $g_menu_include_file ) ?>

<p>
<div align=center>
Preferences have been successfully updated...
<p>
<a href="<? echo $g_account_prefs_page ?>">Click here to proceed</a>
</div>

<? print_footer() ?>
<? print_body_bottom() ?>
<? print_html_bottom() ?>