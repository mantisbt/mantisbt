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

# set up error_handler() as the new default error handling function
set_error_handler( 'mc_error_handler' );

/**
 * Webservice APIs
 *
 * @uses api_token_api.php
 */

require_api( 'api_token_api.php' );

/**
 * A factory class that can abstract away operations that can behave differently based
 * on the underlying soap implementation.
 *
 * TODO: Consider removing this class since it currently has one implementation which
 * targets the php soap extension.
 */
class SoapObjectsFactory {
	/**
	 * Generate a new Soap Fault
	 * @param string $p_fault_code   SOAP fault code.
	 * @param string $p_fault_string SOAP fault description.
	 * @return SoapFault
	 */
	static function newSoapFault( $p_fault_code, $p_fault_string ) {
		return new SoapFault( $p_fault_code, $p_fault_string );
	}

	/**
	 * Convert a soap object to an array
	 * @param stdClass|array $p_object Object.
	 * @return array
	 */
	static function unwrapObject( $p_object ) {
		if( is_object( $p_object ) ) {
			return get_object_vars( $p_object );
		}

		return $p_object;
	}

	/**
	 * Convert a timestamp to a soap DateTime variable
	 * @param integer $p_value Integer value to return as date time string.
	 * @return SoapVar
	 */
	static function newDateTimeVar( $p_value ) {
		$t_string_value = self::newDateTimeString( $p_value );

		return new SoapVar( $t_string_value, XSD_DATETIME, 'xsd:dateTime' );
	}

	/**
	 * Convert a timestamp to a DateTime string
	 * @param integer $p_timestamp Integer value to format as date time string.
	 * @return string
	 */
	static function newDateTimeString ( $p_timestamp ) {
		if( $p_timestamp == null || date_is_null( $p_timestamp ) ) {
			return null;
		}

		return date( 'c', (int)$p_timestamp );
	}

	/**
	 * Process Date Time string with strtotime
	 * @param string $p_string String value to process as a date time string.
	 * @return integer
	 */
	static function parseDateTimeString ( $p_string ) {
		return strtotime( $p_string );
	}

	/**
	 * Perform any necessary encoding on a binary string
	 * @param string $p_binary Binary string.
	 * @return string
	 */
	static function encodeBinary ( $p_binary ) {
		return $p_binary;
	}

	/**
	 * Checks if an object is a SoapFault
	 * @param mixed $p_maybe_fault Object to check whether a SOAP fault.
	 * @return boolean
	 */
	static function isSoapFault ( $p_maybe_fault ) {
		if( !is_object( $p_maybe_fault ) ) {
			return false;
		}

		return get_class( $p_maybe_fault ) == 'SoapFault';
	}
}

/**
 * Get the MantisConnect webservice version.
 * @return string
 */
function mc_version() {
	return MANTIS_VERSION;
}

/**
 * Attempts to login the user.
 * If logged in successfully, return user information.
 * If failed to login in, then throw a fault.
 * @param string $p_username Login username.
 * @param string $p_password Login password.
 * @return array Array of user data for the current API user
 */
function mc_login( $p_username, $p_password ) {
	$t_user_id = mci_check_login( $p_username, $p_password );
	if( $t_user_id === false ) {
		return mci_soap_fault_login_failed();
	}

	return mci_user_get( $p_username, $p_password, $t_user_id );
}

/**
 * Given an id, this method returns the user.
 * When calling this method make sure that the caller has the right to retrieve
 * information about the target user.
 * @param string  $p_username Login username.
 * @param string  $p_password Login password.
 * @param integer $p_user_id  A valid user identifier.
 * @return array array of user data for the supplied user id
 */
function mci_user_get( $p_username, $p_password, $p_user_id ) {
	$t_user_data = array();

	# if user doesn't exist, then mci_account_get_array_by_id() will throw.
	$t_user_data['account_data'] = mci_account_get_array_by_id( $p_user_id );
	$t_user_data['access_level'] = access_get_global_level( $p_user_id );
	$t_user_data['timezone'] = user_pref_get_pref( $p_user_id, 'timezone' );

	return $t_user_data;
}

