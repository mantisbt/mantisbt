<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	if ( !isset( $g_string_cookie_val ) ) {
		### required for variables to get picked up
		global 	$g_string_cookie_val, $g_mantis_user_table, $g_path,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,
				$g_main_page, $g_bug_view_all_page, $g_report_bug_page,
				$g_summary_page, $g_account_page, $g_manage_page,
				$g_news_menu_page, $g_logout_page;
	}

	db_mysql_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### grab the access level and protected information for the
	### currently logged in user
    $query = "SELECT access_level, protected
    		FROM $g_mantis_user_table
			WHERE cookie_string='$g_string_cookie_val'";
    $result = mysql_query($query);
    $t_access_level = mysql_result( $result, 0, 0 );
    $t_protected = mysql_result( $result, 0, 1 );
?>
<div align=center>
<font face=Verdana size=-1>
	<a href="<? echo $g_path.$g_main_page ?>">Main</a> |
	<a href="<? echo $g_path.$g_bug_view_all_page ?>">View Bugs</a> |
<? if ( $t_access_level!="viewer" ) { ?>
	<a href="<? echo $g_path.$g_report_bug_page ?>">Report Bug</a> |
<? } ?>
	<a href="<? echo $g_path.$g_summary_page ?>">Summary</a> |
<? if ( $t_protected!="on" ) { ?>
	<a href="<? echo $g_path.$g_account_page ?>">Account</a> |
<? } ?>
<? if ( $t_access_level=="administrator" ) { ?>
	<a href="<? echo $g_path.$g_manage_page ?>">Manage</a> |
	<a href="<? echo $g_path.$g_news_menu_page ?>">Edit News</a> |
<? } ?>
	<a href="<? echo $g_path.$g_logout_page ?>">Logout</a>
</font>
</div>