<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: changelog_page.php,v 1.9 2004-10-25 19:51:02 marcelloscata Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'bug_api.php' );

	# Print header for the specified project version.
	function print_version_header( $p_version_id ) {
		$t_project_id   = version_get_field( $p_version_id, 'project_id' );
		$t_version_name = version_get_field( $p_version_id, 'version' );
		$t_project_name = project_get_field( $t_project_id, 'name' );

		$t_release_title = $t_project_name . ' - ' . $t_version_name;
		echo $t_release_title, '<br />';
		echo str_pad( '', strlen( $t_release_title ), '=' ), '<br />';

		$t_description = version_get_field( $p_version_id, 'description' );
		if ( ( $t_description !== false ) && !is_blank( $t_description ) ) {
			echo string_display( "<br />$t_description<br /><br />" );
		}
	}

	$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );

	# this page is invalid for the 'All Project' selection
	if ( ALL_PROJECTS == $f_project_id ) {
		print_header_redirect( 'login_select_proj_page.php?ref=changelog_page.php' );
	}

	access_ensure_project_level( config_get( 'view_changelog_threshold' ), $f_project_id );

	html_page_top1( lang_get( 'changelog' ) );  // title
	html_page_top2();

	$c_project_id   = db_prepare_int( $f_project_id );
	$t_project_name = project_get_field( $f_project_id, 'name' );
	$t_can_view_private = access_has_project_level( config_get( 'private_bug_threshold' ), $f_project_id );

	$t_limit_reporters = config_get( 'limit_reporters' );
	$t_user_id = auth_get_current_user_id();
	$t_user_access_level_is_reporter = ( current_user_get_access_level() <= config_get( 'report_bug_threshold' ) );

	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	$t_bug_table	= config_get( 'mantis_bug_table' );

	$t_version_rows = version_get_all_rows( $f_project_id );

	echo '<br /><span class="pagetitle">', $t_project_name, ' - ', lang_get( 'changelog' ), '</span><br /><br />';
	echo '<tt>';

	$i = 0;

	foreach( $t_version_rows as $t_version_row ) {
		$t_version = $t_version_row['version'];
		$c_version = db_prepare_string( $t_version );

		$t_version_id = version_get_id( $c_version, $f_project_id );

		$query = "SELECT id, view_state FROM $t_bug_table WHERE project_id='$c_project_id' AND fixed_in_version='$c_version' ORDER BY last_updated DESC";

		$t_description = version_get_field( $t_version_id, 'description' );
		if ( !is_blank( $t_description ) ) {
			if ( $i > 0 ) {
				echo '<br />';
			}

			print_version_header( $t_version_id );
			$t_version_header_printed = true;
		} else {
			$t_version_header_printed = false;
		}
		$t_first_entry = true;
		
		for ( $t_result = db_query( $query ); !$t_result->EOF; $t_result->MoveNext() ) {
			# hide private bugs if user doesn't have access to view them.
			if ( !$t_can_view_private && ( $t_result->fields['view_state'] == VS_PRIVATE ) ) {
				continue;
			}

			# check limit_Reporter (Issue #4770)
			# reporters can view just issues they reported
			if ( ON === $t_limit_reporters && $t_user_access_level_is_reporter &&
			     !bug_is_user_reporter( $t_result->fields['id'], $t_user_id )) {
			  continue;
			}

			$t_issue_id = $t_result->fields['id'];

			if ( !helper_call_custom_function( 'changelog_include_issue', array( $t_issue_id ) ) ) {
				continue;
			}

			# Print the header for the version with the first changelog entry to be added.
			if ( $t_first_entry && !$t_version_header_printed ) {
				if ( $i > 0 ) {
					echo '<br />';
				}

				print_version_header( $t_version_id );

				$t_version_header_printed = true;
				$t_first_entry = false;
			}

			helper_call_custom_function( 'changelog_print_issue', array( $t_issue_id ) );
		}

		$i++;
	}

	echo '</tt>';

	html_page_bottom1( __FILE__ );
?>