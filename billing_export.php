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
 * Export billing information to csv
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses billing_api.php
 * @uses bug_api.php
 * @uses csv_api.php
 */

require_once( 'core.php' );
require_api( 'billing_api.php' );
require_api( 'bug_api.php' );
require_api( 'export_api.php' );

use Mantis\Export;

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

helper_begin_long_process();

$t_date_format = config_get( 'normal_date_format' );

$f_project_id = gpc_get_int( 'project_id' );
$f_cost = gpc_get_int( 'cost' );
$f_from = gpc_get_string( 'from' );
$f_to = gpc_get_string( 'to' );
$f_include_subprojects = gpc_get_bool( 'include_subprojects' );
$f_export_type = gpc_get_string( 'type' );

billing_ensure_reporting_access( $f_project_id );

$t_show_cost = ON == config_get( 'time_tracking_with_billing' ) && $f_cost != 0;

$t_billing_rows = billing_get_for_project( $f_project_id, $f_from, $f_to, $f_cost, $f_include_subprojects );

$t_provider = Export\TableWriterFactory::getProviderByType( $f_export_type );
if( !$t_provider ) {
	# @TODO error
	exit();
}

$t_filename = export_get_default_filename() . '.' . $t_provider->file_extension;
$t_writer = Export\TableWriterFactory::createWriterFromProvider( $t_provider );
$t_writer->openToBrowser( $t_filename );

$t_titles = array();
$t_titles[] = lang_get( 'issue_id' );
$t_titles[] = lang_get( 'project_name' );
$t_titles[] = lang_get( 'category' );
$t_titles[] = lang_get( 'summary' );
$t_titles[] = lang_get( 'username' );
$t_titles[] = lang_get( 'timestamp' );
$t_titles[] = lang_get( 'minutes' );
$t_titles[] = lang_get( 'time_tracking_time_spent' );
if( $t_show_cost ) {
	$t_titles[] = 'cost';
}
$t_titles[] = 'note';

$t_writer->addRowFromArray( $t_titles );

foreach( $t_billing_rows as $t_billing ) {
	$t_values = array();
	$t_values[] = bug_format_id( $t_billing['bug_id'] );
	$t_values[] = $t_billing['project_name'];
	$t_values[] = $t_billing['bug_category'];
	$t_values[] = $t_billing['bug_summary'];
	$t_values[] = $t_billing['reporter_name'];
	$t_values[] = date( $t_date_format, $t_billing['date_submitted'] );
	$t_values[] = $t_billing['minutes'];
	$t_values[] = $t_billing['duration'];
	if( $t_show_cost ) {
		$t_values[] = $t_billing['cost'];
	}
	$t_values[] = $t_billing['note'];
	$t_writer->addRowFromArray( $t_values );
}

$t_writer->close();


