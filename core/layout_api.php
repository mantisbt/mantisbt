<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Layout API
 *
 * UI functions to render layout elements in every page. The layout api layer sits above the html api and abstract
 * the lower level html markup into web components
 *
 * Here is the call order for the layout functions
 *
 * layout_page_header
 *      layout_page_header_begin
 *      layout_page_header_end
 * layout_page_begin
 *      ...Page content here...
 * layout_page_end
 *
 *
 *
 * @package CoreAPI
 * @subpackage LayoutAPI
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses utility_api.php
 */


require_api( 'access_api.php' );
require_api( 'utility_api.php' );


/**
 * Print the page header section
 * @param string $p_page_title   Html page title.
 * @param string $p_redirect_url URL to redirect to if necessary.
 * @param string $p_page_id      The page id.
 * @return void
 */
function layout_page_header( $p_page_title = null, $p_redirect_url = null, $p_page_id = null ) {
	layout_page_header_begin( $p_page_title );
	if( $p_redirect_url !== null ) {
		html_meta_redirect( $p_redirect_url );
	}

	layout_page_header_end( $p_page_id );
}

/**
 * Print the part of the page that comes before meta redirect tags should be inserted
 * @param string $p_page_title Page title.
 * @return void
 */
function layout_page_header_begin( $p_page_title = null ) {
	html_begin();
	html_head_begin();
	html_content_type();

	global $g_robots_meta;
	if( !is_blank( $g_robots_meta ) ) {
		echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
	}

	html_title( $p_page_title );
	layout_head_meta();
	html_css();
	layout_head_css();
	html_rss_link();

	$t_favicon_image = config_get( 'favicon_image' );
	if( !is_blank( $t_favicon_image ) ) {
		echo "\t", '<link rel="shortcut icon" href="', helper_mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
	}

	# Advertise the availability of the browser search plug-ins.
	$t_title = config_get( 'search_title' );
	$t_searches = array( 'text', 'id' );
	foreach( $t_searches as $t_type ) {
		echo "\t",
			'<link rel="search" type="application/opensearchdescription+xml" ',
				'title="' . sprintf( lang_get( "opensearch_{$t_type}_description" ), $t_title ) . '" ',
				'href="' . string_sanitize_url( 'browser_search_plugin.php?type=' . $t_type, true ) .
				'"/>',
			"\n";
	}

	html_head_javascript();
}

/**
 * Print the part of the page that comes after meta tags and before the
 *  actual page content, but without login info or menus.  This is used
 *  directly during the login process and other times when the user may
 *  not be authenticated
 *
 * @param string $p_page_id The id of the page.
 *
 * @return void
 */
function layout_page_header_end( $p_page_id = null) {
	global $g_error_send_page_header;

	event_signal( 'EVENT_LAYOUT_RESOURCES' );
	html_head_end();

	if ( $p_page_id === null ) {
		$t_body_id = '';
	} else {
		$t_body_id = 'id="' . $p_page_id . '" ';
	}

	# Add right-to-left css if needed
	if( layout_is_rtl() ) {
		echo '<body ' . $t_body_id . 'class="skin-3 rtl">', "\n";
	} else {
		echo '<body ' . $t_body_id . 'class="skin-3">', "\n";
	}

	# Set user font preference
	layout_user_font_preference();

	event_signal( 'EVENT_LAYOUT_BODY_BEGIN' );

	$g_error_send_page_header = false;
}

/**
 * Print page common elements including navbar, sidebar, info bar
 * @param string $p_active_sidebar_page sidebar page where the current page lives under
 * @return void
 */
function layout_page_begin( $p_active_sidebar_page = null ) {
	if( !db_is_connected() ) {
		return;
	}
	current_user_modify_single_project_default();

	layout_navbar();

	layout_main_container_begin();

	layout_print_sidebar( $p_active_sidebar_page );

	layout_main_content_begin();

	layout_breadcrumbs();

	layout_page_content_begin();

	if( auth_is_user_authenticated() ) {
		if( ON == config_get( 'show_project_menu_bar' ) ) {
			echo '<div class="row">' , "\n";
			print_project_menu_bar();
			echo '</div>' , "\n";
		}
	}
	echo '<div class="row">' , "\n";

	event_signal( 'EVENT_LAYOUT_CONTENT_BEGIN' );
}

/**
 * Print elements at the end of each page
 * @return void
 */
function layout_page_end() {
	if( !db_is_connected() ) {
		return;
	}

	event_signal( 'EVENT_LAYOUT_CONTENT_END' );

	echo '</div>' , "\n";

	layout_page_content_end();
	layout_main_content_end();

	layout_footer();
	layout_scroll_up_button();

	layout_main_container_end();
	layout_body_javascript();

	html_body_end();
	html_end();
}

/**
 * Print common elements for admin pages
 * @return void
 */
function layout_admin_page_begin() {
	layout_navbar();

	layout_main_container_begin();
}

/**
 * Print elements at the end of each admin page
 * @return void
 */
function layout_admin_page_end() {
	layout_footer();
	layout_scroll_up_button();

	layout_main_container_end();
	layout_body_javascript();

	html_body_end();
    html_end();
}



/**
 * Check if the layout is setup for right to left languages
 * @return bool
 */
function layout_is_rtl() {
	if( lang_get( 'directionality' ) == 'rtl' ) {
		return true;
	}
	return false;
}

/**
 * Print meta tags for the page head
 * @return void
 */
function layout_head_meta() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />' . "\n";
}

/**
 * Print css link directives for the head section of the page
 * @return void
 */
