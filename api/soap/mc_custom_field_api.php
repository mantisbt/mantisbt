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
 * A webservice interface to Mantis Bug Tracker
 *
 * @package MantisBT
 * @copyright Copyright 2004  Victor Boctor - vboctor@users.sourceforge.net
 * @copyright Copyright 2005  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Get the custom field id given an object ref.  The id is set based on the following algorithm:
 * - id from objectref (if not zero).
 * - id corresponding to name in object ref.
 * - 0, if object ref doesn't contain an id or a name.
 *
 * @param stdClass $p_object_ref An associate array with "id" and "name" keys.
 * @return integer
 */
function mci_get_custom_field_id_from_objectref( stdClass $p_object_ref ) {
	$p_object_ref = ApiObjectFactory::objectToArray( $p_object_ref );

	if( isset( $p_object_ref['id'] ) && (int) $p_object_ref['id'] != 0 ) {
		$t_id = (int)$p_object_ref['id'];
	} else {
		if( isset( $p_object_ref['name'] ) && !is_blank( $p_object_ref['name'] ) ) {
			$t_id = custom_field_get_id_from_name( $p_object_ref['name'] );
		} else {
			$t_id = 0;
		}
	}

	return $t_id;
}
