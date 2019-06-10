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

use Mantis\Exceptions\ClientException;

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

		if( !isset( $g_cache_user[$c_user_id] ) ) {
			if( $p_trigger_errors ) {
				throw new ClientException(
					sprintf( "User id '%d' not found.", (integer)$p_user_id ),
					ERROR_USER_BY_ID_NOT_FOUND,
					array( (integer)$p_user_id )
				);
			}

			return false;
		}
	}

	return $g_cache_user[$c_user_id];
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

	if( $t_result !== false ) {
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
		throw new ClientException( "User $c_user_id not found", ERROR_USER_BY_ID_NOT_FOUND, array( $c_user_id ) );
	}
}

/**
 * return true if the username is unique, false if there is already a user with that username
 * @param string $p_username The username to check.
 * @param integer|null The user id allowed to conflict, otherwise null.
 * @return boolean
 */
function user_is_name_unique( $p_username, $p_user_id = null ) {
	$t_existing_user_id = user_get_id_by_name( $p_username );
	if( $t_existing_user_id !== false && ( $p_user_id === null || (int)$t_existing_user_id !== $p_user_id ) ) {
		return false;
	}

	$t_existing_user_id = user_get_id_by_realname( $p_username );
	if( $t_existing_user_id !== false && ( $p_user_id === null || (int)$t_existing_user_id !== $p_user_id ) ) {
		return false;
	}

	return true;
}

/**
 * Check if the username is unique and trigger an ERROR if it isn't
 * @param string $p_username The username to check.
 * @param integer|null $p_user_id The user id allowed to conflict, otherwise null.
 * @return void
 */
function user_ensure_name_unique( $p_username, $p_user_id = null ) {
	if( !user_is_name_unique( $p_username, $p_user_id ) ) {
		throw new ClientException(
			sprintf( "Username '%s' already used.", $p_username ),
			ERROR_USER_NAME_NOT_UNIQUE );
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
		throw new ClientException(
			sprintf( "Email '%s' already used.", $p_email ),
			ERROR_USER_EMAIL_NOT_UNIQUE );
	}
}

/**
 * Check if the username is a valid username (does not account for uniqueness) realname can match
 * @param string $p_username The username to check.
 * @return boolean return true if user name is valid, false otherwise
 */
