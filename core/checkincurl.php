<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
# Modification for CURL checkin by Bart van Leeuwen bart-nospam@netage.nl

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
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: checkin.php,v 1.5.2.1 2007-10-13 22:35:16 giallu Exp $
	# --------------------------------------------------------

	global $g_bypass_headers;
	$g_bypass_headers = 1;
	
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	# Check that failover the username is set and exists
	$t_username = config_get( 'source_control_account' );
	if ( is_blank( $t_username ) || ( user_get_id_by_name( $t_username ) === false ) ) {
		echo "Invalid source control account ('$t_username').\n";
		exit( 1 );
	}

	# Detect references to issues + concat all lines to have the comment log.
	$t_commit_regexp = config_get( 'source_control_regexp' );
    $t_commit_fixed_regexp = config_get( 'source_control_fixed_regexp' );
    $t_source_control_server = config_get( 'source_control_server' );

	$t_comment = '';
	$t_issues = array();
	$t_fixed_issues = array();
	
	# check if we are called from the right IP ( @todo might wanna use a array here )
	$t_remote = $_SERVER['REMOTE_ADDR'];
	if(is_array($t_source_control_server)) {
		if( !in_array($t_remote, $t_source_control_server)) {
			echo "Not allowed from this IP ($t_remote)!!\n";
		        exit(0);
		}
	} else {
		if($t_remote != $t_source_control_server ) {
			echo "Not allowed from this IP ($t_remote)!!\n";
			exit(0);
		}	
	}
	$t_line = isset($_POST['log']) ? $_POST['log'] : '';
	#var_dump("$t_line");
		$t_comment .= $t_line;
		if ( preg_match_all( $t_commit_regexp, $t_line, $t_matches ) ) {
			for ( $i = 0; $i < count( $t_matches[0] ); ++$i ) {
				$t_issues[] = $t_matches[1][$i];
			}
		}

		if ( preg_match_all( $t_commit_fixed_regexp, $t_line, $t_matches) ) {
			for ( $i = 0; $i < count( $t_matches[0] ); ++$i ) {
				$t_fixed_issues[] = $t_matches[1][$i];
			}
		}

	# If no issues found, then no work to do.
	if ( ( count( $t_issues ) == 0 ) && ( count( $t_fixed_issues ) == 0 ) ) {
		echo "Comment does not reference any issues.\n";
		exit(0);
	}

   	# first we try to figure out if we can login with the source control user
   	$temp_username = $_POST['user'];
    if( !auth_attempt_script_login( $temp_username ) ) {
    	# Login as source control user
		if ( !auth_attempt_script_login( $t_username ) ) {
			echo "Unable to login\n";
			exit( 1 );
		}	
	}

	# grab alternate message if set
	# this allows one to submt a large change log, but enter a short message
	# I added this to use with a loadbuild process to identify when
	# the bug fixes were configured into a system (and it's build version)
	$t_alt_msg = isset($_POST['msg']) ? $_POST['msg'] : $t_comment;

	# history parameters are reserved for future use.
	$t_history_old_value = '';
	$t_history_new_value = '';

	# add note to each bug only once
	$t_issues = array_unique( $t_issues );
	$t_fixed_issues = array_unique( $t_fixed_issues );

	# Call the custom function to register the checkin on each issue.

	foreach ( $t_issues as $t_issue_id ) {
		if ( !in_array( $t_issue_id, $t_fixed_issues ) ) {
			helper_call_custom_function( 'checkin', array( $t_issue_id, $t_alt_msg, $t_history_old_value, $t_history_new_value, false ) );
		}
	}

	foreach ( $t_fixed_issues as $t_issue_id ) {
		helper_call_custom_function( 'checkin', array( $t_issue_id, $t_alt_msg, $t_history_old_value, $t_history_new_value, true ) );
	}

	exit( 0 );
?>
