<?php
# MantisBT - a php based bugtracking system

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
 * XML Import / Export Plugin
 * @package MantisPlugin
 * @subpackage MantisPlugin
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires MantisPlugin.class.php
 */
require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );

/**
 * XmlImportExportPlugin Class
 */
class XmlImportExportPlugin extends MantisPlugin {

	/**
	 *  A method that populates the plugin information and minimum requirements.
	 */
	function register( ) {
		$this->name = plugin_lang_get( 'title' );
		$this->description = plugin_lang_get( 'description' );
		$this->page = "config_page";

		$this->version = '1.0';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Default plugin configuration.
	 */
	public function config() {
		return array(
			"import_threshold" => ADMINISTRATOR,
			"export_threshold" => DEVELOPER,
		);
	}

	/**
	 * Plugin hooks
	 * @return array
	 */
	function hooks( ) {
		$hooks = array(
			'EVENT_MENU_MANAGE' => 'import_issues_menu',
			'EVENT_MENU_FILTER' => 'export_issues_menu',
		);
		return $hooks;
	}

	function import_issues_menu( ) {
		return array( '<a href="' . plugin_page( 'import' ) . '">' . plugin_lang_get( 'import' ) . '</a>', );
	}

	function export_issues_menu( ) {
		if( !access_has_project_level( plugin_config_get( 'export_threshold' ) ) ) {
			return array();
		}
		return array( '<a href="' . plugin_page( 'export' ) . '">' . plugin_lang_get( 'export' ) . '</a>', );
	}

	function install() {
		$result = extension_loaded("xmlreader") && extension_loaded("xmlwriter");
		if ( ! $result ) {
			#\todo returning false should trigger some error reporting, needs rethinking error_api
			error_parameters( plugin_lang_get( 'error_no_xml' ) );
			trigger_error( ERROR_PLUGIN_INSTALL_FAILED, ERROR );
		}
		return $result;
	}
}
