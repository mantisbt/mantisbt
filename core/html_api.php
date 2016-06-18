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
 * HTML API
 *
 * These functions control the HTML output of each page.
 *
 *
 * @package CoreAPI
 * @subpackage HTMLAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses php_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses rss_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses layout_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'php_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'rss_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'layout_api.php' );

$g_rss_feed_url = null;

$g_robots_meta = '';

# flag for error handler to skip header menus
$g_error_send_page_header = true;

$g_stylesheets_included = array();
$g_scripts_included = array();

/**
 * Sets the url for the rss link associated with the current page.
 * null: means no feed (default).
 * @param string $p_rss_feed_url RSS feed URL.
 * @return void
 */
function html_set_rss_link( $p_rss_feed_url ) {
	if( OFF != config_get( 'rss_enabled' ) ) {
		global $g_rss_feed_url;
		$g_rss_feed_url = $p_rss_feed_url;
	}
}

/**
 * This method marks the page as not for indexing by search engines
 * @return void
 */
function html_robots_noindex() {
	global $g_robots_meta;
	$g_robots_meta = 'noindex,follow';
}

/**
 * Prints the link that allows auto-detection of the associated feed.
 * @return void
 */
function html_rss_link() {
	global $g_rss_feed_url;

	if( $g_rss_feed_url !== null ) {
		echo '<link rel="alternate" type="application/rss+xml" title="RSS" href="', string_attribute( $g_rss_feed_url ), '" />', "\n";
	}
}

/**
 * Prints a <script> tag to include a JavaScript file.
 * @param string $p_filename Name of JavaScript file (with extension) to include.
 * @return void
 */
function html_javascript_link( $p_filename ) {
	echo "\t", '<script type="text/javascript" src="', helper_mantis_url( 'js/' . $p_filename ), '"></script>', "\n";
}

/**
 * Prints a <script> tag to include a JavaScript file.
 * @param string $p_url fully qualified domain name for the cdn js file
 * @return void
 */
function html_javascript_cdn_link( $p_url ) {
	echo "\t", '<script type="text/javascript" src="', $p_url, '"></script>', "\n";
}

/**
 * Print the document type and the opening <html> tag
 * @return void
 */
function html_begin() {
	echo '<!DOCTYPE html>', "\n";
	echo '<html>', "\n";
}

/**
 * Begin the <head> section
 * @return void
 */
function html_head_begin() {
	echo '<head>', "\n";
}

/**
 * Print the content-type
 * @return void
 */
function html_content_type() {
	echo "\t", '<meta http-equiv="Content-type" content="text/html; charset=utf-8" />', "\n";
}

/**
 * Print the window title
 * @param string $p_page_title Window title.
 * @return void
 */
function html_title( $p_page_title = null ) {
	$t_page_title = string_html_specialchars( $p_page_title );
	$t_title = string_html_specialchars( config_get( 'window_title' ) );
	echo "\t", '<title>';
	if( empty( $t_page_title ) ) {
		echo $t_title;
	} else {
		if( empty( $t_title ) ) {
			echo $t_page_title;
		} else {
			echo $t_page_title, ' - ', $t_title;
		}
	}
	echo '</title>', "\n";
}

/**
 * Require a CSS file to be in html page headers
 * @param string $p_stylesheet_path Path to CSS style sheet.
 * @return void
 */
function require_css( $p_stylesheet_path ) {
	global $g_stylesheets_included;
	$g_stylesheets_included[$p_stylesheet_path] = $p_stylesheet_path;
}

/**
 * Print the link to include the CSS file
 * @return void
 */
function html_css() {
	global $g_stylesheets_included;
	html_css_link( config_get( 'css_include_file' ) );

	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		html_css_cdn_link( 'https://ajax.googleapis.com/ajax/libs/jqueryui/' . JQUERY_UI_VERSION . '/themes/smoothness/jquery-ui.css' );
	} else {
		html_css_link( 'jquery-ui-' . JQUERY_UI_VERSION . '.min.css' );
	}

	html_css_link( 'common_config.php' );
	# Add right-to-left css if needed
	if( lang_get( 'directionality' ) == 'rtl' ) {
		html_css_link( config_get( 'css_rtl_include_file' ) );
	}
	foreach( $g_stylesheets_included as $t_stylesheet_path ) {
		html_css_link( $t_stylesheet_path );
	}
}

/**
 * Prints a CSS link
 * @param string $p_filename Filename.
 * @return void
 */
function html_css_link( $p_filename ) {
	echo "\t", '<link rel="stylesheet" type="text/css" href="', string_sanitize_url( helper_mantis_url( 'css/' . $p_filename ), true ), '" />', "\n";
}

/**
 * Prints a CSS link for CDN
 * @param string $p_url fully qualified domain name to the js file name
 * @return void
 */
function html_css_cdn_link( $p_url ) {
	echo "\t", '<link rel="stylesheet" type="text/css" href="', $p_url, '" />', "\n";
}

