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
 * User API
 * @package CoreAPI
 * @subpackage UserAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * requires email_api
 */
require_once( 'email_api.php' );
/**
 * requires ldap_api
 */
require_once( 'ldap_api.php' );

# ===================================
# Caching
# ===================================
# ########################################
# SECURITY NOTE: cache globals are initialized here to prevent them
#   being spoofed if register_globals is turned on

$g_cache_user = array();

# --------------------
# Cache a user row if necessary and return the cached copy
#  If the second parameter is true (default), trigger an error
#  if the user can't be found.  If the second parameter is
#  false, return false if the user can't be found.
function user_cache_row( $p_user_id, $p_trigger_errors = true ) {
	global $g_cache_user;

	if( isset( $g_cache_user[$p_user_id] ) ) {
		return $g_cache_user[$p_user_id];
	}

	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT *
				  FROM $t_user_table
				  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $p_user_id ) );

	if( 0 == db_num_rows( $result ) ) {
		$g_cache_user[$p_user_id] = false;

		if( $p_trigger_errors ) {
			error_parameters( (integer)$p_user_id );
			trigger_error( ERROR_USER_BY_ID_NOT_FOUND, ERROR );
		}

		return false;
	}

	$row = db_fetch_array( $result );

	$g_cache_user[$p_user_id] = $row;

	return $row;
}

