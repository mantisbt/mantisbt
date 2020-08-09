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
 * Current User API
 *
 * @package CoreAPI
 * @subpackage CurrentUserAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'user_api.php' );
require_api( 'user_pref_api.php' );
require_api( 'utility_api.php' );

/**
 * Sets the current user
 *
 * @param integer $p_user_id Id to set as current user
 * @return integer Old current user id
 * @access public
 */
function current_user_set( $p_user_id ) {
	global $g_cache_current_user_id;
	global $g_cache_current_user_pref;

	$t_user_id = (int)$p_user_id;

	if( $t_user_id == $g_cache_current_user_id ) {
		return $t_user_id;
	}

	$t_old_current = $g_cache_current_user_id;
	$g_cache_current_user_id = $t_user_id;

	# Clear current user preferences cache
	$g_cache_current_user_pref = array();

	return $t_old_current;
}

# ## Current User API ###
# Wrappers around the User API that pass in the logged-in user for you
/**
 * Returns the access level of the current user in the current project
 *
 * @return int access level code
 * @access public
 */
function current_user_get_access_level() {
	return user_get_access_level( auth_get_current_user_id(), helper_get_current_project() );
}

/**
 * Returns the number of open issues that are assigned to the current user
 * in the current project.
 *
 * @return int Number of issues assigned to current user that are still open.
 * @access public
 */
function current_user_get_assigned_open_bug_count() {
	return user_get_assigned_open_bug_count( auth_get_current_user_id(), helper_get_current_project() );
}

/**
 * Returns the number of open reported bugs by the current user in
 * the current project
 *
 * @return int Number of issues reported by current user that are still open.
 * @access public
 */
function current_user_get_reported_open_bug_count() {
	return user_get_reported_open_bug_count( auth_get_current_user_id(), helper_get_current_project() );
}

/**
 * Returns the specified field of the currently logged in user
 *
 * @param string $p_field_name Name of user property as in the table definition.
 * @return mixed Get the value of the specified field for current user.
 * @access public
 */
function current_user_get_field( $p_field_name ) {
	return user_get_field( auth_get_current_user_id(), $p_field_name );
}

/**
 * Returns the specified field of the currently logged in user
 *
 * @param string $p_pref_name Name of user preference as in the preferences table definition.
 * @return mixed Get the value of the specified preference for current user.
 * @access public
 */
function current_user_get_pref( $p_pref_name ) {
	return user_pref_get_pref( auth_get_current_user_id(), $p_pref_name );
}

/**
 * Sets the specified preference for the current logged in user.
 *
 * @param string                 $p_pref_name  The name of the preference as in the preferences table.
 * @param boolean|integer|string $p_pref_value The preference new value.
 * @access public
 * @return boolean
 */
function current_user_set_pref( $p_pref_name, $p_pref_value ) {
	return user_pref_set_pref( auth_get_current_user_id(), $p_pref_name, $p_pref_value );
}

/**
 * Set Current Users Default project in preferences
 *
 * @param integer $p_project_id The new default project id.
 * @return void
 * @access public
 */
function current_user_set_default_project( $p_project_id ) {
	user_set_default_project( auth_get_current_user_id(), $p_project_id );
}

/**
 * Returns an array of projects that are accessible to the current logged in
 * user.
 *
 * @param boolean $p_show_disabled	Include disabled projects.
 * @return array an array of accessible project ids.
 * @access public
 */
function current_user_get_accessible_projects( $p_show_disabled = false ) {
	return user_get_accessible_projects( auth_get_current_user_id(), $p_show_disabled );
}

/**
 * Returns an array of subprojects of the specified project to which the
 * currently logged in user has access to.
 *
 * @param integer $p_project_id    Parent project id.
 * @param boolean $p_show_disabled Include disabled projects.
 * @return array an array of accessible sub-project ids.
 * @access public
 */
function current_user_get_accessible_subprojects( $p_project_id, $p_show_disabled = false ) {
	return user_get_accessible_subprojects( auth_get_current_user_id(), $p_project_id, $p_show_disabled );
}

