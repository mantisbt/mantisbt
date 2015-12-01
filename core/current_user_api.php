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
 * @uses tokens_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'tokens_api.php' );
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

	if( $p_user_id == $g_cache_current_user_id ) {
		return $p_user_id;
	}

	$t_old_current = $g_cache_current_user_id;
	$g_cache_current_user_id = $p_user_id;

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
	return user_is_administrator( auth_get_current_user_id() );
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
 * Returns the issue filter parameters for the current user
 *
 * @param integer $p_project_id Project id. This argument is only used if a 'filter' string is not passed via the web request.
 *                              The default value is null meaning return the current filter for user's current project
                                if a filter string is not supplied.
 * @return array User filter, if not set, then default filter.
 * @access public
 */
function current_user_get_bug_filter( $p_project_id = null ) {
	$f_filter_string = gpc_get_string( 'filter', '' );
	$t_filter = '';

	if( !is_blank( $f_filter_string ) ) {
		if( is_numeric( $f_filter_string ) ) {
			$t_token = token_get_value( TOKEN_FILTER );
			if( null != $t_token ) {
				$t_filter = json_decode( $t_token, true );
			}
		} else {
			$t_filter = json_decode( $f_filter_string, true );
		}
		$t_filter = filter_ensure_valid_filter( $t_filter );
	} else if( !filter_is_cookie_valid() ) {
		$t_filter = filter_get_default();
	} else {
		$t_user_id = auth_get_current_user_id();
		$t_filter = user_get_bug_filter( $t_user_id, $p_project_id );
	}

	return $t_filter;
}
