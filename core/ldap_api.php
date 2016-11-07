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
 * LDAP API
 *
 * @package CoreAPI
 * @subpackage LDAPAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses logging_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'logging_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

class Auth_LDAP {
	//@param resource $p_ds LDAP resource identifier returned by ldap_connect.
	public $p_ds = null;

	// LDAP data source
	public $t_ds = null;

	// Is simulation enabled?
	public $simulationEnabled =  false;

	// The LDAP config parameters
	private $config = null;



	// Constructor
	public function __construct($config) {
		$this->config = $config;
	}

	/**
	 * Logs the most recent LDAP error
	 * @param resource $p_ds LDAP resource identifier returned by ldap_connect.
	 * @return void
	 */
	function log_error( $p_ds ) {
		log_event( LOG_LDAP, 'ERROR #' . ldap_errno( $p_ds ) . ': ' . ldap_error( $p_ds ) );
	}


	/**
	 * Connect and bind to the LDAP directory
	 * @param string $p_binddn   DN to use for LDAP bind.
	 * @param string $p_password Password to use for LDAP bind.
	 * @return resource|false
	 */
	function connect_bind( ) {
		if( !extension_loaded( 'ldap' ) ) {
			log_event( LOG_LDAP, 'Error: LDAP extension missing in php' );
			trigger_error( ERROR_LDAP_EXTENSION_NOT_LOADED, ERROR );
		}

		$t_ldap_server = $this->config['server'];

		log_event( LOG_LDAP, 'Attempting connection to LDAP server/URI \'' . $t_ldap_server . '\'.' );
		$t_ds = @ldap_connect( $t_ldap_server );
		if( $t_ds !== false && $t_ds > 0 ) {
			log_event( LOG_LDAP, 'Connection accepted by LDAP server' );

			$t_network_timeout = $this->config['network_timeout'];
			if( $t_network_timeout > 0 ) {
				log_event( LOG_LDAP, "Setting LDAP network timeout to " . $t_network_timeout );
				$t_result = @ldap_set_option( $t_ds, LDAP_OPT_NETWORK_TIMEOUT, $t_network_timeout );
				if( !$t_result ) {
					$this->log_error( $t_ds );
				}
			}

			$t_protocol_version = $this->config['protocol_version'];
			if( $t_protocol_version > 0 ) {
				log_event( LOG_LDAP, 'Setting LDAP protocol version to ' . $t_protocol_version );
				$t_result = @ldap_set_option( $t_ds, LDAP_OPT_PROTOCOL_VERSION, $t_protocol_version );
				if( !$t_result ) {
					$this->log_error( $t_ds );
				}
			}

			# Set referrals flag.
			$t_follow_referrals = ON == $this->config['follow_referrals'];
			$t_result = @ldap_set_option( $t_ds, LDAP_OPT_REFERRALS, $t_follow_referrals );
			if( !$t_result ) {
				$this->log_error( $t_ds );
			}

			# If no Bind DN and Password is set, attempt to login as the configured
			#  Bind DN.
			$p_binddn = $this->config['bind_dn'];
			$p_password = $this->config['bind_passwd'];

			if( !is_blank( $p_binddn ) && !is_blank( $p_password ) ) {
				log_event( LOG_LDAP, 'Attempting bind to ldap server with username and password' );
				$t_br = @ldap_bind( $t_ds, $p_binddn, $p_password );
			} else {
				# Either the Bind DN or the Password are empty, so attempt an anonymous bind.
				log_event( LOG_LDAP, 'Attempting anonymous bind to ldap server' );
				$t_br = @ldap_bind( $t_ds );
			}

			if( !$t_br ) {
				$this->log_error( $t_ds );
				log_event( LOG_LDAP, 'Bind to ldap server failed' );
				trigger_error( ERROR_LDAP_SERVER_CONNECT_FAILED, ERROR );
			} else {
				log_event( LOG_LDAP, 'Bind to ldap server successful' );
			}
		} else {
			log_event( LOG_LDAP, 'Connection to ldap server failed' );
			trigger_error( ERROR_LDAP_SERVER_CONNECT_FAILED, ERROR );
		}

		if( $t_ds === false ) {
			$authLdap->log_error( $t_ds );
			trigger_error( ERROR_LDAP_AUTH_FAILED, ERROR );
		}

		$this->t_ds = $t_ds;

		return $t_ds;
	}