function user_is_name_valid( $p_username ) {
	# The DB field is hard-coded. DB_FIELD_SIZE_USERNAME should not be modified.
	if( mb_strlen( $p_username ) > DB_FIELD_SIZE_USERNAME ) {
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
		throw new ClientException(
			sprintf( "Invalid username '%s'", $p_username ),
			ERROR_USER_NAME_INVALID );
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
	return auth_anonymous_enabled() && strcasecmp( user_get_username( $p_user_id ), auth_anonymous_account() ) == 0;
}

/**
 * Trigger an ERROR if the user account is protected
 *
 * @param integer $p_user_id A valid user identifier.
 * @return void
 */
function user_ensure_unprotected( $p_user_id ) {
	if( user_is_protected( $p_user_id ) ) {
		throw new ClientException(
			'User protected.',
			ERROR_PROTECTED_ACCOUNT );
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
		$t_users_connected[] = (int)$t_row['id'];
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
	email_ensure_valid( $p_email );
	email_ensure_not_disposable( $p_email );

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
 * ldap. $p_email may be empty, but the user won't get any emails.
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
	user_pref_db_delete_user( $p_user_id );

	# Remove project specific access levels
	user_delete_project_specific_access_levels( $p_user_id );
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
 * @param boolean $p_throw true to throw if not found, false otherwise.
 * @return integer|boolean
 */
function user_get_id_by_name( $p_username, $p_throw = false ) {
	if( $t_user = user_search_cache( 'username', $p_username ) ) {
		return (int)$t_user['id'];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {user} WHERE username=' . db_param();
	$t_result = db_query( $t_query, array( $p_username ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		user_cache_database_result( $t_row );
		return (int)$t_row['id'];
	}

	if( $p_throw ) {
		throw new ClientException(
			"Username '$p_username' not found",
			ERROR_USER_BY_NAME_NOT_FOUND,
			array( $p_username ) );
	}

	return false;
}

/**
 * Get a user id from their email address
 *
 * @param string $p_email The email address to retrieve data for.
 * @param boolean $p_throw true to throw exception when not found, false otherwise.
 * @return array
 */
function user_get_id_by_email( $p_email, $p_throw = false ) {
	if( $t_user = user_search_cache( 'email', $p_email ) ) {
		return (int)$t_user['id'];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {user} WHERE email=' . db_param();
	$t_result = db_query( $t_query, array( $p_email ) );

	$t_row = db_fetch_array( $t_result );
	if( $t_row ) {
		user_cache_database_result( $t_row );
		return (int)$t_row['id'];
	}

	if( $p_throw ) {
		throw new ClientException(
			"User with email '$p_email' not found",
			ERROR_USER_BY_EMAIL_NOT_FOUND,
			array( $p_email ) );
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
 * @param boolean $p_throw true to throw if not found, false otherwise.
 * @return array
 */
function user_get_id_by_realname( $p_realname, $p_throw = false ) {
	if( $t_user = user_search_cache( 'realname', $p_realname ) ) {
		return (int)$t_user['id'];
	}

	db_param_push();
	$t_query = 'SELECT * FROM {user} WHERE realname=' . db_param();
	$t_result = db_query( $t_query, array( $p_realname ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		if( $p_throw ) {
			throw new ClientException( "User realname '$p_realname' not found", ERROR_USER_BY_NAME_NOT_FOUND, array( $p_realname ) );
		}

		return false;
	}

	user_cache_database_result( $t_row );
	return (int)$t_row['id'];
}

/**
 * Get a user id given an array that may have id, name, real_name, email, or name_or_realname.
 *
 * @param array $p_user The user info.
 * @param boolean $p_throw_if_id_not_found If id specified and doesn't exist, then throw.
 * @return integer user id
 * @throws ClientException
 */
function user_get_id_by_user_info( array $p_user, $p_throw_if_id_not_found = false ) {
	if( isset( $p_user['id'] ) && (int)$p_user['id'] != 0 ) {
		$t_user_id = (int)$p_user['id'];
		if( $p_throw_if_id_not_found && !user_exists( $t_user_id ) ) {
			throw new ClientException(
				sprintf( "User with id '%d' doesn't exist", $t_user_id ),
				ERROR_USER_BY_ID_NOT_FOUND,
				array( $t_user_id ) );
		}
	} else if( isset( $p_user['name'] ) && !is_blank( $p_user['name'] ) ) {
		$t_user_id = user_get_id_by_name( $p_user['name'], /* throw */ true );
	} else if( isset( $p_user['email'] ) && !is_blank( $p_user['email'] ) ) {
		$t_user_id = user_get_id_by_email( $p_user['email'], /* throw */ true );
	} else if( isset( $p_user['real_name'] ) && !is_blank( $p_user['real_name'] ) ) {
		$t_user_id = user_get_id_by_realname( $p_user['real_name'], /* throw */ true );
	} else if( isset( $p_user['name_or_realname' ] ) && !is_blank( $p_user['name_or_realname' ] ) ) {
		$t_identifier = $p_user['name_or_realname'];
		$t_user_id = user_get_id_by_name( $t_identifier );

		if( !$t_user_id ) {
			$t_user_id = user_get_id_by_realname( $t_identifier );
		}

		if( !$t_user_id ) {
			throw new ClientException(
				"User '$t_identifier' not found",
				ERROR_USER_BY_NAME_NOT_FOUND,
				array( $t_identifier ) );
		}
	} else {
		throw new ClientException(
			"User id missing",
			ERROR_GPC_VAR_NOT_FOUND,
			array( 'user id' ) );
	}

	return $t_user_id;
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
	if( LDAP == config_get_global( 'login_method' ) && ON == config_get( 'use_ldap_email' ) ) {
		$t_email = ldap_email( $p_user_id );
	}
	if( is_blank( $t_email ) ) {
		$t_email = user_get_field( $p_user_id, 'email' );
	}
	return $t_email;
}

/**
 * Lookup the user's login name (username)
 *
 * @param integer $p_user_id A valid user identifier.
 * @return string
 */
function user_get_username( $p_user_id ) {
	$t_row = user_cache_row( $p_user_id, false );
	if( false == $t_row ) {
		return lang_get( 'prefix_for_deleted_users' ) . (int)$p_user_id;
	}

	return $t_row['username'];
}

/**
 * lookup the user's realname
 *
 * @param integer $p_user_id A valid user identifier.
 * @return string
 */
function user_get_realname( $p_user_id ) {
	$t_realname = '';

	if( LDAP == config_get_global( 'login_method' ) && ON == config_get( 'use_ldap_realname' ) ) {
		$t_realname = ldap_realname( $p_user_id );
	}

	if( is_blank( $t_realname ) ) {
		$t_realname = user_get_field( $p_user_id, 'realname' );
	}

	return $t_realname;
}

/**
 * Return the user's name for display.
 *
 * The name is determined based on the following sequence:
 * - if the user does not exist, returns the user ID prefixed by a localized
 *   string (prefix_for_deleted_users, "user" by default);
 * - if user_show_realname() is true and realname is not empty, return the user's Real Name;
 * - Otherwise, return the username
 *
 * NOTE: do not use this function to retrieve the user's username
 * @see user_get_username()
 *
 * @param integer $p_user_id A valid user identifier.
 *
 * @return string
 */
function user_get_name( $p_user_id ) {
	$t_row = user_cache_row( $p_user_id, false );

	if( false == $t_row ) {
		return lang_get( 'prefix_for_deleted_users' ) . (int)$p_user_id;
	}

	return user_get_name_from_row( $t_row );
}

/**
 * Should realnames be shown to logged in user?
 *
 * @return bool true to show, false otherwise.
 */
function user_show_realname() {
	return config_get( 'show_realname' ) == ON;
}

/**
 * Return the user's name for display.  If user_show_realname() is true and realname is not empty
 * return realname otherwise return username.
 *
 * @param array $p_user_row The user row with 'realname' and 'username' fields
 * @return string display name
 */
function user_get_name_from_row( array $p_user_row ) {
	if( user_show_realname() ) {
		if( !is_blank( $p_user_row['realname'] ) ) {
			return $p_user_row['realname'];
		}
	}

	return $p_user_row['username'];
}

/**
 * Return display name in format "realname (username)" if user_show_realname() is true and
 * realname is not empty otherwise return username
 *
 * @param array $p_user_row The user row with 'realname' and 'username' fields
 * @return string display name
 */
function user_get_expanded_name_from_row( array $p_user_row ) {
	$t_name = user_get_name_from_row( $p_user_row );
	if( $t_name != $p_user_row['username'] ) {
		return $t_name . ' (' . $p_user_row['username'] . ')';
	}

	return $p_user_row['username'];
}

/**
 * Get name used for sorting.
 * 
 * @param array $p_user_row The user row with 'realname' and 'username' fields
 * @return string name for sorting
 */
function user_get_name_for_sorting_from_row( array $p_user_row ) {
	if( !is_blank( $p_user_row['realname'] ) ) {
		if( user_show_realname() ) {
			if( config_get( 'sort_by_last_name' ) == ON ) {
				$t_sort_name_bits = explode( ' ', mb_strtolower( trim( $p_user_row['realname'] ) ), 2 );
				return ( isset( $t_sort_name_bits[1] ) ? $t_sort_name_bits[1] . ', ' : '' ) . $t_sort_name_bits[0];
			}

			return mb_strtolower( trim( $p_user_row['realname'] ) );
		}
	}

	return mb_strtolower( $p_user_row['username'] );
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

	$t_project_access_level = access_get_local_level( $p_user_id, $p_project_id );

	if( false === $t_project_access_level ) {
		return $t_access_level;
	} else {
		return $t_project_access_level;
	}
}

$g_user_accessible_projects_cache = null;

/**
 * return an array of project IDs to which the user has access
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
 * return an array of sub-project IDs of all sub-projects project to which the user has access
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
		access_ensure_project_level( config_get( 'view_bug_threshold' ), $p_project_id );
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

	while( $t_row = db_fetch_array( $t_result ) ) {
		$t_users[] = (int)$t_row['id'];
		$t_display[] = user_get_expanded_name_from_row( $t_row );
		$t_sort[] = user_get_name_for_sorting_from_row( $t_row );
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

	# Currently we use the filters saved in db as "current" special filters,
	# to track the active settings for filters in use.

	# for anonymous user, we don't allow using persistent filter
	# if this function is reached, we return a default filter for it.
	if( user_is_anonymous( $p_user_id ) ) {
		return filter_get_default();
	}

	$t_filter_id = filter_db_get_project_current( $t_project_id, $p_user_id );
	if( $t_filter_id ) {
		return filter_get( $t_filter_id );
	} else {
		return filter_get_default();
	}
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
 * Set the user's password to the given string, encoded as appropriate
 *
 * @param integer $p_user_id         A valid user identifier.
 * @param string  $p_password        A password to set.
 * @param boolean $p_allow_protected Whether Allow password change to a protected account. This defaults to false.
 * @return boolean always true
 */
function user_set_password( $p_user_id, $p_password, $p_allow_protected = false ) {
	if( !$p_allow_protected ) {
		user_ensure_unprotected( $p_user_id );
	}

	# When the password is changed, invalidate the cookie to expire sessions that
	# may be active on all browsers.
	$c_cookie_string = auth_generate_unique_cookie_string();
	# Delete token for password activation if there is any
	token_delete( TOKEN_ACCOUNT_ACTIVATION, $p_user_id );

	$c_password = auth_process_plain_password( $p_password );

	db_param_push();
	$t_query = 'UPDATE {user}
				  SET password=' . db_param() . ', cookie_string=' . db_param() . '
				  WHERE id=' . db_param();
	db_query( $t_query, array( $c_password, $c_cookie_string, (int)$p_user_id ) );

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
	user_ensure_name_unique( $p_username, $p_user_id );

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

/**
 * Helper function to check if the user has access to more than one project
 * (any kind of project or subproject). This can be used to simplify logic when
 * the user only has one project to choose from.
 *
 * @param integer $p_user_id	A valid user identifier.
 * @return boolean	True if the user has access to more than one project.
 */
function user_has_more_than_one_project( $p_user_id ) {
	$t_project_ids = user_get_accessible_projects( $p_user_id );
	$t_count = count( $t_project_ids );
	if( 0 == $t_count ) {
		return false;
	}
	if( 1 == $t_count ) {
		$t_project_id = (int) $t_project_ids[0];
		if( count( user_get_accessible_subprojects( $p_user_id, $t_project_id ) ) == 0 ) {
			return false;
		}
	}
	return true;
}
