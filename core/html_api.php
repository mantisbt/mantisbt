<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: html_api.php,v 1.89 2004-04-06 19:38:33 prescience Exp $
	# --------------------------------------------------------

	###########################################################################
	# HTML API
	#
	# These functions control the display of each page
	#
	# This is the call order of these functions, should you need to figure out
	#  which to modify or which to leave out.
	#
	#   html_page_top1
	#     html_begin
	#     html_head_begin
	#     html_content_type
	#     html_title
	#     html_css
	#  (html_meta_redirect)
	#   html_page_top2
	#     html_page_top2a
	#       html_head_end
	#       html_body_begin
	#       html_header
	#       html_top_banner
	#     html_login_info
	#    (print_project_menu_bar)
	#     print_menu
	#
	#  ...Page content here...
	#
	#   html_page_bottom1
	#    (print_menu)
	#     html_page_bottom1a
	#       html_bottom_banner
	#  	 html_footer
	#  	 html_body_end
	#  	 html_end
	#
	###########################################################################

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'string_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'project_api.php' );
	require_once( $t_core_dir . 'helper_api.php' );

	# --------------------
	# Print the part of the page that comes before meta redirect tags should
	#  be inserted
	function html_page_top1() {
		html_begin();
		html_head_begin();
		html_content_type();
		html_title();
		html_css();
		include( config_get( 'meta_include_file' ) );
	}

	# --------------------
	# Print the part of the page that comes after meta tags, but before the
	#  actual page content
	function html_page_top2() {
		html_page_top2a();

		if ( !db_is_connected() ) {
			return;
		}

		html_login_info();
		if( ON == config_get( 'show_project_menu_bar' ) ) {
			print_project_menu_bar();
			echo '<br />';
		}
		print_menu();
	}

	# --------------------
	# Print the part of the page that comes after meta tags and before the
	#  actual page content, but without login info or menus.  This is used
	#  directly during the login process and other times when the user may
	#  not be authenticated
	function html_page_top2a() {
		html_head_end();
		html_body_begin();
		html_header();
		html_top_banner();
	}

	# --------------------
	# Print the part of the page that comes below the page content
	# $p_file should always be the __FILE__ variable. This is passed to show source
	function html_page_bottom1( $p_file = null ) {
		if ( !db_is_connected() ) {
			return;
		}

		if ( config_get( 'show_footer_menu' ) ) {
			echo '<br />';
			print_menu();
		}

		html_page_bottom1a( $p_file );
	}

	# --------------------
	# Print the part of the page that comes below the page content but leave off
	#  the menu.  This is used during the login process and other times when the
	#  user may not be authenticated.
	function html_page_bottom1a( $p_file = null ) {
		if ( ! php_version_at_least( '4.1.0' ) ) {
			global $_SERVER;
		}

		if ( null === $p_file ) {
			$p_file = basename( $_SERVER['PHP_SELF'] );
		}

		html_bottom_banner();
		html_footer( $p_file );
		html_body_end();
		html_end();
	}

	# --------------------
	# (1) Print the document type and the opening <html> tag
	function html_begin() {
		# @@@ NOTE make this a configurable global.
		#echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		#echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/transitional.dtd">';

		echo '<html>';
	}

	# --------------------
	# (2) Begin the <head> section
	function html_head_begin() {
	   echo '<head>';
	}

	# --------------------
	# (3) Print the content-type
	function html_content_type() {
		echo '<meta http-equiv="Content-type" content="text/html;charset=' . lang_get( 'charset' ) . '" />';
	}

	# --------------------
	# (4) Print the window title
	function html_title() {
		$t_title = config_get( 'window_title' );
		echo '<title>' . string_display( $t_title ) . "</title>\n";
	}

	# --------------------
	# (5) Print the link to include the css file
	function html_css() {
		$t_css_url = config_get( 'css_include_file' );
		echo '<link rel="stylesheet" type="text/css" href="' . $t_css_url . '" />';
		echo '<script type="text/javascript" language="JavaScript">';
		echo 'if(document.layers) {document.write("<style>td{padding:0px;}<\/style>")}';
		echo '</script>';
	}

	# --------------------
	# (6) Print an HTML meta tag to redirect to another page
	# This function is optional and may be called by pages that need a redirect.
	# $p_time is the number of seconds to wait before redirecting.
	# If we have handled any errors on this page and the 'stop_on_errors' config
	#  option is turned on, return false and don't redirect.
	function html_meta_redirect( $p_url, $p_time=null ) {
		if ( ON == config_get( 'stop_on_errors' ) && error_handled() ) {
			return false;
		}

		if ( null === $p_time ) {
			$p_time = config_get( 'wait_time' );
		}

		echo "<meta http-equiv=\"Refresh\" content=\"$p_time;URL=$p_url\" />";

		return true;
	}

	# --------------------
	# (7) End the <head> section
	function html_head_end() {
		echo '</head>';
	}

	# --------------------
	# (8) Begin the <body> section
	function html_body_begin() {
		echo '<body>';
	}

	# --------------------
	# (9) Print the title displayed at the top of the page
	function html_header() {
		$t_title = config_get( 'page_title' );
		echo '<div class="center"><span class="pagetitle">' . string_display( $t_title ) . '</span></div>';
	}

	# --------------------
	# (10) Print a user-defined banner at the top of the page if there is one.
	function html_top_banner() {
		$t_page = config_get( 'top_include_page' );

		if ( !is_blank( $t_page ) &&
			 file_exists( $t_page ) &&
			 !is_dir( $t_page ) ) {
			include( $t_page );
		}
	}

	# --------------------
	# (11) Print the user's account information
	# Also print the select box where users can switch projects
	function html_login_info() {
		$t_username = current_user_get_field( 'username' );
		$t_access_level = get_enum_element( 'access_levels', current_user_get_access_level() );
		$t_now = date( config_get( 'complete_date_format' ) );

		echo '<table class="hide">';
		echo '<tr>';
			echo '<td class="login-info-left">';
				if ( current_user_is_anonymous() ) {
					if ( ! php_version_at_least( '4.1.0' ) ) {
						global $_SERVER;
					}

					$t_return_page = $_SERVER['PHP_SELF'];
					if ( isset( $_SERVER['QUERY_STRING'] ) ) {
						$t_return_page .=  '?' . $_SERVER['QUERY_STRING'];
					}

					$t_return_page = string_url(  $t_return_page );
					echo lang_get( 'anonymous' ) . ' | <a href="login_page.php?return=' . $t_return_page . '">' . lang_get( 'login_link' ) . '</a>';
					if ( config_get( 'allow_signup' ) == ON ) {
						echo ' | <a href="signup_page.php">' . lang_get( 'signup_link' ) . '</a>';
					}
				} else {
					echo lang_get( 'logged_in_as' ) . ": <span class=\"italic\">$t_username</span> <span class=\"small\">($t_access_level)</span>";
				}
			echo '</td>';
			echo '<td class="login-info-middle">';
				echo "<span class=\"italic\">$t_now</span>";
			echo '</td>';
			echo '<td class="login-info-right">';
				echo '<form method="post" name="form_set_project" action="set_project.php">';

				if ( ON == config_get( 'use_javascript' )) {
					echo '<select name="project_id" class="small" onchange="document.forms.form_set_project.submit();">';
				} else {
					echo '<select name="project_id" class="small">';
				}
				print_project_option_list( helper_get_current_project() );
				echo '</select>';
				echo '<input type="submit" value="' . lang_get( 'switch' ) . '" class="small" />';
				echo '</form>';
			echo '</td>';
		echo '</tr>';
		echo '</table>';
	}

	# --------------------
	# (12) Print a user-defined banner at the bottom of the page if there is one.
	function html_bottom_banner() {
		$t_page = config_get( 'bottom_include_page' );

		if ( !is_blank( $t_page ) &&
			 file_exists( $t_page ) &&
			 !is_dir( $t_page ) ) {
			include( $t_page );
		}
	}

	# --------------------
	# (13) Print the page footer information
	function html_footer( $p_file ) {
		global $g_timer, $g_queries_array;

		# If a user is logged in, update their last visit time.
		# We do this at the end of the page so that:
		#  1) we can display the user's last visit time on a page before updating it
		#  2) we don't invalidate the user cache immediately after fetching it
		if ( auth_is_user_authenticated() ) {
			$t_user_id = auth_get_current_user_id();
			user_update_last_visit( $t_user_id );
		}

		echo '<br />';
		echo '<hr size="1" />';
		if ( ON == config_get( 'show_version' ) ) {
			echo '<span class="timer"><a href="http://www.mantisbt.org/">Mantis ' . config_get( 'mantis_version' ) . '</a></span>';
		}
		echo '<address>Copyright &copy; 2000 - 2004</address>';
		echo '<address><a href="mailto:' . config_get( 'webmaster_email' ) . '">' . config_get( 'webmaster_email' ) . '</a></address>';
		if ( ON == config_get( 'show_timer' ) ) {
			$g_timer->print_times();
		}
		if ( ON == config_get( 'show_queries_count' ) ) {
			$t_count = count( $g_queries_array );
			echo $t_count.' total queries executed.<br />';
			echo count( array_unique ( $g_queries_array ) ).' unique queries executed.<br />';
			if ( ON == config_get( 'show_queries_list' ) ) {
				echo '<table>';
				$t_shown_queries = array();
				for ( $i = 0; $i < $t_count; $i++ ) {
					if ( in_array( $g_queries_array[$i], $t_shown_queries ) ) {
						echo '<tr><td style="color: red">'.($i+1).'</td><td style="color: red">'.htmlspecialchars($g_queries_array[$i]).'</td></tr>';
					} else {
						array_push( $t_shown_queries, $g_queries_array[$i] );
						echo '<tr><td>'.($i+1).'</td><td>'.htmlspecialchars($g_queries_array[$i]) . '</td></tr>';
					}
				}
				echo '</table>';
			}
		}
	}

	# --------------------
	# (14) End the <body> section
	function html_body_end() {
		echo '</body>';
	}

	# --------------------
	# (15) Print the closing <html> tag
	function html_end() {
		echo '</html>';
	}


	###########################################################################
	# HTML Menu API
	###########################################################################

	# --------------------
	# Print the main menu
	function print_menu() {
		if ( auth_is_user_authenticated() ) {
			$t_protected = current_user_get_field( 'protected' );
			echo '<table class="width100" cellspacing="0">';
			echo '<tr>';
			echo '<td class="menu">';
				$t_menu_options = array();

				# Main Page
				$t_menu_options[] = '<a href="main_page.php">' . lang_get( 'main_link' ) . '</a>';

				# View Bugs
				$t_menu_options[] = '<a href="view_all_bug_page.php">' . lang_get( 'view_bugs_link' ) . '</a>';

				# Report Bugs
				if ( access_has_project_level( REPORTER ) ) {
					$t_menu_options[] = string_get_bug_report_link();
				}

				# Summary Page
				if ( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
					$t_menu_options[] = '<a href="summary_page.php">' . lang_get( 'summary_link' ) . '</a>';
				}

				# Project Documentation Page
				if( ON == config_get( 'enable_project_documentation' ) ) {
					$t_menu_options[] = '<a href="proj_doc_page.php">' . lang_get( 'docs_link' ) . '</a>';
				}

				# Manage Users (admins) or Manage Project (managers)
				if ( access_has_project_level( config_get( 'manage_project_threshold' ) ) ) {
					if ( access_has_project_level( ADMINISTRATOR ) ) {
						$t_link = 'manage_user_page.php';
					} else {
						$t_link = 'manage_proj_page.php';
					}
					$t_menu_options[] = "<a href=\"$t_link\">" . lang_get( 'manage_link' ) . '</a>';
				}

				# News Page
				if ( access_has_project_level( config_get( 'manage_news_threshold' ) ) ) {
					# Admin can edit news for All Projects (site-wide)
					if ( ( ALL_PROJECTS != helper_get_current_project() ) || ( access_has_project_level( ADMINISTRATOR ) ) ) {
						$t_menu_options[] = '<a href="news_menu_page.php">' . lang_get( 'edit_news_link' ) . '</a>';
					} else {
						$t_menu_options[] = '<a href="login_select_proj_page.php">' . lang_get( 'edit_news_link' ) . '</a>';
					}
				}

				# Account Page (only show accounts that are NOT protected)
				if ( OFF == $t_protected ) {
					$t_menu_options[] = '<a href="account_page.php">' . lang_get( 'account_link' ) . '</a>';
				}

				# Logout (no if anonymously logged in)
				if ( !current_user_is_anonymous() ) {
					$t_menu_options[] = '<a href="logout_page.php">' . lang_get( 'logout_link' ) . '</a>';
				}
				echo implode( $t_menu_options, ' | ' );
			echo '</td>';
			echo '<td class="right" style="white-space: nowrap;">';
				echo '<form method="post" action="jump_to_bug.php">';
				echo "<input type=\"text\" name=\"bug_id\" size=\"10\" class=\"small\" />&nbsp;";
				echo '<input type="submit" value="' . lang_get( 'jump' ) . '" class="small" />&nbsp;';
				echo '</form>';
			echo '</td>';
			echo '</tr>';
			echo '</table>';
		}
	}

	# --------------------
	# Print the menu bar with a list of projects to which the user has access
	function print_project_menu_bar() {
		$t_project_ids = current_user_get_accessible_projects();

		echo '<table class="width100" cellspacing="0">';
		echo '<tr>';
			echo '<td class="menu">';
			echo '<a href="set_project.php?project_id=' . ALL_PROJECTS . '">' . lang_get( 'all_projects' ) . '</a>';
			$t_project_count = count( $t_project_ids );
			for ( $i=0 ; $i < $t_project_count ; $i++ ) {
				$t_id = $t_project_ids[$i];
				echo " | <a href=\"set_project.php?project_id=$t_id\">" . string_display( project_get_field( $t_id, 'name' ) ) . '</a>';
			}
			echo '</td>';
		echo '</tr>';
		echo '</table>';
	}

	# --------------------
	# Print the menu for the graph summary section
	function print_menu_graph() {
		if ( config_get( 'use_jpgraph' ) ) {
			$t_icon_path = config_get( 'icon_path' );

			echo '<br />';
			echo '<a href="summary_page.php"><img src="' . $t_icon_path.'synthese.gif" border="0" align="center" />' . lang_get( 'synthesis_link' ) . '</a> | ';
			echo '<a href="summary_graph_imp_status.php"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'status_link' ) . '</a> | ';
			echo '<a href="summary_graph_imp_priority.php"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'priority_link' ) . '</a> | ';
			echo '<a href="summary_graph_imp_severity.php"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'severity_link' ) . '</a> | ';
			echo '<a href="summary_graph_imp_category.php"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'category_link' ) . '</a> | ';
			echo '<a href="summary_graph_imp_resolution.php"><img src="' . $t_icon_path.'synthgraph.gif" border="0" align="center" />' . lang_get( 'resolution_link' ) . '</a>';
		}
	}

	# --------------------
	# Print the menu for the manage section
	# $p_page specifies the current page name so it's link can be disabled
	function print_manage_menu( $p_page='' ) {
		if ( !access_has_project_level( ADMINISTRATOR ) ) {
			return;
		}

		$t_manage_user_page 		= 'manage_user_page.php';
		$t_manage_project_menu_page = 'manage_proj_page.php';
		$t_manage_custom_field_page = 'manage_custom_field_page.php';
		$t_documentation_page 		= 'documentation_page.php';

		switch ( $p_page ) {
			case $t_manage_user_page				: $t_manage_user_page 				= ''; break;
			case $t_manage_project_menu_page: $t_manage_project_menu_page 	= ''; break;
			case $t_manage_custom_field_page: $t_manage_custom_field_page 	= ''; break;
			case $t_documentation_page		: $t_documentation_page 		= ''; break;
		}

		echo '<br /><div align="center">';
			print_bracket_link( $t_manage_user_page, lang_get( 'manage_users_link' ) );
			print_bracket_link( $t_manage_project_menu_page, lang_get( 'manage_projects_link' ) );
			print_bracket_link( $t_manage_custom_field_page, lang_get( 'manage_custom_field_link' ) );
			print_bracket_link( $t_documentation_page, lang_get( 'documentation_link' ) );
		echo '</div>';
	}

	# --------------------
	# Print the menu for the account section
	# $p_page specifies the current page name so it's link can be disabled
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
		if ( access_has_project_level( config_get( 'add_profile_threshold' ) ) ) {
			print_bracket_link( $t_account_profile_menu_page, lang_get( 'manage_profiles_link' ) );
		}
	}

	# --------------------
	# Print the menu for the docs section
	# $p_page specifies the current page name so it's link can be disabled
	function print_doc_menu( $p_page='' ) {
		$t_documentation_html 	= config_get( 'manual_url' );
		$t_proj_doc_page 		= 'proj_doc_page.php';
		$t_proj_doc_add_page 	= 'proj_doc_add_page.php';

		switch ( $p_page ) {
			case $t_documentation_html	: $t_documentation_html	= ''; break;
			case $t_proj_doc_page		: $t_proj_doc_page		= ''; break;
			case $t_proj_doc_add_page	: $t_proj_doc_add_page	= ''; break;
		}

		print_bracket_link( $t_documentation_html, lang_get( 'user_documentation' ) );
		print_bracket_link( $t_proj_doc_page, lang_get( 'project_documentation' ) );
		if ( file_allow_project_upload() ) {
			print_bracket_link( $t_proj_doc_add_page, lang_get( 'add_file' ) );
		}
	}

	# --------------------
	# Print the menu for the management docs section
	# $p_page specifies the current page name so it's link can be disabled
	function print_manage_doc_menu( $p_page='' ) {
		$t_path = config_get( 'path' ).'doc/';
		$t_documentation_page = 'documentation_page.php';

		switch ( $p_page ) {
			case $t_documentation_page: $t_documentation_page = ''; break;
		}

		echo '<br /><div align="center">';
			print_bracket_link( $t_documentation_page, lang_get( 'system_info_link' ) );
			print_bracket_link( $t_path.'ChangeLog', 'ChangeLog' );
			print_bracket_link( $t_path.'README', 'README' );
			print_bracket_link( $t_path.'INSTALL', 'INSTALL' );
			print_bracket_link( $t_path.'UPGRADING', 'UPGRADING' );
			print_bracket_link( $t_path.'CUSTOMIZATION', 'CUSTOMIZATION' );
		echo '</div>';
	}

	# --------------------
	# Print the menu for the summary section
	# $p_page specifies the current page name so it's link can be disabled
	function print_summary_menu( $p_page='' ) {
		echo '<div align="center">';
		print_bracket_link( 'print_all_bug_page.php', lang_get( 'print_all_bug_page_link' ) );

		if ( config_get( 'use_jpgraph' ) != 0 ) {
			$t_summary_page 		= 'summary_page.php';
			$t_summary_jpgraph_page = 'summary_jpgraph_page.php';

			switch ( $p_page ) {
				case $t_summary_page		: $t_summary_page			= ''; break;
				case $t_summary_jpgraph_page: $t_summary_jpgraph_page	= ''; break;
			}

			print_bracket_link( $t_summary_page, lang_get( 'summary_link' ) );
			print_bracket_link( $t_summary_jpgraph_page, lang_get( 'summary_jpgraph_link' ) );
		}
		echo '</div>';
	}


	#=========================
	# Candidates for moving to print_api
	#=========================

	# --------------------
	# Print the color legend for the status colors
	function html_status_legend() {
		echo '<br />';
		echo '<table class="width100" cellspacing="1">';
		echo '<tr>';
		$t_arr  = explode_enum_string( config_get( 'status_enum_string' ) );
		$enum_count = count( $t_arr );
		$width = (integer) (100 / $enum_count);
		for ($i=0;$i<$enum_count;$i++) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			$t_val = get_enum_element( 'status', $t_s[0] );

			$t_color = get_status_color( $t_s[0] );
			echo "<td class=\"small-caption\" width=\"$width%\" bgcolor=\"$t_color\">$t_val</td>";
		}
		echo '</tr>';
		echo '</table>';
	}

	# --------------------
	# Print an html button inside a form
	function html_button ( $p_action, $p_button_text, $p_fields = null ) {
		$p_action = urlencode( $p_action );
		$p_button_text = string_attribute( $p_button_text );
		if ( null === $p_fields ) {
			$p_fields = array();
		}

		echo "<form method=\"post\" action=\"$p_action\">\n";

		foreach ( $p_fields as $key => $val ) {
			$key = string_attribute( $key );
			$val = string_attribute( $val );

			echo "	<input type=\"hidden\" name=\"$key\" value=\"$val\" />\n";
		}

		echo "	<input type=\"submit\" value=\"$p_button_text\" />\n";
		echo "</form>\n";
	}

	# --------------------
	# Print a button to update the given bug
	function html_button_bug_update( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			html_button( string_get_bug_update_page(),
						 lang_get( 'update_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print Assign To: combo box of possible handlers
	function html_button_bug_assign_to( $p_bug_id ) {
		# make sure current user has access to modify bugs.
		if ( !access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			return;
		}

		$t_reporter_id = bug_get_field( $p_bug_id, 'reporter_id' );
	    $t_handler_id = bug_get_field( $p_bug_id, 'handler_id' );
		$t_current_user_id = auth_get_current_user_id();

		$t_options = array();
		$t_default_assign_to = null;

		if ( ( $t_handler_id != $t_current_user_id ) &&
			( access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id, $t_current_user_id ) ) ) {
		    $t_options[] = array( $t_current_user_id, '[' . lang_get( 'myself' ) . ']' );
			$t_default_assign_to = $t_current_user_id;
		}

		if ( ( $t_handler_id != $t_reporter_id ) &&
			( access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id, $t_reporter_id ) ) ) {
		    $t_options[] = array( $t_reporter_id, '[' . lang_get( 'reporter' ) . ']' );

			if ( $t_default_assign_to === null ) {
				$t_default_assign_to = $t_reporter_id;
			}
		}

		echo "<form method=\"post\" action=\"bug_assign.php\">\n";

		$t_button_text = lang_get( 'bug_assign_to_button' );
		echo "	<input type=\"submit\" value=\"$t_button_text\" />\n";

		echo "<select name=\"handler_id\">";

		$t_already_selected = false;

		foreach ( $t_options as $t_entry ) {
			$t_id = string_attribute( $t_entry[0] );
			$t_caption = string_attribute( $t_entry[1] );

			# if current user and reporter can't be selected, then select the first
			# user in the list.
			if ( $t_default_assign_to === null ) {
			    $t_default_assign_to = $t_id;
			}

		    echo "<option value=\"$t_id\" ";

			if ( ( $t_id == $t_default_assign_to ) && !$t_already_selected ) {
				check_selected( $t_id, $t_default_assign_to );
			    $t_already_selected = true;
			}

			echo ">$t_caption</option>";
		}

		# allow un-assigning if already assigned.
		if ( $t_handler_id != 0 ) {
			echo "<option value=\"0\"></option>";
		}

		$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
		print_assign_to_option_list( 0 /* currently selected */, $t_project_id );
		echo "</select>";

		$t_bug_id = string_attribute( $p_bug_id );
		echo "	<input type=\"hidden\" name=\"bug_id\" value=\"$t_bug_id\" />\n";

		echo "</form>\n";
	}

	# --------------------
	# Print a button to resolve the given bug
	function html_button_bug_resolve( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'bug_resolve_page.php',
						 lang_get( 'resolve_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print a button to reopen the given bug
	function html_button_bug_reopen( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'reopen_bug_threshold' ), $p_bug_id )
			 || ( bug_get_field( $p_bug_id, 'reporter_id' ) == auth_get_current_user_id()
				  && ON == config_get( 'allow_reporter_reopen' ) ) ) {
			html_button( 'bug_reopen_page.php',
						 lang_get( 'reopen_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print a button to close the given bug
	function html_button_bug_close( $p_bug_id ) {
		$t_status = bug_get_field( $p_bug_id, 'status' );

		if ( access_can_close_bug ( $p_bug_id ) && ( $t_status < CLOSED ) ) {
			html_button( 'bug_close_page.php',
						 lang_get( 'close_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print a button to monitor the given bug
	function html_button_bug_monitor( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'monitor_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'bug_monitor.php',
						 lang_get( 'monitor_bug_button' ),
						 array( 'bug_id' => $p_bug_id, 'action' => 'add' ) );
		}
	}

	# --------------------
	# Print a button to unmonitor the given bug
	#  no reason to ever disallow someone from unmonitoring a bug
	function html_button_bug_unmonitor( $p_bug_id ) {
		html_button( 'bug_monitor.php',
					 lang_get( 'unmonitor_bug_button' ),
					 array( 'bug_id' => $p_bug_id, 'action' => 'delete' ) );
	}

	# --------------------
	# Print a button to delete the given bug
	function html_button_bug_delete( $p_bug_id ) {
		if ( access_has_bug_level( config_get( 'delete_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'bug_delete.php',
						 lang_get( 'delete_bug_button' ),
						 array( 'bug_id' => $p_bug_id ) );
		}
	}

	# --------------------
	# Print all buttons for view bug pages
	function html_buttons_view_bug_page( $p_bug_id ) {
		$t_resolved = config_get( 'bug_resolved_status_threshold' );
		$t_status = bug_get_field( $p_bug_id, 'status' );

		echo '<table><tr><td>';
		if ( $t_status < $t_resolved ) {
			# UPDATE button
			html_button_bug_update( $p_bug_id );

			echo '</td><td>';

			# ASSIGN button
			html_button_bug_assign_to( $p_bug_id );

			echo '</td><td>';

			# RESOLVE button
			html_button_bug_resolve( $p_bug_id );
		} else {
			# REOPEN button
			html_button_bug_reopen( $p_bug_id );
		}
		echo '</td>';

		# CLOSE button
		echo '<td>';
		html_button_bug_close( $p_bug_id );
		echo '</td>';

		# MONITOR/UNMONITOR button
		echo '<td>';
		if ( !current_user_is_anonymous() ) {
			if ( user_is_monitoring_bug( auth_get_current_user_id(), $p_bug_id ) ) {
				html_button_bug_unmonitor( $p_bug_id );
			} else {
				html_button_bug_monitor( $p_bug_id );
			}
		}
		echo '</td>';

		# DELETE button
		echo '<td>';
		html_button_bug_delete( $p_bug_id );
		echo '</td></tr></table>';
	}
?>
