<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: html_api.php,v 1.25 2002-12-10 21:02:56 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# HTML API
	#
	# These functions control the display of each page
	# I've numbered the functions in the order they should appear
	###########################################################################

	# --------------------
	# first part of the html followed by meta tags then the second part
	function print_page_top1() {
		global $g_window_title, $g_css_include_file, $g_meta_include_file;

		print_html_top();
		print_head_top();
		print_content_type();
		print_title( $g_window_title );
		print_css( $g_css_include_file );
		include( $g_meta_include_file );
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
	# second part of the html, comes after the meta tags
	function print_page_top2() {
		print_page_top2a();
		print_login_info();
		print_menu();
	}
	# --------------------
	# comes at the bottom of the html
	# $p_file should always be the __FILE__ variable. This is passed to show source.
	function print_page_bot1( $p_file = null ) {
		global $g_bottom_include_page;

		if ( null === $p_file ) {
			$p_file = basename( $GLOBALS['PHP_SELF'] );
		}

		print_bottom_page( $g_bottom_include_page );
		print_footer( $p_file );
		print_body_bottom();
		print_html_bottom();
	}
	# --------------------
	# (1) this is the first text sent by the page
	function print_html_top() {
		# @@@ NOTE make this a configurable global.
		#PRINT '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		PRINT '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		#PRINT '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';

		PRINT '<html>';
	}
	# --------------------
	# (2) Opens the <HEAD> section
	function print_head_top() {
	   PRINT '<head>';
	}
	# --------------------
	# (3) Prints the content-type
	function print_content_type() {
		PRINT '<meta http-equiv="Content-type" content="text/html;charset=' . lang_get( 'charset' ) . '" />';
	}

	# --------------------
	# (4) Prints the <TITLE> tag
	function print_title( $p_title ) {
		global 	$g_show_project_in_title,
				$g_project_cookie_val;

		if ( 0 == $g_project_cookie_val ) {
			$t_project_name = lang_get( 'all_projects' );
		} else {
			$t_project_name = project_get_field( $g_project_cookie_val, 'name' );
		}

		if ( 1 == $g_show_project_in_title ) {
			PRINT "<title>$p_title - $t_project_name</title>";
		} else if ( 2 == $g_show_project_in_title ) {
			PRINT "<title>$t_project_name</title>";
		} else {
			PRINT "<title>$p_title</title>";
		}
	}
	# --------------------
	# (5) includes the css include file to use, is likely to be either empty or css_inc.php
	function print_css( $p_css ) {
		PRINT '<link rel="stylesheet" type="text/css" href="'.$p_css.'" />';
		PRINT '<script language="JavaScript" type="text/javascript">';
		PRINT '<!--';
		PRINT 'if(document.layers) {document.write("<style>td{padding:0px;}<\/style>")}';
		PRINT '//-->';
		PRINT '</script>';
	}
	# --------------------
	# (6) OPTIONAL: for pages that require a redirect
	# The time field is the number of seconds to wait before redirecting
	# If we have handled any errors on this page and the 'stop_on_errors' config
	#  option is turned on, return false and don't redirect.
	function print_meta_redirect( $p_url, $p_time=null ) {
		if ( ON == config_get( 'stop_on_errors' ) && error_handled() ) {
			return false;
		}

		if ( null === $p_time ) {
			$p_time = config_get( 'wait_time' );
		}

		echo "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\">";

		return true;
	}
	# --------------------
	# (7) Ends the <HEAD> section
	function print_head_bottom() {
	   PRINT '</head>';
	}
	# --------------------
	# (8) Starts the <BODY> of the page
	function print_body_top() {
		PRINT '<body>';
	}
	# --------------------
	# (9) Prints the title that is visible in the main panel of the browser
	# We use a temporary variable to create the title then print it.
	function print_header( $p_title='Mantis' ) {
		global 	$g_show_project_in_title, $g_project_cookie_val;

		if ( 0 == $g_project_cookie_val ) {
			$t_project_name = lang_get( 'all_projects' );
		} else {
			$t_project_name = project_get_field( $g_project_cookie_val, 'name' );
		}

		$t_title = '';
		switch ( $g_show_project_in_title ) {
			case 1:	$t_title = $p_title.' - '.$t_project_name;
					break;
			case 2:	$t_title = $t_project_name;
					break;
			default:$t_title = $p_title;
					break;
		}

		PRINT "<div class=\"center\"><span class=\"pagetitle\">$t_title</span></div>";
	}
	# --------------------
	# (10) $p_page is included.  This allows for the admin to have a nice banner or
	# graphic at the top of every page
	function print_top_page( $p_page ) {
		if (( !is_blank( $p_page ) )&&( file_exists( $p_page ) )&&( !is_dir( $p_page ) )) {
			include( $p_page );
		}
	}
	# --------------------
	# (11) $p_page is included.  This allows for the admin to have a nice baner or
	# graphic at the bottom of every page
	function print_bottom_page( $p_page ) {
		if (( !is_blank( $p_page ) )&&( file_exists( $p_page ) )&&( !is_dir( $p_page ) )) {
			include( $p_page );
		}
	}
	# --------------------
	# (12) Prints the bottom of page information
	function print_footer( $p_file ) {
		global 	$g_string_cookie_val, $g_webmaster_email,
				$g_menu_include_file, $g_show_footer_menu,
				$g_mantis_version, $g_show_version,
				$g_timer, $g_show_timer,
				$g_show_queries_count, $g_show_queries_list, $g_queries_array;

		# @@@
		if (isset($g_string_cookie_val)&&!is_blank($g_string_cookie_val)) {
			if ( $g_show_footer_menu ) {
				PRINT '<br />';
				print_menu();
			}
		}

		print_source_link( $p_file );

		PRINT '<br />';
		PRINT '<hr size="1" />';
		if ( ON == $g_show_version ) {
			PRINT "<span class=\"timer\"><a href=\"http://mantisbt.sourceforge.net/\">Mantis $g_mantis_version</a></span>";
		}
		PRINT '<address>Copyright (C) 2000 - 2002</address>';
		PRINT "<address><a href=\"mailto:$g_webmaster_email\">$g_webmaster_email</a></address>";
		if ( ON == $g_show_timer ) {
			$g_timer->print_times();
		}
		if ( ON == $g_show_queries_count ) {
			$t_count = count( $g_queries_array );
			PRINT "$t_count total queries executed.<br />";
			PRINT count( array_unique ( $g_queries_array ) ) . " unique queries executed.<br />";
			if ( ON == $g_show_queries_list ) {
				PRINT '<table>';
				$t_shown_queries = array();
				for ( $i = 0; $i < $t_count; $i++ ) {
					if ( in_array( $g_queries_array[$i], $t_shown_queries ) ) {
						PRINT '<tr><td style="color: red">'. ($i+1) ."</td><td style=\"color: red\">$g_queries_array[$i]</td></tr>";
					} else {
						array_push( $t_shown_queries, $g_queries_array[$i] );
						PRINT '<tr><td>'. ($i+1) ."</td><td>$g_queries_array[$i]</td></tr>";
					}
				}
				PRINT '</table>';
			}
		}
	}
	# --------------------
	# (13) Ends the <BODY> section.
	function print_body_bottom() {
		PRINT '</body>';
	}
	# --------------------
	# (14) The very last text that is sent in a html page.
	function print_html_bottom() {
		PRINT '</html>';
	}
	# --------------------
	###########################################################################
	# HTML Appearance Helper API
	###########################################################################
	# --------------------
	# prints the user that is logged in and the date/time
	# it also creates the form where users can switch projects
	function print_login_info() {
		global 	$g_string_cookie_val, $g_project_cookie_val,
				$g_complete_date_format, $g_use_javascript;

		$t_username = current_user_get_field( 'username' );
		$t_access_level = get_enum_element( 'access_levels', current_user_get_access_level() );
		$t_now = date( $g_complete_date_format );

		PRINT '<table class="hide">';
		PRINT '<tr>';
			PRINT '<td class="login-info-left">';
				PRINT lang_get( 'logged_in_as' ) . ": <span class=\"italic\">$t_username</span> <span class=\"small\">($t_access_level)</span>";
			PRINT '</td>';
			PRINT '<td class="login-info-middle">';
				PRINT "<span class=\"italic\">$t_now</span>";
			PRINT '</td>';
			PRINT '<td class="login-info-right">';
				PRINT '<form method="post" name="form_set_project" action="set_project.php">';

				if ( ON == $g_use_javascript) { // use javascript auto-submit -SC 2002.Jun.21
					PRINT '<select name="f_project_id" class="small" onchange="document.forms.form_set_project.submit();">';
				} else {
					PRINT '<select name="f_project_id" class="small">';
				}
				print_project_option_list( helper_get_current_project() );
				PRINT '</select>';
				PRINT '<input type="submit" value="' . lang_get( 'switch' ) . '" class="small" />';
				PRINT '</form>';
			PRINT '</td>';
		PRINT '</tr>';
		PRINT '</table>';
	}
	# --------------------
	# This prints the little [?] link for user help
	# The $p_a_name is a link into the documentation.html file
	function print_documentation_link( $p_a_name='' ) {
		PRINT "<a href=\"doc/documentation.html#$p_a_name\" target=\"_info\">[?]</a>";
	}
	# --------------------
	# checks to see whether we need to be displaying the source link
	# WARNING: displaying source (and the ability to do so) can be a security risk
	# used in print_footer()
	function print_source_link( $p_file ) {
		global $g_show_source, $g_string_cookie_val;

		if (!isset($g_string_cookie_val)) {
			return;
		}

		if (( ON == $g_show_source )&&
			( access_level_check_greater_or_equal( ADMINISTRATOR ) )) {
				PRINT '<br />';
				PRINT '<div align="center">';
				PRINT "<a href=\"show_source_page.php?f_url=$p_file\">Show Source</a>";
				PRINT '</div>';
		}
	}
 	# --------------------
	# print the hr
	function print_hr( $p_hr_size=null, $p_hr_width=null ) {
		if ( $p_hr_size === null ) {
			$p_hr_size = config_get( 'hr_size' );
		}
		if ( $p_hr_width === null ) {
			$p_hr_width = config_get( 'hr_width' );
		}
		echo "<hr size=\"$p_hr_size\" width=\"$p_hr_width%\" />";
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
				$g_show_report, $g_view_summary_threshold;

		if ( isset( $g_string_cookie_val ) ) {
			$t_protected = current_user_get_field( 'protected' );
			PRINT '<table class="width100" cellspacing="0">';
			PRINT '<tr>';
				PRINT '<td class="menu">';
				PRINT '<a href="main_page.php">' . lang_get( 'main_link' ) . '</a> | ';
				PRINT '<a href="view_all_bug_page.php">' . lang_get( 'view_bugs_link' ) . '</a> | ';
				if ( access_level_check_greater_or_equal( REPORTER ) ) {
					PRINT string_get_bug_report_link() . ' | ';
				}

				if ( access_level_check_greater_or_equal( $g_view_summary_threshold ) ) {
					PRINT '<a href="summary_page.php">' . lang_get( 'summary_link' ) . '</a> | ';
				}

				# only show accounts that are NOT protected
				if ( OFF == $t_protected ) {
					PRINT '<a href="account_page.php">' . lang_get( 'account_link' ) . '</a> | ';
				}

				if ( access_level_check_greater_or_equal( MANAGER ) ) {
					if ( "0000000" != $g_project_cookie_val ) {
						PRINT '<a href="proj_user_menu_page.php">' . lang_get( 'users_link' ) . '</a> | ';
					} else {
						PRINT '<a href="login_select_proj_page.php?ref=proj_user_menu_page.php">' . lang_get( 'users_link' ) . '</a> | ';
					}
				}

				if ( access_level_check_greater_or_equal( MANAGER ) ) {
					if ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) {
					  $t_link = 'manage_page.php';
					} else {
					  $t_link = 'manage_proj_menu_page.php';
					}
					PRINT "<a href=\"$t_link\">" . lang_get( 'manage_link' ) . '</a> | ';
				}
				if ( access_level_check_greater_or_equal( MANAGER ) ) {
					# Admin can edit news for All Projects (site-wide)
					if ( ( "0000000" != $g_project_cookie_val ) || ( access_level_check_greater_or_equal( ADMINISTRATOR ) ) ) {
						PRINT '<a href="news_menu_page.php">' . lang_get( 'edit_news_link' ) . '</a> | ';
					} else {
						PRINT '<a href="login_select_proj_page.php">' . lang_get( 'edit_news_link' ) . '</a> | ';
					}
				}

				PRINT '<a href="proj_doc_page.php">' . lang_get( 'docs_link' ) . '</a> | ';
				PRINT '<a href="logout_page.php">' . lang_get( 'logout_link' ) . '</a>';
				PRINT '</td>';
				PRINT '<td class="right" style="white-space: nowrap;">';
					PRINT '<form method="post" action="jump_to_bug.php">';
					PRINT "<input type=\"text\" name=\"f_bug_id\" size=\"10\" class=\"small\" />&nbsp;";
					PRINT '<input type="submit" value="' . lang_get( 'jump' ) . '" class="small" />&nbsp;';
					PRINT '</form>';
				PRINT '</td>';
			PRINT '</tr>';
			PRINT '</table>';
		}
	}

	### --------------------
	# prints the links to the graphic pages, in summary_page.php
	function print_menu_graph() {
		global $g_use_jpgraph;

		if ( $g_use_jpgraph != 0 ) {
			PRINT '<a href="summary_page.php"><img src="images/synthese.gif" border="0" align="center" />' . lang_get( 'synthesis_link' ) . '</a> | ';
			PRINT '<a href="summary_graph_imp_status.php"><img src="images/synthgraph.gif" border="0" align="center" />' . lang_get( 'status_link' ) . '</a> | ';
			PRINT '<a href="summary_graph_imp_priority.php"><img src="images/synthgraph.gif" border="0" align="center" />' . lang_get( 'priority_link' ) . '</a> | ';
			PRINT '<a href="summary_graph_imp_severity.php"><img src="images/synthgraph.gif" border="0" align="center" />' . lang_get( 'severity_link' ) . '</a> | ';
			PRINT '<a href="summary_graph_imp_category.php"><img src="images/synthgraph.gif" border="0" align="center" />' . lang_get( 'category_link' ) . '</a> | ';
			PRINT '<a href="summary_graph_imp_resolution.php"><img src="images/synthgraph.gif" border="0" align="center" />' . lang_get( 'resolution_link' ) . '</a>';
		}
	}

	# --------------------
	# prints the manage menu
	# if the $p_page matches a url then don't make that a link
	function print_manage_menu( $p_page='' ) {
		if ( !access_level_check_greater_or_equal ( ADMINISTRATOR ) ) {
			return;
		}

		$t_manage_page 				= 'manage_page.php';
		$t_manage_project_menu_page = 'manage_proj_menu_page.php';
		$t_manage_custom_field_page = 'manage_custom_field_page.php';
		$t_manage_user_create_page 	= 'manage_user_create_page.php';
		$t_documentation_page 		= 'documentation_page.php';

		switch ( $p_page ) {
			case $t_manage_page				: $t_manage_page 				= ''; break;
			case $t_manage_project_menu_page: $t_manage_project_menu_page 	= ''; break;
			case $t_manage_custom_field_page: $t_manage_custom_field_page 	= ''; break;
			case $t_manage_user_create_page	: $t_manage_user_create_page 	= ''; break;
			case $t_documentation_page		: $t_documentation_page 		= ''; break;
		}

		PRINT '<br /><div align="center">';
			print_bracket_link( $t_manage_page, lang_get( 'manage_users_link' ) );
			print_bracket_link( $t_manage_project_menu_page, lang_get( 'manage_projects_link' ) );
			if ( ON == config_get( 'use_experimental_custom_fields' ) ) {
				print_bracket_link( $t_manage_custom_field_page, lang_get( 'manage_custom_field_link' ) );
			}		
			print_bracket_link( $t_manage_user_create_page, lang_get( 'create_new_account_link' ) );
			print_bracket_link( $t_documentation_page, lang_get( 'documentation_link' ) );
		PRINT '</div>';
	}
	# --------------------
	# prints the account menu
	# if the $p_page matches a url then don't make that a link
	function print_account_menu( $p_page='' ) {
		$t_account_page 				= 'account_page.php';
		$t_account_prefs_page 			= 'account_prefs_page.php';
		$t_account_profile_menu_page 	= 'account_prof_menu_page.php';

		switch ( $p_page ) {
			case $t_account_page				: $t_account_page 				= ''; break;
			case $t_account_prefs_page			: $t_account_prefs_page 		= ''; break;
			case $t_account_profile_menu_page	: $t_account_profile_menu_page 	= ''; break;
		}

		print_bracket_link( $t_account_page, lang_get( 'account_link' ) );
		print_bracket_link( $t_account_prefs_page, lang_get( 'change_preferences_link' ) );
		print_bracket_link( $t_account_profile_menu_page, lang_get( 'manage_profiles_link' ) );
	}
	# --------------------
	# prints the doc menu
	# if the $p_page matches a url then don't make that a link
	function print_doc_menu( $p_page='' ) {
		global $g_allow_file_upload;

		$t_documentation_html 	= 'doc/documentation.html';
		$t_proj_doc_page 		= 'proj_doc_page.php';
		$t_proj_doc_add_page 	= 'proj_doc_add_page.php';

		switch ( $p_page ) {
			case $t_documentation_html	: $t_documentation_html	= ''; break;
			case $t_proj_doc_page		: $t_proj_doc_page		= ''; break;
			case $t_proj_doc_add_page	: $t_proj_doc_add_page	= ''; break;
		}

		print_bracket_link( $t_documentation_html, lang_get( 'user_documentation' ) );
		print_bracket_link( $t_proj_doc_page, lang_get( 'project_documentation' ) );
		if ( ( ON == $g_allow_file_upload )&&( access_level_check_greater_or_equal( MANAGER ) ) ) {
			print_bracket_link( $t_proj_doc_add_page, lang_get( 'add_file' ) );
		}
	}
	# --------------------
	# prints the manage doc menu
	# if the $p_page matches a url then don't make that a link
	function print_manage_doc_menu( $p_page='' ) {
		global $g_path;

		$g_path = $g_path.'doc/';
		$t_documentation_page = 'documentation_page.php';

		switch ( $p_page ) {
			case $t_documentation_page: $t_documentation_page = ''; break;
		}

		PRINT '<br /><div align="center">';
			print_bracket_link( $t_documentation_page, lang_get( 'system_info_link' ) );
			print_bracket_link( $g_path.'ChangeLog', 'ChangeLog' );
			print_bracket_link( $g_path.'README', 'README' );
			print_bracket_link( $g_path.'INSTALL', 'INSTALL' );
			print_bracket_link( $g_path.'UPGRADING', 'UPGRADING' );
			print_bracket_link( $g_path.'CUSTOMIZATION', 'CUSTOMIZATION' );
		PRINT '</div>';
	}
	# --------------------
	# prints the summary menu
	function print_summary_menu( $p_page='' ) {
		global $g_use_jpgraph;

		PRINT '<div align="center">';
		print_bracket_link( 'print_all_bug_page.php', lang_get( 'print_all_bug_page_link' ) );

		if ( $g_use_jpgraph != 0 ) {
			$t_summary_page 		= 'summary_page.php';
			$t_summary_jpgraph_page = 'summary_jpgraph_page.php';

			switch ( $p_page ) {
				case $t_summary_page		: $t_summary_page			= ''; break;
				case $t_summary_jpgraph_page: $t_summary_jpgraph_page	= ''; break;
			}

			print_bracket_link( $t_summary_page, lang_get( 'summary_link' ) );
			print_bracket_link( $t_summary_jpgraph_page, lang_get( 'summary_jpgraph_link' ) );
		}
		PRINT '</div>';
	}
	# --------------------
	# prints the signup link
	function print_signup_link() {
		global $g_allow_signup;

		if ( $g_allow_signup != 0 ) {
			PRINT '<br /><div align="center">';
			print_bracket_link( 'signup_page.php', lang_get( 'signup_link' ) );
			PRINT '</div>';
		}
	}
	# --------------------
	function print_proceed( $p_result, $p_query, $p_link ) {
		PRINT '<br />';
		PRINT '<div align="center">';
		if ( $p_result ) {						# SUCCESS
			PRINT lang_get( 'operation_successful' ) . '<br />';
		} else {								# FAILURE
			print_sql_error( $p_query );
		}
		print_bracket_link( $p_link, lang_get( 'proceed' ) );
		PRINT '</div>';
	}
	# --------------------
	# This is our generic error printing function
	# Errors should terminate the script immediately
	function print_mantis_error( $p_error_num=0 ) {
		global $MANTIS_ERROR;

		PRINT '<html><head></head><body>';
		PRINT $MANTIS_ERROR[$p_error_num];
		PRINT '</body></html>';
		exit;
	}
	# --------------------
	# Print the color legend for the colors
	function print_status_colors() {
		global	$g_status_enum_string;

		PRINT '<br />';
		PRINT '<table class="width100" cellspacing="1">';
		PRINT '<tr>';
		$t_arr  = explode_enum_string( $g_status_enum_string );
		$enum_count = count( $t_arr );
		$width = (integer) (100 / $enum_count);
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$t_val = get_enum_element( 'status', $t_s[0] );

			$t_color = get_status_color( $t_s[0] );
			PRINT "<td class=\"small-caption\" width=\"$width%\" bgcolor=\"$t_color\">$t_val</td>";
		}
		PRINT '</tr>';
		PRINT '</table>';
	}

	# --------------------
	# Print an html button inside a form
	function html_button ( $p_action, $p_button_text, $p_fields = null ) {
		$p_action = urlencode( $p_action );
		$p_button_text = string_edit_text( $p_button_text );
		if ( null === $p_fields ) {
			$p_fields = array();
		}

		echo "<form method=\"post\" action=\"$p_action\">\n";

		foreach ( $p_fields as $key => $val ) {
			$key = string_edit_text( $key );
			$val = string_edit_text( $val );

			echo "	<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
		}

		echo "	<input type=\"submit\" value=\"$p_button_text\" />\n";
		echo "</form>\n";
	}

	# --------------------
	# Print a button to update the given bug
	function html_button_bug_update( $p_bug_id ) {
		if ( access_level_check_greater_or_equal( config_get( 'update_bug_threshold' ) ) ) {
			html_button( string_get_bug_update_page(),
						 lang_get( 'update_bug_button' ), 
						 array( 'f_bug_id' => $p_bug_id ) );
		}
	}
	# --------------------
	# Print a button to assign the given bug
	function html_button_bug_assign( $p_bug_id ) {
		if ( access_level_check_greater_or_equal( config_get( 'handle_bug_threshold' ) ) ) {
			$t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );

			if ( $t_handler_id != auth_get_current_user_id() ) {
				html_button( 'bug_assign.php',
							 lang_get( 'bug_assign_button' ), 
							 array( 'f_bug_id' => $p_bug_id ) );
			}
		}
	}
	# --------------------
	# Print a button to resolve the given bug
	function html_button_bug_resolve( $p_bug_id ) {
		if ( access_level_check_greater_or_equal( config_get( 'handle_bug_threshold' ) ) ) {
			html_button( 'bug_resolve_page.php',
						 lang_get( 'resolve_bug_button' ), 
						 array( 'f_bug_id' => $p_bug_id ) );
		}
	}
	# --------------------
	# Print a button to reopen the given bug
	function html_button_bug_reopen( $p_bug_id ) {
		if ( access_level_check_greater_or_equal( config_get( 'reopen_bug_threshold' ) )
			 || ( bug_get_field( $p_bug_id, 'reporter_id' ) == auth_get_current_user_id() 
				  && ON == config_get( 'allow_reporter_reopen' ) ) ) {
			html_button( 'bug_reopen_page.php',
						 lang_get( 'reopen_bug_button' ), 
						 array( 'f_bug_id' => $p_bug_id ) );
		}
	}
	# --------------------
	# Print a button to close the given bug
	function html_button_bug_close( $p_bug_id ) {
		if ( access_level_check_greater_or_equal( config_get( 'close_bug_threshold' ) )
			 || ( bug_get_field( $p_bug_id, 'reporter_id' ) == auth_get_current_user_id() 
				  && ON == config_get( 'allow_reporter_close' ) ) ) {
			html_button( 'bug_close_page.php',
						 lang_get( 'close_bug_button' ), 
						 array( 'f_bug_id' => $p_bug_id ) );
		}
	}
	# --------------------
	# Print a button to monitor the given bug
	function html_button_bug_monitor( $p_bug_id ) {
		if ( PUBLIC == bug_get_field( $p_bug_id, 'view_state' ) ) {
			$t_threshold = config_get( 'monitor_bug_threshold' );
		} else {
			$t_threshold = config_get( 'private_bug_threshold' );
		}

		if ( access_level_check_greater_or_equal( $t_threshold ) ) {
			html_button( 'bug_monitor.php',
						 lang_get( 'monitor_bug_button' ), 
						 array( 'f_bug_id' => $p_bug_id, 'f_action' => 'add' ) );
		}
	}
	# --------------------
	# Print a button to unmonitor the given bug
	#  no reason to ever disallow someone from unmonitoring a bug
	function html_button_bug_unmonitor( $p_bug_id ) {
		html_button( 'bug_monitor.php',
					 lang_get( 'unmonitor_bug_button' ), 
					 array( 'f_bug_id' => $p_bug_id, 'f_action' => 'delete' ) );
	}
	# --------------------
	# Print a button to delete the given bug
	function html_button_bug_delete( $p_bug_id ) {
		if ( access_level_check_greater_or_equal( config_get( 'allow_bug_delete_access_level' ) ) ) {
			html_button( 'bug_delete.php',
						 lang_get( 'delete_bug_button' ), 
						 array( 'f_bug_id' => $p_bug_id ) );
		}
	}
?>
