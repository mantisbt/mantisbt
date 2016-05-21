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
 * Profile API
 *
 * @package CoreAPI
 * @subpackage ProfileAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

/**
 * Create a new profile for the user, return the ID of the new profile
 * @param integer $p_user_id     A valid user identifier.
 * @param string  $p_platform    Value for profile platform.
 * @param string  $p_os          Value for profile operating system.
 * @param string  $p_os_build    Value for profile operation system build.
 * @param string  $p_description Description of profile.
 * @return integer
 */
function profile_create( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description ) {
	$p_user_id = (int)$p_user_id;

	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	# platform cannot be blank
	if( is_blank( $p_platform ) ) {
		error_parameters( lang_get( 'platform' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os cannot be blank
	if( is_blank( $p_os ) ) {
		error_parameters( lang_get( 'os' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os_build cannot be blank
	if( is_blank( $p_os_build ) ) {
		error_parameters( lang_get( 'version' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# Add profile
	db_param_push();
	$t_query = 'INSERT INTO {user_profile}
				    ( user_id, platform, os, os_build, description )
				  VALUES
				    ( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ' )';
	db_query( $t_query, array( $p_user_id, $p_platform, $p_os, $p_os_build, $p_description ) );

	return db_insert_id( db_get_table( 'user_profile' ) );
}

/**
 * Delete a profile for the user
 *
 * Note that although profile IDs are currently globally unique, the existing
 * code included the user_id in the query and I have chosen to keep that for
 * this API as it hides the details of id implementation from users of the API
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_profile_id A profile identifier.
 * @return void
 */
function profile_delete( $p_user_id, $p_profile_id ) {
	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	# Delete the profile
	db_param_push();
	$t_query = 'DELETE FROM {user_profile} WHERE id=' . db_param() . ' AND user_id=' . db_param();
	db_query( $t_query, array( $p_profile_id, $p_user_id ) );
}

/**
 * Update a profile for the user
 * @param integer $p_user_id     A valid user identifier.
 * @param integer $p_profile_id  A profile identifier.
 * @param string  $p_platform    Value for profile platform.
 * @param string  $p_os          Value for profile operating system.
 * @param string  $p_os_build    Value for profile operation system build.
 * @param string  $p_description Description of profile.
 * @return void
 */
function profile_update( $p_user_id, $p_profile_id, $p_platform, $p_os, $p_os_build, $p_description ) {
	if( ALL_USERS != $p_user_id ) {
		user_ensure_unprotected( $p_user_id );
	}

	# platform cannot be blank
	if( is_blank( $p_platform ) ) {
		error_parameters( lang_get( 'platform' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os cannot be blank
	if( is_blank( $p_os ) ) {
		error_parameters( lang_get( 'os' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# os_build cannot be blank
	if( is_blank( $p_os_build ) ) {
		error_parameters( lang_get( 'version' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	# Add item
	db_param_push();
	$t_query = 'UPDATE {user_profile}
				  SET platform=' . db_param() . ',
				  	  os=' . db_param() . ',
					  os_build=' . db_param() . ',
					  description=' . db_param() . '
				  WHERE id=' . db_param() . ' AND user_id=' . db_param();
	db_query( $t_query, array( $p_platform, $p_os, $p_os_build, $p_description, $p_profile_id, $p_user_id ) );
}

/**
 * Return a profile row from the database
 * @param integer $p_user_id    A valid user identifier.
 * @param integer $p_profile_id A profile identifier.
 * @return array
 */
function profile_get_row( $p_user_id, $p_profile_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {user_profile} WHERE id=' . db_param() . ' AND user_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_profile_id, $p_user_id ) );

	return db_fetch_array( $t_result );
}

/**
 * Return a profile row from the database
 * @param integer $p_profile_id A profile identifier.
 * @return array
 * @todo relationship of this function to profile_get_row?
 */
function profile_get_row_direct( $p_profile_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {user_profile} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_profile_id ) );

	return db_fetch_array( $t_result );
}

/**
 * Return an array containing all rows for a given user
 * @param integer $p_user_id   A valid user identifier.
 * @param boolean $p_all_users Include profiles for all users.
 * @return array
 */
function profile_get_all_rows( $p_user_id, $p_all_users = false ) {
	db_param_push();
	$t_query_where = 'user_id = ' . db_param();
	$t_param[] = (int)$p_user_id;

	if( $p_all_users && ALL_USERS != $p_user_id ) {
		$t_query_where .= ' OR user_id = ' . db_param();
		$t_param[] = ALL_USERS;
	}

	$t_query = 'SELECT * FROM {user_profile} WHERE ' . $t_query_where . ' ORDER BY platform, os, os_build';
	$t_result = db_query( $t_query, $t_param );

	$t_rows = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		array_push( $t_rows, $t_row );
	}

	return $t_rows;
}

/**
 * Return an array containing all profiles for a given user,
 * including global profiles
 * @param integer $p_user_id A valid user identifier.
 * @return array
 */
function profile_get_all_for_user( $p_user_id ) {
	return profile_get_all_rows( $p_user_id, $p_user_id != ALL_USERS );
}

/**
 * Return an array of strings containing unique values for the specified field based
 * on private and public profiles accessible to the specified user.
 * @param string  $p_field   Field name of the profile to retrieve.
 * @param integer $p_user_id A valid user identifier.
 * @return array
 */
function profile_get_field_all_for_user( $p_field, $p_user_id = null ) {
	$c_user_id = ( $p_user_id === null ) ? auth_get_current_user_id() : $p_user_id;

	switch( $p_field ) {
		case 'id':
		case 'user_id':
		case 'platform':
		case 'os':
		case 'os_build':
		case 'description':
			$c_field = $p_field;
			break;
		default:
			trigger_error( ERROR_GENERIC, ERROR );
	}

	db_param_push();
	$t_query = 'SELECT DISTINCT ' . $c_field . '
				  FROM {user_profile}
				  WHERE ( user_id=' . db_param() . ' ) OR ( user_id = 0 )
				  ORDER BY ' . $c_field;
	$t_result = db_query( $t_query, array( $c_user_id ) );

	$t_rows = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		array_push( $t_rows, $t_row[$c_field] );
	}

	return $t_rows;
}

/**
 * Return an array containing all profiles used in a given project
 * @param integer $p_project_id A valid project identifier.
 * @return array
 */
function profile_get_all_for_project( $p_project_id ) {
	$t_project_where = helper_project_specific_where( $p_project_id );

	$t_query = 'SELECT DISTINCT(up.id), up.user_id, up.platform, up.os, up.os_build
				  FROM {user_profile} up, {bug} b
				  WHERE ' . $t_project_where . '
				  AND up.id = b.profile_id
				  ORDER BY up.platform, up.os, up.os_build';
	$t_result = db_query( $t_query );

	$t_rows = array();

	while( $t_row = db_fetch_array( $t_result ) ) {
		array_push( $t_rows, $t_row );
	}

	return $t_rows;
}

/**
 * Returns the default profile
 * @param integer $p_user_id A valid user identifier.
 * @return string
 */
function profile_get_default( $p_user_id ) {
	db_param_push();
	$t_query = 'SELECT default_profile FROM {user_pref} WHERE user_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_user_id ) );

	$t_default_profile = (int)db_result( $t_result, 0, 0 );

	return $t_default_profile;
}

/**
 * Returns whether the specified profile is global
 * @param integer $p_profile_id A valid profile identifier.
 * @return boolean
 */
function profile_is_global( $p_profile_id ) {
	$t_row = profile_get_row( ALL_USERS, $p_profile_id );
	return( $t_row !== false );
}

