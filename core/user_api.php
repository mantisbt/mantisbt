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
 * User API
 *
 * @package CoreAPI
 * @subpackage UserAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses email_api.php
 * @uses error_api.php
 * @uses filter_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses ldap_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses string_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 */

require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'email_api.php' );
require_api( 'error_api.php' );
require_api( 'filter_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'ldap_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );
require_api( 'string_api.php' );
require_api( 'user_pref_api.php' );
require_api( 'utility_api.php' );

# Cache of user rows from {user} table, indexed by user_id
# If id does not exists, a value of 'false' is stored
$g_cache_user = array();

$g_user_accessible_subprojects_cache = null;

/**
 * Cache a user row if necessary and return the cached copy
 * If the second parameter is true (default), trigger an error
 * if the user can't be found.  If the second parameter is
 * false, return false if the user can't be found.
 *
 * @param integer $p_user_id        A valid user identifier.
 * @param boolean $p_trigger_errors Trigger an error is the user does not exist.
 * @return array|boolean array of database data or false if not found
 */
function user_cache_row( $p_user_id, $p_trigger_errors = true ) {
	global $g_cache_user;

	$c_user_id = (int)$p_user_id;

	if( !isset( $g_cache_user[$c_user_id] ) ) {
		user_cache_array_rows( array( $c_user_id ) );
	}

	$t_user_row = $g_cache_user[$c_user_id];

	if( !$t_user_row ) {
		if( $p_trigger_errors ) {
			error_parameters( (integer)$p_user_id );
			trigger_error( ERROR_USER_BY_ID_NOT_FOUND, ERROR );
		}

		return false;
	}

	return $t_user_row;
}

/**
 * Loads user rows in cache for a set of User ID's
 * Store false if the user does not exists
 * @param array $p_user_id_array An array of user identifiers.
 * @return void
 */
function user_cache_array_rows( array $p_user_id_array ) {
	global $g_cache_user;
	$c_user_id_array = array();

	foreach( $p_user_id_array as $t_user_id ) {
		if( !isset( $g_cache_user[(int)$t_user_id] ) ) {
			$c_user_id_array[(int)$t_user_id] = (int)$t_user_id;
		}
	}
	if( empty( $c_user_id_array ) ) {
		return;
	}

	db_param_push();
	$t_params = array();
	$t_sql_in_params = array();
	foreach( $c_user_id_array as $t_id ) {
		$t_params[] = $t_id;
		$t_sql_in_params[] = db_param();
	}
	$t_query = 'SELECT * FROM {user} WHERE id IN (' . implode( ',', $t_sql_in_params ) . ')';
	$t_result = db_query( $t_query, $t_params );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$c_user_id = (int)$t_row['id'];
		$g_cache_user[$c_user_id] = $t_row;
		unset( $c_user_id_array[$c_user_id] );
	}
	# set the remaining ids to false as not-found
	foreach( $c_user_id_array as $t_id ) {
		$g_cache_user[$t_id] = false;
	}
}

/**
 * Cache a user row
 * @param array $p_user_database_result A user row to cache.
 * @return void
 */
function user_cache_database_result( array $p_user_database_result ) {
	global $g_cache_user;

	$g_cache_user[$p_user_database_result['id']] = $p_user_database_result;
}

/**
 * Clear the user cache (or just the given id if specified)
 * @param integer $p_user_id A valid user identifier or the default of null to clear cache for all users.
 * @return boolean
 */
function user_clear_cache( $p_user_id = null ) {
	global $g_cache_user;

	if( null === $p_user_id ) {
		$g_cache_user = array();
	} else {
		unset( $g_cache_user[$p_user_id] );
	}

	return true;
}

/**
 * Update Cache entry for a given user and field
 * @param integer $p_user_id A valid user id to update.
 * @param string  $p_field   The name of the field on the user object to update.
 * @param mixed   $p_value   The updated value for the user object field.
 * @return void
 */
function user_update_cache( $p_user_id, $p_field, $p_value ) {
	global $g_cache_user;

	if( isset( $g_cache_user[$p_user_id] ) && isset( $g_cache_user[$p_user_id][$p_field] ) ) {
		$g_cache_user[$p_user_id][$p_field] = $p_value;
	} else {
		user_clear_cache( $p_user_id );
	}
}

/**
 * Searches the cache for a given field and value pair against any user,
 * and returns the first user id that matches
 *
 * @param string $p_field The user object field name to search the cache for.
 * @param mixed  $p_value The field value to look for in the cache.
 * @return integer|boolean
 */
function user_search_cache( $p_field, $p_value ) {
	global $g_cache_user;
	if( isset( $g_cache_user ) ) {
		foreach( $g_cache_user as $t_user ) {
			if( $t_user && $t_user[$p_field] == $p_value ) {
				return $t_user;
			}
		}
	}
	return false;
}

/**
 * check to see if user exists by id
 * return true if it does, false otherwise
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean
 */
function user_exists( $p_user_id ) {
	$t_row = user_cache_row( $p_user_id, false );

	if( false === $t_row ) {
		return false;
	} else {
		return true;
	}
}

/**
 * check to see if user exists by id
 * if the user does not exist, trigger an error
 *
 * @param integer $p_user_id A valid user identifier.
 * @return void
 */
function user_ensure_exists( $p_user_id ) {
	$c_user_id = (integer)$p_user_id;

	if( !user_exists( $c_user_id ) ) {
		error_parameters( $c_user_id );
		trigger_error( ERROR_USER_BY_ID_NOT_FOUND, ERROR );
	}
}

/**
 * return true if the username is unique, false if there is already a user with that username
 * @param string $p_username The username to check.
 * @return boolean
 */
function user_is_name_unique( $p_username ) {
	db_param_push();
	$t_query = 'SELECT username FROM {user} WHERE username=' . db_param();
	$t_result = db_query( $t_query, array( $p_username ), 1 );

	return !db_result( $t_result );
}

/**
 * Check if the username is unique and trigger an ERROR if it isn't
 * @param string $p_username The username to check.
 * @return void
 */
function user_ensure_name_unique( $p_username ) {
	if( !user_is_name_unique( $p_username ) ) {
		trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
	}
}

/**
 * Checks if the email address is unique.
 *
 * @param string $p_email The email to check.
 * @param integer $p_user_id The user id that we are validating for or null for
 *                           the case of a new user.
 *
 * @return boolean true: unique or blank, false: otherwise
 */
