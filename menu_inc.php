<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details
?>
<?
	if ( !isset( $g_string_cookie_val ) ) {
		### required for variables to get picked up
		global 	$g_string_cookie_val, $g_path,
				$g_mantis_user_table, $g_mantis_user_pref_table,
				$g_hostname, $g_db_username, $g_db_password, $g_database_name,

				$g_main_page, $g_view_bug_all_page,
				$g_report_bug_page, $g_report_bug_advanced_page,
				$g_summary_page, $g_account_page, $g_manage_page,
				$g_news_menu_page, $g_usage_doc_page, $g_logout_page,

				$s_main_link, $s_view_bugs_link, $s_report_bug_link,
				$s_summary_link, $s_account_link, $g_manage_project_menu_page,
				$s_manage_link, $s_edit_news_link, $s_docs_link,
				$s_logout_link;
	}

	db_connect( $g_hostname, $g_db_username, $g_db_password, $g_database_name );

	### grab the access level and protected information for the
	### currently logged in user
    $t_access_level = get_current_user_field( "access_level" );
    $t_protected = get_current_user_field( "protected" );
?>
<div align=center>
	<a href="<? echo $g_path.$g_main_page ?>"><? echo $s_main_link ?></a> |
	<a href="<? echo $g_path.$g_view_bug_all_page ?>"><? echo $s_view_bugs_link ?></a> |
<?
	if ( $t_access_level!="viewer" ) {
		if ( get_user_value( $g_mantis_user_pref_table, "advanced_report" )=="on" ) {
?>
			<a href="<? echo $g_path.$g_report_bug_advanced_page ?>"><? echo $s_report_bug_link ?></a> |
<? 		} else { ?>
			<a href="<? echo $g_path.$g_report_bug_page ?>"><? echo $s_report_bug_link ?></a> |
<?
		}
	}  # end report/viewer if
?>
	<a href="<? echo $g_path.$g_summary_page ?>"><? echo $s_summary_link ?></a> |
	<a href="<? echo $g_path.$g_account_page ?>"><? echo $s_account_link ?></a> |
<? if ( $t_access_level=="administrator" ) { ?>
	<a href="<? echo $g_path.$g_manage_page ?>"><? echo $s_manage_link ?></a> |
	<a href="<? echo $g_path.$g_news_menu_page ?>"><? echo $s_edit_news_link ?></a> |
<? } ?>
	<a href="<? echo $g_path.$g_usage_doc_page ?>"><? echo $s_docs_link ?></a> |
	<a href="<? echo $g_path.$g_logout_page ?>"><? echo $s_logout_link ?></a>
</div>