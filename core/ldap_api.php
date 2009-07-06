<?php
# MantisBT - a php based bugtracking system

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
 * LDAP API
 * @package CoreAPI
 * @subpackage LDAPAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2009  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * Connect and bind to the LDAP directory
 * @param string $p_binddn
 * @param string $p_password
 * @return resource
 */
function ldap_connect_bind( $p_binddn = '', $p_password = '' ) {
	$t_ldap_server = config_get( 'ldap_server' );

	if( !extension_loaded( 'ldap' ) ) {
		log_event( LOG_LDAP, "Error: LDAP extension missing in php" );
		trigger_error( ERROR_LDAP_EXTENSION_NOT_LOADED, ERROR );
	}

	log_event( LOG_LDAP, "Attempting connection to LDAP server" );
	$t_ds = @ldap_connect( $t_ldap_server );
	if( $t_ds > 0 ) {
		log_event( LOG_LDAP, "Connection accepted to LDAP server" );
		$t_protocol_version = config_get( 'ldap_protocol_version' );

		if( $t_protocol_version > 0 ) {
			log_event( LOG_LDAP, "Setting LDAP protocol to  to ldap server to " . $t_protocol_version );
			ldap_set_option( $t_ds, LDAP_OPT_PROTOCOL_VERSION, $t_protocol_version );
		}

		# If no Bind DN and Password is set, attempt to login as the configured
		#  Bind DN.
		if( is_blank( $p_binddn ) && is_blank( $p_password ) ) {
			$p_binddn = config_get( 'ldap_bind_dn', '' );
			$p_password = config_get( 'ldap_bind_passwd', '' );
		}

		if( !is_blank( $p_binddn ) && !is_blank( $p_password ) ) {
			log_event( LOG_LDAP, "Attempting bind to ldap server with username and password" );
			$t_br = @ldap_bind( $t_ds, $p_binddn, $p_password );
		} else {
			# Either the Bind DN or the Password are empty, so attempt an anonymous bind.
			log_event( LOG_LDAP, "Attempting anonymous bind to ldap server" );
			$t_br = @ldap_bind( $t_ds );
		}
		if( !$t_br ) {
			log_event( LOG_LDAP, "bind to ldap server  failed - authentication error?" );
			trigger_error( ERROR_LDAP_AUTH_FAILED, ERROR );
		}
		log_event( LOG_LDAP, "bind to ldap server successful" );
	} else {
		log_event( LOG_LDAP, "Connection to ldap server failed" );
		trigger_error( ERROR_LDAP_SERVER_CONNECT_FAILED, ERROR );
	}

	return $t_ds;
}

$g_cache_ldap_email = array();

/**
 * returns an email address from LDAP, given a userid
 * @param int $p_user_id
 * @return string
 */
function ldap_email( $p_user_id ) {
	global $g_cache_ldap_email;

	if( isset( $g_cache_ldap_email[ (int)$p_user_id ] ) ) {
		return $g_cache_ldap_email[ (int)$p_user_id ];
	}

	$t_username = user_get_field( $p_user_id, 'username' );
	$t_email = ldap_email_from_username( $t_username );

	$g_cache_ldap_email[ (int)$p_user_id ] = $t_email;
	return $t_email;
}

/**
 * Return an email address from LDAP, given a username
 * @param string $p_username
 * @return string
 */
function ldap_email_from_username( $p_username ) {
	if ( ldap_simulation_is_enabled() ) {
		return ldap_simulation_email_from_username( $p_username );
	}

	$t_ldap_organization = config_get( 'ldap_organization' );
	$t_ldap_root_dn = config_get( 'ldap_root_dn' );

	$t_ldap_uid_field = config_get( 'ldap_uid_field', 'uid' );
	$t_search_filter = "(&$t_ldap_organization($t_ldap_uid_field=$p_username))";
	$t_search_attrs = array(
		$t_ldap_uid_field,
		'mail',
		'dn',
	);
	$t_ds = ldap_connect_bind();

	log_event( LOG_LDAP, "Searching for $t_search_filter" );
	$t_sr = ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );

	$t_info = ldap_get_entries( $t_ds, $t_sr );
	ldap_free_result( $t_sr );
	ldap_unbind( $t_ds );

	return $t_info[0]['mail'][0];
}

/**
 * Attempt to authenticate the user against the LDAP directory
 * return true on successful authentication, false otherwise
 * @param int $p_user_id
 * @param string $p_password
 * @return bool
 */