function layout_head_css() {
	# bootstrap & fontawesome
	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		html_css_cdn_link( 'https://maxcdn.bootstrapcdn.com/bootstrap/' . BOOTSTRAP_VERSION . '/css/bootstrap.min.css' );
		html_css_cdn_link( 'https://maxcdn.bootstrapcdn.com/font-awesome/' . FONT_AWESOME_VERSION . '/css/font-awesome.min.css' );

		# theme text fonts
		$t_font_family =  config_get( 'font_family', null, null, ALL_PROJECTS );
		html_css_cdn_link( 'https://fonts.googleapis.com/css?family=' . urlencode( $t_font_family ) );

		# datetimepicker
		html_css_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/' . DATETIME_PICKER_VERSION . '/css/bootstrap-datetimepicker.min.css' );
	} else {
		html_css_link( 'bootstrap-' . BOOTSTRAP_VERSION . '.min.css' );
		html_css_link( 'font-awesome-' . FONT_AWESOME_VERSION . '.min.css' );

		# theme text fonts
		html_css_link( 'fonts.css' );

		# datetimepicker
		html_css_link( 'bootstrap-datetimepicker-' . DATETIME_PICKER_VERSION . '.min.css' );
	}

	# page specific plugin styles

	# theme styles
	html_css_link( 'ace.min.css' );
	html_css_link( 'ace-mantis.css' );
	html_css_link( 'ace-skins.min.css' );

	if( layout_is_rtl() ) {
		html_css_link( 'ace-rtl.min.css' );
	}

	echo "\n";
}

/**
 * Print user font preference
 * @return void
 */
function layout_user_font_preference() {
	$t_font_family = config_get( 'font_family', null, null, ALL_PROJECTS );
	echo '<style>', "\n";
	echo  '* { font-family: "' . $t_font_family . '"; } ', "\n";
	echo  'h1, h2, h3, h4, h5 { font-family: "' . $t_font_family . '"; } ', "\n";
	echo '</style>', "\n";
}

/**
 * Print javascript directives before the closing of the page body element
 * @return void
 */
function layout_body_javascript() {
	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		# bootstrap
		html_javascript_cdn_link( 'https://maxcdn.bootstrapcdn.com/bootstrap/' . BOOTSTRAP_VERSION . '/js/bootstrap.min.js', BOOTSTRAP_HASH );

		# moment & datetimepicker
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/' . MOMENT_VERSION . '/moment-with-locales.min.js', MOMENT_HASH );
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/' . DATETIME_PICKER_VERSION . '/js/bootstrap-datetimepicker.min.js', DATETIME_PICKER_HASH );

		# typeahead.js
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/corejs-typeahead/' . TYPEAHEAD_VERSION . '/typeahead.jquery.min.js', TYPEAHEAD_HASH );

		# listjs
		html_javascript_cdn_link( 'https://cdnjs.cloudflare.com/ajax/libs/list.js/' . LISTJS_VERSION . '/list.min.js', LISTJS_HASH );
	} else {
		# bootstrap
		html_javascript_link( 'bootstrap-' . BOOTSTRAP_VERSION . '.min.js' );

		# moment & datetimepicker
		html_javascript_link( 'moment-with-locales-' . MOMENT_VERSION . '.min.js' );
		html_javascript_link( 'bootstrap-datetimepicker-' . DATETIME_PICKER_VERSION . '.min.js' );

		# typeahead.js
		html_javascript_link( 'typeahead.jquery-' . TYPEAHEAD_VERSION . '.min.js' );

		# listjs
		html_javascript_link( 'list-' . LISTJS_VERSION . '.min.js' );
	}

	# ace theme scripts
	html_javascript_link( 'ace.min.js' );
}


/**
 * Print opening markup for login/signup/register pages
 * @return void
 */
function layout_login_page_begin() {
	html_begin();
	html_head_begin();
	html_content_type();

	global $g_robots_meta;
	if( !is_blank( $g_robots_meta ) ) {
		echo "\t", '<meta name="robots" content="', $g_robots_meta, '" />', "\n";
	}

	html_title();
	layout_head_meta();
	html_css();
	layout_head_css();
	html_rss_link();

	$t_favicon_image = config_get( 'favicon_image' );
	if( !is_blank( $t_favicon_image ) ) {
		echo "\t", '<link rel="shortcut icon" href="', helper_mantis_url( $t_favicon_image ), '" type="image/x-icon" />', "\n";
	}

	# Advertise the availability of the browser search plug-ins.
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Text Search" href="' . string_sanitize_url( 'browser_search_plugin.php?type=text', true) . '" />' . "\n";
	echo "\t", '<link rel="search" type="application/opensearchdescription+xml" title="MantisBT: Issue Id" href="' . string_sanitize_url( 'browser_search_plugin.php?type=id', true) . '" />' . "\n";
	
	html_head_javascript();
	
	event_signal( 'EVENT_LAYOUT_RESOURCES' );
	html_head_end();

	echo '<body class="login-layout light-login">';

	# Set font preference
	layout_user_font_preference();

	layout_main_container_begin();
	layout_main_content_begin();
	echo '<div class="row">';
}

/**
 * Print closing markup for login/signup/register pages
 * @return void
 */
function layout_login_page_end() {
	echo '</div>';
	layout_main_content_end();
	layout_main_container_end();
	layout_body_javascript();

	echo '</body>', "\n";
}

/**
 * Render navbar at the top of the page
 * @return void
 */