/**
 * Returns an array of subprojects of the specified project to which the
 * currently logged in user has access, including subprojects of subprojects
 *
 * @param integer $p_project_id Parent project id.
 * @return array an array of accessible sub-project ids.
 * @access public
 */
function current_user_get_all_accessible_subprojects( $p_project_id ) {
	return user_get_all_accessible_subprojects( auth_get_current_user_id(), $p_project_id );
}

/**
 * Returns true if the currently logged in user is has a role of administrator
 * or higher, false otherwise
 *
 * @return boolean true: administrator; false: otherwise.
 * @access public
 */
function current_user_is_administrator() {
	return auth_is_user_authenticated() && user_is_administrator( auth_get_current_user_id() );
}

/**
 * Returns true if the current user is a protected user, false otherwise.
 * The $g_anonymous_account user is always considered protected.
 *
 * @return true: user is protected; false: otherwise.
 * @access public
 */
function current_user_is_protected() {
	return user_is_protected( auth_get_current_user_id() );
}

/**
 * Returns true if the current user is the anonymous user.
 *
 * @return true: user is anonymous; false: otherwise.
 * @access public
 */
function current_user_is_anonymous() {
	return user_is_anonymous( auth_get_current_user_id() );
}

/**
 * Triggers an ERROR if the current user account is protected.
 * The $g_anonymous_account user is always considered protected.
 *
 * @access public
 * @return void
 */
function current_user_ensure_unprotected() {
	user_ensure_unprotected( auth_get_current_user_id() );
}

/**
 * Returns the issue filter for the current user, which is retrieved by
 * evaluating these steps:
 * 1) Reads gpc vars for a token id, which means to load a temporary filter
 * 2) Otherwise, get the filter saved as current, for the user, project 
 *
 * @param integer $p_project_id Project id to get the user's filter from, if needed.
 * @return array	A filter array
 * @access public
 */
function current_user_get_bug_filter( $p_project_id = null ) {
	$f_tmp_key = gpc_get_string( 'filter', null );

	if( null !== $f_tmp_key ) {
		$t_filter = filter_temporary_get( $f_tmp_key, null );
		# if filter doesn't exist or can't be loaded, return a default filter (doesn't throw error)
		if( null === $t_filter ) {
			$t_filter = filter_get_default();
		}
	} else {
		$t_user_id = auth_get_current_user_id();
		$t_filter = user_get_bug_filter( $t_user_id, $p_project_id );
	}

	return $t_filter;
}

/**
 * Returns true if the user has access to more that one project
 *
 * @return boolean
 */
function current_user_has_more_than_one_project() {
	return user_has_more_than_one_project( auth_get_current_user_id() );
}

/**
 * Checks if the user has only one, or none, visible project and modify his
 * current and default project to be coherent.
 * - If current project is ALL_PROJECTS, sets the the visible project as current.
 * - If default project is ALL_PROJECTS, sets the visible project as his default
 *   project for future sessions.
 * - If default project is not the visible one, modify it to be that project,
 *   or ALL_PROJECTS if the user has no accessible projects.
 *
 * These changes only apply to users who can't use the project selection.
 *
 * @return void
 */
function current_user_modify_single_project_default() {
	# The user must not be able to use the project selector
	if( layout_navbar_can_show_projects_menu() ) {
		return;
	}
	# The user must have one, or none, projects
	$t_user_id = auth_get_current_user_id();
	if( user_has_more_than_one_project( $t_user_id ) ) {
		return;
	}
	$t_default = user_pref_get_pref( $t_user_id, 'default_project' );
	$t_current = helper_get_current_project();
	$t_projects = user_get_all_accessible_projects( $t_user_id );
	$t_count = count( $t_projects );

	if( 0 == $t_count ) {
		$t_project_id = ALL_PROJECTS;
	} else {
		$t_project_id = reset( $t_projects );
	}

	if( $t_project_id != $t_current ) {
		helper_set_current_project( $t_project_id );
	}
	if( $t_project_id != $t_default ) {
		user_pref_set_pref( $t_user_id, 'default_project', (int)$t_project_id, ALL_PROJECTS, false /* skip protected check */ );
	}
}