function ldap_authenticate( $p_user_id, $p_password ) {
	# if password is empty and ldap allows anonymous login, then
	# the user will be able to login, hence, we need to check
	# for this special case.
	if( is_blank( $p_password ) ) {
		return false;
	}

	if ( ldap_simulation_is_enabled() ) {
		return ldap_simulation_authenticate( $p_user_id, $p_password );
	}

	$t_ldap_organization = config_get( 'ldap_organization' );
	$t_ldap_root_dn = config_get( 'ldap_root_dn' );

	$t_username = user_get_field( $p_user_id, 'username' );
	$t_ldap_uid_field = config_get( 'ldap_uid_field', 'uid' );
	$t_search_filter = "(&$t_ldap_organization($t_ldap_uid_field=$t_username))";
	$t_search_attrs = array(
		$t_ldap_uid_field,
		'dn',
	);
	$t_ds = ldap_connect_bind();

	# Search for the user id
	log_event( LOG_LDAP, "Searching for $t_search_filter" );
	$t_sr = ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
	$t_info = ldap_get_entries( $t_ds, $t_sr );

	$t_authenticated = false;

	if( $t_info ) {
		# Try to authenticate to each until we get a match
		for( $i = 0;$i < $t_info['count'];$i++ ) {
			$t_dn = $t_info[$i]['dn'];

			# Attempt to bind with the DN and password
			if( @ldap_bind( $t_ds, $t_dn, $p_password ) ) {
				$t_authenticated = true;
				break;
				# Don't need to go any further
			}
		}
	}

	ldap_free_result( $t_sr );
	ldap_unbind( $t_ds );

	return $t_authenticated;
}

/**
 * Checks if the LDAP simulation mode is enabled.
 *
 * @return bool true if enabled, false otherwise.
 */
function ldap_simulation_is_enabled() {
	$t_filename = config_get( 'ldap_simulation_file_path' );
	return !is_blank( $t_filename );
}

/**
 * Gets a user from LDAP simulation mode given the username.
 *
 * @param string $p_username  The user name.
 * @return mixed an associate array with user information or null if not found.
 */
function ldap_simulation_get_user( $p_username ) {
	$t_filename = config_get( 'ldap_simulation_file_path' );

	$t_lines = file( $t_filename );

	foreach ( $t_lines as $t_line ) {
		$t_line = trim( $t_line, " \t\r\n" );
		$t_row = explode( ',', $t_line );

		if ( $t_row[0] != $p_username ) {
			continue;
		}

		$t_user = array();

		$t_user['username'] = $t_row[0];
		$t_user['realname'] = $t_row[1];
		$t_user['email'] = $t_row[2];
		$t_user['password'] = $t_row[3];

		return $t_user;
	}

	log_event( LOG_LDAP, "ldap_simulation_get_user: user '$t_username' not found." );
	return null;
}

/**
 * Given a username, gets the email address or empty address if user is not found.
 *
 * @param string $p_username The user name.
 * @return The email address or blank if user is not found.
 */
function ldap_simulation_email_from_username( $p_username ) {
	$t_user = ldap_simulation_get_user( $p_username );
	if ( $t_user === null ) {
		log_event( LOG_LDAP, "ldap_simulation_email_from_username: user '$p_username' not found." );
		return '';
	}

	log_event( LOG_LDAP, "ldap_simulation_email_from_username: user '$p_username' has email '{$t_user['email']}'." );
	return $t_user['email'];
}

/**
 * Authenticates the specified user id / password based on the simulation data.
 *
 * @param string $p_user_id   The user id.
 * @param string $p_password  The password.
 * @return bool true for authenticated, false otherwise.
 */
function ldap_simulation_authenticate( $p_user_id, $p_password ) {
	$t_username = user_get_field( $p_user_id, 'username' );

	$t_user = ldap_simulation_get_user( $t_username );
	if ( $t_user === null ) {
		log_event( LOG_LDAP, "ldap_simulation_authenticate: user '$t_username' not found." );
		return false;
	}

	if ( $t_user['password'] != $p_password ) {
		log_event( LOG_LDAP, "ldap_simulation_authenticate: expected password '{$t_user['password']}' and got '$p_password'." );
		return false;
	}

	log_event( LOG_LDAP, "ldap_simulation_authenticate: authentication successful for user '$t_username'." );
	return true;
}
