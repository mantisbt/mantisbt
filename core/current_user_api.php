<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: current_user_api.php,v 1.1 2002-08-29 03:00:07 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Current User API
	#
	# Wrappers around the User API that pass in the logged-in user for you
	###########################################################################

	# --------------------
	# Return the access level of the current user in the current project
	function current_user_get_access_level() {
		return user_get_access_level( auth_get_current_user_id(),
										helper_get_current_project() );
	}
	# --------------------
	# Return the number of open assigned bugs to the current user in
	#  the current project
	function current_user_get_assigned_open_bug_count() {
		return user_get_assigned_open_bug_count( auth_get_current_user_id(),
													helper_get_current_project() );
	}
	# --------------------
	# Return the number of open reported bugs by the current user in
	#  the current project
	function current_user_get_reported_open_bug_count() {
		return user_get_reported_open_bug_count( auth_get_current_user_id(),
													helper_get_current_project() );
	}
	# --------------------
	# Return the specified field of the currently logged in user
	function current_user_get_field( $p_field_name ) {
		return user_get_field( auth_get_current_user_id(),
								$p_field_name );
	}
	# --------------------
	# Return the specified field of the currently logged in user
	function current_user_get_pref( $p_field_name ) {
		return user_get_pref( auth_get_current_user_id(), $p_field_name );
	}
?>
