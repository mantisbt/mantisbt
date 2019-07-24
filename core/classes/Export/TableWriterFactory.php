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
 * @package MantisBT
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

namespace Mantis\Export;

/**
 * An utility class that contains functions to manage the export system objects
 * and properties provided by the plugins
 */
class TableWriterFactory {
	# cache of all providers. Contains TableExportProvider objects
	protected static $providers = null;
	# cache of enabled providers. Contains TableExportProvider objects
	protected static $providers_enabled = null;

	/**
	 * From a provider definition, creates a TableWriter object, by calling the plugin and
	 * requesting the object.
	 * An additional $p_context is used to provide additional information to the plugin,
	 * related to the requester. Information like the user is, or the project id, may
	 * be used by the plugin to apply different default configurations when creating
	 * the writer object.
	 * GET and POST parameters are also passed, which can be used by the plugin to parse
	 * specific values from forms, especially for those custom form inputs that may have
	 * been rendered by the EVENT_EXPORT_OPTIONS_FORM event.
	 *
	 * @param \Mantis\Export\TableExportProvider $p_provider	The provider definition to create a Writer instance from-
	 * @param array $p_context	An array with additional runtime information with information about the requester.
	 * @return null|TableWriterInterface	An instantiated TableWriter object
	 */
	public static function createWriterFromProvider( $p_provider, array $p_context = array() ) {
		if( $p_provider instanceof TableExportProvider ) {
			$t_id = $p_provider->unique_id;
		} else {
			$t_id = $p_provider;
		}
		$t_default_context = array(
				'unique_id' => $t_id,
				'user_id' => auth_get_current_user_id(),
				'project_id' => helper_get_current_project(),
				'request' => array_merge( $_GET, $_POST ),
			);
		$t_context = $p_context + $t_default_context;
		$t_object = event_signal( 'EVENT_EXPORT_REQUEST', array( $t_id, $t_context ) );
		return $t_object;
	}

	/**
	 * Return a provider definition based on its unique identifier
	 * @param strinf $p_id	An export provider identifier
	 * @return null|\Mantis\Export\TableExportProvider
	 */
	public static function getProviderById( $p_id ) {
		$t_providers = self::getAllProviders();
		if( isset( $t_providers[$p_id] ) ) {
			return $t_providers[$p_id];
		}
		return null;
	}

	/**
	 * This is a compatibility method to guess a suitable provider, from any of
	 * those that are installed, that meets the format types used in the old
	 * mantis exports, namely: csv and excel export formats
	 * If a suitable provider is not found, fallbacks to any other of the available ones.
	 * @param string $p_type	Type of export as "csv" or "excel"
	 * @return null|\Mantis\Export\TableExportProvider
	 */
	public static function getProviderByType( $p_type ) {
		$t_map = array (
			'csv' => array( 'csv' ),
			'excel' => array( 'xlsx', 'xls', 'ods' ),
			);
		$t_providers = self::getProviders();
		if( isset( $t_map[$p_type] ) ) {
			$t_targets = $t_map[$p_type];
			foreach( $t_providers as $t_prov ) {
				if( in_array( $t_prov->file_extension, $t_targets ) ) {
					return $t_prov;
				}
			}
		}
		return null;
	}

	/**
	 * Returns a list of all available (and enabled) export providers
	 * @return array	Array of TableExportProvider objects
	 */
	public static function getProviders() {
		if( null === self::$providers_enabled ) {
			self::getAllProviders();
		}
		return self::$providers_enabled;
	}

	/**
	 * Returns a list of all available (enabled or not) export providers
	 * @return array	Array of TableExportProvider objects
	 */
	public static function getAllProviders() {
		if( self::$providers === null ) {
			$t_config = config_get( 'export_plugins', array(), ALL_USERS, ALL_PROJECTS );
			$t_plugin_items = event_signal( 'EVENT_EXPORT_DISCOVERY' );
			$t_providers = array();
			$t_providers_enabled = array();
			$t_config_updated = false;

			/**
			 * function to treat each of the items returned by the plugins
			 */
			$fn_collect = function( $t_item ) use( &$t_providers, &$t_providers_enabled, &$t_config, &$t_config_updated ) {
				if( $t_item instanceof TableExportProvider ) {
					$t_id = $t_item->unique_id;
					$t_providers[$t_id] = $t_item;
					# if a provider is not present in the configuration, update the configuration
					# this ensures automatic setup after a plugin installation
					if( !isset( $t_config[$t_id] ) ) {
						$t_config[$t_id] = array( 'enabled' => $t_item->enabled_by_default );
						$t_config_updated = true;
					}
					if( $t_config[$t_id]['enabled'] ) {
						$t_providers_enabled[$t_id] = $t_item;
					}
				}
				# no need to return anything, as we have already filled all our collections
				return null;
			};
			event_process_result_type_default( $t_plugin_items, $fn_collect );
			self::$providers = $t_providers;
			self::$providers_enabled = $t_providers_enabled;
			if( $t_config_updated ) {
				# update the configuration, if modified.
				config_set( 'export_plugins', $t_config, ALL_USERS, ALL_PROJECTS );
			}
		}
		return self::$providers;
	}

	/**
	 * Returns the default provider for a user, as defined by configuration options.
	 * The 'export_default_plugin' option can be defined at global level, and also
	 * for each user as config override.
	 * If there is not a defined provider, or the one is not available, a fallback
	 * for any other of the available ones will be returned.
	 *
	 * @param integer $p_user_id	User id to get his default provider
	 * @return null|\Mantis\Export\TableExportProvider
	 */
	public static function getDefaultProvider( $p_user_id = null ) {
		if( null === $p_user_id ) {
			$p_user_id = auth_get_current_user_id();
		}
		$t_default_id = config_get( 'export_default_plugin', null, $p_user_id );
		$t_providers = self::getProviders();
		if( $t_default_id ) {
			if( isset( $t_providers[$t_default_id] ) ) {
				return $t_providers[$t_default_id];
			}
		}
		if( !empty( $t_providers ) ) {
			return reset( $t_providers );
		}
		return null;
	}
}