	/**
	 * Escapes the LDAP string to disallow injection.
	 *
	 * @param string $p_string The string to escape.
	 * @return string The escaped string.
	 */
	function escape_string( $p_string ) {
		$t_find = array( '\\', '*', '(', ')', '/', "\x00" );
		$t_replace = array( '\5c', '\2a', '\28', '\29', '\2f', '\00' );

		$t_string = str_replace( $t_find, $t_replace, $p_string );

		return $t_string;
	}


	/**
	 * Attempt to authenticate the user against the LDAP directory
	 * return true on successful authentication, false otherwise
	 * @param integer $p_user_id  A valid user identifier.
	 * @param string  $p_password A password to test against the user user.
	 * @return boolean
	 */
	function authenticate( $p_user_id, $p_password ) {
		# if password is empty and ldap allows anonymous login, then
		# the user will be able to login, hence, we need to check
		# for this special case.
		if( is_blank( $p_password ) ) {
			return false;
		}

		$t_username = user_get_field( $p_user_id, 'username' );

		return $this->authenticate_by_username( $t_username, $p_password );
	}


	/**
	 * Authenticates an user via LDAP given the username and password.
	 *
	 * @param string $p_username The user name.
	 * @param string $p_password The password.
	 * @return true: authenticated, false: failed to authenticate.
	 */
	function authenticate_by_username( $p_username, $p_password ) {
		if( $this->simulation_is_enabled() ) {
			log_event( LOG_LDAP, 'Authenticating via LDAP simulation' );
			$t_authenticated = $this->simulation_authenticate_by_username( $p_username, $p_password );
		} else {
			$c_username = $this->escape_string( $p_username );

			$t_ldap_organization = $this->config['organization'];
			$t_ldap_root_dn = $this->config['root_dn'];

			$t_ldap_uid_field = $this->config['uid_field'];
			$t_search_filter = '(&' . $t_ldap_organization . '(' . $t_ldap_uid_field . '=' . $c_username . '))';
			$t_search_attrs = array(
				$t_ldap_uid_field,
				'dn',
			);

			# Bind
			log_event( LOG_LDAP, 'Binding to LDAP server' );
			$t_ds = $this->connect_bind();
			if( $t_ds === false ) {
				return false;
			}

			# Search for the user id
			log_event( LOG_LDAP, 'Searching for ' . $t_search_filter );
			$t_sr = ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
			if( $t_sr === false ) {
				$this->log_error( $t_ds );
				ldap_unbind( $t_ds );
				log_event( LOG_LDAP, 'ldap search failed' );
				trigger_error( ERROR_LDAP_AUTH_FAILED, ERROR );
			}

			$t_info = @ldap_get_entries( $t_ds, $t_sr );
			if( $t_info === false ) {
				$this->log_error( $t_ds );
				ldap_free_result( $t_sr );
				ldap_unbind( $t_ds );
				trigger_error( ERROR_LDAP_AUTH_FAILED, ERROR );
			}

			$t_authenticated = false;

			if( $t_info['count'] > 0 ) {
				# Try to authenticate to each until we get a match
				for( $i = 0; $i < $t_info['count']; $i++ ) {
					$t_dn = $t_info[$i]['dn'];
					log_event( LOG_LDAP, 'Checking ' . $t_info[$i]['dn'] );

					# Attempt to bind with the DN and password
					if( @ldap_bind( $t_ds, $t_dn, $p_password ) ) {
						$t_authenticated = true;
						break;
					}
				}
			} else {
				log_event( LOG_LDAP, 'No matching entries found' );
			}

			log_event( LOG_LDAP, 'Unbinding from LDAP server' );
			ldap_free_result( $t_sr );
			ldap_unbind( $t_ds );
		}

		# If user authenticated successfully then update the local DB with information
		# from LDAP.  This will allow us to use the local data after login without
		# having to go back to LDAP.  This will also allow fallback to DB if LDAP is down.
		if( $t_authenticated ) {
			$t_user_id = user_get_id_by_name( $p_username );

			if( false !== $t_user_id ) {

				$t_fields_to_update = array('password' => md5( $p_password ));

				if( ON == config_get( 'use_ldap_realname' ) ) {
					$t_fields_to_update['realname'] = ldap_realname( $t_user_id );
				}

				if( ON == config_get( 'use_ldap_email' ) ) {
					$t_fields_to_update['email'] = $this->email_from_username( $p_username );
				}

				user_set_fields( $t_user_id, $t_fields_to_update );
			}
			log_event( LOG_LDAP, 'User \'' . $p_username . '\' authenticated' );
		} else {
			log_event( LOG_LDAP, 'Authentication failed' );
		}

		return $t_authenticated;
	}

