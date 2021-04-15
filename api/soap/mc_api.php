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
 * Webservice APIs
 *
 * @uses api_token_api.php
 */

require_api( 'api_token_api.php' );

use Mantis\Exceptions\ClientException;
use Mantis\Exceptions\LegacyApiFaultException;

/**
 * A class to capture a RestFault
 */
class RestFault {
	/**
	 * @var integer The http status code
	 */
	public $status_code;

	/**
	 * @var string The http status string
	 */
	public $fault_string;

	/**
	 * RestFault constructor.
	 *
	 * @param integer $p_status_code The http status code
	 * @param string $p_fault_string The error description
	 */
	function __construct( $p_status_code, $p_fault_string = '' ) {
		$this->status_code = $p_status_code;
		$this->fault_string = $p_fault_string === null ? '' : $p_fault_string;
	}
}

/**
 * A factory class that can abstract away operations that can behave differently based
 * on the API being accessed (SOAP vs. REST).
 */
class ApiObjectFactory {
	/**
	 * @var bool true: SOAP API, false: REST API
	 */
	static public $soap = true;

	/**
	 * Generate a new fault - this method should only be called from within this factory class.  Use methods for
	 * specific error cases.
	 *
	 * @param string $p_fault_code   SOAP fault code (Server or Client).
	 * @param string $p_fault_string Fault description.
	 * @param integer $p_status_code The http status code.
	 * @return RestFault|SoapFault The fault object.
	 * @access private
	 */
	static function fault( $p_fault_code, $p_fault_string, $p_status_code = null ) {
		# Default status code based on fault code, if not specified.
		if( $p_status_code === null ) {
			$p_status_code = ( $p_fault_code == 'Server' ) ? 500 : 400;
		}

		if( ApiObjectFactory::$soap ) {
			return new SoapFault( $p_fault_code, $p_fault_string );
		}

		return new RestFault( $p_status_code, $p_fault_string );
	}

