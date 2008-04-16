<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2002 - 2008  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

/**
 * API for simplifying some JSON interactions.
 */

/**
 * Get a chunk of JSON from a given URL.
 * @param string URL
 * @param string Top-level member to retrieve
 * @return multi JSON class structure
 */
function json_url( $p_url, $p_member=null ) {
	if ( ini_get( 'allow_url_fopen' ) ) {
		$t_data = file_get_contents( $p_url );
	} else {
		$t_data = `curl $p_url`;
	}
	$t_json = json_decode( $t_data );

	if ( is_null( $p_member ) ) {
		return $t_json;
	} else {
		return $t_json->$p_member;
	}
}