/**
 * Print an HTML meta tag to redirect to another page
 * This function is optional and may be called by pages that need a redirect.
 * $p_time is the number of seconds to wait before redirecting.
 * If we have handled any errors on this page return false and don't redirect.
 *
 * @param string  $p_url      The page to redirect: has to be a relative path.
 * @param integer $p_time     Seconds to wait for before redirecting.
 * @param boolean $p_sanitize Apply string_sanitize_url to passed URL.
 * @return boolean
 */
function html_meta_redirect( $p_url, $p_time = null, $p_sanitize = true ) {
	if( ON == config_get_global( 'stop_on_errors' ) && error_handled() ) {
		return false;
	}

	if( null === $p_time ) {
		$p_time = current_user_get_pref( 'redirect_delay' );
	}

	$t_url = config_get( 'path' );
	if( $p_sanitize ) {
		$t_url .= string_sanitize_url( $p_url );
	} else {
		$t_url .= $p_url;
	}

	$t_url = htmlspecialchars( $t_url );

	echo "\t", '<meta http-equiv="Refresh" content="', $p_time, ';URL=', $t_url, '" />', "\n";

	return true;
}

/**
 * Require a javascript file to be in html page headers
 * @param string $p_script_path Path to javascript file.
 * @return void
 */
function require_js( $p_script_path ) {
	global $g_scripts_included;
	$g_scripts_included[$p_script_path] = $p_script_path;
}

/**
 * Javascript...
 * @return void
 */
function html_head_javascript() {
	global $g_scripts_included;
	echo "\t", '<script type="text/javascript" src="', helper_mantis_url( 'javascript_config.php' ), '"></script>', "\n";
	echo "\t", '<script type="text/javascript" src="', helper_mantis_url( 'javascript_translations.php' ), '"></script>', "\n";

	if ( config_get_global( 'cdn_enabled' ) == ON ) {
		echo "\t", '<script src="https://ajax.googleapis.com/ajax/libs/jquery/', JQUERY_VERSION, '/jquery.min.js"></script>', "\n";
		echo "\t", '<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/', JQUERY_UI_VERSION, '/jquery-ui.min.js"></script>', "\n";
	} else {
		html_javascript_link( 'jquery-' . JQUERY_VERSION . '.min.js' );
		html_javascript_link( 'jquery-ui-' . JQUERY_UI_VERSION . '.min.js' );
	}

	html_javascript_link( 'common.js' );
	foreach ( $g_scripts_included as $t_script_path ) {
		html_javascript_link( $t_script_path );
	}
}

/**
 * End the <head> section
 * @return void
 */
function html_head_end() {
	echo '</head>', "\n";
}

/**
 * Prints the logo with an URL link.
 * @param string $p_logo Path to the logo image. If not specified, will get it
 *                       from $g_logo_image
 * @return void
 */
function html_print_logo( $p_logo = null ) {
	if( !$p_logo ) {
		$p_logo = config_get( 'logo_image' );
	}

	if( !is_blank( $p_logo ) ) {
		$t_logo_url = config_get( 'logo_url' );
		$t_show_url = !is_blank( $t_logo_url );

		if( $t_show_url ) {
			echo '<a id="logo-link" href="', config_get( 'logo_url' ), '">';
		}
		$t_alternate_text = string_html_specialchars( config_get( 'window_title' ) );
		echo '<img id="logo-image" alt="', $t_alternate_text, '" style="max-height: 80px;" src="', helper_mantis_url( $p_logo ), '" />';
		if( $t_show_url ) {
			echo '</a>';
		}
	}
}



/**
 * Print a user-defined banner at the top of the page if there is one.
 * @return void
 */
function html_top_banner() {
	$t_page = config_get( 'top_include_page' );
	$t_logo_image = config_get( 'logo_image' );

	if( !is_blank( $t_page ) && file_exists( $t_page ) && !is_dir( $t_page ) ) {
		include( $t_page );
	} else if( !is_blank( $t_logo_image ) ) {
		echo '<div id="banner">';
		html_print_logo( $t_logo_image );
		echo '</div>';
	}

	event_signal( 'EVENT_LAYOUT_PAGE_HEADER' );
}

/**
 * A function that outputs that an operation was successful and provides a redirect link.
 * @param string $p_redirect_url The url to redirect to.
 * @param string $p_message      Message to display to the user.
 * @return void
 */
function html_operation_successful( $p_redirect_url, $p_message = '' ) {
	echo '<div class="col-md-12 col-xs-12">';
	echo '<div class="space-0"></div>';
	echo '<div class="alert alert-success center">';

	if( !is_blank( $p_message ) ) {
		echo $p_message, '<br />';
	}

	echo '<p class="bold bigger-110">', lang_get( 'operation_successful' ), '</p><br />';
	print_button( string_sanitize_url( $p_redirect_url ), lang_get( 'proceed' ) );
	echo '</div></div>';
}

/**
 * End the <body> section
 * @return void
 */
function html_body_end() {
	# Should code need to be added to this function in the future, it should be
	# placed *above* this event, which needs to be the last thing to occur
	# before the actual body ends (see #20084)
	event_signal( 'EVENT_LAYOUT_BODY_END' );

	echo '</div>', "\n";

	echo '</body>', "\n";
}

