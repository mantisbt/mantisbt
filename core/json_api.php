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
 * API for simplifying some JSON interactions.
 * @package CoreAPI
 * @subpackage JSONAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * requires url_api
 */
require_once( 'url_api.php' );

/**
 * Get a chunk of JSON from a given URL.
 * @param string URL
 * @param string Top-level member to retrieve
 * @return multi JSON class structure
 */
function json_url( $p_url, $p_member = null ) {
	$t_data = url_get( $p_url );
	$t_json = json_decode( utf8_encode($t_data) );

	if( is_null( $p_member ) ) {
		return $t_json;
	} else {
		return $t_json->$p_member;
	}
}
