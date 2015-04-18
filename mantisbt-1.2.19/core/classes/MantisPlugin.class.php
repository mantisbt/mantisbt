<?php
# MantisBT - a php based bugtracking system

# Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.

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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 */

/**
 * Base class that implements basic plugin functionality
 * and integration with MantisBT. See the Mantis wiki for
 * more information.
 * @package MantisBT
 * @subpackage classes 
 */
abstract class MantisPlugin {

	/**
	 * name - Your plugin's full name. Required value.
	 */
	public $name		= null;
	/**
	 * description - A full description of your plugin.
	 */
	public $description	= null;
	/**
	 * page - The name of a plugin page for further information and administration.
	 */
	public $page		= null;
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $version		= null;
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies. 
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $requires	= null;
	/**
	 * An array of key/value pairs of basename/version  plugin dependencies (soft dependency)
	 */
	public $uses		= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $author		= null;
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $contact		= null;
	/**
	 * url - A web address for your plugin.
	 */
	public $url			= null;

	/**
	 * this function registers your plugin - must set at least name and version
	 */
	abstract public function register();

	/**
	 * this function allows your plugin to set itself up, include any necessary API's, declare or hook events, etc. 
	 * Alternatively, your can plugin can hook the EVENT_PLUGIN_INIT event that will be called after all plugins have be initialized.
	 */
	public function init() {}

	public function errors() {
		return array();
	}

	/**
	 * return an array of default configuration name/value pairs
	 */
	public function config() {
		return array();
	}

	public function events() {
		return array();
	}

	public function hooks() {
		return array();
	}

	public function schema() {
		return array();
	}

	public function install() {
		return true;
	}

	/**
	 * This callback is executed after the normal schema upgrade process has executed. 
	 * This gives your plugin the chance to convert or normalize data after an upgrade
	 */
	public function upgrade( $p_schema ) {
		return true;
	}

	/**
	 * This callback is executed after the normal uninstallation process, and should 
	 * handle such operations as reverting database schemas, removing unnecessary data, 
	 * etc. This callback should be used only if Mantis would break when this plugin 
	 * is uninstalled without any other actions taken, as users may not want to lose 
	 * data, or be able to re-install the plugin later.
	 */
	public function uninstall() {
	}

	### Core plugin functionality ###

	public $basename	= null;
	final public function __construct( $p_basename ) {
		$this->basename = $p_basename;
		$this->register();
	}

	final public function __init() {
		plugin_config_defaults( $this->config() );
		event_declare_many( $this->events() );
		plugin_event_hook_many( $this->hooks() );

		$this->init();
	}
}

/**
 * MantisBT Core Plugin
 * Used to give other plugins a permanent core plugin to 'require' for compatibility.
 * Can/should not be used as a base class.
 * @package MantisBT
 * @subpackage classes 
 */
final class MantisCorePlugin extends MantisPlugin {
	function register() {
		$this->name = 'MantisBT Core';
		$this->description = 'Core plugin API for the Mantis Bug Tracker.';

		$this->version = MANTIS_VERSION;

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}
}
