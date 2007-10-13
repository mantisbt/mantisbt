<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: ldap_api.php,v 1.20.2.1 2007-10-13 22:35:33 giallu Exp $
	# --------------------------------------------------------

	###########################################################################
	# LDAP API
	###########################################################################

 	# --------------------
	# Connect and bind to the LDAP directory
	function ldap_connect_bind( $p_binddn = '', $p_password = '' ) {
		$t_ldap_server	= config_get( 'ldap_server' );
		$t_ldap_port	= config_get( 'ldap_port' );

		if (!extension_loaded('ldap')) {
			trigger_error(ERROR_LDAP_EXTENSION_NOT_LOADED,ERROR);
		}
		
		$t_ds = @ldap_connect ( $t_ldap_server, $t_ldap_port );
		if ( $t_ds > 0 ) {
			$t_protocol_version = config_get( 'ldap_protocol_version' );

			if ( $t_protocol_version > 0 ) {
				ldap_set_option( $t_ds, LDAP_OPT_PROTOCOL_VERSION, $t_protocol_version );
			}

			# If no Bind DN and Password is set, attempt to login as the configured
			#  Bind DN.
			if ( is_blank( $p_binddn ) && is_blank( $p_password ) ) {
				$p_binddn	= config_get( 'ldap_bind_dn', '' );
				$p_password	= config_get( 'ldap_bind_passwd', '' );
			}

			if ( !is_blank( $p_binddn ) && !is_blank( $p_password ) ) {
				$t_br = @ldap_bind( $t_ds, $p_binddn, $p_password );
			} else {
				# Either the Bind DN or the Password are empty, so attempt an anonymous bind.
				$t_br = @ldap_bind( $t_ds );
			}
			if ( !$t_br ) {
				trigger_error( ERROR_LDAP_AUTH_FAILED, ERROR );
			}
		} else {
			trigger_error( ERROR_LDAP_SERVER_CONNECT_FAILED, ERROR );
		}

		return $t_ds;
	}

 	# --------------------
	# Return an email address from LDAP, given a userid
	function ldap_email( $p_user_id ) {
		$t_username = user_get_field( $p_user_id, 'username' );
		return ldap_email_from_username($t_username);
	}

 	# --------------------
	# Return an email address from LDAP, given a username
	function ldap_email_from_username( $p_username ) {
		$t_ldap_organization	= config_get( 'ldap_organization' );
		$t_ldap_root_dn	    	= config_get( 'ldap_root_dn' );

		$t_ldap_uid_field = config_get( 'ldap_uid_field', 'uid' ) ;
		$t_search_filter	= "(&$t_ldap_organization($t_ldap_uid_field=$p_username))";
		$t_search_attrs		= array( $t_ldap_uid_field, 'mail', 'dn' );
		$t_ds           	= ldap_connect_bind();

		$t_sr	= ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
		$t_info	= ldap_get_entries( $t_ds, $t_sr );
		ldap_free_result( $t_sr );
		ldap_unbind( $t_ds );

		return $t_info[0]['mail'][0];
	}

	# --------------------
	# Return true if the $uid has an assigngroup=$p_group tag, false otherwise
	function ldap_has_group( $p_user_id, $p_group ) {
		$t_ldap_organization	= config_get( 'ldap_organization' );
		$t_ldap_root_dn			= config_get( 'ldap_root_dn' );

		$t_username      	= user_get_field( $p_user_id, 'username' );
		$t_ldap_uid_field	= config_get( 'ldap_uid_field', 'uid' ) ;
		$t_search_filter 	= "(&$t_ldap_organization($t_ldap_uid_field=$t_username)(assignedgroup=$p_group))";
		$t_search_attrs	 	= array( $t_ldap_uid_field, 'dn', 'assignedgroup' );
		$t_ds            	= ldap_connect_bind();

		$t_sr     	= ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
		$t_entries	= ldap_count_entries( $t_ds, $t_sr );
		ldap_free_result( $t_sr );
		ldap_unbind( $t_ds );

		if ( $t_entries > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	# --------------------
	# Attempt to authenticate the user against the LDAP directory
	#  return true on successful authentication, false otherwise
	function ldap_authenticate( $p_user_id, $p_password ) {
		# if password is empty and ldap allows anonymous login, then
		# the user will be able to login, hence, we need to check
		# for this special case.
		if ( is_blank( $p_password ) ) {
			return false;
		}

		$t_ldap_organization	= config_get( 'ldap_organization' );
		$t_ldap_root_dn			= config_get( 'ldap_root_dn' );

		$t_username      	= user_get_field( $p_user_id, 'username' );
		$t_ldap_uid_field	= config_get( 'ldap_uid_field', 'uid' ) ;
		$t_search_filter 	= "(&$t_ldap_organization($t_ldap_uid_field=$t_username))";
		$t_search_attrs  	= array( $t_ldap_uid_field, 'dn' );
		$t_ds            	= ldap_connect_bind();

		# Search for the user id
		$t_sr	= ldap_search( $t_ds, $t_ldap_root_dn, $t_search_filter, $t_search_attrs );
		$t_info	= ldap_get_entries( $t_ds, $t_sr );

		$t_authenticated = false;

		if ( $t_info ) {
			# Try to authenticate to each until we get a match
			for ( $i = 0 ; $i < $t_info['count'] ; $i++ ) {
				$t_dn = $t_info[$i]['dn'];

				# Attempt to bind with the DN and password
				if ( @ldap_bind( $t_ds, $t_dn, $p_password ) ) {
					$t_authenticated = true;
					break; # Don't need to go any further
				}
			}
		}

		ldap_free_result( $t_sr );
		ldap_unbind( $t_ds );

		return $t_authenticated;
	}

	# --------------------
	# Create a new user account in the LDAP Directory.

	# --------------------
	# Update the user's account in the LDAP Directory

	# --------------------
	# Change the user's password in the LDAP Directory
?>
