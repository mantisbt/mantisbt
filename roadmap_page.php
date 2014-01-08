<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'bug_api.php' );

	# Print header for the specified project version.
	function print_version_header( $p_version_row ) {
		$t_project_id   = $p_version_row['project_id'];
		$t_version_id   = $p_version_row['id'];
		$t_version_name = $p_version_row['version'];
		$t_project_name = project_get_field( $t_project_id, 'name' );

		$t_release_title = '<a href="roadmap_page.php?project_id=' . $t_project_id . '">' . string_display_line( $t_project_name ) . '</a> - <a href="roadmap_page.php?version_id=' . $t_version_id . '">' . string_display_line( $t_version_name ) . '</a>';

		if ( config_get( 'show_roadmap_dates' ) ) {
			$t_version_timestamp = $p_version_row['date_order'];

			$t_scheduled_release_date = ' (' . lang_get( 'scheduled_release' ) . ' ' . string_display_line( date( config_get( 'short_date_format' ), $t_version_timestamp ) ) . ')';
		} else {
			$t_scheduled_release_date = '';
		}

		echo '<tt>';
		echo '<br />', $t_release_title, $t_scheduled_release_date, lang_get( 'word_separator' ), print_bracket_link( 'view_all_set.php?type=1&temporary=y&' . FILTER_PROPERTY_PROJECT_ID . '=' . $t_project_id . '&' . filter_encode_field_and_value( FILTER_PROPERTY_TARGET_VERSION, $t_version_name ), lang_get( 'view_bugs_link' ) ), '<br />';

		$t_release_title_without_hyperlinks = $t_project_name . ' - ' . $t_version_name . $t_scheduled_release_date;
		echo utf8_str_pad( '', utf8_strlen( $t_release_title_without_hyperlinks ), '=' ), '<br />';
	}

	# print project header
	function print_project_header_roadmap( $p_project_name ) {
		echo '<br /><span class="pagetitle">', string_display( $p_project_name ), ' - ', lang_get( 'roadmap' ), '</span><br />';
	}

	$t_user_id = auth_get_current_user_id();

	$f_project = gpc_get_string( 'project', '' );
	if ( is_blank( $f_project ) ) {
		$f_project_id = gpc_get_int( 'project_id', -1 );
	} else {
		$f_project_id = project_get_id_by_name( $f_project );

		if ( $f_project_id === 0 ) {
			trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
		}
	}

	$f_version = gpc_get_string( 'version', '' );

	if ( is_blank( $f_version ) ) {
		$f_version_id = gpc_get_int( 'version_id', -1 );

		# If both version_id and project_id parameters are supplied, then version_id take precedence.
		if ( $f_version_id == -1 ) {
			if ( $f_project_id == -1 ) {
				$t_project_id = helper_get_current_project();
			} else {
				$t_project_id = $f_project_id;
			}
		} else {
			$t_project_id = version_get_field( $f_version_id, 'project_id' );
		}
	} else {
		if ( $f_project_id == -1 ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = $f_project_id;
		}

		$f_version_id = version_get_id( $f_version, $t_project_id );

		if ( $f_version_id === false ) {
			error_parameters( $f_version );
			trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
		}
	}

	if ( ALL_PROJECTS == $t_project_id ) {
		$t_topprojects = $t_project_ids = user_get_accessible_projects( $t_user_id );
		foreach ( $t_topprojects as $t_project ) {
			$t_project_ids = array_merge( $t_project_ids, user_get_all_accessible_subprojects( $t_user_id, $t_project ) );
		}

		$t_project_ids_to_check = array_unique( $t_project_ids );
		$t_project_ids = array();

		foreach ( $t_project_ids_to_check as $t_project_id ) {
			$t_roadmap_view_access_level = config_get( 'roadmap_view_threshold', null, null, $t_project_id );
			if ( access_has_project_level( $t_roadmap_view_access_level, $t_project_id ) ) {
				$t_project_ids[] = $t_project_id;
			}
		}
	} else {
		access_ensure_project_level( config_get( 'roadmap_view_threshold' ), $t_project_id );
		$t_project_ids = user_get_all_accessible_subprojects( $t_user_id, $t_project_id );
		array_unshift( $t_project_ids, $t_project_id );
	}

	html_page_top( lang_get( 'roadmap' ) );

	$t_project_index = 0;

	version_cache_array_rows( $t_project_ids );
	category_cache_array_rows_by_project( $t_project_ids );

	foreach( $t_project_ids as $t_project_id ) {
		$t_project_name = project_get_field( $t_project_id, 'name' );
		$t_can_view_private = access_has_project_level( config_get( 'private_bug_threshold' ), $t_project_id );

		$t_limit_reporters = config_get( 'limit_reporters' );
		$t_user_access_level_is_reporter = ( REPORTER == access_get_project_level( $t_project_id ) );

		$t_resolved = config_get( 'bug_resolved_status_threshold' );
		$t_bug_table	= db_get_table( 'mantis_bug_table' );
		$t_relation_table = db_get_table( 'mantis_bug_relationship_table' );

		$t_version_rows = array_reverse( version_get_all_rows( $t_project_id ) );

		# cache category info, but ignore the results for now
		category_get_all_rows( $t_project_id );

		$t_project_header_printed = false;

		foreach( $t_version_rows as $t_version_row ) {
			if ( $t_version_row['released'] == 1 ) {
				continue;
			}

			# Skip all versions except the specified one (if any).
			if ( $f_version_id != -1 && $f_version_id != $t_version_row['id'] ) {
				continue;
			}

			$t_issues_planned = 0;
			$t_issues_resolved = 0;
			$t_issues_counted = array();

			$t_version_header_printed = false;

			$t_version = $t_version_row['version'];

			$query = "SELECT sbt.*, $t_relation_table.source_bug_id, dbt.target_version as parent_version FROM $t_bug_table AS sbt
						LEFT JOIN $t_relation_table ON sbt.id=$t_relation_table.destination_bug_id AND $t_relation_table.relationship_type=2
						LEFT JOIN $t_bug_table AS dbt ON dbt.id=$t_relation_table.source_bug_id
						WHERE sbt.project_id=" . db_param() . " AND sbt.target_version=" . db_param() . " ORDER BY sbt.status ASC, sbt.last_updated DESC";

			$t_description = $t_version_row['description'];

			$t_first_entry = true;

			$t_result = db_query_bound( $query, Array( $t_project_id, $t_version ) );

			$t_issue_ids = array();
			$t_issue_parents = array();
			$t_issue_handlers = array();

			while ( $t_row = db_fetch_array( $t_result ) ) {
				# hide private bugs if user doesn't have access to view them.
				if ( !$t_can_view_private && ( $t_row['view_state'] == VS_PRIVATE ) ) {
					continue;
				}

				bug_cache_database_result( $t_row );

				# check limit_Reporter (Issue #4770)
				# reporters can view just issues they reported
				if ( ON === $t_limit_reporters && $t_user_access_level_is_reporter &&
					 !bug_is_user_reporter( $t_row['id'], $t_user_id )) {
					continue;
				}

				$t_issue_id = $t_row['id'];
				$t_issue_parent = $t_row['source_bug_id'];
				$t_parent_version = $t_row['parent_version'];

				if ( !helper_call_custom_function( 'roadmap_include_issue', array( $t_issue_id ) ) ) {
					continue;
				}

				if ( !isset( $t_issues_counted[$t_issue_id] ) ) {
					$t_issues_planned++;

					if ( bug_is_resolved( $t_issue_id ) ) {
						$t_issues_resolved++;
					}

					$t_issues_counted[$t_issue_id] = true;
				}

				if ( 0 === strcasecmp( $t_parent_version, $t_version ) ) {
					$t_issue_ids[] = $t_issue_id;
					$t_issue_parents[] = $t_issue_parent;
				} else if ( !in_array( $t_issue_id, $t_issue_ids ) ) {
					$t_issue_ids[] = $t_issue_id;
					$t_issue_parents[] = null;
				}

				$t_issue_handlers[] = $t_row['handler_id'];
			}

			user_cache_array_rows( array_unique( $t_issue_handlers ) );

			$t_progress = $t_issues_planned > 0 ? ( (integer) ( $t_issues_resolved * 100 / $t_issues_planned ) ) : 0;

			if ( $t_issues_planned > 0 ) {
				$t_progress = (integer) ( $t_issues_resolved * 100 / $t_issues_planned );

 				if ( !$t_project_header_printed ) {
					print_project_header_roadmap( $t_project_name );
					$t_project_header_printed = true;
				}

				if ( !$t_version_header_printed ) {
					print_version_header( $t_version_row );
					$t_version_header_printed = true;
				}

				if ( !is_blank( $t_description ) ) {
					echo string_display( '<br />' .$t_description . '<br />' );
				}

				// show progress bar
				echo '<div class="progress400">';
				echo '  <span class="bar" style="width: ' . $t_progress . '%;">' . $t_progress . '%</span>';
				echo '</div>';
			}

			$t_issue_set_ids = array();
			$t_issue_set_levels = array();
			$k = 0;

			$t_cycle = false;
			$t_cycle_ids = array();

			while ( 0 < count( $t_issue_ids ) ) {
				$t_issue_id = $t_issue_ids[$k];
				$t_issue_parent = $t_issue_parents[$k];

				if ( in_array( $t_issue_id, $t_cycle_ids ) && in_array( $t_issue_parent, $t_cycle_ids ) ) {
					$t_cycle = true;
				} else {
					$t_cycle = false;
					$t_cycle_ids[] = $t_issue_id;
				}

				if ( $t_cycle || !in_array( $t_issue_parent, $t_issue_ids ) ) {
					$l = array_search( $t_issue_parent, $t_issue_set_ids );
					if ( $l !== false ) {
						for ( $m = $l+1; $m < count( $t_issue_set_ids ) && $t_issue_set_levels[$m] > $t_issue_set_levels[$l]; $m++ ) {
							#do nothing
						}
						$t_issue_set_ids_end = array_splice( $t_issue_set_ids, $m );
						$t_issue_set_levels_end = array_splice( $t_issue_set_levels, $m );
						$t_issue_set_ids[] = $t_issue_id;
						$t_issue_set_levels[] = $t_issue_set_levels[$l] + 1;
						$t_issue_set_ids = array_merge( $t_issue_set_ids, $t_issue_set_ids_end );
						$t_issue_set_levels = array_merge( $t_issue_set_levels, $t_issue_set_levels_end );
					} else {
						$t_issue_set_ids[] = $t_issue_id;
						$t_issue_set_levels[] = 0;
					}
					array_splice( $t_issue_ids, $k, 1 );
					array_splice( $t_issue_parents, $k, 1 );

					$t_cycle_ids = array();
				} else {
					$k++;
				}
				if ( count( $t_issue_ids ) <= $k ) {
					$k = 0;
				}
			}

			$t_count_ids = count( $t_issue_set_ids );
			for ( $j = 0; $j < $t_count_ids; $j++ ) {
				$t_issue_set_id = $t_issue_set_ids[$j];
				$t_issue_set_level = $t_issue_set_levels[$j];

				helper_call_custom_function( 'roadmap_print_issue', array( $t_issue_set_id, $t_issue_set_level ) );
			}

			if ( $t_issues_planned > 0 ) {
				echo '<br />';
				echo sprintf( lang_get( 'resolved_progress' ), $t_issues_resolved, $t_issues_planned, $t_progress );
				echo '<br /></tt>';
			}
		}

		$t_project_index++;
	}

	html_page_bottom();
