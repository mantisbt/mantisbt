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
 * Mantis Plugin Handling
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */

/**
 * Base class that implements basic plugin functionality
 * and integration with MantisBT. See the Mantis wiki for
 * more information.
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
	 * @return void
	 */
	abstract public function register();

	/**
	 * this function allows your plugin to set itself up, include any
	 * necessary API's, declare or hook events, etc.
	 * Alternatively, your plugin can hook the EVENT_PLUGIN_INIT event
	 * that will be called after all plugins have been initialized.
	 * @return void
	 */
	public function init() {}

	/**
	 * This function allows plugins to add new error messages for Mantis usage
	 *
	 * @return array The error_name=>error_message list to add
	 */
	public function errors() {
		return array();
	}

	/**
	 * return an array of default configuration name/value pairs
	 * @return array
	 */
	public function config() {
		return array();
	}

	/**
	 * This function allows you to declare a new event, or a set of events that the plugin will
	 * trigger. This function returns an associative array with event names as the key and the event
	 * type as the value.
	 *
	 * For example, to add an event "foo" to our Example plugin that does not expect a return value
	 * (an "execute" event type), and another event 'bar' that expects a single value that gets
	 * modified by each hooked function (a "chain" event type):
	 *
	 * <code>
	 * class ExamplePlugin extends MantisPlugin {
	 * ...
	 * function events() {
	 *   return array(
	 *       'EVENT_EXAMPLE_FOO' => EVENT_TYPE_EXECUTE,
	 *       'EVENT_EXAMPLE_BAR' => EVENT_TYPE_CHAIN,
	 *   );
	 *  }
	 * }
	 * </code>
	 *
	 * When the Example plugin is loaded, the event system in MantisBT will add these two events to
	 * its list of events, and will then allow other plugins or functions to hook them. Naming the
	 * events "EVENT_PLUGINNAME_EVENTNAME" is not necessary, but is considered best practice to
	 * avoid conflicts between plugins.
	 * @return array
	 */
	public function events() {
		return array();
	}

	/**
	 * Hooking other events (or events from your own plugin) is almost identical to declaring them.
	 * Instead of passing an event type as the value, your plugin must pass the name of a class
	 * method on your plugin that will be called when the event is triggered. For our Example
	 * plugin, we'll create a foo() and bar() method on our plugin class, and hook them to the
	 * events we declared earlier.
	 *
	 * <code>
	 * class ExamplePlugin extends MantisPlugin {
	 * 		...
	 * 		function hooks() {
	 *   	return array(
	 *    	 	'EVENT_EXAMPLE_FOO' => 'foo',
	 *       	'EVENT_EXAMPLE_BAR' => 'bar',
	 *   	);
	 * 	}
	 * 	function foo( $p_event ) {
	 *  	...
	 * 	}
	 * 	function bar( $p_event, $p_chained_param ) {
	 *  	...
	 *   	return $p_chained_param;
	 * 	}
	 * }
	 * </code>
	 * Note that both hooked methods need to accept the $p_event parameter, as that contains the
	 * event name triggering the method (for cases where you may want a method hooked to multiple
	 * events).
	 * The bar() * method also accepts and returns the chained parameter in order to match the
	 * expectations of the "bar" event.
	 * @return array
	 */
	public function hooks() {
		return array();
	}

	/**
	 * Array of Database Schema changes for the plugin
	 *
	 * For example, the following code example would try and perform 4 schema updates:
	 * 1. create a user table with a user id column.
	 * 2. add an additional column called 'count'.
	 * 3. Updates the existing database data by adding 1 to count
	 * 4. Tries to create a unique index on the column userid
	 *
	 * <code>
	 * function schema() {
	 *	return array(
	 *		array( 'CreateTableSQL', array( plugin_table( 'user' ), "
	 *	 		user_id		I		NOTNULL UNSIGNED PRIMARY",
	 *			array('mysql' => 'ENGINE=MyISAM DEFAULT CHARSET=utf8', 'pgsql' => 'WITHOUT OIDS')));
	 *		array( 'AddColumnSQL', array( plugin_table( 'user' ), "
	 *			count	I		NOTNULL UNSIGNED DEFAULT '0' " ) ),
	 *		array( 'UpdateSQL', array( plugin_table( 'user' ), " SET count=count+1" ) ),
	 *		array( 'CreateIndexSQL',
	 *         array( 'idx_<plugin>_userid', plugin_table('user'), 'userid', array('UNIQUE'))),
	 *  );
	 * }
	 * </code>
	 * @return array
	 */
	public function schema() {
		return array();
	}

	/**
	  * Perform pre-installation operations
	  *
	  * This method is called before installing the given plugin.
	  * It can be used to add pre-install checks on external requirements
	  *
	  * @return bool true if install can proceed
	  */
	public function install() {
		return true;
	}

	/**
	 * This callback is executed after the normal schema upgrade process has executed.
	 * This gives your plugin the chance to convert or normalize data after an upgrade
	 *
	 * @todo It is possible to call php functions from within the schema upgrade itself, so really needed?
	 *
	 * @param integer $p_schema Schema Version ID.
	 * @return boolean
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
	 * @return void
	 */
	public function uninstall() {
	}

	### Core plugin functionality ###

	/**
	 * Plugin Basename
	 */
	public $basename	= null;

	/**
	 * Constructor
	 *
	 * @param string $p_basename Plugin Basename.
	 */
	final public function __construct( $p_basename ) {
		$this->basename = $p_basename;
		$this->register();
	}

	/**
	 * Initialisation
	 * @return void
	 */
	final public function __init() {
		plugin_config_defaults( $this->config() );
		event_declare_many( $this->events() );
		plugin_event_hook_many( $this->hooks() );

		$this->init();
	}
}
