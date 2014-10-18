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
 * Mantis Menu class.
 *
 * @copyright Copyright 2014 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */
class MantisMenu {
	/**
	 * Cache links
	 */
	protected static $mantis_links = array();
	/**
	 * An array of menu objects by name
	 */
	protected static $menus = array();
	/**
	 * An list of submenus by parent link name
	 * The actual menus are stored in the menus array.
	 */
	protected $submenus = array();
	/**
	 * Menu name
	 */
	public $name = null;
	/**
	 * Array of links
	 */
	public $links = array();

	/**
	 * Boolean whether or not to sort the sub menus alphabetically.
	 */
	protected $alpha_sort = OFF;

	/**
	 * Whether to include <div> tags when generating menu
	 */
	public $include_div = true;

	/**
	 * Whether menu is a child menu
	 */
	public $is_child = false;

	/**
	 * Default Constructor
	 */
	public function __construct( $p_name ) {
		$this->name = $p_name;
	}

	/**
	 * Add a link to the links list for this menu. If the $p_link parameter is a cooked link, parse out the displayed 
	 * string and add both the link and the string as an array. If the param is an array just add it to the links array.
	 * Otherwise, the param should be a key to an element in the Mantis available links array.
	 *
	 * @param mixed  $p_link p_link may be an array of data in the format array( 'id'=>X,'url'=>X,'label'=>X ), for 
	                         building a link or a string keyto the available Mantis links, or a cooked link.
	 * @param string $p_key  Key.
	 */
	public function addLink( $p_link, $p_key=null ) {
		static $s_i = 0;
		# build a unique key when no key is passed in.
		if( is_null( $p_key ) ) {
			$p_key = $this->name . '_' . $s_i++;
		}
		if( is_array( $p_link ) ) {
			# just add the array
			$this->links[$p_key] = $p_link;
		} else if( strpos( $p_link, 'href' ) !== false ) {
			$t_label_start = strpos( $p_link, '>' ) + 1;
			$t_label_end = strpos( $p_link, '</a>' );
			$t_label = substr( $p_link, $t_label_start, $t_label_end );
			# this is a cooked link add it with a sort param
			$this->links[$p_key] = array( 'link'=>$p_link, 'label'=>$t_label );
		} else {
			$t_links = self::getAvailableLinks();
			if( is_array( $t_links ) && array_key_exists( $p_link, $t_links ) ) {
				$this->links[$p_link] = $t_links[$p_link];
			}
		}
	}

	/**
	 * Check whether or not the link exists in the links array.
	 * @param string $p_link A link identifier.
	 * @return bool
	 */
	public function hasLink( $p_link ) {
		return array_key_exists( $p_link, $this->links );
	}

	/**
	 * Print the menu and its sub menus as appropriate.
	 * @param boolean $p_footer Indicates whether this menu is in the page footer.
	 */
	public function ToString( $p_footer = false ) {
		$t_is_child = false;
		if( config_get( 'nested_menus' ) == ON && $p_footer == false ) {
			if( $this->alpha_sort == ON ) {
				usort( $this->links, array( 'MantisMenu', 'cmp' ) );
			}

			if( $this->is_child ) {
				$t_is_child = true;
			}
		}

		if( $t_is_child ) {
			$t_class = str_replace( '_', '-', $this->name ) . '-child-menu';
		} else {
			$t_class = str_replace( '_', '-', $this->name ) . '-menu';
		}

		if( $this->include_div ) {
			echo '<div class="' . $t_class . '">' . "\n";
			echo "\t" . '<div>' . "\n";
		}

		if( $t_is_child ) {
			echo "\t\t" . '<ul class="subs">' . "\n";
		} else {
			if( $this->name == 'main' ) {
				echo "\t\t" . '<ul class="nav">' . "\n";
			} else {
				echo "\t\t" . '<ul class="menu">' . "\n";
			}
		}
		foreach( $this->links AS $t_key=>$t_link ) {
			echo "\t\t\t" . '<li>';
			if( is_array( $t_link ) && array_key_exists( 'link', $t_link ) ) {
				# cooked link. just print it
				echo $t_link['link'];
			} else {
				echo '<a ';
				if( is_array( $t_link ) && array_key_exists('id', $t_link ) ) {
					echo 'id="' . $t_link['id'] . '"';
				}
				echo ' href="' . $t_link['url'] . '">' . $t_link['label'] . '</a>';
			}

			# child menu here
			if( config_get( 'nested_menus' ) == ON && $p_footer == false && array_key_exists( $t_key, $this->submenus ) ) {
				if( array_key_exists( $this->submenus[$t_key], self::$menus ) ) {
					$t_submenu = self::$menus[$this->submenus[$t_key]];
					# make sure this link doesn't exist in the submenu or it will infinitely loop.
					if( is_a( $t_submenu, 'MantisMenu' ) && !$t_submenu->hasLink( $t_key ) ) {
						$t_submenu->include_div = $this->include_div;
						$t_submenu->is_child = true;
						$t_submenu->ToString();
						$t_submenu->is_child = false;
					}
				}
			}
			echo '</li>' . "\n";
		}
		echo "\t\t" . '</ul>' . "\n";
		if( $this->include_div ) {
			echo "\t" . '</div>' . "\n";
			echo '</div>' . "\n";
		}
	}