function user_is_email_unique( $p_email, $p_user_id = null ) {
	if( is_blank( $p_email ) ) {
		return true;
	}

	$p_email = trim( $p_email );

	db_param_push();
	if ( $p_user_id === null ) {
		$t_query = 'SELECT email FROM {user} WHERE email=' . db_param();
		$t_result = db_query( $t_query, array( $p_email ), 1 );
	} else {
		$t_query = 'SELECT email FROM {user} WHERE id<>' . db_param() .
			' AND email=' . db_param();
		$t_result = db_query( $t_query, array( $p_user_id, $p_email ), 1 );
	}

	return !db_result( $t_result );
}

/**
 * Check if the email is unique and trigger an ERROR if it isn't
 *
 * @param string $p_email The email address to check.
 * @param integer $p_user_id The user id that we are validating for or null for
 *                           the case of a new user.
 *
 * @return void
 */
function user_ensure_email_unique( $p_email, $p_user_id = null ) {
	if( !config_get_global( 'email_ensure_unique' ) ) {
		return;
	}

	if( !user_is_email_unique( $p_email, $p_user_id ) ) {
		trigger_error( ERROR_USER_EMAIL_NOT_UNIQUE, ERROR );
	}
}

/**
 * Check if the realname is a valid username (does not account for uniqueness)
 * Return 0 if it is invalid, The number of matches + 1
 *
 * @param string $p_username The username to check.
 * @param string $p_realname The realname to check.
 * @return integer
 */
function user_is_realname_unique( $p_username, $p_realname ) {
	if( is_blank( $p_realname ) ) {
		# don't bother checking if realname is blank
		return 1;
	}

	$p_username = trim( $p_username );
	$p_realname = trim( $p_realname );

	# allow realname to match username
	$t_duplicate_count = 0;
	if( $p_realname !== $p_username ) {
		# check realname does not match an existing username
		#  but allow it to match the current user
		$t_target_user = user_get_id_by_name( $p_username );
		$t_other_user = user_get_id_by_name( $p_realname );
		if( ( false !== $t_other_user ) && ( $t_target_user !== $t_other_user ) ) {
			return 0;
		}

		# check to see if the realname is unique
		db_param_push();
		$t_query = 'SELECT id FROM {user} WHERE realname=' . db_param();
		$t_result = db_query( $t_query, array( $p_realname ) );

		$t_users = array();
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_users[] = $t_row;
		}
		$t_duplicate_count = count( $t_users );

		if( $t_duplicate_count > 0 ) {
			# set flags for non-unique realnames
			if( config_get( 'differentiate_duplicates' ) ) {
				for( $i = 0;$i < $t_duplicate_count;$i++ ) {
					$t_user_id = $t_users[$i]['id'];
					user_set_field( $t_user_id, 'duplicate_realname', ON );
				}
			}
		}
	}
	return $t_duplicate_count + 1;
}

/**
 * Check if the realname is a unique
 * Trigger an error if the username is not valid
 *
 * @param string $p_username The username to check.
 * @param string $p_realname The realname to check.
 * @return void
 */
function user_ensure_realname_unique( $p_username, $p_realname ) {
	if( 1 > user_is_realname_unique( $p_username, $p_realname ) ) {
		trigger_error( ERROR_USER_REAL_MATCH_USER, ERROR );
	}
}

/**
 * Check if the username is a valid username (does not account for uniqueness) realname can match
 * @param string $p_username The username to check.
 * @return boolean return true if user name is valid, false otherwise
 */
function user_is_name_valid( $p_username ) {
	# The DB field is hard-coded. DB_FIELD_SIZE_USERNAME should not be modified.
	if( utf8_strlen( $p_username ) > DB_FIELD_SIZE_USERNAME ) {
		return false;
	}

	# username must consist of at least one character
	if( is_blank( $p_username ) ) {
		return false;
	}

	# Only allow a basic set of characters
	if( 0 == preg_match( config_get( 'user_login_valid_regex' ), $p_username ) ) {
		return false;
	}

	# We have a valid username
	return true;
}

/**
 * Check if the username is a valid username (does not account for uniqueness)
 * Trigger an error if the username is not valid
 * @param string $p_username The username to check.
 * @return void
 */
function user_ensure_name_valid( $p_username ) {
	if( !user_is_name_valid( $p_username ) ) {
		trigger_error( ERROR_USER_NAME_INVALID, ERROR );
	}
}

/**
 * return whether user is monitoring bug for the user id and bug id
 * @param integer $p_user_id A valid user identifier.
 * @param integer $p_bug_id  A valid bug identifier.
 * @return boolean
 */
