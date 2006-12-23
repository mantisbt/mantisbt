<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: roadmap_page.php,v 1.5 2006-12-23 06:23:15 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'bug_api.php' );

	# Print header for the specified project version.
	function print_version_header( $p_version_row ) {
		$t_project_id   = $p_version_row['project_id'];
		$t_version_name = $p_version_row['version'];
		$t_project_name = project_get_field( $t_project_id, 'name' );

		$t_release_title = string_display( $t_project_name ) . ' - ' . string_display( $t_version_name );
		echo $t_release_title, '<br />';
		echo str_pad( '', strlen( $t_release_title ), '=' ), '<br />';
	}

	# print project header
	function print_project_header( $p_project_name ) {
		echo '<br /><span class="pagetitle">', string_display( $p_project_name ), ' - ', lang_get( 'roadmap' ), '</span><br /><br />';
		echo '<tt>';
	}
	
	$t_user_id = auth_get_current_user_id();
	$f_project_id = gpc_get_int( 'project_id', helper_get_current_project() );
	
	$t_roadmap_view_access_level = config_get( 'roadmap_view_threshold' );

	if ( ALL_PROJECTS == $f_project_id ) {
		$t_topprojects = $t_project_ids = user_get_accessible_projects( $t_user_id );
		foreach ( $t_topprojects as $t_project ) {
			$t_project_ids = array_merge( $t_project_ids, user_get_all_accessible_subprojects( $t_user_id, $t_project ) );
		}

		$t_project_ids_to_check = array_unique( $t_project_ids );
		$t_project_ids = array();

		foreach ( $t_project_ids_to_check as $t_project_id ) {
			if ( access_has_project_level( $t_roadmap_view_access_level, $t_project_id ) ) {
				$t_project_ids[] = $t_project_id;
			}
		}
	} else {
		access_ensure_project_level( $t_roadmap_view_access_level, $f_project_id );
		$t_project_ids = user_get_all_accessible_subprojects( $t_user_id, $f_project_id );
		array_unshift( $t_project_ids, $f_project_id );
	}

	html_page_top1( lang_get( 'roadmap' ) );  // title
	html_page_top2();

	$t_project_index = 0;

	foreach( $t_project_ids as $t_project_id ) {
		if ( $t_project_index > 0 ) {
			echo '<br />';
		}

		$c_project_id   = db_prepare_int( $t_project_id );
		$t_project_name = project_get_field( $t_project_id, 'name' );
		$t_can_view_private = access_has_project_level( config_get( 'private_bug_threshold' ), $t_project_id );

		$t_limit_reporters = config_get( 'limit_reporters' );
		$t_user_access_level_is_reporter = ( REPORTER == access_get_project_level( $t_project_id ) );

		$t_resolved = config_get( 'bug_resolved_status_threshold' );
		$t_bug_table	= config_get( 'mantis_bug_table' );

		$t_version_rows = version_get_all_rows( $t_project_id );

		$t_project_header_printed = false;
		
		$i = 0;

		foreach( $t_version_rows as $t_version_row ) {
			if ( $t_version_row['released'] == 1 ) {
				continue;
			}
			
			$t_issues_planned = 0;
			$t_issues_resolved = 0;


			$t_version = $t_version_row['version'];
			$c_version = db_prepare_string( $t_version );

			$query = "SELECT * FROM $t_bug_table WHERE project_id='$c_project_id' AND target_version='$c_version' ORDER BY status ASC, last_updated DESC";

			$t_description = $t_version_row['description'];

			$t_first_entry = true;

			$t_result = db_query( $query );

			$t_issue_ids = array();

			while ( $t_row = db_fetch_array( $t_result ) ) {
				# hide private bugs if user doesn't have access to view them.
				if ( !$t_can_view_private && ( $t_result->fields['view_state'] == VS_PRIVATE ) ) {
					continue;
				}

				bug_cache_database_result( $t_row );

				# check limit_Reporter (Issue #4770)
				# reporters can view just issues they reported
				if ( ON === $t_limit_reporters && $t_user_access_level_is_reporter &&
					 !bug_is_user_reporter( $t_result->fields['id'], $t_user_id )) {
					continue;
				}

				$t_issue_id = $t_row['id'];

				if ( !helper_call_custom_function( 'roadmap_include_issue', array( $t_issue_id ) ) ) {
					continue;
				}
				
				$t_issues_planned++;
				
				if ( bug_is_resolved( $t_issue_id ) ) {
					$t_issues_resolved++;
				}


				$t_issue_ids[] = $t_issue_id;
			}

			$i++;

			if ( $t_issues_planned > 0 ) {
				$t_progress = (integer) ( $t_issues_resolved * 100 / $t_issues_planned );

				print_project_header( $t_project_name );
				$t_project_header_printed = true;

				print_version_header( $t_version_row );
				$t_version_header_printed = true;

				// show progress bar
				echo '<div class="progress400">';
				echo '  <span class="bar" style="width: ' . $t_progress . '%;">' . $t_progress . '%</span>';
				echo '</div>';
			} else {
				$t_project_header_printed = false;
				$t_version_header_printed = false;
			}

			if ( !is_blank( $t_description ) ) {
				echo string_display( "<br />$t_description<br /><br />" );

				#if ( $i > 0 ) {
				#	echo '<br />';
				#}
				
				if ( !$t_project_header_printed ) {
					print_project_header( $t_project_name );
					$t_project_header_printed = true;
				}

				if ( !$t_version_header_printed ) {
					print_version_header( $t_version_row );
					$t_version_header_printed = true;
				}
			}

			foreach ( $t_issue_ids as $t_issue_id ) {
				# Print the header for the version with the first roadmap entry to be added.
				if ( $t_first_entry && !$t_version_header_printed ) {
					if ( $i > 0 ) {
						echo '<br />';
					}

					if ( !$t_project_header_printed ) {
						print_project_header( $t_project_name );
						$t_project_header_printed = true;
					}

					if ( !$t_version_header_printed ) {
						print_version_header( $t_version_row );
						$t_version_header_printed = true;
					}

					$t_first_entry = false;
				}

				helper_call_custom_function( 'roadmap_print_issue', array( $t_issue_id ) );
			}

			if ( $t_issues_planned > 0 ) {
				echo '<br />';
				echo sprintf( lang_get( 'resolved_progress' ), $t_issues_resolved, $t_issues_planned, $t_progress );
				echo '<br /></tt>';
			}
		}
		
		$t_project_index++;
	}

	html_page_bottom1( __FILE__ );
?>