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
	$t_query = 'SELECT * FROM {project}';
	$t_result = db_query( $t_query, array(), 1 );

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
 */
function project_cache_row( $p_project_id, $p_trigger_errors = true ) {
	global $g_cache_project, $g_cache_project_missing;

	if( $p_project_id == ALL_PROJECTS ) {
		return false;
	}

	if( isset( $g_cache_project[(int)$p_project_id] ) ) {
		return $g_cache_project[(int)$p_project_id];
	} else if( isset( $g_cache_project_missing[(int)$p_project_id] ) ) {
		return false;
	}

	db_param_push();
	$t_query = 'SELECT * FROM {project} WHERE id=' . db_param();
	$t_result = db_query( $t_query, array( $p_project_id ) );

	if( 0 == db_num_rows( $t_result ) ) {
		$g_cache_project_missing[(int)$p_project_id] = true;

		if( $p_trigger_errors ) {
			error_parameters( $p_project_id );
			trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	$t_row = db_fetch_array( $t_result );

	$g_cache_project[(int)$p_project_id] = $t_row;

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
		if( !isset( $g_cache_project[(int)$t_project_id] ) && !isset( $g_cache_project_missing[(int)$t_project_id] ) ) {
			$c_project_id_array[] = (int)$t_project_id;
		}
	}

	if( empty( $c_project_id_array ) ) {
		return;
	}

	$t_query = 'SELECT * FROM {project} WHERE id IN (' . implode( ',', $c_project_id_array ) . ')';
	$t_result = db_query( $t_query );

	$t_projects_found = array();
	while( $t_row = db_fetch_array( $t_result ) ) {
		$g_cache_project[(int)$t_row['id']] = $t_row;
		$t_projects_found[(int)$t_row['id']] = true;
	}

	foreach ( $c_project_id_array as $c_project_id ) {
		if( !isset( $t_projects_found[$c_project_id] ) ) {
			$g_cache_project_missing[(int)$c_project_id] = true;
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
		$t_query = 'SELECT * FROM {project}';
		$t_result = db_query( $t_query );

		while( $t_row = db_fetch_array( $t_result ) ) {
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
 * @param string $p_name The project name.
 * @return boolean
 */
function project_is_name_unique( $p_name ) {
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {project} WHERE name=' . db_param();
	$t_result = db_query( $t_query, array( $p_name ) );

	if( 0 == db_result( $t_result ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * check to see if project exists by id
 * if it doesn't exist then error
 * otherwise let execution continue undisturbed
 * @param string $p_name The project name.
 * @return void
 */
function project_ensure_name_unique( $p_name ) {
	if( !project_is_name_unique( $p_name ) ) {
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
	db_param_push();
	$t_query = 'SELECT COUNT(*) FROM {project_user_list}
				  WHERE project_id=' . db_param() . ' AND
						user_id=' . db_param();
	$t_result = db_query( $t_query, array( $p_project_id, $p_user_id ) );

	if( 0 == db_result( $t_result ) ) {
		return false;
	} else {
		return true;
	}
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
		if( !strcmp( $p_file_path, config_get( 'absolute_path_default_upload_folder' ) ) ) {
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

	db_param_push();
	$t_query = 'INSERT INTO {project}
					( name, status, enabled, view_state, file_path, description, inherit_global )
				  VALUES
					( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';

	db_query( $t_query, array( $p_name, (int)$p_status, $c_enabled, (int)$p_view_state, $p_file_path, $p_description, $p_inherit_global ) );

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

	# Delete the records assigning users to this project
	project_remove_all_users( $p_project_id );

	# Delete all news entries associated with the project being deleted
	news_delete_all( $p_project_id );

	# Delete project specific configurations
	config_delete_project( $p_project_id );

	# Delete any user prefs that are project specific
	user_pref_delete_project( $p_project_id );

	# Delete the project entry
	db_param_push();
	$t_query = 'DELETE FROM {project} WHERE id=' . db_param();
	db_query( $t_query, array( $p_project_id ) );

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
		project_ensure_name_unique( $p_name );
	}

	if( DATABASE !== config_get( 'file_upload_method', null, null, $p_project_id ) ) {
		$p_file_path = validate_project_file_path( $p_file_path );
	}

	db_param_push();
	$t_query = 'UPDATE {project}
				  SET name=' . db_param() . ',
					status=' . db_param() . ',
					enabled=' . db_param() . ',
					view_state=' . db_param() . ',
					file_path=' . db_param() . ',
					description=' . db_param() . ',
					inherit_global=' . db_param() . '
				  WHERE id=' . db_param();
	db_query( $t_query, array( $p_name, (int)$p_status, $c_enabled, (int)$p_view_state, $p_file_path, $p_description, $c_inherit_global, $p_project_id ) );

	project_clear_cache( $p_project_id );

	# User just locked themselves out of the project by making it private,
	# so we add them to the project with their previous access level
	if( $t_is_becoming_private && !access_has_project_level( $t_manage_project_threshold, $p_project_id ) ) {
		project_add_user( $p_project_id, $t_user_id, $t_access_level );
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
 * @return integer
 */
function project_get_id_by_name( $p_project_name ) {
	db_param_push();
	$t_query = 'SELECT id FROM {project} WHERE name = ' . db_param();
	$t_result = db_query( $t_query, array( $p_project_name ), 1 );

	$t_id = db_result( $t_result );
	if( $t_id ) {
		return $t_id;
	} else {
		return 0;
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
 */
function project_get_local_user_access_level( $p_project_id, $p_user_id ) {
	$p_project_id = (int)$p_project_id;

	if( ALL_PROJECTS == $p_project_id ) {
		return false;
	}

	db_param_push();
	$t_query = 'SELECT access_level
				  FROM {project_user_list}
				  WHERE user_id=' . db_param() . ' AND project_id=' . db_param();
	$t_result = db_query( $t_query, array( (int)$p_user_id, $p_project_id ) );

	$t_level = db_result( $t_result );
	if( $t_level ) {
		return (int)$t_level;
	} else {
		return false;
	}
}

/**
 * return the descriptor holding all the info from the project user list
 * for the specified project
 * @param integer $p_project_id A project identifier.
 * @return array
 */
function project_get_local_user_rows( $p_project_id ) {
	db_param_push();
	$t_query = 'SELECT * FROM {project_user_list} WHERE project_id=' . db_param();
	$t_result = db_query( $t_query, array( (int)$p_project_id ) );

	$t_user_rows = array();
	$t_row_count = db_num_rows( $t_result );

	while( $t_row = db_fetch_array( $t_result ) ) {
		array_push( $t_user_rows, $t_row );
	}

	return $t_user_rows;
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

	if( is_array( $t_global_access_level ) ) {
		if( 0 == count( $t_global_access_level ) ) {
			$t_global_access_clause = '>= ' . NOBODY . ' ';
		} else if( 1 == count( $t_global_access_level ) ) {
			$t_global_access_clause = '= ' . array_shift( $t_global_access_level ) . ' ';
		} else {
			$t_global_access_clause = 'IN (' . implode( ',', $t_global_access_level ) . ')';
		}
	} else {
		$t_global_access_clause = '>= ' . $t_global_access_level . ' ';
	}

	if( $p_include_global_users ) {
		db_param_push();
		$t_query = 'SELECT id, username, realname, access_level
				FROM {user}
				WHERE enabled = ' . db_param() . '
					AND access_level ' . $t_global_access_clause;
		$t_result = db_query( $t_query, array( $t_on ) );

		while( $t_row = db_fetch_array( $t_result ) ) {
			$t_users[(int)$t_row['id']] = $t_row;
		}
	}

	if( $c_project_id != ALL_PROJECTS ) {
		# Get the project overrides
		db_param_push();
		$t_query = 'SELECT u.id, u.username, u.realname, l.access_level
				FROM {project_user_list} l, {user} u
				WHERE l.user_id = u.id
				AND u.enabled = ' . db_param() . '
				AND l.project_id = ' . db_param();
		$t_result = db_query( $t_query, array( $t_on, $c_project_id ) );

		while( $t_row = db_fetch_array( $t_result ) ) {
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
		$t_path = config_get( 'absolute_path_default_upload_folder', '', ALL_USERS, ALL_PROJECTS );
	} else {
		$t_path = project_get_field( $p_project_id, 'file_path' );
		if( is_blank( $t_path ) ) {
			$t_path = config_get( 'absolute_path_default_upload_folder', '', ALL_USERS, $p_project_id );
		}
	}

	return $t_path;
}

/**
 * add user with the specified access level to a project
 * @param integer $p_project_id   A project identifier.
 * @param integer $p_user_id      A valid user id identifier.
 * @param integer $p_access_level The access level to add the user with.
 * @return void
 */
function project_add_user( $p_project_id, $p_user_id, $p_access_level ) {
	$t_access_level = (int)$p_access_level;
	if( DEFAULT_ACCESS_LEVEL == $t_access_level ) {
		# Default access level for this user
		$t_access_level = user_get_access_level( $p_user_id );
	}

	db_param_push();
	$t_query = 'INSERT INTO {project_user_list}
				    ( project_id, user_id, access_level )
				  VALUES
				    ( ' . db_param() . ', ' . db_param() . ', ' . db_param() . ')';

	db_query( $t_query, array( (int)$p_project_id, (int)$p_user_id, $t_access_level ) );
}

/**
 * update entry
 * must make sure entry exists beforehand
 * @param integer $p_project_id   A project identifier.
 * @param integer $p_user_id      A user identifier.
 * @param integer $p_access_level Access level to set.
 * @return void
 */
function project_update_user_access( $p_project_id, $p_user_id, $p_access_level ) {
	db_param_push();
	$t_query = 'UPDATE {project_user_list}
				  SET access_level=' . db_param() . '
				  WHERE	project_id=' . db_param() . ' AND
						user_id=' . db_param();

	db_query( $t_query, array( (int)$p_access_level, (int)$p_project_id, (int)$p_user_id ) );
}

/**
 * update or add the entry as appropriate
 * This function involves one more database query than project_update_user_acces() or project_add_user()
 * @param integer $p_project_id   A project identifier.
 * @param integer $p_user_id      A user identifier.
 * @param integer $p_access_level Project Access level to grant the user.
 * @return boolean
 */
function project_set_user_access( $p_project_id, $p_user_id, $p_access_level ) {
	if( project_includes_user( $p_project_id, $p_user_id ) ) {
		return project_update_user_access( $p_project_id, $p_user_id, $p_access_level );
	} else {
		return project_add_user( $p_project_id, $p_user_id, $p_access_level );
	}
}

/**
 * remove user from project
 * @param integer $p_project_id A project identifier.
 * @param integer $p_user_id    A user identifier.
 * @return void
 */
function project_remove_user( $p_project_id, $p_user_id ) {
	db_param_push();
	$t_query = 'DELETE FROM {project_user_list}
				  WHERE project_id=' . db_param() . ' AND user_id=' . db_param();

	db_query( $t_query, array( (int)$p_project_id, (int)$p_user_id ) );
}

/**
 * Delete all users from the project user list for a given project. This is
 * useful when deleting or closing a project. The $p_access_level_limit
 * parameter can be used to only remove users from a project if their access
 * level is below or equal to the limit.
 * @param integer $p_project_id         A project identifier.
 * @param integer $p_access_level_limit Access level limit (null = no limit).
 * @return void
 */
function project_remove_all_users( $p_project_id, $p_access_level_limit = null ) {
	db_param_push();
	$t_query = 'DELETE FROM {project_user_list} WHERE project_id = ' . db_param();

	if( $p_access_level_limit !== null ) {
		$t_query .= ' AND access_level <= ' . db_param();
		db_query( $t_query, array( (int)$p_project_id, (int)$p_access_level_limit ) );
	} else {
		db_query( $t_query, array( (int)$p_project_id ) );
	}
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
 * Pads the project id with the appropriate number of zeros.
 * @param integer $p_project_id A project identifier.
 * @return string
 */
function project_format_id( $p_project_id ) {
	$t_padding = config_get( 'display_project_padding' );
	return( utf8_str_pad( $p_project_id, $t_padding, '0', STR_PAD_LEFT ) );
}
