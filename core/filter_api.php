<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: filter_api.php,v 1.122 2005-08-26 17:05:11 thraxisp Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'user_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'collapse_api.php' );
	require_once( $t_core_dir . 'relationship_api.php' );

	###########################################################################
	# Filter API
	###########################################################################

	# Get the standard filter that is to be used when no filter was previously saved.
	# When creating specific filters, this can be used as a basis for the filter, where
	# specific entries can be overridden.
	function filter_get_default() {
		$t_hide_status_default  = config_get( 'hide_status_default' );
		$t_default_show_changed = config_get( 'default_show_changed' );

		$t_filter = array(
			'show_category'		=> Array ( '0' => META_FILTER_ANY ),
			'show_severity'		=> Array ( '0' => META_FILTER_ANY ),
			'show_status'		=> Array ( '0' => META_FILTER_ANY ),
			'highlight_changed'	=> $t_default_show_changed,
			'reporter_id'		=> Array ( '0' => META_FILTER_ANY ),
			'handler_id'		=> Array ( '0' => META_FILTER_ANY ),
			'show_resolution'	=> Array ( '0' => META_FILTER_ANY ),
			'show_build'		=> Array ( '0' => META_FILTER_ANY ),
			'show_version'		=> Array ( '0' => META_FILTER_ANY ),
			'hide_status'		=> Array ( '0' => $t_hide_status_default ),
			'user_monitor'		=> Array ( '0' => META_FILTER_ANY ),
			'sort'              => 'last_updated',
			'dir'               => 'DESC'
		);

		return $t_filter;
	}

	# @@@ Had to make all these parameters required because we can't use
	#  call-time pass by reference anymore.  I really preferred not having
	#  to pass all the params in if you didn't want to, but I wanted to get
	#  rid of the errors for now.  If we can think of a better way later
	#  (maybe return an object) that would be great.
	#
	# $p_page_numer
	#   - the page you want to see (set to the actual page on return)
	# $p_per_page
	#   - the number of bugs to see per page (set to actual on return)
	#     -1   indicates you want to see all bugs
	#     null indicates you want to use the value specified in the filter
	# $p_page_count
	#   - you don't need to give a value here, the number of pages will be
	#     stored here on return
	# $p_bug_count
	#   - you don't need to give a value here, the number of bugs will be
	#     stored here on return
	# $p_custom_filter
	#   - Filter to use.
	# $p_project_id
	#   - project id to use in filtering.
	# $p_user_id
	#   - user id to use as current user when filtering.
	# $p_show_sticky
	#	- get sticky issues only.
	function filter_get_bug_rows( &$p_page_number, &$p_per_page, &$p_page_count, &$p_bug_count, $p_custom_filter = null, $p_project_id = null, $p_user_id = null, $p_show_sticky = null ) {
		$t_bug_table			= config_get( 'mantis_bug_table' );
		$t_bug_text_table		= config_get( 'mantis_bug_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_custom_field_string_table	= config_get( 'mantis_custom_field_string_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_project_table		= config_get( 'mantis_project_table' );
		$t_bug_monitor_table	= config_get( 'mantis_bug_monitor_table' );
		$t_limit_reporters		= config_get( 'limit_reporters' );
		$t_bug_relationship_table	= config_get( 'mantis_bug_relationship_table' );
		$t_report_bug_threshold		= config_get( 'report_bug_threshold' );

		$t_current_user_id = auth_get_current_user_id();

		if ( null === $p_user_id ) {
			$t_user_id = $t_current_user_id;
		} else {
			$t_user_id = $p_user_id;
		}

		$c_user_id = db_prepare_int( $t_user_id );

		if ( null === $p_project_id ) {
			$t_project_id	= helper_get_current_project();
		} else {
			$t_project_id	= $p_project_id;
		}

		if ( $p_custom_filter === null ) {
			# Prefer current_user_get_bug_filter() over user_get_filter() when applicable since it supports
			# cookies set by previous version of the code.
			if ( $t_user_id == $t_current_user_id ) {
				$t_filter = current_user_get_bug_filter();
			} else {
				$t_filter = user_get_bug_filter( $t_user_id, $t_project_id );
			}
		} else {
			$t_filter = $p_custom_filter;
		}

		$t_filter = filter_ensure_valid_filter( $t_filter );

		if ( false === $t_filter ) {
			return false; # signify a need to create a cookie
			#@@@ error instead?
		}

		$t_where_clauses = array( "$t_project_table.enabled = 1", "$t_project_table.id = $t_bug_table.project_id" );
		$t_select_clauses = array( "$t_bug_table.*" );
		$t_join_clauses = array();
		$t_from_clauses = array();

		if ( ALL_PROJECTS == $t_project_id ) {
			if ( !user_is_administrator( $t_user_id ) ) {
				$t_topprojects = $t_projects = user_get_accessible_projects( $t_user_id );
				foreach ( $t_topprojects as $t_project ) {
					$t_projects = array_merge( $t_projects, user_get_all_accessible_subprojects( $t_user_id, $t_project ) );
				}

				$t_projects = array_unique( $t_projects );

				if ( 0 == count( $t_projects ) ) {
					return array();  # no accessible projects, return an empty array
				} else if ( 1 == count( $t_projects ) ) {
					$t_project = $t_projects[0];
					array_push( $t_where_clauses, "( $t_bug_table.project_id=$t_project )" );
				} else {
					array_push( $t_where_clauses, "( $t_bug_table.project_id in (". implode( ', ', $t_projects ) . ") )" );
				}
			}
		} else {
			access_ensure_project_level( VIEWER, $t_project_id, $t_user_id );

			$t_projects = user_get_all_accessible_subprojects( $t_user_id, $t_project_id );
			$t_projects[] = $t_project_id;

			$t_projects = array_unique( $t_projects );

			if ( 1 == count( $t_projects ) ) {
				$t_project = $t_projects[0];
				array_push( $t_where_clauses, "( $t_bug_table.project_id=$t_project )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.project_id in (". implode( ', ', $t_projects ) . ") )" );
			}
		}

		# private bug selection
		if ( !access_has_project_level( config_get( 'private_bug_threshold' ), $t_project_id, $t_user_id ) ) {
			$t_public = VS_PUBLIC;
			$t_private = VS_PRIVATE;
			switch ( $t_filter['view_state'] ) {
				case META_FILTER_ANY:
					array_push( $t_where_clauses, "($t_bug_table.view_state='$t_public' OR $t_bug_table.reporter_id='$t_user_id')" );
					break;
				case VS_PUBLIC:
					array_push( $t_where_clauses, "($t_bug_table.view_state='$t_public')" );
					break;
				case VS_PRIVATE:
					array_push( $t_where_clauses, "($t_bug_table.view_state='$t_private' AND $t_bug_table.reporter_id='$t_user_id')" );
					break;
			}
		} else {
			$t_view_state = db_prepare_int( $t_filter['view_state'] );
			if ( ( $t_filter['view_state'] != META_FILTER_ANY ) && ( !is_blank( $t_filter['view_state'] ) ) ) {
				array_push( $t_where_clauses, "($t_bug_table.view_state='$t_view_state')" );
			}
		}

		# reporter
		$t_any_found = false;

		foreach( $t_filter['reporter_id'] as $t_filter_member ) {
			if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['reporter_id'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['reporter_id'] as $t_filter_member ) {
				if ( META_FILTER_NONE == $t_filter_member ) {
					array_push( $t_clauses, "0" );
				} else {
					$c_reporter_id = db_prepare_int( $t_filter_member );
					if ( META_FILTER_MYSELF == $c_reporter_id ) {
						array_push( $t_clauses, $c_user_id );
					} else {
						array_push( $t_clauses, $c_reporter_id );
					}
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.reporter_id in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.reporter_id=$t_clauses[0] )" );
			}
		}

		# limit reporter
		# @@@ thraxisp - access_has_project_level checks greater than or equal to,
		#   this assumed that there aren't any holes above REPORTER where the limit would apply
		#
		if ( ( ON === $t_limit_reporters ) && ( ! access_has_project_level( REPORTER + 1, $t_project_id, $t_user_id ) ) ) {
			$c_reporter_id = $c_user_id;
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# handler
		$t_any_found = false;

		foreach( $t_filter['handler_id'] as $t_filter_member ) {
			if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['handler_id'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['handler_id'] as $t_filter_member ) {
				if ( META_FILTER_NONE == $t_filter_member ) {
					array_push( $t_clauses, 0 );
				} else {
					$c_handler_id = db_prepare_int( $t_filter_member );
					if ( META_FILTER_MYSELF == $c_handler_id ) {
						array_push( $t_clauses, $c_user_id );
					} else {
						array_push( $t_clauses, $c_handler_id );
					}
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.handler_id in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.handler_id=$t_clauses[0] )" );
			}
		}

		# category
		$t_any_found = false;

		foreach( $t_filter['show_category'] as $t_filter_member ) {
			if ( META_FILTER_ANY == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_category'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_category'] as $t_filter_member ) {
				$t_filter_member = stripslashes( $t_filter_member );
				if ( META_FILTER_NONE == $t_filter_member ) {
					array_push( $t_clauses, "''" );
				} else {
					$c_show_category = db_prepare_string( $t_filter_member );
					array_push( $t_clauses, "'$c_show_category'" );
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.category in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.category=$t_clauses[0] )" );
			}
		}

		# severity
		$t_any_found = false;
		foreach( $t_filter['show_severity'] as $t_filter_member ) {
			if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_severity'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_severity'] as $t_filter_member ) {
				$c_show_severity = db_prepare_int( $t_filter_member );
				array_push( $t_clauses, $c_show_severity );
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.severity in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.severity=$t_clauses[0] )" );
			}
		}

		# show / hide status
		# take a list of all available statuses then remove the ones that we want hidden, then make sure
		# the ones we want shown are still available
		$t_status_arr = explode_enum_string( config_get( 'status_enum_string' ) );
		$t_available_statuses = array();
		$t_desired_statuses = array();
		foreach( $t_status_arr as $t_this_status ) {
			$t_this_status_arr = explode_enum_arr( $t_this_status );
			$t_available_statuses[] = $t_this_status_arr[0];
		}

		if ( 'simple' == $t_filter['_view_type'] ) {
			# simple filtering: if showing any, restrict by the hide status value, otherwise ignore the hide
			$t_any_found = false;
			$t_this_status = $t_filter['show_status'][0];
			$t_this_hide_status = $t_filter['hide_status'][0];

			if ( ( META_FILTER_ANY == $t_this_status ) || ( is_blank( $t_this_status ) ) || ( 0 === $t_this_status ) ) {
				$t_any_found = true;
			}
			if ( $t_any_found ) {
				foreach( $t_available_statuses as $t_this_available_status ) {
					if ( $t_this_hide_status > $t_this_available_status ) {
						$t_desired_statuses[] = $t_this_available_status;
					}
				}
			} else {
				$t_desired_statuses[] = $t_this_status;
			}
		} else {
			# advanced filtering: ignore the hide
			$t_any_found = false;
			foreach( $t_filter['show_status'] as $t_this_status ) {
				$t_desired_statuses[] = $t_this_status;
				if ( ( META_FILTER_ANY == $t_this_status ) || ( is_blank( $t_this_status ) ) || ( 0 === $t_this_status ) ) {
					$t_any_found = true;
				}
			}
			if ( $t_any_found ) {
				$t_desired_statuses = array();
			}
		}

		if ( count( $t_desired_statuses ) > 0 ) {
			$t_clauses = array();

			foreach( $t_desired_statuses as $t_filter_member ) {
				$c_show_status = db_prepare_int( $t_filter_member );
				array_push( $t_clauses, $c_show_status );
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.status in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.status=$t_clauses[0] )" );
			}
		}

		# resolution
		$t_any_found = false;
		foreach( $t_filter['show_resolution'] as $t_filter_member ) {
			if ( META_FILTER_ANY == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_resolution'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_resolution'] as $t_filter_member ) {
				$c_show_resolution = db_prepare_int( $t_filter_member );
				array_push( $t_clauses, $c_show_resolution );
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.resolution in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.resolution=$t_clauses[0] )" );
			}
		}

		# priority
		$t_any_found = false;
		foreach( $t_filter['show_priority'] as $t_filter_member ) {
				if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
					$t_any_found = true;
				}
		}
		if ( count( $t_filter['show_priority'] ) == 0 ) {
				$t_any_found = true;
		}
		if ( !$t_any_found ) {
				$t_clauses = array();

				foreach( $t_filter['show_priority'] as $t_filter_member ) {
						$c_show_priority = db_prepare_int( $t_filter_member );
						array_push( $t_clauses, $c_show_priority );
				}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.priority in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.priority=$t_clauses[0] )" );
			}
		}


		# product build
		$t_any_found = false;
		foreach( $t_filter['show_build'] as $t_filter_member ) {
				if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_build'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_build'] as $t_filter_member ) {
				$t_filter_member = stripslashes( $t_filter_member );
				if ( META_FILTER_NONE == $t_filter_member ) {
					array_push( $t_clauses, "''" );
				} else {
					$c_show_build = db_prepare_string( $t_filter_member );
					array_push( $t_clauses, "'$c_show_build'" );
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.build in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.build=$t_clauses[0] )" );
			}
		}

		# product version
		$t_any_found = false;
		foreach( $t_filter['show_version'] as $t_filter_member ) {
				if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_version'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_version'] as $t_filter_member ) {
				$t_filter_member = stripslashes( $t_filter_member );
				if ( META_FILTER_NONE == $t_filter_member ) {
					array_push( $t_clauses, "''" );
				} else {
					$c_show_version = db_prepare_string( $t_filter_member );
					array_push( $t_clauses, "'$c_show_version'" );
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.version in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.version=$t_clauses[0] )" );
			}
		}

		# profile
		$t_any_found = false;
		foreach( $t_filter['show_profile'] as $t_filter_member ) {
				if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_profile'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_profile'] as $t_filter_member ) {
				$t_filter_member = stripslashes( $t_filter_member );
				if ( META_FILTER_NONE == $t_filter_member ) {
					array_push( $t_clauses, "0" );
				} else {
					$c_show_profile = db_prepare_int( $t_filter_member );
					array_push( $t_clauses, "$c_show_profile" );
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.profile_id in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.profile_id=$t_clauses[0] )" );
			}
		}

		# date filter
		if ( ( 'on' == $t_filter['do_filter_by_date'] ) &&
				is_numeric( $t_filter['start_month'] ) &&
				is_numeric( $t_filter['start_day'] ) &&
				is_numeric( $t_filter['start_year'] ) &&
				is_numeric( $t_filter['end_month'] ) &&
				is_numeric( $t_filter['end_day'] ) &&
				is_numeric( $t_filter['end_year'] )
			) {

			$t_start_string = db_prepare_string( $t_filter['start_year']  . "-". $t_filter['start_month']  . "-" . $t_filter['start_day'] ." 00:00:00" );
			$t_end_string   = db_prepare_string( $t_filter['end_year']  . "-". $t_filter['end_month']  . "-" . $t_filter['end_day'] ." 23:59:59" );

			array_push( $t_where_clauses, "($t_bug_table.date_submitted BETWEEN '$t_start_string' AND '$t_end_string' )" );
		}

		# fixed in version
		$t_any_found = false;
		foreach( $t_filter['fixed_in_version'] as $t_filter_member ) {
			if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['fixed_in_version'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['fixed_in_version'] as $t_filter_member ) {
				$t_filter_member = stripslashes( $t_filter_member );
				if ( META_FILTER_NONE == $t_filter_member ) {
					array_push( $t_clauses, "''" );
				} else {
					$c_fixed_in_version = db_prepare_string( $t_filter_member );
					array_push( $t_clauses, "'$c_fixed_in_version'" );
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_bug_table.fixed_in_version in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_bug_table.fixed_in_version=$t_clauses[0] )" );
			}
		}

		# users monitoring a bug
		$t_any_found = false;
		foreach( $t_filter['user_monitor'] as $t_filter_member ) {
			if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['user_monitor'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();
			$t_table_name = 'user_monitor';
			array_push( $t_from_clauses, $t_bug_monitor_table );
			array_push( $t_join_clauses, "LEFT JOIN $t_bug_monitor_table $t_table_name ON $t_table_name.bug_id = $t_bug_table.id" );

			foreach( $t_filter['user_monitor'] as $t_filter_member ) {
				$c_user_monitor = db_prepare_int( $t_filter_member );
				if ( META_FILTER_MYSELF == $c_user_monitor ) {
					array_push( $t_clauses, $c_user_id );
				} else {
					array_push( $t_clauses, $c_user_monitor );
				}
			}
			if ( 1 < count( $t_clauses ) ) {
				array_push( $t_where_clauses, "( $t_table_name.user_id in (". implode( ', ', $t_clauses ) .") )" );
			} else {
				array_push( $t_where_clauses, "( $t_table_name.user_id=$t_clauses[0] )" );
			}
		}
		# bug relationship
		$t_any_found = false;
		$c_rel_type = $t_filter['relationship_type'];
		$c_rel_bug = $t_filter['relationship_bug'];
		if ( -1 == $c_rel_type || 0 == $c_rel_bug) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
		    # use the complementary type
		    $c_rel_type = relationship_get_complementary_type($c_rel_type);
			$t_clauses = array();
			$t_table_name = 'relationship';
			array_push( $t_from_clauses, $t_bug_relationship_table );
			array_push( $t_join_clauses, "LEFT JOIN $t_bug_relationship_table $t_table_name ON $t_table_name.destination_bug_id = $t_bug_table.id" );
			// get reverse relationships
			if ( $c_rel_type == 1 ) array_push( $t_join_clauses, "LEFT JOIN $t_bug_relationship_table $t_table_name"."2 ON $t_table_name"."2.source_bug_id = $t_bug_table.id" );
 			array_push( $t_clauses, "($t_table_name.relationship_type='$c_rel_type' AND $t_table_name.source_bug_id='$c_rel_bug')" );
 			// get reverse relationships
			if ( $c_rel_type == 1 ) array_push( $t_clauses, "($t_table_name"."2.relationship_type='$c_rel_type' AND $t_table_name"."2.destination_bug_id='$c_rel_bug')" );
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# custom field filters
		if( ON == config_get( 'filter_by_custom_fields' ) ) {
			# custom field filtering
			$t_custom_fields = custom_field_get_linked_ids( $t_project_id );

			foreach( $t_custom_fields as $t_cfid ) {
				$t_first_time = true;
				$t_custom_where_clause = '';
				# Ignore all custom filters that are not set, or that are set to '' or "any"
				$t_any_found = false;
				foreach( $t_filter['custom_fields'][$t_cfid] as $t_filter_member ) {
				if ( ( META_FILTER_ANY == $t_filter_member ) || ( 0 === $t_filter_member ) ) {
						$t_any_found = true;
					}
				}
				if ( !isset( $t_filter['custom_fields'][$t_cfid] ) ) {
					$t_any_found = true;
				}
				if ( !$t_any_found ) {
					$t_def = custom_field_get_definition( $t_cfid );
					$t_table_name = $t_custom_field_string_table . '_' . $t_cfid;
					array_push( $t_join_clauses, "LEFT JOIN $t_custom_field_string_table $t_table_name ON $t_table_name.bug_id = $t_bug_table.id" );

					if ($t_def['type'] == CUSTOM_FIELD_TYPE_DATE) {
						switch ($t_filter['custom_fields'][$t_cfid][0]) {
						case CUSTOM_FIELD_DATE_ANY:
							break ;
						case CUSTOM_FIELD_DATE_NONE:
							// need to modify that last join, nasty I know but unless you want to upgrade everyone using mysql to 4.1.....
							$t_my_join = array_pop($t_join_clauses) ;
							$t_my_join .= ' AND ' . $t_table_name . '.field_id = ' . $t_cfid ;
							array_push( $t_join_clauses, $t_my_join ) ;
							$t_custom_where_clause = '(( ' . $t_table_name . '.bug_id is null) OR ( ' . $t_table_name . '.value = 0)' ;
							break ;
						case CUSTOM_FIELD_DATE_BEFORE:
							$t_custom_where_clause = '(( ' . $t_table_name . '.field_id = ' . $t_cfid . ' AND ' . $t_table_name . '.value != 0 AND (' . $t_table_name . '.value+0) < ' . ($t_filter['custom_fields'][$t_cfid][2]) . ')' ;
							break ;
						case CUSTOM_FIELD_DATE_AFTER:
							$t_custom_where_clause = '(( ' . $t_table_name . '.field_id = ' . $t_cfid . ' AND (' . $t_table_name . '.value+0) > ' . ($t_filter['custom_fields'][$t_cfid][1]+1) . ')' ;
							break ;
						default:
							$t_custom_where_clause = '(( ' . $t_table_name . '.field_id = ' . $t_cfid . ' AND (' . $t_table_name . '.value+0) BETWEEN ' . $t_filter['custom_fields'][$t_cfid][1] . ' AND ' . $t_filter['custom_fields'][$t_cfid][2] . ')' ;
							break ;
						}
					} else {

						foreach( $t_filter['custom_fields'][$t_cfid] as $t_filter_member ) {
							if ( isset( $t_filter_member ) &&
								( META_FILTER_ANY != strtolower( $t_filter_member ) ) ) {

								$t_filter_member = stripslashes( $t_filter_member );
								if ( META_FILTER_NONE == $t_filter_member ) { # coerce filter value if selecting META_FILTER_NONE
									$t_filter_member = '';
								}

								if( $t_first_time ) {
									$t_first_time = false;
									$t_custom_where_clause = '(';
								} else {
									$t_custom_where_clause .= ' OR ';
								}

								$t_custom_where_clause .= "(  $t_table_name.field_id = $t_cfid AND $t_table_name.value ";
								switch( $t_def['type'] ) {
								case CUSTOM_FIELD_TYPE_MULTILIST:
								case CUSTOM_FIELD_TYPE_CHECKBOX:
									$t_custom_where_clause .= "LIKE '%|";
									$t_custom_where_clause_closing = "|%' )";
									break;
								default:
									$t_custom_where_clause .= "= '";
									$t_custom_where_clause_closing = "' )";
								}
								$t_custom_where_clause .= db_prepare_string( $t_filter_member );
								$t_custom_where_clause .= $t_custom_where_clause_closing;
							}
						}
					}
					if ( !is_blank( $t_custom_where_clause ) ) {
						array_push( $t_where_clauses, $t_custom_where_clause . ')' );
					}
				}
			}
		}

		$t_textsearch_where_clause = '';
		$t_textsearch_wherejoin_clause = '';
		# Simple Text Search - Thanks to Alan Knowles
		if ( !is_blank( $t_filter['search'] ) ) {
			$c_search = db_prepare_string( $t_filter['search'] );
			$c_search_int = db_prepare_int( $t_filter['search'] );
			$t_textsearch_where_clause = "((summary LIKE '%$c_search%')
							 OR ($t_bug_text_table.description LIKE '%$c_search%')
							 OR ($t_bug_text_table.steps_to_reproduce LIKE '%$c_search%')
							 OR ($t_bug_text_table.additional_information LIKE '%$c_search%')
							 OR ($t_bug_table.id = '$c_search_int'))";

			$t_textsearch_wherejoin_clause = "((summary LIKE '%$c_search%')
							 OR ($t_bug_text_table.description LIKE '%$c_search%')
							 OR ($t_bug_text_table.steps_to_reproduce LIKE '%$c_search%')
							 OR ($t_bug_text_table.additional_information LIKE '%$c_search%')
							 OR ($t_bug_table.id LIKE '%$c_search%')
							 OR ($t_bugnote_text_table.note LIKE '%$c_search%'))";

			array_push( $t_where_clauses, "($t_bug_text_table.id = $t_bug_table.bug_text_id)" );

			$t_from_clauses = array( $t_bug_text_table, $t_project_table, $t_bug_table );
		} else {
			$t_from_clauses = array( $t_project_table, $t_bug_table );
		}

		$t_select	= implode( ', ', array_unique( $t_select_clauses ) );
		$t_from		= 'FROM ' . implode( ', ', array_unique( $t_from_clauses ) );
		$t_join		= implode( ' ', $t_join_clauses );
		if ( count( $t_where_clauses ) > 0 ) {
			$t_where	= 'WHERE ' . implode( ' AND ', $t_where_clauses );
		} else {
			$t_where	= '';
		}

		# Possibly do two passes. First time, grab the IDs of issues that match the filters. Second time, grab the IDs of issues that
		# have bugnotes that match the text search if necessary.
		$t_id_array = array();
		for ( $i = 0; $i < 2; $i++ ) {
			$t_id_where = $t_where;
			$t_id_join = $t_join;
			if ( $i == 0 ) {
				if ( !is_blank( $t_id_where ) && !is_blank( $t_textsearch_where_clause ) ) {
					$t_id_where = $t_id_where . ' AND ' . $t_textsearch_where_clause;
				}
			} else if ( !is_blank( $t_textsearch_wherejoin_clause ) ) {
				$t_id_where = $t_id_where . ' AND ' . $t_textsearch_wherejoin_clause;
				$t_id_join = $t_id_join . " INNER JOIN $t_bugnote_table ON $t_bugnote_table.bug_id = $t_bug_table.id";
				$t_id_join = $t_id_join . " INNER JOIN $t_bugnote_text_table ON $t_bugnote_text_table.id = $t_bugnote_table.bugnote_text_id";
			}
			$query  = "SELECT DISTINCT $t_bug_table.id AS id
						$t_from
						$t_id_join
						$t_id_where";
			if ( ( $i == 0 ) || ( !is_blank( $t_textsearch_wherejoin_clause ) ) ) {
				$result = db_query( $query );
				$row_count = db_num_rows( $result );

				for ( $j=0; $j < $row_count; $j++ ) {
					$row = db_fetch_array( $result );
					$t_id_array[] = db_prepare_int ( $row['id'] );
				}
			}
		}

		$t_id_array = array_unique( $t_id_array );
		if ( count( $t_id_array ) > 0 ) {
			$t_where = "WHERE $t_bug_table.id in (" . implode( ", ", $t_id_array ) . ")";
		} else {
			$t_where = "WHERE 1 != 1";
		}

		$t_from = 'FROM ' . $t_bug_table;

		# Get the total number of bugs that meet the criteria.
		$bug_count = count( $t_id_array );

		# write the value back in case the caller wants to know
		$p_bug_count = $bug_count;

		if ( null === $p_per_page ) {
			$p_per_page = (int)$t_filter['per_page'];
		} else if ( -1 == $p_per_page ) {
			$p_per_page = $bug_count;
		}

		# Guard against silly values of $f_per_page.
		if ( 0 == $p_per_page ) {
			$p_per_page = 1;
		}
		$p_per_page = (int)abs( $p_per_page );


		# Use $bug_count and $p_per_page to determine how many pages
		# to split this list up into.
		# For the sake of consistency have at least one page, even if it
		# is empty.
		$t_page_count = ceil($bug_count / $p_per_page);
		if ( $t_page_count < 1 ) {
			$t_page_count = 1;
		}

		# write the value back in case the caller wants to know
		$p_page_count = $t_page_count;

		# Make sure $p_page_number isn't past the last page.
		if ( $p_page_number > $t_page_count ) {
			$p_page_number = $t_page_count;
		}

		# Make sure $p_page_number isn't before the first page
		if ( $p_page_number < 1 ) {
			$p_page_number = 1;
		}

		# Now add the rest of the criteria i.e. sorting, limit.

		# if sort is blank then default the sort and direction.  This is to fix the
		# symptoms of #3953.  Note that even if the main problem is fixed, we may
		# have to keep this code for a while to handle filters saved with this blank field.
		if ( is_blank( $t_filter['sort'] ) ) {
			$t_filter['sort'] = 'last_updated';
			$t_filter['dir'] = 'DESC';
		}

		$t_order_array = array();
		$t_sort_fields = split( ',', $t_filter['sort'] );
		$t_dir_fields = split( ',', $t_filter['dir'] );

		if ( ( 'on' == $t_filter['sticky_issues'] ) && ( NULL !== $p_show_sticky ) ) {
			$t_order_array[] = "sticky DESC";
		}
		for ( $i=0; $i < count( $t_sort_fields ); $i++ ) {
			$c_sort = db_prepare_string( $t_sort_fields[$i] );

			if ( ! in_array( $t_sort_fields[$i], array_slice( $t_sort_fields, $i + 1) ) ) {

        		# if sorting by a custom field
        		if ( strpos( $c_sort, 'custom_' ) === 0 ) {
	        		$t_custom_field = substr( $c_sort, strlen( 'custom_' ) );
        			$t_custom_field_id = custom_field_get_id_from_name( $t_custom_field );
    	    		$t_join .= " LEFT JOIN $t_custom_field_string_table ON ( ( $t_custom_field_string_table.bug_id = $t_bug_table.id ) AND ( $t_custom_field_string_table.field_id = $t_custom_field_id ) )";
        			$c_sort = "$t_custom_field_string_table.value";
        			$t_select_clauses[] = "$t_custom_field_string_table.value";
     		   	}

				if ( 'DESC' == $t_dir_fields[$i] ) {
					$c_dir = 'DESC';
				} else {
					$c_dir = 'ASC';
				}

				$t_order_array[] = "$c_sort $c_dir";
			}
		}

		# add basic sorting if necessary
		if ( ! in_array( 'last_updated', $t_sort_fields ) ) {
			$t_order_array[] = 'last_updated DESC';
        }
		if ( ! in_array( 'date_submitted', $t_sort_fields ) ) {
			$t_order_array[] = 'date_submitted DESC';
        }

		$t_order = " ORDER BY " . implode( ', ', $t_order_array );
		$t_select	= implode( ', ', array_unique( $t_select_clauses ) );

		$query2  = "SELECT DISTINCT $t_select
					$t_from
					$t_join
					$t_where
					$t_order";

		# Figure out the offset into the db query
		#
		# for example page number 1, per page 5:
		#     t_offset = 0
		# for example page number 2, per page 5:
		#     t_offset = 5
		$c_per_page = db_prepare_int( $p_per_page );
		$c_page_number = db_prepare_int( $p_page_number );
		$t_offset = ( ( $c_page_number - 1 ) * $c_per_page );

		# perform query
		$result2 = db_query( $query2, $c_per_page, $t_offset );

		$row_count = db_num_rows( $result2 );

		$rows = array();

		for ( $i=0 ; $i < $row_count ; $i++ ) {
			$row = db_fetch_array( $result2 );
			$row['date_submitted'] = db_unixtimestamp ( $row['date_submitted'] );
			$row['last_updated'] = db_unixtimestamp ( $row['last_updated'] );

			array_push( $rows, $row );
			bug_add_to_cache( $row );
		}

		return $rows;
	}

	# --------------------
	# return true if the filter cookie exists and is of the correct version,
	#  false otherwise
	function filter_is_cookie_valid() {
		$t_view_all_cookie_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
		$t_view_all_cookie = filter_db_get_filter( $t_view_all_cookie_id );

		# check to see if the cookie does not exist
		if ( is_blank( $t_view_all_cookie ) ) {
			return false;
		}

		# check to see if new cookie is needed
		$t_setting_arr = explode( '#', $t_view_all_cookie, 2 );
		if ( ( $t_setting_arr[0] == 'v1' ) ||
			 ( $t_setting_arr[0] == 'v2' ) ||
			 ( $t_setting_arr[0] == 'v3' ) ||
			 ( $t_setting_arr[0] == 'v4' ) ) {
			return false;
		}

		# We shouldn't need to do this anymore, as filters from v5 onwards should cope with changing
		# filter indices dynamically
		$t_filter_cookie_arr = array();
		if ( isset( $t_setting_arr[1] ) ) {
			$t_filter_cookie_arr = unserialize( $t_setting_arr[1] );
		} else {
			return false;
		}
		if ( $t_filter_cookie_arr['_version'] != config_get( 'cookie_version' ) ) {
			return false;
		}

		return true;
	}

	# --------------------
	# return filter array if supplied serialized filter is valid, otherwise false.otherwise
	function filter_deserialize( $p_serialized_filter ) {
		if ( is_blank( $p_serialized_filter ) ) {
			return false;
		}

		# check to see if new cookie is needed
		$t_setting_arr = explode( '#', $p_serialized_filter, 2 );
		if ( ( $t_setting_arr[0] == 'v1' ) ||
			 ( $t_setting_arr[0] == 'v2' ) ||
			 ( $t_setting_arr[0] == 'v3' ) ||
			 ( $t_setting_arr[0] == 'v4' ) ) {
			# these versions can't be salvaged, they are too old to update
			return false;
		}

		# We shouldn't need to do this anymore, as filters from v5 onwards should cope with changing
		# filter indices dynamically
		$t_filter_array = array();
		if ( isset( $t_setting_arr[1] ) ) {
			$t_filter_array = unserialize( $t_setting_arr[1] );
		} else {
			return false;
		}
		if ( $t_filter_array['_version'] != config_get( 'cookie_version' ) ) {
			# if the version is new enough, update it using defaults
			return filter_ensure_valid_filter( $t_filter_array );
		}

		return $t_filter_array;
	}

	# --------------------
	# Mainly based on filter_draw_selection_area2() but adds the support for the collapsible
	# filter display.
	function filter_draw_selection_area( $p_page_number, $p_for_screen = true )
	{
		collapse_open( 'filter' );
		filter_draw_selection_area2( $p_page_number, $p_for_screen, true );
		collapse_closed( 'filter' );
		filter_draw_selection_area2( $p_page_number, $p_for_screen, false );
		collapse_end( 'filter' );
	}

	# --------------------
	# Will print the filter selection area for both the bug list view screen, as well
	# as the bug list print screen. This function was an attempt to make it easier to
	# add new filters and rearrange them on screen for both pages.
	function filter_draw_selection_area2( $p_page_number, $p_for_screen = true, $p_expanded = true )
	{
		$t_form_name_suffix = $p_expanded ? '_open' : '_closed';

		$t_filter = current_user_get_bug_filter();
		$t_filter = filter_ensure_valid_filter( $t_filter );
		$t_project_id = helper_get_current_project();

		$t_view_type = $t_filter['_view_type'];

		$t_tdclass = 'small-caption';
		$t_trclass = 'row-category2';
		$t_action  = 'view_all_set.php?f=3';

		if ( $p_for_screen == false ) {
			$t_tdclass = 'print';
			$t_trclass = '';
			$t_action  = 'view_all_set.php';
		}
?>

		<br />
		<form method="post" name="filters<?php echo $t_form_name_suffix ?>" id="filters_form<?php echo $t_form_name_suffix ?>" action="<?php PRINT $t_action; ?>">
		<table class="width100" cellspacing="1">
		<input type="hidden" name="type" value="1" />
		<?php
			if ( $p_for_screen == false ) {
				PRINT '<input type="hidden" name="print" value="1" />';
				PRINT '<input type="hidden" name="offset" value="0" />';
			}
		?>
		<input type="hidden" name="page_number" value="<?php PRINT $p_page_number ?>" />
		<input type="hidden" name="view_type" value="<?php PRINT $t_view_type ?>" />

		<?php
		$t_filter_cols = config_get( 'filter_custom_fields_per_row' );
		if ( $p_expanded ) {
			$t_custom_cols = $t_filter_cols;

			$t_current_user_access_level = current_user_get_access_level();
			$t_accessible_custom_fields_ids = array();
			$t_accessible_custom_fields_names = array();
			$t_accessible_custom_fields_values = array();
			$t_num_custom_rows = 0;
			$t_per_row = 0;

			if ( ON == config_get( 'filter_by_custom_fields' ) ) {
				$t_custom_fields = custom_field_get_linked_ids( $t_project_id );

				foreach ( $t_custom_fields as $t_cfid ) {
					$t_field_info = custom_field_cache_row( $t_cfid, true );
					if ( $t_field_info['access_level_r'] <= $t_current_user_access_level ) {
						$t_accessible_custom_fields_ids[] = $t_cfid;
						$t_accessible_custom_fields_names[] = $t_field_info['name'];
						$t_accessible_custom_fields_types[] = $t_field_info['type'];
						$t_accessible_custom_fields_values[] = custom_field_distinct_values( $t_cfid );
					}
				}

				if ( count( $t_accessible_custom_fields_ids ) > 0 ) {
					$t_per_row = config_get( 'filter_custom_fields_per_row' );
					$t_num_custom_rows = ceil( count( $t_accessible_custom_fields_ids ) / $t_per_row );
				}
			}

			$t_filters_url = 'view_filters_page.php?for_screen=' . $p_for_screen;
			if ( 'advanced' == $t_view_type ) {
				$t_filters_url = $t_filters_url . '&amp;view_type=advanced';
			}
			$t_filters_url = $t_filters_url . '&amp;target_field=';

			$t_show_version = ( ON == config_get( 'show_product_version' ) )
					|| ( ( AUTO == config_get( 'show_product_version' ) )
								&& ( count( version_get_all_rows_with_subs( $t_project_id ) ) > 0 ) );
			# overload handler_id setting if user isn't supposed to see them (ref #6189)
			if ( ! access_has_project_level( config_get( 'view_handler_threshold' ), $t_project_id ) ) { 
				$t_filter['handler_id'] = array( META_FILTER_ANY ); 
			} 
		?>

		<tr <?php PRINT "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'reporter_id[]'; ?>" id="reporter_id_filter"><?php PRINT lang_get( 'reporter' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'user_monitor[]'; ?>" id="user_monitor_filter"><?php PRINT lang_get( 'monitored_by' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'handler_id[]'; ?>" id="handler_id_filter"><?php PRINT lang_get( 'assigned_to' ) ?>:</a>
			</td>
			<td colspan="2" class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_category[]'; ?>" id="show_category_filter"><?php PRINT lang_get( 'category' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_severity[]'; ?>" id="show_severity_filter"><?php PRINT lang_get( 'severity' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_resolution[]'; ?>" id="show_resolution_filter"><?php PRINT lang_get( 'resolution' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_profile[]'; ?>" id="show_profile_filter"><?php PRINT lang_get( 'profile' ) ?>:</a>
			</td>
			<?php if ( $t_filter_cols > 8 ) {
				echo '<td class="small-caption" valign="top" colspan="' . ( $t_filter_cols - 8 ) . '">&nbsp;</td>';
			} ?>
		</tr>

		<tr class="row-1">
			<td class="small-caption" valign="top" id="reporter_id_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['reporter_id'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['reporter_id'] as $t_current ) {
										$t_this_name = '';
										?>
										<input type="hidden" name="reporter_id[]" value="<?php echo $t_current;?>" />
										<?php
										if ( ( $t_current === 0 ) || ( is_blank( $t_current ) ) || ( META_FILTER_ANY == $t_current ) ) {
											$t_any_found = true;
										} else if ( META_FILTER_MYSELF == $t_current ) {
											if ( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
												$t_this_name = '[' . lang_get( 'myself' ) . ']';
											} else {
												$t_any_found = true;
											}
										} else if ( META_FILTER_NONE == $t_current ) {
											$t_this_name = lang_get( 'none' );
										} else {
											$t_this_name = user_get_name( $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_name;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td class="small-caption" valign="top" id="user_monitor_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['user_monitor'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['user_monitor'] as $t_current ) {
										?>
										<input type="hidden" name="user_monitor[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_name = '';
										if ( ( $t_current === 0 ) || ( is_blank( $t_current ) ) || ( META_FILTER_ANY == $t_current ) ) {
											$t_any_found = true;
										} else if ( META_FILTER_MYSELF == $t_current ) {
											if ( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
												$t_this_name = '[' . lang_get( 'myself' ) . ']';
											} else {
												$t_any_found = true;
											}
										} else {
											$t_this_name = user_get_name( $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_name;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td class="small-caption" valign="top" id="handler_id_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['handler_id'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['handler_id'] as $t_current ) {
										?>
										<input type="hidden" name="handler_id[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_name = '';
										if ( META_FILTER_NONE == $t_current ) {
											$t_this_name = lang_get( 'none' );
										} else if ( ( $t_current === 0 ) || ( is_blank( $t_current ) ) || ( META_FILTER_ANY == $t_current ) ) {
											$t_any_found = true;
										} else if ( META_FILTER_MYSELF == $t_current ) {
											if ( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
												$t_this_name = '[' . lang_get( 'myself' ) . ']';
											} else {
												$t_any_found = true;
											}
										} else {
											$t_this_name = user_get_name( $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_name;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td colspan="2" class="small-caption" valign="top" id="show_category_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_category'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_category'] as $t_current ) {
										$t_current = stripslashes( $t_current );
										?>
										<input type="hidden" name="show_category[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td class="small-caption" valign="top" id="show_severity_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_severity'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_severity'] as $t_current ) {
										?>
										<input type="hidden" name="show_severity[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) || ( $t_current == 0 ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = get_enum_element( 'severity', $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td class="small-caption" valign="top" id="show_resolution_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_resolution'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_resolution'] as $t_current ) {
										?>
										<input type="hidden" name="show_resolution[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) || ( $t_current === 0 ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = get_enum_element( 'resolution', $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td class="small-caption" valign="top" id="show_profile_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_profile'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_profile'] as $t_current ) {
										?>
										<input type="hidden" name="show_profile[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) || ( $t_current === 0 ) ) {
											$t_any_found = true;
										} else {
											$t_profile = profile_get_row_direct( $t_current );

											$t_this_string = "${t_profile['platform']} ${t_profile['os']} ${t_profile['os_build']}";
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<?php if ( $t_filter_cols > 8 ) {
				echo '<td class="small-caption" valign="top" colspan="' . ( $t_filter_cols - 8 ) . '">&nbsp;</td>';
			} ?>
			</tr>

		<tr <?php PRINT "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_status[]'; ?>" id="show_status_filter"><?php PRINT lang_get( 'status' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<?php if ( 'simple' == $t_view_type ) { ?>
					<a href="<?php PRINT $t_filters_url . 'hide_status[]'; ?>" id="hide_status_filter"><?php PRINT lang_get( 'hide_status' ) ?>:</a>
				<?php } ?>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_build[]'; ?>" id="show_build_filter"><?php PRINT lang_get( 'product_build' ) ?>:</a>
			</td>
			<?php if ( $t_show_version ) { ?>
			<td colspan="2" class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_version[]'; ?>" id="show_version_filter"><?php PRINT lang_get( 'product_version' ) ?>:</a>
			</td>
			<td colspan="1" class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'fixed_in_version[]'; ?>" id="show_fixed_in_version_filter"><?php PRINT lang_get( 'fixed_in_version' ) ?>:</a>
			</td>
			<?php } else { ?>
			<td colspan="2" class="small-caption" valign="top">
				&nbsp;
			</td>
			<td colspan="1" class="small-caption" valign="top">
				&nbsp;
			</td>
			<?php } ?>
			<td colspan="1" class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_priority[]'; ?>" id="show_priority_filter"><?php PRINT lang_get( 'priority' ) ?>:</a>
			</td>
			<?php if ( $t_filter_cols > 7 ) {
				echo '<td class="small-caption" valign="top" colspan="' . ( $t_filter_cols - 7 ) . '">&nbsp;</td>';
			} ?>
		</tr>

		<tr class="row-1">
			<td class="small-caption" valign="top" id="show_status_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_status'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_status'] as $t_current ) {
										?>
										<input type="hidden" name="show_status[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) || ( $t_current === 0 ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = get_enum_element( 'status', $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td class="small-caption" valign="top" id="hide_status_filter_target">
							<?php
								if ( 'simple' == $t_view_type ) {
									$t_output = '';
									$t_none_found = false;
									if ( count( $t_filter['hide_status'] ) == 0 ) {
										PRINT lang_get( 'none' );
									} else {
										$t_first_flag = true;
										foreach( $t_filter['hide_status'] as $t_current ) {
											?>
											<input type="hidden" name="hide_status[]" value="<?php echo $t_current;?>" />
											<?php
											$t_this_string = '';
											if ( ( $t_current == META_FILTER_NONE ) || ( is_blank( $t_current ) ) || ( $t_current === 0 ) ) {
												$t_none_found = true;
											} else {
												$t_this_string = get_enum_element( 'status', $t_current );
											}
											if ( $t_first_flag != true ) {
												$t_output = $t_output . '<br />';
											} else {
												$t_first_flag = false;
											}
											$t_output = $t_output . $t_this_string;
										}
										$t_hide_status_post = '';
										if ( count( $t_filter['hide_status'] ) == 1 ) {
											$t_hide_status_post = ' (' . lang_get( 'and_above' ) . ')';
										}
										if ( true == $t_none_found ) {
											PRINT lang_get( 'none' );
										} else {
											PRINT $t_output . $t_hide_status_post;
										}
									}
								}
							?>
			</td>
			<td class="small-caption" valign="top" id="show_build_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_build'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_build'] as $t_current ) {
										$t_current = stripslashes( $t_current );
										?>
										<input type="hidden" name="show_build[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else if ( META_FILTER_NONE == $t_current ) {
											$t_this_string = lang_get( 'none' );
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<?php if ( $t_show_version ) { ?>
			<td colspan="2" class="small-caption" valign="top" id="show_version_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_version'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_version'] as $t_current ) {
										$t_current = stripslashes( $t_current );
										?>
										<input type="hidden" name="show_version[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else if ( META_FILTER_NONE == $t_current ) {
											$t_this_string = lang_get( 'none' );
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<td colspan="1" class="small-caption" valign="top" id="show_fixed_in_version_filter_target">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['fixed_in_version'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['fixed_in_version'] as $t_current ) {
										$t_current = stripslashes( $t_current );
										?>
										<input type="hidden" name="fixed_in_version[]" value="<?php echo $t_current;?>" />
										<?php
										$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else if ( META_FILTER_NONE == $t_current ) {
											$t_this_string = lang_get( 'none' );
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br />';
										} else {
											$t_first_flag = false;
										}
										$t_output = $t_output . $t_this_string;
									}
									if ( true == $t_any_found ) {
										PRINT lang_get( 'any' );
									} else {
										PRINT $t_output;
									}
								}
							?>
			</td>
			<?php } else { ?>
			<td colspan="2" class="small-caption" valign="top">
				&nbsp;
			</td>
			<td colspan="1" class="small-caption" valign="top">
				&nbsp;
			</td>
			<?php } ?>
			<td colspan="1" class="small-caption" valign="top" id="show_priority_filter_target">
              <?php
							  $t_output = '';
                $t_any_found = false;
                if ( count( $t_filter['show_priority'] ) == 0 ) {
                	PRINT lang_get( 'any' );
                } else {
                  $t_first_flag = true;
                  foreach( $t_filter['show_priority'] as $t_current ) {
										?>
										<input type="hidden" name="show_priority[]" value="<?php echo $t_current;?>" />
										<?php
                  	$t_this_string = '';
										if ( ( $t_current == META_FILTER_ANY ) || ( is_blank( $t_current ) ) || ( $t_current === 0 ) ) {
                  		$t_any_found = true;
	                  } else {
	                  	$t_this_string = get_enum_element( 'priority', $t_current );
	                  }
	                  if ( $t_first_flag != true ) {
	                  	$t_output = $t_output . '<br />';
	                  } else {
	                  	$t_first_flag = false;
	                  }
	                  $t_output = $t_output . $t_this_string;
	                }
	                if ( true == $t_any_found ) {
	                 	PRINT lang_get( 'any' );
	                } else {
	                	PRINT $t_output;
	                }
	               }
	              ?>
	    	</td>
			<?php if ( $t_filter_cols > 7 ) {
				echo '<td class="small-caption" valign="top" colspan="' . ( $t_filter_cols - 7 ) . '">&nbsp;</td>';
			} ?>

		</tr>

		<tr <?php PRINT "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'per_page'; ?>" id="per_page_filter"><?php PRINT lang_get( 'show' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'view_state'; ?>" id="view_state_filter"><?php PRINT lang_get( 'view_status' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'sticky_issues'; ?>" id="sticky_issues_filter"><?php PRINT lang_get( 'sticky' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'highlight_changed'; ?>" id="highlight_changed_filter"><?php PRINT lang_get( 'changed' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top" colspan="2">
				<a href="<?php PRINT $t_filters_url . 'do_filter_by_date'; ?>" id="do_filter_by_date_filter"><?php PRINT lang_get( 'use_date_filters' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top" colspan="2">
				<a href="<?php PRINT $t_filters_url . 'relationship_type'; ?>" id="relationship_type_filter"><?php PRINT lang_get( 'bug_relationships' ) ?>:</a>
			</td>
			<?php if ( $t_filter_cols > 8 ) {
				echo '<td class="small-caption" valign="top" colspan="' . ( $t_filter_cols - 8 ) . '">&nbsp;</td>';
			} ?>
		</tr>
		<tr class="row-1">
			<td class="small-caption" valign="top" id="per_page_filter_target">
				<?php PRINT $t_filter['per_page']; ?>
				<input type="hidden" name="per_page" value="<?php echo $t_filter['per_page'];?>" />
			</td>
			<td class="small-caption" valign="top" id="view_state_filter_target">
				<?php
				if ( VS_PUBLIC == $t_filter['view_state'] ) {
					PRINT lang_get( 'public' );
				} else if ( VS_PRIVATE == $t_filter['view_state'] ) {
					PRINT lang_get( 'private' );
				} else {
					PRINT lang_get( 'any' );
				}
				?>
				<input type="hidden" name="view_state" value="<?php echo $t_filter['view_state'];?>" />
			</td>
			<td class="small-caption" valign="top" id="sticky_issues_filter_target">
				<?php
					$t_sticky_filter_state = gpc_string_to_bool( $t_filter['sticky_issues'] )  ;
					PRINT ( $t_sticky_filter_state ? lang_get( 'yes' ) : lang_get( 'no' ) );
				?>
				<input type="hidden" name="sticky_issues" value="<?php echo $t_sticky_filter_state ? 'on' : 'off';?>" />
			</td>
			<td class="small-caption" valign="top" id="highlight_changed_filter_target">
				<?php PRINT $t_filter['highlight_changed']; ?>
				<input type="hidden" name="highlight_changed" value="<?php echo $t_filter['highlight_changed'];?>" />
			</td>
			<td class="small-caption" valign="top" colspan="2" id="do_filter_by_date_filter_target">
							<?php
							if ( ( ON == config_get( 'dhtml_filters' ) ) && ( ON == config_get( 'use_javascript' ) ) ){
								?>
		<script language="Javascript">
		<!--
			function SwitchDateFields() {
		    	// All fields need to be enabled to go back to the script
				document.filters_open.start_month.disabled = ! document.filters_open.do_filter_by_date.checked;
				document.filters_open.start_day.disabled = ! document.filters_open.do_filter_by_date.checked;
				document.filters_open.start_year.disabled = ! document.filters_open.do_filter_by_date.checked;
				document.filters_open.end_month.disabled = ! document.filters_open.do_filter_by_date.checked;
				document.filters_open.end_day.disabled = ! document.filters_open.do_filter_by_date.checked;
				document.filters_open.end_year.disabled = ! document.filters_open.do_filter_by_date.checked;

		   		return true;
			}
		// -->
		</script>
							<?php
							} # end if dhtml_filters
							if ( 'on' == $t_filter['do_filter_by_date'] ) {
								?>
								<input type="hidden" name="do_filter_by_date" value="<?php echo $t_filter['do_filter_by_date'];?>" />
								<input type="hidden" name="start_month" value="<?php echo $t_filter['start_month'];?>" />
								<input type="hidden" name="start_day" value="<?php echo $t_filter['start_day'];?>" />
								<input type="hidden" name="start_year" value="<?php echo $t_filter['start_year'];?>" />
								<input type="hidden" name="end_month" value="<?php echo $t_filter['end_month'];?>" />
								<input type="hidden" name="end_day" value="<?php echo $t_filter['end_day'];?>" />
								<input type="hidden" name="end_year" value="<?php echo $t_filter['end_year'];?>" />
								<?php
								$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
								$t_time = mktime( 0, 0, 0, $t_filter['start_month'], $t_filter['start_day'], $t_filter['start_year'] );
								foreach( $t_chars as $t_char ) {
									if ( strcasecmp( $t_char, "M" ) == 0 ) {
										PRINT ' ';
										PRINT date( 'F', $t_time );
									}
									if ( strcasecmp( $t_char, "D" ) == 0 ) {
										PRINT ' ';
										PRINT date( 'd', $t_time );
									}
									if ( strcasecmp( $t_char, "Y" ) == 0 ) {
										PRINT ' ';
										PRINT date( 'Y', $t_time );
									}
								}

								PRINT ' - ';

								$t_time = mktime( 0, 0, 0, $t_filter['end_month'], $t_filter['end_day'], $t_filter['end_year'] );
								foreach( $t_chars as $t_char ) {
									if ( strcasecmp( $t_char, "M" ) == 0 ) {
										PRINT ' ';
										PRINT date( 'F', $t_time );
									}
									if ( strcasecmp( $t_char, "D" ) == 0 ) {
										PRINT ' ';
										PRINT date( 'd', $t_time );
									}
									if ( strcasecmp( $t_char, "Y" ) == 0 ) {
										PRINT ' ';
										PRINT date( 'Y', $t_time );
									}
								}
							} else {
								PRINT lang_get( 'no' );
							}
							?>
			</td>

			<td class="small-caption" valign="top" colspan="2" id="relationship_type_filter_target">
							<input type="hidden" name="relationship_type" value="<?php echo $t_filter['relationship_type'];?>" />
							<input type="hidden" name="relationship_bug" value="<?php echo $t_filter['relationship_bug'];?>" />
							<?php
								$c_rel_type = $t_filter['relationship_type'];
								$c_rel_bug = $t_filter['relationship_bug'];
								if ( -1 == $c_rel_type || 0 == $c_rel_bug ) {
									PRINT lang_get( 'any' );
								} else {
								    PRINT relationship_get_description_for_history ($c_rel_type) . ' ' . $c_rel_bug;
								}

							?>
			</td>
			<?php if ( $t_filter_cols > 8 ) {
				echo '<td class="small-caption" valign="top" colspan="' . ( $t_filter_cols - 8 ) . '">&nbsp;</td>';
			} ?>
		</tr>
		<?php

		if ( ON == config_get( 'filter_by_custom_fields' ) ) {

			# -- Custom Field Searching --

			if ( count( $t_accessible_custom_fields_ids ) > 0 ) {
				$t_per_row = config_get( 'filter_custom_fields_per_row' );
				$t_num_fields = count( $t_accessible_custom_fields_ids ) ;
				$t_row_idx = 0;
				$t_col_idx = 0;

				$t_fields = "";
				$t_values = "";

				for ( $i = 0; $i < $t_num_fields; $i++ ) {
					if ( $t_col_idx == 0 ) {
						$t_fields = '<tr class="' . $t_trclass . '">';
						$t_values = '<tr class="row-1">';
					}

					if ( isset( $t_accessible_custom_fields_names[ $i ] ) ) {
						$t_fields .= '<td class="small-caption" valign="top"> ';
						$t_fields .= '<a href="' . $t_filters_url . 'custom_field_' . $t_accessible_custom_fields_ids[$i] . '[]" id="custom_field_'. $t_accessible_custom_fields_ids[$i] .'_filter">';
						$t_fields .= string_display( lang_get_defaulted( $t_accessible_custom_fields_names[$i] ) );
						$t_fields .= '</a> </td> ';
					}
					$t_output = '';
					$t_any_found = false;

					$t_values .= '<td class="small-caption" valign="top" id="custom_field_' . $t_accessible_custom_fields_ids[$i] . '_filter_target"> ' ;
					if ( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] ) ) {
						$t_values .= lang_get( 'any' );
					} else {
						if ( $t_accessible_custom_fields_types[$i] == CUSTOM_FIELD_TYPE_DATE ) {
							# @@@ moved embedded javascript here from print_filter_custom_field_date
							#  it appears not to load properly on Firefox and other browsers if loaded through the httpxmlreq
							$t_field_id = $t_accessible_custom_fields_ids[$i];
							$t_js_toggle_func = "toggle_custom_date_field_" . $t_field_id . "_controls" ;
							if ( ( ON == config_get( 'dhtml_filters' ) ) && ( ON == config_get( 'use_javascript' ) ) ) {
								?>
	<script language="Javascript">
	<!--
	function <?php echo $t_js_toggle_func . "_start" ; ?>(disable) {
			document.filters_open.custom_field_<?php echo $t_field_id ; ?>_start_year.disabled = disable ;
			document.filters_open.custom_field_<?php echo $t_field_id ; ?>_start_month.disabled = disable ;
			document.filters_open.custom_field_<?php echo $t_field_id ; ?>_start_day.disabled = disable ;
	} ;

	function <?php echo $t_js_toggle_func . "_end" ; ?>(disable) {
			document.filters_open.custom_field_<?php echo $t_field_id ; ?>_end_year.disabled = disable ;
			document.filters_open.custom_field_<?php echo $t_field_id ; ?>_end_month.disabled = disable ;
			document.filters_open.custom_field_<?php echo $t_field_id ; ?>_end_day.disabled = disable ;
	} ;

	function <?php echo $t_js_toggle_func ; ?>() {
		switch (document.filters_open.custom_field_<?php echo $t_field_id ; ?>_control.selectedIndex) {
		case <?php echo CUSTOM_FIELD_DATE_ANY ; ?>:
		case <?php echo CUSTOM_FIELD_DATE_NONE ; ?>:
			<?php echo $t_js_toggle_func . "_start" ; ?>(true) ;
			<?php echo $t_js_toggle_func . "_end" ; ?>(true) ;
			break ;
		case <?php echo CUSTOM_FIELD_DATE_BETWEEN ; ?>:
			<?php echo $t_js_toggle_func . "_start" ; ?>(false) ;
			<?php echo $t_js_toggle_func . "_end" ; ?>(false) ;
			break ;
		default:
			<?php echo $t_js_toggle_func . "_start" ; ?>(false) ;
			<?php echo $t_js_toggle_func . "_end" ; ?>(true) ;
			break ;
		}
	}
	// -->
	</script>
<?php
							} # end if dhtml_filters
							$t_short_date_format = config_get( 'short_date_format' );
							if ( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] ) ) {
								$t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] = 0;
							}
							$t_start = date( $t_short_date_format, $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][1] );

							if ( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] ) ) {
								$t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] = 0;
							}
							$t_end = date( $t_short_date_format, $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][2] );
							switch ($t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]][0]) {
							case CUSTOM_FIELD_DATE_ANY:
								$t_values .= lang_get( 'any' ) ;
								break;
							case CUSTOM_FIELD_DATE_NONE:
								$t_values .= lang_get( 'none' ) ;
								break;
							case CUSTOM_FIELD_DATE_BETWEEN:
								$t_values .= lang_get( 'between' ) . '<br />';
								$t_values .= $t_start . '<br />' . $t_end;
								break;
							case CUSTOM_FIELD_DATE_ONORBEFORE:
								$t_values .= lang_get( 'on_or_before' ) . '<br />';
								$t_values .= $t_end;
								break;
							case CUSTOM_FIELD_DATE_BEFORE:
								$t_values .= lang_get( 'before' ) . '<br />';
								$t_values .= $t_end;
								break;
							case CUSTOM_FIELD_DATE_ON:
								$t_values .= lang_get( 'on' ) . '<br />';
								$t_values .= $t_start;
								break;
							case CUSTOM_FIELD_DATE_AFTER:
								$t_values .= lang_get( 'after' ) . '<br />';
								$t_values .= $t_start;
								break ;
							case CUSTOM_FIELD_DATE_ONORAFTER:
								$t_values .= lang_get( 'on_or_after' ) . '<br />';
								$t_values .= $t_start;
								break ;
							}
						} else {
							$t_first_flag = true;
							foreach( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] as $t_current ) {
								$t_current = stripslashes( $t_current );
								$t_this_string = '';
								if ( ( $t_current == META_FILTER_ANY ) || ( $t_current === 0 ) ) {
									$t_any_found = true;
								} else if ( META_FILTER_NONE == $t_current ) {
									$t_this_string = lang_get( 'none' );
								} else {
									$t_this_string = $t_current;
								}

								if ( $t_first_flag != true ) {
									$t_output = $t_output . '<br />';
								} else {
									$t_first_flag = false;
								}

								$t_output = $t_output . $t_this_string;
								$t_values .= '<input type="hidden" name="custom_field_'.$t_accessible_custom_fields_ids[$i].'[]" value="'.$t_current.'" />';
							}
						}

						if ( true == $t_any_found ) {
							$t_values .= lang_get( 'any' );
						} else {
							$t_values .= $t_output;
						}
					}
					$t_values .= ' </td>';

					$t_col_idx++;

					if ( $t_col_idx == $t_per_row ) {
						if ( $t_filter_cols > $t_per_row ) {
							$t_fields .= '<td colspan="' . ($t_filter_cols - $t_per_row ) . '">&nbsp;</td> ';
							$t_values .= '<td colspan="' . ($t_filter_cols - $t_per_row) . '">&nbsp;</td> ';
						}

						$t_fields .= '</tr>' . "\n";
						$t_values .= '</tr>' . "\n";

						echo $t_fields;
						echo $t_values;

						$t_col_idx = 0;
						$t_row_idx++;
					}
				}


				if ( $t_col_idx > 0 ) {
					if ( $t_col_idx < $t_per_row ) {
						$t_fields .= '<td colspan="' . ($t_per_row - $t_col_idx) . '">&nbsp;</td> ';
						$t_values .= '<td colspan="' . ($t_per_row - $t_col_idx) . '">&nbsp;</td> ';
					}

					if ( $t_filter_cols > $t_per_row ) {
						$t_fields .= '<td colspan="' . ($t_filter_cols - $t_per_row ) . '">&nbsp;</td> ';
						$t_values .= '<td colspan="' . ($t_filter_cols - $t_per_row) . '">&nbsp;</td> ';
					}

					$t_fields .= '</tr>' . "\n";
					$t_values .= '</tr>' . "\n";

					echo $t_fields;
					echo $t_values;
				}
			}
		}
		?>
		<tr class="row-1">
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_sort'; ?>" id="show_sort_filter"><?php PRINT lang_get( 'sort' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top" colspan="4" id="show_sort_filter_target">
				<?php
					$t_sort_fields = split( ',', $t_filter['sort'] );
					$t_dir_fields = split( ',', $t_filter['dir'] );

					for ( $i=0; $i<2; $i++ ) {
						if ( isset( $t_sort_fields[$i] ) ) {
							if ( 0 < $i ) {
								echo ", ";
							}
							$t_sort = $t_sort_fields[$i];
        					if ( strpos( $t_sort, 'custom_' ) === 0 ) {
        						$t_field_name = string_display( lang_get_defaulted( substr( $t_sort, strlen( 'custom_' ) ) ) );
        					} else {
        						$t_field_name = string_get_field_name( $t_sort );
        					}

							echo $t_field_name . " " . lang_get( 'bugnote_order_' . strtolower( $t_dir_fields[$i] ) );
							echo "<input type=\"hidden\" name=\"sort_$i\" value=\"$t_sort_fields[$i]\" />";
							echo "<input type=\"hidden\" name=\"dir_$i\" value=\"$t_dir_fields[$i]\" />";
						}
					}
				?>
			</td>
			<?php if ( $t_filter_cols > 5 ) {
				echo '<td class="small-caption" valign="top" colspan="' . ( $t_filter_cols - 5 ) . '">&nbsp;</td>';
			} ?>
		</tr>
		<?php
		} // expanded
		?>
		<tr>
			<td colspan="2">
				<?php
					collapse_icon( 'filter' );
					echo lang_get( 'search' );
				?>:
				<input type="text" size="16" name="search" value="<?php PRINT htmlspecialchars( $t_filter['search'] ); ?>" />

				<input type="submit" name="filter" class="button-small" value="<?php PRINT lang_get( 'filter_button' ) ?>" />
			</td>
			</form>
			<td class="center" colspan="<?php echo ( $t_filter_cols - 6 ) ?>"> <!-- use this label for padding -->
				<?php
					if ( ON == config_get( 'dhtml_filters' ) ) {
						$f_switch_view_link = 'view_all_set.php?type=6&amp;view_type=';
					} else {
						$f_switch_view_link = 'view_filters_page.php?view_type=';
					}

					if ( ( SIMPLE_ONLY != config_get( 'view_filters' ) ) && ( ADVANCED_ONLY != config_get( 'view_filters' ) ) ) {
						if ( 'advanced' == $t_view_type ) {
							print_bracket_link( $f_switch_view_link . 'simple', lang_get( 'simple_filters' ) );
						} else {
							print_bracket_link( $f_switch_view_link . 'advanced', lang_get( 'advanced_filters' ) );
						}
					}
				?>
			</td>
			<td class="right" colspan="4">
			<?php
			$t_stored_queries_arr = array();
			$t_stored_queries_arr = filter_db_get_available_queries();

			if ( count( $t_stored_queries_arr ) > 0 ) {
				?>
					<form method="get" name="list_queries<?php echo $t_form_name_suffix; ?>" action="view_all_set.php">
					<input type="hidden" name="type" value="3" />
					<?php
					if ( ON == config_get( 'use_javascript' ) ) {
						echo "<select name=\"source_query_id\" onchange=\"document.forms.list_queries$t_form_name_suffix.submit();\">";
					} else {
						PRINT '<select name="source_query_id">';
					}
					?>
					<option value="-1"><?php PRINT '[' . lang_get( 'reset_query' ) . ']' ?></option>
					<option value="-1"></option>
					<?php
					foreach( $t_stored_queries_arr as $t_query_id => $t_query_name ) {
						PRINT '<option value="' . $t_query_id . '">' . $t_query_name . '</option>';
					}
					?>
					</select>
					<input type="submit" name="switch_to_query_button" class="button-small" value="<?php PRINT lang_get( 'use_query' ) ?>" />
					</form>
					<form method="post" name="open_queries" action="query_view_page.php">
					<input type="submit" name="switch_to_query_button" class="button-small" value="<?php PRINT lang_get( 'open_queries' ) ?>" />
					</form>
				<?php
			} else {
				?>
					<form method="get" name="reset_query" action="view_all_set.php">
					<input type="hidden" name="type" value="3" />
					<input type="hidden" name="source_query_id" value="-1" />
					<input type="submit" name="reset_query_button" class="button-small" value="<?php PRINT lang_get( 'reset_query' ) ?>" />
					</form>
				<?php
			}

			if ( access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) {
			?>
					<form method="post" name="save_query" action="query_store_page.php">
					<input type="submit" name="save_query_button" class="button-small" value="<?php PRINT lang_get( 'save_query' ) ?>" />
					</form>
			<?php
			} else {
			?>
			<?php
			}
			?>
			</td>
		</tr>
		</table>
