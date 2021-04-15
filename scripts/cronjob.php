#!/usr/bin/php -q
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
# See the README and LICENSE files for details

/**
 * Cron script to allow scheduled execution of plugin tasks.
 *
 * @package scripts
 * @copyright Copyright 2021  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://mantisbt.org
 */

/**
 * Global Bypass http headers
 */
global $g_bypass_headers;
$g_bypass_headers = 1;

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

# Make sure this script doesn't run via the webserver
if( php_sapi_name() != 'cli' ) {
	echo basename( __FILE__ ) . " is not allowed to run through the webserver.\n";
	exit( 1 );
}

event_signal( 'EVENT_CRONJOB' );
exit( 0 );
