<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
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
	function ldap_uid_pass($login, $pass) {
	global $g_ldap_organisation,$g_ldap_server,$g_ldap_root_dn,$g_ldapauth_type;

	$search_dn = "(&$g_ldap_organisation(uid=$login))";
	$ds        = ldap_connect( "$g_ldap_server" );

	if ( $ds ) {
		$r       = ldap_bind( $ds ); # bind to server

		if ("CLEAR" == $g_ldapauth_type)
		{
			$crypted_pass = $pass;
		}
		elseif ("CRYPT" == $g_ldapauth_type)
		{
			$sr	= ldap_search( $ds, $g_ldap_root_dn, $search_dn ); # query without password
			$entry	= ldap_first_entry($ds, $sr);
			if (!($entry)) return false;
			$values	= ldap_get_values($ds, $entry,"userpassword");
			$salt	= $values[0][0].$values[0][1];
			$crypted_pass=crypt($pass,$salt);
		}
		else
		{
			die ("wrong LDAP parameter g_ldapauth_type : [$g_ldapauth_type]");
		}

		$search_dn = "(&$g_ldap_organisation(uid=$login)(userpassword=$crypted_pass))";
		$sr      = ldap_search( $ds, $g_ldap_root_dn, $search_dn ); # query with password matching
		#---------------------------
		$entries = ldap_count_entries( $ds, $sr );
		ldap_close( $ds ); # clean up
		if ( $entries >= 1 ) {
			return true;
		} else {
			return false;
		}
	} else {
		die ("Unable to connect to LDAP server");
	}
}
	# --------------------
?>