function user_cache_array_rows( $p_user_id_array ) {
	global $g_cache_user;
	$c_user_id_array = array();

	foreach( $p_user_id_array as $t_user_id ) {
		if( !isset( $g_cache_user[(int) $t_user_id] ) ) {
			$c_user_id_array[] = (int) $t_user_id;
		}
	}

	if( empty( $c_user_id_array ) ) {
		return;
	}

	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT *
				  FROM $t_user_table
				  WHERE id IN (" . implode( ',', $c_user_id_array ) . ')';
	$result = db_query_bound( $query );

	while( $row = db_fetch_array( $result ) ) {
		$g_cache_user[(int) $row['id']] = $row;
	}
	return;
}

# --------------------
# Cache an object as a bug.
function user_cache_database_result( $p_user_database_result ) {
	global $g_cache_user;

	if( isset( $g_cache_user[$p_user_database_result['id']] ) ) {
		return $g_cache_user[$p_user_database_result['id']];
	}

	$g_cache_user[$p_user_database_result['id']] = $p_user_database_result;
}

# --------------------
# Clear the user cache (or just the given id if specified)
function user_clear_cache( $p_user_id = null ) {
	global $g_cache_user;

	if( null === $p_user_id ) {
		$g_cache_user = array();
	} else {
		unset( $g_cache_user[$p_user_id] );
	}

	return true;
}

function user_update_cache( $p_user_id, $p_field, $p_value ) {
	global $g_cache_user;

	if( isset( $g_cache_user[$p_user_id] ) && isset( $g_cache_user[$p_user_id][$p_field] ) ) {
		$g_cache_user[$p_user_id][$p_field] = $p_value;
	} else {
		user_clear_cache( $p_user_id );
	}
}

function user_search_cache( $p_field, $p_value ) {
	global $g_cache_user;
	if( isset( $g_cache_user ) ) {
		foreach( $g_cache_user as $t_user ) {
			if( $t_user[$p_field] == $p_value ) {
				return $t_user;
			}
		}
	}
	return false;
}

# ===================================
# Boolean queries and ensures
# ===================================
# --------------------
# check to see if user exists by id
# return true if it does, false otherwise
#
# Use user_cache_row() to benefit from caching if called multiple times
#  and because if the user does exist the data may well be wanted
function user_exists( $p_user_id ) {
	$row = user_cache_row( $p_user_id, false );

	if( false === $row ) {
		return false;
	} else {
		return true;
	}
}

# --------------------
# check to see if project exists by id
# if it doesn't exist then error
#  otherwise let execution continue undisturbed
function user_ensure_exists( $p_user_id ) {
	$c_user_id = (integer)$p_user_id;

	if ( !user_exists( $c_user_id ) ) {
		error_parameters( $c_user_id );
		trigger_error( ERROR_USER_BY_ID_NOT_FOUND, ERROR );
	}
}

# --------------------
# return true if the username is unique, false if there is already a user
#  with that username
function user_is_name_unique( $p_username ) {
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT username
				FROM $t_user_table
				WHERE username=" . db_param();
	$result = db_query_bound( $query, Array( $p_username ), 1 );

	if( db_num_rows( $result ) > 0 ) {
		return false;
	} else {
		return true;
	}
}

# --------------------
# Check if the username is unique and trigger an ERROR if it isn't
function user_ensure_name_unique( $p_username ) {
	if( !user_is_name_unique( $p_username ) ) {
		trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
	}
}

# --------------------
# Check if the realname is a valid username (does not account for uniqueness)
# Return 0 if it is invalid, The number of matches + 1
function user_is_realname_unique( $p_username, $p_realname ) {
	if( is_blank( $p_realname ) ) {
		# don't bother checking if realname is blank
		return 1;
	}

	$p_username = trim( $p_username );
	$p_realname = trim( $p_realname );

	# allow realname to match username
	$t_count = 0;
	if( $p_realname <> $p_username ) {
		# check realname does not match an existing username
		#  but allow it to match the current user
		$t_target_user = user_get_id_by_name( $p_username );
		$t_other_user = user_get_id_by_name( $p_realname );
		if( ( 0 != $t_other_user ) && ( $t_target_user != $t_other_user ) ) {
			return 0;
		}

		# check to see if the realname is unique
		$t_user_table = db_get_table( 'mantis_user_table' );
		$query = "SELECT id
				FROM $t_user_table
				WHERE realname=" . db_param();
		$result = db_query_bound( $query, Array( $p_realname ) );
		$t_count = db_num_rows( $result );

		if( $t_count > 0 ) {
			# set flags for non-unique realnames
			if( config_get( 'differentiate_duplicates' ) ) {
				for( $i = 0;$i < $t_count;$i++ ) {
					$t_user_id = db_result( $result, $i );
					user_set_field( $t_user_id, 'duplicate_realname', ON );
				}
			}
		}
	}
	return $t_count + 1;
}

# --------------------
# Check if the realname is a unique
# Trigger an error if the username is not valid
function user_ensure_realname_unique( $p_username, $p_realname ) {
	if( 1 > user_is_realname_unique( $p_username, $p_realname ) ) {
		trigger_error( ERROR_USER_REAL_MATCH_USER, ERROR );
	}
}

# --------------------
# Check if the realname is a valid (does not account for uniqueness)
# true: valid, false: not valid
function user_is_realname_valid( $p_realname ) {
	return( !string_contains_scripting_chars( $p_realname ) );
}

# --------------------
# Check if the realname is a valid (does not account for uniqueness), if not, trigger an error
function user_ensure_realname_valid( $p_realname ) {
	if( !user_is_realname_valid( $p_realname ) ) {
		trigger_error( ERROR_USER_REAL_NAME_INVALID, ERROR );
	}
}

# --------------------
# Check if the username is a valid username (does not account for uniqueness)
#  realname can match
# Return true if it is, false otherwise
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

# --------------------
# Check if the username is a valid username (does not account for uniqueness)
# Trigger an error if the username is not valid
function user_ensure_name_valid( $p_username ) {
	if( !user_is_name_valid( $p_username ) ) {
		trigger_error( ERROR_USER_NAME_INVALID, ERROR );
	}
}

# --------------------
# return whether user is monitoring bug for the user id and bug id
function user_is_monitoring_bug( $p_user_id, $p_bug_id ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_bug_id = db_prepare_int( $p_bug_id );

	$t_bug_monitor_table = db_get_table( 'mantis_bug_monitor_table' );

	$query = "SELECT COUNT(*)
				  FROM $t_bug_monitor_table
				  WHERE user_id=" . db_param() . " AND bug_id=" . db_param();

	$result = db_query_bound( $query, Array( $c_user_id, $c_bug_id ) );

	if( 0 == db_result( $result ) ) {
		return false;
	} else {
		return true;
	}
}

# --------------------
# return true if the user has access of ADMINISTRATOR or higher, false otherwise
function user_is_administrator( $p_user_id ) {
	$t_access_level = user_get_field( $p_user_id, 'access_level' );

	if( $t_access_level >= config_get_global( 'admin_site_threshold' ) ) {
		return true;
	} else {
		return false;
	}
}

/*
 * Check if a user has a protected user account.
 * Protected user accounts cannot be updated without manage_user_threshold
 * permission. If the user ID supplied is that of the anonymous user, this
 * function will always return true. The anonymous user account is always
 * considered to be protected.
 *
 * @param int $p_user_id
 * @return true: user is protected; false: user is not protected.
 * @access public
 */
function user_is_protected( $p_user_id ) {
	if( user_is_anonymous( $p_user_id ) || ON == user_get_field( $p_user_id, 'protected' ) ) {
		return true;
	}
	return false;
}

/*
 * Check if a user is the anonymous user account.
 * When anonymous logins are disabled this function will always return false.
 *
 * @param int $p_user_id
 * @return true: user is the anonymous user; false: user is not the anonymous user.
 * @access public
 */
function user_is_anonymous( $p_user_id ) {
	if( ON == config_get( 'allow_anonymous_login' ) && user_get_field( $p_user_id, 'username' ) == config_get( 'anonymous_account' ) ) {
		return true;
	}
	return false;
}

# --------------------
# Trigger an ERROR if the user account is protected
function user_ensure_unprotected( $p_user_id ) {
	if( user_is_protected( $p_user_id ) ) {
		trigger_error( ERROR_PROTECTED_ACCOUNT, ERROR );
	}
}

# --------------------
# return true is the user account is enabled, false otherwise
function user_is_enabled( $p_user_id ) {
	if( ON == user_get_field( $p_user_id, 'enabled' ) ) {
		return true;
	} else {
		return false;
	}
}

# --------------------
# count the number of users at or greater than a specific level
function user_count_level( $p_level = ANYBODY ) {
	$t_level = db_prepare_int( $p_level );
	$t_user_table = db_get_table( 'mantis_user_table' );
	$query = "SELECT COUNT(id) FROM $t_user_table WHERE access_level>=" . db_param();
	$result = db_query_bound( $query, Array( $t_level ) );

	# Get the list of connected users
	$t_users = db_result( $result );

	return $t_users;
}

# --------------------
# Return an array of user ids that are logged in.
# A user is considered logged in if the last visit timestamp is within the
# specified session duration.
# If the session duration is 0, then no users will be returned.
function user_get_logged_in_user_ids( $p_session_duration_in_minutes ) {
	$t_session_duration_in_minutes = (integer) $p_session_duration_in_minutes;

	# if session duration is 0, then there is no logged in users.
	if( $t_session_duration_in_minutes == 0 ) {
		return array();
	}

	# Generate timestamp
	$t_last_timestamp_threshold = mktime( date( 'H' ), date( 'i' ) - 1 * $t_session_duration_in_minutes, date( 's' ), date( 'm' ), date( 'd' ), date( 'Y' ) );

	$t_user_table = db_get_table( 'mantis_user_table' );

	# Execute query
	$query = 'SELECT id FROM ' . $t_user_table . ' WHERE last_visit > ' . db_param();
	$result = db_query_bound( $query, array( $c_last_timestamp_threshold ), 1 );

	# Get the list of connected users
	$t_users_connected = array();
	while( $row = db_fetch_array( $result ) ) {
		$t_users_connected[] = $row['id'];
	}

	return $t_users_connected;
}

# ===================================
# Creation / Deletion / Updating
# ===================================
# --------------------
# Create a user.
# returns false if error, the generated cookie string if ok
function user_create( $p_username, $p_password, $p_email = '',
	$p_access_level = null, $p_protected = false, $p_enabled = true,
	$p_realname = '', $p_admin_name = '' ) {
	if( null === $p_access_level ) {
		$p_access_level = config_get( 'default_new_account_access_level' );
	}

	$t_password = auth_process_plain_password( $p_password );

	$c_access_level = db_prepare_int( $p_access_level );
	$c_protected = db_prepare_bool( $p_protected );
	$c_enabled = db_prepare_bool( $p_enabled );

	user_ensure_name_valid( $p_username );
	user_ensure_name_unique( $p_username );
	user_ensure_realname_valid( $p_realname );
	user_ensure_realname_unique( $p_username, $p_realname );
	email_ensure_valid( $p_email );

	$t_seed = $p_email . $p_username;
	$t_cookie_string = auth_generate_unique_cookie_string( $t_seed );
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "INSERT INTO $t_user_table
				    ( username, email, password, date_created, last_visit,
				     enabled, access_level, login_count, cookie_string, realname )
				  VALUES
				    ( " . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param()  . ",
				     " . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ', ' . db_param() . ')';
	db_query_bound( $query, Array( $p_username, $p_email, $t_password, db_now(), db_now(), $c_enabled, $c_access_level, 0, $t_cookie_string, $p_realname ) );

	# Create preferences for the user
	$t_user_id = db_insert_id( $t_user_table );

	# Users are added with protected set to FALSE in order to be able to update
	# preferences.  Now set the real value of protected.
	if( $c_protected ) {
		user_set_field( $t_user_id, 'protected', 1 );
	}

	# Send notification email
	if( !is_blank( $p_email ) ) {
		$t_confirm_hash = auth_generate_confirm_hash( $t_user_id );
		email_signup( $t_user_id, $p_password, $t_confirm_hash, $p_admin_name );
	}

	return $t_cookie_string;
}

# --------------------
# Signup a user.
# If the use_ldap_email config option is on then tries to find email using
# ldap. $p_email may be empty, but the user wont get any emails.
# returns false if error, the generated cookie string if ok
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
		/*			$t_email = '';
					if ( ON == config_get( 'use_ldap_email' ) ) {
						$t_email = ldap_email_from_username( $p_username );
					}

					if ( !is_blank( $t_email ) ) {
						$p_email = $t_email;
					}
		*/
	}

	$p_email = trim( $p_email );

	$t_seed = $p_email . $p_username;

	# Create random password
	$t_password = auth_generate_random_password( $t_seed );

	return user_create( $p_username, $t_password, $p_email );
}

