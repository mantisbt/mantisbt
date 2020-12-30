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
 * Access API
 *
 * @package CoreAPI
 * @subpackage AccessAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'bugnote_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

use Mantis\Exceptions\ClientException;

# @global array $g_cache_access_matrix
$g_cache_access_matrix = array();

# @global array $g_cache_access_matrix_project_ids
$g_cache_access_matrix_project_ids = array();

# @global array $g_cache_access_matrix_user_ids
$g_cache_access_matrix_user_ids = array();

/**
 * Function to be called when a user is attempting to access a page that
 * he/she is not authorised to.  This outputs an access denied message then
 * re-directs to the mainpage.
 *
 * @return void
 */
function access_denied() {
	if( !auth_is_user_authenticated() ) {
		if( basename( $_SERVER['SCRIPT_NAME'] ) != auth_login_page() ) {
			$t_return_page = $_SERVER['SCRIPT_NAME'];
			if( isset( $_SERVER['QUERY_STRING'] ) ) {
				$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
			}
			$t_return_page = string_url( string_sanitize_url( $t_return_page ) );
			print_header_redirect( auth_login_page( 'return=' . $t_return_page ) );
		}
	} else {
		if( current_user_is_anonymous() ) {
			if( basename( $_SERVER['SCRIPT_NAME'] ) != auth_login_page() ) {
				$t_return_page = $_SERVER['SCRIPT_NAME'];
				if( isset( $_SERVER['QUERY_STRING'] ) ) {
					$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
				}
				$t_return_page = string_url( string_sanitize_url( $t_return_page ) );
				echo '<p class="center">' . error_string( ERROR_ACCESS_DENIED ) . '</p><p class="center">';
				print_link_button( auth_login_page( 'return=' . $t_return_page ), lang_get( 'login' ) );
				echo '</p><p class="center">';
				print_link_button(
					helper_mantis_url( config_get_global( 'default_home_page' ) ),
					lang_get( 'proceed' )
				);
				echo '</p>';
			}
		} else {
			layout_page_header();
			layout_admin_page_begin();
			echo '<div class="space-10"></div>';
			html_operation_failure(
				helper_mantis_url( config_get_global( 'default_home_page' ) ),
				error_string( ERROR_ACCESS_DENIED )
			);
			layout_admin_page_end();
		}
	}
	exit;
}

/**
 * retrieves and returns access matrix for a project from cache or caching if required.
 * @param integer $p_project_id Integer representing project identifier.
 * @return array returns an array of users->accesslevel for the given user
 * @access private
 */
function access_cache_matrix_project( $p_project_id ) {
	global $g_cache_access_matrix, $g_cache_access_matrix_project_ids;

	if( ALL_PROJECTS == (int)$p_project_id ) {
		return array();
	}

	if( !in_array( (int)$p_project_id, $g_cache_access_matrix_project_ids ) ) {
		db_param_push();
		$t_query = 'SELECT user_id, access_level FROM {project_user_list} WHERE project_id=' . db_param();
		$t_result = db_query( $t_query, array( (int)$p_project_id ) );
		while( $t_row = db_fetch_array( $t_result ) ) {
			$g_cache_access_matrix[(int)$t_row['user_id']][(int)$p_project_id] = (int)$t_row['access_level'];
		}

		$g_cache_access_matrix_project_ids[] = (int)$p_project_id;
	}

	$t_results = array();

	foreach( $g_cache_access_matrix as $t_user ) {
		if( isset( $t_user[(int)$p_project_id] ) ) {
			$t_results[(int)$p_project_id] = $t_user[(int)$p_project_id];
		}
	}

	return $t_results;
}

/**
 * retrieves and returns access matrix for a user from cache or caching if required.
 * @param integer $p_user_id Integer representing user identifier.
 * @return array returns an array of projects->accesslevel for the given user
 * @access private
 */
