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
 * Project API
 *
 * @package CoreAPI
 * @subpackage ProjectAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses custom_field_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses file_api.php
 * @uses lang_api.php
 * @uses news_api.php
 * @uses project_hierarchy_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'custom_field_api.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'file_api.php' );
require_api( 'lang_api.php' );
require_api( 'news_api.php' );
require_api( 'project_hierarchy_api.php' );
require_api( 'user_api.php' );
require_api( 'user_pref_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

$g_cache_project = array();
$g_cache_project_missing = array();
$g_cache_project_all = false;

use Mantis\Exceptions\ClientException;

/**
 * Checks if there are no projects defined.
 * @return boolean true if there are no projects defined, false otherwise.
 * @access public
 */
function project_table_empty() {
	global $g_cache_project;

	# If projects already cached, use the cache.
	if( isset( $g_cache_project ) && count( $g_cache_project ) > 0 ) {
		return false;
	}

	# Otherwise, check if the projects table contains at least one project.
	$t_query = new DbQuery();
	$t_query->sql( 'SELECT * FROM {project}' );
	$t_query->set_limit( 1 );
	$t_result = $t_query->execute();

	return db_num_rows( $t_result ) == 0;
}

/**
 * Cache a project row if necessary and return the cached copy
 *  If the second parameter is true (default), trigger an error
 *  if the project can't be found.  If the second parameter is
 *  false, return false if the project can't be found.
 * @param integer $p_project_id     A project identifier.
 * @param boolean $p_trigger_errors Whether to trigger errors.
 * @return array|boolean
 * @throws ClientException
 */
function project_cache_row( $p_project_id, $p_trigger_errors = true ) {
	global $g_cache_project, $g_cache_project_missing;
	$c_project_id = (int)$p_project_id;

	if( $c_project_id == ALL_PROJECTS ) {
		return false;
	}


	if( isset( $g_cache_project[$c_project_id] ) ) {
		return $g_cache_project[$c_project_id];
	} else if( isset( $g_cache_project_missing[$c_project_id] ) ) {
		return false;
	}

	$t_query = new DbQuery();
	$t_query->sql( 'SELECT * FROM {project} WHERE id=' . $t_query->param( $p_project_id ) );
	$t_row = $t_query->fetch();

	if( $t_row === false ) {
		$g_cache_project_missing[$c_project_id] = true;

		if( $p_trigger_errors ) {
			throw new ClientException( "Project #$p_project_id not found", ERROR_PROJECT_NOT_FOUND, array( $p_project_id ) );
		}

		return false;
	}

	$g_cache_project[$c_project_id] = $t_row;
	return $t_row;
}

/**
 * Cache project data for array of project ids
 * @param array $p_project_id_array An array of project identifiers.
 * @return void
 */
function project_cache_array_rows( array $p_project_id_array ) {
	global $g_cache_project, $g_cache_project_missing;

	$c_project_id_array = array();
	foreach( $p_project_id_array as $t_project_id ) {
		$c_id = (int)$t_project_id;
		if( !isset( $g_cache_project[$c_id] ) && !isset( $g_cache_project_missing[$c_id] ) ) {
			$c_project_id_array[] = $c_id;
		}
	}

	if( empty( $c_project_id_array ) ) {
		return;
	}

	$t_query = new DbQuery();
	$t_query->sql(
		'SELECT * FROM {project} WHERE '
		. $t_query->sql_in( 'id', $c_project_id_array )
	);
	$t_query->execute();

	$t_projects_found = array();
	while( $t_row = $t_query->fetch() ) {
		$t_id = (int)$t_row['id'];
		$g_cache_project[$t_id] = $t_row;
		$t_projects_found[$t_id] = true;
	}

	foreach ( $c_project_id_array as $c_project_id ) {
		if( !isset( $t_projects_found[$c_project_id] ) ) {
			$g_cache_project_missing[$c_project_id] = true;
		}
	}
}

/**
 * Cache all project rows and return an array of them
 * @return array
 */
function project_cache_all() {
	global $g_cache_project, $g_cache_project_all;

	if( !$g_cache_project_all ) {
		$t_query = new DbQuery( 'SELECT * FROM {project}' );
		$t_query->execute();

		while( $t_row = $t_query->fetch() ) {
			$g_cache_project[(int)$t_row['id']] = $t_row;
		}

		$g_cache_project_all = true;
	}

	return $g_cache_project;
}

/**
 * Clear the project cache (or just the given id if specified)
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function project_clear_cache( $p_project_id = null ) {
	global $g_cache_project, $g_cache_project_missing, $g_cache_project_all;

	if( null === $p_project_id ) {
		$g_cache_project = array();
		$g_cache_project_missing = array();
		$g_cache_project_all = false;
	} else {
		unset( $g_cache_project[(int)$p_project_id] );
		unset( $g_cache_project_missing[(int)$p_project_id] );
		$g_cache_project_all = false;
	}
}

/**
 * Check if project is enabled.
 * @param integer $p_project_id The project id.
 * @return boolean
 */
function project_enabled( $p_project_id ) {
	return project_get_field( $p_project_id, 'enabled' ) ? true : false;
}

/**
 * check to see if project exists by id
 * return true if it does, false otherwise
 * @param integer $p_project_id A project identifier.
 * @return boolean
 */
function project_exists( $p_project_id ) {
	# we're making use of the caching function here.  If we succeed in caching the project then it exists and is
	# now cached for use by later function calls.  If we can't cache it we return false.
	if( false == project_cache_row( $p_project_id, false ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * check to see if project exists by id
 * if it does not exist then error
 * otherwise let execution continue undisturbed
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function project_ensure_exists( $p_project_id ) {
	if( !project_exists( $p_project_id ) ) {
		error_parameters( $p_project_id );
		trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
	}
}

/**
 * check to see if project exists by name
 * @param string  $p_name       The project name.
 * @param integer $p_exclude_id Optional project id to exclude from the check,
 *                              to allow uniqueness check when updating.
 * @return boolean
 */
function project_is_name_unique( $p_name, $p_exclude_id = null ) {
	$t_query = new DbQuery();
	$t_query->sql( 'SELECT COUNT(*) FROM {project} WHERE name=' . $t_query->param( $p_name ) );
	if( $p_exclude_id ) {
		$t_query->append_sql( ' AND id <> ' . $t_query->param( (int)$p_exclude_id ) );
	}
	$t_query->execute();

	return $t_query->value() == 0;
}

/**
 * check to see if project exists by id
 * if it doesn't exist then error
 * otherwise let execution continue undisturbed
 * @param string  $p_name       The project name.
 * @param integer $p_exclude_id Optional project id to exclude from the check,
 *                              to allow uniqueness check when updating.
 * @return void
 */
function project_ensure_name_unique( $p_name, $p_exclude_id = null ) {
	if( !project_is_name_unique( $p_name, $p_exclude_id ) ) {
		trigger_error( ERROR_PROJECT_NAME_NOT_UNIQUE, ERROR );
	}
}

/**
 * check to see if the user/project combo already exists
 * returns true is duplicate is found, otherwise false
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A user id identifier.
 * @return boolean
 */
function project_includes_user( $p_project_id, $p_user_id ) {
	$t_query = new DbQuery();
	$t_query->sql( 'SELECT COUNT(*) FROM {project_user_list}
		WHERE project_id=' . $t_query->param( $p_project_id ) . '
		AND user_id=' . $t_query->param( $p_user_id )
	);
	$t_query->execute();

	return $t_query->value() != 0;
}

/**
 * Make sure that the project file path is valid: add trailing slash and
 * set it to blank if equal to default path
 * @param string $p_file_path A file path.
 * @return string
 * @access public
 */
function validate_project_file_path( $p_file_path ) {
	if( !is_blank( $p_file_path ) ) {
		# Make sure file path has trailing slash
		$p_file_path = terminate_directory_path( $p_file_path );

		# If the provided path is the same as the default, make the path blank.
		# This means that if the default upload path is changed, you don't have
		# to update the upload path for every single project.
		if( !strcmp( $p_file_path, config_get_global( 'absolute_path_default_upload_folder' ) ) ) {
			$p_file_path = '';
		} else {
			file_ensure_valid_upload_path( $p_file_path );
		}
	}

	return $p_file_path;
}



/**
 * Create a new project
 * @param string  $p_name           The name of the project being created.
 * @param string  $p_description    A description for the project.
 * @param integer $p_status         The status of the project.
 * @param integer $p_view_state     The view state of the project - public or private.
 * @param string  $p_file_path      The attachment file path for the project, if not storing in the database.
 * @param boolean $p_enabled        Whether the project is enabled.
 * @param boolean $p_inherit_global Whether the project inherits global categories.
 * @return integer
 */
function project_create( $p_name, $p_description, $p_status, $p_view_state = VS_PUBLIC, $p_file_path = '', $p_enabled = true, $p_inherit_global = true ) {
	$c_enabled = (bool)$p_enabled;

	if( is_blank( $p_name ) ) {
		trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
	}

	project_ensure_name_unique( $p_name );

	# Project does not exist yet, so we get global config
	if( DATABASE !== config_get( 'file_upload_method', null, null, ALL_PROJECTS ) ) {
		$p_file_path = validate_project_file_path( $p_file_path );
	}

	$t_param = array(
		'name' => $p_name,
		'status' => (int)$p_status,
		'enabled' => $c_enabled,
		'view_state' => (int)$p_view_state,
		'file_path' => $p_file_path,
		'description' => $p_description,
		'inherit_global' => $p_inherit_global,
	);
	$t_query = new DbQuery( 'INSERT INTO {project}
		( ' . implode( ', ', array_keys( $t_param ) ) . ' )
		VALUES :param'
	);
	$t_query->bind( 'param', $t_param );
	$t_query->execute();

	# return the id of the new project
	return db_insert_id( db_get_table( 'project' ) );
}

/**
 * Delete a project
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function project_delete( $p_project_id ) {
	event_signal( 'EVENT_MANAGE_PROJECT_DELETE', array( $p_project_id ) );

	$t_email_notifications = config_get( 'enable_email_notification' );

	# temporarily disable all notifications
	config_set_cache( 'enable_email_notification', OFF, CONFIG_TYPE_INT );

	# Delete the bugs
	bug_delete_all( $p_project_id );

	# Delete associations with custom field definitions.
	custom_field_unlink_all( $p_project_id );

	# Delete the project categories
	category_remove_all( $p_project_id );

	# Delete the project versions
	version_remove_all( $p_project_id );

	# Delete relations to other projects
	project_hierarchy_remove_all( $p_project_id );

	# Delete the project files
	project_delete_all_files( $p_project_id );

	# Set default to ALL_PROJECTS for all users who had the project as default
	user_pref_clear_project_default( $p_project_id );

	# Delete the records assigning users to this project
	project_remove_all_users( $p_project_id );

	# Delete all news entries associated with the project being deleted
	news_delete_all( $p_project_id );

	# Delete project specific configurations
	config_delete_project( $p_project_id );

	# Delete any user prefs that are project specific
	user_pref_db_delete_project( $p_project_id );

	# Delete the project entry
	$t_query = new DbQuery( 'DELETE FROM {project} WHERE id=:project_id' );
	$t_query->bind( 'project_id', $p_project_id );
	$t_query->execute();

	config_set_cache( 'enable_email_notification', $t_email_notifications, CONFIG_TYPE_INT );

	project_clear_cache( $p_project_id );
}

/**
 * Update a project
 * @param integer $p_project_id     The project identifier being updated.
 * @param string  $p_name           The project name.
 * @param string  $p_description    A description of the project.
 * @param integer $p_status         The current status of the project.
 * @param integer $p_view_state     The view state of the project - public or private.
 * @param string  $p_file_path      The attachment file path for the project, if not storing in the database.
 * @param boolean $p_enabled        Whether the project is enabled.
 * @param boolean $p_inherit_global Whether the project inherits global categories.
 * @return void
 */
function project_update( $p_project_id, $p_name, $p_description, $p_status, $p_view_state, $p_file_path, $p_enabled, $p_inherit_global ) {
	$p_project_id = (int)$p_project_id;
	$c_enabled = (bool)$p_enabled;
	$c_inherit_global = (bool)$p_inherit_global;

	if( is_blank( $p_name ) ) {
		trigger_error( ERROR_PROJECT_NAME_INVALID, ERROR );
	}

	$t_old_name = project_get_field( $p_project_id, 'name' );

	# If project is becoming private, save current user's access level
	# so we can add them to the project afterwards so they don't lock
	# themselves out
	$t_old_view_state = project_get_field( $p_project_id, 'view_state' );
	$t_is_becoming_private = VS_PRIVATE == $p_view_state && VS_PRIVATE != $t_old_view_state;
	if( $t_is_becoming_private ) {
		$t_user_id = auth_get_current_user_id();
		$t_access_level = user_get_access_level( $t_user_id, $p_project_id );
		$t_manage_project_threshold = config_get( 'manage_project_threshold' );
	}

	if( strcasecmp( $p_name, $t_old_name ) != 0 ) {
		project_ensure_name_unique( $p_name, $p_project_id );
	}

	if( DATABASE !== config_get( 'file_upload_method', null, null, $p_project_id ) ) {
		$p_file_path = validate_project_file_path( $p_file_path );
	}

	$t_param = array(
		'name' => $p_name,
		'status' => (int)$p_status,
		'enabled' => $c_enabled,
		'view_state' => (int)$p_view_state,
		'file_path' => $p_file_path,
		'description' => $p_description,
		'inherit_global' => $c_inherit_global,
	);
	$t_columns = '';
	foreach( array_keys( $t_param ) as $t_col ) {
		$t_columns .= "\n\t\t$t_col = :$t_col,";
	}

	$t_param['project_id'] = $p_project_id;
	$t_query = new DbQuery( 'UPDATE {project} SET'
		. rtrim( $t_columns, ',' ) . '
		WHERE id = :project_id'
	);
	$t_query->execute( $t_param );

	project_clear_cache( $p_project_id );

	# User just locked themselves out of the project by making it private,
	# so we add them to the project with their previous access level
	if( $t_is_becoming_private && !access_has_project_level( $t_manage_project_threshold, $p_project_id ) ) {
		project_add_user( $p_project_id, $t_user_id, $t_access_level );
	}

	if( $t_is_becoming_private ) {
		user_pref_clear_invalid_project_default( $p_project_id );
	}
}

/**
 * Copy custom fields
 * @param integer $p_destination_id The destination project identifier.
 * @param integer $p_source_id      The source project identifier.
 * @return void
 */
function project_copy_custom_fields( $p_destination_id, $p_source_id ) {
	$t_custom_field_ids = custom_field_get_linked_ids( $p_source_id );
	foreach( $t_custom_field_ids as $t_custom_field_id ) {
		if( !custom_field_is_linked( $t_custom_field_id, $p_destination_id ) ) {
			custom_field_link( $t_custom_field_id, $p_destination_id );
			$t_sequence = custom_field_get_sequence( $t_custom_field_id, $p_source_id );
			custom_field_set_sequence( $t_custom_field_id, $p_destination_id, $t_sequence );
		}
	}
}

/**
 * Get the id of the project with the specified name
 * @param string $p_project_name Project name to retrieve.
 * @param integer|boolean $p_default The default value or false if the default should not be applied.
 * @return null|integer
 */
function project_get_id_by_name( $p_project_name, $p_default = ALL_PROJECTS ) {
	$t_query = new DbQuery();
	$t_query->sql(
		'SELECT id FROM {project} WHERE name = '
		. $t_query->param( $p_project_name )
	);
	$t_id = $t_query->value();
	if( $t_id ) {
		return $t_id;
	} elseif( $p_default === false ) {
		return null;
	} else {
		return $p_default;
	}
}

/**
 * Return the row describing the given project
 * @param integer $p_project_id     A project identifier.
 * @param boolean $p_trigger_errors Whether to trigger errors.
 * @return array
 */
function project_get_row( $p_project_id, $p_trigger_errors = true ) {
	return project_cache_row( $p_project_id, $p_trigger_errors );
}

/**
 * Return all rows describing all projects
 * @return array
 */
function project_get_all_rows() {
	return project_cache_all();
}

/**
 * Return the specified field of the specified project
 * @param integer $p_project_id     A project identifier.
 * @param string  $p_field_name     The field name to retrieve.
 * @param boolean $p_trigger_errors Whether to trigger errors.
 * @return string
 */
function project_get_field( $p_project_id, $p_field_name, $p_trigger_errors = true ) {
	$t_row = project_get_row( $p_project_id, $p_trigger_errors );

	if( isset( $t_row[$p_field_name] ) ) {
		return $t_row[$p_field_name];
	} else if( $p_trigger_errors ) {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
	}

	return '';
}

/**
 * Return the name of the project
 * Handles ALL_PROJECTS by returning the internationalized string for All Projects
 * @param integer $p_project_id     A project identifier.
 * @param boolean $p_trigger_errors Whether to trigger errors.
 * @return string
 */
function project_get_name( $p_project_id, $p_trigger_errors = true ) {
	if( ALL_PROJECTS == $p_project_id ) {
		return lang_get( 'all_projects' );
	} else {
		return project_get_field( $p_project_id, 'name', $p_trigger_errors );
	}
}

/**
 * Return the user's local (overridden) access level on the project or false
 *  if the user is not listed on the project
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A user identifier.
 * @return integer
 * @deprecated     access_get_local_level() should be used in preference to this function
 *                 This function has been deprecated in version 2.6
 */
function project_get_local_user_access_level( $p_project_id, $p_user_id ) {
	error_parameters( __FUNCTION__ . '()', 'access_get_local_level()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );
	return access_get_local_level( $p_user_id, $p_project_id );
}

/**
 * return the descriptor holding all the info from the project user list
 * for the specified project
 * @param integer $p_project_id A project identifier.
 * @return array
 */
function project_get_local_user_rows( $p_project_id ) {
	$t_query = new DbQuery();
	$t_query->sql(
		'SELECT * FROM {project_user_list} WHERE project_id='
		. $t_query->param( (int)$p_project_id )
	);
	return $t_query->fetch_all();
}

/**
 * Return an array of info about users who have access to the the given project
 * For each user we have 'id', 'username', and 'access_level' (overall access level)
 * If the second parameter is given, return only users with an access level
 * higher than the given value.
 * if the first parameter is given as 'ALL_PROJECTS', return the global access level (without
 * any reference to the specific project
 * @param integer $p_project_id           A project identifier.
 * @param integer $p_access_level         Access level.
 * @param boolean $p_include_global_users Whether to include global users.
 * @return array List of users, array key is user ID
 */
function project_get_all_user_rows( $p_project_id = ALL_PROJECTS, $p_access_level = ANYBODY, $p_include_global_users = true ) {
	$c_project_id = (int)$p_project_id;

	# Optimization when access_level is NOBODY
	if( NOBODY == $p_access_level ) {
		return array();
	}

	$t_on = ON;
	$t_users = array();

	$t_global_access_level = $p_access_level;
	if( $c_project_id != ALL_PROJECTS && $p_include_global_users ) {

		# looking for specific project
		if( VS_PRIVATE == project_get_field( $p_project_id, 'view_state' ) ) {
			# @todo (thraxisp) this is probably more complex than it needs to be
			# When a new project is created, those who meet 'private_project_threshold' are added
			# automatically, but don't have an entry in project_user_list_table.
			#  if they did, you would not have to add global levels.
			$t_private_project_threshold = config_get( 'private_project_threshold' );
			if( is_array( $t_private_project_threshold ) ) {
				if( is_array( $p_access_level ) ) {
					# both private threshold and request are arrays, use intersection
					$t_global_access_level = array_intersect( $p_access_level, $t_private_project_threshold );
				} else {
					# private threshold is an array, but request is a number, use values in threshold higher than request
					$t_global_access_level = array();
					foreach( $t_private_project_threshold as $t_threshold ) {
						if( $p_access_level <= $t_threshold ) {
							$t_global_access_level[] = $t_threshold;
						}
					}
				}
			} else {
				if( is_array( $p_access_level ) ) {
					# private threshold is a number, but request is an array, use values in request higher than threshold
					$t_global_access_level = array();
					foreach( $p_access_level as $t_threshold ) {
						if( $t_threshold >= $t_private_project_threshold ) {
							$t_global_access_level[] = $t_threshold;
						}
					}
				} else {
					# both private threshold and request are numbers, use maximum
					$t_global_access_level = max( $p_access_level, $t_private_project_threshold );
				}
			}
		}
	}

	if( $p_include_global_users ) {
		$t_query = new DbQuery();
		$t_query->sql( 'SELECT id, username, realname, access_level
			FROM {user}
			WHERE enabled = ' . $t_query->param( $t_on ) . ' 
				AND '
		);
		if( is_array( $t_global_access_level ) ) {
			if( empty( $t_global_access_level ) ) {
				$t_query->append_sql( 'access_level >= ' . $t_query->param( NOBODY ) );
			} else {
				$t_query->append_sql( $t_query->sql_in( 'access_level', $t_global_access_level ) );
			}
		} else {
			$t_query->append_sql( 'access_level >= ' . $t_query->param( $t_global_access_level ) );
		}
		$t_query->execute();

		while( $t_row = $t_query->fetch() ) {
			$t_users[(int)$t_row['id']] = $t_row;
		}
	}

	if( $c_project_id != ALL_PROJECTS ) {
		# Get the project overrides
		$t_query = new DbQuery();
		$t_query->sql( 'SELECT u.id, u.username, u.realname, l.access_level
			FROM {project_user_list} l, {user} u
			WHERE l.user_id = u.id
			AND u.enabled = ' . $t_query->param( $t_on ) . '
			AND l.project_id = ' . $t_query->param( $c_project_id )
		);
		$t_query->execute();

		while( $t_row = $t_query->fetch() ) {
			if( is_array( $p_access_level ) ) {
				$t_keep = in_array( $t_row['access_level'], $p_access_level );
			} else {
				$t_keep = $t_row['access_level'] >= $p_access_level;
			}

			if( $t_keep ) {
				$t_users[(int)$t_row['id']] = $t_row;
			} else {
				# If user's overridden level is lower than required, so remove
				#  them from the list if they were previously there
				unset( $t_users[(int)$t_row['id']] );
			}
		}
	}

	return $t_users;
}

/**
 * Returns the upload path for the specified project, empty string if
 * file_upload_method is DATABASE
 * @param integer $p_project_id A project identifier.
 * @return string upload path
 */
function project_get_upload_path( $p_project_id ) {
	if( DATABASE == config_get( 'file_upload_method', null, ALL_USERS, $p_project_id ) ) {
		return '';
	}

	if( $p_project_id == ALL_PROJECTS ) {
		$t_path = config_get_global( 'absolute_path_default_upload_folder', '' );
	} else {
		$t_path = project_get_field( $p_project_id, 'file_path' );
		if( is_blank( $t_path ) ) {
			$t_path = config_get_global( 'absolute_path_default_upload_folder', '' );
		}
	}

	return $t_path;
}

/**
 * Add user with the specified access level to a project.
 * @param integer $p_project_id   A project identifier.
 * @param integer $p_user_id      A valid user id identifier.
 * @param integer $p_access_level The access level to add the user with.
 * @return void
 */
function project_add_user( $p_project_id, $p_user_id, $p_access_level ) {
	project_add_users( $p_project_id, array( $p_user_id => $p_access_level ) );
}

/**
 * Update user with the specified access level to a project.
 * @param integer $p_project_id   A project identifier.
 * @param integer $p_user_id      A user identifier.
 * @param integer $p_access_level Access level to set.
 * @return void
 */
function project_update_user_access( $p_project_id, $p_user_id, $p_access_level ) {
	project_add_users( $p_project_id, array( $p_user_id => $p_access_level ) );
}

/**
 * Update or add user with the specified access level to a project.
 * This function involves one more database query than project_update_user_acces() or project_add_user().
 * @param integer $p_project_id   A project identifier.
 * @param integer $p_user_id      A user identifier.
 * @param integer $p_access_level Project Access level to grant the user.
 * @return void
 */
function project_set_user_access( $p_project_id, $p_user_id, $p_access_level ) {
	project_add_users( $p_project_id, array( $p_user_id => $p_access_level ) );
}

/**
 * Add or modify multiple users associated to a project with a specific access level.
 * $p_changes is an array of access levels indexed by user_id, such as:
 *   array ( user1 => access_level, user2 => access_level, ... )
 * This function will manage inserts and updates as needed.
 *
 * @param integer $p_project_id   A project identifier.
 * @param array $p_changes        An array of modifications.
 * @return void
 */
function project_add_users( $p_project_id, array $p_changes ) {
	# normalize input
	$t_changes = array();
	foreach( $p_changes as $t_id => $t_value ) {
		if( DEFAULT_ACCESS_LEVEL == $t_value ) {
			$t_changes[(int)$t_id] = user_get_access_level( $t_id );
		} else {
			$t_changes[(int)$t_id] = (int)$t_value;
		}
	}

	$t_user_ids = array_keys( $t_changes );
	if( empty( $t_user_ids ) ) {
		return;
	}

	$t_project_id = (int)$p_project_id;
	$t_query = new DbQuery();
	$t_sql = 'SELECT user_id FROM {project_user_list} 
		WHERE project_id = ' . $t_query->param( $t_project_id ) . ' 
		AND ' . $t_query->sql_in( 'user_id', $t_user_ids );
	$t_query->sql( $t_sql );
	$t_updating = array_column( $t_query->fetch_all(), 'user_id' );

	if( !empty( $t_updating ) ) {
		$t_update = new DbQuery( 'UPDATE {project_user_list} 
			SET access_level = :new_value 
			WHERE user_id = :user_id AND project_id = :project_id'
		);
		foreach( $t_updating as $t_id ) {
			$t_params = array(
				'project_id' => $t_project_id,
				'user_id' => (int)$t_id,
				'new_value' => $t_changes[$t_id]
			);
			$t_update->execute( $t_params );
			unset( $t_changes[$t_id] );
		}
	}
	# remaining items are for insert
	if( !empty( $t_changes ) ) {
		$t_insert = new DbQuery( 'INSERT INTO {project_user_list} 
			( project_id, user_id, access_level ) 
			VALUES :params'
		);
		foreach( $t_changes as $t_id => $t_value ) {
			$t_insert->bind( 'params', array( $t_project_id, $t_id, $t_value ) );
			$t_insert->execute();
		}
	}
}

/**
 * Remove user from project.
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A user identifier.
 * @return void
 */
function project_remove_user( $p_project_id, $p_user_id ) {
	project_remove_users( $p_project_id, array( $p_user_id ) );
}

/**
 * Remove multiple users from project.
 *
 * The user's default_project preference will be set to ALL_PROJECTS if they
 * no longer have access to the project.

 * @param integer $p_project_id  A project identifier.
 * @param array $p_user_ids      Array of user identifiers.
 * @return void
 */
function project_remove_users( $p_project_id, array $p_user_ids ) {
	# normalize input
	$t_user_ids = array();
	foreach( $p_user_ids as $t_id ) {
		$t_user_ids[] = (int)$t_id;
	}
	if( empty( $t_user_ids ) ) {
		return;
	}

	# Remove users from the project
	$t_query = new DbQuery();
	$t_sql = 'DELETE FROM {project_user_list}'
		. ' WHERE project_id = ' . $t_query->param( (int)$p_project_id )
		. ' AND ' . $t_query->sql_in( 'user_id', $t_user_ids );
	$t_query->sql( $t_sql );
	$t_query->execute();

	user_pref_clear_invalid_project_default( $p_project_id );
}

/**
 * Delete all users from the project user list for a given project.
 *
 * This is useful when deleting or closing a project. The $p_access_level_limit
 * parameter can be used to only remove users from a project if their access
 * level is below or equal to the limit.
 *
 * The user's default_project preference will be set to ALL_PROJECTS if they
 * no longer have access to the project.
 *
 * @param integer $p_project_id         A project identifier.
 * @param integer $p_access_level_limit Access level limit (null = no limit).
 * @return void
 */
function project_remove_all_users( $p_project_id, $p_access_level_limit = null ) {
	$t_query = new DbQuery();
	$t_sql = 'DELETE FROM {project_user_list} '
		. 'WHERE project_id = ' . $t_query->param( (int)$p_project_id );
	if( $p_access_level_limit !== null ) {
		$t_sql .= ' AND access_level <= ' . $t_query->param( (int)$p_access_level_limit );
	}
	$t_query->sql( $t_sql );
	$t_query->execute();
}

/**
 * Copy all users and their permissions from the source project to the
 * destination project. The $p_access_level_limit parameter can be used to
 * limit the access level for users as they're copied to the destination
 * project (the highest access level they'll receive in the destination
 * project will be equal to $p_access_level_limit).
 * @param integer $p_destination_id     The destination project identifier.
 * @param integer $p_source_id          The source project identifier.
 * @param integer $p_access_level_limit Access level limit (null = no limit).
 * @return void
 */
function project_copy_users( $p_destination_id, $p_source_id, $p_access_level_limit = null ) {
	# Copy all users from current project over to another project
	$t_rows = project_get_local_user_rows( $p_source_id );

	$t_count = count( $t_rows );
	for( $i = 0; $i < $t_count; $i++ ) {
		$t_row = $t_rows[$i];

		if( $p_access_level_limit !== null &&
			$t_row['access_level'] > $p_access_level_limit ) {
			$t_destination_access_level = $p_access_level_limit;
		} else {
			$t_destination_access_level = $t_row['access_level'];
		}

		# if there is no duplicate then add a new entry
		# otherwise just update the access level for the existing entry
		if( project_includes_user( $p_destination_id, $t_row['user_id'] ) ) {
			project_update_user_access( $p_destination_id, $t_row['user_id'], $t_destination_access_level );
		} else {
			project_add_user( $p_destination_id, $t_row['user_id'], $t_destination_access_level );
		}
	}
}

/**
 * Delete all files associated with a project
 * @param integer $p_project_id A project identifier.
 * @return void
 */
function project_delete_all_files( $p_project_id ) {
	file_delete_project_files( $p_project_id );
}

/**
 * Returns the project name as a link formatted for display in menus and buttons.
 *
 * The link is formatted as a link to set_project.php, which can be used to
 * display project selection menus:
 * - projects list in navbar {@see layout_navbar_projects_menu()}
 * - project menu bar {@see print_project_menu_bar()}
 *
 * @param integer $p_project_id Project Id to display
 * @param bool    $p_active     True if it's the currently active project
 * @param string  $p_class      CSS classes to apply
 * @param array   $p_parents    Array of parent projects (empty if top-level)
 * @param string  $p_indent     String to use to indent the subprojects
 *
 * @return string Fully formatted HTML link to the project
 */
function project_link_for_menu( $p_project_id, $p_active = false, $p_class = '', array $p_parents = array(), $p_indent = '' ) {
	if( $p_parents ) {
		$t_full_id = implode( ";", $p_parents ) . ';' . $p_project_id;
		$t_indent = str_repeat( $p_indent, count( $p_parents ) ) . '&nbsp;';
	} else {
		$t_full_id = $p_project_id;
		$t_indent = '';
	}

	$t_url = helper_mantis_url( 'set_project.php?project_id=' . $t_full_id );
	$t_label = $t_indent . string_html_specialchars( project_get_name( $p_project_id ) );

	if( $p_active ) {
		$p_class .= ' active';
	}

	return sprintf('<a class="%s" href="%s">%s</a>', $p_class, $t_url, $t_label );
}

/**
 * Returns the number of issues associated with the given Project.
 *
 * @param int $p_project_id A project identifier.
 *
 * @return int
 */
function project_get_bug_count( $p_project_id ) {
	$t_query = new DbQuery();
	$t_query->sql( 'SELECT COUNT(*) FROM {bug} WHERE project_id='
		. $t_query->param( $p_project_id )
	);
	$t_query->execute();
	return $t_query->value();
}