/**
 * access_ if MantisBT installation is marked as offline by the administrator.
 * @return true: offline, false: online
 */
function mci_is_mantis_offline() {
	$t_offline_file = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'mantis_offline.php';
	return file_exists( $t_offline_file );
}

/**
 * handle a soap API login
 * @param string $p_username Login username.
 * @param string $p_password Login password.
 * @return integer|false return user_id if successful, otherwise false.
 */
function mci_check_login( $p_username, $p_password ) {
	if( mci_is_mantis_offline() ) {
		return false;
	}

	# Must not pass in null password, otherwise, authentication will be by-passed
	# by auth_attempt_script_login().
	$t_password = ( $p_password === null ) ? '' : $p_password;

	# Validate the token
	if( api_token_validate( $p_username, $t_password ) ) {
		# Token is valid, then login the user without worrying about a password.
		if( auth_attempt_script_login( $p_username, null ) === false ) {
			return false;
		}
	} else {
		# Not a valid token, validate as username + password.
		if( auth_attempt_script_login( $p_username, $t_password ) === false ) {
			return false;
		}
	}

	return auth_get_current_user_id();
}

/**
 * Check with a user has readonly access to the webservice for a given project
 * @param integer $p_user_id    A user identifier.
 * @param integer $p_project_id A project identifier ( Default All Projects ).
 * @return boolean indicating whether user has readonly access
 */
function mci_has_readonly_access( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_access_level = user_get_access_level( $p_user_id, $p_project_id );
	return( $t_access_level >= config_get( 'webservice_readonly_access_level_threshold' ) );
}

/**
 * Check with a user has readwrite access to the webservice for a given project
 * @param integer $p_user_id    User id.
 * @param integer $p_project_id Project Id ( Default All Projects ).
 * @return boolean indicating whether user has readwrite access
 */
function mci_has_readwrite_access( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_access_level = user_get_access_level( $p_user_id, $p_project_id );
	return( $t_access_level >= config_get( 'webservice_readwrite_access_level_threshold' ) );
}

/**
 * Check with a user has the required access level for a given project
 * @param integer $p_access_level Access level.
 * @param integer $p_user_id      User id.
 * @param integer $p_project_id   Project Id ( Default All Projects ).
 * @return boolean indicating whether user has the required access
 */
function mci_has_access( $p_access_level, $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_access_level = user_get_access_level( $p_user_id, $p_project_id );
	return( $t_access_level >= (int)$p_access_level );
}

/**
 * Check with a user has administrative access to the webservice
 * @param integer $p_user_id    User id.
 * @param integer $p_project_id Project Id ( Default All Projects ).
 * @return boolean indicating whether user has the required access
 */
function mci_has_administrator_access( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_access_level = user_get_access_level( $p_user_id, $p_project_id );
	return( $t_access_level >= config_get( 'webservice_admin_access_level_threshold' ) );
}

/**
 * Given an object, return the project id
 * @param object $p_project Project Object.
 * @return integer project id
 */
function mci_get_project_id( $p_project ) {
	if( is_object( $p_project ) ) {
		$p_project = get_object_vars( $p_project );
	}

	if( isset( $p_project['id'] ) && (int)$p_project['id'] != 0 ) {
		$t_project_id = (int)$p_project['id'];
	} else if( isset( $p_project['name'] ) && !is_blank( $p_project['name'] ) ) {
		$t_project_id = project_get_id_by_name( $p_project['name'] );
	} else {
		$t_project_id = ALL_PROJECTS;
	}

	return $t_project_id;
}

/**
 * Return project Status
 * @param object $p_status Status.
 * @return integer Status
 */
function mci_get_project_status_id( $p_status ) {
	return mci_get_enum_id_from_objectref( 'project_status', $p_status );
}

/**
 * Return project view state
 * @param object $p_view_state View state.
 * @return integer View state
 */