/**
 * Print the closing <html> tag
 * @return void
 */
function html_end() {
	echo '</html>', "\n";

	if( function_exists( 'fastcgi_finish_request' ) ) {
		fastcgi_finish_request();
	}
}

/**
 * Prepare an array of additional menu options from a configuration variable
 * @param string $p_config Configuration variable name.
 * @return array
 */
function prepare_custom_menu_options( $p_config ) {
	$t_custom_menu_options = config_get( $p_config );
	$t_options = array();

	foreach( $t_custom_menu_options as $t_custom_option ) {
		$t_access_level = $t_custom_option[1];
		if( access_has_project_level( $t_access_level ) ) {
			$t_caption = string_html_specialchars( lang_get_defaulted( $t_custom_option[0] ) );
			$t_link = string_attribute( $t_custom_option[2] );
			$t_options[] = '<a href="' . $t_link . '">' . $t_caption . '</a>';
		}
	}

	return $t_options;
}

/**
 * Print the menu bar with a list of projects to which the user has access
 * @return void
 */
function print_project_menu_bar() {
	$t_project_ids = current_user_get_accessible_projects();
	$t_current_project_id = helper_get_current_project();

	echo '<ul class="nav nav-tabs padding-18">', "\n";

	if( ALL_PROJECTS == $t_current_project_id) {
		echo '<li class="active">', "\n";
	} else {
		echo '<li>', "\n";
	}
	echo '<a href="', helper_mantis_url( 'set_project.php?project_id=' . ALL_PROJECTS ), '">', lang_get( 'all_projects' ), '</a>', "\n";
	echo '</li>', "\n";

	foreach( $t_project_ids as $t_id ) {
		if( $t_id == $t_current_project_id) {
			echo '<li class="active">', "\n";
		} else {
			echo '<li>', "\n";
		}
		echo '<a href="', helper_mantis_url( 'set_project.php?project_id=' . $t_id ), '">', string_html_specialchars( project_get_field( $t_id, 'name' ) ), '</a>', "\n";
		echo '</li>', "\n";
		print_subproject_menu_bar( $t_id, $t_id . ';' );
	}

	echo '</ul>', "\n";
}

/**
 * Print the menu bar with a list of projects to which the user has access
 * @todo check parents param - set_project.php?project_id=' . $p_parents . $t_subproject
 * @param integer $p_project_id A Project id.
 * @param string  $p_parents    Parent project identifiers.
 * @return void
 */
function print_subproject_menu_bar( $p_project_id, $p_parents = '' ) {
	$t_subprojects = current_user_get_accessible_subprojects( $p_project_id );
	$t_char = ':';
	foreach( $t_subprojects as $t_subproject ) {
		echo $t_char, ' <a href="', helper_mantis_url( 'set_project.php?project_id=' . $p_parents . $t_subproject ), '">', string_html_specialchars( project_get_field( $t_subproject, 'name' ) ), '</a>';
		print_subproject_menu_bar( $t_subproject, $p_parents . $t_subproject . ';' );
		$t_char = ',';
	}
}

/**
 * Print the menu for the graph summary section
 * @return void
 */
function print_summary_submenu() {
	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_SUBMENU_SUMMARY' );
	$t_menu_options = array();
	foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
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

	if( count($t_menu_options) > 0 ) {
		echo '<div class="space-10"></div>';
		echo '<div class="center">';
		echo '<div class="btn-toolbar inline">';
		echo '<div class="btn-group">';

		# Plugins menu items - these are cooked links
		foreach ($t_menu_options as $t_menu_item) {
			echo $t_menu_item;
		}
		echo '</div></div></div>';
	}
}