function access_cache_matrix_user( $p_user_id ) {
	global $g_cache_access_matrix, $g_cache_access_matrix_user_ids;

	if( !in_array( (int)$p_user_id, $g_cache_access_matrix_user_ids ) ) {
		db_param_push();
		$t_query = 'SELECT project_id, access_level FROM {project_user_list} WHERE user_id=' . db_param();
		$t_result = db_query( $t_query, array( (int)$p_user_id ) );

		# make sure we always have an array to return
		$g_cache_access_matrix[(int)$p_user_id] = array();

		while( $t_row = db_fetch_array( $t_result ) ) {
			$g_cache_access_matrix[(int)$p_user_id][(int)$t_row['project_id']] = (int)$t_row['access_level'];
		}

		$g_cache_access_matrix_user_ids[] = (int)$p_user_id;
	}

	return $g_cache_access_matrix[(int)$p_user_id];
}

/**
 * Check the a user's access against the given "threshold" and return true
 * if the user can access, false otherwise.
 * $p_threshold may be a single value, or an array. If it is a single
 * value, treat it as a threshold so return true if user is >= threshold.
 * If it is an array, look for exact matches to one of the values
 * @param integer       $p_user_access_level User access level.
 * @param integer|array $p_threshold         Access threshold, defaults to NOBODY.
 * @return boolean true or false depending on whether given access level matches the threshold
 * @access public
 */
function access_compare_level( $p_user_access_level, $p_threshold = NOBODY ) {
	if( is_array( $p_threshold ) ) {
		return( in_array( $p_user_access_level, $p_threshold ) );
	} else {
		return( $p_user_access_level >= $p_threshold );
	}
}

/**
 * This function only checks the user's global access level, ignoring any
 * overrides they might have at a project level
 * @param integer|null $p_user_id Integer representing user identifier, defaults to null to use current user.
 * @return integer global access level
 * @access public
 */
function access_get_global_level( $p_user_id = null ) {
	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	# Deal with not logged in silently in this case
	# @@@ we may be able to remove this and just error
	#     and once we default to anon login, we can remove it for sure
	if( empty( $p_user_id ) && !auth_is_user_authenticated() ) {
		return false;
	}

	return user_get_field( $p_user_id, 'access_level' );
}

/**
 * Check the current user's access against the given value and return true
 * if the user's access is equal to or higher, false otherwise.
 * @param integer      $p_access_level Integer representing access level.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access level specified
 * @access public
 */
function access_has_global_level( $p_access_level, $p_user_id = null ) {
	# Short circuit the check in this case
	if( NOBODY == $p_access_level ) {
		return false;
	}

	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_access_level = access_get_global_level( $p_user_id );

	return access_compare_level( $t_access_level, $p_access_level );
}

/**
 * Check if the user has the specified global access level
 * and deny access to the page if not
 * @see access_has_global_level
 * @param integer      $p_access_level Integer representing access level.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @access public
 * @return void
 */
