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
 * Manage configuration for email
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

form_security_validate( 'manage_config_email_set' );

auth_reauthenticate();

$t_can_change_level = min( config_get_access( 'notify_flags' ), config_get_access( 'default_notify_flags' ) );
access_ensure_project_level( $t_can_change_level );

$t_redirect_url = 'manage_config_email_page.php';
$t_project = helper_get_current_project();

$f_flags			= gpc_get( 'flag', array() );
$f_thresholds		= gpc_get( 'flag_threshold', array() );
$f_actions_access	= gpc_get_int( 'notify_actions_access' );

layout_page_header( lang_get( 'manage_email_config' ), $t_redirect_url );

layout_page_begin();

$t_access = current_user_get_access_level();
$t_can_change_flags = $t_access >= config_get_access( 'notify_flags' );
$t_can_change_defaults = $t_access >= config_get_access( 'default_notify_flags' );

# build a list of the possible actions and flags
$t_valid_actions = email_get_actions();
$t_valid_flags = array( 'reporter', 'handler', 'monitor' , 'bugnotes', 'category' );

# initialize the thresholds
foreach( $t_valid_actions as $t_action ) {
	$t_thresholds_min[$t_action] = NOBODY;
	$t_thresholds_max[$t_action] = ANYBODY;
}


# parse flags and thresholds
foreach( $f_flags as $t_flag_value ) {
	list( $t_action, $t_flag ) = explode( ':', $t_flag_value );
	$t_flags[$t_action][$t_flag] = ON;
}
foreach( $f_thresholds as $t_threshold_value ) {
	list( $t_action, $t_threshold ) = explode( ':', $t_threshold_value );
	if( $t_threshold < $t_thresholds_min[$t_action] ) {
		$t_thresholds_min[$t_action] = $t_threshold;
	}
	if( $t_threshold > $t_thresholds_max[$t_action] ) {
		$t_thresholds_max[$t_action] = $t_threshold;
	}
}

# if we can set defaults, find them
if( $t_can_change_defaults ) {
	$t_first = true;

	# for flags, assume they are true, unless one of the actions has them off
	foreach ( $t_valid_flags as $t_flag ) {
		$t_default_flags[$t_flag] = ON;
		foreach ( $t_valid_actions as $t_action ) {
			if( !isset( $t_flags[$t_action][$t_flag] ) ) {
				unset( $t_default_flags[$t_flag] );
			}
		}
	}
	# for thresholds, find the subset that matches all of the actions
	$t_default_min = ANYBODY;
	$t_default_max = NOBODY;
	foreach ( $t_valid_actions as $t_action ) {
		if( $t_default_max > $t_thresholds_max[$t_action] ) {
			$t_default_max = $t_thresholds_max[$t_action];
		}
		if( $t_default_min < $t_thresholds_min[$t_action] ) {
			$t_default_min = $t_thresholds_min[$t_action];
		}
	}

	# We may end up with min = 100, max = 0 - make it 100, 100.
	if( $t_default_max < $t_default_min ) {
		$t_default_max = $t_default_min;
	}

	$t_default_flags['threshold_min'] = $t_default_min;
	$t_default_flags['threshold_max'] = $t_default_max;

	$t_existing_default_flags = config_get( 'default_notify_flags' );
	$t_existing_default_access = config_get_access( 'default_notify_flags' );
	if( ( $t_existing_default_flags != $t_default_flags )
			|| ( $t_existing_default_access != $f_actions_access ) ) { # only set the flags if they are different
		config_set( 'default_notify_flags', $t_default_flags, NO_USER, $t_project, $f_actions_access );
	}
} else {
	$t_default_flags = config_get( 'default_notify_flags' );
}

# set the values for specific actions if different from the defaults
$t_notify_flags = array();
foreach ( $t_valid_actions as $t_action ) {
	$t_action_printed = false;
	foreach ( $t_valid_flags as $t_flag ) {
		if( !isset( $t_default_flags[$t_flag] ) ) {
			$t_default_flags[$t_flag] = OFF;
		}

		# Always generate a complete set of flag to have a full override that can be compared
		# against defaults later in the manage_config_email_page.php rendering.
		$t_notify_flags[$t_action][$t_flag] = isset( $t_flags[$t_action][$t_flag] ) ? ON : OFF;
	}
	if( $t_default_flags['threshold_min'] <> $t_thresholds_min[$t_action] ) {
		$t_notify_flags[$t_action]['threshold_min'] = $t_thresholds_min[$t_action];
	}
	if( $t_default_flags['threshold_max'] <> $t_thresholds_max[$t_action] ) {
		$t_notify_flags[$t_action]['threshold_max'] = $t_thresholds_max[$t_action];
	}
}
if( isset( $t_notify_flags ) ) {
	$t_existing_flags = config_get( 'notify_flags' );
	$t_existing_access = config_get_access( 'notify_flags' );
	if( ( $t_existing_flags != $t_notify_flags )
			|| ( $t_existing_access != $f_actions_access ) ) { # only set the flags if they are different
		config_set( 'notify_flags', $t_notify_flags, NO_USER, $t_project, $f_actions_access );
	}
}

form_security_purge( 'manage_config_email_set' );

html_operation_successful( $t_redirect_url );

layout_page_end();