	/**
	 * Fault generated when a resource doesn't exist.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultNotFound( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_NOT_FOUND );
	}

	/**
	 * Fault generated when an operation is not allowed.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultForbidden( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_FORBIDDEN );
	}

	/**
	 * Fault generated when a request is invalid.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultBadRequest( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_BAD_REQUEST );
	}

	/**
	 * Fault generated when a client hits rate limits.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultTooManyRequests( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_TOO_MANY_REQUESTS );
	}

	/**
	 * Fault generated when the request is failed due to conflict with current state of the data.
	 * This can happen either due to a race condition or lack of checking on client side before
	 * issuing the request.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultConflict( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Client', $p_fault_string, HTTP_STATUS_CONFLICT );
	}

	/**
	 * Fault generated when a request fails due to server error.
	 *
	 * @param string $p_fault_string The fault details.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultServerError( $p_fault_string ) {
		return ApiObjectFactory::fault( 'Server', $p_fault_string, HTTP_STATUS_INTERNAL_SERVER_ERROR );
	}

	/**
	 * Generate fault based on provided exception.
	 *
	 * @param Exception $p_exception The exception to process.
	 * @return RestFault|SoapFault The fault object.
	 */
	static function faultFromException( Exception $p_exception ) {
		$t_code = $p_exception->getCode();

		switch( $t_code ) {
			case ERROR_NO_FILE_SPECIFIED:
			case ERROR_FILE_DISALLOWED:
			case ERROR_DUPLICATE_PROJECT:
			case ERROR_EMPTY_FIELD:
			case ERROR_INVALID_REQUEST_METHOD:
			case ERROR_INVALID_SORT_FIELD:
			case ERROR_INVALID_DATE_FORMAT:
			case ERROR_INVALID_RESOLUTION:
			case ERROR_FIELD_TOO_LONG:
			case ERROR_CONFIG_OPT_NOT_FOUND:
			case ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB:
			case ERROR_CONFIG_OPT_BAD_SYNTAX:
			case ERROR_GPC_VAR_NOT_FOUND:
			case ERROR_GPC_ARRAY_EXPECTED:
			case ERROR_GPC_ARRAY_UNEXPECTED:
			case ERROR_GPC_NOT_NUMBER:
			case ERROR_FILE_TOO_BIG:
			case ERROR_FILE_NOT_ALLOWED:
			case ERROR_FILE_DUPLICATE:
			case ERROR_FILE_NO_UPLOAD_FAILURE:
			case ERROR_PROJECT_NAME_NOT_UNIQUE:
			case ERROR_PROJECT_NAME_INVALID:
			case ERROR_PROJECT_RECURSIVE_HIERARCHY:
			case ERROR_USER_NAME_NOT_UNIQUE:
			case ERROR_USER_CREATE_PASSWORD_MISMATCH:
			case ERROR_USER_NAME_INVALID:
			case ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS:
			case ERROR_USER_CHANGE_LAST_ADMIN:
			case ERROR_USER_REAL_NAME_INVALID:
			case ERROR_USER_EMAIL_NOT_UNIQUE:
			case ERROR_BUG_DUPLICATE_SELF:
			case ERROR_BUG_RESOLVE_DEPENDANTS_BLOCKING:
			case ERROR_BUG_CONFLICTING_EDIT:
			case ERROR_EMAIL_INVALID:
			case ERROR_EMAIL_DISPOSABLE:
			case ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE:
			case ERROR_CUSTOM_FIELD_IN_USE:
			case ERROR_CUSTOM_FIELD_INVALID_VALUE:
			case ERROR_CUSTOM_FIELD_INVALID_DEFINITION:
			case ERROR_CUSTOM_FIELD_NOT_LINKED_TO_PROJECT:
			case ERROR_CUSTOM_FIELD_INVALID_PROPERTY:
			case ERROR_CATEGORY_DUPLICATE:
			case ERROR_CATEGORY_NO_ACTION:
			case ERROR_CATEGORY_NOT_FOUND_FOR_PROJECT:
			case ERROR_VERSION_DUPLICATE:
			case ERROR_SPONSORSHIP_NOT_ENABLED:
			case ERROR_SPONSORSHIP_AMOUNT_TOO_LOW:
			case ERROR_SPONSORSHIP_SPONSOR_NO_EMAIL:
			case ERROR_RELATIONSHIP_ALREADY_EXISTS:
			case ERROR_RELATIONSHIP_SAME_BUG:
			case ERROR_LOST_PASSWORD_CONFIRM_HASH_INVALID:
			case ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED:
			case ERROR_LOST_PASSWORD_NOT_MATCHING_DATA:
			case ERROR_SIGNUP_NOT_MATCHING_CAPTCHA:
			case ERROR_TAG_DUPLICATE:
			case ERROR_TAG_NAME_INVALID:
			case ERROR_TAG_NOT_ATTACHED:
			case ERROR_TAG_ALREADY_ATTACHED:
			case ERROR_COLUMNS_DUPLICATE:
			case ERROR_COLUMNS_INVALID:
			case ERROR_API_TOKEN_NAME_NOT_UNIQUE:
			case ERROR_INVALID_FIELD_VALUE:
			case ERROR_PROJECT_SUBPROJECT_DUPLICATE:
			case ERROR_PROJECT_SUBPROJECT_NOT_FOUND:
				return ApiObjectFactory::faultBadRequest( $p_exception->getMessage() );

			case ERROR_BUG_NOT_FOUND:
			case ERROR_FILE_NOT_FOUND:
			case ERROR_BUGNOTE_NOT_FOUND:
			case ERROR_PROJECT_NOT_FOUND:
			case ERROR_USER_PREFS_NOT_FOUND:
			case ERROR_USER_PROFILE_NOT_FOUND:
			case ERROR_USER_BY_NAME_NOT_FOUND:
			case ERROR_USER_BY_ID_NOT_FOUND:
			case ERROR_USER_BY_EMAIL_NOT_FOUND:
			case ERROR_USER_BY_REALNAME_NOT_FOUND:
			case ERROR_NEWS_NOT_FOUND:
			case ERROR_BUG_REVISION_NOT_FOUND:
			case ERROR_CUSTOM_FIELD_NOT_FOUND:
			case ERROR_CATEGORY_NOT_FOUND:
			case ERROR_VERSION_NOT_FOUND:
			case ERROR_SPONSORSHIP_NOT_FOUND:
			case ERROR_RELATIONSHIP_NOT_FOUND:
			case ERROR_FILTER_NOT_FOUND:
			case ERROR_TAG_NOT_FOUND:
			case ERROR_TOKEN_NOT_FOUND:
				return ApiObjectFactory::faultNotFound( $p_exception->getMessage() );
				
			case ERROR_ACCESS_DENIED:
			case ERROR_PROTECTED_ACCOUNT:
			case ERROR_HANDLER_ACCESS_TOO_LOW:
			case ERROR_USER_CURRENT_PASSWORD_MISMATCH:
			case ERROR_AUTH_INVALID_COOKIE:
			case ERROR_BUG_READ_ONLY_ACTION_DENIED:
			case ERROR_LDAP_AUTH_FAILED:
			case ERROR_LDAP_USER_NOT_FOUND:
			case ERROR_CATEGORY_CANNOT_DELETE_DEFAULT:
			case ERROR_CATEGORY_CANNOT_DELETE_HAS_ISSUES:
			case ERROR_SPONSORSHIP_HANDLER_ACCESS_LEVEL_TOO_LOW:
			case ERROR_SPONSORSHIP_ASSIGNER_ACCESS_LEVEL_TOO_LOW:
			case ERROR_RELATIONSHIP_ACCESS_LEVEL_TO_DEST_BUG_TOO_LOW:
			case ERROR_LOST_PASSWORD_NOT_ENABLED:
			case ERROR_LOST_PASSWORD_MAX_IN_PROGRESS_ATTEMPTS_REACHED:
			case ERROR_FORM_TOKEN_INVALID:
				return ApiObjectFactory::faultForbidden( $p_exception->getMessage() );

			case ERROR_SPAM_SUSPECTED:
				return ApiObjectFactory::faultTooManyRequests( $p_exception->getMessage() );

			case ERROR_CONFIG_OPT_INVALID:
			case ERROR_FILE_INVALID_UPLOAD_PATH:
				# TODO: These are configuration or db state errors.
				return ApiObjectFactory::faultServerError( $p_exception->getMessage() );

			default:
				return ApiObjectFactory::faultServerError( $p_exception->getMessage() );
		}
	}