	/**
	 * Prepare an array of additional menu options from a config variable
	 * @param string $p_config config name
	 * @return array
	 */
	public function customMenuOptions( $p_config ) {
		$t_custom_menu_options = config_get( $p_config );
		foreach( $t_custom_menu_options as $t_custom_option ) {
			$t_access_level = $t_custom_option[1];
			if( access_has_project_level( $t_access_level ) ) {
				$t_tmp = array();
				$t_tmp['url'] = $t_custom_option[2];
				$t_tmp['label'] = lang_get_defaulted( $t_custom_option[0] );
				$this->addLink( $t_tmp );
			}
		}
	}

	/**
	 *	custom sort comparison for assoc array.
	 *	Elements should be sorted by the label element
	 */
	private static function cmp( $a, $b ) {
		if ($a['label'] == $b['label']) {
			return 0;
		}
		return ($a['label'] < $b['label']) ? -1 : 1;
	}

	/**
	 *	Get the menus configured to be displayed for the
	 *	requested page.
	 *	@param string $p_page
	 *	@return array An array of menu objects to be printed on the page.
	 */
	public static function getMenusForPage( $p_page, $p_plugin=null ) {
		$t_menus = config_get( 'menus' );

		foreach( $t_menus as $t_menu_name=>$t_func ) {
			# If no include or exclude configuration is found, treat the menu as though
			# it is global and include it in the page.
			$t_include_menu = true;
			$t_pages = array();

			$t_exclude_config_name = 'exclude_' . $t_menu_name . '_menu_pages';
			$t_include_config_name = 'include_' . $t_menu_name . '_menu_pages';
			if( config_is_set( $t_include_config_name ) ) {
				# If an include configuration is set for this menu, check to see if the
				# page is included in the list.  If it is, include the menu.
				$t_pages = config_get( $t_include_config_name, array() );
				if( !is_null( $p_plugin ) ) {
					# only check plugin pages
					if( array_key_exists( $p_plugin, $t_pages ) ) {
						if( !in_array( $p_page, $t_pages[$p_plugin] ) ) {
							$t_include_menu = false;
						}
					} else {
						# the page is a plugin page but the plugin has no entry
						$t_include_menu = false;
					}
				} else if( !in_array( $p_page, $t_pages ) ) {
					$t_include_menu = false;
				}
			} else if( config_is_set( $t_exclude_config_name ) ) {
				# If an exclude configuration is set for this menu, it is a global menu.
				# If the page is found in this list, do not include this menu.  If it is
				# not found, include the menu in the page.
				$t_pages = config_get( $t_exclude_config_name );
				if( !is_null( $p_plugin ) ) {
					# only check plugin pages
					if( array_key_exists( $p_plugin, $t_pages ) ) {
						if( in_array( $p_page, $t_pages[$p_plugin] ) ) {
							$t_include_menu = false;
						}
					}
				} else if( in_array( $p_page, $t_pages ) ) {
					$t_include_menu = false;
				}
			}
			if( $t_include_menu ) {
				$t_menu_class = config_get( 'menu_class' );
				# include menus where page is found
				$t_menu_arr[] = call_user_func( array( $t_menu_class, $t_func ) );
			}
		}
		return $t_menu_arr;
	}

