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
 * User Preferences API
 *
 * @package CoreAPI
 * @subpackage UserPreferencesAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses lang_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'lang_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

/**
 * Preference Structure Definition
 * @package MantisBT
 * @subpackage classes
 */
class UserPreferences {
	/**
	 * Default Profile
	 */
	protected $default_profile = NULL;

	/**
	 * Default Project for user
	 */
	protected $default_project = NULL;
	
	/**
	 * Automatic Refresh delay
	 */
	protected $refresh_delay = NULL;
	
	/**
	 * Automatic Redirect delay
	 */	
	protected $redirect_delay = NULL;

	/**
	 * Bugnote order - oldest/newest first
	 */
	protected $bugnote_order = NULL;
	
	/**
	 * Receive email on new bugs
	 */
	protected $email_on_new = NULL;

	/**
	 * Receive email on assigned bugs
	 */
	protected $email_on_assigned = NULL;
	
	/**
	 * Receive email on feedback
	 */
	protected $email_on_feedback = NULL;

	/**
	 * Receive email on resolved bugs
	 */
	protected $email_on_resolved = NULL;

	/**
	 * Receive email on closed bugs
	 */
	protected $email_on_closed = NULL;

	/**
	 * Receive email on reopened bugs
	 */
	protected $email_on_reopened = NULL;

	/**
	 * Receive email on new bugnote
	 */
	protected $email_on_bugnote = NULL;

	/**
	 * Receive email on bug status change
	 */
	protected $email_on_status = NULL;

	/**
	 * Receive email on bug priority change
	 */
	protected $email_on_priority = NULL;
	
	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_new_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_assigned_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_feedback_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_resolved_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_closed_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_reopened_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_bugnote_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_status_min_severity = NULL;

	/**
	 * Minimum Severity on which to trigger email if set to receive
	 */	
	protected $email_on_priority_min_severity = NULL;

	/**
	 * Number of bugnotes to include in generated emails
	 */
	protected $email_bugnote_limit = NULL;

	/**
	 * Users language preference
	 */
	protected $language = NULL;

	/**
	 * User Timezone
	 */
	protected $timezone = NULL;

	/**
	 * User id
	 */
	private $pref_user_id;

	/**
	 * Project ID
	 */
	private $pref_project_id;

