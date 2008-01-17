<?php
	# MantisConnect - A webservice interface to Mantis Bug Tracker
	# Copyright (C) 2004-2007  Victor Boctor - vboctor@users.sourceforge.net
	# This program is distributed under dual licensing.  These include
	# GPL and a commercial licenses.  Victor Boctor reserves the right to
	# change the license of future releases.
	# See docs/ folder for more details

	# --------------------------------------------------------
	# $Id: mc_enum_api.php,v 1.1 2007-07-18 06:52:53 vboctor Exp $
	# --------------------------------------------------------


	/**
	 * Get all available status.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_status( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'status' ) );
	}

	/**
	 * Get all available priorities.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_priorities( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'priority' ) );
	}

	/**
	 * Get all available severities.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_severities( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'severity' ) );
	}

	/**
	 * Get all available reproducibilities.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_reproducibilities( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'reproducibility' ) );
	}

	/**
	 * Get all available projections.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_projections( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'projection' ) );
	}

	/**
	 * Get all available etas.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_etas( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'eta' ) );
	}

	/**
	 * Get all available resolutions.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_resolutions( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'resolution' ) );
	}

	/**
	 * Get all available access levels.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_access_levels( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'access_levels' ) );
	}

	/**
	 * Get all available project status.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_project_status( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'project_status' ) );
	}

	/**
	 * Get all available project view states.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_project_view_states( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'project_view_state' ) );
	}

	/**
	 * Get all available view states.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_view_states( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'view_state' ) );
	}

	/**
	 * Get all available custom field types.
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @return Array  The requested enumeration
	 */
	function mc_enum_custom_field_types( $p_username, $p_password ) {
		return mci_explode_to_objectref( mc_enum_get( $p_username, $p_password, 'custom_field_type' ) );
	}

	/**
	 * Get an appropriate enumeration. (Should become an internal function.)
	 *
	 * @param string $p_username  The name of the user trying to access the enumeration.
	 * @param string $p_password  The password of the user.
	 * @param string $p_enumeration  The enumeration to get.
	 * @return string  The requested enumeration.
	 */
	function mc_enum_get( $p_username, $p_password, $p_enumeration ) {
		$t_user_id = mci_check_login( $p_username, $p_password );
		if ( $t_user_id === false ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
		if ( !mci_has_readonly_access( $t_user_id ) ) {
			return new soap_fault( 'Client', '', 'Access Denied' );
		}
		$t_lang = mci_get_user_lang( $t_user_id );
		return lang_get( $p_enumeration . '_enum_string', $t_lang );
	}

	/**
	 * Explode a configuration enumeration string into an array structure that can
	 * be safely converted into an ObjectRef structure.
	 *
	 * @param string $p_config_enum_string  The string to convert
	 * @return Array  The converted enumeration
	 */
	function mci_explode_to_objectref( $p_config_enum_string ) {
		if( get_class( $p_config_enum_string ) == 'soap_fault' ) {
			return $p_config_enum_string;
		}
		foreach( explode_enum_string( $p_config_enum_string ) as $t_enum_element ) {
			list($t_id, $t_name) = explode_enum_arr( $t_enum_element );
			$t_result[] = array(
			  'id'		=> $t_id,
			  'name'	=> $t_name
			);
		};
		return $t_result;
	}

	/**
	 * Get a localized enumeration element.
	 *
	 * @param integer $p_enum_id  The id of the enumeration element to retrieve.
	 * @param String $p_enum_type  The type of the enumeration element (e.g. 'status', 'reproducibility' etc.).
	 * @param String $p_lang  The language for the name field.
	 * @return Array an Array containing the id and the name of the enumeration element.
	 */
	function mci_enum_get_array_by_id( $p_enum_id, $p_enum_type, $p_lang ) {
 		$t_result = array();
		$t_result['id'] = $p_enum_id;
		$t_result['name'] = mci_get_enum_element( $p_enum_type, $p_enum_id, $p_lang );
		return $t_result;
	}

	/**
	 * Get the enum id given the enum label.
	 *
	 * @param $p_enum_string   The enum string to search in.
	 * @param $p_label         The label to search for.
	 *
	 * @return The id corresponding to the given label, or 0 if not found.
	 */
	function mci_get_enum_value_from_label( $p_enum_string, $p_label ) {
		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );

		for ( $i = 0; $i < $enum_count; $i++ ) {
			$t_s = explode_enum_arr( $t_arr[$i] );
			if ( $t_s[1] == $p_label ) {
				return $t_s[0];
			}
		}

		return 0;
	}

	/**
	 * Get the enumeration id given an object ref.  The id is set based on the following algorithm:
	 * - id from objectref.
	 * - id corresponding to name in object ref.
	 * - default value for the specified enumeration, if exists in configuration.
	 * - first id, if object ref doesn't contain an id or a name.
	 *
	 * @param string     $p_enum         The name of the enumeration as in the Mantis configuration file
	 * @param ObjectRef  $p_object_ref   An associate array with "id" and "name" keys.
	 * @return enum id
	 */
	function mci_get_enum_id_from_objectref( $p_enum, $p_object_ref ) {
		if ( !is_null( $p_object_ref ) && isset( $p_object_ref['id'] ) && (int)$p_object_ref['id'] != 0 ) {
			$t_id =  (int)$p_object_ref['id'];
		} else {
			$t_enum = config_get( $p_enum . '_enum_string' );
			if ( !is_null( $p_object_ref ) && isset( $p_object_ref['name'] ) && !is_blank( $p_object_ref['name'] ) ) {
				$t_id = mci_get_enum_value_from_label( $t_enum, $p_object_ref['name'] );
				if ( $t_id == 0 ) {
					$t_id = config_get( 'mc_' . $p_enum . '_enum_default_when_not_found' );
				}
			} else {
				$t_default_id = config_get( 'default_bug_' . $p_enum, 0 );
				if ( $t_default_id == 0 ) {
					$t_array = mci_explode_to_objectref( $t_enum );
					$t_id = (int)$t_array[0]['id'];
				} else {
					$t_id = $t_default_id;
				}
			}
		}

		return $t_id;
	}
?>
