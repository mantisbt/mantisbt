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
 * Manage configuration for workflow Config
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'manage_config_workflow_set' );

auth_reauthenticate();

/**
 * Retrieves the value of configuration option for the project's parent
 * (ALL_PROJECTS level if project, or file-level if all projects)
 * @param integer $p_project Project.
 * @param string  $p_option  Configuration option to retrieve.
 * @return mixed configuration option value
 */
function config_get_parent( $p_project, $p_option ) {
	if( $p_project == ALL_PROJECTS ) {
		return config_get_global( $p_option );
	} else {
		return config_get( $p_option, null, null, ALL_PROJECTS );
	}
}

/**
 * Retrieves the access level needed to change the configuration option in the
 * project's parent (ALL_PROJECTS level if project, or file-level if all projects)
 * @param integer $p_project Project.
 * @param string  $p_option  Configuration option to retrieve.
 * @return integer access level
 */
function config_get_access_parent( $p_project, $p_option ) {
	if( $p_project == ALL_PROJECTS ) {
		return config_get_global( 'admin_site_threshold' );
	} else {
		return config_get_access( $p_option, null, ALL_PROJECTS );
	}
}


$t_can_change_level = min( config_get_access( 'status_enum_workflow' ), config_get_access( 'report_bug_threshold' ), config_get_access( 'set_status_threshold' )
		, config_get_access( 'bug_submit_status' ), config_get_access( 'bug_resolved_status_threshold' ), config_get_access( 'bug_reopen_status' ) );
access_ensure_project_level( $t_can_change_level );

$t_redirect_url = 'manage_config_workflow_page.php';
$t_project = helper_get_current_project();
$t_access = current_user_get_access_level();

layout_page_header( lang_get( 'manage_workflow_config' ), $t_redirect_url );

layout_page_begin( 'manage_overview_page.php' );

# process the changes to threshold values
$t_valid_thresholds = array(
	'bug_submit_status',
	'bug_resolved_status_threshold',
	'bug_reopen_status',
);

foreach( $t_valid_thresholds as $t_threshold ) {
	$t_access_current = config_get_access( $t_threshold );
	if( $t_access >= $t_access_current ) {
		$f_value = gpc_get( 'threshold_' . $t_threshold );
		$t_value_current = config_get( $t_threshold );
		$t_value_parent = config_get_parent( $t_project, $t_threshold );

		$f_access = gpc_get( 'access_' . $t_threshold );
		$t_access_parent = config_get_access_parent( $t_project, $t_threshold );

		if( $f_value == $t_value_parent && $f_access == $t_access_parent ) {
			# If new value is equal to parent and access has not changed
			config_delete( $t_threshold, ALL_USERS, $t_project );
		} else if( $f_value != $t_value_current || $f_access != $t_access_current ) {
			# Set config if value or access have changed
			config_set( $t_threshold, $f_value, NO_USER, $t_project, $f_access );
		}
	}
}

$t_enum_status = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );

# process the workflow by reversing the flags to a matrix and creating the appropriate string
if( config_get_access( 'status_enum_workflow' ) <= $t_access ) {
	$f_value = gpc_get( 'flag', array() );
	$f_access = gpc_get( 'workflow_access' );
	$t_matrix = array();

	foreach( $f_value as $t_transition ) {
		list( $t_from, $t_to ) = explode( ':', $t_transition );
		$t_matrix[$t_from][$t_to] = '';
	}
	$t_workflow = array();
	foreach( $t_enum_status as $t_state => $t_label ) {
		$t_workflow_row = '';
		$t_default = gpc_get_int( 'default_' . $t_state );
		if( isset( $t_matrix[$t_state] ) && isset( $t_matrix[$t_state][$t_default] ) ) {
			$t_workflow_row .= $t_default . ':' . get_enum_element( 'status', $t_default );
			unset( $t_matrix[$t_state][$t_default] );
			$t_first = false;
		} else {
			# error default state isn't in the matrix
			echo '<p>' . sprintf( lang_get( 'default_not_in_flow' ), get_enum_element( 'status', $t_default ), get_enum_element( 'status', $t_state ) )  . '</p>';
			$t_first = true;
		}
		if( isset( $t_matrix[$t_state] ) ) {
			foreach ( $t_matrix[$t_state] as $t_next_state => $t_junk ) {
				if( false == $t_first ) {
					$t_workflow_row .= ',';
				}
				$t_workflow_row .= $t_next_state . ':' . get_enum_element( 'status', $t_next_state );
				$t_first = false;
			}
		}
		# $t_workflow_row is allowed to be empty ''
		$t_workflow[$t_state] = $t_workflow_row;
	}

	# Get the parent's workflow, if not set default to all transitions
	$t_access_parent = config_get_access_parent( $t_project, 'status_enum_workflow' );
	$t_access_current = config_get_access( 'status_enum_workflow' );
	$t_workflow_parent = config_get_parent( $t_project, 'status_enum_workflow' );
	if( 0 == count( $t_workflow_parent ) ) {
		foreach( $t_enum_status as $t_status => $t_label ) {
			$t_temp_workflow = array();
			foreach( $t_enum_status as $t_next => $t_next_label ) {
				if( $t_status != $t_next ) {
					$t_temp_workflow[] = $t_next . ':' . $t_next_label;
				}
			}
			$t_workflow_parent[$t_status] = implode( ',', $t_temp_workflow );
		}
	}

	if( $t_workflow == $t_workflow_parent && $f_access == $t_access_parent ) {
		# If new value is equal to parent and access has not changed
		config_delete( 'status_enum_workflow', ALL_USERS, $t_project );
	} else if( $t_workflow != config_get( 'status_enum_workflow' ) || $f_access != $t_access_current ) {
		# Set config if value or access have changed
		config_set( 'status_enum_workflow', $t_workflow, NO_USER, $t_project, $f_access );
	}
}

