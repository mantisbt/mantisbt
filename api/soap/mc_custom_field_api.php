<?php
# MantisConnect - A webservice interface to Mantis Bug Tracker
# Copyright (C) 2004-2012  Victor Boctor - vboctor@users.sourceforge.net
# This program is distributed under dual licensing.  These include
# GPL and a commercial licenses.  Victor Boctor reserves the right to
# change the license of future releases.
# See docs/ folder for more details

/**
 * Get the custom field id given an object ref.  The id is set based on the following algorithm:
 * - id from objectref (if not zero).
 * - id corresponding to name in object ref.
 * - 0, if object ref doesn't contain an id or a name.
 *
 * @param ObjectRef  $p_object_ref   An associate array with "id" and "name" keys.
 */
function mci_get_custom_field_id_from_objectref( $p_object_ref ) {
	if( (int) $p_object_ref['id'] != 0 ) {
		$t_id = (int) $p_object_ref['id'];
	} else {
		if( !is_blank( $p_object_ref['name'] ) ) {
			$t_id = custom_field_get_id_from_name( $p_object_ref['name'] );
		} else {
			$t_id = 0;
		}
	}

	return $t_id;
}
