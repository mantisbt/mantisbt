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
 * User Preferences API
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package CoreAPI
 * @subpackage UserPreferencesAPI
 */

/**
 * Preference Structure Definition
 * @package MantisBT
 * @subpackage classes
 */
class UserPreferences {
	protected $default_profile = NULL;
	protected $default_project = NULL;
	protected $refresh_delay = NULL;
	protected $redirect_delay = NULL;
	protected $bugnote_order = NULL;
	protected $email_on_new = NULL;
	protected $email_on_assigned = NULL;
	protected $email_on_feedback = NULL;
	protected $email_on_resolved = NULL;
	protected $email_on_closed = NULL;
	protected $email_on_reopened = NULL;
	protected $email_on_bugnote = NULL;
	protected $email_on_status = NULL;
	protected $email_on_priority = NULL;
	protected $email_on_new_min_severity = NULL;
	protected $email_on_assigned_min_severity = NULL;
	protected $email_on_feedback_min_severity = NULL;
	protected $email_on_resolved_min_severity = NULL;
	protected $email_on_closed_min_severity = NULL;
	protected $email_on_reopened_min_severity = NULL;
	protected $email_on_bugnote_min_severity = NULL;
	protected $email_on_status_min_severity = NULL;
	protected $email_on_priority_min_severity = NULL;
	protected $email_bugnote_limit = NULL;
	protected $language = NULL;
	protected $timezone = NULL;

	private $pref_user_id;
	private $pref_project_id;

	private static $default_mapping = array(
	'default_profile' => 'default_profile',
	'default_project' => 'default_project',
	'refresh_delay' => 'default_refresh_delay',
	'redirect_delay' => 'default_redirect_delay',
	'bugnote_order' => 'default_bugnote_order',
	'email_on_new' => 'default_email_on_new',
	'email_on_assigned' => 'default_email_on_assigned',
	'email_on_feedback' => 'default_email_on_feedback',
	'email_on_resolved' => 'default_email_on_resolved',
	'email_on_closed' => 'default_email_on_closed',
	'email_on_reopened' => 'default_email_on_reopened',
	'email_on_bugnote' => 'default_email_on_bugnote',
	'email_on_status' => 'default_email_on_status',
	'email_on_priority' => 'default_email_on_priority',
	'email_on_new_min_severity' => 'default_email_on_new_minimum_severity',
	'email_on_assigned_min_severity' => 'default_email_on_assigned_minimum_severity',
	'email_on_feedback_min_severity' => 'default_email_on_feedback_minimum_severity',
	'email_on_resolved_min_severity' => 'default_email_on_resolved_minimum_severity',
	'email_on_closed_min_severity' => 'default_email_on_closed_minimum_severity',
	'email_on_reopened_min_severity' => 'default_email_on_reopened_minimum_severity',
	'email_on_bugnote_min_severity' => 'default_email_on_bugnote_minimum_severity',
	'email_on_status_min_severity' => 'default_email_on_status_minimum_severity',
	'email_on_priority_min_severity' => 'default_email_on_priority_minimum_severity',
	'email_bugnote_limit' => 'default_email_bugnote_limit',
	'language' => 'default_language',
	'timezone' => 'default_timezone',
	);
	