/**
 * Print the menu for the manage section
 *
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_manage_menu( $p_page = '' ) {
	$t_pages = array();

	$t_pages['manage_overview_page.php'] = array( 'url'   => 'manage_overview_page.php', 'label' => '' );
	if( access_has_global_level( config_get( 'manage_user_threshold' ) ) ) {
		$t_pages['manage_user_page.php'] = array( 'url'   => 'manage_user_page.php', 'label' => 'manage_users_link' );
	}
	if( access_has_project_level( config_get( 'manage_project_threshold' ) ) ) {
		$t_pages['manage_proj_page.php'] = array( 'url'   => 'manage_proj_page.php', 'label' => 'manage_projects_link' );
	}
	if( access_has_global_level( config_get( 'tag_edit_threshold' ) ) ) {
		$t_pages['manage_tags_page.php'] = array( 'url'   => 'manage_tags_page.php', 'label' => 'manage_tags_link' );
	}
	if( access_has_global_level( config_get( 'manage_custom_fields_threshold' ) ) ) {
		$t_pages['manage_custom_field_page.php'] = array( 'url'   => 'manage_custom_field_page.php', 'label' => 'manage_custom_field_link' );
	}
	if( config_get( 'enable_profiles' ) == ON && access_has_global_level( config_get( 'manage_global_profile_threshold' ) ) ) {
		$t_pages['manage_prof_menu_page.php'] = array( 'url'   => 'manage_prof_menu_page.php', 'label' => 'manage_global_profiles_link' );
	}
	if( access_has_global_level( config_get( 'manage_plugin_threshold' ) ) ) {
		$t_pages['manage_plugin_page.php'] = array( 'url'   => 'manage_plugin_page.php', 'label' => 'manage_plugin_link' );
	}

	if( access_has_project_level( config_get( 'manage_configuration_threshold' ) ) ) {
		$t_pages['adm_permissions_report.php'] = array(
			'url'   => 'adm_permissions_report.php',
			'label' => 'manage_config_link'
		);
	}

	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_MENU_MANAGE' );
	$t_menu_options = array();
	foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
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

	echo '<ul class="nav nav-tabs padding-18">', "\n";
	foreach( $t_pages AS $t_page ) {
		$t_active =  $t_page['url'] == $p_page ? 'active' : '';
		echo '<li class="', $t_active,  '">', "\n";
		if( $t_page['label'] == '' ) {
			echo '<a href="', lang_get_defaulted( $t_page['url'] ), '"><i class="blue ace-icon fa fa-info-circle"></i> </a>';
		} else {
			echo '<a href="', helper_mantis_url( $t_page['url'] ), '">', lang_get_defaulted( $t_page['label'] ), '</a>';
		}
		echo '</li>', "\n";
	}

	# Plugins menu items - these are cooked links
	foreach( $t_menu_options as $t_menu_item ) {
		echo '<li>', $t_menu_item, '</li>';
	}

	echo '</ul>', "\n";
}

/**
 * Print the menu for the manage configuration section
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_manage_config_menu( $p_page = '' ) {
	if( !access_has_project_level( config_get( 'manage_configuration_threshold' ) ) ) {
		return;
	}

	$t_pages = array();

	$t_pages['adm_permissions_report.php'] = array( 'url'   => 'adm_permissions_report.php',
	                                                'label' => 'permissions_summary_report' );

	if( access_has_global_level( config_get( 'view_configuration_threshold' ) ) ) {
		$t_pages['adm_config_report.php'] = array( 'url'   => 'adm_config_report.php',
		                                           'label' => 'configuration_report' );
	}

	$t_pages['manage_config_work_threshold_page.php'] = array( 'url'   => 'manage_config_work_threshold_page.php',
	                                                           'label' => 'manage_threshold_config' );

	$t_pages['manage_config_workflow_page.php'] = array( 'url'   => 'manage_config_workflow_page.php',
	                                                     'label' => 'manage_workflow_config' );

	if( config_get( 'relationship_graph_enable' ) ) {
		$t_pages['manage_config_workflow_graph_page.php'] = array( 'url'   => 'manage_config_workflow_graph_page.php',
		                                                           'label' => 'manage_workflow_graph' );
	}

	if( config_get( 'enable_email_notification' ) == ON ) {
		$t_pages['manage_config_email_page.php'] = array( 'url'   => 'manage_config_email_page.php',
		                                                  'label' => 'manage_email_config' );
	}

	$t_pages['manage_config_columns_page.php'] = array( 'url'   => 'manage_config_columns_page.php',
	                                                    'label' => 'manage_columns_config' );

	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_MENU_MANAGE_CONFIG' );
	$t_menu_options = array();
	foreach ( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
		foreach ( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
			if( is_array( $t_callback_menu_options ) ) {
				$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
			} else {
				if( !is_null( $t_callback_menu_options ) ) {
					$t_menu_options[] = $t_callback_menu_options;
				}
			}
		}
	}

	echo '<div class="space-10"></div>', "\n";
	echo '<div class="center">', "\n";
	echo '<div class="btn-toolbar inline">', "\n";
	echo '<div class="btn-group">', "\n";

	foreach ( $t_pages as $t_page ) {
		$t_active =  $t_page['url'] == $p_page ? 'active' : '';
		echo '<a class="btn btn-sm btn-white btn-primary ', $t_active, '" href="', helper_mantis_url( $t_page['url'] ),'">', "\n";
		echo lang_get_defaulted( $t_page['label'] );
		echo '</a>', "\n";
	}

	foreach ( $t_menu_options as $t_menu_item ) {
		echo $t_menu_item;
	}

	echo '</div>', "\n";
	echo '</div>', "\n";
	echo '</div>', "\n";
}

/**
 * Print the menu for the account section
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_account_menu( $p_page = '' ) {
	$t_pages['account_page.php'] = array( 'url'=>'account_page.php', 'label'=>'account_link' );
	$t_pages['account_prefs_page.php'] = array( 'url'=>'account_prefs_page.php', 'label'=>'change_preferences_link' );
	$t_pages['account_manage_columns_page.php'] = array( 'url'=>'account_manage_columns_page.php', 'label'=>'manage_columns_config' );

	if( config_get( 'enable_profiles' ) == ON && access_has_project_level( config_get( 'add_profile_threshold' ) ) ) {
		$t_pages['account_prof_menu_page.php'] = array( 'url'=>'account_prof_menu_page.php', 'label'=>'manage_profiles_link' );
	}

	if( config_get( 'enable_sponsorship' ) == ON && access_has_project_level( config_get( 'view_sponsorship_total_threshold' ) ) && !current_user_is_anonymous() ) {
		$t_pages['account_sponsor_page.php'] = array( 'url'=>'account_sponsor_page.php', 'label'=>'my_sponsorship' );
	}

	$t_pages['api_tokens_page.php'] = array( 'url' => 'api_tokens_page.php', 'label' => 'api_tokens_link' );

	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_MENU_ACCOUNT' );
	$t_menu_options = array();
	foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
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

	echo '<ul class="nav nav-tabs padding-18">', "\n";
	foreach ( $t_pages as $t_page ) {
		$t_active =  $t_page['url'] == $p_page ? 'active' : '';
		echo '<li class="', $t_active, '">', "\n";
		echo '<a href="', helper_mantis_url( $t_page['url'] ),'">', "\n";
		echo lang_get( $t_page['label'] );
		echo '</a>', "\n";
		echo '</li>', "\n";
	}

	# Plugins menu items - these are cooked links
	foreach ( $t_menu_options as $t_menu_item ) {
		echo '<li>', $t_menu_item, '</li>';
	}
	echo '</ul>', "\n";
}

/**
 * Print the menu for the documentation section
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_doc_menu( $p_page = '' ) {
	# User Documentation
	$t_doc_url = config_get( 'manual_url' );
	if( is_null( parse_url( $t_doc_url, PHP_URL_SCHEME ) ) ) {
		# URL has no scheme, so it is relative to MantisBT root
		if( is_blank( $t_doc_url ) ||
			!file_exists( config_get_global( 'absolute_path' ) . $t_doc_url )
		) {
			# Local documentation not available, use online docs
			$t_doc_url = 'http://www.mantisbt.org/documentation.php';
		} else {
			$t_doc_url = helper_mantis_url( $t_doc_url );
		}
	}

	$t_pages[$t_doc_url] = array(
		'url'   => $t_doc_url,
		'label' => 'user_documentation'
	);

	# Project Documentation
	$t_pages['proj_doc_page.php'] = array(
		'url'   => helper_mantis_url( 'proj_doc_page.php' ),
		'label' => 'project_documentation'
	);

	# Add File
	if( file_allow_project_upload() ) {
		$t_pages['proj_doc_add_page.php'] = array(
			'url'   => helper_mantis_url( 'proj_doc_add_page.php' ),
			'label' => 'add_file'
		);
	}

	echo '<ul class="nav nav-tabs padding-18">', "\n";

	foreach ( $t_pages as $key => $t_page ) {
		$t_active =  $key == $p_page ? 'active' : '';
		echo '<li class="', $t_active, '">', "\n";
		echo '<a href="', $t_page['url'], '">', "\n";
		echo lang_get($t_page['label']);

		echo '</a>', "\n";
		echo '</li>', "\n";
	}

	echo '</ul>', "\n";
}

/**
 * Print the menu for the summary section
 * @param string $p_page Specifies the current page name so it's link can be disabled.
 * @return void
 */