function mci_get_project_view_state_id( $p_view_state ) {
	return mci_get_enum_id_from_objectref( 'project_view_state', $p_view_state );
}

/**
 * Return user id
 * @param stdClass $p_user User.
 * @return integer user id
 */
function mci_get_user_id( stdClass $p_user ) {
	$p_user = SoapObjectsFactory::unwrapObject( $p_user );

	$t_user_id = 0;

	if( isset( $p_user['id'] ) && (int)$p_user['id'] != 0 ) {
		$t_user_id = (int)$p_user['id'];
	} elseif( isset( $p_user['name'] ) ) {
		$t_user_id = user_get_id_by_name( $p_user['name'] );
	} elseif( isset( $p_user['email'] ) ) {
		$t_user_id = user_get_id_by_email( $p_user['email'] );
	}

	return $t_user_id;
}

/**
 * Return user's default language given a user id
 * @param integer $p_user_id User id.
 * @return string language string
 */
function mci_get_user_lang( $p_user_id ) {
	$t_lang = user_pref_get_pref( $p_user_id, 'language' );
	if( $t_lang == 'auto' ) {
		$t_lang = config_get( 'fallback_language' );
	}
	return $t_lang;
}

/**
 * Return Status
 * @param object $p_status Status.
 * @return integer status id
 */
function mci_get_status_id( $p_status ) {
	return mci_get_enum_id_from_objectref( 'status', $p_status );
}

/**
 * Return Severity
 * @param object $p_severity Severity.
 * @return integer severity id
 */
function mci_get_severity_id( $p_severity ) {
	return mci_get_enum_id_from_objectref( 'severity', $p_severity );
}

/**
 * Return Priority
 * @param object $p_priority Priority.
 * @return integer priority id
 */
function mci_get_priority_id( $p_priority ) {
	return mci_get_enum_id_from_objectref( 'priority', $p_priority );
}

/**
 * Return Reproducibility
 * @param object $p_reproducibility Reproducibility.
 * @return integer reproducibility id
 */
function mci_get_reproducibility_id( $p_reproducibility ) {
	return mci_get_enum_id_from_objectref( 'reproducibility', $p_reproducibility );
}

/**
 * Return Resolution
 * @param object $p_resolution Resolution object.
 * @return integer Resolution id
 */
function mci_get_resolution_id( $p_resolution ) {
	return mci_get_enum_id_from_objectref( 'resolution', $p_resolution );
}

/**
 * Return projection
 * @param object $p_projection Projection object.
 * @return integer projection id
 */
function mci_get_projection_id( $p_projection ) {
	return mci_get_enum_id_from_objectref( 'projection', $p_projection );
}

/**
 * Return ETA id
 * @param object $p_eta ETA object.
 * @return integer eta id
 */
function mci_get_eta_id( $p_eta ) {
	return mci_get_enum_id_from_objectref( 'eta', $p_eta );
}

/**
 * Return view state id
 * @param object $p_view_state View state object.
 * @return integer view state
 */
function mci_get_view_state_id( $p_view_state ) {
	return mci_get_enum_id_from_objectref( 'view_state', $p_view_state );
}

/**
 * Get null on empty value.
 *
 * @param string $p_value The value.
 * @return string|null The value if not empty; null otherwise.
 */
function mci_null_if_empty( $p_value ) {
	if( !is_blank( $p_value ) ) {
		return $p_value;
	}

	return null;
}

/**
 * Removes any invalid character from the string per XML 1.0 specification
 *
 * @param string $p_input XML string.
 * @return string the sanitized XML
 */