	/**
	 * Constructor
	 * @param int $p_user_id
	 * @param int $p_project_id
	 */
	function UserPreferences( $p_user_id, $p_project_id ) {
		$this->default_profile = 0;
		$this->default_project = ALL_PROJECTS;

		$this->pref_user_id = (int)$p_user_id;
		$this->pref_project_id = (int)$p_project_id;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @private
	 */
	public function __set($name, $value) {
		switch ($name) {
			case 'timezone':
				if( $value == '' ) {
					$value = null;
				}
		}
		$this->$name = $value;
	}

	/**
	 * @param string $t_string
	 * @private
	 */
	public function __get( $p_string ) {
		if( is_null( $this->$p_string ) ) {
			$this->$p_string = config_get( self::$default_mapping[$p_string], null, $this->pref_user_id, $this->pref_project_id );
		}
		return $this->$p_string;
	}

	/**
	 * @param string $t_string
	 */
	function Get( $p_string ) {
		if( is_null( $this->$p_string ) ) {
			$this->$p_string = config_get( self::$default_mapping[$p_string], null, $this->pref_user_id, $this->pref_project_id );
		}
		return $this->$p_string;
	}
}

# ########################################
# SECURITY NOTE: cache globals are initialized here to prevent them
#   being spoofed if register_globals is turned on

$g_cache_user_pref = array();
$g_cache_current_user_pref = array();

/**
 * Cache a user preferences row if necessary and return the cached copy
 *  If the third parameter is true (default), trigger an error
 *  if the preferences can't be found.  If the second parameter is
 *  false, return false if the preferences can't be found.
 *
 * @param int $p_user_id
 * @param int $p_project_id
 * @param bool $p_trigger_errors
 * @return false|array
 */
function user_pref_cache_row( $p_user_id, $p_project_id = ALL_PROJECTS, $p_trigger_errors = true ) {
	global $g_cache_user_pref;

	if( isset( $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] ) ) {
		return $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id];
	}

	$t_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	$query = "SELECT *
				  FROM $t_user_pref_table
				  WHERE user_id=" . db_param() . " AND project_id=" . db_param();
	$result = db_query_bound( $query, Array( (int)$p_user_id, (int)$p_project_id ) );

	if( 0 == db_num_rows( $result ) ) {
		if( $p_trigger_errors ) {
			trigger_error( ERROR_USER_PREFS_NOT_FOUND, ERROR );
		} else {
			$g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] = false;
			return false;
		}
	}

	$row = db_fetch_array( $result );

	if( !isset( $g_cache_user_pref[(int)$p_user_id] ) ) {
		$g_cache_user_pref[(int)$p_user_id] = array();
	}

	$g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] = $row;

	return $row;
}

/**
 * Cache user preferences for a set of users
 * @param array $p_user_id_array 
 * @param int $p_project_id
 * @return null
 */
function user_pref_cache_array_rows( $p_user_id_array, $p_project_id = ALL_PROJECTS ) {
	global $g_cache_user_pref;
	$c_user_id_array = array();

	# identify the user ids that are not cached already.
	foreach( $p_user_id_array as $t_user_id ) {
		if( !isset( $g_cache_user_pref[(int) $t_user_id][(int)$p_project_id] ) ) {
			$c_user_id_array[(int)$t_user_id] = (int)$t_user_id;
		}
	}

	# if all users are already cached, then return
	if ( empty( $c_user_id_array ) ) {
		return;
	}

	$t_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	$query = "SELECT *
				  FROM $t_user_pref_table
				  WHERE user_id IN (" . implode( ',', $c_user_id_array ) . ') AND project_id=' . db_param();

	$result = db_query_bound( $query, Array( (int)$p_project_id ) );

	while( $row = db_fetch_array( $result ) ) {
		if ( !isset( $g_cache_user_pref[(int) $row['user_id']] ) ) {
			$g_cache_user_pref[(int) $row['user_id']] = array();
		}

		$g_cache_user_pref[(int) $row['user_id']][(int)$p_project_id] = $row;

		# remove found users from required set.
		unset( $c_user_id_array[(int) $row['user_id']] );
	}

	# cache users that are not found as false (i.e. negative cache)
	foreach( $c_user_id_array as $t_user_id ) {
		$g_cache_user_pref[(int) $t_user_id][(int)$p_project_id] = false;
	}
}

/** 
 * Clear the user preferences cache (or just the given id if specified)
 * @param $p_user_id
 * @param $p_project_id
 * @return true
 */
function user_pref_clear_cache( $p_user_id = null, $p_project_id = null ) {
	global $g_cache_user_pref;

	if( null === $p_user_id ) {
		$g_cache_user_pref = array();
	} else if( null === $p_project_id ) {
		unset( $g_cache_user_pref[(int)$p_user_id] );
	} else {
		unset( $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] );
	}

	return true;
}

/**
 * return true if the user has prefs assigned for the given project,
 *  false otherwise
 * @param int $p_user_id
 * @param int $p_project_id
 * @return bool
 */