function print_summary_menu( $p_page = '' ) {
	# Plugin / Event added options
	$t_event_menu_options = event_signal( 'EVENT_MENU_SUMMARY' );
	$t_menu_options = array();
	foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
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

	$t_pages['summary_page.php'] = array( 'url'=>'summary_page.php', 'label'=>'summary_link' );

	echo '<ul class="nav nav-tabs padding-18">', "\n";

	foreach ( $t_pages as $t_page ) {
		$t_active =  $t_page['url'] == $p_page ? 'active' : '';
		echo '<li class="', $t_active, '">', "\n";
		echo '<a href="', helper_mantis_url( $t_page['url'] ), '">', "\n";
		echo lang_get( $t_page['label'] );
		echo '</a>', "\n";
		echo '</li>', "\n";
	}

	# Plugins menu items - these are cooked links
	foreach ( $t_menu_options as $t_menu_item ) {
		echo '<li>', $t_menu_item, '</li>';
	}

	echo '</ul>', "\n";
}

/**
 * Print the admin tab bar
 * @param string $p_page Specifies the current page name so it set to active.
 * @return void
 */
function print_admin_menu_bar( $p_page ) {
	global $g_upgrade;
	echo '<div class="space-10"></div>';
	echo '<ul class="nav nav-tabs padding-18">', "\n";

	$t_active = 'index.php' == $p_page ? 'active' : '';
	echo '<li class="green ', $t_active, '">', "\n";
	echo '<a href="index.php"><i class="blue ace-icon fa fa-info-circle"></i> </a>';
	echo '</li>', "\n";

	if( count( $g_upgrade ) - 1 != config_get( 'database_version' ) ) {
		echo '<li class="bold">', "\n";
		echo '<a class="green" href="install.php">Upgrade your installation</a>';
		echo '</li>', "\n";
	}

	$t_active = 'system_utils.php' == $p_page ? 'active' : '';
	echo '<li class="', $t_active, '">', "\n";
	echo '<a href="system_utils.php">System Utilities</a>', "\n";
	echo '</li>', "\n";

	$t_active = 'test_langs.php' == $p_page ? 'active' : '';
	echo '<li class="', $t_active, '">', "\n";
	echo '<a href="test_langs.php">Test Langs</a>', "\n";
	echo '</li>', "\n";

	$t_active = 'email_queue.php' == $p_page ? 'active' : '';
	echo '<li class="', $t_active, '">', "\n";
	echo '<a href="email_queue.php">Email Queue</a>', "\n";
	echo '</li>', "\n";

	echo '</ul>', "\n";
}