function layout_navbar() {
	$t_logo_url = config_get_global('logo_url');
	$t_short_path = config_get_global('short_path');

	echo '<div id="navbar" class="navbar navbar-default navbar-collapse navbar-fixed-top noprint">';
	echo '<div id="navbar-container" class="navbar-container">';

	echo '<button id="menu-toggler" type="button" class="navbar-toggle menu-toggler pull-left hidden-lg hidden-md" data-target="#sidebar">';
	echo '<span class="sr-only">Toggle sidebar</span>';
	echo '<span class="icon-bar"></span>';
	echo '<span class="icon-bar"></span>';
	echo '<span class="icon-bar"></span>';
	echo '</button>';

	echo '<div class="navbar-header">';
	echo '<a href="' . $t_short_path . $t_logo_url . '" class="navbar-brand">';
	echo '<span class="smaller-75"> ';
	echo string_display_line( config_get('window_title') );
	echo ' </span>';
	echo '</a>';

	$t_toggle_class = (OFF == config_get('show_avatar') ? 'navbar-toggle' : 'navbar-toggle-img');
	echo '<button type="button" class="navbar-toggle ' . $t_toggle_class . ' collapsed pull-right hidden-sm hidden-md hidden-lg" data-toggle="collapse" data-target=".navbar-buttons,.navbar-menu">';
	echo '<span class="sr-only">Toggle user menu</span>';
	if (auth_is_user_authenticated()) {
		layout_navbar_user_avatar();
	}
	echo '</button>';

	echo '</div>';

	echo '<div class="navbar-buttons navbar-header navbar-collapse collapse">';
	echo '<ul class="nav ace-nav">';
	if (auth_is_user_authenticated()) {
		# shortcuts button bar
		layout_navbar_button_bar();
		# projects dropdown menu
		layout_navbar_projects_menu();
		# user buttons such as messages, notifications and user menu
		layout_navbar_user_menu();
	}
	echo '</ul>';
	echo '</div>';

	echo '</div>';
	echo '</div>';
}

/**
 * Print navbar menu item
 * @param string $p_url destination url of the menu item
 * @param string $p_title menu item title
 * @param string $p_icon icon to use for this menu
 * @return void
 */
function layout_navbar_menu_item( $p_url, $p_title, $p_icon ) {
	echo '<li>';
	echo '<a href="' . $p_url . '">';
	echo '<i class="ace-icon fa ' . $p_icon . '"> </i> ' . $p_title;
	echo '</a>';
	echo '</li>';
}

/**
 * Print navbar user menu at the top right of the page
 * @param bool $p_show_avatar decide whether to show logged in user avatar
 * @return void
 */
function layout_navbar_user_menu( $p_show_avatar = true ) {
	if( !auth_is_user_authenticated() ) {
		return;
	}

	$t_username = current_user_get_field( 'username' );

	echo '<li class="grey">';
	echo '<a data-toggle="dropdown" href="#" class="dropdown-toggle">';
	if( $p_show_avatar ) {
		layout_navbar_user_avatar();
		echo '<span class="user-info">';
		echo $t_username;
		echo '</span>';
		echo '<i class="ace-icon fa fa-angle-down"></i>';
	} else {
		echo '&#160;' . $t_username . '&#160;' . "\n";
		echo '<i class="ace-icon fa fa-angle-down bigger-110"></i>';
	}
	echo '</a>';
	echo '<ul class="user-menu dropdown-menu dropdown-menu-right dropdown-yellow dropdown-caret dropdown-close">';

	# My Account
	if( !current_user_is_protected() ) {
		layout_navbar_menu_item( helper_mantis_url( 'account_page.php' ), lang_get( 'account_link' ), 'fa-user' );
	}

	# RSS Feed
	if( OFF != config_get( 'rss_enabled' ) ) {
		layout_navbar_menu_item( htmlspecialchars( rss_get_issues_feed_url() ), lang_get( 'rss' ), 'fa-rss-square orange' );
	}

	echo '<li class="divider"></li>';

	# Logout
	layout_navbar_menu_item( helper_mantis_url( auth_logout_page() ), lang_get( 'logout_link' ), 'fa-sign-out' );
	echo '</ul>';
	echo '</li>';
}


/**
 * Print navbar projects menu at the top right of the page
 * @return void
 */
function layout_navbar_projects_menu() {
	if( !layout_navbar_can_show_projects_menu() ) {
		return;
	}
	echo '<li class="grey" id="dropdown_projects_menu">' . "\n";
	echo '<a data-toggle="dropdown" href="#" class="dropdown-toggle">' . "\n";

	$t_current_project_id = helper_get_current_project();
	if( ALL_PROJECTS == $t_current_project_id) {
		echo '&#160;' . string_attribute( lang_get( 'all_projects' ) ) . '&#160;' . "\n";
	} else {
		echo '&#160;' . string_attribute( project_get_field( $t_current_project_id, 'name' ) ) . '&#160;' . "\n";
	}

	echo ' <i class="ace-icon fa fa-angle-down bigger-110"></i>' . "\n";
	echo '</a>' . "\n";

	echo '<ul id="projects-list" class=" dropdown-menu dropdown-menu-right dropdown-yellow dropdown-caret dropdown-close">' . "\n";
	layout_navbar_projects_list( join( ';', helper_get_current_project_trace() ), true, null, true );
	echo '</ul>' . "\n";
	echo '</li>' . "\n";
}

/**
 * Print navbar buttons
 * @return void
 */
function layout_navbar_button_bar() {
	if( !auth_is_user_authenticated() ) {
		return;
	}

	$t_show_report_bug_button = access_has_any_project_level( 'report_bug_threshold' ) &&
		!is_page_name( string_get_bug_page( "report" ) ) &&
		!is_page_name( string_get_bug_page( "update" ) );
	$t_show_invite_user_button = access_has_global_level( config_get( 'manage_user_threshold' ) );

	if( !$t_show_report_bug_button && !$t_show_invite_user_button ) {
		return;
	}

	echo '<li class="hidden-sm hidden-xs">';
	echo '<div class="btn-group btn-corner padding-right-8 padding-left-8">';

	if( $t_show_report_bug_button )  {
		$t_bug_url = string_get_bug_report_url();
		echo '<a class="btn btn-primary btn-sm" href="' . $t_bug_url . '">';
		echo '<i class="fa fa-edit"></i> ' . lang_get( 'report_bug_link' );
		echo '</a>';
	}

	if( $t_show_invite_user_button ) {
		echo '<a class="btn btn-primary btn-sm" href="manage_user_create_page.php">';
		echo '<i class="fa fa-user-plus"></i> ' . lang_get( 'invite_users' );
		echo '</a>';
	}

	echo '</div>';
	echo '</li>';
}

