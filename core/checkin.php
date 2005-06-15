#!/usr/local/bin/php -q
<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: checkin.php,v 1.4 2005-06-15 14:46:20 vboctor Exp $
	# --------------------------------------------------------

	global $g_bypass_headers;
	$g_bypass_headers = 1;
	require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );

	# Make sure this script doesn't run via the webserver
	# @@@ This is a hack to detect php-cgi, there must be a better way.
	if ( isset( $_SERVER['SERVER_PORT'] ) ) {
		echo "checkin.php is not allowed to run through the webserver.\n";
		exit( 1 );
	}

	# Check that the username is set and exists
	$t_username = config_get( 'source_control_account' );
	if ( is_blank( $t_username ) || ( user_get_id_by_name( $t_username ) === false ) ) {
		echo "Invalid source control account ('$t_username').\n";
		exit( 1 );
	}

	if ( !defined( "STDIN" ) ) {
		define("STDIN", fopen('php://stdin','r'));
	}

	# Detect references to issues + concat all lines to have the comment log.
	$t_commit_regexp = config_get( 'source_control_regexp' );
    $t_commit_fixed_regexp = config_get( 'source_control_fixed_regexp' );

	$t_comment = '';
	$t_issues = array();
	$t_fixed_issues = array();
	while ( ( $t_line = fgets( STDIN, 1024 ) ) ) {
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
	}

	# If no issues found, then no work to do.
	if ( ( count( $t_issues ) == 0 ) && ( count( $t_fixed_issues ) == 0 ) ) {
		echo "Comment does not reference any issues.\n";
		exit(0);
	}

	# Login as source control user
	if ( !auth_attempt_script_login( $t_username ) ) {
		echo "Unable to login\n";
		exit( 1 );
	}

	# history parameters are reserved for future use.
	$t_history_old_value = '';
	$t_history_new_value = '';

	# Call the custom function to register the checkin on each issue.

	foreach ( $t_issues as $t_issue_id ) {
		if ( !in_array( $t_issue_id, $t_fixed_issues ) ) {
			helper_call_custom_function( 'checkin', array( $t_issue_id, $t_comment, $t_history_old_value, $t_history_new_value, false ) );
		}
	}

	foreach ( $t_fixed_issues as $t_issue_id ) {
		helper_call_custom_function( 'checkin', array( $t_issue_id, $t_comment, $t_history_old_value, $t_history_new_value, true ) );
	}

	exit( 0 );
?>
