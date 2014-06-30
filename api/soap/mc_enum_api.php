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
 * Get all available status.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_status( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'status' );
}

/**
 * Get all available priorities.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_priorities( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'priority' );
}

/**
 * Get all available severities.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_severities( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'severity' );
}

/**
 * Get all available reproducibility's.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_reproducibilities( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'reproducibility' );
}

/**
 * Get all available projections.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_projections( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'projection' );
}

/**
 * Get all available etas.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_etas( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'eta' );
}

/**
 * Get all available resolutions.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_resolutions( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'resolution' );
}

/**
 * Get all available access levels.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_access_levels( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'access_levels' );
}

/**
 * Get all available project status.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_project_status( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'project_status' );
}

/**
 * Get all available project view states.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_project_view_states( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'project_view_state' );
}

/**
 * Get all available view states.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array  The requested enumeration
 */
function mc_enum_view_states( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'view_state' );
}

/**
 * Get all available custom field types.
 *
 * @param string $p_username The name of the user trying to access the enumeration.
 * @param string $p_password The password of the user.
 * @return array The requested enumeration
 */
function mc_enum_custom_field_types( $p_username, $p_password ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	return mci_explode_to_objectref( 'custom_field_type' );
}

/**
 * Get an appropriate enumeration. (Should become an internal function.)
 *
 * @param string $p_username    The name of the user trying to access the enumeration.
 * @param string $p_password    The password of the user.
 * @param string $p_enumeration The enumeration to get.
 * @return string  The requested enumeration.
 */
function mc_enum_get( $p_username, $p_password, $p_enumeration ) {
	if( !mci_validate_enum_access( $p_username, $p_password ) ) {
		return mci_soap_fault_login_failed();
	}

	# safe to call directly after login checks
	$t_user_id = auth_get_current_user_id();
	$t_lang = mci_get_user_lang( $t_user_id );

	return lang_get( $p_enumeration . '_enum_string', $t_lang );
}

/**
 * Explode a configuration enumeration name into an array structure that can
 * be safely converted into an ObjectRef structure.
 *
 * @param string $p_enumeration_name The name of the enumeration to convert.
 * @return array  The converted enumeration
 */
function mci_explode_to_objectref( $p_enumeration_name ) {
	$t_config_var_name = $p_enumeration_name . '_enum_string';
	$t_config_var_value = config_get( $t_config_var_name );
	$t_translated_values = lang_get( $t_config_var_name, mci_get_user_lang( auth_get_current_user_id() ) );

	$t_enum_values = MantisEnum::getValues( $t_config_var_value );

	$t_result = array();

	foreach ( $t_enum_values as $t_key ) {
		$t_translated = MantisEnum::getLocalizedLabel( $t_config_var_value, $t_translated_values, $t_key );

		$t_result[] = array(
			'id' => $t_key,
			'name' => $t_translated,
		);
	}
	return $t_result;
}

/**
 * Validates that the user has access to the enumeration values
 *
 * @param string $p_username Username.
 * @param string $p_password Password.
 * @return boolean true if the user has access, false otherwise
 */
function mci_validate_enum_access( $p_username, $p_password ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return false;
	}
	if( !mci_has_readonly_access( $t_user_id ) ) {
		return false;
	}

	return true;
}

/**
 * Get a localized enumeration element.
 *
 * @param integer $p_enum_id   The id of the enumeration element to retrieve.
 * @param string  $p_enum_type The type of the enumeration element (e.g. 'status', 'reproducibility' etc.).
 * @param string  $p_lang      The language for the name field.
 * @return array an array containing the id and the name of the enumeration element.
 */
function mci_enum_get_array_by_id( $p_enum_id, $p_enum_type, $p_lang ) {
	$t_result = array();
	$t_result['id'] = $p_enum_id;
	$t_result['name'] = mci_get_enum_element( $p_enum_type, $p_enum_id, $p_lang );
	return $t_result;
}

/**
 * Get the enumeration identifier given the enumeration label.
 *
 * @param string $p_enum_string The enumeration string to search in.
 * @param string $p_label       The label to search for.
 *
 * @return integer The id corresponding to the given label, or 0 if not found.
 */
function mci_get_enum_value_from_label( $p_enum_string, $p_label ) {
	$t_value = MantisEnum::getValue( $p_enum_string, $p_label );
	if( $t_value === false ) {
		return 0;
	}

	return $t_value;
}

/**
 * Get the enumeration id given an object ref.  The id is set based on the following algorithm:
 * - id from objectref.
 * - id corresponding to name in object ref.
 * - default value for the specified enumeration, if exists in configuration.
 * - first id, if object ref does not contain an id or a name.
 *
 * @param string         $p_enum       The name of the enumeration as in the MantisBT configuration file.
 * @param stdClass|array $p_object_ref An associate array with "id" and "name" keys.
 * @return integer enum id
 */
function mci_get_enum_id_from_objectref( $p_enum, $p_object_ref ) {
	$p_object_ref = SoapObjectsFactory::unwrapObject( $p_object_ref );

	if( !is_null( $p_object_ref ) && isset( $p_object_ref['id'] ) && (int)$p_object_ref['id'] != 0 ) {
		$t_id = (int)$p_object_ref['id'];
	} else {
		$t_enum = config_get( $p_enum . '_enum_string' );
		if( !is_null( $p_object_ref ) && isset( $p_object_ref['name'] ) && !is_blank( $p_object_ref['name'] ) ) {
			$t_id = mci_get_enum_value_from_label( $t_enum, $p_object_ref['name'] );
			if( $t_id == 0 ) {
				$t_id = config_get( 'webservice_' . $p_enum . '_enum_default_when_not_found' );
			}
		} else {
			$t_default_id = config_get( 'default_bug_' . $p_enum, 0 );
			if( $t_default_id == 0 ) {
				$t_array = mci_explode_to_objectref( $p_enum );
				$t_id = (int)$t_array[0]['id'];
			} else {
				$t_id = $t_default_id;
			}
		}
	}

	return $t_id;
}