/**
 * Print an html button inside a form
 * @param string $p_action      Form Action.
 * @param string $p_button_text Button Text.
 * @param array  $p_fields      An array of hidden fields to include on the form.
 * @param string $p_method      Form submit method - default post.
 * @return void
 */
function html_button( $p_action, $p_button_text, array $p_fields = array(), $p_method = 'post' ) {
	$t_form_name = explode( '.php', $p_action, 2 );
	$p_action = urlencode( $p_action );
	$p_button_text = string_attribute( $p_button_text );

	if( strtolower( $p_method ) == 'get' ) {
		$t_method = 'get';
	} else {
		$t_method = 'post';
	}

	echo '<form method="', $t_method, '" action="', $p_action, '" class="form-inline">', "\n";
	echo "\t", '<fieldset>';
	# Add a CSRF token only when the form is being sent via the POST method
	if( $t_method == 'post' ) {
		echo form_security_field( $t_form_name[0] );
	}

	foreach( $p_fields as $t_key => $t_val ) {
		$t_key = string_attribute( $t_key );
		$t_val = string_attribute( $t_val );

		echo "\t\t", '<input type="hidden" name="', $t_key, '" value="', $t_val, '" />', "\n";
	}

	echo "\t\t", '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="', $p_button_text, '" />', "\n";
	echo "\t", '</fieldset>';
	echo '</form>', "\n";
}

/**
 * Print a button to update the given bug
 * @param integer $p_bug_id A Bug identifier.
 * @return void
 */
function html_button_bug_update( $p_bug_id ) {
	if( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
		html_button( string_get_bug_update_page(), lang_get( 'update_bug_button' ), array( 'bug_id' => $p_bug_id ) );
	}
}

/**
 * Print Change Status to: button
 * This code is similar to print_status_option_list except
 * there is no masking, except for the current state
 *
 * @param BugData $p_bug A valid bug object.
 * @return void
 */
function html_button_bug_change_status( BugData $p_bug ) {
	$t_current_access = access_get_project_level( $p_bug->project_id );

	# User must have rights to change status to use this button
	if( !access_has_bug_level( config_get( 'update_bug_status_threshold' ), $p_bug->id ) ) {
		return;
	}

	$t_enum_list = get_status_option_list(
		$t_current_access,
		$p_bug->status,
		false,
		# Add close if user is bug's reporter, still has rights to report issues
		# (to prevent users downgraded to viewers from updating issues) and
		# reporters are allowed to close their own issues
		(  bug_is_user_reporter( $p_bug->id, auth_get_current_user_id() )
		&& access_has_bug_level( config_get( 'report_bug_threshold' ), $p_bug->id )
		&& ON == config_get( 'allow_reporter_close' )
		),
		$p_bug->project_id );

	if( count( $t_enum_list ) > 0 ) {
		# resort the list into ascending order after noting the key from the first element (the default)
		$t_default_arr = each( $t_enum_list );
		$t_default = $t_default_arr['key'];
		ksort( $t_enum_list );
		reset( $t_enum_list );

		echo '<form method="post" action="bug_change_status_page.php" class="form-inline">';
		# CSRF protection not required here - form does not result in modifications

		$t_button_text = lang_get( 'bug_status_to_button' );
		echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="', $t_button_text, '" />';

		echo ' <select name="new_status" class="input-sm">';

		# space at beginning of line is important
		foreach( $t_enum_list as $t_key => $t_val ) {
			echo '<option value="', $t_key, '" ';
			check_selected( $t_key, $t_default );
			echo '>', $t_val, '</option>';
		}
		echo '</select>';

		$t_bug_id = string_attribute( $p_bug->id );
		echo '<input type="hidden" name="id" value="', $t_bug_id, '" />', "\n";
		echo '<input type="hidden" name="change_type" value="', BUG_UPDATE_TYPE_CHANGE_STATUS, '" />', "\n";

		echo '</form>', "\n";
	}
}

/**
 * Print Assign To: combo box of possible handlers
 * @param BugData $p_bug Bug object.
 * @return void
 */