	/**
	 * Convert a soap object to an array
	 * @param stdClass|array $p_object Object.
	 * @param boolean $p_recursive
	 * @return array
	 */
	static function objectToArray( $p_object, $p_recursive = false ) {
		$t_object = is_object( $p_object ) ? get_object_vars( $p_object ) : $p_object;
		if( $p_recursive && is_array( $t_object ) ) {
			foreach( $t_object as $t_key => $t_value ) {
				if( is_object( $t_object[$t_key] ) || is_array( $t_object[$t_key] ) ) {
					$t_object[$t_key] = ApiObjectFactory::objectToArray( $t_object[$t_key], $p_recursive );
				}
			}
		}

		return $t_object;
	}

	/**
	 * Convert a timestamp to a soap DateTime variable
	 * @param integer $p_value Integer value to return as date time string.
	 * @return datetime in expected API format.
	 */
	static function datetime($p_value ) {
		$t_string_value = self::datetimeString( $p_value );

		if( ApiObjectFactory::$soap ) {
			return new SoapVar($t_string_value, XSD_DATETIME, 'xsd:dateTime');
		}

		return $t_string_value;
	}

	/**
	 * Convert a timestamp to a DateTime string
	 * @param integer $p_timestamp Integer value to format as date time string.
	 * @return string for provided timestamp
	 */
	static function datetimeString($p_timestamp ) {
		if( $p_timestamp == null || date_is_null( $p_timestamp ) ) {
			return null;
		}

		return date( 'c', (int)$p_timestamp );
	}

