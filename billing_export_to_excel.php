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
 * Excel (2003 SP2 and above) export page for billing information
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses billing_api.php
 * @uses bug_api.php
 * @uses excel_api.php
 */

require_once( 'core.php' );
require_api( 'billing_api.php' );
require_api( 'bug_api.php' );
require_api( 'excel_api.php' );

helper_begin_long_process();

$t_filename = excel_get_default_filename();
$t_date_format = config_get( 'normal_date_format' );

$f_project_id = gpc_get_int( 'project_id' );
$f_cost = gpc_get_int( 'cost' );
$f_from = gpc_get_string( 'from' );
$f_to = gpc_get_string( 'to' );

billing_ensure_reporting_access( $f_project_id );

$t_show_cost = ON == config_get( 'time_tracking_with_billing' ) && $f_cost != 0;

$t_billing_rows = billing_get_for_project( $f_project_id, $f_from, $f_to, $f_cost );
$t_show_realname = config_get( 'show_realname' ) == ON;

header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
header( 'Pragma: public' );
header( 'Content-Disposition: attachment; filename="' . urlencode( file_clean_name( $t_filename ) ) . '.xml"' ) ;

echo excel_get_header( $t_filename );
echo excel_get_start_row();
echo excel_format_column_title( lang_get( 'issue_id' ) );
echo excel_format_column_title( lang_get( 'project_name' ) );
echo excel_format_column_title( lang_get( 'category' ) );
echo excel_format_column_title( lang_get( 'summary' ) );

if( $t_show_realname ) {
	echo excel_format_column_title( lang_get( 'realname' ) );
} else {
	echo excel_format_column_title( lang_get( 'username' ) );
}

echo excel_format_column_title( lang_get( 'timestamp' ) );
echo excel_format_column_title( lang_get( 'minutes' ) );
echo excel_format_column_title( lang_get( 'time_tracking_time_spent' ) );

if( $t_show_cost ) {
	echo excel_format_column_title( 'cost' );
}

echo excel_format_column_title( 'note' );
echo '</Row>';

foreach( $t_billing_rows as $t_billing ) {
	echo "\n<Row>\n";
	echo excel_prepare_number( $t_billing['bug_id'] );
	echo excel_prepare_string( $t_billing['project_name'] );
	echo excel_prepare_string( $t_billing['bug_category'] );
	echo excel_prepare_string( $t_billing['bug_summary'] );

	if( $t_show_realname ) {
		echo excel_prepare_string( $t_billing['reporter_realname'] );
	} else {
		echo excel_prepare_string( $t_billing['reporter_username'] );
	}

	echo excel_prepare_string( date( $t_date_format, $t_billing['date_submitted'] ) );
	echo excel_prepare_number( $t_billing['minutes'] );
	echo excel_prepare_string( $t_billing['duration'] );

	if( $t_show_cost ) {
		echo excel_prepare_string( $t_billing['cost'] );
	}

	echo excel_prepare_string( $t_billing['note'] );
	echo "</Row>\n";
}

echo excel_get_footer();

