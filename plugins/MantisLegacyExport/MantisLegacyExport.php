<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

use \Mantis\Export\TableExportProvider;

/**
 * This plugin provides the legacy export functionality for csv and excel/xml formats
 * Also, provides the old api files for backward compatibility.
 */
class MantisLegacyExportPlugin extends \MantisPlugin {
	function register() {
		$this->name = 'MantisLegacyExport';
		$this->description = 'MantisLegacyExport';
		$this->page = '';

		$this->version = MANTIS_VERSION;
		$this->requires = array(
			'MantisCore' => '2.22.0-dev',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Executed afer plugin initialization (only if enabled)
	 */
	function init() {
		# the old api files are included in the global namespace
		# so they are available in the same way as core files
		plugin_require_api( 'include/csv_api.php' );
		plugin_require_api( 'include/excel_api.php' );
	}

	function hooks() {
		$t_hooks = array();
		$t_hooks['EVENT_EXPORT_DISCOVERY'] = 'ev_export_discovery';
		$t_hooks['EVENT_EXPORT_REQUEST'] = 'ev_export_request';
		return $t_hooks;
	}

	/**
	 * Returns response to the event EVENT_EXPORT_DISCOVERY
	 * Builds each provider definition
	 * @param string $p_event	Event name
	 * @return array	Array ot TableExportProvider objects
	 */
	function ev_export_discovery( $p_event ) {
		$t_csv = new TableExportProvider();
		$t_csv->unique_id = 'MantisLegacyExport_csv';
		$t_csv->file_extension = 'csv';
		$t_csv->short_name = 'Text CSV';
		$t_csv->provider_name = 'Mantis';

		$t_excel = new TableExportProvider();
		$t_excel->unique_id = 'MantisLegacyExport_excel';
		$t_excel->file_extension = 'xml';
		$t_excel->short_name = 'Excel XML';
		$t_excel->provider_name = 'Mantis';

		return array( $t_csv, $t_excel );
	}

	/**
	 * Returns response to the event EVENT_EXPORT_REQUEST
	 * @param string $p_event	Event name
	 * @param string $p_id		The requested provider identifier
	 * @return \Mantis\Export\TableWriterInterface	An instantiated writer object
	 */
	function ev_export_request( $p_event, $p_id ) {
		plugin_require_api( 'classes/ObFileWriter.php' );
		switch( $p_id ) {
			case 'MantisLegacyExport_csv':
				plugin_require_api( 'classes/MantisCsvWriter.php' );
				return new MantisLegacyExport\MantisCsvWriter();
			case 'MantisLegacyExport_excel':
				plugin_require_api( 'classes/MantisExcelWriter.php' );
				return new MantisLegacyExport\MantisExcelWriter();
		}
		return null;
	}
}