	/**
	 * Function called from pages to build and display menus.
	 * @param string $p_menu The name of the menu to retrieve. This is the key in the menus array.
	 * @param boolean $p_footer Indicates whether this menu is in the page footer
	 */
	public static function printMenu( $p_menu, $p_footer = false ) {
		switch( $p_menu ) {
			case 'main':
				$t_menu = self::getMainMenu();
			break;
			case 'manage':
				$t_menu = self::getManageMenu();
			break;
			case 'manage_config':
				$t_menu = self::getManageConfigMenu();
			break;
			case 'account':
				$t_menu = self::getAccountMenu();
			break;
			case 'summary':
				$t_menu = self::getSummaryMenu();
			break;
			case 'graphs':
				$t_menu = self::getGraphsMenu();
			break;
			case 'doc':
				$t_menu = self::getDocMenu();
			break;
		}
		$t_menu->ToString( $p_footer );
	}

	/**
	 *	Build, cache, and return the default main menu
	 *	If the menu already exists, just return it.
	 */
	public static function getMainMenu() {
		if( !array_key_exists( 'main', self::$menus ) ) {
			$t_main = new MantisMenu( 'main' );
			if( config_get( 'nested_menus' ) == ON ) {
				$t_main->submenus = array(
					'summary_link'=>'summary',
					'manage_link'=>'manage',
					'account_link'=>'account',
					'doc_link'=>'doc',
				);
				# don't need the return. Just load the submenus into the cache
				self::getSummaryMenu();
				self::getManageMenu();
				self::getAccountMenu();
				self::getDocMenu();
			}
			$t_main->addLink( 'main_link' );
			$t_options = self::getEventLinks( 'EVENT_MENU_MAIN_FRONT' );

			if( is_array( $t_options ) ) {
				foreach( $t_options AS $t_link ) {
					$t_main->addLink( $t_link );
				}
			}
			$t_main->addLink( 'my_view_link' );
			$t_main->addLink( 'view_bugs_link' );
			$t_main->addLink( 'report_bug_link' );
			$t_main->addLink( 'changelog_link' );
			$t_main->addLink( 'roadmap_link' );
			$t_main->addLink( 'summary_link' );
			$t_main->addLink( 'docs_link' );
			$t_main->addLink( 'wiki' );
			$t_options = self::getEventLinks( 'EVENT_MENU_MAIN' );
			if( is_array( $t_options ) ) {
				foreach( $t_options AS $t_link ) {
					$t_main->addLink( $t_link );
				}
			}
			$t_main->addLink( 'manage_link' );
			$t_main->addLink( 'edit_news_link' );
			$t_main->addLink( 'account_link' );
			$t_main->customMenuOptions( 'main_menu_custom_options' );
			$t_main->addLink( 'time_tracking_billing_link' );
			$t_main->addLink( 'logout_link' );
			self::$menus['main'] = $t_main;
		}
		return self::$menus['main'];
	}

	/**
	 *	Build, cache, and return the default manage menu
	 *	If the menu already exists, just return it.
	 */
	public static function getManageMenu() {
		if( !array_key_exists( 'manage', self::$menus ) ) {
			$t_manage = new MantisMenu('manage');
			$t_manage->alpha_sort = config_get( 'alpha_sort_nested_menus' );
			$t_manage->addLink( 'manage_users_link' );
			$t_manage->addLink( 'manage_projects_link' );
			$t_manage->addLink( 'manage_users_link' );
			$t_manage->addLink( 'manage_projects_link' );
			$t_manage->addLink( 'manage_tags_link' );
			$t_manage->addLink( 'manage_custom_field_link' );
			$t_manage->addLink( 'manage_global_profiles_link' );
			$t_manage->addLink( 'manage_plugin_link' );
			$t_manage->addLink( 'manage_config_link' );
			$t_options = self::getEventLinks( 'EVENT_MENU_MANAGE' );
			foreach( $t_options AS $t_link ) {
				$t_manage->addLink( $t_link );
			}
			self::$menus['manage'] = $t_manage;
		}
		return self::$menus['manage'];
	}