	/**
	 * Checks if the LDAP simulation mode is enabled.
	 *
	 * @return boolean true if enabled, false otherwise.
	 */
	function simulation_is_enabled() {
		$t_filename = config_get( 'ldap_simulation_file_path' );
		$this->simulationEnabled = !is_blank( $t_filename );
		return $this->simulationEnabled;
	}


	/**
	 * Gets a user from LDAP simulation mode given the username.
	 *
	 * @param string $p_username The user name.
	 * @return array|null An associate array with user information or null if not found.
	 */
	function simulation_get_user( $p_username ) {
		$t_filename = config_get( 'ldap_simulation_file_path' );
		$t_lines = file( $t_filename );
		if( $t_lines === false ) {
			log_event( LOG_LDAP, 'Auth_LDAP::simulation_get_user: could not read simulation data from ' . $t_filename );
			trigger_error( ERROR_LDAP_SERVER_CONNECT_FAILED, ERROR );
		}

		foreach ( $t_lines as $t_line ) {
			$t_line = trim( $t_line, " \t\r\n" );
			$t_row = explode( ',', $t_line );

			if( $t_row[0] != $p_username ) {
				continue;
			}

			$t_user = array();

			$t_user['username'] = $t_row[0];
			$t_user['realname'] = $t_row[1];
			$t_user['email'] = $t_row[2];
			$t_user['password'] = $t_row[3];

			return $t_user;
		}

		log_event( LOG_LDAP, 'Auth_LDAP::simulation_get_user: user \'' . $p_username . '\' not found.' );
		return null;
	}

	/**
	 * Given a username, gets the email address or empty address if user is not found.
	 *
	 * @param string $p_username The user name.
	 * @return string The email address or blank if user is not found.
	 */
	function simulation_email_from_username( $p_username ) {
		$t_user = $this->simulation_get_user( $p_username );
		if( $t_user === null ) {
			log_event( LOG_LDAP, 'Auth_LDAP::simulation_email_from_username: user \'' . $p_username . '\' not found.' );
			return '';
		}

		log_event( LOG_LDAP, 'Auth_LDAP::simulation_email_from_username: user \'' . $p_username . '\' has email \'' . $t_user['email'] .'\'.' );
		return $t_user['email'];
	}


	/**
	 * Authenticates the specified user id / password based on the simulation data.
	 *
	 * @param string $p_username The username.
	 * @param string $p_password The password.
	 * @return boolean true for authenticated, false otherwise.
	 */
	function simulation_authenticate_by_username( $p_username, $p_password ) {
		$c_username = $this->escape_string( $p_username );

		$t_user = $this->simulation_get_user( $c_username );
		if( $t_user === null ) {
			log_event( LOG_LDAP, 'Auth_LDAP::simulation_authenticate: user \'' . $p_username . '\' not found.' );
			return false;
		}

		if( $t_user['password'] != $p_password ) {
			log_event( LOG_LDAP, 'Auth_LDAP::simulation_authenticate: expected password \'' . $t_user['password'] . '\' and got \'' . $p_password . '\'.' );
			return false;
		}

		log_event( LOG_LDAP, 'Auth_LDAP::simulation_authenticate: authentication successful for user \'' . $p_username . '\'.' );
		return true;
	}


	/**
	 * Given a username, this methods gets the realname or empty string if not found.
	 *
	 * @param string $p_username The username.
	 * @return string The real name or an empty string if not found.
	 */
	function simulation_realname_from_username( $p_username ) {
		$t_user = $this->simulation_get_user( $p_username );
		if( $t_user === null ) {
			log_event( LOG_LDAP, 'Auth_LDAP::simulatiom_realname_from_username: user \'' . $p_username . '\' not found.' );
			return '';
		}

		log_event( LOG_LDAP, 'Auth_LDAP::simulatiom_realname_from_username: user \'' . $p_username . '\' has email \'' . $t_user['realname'] . '\'.' );
		return $t_user['realname'];
	}