function user_pref_exists( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	if( false === user_pref_cache_row( $p_user_id, $p_project_id, false ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * perform an insert of a preference object into the DB
 * @param int $p_user_id
 * @param int $p_project_id
 * @param UserPreferences $p_prefs
 * @return true
 */
function user_pref_insert( $p_user_id, $p_project_id, $p_prefs ) {
	static $t_vars;
	$c_user_id = db_prepare_int( $p_user_id );
	$c_project_id = db_prepare_int( $p_project_id );

	user_ensure_unprotected( $p_user_id );

	$t_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	if ($t_vars == null ) {
		$t_vars = getClassProperties( 'UserPreferences', 'protected');
	}

	$t_values = array();

	$t_params[] = db_param(); // user_id
	$t_values[] = $c_user_id;
	$t_params[] = db_param(); // project_id
	$t_values[] = $c_project_id;
	foreach( $t_vars as $var => $val ) {
		array_push( $t_params, db_param());
		array_push( $t_values, $p_prefs->Get( $var ) );
	}

	$t_vars_string = implode( ', ', array_keys( $t_vars ) );
	$t_params_string = implode( ',', $t_params );

	$query = 'INSERT INTO ' . $t_user_pref_table .
			 ' (user_id, project_id, ' . $t_vars_string . ') ' .
			 ' VALUES ( ' . $t_params_string . ')';
	db_query_bound( $query, $t_values  );

	# db_query errors on failure so:
	return true;
}

/**
 * perform an update of a preference object into the DB
 * @param int $p_user_id
 * @param int $p_project_id
 * @param UserPreferences $p_prefs
 * @return true
 */
function user_pref_update( $p_user_id, $p_project_id, $p_prefs ) {
	static $t_vars;
	$c_user_id = db_prepare_int( $p_user_id );
	$c_project_id = db_prepare_int( $p_project_id );

	user_ensure_unprotected( $p_user_id );

	$t_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	if ($t_vars == null ) {
		$t_vars = getClassProperties( 'UserPreferences', 'protected');
	}

	$t_pairs = array();
	$t_values = array();

	foreach( $t_vars as $var => $val ) {
		array_push( $t_pairs, "$var = " . db_param() ) ;
		array_push( $t_values, $p_prefs->$var );
	}

	$t_pairs_string = implode( ', ', $t_pairs );
	$t_values[] = $c_user_id;
	$t_values[] = $c_project_id;

	$query = "UPDATE $t_user_pref_table
				  SET $t_pairs_string
				  WHERE user_id=" . db_param() . " AND project_id=" . db_param();
	db_query_bound( $query, $t_values );

	user_pref_clear_cache( $p_user_id, $p_project_id );

	# db_query errors on failure so:
	return true;
}

/**
 * delete a preferencess row
 * returns true if the prefs were successfully deleted
 * @param int $p_user_id
 * @param int $p_project_id
 * @return true
 */
function user_pref_delete( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$c_user_id = db_prepare_int( $p_user_id );
	$c_project_id = db_prepare_int( $p_project_id );

	user_ensure_unprotected( $p_user_id );

	$t_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	$query = "DELETE FROM $t_user_pref_table
				  WHERE user_id=" . db_param() . " AND
				  		project_id=" . db_param();
	db_query_bound( $query, Array( $c_user_id, $c_project_id ) );

	user_pref_clear_cache( $p_user_id, $p_project_id );

	# db_query errors on failure so:
	return true;
}

/**
 * delete all preferences for a user in all projects
 * returns true if the prefs were successfully deleted
 *
 * It is far more efficient to delete them all in one query than to
 *  call user_pref_delete() for each one and the code is short so that's
 *  what we do
 * @param int $p_user_id
 * @return true
 */
function user_pref_delete_all( $p_user_id ) {
	$c_user_id = db_prepare_int( $p_user_id );

	user_ensure_unprotected( $p_user_id );

	$t_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	$query = 'DELETE FROM ' . $t_user_pref_table . ' WHERE user_id=' . db_param();
	db_query_bound( $query, Array( $c_user_id ) );

	user_pref_clear_cache( $p_user_id );

	# db_query errors on failure so:
	return true;
}

/**
 * delete all preferences for a project for all users (part of deleting the project)
 * returns true if the prefs were successfully deleted
 *
 * It is far more efficient to delete them all in one query than to
 *  call user_pref_delete() for each one and the code is short so that's
 *  what we do
 * @param $p_project_id
 * @return true
 */
function user_pref_delete_project( $p_project_id ) {
	$c_project_id = db_prepare_int( $p_project_id );

	$t_user_pref_table = db_get_table( 'mantis_user_pref_table' );

	$query = 'DELETE FROM ' . $t_user_pref_table . ' WHERE project_id=' . db_param();
	db_query_bound( $query, Array( $c_project_id ) );

	# db_query errors on failure so:
	return true;
}

/**
 * return the user's preferences in a UserPreferences object
 * @param int $p_user_id
 * @param int $p_project_id
 * @return UserPreferences
 */
function user_pref_get( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	static $t_vars;
	global $g_cache_current_user_pref;

	if ( isset( $g_cache_current_user_pref[(int)$p_project_id] ) &&
		auth_is_user_authenticated() &&
		auth_get_current_user_id() == $p_user_id ) {
		return $g_cache_current_user_pref[(int)$p_project_id];
	}

	$t_prefs = new UserPreferences( $p_user_id, $p_project_id );

	$row = user_pref_cache_row( $p_user_id, $p_project_id, false );

	# If the user has no preferences for the given project
	if( false === $row ) {
		if( ALL_PROJECTS != $p_project_id ) {
			# Try to get the prefs for ALL_PROJECTS (the defaults)
			$row = user_pref_cache_row( $p_user_id, ALL_PROJECTS, false );
		}

		# If $row is still false (the user doesn't have default preferences)
		if( false === $row ) {
			# We use an empty array
			$row = array();
		}
	}

	if ($t_vars == null ) {
		$t_vars = getClassProperties( 'UserPreferences', 'protected');
	}

	$t_row_keys = array_keys( $row );

	# Check each variable in the class
	foreach( $t_vars as $var => $val ) {
		# If we got a field from the DB with the same name
		if( in_array( $var, $t_row_keys, true ) ) {
			# Store that value in the object
			$t_prefs->$var = $row[$var];
		}
	}
	if ( auth_is_user_authenticated() && auth_get_current_user_id() == $p_user_id ) {
		$g_cache_current_user_pref[ (int)$p_project_id ] = $t_prefs;
	}
	return $t_prefs;
}

/**
 * Return the specified preference field for the user id
 * If the preference can't be found try to return a defined default
 * If that fails, trigger a WARNING and return ''
 * @param int $p_user_id
 * @param string $p_pref_name
 * @param int $p_project_id
 * @return string
 */
function user_pref_get_pref( $p_user_id, $p_pref_name, $p_project_id = ALL_PROJECTS ) {
	static $t_vars;

	$t_prefs = user_pref_get( $p_user_id, $p_project_id );

	if ($t_vars == null ) {
		$t_reflection = new ReflectionClass('UserPreferences');
		$t_vars = $t_reflection->getDefaultProperties();
	}

	if( in_array( $p_pref_name, array_keys( $t_vars ), true ) ) {
		return $t_prefs->Get( $p_pref_name );
	} else {
		error_parameters( $p_pref_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * returns user language
 * @param int $p_user_id
 * @param int $p_project_id
 * @return string language name or null if invalid language specified
 */
function user_pref_get_language( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	$t_prefs = user_pref_get( $p_user_id, $p_project_id );

	// ensure the language is a valid one
	$t_lang = $t_prefs->language;
	if( !lang_language_exists( $t_lang ) ) {
		$t_lang = null;
	}
	return $t_lang;
}

/**
 * Set a user preference
 *
 * By getting the prefs for the project first we deal fairly well with defaults.
 *  If there are currently no prefs for that project, the ALL_PROJECTS prefs will
 *  be returned so we end up storing a new set of prefs for the given project
 *  based on the prefs for ALL_PROJECTS.  If there isn't even an entry for
 *  ALL_PROJECTS, we'd get returned a default UserPreferences object to modify.
 * @param int $p_user_id
 * @param string $p_pref_name
 * @param string $p_pref_value
 * @param int $p_project_id
 * @return true
 */
function user_pref_set_pref( $p_user_id, $p_pref_name, $p_pref_value, $p_project_id = ALL_PROJECTS ) {
	$t_prefs = user_pref_get( $p_user_id, $p_project_id );

	$t_prefs->$p_pref_name = $p_pref_value;

	user_pref_set( $p_user_id, $t_prefs, $p_project_id );

	return true;
}

/**
 * set the user's preferences for the project from the given preferences object
 * Do the work by calling user_pref_update() or user_pref_insert() as appropriate
 * @param int $p_user_id
 * @param UserPreferences $p_prefs
 * @param int $p_project_id
 * @return true
 */
function user_pref_set( $p_user_id, $p_prefs, $p_project_id = ALL_PROJECTS ) {
	if( user_pref_exists( $p_user_id, $p_project_id ) ) {
		return user_pref_update( $p_user_id, $p_project_id, $p_prefs );
	} else {
		return user_pref_insert( $p_user_id, $p_project_id, $p_prefs );
	}
}