	/**
	 * Default Values - Config Field Mappings
	 */
	private static $default_mapping = array(
	'default_profile' => array( 'default_profile', 'int' ),
	'default_project' => array( 'default_project', 'int' ),
	'refresh_delay' => array( 'default_refresh_delay', 'int' ),
	'redirect_delay' => array( 'default_redirect_delay', 'int' ),
	'bugnote_order' => array( 'default_bugnote_order', 'string' ),
	'email_on_new' => array( 'default_email_on_new', 'int' ),
	'email_on_assigned' => array(  'default_email_on_assigned', 'int' ),
	'email_on_feedback' => array(  'default_email_on_feedback', 'int' ),
	'email_on_resolved' => array(  'default_email_on_resolved', 'int' ),
	'email_on_closed' => array(  'default_email_on_closed', 'int' ),
	'email_on_reopened' => array(  'default_email_on_reopened', 'int' ),
	'email_on_bugnote' => array(  'default_email_on_bugnote', 'int' ),
	'email_on_status' => array(  'default_email_on_status', 'int' ),
	'email_on_priority' => array(  'default_email_on_priority', 'int' ),
	'email_on_new_min_severity' => array(  'default_email_on_new_minimum_severity', 'int' ),
	'email_on_assigned_min_severity' => array(  'default_email_on_assigned_minimum_severity', 'int' ),
	'email_on_feedback_min_severity' => array(  'default_email_on_feedback_minimum_severity', 'int' ),
	'email_on_resolved_min_severity' => array(  'default_email_on_resolved_minimum_severity', 'int' ),
	'email_on_closed_min_severity' => array(  'default_email_on_closed_minimum_severity', 'int' ),
	'email_on_reopened_min_severity' => array(  'default_email_on_reopened_minimum_severity', 'int' ),
	'email_on_bugnote_min_severity' => array(  'default_email_on_bugnote_minimum_severity', 'int' ),
	'email_on_status_min_severity' => array(  'default_email_on_status_minimum_severity', 'int' ),
	'email_on_priority_min_severity' => array(  'default_email_on_priority_minimum_severity', 'int' ),
	'email_bugnote_limit' => array(  'default_email_bugnote_limit', 'int' ),
	'language' => array(  'default_language', 'string' ),
	'timezone' => array( 'default_timezone', 'string' ),
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
	 * Overloaded function
	 * @param string $p_name Property name
	 * @param string $p_value Value
	 * @access private
	 */
	public function __set($p_name, $p_value) {
		switch ($p_name) {
			case 'timezone':
				if( $p_value == '' ) {
					$p_value = null;
				}
		}
		$this->$p_name = $p_value;
	}

	/**
	 * Overloaded function
	 * @param string $p_string Property name
	 * @access private
	 * @return mixed
	 */
	public function __get( $p_string ) {
		if( is_null( $this->$p_string ) ) {
			$this->$p_string = config_get( self::$default_mapping[$p_string][0], null, $this->pref_user_id, $this->pref_project_id );
		}
		switch (self::$default_mapping[$p_string][1]) {
			case 'int':
				return (int)($this->$p_string);
			default:
				return $this->$p_string;
		}
	}

	/**
	 * Public Get() function
	 * @param string $p_string Property
	 * @return mixed
	 */
	function Get( $p_string ) {
		if( is_null( $this->$p_string ) ) {
			$this->$p_string = config_get( self::$default_mapping[$p_string][0], null, $this->pref_user_id, $this->pref_project_id );
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
 * @return bool|array
 */
function user_pref_cache_row( $p_user_id, $p_project_id = ALL_PROJECTS, $p_trigger_errors = true ) {
	global $g_cache_user_pref;

	if( isset( $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] ) ) {
		return $g_cache_user_pref[(int)$p_user_id][(int)$p_project_id];
	}

	$t_user_pref_table = db_get_table( 'user_pref' );
	$t_query = "SELECT * FROM $t_user_pref_table
				  WHERE user_id=" . db_param() . " AND project_id=" . db_param();
	$t_result = db_query_bound( $t_query, array( (int)$p_user_id, (int)$p_project_id ) );

	$t_row = db_fetch_array( $t_result );

	if( !$t_row ) {
		if( $p_trigger_errors ) {
			trigger_error( ERROR_USER_PREFS_NOT_FOUND, ERROR );
		} else {
			$g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] = false;
			return false;
		}
	}

	if( !isset( $g_cache_user_pref[(int)$p_user_id] ) ) {
		$g_cache_user_pref[(int)$p_user_id] = array();
	}

	$g_cache_user_pref[(int)$p_user_id][(int)$p_project_id] = $t_row;

	return $t_row;
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

	$t_user_pref_table = db_get_table( 'user_pref' );

	$query = "SELECT *
				  FROM $t_user_pref_table
				  WHERE user_id IN (" . implode( ',', $c_user_id_array ) . ') AND project_id=' . db_param();

	$t_result = db_query_bound( $query, array( (int)$p_project_id ) );

	while( $row = db_fetch_array( $t_result ) ) {
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
 * @return bool
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
 * @return bool
 */
function user_pref_insert( $p_user_id, $p_project_id, $p_prefs ) {
	static $t_vars;
	$c_user_id = (int)$p_user_id;
	$c_project_id = (int)$p_project_id;

	user_ensure_unprotected( $p_user_id );

	if ($t_vars == null ) {
		$t_vars = getClassProperties( 'UserPreferences', 'protected');
	}

	$t_values = array();

	$t_params[] = db_param(); # user_id
	$t_values[] = $c_user_id;
	$t_params[] = db_param(); # project_id
	$t_values[] = $c_project_id;
	foreach( $t_vars as $var => $val ) {
		array_push( $t_params, db_param());
		array_push( $t_values, $p_prefs->Get( $var ) );
	}

	$t_vars_string = implode( ', ', array_keys( $t_vars ) );
	$t_params_string = implode( ',', $t_params );

	$t_user_pref_table = db_get_table( 'user_pref' );
	$query = 'INSERT INTO ' . $t_user_pref_table .
			 ' (user_id, project_id, ' . $t_vars_string . ') ' .
			 ' VALUES ( ' . $t_params_string . ')';
	db_query_bound( $query, $t_values  );

	# db_query_bound() errors on failure so:
	return true;
}

/**
 * perform an update of a preference object into the DB
 * @param int $p_user_id
 * @param int $p_project_id
 * @param UserPreferences $p_prefs
 */
function user_pref_update( $p_user_id, $p_project_id, $p_prefs ) {
	static $t_vars;

	user_ensure_unprotected( $p_user_id );

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
	$t_values[] = $p_user_id;
	$t_values[] = $p_project_id;

	$t_user_pref_table = db_get_table( 'user_pref' );
	$query = "UPDATE $t_user_pref_table
				  SET $t_pairs_string
				  WHERE user_id=" . db_param() . " AND project_id=" . db_param();
	db_query_bound( $query, $t_values );

	user_pref_clear_cache( $p_user_id, $p_project_id );
}

/**
 * delete a preferencess row
 * returns true if the prefs were successfully deleted
 * @param int $p_user_id
 * @param int $p_project_id
 * @return bool
 */
function user_pref_delete( $p_user_id, $p_project_id = ALL_PROJECTS ) {
	user_ensure_unprotected( $p_user_id );

	$t_user_pref_table = db_get_table( 'user_pref' );
	$query = "DELETE FROM $t_user_pref_table
				  WHERE user_id=" . db_param() . " AND
				  		project_id=" . db_param();
	db_query_bound( $query, array( $p_user_id, $p_project_id ) );

	user_pref_clear_cache( $p_user_id, $p_project_id );

	# db_query_bound() errors on failure so:
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
 * @return bool
 */
function user_pref_delete_all( $p_user_id ) {
	user_ensure_unprotected( $p_user_id );

	$t_user_pref_table = db_get_table( 'user_pref' );
	$query = 'DELETE FROM ' . $t_user_pref_table . ' WHERE user_id=' . db_param();
	db_query_bound( $query, array( $p_user_id ) );

	user_pref_clear_cache( $p_user_id );

	# db_query_bound() errors on failure so:
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
 * @return bool
 */
function user_pref_delete_project( $p_project_id ) {
	$t_user_pref_table = db_get_table( 'user_pref' );
	$query = 'DELETE FROM ' . $t_user_pref_table . ' WHERE project_id=' . db_param();
	db_query_bound( $query, array( $p_project_id ) );

	# db_query_bound() errors on failure so:
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

	$t_row = user_pref_cache_row( $p_user_id, $p_project_id, false );

	# If the user has no preferences for the given project
	if( false === $t_row ) {
		if( ALL_PROJECTS != $p_project_id ) {
			# Try to get the prefs for ALL_PROJECTS (the defaults)
			$t_row = user_pref_cache_row( $p_user_id, ALL_PROJECTS, false );
		}

		# If $t_row is still false (the user doesn't have default preferences)
		if( false === $t_row ) {
			# We use an empty array
			$t_row = array();
		}
	}

	if ($t_vars == null ) {
		$t_vars = getClassProperties( 'UserPreferences', 'protected');
	}

	$t_row_keys = array_keys( $t_row );

	# Check each variable in the class
	foreach( $t_vars as $var => $val ) {
		# If we got a field from the DB with the same name
		if( in_array( $var, $t_row_keys, true ) ) {
			# Store that value in the object
			$t_prefs->$var = $t_row[$var];
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

	# ensure the language is a valid one
	$t_lang = $t_prefs->language;
	if( !lang_language_exists( $t_lang ) ) {
		$t_lang = null;
	}
	return $t_lang;
}

/**
 * Set a user preference
 *
 * By getting the preferences for the project first we deal fairly well with defaults. If there are currently no
 * preferences for that project, the ALL_PROJECTS preferences will be returned so we end up storing a new set of
 * preferences for the given project based on the preferences for ALL_PROJECTS.  If there isn't even an entry for
 *  ALL_PROJECTS, we'd get returned a default UserPreferences object to modify.
 * @param int $p_user_id
 * @param string $p_pref_name
 * @param string $p_pref_value
 * @param int $p_project_id
 * @return bool
 */
function user_pref_set_pref( $p_user_id, $p_pref_name, $p_pref_value, $p_project_id = ALL_PROJECTS ) {
	$t_prefs = user_pref_get( $p_user_id, $p_project_id );

	if ( $t_prefs->$p_pref_name != $p_pref_value ) {
		$t_prefs->$p_pref_name = $p_pref_value;
		user_pref_set( $p_user_id, $t_prefs, $p_project_id );
	}

	return true;
}

/**
 * set the user's preferences for the project from the given preferences object
 * Do the work by calling user_pref_update() or user_pref_insert() as appropriate
 * @param int $p_user_id
 * @param UserPreferences $p_prefs
 * @param int $p_project_id
 * @return null
 */
function user_pref_set( $p_user_id, $p_prefs, $p_project_id = ALL_PROJECTS ) {
	if( user_pref_exists( $p_user_id, $p_project_id ) ) {
		return user_pref_update( $p_user_id, $p_project_id, $p_prefs );
	} else {
		return user_pref_insert( $p_user_id, $p_project_id, $p_prefs );
	}
}