function html_button_bug_assign_to( BugData $p_bug ) {
	# make sure status is allowed of assign would cause auto-set-status
	# workflow implementation
	if( ON == config_get( 'auto_set_status_to_assigned' )
		&& !bug_check_workflow( $p_bug->status, config_get( 'bug_assigned_status' ) )
	) {
		return;
	}

	# make sure current user has access to modify bugs.
	if( !access_has_bug_level( config_get( 'update_bug_assign_threshold', config_get( 'update_bug_threshold' ) ), $p_bug->id ) ) {
		return;
	}

	$t_current_user_id = auth_get_current_user_id();
	$t_options = array();
	$t_default_assign_to = null;

	if( ( $p_bug->handler_id != $t_current_user_id )
		&& access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug->id, $t_current_user_id )
	) {
		$t_options[] = array(
			$t_current_user_id,
			'[' . lang_get( 'myself' ) . ']',
		);
		$t_default_assign_to = $t_current_user_id;
	}

	if( ( $p_bug->handler_id != $p_bug->reporter_id )
		&& user_exists( $p_bug->reporter_id )
		&& access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug->id, $p_bug->reporter_id )
	) {
		$t_options[] = array(
			$p_bug->reporter_id,
			'[' . lang_get( 'reporter' ) . ']',
		);

		if( $t_default_assign_to === null ) {
			$t_default_assign_to = $p_bug->reporter_id;
		}
	}

	echo '<form method="post" action="bug_update.php" class="form-inline">';
	echo form_security_field( 'bug_update' );
	echo '<input type="hidden" name="last_updated" value="', $p_bug->last_updated, '" />';
	echo '<input type="hidden" name="action_type" value="', BUG_UPDATE_TYPE_ASSIGN, '" />';

	$t_button_text = lang_get( 'bug_assign_to_button' );
	echo '<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="', $t_button_text, '" />';

	echo ' <select class="input-sm" name="handler_id">';

	# space at beginning of line is important

	$t_already_selected = false;

	foreach( $t_options as $t_entry ) {
		$t_id = (int)$t_entry[0];
		$t_caption = string_attribute( $t_entry[1] );

		# if current user and reporter can't be selected, then select the first
		# user in the list.
		if( $t_default_assign_to === null ) {
			$t_default_assign_to = $t_id;
		}

		echo '<option value="', $t_id, '" ';

		if( ( $t_id == $t_default_assign_to ) && !$t_already_selected ) {
			check_selected( $t_id, $t_default_assign_to );
			$t_already_selected = true;
		}

		echo '>', $t_caption, '</option>';
	}

	# allow un-assigning if already assigned.
	if( $p_bug->handler_id != 0 ) {
		echo '<option value="0"></option>';
	}

	# 0 means currently selected
	print_assign_to_option_list( 0, $p_bug->project_id );
	echo '</select>';

	$t_bug_id = string_attribute( $p_bug->id );
	echo '<input type="hidden" name="bug_id" value="', $t_bug_id, '" />', "\n";

	echo '</form>', "\n";
}

/**
 * Print a button to move the given bug to a different project
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_bug_move( $p_bug_id ) {
	if( access_has_bug_level( config_get( 'move_bug_threshold' ), $p_bug_id ) ) {
		html_button( 'bug_actiongroup_page.php', lang_get( 'move_bug_button' ), array( 'bug_arr[]' => $p_bug_id, 'action' => 'MOVE' ) );
	}
}

/**
 * Print a button to clone the given bug
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_bug_create_child( $p_bug_id ) {
	if( access_has_bug_level( config_get( 'report_bug_threshold' ), $p_bug_id ) ) {
		html_button( string_get_bug_report_url(), lang_get( 'create_child_bug_button' ), array( 'm_id' => $p_bug_id ) );
	}
}

/**
 * Print a button to reopen the given bug
 * @param BugData $p_bug A valid bug object.
 * @return void
 */
function html_button_bug_reopen( BugData $p_bug ) {
	if( access_can_reopen_bug( $p_bug ) ) {
		$t_reopen_status = config_get( 'bug_reopen_status', null, null, $p_bug->project_id );
		html_button(
			'bug_change_status_page.php',
			lang_get( 'reopen_bug_button' ),
			array( 'id' => $p_bug->id, 'new_status' => $t_reopen_status, 'change_type' => BUG_UPDATE_TYPE_REOPEN ) );
	}
}

/**
 * Print a button to close the given bug
 * Only if user can close bugs and workflow allows moving them to that status
 * @param BugData $p_bug A valid bug object.
 * @return void
 */
function html_button_bug_close( BugData $p_bug ) {
	$t_closed_status = config_get( 'bug_closed_status_threshold', null, null, $p_bug->project_id );
	if( access_can_close_bug( $p_bug )
		&& bug_check_workflow( $p_bug->status, $t_closed_status )
	) {
		html_button(
			'bug_change_status_page.php',
			lang_get( 'close_bug_button' ),
			array( 'id' => $p_bug->id, 'new_status' => $t_closed_status, 'change_type' => BUG_UPDATE_TYPE_CLOSE ) );
	}
}

