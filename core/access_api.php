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
		if( basename( $_SERVER['SCRIPT_NAME'] ) != 'login_page.php' ) {
			$t_return_page = $_SERVER['SCRIPT_NAME'];
			if( isset( $_SERVER['QUERY_STRING'] ) ) {
				$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
			}
			$t_return_page = string_url( string_sanitize_url( $t_return_page ) );
			print_header_redirect( 'login_page.php?return=' . $t_return_page );
		}
	} else {
		if( current_user_is_anonymous() ) {
			if( basename( $_SERVER['SCRIPT_NAME'] ) != 'login_page.php' ) {
				$t_return_page = $_SERVER['SCRIPT_NAME'];
				if( isset( $_SERVER['QUERY_STRING'] ) ) {
					$t_return_page .= '?' . $_SERVER['QUERY_STRING'];
				}
				$t_return_page = string_url( string_sanitize_url( $t_return_page ) );
				echo '<p class="center">' . error_string( ERROR_ACCESS_DENIED ) . '</p><p class="center">';
				print_link_button( helper_mantis_url( 'login_page.php' ) . '?return=' . $t_return_page, lang_get( 'click_to_login' ) );
				echo '</p><p class="center">';
				print_link_button(
					helper_mantis_url( config_get( 'default_home_page' ) ),
					lang_get( 'proceed' )
				);
				echo '</p>';
			}
		} else {
			layout_page_header();
			layout_admin_page_begin();
			echo '<div class="space-10"></div>';
			html_operation_failure(
				helper_mantis_url( config_get( 'default_home_page' ) ),
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
 * Check the current user's access against the given value, in each of the provided projects,
 * and return true if the user's access is equal to or higher in any of the projects, false otherwise.
 * @param integer|array|string  $p_access_level Parameter representing access level threshold, may be:
 *                                              - integer: for a simple threshold
 *                                              - array: for an array threshold
 *                                              - string: for a threshold option which will be evaluated
 *                                                 for each project context
 * @param array                 $p_project_ids  Array of project ids to check access against, default to null
 *                                               to use all user accesible projects
 * @param integer|null          $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access level specified
 * @access public
 */
function access_has_any_project_level( $p_access_level, array $p_project_ids = null, $p_user_id = null ) {
	# Short circuit the check in this case
	if( NOBODY == $p_access_level ) {
		return false;
	}

	if( null === $p_user_id ) {
		$p_user_id = auth_get_current_user_id();
	}
	if( null === $p_project_ids ) {
		$p_project_ids = user_get_all_accessible_projects( $p_user_id );
	}

	# if config will be evaluated for each project, prepare a default value
	if( is_string( $p_access_level ) ) {
		$t_default = config_get( $p_access_level, null, $p_user_id, ALL_PROJECTS );
		if( null === $t_default ) {
			$t_default = config_get_global( $p_access_level );
		}
	}

	$t_check_level = $p_access_level;
	$t_has_access = false;
	foreach( $p_project_ids as $t_project_id ) {
		# If a config string is provided, evaluate for each project
		if( is_string( $p_access_level ) ) {
			$t_check_level = config_get( $p_access_level, $t_default, $p_user_id, $t_project_id );
		}
		if( access_has_project_level( $t_check_level, $t_project_id, $p_user_id ) ) {
			$t_has_access = true;
			break;
		}
	}

	return $t_has_access;
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
 * @param integer      $p_access_level Integer representing access level.
 * @param integer|null $p_user_id      Integer representing user id, defaults to null to use current user.
 * @return boolean whether user has access level specified
 * @access public
 */
function access_has_any_project( $p_access_level, $p_user_id = null ) {
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

	$t_project_id = bug_get_field( $p_bug_id, 'project_id' );
	$t_bug_is_user_reporter = bug_is_user_reporter( $p_bug_id, $p_user_id );
	$t_access_level = access_get_project_level( $t_project_id, $p_user_id );

	# check limit_Reporter (Issue #4769)
	# reporters can view just issues they reported
	$t_limit_reporters = config_get( 'limit_reporters', null, $p_user_id, $t_project_id );
	if( $t_limit_reporters && !$t_bug_is_user_reporter ) {
		# Here we only need to check that the current user has an access level
		# higher than the lowest needed to report issues (report_bug_threshold).
		# To improve performance, esp. when processing for several projects, we
		# build a static array holding that threshold for each project
		static $s_thresholds = array();
		if( !isset( $s_thresholds[$t_project_id] ) ) {
			$t_report_bug_threshold = config_get( 'report_bug_threshold', null, $p_user_id, $t_project_id );
			if( empty( $t_report_bug_threshold ) ) {
				$s_thresholds[$t_project_id] = NOBODY;
			} else {
				$s_thresholds[$t_project_id] = access_threshold_min_level( $t_report_bug_threshold ) + 1;
			}
		}
		if( !access_compare_level( $t_access_level, $s_thresholds[$t_project_id] ) ) {
			return false;
		}
	}

	# If the bug is private and the user is not the reporter, then
	# they must also have higher access than private_bug_threshold
	if( !$t_bug_is_user_reporter && bug_get_field( $p_bug_id, 'view_state' ) == VS_PRIVATE ) {
		$t_private_bug_threshold = config_get( 'private_bug_threshold', null, $p_user_id, $t_project_id );
		return access_compare_level( $t_access_level, $t_private_bug_threshold )
			&& access_compare_level( $t_access_level, $p_access_level );
	}

	return access_compare_level( $t_access_level, $p_access_level );
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