# process the access level changes
if( min( config_get_access( 'set_status_threshold' ), config_get_access( 'report_bug_threshold' ) ) <= $t_access ) {
	# get changes to access level to change these values
	$f_access = gpc_get( 'status_access' );
	$t_access_parent = config_get_access_parent( $t_project, 'set_status_threshold' );
	$t_access_current = config_get_access( 'set_status_threshold' );

	# Build access level reference arrays (parent level and current config)
	$t_set_parent = config_get_parent( $t_project, 'set_status_threshold' );
	$t_set_current = config_get( 'set_status_threshold' );
	$t_bug_submit_status = config_get( 'bug_submit_status' );
	foreach( $t_enum_status as $t_status => $t_status_label ) {
		if( !isset( $t_set_parent[$t_status] ) ) {
			if( $t_bug_submit_status == $t_status && config_get_access( 'report_bug_threshold' ) <= $t_access ) {
				$t_set_parent[$t_status] = config_get_parent( $t_project, 'report_bug_threshold' );
			} elseif( config_get_access( 'set_status_threshold' ) <= $t_access ) {
				$t_set_parent[$t_status] = config_get_parent( $t_project, 'update_bug_status_threshold' );
			}
		}
		if( !isset( $t_set_current[$t_status] ) ) {
			if( $t_bug_submit_status == $t_status && config_get_access( 'report_bug_threshold' ) <= $t_access ) {
				$t_set_current[$t_status] = config_get( 'report_bug_threshold' );
			} elseif( config_get_access( 'set_status_threshold' ) <= $t_access ) {
				$t_set_current[$t_status] = config_get( 'update_bug_status_threshold' );
			}
		}
	}

	# walk through the status labels to set the status threshold
	$t_set_new = array();
	foreach( $t_enum_status as $t_status_id => $t_status_label ) {
		$f_level = gpc_get_int( 'access_change_' . $t_status_id, -1 );
		if( config_get( 'bug_submit_status' ) == $t_status_id ) {
			# Check if input exists
			if( $f_level > -1 ) {
				if( $f_level != $t_set_parent[$t_status_id] ) {
					config_set( 'report_bug_threshold', (int)$f_level, ALL_USERS, $t_project, $f_access );
				} else {
					config_delete( 'report_bug_threshold', ALL_USERS, $t_project );
				}
			}
			unset( $t_set_parent[$t_status_id] );
			unset( $t_set_current[$t_status_id] );
		} else {
			# Only process those inputs that exist, since not all access_change_<status> may have been editable.
			if( $f_level > -1 ) {
				$t_set_new[$t_status_id] = $f_level;
			} else {
				if( isset( $t_set_current[$t_status_id] ) ) {
					$t_set_new[$t_status_id] = $t_set_current[$t_status_id];
				}
			}
		}
	}

	if( $t_set_new == $t_set_parent && $f_access == $t_access_parent ) {
		# If new value is equal to parent and access has not changed
		config_delete( 'set_status_threshold', ALL_USERS, $t_project );
	} else if( $t_set_new != $t_set_current || $f_access != $t_access_current ) {
		# Set config if value or access have changed
		config_set( 'set_status_threshold', $t_set_new, ALL_USERS, $t_project, $f_access );
	}
}

form_security_purge( 'manage_config_workflow_set' );

html_operation_successful( $t_redirect_url );

layout_page_end();
