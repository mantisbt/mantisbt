<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# LDAP API
	###########################################################################

	# Some simple LDAP stuff that makes the work go 'round
	# Leigh Morresi <leighm@linuxbandwagon.com>

 	# --------------------
	# Find someone email address based on their login name
	function ldap_emailaddy($worker) {
	    global $g_ldap_organisation,$g_ldap_server,$g_ldap_root_dn;

	    $search_dn = "(&$g_ldap_organisation(uid=$worker))";
	    $ds        = ldap_connect( "$g_ldap_server" );

		if ( $ds ) {
			$r    = ldap_bind( $ds );
			$sr   = ldap_search( $ds, $g_ldap_root_dn, $search_dn );
			$info = ldap_get_entries( $ds, $sr );
			ldap_close( $ds );
			return ($info[0]["mail"][0]);
		} else {
			echo "<h4>Unable to connect to LDAP server</h4>";
			die;
		}
	}

	# --------------------
	# Return true if the $uid has an assigngroup=$group tag
	function ldap_has_group($uid,$group) {
	    global $g_ldap_organisation,$g_ldap_server,$g_ldap_root_dn;

		$search_dn = "(&$g_ldap_organisation(uid=$uid)(assignedgroup=$group))";
		$ds        = ldap_connect( "$g_ldap_server" );

		if ( $ds ) {
			$r       = ldap_bind( $ds ); # bind to server
			$sr      = ldap_search( $ds, $g_ldap_root_dn, $search_dn ); # query
			$entries = ldap_count_entries( $ds, $sr );
	        ldap_close( $ds ); # clean up
	        return $entries;
		} else {
			echo "<h4>Unable to connect to LDAP server</h4>";
			die;
		}
	}
	# --------------------
	# Return true if the $uid has $password (salt soon!)
	function ldap_uid_pass($uid, $pass) {
		global $g_ldap_organisation,$g_ldap_server,$g_ldap_root_dn;

	# @@@ Add MD5/SALT/OTHER one-way-encryption support for the password <leighm@linuxbandwagon.com>

		$search_dn = "(&$g_ldap_organisation(uid=$uid)(userpassword=$pass))";
		$ds        = ldap_connect( "$g_ldap_server" );

		if ( $ds ) {
			$r       = ldap_bind( $ds ); # bind to server
			$sr      = ldap_search( $ds, $g_ldap_root_dn, $search_dn ); # query
			$entries = ldap_count_entries( $ds, $sr );

			ldap_close( $ds ); # clean up
			if ( $entries >= 1 ) {
				return true;
			} else {
				return false;
			}
		} else {
			echo "<h4>Unable to connect to LDAP server</h4>";
			die;
		}
	}
	# --------------------
?>