<?php
	}

	# Add a filter to the database for the current user
	function filter_db_set_for_current_user( $p_project_id, $p_is_public,
										$p_name, $p_filter_string ) {
		$t_user_id = auth_get_current_user_id();
		$c_project_id = db_prepare_int( $p_project_id );
		$c_is_public = db_prepare_bool( $p_is_public, false );
		$c_name = db_prepare_string( $p_name );
		$c_filter_string = db_prepare_string( $p_filter_string );

		$t_filters_table = config_get( 'mantis_filters_table' );

		# check that the user can save non current filters (if required)
		if ( ( ALL_PROJECTS <= $c_project_id ) && ( !is_blank( $p_name ) ) &&
		     ( !access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) ) {
			return -1;
		}

		# ensure that we're not making this filter public if we're not allowed
		if ( !access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
			$c_is_public = db_prepare_bool( false );
		}

		# Do I need to update or insert this value?
		$query = "SELECT id FROM $t_filters_table
					WHERE user_id='$t_user_id'
					AND project_id='$c_project_id'
					AND name='$c_name'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			$row = db_fetch_array( $result );

			$query = "UPDATE $t_filters_table
					  SET is_public='$c_is_public',
					  	filter_string='$c_filter_string'
					  WHERE id='" . $row['id'] . "'";
			db_query( $query );

			return $row['id'];
		} else {
			$query = "INSERT INTO $t_filters_table
						( user_id, project_id, is_public, name, filter_string )
					  VALUES
						( '$t_user_id', '$c_project_id', '$c_is_public', '$c_name', '$c_filter_string' )";
			db_query( $query );

			# Recall the query, we want the filter ID
			$query = "SELECT id
						FROM $t_filters_table
						WHERE user_id='$t_user_id'
						AND project_id='$c_project_id'
						AND name='$c_name'";
			$result = db_query( $query );

			if ( db_num_rows( $result ) > 0 ) {
				$row = db_fetch_array( $result );
				return $row['id'];
			}

			return -1;
		}
	}

	# We cache filter requests to reduce the number of SQL queries
	$g_cache_filter_db_filters = array();

	# This function will return the filter string that is
	# tied to the unique id parameter. If the user doesn't
	# have permission to see this filter, the function will
	# return null
	function filter_db_get_filter( $p_filter_id, $p_user_id = null ) {
		global $g_cache_filter_db_filters;
		$t_filters_table = config_get( 'mantis_filters_table' );
		$c_filter_id = db_prepare_int( $p_filter_id );

		if ( isset( $g_cache_filter_db_filters[$p_filter_id] ) ) {
			return $g_cache_filter_db_filters[$p_filter_id];
		}

		if ( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = $p_user_id;
		}

		$query = "SELECT *
				  FROM $t_filters_table
				  WHERE id='$c_filter_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			$row = db_fetch_array( $result );

			if ( $row['user_id'] != $t_user_id ) {
				if ( $row['is_public'] != true ) {
					return null;
				}
			}

			# check that the user has access to non current filters
			if ( ( ALL_PROJECTS <= $row['project_id'] ) && ( !is_blank( $row['name'] ) ) && ( !access_has_project_level( config_get( 'stored_query_use_threshold', $row['project_id'], $t_user_id ) ) ) ) {
				return null;
			}

			$g_cache_filter_db_filters[$p_filter_id] = $row['filter_string'];
			return $row['filter_string'];
		}

		return null;
	}

	function filter_db_get_project_current( $p_project_id, $p_user_id = null ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$c_project_id 	= db_prepare_int( $p_project_id );
		$c_project_id 	= $c_project_id * -1;

		if ( null === $p_user_id ) {
			$c_user_id 		= auth_get_current_user_id();
		} else {
			$c_user_id		= db_prepare_int( $p_user_id );
		}

		# we store current filters for each project with a special project index
		$query = "SELECT *
				  FROM $t_filters_table
				  WHERE user_id='$c_user_id'
				  	AND project_id='$c_project_id'
				  	AND name=''";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			$row = db_fetch_array( $result );
			return $row['id'];
		}

		return null;
	}

	function filter_db_get_name( $p_filter_id ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$c_filter_id = db_prepare_int( $p_filter_id );

		$query = "SELECT *
				  FROM $t_filters_table
				  WHERE id='$c_filter_id'";
		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			$row = db_fetch_array( $result );

			if ( $row['user_id'] != auth_get_current_user_id() ) {
				if ( $row['is_public'] != true ) {
					return null;
				}
			}

			return $row['name'];
		}

		return null;
	}

	# Will return true if the user can delete this query
	function filter_db_can_delete_filter( $p_filter_id ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$c_filter_id = db_prepare_int( $p_filter_id );
		$t_user_id = auth_get_current_user_id();

		# Administrators can delete any filter
		if ( access_has_global_level( ADMINISTRATOR ) ) {
			return true;
		}

		$query = "SELECT id
				  FROM $t_filters_table
				  WHERE id='$c_filter_id'
				  AND user_id='$t_user_id'
				  AND project_id!='-1'";

		$result = db_query( $query );

		if ( db_num_rows( $result ) > 0 ) {
			return true;
		}

		return false;
	}

	function filter_db_delete_filter( $p_filter_id ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$c_filter_id = db_prepare_int( $p_filter_id );
		$t_user_id = auth_get_current_user_id();

		if ( !filter_db_can_delete_filter( $c_filter_id ) ) {
			return false;
		}

		$query = "DELETE FROM $t_filters_table
				  WHERE id='$c_filter_id'";
		$result = db_query( $query );

		if ( db_affected_rows( $result ) > 0 ) {
			return true;
		}

		return false;
	}

	function filter_db_delete_current_filters( ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$t_all_id = ALL_PROJECTS;

		$query = "DELETE FROM $t_filters_table
					WHERE project_id<='$t_all_id'
					AND name=''";
		$result = db_query( $query );
	}

	function filter_db_get_available_queries( $p_project_id = null, $p_user_id = null ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$t_overall_query_arr = array();

		if ( null === $p_project_id ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = db_prepare_int( $p_project_id );
		}

		if ( null === $p_user_id ) {
			$t_user_id = auth_get_current_user_id();
		} else {
			$t_user_id = db_prepare_int( $p_user_id );
		}

		# If the user doesn't have access rights to stored queries, just return
		if ( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
			return $t_overall_query_arr;
		}

		# Get the list of available queries. By sorting such that public queries are
		# first, we can override any query that has the same name as a private query
		# with that private one
		$query = "SELECT * FROM $t_filters_table
					WHERE (project_id='$t_project_id'
					OR project_id='0')
					AND name!=''
					ORDER BY is_public DESC, name ASC";
		$result = db_query( $query );
		$query_count = db_num_rows( $result );

		for ( $i = 0; $i < $query_count; $i++ ) {
			$row = db_fetch_array( $result );
			if ( ( $row['user_id'] == $t_user_id ) || db_prepare_bool( $row['is_public'] ) ) {
				$t_overall_query_arr[$row['id']] = $row['name'];
			}
		}

		$t_overall_query_arr = array_unique( $t_overall_query_arr );
		asort( $t_overall_query_arr );

		return $t_overall_query_arr;
	}

	# Make sure that our filters are entirely correct and complete (it is possible that they are not).
	# We need to do this to cover cases where we don't have complete control over the filters given.
	function filter_ensure_valid_filter( $p_filter_arr ) {
		if ( !isset( $p_filter_arr['_version'] ) ) {
			$p_filter_arr['_version'] = config_get( 'cookie_version' );
		}
		$t_cookie_vers = (int) substr( $p_filter_arr['_version'], 1 );
		if ( substr( config_get( 'cookie_version' ), 1 ) > $t_cookie_vers ) { # if the version is old, update it
			$p_filter_arr['_version'] = config_get( 'cookie_version' );
		}
		if ( !isset( $p_filter_arr['_view_type'] ) ) {
			$p_filter_arr['_view_type'] = gpc_get_string( 'view_type', 'simple' );
		}
		if ( !isset( $p_filter_arr['per_page'] ) ) {
			$p_filter_arr['per_page'] = gpc_get_int( 'per_page', config_get( 'default_limit_view' ) );
		}
		if ( !isset( $p_filter_arr['highlight_changed'] ) ) {
			$p_filter_arr['highlight_changed'] = config_get( 'default_show_changed' );
		}
		if ( !isset( $p_filter_arr['sticky_issues'] ) ) {
			$p_filter_arr['sticky_issues'] = config_get( 'show_sticky_issues' );
		}
		if ( !isset( $p_filter_arr['sort'] ) ) {
			$p_filter_arr['sort'] = "last_updated";
		}
		if ( !isset( $p_filter_arr['dir'] ) ) {
			$p_filter_arr['dir'] = "DESC";
		}
		#validate sorting
		$t_fields = helper_call_custom_function( 'get_columns_to_view', array() );
		$t_n_fields = count( $t_fields );
		$t_shown_fields[""] = "";
		for ( $i=0; $i < $t_n_fields; $i++ ) {
			if ( in_array( $t_fields[$i], array( 'selection', 'edit', 'bugnotes_count', 'attachment' ) ) ) {
				unset( $t_fields[$i] );
			}
		}
		$t_sort_fields = split( ',', $p_filter_arr['sort'] );
		$t_dir_fields = split( ',', $p_filter_arr['dir'] );
		for ( $i=0; $i<2; $i++ ) {
			if ( isset( $t_sort_fields[$i] ) ) {
				$t_drop = false;
				$t_sort = $t_sort_fields[$i];
        		if ( strpos( $t_sort, 'custom_' ) === 0 ) {
        			if ( false === custom_field_get_id_from_name( substr( $t_sort, strlen( 'custom_' ) ) ) ) {
        				$t_drop = true;
        			}
        		} else {
        			if ( ! in_array( $t_sort, $t_fields ) ) {
        				$t_drop = true;
        			}
        		}
				if ( ! in_array( $t_dir_fields[$i], array( "ASC", "DESC" ) ) ) {
					$t_drop = true;
				}
				if ( $t_drop ) {
					unset( $t_sort_fields[$i] );
					unset( $t_dir_fields[$i] );
				}
			}
		}
		if ( count( $t_sort_fields ) > 0 ) {
			$p_filter_arr['sort'] = implode( ',', $t_sort_fields );
			$p_filter_arr['dir'] = implode( ',', $t_dir_fields );
		} else {
			$p_filter_arr['sort'] = "last_updated";
			$p_filter_arr['dir'] = "DESC";
		}

		if ( !isset( $p_filter_arr['start_month'] ) ) {
			$p_filter_arr['start_month'] = gpc_get_string( 'start_month', date( 'm' ) );
		}
		if ( !isset( $p_filter_arr['start_day'] ) ) {
			$p_filter_arr['start_day'] = gpc_get_string( 'start_day', 1 );
		}
		if ( !isset( $p_filter_arr['start_year'] ) ) {
			$p_filter_arr['start_year'] = gpc_get_string( 'start_year', date( 'Y' ) );
		}
		if ( !isset( $p_filter_arr['end_month'] ) ) {
			$p_filter_arr['end_month'] = gpc_get_string( 'end_month', date( 'm' ) );
		}
		if ( !isset( $p_filter_arr['end_day'] ) ) {
			$p_filter_arr['end_day'] = gpc_get_string( 'end_day', date( 'd' ) );
		}
		if ( !isset( $p_filter_arr['end_year'] ) ) {
			$p_filter_arr['end_year'] = gpc_get_string( 'end_year', date( 'Y' ) );
		}
		if ( !isset( $p_filter_arr['search'] ) ) {
			$p_filter_arr['search'] = '';
		}
		if ( !isset( $p_filter_arr['and_not_assigned'] ) ) {
			$p_filter_arr['and_not_assigned'] = gpc_get_bool( 'and_not_assigned', false );
		}
		if ( !isset( $p_filter_arr['do_filter_by_date'] ) ) {
			$p_filter_arr['do_filter_by_date'] = gpc_get_bool( 'do_filter_by_date', false );
		}
		if ( !isset( $p_filter_arr['view_state'] ) ) {
			$p_filter_arr['view_state'] = gpc_get_int( 'view_state', '' );
		}
		if ( !isset( $p_filter_arr['relationship_type'] ) ) {
			$p_filter_arr['relationship_type'] = gpc_get_int( 'relationship_type', -1 );
		}
		if ( !isset( $p_filter_arr['relationship_bug'] ) ) {
			$p_filter_arr['relationship_bug'] = gpc_get_int( 'relationship_bug', 0 );
		}

		$t_custom_fields 		= custom_field_get_ids(); # @@@ (thraxisp) This should really be the linked ids, but we don't know the project
		$f_custom_fields_data 	= array();
		if ( is_array( $t_custom_fields ) && ( sizeof( $t_custom_fields ) > 0 ) ) {
			foreach( $t_custom_fields as $t_cfid ) {
				if ( is_array( gpc_get( 'custom_field_' . $t_cfid, null ) ) ) {
					$f_custom_fields_data[$t_cfid] = gpc_get_string_array( 'custom_field_' . $t_cfid, META_FILTER_ANY );
				} else {
					$f_custom_fields_data[$t_cfid] = gpc_get_string( 'custom_field_' . $t_cfid, META_FILTER_ANY );
					$f_custom_fields_data[$t_cfid] = array( $f_custom_fields_data[$t_cfid] );
				}
			}
		}

		$t_multi_select_list = array( 'show_category' => 'string',
									  'show_severity' => 'string',
									  'show_status' => 'string',
									  'reporter_id' => 'string',
									  'handler_id' => 'string',
									  'show_resolution' => 'string',
									  'show_priority' => 'string',
									  'show_build' => 'string',
									  'show_version' => 'string',
									  'hide_status' => 'string',
									  'fixed_in_version' => 'string',
									  'user_monitor' => 'string',
									  'show_profile' => 'string'
									 );
		foreach( $t_multi_select_list as $t_multi_field_name => $t_multi_field_type ) {
			if ( !isset( $p_filter_arr[$t_multi_field_name] ) ) {
				if ( 'hide_status' == $t_multi_field_name ) {
					$p_filter_arr[$t_multi_field_name] = array( config_get( 'hide_status_default' ) );
				} else if ( 'custom_fields' == $t_multi_field_name ) {
					$p_filter_arr[$t_multi_field_name] = array( $f_custom_fields_data );
				} else {
					$p_filter_arr[$t_multi_field_name] = array( META_FILTER_ANY );
				}
			} else {
				if ( !is_array( $p_filter_arr[$t_multi_field_name] ) ) {
					$p_filter_arr[$t_multi_field_name] = array( $p_filter_arr[$t_multi_field_name] );
				}
				$t_checked_array = array();
				foreach ( $p_filter_arr[$t_multi_field_name] as $t_filter_value ) {
					$t_filter_value = stripslashes( $t_filter_value );
					if ( ( 5 <= $t_cookie_vers ) && ( ( $t_filter_value == 'any' ) || ( is_numeric($t_filter_value) && $t_filter_value == 0 ) ) ) {
						$t_filter_value = META_FILTER_ANY;
					}
					if ( ( 5 <= $t_cookie_vers ) && ( $t_filter_value == 'none' ) ) {
						$t_filter_value = META_FILTER_NONE;
					}
					if ( 'string' == $t_multi_field_type ) {
						$t_checked_array[] = db_prepare_string( $t_filter_value );
					} else if ( 'int' == $t_multi_field_type ) {
						$t_checked_array[] = db_prepare_int( $t_filter_value );
					} else if ( 'array' == $t_multi_field_type ) {
						$t_checked_array[] = $t_filter_value;
					}
				}
				$p_filter_arr[$t_multi_field_name] = $t_checked_array;
			}
		}

		if ( is_array( $t_custom_fields ) && ( sizeof( $t_custom_fields ) > 0 ) ) {
			foreach( $t_custom_fields as $t_cfid ) {
				if ( !isset( $p_filter_arr['custom_fields'][$t_cfid] ) ) {
					$p_filter_arr['custom_fields'][$t_cfid] = array( "[any]" );
				} else {
					if ( !is_array( $p_filter_arr['custom_fields'][$t_cfid] ) ) {
						$p_filter_arr['custom_fields'][$t_cfid] = array( $p_filter_arr['custom_fields'][$t_cfid] );
					}
					$t_checked_array = array();
					foreach ( $p_filter_arr['custom_fields'][$t_cfid] as $t_filter_value ) {
						$t_filter_value = stripslashes( $t_filter_value );
						if ( ( 5 == $t_cookie_vers ) && ( $t_filter_value == 'any' ) ) {
							$t_filter_value = META_FILTER_ANY;
						}
						$t_checked_array[] = db_prepare_string( $t_filter_value );
					}
					$p_filter_arr['custom_fields'][$t_cfid] = $t_checked_array;
				}
			}
		}
		# all of our filter values are now guaranteed to be there, and correct.
		return $p_filter_arr;
	}


	/**
	 * The following functions each print out an individual filter field.
	 * They are derived from view_filters_page.php
	 *
	 * The functions follow a strict naming convention:
	 *
	 *   print_filter_[filter_name]
	 *
	 * Where [filter_name] is the same as the "name" of the form element for
	 * that filter. This naming convention is depended upon by the controller
	 * at the end of the script.
	 */
	/**
	 * I expect that this code could be made simpler by refactoring into a
	 * class so as to avoid all those calls to global(which are pretty ugly)
	 *
	 * These functions could also be shared by view_filters_page.php
	 *
	 */
	function print_filter_reporter_id(){
		global $t_select_modifier, $t_filter;
		?>
		<select <?php PRINT $t_select_modifier;?> name="reporter_id[]">
		<?php
			# if current user is a reporter, and limited reports set to ON, only display that name
		# @@@ thraxisp - access_has_project_level checks greater than or equal to,
		#   this assumed that there aren't any holes above REPORTER where the limit would apply
		#
			if ( ( ON === config_get( 'limit_reporters' ) ) && ( ! access_has_project_level( REPORTER + 1 ) ) ) {
				$t_id = auth_get_current_user_id();
				$t_username = user_get_field( $t_id, 'username' );
				$t_realname = user_get_field( $t_id, 'realname' );
				$t_display_name = string_attribute( $t_username );
				if ( ( isset( $t_realname ) ) && ( $t_realname > "" ) && ( ON == config_get( 'show_realname' ) ) ){
					$t_display_name = string_attribute( $t_realname );
				}
				PRINT '<option value="' . $t_id . '" SELECTED >' . $t_display_name . '</option>';
			} else {
		?>
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['reporter_id'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<?php
				if ( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
					PRINT '<option value="' . META_FILTER_MYSELF . '" ';
					check_selected( $t_filter['reporter_id'], META_FILTER_MYSELF );
					PRINT '>[' . lang_get( 'myself' ) . ']</option>';
				}
			?>
			<?php print_reporter_option_list( $t_filter['reporter_id'] ) ?>
			<?php } ?>
		</select>
		<?php
	}


	function print_filter_user_monitor(){
		global $t_select_modifier, $t_filter;
		?>
	<!-- Monitored by -->
		<select <?php PRINT $t_select_modifier;?> name="user_monitor[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['user_monitor'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<?php
				if ( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
					PRINT '<option value="' . META_FILTER_MYSELF . '" ';
					check_selected( $t_filter['user_monitor'], META_FILTER_MYSELF );
					PRINT '>[' . lang_get( 'myself' ) . ']</option>';
				}
			?>
			<?php print_reporter_option_list( $t_filter['user_monitor'] ) ?>
		</select>
		<?php
	}

	function print_filter_handler_id(){
		global $t_select_modifier, $t_filter, $f_view_type;
		?>
		<!-- Handler -->
		<select <?php PRINT $t_select_modifier;?> name="handler_id[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['handler_id'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<?php if ( access_has_project_level( config_get( 'view_handler_threshold' ) ) ) { ?>
			<option value="<?php echo META_FILTER_NONE ?>" <?php check_selected( $t_filter['handler_id'], META_FILTER_NONE ); ?>>[<?php echo lang_get( 'none' ) ?>]</option>
			<?php
				if ( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
					PRINT '<option value="' . META_FILTER_MYSELF . '" ';
					check_selected( $t_filter['handler_id'], META_FILTER_MYSELF );
					PRINT '>[' . lang_get( 'myself' ) . ']</option>';
				}
			?>
			<?php print_assign_to_option_list( $t_filter['handler_id'] ) ?>
			<?php } ?>
		</select>
		<?php
	}

	function print_filter_show_category(){
		global $t_select_modifier, $t_filter;
		?>
		<!-- Category -->
		<select <?php PRINT $t_select_modifier;?> name="show_category[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_category'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<?php # This shows orphaned categories as well as selectable categories ?>
			<?php print_category_complete_option_list( $t_filter['show_category'] ) ?>
		</select>
		<?php
	}

	function print_filter_show_severity(){
		global $t_select_modifier, $t_filter;
		?><!-- Severity -->
			<select <?php PRINT $t_select_modifier;?> name="show_severity[]">
				<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_severity'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
				<?php print_enum_string_option_list( 'severity', $t_filter['show_severity'] ) ?>
			</select>
		<?php
	}

	function print_filter_show_resolution(){
		global $t_select_modifier, $t_filter;
		?><!-- Resolution -->
			<select <?php PRINT $t_select_modifier;?> name="show_resolution[]">
				<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_resolution'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
				<?php print_enum_string_option_list( 'resolution', $t_filter['show_resolution'] ) ?>
			</select>
		<?php
	}

	function print_filter_show_status(){
		global $t_select_modifier, $t_filter;
		?>	<!-- Status -->
			<select <?php PRINT $t_select_modifier;?> name="show_status[]">
				<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_status'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
				<?php print_enum_string_option_list( 'status', $t_filter['show_status'] ) ?>
			</select>
		<?php
	}

	function print_filter_hide_status(){
		global $t_select_modifier, $t_filter;
		?><!-- Hide Status -->
			<select <?php PRINT $t_select_modifier;?> name="hide_status[]">
				<option value="<?php echo META_FILTER_NONE ?>">[<?php echo lang_get( 'none' ) ?>]</option>
				<?php print_enum_string_option_list( 'status', $t_filter['hide_status'] ) ?>
			</select>
		<?php
	}

	function print_filter_show_build(){
		global $t_select_modifier, $t_filter;
		?><!-- Build -->
		<select <?php PRINT $t_select_modifier;?> name="show_build[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_build'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<option value="<?php echo META_FILTER_NONE ?>" <?php check_selected( $t_filter['show_build'], META_FILTER_NONE ); ?>>[<?php echo lang_get( 'none' ) ?>]</option>
			<?php print_build_option_list( $t_filter['show_build'] ) ?>
		</select>
		<?php
	}

	function print_filter_show_version(){
		global $t_select_modifier, $t_filter;
		?><!-- Version -->
		<select <?php PRINT $t_select_modifier;?> name="show_version[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_version'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<option value="<?php echo META_FILTER_NONE ?>" <?php check_selected( $t_filter['show_version'], META_FILTER_NONE ); ?>>[<?php echo lang_get( 'none' ) ?>]</option>
			<?php print_version_option_list( $t_filter['show_version'], null, VERSION_RELEASED, false, true ) ?>
		</select>
		<?php
	}

	function print_filter_show_fixed_in_version(){
		global $t_select_modifier, $t_filter;
		?><!-- Fixed in Version -->
		<select <?php PRINT $t_select_modifier;?> name="fixed_in_version[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['fixed_in_version'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<option value="<?php echo META_FILTER_NONE ?>" <?php check_selected( $t_filter['fixed_in_version'], META_FILTER_NONE ); ?>>[<?php echo lang_get( 'none' ) ?>]</option>
			<?php print_version_option_list( $t_filter['fixed_in_version'], null, VERSION_ALL, false, true ) ?>
		</select>
		<?php
	}

	function print_filter_show_priority(){
		global $t_select_modifier, $t_filter;
		?><!-- Priority -->
    <select <?php PRINT $t_select_modifier;?> name="show_priority[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_priority'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<?php print_enum_string_option_list( 'priority', $t_filter['show_priority'] ) ?>
    </select>
		<?php
	}

	function print_filter_show_profile() {
		global $t_select_modifier, $t_filter;
		?><!-- Profile -->
		<select <?php PRINT $t_select_modifier;?> name="show_profile[]">
			<option value="<?php echo META_FILTER_ANY ?>" <?php check_selected( $t_filter['show_profile'], META_FILTER_ANY ); ?>>[<?php echo lang_get( 'any' ) ?>]</option>
			<?php print_profile_option_list_for_project( helper_get_current_project(), $t_filter['show_profile'] ); ?>
		</select>
		<?php
	}

	function print_filter_per_page(){
		global $t_filter;
		?><!-- Number of bugs per page -->
		<input type="text" name="per_page" size="3" maxlength="7" value="<?php echo $t_filter['per_page'] ?>" />
		<?php
	}

	function print_filter_view_state(){
		global $t_select_modifier, $t_filter;
		?><!-- View Status -->
		<select name="view_state">
			<?php
			PRINT '<option value="' . META_FILTER_ANY . '" ';
			check_selected( $t_filter['view_state'], META_FILTER_ANY );
			PRINT '>[' . lang_get( 'any' ) . ']</option>';
			PRINT '<option value="' . VS_PUBLIC . '" ';
			check_selected( $t_filter['view_state'], VS_PUBLIC );
			PRINT '>' . lang_get( 'public' ) . '</option>';
			PRINT '<option value="' . VS_PRIVATE . '" ';
			check_selected( $t_filter['view_state'], VS_PRIVATE );
			PRINT '>' . lang_get( 'private' ) . '</option>';
			?>
		</select>
		<?php
	}

	function print_filter_sticky_issues(){
		global $t_filter;
		?><!-- Show or hide sticky bugs -->
			<input type="checkbox" name="sticky_issues" <?php check_checked( gpc_string_to_bool( $t_filter['sticky_issues'] ), 'on' ); ?> />
		<?php
	}

	function print_filter_highlight_changed(){
		global $t_filter;
		?><!-- Highlight changed bugs -->
			<input type="text" name="highlight_changed" size="3" maxlength="7" value="<?php echo $t_filter['highlight_changed'] ?>" />
		<?php
	}

	function print_filter_do_filter_by_date( $p_hide_checkbox=false ){
		global $t_filter;
		?>
		<table cellspacing="0" cellpadding="0">
		<?php if ( ! $p_hide_checkbox ) {
		?>
		<tr><td colspan="2">
			<input type="checkbox" name="do_filter_by_date" <?php
				check_checked( $t_filter['do_filter_by_date'], 'on' );
				if ( ON == config_get( 'use_javascript' ) ) {
					print "onclick=\"SwitchDateFields();\""; } ?> />
			<?php echo lang_get( 'use_date_filters' ) ?>
		</td></tr>
		<?php }
		$t_menu_disabled = ( 'on' == $t_filter['do_filter_by_date'] ) ? '' : ' disabled ';
		?>

		<!-- Start date -->
		<tr>
			<td>
			<?php echo lang_get( 'start_date' ) ?>:
			</td>
			<td nowrap="nowrap">
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
			foreach( $t_chars as $t_char ) {
				if ( strcasecmp( $t_char, "M" ) == 0 ) {
					print "<select name=\"start_month\" $t_menu_disabled>";
					print_month_option_list( $t_filter['start_month'] );
					print "</select>\n";
				}
				if ( strcasecmp( $t_char, "D" ) == 0 ) {
					print "<select name=\"start_day\" $t_menu_disabled>";
					print_day_option_list( $t_filter['start_day'] );
					print "</select>\n";
				}
				if ( strcasecmp( $t_char, "Y" ) == 0 ) {
					print "<select name=\"start_year\" $t_menu_disabled>";
					print_year_option_list( $t_filter['start_year'] );
					print "</select>\n";
				}
			}
			?>
			</td>
		</tr>
		<!-- End date -->
		<tr>
			<td>
			<?php echo lang_get( 'end_date' ) ?>:
			</td>
			<td>
			<?php
			$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
			foreach( $t_chars as $t_char ) {
				if ( strcasecmp( $t_char, "M" ) == 0 ) {
					print "<select name=\"end_month\" $t_menu_disabled>";
					print_month_option_list( $t_filter['end_month'] );
					print "</select>\n";
				}
				if ( strcasecmp( $t_char, "D" ) == 0 ) {
					print "<select name=\"end_day\" $t_menu_disabled>";
					print_day_option_list( $t_filter['end_day'] );
					print "</select>\n";
				}
				if ( strcasecmp( $t_char, "Y" ) == 0 ) {
					print "<select name=\"end_year\" $t_menu_disabled>";
					print_year_option_list( $t_filter['end_year'] );
					print "</select>\n";
				}
			}
			?>
			</td>
		</tr>
		</table>
		<?php
	}

	function print_filter_relationship_type(){
		global $t_filter;
		$c_reltype_value = $t_filter['relationship_type'];
		if (!$c_reltype_value) {
			$c_reltype_value = -1;
		}
		relationship_list_box ($c_reltype_value, "relationship_type", true); ?>
		<input type="text" name="relationship_bug" size="5" maxlength="10" value="<?php echo $t_filter['relationship_bug']?>" />
		<?php

	}

	function print_filter_custom_field($p_field_id){
		global $t_filter, $t_accessible_custom_fields_names, $t_accessible_custom_fields_types, $t_accessible_custom_fields_values, $t_accessible_custom_fields_ids, $t_select_modifier;

		$j = array_search($p_field_id, $t_accessible_custom_fields_ids);
		if($j === null || $j === false){
			# Note: Prior to PHP 4.2.0, array_search() returns NULL on failure instead of FALSE.
			?>
			<span style="color:red;weight:bold;">
				unknown custom filter (custom <?php $p_field_id; ?>)
			</span>
			<?php
		} elseif ( isset( $t_accessible_custom_fields_names[$j] ) ) {
			if ($t_accessible_custom_fields_types[$j] == CUSTOM_FIELD_TYPE_DATE) {
				print_filter_custom_field_date($j, $p_field_id) ;
			} else {
				echo '<select ' . $t_select_modifier . ' name="custom_field_' . $p_field_id .'[]">';
				echo '<option value="' . META_FILTER_ANY . '" ';
				check_selected( $t_filter['custom_fields'][ $p_field_id ], META_FILTER_ANY );
				echo '>[' . lang_get( 'any' ) .']</option>';
				# don't show META_FILTER_NONE for enumerated types as it's not possible for them to be blank
				if ( ! in_array( $t_accessible_custom_fields_types[$j], array( CUSTOM_FIELD_TYPE_ENUM, CUSTOM_FIELD_TYPE_LIST, CUSTOM_FIELD_TYPE_MULTILIST ) ) ) {
					echo '<option value="' . META_FILTER_NONE . '" ';
					check_selected( $t_filter['custom_fields'][ $p_field_id ], META_FILTER_NONE );
					echo '>[' . lang_get( 'none' ) .']</option>';
				}
				foreach( $t_accessible_custom_fields_values[$j] as $t_item ) {
					if ( ( strtolower( $t_item ) != META_FILTER_ANY ) && ( strtolower( $t_item ) != META_FILTER_NONE ) ) {
						echo '<option value="' .  string_html_entities( $t_item )  . '" ';
						if ( isset( $t_filter['custom_fields'][ $p_field_id ] ) ) {
							check_selected( $t_filter['custom_fields'][ $p_field_id ], $t_item );
						}
						echo '>' . string_shorten( $t_item )  . '</option>' . "\n";
					}
				}
				echo '</select>';
			}
		}

	}

	function print_filter_show_sort() {
		global $t_filter;

		# get all of the displayed fields for sort, then drop ones that
		#  are not appropriate and translate the rest
		$t_fields = helper_call_custom_function( 'get_columns_to_view', array() );
		$t_n_fields = count( $t_fields );
		$t_shown_fields[""] = "";
		for ( $i=0; $i < $t_n_fields; $i++ ) {
			if ( !in_array( $t_fields[$i], array( 'selection', 'edit', 'bugnotes_count', 'attachment' ) ) ) {
        		if ( strpos( $t_fields[$i], 'custom_' ) === 0 ) {
        			$t_field_name = string_display( lang_get_defaulted( substr( $t_fields[$i], strlen( 'custom_' ) ) ) );
        		} else {
        			$t_field_name = string_get_field_name( $t_fields[$i] );
        		}
				$t_shown_fields[$t_fields[$i]] = $t_field_name;
			}
		}
		$t_shown_dirs[""] = "";
		$t_shown_dirs["ASC"] = lang_get( 'bugnote_order_asc' );
		$t_shown_dirs["DESC"] = lang_get( 'bugnote_order_desc' );

		# get default values from filter structure
		$t_sort_fields = split( ',', $t_filter['sort'] );
		$t_dir_fields = split( ',', $t_filter['dir'] );
		if ( !isset( $t_sort_fields[1] ) ) {
			$t_sort_fields[1] = '';
			$t_dir_fields[1] = '';
		}

		# if there are fields to display, show the dropdowns
		if ( count( $t_fields ) > 0 ) {
			# display a primary and secondary sort fields
			echo '<select name="sort_0">';
			foreach ( $t_shown_fields as $key => $val ) {
				echo "<option value=\"$key\"";
				check_selected( $key, $t_sort_fields[0] );
				echo ">$val</option>";
			}
			echo '</select>';

			echo '<select name="dir_0">';
			foreach ( $t_shown_dirs as $key => $val ) {
				echo "<option value=\"$key\"";
				check_selected( $key, $t_dir_fields[0] );
				echo ">$val</option>";
			}
			echo '</select>';

			echo ', ';

			# for secondary sort
			echo '<select name="sort_1">';
			foreach ( $t_shown_fields as $key => $val ) {
				echo "<option value=\"$key\"";
				check_selected( $key, $t_sort_fields[1] );
				echo ">$val</option>";
			}
			echo '</select>';
			echo '<select name="dir_1">';
			foreach ($t_shown_dirs as $key => $val ) {
				echo "<option value=\"$key\"";
				check_selected( $key, $t_dir_fields[1] );
				echo ">$val</option>";
			}
			echo '</select>';
		} else {
			echo lang_get_defaulted( 'last_updated' ) . lang_get( 'bugnote_order_desc' );
			echo "<input type=\"hidden\" name=\"sort_1\" value=\"last_updated\" />";
			echo "<input type=\"hidden\" name=\"dir_1\" value=\"DESC\" />";
		}
	}



	function print_filter_custom_field_date($p_field_num, $p_field_id) {
		global $t_filter, $t_accessible_custom_fields_names, $t_accessible_custom_fields_types, $t_accessible_custom_fields_values, $t_accessible_custom_fields_ids, $t_select_modifier;

		$t_js_toggle_func = "toggle_custom_date_field_" . $p_field_id . "_controls" ;
		# Resort the values so there ordered numerically, they are sorted as strings otherwise which
		# may be wrong for dates before early 2001.
		array_multisort($t_accessible_custom_fields_values[$p_field_num], SORT_NUMERIC, SORT_ASC) ;
		if (isset($t_accessible_custom_fields_values[$p_field_num][0])) {
			$t_sel_start_year = date( 'Y', $t_accessible_custom_fields_values[$p_field_num][0]) ;
		}
		$t_count = count($t_accessible_custom_fields_values[$p_field_num]) ;
		if (isset($t_accessible_custom_fields_values[$p_field_num][$t_count-1])) {
			$t_sel_end_year = date( 'Y', $t_accessible_custom_fields_values[$p_field_num][$t_count-1]) ;
		}

		$t_start = date( 'U' ); # Default to today in filters..
		$t_end = $t_start;

		if ( isset( $t_filter['custom_fields'][$p_field_id][1] ) ) {
			$t_start_time = $t_filter['custom_fields'][$p_field_id][1];
		} else {
			$t_start_time = 0;
		}

		if ( isset( $t_filter['custom_fields'][$p_field_id][2] ) ) {
			$t_end_time = $t_filter['custom_fields'][$p_field_id][2];
		} else {
			$t_end_time = 0;
		}

		$t_start_disable = true;
		$t_end_disable = true;

		// if $t_filter['custom_fields'][$p_field_id][0] is not set (ie no filter), we will drop through the
		// following switch and use the default values above, so no need to check if stuff is set or not.
		switch ($t_filter['custom_fields'][$p_field_id][0]) {
		case CUSTOM_FIELD_DATE_ANY:
		case CUSTOM_FIELD_DATE_NONE:
			break;
		case CUSTOM_FIELD_DATE_BETWEEN:
			$t_start_disable = false;
			$t_end_disable = false;
			$t_start = $t_start_time;
			$t_end = $t_end_time;
			break;
		case CUSTOM_FIELD_DATE_ONORBEFORE:
			$t_start_disable = false;
			$t_start = $t_end_time;
			break;
		case CUSTOM_FIELD_DATE_BEFORE:
			$t_start_disable = false;
			$t_start = $t_end_time;
			break;
		case CUSTOM_FIELD_DATE_ON:
			$t_start_disable = false;
			$t_start = $t_start_time;
			break;
		case CUSTOM_FIELD_DATE_AFTER:
			$t_start_disable = false;
			$t_start = $t_start_time;
			break;
		case CUSTOM_FIELD_DATE_ONORAFTER:
			$t_start_disable = false;
			$t_start = $t_start_time;
			break;
		}

		echo "\n<table cellspacing=\"0\" cellpadding=\"0\"><tr><td>\n" ;
		echo "<select size=\"1\" name=\"custom_field_" . $p_field_id . "_control\" OnChange=\"" . $t_js_toggle_func . "();\">\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_ANY . '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ANY );
			echo '>' . lang_get( 'any' ) . '</option>' . "\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_NONE	. '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_NONE );
			echo '>' . lang_get( 'none' ) . '</option>' . "\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_BETWEEN . '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_BETWEEN );
			echo '>' . lang_get( 'between' ) . '</option>' . "\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_ONORBEFORE . '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ONORBEFORE );
			echo '>' . lang_get( 'on_or_before' ) . '</option>' . "\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_BEFORE . '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_BEFORE );
			echo '>' . lang_get( 'before' ) . '</option>' . "\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_ON . '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ON );
			echo '>' . lang_get( 'on' ) . '</option>' . "\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_AFTER . '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_AFTER );
			echo '>' . lang_get( 'after' ) . '</option>' . "\n";
		echo '<option value="' . CUSTOM_FIELD_DATE_ONORAFTER . '"';
			check_selected( $t_filter['custom_fields'][$p_field_id][0], CUSTOM_FIELD_DATE_ONORAFTER	);
			echo '>' . lang_get( 'on_or_after' ) . '</option>' . "\n";
		echo '</select>' . "\n";

		echo "</td></tr>\n<tr><td>";

		print_date_selection_set("custom_field_" . $p_field_id . "_start" , config_get( 'short_date_format'), $t_start, $t_start_disable, false, $t_sel_start_year, $t_sel_end_year);
		print "</td></tr>\n<tr><td>";
		print_date_selection_set("custom_field_" . $p_field_id . "_end" , config_get( 'short_date_format'), $t_end, $t_end_disable, false, $t_sel_start_year, $t_sel_end_year);
		print "</td></tr>\n</table>";
	}

	#===================================
	# Caching
	#===================================

	#########################################
	# SECURITY NOTE: cache globals are initialized here to prevent them
	#   being spoofed if register_globals is turned on

	$g_cache_filter = array();

	# --------------------
	# Cache a filter row if necessary and return the cached copy
	# If the second parameter is true (default), trigger an error
	# if the filter can't be found.  If the second parameter is
	# false, return false if the filter can't be found.
	function filter_cache_row( $p_filter_id, $p_trigger_errors=true) {
		global $g_cache_filter;

		$c_filter_id = db_prepare_int( $p_filter_id );

		$t_filters_table = config_get( 'mantis_filters_table' );

		if ( isset ( $g_cache_filter[$c_filter_id] ) ) {
			return $g_cache_filter[$c_filter_id];
		}

		$query = "SELECT *
				  FROM $t_filters_table
				  WHERE id='$c_filter_id'";
		$result = db_query( $query );

		if ( 0 == db_num_rows( $result ) ) {
			if ( $p_trigger_errors ) {
				error_parameters( $p_filter_id );
				trigger_error( ERROR_FILTER_NOT_FOUND, ERROR );
			} else {
				return false;
			}
		}

		$row = db_fetch_array( $result );

		$g_cache_filter[$c_filter_id] = $row;

		return $row;
	}

	# --------------------
	# Clear the filter cache (or just the given id if specified)
	function filter_clear_cache( $p_filter_id = null ) {
		global $g_cache_filter;

		if ( null === $p_filter_id ) {
			$g_cache_filter = array();
		} else {
			$c_filter_id = db_prepare_int( $p_filter_id );
			unset( $g_cache_filter[$c_filter_id] );
		}

		return true;
	}

	# --------------------
	# return a filter row
	function filter_get_row( $p_filter_id ) {
		return filter_cache_row( $p_filter_id );
	}

	# --------------------
	function filter_get_field( $p_filter_id, $p_field_name ) {
		$row = filter_get_row( $p_filter_id );

		if ( isset( $row[$p_field_name] ) ) {
			return $row[$p_field_name];
		} else {
			error_parameters( $p_field_name );
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			return '';
		}
	}
?>
