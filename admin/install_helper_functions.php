<?php
# MantisBT - a php based bugtracking system

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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

function check_php_version( $p_version ) {
	if( $p_version == PHP_MIN_VERSION ) {
		return true;
	} else {
		if( function_exists( 'version_compare' ) ) {
			if( version_compare( phpversion(), PHP_MIN_VERSION, '>=' ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}

/**
 * legacy pre-1.2 date function  used by schema.php
 * return a DB compatible date, representing a unixtime(0) + 1 second. Internally considered a NULL date
 * @return string Formatted Date for DB insertion e.g. 1970-01-01 00:00:00 ready for database insertion
 */
function db_null_date() {
	global $g_db;

	return $g_db->BindTimestamp( $g_db->UserTimeStamp( 1, 'Y-m-d H:i:s', true ) );
}

/**
 * legacy pre-1.2 date function  used by install_functions.php
 * generate a integer unixtimestamp of a date
 * @param $p_date Date
 * @param bool $p_gmt whether to use GMT or current timezone (default false)
 * @return int unix timestamp of a date
 * @todo review date handling
 */
function db_unixtimestamp( $p_date = null, $p_gmt = false ) {
	global $g_db;

	if( null !== $p_date ) {
		$p_timestamp = $g_db->UnixTimeStamp( $p_date, $p_gmt );
	} else {
		$p_timestamp = time();
	}
	return $p_timestamp;
}