/**
 * Print projects that the current user has access to.
 *
 * @param int $p_project_id 	The current project id or null to use cookie.
 * @param bool $p_include_all_projects  true: include "All Projects", otherwise false.
 * @param int|null $p_filter_project_id  The id of a project to exclude or null.
 * @param string|bool $p_trace  The current project trace, identifies the sub-project via a path from top to bottom.
 * @param bool $p_can_report_only If true, disables projects in which user can't report issues; defaults to false (all projects enabled)
 * @return void
 */
function layout_navbar_projects_list( $p_project_id = null, $p_include_all_projects = true, $p_filter_project_id = null, $p_trace = false, $p_can_report_only = false ) {
	$t_user_id = auth_get_current_user_id();

	# Cache all needed projects
	project_cache_array_rows( user_get_all_accessible_projects( $t_user_id ) );

	# Get top level projects
	$t_project_ids = user_get_accessible_projects( $t_user_id );
	$t_can_report = true;

	echo '<li>';
	echo '<div class="projects-searchbox">';
	echo '<input class="search form-control input-md" placeholder="' . lang_get( 'search' ) . '" />';
	echo '</div>';
	echo '</li>';
	echo '<li class="divider"></li>' . "\n";
	echo '<li>';
	echo '<div class="scrollable-menu">';
	echo '<ul class="list dropdown-yellow no-margin">';

	if( $p_include_all_projects && $p_filter_project_id !== ALL_PROJECTS ) {
		echo ALL_PROJECTS == $p_project_id ? '<li class="active">' : '<li>';
		echo '<a href="' . helper_mantis_url( 'set_project.php' ) . '?project_id=' . ALL_PROJECTS . '">';
		echo lang_get( 'all_projects' ) . ' </a></li>' . "\n";
		echo '<li class="divider"></li>' . "\n";
	}

	foreach( $t_project_ids as $t_id ) {
		if( $p_can_report_only ) {
			$t_report_bug_threshold = config_get( 'report_bug_threshold', null, $t_user_id, $t_id );
			$t_can_report = access_has_project_level( $t_report_bug_threshold, $t_id, $t_user_id );
		}

		echo 0 == strcmp( $t_id, $p_project_id ) ? '<li class="active">' : '<li>';
		echo '<a href="' . helper_mantis_url( 'set_project.php' ) . '?project_id=' . $t_id . '"';
		echo ' class="project-link"> ' . string_attribute( project_get_field( $t_id, 'name' ) ) . ' </a></li>' . "\n";
		layout_navbar_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_can_report_only );
	}

	echo '</ul>';
	echo '</div>';
	echo '</li>';
}

/**
 * List projects that the current user has access to
 *
 * @param integer $p_parent_id         A parent project identifier.
 * @param integer $p_project_id        A project identifier.
 * @param integer $p_filter_project_id A filter project identifier.
 * @param boolean $p_trace             Whether to trace parent projects.
 * @param boolean $p_can_report_only   If true, disables projects in which user can't report issues; defaults to false (all projects enabled).
 * @param array   $p_parents           Array of parent projects.
 * @return void
 */
function layout_navbar_subproject_option_list( $p_parent_id, $p_project_id = null, $p_filter_project_id = null, $p_trace = false, $p_can_report_only = false, array $p_parents = array() ) {
	array_push( $p_parents, $p_parent_id );
	$t_user_id = auth_get_current_user_id();
	$t_project_ids = user_get_accessible_subprojects( $t_user_id, $p_parent_id );
	$t_can_report = true;

	foreach( $t_project_ids as $t_id ) {
		if( $p_can_report_only ) {
			$t_report_bug_threshold = config_get( 'report_bug_threshold', null, $t_user_id, $t_id );
			$t_can_report = access_has_project_level( $t_report_bug_threshold, $t_id, $t_user_id );
		}

		if( $p_trace ) {
			$t_full_id = join( $p_parents, ";" ) . ';' . $t_id;
		} else {
			$t_full_id = $t_id;
		}

		echo 0 == strcmp( $p_project_id, $t_full_id ) ? '<li class="active">' : '<li>';
		echo '<a href="' . helper_mantis_url( 'set_project.php' ) . '?project_id=' . $t_full_id . '"';
		echo ' class="project-link"> ' . str_repeat( '&#160;', count( $p_parents ) * 4 );
		echo string_attribute( project_get_field( $t_id, 'name' ) ) . '</a></li>' . "\n";

		layout_navbar_subproject_option_list( $t_id, $p_project_id, $p_filter_project_id, $p_trace, $p_can_report_only, $p_parents );
	}
}


/**
 * Print user avatar in the navbar
 * @param string $p_img_class css class to use with the img tag
 * @return void
 */
function layout_navbar_user_avatar( $p_img_class = 'nav' ) {
	$t_default_avatar = '<i class="ace-icon fa fa-user fa-2x white"></i> ';

	if( OFF === config_get( 'show_avatar' ) ) {
		echo $t_default_avatar;
		return;
	}

	$p_user_id = auth_get_current_user_id();
	if( !user_exists( $p_user_id ) ) {
		echo $t_default_avatar;
		return;
	}

	if( access_has_project_level( config_get( 'show_avatar_threshold' ), null, $p_user_id ) ) {
		$t_avatar = Avatar::get( $p_user_id, 40 );
		if( false !== $t_avatar ) {
			echo prepare_raw_avatar( $t_avatar, $p_img_class, 40 );
			return;
		}
	}

	echo $t_default_avatar;
}