function access_ensure_global_level( $p_access_level, $p_user_id = null ) {
	if( !access_has_global_level( $p_access_level, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * This function checks the project access level first (for the current project
 * if none is specified) and if the user is not listed, it falls back on the
 * user's global access level.
 * @param integer      $p_project_id Integer representing project id to check access against.
 * @param integer|null $p_user_id    Integer representing user id, defaults to null to use current user.
 * @return integer access level user has to given project
 * @access public
 */
function access_get_project_level( $p_project_id = null, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	# Deal with not logged in silently in this case
	# @todo we may be able to remove this and just error and once we default to anon login, we can remove it for sure
	if( empty( $p_user_id ) && !auth_is_user_authenticated() ) {
		return ANYBODY;
	}

	if( null === $p_project_id ) {
		$p_project_id = helper_get_current_project();
	}

	$t_global_access_level = access_get_global_level( $p_user_id );

	if( ALL_PROJECTS == $p_project_id || user_is_administrator( $p_user_id ) ) {
		return $t_global_access_level;
	} else {
		$t_project_access_level = access_get_local_level( $p_user_id, $p_project_id );
		$t_project_view_state = project_get_field( $p_project_id, 'view_state' );

		# Try to use the project access level.
		# If the user is not listed in the project, then try to fall back
		#  to the global access level
		if( false === $t_project_access_level ) {
			# If the project is private and the user isn't listed, then they
			# must have the private_project_threshold access level to get in.
			if( VS_PRIVATE == $t_project_view_state ) {
				if( access_compare_level( $t_global_access_level, config_get( 'private_project_threshold', null, null, ALL_PROJECTS ) ) ) {
					return $t_global_access_level;
				} else {
					return ANYBODY;
				}
			} else {
				# project access not set, but the project is public
				return $t_global_access_level;
			}
		} else {
			# project specific access was set
			return $t_project_access_level;
		}
	}
}

/**
 * Check the current user's access against the given value and return true
 * if the user's access is equal to or higher, false otherwise.
 * @param integer|array $p_access_level Threshold representing an access level.
 * @param integer       $p_project_id   Integer representing project id to check access against.
 * @param integer|null  $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access level specified
 * @access public
 */
function access_has_project_level( $p_access_level, $p_project_id = null, $p_user_id = null ) {
	# Short circuit the check in this case
	if( NOBODY == $p_access_level ) {
		return false;
	}

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}
	if( null === $p_project_id ) {
		$p_project_id = helper_get_current_project();
	}

	$t_access_level = access_get_project_level( $p_project_id, $p_user_id );

	return access_compare_level( $t_access_level, $p_access_level );
}

/**
 * Filters an array of project ids, based on an access level, returning an array
 * containing only those projects which meet said access level.
 * An optional limit for the number of results is provided as a shortcut for access checks.
 *
 * @param integer|array|string  $p_access_level Parameter representing access level threshold, may be:
 *                                              - integer: for a simple threshold
 *                                              - array: for an array threshold
 *                                              - string: for a threshold option which will be evaluated
 *                                                 for each project context
 * @param array                 $p_project_ids  Array of project ids to check access against, default to null
 *                                               to use all user accessible projects
 * @param integer|null          $p_user_id      Integer representing user id, defaults to null to use current user.
 * @param integer               $p_limit        Maximum number of results, default is 0 for all results
 * @return array                The filtered array of project ids
 */
function access_project_array_filter( $p_access_level, array $p_project_ids = null, $p_user_id = null, $p_limit = 0 ) {
	# Short circuit the check in this case
	if( NOBODY == $p_access_level ) {
		return array();
	}

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}
	if( null === $p_project_ids ) {
		$p_project_ids = user_get_all_accessible_projects( $p_user_id );
	}

	# Determine if parameter is a configuration string to be evaluated for each project
	$t_is_config_string = ( is_string( $p_access_level ) && !is_numeric( $p_access_level ) );

	# if config will be evaluated for each project, prepare a default value
	if( $t_is_config_string ) {
		$t_default = config_get( $p_access_level, null, $p_user_id, ALL_PROJECTS );
		if( null === $t_default ) {
			$t_default = config_get_global( $p_access_level );
		}
	}

	$t_check_level = $p_access_level;
	$t_filtered_projects = array();
	foreach( $p_project_ids as $t_project_id ) {
		# If a config string is provided, evaluate for each project
		if( $t_is_config_string ) {
			$t_check_level = config_get( $p_access_level, $t_default, $p_user_id, $t_project_id );
		}
		if( access_has_project_level( $t_check_level, $t_project_id, $p_user_id ) ) {
			$t_filtered_projects[] = $t_project_id;
			# Shortcut if the result limit has been reached
			if( --$p_limit == 0 ) {
				break;
			}
		}
	}

	return $t_filtered_projects;
}

/**
 * Check the current user's access against the given value, in each of the provided projects,
 * and return true if the user's access is equal to or higher in any of the projects, false otherwise.
 * @param integer|array|string  $p_access_level Parameter representing access level threshold, may be:
 *                                              - integer: for a simple threshold
 *                                              - array: for an array threshold
 *                                              - string: for a threshold option which will be evaluated
 *                                                 for each project context
 * @param array                 $p_project_ids  Array of project ids to check access against, default to null
 *                                               to use all user accessible projects
 * @param integer|null          $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean              True if user has the specified access level for any of the projects
 * @access public
 */
function access_has_any_project_level( $p_access_level, array $p_project_ids = null, $p_user_id = null ) {
	# We only need 1 matching project to return positive
	$t_matches = access_project_array_filter( $p_access_level, $p_project_ids, $p_user_id, 1 );
	return !empty( $t_matches );
}

/**
 * Check if the user has the specified access level in any of the given projects
 * Refer to access_has_any_project_level() for detailed parameter information
 * @param integer|array|string $p_access_level  Parameter representing access level threshold
 * @param array $p_project_ids                  Array of project ids to check access against
 * @param integer|null $p_user_id
 */
function access_ensure_any_project_level( $p_access_level, array $p_project_ids = null, $p_user_id = null ) {
	if( !access_has_any_project_level( $p_access_level, $p_project_ids, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * Check if the user has the specified access level for the given project
 * and deny access to the page if not
 * @see access_has_project_level
 * @param integer      $p_access_level Integer representing access level.
 * @param integer|null $p_project_id   Integer representing project id to check access against, defaults to null to use current project.
 * @param integer|null $p_user_id      Integer representing user identifier, defaults to null to use current user.
 * @access public
 * @return void
 */
function access_ensure_project_level( $p_access_level, $p_project_id = null, $p_user_id = null ) {
	if( !access_has_project_level( $p_access_level, $p_project_id, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * Check whether the user has the specified access level for any project project
 *
 * Warning: this function may mislead into incorrect validations. Usually you want to
 * check that a user meets a threshold for any project, but that threshold may be configured
 * differently for each project, and the user may also have different access levels in each
 * project due to private projects assignment.
 * In that scenario, $p_access_level can't be a static threshold, but a "threshold identifier"
 * instead, that must be evaluated for each project.
 * Function "access_has_any_project_level()" provides that functionality, also covers the basic
 * usage of this function.
 * For such reasons, this function has been deprecated.
 *
 * @param integer      $p_access_level Integer representing access level.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access level specified
 * @access public
 * @deprecated	access_has_any_project_level() should be used in preference to this function (since verrsion 2.6)
 */
function access_has_any_project( $p_access_level, $p_user_id = null ) {
	error_parameters( __FUNCTION__ . '()', 'access_has_any_project_level()' );
	trigger_error( ERROR_DEPRECATED_SUPERSEDED, DEPRECATED );

	# Short circuit the check in this case
	if( NOBODY == $p_access_level ) {
		return false;
	}

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_projects = project_get_all_rows();
	foreach( $t_projects as $t_project ) {
		if( access_has_project_level( $p_access_level, $t_project['id'], $p_user_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check the current user's access against the given value and return true
 * if the user's access is equal to or higher, false otherwise.
 * This function looks up the bug's project and performs an access check
 * against that project
 * @param integer      $p_access_level Integer representing access level.
 * @param integer      $p_bug_id       Integer representing bug id to check access against.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access level specified
 * @access public
 */
function access_has_bug_level( $p_access_level, $p_bug_id, $p_user_id = null ) {
	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	# Deal with not logged in silently in this case
	# @@@ we may be able to remove this and just error
	#     and once we default to anon login, we can remove it for sure
	if( empty( $p_user_id ) && !auth_is_user_authenticated() ) {
		return false;
	}

	# Check the requested access level, shortcut to fail if not satisfied
	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
	$t_access_level = access_get_project_level( $t_project_id, $p_user_id );
	if( !access_compare_level( $t_access_level, $p_access_level ) ){
		return false;
	}

	# If the level is met, we still need to verify that user has access to the issue

	# Check if the bug is private
	$t_bug_is_user_reporter = bug_is_user_reporter( $p_bug_id, $p_user_id );
	if( !$t_bug_is_user_reporter && bug_get_field( $p_bug_id, 'view_state' ) == VS_PRIVATE ) {
		$t_private_bug_threshold = config_get( 'private_bug_threshold', null, $p_user_id, $t_project_id );
		if( !access_compare_level( $t_access_level, $t_private_bug_threshold ) ) {
			return false;
		}
	}

	# Check special limits
	# Limited view means this user can only view the issues they reported, is handling, or monitoring
	if( access_has_limited_view( $t_project_id, $p_user_id ) ) {
		$t_allowed = $t_bug_is_user_reporter;
		if( !$t_allowed ) {
			$t_allowed = bug_is_user_handler( $p_bug_id, $p_user_id );
		}
		if( !$t_allowed ) {
			$t_allowed = user_is_monitoring_bug( $p_user_id, $p_bug_id );
		}
		if( !$t_allowed ) {
			return false;
		}
	}

	return true;
}

/**
 * Filter the provided array of user ids to those who has the specified access level for the
 * specified bug.
 * @param  int $p_access_level   The access level.
 * @param  int $p_bug_id         The bug id.
 * @param  array $p_user_ids     The array of user ids.
 * @return array filtered array of user ids.
 */
function access_has_bug_level_filter( $p_access_level, $p_bug_id, $p_user_ids ) {
	$t_users_ids_with_access = array();
	foreach( $p_user_ids as $t_user_id ) {
		if( access_has_bug_level( $p_access_level, $p_bug_id, $t_user_id ) ) {
			$t_users_ids_with_access[] = $t_user_id;
		}
	}

	return $t_users_ids_with_access;
}

/**
 * Check if the user has the specified access level for the given bug
 * and deny access to the page if not
 * @see access_has_bug_level
 * @param integer      $p_access_level Integer representing access level.
 * @param integer      $p_bug_id       Integer representing bug id to check access against.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return void
 * @access public
 */
function access_ensure_bug_level( $p_access_level, $p_bug_id, $p_user_id = null ) {
	if( !access_has_bug_level( $p_access_level, $p_bug_id, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * Check the current user's access against the given value and return true
 * if the user's access is equal to or higher, false otherwise.
 * This function looks up the bugnote's bug and performs an access check
 * against that bug
 * @param integer      $p_access_level Integer representing access level.
 * @param integer      $p_bugnote_id   Integer representing bugnote id to check access against.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access level specified
 * @access public
 */
function access_has_bugnote_level( $p_access_level, $p_bugnote_id, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
	$t_project_id = bug_get_field( $t_bug_id, 'project_id' );

	# If the bug is private and the user is not the reporter, then the
	# the user must also have higher access than private_bug_threshold
	if( bugnote_get_field( $p_bugnote_id, 'view_state' ) == VS_PRIVATE && !bugnote_is_user_reporter( $p_bugnote_id, $p_user_id ) ) {
		$t_private_bugnote_threshold = config_get( 'private_bugnote_threshold', null, $p_user_id, $t_project_id );
		$p_access_level = max( $p_access_level, $t_private_bugnote_threshold );
	}

	return access_has_bug_level( $p_access_level, $t_bug_id, $p_user_id );
}

/**
 * Filter the provided array of user ids to those who has the specified access level for the
 * specified bugnote.
 * @param  int $p_access_level   The access level.
 * @param  int $p_bugnote_id     The bugnote id.
 * @param  array $p_user_ids     The array of user ids.
 * @return array filtered array of user ids.
 */
function access_has_bugnote_level_filter( $p_access_level, $p_bugnote_id, $p_user_ids ) {
	$t_users_ids_with_access = array();
	foreach( $p_user_ids as $t_user_id ) {
		if( access_has_bugnote_level( $p_access_level, $p_bugnote_id, $t_user_id ) ) {
			$t_users_ids_with_access[] = $t_user_id;
		}
	}

	return $t_users_ids_with_access;
}

/**
 * Check if the user has the specified access level for the given bugnote
 * and deny access to the page if not
 * @see access_has_bugnote_level
 * @param integer      $p_access_level Integer representing access level.
 * @param integer      $p_bugnote_id   Integer representing bugnote id to check access against.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @access public
 * @return void
 */
function access_ensure_bugnote_level( $p_access_level, $p_bugnote_id, $p_user_id = null ) {
	if( !access_has_bugnote_level( $p_access_level, $p_bugnote_id, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * Check if the specified bug can be closed
 * @param BugData      $p_bug     Bug to check access against.
 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
 * @return boolean true if user can close the bug
 * @access public
 */
function access_can_close_bug( BugData $p_bug, $p_user_id = null ) {
	if( bug_is_closed( $p_bug->id ) ) {
		# Can't close a bug that's already closed
		return false;
	}

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}

	# If allow_reporter_close is enabled, then reporters can close their own bugs
	# if they are in resolved status
	if( ON == config_get( 'allow_reporter_close', null, null, $p_bug->project_id )
		&& bug_is_user_reporter( $p_bug->id, $p_user_id )
		&& bug_is_resolved( $p_bug->id )
	) {
		return true;
	}

	$t_closed_status = config_get( 'bug_closed_status_threshold', null, null, $p_bug->project_id );
	$t_closed_status_threshold = access_get_status_threshold( $t_closed_status, $p_bug->project_id );
	return access_has_bug_level( $t_closed_status_threshold, $p_bug->id, $p_user_id );
}

/**
 * Make sure that the user can close the specified bug
 * @see access_can_close_bug
 * @param BugData      $p_bug     Bug to check access against.
 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
 * @access public
 * @return void
 */
function access_ensure_can_close_bug( BugData $p_bug, $p_user_id = null ) {
	if( !access_can_close_bug( $p_bug, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * Check if the specified bug can be reopened
 * @param BugData      $p_bug     Bug to check access against.
 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access to reopen bugs
 * @access public
 */
function access_can_reopen_bug( BugData $p_bug, $p_user_id = null ) {
	if( !bug_is_resolved( $p_bug->id ) ) {
		# Can't reopen a bug that's not resolved
		return false;
	}

	if( $p_user_id === null ) {
		$p_user_id = auth_get_current_user_id();
	}

	$t_reopen_status = config_get( 'bug_reopen_status', null, null, $p_bug->project_id );

	# Reopen status must be reachable by workflow
	if( !bug_check_workflow( $p_bug->status, $t_reopen_status ) ) {
		return false;
	}

	# If allow_reporter_reopen is enabled, then reporters can always reopen
	# their own bugs as long as their access level is reporter or above
	if( ON == config_get( 'allow_reporter_reopen', null, null, $p_bug->project_id )
		&& bug_is_user_reporter( $p_bug->id, $p_user_id )
		&& access_has_project_level( config_get( 'report_bug_threshold', null, $p_user_id, $p_bug->project_id ), $p_bug->project_id, $p_user_id )
	) {
		return true;
	}

	# Other users's access level must allow them to reopen bugs
	$t_reopen_bug_threshold = config_get( 'reopen_bug_threshold', null, null, $p_bug->project_id );
	if( access_has_bug_level( $t_reopen_bug_threshold, $p_bug->id, $p_user_id ) ) {

		# User must be allowed to change status to reopen status
		$t_reopen_status_threshold = access_get_status_threshold( $t_reopen_status, $p_bug->project_id );
		return access_has_bug_level( $t_reopen_status_threshold, $p_bug->id, $p_user_id );
	}

	return false;
}

/**
 * Make sure that the user can reopen the specified bug.
 * Calls access_denied if user has no access to terminate script
 * @see access_can_reopen_bug
 * @param BugData      $p_bug     Bug to check access against.
 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
 * @access public
 * @return void
 */
function access_ensure_can_reopen_bug( BugData $p_bug, $p_user_id = null ) {
	if( !access_can_reopen_bug( $p_bug, $p_user_id ) ) {
		access_denied();
	}
}

/**
 * get the user's access level specific to this project.
 * return false (0) if the user has no access override here
 * @param integer $p_user_id    Integer representing user id.
 * @param integer $p_project_id Integer representing project id.
 * @return boolean|integer returns false (if no access) or an integer representing level of access
 * @access public
 */
function access_get_local_level( $p_user_id, $p_project_id ) {
	global $g_cache_access_matrix, $g_cache_access_matrix_project_ids;

	$p_project_id = (int)$p_project_id;
	$p_user_id = (int)$p_user_id;

	if( in_array( $p_project_id, $g_cache_access_matrix_project_ids ) ) {
		if( isset( $g_cache_access_matrix[$p_user_id][$p_project_id] ) ) {
			return $g_cache_access_matrix[$p_user_id][$p_project_id];
		} else {
			return false;
		}
	}

	$t_project_level = access_cache_matrix_user( $p_user_id );

	if( isset( $t_project_level[$p_project_id] ) ) {
		return $t_project_level[$p_project_id];
	} else {
		return false;
	}
}

/**
 * get the access level required to change the issue to the new status
 * If there is no specific differentiated access level, use the
 * generic update_bug_status_threshold.
 * @param integer $p_status     Status.
 * @param integer $p_project_id Default value ALL_PROJECTS.
 * @return integer integer representing user level e.g. DEVELOPER
 * @access public
 */
function access_get_status_threshold( $p_status, $p_project_id = ALL_PROJECTS ) {
	$t_thresh_array = config_get( 'set_status_threshold', null, null, $p_project_id );
	if( isset( $t_thresh_array[(int)$p_status] ) ) {
		return (int)$t_thresh_array[(int)$p_status];
	} else {
		if( $p_status == config_get( 'bug_submit_status', null, null, $p_project_id ) ) {
			return config_get( 'report_bug_threshold', null, null, $p_project_id );
		} else {
			return config_get( 'update_bug_status_threshold', null, null, $p_project_id );
		}
	}
}

/**
 * Given a access level, return the appropriate string for it
 * @param integer $p_access_level
 * @return string
 */
function access_level_get_string( $p_access_level ) {
	if( $p_access_level > ANYBODY ) {
		$t_access_level_string = get_enum_element( 'access_levels', $p_access_level );
	} else {
		$t_access_level_string = lang_get( 'no_access' );
	}
	return $t_access_level_string;
}

/**
 * Return the minimum access level, as integer, that matches the threshold.
 * $p_threshold may be a single value, or an array. If it is a single
 * value, returns that number. If it is an array, return the value of the
 * smallest element
 * @param integer|array $p_threshold         Access threshold
 * @return integer		Integer value for an access level.
 */
function access_threshold_min_level( $p_threshold ) {
	if( is_array( $p_threshold ) ) {
		if( empty( $p_threshold ) ) {
			return NOBODY;
		} else {
			sort( $p_threshold );
			return( reset( $p_threshold ) );
		}
	} else {
		return $p_threshold;
	}

}

/**
 * Checks if the user can view the handler for the bug.
 * @param BugData      $p_bug     Bug to check access against.
 * @param integer|null $p_user_id Integer representing user id, defaults to null to use current user.
 * @return boolean whether user can view the handler user.
 */
function access_can_see_handler_for_bug( BugData $p_bug, $p_user_id = null ) {
	if( null === $p_user_id ) {
		$t_user_id = auth_get_current_user_id();
	} else {
		$t_user_id = $p_user_id;
	}

	# handler can be viewed if allowed by access level, OR the user himself is the handler
	$t_can_view_handler =
		( $p_bug->handler_id == $t_user_id )
		|| access_has_bug_level(
			config_get( 'view_handler_threshold', null, $t_user_id, $p_bug->project_id ),
			$p_bug->id );

	return $t_can_view_handler;
}

/**
 * Parse access level reference array parsed from json.
 *
 * @param array $p_access The access level
 * @return integer The access level
 * @throws ClientException Access level is invalid or not specified.
 */
function access_parse_array( array $p_access ) {
	$t_access_levels_enum = config_get( 'access_levels_enum_string' );
	$t_access_level = false;

	if( isset( $p_access['id'] ) ) {
		$t_access_level = (int)$p_access['id'];

		# Make sure the provided id is valid
		if( !MantisEnum::hasValue( $t_access_levels_enum, $t_access_level ) ) {
			$t_access_level = false;
		}
	}

	if( isset( $p_access['name'] ) ) {
		$t_access_level = MantisEnum::getValue( $t_access_levels_enum, $p_access['name'] );
	}

	if( $t_access_level === false ) {
		throw new ClientException(
			'Invalid access level',
			ERROR_INVALID_FIELD_VALUE,
			array( 'access_level' ) );
	}

	return $t_access_level;
}

/**
 * Returns true if the user has limited view to issues in the specified project.
 *
 * @param integer $p_project_id   Project id, or null for current project
 * @param integer $p_user_id      User id, or null for current user
 * @return boolean	Whether limited view applies
 *
 * @see $g_limit_view_unless_threshold
 * @see $g_limit_reporters
 */
function access_has_limited_view( $p_project_id = null, $p_user_id = null ) {
	$t_user_id = ( null === $p_user_id ) ? auth_get_current_user_id() : $p_user_id;
	$t_project_id = ( null === $p_project_id ) ? helper_get_current_project() : $p_project_id;

	# Old 'limit_reporters' option was previously only supported for ALL_PROJECTS,
	# Use this option if set, otherwise, check the new option for "unlimited view" threshold
	$t_old_limit_reporters = config_get( 'limit_reporters', null, $t_user_id, ALL_PROJECTS );
	$t_threshold_can_view = NOBODY;
	if( ON != $t_old_limit_reporters ) {
		$t_threshold_can_view = config_get( 'limit_view_unless_threshold', null, $t_user_id, $t_project_id );
	} else {
		# If old 'limit_reporters' option is enabled, use that setting
		# Note that the effective threshold can vary for each project, based on
		# the reporting threshold configuration.
		# To improve performance, esp. when processing for several projects, we
		# build a static array holding that threshold for each project
		static $s_thresholds = array();
		if( !isset( $s_thresholds[$t_project_id] ) ) {
			$t_report_bug_threshold = config_get( 'report_bug_threshold', null, $t_user_id, $t_project_id );
			if( empty( $t_report_bug_threshold ) ) {
				$s_thresholds[$t_project_id] = NOBODY;
			} else {
				$s_thresholds[$t_project_id] = access_threshold_min_level( $t_report_bug_threshold ) + 1;
			}
		}
		$t_threshold_can_view = $s_thresholds[$t_project_id];
	}

	$t_project_level = access_get_project_level( $p_project_id, $p_user_id );
	return !access_compare_level( $t_project_level, $t_threshold_can_view );
}

/**
 * Return true if user is allowed to view bug revisions.
 *
 * User must have $g_bug_revision_view_threshold or be the bug's reporter.
 *
 * @param int $p_bug_id
 * @param int $p_user_id
 *
 * @return bool
 */
function access_can_view_bug_revisions( $p_bug_id, $p_user_id = null ) {
	if( !bug_exists( $p_bug_id ) ) {
		return false;
	}
	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
	$t_user_id = null === $p_user_id ? auth_get_current_user_id() : $p_user_id;

	$t_has_access = access_has_bug_level(
		config_get( 'bug_revision_view_threshold', null, $t_user_id, $t_project_id ),
		$p_bug_id,
		$t_user_id
	);

	return $t_has_access || bug_is_user_reporter( $p_bug_id, $t_user_id );
}

/**
 * Return true if user is allowed to view bugnote revisions.
 *
 * User must have $g_bug_revision_view_threshold or be the bugnote's reporter.
 *
 * @param int $p_bugnote_id
 * @param int $p_user_id
 *
 * @return bool
 */
function access_can_view_bugnote_revisions( $p_bugnote_id, $p_user_id = null ) {
	if( !bugnote_exists( $p_bugnote_id ) ) {
		return false;
	}
	$t_bug_id = bugnote_get_field( $p_bugnote_id, 'bug_id' );
	$t_project_id = bug_get_field( $t_bug_id, 'project_id' );
	$t_user_id = null === $p_user_id ? auth_get_current_user_id() : $p_user_id;

	$t_has_access = access_has_bugnote_level(
		config_get( 'bug_revision_view_threshold', null, $t_user_id, $t_project_id ),
		$p_bugnote_id,
		$t_user_id
	);


	return $t_has_access || bugnote_is_user_reporter( $p_bugnote_id, $t_user_id );
}