# --------------------
# delete project-specific user access levels.
# returns true when successfully deleted
function user_delete_project_specific_access_levels( $p_user_id ) {
	$c_user_id = db_prepare_int( $p_user_id );

	user_ensure_unprotected( $p_user_id );

	$t_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );

	$query = "DELETE FROM $t_project_user_list_table
				  WHERE user_id=" . db_param();
	db_query_bound( $query, Array( $c_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

# --------------------
# delete profiles for the specified user
# returns true when successfully deleted
function user_delete_profiles( $p_user_id ) {
	$c_user_id = db_prepare_int( $p_user_id );

	user_ensure_unprotected( $p_user_id );

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	# Remove associated profiles
	$query = "DELETE FROM $t_user_profile_table
				  WHERE user_id=" . db_param();
	db_query_bound( $query, Array( $c_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

# --------------------
# delete a user account (account, profiles, preferences, project-specific access levels)
# returns true when the account was successfully deleted
function user_delete( $p_user_id ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$t_user_table = db_get_table( 'mantis_user_table' );

	user_ensure_unprotected( $p_user_id );

	# Remove associated profiles
	user_delete_profiles( $p_user_id );

	# Remove associated preferences
	user_pref_delete_all( $p_user_id );

	# Remove project specific access levels
	user_delete_project_specific_access_levels( $p_user_id );

	# unset non-unique realname flags if necessary
	if( config_get( 'differentiate_duplicates' ) ) {
		$c_realname = user_get_field( $p_user_id, 'realname' );
		$query = "SELECT id
					FROM $t_user_table
					WHERE realname=" . db_param();
		$result = db_query_bound( $query, Array( $c_realname ) );
		$t_count = db_num_rows( $result );

		if( $t_count == 2 ) {

			# unset flags if there are now only 2 unique names
			for( $i = 0;$i < $t_count;$i++ ) {
				$t_user_id = db_result( $result, $i );
				user_set_field( $t_user_id, 'duplicate_realname', OFF );
			}
		}
	}

	user_clear_cache( $p_user_id );

	# Remove account
	$query = "DELETE FROM $t_user_table
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_user_id ) );

	return true;
}

# ===================================
# Data Access
# ===================================
# --------------------
# get a user id from a username
#  return false if the username does not exist
function user_get_id_by_name( $p_username ) {
	global $g_cache_user;
	if( $t_user = user_search_cache( 'username', $p_username ) ) {
		return $t_user['id'];
	}

	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT *
				  FROM $t_user_table
				  WHERE username=" . db_param();
	$result = db_query_bound( $query, Array( $p_username ) );

	if( 0 == db_num_rows( $result ) ) {
		return false;
	} else {
		$row = db_fetch_array( $result );
		user_cache_database_result( $row );
		return $row['id'];
	}
}

# Get a user id from an email address
function user_get_id_by_email( $p_email ) {
	global $g_cache_user;
	if( $t_user = user_search_cache( 'email', $p_email ) ) {
		return $t_user['id'];
	}

	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT *
				  FROM $t_user_table
				  WHERE email=" . db_param();
	$result = db_query_bound( $query, Array( $p_email ) );

	if( 0 == db_num_rows( $result ) ) {
		return false;
	} else {
		$row = db_fetch_array( $result );
		user_cache_database_result( $row );
		return $row['id'];
	}
}

# Get a user id from their real name
function user_get_id_by_realname( $p_realname ) {
	global $g_cache_user;
	if( $t_user = user_search_cache( 'realname', $p_realname ) ) {
		return $t_user['id'];
	}

	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "SELECT *
				  FROM $t_user_table
				  WHERE realname=" . db_param();
	$result = db_query_bound( $query, Array( $p_realname ) );

	if( 0 == db_num_rows( $result ) ) {
		return false;
	} else {
		$row = db_fetch_array( $result );
		user_cache_database_result( $row );
		return $row['id'];
	}
}

# --------------------
# return all data associated with a particular user name
#  return false if the username does not exist
function user_get_row_by_name( $p_username ) {
	$t_user_id = user_get_id_by_name( $p_username );

	if( false === $t_user_id ) {
		return false;
	}

	$row = user_get_row( $t_user_id );

	return $row;
}

# --------------------
# return a user row
function user_get_row( $p_user_id ) {
	return user_cache_row( $p_user_id );
}

# --------------------
# return the specified user field for the user id
function user_get_field( $p_user_id, $p_field_name ) {
	if( NO_USER == $p_user_id ) {
		trigger_error( 'user_get_field() for NO_USER', WARNING );
		return '@null@';
	}

	$row = user_get_row( $p_user_id );

	if( isset( $row[$p_field_name] ) ) {
		return $row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

# --------------------
# lookup the user's email in LDAP or the db as appropriate
function user_get_email( $p_user_id ) {
	$t_email = '';
	if( ON == config_get( 'use_ldap_email' ) ) {
		$t_email = ldap_email( $p_user_id );
	}
	if( is_blank( $t_email ) ) {
		$t_email = user_get_field( $p_user_id, 'email' );
	}
	return $t_email;
}

# --------------------
# lookup the user's realname
function user_get_realname( $p_user_id ) {
	$t_realname = '';

	if ( ON == config_get( 'use_ldap_realname' ) ) {
		$t_realname = ldap_realname( $p_user_id );
	}

	if ( is_blank( $t_realname ) ) {
		$t_realname = user_get_field( $p_user_id, 'realname' );
	}

	return $t_realname;
}

# --------------------
# return the username or a string "user<id>" if the user does not exist
# if $g_show_realname is set and real name is not empty, return it instead
function user_get_name( $p_user_id ) {
	$row = user_cache_row( $p_user_id, false );

	if( false == $row ) {
		return lang_get( 'prefix_for_deleted_users' ) . (int) $p_user_id;
	} else {
		if( ON == config_get( 'show_realname' ) ) {
			if( is_blank( $row['realname'] ) ) {
				return $row['username'];
			} else {
				if( isset( $row['duplicate_realname'] ) && ( ON == $row['duplicate_realname'] ) ) {
					return $row['realname'] . ' (' . $row['username'] . ')';
				} else {
					return $row['realname'];
				}
			}
		} else {
			return $row['username'];
		}
	}
}

/**
* Return the user avatar image URL
* in this first implementation, only gravatar.com avatars are supported
* @return array|bool an array( URL, width, height ) or false when the given user has no avatar
*/
function user_get_avatar( $p_user_id, $p_size = 80 ) {
	$t_email = utf8_strtolower( trim( user_get_email( $p_user_id ) ) );
	if( is_blank( $t_email ) ) {
		$t_result = false;
	} else {
		$t_size = $p_size;

		$t_use_ssl = false;
		if( isset( $_SERVER['HTTPS'] ) && ( utf8_strtolower( $_SERVER['HTTPS'] ) != 'off' ) ) {
			$t_use_ssl = true;
		}

		if( !$t_use_ssl ) {
			$t_gravatar_domain = 'http://www.gravatar.com/';
		} else {
			$t_gravatar_domain = 'https://secure.gravatar.com/';
		}

		$t_avatar_url = $t_gravatar_domain . 'avatar/' . md5( $t_email ) . '?d=identicon&r=G&s=' . $t_size;

		$t_result = array(
			$t_avatar_url,
			$t_size,
			$t_size,
		);
	}

	return $t_result;
}

# --------------------
# return the user's access level
#  account for private project and the project user lists
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

# --------------------
# retun an array of project IDs to which the user has access
function user_get_accessible_projects( $p_user_id, $p_show_disabled = false ) {
	global $g_user_accessible_projects_cache;

	if( null !== $g_user_accessible_projects_cache && auth_get_current_user_id() == $p_user_id && false == $p_show_disabled ) {
		return $g_user_accessible_projects_cache;
	}

	if( access_has_global_level( config_get( 'private_project_threshold' ), $p_user_id ) ) {
		$t_projects = project_hierarchy_get_subprojects( ALL_PROJECTS, $p_show_disabled );
	} else {
		$t_project_table = db_get_table( 'mantis_project_table' );
		$t_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
		$t_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );

		$t_public = VS_PUBLIC;
		$t_private = VS_PRIVATE;

		$result = null;

		$query = "SELECT p.id, p.name, ph.parent_id
						  FROM $t_project_table p
						  LEFT JOIN $t_project_user_list_table u
						    ON p.id=u.project_id AND u.user_id=" . db_param() . "
						  LEFT JOIN $t_project_hierarchy_table ph
						    ON ph.child_id = p.id
						  WHERE " . ( $p_show_disabled ? '' : ( 'p.enabled = ' . db_param() . ' AND ' ) ) . "
							( p.view_state=" . db_param() . "
							    OR (p.view_state=" . db_param() . "
								    AND
							        u.user_id=" . db_param() . " )
							)
			  ORDER BY p.name";
		$result = db_query_bound( $query, ( $p_show_disabled ? Array( $p_user_id, $t_public, $t_private, $p_user_id ) : Array( $p_user_id, true, $t_public, $t_private, $p_user_id ) ) );

		$row_count = db_num_rows( $result );

		$t_projects = array();

		for( $i = 0;$i < $row_count;$i++ ) {
			$row = db_fetch_array( $result );

			$t_projects[(int)$row['id']] = ( $row['parent_id'] === NULL ) ? 0 : (int)$row['parent_id'];
		}

		# prune out children where the parents are already listed. Make the list
		#  first, then prune to avoid pruning a parent before the child is found.
		$t_prune = array();
		foreach( $t_projects as $t_id => $t_parent ) {
			if(( $t_parent !== 0 ) && isset( $t_projects[$t_parent] ) ) {
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

$g_user_accessible_subprojects_cache = null;

# --------------------
# retun an array of subproject IDs of a certain project to which the user has access
function user_get_accessible_subprojects( $p_user_id, $p_project_id, $p_show_disabled = false ) {
	global $g_user_accessible_subprojects_cache;

	if( null !== $g_user_accessible_subprojects_cache && auth_get_current_user_id() == $p_user_id && false == $p_show_disabled ) {
		if( isset( $g_user_accessible_subprojects_cache[$p_project_id] ) ) {
			return $g_user_accessible_subprojects_cache[$p_project_id];
		} else {
			return Array();
		}
	}

	$t_project_table = db_get_table( 'mantis_project_table' );
	$t_project_user_list_table = db_get_table( 'mantis_project_user_list_table' );
	$t_project_hierarchy_table = db_get_table( 'mantis_project_hierarchy_table' );

	$t_public = VS_PUBLIC;
	$t_private = VS_PRIVATE;

	if( access_has_global_level( config_get( 'private_project_threshold' ), $p_user_id ) ) {
		$t_enabled_clause = $p_show_disabled ? '' : 'p.enabled = ' . db_param() . ' AND';
		$query = "SELECT DISTINCT p.id, p.name, ph.parent_id
					  FROM $t_project_table p
					  LEFT JOIN $t_project_hierarchy_table ph
					    ON ph.child_id = p.id
					  WHERE $t_enabled_clause
					  	 ph.parent_id IS NOT NULL
					  ORDER BY p.name";
		$result = db_query_bound( $query, ( $p_show_disabled ? null : Array( true ) ) );
	} else {
		$query = "SELECT DISTINCT p.id, p.name, ph.parent_id
					  FROM $t_project_table p
					  LEFT JOIN $t_project_user_list_table u
					    ON p.id = u.project_id AND u.user_id=" . db_param() . "
					  LEFT JOIN $t_project_hierarchy_table ph
					    ON ph.child_id = p.id
					  WHERE " . ( $p_show_disabled ? '' : ( 'p.enabled = ' . db_param() . ' AND ' ) ) . '
					  	ph.parent_id IS NOT NULL AND
						( p.view_state=' . db_param() . '
						    OR (p.view_state=' . db_param() . '
							    AND
						        u.user_id=' . db_param() . ' )
						)
					  ORDER BY p.name';
		$result = db_query_bound( $query, ( $p_show_disabled ? Array( $p_user_id, $t_public, $t_private, $p_user_id ) : Array( $p_user_id, 1, $t_public, $t_private, $p_user_id ) ) );
	}

	$row_count = db_num_rows( $result );

	$t_projects = array();

	for( $i = 0;$i < $row_count;$i++ ) {
		$row = db_fetch_array( $result );

		if( !isset( $t_projects[(int)$row['parent_id']] ) ) {
			$t_projects[(int)$row['parent_id']] = array();
		}

		array_push( $t_projects[(int)$row['parent_id']], (int)$row['id'] );
	}

	if( auth_get_current_user_id() == $p_user_id ) {
		$g_user_accessible_subprojects_cache = $t_projects;
	}

	if( !isset( $t_projects[(int)$p_project_id] ) ) {
		$t_projects[(int)$p_project_id] = array();
	}

	return $t_projects[(int)$p_project_id];
}

# --------------------
function user_get_all_accessible_subprojects( $p_user_id, $p_project_id ) {
	/** @todo (thraxisp) Should all top level projects be a sub-project of ALL_PROJECTS implicitly?
	 *  affects how news and some summaries are generated
	 */
	$t_todo = user_get_accessible_subprojects( $p_user_id, $p_project_id );
	$t_subprojects = Array();

	while( $t_todo ) {
		$t_elem = (int)array_shift( $t_todo );
		if( !in_array( $t_elem, $t_subprojects ) ) {
			array_push( $t_subprojects, $t_elem );
			$t_todo = array_merge( $t_todo, user_get_accessible_subprojects( $p_user_id, $t_elem ) );
		}
	}

	return $t_subprojects;
}

function user_get_all_accessible_projects( $p_user_id, $p_project_id ) {
	if( ALL_PROJECTS == $p_project_id ) {
		$t_topprojects = $t_project_ids = user_get_accessible_projects( $p_user_id );
		foreach( $t_topprojects as $t_project ) {
			$t_project_ids = array_merge( $t_project_ids, user_get_all_accessible_subprojects( $p_user_id, $t_project ) );
		}

		$t_project_ids = array_unique( $t_project_ids );
	} else {
		access_ensure_project_level( VIEWER, $p_project_id );
		$t_project_ids = user_get_all_accessible_subprojects( $p_user_id, $p_project_id );
		array_unshift( $t_project_ids, $p_project_id );
	}

	return $t_project_ids;
}


# --------------------
# return the number of open assigned bugs to a user in a project
function user_get_assigned_open_bug_count( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$t_where_prj = helper_project_specific_where( $p_project_id, $p_user_id ) . ' AND';

	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	$query = "SELECT COUNT(*)
				  FROM $t_bug_table
				  WHERE $t_where_prj
				  		status<'$t_resolved' AND
				  		handler_id=" . db_param();
	$result = db_query_bound( $query, Array( $p_user_id ) );

	return db_result( $result );
}

# --------------------
# return the number of open reported bugs by a user in a project
function user_get_reported_open_bug_count( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$t_where_prj = helper_project_specific_where( $p_project_id, $p_user_id ) . ' AND';

	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	$query = "SELECT COUNT(*)
				  FROM $t_bug_table
				  WHERE $t_where_prj
						  status<'$t_resolved' AND
						  reporter_id=" . db_param();
	$result = db_query_bound( $query, Array( $p_user_id ) );

	return db_result( $result );
}

# --------------------
# return a profile row
function user_get_profile_row( $p_user_id, $p_profile_id ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_profile_id = db_prepare_int( $p_profile_id );

	$t_user_profile_table = db_get_table( 'mantis_user_profile_table' );

	$query = "SELECT *
				  FROM $t_user_profile_table
				  WHERE id=" . db_param() . " AND
				  		user_id=" . db_param();
	$result = db_query_bound( $query, Array( $c_profile_id, $c_user_id ) );

	if( 0 == db_num_rows( $result ) ) {
		trigger_error( ERROR_USER_PROFILE_NOT_FOUND, ERROR );
	}

	$row = db_fetch_array( $result );

	return $row;
}

# --------------------
# Get failed login attempts
function user_is_login_request_allowed( $p_user_id ) {
	$t_max_failed_login_count = config_get( 'max_failed_login_count' );
	$t_failed_login_count = user_get_field( $p_user_id, 'failed_login_count' );
	return( $t_failed_login_count < $t_max_failed_login_count || OFF == $t_max_failed_login_count );
}

# --------------------
# Get 'lost password' in progress attempts
function user_is_lost_password_request_allowed( $p_user_id ) {
	if( OFF == config_get( 'lost_password_feature' ) ) {
		return false;
	}
	$t_max_lost_password_in_progress_count = config_get( 'max_lost_password_in_progress_count' );
	$t_lost_password_in_progress_count = user_get_field( $p_user_id, 'lost_password_request_count' );
	return( $t_lost_password_in_progress_count < $t_max_lost_password_in_progress_count || OFF == $t_max_lost_password_in_progress_count );
}

# --------------------
# return the bug filter parameters for the specified user
function user_get_bug_filter( $p_user_id, $p_project_id = null ) {
	if( null === $p_project_id ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $p_project_id;
	}

	$t_view_all_cookie_id = filter_db_get_project_current( $t_project_id, $p_user_id );
	$t_view_all_cookie = filter_db_get_filter( $t_view_all_cookie_id, $p_user_id );
	$t_cookie_detail = explode( '#', $t_view_all_cookie, 2 );

	if( !isset( $t_cookie_detail[1] ) ) {
		return false;
	}

	$t_filter = unserialize( $t_cookie_detail[1] );

	$t_filter = filter_ensure_valid_filter( $t_filter );

	return $t_filter;
}

# ===================================
# Data Modification
# ===================================
# --------------------
# Update the last_visited field to be now
function user_update_last_visit( $p_user_id ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_value = db_now();

	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "UPDATE $t_user_table
				  SET last_visit= " . db_param() . "
				  WHERE id=" . db_param();

	db_query_bound( $query, Array( $c_value, $c_user_id ) );

	user_update_cache( $p_user_id, 'last_visit', $c_value );

	# db_query errors on failure so:
	return true;
}

# --------------------
# Increment the number of times the user has logegd in
# This function is only called from the login.php script
function user_increment_login_count( $p_user_id ) {
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "UPDATE $t_user_table
				SET login_count=login_count+1
				WHERE id=" . db_param();

	db_query_bound( $query, Array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	# db_query errors on failure so:
	return true;
}

# --------------------
# Reset to zero the failed login attempts
function user_reset_failed_login_count_to_zero( $p_user_id ) {
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "UPDATE $t_user_table
				SET failed_login_count=0
				WHERE id=" . db_param();
	db_query_bound( $query, Array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

# --------------------
# Increment the failed login count by 1
function user_increment_failed_login_count( $p_user_id ) {
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "UPDATE $t_user_table
				SET failed_login_count=failed_login_count+1
				WHERE id=" . db_param();
	db_query_bound( $query, Array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

# --------------------
# Reset to zero the 'lost password' in progress attempts
function user_reset_lost_password_in_progress_count_to_zero( $p_user_id ) {
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "UPDATE $t_user_table
				SET lost_password_request_count=0
				WHERE id=" . db_param();
	db_query_bound( $query, Array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

# --------------------
# Increment the failed login count by 1
function user_increment_lost_password_in_progress_count( $p_user_id ) {
	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = "UPDATE $t_user_table
				SET lost_password_request_count=lost_password_request_count+1
				WHERE id=" . db_param();
	db_query_bound( $query, Array( $p_user_id ) );

	user_clear_cache( $p_user_id );

	return true;
}

# --------------------
# Set a user field
function user_set_field( $p_user_id, $p_field_name, $p_field_value ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_field_name = db_prepare_string( $p_field_name );

	if( $p_field_name != 'protected' ) {
		user_ensure_unprotected( $p_user_id );
	}

	$t_user_table = db_get_table( 'mantis_user_table' );

	$query = 'UPDATE ' . $t_user_table .
		     ' SET ' . $c_field_name . '=' . db_param() .
			 ' WHERE id=' . db_param();

	db_query_bound( $query, Array( $p_field_value, $c_user_id ) );

	user_clear_cache( $p_user_id );

	# db_query errors on failure so:
	return true;
}

# --------------------
# Set the user's default project
function user_set_default_project( $p_user_id, $p_project_id ) {
	return user_pref_set_pref( $p_user_id, 'default_project', (int) $p_project_id );
}

# --------------------
# Set the user's password to the given string, encoded as appropriate
function user_set_password( $p_user_id, $p_password, $p_allow_protected = false ) {
	if( !$p_allow_protected ) {
		user_ensure_unprotected( $p_user_id );
	}

	$t_email = user_get_field( $p_user_id, 'email' );
	$t_username = user_get_field( $p_user_id, 'username' );

	# When the password is changed, invalidate the cookie to expire sessions that
	# may be active on all browsers.
	$t_seed = $t_email . $t_username;
	$c_cookie_string = auth_generate_unique_cookie_string( $t_seed );

	$c_user_id = db_prepare_int( $p_user_id );
	$c_password = auth_process_plain_password( $p_password );
	$c_user_table = db_get_table( 'mantis_user_table' );

	$query = "UPDATE $c_user_table
				  SET password=" . db_param() . ",
				  cookie_string=" . db_param() . "
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_password, $c_cookie_string, $c_user_id ) );

	# db_query errors on failure so:
	return true;
}

# --------------------
# Set the user's email to the given string after checking that it is a valid email
function user_set_email( $p_user_id, $p_email ) {
	email_ensure_valid( $p_email );

	return user_set_field( $p_user_id, 'email', $p_email );
}

# --------------------
# Set the user's realname to the given string after checking validity
function user_set_realname( $p_user_id, $p_realname ) {
	/** @todo ensure_realname_valid( $p_realname ); */

	return user_set_field( $p_user_id, 'realname', $p_realname );
}

# --------------------
# Set the user's username to the given string after checking that it is valid
function user_set_name( $p_user_id, $p_username ) {
	user_ensure_name_valid( $p_username );
	user_ensure_name_unique( $p_username );

	return user_set_field( $p_user_id, 'username', $p_username );
}

# --------------------
# Reset the user's password
#  Take into account the 'send_reset_password' setting
#   - if it is ON, generate a random password and send an email
#      (unless the second parameter is false)
#   - if it is OFF, set the password to blank
#  Return false if the user is protected, true if the password was
#   successfully reset
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
	if(( ON == config_get( 'send_reset_password' ) ) && ( ON == config_get( 'enable_email_notification' ) ) ) {

		# Create random password
		$t_email = user_get_field( $p_user_id, 'email' );
		$t_password = auth_generate_random_password( $t_email );
		$t_password2 = auth_process_plain_password( $t_password );

		user_set_field( $p_user_id, 'password', $t_password2 );

		# Send notification email
		if( $p_send_email ) {
			$t_confirm_hash = auth_generate_confirm_hash( $p_user_id );
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
