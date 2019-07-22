<?php

namespace Mantis\Export;

class TableWriterFactory {
	protected static $providers = null;
	protected static $providers_enabled = null;

	public static function createFromType( $p_type ) {
		switch( $p_type ) {
			case 'csv':
				return new MantisCsvWriter();
			case 'excel_xml':
				return new MantisExcelWriter();
		}
	}

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

	public static function getProviderById( $p_id ) {
		$t_providers = self::getAllProviders();
		if( isset( $t_providers[$p_id] ) ) {
			return $t_providers[$p_id];
		}
		return null;
	}

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

	public static function getProviders() {
		if( null === self::$providers_enabled ) {
			self::getAllProviders();
		}
		return self::$providers_enabled;
	}

	public static function getAllProviders() {
		if( self::$providers === null ) {
			$t_config = config_get( 'export_plugins', array(), ALL_USERS, ALL_PROJECTS );
			$t_plugin_items = event_signal( 'EVENT_EXPORT_DISCOVERY' );
			$t_providers = array();
			$t_providers_enabled = array();
			$t_config_updated = false;

			$fn_collect = function( $t_item ) use( &$t_providers, &$t_providers_enabled, &$t_config, &$t_config_updated ) {
				if( $t_item instanceof TableExportProvider ) {
					$t_id = $t_item->unique_id;
					$t_providers[$t_id] = $t_item;
					if( !isset( $t_config[$t_id] ) ) {
						$t_config[$t_id] = array( 'enabled' => $t_item->enabled_by_default );
						$t_config_updated = true;
					}
					if( $t_config[$t_id]['enabled'] ) {
						$t_providers_enabled[$t_id] = $t_item;
					}
				}
				return null;
			};
			event_process_result_type_default( $t_plugin_items, $fn_collect );
			self::$providers = $t_providers;
			self::$providers_enabled = $t_providers_enabled;
			if( $t_config_updated ) {
				config_set( 'export_plugins', $t_config, ALL_USERS, ALL_PROJECTS );
			}
		}
		return self::$providers;
	}
}