	/**
	 *	Build, cache, and return the default manage config menu
	 *	If the menu already exists, just return it.
	 */
	public static function getManageConfigMenu() {
		if( !array_key_exists( 'manage_config', self::$menus ) ) {
			$t_manage_config = new MantisMenu('manage_config');
			$t_manage_config->alpha_sort = config_get( 'alpha_sort_nested_menus' );
			$t_manage_config->addLink( 'configuration_report' );
			$t_manage_config->addLink( 'permissions_summary_report' );
			$t_manage_config->addLink( 'manage_threshold_config' );
			$t_manage_config->addLink( 'manage_workflow_graph' );
			$t_manage_config->addLink( 'manage_email_config' );
			$t_manage_config->addLink( 'manage_columns_config' );
			$t_options = self::getEventLinks( 'EVENT_MENU_MANAGE_CONFIG' );
			foreach( $t_options AS $t_link ) {
				$t_manage_config->addLink( $t_link );
			}
			self::$menus['manage_config'] = $t_manage_config;
		}
		return self::$menus['manage_config'];

	}

	/**
	 *	Build, cache, and return the default account menu
	 *	If the menu already exists, just return it.
	 */
	public static function getAccountMenu() {
		if( !array_key_exists( 'account', self::$menus ) ) {
			$t_account= new MantisMenu('account');
			$t_account->alpha_sort = config_get( 'alpha_sort_nested_menus' );
			$t_account->addLink( 'account_link' );
			$t_account->addLink( 'change_preferences_link' );
			$t_account->addLink( 'manage_columns_config' );
			$t_account->addLink( 'manage_profiles_link' );
			$t_account->addLink( 'my_sponsorship' );
			$t_options = self::getEventLinks( 'EVENT_MENU_ACCOUNT' );
			foreach( $t_options AS $t_link ) {
				$t_account->addLink( $t_link );
			}
			self::$menus['account'] = $t_account;
		}
		return self::$menus['account'];
	}

	/**
	 *	Build, cache, and return the default summary menu
	 *	If the menu already exists, just return it.
	 */
	public static function getSummaryMenu() {
		if( !array_key_exists( 'summary', self::$menus ) ) {
			$t_summary= new MantisMenu('summary');
			$t_summary->alpha_sort = config_get( 'alpha_sort_nested_menus' );
			$t_summary->addLink( 'summary_link' );
			$t_summary->addLink( 'print_all_bug_page_link' );
			$t_options = self::getEventLinks( 'EVENT_MENU_SUMMARY' );
			foreach( $t_options AS $t_link ) {
				$t_summary->addLink( $t_link );
			}
			self::$menus['summary'] = $t_summary;
		}
		return self::$menus['summary'];
	}

	/**
	 *	Build, cache, and return the default graphs menu
	 *	If the menu already exists, just return it.
	 */
	public static function getGraphsMenu() {
		if( !array_key_exists( 'graphs', self::$menus ) ) {
			$t_graphs = new MantisMenu('graphs');
			$t_graphs->alpha_sort = config_get( 'alpha_sort_nested_menus' );
			$t_options = self::getEventLinks( 'EVENT_SUBMENU_SUMMARY' );
			foreach( $t_options AS $t_link ) {
				$t_graphs->addLink( $t_link );
			}
			self::$menus['graphs'] = $t_graphs;
		}
		return self::$menus['graphs'];
	}

	/**
	 *	Build, cache, and return the default doc menu
	 *	If the menu already exists, just return it.
	 */
	public static function getDocMenu() {
		if( !array_key_exists( 'doc', self::$menus ) ) {
			$t_doc= new MantisMenu('doc');
			$t_doc->alpha_sort = config_get( 'alpha_sort_nested_menus' );
			$t_doc->addLink( 'doc_link' );
			$t_doc->addLink( 'add_file' );
			$t_doc->addLink( 'project_documentation' );
			$t_doc->addLink( 'user_documentation' );
			$t_options = self::getEventLinks( 'EVENT_MENU_SUMMARY' );
			foreach( $t_options AS $t_link ) {
				$t_doc->addLink( $t_link );
			}
			self::$menus['doc'] = $t_doc;
		}
		return self::$menus['doc'];
	}