	/**
	 * Gets the value of a specific field from LDAP given the user name
	 * and LDAP field name.
	 *
	 * @todo Implement caching by retrieving all needed information in one query.
	 * @todo Implement logging to LDAP queries same way like DB queries.
	 *
	 * @param string $p_username The user name.
	 * @param string $p_field    The LDAP field name.
	 * @return string The field value or null if not found.
	 */
	function get_field_from_username( $p_username, $p_field ) {
		$t_ldap_organization    = $this->config['organization'];
		$t_ldap_root_dn         = $this->config['root_dn'];
		$t_ldap_uid_field		= $this->config['uid_field'];

		$c_username = $this->escape_string( $p_username );

		log_event( LOG_LDAP, 'Retrieving field \'' . $p_field . '\' for \'' . $p_username . '\'' );

		# Bind
		log_event( LOG_LDAP, 'Binding to LDAP server' );
		$t_ds = @$this->connect_bind();
		if( $t_ds === false ) {
			$this->log_error( $t_ds );
			return null;
		}

		# Search
		$t_search_filter = '(&' . $t_ldap_organization . '(' . $t_ldap_uid_field . '=' . $c_username . '))';
		$t_search_attrs  = array( $t_ldap_uid_field, $p_field, 'dn' );

		log_event( LOG_LDAP, 'Searching for ' . $t_search_filter );
		$t_sr = @ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
		if( $t_sr === false ) {
			$this->log_error( $t_ds );
			ldap_unbind( $t_ds );
			log_event( LOG_LDAP, 'ldap search failed' );
			return null;
		}

		# Get results
		$t_info = ldap_get_entries( $t_ds, $t_sr );
		if( $t_info === false ) {
			$this->log_error( $t_ds );
			log_event( LOG_LDAP, 'ldap_get_entries() returned false.' );
			return null;
		}

		# Free results / unbind
		log_event( LOG_LDAP, 'Unbinding from LDAP server' );
		ldap_free_result( $t_sr );
		ldap_unbind( $t_ds );

		# If no matches, return null.
		if( $t_info['count'] == 0 ) {
			log_event( LOG_LDAP, 'No matches found.' );
			return null;
		}

		# Make sure the requested field exists
		if( is_array( $t_info[0] ) && array_key_exists( $p_field, $t_info[0] ) ) {
			$t_value = $t_info[0][$p_field][0];
			log_event( LOG_LDAP, 'Found value \'' . $t_value . '\' for field \'' . $p_field . '\'.' );
		} else {
			log_event( LOG_LDAP, 'WARNING: field \'' . $p_field . '\' does not exist' );
			return null;
		}

		return $t_value;
	}

	/**
	 * Return an email address from LDAP, given a username
	 * @param string $p_username The username of a user to lookup.
	 * @return string
	 */
	function email_from_username( $p_username ) {
		if( $this->simulation_is_enabled() ) {
			return $this->simulation_email_from_username( $p_username );
		}

		$t_email = $this->get_field_from_username( $p_username, 'mail' );
		if( $t_email === null ) {
			return '';
		}

		return $t_email;
	}


}

$authLdap = new Auth_LDAP( config_get( 'ldap' ) );

$g_cache_ldap_email = array();


/**
 * returns an email address from LDAP, given a userid
 * @param integer $p_user_id A valid user identifier.
 * @return string
 */
function ldap_email( $p_user_id ) {
	global $g_cache_ldap_email;
	global $authLdap;

	if( isset( $g_cache_ldap_email[(int)$p_user_id] ) ) {
		return $g_cache_ldap_email[(int)$p_user_id];
	}

	$t_username = user_get_field( $p_user_id, 'username' );
	$t_email = $authLdap->email_from_username( $t_username );

	$g_cache_ldap_email[(int)$p_user_id] = $t_email;
	return $t_email;
}


/**
 * Gets a user's real name (common name) given the id.
 *
 * @param integer $p_user_id The user id.
 * @return string real name.
 */
function ldap_realname( $p_user_id ) {
	$t_username = user_get_field( $p_user_id, 'username' );
	return ldap_realname_from_username( $t_username );
}

/**
 * Gets a user real name given their user name.
 *
 * @param string $p_username The user's name.
 * @return string The user's real name.
 */
function ldap_realname_from_username( $p_username ) {
	global $authLdap;

	$config = config_get( 'ldap' );

	if( $authLdap->simulation_is_enabled() ) {
		return $authLdap->simulation_realname_from_username( $p_username );
	}

	$t_ldap_realname_field	= $config['realname_field'];
	$t_realname = $authLdap->get_field_from_username( $p_username, $t_ldap_realname_field );
	if( $t_realname === null ) {
		return '';
	}

	return $t_realname;
}