/**
 * Print sidebar
 * @param string $p_active_sidebar_page page where the displayed page lives under
 * @return void
 */
function layout_print_sidebar( $p_active_sidebar_page = null ) {
	if( auth_is_user_authenticated() ) {
		$t_current_project = helper_get_current_project();

		# Starting sidebar markup
		layout_sidebar_begin();

		# Plugin / Event added options
		$t_event_menu_options = event_signal( 'EVENT_MENU_MAIN_FRONT' );
		layout_plugin_menu_options_for_sidebar( $t_event_menu_options, $p_active_sidebar_page );

		# Main Page
		if( config_get( 'news_enabled' ) == ON ) {
			layout_sidebar_menu( 'main_page.php', 'main_link', 'fa-bullhorn', $p_active_sidebar_page  );
		}

		# My View
		layout_sidebar_menu( 'my_view_page.php', 'my_view_link', 'fa-dashboard', $p_active_sidebar_page );

		# View Bugs
		layout_sidebar_menu( 'view_all_bug_page.php', 'view_bugs_link', 'fa-list-alt', $p_active_sidebar_page );

		# Report Bugs
		if( access_has_any_project_level( 'report_bug_threshold' ) ) {
			$t_bug_url = string_get_bug_report_url();
			layout_sidebar_menu( $t_bug_url, 'report_bug_link', 'fa-edit', $p_active_sidebar_page );
		}

		# Changelog Page
		if( access_has_project_level( config_get( 'view_changelog_threshold', $t_current_project ) ) ) {
			layout_sidebar_menu( 'changelog_page.php', 'changelog_link', 'fa-retweet', $p_active_sidebar_page );
		}

		# Roadmap Page
		if( access_has_project_level( config_get( 'roadmap_view_threshold' ), $t_current_project ) ) {
			layout_sidebar_menu( 'roadmap_page.php', 'roadmap_link', 'fa-road', $p_active_sidebar_page );
		}

		# Summary Page
		if( access_has_project_level( config_get( 'view_summary_threshold' ), $t_current_project ) ) {
			layout_sidebar_menu( 'summary_page.php', 'summary_link', 'fa-bar-chart-o', $p_active_sidebar_page );
		}

		# Project Documentation Page
		if( ON == config_get( 'enable_project_documentation' ) ) {
			layout_sidebar_menu( 'proj_doc_page.php', 'docs_link', 'fa-book', $p_active_sidebar_page );
		}

		# Project Wiki
		if( ON == config_get_global( 'wiki_enable' )  ) {
			layout_sidebar_menu( 'wiki.php?type=project&amp;id=' . $t_current_project, 'wiki', 'fa-book', $p_active_sidebar_page );
		}

		# Manage Users (admins) or Manage Project (managers) or Manage Custom Fields
		$t_link = layout_manage_menu_link();
		if( !is_blank( $t_link ) ) {
			layout_sidebar_menu( $t_link , 'manage_link', 'fa-gears', $p_active_sidebar_page );
		}

		# Time Tracking / Billing
		if( config_get( 'time_tracking_enabled' ) && access_has_project_level( config_get( 'time_tracking_reporting_threshold', $t_current_project ) ) ) {
			layout_sidebar_menu( 'billing_page.php', 'time_tracking_billing_link', 'fa-clock-o', $p_active_sidebar_page );
		}

		# Plugin / Event added options
		$t_event_menu_options = event_signal( 'EVENT_MENU_MAIN' );
		layout_plugin_menu_options_for_sidebar( $t_event_menu_options, $p_active_sidebar_page );

		# Config based custom options
		layout_config_menu_options_for_sidebar( $p_active_sidebar_page );

		# Ending sidebar markup
		layout_sidebar_end();
	}
}

/**
 * Process plugin menu options for sidebar
 * @param array $p_plugin_event_response The response from the plugin event signal.
 * @param string $p_active_sidebar_page The active page on the sidebar.
 * @return void
 */
function layout_plugin_menu_options_for_sidebar( $p_plugin_event_response, $p_active_sidebar_page ) {
	$t_menu_options = array();

	foreach( $p_plugin_event_response as $t_plugin => $t_plugin_menu_options ) {
		foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
			if( is_array( $t_callback_menu_options ) ) {
				$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
			} else {
				if( !is_null( $t_callback_menu_options ) ) {
					$t_menu_options[] = $t_callback_menu_options;
				}
			}
		}
	}

	layout_options_for_sidebar( $t_menu_options, $p_active_sidebar_page );
}

/**
 * Process main menu options from config.
 * @param string $p_active_sidebar_page The active page on the sidebar.
 * @return void
 */
function layout_config_menu_options_for_sidebar( $p_active_sidebar_page ) {
	$t_menu_options = array();
	$t_custom_options = config_get( 'main_menu_custom_options' );

	foreach( $t_custom_options as $t_custom_option ) {
		if( isset( $t_custom_option['url'] ) ) {
			$t_menu_option = $t_custom_option;
		} else {
			# Support < 2.0.0 custom menu options config format
			$t_menu_option = array();
			$t_menu_option['title'] = $t_custom_option[0];
			$t_menu_option['access_level'] = $t_custom_option[1];
			$t_menu_option['url'] = $t_custom_option[2];
		}

		$t_menu_options[] = $t_menu_option;
	}

	layout_options_for_sidebar( $t_menu_options, $p_active_sidebar_page );
}

