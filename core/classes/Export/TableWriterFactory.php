<?php

namespace Mantis\Export;

class TableWriterFactory {
	protected static $providers = null;

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
		$t_providers = self::getAllProviders();
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

	public static function getAllProviders() {
		if( self::$providers === null ) {
			$t_plugin_items = event_signal( 'EVENT_EXPORT_DISCOVERY' );
			$t_providers = array();

			$fn_collect = function( $t_item ) use( &$t_providers ) {
				if( $t_item instanceof TableExportProvider ) {
					$t_providers[$t_item->unique_id] = $t_item;
				}
				return null;
			};
			event_process_result_type_default( $t_plugin_items, $fn_collect );
			self::$providers = $t_providers;
		}
		return self::$providers;
	}
}
