<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Core HTML API
	###########################################################################

	# These functions control the display of each page
	# I've numbered the functions in the order they should appear

	# --------------------
	# first part of the html followed by meta tags then the second part
	function print_page_top1() {
		global $g_window_title, $g_css_include_file, $g_meta_include_file;

		print_html_top();
		print_head_top();
		print_title( $g_window_title );
		print_css( $g_css_include_file );
		include( $g_meta_include_file );
	}
	# --------------------
	# second part of the html, comes after the meta tags
	function print_page_top2() {
		global $g_page_title, $g_top_include_page;

		print_head_bottom();
		print_body_top();
		print_header( $g_page_title );
		print_top_page( $g_top_include_page );
		print_login_info();
		print_menu();
	}
	# --------------------
	# second part of the html, comes after the meta tags
	function print_page_top2a() {
		global $g_page_title, $g_top_include_page;

		print_head_bottom();
		print_body_top();
		print_header( $g_page_title );
		print_top_page( $g_top_include_page );
	}
	# --------------------
	# comes at the bottom of the html
	# $p_file should always be the __FILE__ variable. This is passed to show source.
	function print_page_bot1( $p_file ) {
		global $g_bottom_include_page;

		print_bottom_page( $g_bottom_include_page );
		print_footer( $p_file );
		print_body_bottom();
		print_html_bottom();
	}
	# --------------------
	# (1) this is the first text sent by the page
	function print_html_top() {
		PRINT "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
		PRINT "<html>";
	}
	# --------------------
	# (2)
	function print_head_top() {
	   PRINT "<head>";
	}
	# --------------------
	# (3)
	function print_title( $p_title ) {
		global 	$g_show_project_in_title,
				$g_project_cookie_val;

		if ( 1 == $g_show_project_in_title ) {
			$t_project_name = get_project_field( $g_project_cookie_val, "name" );

			if ( empty( $t_project_name ) ) {
				PRINT "<title>$p_title</title>";
			} else {
				PRINT "<title>$p_title - $t_project_name</title>";
			}
		} else if ( 2 == $g_show_project_in_title ) {
			$t_project_name = get_project_field( $g_project_cookie_val, "name" );

			PRINT "<title>$t_project_name</title>";
		} else {
			PRINT "<title>$p_title</title>";
		}
	}
	# --------------------
	# (4) the css include file to use, is likely to be either empty or css_inc.php
	function print_css( $p_css="" ) {
		if ( !empty($p_css )) {
			include( "$p_css" );
		}
	}
	# --------------------
	# (5) OPTIONAL: for pages that require a redirect
	# The time field is the number of seconds to wait before redirecting
	function print_meta_redirect( $p_url, $p_time="" ) {
		global $g_wait_time;

		if ( empty( $p_time ) ) {
			$p_time = $g_wait_time;
		}

		PRINT "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\">";
	}
	# --------------------
	# (6)
	function print_head_bottom() {
	   PRINT "</head>";
	}
	# --------------------
	# (7)
	function print_body_top() {
		PRINT "<body>";
	}
	# --------------------
	# (8) This prints the title that is visible in the main panel of the browser
	# We use a temporary vairable to create the title then print it.
	function print_header( $p_title="Mantis" ) {
		global 	$g_show_project_in_title,
				$g_project_cookie_val;

		$t_title = "";
		switch ( $g_show_project_in_title ) {
			case 1:	$t_project_name = get_project_field( $g_project_cookie_val, "name" );
					if ( empty( $t_project_name ) ) {
						$t_title = $p_title;
					} else {
						$t_title = $p_title." - ".$t_project_name;
					}
					break;
			case 2:	$t_title = get_project_field( $g_project_cookie_val, "name" );
					break;
			default:$t_title = $p_title;
					break;
		}

		PRINT "<div align=\"center\"><span class=\"pagetitle\">$t_title</span></div>";
	}
	# --------------------
	# (9) $p_page is included.  This allows for the admin to have a nice baner or
	# graphic at the top of every page
	function print_top_page( $p_page ) {
		if (( !empty( $p_page ) )&&( file_exists( $p_page ) )&&( !is_dir( $p_page ) )) {
			include( $p_page );
		}
	}
	# --------------------
	# (10) $p_page is included.  This allows for the admin to have a nice baner or
	# graphic at the bottom of every page
	function print_bottom_page( $p_page ) {
		if (( !empty( $p_page ) )&&( file_exists( $p_page ) )&&( !is_dir( $p_page ) )) {
			include( $p_page );
		}
	}
	# --------------------
	# (11) Prints the bottom of page information
	function print_footer( $p_file ) {
		global 	$g_string_cookie_val, $g_webmaster_email,
				$g_menu_include_file, $g_show_footer_menu,
				$g_mantis_version, $g_show_version,
				$g_timer, $g_show_timer;

		# @@@
		if (isset($g_string_cookie_val)&&!empty($g_string_cookie_val)) {
			if ( $g_show_footer_menu ) {
				PRINT "<p>";
				print_menu();
			}
		}

		print_source_link( $p_file );

		PRINT "<p>";
		PRINT "<hr size=\"1\">";
		if ( ON == $g_show_version ) {
			PRINT "<span class=\"timer\"><a href=\"http://mantisbt.sourceforge.net/\">Mantis $g_mantis_version</a></span>";
		}
		PRINT "<address>Copyright (C) 2000 - 2002</address>";
		PRINT "<address><a href=\"mailto:$g_webmaster_email\">$g_webmaster_email</a></address>";
		if ( ON == $g_show_timer ) {
			$g_timer->end_time();
			PRINT "<span class=\"italic\">Time: ".$g_timer->elapsed_time()." seconds</span><br />";
		}
	}
	# --------------------
	# (12)
	function print_body_bottom() {
		PRINT "</body>";
	}
	# --------------------
	# (13)  The very last text that is sent in a html page
	function print_html_bottom() {
		PRINT "</html>";
	}
	# --------------------
	###########################################################################
	# HTML Appearance Helper API
	###########################################################################
	# --------------------
	# prints the user that is logged in and the date/time
	# it also creates the form where users can switch projects
	function print_login_info() {
		global 	$g_mantis_user_table,
				$g_string_cookie_val, $g_project_cookie_val,
				$g_complete_date_format, $g_set_project,
				$s_switch, $s_logged_in_as, $s_all_projects,
				$s_access_levels_enum_string;

		$t_username = get_current_user_field( "username" );
		$t_access_level = get_enum_element( "access_levels", get_current_user_access_level() );
		$t_now = date( $g_complete_date_format );

		PRINT "<table class=\"hide\">";
		PRINT "<form method=\"post\" action=\"$g_set_project\">";
		PRINT "<tr>";
			PRINT "<td class=\"login-info-left\">";
				PRINT "$s_logged_in_as: <span class=\"login-username\">$t_username</span> <span class=\"small\">($t_access_level)</span>";
			PRINT "</td>";
			PRINT "<td class=\"login-info-middle\">";
				PRINT "<span class=\"login-time\">$t_now</span>";
			PRINT "</td>";
			PRINT "<td class=\"login-info-right\">";
				PRINT "<select name=\"f_project_id\" class=\"small\">";
					PRINT "<option value=\"0000000\">$s_all_projects</option>";
					print_project_option_list( $g_project_cookie_val );
				PRINT "</select>";
				PRINT "<input type=\"submit\" value=\"$s_switch\" class=\"small\">";
			PRINT "</td>";
		PRINT "</tr>";
		PRINT "</form>";
		PRINT "</table>";
	}
	# --------------------
	# This prints the little [?] link for user help
	# The $p_a_name is a link into the documentation.html file
	function print_documentaion_link( $p_a_name="" ) {
		global $g_documentation_html;

		PRINT "<a href=\"$g_documentation_html#$p_a_name\" target=_info>[?]</a>";
	}
	# --------------------
	# checks to see whether we need to be displaying the source link
	# WARNING: displaying source (and the ability to do so) can be a security risk
	# used in print_footer()
	function print_source_link( $p_file ) {
		global $g_show_source, $g_show_source_page, $g_string_cookie_val;

		if (!isset($g_string_cookie_val)) {
			return;
		}

		if (( ON == $g_show_source )&&
			( access_level_check_greater_or_equal( ADMINISTRATOR ) )) {
				PRINT "<p>";
				PRINT "<div align=\"center\">";
				PRINT "<a href=\"$g_show_source_page?f_url=$p_file\">Show Source</a>";
				PRINT "</div>";
		}
	}
 	# --------------------
	# print the hr
	function print_hr( $p_hr_size, $p_hr_width ) {
		PRINT "<hr size=\"$p_hr_size\" width=\"$p_hr_width%\">";
	}
	# --------------------
	###########################################################################
	# HTML Menu API
	###########################################################################
	# --------------------
	# print the standard command menu at the top of the pages
	# also prints the login info, time, and project select form
	function print_menu() {
		global	$g_string_cookie_val, $g_project_cookie_val,

				$g_show_report,

				$g_main_page, $g_view_all_bug_page,
				$g_report_bug_page, $g_report_bug_advanced_page,
				$g_summary_page, $g_account_page, $g_proj_doc_page, $g_manage_page,
				$g_news_menu_page, $g_usage_doc_page, $g_logout_page,
				$g_proj_user_menu_page, $g_login_select_proj_page,

				$s_main_link, $s_view_bugs_link, $s_report_bug_link,
				$s_summary_link, $s_account_link, $g_manage_project_menu_page,
				$s_manage_link, $s_users_link, $s_edit_news_link, $s_docs_link,
				$s_jump, $g_jump_to_bug,

				$s_logout_link;

		if ( isset( $g_string_cookie_val ) ) {
		    $t_protected = get_current_user_field( "protected" );
			PRINT "<table class=\"width100\" cellspacing=\"0\">";
			PRINT "<tr>";
				PRINT "<td class=\"menu\">";
				PRINT "<a href=\"$g_main_page\">$s_main_link</a> | ";
				PRINT "<a href=\"$g_view_all_bug_page\">$s_view_bugs_link</a> | ";
				if ( access_level_check_greater_or_equal( REPORTER ) ) {
					if ( "0000000" != $g_project_cookie_val ) {
						$t_report_url = get_report_redirect_url( 1 );
						PRINT "<a href=\"$t_report_url\">$s_report_bug_link</a> | ";
					} else {
						PRINT "<a href=\"$g_login_select_proj_page?f_ref=".get_report_redirect_url( 1 )."\">$s_report_bug_link</a> | ";
					}
				}

				PRINT "<a href=\"$g_summary_page\">$s_summary_link</a> | ";

				# only show accounts that are NOT protected
				if ( OFF == $t_protected ) {
					PRINT "<a href=\"$g_account_page\">$s_account_link</a> | ";
				}

				if ( access_level_check_greater_or_equal( MANAGER ) ) {
					if ( "0000000" != $g_project_cookie_val ) {
						PRINT "<a href=\"$g_proj_user_menu_page\">$s_users_link</a> | ";
					} else {
						PRINT "<a href=\"$g_login_select_proj_page\">$s_users_link</a> | ";
					}
				}

				if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
					PRINT "<a href=\"$g_manage_page\">$s_manage_link</a> | ";
				}
				if ( access_level_check_greater_or_equal( MANAGER ) ) {
					if ( "0000000" != $g_project_cookie_val ) {
					PRINT "<a href=\"$g_news_menu_page\">$s_edit_news_link</a> | ";
					} else {
						PRINT "<a href=\"$g_login_select_proj_page\">$s_edit_news_link</a> | ";
					}
				}

				PRINT "<a href=\"$g_proj_doc_page\">$s_docs_link</a> | ";
				PRINT "<a href=\"$g_logout_page\">$s_logout_link</a>";
				PRINT "</td>";
				PRINT "<form method=\"post\" action=\"$g_jump_to_bug\">";
				PRINT "<td nowrap class=\"right\">";
				PRINT "<input type=\"text\" name=\"f_id\" size=\"10\" class=\"small\">&nbsp;";
				PRINT "<input type=\"submit\" value=\"$s_jump\" class=\"small\">&nbsp;";
				PRINT "</td>";
				PRINT "</form>";
			PRINT "</tr>";
			PRINT "</table>";
		}
	}
	# --------------------
	# prints the manage menu
	# if the $p_page matches a url then don't make that a link
	function print_manage_menu( $p_page="" ) {
		global 	$g_manage_page,
				$s_manage_users_link, $s_manage_projects_link,
				$g_manage_create_user_page, $s_create_new_account_link,
				$g_manage_project_menu_page, $s_projects,
				$g_documentation_page, $s_documentation_link;

		$t_manage_page 				= $g_manage_page;
		$t_manage_project_menu_page = $g_manage_project_menu_page;
		$t_manage_create_user_page 	= $g_manage_create_user_page;
		$t_documentation_page 		= $g_documentation_page;

		switch ( $p_page ) {
			case $t_manage_page				: $t_manage_page 				= ""; break;
			case $t_manage_project_menu_page: $t_manage_project_menu_page 	= ""; break;
			case $t_manage_create_user_page	: $t_manage_create_user_page 	= ""; break;
			case $t_documentation_page		: $t_documentation_page 		= ""; break;
		}

		PRINT "<p><div align=\"center\">";
			print_bracket_link( $t_manage_page, $s_manage_users_link );
			print_bracket_link( $t_manage_project_menu_page, $s_manage_projects_link );
			print_bracket_link( $t_manage_create_user_page, $s_create_new_account_link );
			print_bracket_link( $t_documentation_page, $s_documentation_link );
		PRINT "</div>";
	}
	# --------------------
	# prints the account menu
	# if the $p_page matches a url then don't make that a link
	function print_account_menu( $p_page="" ) {
		global 	$g_account_page, $s_account_link,
				$g_account_profile_menu_page, $s_manage_profiles_link,
				$g_account_prefs_page, $s_change_preferences_link;

		$t_account_page 				= $g_account_page;
		$t_account_prefs_page 			= $g_account_prefs_page;
		$t_account_profile_menu_page 	= $g_account_profile_menu_page;

		switch ( $p_page ) {
			case $t_account_page				: $t_account_page 				= ""; break;
			case $t_account_prefs_page			: $t_account_prefs_page 		= ""; break;
			case $t_account_profile_menu_page	: $t_account_profile_menu_page 	= ""; break;
		}

		print_bracket_link( $t_account_page, $s_account_link );
		print_bracket_link( $t_account_prefs_page, $s_change_preferences_link );
		print_bracket_link( $t_account_profile_menu_page, $s_manage_profiles_link );
	}
	# --------------------
	# prints the doc menu
	# if the $p_page matches a url then don't make that a link
	function print_doc_menu( $p_page="" ) {
		global	$g_documentation_html, $s_user_documentation,
				$g_proj_doc_page, $s_project_documentation,
				$g_proj_doc_add_page, $s_add_file,
				$g_allow_file_upload;

		$t_documentation_html 	= $g_documentation_html;
		$t_proj_doc_page 		= $g_proj_doc_page;
		$t_proj_doc_add_page 	= $g_proj_doc_add_page;

		switch ( $p_page ) {
			case $t_documentation_html	: $t_documentation_html	= ""; break;
			case $t_proj_doc_page		: $t_proj_doc_page		= ""; break;
			case $t_proj_doc_add_page	: $t_proj_doc_add_page	= ""; break;
		}

		print_bracket_link( $t_documentation_html, $s_user_documentation );
		print_bracket_link( $t_proj_doc_page, $s_project_documentation );
		if ( ( ON == $g_allow_file_upload )&&( access_level_check_greater_or_equal( MANAGER ) ) ) {
			print_bracket_link( $t_proj_doc_add_page, $s_add_file );
		}
	}
	# --------------------
	# prints the manage doc menu
	# if the $p_page matches a url then don't make that a link
	function print_manage_doc_menu( $p_page="" ) {
		global	$g_documentation_page, $s_system_info_link, $g_path;

		$g_path = $g_path."doc/";
		$t_documentation_page = $g_documentation_page;

		switch ( $p_page ) {
			case $t_documentation_page: $t_documentation_page = ""; break;
		}

		PRINT "<p><div align=\"center\">";
			print_bracket_link( $t_documentation_page, $s_system_info_link );
			print_bracket_link( $g_path."ChangeLog", "ChangeLog" );
			print_bracket_link( $g_path."README", "README" );
			print_bracket_link( $g_path."INSTALL", "INSTALL" );
			print_bracket_link( $g_path."UPGRADING", "UPGRADING" );
			print_bracket_link( $g_path."CUSTOMIZATION", "CUSTOMIZATION" );
		PRINT "</div>";
	}
	# --------------------
	# prints the summary menu
	function print_summary_menu( $p_page="" ) {
		global	$g_summary_page, $s_summary_link,
				$g_use_jpgraph, $s_summary_jpgraph_link,
				$g_print_all_bug_page, $s_print_all_bug_page_link,
				$g_summary_jpgraph_page, $s_print_reports;

		PRINT "<p><div align=\"center\">";
		print_bracket_link( $g_print_all_bug_page, $s_print_all_bug_page_link );

        if ( $g_use_jpgraph != 0 ) {
			$t_summary_page 		= $g_summary_page;
			$t_summary_jpgraph_page = $g_summary_jpgraph_page;

			switch ( $p_page ) {
				case $t_summary_page		: $t_summary_page			= ""; break;
				case $t_summary_jpgraph_page: $t_summary_jpgraph_page	= ""; break;
			}

			print_bracket_link( $t_summary_page, $s_summary_link );
			print_bracket_link( $t_summary_jpgraph_page, $s_summary_jpgraph_link );
		}
		PRINT "</div>";
	}
	# --------------------
	# prints the signup link
	function print_signup_link() {
		global $g_allow_signup, $g_signup_page, $s_signup_link;

		if ( $g_allow_signup != 0 ) {
			PRINT "<p><div align=\"center\">";
			print_bracket_link( $g_signup_page, $s_signup_link );
			PRINT "</div>";
		}
	}
	# --------------------
	function print_proceed( $p_result, $p_query, $p_link ) {
		global $s_operation_successful, $s_proceed;

		PRINT "<p>";
		PRINT "<div align=\"center\">";
		if ( $p_result ) {						# SUCCESS
			PRINT "$s_operation_successful<p>";
		} else {								# FAILURE
			print_sql_error( $p_query );
		}
		print_bracket_link( $p_link, $s_proceed );
		PRINT "</div>";
	}
	# --------------------
	# This is our generic error printing function
	# Errors should terminate the script immediately
	function print_mantis_error( $p_error_num=0 ) {
		global $MANTIS_ERROR;

		PRINT "<html><head></head><body>";
		PRINT $MANTIS_ERROR[$p_error_num];
		PRINT "</body></html>";
		exit;
	}
	# --------------------
	# Print the color legend for the colors
	function print_status_colors() {
		global	$g_status_enum_string,
				$g_new_color, $g_feedback_color,
				$g_acknowledged_color, $g_confirmed_color,
				$g_assigned_color, $g_resolved_color,
				$g_closed_color;

		PRINT "<p>";
		PRINT "<table class=\"width100\" cellspacing=\"1\">";
		PRINT "<tr>";
		$t_arr  = explode_enum_string( $g_status_enum_string );
		$enum_count = count( $t_arr );
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$t_val = get_enum_element( "status", $t_s[0] );

			$t_color = "#ffffff";
			$t_color_variable_name = "g_".$t_s[1]."_color";
			if ( isset( $$t_color_variable_name ) ) {
				$t_color = $$t_color_variable_name;
			}
			PRINT "<td class=\"small-caption\" bgcolor=\"$t_color\">$t_val</td>";
		}
		PRINT "</tr>";
		PRINT "</table>";
	}
	# --------------------
?>