	/**
	 * Checks if an object is a SoapFault
	 * @param mixed $p_maybe_fault Object to check whether it is a SOAP/REST fault.
	 * @return boolean
	 */
	static function isFault( $p_maybe_fault ) {
		if( !is_object( $p_maybe_fault ) ) {
			return false;
		}

		if( ApiObjectFactory::$soap && get_class( $p_maybe_fault ) == 'SoapFault') {
			return true;
		}

		if( !ApiObjectFactory::$soap && get_class( $p_maybe_fault ) == 'RestFault') {
			return true;
		}

		return false;
	}

	/**
	 * Throw if the provided parameter is a SoapFault or RestFault/
	 *
	 * @param mixed $p_maybe_fault Object to check whether it is a SOAP/REST fault.
	 * @return void
	 * @throws LegacyApiFaultException
	 */
	static function throwIfFault( $p_maybe_fault ) {
		if( ApiObjectFactory::isFault( $p_maybe_fault ) ) {
			throw new LegacyApiFaultException( $p_maybe_fault->getMessage(), $p_maybe_fault->getCode() );
		}
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
		return mci_fault_login_failed();
	}

	return mci_user_get( $t_user_id );
}

/**
 * Given an id, this method returns the user.
 * When calling this method make sure that the caller has the right to retrieve
 * information about the target user.
 * @param integer $p_user_id  A valid user identifier.
 * @return array array of user data for the supplied user id
 */
function mci_user_get( $p_user_id ) {
	$t_user_data = array();

	# if user doesn't exist, then mci_account_get_array_by_id() will throw.
	if( ApiObjectFactory::$soap ) {
		$t_user_data['account_data'] = mci_account_get_array_by_id( $p_user_id );
		$t_user_data['access_level'] = access_get_global_level( $p_user_id );
		$t_user_data['timezone'] = user_pref_get_pref( $p_user_id, 'timezone' );
	} else {
		$t_account_data = mci_account_get_array_by_id( $p_user_id );
		foreach( $t_account_data as $t_key => $t_value ) {
			$t_user_data[$t_key] = $t_value;
		}

		$t_user_data['language'] = mci_get_user_lang( $p_user_id );
		$t_user_data['timezone'] = user_pref_get_pref( $p_user_id, 'timezone' );

		$t_access_level = access_get_global_level( $p_user_id );
		$t_user_data['access_level'] = mci_enum_get_array_by_id(
			$t_access_level, 'access_levels', $t_user_data['language'] );

		$t_project_ids = user_get_accessible_projects( $p_user_id, /* disabled */ false );
		$t_projects = array();
		foreach( $t_project_ids as $t_project_id ) {
			$t_projects[] = mci_project_get( $t_project_id, $t_user_data['language'], /* detail */ false );
		}

		$t_user_data['projects'] = $t_projects;
	}

	return $t_user_data;
}

/**
 * Get project info for the specified id.
 *
 * @param int $p_project_id The project id to get info for.
 * @param string $p_lang The user's language.
 * @param bool @p_detail Include all project details vs. just reference info.
 * @return array project info.
 */
function mci_project_get( $p_project_id, $p_lang, $p_detail ) {
	$t_row = project_get_row( $p_project_id );

	$t_user_id = auth_get_current_user_id();
	$t_user_access_level = access_get_project_level( $p_project_id, $t_user_id );

	# Get project info that makes sense to publish via API.  For example, skip file_path.
	$t_project = array(
		'id' => $p_project_id,
		'name' => $t_row['name'],
	);

	if( $p_detail ) {
		$t_project['status'] = mci_enum_get_array_by_id( (int)$t_row['status'], 'project_status', $p_lang );
		$t_project['description'] = $t_row['description'];
		$t_project['enabled'] = (int)$t_row['enabled'] != 0;
		$t_project['view_state'] = mci_enum_get_array_by_id( (int)$t_row['view_state'], 'view_state', $p_lang );

		# access_min field is not used
		# $t_project['access_min'] = mci_enum_get_array_by_id( (int)$t_row['access_min'], 'access_levels', $p_lang );

		$t_project['access_level'] = mci_enum_get_array_by_id( $t_user_access_level, 'access_levels', $p_lang );
		$t_project['custom_fields'] = mci_project_get_custom_fields( $p_project_id );
		$t_project['versions'] = mci_project_versions( $p_project_id );
		$t_project['categories'] = mci_project_categories( $p_project_id );
	}

	return $t_project;
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
	static $s_already_called = false;

	if( $s_already_called === true ) {
		return auth_get_current_user_id();
	}

	$s_already_called = true;

	if( mci_is_mantis_offline() ) {
		return false;
	}

	# Must not pass in null password, otherwise, authentication will be by-passed
	# by auth_attempt_script_login().
	$t_password = ( $p_password === null ) ? '' : $p_password;

	if( api_token_validate( $p_username, $t_password ) ) {
		# Token is valid, then login the user without worrying about a password.
		if( auth_attempt_script_login( $p_username, null ) === false ) {
			return false;
		}
	} else {
		# User cookie
		$t_user_id = auth_user_id_from_cookie( $p_password );
		if( $t_user_id !== false ) {
			# Cookie is valid
			if( auth_attempt_script_login( $p_username, null ) === false ) {
				return false;
			}
		} else {
			# Use regular passwords
			if( auth_attempt_script_login( $p_username, $t_password ) === false ) {
				return false;
			}
		}
	}

	# Set language to user's language
	lang_push( lang_get_default() );

	return auth_get_current_user_id();
}