	/**
	 *	Generic function to process menu events.
	 *	Grab the results of the event results of all the plugins
	 *	into a single menu options array and return it.
	 *	@param string $p_event The name of the event to process.
	 *	@return array $t_menu_options The links to be added to the menu.
	 */
	public static function getEventLinks( $p_event ) {
		$t_menu_options = array();
		$t_event_menu_options = event_signal( $p_event );
		foreach( $t_event_menu_options as $t_plugin => $t_plugin_menu_options ) {
			foreach( $t_plugin_menu_options as $t_callback => $t_callback_menu_options ) {
				if( is_array( $t_callback_menu_options ) ) {
					$t_menu_options = array_merge( $t_menu_options, $t_callback_menu_options );
				} else {
					if ( !is_null( $t_callback_menu_options ) ) {
						$t_menu_options[] = $t_callback_menu_options;
					}
				}
			}
		}
		return $t_menu_options;
	}

	/**
	 *	Cache and return any enabled default MantisBT menu links that an authenticated user
	 *	has permission to access.
	 *	@return array The links.
	 *		The key is a localization key and the element returned is
	 *		$t_links[$t_key] =
	 *			array(
	 *				'url'=>helper_mantis_url( $t_link ),
	 *				'label'=>lang_get( $t_key ),
	 *				'id'=>str_replace( '_', '-', $t_key ),
	 *			)
	 */
	public static function getAvailableLinks() {
		if( count( self::$mantis_links ) == 0 ) {
			$t_links = array();

			if( config_get( 'news_enabled' ) == ON ) {
				# Main Page
				$t_links['main_link'] = 'main_page.php';
			}

			# My View
			$t_links['my_view_link'] = 'my_view_page.php';

			# View Bugs
			$t_links['view_bugs_link'] = 'view_all_bug_page.php';

			# Changelog Page
			if( access_has_project_level( config_get( 'view_changelog_threshold' ) ) ) {
				$t_links['changelog_link'] = 'changelog_page.php';
			}

			# Roadmap Page
			if( access_has_project_level( config_get( 'roadmap_view_threshold' ) ) ) {
				$t_links['roadmap_link'] = 'roadmap_page.php';
			}

			if( auth_is_user_authenticated() ) {
				$t_protected = current_user_get_field( 'protected' );
				$t_current_project = helper_get_current_project();

				# Report Bugs
				if( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
					$t_links['report_bug_link'] = string_get_bug_report_url();
				}

				# Summary Page
				if( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
					$t_links['summary_link'] = 'summary_page.php';
				}

				# Project Documentation Page
				if( ON == config_get( 'enable_project_documentation' ) ) {
					$t_links['docs_link'] = 'proj_doc_page.php';
				}

				# Project Wiki
				if( config_get_global( 'wiki_enable' ) == ON ) {
					$t_links['wiki'] = 'wiki.php?type=project&amp;id=' . $t_current_project;
				}

				# Manage Users (admins) or Manage Project (managers) or Manage Custom Fields
				if( access_has_global_level( config_get( 'manage_site_threshold' ) ) ) {
					$t_links['manage_link'] = 'manage_overview_page.php';
				} else {
					$t_show_access = min( config_get( 'manage_user_threshold' ), config_get( 'manage_project_threshold' ), config_get( 'manage_custom_fields_threshold' ) );
					if( access_has_global_level( $t_show_access ) || access_has_any_project( $t_show_access ) ) {
						if( access_has_global_level( config_get( 'manage_user_threshold' ) ) ) {
							$t_links['manage_link'] = 'manage_user_page.php';
						} else {
							if( access_has_project_level( config_get( 'manage_project_threshold' ), $t_current_project ) && ( $t_current_project <> ALL_PROJECTS ) ) {
								$t_links['manage_link'] = 'manage_proj_edit_page.php?project_id=' . $t_current_project;
							} else {
								$t_links['manage_link'] = 'manage_proj_page.php';
							}
						}
					}
				}

				# News Page
				if ( news_is_enabled() && access_has_project_level( config_get( 'manage_news_threshold' ) ) ) 	{
					# Admin can edit news for All Projects (site-wide)
					if( ALL_PROJECTS != helper_get_current_project() || current_user_is_administrator() ) {
						$t_links['edit_news_link'] = 'news_menu_page.php';
					} else {
						$t_links['edit_news_link'] = 'login_select_proj_page.php';
					}
				}

				# Account Page (only show accounts that are NOT protected)
				if( OFF == $t_protected ) {
					$t_links['account_link'] = 'account_page.php';
				}

				if( config_get( 'time_tracking_enabled' ) && config_get( 'time_tracking_with_billing' ) && access_has_global_level( config_get( 'time_tracking_reporting_threshold' ) ) ) {
					$t_links['time_tracking_billing_link'] = 'billing_page.php';
				}

				# Logout (no if anonymously logged in)
				if( !current_user_is_anonymous() ) {
					$t_links['logout_link'] = 'logout_page.php';
				}

				# manage links
				if( access_has_global_level( config_get( 'manage_user_threshold' ) ) ) {
					$t_links['manage_users_link'] = 'manage_user_page.php';
				}
				if( access_has_project_level( config_get( 'manage_project_threshold' ) ) ) {
					$t_links['manage_projects_link'] = 'manage_proj_page.php';
				}
				if( access_has_project_level( config_get( 'tag_edit_threshold' ) ) ) {
					$t_links['manage_tags_link'] = 'manage_tags_page.php';
				}
				if( access_has_global_level( config_get( 'manage_custom_fields_threshold' ) ) ) {
					$t_links['manage_custom_field_link'] = 'manage_custom_field_page.php';
				}
				if( access_has_global_level( config_get( 'manage_global_profile_threshold' ) ) ) {
					$t_links['manage_global_profiles_link'] = 'manage_prof_menu_page.php';
				}
				if( access_has_global_level( config_get( 'manage_plugin_threshold' ) ) ) {
					$t_links['manage_plugin_link'] = 'manage_plugin_page.php';
				}

				if ( access_has_project_level( config_get( 'manage_configuration_threshold' ) ) ) {
					if ( access_has_global_level( config_get( 'view_configuration_threshold' ) ) ) {
						$t_links['manage_config_link'] = 'adm_config_report.php';
					} else {
						$t_links['manage_config_link'] = 'adm_permissions_report.php';
					}
				}

				# account links
				$t_links['account_link'] = 'account_page.php';
				$t_links['change_preferences_link'] = 'account_prefs_page.php';
				$t_links['manage_columns_config'] = 'account_manage_columns_page.php';

				if( config_get ( 'enable_profiles' ) == ON && access_has_project_level( config_get( 'add_profile_threshold' ) ) ) {
					$t_links['manage_profiles_link'] = 'account_prof_menu_page.php';
				}

				if( config_get( 'enable_sponsorship' ) == ON && access_has_project_level( config_get( 'view_sponsorship_total_threshold' ) ) && !current_user_is_anonymous() ) {
					$t_links['my_sponsorship'] = 'account_sponsor_page.php';
				}

				#manage config links
				if ( access_has_project_level( config_get( 'manage_configuration_threshold' ) ) ) {
					if ( access_has_global_level( config_get( 'view_configuration_threshold' ) ) ) {
						$t_links['configuration_report'] = 'adm_config_report.php';
					}

					$t_links['permissions_summary_report'] = 'adm_permissions_report.php';
					$t_links['manage_threshold_config'] = 'manage_config_work_threshold_page.php';
					$t_links['manage_workflow_config'] = 'manage_config_workflow_page.php';

					if ( config_get( 'relationship_graph_enable' ) ) {
						$t_links['manage_workflow_graph'] = 'manage_config_workflow_graph_page.php';
					}

					$t_links['manage_email_config'] = 'manage_config_email_page.php';
					$t_links['manage_columns_config'] = 'manage_config_columns_page.php';

				}

				# doc links
				$t_documentation_html = config_get( 'manual_url' );
				$t_links['user_documentation'] = $t_documentation_html;
				$t_links['project_documentation'] = 'proj_doc_page.php';
				if( file_allow_project_upload() ) {
					$t_links['add_file'] = 'proj_doc_add_page.php';
				}

				$t_links['print_all_bug_page_link'] = 'print_all_bug_page.php';

				# summary menu
				if( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
					$t_links['summary_link'] = 'summary_page.php';
				}
			}
			foreach( $t_links AS $t_key=>$t_link ) {
				$t_tmp = array();
				$t_tmp['url'] = helper_mantis_url( $t_link );
				$t_tmp['label'] = lang_get( $t_key );
				$t_tmp['id'] = str_replace( '_', '-', $t_key );
				self::$mantis_links[$t_key] = $t_tmp;
			}
		}
		return self::$mantis_links;
	}
}