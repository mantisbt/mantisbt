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
 * Update User
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
 * @uses database_api.php
 * @uses email_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses logging_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses user_pref_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'email_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'logging_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'user_pref_api.php' );

form_security_validate( 'manage_user_update' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_user_threshold' ) );

$f_protected	= gpc_get_bool( 'protected' );
$f_enabled		= gpc_get_bool( 'enabled' );
$f_email		= gpc_get_string( 'email', '' );
$f_username		= gpc_get_string( 'username', '' );
$f_realname		= gpc_get_string( 'realname', '' );
$f_access_level	= gpc_get_int( 'access_level' );
$f_user_id		= gpc_get_int( 'user_id' );

if( config_get( 'enable_email_notification' ) == ON ) {
	$f_send_email_notification = gpc_get_bool( 'send_email_notification' );
} else {
	$f_send_email_notification = 0;
}

user_ensure_exists( $f_user_id );

$t_user = user_get_row( $f_user_id );

$f_username	= trim( $f_username );

$t_old_username = $t_user['username'];

if( $f_send_email_notification ) {
	$t_old_realname = $t_user['realname'];
	$t_old_email = $t_user['email'];
	$t_old_access_level = $t_user['access_level'];
}

# Ensure that the account to be updated is of equal or lower access to the
# current user.
access_ensure_global_level( $t_user['access_level'] );

# check that the username is unique
if( 0 != strcasecmp( $t_old_username, $f_username )
	&& false == user_is_name_unique( $f_username ) ) {
	trigger_error( ERROR_USER_NAME_NOT_UNIQUE, ERROR );
}

user_ensure_name_valid( $f_username );

$t_ldap = ( LDAP == config_get( 'login_method' ) );

if( $t_ldap && config_get( 'use_ldap_realname' ) ) {
	$t_realname = ldap_realname_from_username( $f_username );
} else {
	# strip extra space from real name
	$t_realname = string_normalize( $f_realname );
	user_ensure_realname_unique( $t_old_username, $t_realname );
}

if( $t_ldap && config_get( 'use_ldap_email' ) ) {
	$t_email = ldap_email( $f_user_id );
} else {
	$t_email = trim( $f_email );
	email_ensure_valid( $t_email );
	email_ensure_not_disposable( $t_email );
	user_ensure_email_unique( $t_email, $f_user_id );
}

$c_email = $t_email;
$c_username = $f_username;
$c_realname = $t_realname;
$c_protected = (bool)$f_protected;
$c_enabled = (bool)$f_enabled;
$c_user_id = (int)$f_user_id;
$c_access_level = (int)$f_access_level;

$t_old_protected = $t_user['protected'];

# Ensure that users aren't escalating privileges of accounts beyond their
# own global access level.
access_ensure_global_level( $f_access_level );

# check that we are not downgrading the last administrator
$t_admin_threshold = config_get_global( 'admin_site_threshold' );
if( user_is_administrator( $f_user_id ) &&
	user_count_level( $t_admin_threshold, /* enabled */ true ) <= 1 ) {
	if( $f_access_level < $t_admin_threshold || $c_enabled === false ) {
		trigger_error( ERROR_USER_CHANGE_LAST_ADMIN, ERROR );
	}
}

# Project specific access rights override global levels, hence, for users who are changed
# to be administrators, we have to remove project specific rights.
if( ( $f_access_level >= $t_admin_threshold ) && ( !user_is_administrator( $f_user_id ) ) ) {
	user_delete_project_specific_access_levels( $f_user_id );
}

# if the user is already protected and the admin is not removing the
#  protected flag then don't update the access level and enabled flag.
#  If the user was unprotected or the protected flag is being turned off
#  then proceed with a full update.
$t_query_params = array();
if( $f_protected && $t_old_protected ) {
	$t_query = 'UPDATE {user}
			SET username=' . db_param() . ', email=' . db_param() . ',
				protected=' . db_param() . ', realname=' . db_param() . '
			WHERE id=' . db_param();
	$t_query_params = array( $c_username, $c_email, $c_protected, $c_realname, $c_user_id );
	# Prevent e-mail notification for a change that did not happen
	$f_access_level = $t_old_access_level;
} else {
	$t_query = 'UPDATE {user}
			SET username=' . db_param() . ', email=' . db_param() . ',
				access_level=' . db_param() . ', enabled=' . db_param() . ',
				protected=' . db_param() . ', realname=' . db_param() . '
			WHERE id=' . db_param();
	$t_query_params = array( $c_username, $c_email, $c_access_level, $c_enabled, $c_protected, $c_realname, $c_user_id );
}

$t_result = db_query( $t_query, $t_query_params );

event_signal( 'EVENT_MANAGE_USER_UPDATE', array( $c_user_id ) );

if( $f_send_email_notification ) {
	lang_push( user_pref_get_language( $f_user_id ) );
	$t_changes = '';

	if( strcmp( $f_username, $t_old_username ) ) {
		$t_changes .= lang_get( 'username_label' ) . ' ' . $t_old_username . ' => ' . $f_username . "\n";
	}

	if( strcmp( $t_realname, $t_old_realname ) ) {
		$t_changes .= lang_get( 'realname_label' ) . ' ' . $t_old_realname . ' => ' . $t_realname . "\n";
	}

	if( strcmp( $t_email, $t_old_email ) ) {
		$t_changes .= lang_get( 'email_label' ) . ' ' . $t_old_email . ' => ' . $t_email . "\n";
	}

	if( strcmp( $f_access_level, $t_old_access_level ) ) {
		$t_old_access_string = get_enum_element( 'access_levels', $t_old_access_level );
		$t_new_access_string = get_enum_element( 'access_levels', $f_access_level );
		$t_changes .= lang_get( 'access_level_label' ) . ' ' . $t_old_access_string . ' => ' . $t_new_access_string . "\n\n";
	}

	if( !empty( $t_changes ) ) {
		$t_subject = '[' . config_get( 'window_title' ) . '] ' . lang_get( 'email_user_updated_subject' );
		$t_updated_msg = lang_get( 'email_user_updated_msg' );
		$t_message = $t_updated_msg . "\n\n" . config_get( 'path' ) . 'account_page.php' . "\n\n" . $t_changes;

		if( null === email_store( $t_email, $t_subject, $t_message ) ) {
			log_event( LOG_EMAIL, 'Notification was NOT sent to ' . $f_username );
		} else {
			log_event( LOG_EMAIL, 'Account update notification sent to ' . $f_username . ' (' . $t_email . ')' );
			if( config_get( 'email_send_using_cronjob' ) == OFF ) {
				email_send_all();
			}
		}
	}

	lang_pop();
}

$t_redirect_url = 'manage_user_edit_page.php?user_id=' . $c_user_id;

form_security_purge( 'manage_user_update' );

html_page_top( null, $t_result ? $t_redirect_url : null );

if( $f_protected && $t_old_protected ) {				# PROTECTED
	echo '<div class="failure-msg">';
	echo lang_get( 'manage_user_protected_msg' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
	echo '</div>';
} else if( $t_result ) {					# SUCCESS
	html_operation_successful( $t_redirect_url );
}

html_page_bottom();
