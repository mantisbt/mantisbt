<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Core HTML API                                                         ###
	###########################################################################

	# These functions control the display of each page
	# I've numbered the functions in the order they should appear

	### --------------------
	# (1) this is the first text sent by the page
	function print_html_top() {
		PRINT "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
		PRINT "<html>";
	}
	### --------------------
	# (2)
	function print_head_top() {
	   PRINT "<head>";
	}
	### --------------------
	# (3)
	function print_title( $p_title ) {
	   PRINT "<title>$p_title</title>";
	}
	### --------------------
	# (4) the css include file to use, is likely to be either empty or css_inc.php
	function print_css( $p_css="" ) {
		if ( !empty($p_css )) {
			include( "$p_css" );
		}
	}
	### --------------------
	# (5) OPTIONAL: for pages that require a redirect
	# The time field is the number of seconds to wait before redirecting
	function print_meta_redirect( $p_url, $p_time ) {
	   PRINT "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\">";
	}
	### --------------------
	# (6)
	function print_head_bottom() {
	   PRINT "</head>";
	}
	### --------------------
	# (7)
	function print_body_top() {
		PRINT "<body>";
	}
	### --------------------
	# (8) This prints the title that is visible in the main panel of the browser
	function print_header( $p_title="Mantis" ) {
		global 	$g_show_project_in_title,
				$g_project_cookie_val,
				$g_mantis_project_table;

		if ( $g_show_project_in_title==1 ) {
			$query = "SELECT name
					FROM $g_mantis_project_table
					WHERE id='$g_project_cookie_val'";
			$result = db_query( $query );
			$t_project_name = db_result( $result, 0, 0 );

			if ( empty( $t_project_name ) ) {
				PRINT "<h3>$p_title</h3>";
			} else {
				PRINT "<h3>$p_title - $t_project_name</h3>";
			}
		} else if ( $g_show_project_in_title==2 ) {
			$query = "SELECT name
					FROM $g_mantis_project_table
					WHERE id='$g_project_cookie_val'";
			$result = db_query( $query );
			$t_project_name = db_result( $result, 0, 0 );

			PRINT "<h3>$t_project_name</h3>";
		} else {
			PRINT "<h3>$p_title</h3>";
		}
	}
	### --------------------
	# (9) $p_page is included.  This allows for the admin to have a nice baner or
	# graphic at the top of every page
	function print_top_page( $p_page ) {
		if (( file_exists( $p_page ) )&&( !is_dir( $p_page ) )) {
			include( $p_page );
		}
	}
	### --------------------
	# (10) $p_page is included.  This allows for the admin to have a nice baner or
	# graphic at the bottom of every page
	function print_bottom_page( $p_page ) {
		if (( file_exists( $p_page ) )&&( !is_dir( $p_page ) )) {
			include( $p_page );
		}
	}
	### --------------------
	# (11) Prints the bottom of page information
	function print_footer( $p_file ) {
		global 	$g_string_cookie_val, $g_webmaster_email, $g_show_source,
				$g_menu_include_file, $g_show_footer_menu;

		if (isset($g_string_cookie_val)) {
			if ( $g_show_footer_menu ) {
				print_bottom_menu( $g_menu_include_file );
			}
		}

		print_source_link( $p_file );

		PRINT "<p>";
		PRINT "<hr size=\"1\">";
		print_mantis_version();
		PRINT "<address>Copyright (C) 2000, 2001</address>";
		PRINT "<address><a href=\"mailto:$g_webmaster_email\">$g_webmaster_email</a></address>";
	}
	### --------------------
	# (12)
	function print_body_bottom() {
		PRINT "</body>";
	}
	### --------------------
	# (13)  The very last text that is sent in a html page
	function print_html_bottom() {
		PRINT "</html>";
	}
	### --------------------
	###########################################################################
	# HTML Appearance Helper API
	###########################################################################
	### --------------------
	# prints the user that is logged in and the date/time
	# it also creates the form where users can switch projects
	# this is used by print_menu()
	function print_login_info() {
		global 	$g_mantis_user_table,
				$g_string_cookie_val, $g_project_cookie_val,
				$g_complete_date_format, $g_set_project,
				$s_switch, $s_logged_in_as;

		$t_username = get_current_user_field( "username" );
		$t_now = date($g_complete_date_format);

		PRINT "<table width=\"100%\">";
		PRINT "<form method=post action=$g_set_project>";
		PRINT "<tr>";
			PRINT "<td align=left width=\"33%\">";
				PRINT "$s_logged_in_as: <i>$t_username</i>";
			PRINT "</td>";
			PRINT "<td align=\"center\" width=\"34%\">";
				PRINT "<i>$t_now</i>";
			PRINT "</td>";
			PRINT "<td align=\"right\" width=\"33%\">";
				PRINT "<select name=f_project_id>";
					print_project_option_list( $g_project_cookie_val );
				PRINT "</select>";
				PRINT "<input type=\"submit\" value=\"$s_switch\">";
			PRINT "</td>";
		PRINT "</tr>";
		PRINT "</form>";
		PRINT "</table>";

	}
	### --------------------
	# This prints the little [?] link for user help
	# The $p_a_name is a link into the documentation.html file
	function print_documentaion_link( $p_a_name="" ) {
		global $g_documentation_html;

		PRINT "<a href=\"$g_documentation_html#$p_a_name\" target=_info>[?]</a>";
	}
	### --------------------
	# checks to see whether we need to be displaying the source link
	# WARNING: displaying source (and the ability to do so) can be a security risk
	# used in print_footer()
	function print_source_link( $p_file ) {
		global $g_show_source, $g_show_source_page, $g_string_cookie_val;

		if (!isset($g_string_cookie_val)) {
			return;
		}

		if (( $g_show_source==1 )&&
			( access_level_check_greater_or_equal( ADMINISTRATOR ) )) {
				PRINT "<p>";
				PRINT "<div align=\"center\">";
				PRINT "<a href=\"$g_show_source_page?f_url=$p_file\">Show Source</a>";
				PRINT "</div>";
		}
	}
	### --------------------
	# checks to see whether we need to be displaying the version number
	# used in print_footer()
	function print_mantis_version() {
		global $g_mantis_version, $g_show_version;

		if ( $g_show_version==1 ) {
			PRINT "<i>Mantis $g_mantis_version</i>";
		}
	}
	### --------------------
	# print the hr
	function print_hr( $p_hr_size, $p_hr_width ) {
		PRINT "<hr size=\"$p_hr_size\" width=\"$p_hr_width%\">";
	}
	### --------------------
	###########################################################################
	# HTML Menu API
	###########################################################################
	### --------------------
	# print the standard command menu at the top of the pages
	# also prints the login info, time, and project select form
	function print_menu( $p_menu_file="" ) {
		global 	$g_primary_border_color, $g_primary_color_light;

		print_login_info();

		PRINT "<table width=\"100%\" bgcolor=\"$g_primary_border_color\">";
		PRINT "<tr align=\"center\">";
			PRINT "<td align=\"center\" bgcolor=\"$g_primary_color_light\">";
				include( $p_menu_file );
			PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
	}
	### --------------------
	# this is the same as print_menu except without the login info
	# this is set by setting $g_show_footer_menu to 1
	function print_bottom_menu( $p_menu_file="" ) {
		global 	$g_primary_border_color, $g_primary_color_light;

		PRINT "<p>";
		PRINT "<table width=\"100%\" bgcolor=\"$g_primary_border_color\">";
		PRINT "<tr align=\"center\">";
			PRINT "<td align=\"center\" bgcolor=\"$g_primary_color_light\">";
				include( $p_menu_file );
			PRINT "</td>";
		PRINT "</tr>";
		PRINT "</table>";
	}
	### --------------------
	# prints the manage menu
	# if the $p_page matches a url then don't make that a link
	function print_manage_menu( $p_page="" ) {
		global 	$g_manage_page,
				$g_manage_create_user_page, $s_create_new_account_link,
				$g_manage_project_menu_page, $s_projects,
				$g_documentation_page, $s_documentation_link;

		switch ( $p_page ) {
		case $g_manage_page: $g_manage_page="";break;
		case $g_manage_project_menu_page: $g_manage_project_menu_page="";break;
		case $g_manage_create_user_page: $g_manage_create_user_page="";break;
		case $g_documentation_page: $g_documentation_page="";break;
		}

		PRINT "<p><div align=\"center\">";
			print_bracket_link( $g_manage_page, "Manage Users" );
			print_bracket_link( $g_manage_project_menu_page, "Manage Projects");
			print_bracket_link( $g_manage_create_user_page, $s_create_new_account_link );
			print_bracket_link( $g_documentation_page, $s_documentation_link );
		PRINT "</div>";
	}
	### --------------------
	# prints the account menu
	# if the $p_page matches a url then don't make that a link
	function print_account_menu( $p_page="" ) {
		global 	$g_account_page, $s_account_link,
				$g_account_profile_menu_page, $s_manage_profiles_link,
				$g_account_prefs_page, $s_change_preferences_link;

		switch ( $p_page ) {
		case $g_account_page: $g_account_page="";break;
		case $g_account_profile_menu_page: $g_account_profile_menu_page="";break;
		case $g_account_prefs_page: $g_account_prefs_page="";break;
		}

		PRINT "<p><div align=\"center\">";
			print_bracket_link( $g_account_page, $s_account_link );
			print_bracket_link( $g_account_profile_menu_page, $s_manage_profiles_link );
			print_bracket_link( $g_account_prefs_page, $s_change_preferences_link );
		PRINT "</div>";
	}
	### --------------------
	# prints the doc menu
	# if the $p_page matches a url then don't make that a link
	function print_doc_menu( $p_page="" ) {
		global	$g_documentation_html, $s_user_documentation,
				$g_proj_doc_page, $s_project_documentation,
				$g_proj_doc_add_page, $s_add_file,
				$g_allow_file_upload;

		switch ( $p_page ) {
		case $g_documentation_html: $g_documentation_html="";break;
		case $g_proj_doc_page: $g_proj_doc_page="";break;
		case $g_proj_doc_add_page: $g_proj_doc_add_page="";break;
		}

		PRINT "<p><div align=\"center\">";
			print_bracket_link( $g_documentation_html, $s_user_documentation );
			print_bracket_link( $g_proj_doc_page, $s_project_documentation );
			if ( $g_allow_file_upload==1 ) {
				print_bracket_link( $g_proj_doc_add_page, $s_add_file );
			}
		PRINT "</div>";
	}
	### --------------------
	# prints the view all bugs
	# if the $p_page matches a url then don't make that a link
	function print_view_all_bugs_menu( $p_page="" ) {
		global 	$g_view_all_bug_page, $s_all_bugs_link,
				$g_view_all_reported_bug_page, $s_reported_bugs_link,
				$g_view_all_assigned_bug_page, $s_assigned_bugs_link,
				$g_view_all_unassign_bug_page, $s_unassigned_bugs_link;

		switch ( $p_page ) {
		case $g_view_all_bug_page: $g_view_all_bug_page="";break;
		case $g_view_all_reported_bug_page: $g_view_all_reported_bug_page="";break;
		case $g_view_all_assigned_bug_page: $g_view_all_assigned_bug_page="";break;
		case $g_view_all_unassign_bug_page: $g_view_all_unassign_bug_page="";break;
		}

		PRINT "<p><div align=\"center\">";
			print_bracket_link( $g_view_all_bug_page, $s_all_bugs_link );
			print_bracket_link( $g_view_all_reported_bug_page, $s_reported_bugs_link );
			print_bracket_link( $g_view_all_assigned_bug_page, $s_assigned_bugs_link );
			print_bracket_link( $g_view_all_unassign_bug_page, $s_unassigned_bugs_link );
		PRINT "</div>";
	}
	### --------------------
	# prints the manage doc menu
	# if the $p_page matches a url then don't make that a link
	function print_manage_doc_menu( $p_page="" ) {
		global	$g_site_settings_page, $s_site_settings_link,
				$g_documentation_page, $s_system_info_link;

		switch ( $p_page ) {
		case $g_documentation_page: $g_documentation_page="";break;
		case $g_site_settings_page: $g_site_settings_page="";break;
		}

		PRINT "<p><div align=\"center\">";
			print_bracket_link( $g_documentation_page, $s_system_info_link );
			print_bracket_link( $g_site_settings_page, $s_site_settings_link );
			print_bracket_link( "ChangeLog", "ChangeLog" );
			print_bracket_link( "README", "README" );
			print_bracket_link( "INSTALL", "INSTALL" );
			print_bracket_link( "UPGRADING", "UPGRADING" );
			print_bracket_link( "CONFIGURATION", "CONFIGURATION" );
		PRINT "</div>";
	}
	### --------------------
	# prints the signup link
	function print_signup_link() {
		global $g_allow_signup, $g_signup_page, $s_signup_link;

		if ( $g_allow_signup != 0 ) {
			PRINT "<p><div align=\"center\">";
			print_bracket_link( $g_signup_page, $s_signup_link );
			PRINT "</div>";
		}
	}
	### --------------------
	###########################################################################
	### END                                                                 ###
	###########################################################################
?>