/**
 * Process main menu options
 * @param array $p_menu_options Array of menu options to output.
 * @param string $p_active_sidebar_page The active page on the sidebar.
 * @return void
 */
function layout_options_for_sidebar( $p_menu_options, $p_active_sidebar_page ) {
	foreach( $p_menu_options as $t_menu_option ) {
		$t_icon = isset( $t_menu_option['icon'] ) ? $t_menu_option['icon'] : 'fa-plug';
		if( !isset( $t_menu_option['url'] ) || !isset( $t_menu_option['title'] ) ) {
			continue;
		}

		if( isset( $t_menu_option['access_level'] ) ) {
			if( !access_has_project_level( $t_menu_option['access_level'] ) ) {
				continue;
			}
		}

		layout_sidebar_menu( $t_menu_option['url'], $t_menu_option['title'], $t_icon, $p_active_sidebar_page );
	}
}

/**
 * Print sidebar opening elements
 * @return void
 */
function layout_sidebar_begin() {
	$t_collapse_block = is_collapsed( 'sidebar' );
	$t_block_css = $t_collapse_block ? 'menu-min' : '';

	echo '<div id="sidebar" class="sidebar sidebar-fixed responsive compact ' . $t_block_css . '">';

	echo '<ul class="nav nav-list">';
}


/**
 * Print sidebar menu item
 * @param string $p_page page name
 * @param string $p_title menu title in english
 * @param string $p_icon icon to use for this menu
 * @param string $p_active_sidebar_page page name to set as active
 * @return void
 */
function layout_sidebar_menu( $p_page, $p_title, $p_icon, $p_active_sidebar_page = null ) {
	if( $p_page == $p_active_sidebar_page ||
		$p_page == basename( $_SERVER['SCRIPT_NAME'] ) ) {
		echo '<li class="active">' . "\n";
	} else {
		echo '<li>' . "\n";
	}

	# Handle relative / absolute urls
	if ( stripos( $p_page, 'https:' ) === 0 || stripos( $p_page, 'http:' ) === 0 ) {
		$t_url = $p_page;
	} else {
		$t_url = helper_mantis_url( $p_page );
	}

	echo '<a href="' . $t_url . '">' . "\n";
	echo '<i class="menu-icon fa ' . $p_icon . '"></i> ' . "\n";
	echo '<span class="menu-text"> ' . lang_get_defaulted( $p_title ) . ' </span>' . "\n";
	echo '</a>' . "\n";
	echo '<b class="arrow"></b>' . "\n";
	echo '</li>' . "\n";
}


/**
 * Print sidebar closing elements
 * @return void
 */
function layout_sidebar_end() {
	echo '</ul>';

	$t_collapse_block = is_collapsed( 'sidebar' );

	echo '<div id="sidebar-btn" class="sidebar-toggle sidebar-collapse">';
	if( layout_is_rtl() ) {
		$t_block_icon = $t_collapse_block ? 'fa-angle-double-left' : 'fa-angle-double-right';
		echo '<i data-icon2="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-right"
		class="ace-icon fa ' . $t_block_icon . '"></i>';
	} else {
		$t_block_icon = $t_collapse_block ? 'fa-angle-double-right' : 'fa-angle-double-left';
		echo '<i data-icon2="ace-icon fa fa-angle-double-right" data-icon1="ace-icon fa fa-angle-double-left"
		class="ace-icon fa ' . $t_block_icon . '"></i>';
	}
	echo '</div>';
	echo '</div>';
}

/**
 * Render opening markup for main container
 * @return void
 */
function layout_main_container_begin() {
	echo '<div class="main-container" id="main-container">', "\n";
}

/**
 * Render closing markup for main container
 * @return void
 */
function layout_main_container_end() {
	echo '</div>' , "\n";
}

/**
 * Render opening markup for main content
 * @return void
 */
function layout_main_content_begin() {
	echo '<div class="main-content">' , "\n";
}

/**
 * Render closing markup for main content
 * @return void
 */
function layout_main_content_end() {
	echo '</div>' , "\n";
}

/**
 * Render opening markup for main page content
 * @return void
 */
function layout_page_content_begin() {
	echo '  <div class="page-content">' , "\n";
}

/**
 * Render closing markup for main page content
 * @return void
 */
function layout_page_content_end() {
	error_print_delayed();

	# Print table of log events
	log_print_to_page();

	echo '</div>' , "\n";
}


/**
 * Render breadcrumbs bar.
 * @return void
 */
