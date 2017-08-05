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
require_api( 'csv_api.php' );

helper_begin_long_process();

$t_date_format = config_get( 'normal_date_format' );

$f_project_id = gpc_get_int( 'project_id' );
$f_cost = gpc_get_int( 'cost' );
$f_from = gpc_get_string( 'from' );
$f_to = gpc_get_string( 'to' );

$t_new_line = csv_get_newline();
$t_separator = csv_get_separator();

billing_ensure_reporting_access( $f_project_id );

$t_show_cost = ON == config_get( 'time_tracking_with_billing' ) && $f_cost != 0;

$t_billing_rows = billing_get_for_project( $f_project_id, $f_from, $f_to, $f_cost );
$t_show_realname = config_get( 'show_realname' ) == ON;

csv_start( csv_get_default_filename() );

echo csv_escape_string( lang_get( 'issue_id' ) ) . $t_separator;
echo csv_escape_string( lang_get( 'project_name' ) ) . $t_separator;
echo csv_escape_string( lang_get( 'category' ) ) . $t_separator;
echo csv_escape_string( lang_get( 'summary' ) ) . $t_separator;

if( $t_show_realname ) {
	echo csv_escape_string( lang_get( 'realname' ) ) . $t_separator;
} else {
	echo csv_escape_string( lang_get( 'username' ) ) . $t_separator;
}

echo csv_escape_string( lang_get( 'timestamp' ) ) . $t_separator;
echo csv_escape_string( lang_get( 'minutes' ) ) . $t_separator;
echo csv_escape_string( lang_get( 'time_tracking_time_spent' ) ) . $t_separator;

if( $t_show_cost ) {
	echo csv_escape_string( 'cost' ) . $t_separator;
}

echo csv_escape_string( 'note' );
echo $t_new_line;

foreach( $t_billing_rows as $t_billing ) {
	echo csv_escape_string( bug_format_id( $t_billing['bug_id'] ) ) . $t_separator;
	echo csv_escape_string( $t_billing['project_name'] ) . $t_separator;
	echo csv_escape_string( $t_billing['bug_category'] ) . $t_separator;
	echo csv_escape_string( $t_billing['bug_summary'] ) . $t_separator;

	if( $t_show_realname ) {
		echo csv_escape_string( $t_billing['reporter_realname'] ) . $t_separator;
	} else {
		echo csv_escape_string( $t_billing['reporter_username'] ) . $t_separator;
	}

	echo csv_escape_string( date( $t_date_format, $t_billing['date_submitted'] ) ) . $t_separator;
	echo csv_escape_string( $t_billing['minutes'] ) . $t_separator;
	echo csv_escape_string( $t_billing['duration'] ) . $t_separator;

	if( $t_show_cost ) {
		echo csv_escape_string( $t_billing['cost'] ) . $t_separator;
	}

	echo csv_escape_string( $t_billing['note'] );
	echo $t_new_line;
}


