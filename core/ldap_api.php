<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: ldap_api.php,v 1.16 2004-08-06 17:17:06 jlatour Exp $
	# --------------------------------------------------------

	###########################################################################
	# LDAP API
	###########################################################################

 	# --------------------
	# Connect and bind to the LDAP directory
	function ldap_connect_bind( $p_binddn = '', $p_password = '' ) {
		$t_ldap_server	= config_get( 'ldap_server' );
		$t_ldap_port	= config_get( 'ldap_port' );

		$t_ds = @ldap_connect ( $t_ldap_server, $t_ldap_port );
		if ( $t_ds > 0 ) {
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