<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: access_api.php,v 1.4 2002-09-01 21:45:59 prescience Exp $
	# --------------------------------------------------------

	###########################################################################
	# Access Control API
	###########################################################################
	# function to be called when a user is attempting to access a page that
	# he/she is not authorised to.  This outputs an access denied message then
	# re-directs to the mainpage.
	function access_denied() {
		global $MANTIS_ERROR;
		print '<center>';
		print '<p />' . $MANTIS_ERROR[ERROR_ACCESS_DENIED] . '<p />';
		print_bracket_link( 'main_page.php', lang_get( 'proceed' ) );
		print '</center>';
		exit;
	}
	# --------------------
	# check to see if the access level is strictly equal
	function access_level_check_equal( $p_access_level, $p_project_id=0 ) {
		global $g_string_cookie_val;

		if ( !isset( $g_string_cookie_val ) ) {
			return false;
		}

		$t_access_level = current_user_get_field( 'access_level' );
		$t_access_level2 = get_project_access_level( $p_project_id );

		if ( $t_access_level2 == $p_access_level ) {
			return true;
		} else if ( ( $t_access_level == $p_access_level ) &&
					( -1 == $t_access_level2 ) ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# check to see if the current user has access to the specified bug.  This assume that the bug exists and
	# that the user has access to the project (check_bug_exists() and project_access_check()).
	function access_bug_check( $p_bug_id, $p_view_state='' ) {
		global $g_private_bug_threshold;

		if ( empty ( $p_view_state ) ) {
			$t_view_state = get_bug_field( $p_bug_id, 'view_state' );
		} else {
			$t_view_state = (integer)$p_view_state;
		}

		# Make sure if the bug is private, the logged in user has access to it.
		if ( ( $t_view_state == PRIVATE ) && !access_level_check_greater_or_equal( $g_private_bug_threshold ) ) {
			access_denied();
		}
 	}
	# --------------------
	# check to see if the access level is equal or greater
	# this checks to see if the user has a higher access level for the current project
	function access_level_check_greater_or_equal( $p_access_level, $p_project_id=0 ) {
		global $g_string_cookie_val;

		# user isn't logged in
		if (( !isset( $g_string_cookie_val ) )||( empty( $g_string_cookie_val ) )) {
			return false;
		}

		# Administrators ALWAYS pass.
		if ( current_user_get_field( 'access_level' ) >= ADMINISTRATOR ) {
			return true;
		}

		$t_access_level = current_user_get_field( 'access_level' );
		$t_access_level2 = get_project_access_level( $p_project_id );

		# use the project level access level instead of the global access level
		# if the project level is not specified then use the global access level
		if ( -1 != $t_access_level2 ) {
			$t_access_level = $t_access_level2;
		}

		if ( $t_access_level >= $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}
    # Checks if the access level is greater than or equal the specified access level
	# The return will be true for administrators, will be the project-specific access
	# right if found, or the default if project is PUBLIC and no specific access right
	# found, otherwise, (private/not found) will return false
	function access_level_ge_no_default_for_private ( $p_access_level, $p_project_id ) {
		global $g_string_cookie_val;

		# user isn't logged in
		if (( !isset( $g_string_cookie_val ) )||( empty( $g_string_cookie_val ) )) {
			return false;
		}

		# Administrators ALWAYS pass.
		if ( current_user_get_field( 'access_level' ) >= ADMINISTRATOR ) {
			return true;
		}

		$t_access_level = get_project_access_level( $p_project_id );
		$t_project_view_state = project_get_field( $p_project_id, 'view_state' );

		# use the project level access level instead of the global access level
		# if the project level is not specified then use the global access level
		if ( ( -1 == $t_access_level ) && ( PUBLIC == $t_project_view_state ) ) {
			$t_access_level = current_user_get_field( 'access_level' );
		}

		return ( $t_access_level >= $p_access_level );
	}
	# --------------------
	# check to see if the access level is strictly equal
	function absolute_access_level_check_equal( $p_access_level ) {
		global $g_string_cookie_val;

		if ( !isset( $g_string_cookie_val ) ) {
			return false;
		}

		$t_access_level = current_user_get_field( 'access_level' );
		if ( $t_access_level == $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# check to see if the access level is equal or greater
	# this checks to see if the user has a higher access level for the current project
	function absolute_access_level_check_greater_or_equal( $p_access_level ) {
		global $g_string_cookie_val;

		# user isn't logged in
		if (( !isset( $g_string_cookie_val ) ) ||
			( empty( $g_string_cookie_val ) )) {
			return false;
		}

		$t_access_level = current_user_get_field( 'access_level' );

		if ( $t_access_level >= $p_access_level ) {
			return true;
		} else {
			return false;
		}
	}
	# --------------------
	# Checks to see if the user should be here.  If not then log the user out.
	function check_access( $p_access_level ) {
		# Administrators ALWAYS pass.
		if ( current_user_get_field( 'access_level' ) >= ADMINISTRATOR ) {
			return;
		}
		if ( !access_level_check_greater_or_equal( $p_access_level ) ) {
			access_denied();
		}
	}
	# --------------------
	# Checks to see if the user has access to this project
	# If not then log the user out
	# If not logged into the project it attempts to log you into that project
	function project_access_check( $p_bug_id, $p_project_id='0' ) {
		global	$g_mantis_project_user_list_table,
				$g_mantis_project_table, $g_mantis_bug_table,
				$g_project_cookie_val;

		project_check( $p_bug_id );

		# Administrators ALWAYS pass.
		if ( current_user_get_field( 'access_level' ) >= ADMINISTRATOR ) {
			return;
		}

		# access_level check
		$t_project_id = get_bug_field( $p_bug_id, 'project_id' );
		$t_project_view_state = project_get_field( $t_project_id, 'view_state' );

		# public project accept all users
		if ( PUBLIC == $t_project_view_state ) {
			return;
		} else {
			# private projects require users to be assigned
			$t_project_access_level = get_project_access_level( $t_project_id );
			if ( -1 == $t_project_access_level ) {
				print_header_redirect( 'login_select_proj_page.php' );
			} else {
				return;
			}
		}
	}
	# --------------------
	# Check to see if the currently logged in project and bug project id match
	# If there is no match then the project cookie will be set to the bug project id
	# No access check is done.  It is expected to be checked afterwards.
	function project_check( $p_bug_id ) {
		global	$g_project_cookie, $g_project_cookie_val, $g_view_all_cookie,
				$g_cookie_time_length, $g_cookie_path;

		$t_project_id = get_bug_field( $p_bug_id, 'project_id' );
		if ( $t_project_id != $g_project_cookie_val ) {
			setcookie( $g_project_cookie, $t_project_id, time()+$g_cookie_time_length, $g_cookie_path );
			setcookie( $g_view_all_cookie, '' );

			$t_redirect_url = get_view_redirect_url( $p_bug_id, 1 );
			print_header_redirect( $t_redirect_url );
		}
	}
	# --------------------
	# Check to see if the current user has access on the specified project
	function check_access_to_project( $p_project_id ) {
		$t_project_view_state = project_get_field( $p_project_id, 'view_state' );

		# Administrators ALWAYS pass.
		if ( current_user_get_field( 'access_level' ) >= ADMINISTRATOR ) {
			return;
		}

		# public project accept all users
		if ( PUBLIC == $t_project_view_state ) {
			return;
		} else {
			# private projects require users to be assigned
			$t_project_access_level = get_project_access_level( $p_project_id );
			# -1 means not assigned, kick them out to the project selection screen
			if ( -1 == $t_project_access_level ) {
				print_header_redirect( 'login_select_proj_page.php' );
			} else { # passed
				return;
			}
		}
	}
	# --------------------
	# return the project access level for the current user/project key pair.
	# use the project_id if supplied.
	function get_project_access_level( $p_project_id=0 ) {
		global	$g_mantis_project_user_list_table,
				$g_project_cookie_val;

		$c_project_id = (integer)$p_project_id;

		$t_user_id = current_user_get_field( 'id' );
		if ( 0 == $p_project_id ) {
			if ( (integer)$g_project_cookie_val == 0 ) {
				return -1;
			}
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$g_project_cookie_val'";
		} else {
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$c_project_id'";
		}
		$result = db_query( $query );
		if ( db_num_rows( $result ) > 0 ) {
			return db_result( $result, 0, 0 );
		} else {
			return -1;
		}
	}
	# --------------------
	# Return the project user list access level for the current user/project key pair if it exists.
	# Otherwise return the default user access level.
	function get_effective_access_level( $p_user_id=0, $p_project_id=-1 ) {
		global	$g_mantis_project_user_list_table,
				$g_project_cookie_val;

		$c_project_id = (integer)$p_project_id;

		# use the current user unless otherwise specified
		if ( 0 == $p_user_id ) {
			$t_user_id = current_user_get_field( 'id' );
		} else {
			$t_user_id = (integer)$p_user_id;
		}

		# all projects
		if ( -1 == $p_project_id ) {
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$g_project_cookie_val'";
		} else if ( 0 == $p_project_id ) {
			$g_project_cookie_val = p_project_id;
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id'";
		} else {
			$query = "SELECT access_level
					FROM $g_mantis_project_user_list_table
					WHERE user_id='$t_user_id' AND project_id='$c_project_id'";
		}

		$result = db_query( $query );
		$count = db_num_rows( $result, 0, 0 );
		if ( $count>0 ) {
			return db_result( $result, 0, 0 );
		} else {
			return user_get_field( $t_user_id, 'access_level' );
		}
	}


?>