function mci_sanitize_xml_string ( $p_input ) {
	return preg_replace( '/[^\x9\xA\xD\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $p_input );
}

/**
 * Gets the url for MantisBT.
 *
 * @return string MantisBT URL terminated by a /.
 */
function mci_get_mantis_path() {
	return config_get( 'path' );
}

/**
 * Given a enum string and num, return the appropriate localized string
 * @param string $p_enum_name Enumeration name.
 * @param string $p_val       Enumeration value.
 * @param string $p_lang      Language string.
 * @return string
 */
function mci_get_enum_element( $p_enum_name, $p_val, $p_lang ) {
	$t_enum_string = config_get( $p_enum_name . '_enum_string' );
	$t_localized_enum_string = lang_get( $p_enum_name . '_enum_string', $p_lang );

	return MantisEnum::getLocalizedLabel( $t_enum_string, $t_localized_enum_string, $p_val );
}

/**
 * Gets the sub-projects that are accessible to the specified user / project.
 * @param integer $p_user_id           User id.
 * @param integer $p_parent_project_id Parent Project id.
 * @param string  $p_lang              Language string.
 * @return array
 */
function mci_user_get_accessible_subprojects( $p_user_id, $p_parent_project_id, $p_lang = null ) {
	if( $p_lang === null ) {
		$t_lang = mci_get_user_lang( $p_user_id );
	} else {
		$t_lang = $p_lang;
	}

	$t_result = array();
	foreach( user_get_accessible_subprojects( $p_user_id, $p_parent_project_id ) as $t_subproject_id ) {
		$t_subproject_row = project_cache_row( $t_subproject_id );
		$t_subproject = array();
		$t_subproject['id'] = $t_subproject_id;
		$t_subproject['name'] = $t_subproject_row['name'];
		$t_subproject['status'] = mci_enum_get_array_by_id( $t_subproject_row['status'], 'project_status', $t_lang );
		$t_subproject['enabled'] = $t_subproject_row['enabled'];
		$t_subproject['view_state'] = mci_enum_get_array_by_id( $t_subproject_row['view_state'], 'project_view_state', $t_lang );
		$t_subproject['access_min'] = mci_enum_get_array_by_id( $t_subproject_row['access_min'], 'access_levels', $t_lang );
		$t_subproject['file_path'] = array_key_exists( 'file_path', $t_subproject_row ) ? $t_subproject_row['file_path'] : '';
		$t_subproject['description'] = array_key_exists( 'description', $t_subproject_row ) ? $t_subproject_row['description'] : '';
		$t_subproject['subprojects'] = mci_user_get_accessible_subprojects( $p_user_id, $t_subproject_id, $t_lang );
		$t_result[] = $t_subproject;
	}

	return $t_result;
}

/**
 * Convert a category name to a category id for a given project
 * @param string  $p_category_name Category name.
 * @param integer $p_project_id    Project id.
 * @return integer category id or 0 if not found
 */
function translate_category_name_to_id( $p_category_name, $p_project_id ) {
	if( !isset( $p_category_name ) ) {
		return 0;
	}

	$t_cat_array = category_get_all_rows( $p_project_id );
	foreach( $t_cat_array as $t_category_row ) {
		if( $t_category_row['name'] == $p_category_name ) {
			return $t_category_row['id'];
		}
	}
	return 0;
}

/**
 * Basically this is a copy of core/filter_api.php#filter_db_get_available_queries().
 * The only difference is that the result of this function is not an array of filter
 * names but an array of filter structures.
 * @param integer $p_project_id Project id.
 * @param integer $p_user_id    User id.
 * @return array
 */
function mci_filter_db_get_available_queries( $p_project_id = null, $p_user_id = null ) {
	$t_overall_query_arr = array();

	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = (int)$p_project_id;
	}

	if( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = (int)$p_user_id;
	}

	# If the user doesn't have access rights to stored queries, just return
	if( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
		return $t_overall_query_arr;
	}

	# Get the list of available queries. By sorting such that public queries are
	# first, we can override any query that has the same name as a private query
	# with that private one
	$t_query = 'SELECT * FROM {filters}
					WHERE (project_id=' . db_param() . '
						OR project_id=0)
					AND name!=\'\'
					AND (is_public = ' . db_param() . '
						OR user_id = ' . db_param() . ')
					ORDER BY is_public DESC, name ASC';
	$t_result = db_query( $t_query, array( $t_project_id, true, $t_user_id ) );
	$t_query_count = db_num_rows( $t_result );

	for( $i = 0;$i < $t_query_count;$i++ ) {
		$t_row = db_fetch_array( $t_result );

		$t_filter_detail = explode( '#', $t_row['filter_string'], 2 );
		if( !isset($t_filter_detail[1]) ) {
			continue;
		}
		$t_filter = json_decode( $t_filter_detail[1], true );
		$t_filter = filter_ensure_valid_filter( $t_filter );
		$t_row['url'] = filter_get_url( $t_filter );
		$t_overall_query_arr[$t_row['name']] = $t_row;
	}

	return array_values( $t_overall_query_arr );
}