/**
 * Check with a user has readonly access to the webservice for a given project
 * @param integer|null $p_user_id A user id or null for logged in user.
 * @param integer $p_project_id A project identifier ( Default All Projects ).
 * @return boolean indicating whether user has readonly access
 */
function mci_has_readonly_access( $p_user_id = null, $p_project_id = ALL_PROJECTS ) {
	$t_user_id = is_null( $p_user_id ) ? auth_get_current_user_id() : $p_user_id;
	$t_access_level = user_get_access_level( $t_user_id, $p_project_id );
	return( $t_access_level >= config_get( 'webservice_readonly_access_level_threshold' ) );
}

/**
 * Check with a user has readwrite access to the webservice for a given project
 * @param integer|null $p_user_id User id or null for logged in user.
 * @param integer $p_project_id Project Id ( Default All Projects ).
 * @return boolean indicating whether user has readwrite access
 */
function mci_has_readwrite_access( $p_user_id = null, $p_project_id = ALL_PROJECTS ) {
	$t_user_id = is_null( $p_user_id ) ? auth_get_current_user_id() : $p_user_id;
	$t_access_level = user_get_access_level( $t_user_id, $p_project_id );
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
 * @param integer|boolean $p_default The default value or false if the default should not be applied.
 * @return null|integer project id
 */
function mci_get_project_id( $p_project, $p_default = ALL_PROJECTS ) {
	if( is_object( $p_project ) ) {
		$p_project = get_object_vars( $p_project );
	}

	if( isset( $p_project['id'] ) && (int)$p_project['id'] != 0 ) {
		$t_project_id = (int)$p_project['id'];
	} else if( isset( $p_project['name'] ) && !is_blank( $p_project['name'] ) ) {
		$t_project_id = project_get_id_by_name( $p_project['name'], $p_default );
	} else if( $p_default === false ) {
		$t_project_id = null;
	} else {
		$t_project_id = $p_default;
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
 * @param stdClass|array $p_user User.
 * @return integer user id or 0 if not found.
 */
function mci_get_user_id( $p_user ) {
	if( is_object( $p_user ) ) {
		$p_user = ApiObjectFactory::objectToArray( $p_user );
	}

	try {
		return user_get_id_by_user_info( $p_user );
	} catch( Exception $e ) {
		return 0;
	}
}

/**
 * Given a profile id, return its information as an array or null
 * if profile id is 0 or not found.
 *
 * @param integer $p_profile_id The profile id, can be 0.
 * @return array|null The profile or null if not found.
 */
function mci_profile_as_array_by_id( $p_profile_id ) {
	$t_profile_id = (int)$p_profile_id;
	if( $t_profile_id == 0 ) {
		return null;
	}

	try {
		$t_profile = profile_get_row( $t_profile_id );
	} catch (ClientException $e) {
		return null;
	}

	return array(
		'id' => $t_profile_id,
		'user' => mci_account_get_array_by_id( $t_profile['user_id'] ),
		'platform' => $t_profile['platform'],
		'os' => $t_profile['os'],
		'os_build' => $t_profile['os_build'],
		'description' => $t_profile['description']
	);
}

/**
 * Get basic issue info for related issues.
 *
 * @param integer $p_issue_id The issue id.
 * @return array|null The issue id or null if not found.
 */
function mci_related_issue_as_array_by_id( $p_issue_id ) {
	$t_issue_id = (int)$p_issue_id;

	if( !bug_exists( $t_issue_id ) ) {
		return null;
	}

	$t_user_id = auth_get_current_user_id();
	$t_lang = mci_get_user_lang( $t_user_id );

	$t_bug = bug_get( $t_issue_id );

	$t_related_issue = array(
		'id' => $t_bug->id,
		'status' => mci_enum_get_array_by_id( $t_bug->status, 'status', $t_lang ),
		'resolution' => mci_enum_get_array_by_id( $t_bug->resolution, 'resolution', $t_lang ),
		'summary' => $t_bug->summary
	);

	if( !empty( $t_bug->handler_id ) ) {
		if( access_has_bug_level(
			config_get( 'view_handler_threshold', null, null, $t_bug->project_id ),
			$t_issue_id, $t_user_id ) ) {
			$t_related_issue['handler'] = mci_account_get_array_by_id( $t_bug->handler_id );
		}
	}

	return $t_related_issue;
}

/**
 * Return user's default language given a user id
 * @param integer $p_user_id User id.
 * @return string language string
 */
function mci_get_user_lang( $p_user_id ) {
	$t_lang = user_pref_get_pref( $p_user_id, 'language' );
	if( $t_lang == 'auto' ) {
		$t_lang = lang_map_auto();
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
	if( ApiObjectFactory::$soap ) {
		return preg_replace( '/[^\x9\xA\xD\x20-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]+/u', '', $p_input );
	}

	return $p_input;
}

/**
 * Gets the url for MantisBT.
 *
 * @return string MantisBT URL terminated by a /.
 */
function mci_get_mantis_path() {
	return config_get_global( 'path' );
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
 * Convert version into appropriate format for SOAP/REST.
 *
 * @param string $p_version The version
 * @param int $p_project_id The project id
 * @return array|null|string The converted version
 */
function mci_get_version( $p_version, $p_project_id ) {
	$t_version_id = version_get_id( $p_version, $p_project_id );
	if( $t_version_id === false ) {
		return null;
	}

	if( is_blank( $p_version ) ) {
		return null;
	}

	if( ApiObjectFactory::$soap ) {
		return $p_version;
	}

	return array(
		'id' => (int)$t_version_id,
		'name' => $p_version,
	);
}

/**
 * Gets the version id based on version input from the API.  This can be
 * a string or an object (with id or name or both).  If both id and name
 * exist on the object, id takes precedence.
 *
 * @param string|object $p_version The version string or object with name or id or both.
 * @param int $p_project_id The project id.
 * @param string $p_field_name Version field name (e.g. version, target_version, fixed_in_version)
 * @return int|RestFault|SoapFault The version id, 0 if not supplied.
 */
function mci_get_version_id( $p_version, $p_project_id, $p_field_name = 'version' ) {
	$t_version_id = 0;
	$t_version_for_error = '';

	if( is_array( $p_version ) ) {
		if( isset( $p_version['id'] ) && is_numeric( $p_version['id'] ) ) {
			$t_version_id = (int)$p_version['id'];
			$t_version_for_error = $p_version['id'];
			if( !version_exists( $t_version_id ) ) {
				$t_version_id = false;
			}
		} elseif( isset( $p_version['name'] ) ) {
			$t_version_for_error = $p_version['name'];
			$t_version_id = version_get_id( $p_version['name'], $p_project_id );
		}
	} elseif( is_string( $p_version ) && !is_blank( $p_version ) ) {
		$t_version_for_error = $p_version;
		$t_version_id = version_get_id( $p_version, $p_project_id );
	}

	# Error when supplied, but not found
	if( $t_version_id === false ) {
		$t_error_when_version_not_found = config_get( 'webservice_error_when_version_not_found' );
		if( $t_error_when_version_not_found == ON ) {
			$t_project_name = project_get_name( $p_project_id );
			throw new ClientException(
				"Version '$t_version_for_error' does not exist in project '$t_project_name'.",
				ERROR_INVALID_FIELD_VALUE,
				array( 'version' )
			);
		}

		$t_version_when_not_found = config_get( 'webservice_version_when_not_found' );
		$t_version_id = version_get_id( $t_version_when_not_found );
	}

	return $t_version_id;
}


/**
 * Returns the category name, possibly null if no category is assigned
 *
 * @param integer $p_category_id A category identifier.
 * @return string
 */
function mci_get_category( $p_category_id ) {
	if( ApiObjectFactory::$soap ) {
		if( $p_category_id == 0 ) {
			# This should be really null, but will leaving it to avoid changing the behavior
			return '';
		}

		return mci_null_if_empty( category_get_name( $p_category_id ) );
	}

	if( $p_category_id == 0 ) {
		return null;
	}

	return array(
		'id' => $p_category_id,
		'name' => mci_null_if_empty( category_get_name( $p_category_id ) ),
	);
}

/**
 * Convert a category name, or category object reference (array w/ id, name,
 * or id + name) to a category id for a given project.
 *
 * @param string|array $p_category Category name or array with id and/or name.
 * @param integer $p_project_id    Project id.
 * @return integer|SoapFault|RestFault category id or error.
 */
function mci_get_category_id( $p_category, $p_project_id ) {
	$fn_get_category_id_internal = function( $p_category, $p_project_id ) {
		if( !isset( $p_category ) ) {
			return 0;
		}

		$t_category_name = '';

		if( is_array( $p_category ) ) {
			if( isset( $p_category['id'] ) ) {
				if( category_exists( $p_category['id'] ) ) {
					return $p_category['id'];
				}
			} else if( isset( $p_category['name'] ) ) {
				$t_category_name = $p_category['name'];
			} else {
				return 0;
			}
		} else {
			$t_category_name = $p_category;
		}

		$t_cat_array = category_get_all_rows( $p_project_id );
		foreach( $t_cat_array as $t_category_row ) {
			if( strcasecmp( $t_category_row['name'], $t_category_name ) == 0 ) {
				return $t_category_row['id'];
			}
		}

		return 0;
	};

	$t_category_id = $fn_get_category_id_internal( $p_category, $p_project_id );
	if( $t_category_id == 0 && !config_get( 'allow_no_category' ) ) {
		if( !isset( $p_category ) ) {
			throw new ClientException(
				'Category field must be supplied.',
				ERROR_EMPTY_FIELD,
				array( 'category' )
			);
		}

		# category may be a string, array with id, array with name, or array
		# with id + name. Serialize to json to include in error message.
		$t_cat_desc = json_encode( $p_category );

		return ApiObjectFactory::faultBadRequest(
			"Category '{$t_cat_desc}' not found." );
	}

	# Make sure the category belongs to the given project's hierarchy
	category_ensure_exists_in_project( $t_category_id, $p_project_id );

	return $t_category_id;
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
			'date_order' => ApiObjectFactory::datetime( $p_version['date_order'] ),
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
 * Unhandled exception handler
 *
 * @param Exception|Error $p_exception The exception to handle
 * @return void
 */
function mc_error_exception_handler( $p_exception ) {
	if( is_a( $p_exception, 'Mantis\Exceptions\ClientException' ) ) {
		$t_cause = 'Client';
		$t_message = $p_exception->getMessage();
		$t_log = false;
	} else if( is_a( $p_exception, 'Mantis\Exceptions\MantisException' ) ) {
		$t_cause = 'Server';
		$t_message = $p_exception->getMessage();
		$t_log = true;
	} else {
		$t_cause = 'Server';
		$t_message = 'Internal Service Error';		
		$t_log = true;
	}

	if( $t_log ) {
		$t_stack_as_string = error_stack_trace_as_string( $p_exception );
		$t_error_to_log =  $p_exception->getMessage() . "\n" . $t_stack_as_string;
		error_log( $t_error_to_log );
	}

	$t_fault = htmlentities( $t_message );

	echo <<<EOL
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
<SOAP-ENV:Body>
	<SOAP-ENV:Fault>
		<faultcode>SOAP-ENV:$t_cause</faultcode>
		<faultstring>$t_fault</faultstring>
	</SOAP-ENV:Fault>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>
EOL;
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
 * @return void
 */
function mc_error_handler( $p_type, $p_error, $p_file, $p_line ) {
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
			$t_trace .= ( isset( $t_frame['file'] ) ? basename( $t_frame['file'] ) : 'UnknownFile' )
				. ' L' . ( isset( $t_frame['line'] ) ? $t_frame['line'] : '?' )
				. ' ' . ( isset( $t_frame['function'] ) ? $t_frame['function'] : 'UnknownFunction' );

			$t_args = array();
			if( isset( $t_frame['params'] ) && ( count( $t_frame['params'] ) > 0 ) ) {
				$t_trace .= ' Params: ';
				foreach( $t_frame['params'] as $t_value ) {
					$t_args[] = error_build_parameter_string( $t_value );
				}

				$t_trace .= '(' . implode( ', ', $t_args ) . ')';
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
			$t_trace .= ( isset( $t_frame['file'] ) ? basename( $t_frame['file'] ) : 'UnknownFile' )
				. ' L' . ( isset( $t_frame['line'] ) ? $t_frame['line'] : '?' )
				. ' ' . ( isset( $t_frame['function'] ) ? $t_frame['function'] : 'UnknownFunction' );

			$t_args = array();
			if( isset( $t_frame['args'] ) ) {
				foreach( $t_frame['args'] as $t_value ) {
					$t_args[] = error_build_parameter_string( $t_value );
				}

				$t_trace .= '(' . implode( ', ', $t_args ) . ')';
			} else {
				$t_trace .= '()';
			}

			$t_trace .= "\n";
		}
	}

	return $t_trace;
}

/**
 * Returns a fault signalling corresponding to a failed login
 * situation
 *
 * @return RestFault|SoapFault
 */
function mci_fault_login_failed() {
	return ApiObjectFactory::faultForbidden( 'Access denied' );
}

/**
 * Returns a soap_fault signalling that the user does not have
 * access rights for the specific action.
 *
 * @param integer $p_user_id A user id, optional.
 * @param string  $p_detail  The optional details to append to the error message.
 * @return RestFault|SoapFault
 */
function mci_fault_access_denied($p_user_id = 0, $p_detail = '' ) {
	if( $p_user_id ) {
		$t_user_name = user_get_name( $p_user_id );
		$t_reason = 'Access denied for user '. $t_user_name . '.';
	} else {
		$t_reason = 'Access denied';
	}

	if( !is_blank( $p_detail ) ) {
		$t_reason .= ' Reason: ' . $p_detail . '.';
	}

	return ApiObjectFactory::faultForbidden( $t_reason );
}

/**
 * Remove the keys with null values from the supplied array.
 *
 * @param array $p_array The array to filter.
 * @return void
 */
function mci_remove_null_keys( &$p_array ) {
	$t_keys_to_remove = array();

	foreach( $p_array as $t_key => $t_value ) {
		if( is_null( $t_value ) ) {
			$t_keys_to_remove[] = $t_key;
		}
	}

	foreach( $t_keys_to_remove as $t_key ) {
		unset( $p_array[$t_key] );
	}
}

/**
 * Remove the keys with empty arrays from the supplied array.
 *
 * @param array $p_array The array to filter.
 * @return void
 */
function mci_remove_empty_arrays( &$p_array ) {
	$t_keys_to_remove = array();

	foreach( $p_array as $t_key => $t_value ) {
		if( is_array( $t_value ) && empty( $t_value ) ) {
			$t_keys_to_remove[] = $t_key;
		}
	}

	foreach( $t_keys_to_remove as $t_key ) {
		unset( $p_array[$t_key] );
	}
}

/**
 * Hash a string for etag.
 *
 * @param string $p_string The string to hash
 * @return string The hash.
 */
function mci_etag_hash( $p_string ) {
	return hash( 'sha256', $p_string );
}