function layout_breadcrumbs() {
	if( !auth_is_user_authenticated() ) {
		return;
	}

	echo '<div id="breadcrumbs" class="breadcrumbs noprint">' , "\n";

	# Login information
	echo '<ul class="breadcrumb">' , "\n";
	if( current_user_is_anonymous() ) {
		$t_return_page = $_SERVER['SCRIPT_NAME'];
		if( isset( $_SERVER['QUERY_STRING'] ) && !is_blank( $_SERVER['QUERY_STRING'] )) {
			$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
		}

		$t_return_page = string_url( $t_return_page );

		echo ' <li><i class="fa fa-user home-icon active"></i> ' . lang_get( 'anonymous' ) . ' </li>' . "\n";

		echo '<div class="btn-group btn-corner">' . "\n";
		echo '	<a href="' . helper_mantis_url( auth_login_page( 'return=' . $t_return_page ) ) .
			'" class="btn btn-primary btn-xs">' . lang_get( 'login_link' ) . '</a>' . "\n";
		if( auth_signup_enabled() ) {
			echo '	<a href="' . helper_mantis_url( 'signup_page.php' ) . '" class="btn btn-primary btn-xs">' .
				lang_get( 'signup_link' ) . '</a>' . "\n";
		}
		echo '</div>' . "\n";

	} else {
		$t_protected = current_user_get_field( 'protected' );
		$t_access_level = get_enum_element( 'access_levels', current_user_get_access_level() );
		$t_display_username = string_html_specialchars( current_user_get_field( 'username' ) );
		$t_realname = current_user_get_field( 'realname' );
		$t_display_realname = is_blank( $t_realname ) ? '' : ' ( ' . string_html_specialchars( $t_realname ) . ' ) ';

		echo '  <li><i class="fa fa-user home-icon active"></i>';
		$t_page = ( OFF == $t_protected ) ? 'account_page.php' : 'my_view_page.php';
		echo '  <a href="' . helper_mantis_url( $t_page ) . '">' .
			$t_display_username . $t_display_realname . '</a>' . "\n";

		$t_label = layout_is_rtl() ? 'arrowed-right' : 'arrowed';
		echo '  <span class="label hidden-xs label-default ' . $t_label . '">' . $t_access_level . '</span></li>' . "\n";
	}
	echo '</ul>' , "\n";

	# Recently visited
	if( last_visited_enabled() ) {
		$t_ids = last_visited_get_array();

		if( count( $t_ids ) > 0 ) {
			echo '<div class="nav-recent hidden-xs">' . lang_get( 'recently_visited' ) . ': ';
			$t_first = true;

			foreach( $t_ids as $t_id ) {
				if( !$t_first ) {
					echo ', ';
				} else {
					$t_first = false;
				}

				echo string_get_bug_view_link( $t_id );
			}
			echo '</div>';
		}
	}

	# Bug Jump form
	# CSRF protection not required here - form does not result in modifications
	echo '<div id="nav-search" class="nav-search">';
	echo '<form class="form-search" method="post" action="' . helper_mantis_url( 'jump_to_bug.php' ) . '">';
	echo '<span class="input-icon">';
	echo '<input type="text" name="bug_id" autocomplete="off" class="nav-search-input" placeholder="' . lang_get( 'issue_id' ) . '">';
	echo '<i class="ace-icon fa fa-search nav-search-icon"></i>';
	echo '</span>';
	echo '</form>';
	echo '</div>';
	echo PHP_EOL;

	echo '</div>';
	echo PHP_EOL;
}

/**
 * Print the page footer information
 * @return void
 */
function layout_footer() {
	global $g_queries_array, $g_request_time;

	# If a user is logged in, update their last visit time.
	# We do this at the end of the page so that:
	#  1) we can display the user's last visit time on a page before updating it
	#  2) we don't invalidate the user cache immediately after fetching it
	#  3) don't do this on pages that auto-refresh
	#  4) don't do this on the password verification or update page, as it causes the
	#    verification comparison to fail
	if( !gpc_get_bool( 'refresh' ) &&
		auth_is_user_authenticated() &&
		!current_user_is_anonymous() &&
		!( is_page_name( 'verify.php' ) || is_page_name( 'account_update.php' ) ) ) {
		$t_user_id = auth_get_current_user_id();
		user_update_last_visit( $t_user_id );
	}

	layout_footer_begin();

	# Show MantisBT version and copyright statement
	$t_version_suffix = '';
	$t_copyright_years = ' 2000 - ' . date( 'Y' );
	if( config_get( 'show_version' ) == ON ) {
		$t_version_suffix = ' ' . htmlentities( MANTIS_VERSION . config_get_global( 'version_suffix' ) );
	}
	echo '<div class="col-md-6 col-xs-12 no-padding">' . "\n";
	echo '<address>' . "\n";
	echo '<strong>Powered by <a href="https://www.mantisbt.org" title="bug tracking software">MantisBT ' . $t_version_suffix . '</a></strong> <br>' . "\n";
	echo "<small>Copyright &copy;$t_copyright_years MantisBT Team</small>" . '<br>';

	# Show optional user-specified custom copyright statement
	$t_copyright_statement = config_get( 'copyright_statement' );
	if( $t_copyright_statement ) {
		echo '<small>' . $t_copyright_statement . '</small>' . "\n";
	}

	# Show contact information
	if( !is_page_name( 'login_page' ) ) {
		$t_webmaster_contact_information = sprintf( lang_get( 'webmaster_contact_information' ), string_html_specialchars( config_get( 'webmaster_email' ) ) );
		echo '<small>' . $t_webmaster_contact_information . '</small>' . '<br>' . "\n";
	}

	echo '</address>' . "\n";
	echo '</div>' . "\n";


	# We don't have a button anymore, so for now we will only show the resized
	# version of the logo when not on login page.
	if( !is_page_name( 'login_page' ) ) {
		echo '<div class="col-md-6 col-xs-12">' . "\n";
		echo '<div class="pull-right" id="powered-by-mantisbt-logo">' . "\n";
		$t_mantisbt_logo_url = helper_mantis_url( 'images/mantis_logo.png' );
		echo '<a href="https://www.mantisbt.org" '.
			'title="Mantis Bug Tracker: a free and open source web based bug tracking system.">' .
			'<img src="' . $t_mantisbt_logo_url . '" width="102" height="35" ' .
			'alt="Powered by Mantis Bug Tracker: a free and open source web based bug tracking system." />' .
			'</a>' . "\n";
		echo '</div>' . "\n";
		echo '</div>' . "\n";
	}

	event_signal( 'EVENT_LAYOUT_PAGE_FOOTER' );

	$t_show_timer = config_get_global( 'show_timer' );
	$t_show_memory_usage = config_get_global( 'show_memory_usage' );
	$t_show_queries_count = config_get_global( 'show_queries_count' );
	$t_display_debug_info = $t_show_timer || $t_show_memory_usage || $t_show_queries_count;

	if( $t_display_debug_info ) {
		echo '<div class="col-xs-12 no-padding grey">' . "\n";
		echo '<address class="no-margin pull-right">' . "\n";
	}

	# Print the page execution time
	if( $t_show_timer ) {
		$t_page_execution_time = sprintf( lang_get( 'page_execution_time' ), number_format( microtime( true ) - $g_request_time, 4 ) );
		echo '<small><i class="fa fa-clock-o"></i> ' . $t_page_execution_time . '</small>&#160;&#160;&#160;&#160;' . "\n";
	}

	# Print the page memory usage
	if( $t_show_memory_usage ) {
		$t_page_memory_usage = sprintf( lang_get( 'memory_usage_in_kb' ), number_format( memory_get_peak_usage() / 1024 ) );
		echo '<small><i class="fa fa-bolt"></i> ' . $t_page_memory_usage . '</small>&#160;&#160;&#160;&#160;' . "\n";
	}

	# Determine number of unique queries executed
	if( $t_show_queries_count ) {
		$t_total_queries_count = count( $g_queries_array );
		$t_unique_queries_count = 0;
		$t_total_query_execution_time = 0;
		$t_unique_queries = array();
		for ( $i = 0; $i < $t_total_queries_count; $i++ ) {
			if( !in_array( $g_queries_array[$i][0], $t_unique_queries ) ) {
				$t_unique_queries_count++;
				$g_queries_array[$i][3] = false;
				array_push( $t_unique_queries, $g_queries_array[$i][0] );
			} else {
				$g_queries_array[$i][3] = true;
			}
			$t_total_query_execution_time += $g_queries_array[$i][1];
		}

		$t_total_queries_executed = sprintf( lang_get( 'total_queries_executed' ), $t_total_queries_count );
		echo '<small><i class="fa fa-database"></i> ' . $t_total_queries_executed . '</small>&#160;&#160;&#160;&#160;' . "\n";
		if( config_get_global( 'db_log_queries' ) ) {
			$t_unique_queries_executed = sprintf( lang_get( 'unique_queries_executed' ), $t_unique_queries_count );
			echo '<small><i class="fa fa-database"></i> ' . $t_unique_queries_executed . '</small>&#160;&#160;&#160;&#160;' . "\n";
		}
		$t_total_query_time = sprintf( lang_get( 'total_query_execution_time' ), $t_total_query_execution_time );
		echo '<small><i class="fa fa-clock-o"></i> ' . $t_total_query_time . '</small>&#160;&#160;&#160;&#160;' . "\n";
	}

	if( $t_display_debug_info ) {
		echo '</address>' . "\n";
		echo '</div>' . "\n";
	}

	layout_footer_end();
}