/**
 * Print a button to monitor the given bug
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_bug_monitor( $p_bug_id ) {
	if( access_has_bug_level( config_get( 'monitor_bug_threshold' ), $p_bug_id ) ) {
		html_button( 'bug_monitor_add.php', lang_get( 'monitor_bug_button' ), array( 'bug_id' => $p_bug_id ) );
	}
}

/**
 * Print a button to unmonitor the given bug
 * no reason to ever disallow someone from unmonitoring a bug
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_bug_unmonitor( $p_bug_id ) {
	html_button( 'bug_monitor_delete.php', lang_get( 'unmonitor_bug_button' ), array( 'bug_id' => $p_bug_id ) );
}

/**
 * Print a button to stick the given bug
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_bug_stick( $p_bug_id ) {
	if( access_has_bug_level( config_get( 'set_bug_sticky_threshold' ), $p_bug_id ) ) {
		html_button( 'bug_stick.php', lang_get( 'stick_bug_button' ), array( 'bug_id' => $p_bug_id, 'action' => 'stick' ) );
	}
}

/**
 * Print a button to unstick the given bug
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_bug_unstick( $p_bug_id ) {
	if( access_has_bug_level( config_get( 'set_bug_sticky_threshold' ), $p_bug_id ) ) {
		html_button( 'bug_stick.php', lang_get( 'unstick_bug_button' ), array( 'bug_id' => $p_bug_id, 'action' => 'unstick' ) );
	}
}

/**
 * Print a button to delete the given bug
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_bug_delete( $p_bug_id ) {
	if( access_has_bug_level( config_get( 'delete_bug_threshold' ), $p_bug_id ) ) {
		html_button( 'bug_actiongroup_page.php', lang_get( 'delete_bug_button' ), array( 'bug_arr[]' => $p_bug_id, 'action' => 'DELETE' ) );
	}
}

/**
 * Print a button to create a wiki page
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_button_wiki( $p_bug_id ) {
	if( config_get_global( 'wiki_enable' ) == ON ) {
		if( access_has_bug_level( config_get( 'update_bug_threshold' ), $p_bug_id ) ) {
			html_button( 'wiki.php', lang_get_defaulted( 'Wiki' ), array( 'id' => $p_bug_id, 'type' => 'issue' ), 'get' );
		}
	}
}

/**
 * Print all buttons for view bug pages
 * @param integer $p_bug_id A valid bug identifier.
 * @return void
 */
function html_buttons_view_bug_page( $p_bug_id ) {
	$t_readonly = bug_is_readonly( $p_bug_id );
	$t_sticky = config_get( 'set_bug_sticky_threshold' );

	$t_bug = bug_get( $p_bug_id );

	echo '<div class="btn-group">';
	if( !$t_readonly ) {
		# UPDATE button
		echo '<div class="pull-left padding-right-8">';
		html_button_bug_update( $p_bug_id );
		echo '</div>';

		# ASSIGN button
		echo '<div class="pull-left padding-right-8">';
		html_button_bug_assign_to( $t_bug );
		echo '</div>';

		# Change status button/dropdown
		echo '<div class="pull-left padding-right-8">';
		html_button_bug_change_status( $t_bug );
		echo '</div>';
	}

	# MONITOR/UNMONITOR button
	if( !current_user_is_anonymous() ) {
		echo '<div class="pull-left padding-right-2">';
		if( user_is_monitoring_bug( auth_get_current_user_id(), $p_bug_id ) ) {
			html_button_bug_unmonitor( $p_bug_id );
		} else {
			html_button_bug_monitor( $p_bug_id );
		}
		echo '</div>';
	}

	# STICK/UNSTICK button
	if( access_has_bug_level( $t_sticky, $p_bug_id ) ) {
		echo '<div class="pull-left padding-right-2">';
		if( !bug_get_field( $p_bug_id, 'sticky' ) ) {
			html_button_bug_stick( $p_bug_id );
		} else {
			html_button_bug_unstick( $p_bug_id );
		}
		echo '</div>';
	}

	# CLONE button
	if( !$t_readonly ) {
		echo '<div class="pull-left padding-right-2">';
		html_button_bug_create_child( $p_bug_id );
		echo '</div>';
	}

	# REOPEN button
	echo '<div class="pull-left padding-right-2">';
	html_button_bug_reopen( $t_bug );
	echo '</div>';

	# CLOSE button
	echo '<div class="pull-left padding-right-2">';
	html_button_bug_close( $t_bug );
	echo '</div>';

	# MOVE button
	echo '<div class="pull-left padding-right-2">';
	html_button_bug_move( $p_bug_id );
	echo '</div>';

	# DELETE button
	echo '<div class="pull-left padding-right-2">';
	html_button_bug_delete( $p_bug_id );
	echo '</div>';

	helper_call_custom_function( 'print_bug_view_page_custom_buttons', array( $p_bug_id ) );

	echo '</div>';
}

/**
 * get the css class name for the given status, user and project
 * @param integer $p_status  An enumeration value.
 * @param integer $p_user    A valid user identifier.
 * @param integer $p_project A valid project identifier.
 * @return string
 *
 * @todo This does not work properly when displaying issues from a project other
 * than then current one, if the other project has custom status or colors.
 * This is due to the dynamic css for color coding (css/status_config.php).
 * Build CSS including project or even user-specific colors ?
 */
function html_get_status_css_class( $p_status, $p_user = null, $p_project = null ) {
	$t_status_enum = config_get( 'status_enum_string', null, $p_user, $p_project );
	if( MantisEnum::hasValue( $t_status_enum, $p_status ) ) {
		return 'status-' . $p_status . '-color';
	} else {
		return '';
	}
}
