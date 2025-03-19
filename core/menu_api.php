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
 * Menu API
 *
 * @package CoreAPI
 * @subpackage MenuAPI
 * @copyright Copyright 2025 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses config_api.php
 * @uses event_api.php
 * @uses plugin_api.php
 */

require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'event_api.php' );
require_api( 'plugin_api.php' );

/**
 * Returns a single link for the "manage" menu item in sidebar, based on current
 * user permissions, and priority if several subpages are available.
 * If there is not any accesible manage page, returns null.
 *
 * @return string|null	Page name for the manage menu link, or null if unavailable.
 */
function menu_manage_link() {
	$t_link = null;

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
 * Process main menu custom options from config.
 *
 * @return array containing sidebar items.
 */
function menu_config_options(): array {
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

	return $t_menu_options;
}

/**
 * Process plugin menu options.
 *
 * @param string $p_plugin_event The plugin event signal name.
 * @return array containing sidebar items.
 */
function menu_plugin_options( string $p_plugin_event ): array {
	$t_menu_options = array();
	$t_plugin_event_response = event_signal( $p_plugin_event );

	foreach( $t_plugin_event_response as $t_plugin => $t_plugin_menu_options ) {
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

	return $t_menu_options;
}

/**
 * Get sidebar items.
 *
 * @return array containing sidebar items.
 */
function menu_sidebar_options(): array {
	$t_sidebar_items = array();

	# Plugin / Event added options
	$t_plugin_menu_items_front = menu_plugin_options( 'EVENT_MENU_MAIN_FRONT' );
	if( is_array( $t_plugin_menu_items_front ) ) {
		$t_sidebar_items = $t_plugin_menu_items_front;
	}

	# Main Page
	if( ON == config_get( 'news_enabled' ) ) {
		$t_sidebar_items[] = array(
			'url' => 'main_page.php',
			'title' => 'main_link',
			'icon' => 'fa-bullhorn'
		);
	}

	# My View
	$t_sidebar_items[] = array(
		'url' => 'my_view_page.php',
		'title' => 'my_view_link',
		'icon' => 'fa-dashboard'
	);

	# View Bugs
	$t_sidebar_items[] = array(
		'url' => 'view_all_bug_page.php',
		'title' => 'view_bugs_link',
		'icon' => 'fa-list-alt'
	);

	# Report Bugs
	$t_sidebar_items[] = array(
		'url' => string_get_bug_report_url(),
		'title' => 'report_bug_link',
		'icon' => 'fa-edit',
		'access_level_any' => config_get( 'report_bug_threshold' ),
	);

	# Changelog Page
	$t_sidebar_items[] = array(
		'url' => 'changelog_page.php',
		'title' => 'changelog_link',
		'icon' => 'fa-retweet',
		'access_level' => config_get( 'view_changelog_threshold' )
	);

	# Roadmap Page
	$t_sidebar_items[] = array(
		'url' => 'roadmap_page.php',
		'title' => 'roadmap_link',
		'icon' => 'fa-road',
		'access_level' => config_get( 'roadmap_view_threshold' )
	);

	# Summary Page
	$t_sidebar_items[] = array(
		'url' => 'summary_page.php',
		'title' => 'summary_link',
		'icon' => 'fa-bar-chart-o',
		'access_level' => config_get( 'view_summary_threshold' )
	);

	# Project Documentation Page
	if( ON == config_get( 'enable_project_documentation' ) ) {
		$t_sidebar_items[] = array(
			'url' => 'proj_doc_page.php',
			'title' => 'docs_link',
			'icon' => 'fa-book'
		);
	}

	# Project Wiki
	if( ON == config_get_global( 'wiki_enable' )  ) {
		$t_sidebar_items[] = array(
			'url' => 'wiki.php?type=project&amp;id=' . helper_get_current_project(),
			'title' => 'wiki',
			'icon' => 'fa-book'
		);
	}

	# Manage Users (admins) or Manage Project (managers) or Manage Custom Fields
	$t_sidebar_items[] = array(
		'url' => menu_manage_link(),
		'title' => 'manage_link',
		'icon' => 'fa-gears',
	);

	# Time Tracking / Billing
	if( ON == config_get( 'time_tracking_enabled' ) ) {
		$t_sidebar_items[] = array(
			'url' => 'billing_page.php',
			'title' => 'time_tracking_billing_link',
			'icon' => 'fa-clock-o',
			'access_level' => config_get( 'time_tracking_reporting_threshold' ),
		);
	}

	# Plugin / Event added options
	$t_plugin_menu_items_back = menu_plugin_options( 'EVENT_MENU_MAIN' );
	if( is_array( $t_plugin_menu_items_back ) ) {
		$t_sidebar_items = array_merge( $t_sidebar_items, $t_plugin_menu_items_back );
	}

	# Config based custom options
	$t_config_menu_items = menu_config_options();
	if( is_array( $t_config_menu_items ) ) {
		$t_sidebar_items = array_merge( $t_sidebar_items, $t_config_menu_items );
	}

	# Allow plugins to alter the sidebar items array
	$t_modified_sidebar_items = event_signal( 'EVENT_MENU_MAIN_FILTER', array( $t_sidebar_items ) );
	if( is_array( $t_modified_sidebar_items ) && count( $t_modified_sidebar_items ) > 0 ) {
		$t_sidebar_items = $t_modified_sidebar_items[0];
	}

	# Filter out inaccessible items
	return array_filter( $t_sidebar_items,
		function( $p_item ) {
			return isset( $p_item['url'] )
				&& isset( $p_item['title'] )
				&& ( !isset( $p_item['access_level'] ) || access_has_project_level( $p_item['access_level'] ) )
				&& ( !isset( $p_item['access_level_any'] ) || access_has_any_project_level( $p_item['access_level_any'] ) );
		}
	);
}