/**
 * Get a category definition.
 *
 * @param integer $p_category_id The id of the category to retrieve.
 * @return array an array containing the id and the name of the category.
 */
function mci_category_as_array_by_id( $p_category_id ) {
	$t_result = array();
	$t_result['id'] = $p_category_id;
	$t_result['name'] = category_get_name( $p_category_id );
	return $t_result;
}

/**
 * Transforms a version array into an array suitable for marshalling into ProjectVersionData
 *
 * @param array $p_version Version array.
 * @return array
 */
function mci_project_version_as_array( array $p_version ) {
	return array(
			'id' => $p_version['id'],
			'name' => $p_version['version'],
			'project_id' => $p_version['project_id'],
			'date_order' => SoapObjectsFactory::newDateTimeVar( $p_version['date_order'] ),
			'description' => mci_null_if_empty( $p_version['description'] ),
			'released' => $p_version['released'],
			'obsolete' => $p_version['obsolete']
		);
}

/**
 * Returns time tracking information from a bug note.
 *
 * @param integer $p_issue_id The id of the issue.
 * @param array   $p_note     A note as passed to the soap api methods.
 *
 * @return String the string time entry to be added to the bugnote, in 'HH:mm' format
 */
function mci_get_time_tracking_from_note( $p_issue_id, array $p_note ) {
	if( !access_has_bug_level( config_get( 'time_tracking_view_threshold' ), $p_issue_id ) ) {
		return '00:00';
	}

	if( !isset( $p_note['time_tracking'] ) ) {
		return '00:00';
	}

	return db_minutes_to_hhmm( $p_note['time_tracking'] );
}

/**
 * Default error handler
 *
 * This handler will not receive E_ERROR, E_PARSE, E_CORE_*, or E_COMPILE_* errors.
 *
 * E_USER_* are triggered by us and will contain an error constant in $p_error
 * The others, being system errors, will come with a string in $p_error
 * @param integer $p_type    Contains the level of the error raised, as an integer.
 * @param string  $p_error   Contains the error message, as a string.
 * @param string  $p_file    Contains the filename that the error was raised in, as a string.
 * @param integer $p_line    Contains the line number the error was raised at, as an integer.
 * @param array   $p_context To the active symbol table at the point the error occurred (optional).
 * @return void
 */
function mc_error_handler( $p_type, $p_error, $p_file, $p_line, array $p_context ) {
	# check if errors were disabled with @ somewhere in this call chain
	# also suppress php 5 strict warnings
	if( 0 == error_reporting() || 2048 == $p_type ) {
		return;
	}

	# flush any language overrides to return to user's natural default
	if( function_exists( 'db_is_connected' ) ) {
		if( db_is_connected() ) {
			lang_push( lang_get_default() );
		}
	}

	# build an appropriate error string
	switch( $p_type ) {
		case E_WARNING:
			$t_error_type = 'SYSTEM WARNING';
			$t_error_description = $p_error;
			break;
		case E_NOTICE:
			$t_error_type = 'SYSTEM NOTICE';
			$t_error_description = $p_error;
			break;
		case E_USER_ERROR:
			$t_error_type = 'APPLICATION ERROR #' . $p_error;
			$t_error_description = error_string( $p_error );
			break;
		case E_USER_WARNING:
			$t_error_type = 'APPLICATION WARNING #' . $p_error;
			$t_error_description = error_string( $p_error );
			break;
		case E_USER_NOTICE:
			# used for debugging
			$t_error_type = 'DEBUG';
			$t_error_description = $p_error;
			break;
		default:
			#shouldn't happen, just display the error just in case
			$t_error_type = '';
			$t_error_description = $p_error;
	}

	$t_error_stack = error_get_stack_trace();

	error_log( '[mantisconnect.php] Error Type: ' . $t_error_type . ',' . "\n" . 'Error Description: ' . $t_error_description . "\n" . 'Stack Trace:' . "\n" . $t_error_stack );

	throw new SoapFault( 'Server', 'Error Type: ' . $t_error_type . ',' . "\n" . 'Error Description: ' . $t_error_description );
}