/**
 * Render opening markup for footer section
 * @return void
 */
function layout_footer_begin() {
	echo '<div class="clearfix"></div>' . "\n";
	echo '<div class="space-20"></div>' . "\n";
	echo '<div class="footer noprint">' . "\n";
	echo '<div class="footer-inner">' . "\n";
	echo '<div class="footer-content">' . "\n";
}

/**
 * Render closing markup for footer section
 * @return void
 */
function layout_footer_end() {
	echo '</div>' . "\n";
	echo '</div>' . "\n";
	echo '</div>' . "\n";
}

/**
 * Render scroll up link to go at the bottom of the page
 * @return void
 */
function layout_scroll_up_button() {
	echo '<a class="btn-scroll-up btn btn-sm btn-inverse display" id="btn-scroll-up" href="#">' . "\n";
	echo '<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>' . "\n";
	echo '</a>' . "\n";
}

/**
 * Render the div area with logo used in login pages
 * @return void
 */
function layout_login_page_logo() {
	?>
	<div class="login-logo">
		<img src="<?php echo helper_mantis_url( config_get( 'logo_image' ) ); ?>">
	</div>
	<?php
}

/**
 * Returns a single link for the "manage" menu item in sidebar, based on current
 * user permissions, and priority if several subpages are available.
 * If there is not any accesible manage page, returns null.
 * @return string|null	Page name for the manage menu link, or null if unavailable.
 */
function layout_manage_menu_link() {
	static $t_link = null;
	if( access_has_global_level( config_get( 'manage_site_threshold' ) ) ) {
		$t_link = 'manage_overview_page.php';
	} else {
		if( access_has_global_level( config_get( 'manage_user_threshold' ) ) ) {
			$t_link = 'manage_user_page.php';
		} else {
			if( access_has_any_project_level( 'manage_project_threshold' ) ) {
				$t_current_project = helper_get_current_project();
				if( $t_current_project == ALL_PROJECTS ) {
					$t_link = 'manage_proj_page.php';
				} else {
					if( access_has_project_level( config_get( 'manage_project_threshold' ), $t_current_project ) ) {
						$t_link = 'manage_proj_edit_page.php?project_id=' . $t_current_project;
					} else {
						if ( access_has_global_level( config_get( 'manage_custom_fields_threshold' ) ) ) {
							$t_link = 'manage_custom_field_page.php';
						}
					}
				}
			}
		}
	}
	return $t_link;
}

/**
 * Returns true if the projects menu can be shown for current user.
 * In some circumstances, we won't show the menu to simplify the UI.
 * @return boolean	True if the projects menu can be shown.
 */
function layout_navbar_can_show_projects_menu() {
	if( !auth_is_user_authenticated() ) {
		return false;
	}

	# Project selector is only shown if there are more than one project, or
	# if the user hass access to manage pages, where having ALL_PROJECTS is
	# needed (#20054)
	$t_show_project_selector =
		!is_blank( layout_manage_menu_link() )
		|| current_user_has_more_than_one_project();
	return $t_show_project_selector;
}
