<?php
# MantisBT - A PHP based bugtracking system

# Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 *	Default Menu Configuration Variables
 *
 *	This file should not be changed. If you want to override any of the values
 *	defined here, define them in a file called config_menu_inc.php, which will
 *	be loaded after this file.
 * 
 *	These configurations enable more advanced customization of mantis menus.
 *	The include and exclude configuration options below determine on which pages the 
 *	menus configured in 'g_menus' are displayed.  The format for the configuration
 *	names is 'g_exclude_' . menu_key . '_menu_pages' or 'g_include_' . menu_key . '_menu_pages'
 *	'menu_key' is the array key in the 'g_menus' configuration array.
 
 *	The menu class checks for both configuration options.  
 *		If neither option exists, the menu is considered global and is displayed on every page.
 *		If an 'exclude' option exists for the menu, the menu is considered global and is included on every page except those in the configuration list. 
 *		If an 'include' option exists for the menu, it is only included on pages specified in the configuration list.
 *
 *	Developers may extend and override the MantisMenu class to modify or completely replace the default MantisBT menus.
 *	To do so, create a config_menus_inc.php file at the root of your mantis site.
 *	Add the $g_menu_class variable and the name of the new menu class to the new file.  Be sure it extends the MantisMenu class.
 *	Add the $g_menus array variable and add the menu names and function names as defined in the new menu class.
 *
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */


/**
 *	The name of the default class.  Developers may extend this class to 
 *	customize the menus
 */
$g_menu_class = 'MantisMenu';

/**
 *	If ON this option causes submenus to be nested in the main menu ( manage, account, summary, etc. )
 */
$g_nested_menus = OFF;

/**
 *	Alphabetize submenus
 */
$g_alpha_sort_nested_menus = OFF;

/**
 *	list of default menus and the (static) class functions to build them
 */
$g_menus = array(
	'main'=>'getMainMenu',
	'manage'=>'getManageMenu',
	'manage_config' => 'getManageConfigMenu',
	'account' => 'getAccountMenu',
	'summary'=>'getSummaryMenu',
	'graphs'=>'getGraphsMenu',
	'doc'=>'getDocMenu'
);

/**
 *	Configurations are used to determine which pages menus should be displayed on.
 */

/**
 *	List of pages which should exclude the main menu
 */	
$g_exclude_main_menu_pages = array();

/**
 *	List of pages on which to include the manage menu.
 */
$g_include_manage_menu_pages = array( 
	'account_prof_edit_page.php',# depends if global profiles or not
	'account_prof_menu_page.php', # depends if global profiles or not
	'adm_config_report.php',
	'adm_permissions_report.php',
	'manage_config_columns_page.php',
	'manage_config_email_page.php',
	'manage_config_work_threshold_page.php',
	'manage_config_workflow_graph_page.php',
	'manage_config_workflow_page.php',
	'manage_custom_field_edit_page.php',
	'manage_custom_field_page.php',
	'manage_overview_page.php',
	'manage_plugin_page.php',
	'manage_proj_cat_edit_page.php',
	'manage_proj_create_page.php',
	'manage_proj_edit_page.php',
	'manage_proj_page.php',
	'manage_proj_ver_edit_page.php',
	'manage_tags_page.php',
	'manage_user_create_page.php',
	'manage_user_edit_page.php',
	'manage_user_page.php',
	'XmlImportExport'=>array(
		'import',
		'import_action',
	),
	'MantisGraph'=>array(
		'config',
	),
	'MantisCoreFormatting'=>array(
		'config',
	),
);

$g_include_manage_config_menu_pages = array(
	'adm_config_report.php',
	'adm_permissions_report.php',
	'manage_config_columns_page.php',
	'manage_config_email_page.php',
	'manage_config_work_threshold_page.php',
	'manage_config_workflow_graph_page.php',
	'manage_config_workflow_page.php',
);

$g_include_account_menu_pages = array(
	'account_page.php',
	'account_prefs_page.php',
	'account_prof_edit_page.php', # depends if global profiles or not
	'account_prof_menu_page.php', # depends on if global profiles or not.
	'account_sponsor_page.php',
	'account_manage_columns_page.php',
);

$g_include_doc_menu_pages = array(
	'proj_doc_add_page.php',
	'proj_doc_edit_page.php',
	'proj_doc_page.php',
);

$g_include_summary_menu_pages = array(
	'summary_page.php',
	'MantisGraph'=>array(
		'summary_graph_imp_resolution.php',
		'summary_graph_imp_severity.php',
		'summary_graph_imp_category.php',
		'summary_graph_imp_priority.php',
		'summary_jpgraph_page',
		'summary_graph_imp_status.php',
	),
);

$g_include_graphs_menu_pages = array(
	'summary_page.php',
	'MantisGraph'=>array(
		'summary_jpgraph_page',
		'summary_graph_imp_resolution.php',
		'summary_graph_imp_severity.php',
		'summary_graph_imp_category.php',
		'summary_graph_imp_priority.php',
		'summary_graph_imp_status.php',
	),
);