/**
 * Get a stack trace from either PHP or xdebug if present
 * @return string
 */
function error_get_stack_trace() {
	$t_trace = '';

	if( extension_loaded( 'xdebug' ) ) {

		#check for xdebug presence
		$t_stack = xdebug_get_function_stack();

		# reverse the array in a separate line of code so the
		#  array_reverse() call doesn't appear in the stack
		$t_stack = array_reverse( $t_stack );
		array_shift( $t_stack );

		#remove the call to this function from the stack trace
		foreach( $t_stack as $t_frame ) {
			$t_trace .= ( isset( $t_frame['file'] ) ? basename( $t_frame['file'] ) : 'UnknownFile' ) . ' L' . ( isset( $t_frame['line'] ) ? $t_frame['line'] : '?' ) . ' ' . ( isset( $t_frame['function'] ) ? $t_frame['function'] : 'UnknownFunction' );

			$t_args = array();
			if( isset( $t_frame['params'] ) && ( count( $t_frame['params'] ) > 0 ) ) {
				$t_trace .= ' Params: ';
				foreach( $t_frame['params'] as $t_value ) {
					$t_args[] = error_build_parameter_string( $t_value );
				}

				$t_trace .= '(' . implode( $t_args, ', ' ) . ')';
			} else {
				$t_trace .= '()';
			}

			$t_trace .= "\n";
		}
	} else {
		$t_stack = debug_backtrace();

		array_shift( $t_stack ); #remove the call to this function from the stack trace
		array_shift( $t_stack ); #remove the call to the error handler from the stack trace

		foreach( $t_stack as $t_frame ) {
			$t_trace .= ( isset( $t_frame['file'] ) ? basename( $t_frame['file'] ) : 'UnknownFile' ) . ' L' . ( isset( $t_frame['line'] ) ? $t_frame['line'] : '?' ) . ' ' . ( isset( $t_frame['function'] ) ? $t_frame['function'] : 'UnknownFunction' );

			$t_args = array();
			if( isset( $t_frame['args'] ) ) {
				foreach( $t_frame['args'] as $t_value ) {
					$t_args[] = error_build_parameter_string( $t_value );
				}

				$t_trace .= '(' . implode( $t_args, ', ' ) . ')';
			} else {
				$t_trace .= '()';
			}

			$t_trace .= "\n";
		}
	}

	return $t_trace;
}

/**
 * Returns a soap_fault signalling corresponding to a failed login
 * situation
 *
 * @return soap_fault
 */
function mci_soap_fault_login_failed() {
	return SoapObjectsFactory::newSoapFault( 'Client', 'Access denied' );
}

/**
 * Returns a soap_fault signalling that the user does not have
 * access rights for the specific action.
 *
 * @param integer $p_user_id A user id, optional.
 * @param string  $p_detail  The optional details to append to the error message.
 * @return soap_fault
 */
function mci_soap_fault_access_denied( $p_user_id = 0, $p_detail = '' ) {
	if( $p_user_id ) {
		$t_user_name = user_get_name( $p_user_id );
		$t_reason = 'Access denied for user '. $t_user_name . '.';
	} else {
		$t_reason = 'Access denied';
	}

	if( !is_blank( $p_detail ) ) {
		$t_reason .= ' Reason: ' . $p_detail . '.';
	}

	return SoapObjectsFactory::newSoapFault( 'Client', $t_reason );
}