function user_is_monitoring_bug( $p_user_id, $p_bug_id ) {
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {bug_monitor}
				  WHERE user_id=' . db_param() . ' AND bug_id=' . db_param();

	$t_result = db_query( $t_query, array( (int)$p_user_id, (int)$p_bug_id ) );

	if( 0 == db_result( $t_result ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check if the specified user is an enabled user with admin access level or above.
 * @param integer $p_user_id A valid user identifier.
 * @return boolean true: admin, false: otherwise.
 */
function user_is_administrator( $p_user_id ) {
	if( !user_is_enabled( $p_user_id ) ) {
		return false;
	}

	$t_access_level = user_get_field( $p_user_id, 'access_level' );
	return $t_access_level >= config_get_global( 'admin_site_threshold' );
}

/**
 * Check if a user has a protected user account.
 * Protected user accounts cannot be updated without manage_user_threshold
 * permission. If the user ID supplied is that of the anonymous user, this
 * function will always return true. The anonymous user account is always
 * considered to be protected.
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean true: user is protected; false: user is not protected.
 * @access public
 */
function user_is_protected( $p_user_id ) {
	return user_is_anonymous( $p_user_id ) || ON == user_get_field( $p_user_id, 'protected' );
}

/**
 * Check if a user is the anonymous user account.
 * When anonymous logins are disabled this function will always return false.
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean true: user is the anonymous user; false: user is not the anonymous user.
 * @access public
 */
function user_is_anonymous( $p_user_id ) {
	return auth_anonymous_enabled() && user_get_field( $p_user_id, 'username' ) == auth_anonymous_account();
}

/**
 * Trigger an ERROR if the user account is protected
 *
 * @param integer $p_user_id A valid user identifier.
 * @return void
 */
function user_ensure_unprotected( $p_user_id ) {
	if( user_is_protected( $p_user_id ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}
}

/**
 * return true is the user account is enabled, false otherwise
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean
 */
function user_is_enabled( $p_user_id ) {
	if( ON == user_get_field( $p_user_id, 'enabled' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Count the number of users at or greater than a specific level
 *
 * @param integer $p_level   Access Level to count users. The default is to include ANYBODY.
 * @param bool    $p_enabled true: must be enabled, false: must be disabled, null: don't care.
 * @return integer The number of users.
 */
function user_count_level( $p_level = ANYBODY, $p_enabled = null ) {
	db_param_push();
	$t_query = 'SELECT COUNT(id) FROM {user} WHERE access_level >= ' . db_param();
	$t_param = array( $p_level );

	if( $p_enabled !== null ) {
		$t_query .= ' AND enabled = ' . db_param();
		$t_param[] = (bool)$p_enabled;
	}

	# Get the number of users
	$t_result = db_query( $t_query, $t_param );
	$t_count = db_result( $t_result );

	return $t_count;
}

/**
 * Return an array of user ids that are logged in.
 * A user is considered logged in if the last visit timestamp is within the
 * specified session duration.
 * If the session duration is 0, then no users will be returned.
 * @param integer $p_session_duration_in_minutes The duration to return logged in users for.
 * @return array
 */
function user_get_logged_in_user_ids( $p_session_duration_in_minutes ) {
	$t_session_duration_in_minutes = (integer)$p_session_duration_in_minutes;

	# if session duration is 0, then there is no logged in users.
	if( $t_session_duration_in_minutes == 0 ) {
		return array();
	}

	# Generate timestamp
	$t_last_timestamp_threshold = mktime( date( 'H' ), date( 'i' ) - 1 * $t_session_duration_in_minutes, date( 's' ), date( 'm' ), date( 'd' ), date( 'Y' ) );

	# Execute query
	db_param_push();
	$t_query = 'SELECT id FROM {user} WHERE last_visit > ' . db_param();
	$t_result = db_query( $t_query, array( $t_last_timestamp_threshold ), 1 );

	# Get the list of connected users
	$t_users_connected = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_users_connected[] = $t_row['id'];
	}

	return $t_users_connected;
}

/**
 * Create a user.
 * returns false if error, the generated cookie string if valid
 *
 * @param string  $p_username     A valid username.
 * @param string  $p_password     The password to set for the user.
 * @param string  $p_email        The Email Address of the user.
 * @param integer $p_access_level The global access level for the user.
 * @param boolean $p_protected    Whether the account is protected from modifications (default false).
 * @param boolean $p_enabled      Whether the account is enabled.
 * @param string  $p_realname     The realname of the user.
 * @param string  $p_admin_name   The name of the administrator creating the account.
 * @return string Cookie String
 */
function user_create( $p_username, $p_password, $p_email = '',
	$p_access_level = null, $p_protected = false, $p_enabled = true,
	$p_realname = '', $p_admin_name = '' ) {
	if( null === $p_access_level ) {
		$p_access_level = config_get( 'default_new_account_access_level' );
	}

	$t_password = auth_process_plain_password( $p_password );

	$c_enabled = (bool)$p_enabled;

	user_ensure_name_valid( $p_username );
	user_ensure_name_unique( $p_username );
	user_ensure_email_unique( $p_email );
	user_ensure_realname_unique( $p_username, $p_realname );
	email_ensure_valid( $p_email );

	$t_cookie_string = auth_generate_unique_cookie_string();

	db_param_push();
	$t_query = 'INSERT INTO {user}
				    ( username, email, password, date_created, last_visit,
				     enabled, access_level, login_count, cookie_string, realname )
				  VALUES
				    ( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param()  . ',
				     ' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ', ' . db_param() . ')';
	db_query( $t_query, array( $p_username, $p_email, $t_password, db_now(), db_now(), $c_enabled, (int)$p_access_level, 0, $t_cookie_string, $p_realname ) );

	# Create preferences for the user
	$t_user_id = db_insert_id( db_get_table( 'user' ) );

	# Users are added with protected set to FALSE in order to be able to update
	# preferences.  Now set the real value of protected.
	if( $p_protected ) {
		user_set_field( $t_user_id, 'protected', (bool)$p_protected );
	}

	# Send notification email
	if( !is_blank( $p_email ) ) {
		$t_confirm_hash = auth_generate_confirm_hash( $t_user_id );
		token_set( TOKEN_ACCOUNT_ACTIVATION, $t_confirm_hash, TOKEN_EXPIRY_ACCOUNT_ACTIVATION, $t_user_id );
		email_signup( $t_user_id, $t_confirm_hash, $p_admin_name );
	}

	event_signal( 'EVENT_MANAGE_USER_CREATE', array( $t_user_id ) );

	return $t_cookie_string;
}

/**
 * Signup a user.
 * If the use_ldap_email config option is on then tries to find email using
 * ldap. $p_email may be empty, but the user wont get any emails.
 * returns false if error, the generated cookie string if ok
 * @param string $p_username The username to sign up.
 * @param string $p_email    The email address of the user signing up.
 * @return string|boolean cookie string or false on error
 */
function user_signup( $p_username, $p_email = null ) {
	if( null === $p_email ) {
		$p_email = '';

		# @@@ I think the ldap_email stuff is a bit borked
		#  Where is it being set?  When is it being used?
		#  Shouldn't we override an email that is passed in here?
		#  If the user doesn't exist in ldap, is the account created?
		#  If so, there password won't get set anywhere...  (etc)
		#  RJF: I was going to check for the existence of an LDAP email.
		#  however, since we can't create an LDAP account at the moment,
		#  and we don't know the user password in advance, we may not be able
		#  to retrieve it anyway.
		#  I'll re-enable this once a plan has been properly formulated for LDAP
		#  account management and creation.
		#			$t_email = '';
		#			if( ON == config_get( 'use_ldap_email' ) ) {
		#				$t_email = ldap_email_from_username( $p_username );
		#			}
		#			if( !is_blank( $t_email ) ) {
		#				$p_email = $t_email;
		#			}
	}

	$p_email = trim( $p_email );

	email_ensure_not_disposable( $p_email );
	email_ensure_valid( $p_email );
	user_ensure_email_unique( $p_email );

	# Create random password
	$t_password = auth_generate_random_password();

	return user_create( $p_username, $t_password, $p_email, auth_signup_access_level() );
}

/**
 * delete project-specific user access levels.
 * returns true when successfully deleted
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean Always true
 */
function user_delete_project_specific_access_levels( $p_user_id ) {
	user_ensure_unprotected( $p_user_id );

	db_param_push();
	$t_query = 'DELETE FROM {project_user_list} WHERE user_id=' . db_param();
	db_query( $t_query, array( (int)$p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

/**
 * delete profiles for the specified user
 * returns true when successfully deleted
 * @param integer $p_user_id A valid user identifier.
 * @return boolean
 */
function user_delete_profiles( $p_user_id ) {
	user_ensure_unprotected( $p_user_id );

	# Remove associated profiles
	db_param_push();
	$t_query = 'DELETE FROM {user_profile} WHERE user_id=' . db_param();
	db_query( $t_query, array( (int)$p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

/**
 * delete a user account (account, profiles, preferences, project-specific access levels)
 * returns true when the account was successfully deleted
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean Always true
 */
function user_delete( $p_user_id ) {
	$c_user_id = (int)$p_user_id;

	user_ensure_unprotected( $p_user_id );

	event_signal( 'EVENT_MANAGE_USER_DELETE', array( $p_user_id ) );

	# Remove associated profiles
	user_delete_profiles( $p_user_id );

	# Remove associated preferences
	user_pref_delete_all( $p_user_id );

	# Remove project specific access levels
	user_delete_project_specific_access_levels( $p_user_id );

	# unset non-unique realname flags if necessary
	if( config_get( 'differentiate_duplicates' ) ) {
		$c_realname = user_get_field( $p_user_id, 'realname' );
		db_param_push();
		$t_query = 'SELECT id FROM {user} WHERE realname=' . db_param();
		$t_result = db_query( $t_query, array( $c_realname ) );

		$t_users = array();
		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_users[] = $t_row;
		}

		$t_user_count = count( $t_users );

		if( $t_user_count == 2 ) {
			# unset flags if there are now only 2 unique names
			for( $i = 0;$i < $t_user_count;$i++ ) {
				$t_user_id = $t_users[$i]['id'];
				user_set_field( $t_user_id, 'duplicate_realname', OFF );
			}
		}
	}

	user_clear_cache( $p_user_id );

	# Remove account
	db_param_push();
	$t_query = 'DELETE FROM {user} WHERE id=' . db_param();
	db_query( $t_query, array( $c_user_id ) );

	return true;
}

/**
 * get a user id from a username
 * return false if the username does not exist
 *
 * @param string $p_username The username to retrieve data for.
 * @return integer|boolean
 */
function user_get_id_by_name( $p_username ) {
	if( $t_user = user_search_cache( 'username', $p_username ) ) {
		return $t_user['id'];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {user} WHERE username=' . db_param();
	$t_result = db_query( $t_query, array( $p_username ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		user_cache_database_result( $t_row );
		return $t_row['id'];
	}
	return false;
}

/**
 * Get a user id from their email address
 *
 * @param string $p_email The email address to retrieve data for.
 * @return array
 */
function user_get_id_by_email( $p_email ) {
	if( $t_user = user_search_cache( 'email', $p_email ) ) {
		return $t_user['id'];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {user} WHERE email=' . db_param();
	$t_result = db_query( $t_query, array( $p_email ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		user_cache_database_result( $t_row );
		return $t_row['id'];
	}
	return false;
}

/**
 * Given an email address, this method returns the ids of the enabled users with
 * that address.
 *
 * The returned list will be sorted by higher access level first.
 *
 * @param string $p_email The email address, can be an empty string to get users
 *                        without an email address.
 *
 * @return array The user ids or an empty array.
 */
function user_get_enabled_ids_by_email( $p_email ) {
	db_param_push();
	$t_query = 'SELECT * FROM {user} WHERE email=' . db_param() .
		' AND enabled=' . db_param() . ' ORDER BY access_level DESC';
	$t_result = db_query( $t_query, array( $p_email, 1 ) );

	$t_user_ids = array();
	while ( $t_row = db_fetch_array( $t_result ) ) {
		user_cache_database_result( $t_row );
		$t_user_ids[] = (int)$t_row['id'];
	}

	return $t_user_ids;
}

/**
 * Get a user id from their real name
 *
 * @param string $p_realname The realname to retrieve data for.
 * @return array
 */
function user_get_id_by_realname( $p_realname ) {
	if( $t_user = user_search_cache( 'realname', $p_realname ) ) {
		return $t_user['id'];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {user} WHERE realname=' . db_param();
	$t_result = db_query( $t_query, array( $p_realname ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		return false;
	} else {
		user_cache_database_result( $t_row );
		return $t_row['id'];
	}
}

/**
 * return all data associated with a particular user name
 * return false if the username does not exist
 *
 * @param integer $p_username The username to retrieve data for.
 * @return array
 */
function user_get_row_by_name( $p_username ) {
	$t_user_id = user_get_id_by_name( $p_username );

	if( false === $t_user_id ) {
		return false;
	}

	$t_row = user_get_row( $t_user_id );

	return $t_row;
}

/**
 * return a user row
 *
 * @param integer $p_user_id A valid user identifier.
 * @return array
 */
function user_get_row( $p_user_id ) {
	return user_cache_row( $p_user_id );
}

/**
 * return the specified user field for the user id
 *
 * @param integer $p_user_id    A valid user identifier.
 * @param string  $p_field_name The field name to retrieve.
 * @return string
 */
function user_get_field( $p_user_id, $p_field_name ) {
	if( NO_USER == $p_user_id ) {
		error_parameters( NO_USER );
		trigger_error( ERROR_USER_BY_ID_NOT_FOUND, WARNING );
		return '@null@';
	}

	$t_row = user_get_row( $p_user_id );

	if( isset( $t_row[$p_field_name] ) ) {
		switch( $p_field_name ) {
			case 'access_level':
				return (int)$t_row[$p_field_name];
			default:
				return $t_row[$p_field_name];
		}
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * lookup the user's email in LDAP or the db as appropriate
 *
 * @param integer $p_user_id A valid user identifier.
 * @return string
 */
function user_get_email( $p_user_id ) {
	$t_email = '';
	if( LOGIN_METHOD_LDAP == config_get( 'login_method' ) && ON == config_get( 'use_ldap_email' ) ) {
		$t_email = ldap_email( $p_user_id );
	}
	if( is_blank( $t_email ) ) {
		$t_email = user_get_field( $p_user_id, 'email' );
	}
	return $t_email;
}

/**
 * lookup the user's realname
 *
 * @param integer $p_user_id A valid user identifier.
 * @return string
 */
function user_get_realname( $p_user_id ) {
	$t_realname = '';

	if( LOGIN_METHOD_LDAP == config_get( 'login_method' ) && ON == config_get( 'use_ldap_realname' ) ) {
		$t_realname = ldap_realname( $p_user_id );
	}

	if( is_blank( $t_realname ) ) {
		$t_realname = user_get_field( $p_user_id, 'realname' );
	}

	return $t_realname;
}

/**
 * return the username or a string "user<id>" if the user does not exist
 * if show_user_realname_threshold is set and real name is not empty, return it instead
 *
 * @param integer $p_user_id A valid user identifier.
 * @return string
 */
function user_get_name( $p_user_id ) {
	$t_row = user_cache_row( $p_user_id, false );

	if( false == $t_row ) {
		return lang_get( 'prefix_for_deleted_users' ) . (int)$p_user_id;
	} else {
		if( ON == config_get( 'show_realname' ) ) {
			if( is_blank( $t_row['realname'] ) ) {
				return $t_row['username'];
			} else {
				if( isset( $t_row['duplicate_realname'] ) && ( ON == $t_row['duplicate_realname'] ) ) {
					return $t_row['realname'] . ' (' . $t_row['username'] . ')';
				} else {
					return $t_row['realname'];
				}
			}
		} else {
			return $t_row['username'];
		}
	}
}

/**
 * return the user's access level
 * account for private project and the project user lists
 *
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_project_id A valid project identifier.
 * @return integer
 */
function user_get_access_level( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_access_level = user_get_field( $p_user_id, 'access_level' );

	if( user_is_administrator( $p_user_id ) ) {
		return $t_access_level;
	}

	$t_project_access_level = project_get_local_user_access_level( $p_project_id, $p_user_id );

	if( false === $t_project_access_level ) {
		return $t_access_level;
	} else {
		return $t_project_access_level;
	}
}

$g_user_accessible_projects_cache = null;

/**
 * retun an array of project IDs to which the user has access
 *
 * @param integer $p_user_id       A valid user identifier.
 * @param boolean $p_show_disabled Whether to include disabled projects in the result array.
 * @return array
 */
function user_get_accessible_projects( $p_user_id, $p_show_disabled = false ) {
	global $g_user_accessible_projects_cache;

	if( null !== $g_user_accessible_projects_cache && auth_get_current_user_id() == $p_user_id && false == $p_show_disabled ) {
		return $g_user_accessible_projects_cache;
	}

	if( access_has_global_level( config_get( 'private_project_threshold' ), $p_user_id ) ) {
		$t_projects = project_hierarchy_get_subprojects( ALL_PROJECTS, $p_show_disabled );
	} else {
		$t_public = VS_PUBLIC;
		$t_private = VS_PRIVATE;

		db_param_push();
		$t_query = 'SELECT p.id, p.name, ph.parent_id
						  FROM {project} p
						  LEFT JOIN {project_user_list} u
						    ON p.id=u.project_id AND u.user_id=' . db_param() . '
						  LEFT JOIN {project_hierarchy} ph
						    ON ph.child_id = p.id
						  WHERE ' . ( $p_show_disabled ? '' : ( 'p.enabled = ' . db_param() . ' AND ' ) ) . '
							( p.view_state=' . db_param() . '
							    OR (p.view_state=' . db_param() . '
								    AND
							        u.user_id=' . db_param() . ' )
							) ORDER BY p.name';
		$t_result = db_query( $t_query, ( $p_show_disabled ? array( $p_user_id, $t_public, $t_private, $p_user_id ) : array( $p_user_id, true, $t_public, $t_private, $p_user_id ) ) );

		$t_projects = array();

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_projects[(int)$t_row['id']] = ( $t_row['parent_id'] === null ) ? 0 : (int)$t_row['parent_id'];
		}

		# prune out children where the parents are already listed. Make the list
		#  first, then prune to avoid pruning a parent before the child is found.
		$t_prune = array();
		foreach( $t_projects as $t_id => $t_parent ) {
			if( ( $t_parent !== 0 ) && isset( $t_projects[$t_parent] ) ) {
				$t_prune[] = $t_id;
			}
		}
		foreach( $t_prune as $t_id ) {
			unset( $t_projects[$t_id] );
		}
		$t_projects = array_keys( $t_projects );
	}

	if( auth_get_current_user_id() == $p_user_id ) {
		$g_user_accessible_projects_cache = $t_projects;
	}

	return $t_projects;
}

/**
 * return an array of sub-project IDs of a certain project to which the user has access
 * @param integer $p_user_id       A valid user identifier.
 * @param integer $p_project_id    A valid project identifier.
 * @param boolean $p_show_disabled Include disabled projects in the resulting array.
 * @return array
 */
function user_get_accessible_subprojects( $p_user_id, $p_project_id, $p_show_disabled = false ) {
	global $g_user_accessible_subprojects_cache;

	if( null !== $g_user_accessible_subprojects_cache && auth_get_current_user_id() == $p_user_id && false == $p_show_disabled ) {
		if( isset( $g_user_accessible_subprojects_cache[$p_project_id] ) ) {
			return $g_user_accessible_subprojects_cache[$p_project_id];
		} else {
			return array();
		}
	}

	db_param_push();

	if( access_has_global_level( config_get( 'private_project_threshold' ), $p_user_id ) ) {
		$t_enabled_clause = $p_show_disabled ? '' : 'p.enabled = ' . db_param() . ' AND';
		$t_query = 'SELECT DISTINCT p.id, p.name, ph.parent_id
					  FROM {project} p
					  LEFT JOIN {project_hierarchy} ph
					    ON ph.child_id = p.id
					  WHERE ' . $t_enabled_clause . '
					  	 ph.parent_id IS NOT NULL
					  ORDER BY p.name';
		$t_result = db_query( $t_query, ( $p_show_disabled ? array() : array( true ) ) );
	} else {
		$t_query = 'SELECT DISTINCT p.id, p.name, ph.parent_id
					  FROM {project} p
					  LEFT JOIN {project_user_list} u
					    ON p.id = u.project_id AND u.user_id=' . db_param() . '
					  LEFT JOIN {project_hierarchy} ph
					    ON ph.child_id = p.id
					  WHERE ' . ( $p_show_disabled ? '' : ( 'p.enabled = ' . db_param() . ' AND ' ) ) . '
					  	ph.parent_id IS NOT NULL AND
						( p.view_state=' . db_param() . '
						    OR (p.view_state=' . db_param() . '
							    AND
						        u.user_id=' . db_param() . ' )
						)
					  ORDER BY p.name';
		$t_param = array( $p_user_id, VS_PUBLIC, VS_PRIVATE, $p_user_id );
		if( !$p_show_disabled ) {
			# Insert enabled flag value in 2nd position of parameter array
			array_splice( $t_param, 1, 0, true );
		}
		$t_result = db_query( $t_query, $t_param );
	}

	$t_projects = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		if( !isset( $t_projects[(int)$t_row['parent_id']] ) ) {
			$t_projects[(int)$t_row['parent_id']] = array();
		}

		array_push( $t_projects[(int)$t_row['parent_id']], (int)$t_row['id'] );
	}

	if( auth_get_current_user_id() == $p_user_id ) {
		$g_user_accessible_subprojects_cache = $t_projects;
	}

	if( !isset( $t_projects[(int)$p_project_id] ) ) {
		$t_projects[(int)$p_project_id] = array();
	}

	return $t_projects[(int)$p_project_id];
}

/**
 * retun an array of sub-project IDs of all sub-projects project to which the user has access
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_project_id A valid project identifier.
 * @return array
 */
function user_get_all_accessible_subprojects( $p_user_id, $p_project_id ) {
	# @todo (thraxisp) Should all top level projects be a sub-project of ALL_PROJECTS implicitly?
	# affects how news and some summaries are generated
	$t_todo = user_get_accessible_subprojects( $p_user_id, $p_project_id );
	$t_subprojects = array();

	while( $t_todo ) {
		$t_elem = (int)array_shift( $t_todo );
		if( !in_array( $t_elem, $t_subprojects ) ) {
			array_push( $t_subprojects, $t_elem );
			$t_todo = array_merge( $t_todo, user_get_accessible_subprojects( $p_user_id, $t_elem ) );
		}
	}

	return $t_subprojects;
}

/**
 * Returns an array of project and sub-project IDs of all projects to which the
 * user has access and that are children of the specified project.
 *
 * @param integer $p_user_id    A valid user identifier or null for logged in user.
 * @param integer $p_project_id A valid project identifier.  ALL_PROJECTS returns
 *                              all top level projects and sub-projects.
 * @return array
 */
function user_get_all_accessible_projects( $p_user_id = null, $p_project_id = ALL_PROJECTS ) {
	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	if( ALL_PROJECTS == $p_project_id ) {
		$t_top_projects = user_get_accessible_projects( $p_user_id );

		# Cover the case for PHP < 5.4 where array_combine() returns
		# false and triggers warning if arrays are empty (see #16187)
		if( empty( $t_top_projects ) ) {
			return array();
		}

		# Create a combined array where key = value
		$t_project_ids = array_combine( $t_top_projects, $t_top_projects );

		# Add all subprojects user has access to
		foreach( $t_top_projects as $t_project ) {
			$t_subprojects_ids = user_get_all_accessible_subprojects( $p_user_id, $t_project );
			foreach( $t_subprojects_ids as $t_id ) {
				$t_project_ids[$t_id] = $t_id;
			}
		}
	} else {
		access_ensure_project_level( VIEWER, $p_project_id );
		$t_project_ids = user_get_all_accessible_subprojects( $p_user_id, $p_project_id );
		array_unshift( $t_project_ids, $p_project_id );
	}

	return $t_project_ids;
}

/**
 * Get a list of projects the specified user is assigned to.
 * @param integer $p_user_id A valid user identifier.
 * @return array An array of projects by project id the specified user is assigned to.
 *		The array contains the id, name, view state, and project access level for the user.
 */
function user_get_assigned_projects( $p_user_id ) {
	db_param_push();
	$t_query = 'SELECT DISTINCT p.id, p.name, p.view_state, u.access_level
				FROM {project} p
				LEFT JOIN {project_user_list} u
				ON p.id=u.project_id
				WHERE p.enabled = \'1\' AND
					u.user_id=' . db_param() . '
				ORDER BY p.name';
	$t_result = db_query( $t_query, array( $p_user_id ) );
	$t_projects = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_project_id = $t_row['id'];
		$t_projects[$t_project_id] = $t_row;
	}
	return $t_projects;
}

/**
 * List of users that are NOT in the specified project and that are enabled
 * if no project is specified use the current project
 * also exclude any administrators
 * @param integer $p_project_id A valid project identifier.
 * @return array List of users not assigned to the specified project
 */
function user_get_unassigned_by_project_id( $p_project_id = null ) {
	if( null === $p_project_id ) {
		$p_project_id = helper_get_current_project();
	}

	$t_adm = config_get_global( 'admin_site_threshold' );
	db_param_push();
	$t_query = 'SELECT DISTINCT u.id, u.username, u.realname
				FROM {user} u
				LEFT JOIN {project_user_list} p
				ON p.user_id=u.id AND p.project_id=' . db_param() . '
				WHERE u.access_level<' . db_param() . ' AND
					u.enabled = ' . db_param() . ' AND
					p.user_id IS NULL
				ORDER BY u.realname, u.username';
	$t_result = db_query( $t_query, array( $p_project_id, $t_adm, true ) );
	$t_display = array();
	$t_sort = array();
	$t_users = array();
	$t_show_realname = ( ON == config_get( 'show_realname' ) );
	$t_sort_by_last_name = ( ON == config_get( 'sort_by_last_name' ) );

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_users[] = $t_row['id'];
		$t_user_name = string_attribute( $t_row['username'] );
		$t_sort_name = $t_user_name;
		if( ( isset( $t_row['realname'] ) ) && ( $t_row['realname'] <> '' ) && $t_show_realname ) {
			$t_user_name = string_attribute( $t_row['realname'] );
			if( $t_sort_by_last_name ) {
				$t_sort_name_bits = explode( ' ', utf8_strtolower( $t_user_name ), 2 );
				$t_sort_name = ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
			} else {
				$t_sort_name = utf8_strtolower( $t_user_name );
			}
		}
		$t_display[] = $t_user_name;
		$t_sort[] = $t_sort_name;
	}
	array_multisort( $t_sort, SORT_ASC, SORT_STRING, $t_users, $t_display );
	$t_count = count( $t_sort );
	$t_user_list = array();
	for( $i = 0;$i < $t_count; $i++ ) {
		$t_user_list[$t_users[$i]] = $t_display[$i];
	}
	return $t_user_list;
}

/**
 * return the number of open assigned bugs to a user in a project
 *
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_project_id A valid project identifier.
 * @return integer
 */
function user_get_assigned_open_bug_count( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_where_prj = helper_project_specific_where( $p_project_id, $p_user_id ) . ' AND';

	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	db_param_push();
	$t_query = 'SELECT COUNT(*)
				  FROM {bug}
				  WHERE ' . $t_where_prj . '
						status<' . db_param() . ' AND
						handler_id=' . db_param();
	$t_result = db_query( $t_query, array( $t_resolved, $p_user_id ) );

	return db_result( $t_result );
}

/**
 * return the number of open reported bugs by a user in a project
 *
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_project_id A valid project identifier.
 * @return integer
 */
function user_get_reported_open_bug_count( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_where_prj = helper_project_specific_where( $p_project_id, $p_user_id ) . ' AND';

	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {bug}
				  WHERE ' . $t_where_prj . '
						  status<' . db_param() . ' AND
						  reporter_id=' . db_param();
	$t_result = db_query( $t_query, array( $t_resolved, $p_user_id ) );

	return db_result( $t_result );
}

/**
 * return a profile row
 *
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_profile_id The profile identifier to retrieve.
 * @return array
 */
function user_get_profile_row( $p_user_id, $p_profile_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {user_profile}
				  WHERE id=' . db_param() . ' AND
						user_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_profile_id, $p_user_id ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		trigger_error( ERROR_USER_PROFILE_NOT_FOUND, ERROR );
	}

	return $t_row;
}

/**
 * Get failed login attempts
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean
 */
function user_is_login_request_allowed( $p_user_id ) {
	$t_max_failed_login_count = config_get( 'max_failed_login_count' );
	$t_failed_login_count = user_get_field( $p_user_id, 'failed_login_count' );
	return( $t_failed_login_count < $t_max_failed_login_count || OFF == $t_max_failed_login_count );
}

/**
 * Get 'lost password' in progress attempts
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean
 */
function user_is_lost_password_request_allowed( $p_user_id ) {
	if( OFF == config_get( 'lost_password_feature' ) ) {
		return false;
	}
	$t_max_lost_password_in_progress_count = config_get( 'max_lost_password_in_progress_count' );
	$t_lost_password_in_progress_count = user_get_field( $p_user_id, 'lost_password_request_count' );
	return( $t_lost_password_in_progress_count < $t_max_lost_password_in_progress_count || OFF == $t_max_lost_password_in_progress_count );
}

/**
 * return the bug filter parameters for the specified user
 *
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_project_id A valid project identifier.
 * @return array The user filter, or default filter if not valid.
 */
function user_get_bug_filter( $p_user_id, $p_project_id = null ) {
	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	$t_view_all_cookie_id = filter_db_get_project_current( $t_project_id, $p_user_id );
	$t_view_all_cookie = filter_db_get_filter( $t_view_all_cookie_id, $p_user_id );

	$t_filter = filter_deserialize( $t_view_all_cookie );
	if( !$t_filter ) {
		return filter_get_default();
	}

	# when the user specific filter references a stored filter id, get that filter instead
	if( isset( $t_filter['_source_query_id'] ) && $t_view_all_cookie_id != $t_filter['_source_query_id'] ) {
		$t_source_query_id = $t_filter['_source_query_id'];
		# check if filter id is a proper stored filter, and is accesible
		if( filter_is_named_filter( $t_source_query_id ) && filter_is_accessible( $t_source_query_id ) ){
			# the actual stored filter can be retrieved
			$t_filter_row = filter_cache_row( $t_source_query_id, /* trigger_errors */ false );
			$t_filter = filter_deserialize( filter_db_get_filter( $t_source_query_id ) );
			# update the referenced stored filter id
			$t_filter['_source_query_id'] = $t_source_query_id;
		} else {
			# If the filter id is not valid, clean the referenced filter id
			unset( $t_filter['_source_query_id'] );
		}
	}
	$t_filter = filter_ensure_valid_filter( $t_filter );

	return $t_filter;
}

/**
 * Update the last_visited field to be now
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean always true
 */
function user_update_last_visit( $p_user_id ) {
	$c_user_id = (int)$p_user_id;
	$c_value = db_now();

	db_param_push();
	$t_query = 'UPDATE {user} SET last_visit=' . db_param() . ' WHERE id=' . db_param();
	db_query( $t_query, array( $c_value, $c_user_id ) );

	user_update_cache( $c_user_id, 'last_visit', $c_value );

	return true;
}

/**
 * Increment the number of times the user has logged in
 * This function is only called from the login.php script
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean always true
 */
function user_increment_login_count( $p_user_id ) {
	db_param_push();
	$t_query = 'UPDATE {user} SET login_count=login_count+1 WHERE id=' . db_param();
	db_query( $t_query, array( (int)$p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

/**
 * Reset to zero the failed login attempts
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean always true
 */
function user_reset_failed_login_count_to_zero( $p_user_id ) {
	db_param_push();
	$t_query = 'UPDATE {user} SET failed_login_count=0 WHERE id=' . db_param();
	db_query( $t_query, array( (int)$p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

/**
 * Increment the failed login count by 1
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean always true
 */
function user_increment_failed_login_count( $p_user_id ) {
	db_param_push();
	$t_query = 'UPDATE {user} SET failed_login_count=failed_login_count+1 WHERE id=' . db_param();
	db_query( $t_query, array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

/**
 * Reset to zero the 'lost password' in progress attempts
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean always true
 */
function user_reset_lost_password_in_progress_count_to_zero( $p_user_id ) {
	db_param_push();
	$t_query = 'UPDATE {user} SET lost_password_request_count=0 WHERE id=' . db_param();
	db_query( $t_query, array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

/**
 * Increment the failed login count by 1
 *
 * @param integer $p_user_id A valid user identifier.
 * @return boolean always true
 */
function user_increment_lost_password_in_progress_count( $p_user_id ) {
	db_param_push();
	$t_query = 'UPDATE {user}
				SET lost_password_request_count=lost_password_request_count+1
				WHERE id=' . db_param();
	db_query( $t_query, array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

/**
 * Sets multiple fields on a user
 *
 * @param integer $p_user_id A valid user identifier.
 * @param array   $p_fields  Keys are the field names and the values are the field values.
 * @return void
 */
function user_set_fields( $p_user_id, array $p_fields ) {
	if( empty( $p_fields ) ) {
		return;
	}

	if( !array_key_exists( 'protected', $p_fields ) ) {
		user_ensure_unprotected( $p_user_id );
	}

	db_param_push();
	$t_query = 'UPDATE {user}';
	$t_parameters = array();

	foreach ( $p_fields as $t_field_name => $t_field_value ) {
		$c_field_name = db_prepare_string( $t_field_name );

		if( count( $t_parameters ) == 0 ) {
			$t_query .= ' SET '. $c_field_name. '=' . db_param();
		} else {
			$t_query .= ' , ' . $c_field_name. '=' . db_param();
		}

		array_push( $t_parameters, $t_field_value );
	}

	$t_query .= ' WHERE id=' . db_param();
	array_push( $t_parameters, (int)$p_user_id );

	db_query( $t_query, $t_parameters );

	user_clear_cache( $p_user_id );
}

/**
 * Set a user field
 *
 * @param integer $p_user_id     A valid user identifier.
 * @param string  $p_field_name  A valid field name to set.
 * @param string  $p_field_value The field value to set.
 * @return boolean always true
 */
function user_set_field( $p_user_id, $p_field_name, $p_field_value ) {
	user_set_fields( $p_user_id, array ( $p_field_name => $p_field_value ) );

	return true;
}

/**
 * Set Users Default project in preferences
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_project_id A valid project identifier.
 * @return void
 */
function user_set_default_project( $p_user_id, $p_project_id ) {
	user_pref_set_pref( $p_user_id, 'default_project', (int)$p_project_id );
}

/**
 * Set the user's password to the given string, encoded as appropriate.
 *
 * @param integer $p_user_id         A valid user identifier.
 * @param string  $p_password        A password to set.
 * @param boolean $p_hash_update     If True then protected accounts will be
 *                                   processed, and the user's sessions will not
 *                                   be expired (defaults to False).
 * @return boolean always true
 */
function user_set_password( $p_user_id, $p_password, $p_hash_update = false ) {
	if( !$p_hash_update ) {
		user_ensure_unprotected( $p_user_id );
	}

	$t_query = 'UPDATE {user} SET password=' . db_param();
	$t_param = array( auth_process_plain_password( $p_password ) );

	if( !$p_hash_update ) {
		# When the password is changed, invalidate the cookie to expire all
		# active sessions, and delete password activation token if there is any.
		token_delete( TOKEN_ACCOUNT_ACTIVATION, $p_user_id );

		$t_query .= ', cookie_string=' . db_param();
		$t_param[] = auth_generate_unique_cookie_string();
	}

	$t_query .= ' WHERE id=' . db_param();
	$t_param[] = (int)$p_user_id;

	db_param_push();
	db_query( $t_query, $t_param );

	return true;
}

/**
 * Set the user's email to the given string after checking that it is a valid email
 * @param integer $p_user_id A valid user identifier.
 * @param string  $p_email   An email address to set.
 * @return boolean
 */
function user_set_email( $p_user_id, $p_email ) {
	$p_email = trim( $p_email );

	email_ensure_valid( $p_email );
	email_ensure_not_disposable( $p_email );

	$t_old_email = user_get_email( $p_user_id );
	if( strcasecmp( $t_old_email, $p_email ) != 0 ) {
		user_ensure_email_unique( $p_email );
	}

	return user_set_field( $p_user_id, 'email', $p_email );
}

/**
 * Set the user's realname to the given string after checking validity
 * @param integer $p_user_id  A valid user identifier.
 * @param string  $p_realname A realname to set.
 * @return boolean
 */
function user_set_realname( $p_user_id, $p_realname ) {
	return user_set_field( $p_user_id, 'realname', $p_realname );
}

/**
 * Set the user's username to the given string after checking that it is valid
 * @param integer $p_user_id  A valid user identifier.
 * @param string  $p_username A valid username to set.
 * @return boolean
 */
function user_set_name( $p_user_id, $p_username ) {
	user_ensure_name_valid( $p_username );
	user_ensure_name_unique( $p_username );

	return user_set_field( $p_user_id, 'username', $p_username );
}

/**
 * Reset the user's password
 *  Take into account the 'send_reset_password' setting
 *   - if it is ON, generate a random password and send an email
 *      (unless the second parameter is false)
 *   - if it is OFF, set the password to blank
 *  Return false if the user is protected, true if the password was
 *   successfully reset
 *
 * @param integer $p_user_id    A valid user identifier.
 * @param boolean $p_send_email Whether to send confirmation email.
 * @return boolean
 */
function user_reset_password( $p_user_id, $p_send_email = true ) {
	$t_protected = user_get_field( $p_user_id, 'protected' );

	# Go with random password and email it to the user
	if( ON == $t_protected ) {
		return false;
	}

	# @@@ do we want to force blank password instead of random if
	#      email notifications are turned off?
	#     How would we indicate that we had done this with a return value?
	#     Should we just have two functions? (user_reset_password_random()
	#     and user_reset_password() )?
	if( ( ON == config_get( 'send_reset_password' ) ) && ( ON == config_get( 'enable_email_notification' ) ) ) {
		$t_email = user_get_field( $p_user_id, 'email' );
		if( is_blank( $t_email ) ) {
			trigger_error( ERROR_LOST_PASSWORD_NO_EMAIL_SPECIFIED, ERROR );
		}

		# Create random password
		$t_password = auth_generate_random_password();
		$t_password2 = auth_process_plain_password( $t_password );

		user_set_field( $p_user_id, 'password', $t_password2 );

		# Send notification email
		if( $p_send_email ) {
			$t_confirm_hash = auth_generate_confirm_hash( $p_user_id );
			token_set( TOKEN_ACCOUNT_ACTIVATION, $t_confirm_hash, TOKEN_EXPIRY_ACCOUNT_ACTIVATION, $p_user_id );
			email_send_confirm_hash_url( $p_user_id, $t_confirm_hash );
		}
	} else {
		# use blank password, no emailing
		$t_password = auth_process_plain_password( '' );
		user_set_field( $p_user_id, 'password', $t_password );

		# reset the failed login count because in this mode there is no emailing
		user_reset_failed_login_count_to_zero( $p_user_id );
	}

	return true;
}
