<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: filter_api.php,v 1.57 2004-08-21 13:07:14 prichards Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );
	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'collapse_api.php' );

	###########################################################################
	# Filter API
	###########################################################################

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
	function filter_get_bug_rows( &$p_page_number, &$p_per_page, &$p_page_count, &$p_bug_count, $custom_filter = null ) {
		$t_bug_table			= config_get( 'mantis_bug_table' );
		$t_bug_text_table		= config_get( 'mantis_bug_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_custom_field_string_table	= config_get( 'mantis_custom_field_string_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_project_table		= config_get( 'mantis_project_table' );
		$t_bug_monitor_table	= config_get( 'mantis_bug_monitor_table' );
		$t_limit_reporters		= config_get( 'limit_reporters' );
		$t_report_bug_threshold		= config_get( 'report_bug_threshold' );

		if ( $custom_filter == null ) {
			$t_filter = current_user_get_bug_filter();
		} else {
			$t_filter = $custom_filter;
		}
		$t_filter = filter_ensure_valid_filter( $t_filter );

		if ( false === $t_filter ) {
			return false; # signify a need to create a cookie
			#@@@ error instead?
		}

		$t_project_id	= helper_get_current_project();
		$t_user_id		= auth_get_current_user_id();

		$t_where_clauses = array( "$t_project_table.enabled = 1", "$t_project_table.id = $t_bug_table.project_id" );
		$t_select_clauses = array( "$t_bug_table.*" );
		$t_join_clauses = array();
		$t_from_clauses = array();

		if ( ALL_PROJECTS == $t_project_id ) {
			if ( !current_user_is_administrator() ) {
				$t_projects = current_user_get_accessible_projects();

				if ( 0 == count( $t_projects ) ) {
					return array();  # no accessible projects, return an empty array
				} else {
					$t_clauses = array();

					#@@@ use project_id IN (1,2,3,4) syntax if we can
					for ( $i=0 ; $i < count( $t_projects ) ; $i++) {
						array_push( $t_clauses, "($t_bug_table.project_id='$t_projects[$i]')" );
					}

					array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
				}
			}
		} else {
			access_ensure_project_level( VIEWER, $t_project_id );

			array_push( $t_where_clauses, "($t_bug_table.project_id='$t_project_id')" );
		}

		# private bug selection
		if ( !access_has_project_level( config_get( 'private_bug_threshold' ) ) ) {
			$t_public = VS_PUBLIC;
			array_push( $t_where_clauses, "($t_bug_table.view_state='$t_public' OR $t_bug_table.reporter_id='$t_user_id')" );
		} else {
			$t_view_state = db_prepare_int( $t_filter['view_state'] );
			if ( ( $t_view_state != 'any' ) && ( !is_blank( $t_view_state ) ) ) {
				array_push( $t_where_clauses, "($t_bug_table.view_state='$t_view_state')" );
			}
		}

		# reporter
		$t_any_found = false;

		foreach( $t_filter['reporter_id'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['reporter_id'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['reporter_id'] as $t_filter_member ) {
				$c_reporter_id = db_prepare_int( $t_filter_member );
				if ( META_FILTER_MYSELF == $c_reporter_id ) {
					if ( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
						$c_reporter_id = auth_get_current_user_id();
						array_push( $t_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
					}
				} else {
					array_push( $t_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
				}
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# limit reporter
		if ( ( ON === $t_limit_reporters ) && ( current_user_get_access_level() <= $t_report_bug_threshold ) ) {
			$c_reporter_id = db_prepare_int( auth_get_current_user_id() );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# handler
		$t_any_found = false;

		foreach( $t_filter['handler_id'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['handler_id'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();
			if ( !isset( $t_filter['and_not_assigned'] ) ) {
				$t_filter['and_not_assigned'] = '';
			}

			foreach( $t_filter['handler_id'] as $t_filter_member ) {
				if ( 'none' == $t_filter_member ) {
					array_push( $t_clauses, "$t_bug_table.handler_id=0" );
				} else {
					$c_handler_id = db_prepare_int( $t_filter_member );
					if ( META_FILTER_MYSELF == $c_handler_id ) {
						if ( access_has_project_level( config_get( 'handle_bug_threshold' ) ) ) {
							$c_handler_id = auth_get_current_user_id();
							if ( 'on' != $t_filter['and_not_assigned'] ) {
								array_push( $t_clauses, "($t_bug_table.handler_id='$c_handler_id')" );
							} else {
								array_push( $t_clauses, "(($t_bug_table.handler_id='$c_handler_id') OR ($t_bug_table.handler_id=0))" );
							}
						}
					} else {
						if ( 'on' != $t_filter['and_not_assigned'] ) {
							array_push( $t_clauses, "($t_bug_table.handler_id='$c_handler_id')" );
						} else {
							array_push( $t_clauses, "(($t_bug_table.handler_id='$c_handler_id') OR ($t_bug_table.handler_id=0))" );
						}
					}
				}
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# category
		$t_any_found = false;

		foreach( $t_filter['show_category'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_category'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_category'] as $t_filter_member ) {
				$c_show_category = db_prepare_string( $t_filter_member );
				array_push( $t_clauses, "($t_bug_table.category='$c_show_category')" );
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# severity
		$t_any_found = false;
		foreach( $t_filter['show_severity'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
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
				array_push( $t_clauses, "($t_bug_table.severity='$c_show_severity')" );
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
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

			if ( ( 'any' == $t_this_status ) || ( is_blank( $t_this_status ) ) ) {
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
				if ( ( 'any' == $t_this_status ) || ( is_blank( $t_this_status ) ) ) {
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
				array_push( $t_clauses, "($t_bug_table.status='$c_show_status')" );
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# resolution
		$t_any_found = false;
		foreach( $t_filter['show_resolution'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
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
				array_push( $t_clauses, "($t_bug_table.resolution='$c_show_resolution')" );
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# product build
		$t_any_found = false;
		foreach( $t_filter['show_build'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_build'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_build'] as $t_filter_member ) {
				$c_show_build = db_prepare_string( $t_filter_member );
				array_push( $t_clauses, "($t_bug_table.build='$c_show_build')" );
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# product version
		$t_any_found = false;
		foreach( $t_filter['show_version'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['show_version'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['show_version'] as $t_filter_member ) {
				$c_show_version = db_prepare_string( $t_filter_member );
				array_push( $t_clauses, "($t_bug_table.version='$c_show_version')" );
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
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
			if ( 'any' == $t_filter_member ) {
				$t_any_found = true;
			}
		}
		if ( count( $t_filter['fixed_in_version'] ) == 0 ) {
			$t_any_found = true;
		}
		if ( !$t_any_found ) {
			$t_clauses = array();

			foreach( $t_filter['fixed_in_version'] as $t_filter_member ) {
				$c_fixed_in_version = db_prepare_string( $t_filter_member );
				array_push( $t_clauses, "($t_bug_table.fixed_in_version='$c_fixed_in_version')" );
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# users monitoring a bug
		$t_any_found = false;
		foreach( $t_filter['user_monitor'] as $t_filter_member ) {
			if ( 'any' == $t_filter_member ) {
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
			array_push( $t_join_clauses, "LEFT JOIN $t_bug_monitor_table as $t_table_name ON $t_table_name.bug_id = $t_bug_table.id" );

			foreach( $t_filter['user_monitor'] as $t_filter_member ) {
				$c_user_monitor = db_prepare_int( $t_filter_member );
				if ( META_FILTER_MYSELF == $c_user_monitor ) {
					if ( access_has_project_level( config_get( 'monitor_bug_threshold' ) ) ) {
						$c_user_monitor = auth_get_current_user_id();
						array_push( $t_clauses, "($t_table_name.user_id='$c_user_monitor')" );
					}
				} else {
					array_push( $t_clauses, "($t_table_name.user_id='$c_user_monitor')" );
				}
			}
			array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
		}

		# custom field filters
		if( ON == config_get( 'filter_by_custom_fields' ) ) {
			# custom field filtering
			$t_custom_fields = custom_field_get_ids();

			foreach( $t_custom_fields as $t_cfid ) {
			$t_first_time = true;
			$t_custom_where_clause = '';
				# Ignore all custom filters that are not set, or that are set to '' or "any"
				$t_any_found = false;
				foreach( $t_filter['custom_fields'][$t_cfid] as $t_filter_member ) {
					if ( 'any' == $t_filter_member ) {
						$t_any_found = true;
					}
				}
				if ( !isset( $t_filter['custom_fields'][$t_cfid] ) ) {
					$t_any_found = true;
				}
				if ( !$t_any_found ) {
					$t_def = custom_field_get_definition( $t_cfid );				
					$t_table_name = $t_custom_field_string_table . '_' . $t_cfid;
					array_push( $t_join_clauses, "LEFT JOIN $t_custom_field_string_table as $t_table_name ON $t_table_name.bug_id = $t_bug_table.id" );
					foreach( $t_filter['custom_fields'][$t_cfid] as $t_filter_member ) {
						if ( isset( $t_filter_member ) &&
							( 'any' != strtolower( $t_filter_member ) ) &&
							( !is_blank( trim( $t_filter_member ) ) ) ) {

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
							$t_custom_where_clause .= db_prepare_string( trim( $t_filter_member ) );
							$t_custom_where_clause .= $t_custom_where_clause_closing;
						}
					}
					if ( !is_blank( $t_custom_where_clause ) ) {
						array_push( $t_where_clauses, $t_custom_where_clause . ')' );
					}
				}
			}
		}

		# Simple Text Search - Thnaks to Alan Knowles
		if ( !is_blank( $t_filter['search'] ) ) {
			$c_search = db_prepare_string( $t_filter['search'] );
			array_push( $t_where_clauses,
							"((summary LIKE '%$c_search%')
							 OR ($t_bug_text_table.description LIKE '%$c_search%')
							 OR ($t_bug_text_table.steps_to_reproduce LIKE '%$c_search%')
							 OR ($t_bug_text_table.additional_information LIKE '%$c_search%')
							 OR ($t_bug_table.id LIKE '%$c_search%'))" );
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

		# Get the total number of bugs that meet the criteria.
		$query = "SELECT COUNT( DISTINCT $t_bug_table.id ) as count $t_from $t_join $t_where";
		$result = db_query( $query );
		$bug_count = db_result( $result );

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

		$query2  = "SELECT DISTINCT $t_select
					$t_from
					$t_join
					$t_where";

		# Now add the rest of the criteria i.e. sorting, limit.
		$c_sort = db_prepare_string( $t_filter['sort'] );

		# if sort is blank then default the sort and direction.  This is to fix the
		# symptoms of #3953.  Note that even if the main problem is fixed, we may
		# have to keep this code for a while to handle filters saved with this blank field.
		if ( is_blank( $c_sort ) ) {
			$c_sort = 'last_updated';
			$t_filter['dir'] = 'DESC';
		}

		if ( 'DESC' == $t_filter['dir'] ) {
			$c_dir = 'DESC';
		} else {
			$c_dir = 'ASC';
		}

		$query2 .= " ORDER BY $c_sort $c_dir";
		if ( $c_sort != 'last_updated' ) {
            $query2 .= ', last_updated DESC, date_submitted DESC';
        }
        else {
            $query2 .= ', date_submitted DESC';
        }

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

		$t_sort = $t_filter['sort'];
		$t_dir = $t_filter['dir'];
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
		<form method="post" name="filters" action="<?php PRINT $t_action; ?>">
		<input type="hidden" name="type" value="5" />
		<?php
			if ( $p_for_screen == false ) {
				PRINT '<input type="hidden" name="print" value="1" />';
				PRINT '<input type="hidden" name="offset" value="0" />';
			}
		?>
		<input type="hidden" name="sort" value="<?php PRINT $t_sort ?>" />
		<input type="hidden" name="dir" value="<?php PRINT $t_dir ?>" />
		<input type="hidden" name="page_number" value="<?php PRINT $p_page_number ?>" />
		<input type="hidden" name="view_type" value="<?php PRINT $t_view_type ?>" />
		<table class="width100" cellspacing="1">

		<?php
		if ( $p_expanded ) {
			$t_filter_cols = 7;
			$t_custom_cols = $t_filter_cols;
			if ( ON == config_get( 'filter_by_custom_fields' ) ) {
				$t_custom_cols = config_get( 'filter_custom_fields_per_row' );
			}

			$t_current_user_access_level = current_user_get_access_level();
			$t_accessible_custom_fields_ids = array();
			$t_accessible_custom_fields_names = array();
			$t_accessible_custom_fields_values = array();
			$t_num_custom_rows = 0;
			$t_per_row = 0;

			if ( ON == config_get( 'filter_by_custom_fields' ) ) {
				$t_custom_fields = custom_field_get_ids( $t_project_id );

				foreach ( $t_custom_fields as $t_cfid ) {
					$t_field_info = custom_field_cache_row( $t_cfid, true );
					if ( $t_field_info['access_level_r'] <= $t_current_user_access_level ) {
						$t_accessible_custom_fields_ids[] = $t_cfid;
						$t_accessible_custom_fields_names[] = $t_field_info['name'];
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
		?>

		<tr <?php PRINT "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'reporter_id[]'; ?>"><?php PRINT lang_get( 'reporter' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'user_monitor[]'; ?>"><?php PRINT lang_get( 'monitored_by' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'handler_id[]'; ?>"><?php PRINT lang_get( 'assigned_to' ) ?>:</a>
			</td>
			<td colspan="2" class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_category[]'; ?>"><?php PRINT lang_get( 'category' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_severity[]'; ?>"><?php PRINT lang_get( 'severity' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_resolution[]'; ?>"><?php PRINT lang_get( 'resolution' ) ?>:</a>
			</td>

			<?php 
				if ( $t_custom_cols > $t_filter_cols ) {
					echo '<td colspan="' . ($t_custom_cols - $t_filter_cols) . '">&nbsp;</td>';
				}
			?>
		</tr>

		<tr class="row-1">
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['reporter_id'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['reporter_id'] as $t_current ) {
										$t_this_name = '';
										if ( ( $t_current == 0 ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else if ( META_FILTER_MYSELF == $t_current ) {
											if ( access_has_project_level( config_get( 'report_bug_threshold' ) ) ) {
												$t_this_name = '[' . lang_get( 'myself' ) . ']';
											} else {
												$t_any_found = true;
											}
										} else {
											$t_this_name = user_get_name( $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['user_monitor'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['user_monitor'] as $t_current ) {
										$t_this_name = '';
										if ( ( $t_current == 0 ) || ( is_blank( $t_current ) ) ) {
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
											$t_output = $t_output . '<br>';
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
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['handler_id'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['handler_id'] as $t_current ) {
										$t_this_name = '';
										if ( 'none' == $t_current ) {
											$t_this_name = lang_get( 'none' );
										} else if ( ( $t_current == 0 ) || ( is_blank( $t_current ) ) ) {
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
											$t_output = $t_output . '<br>';
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
								if ( 'on' == $t_filter['and_not_assigned'] ) {
									PRINT ' (' . lang_get( 'or_unassigned' ) . ')';
								}
							?>
			</td>
			<td colspan="2" class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_category'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_category'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_severity'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_severity'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = get_enum_element( 'severity', $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_resolution'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_resolution'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = get_enum_element( 'resolution', $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
			<?php 
				if ( $t_custom_cols > $t_filter_cols ) {
					echo '<td colspan="' . ($t_custom_cols - $t_filter_cols) . '">&nbsp;</td>';
				}
			?>
		</tr>

		<tr <?php PRINT "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_status[]'; ?>"><?php PRINT lang_get( 'status' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'hide_status[]'; ?>"><?php PRINT lang_get( 'hide_status' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_build[]'; ?>"><?php PRINT lang_get( 'product_build' ) ?>:</a>
			</td>
			<td colspan="2" class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'show_version[]'; ?>"><?php PRINT lang_get( 'product_version' ) ?>:</a>
			</td>
			<td colspan="2" class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'fixed_in_version[]'; ?>"><?php PRINT lang_get( 'fixed_in_version' ) ?>:</a>
			</td>
		</tr>

		<tr class="row-1">
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_status'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_status'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = get_enum_element( 'status', $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_none_found = false;
								if ( count( $t_filter['hide_status'] ) == 0 ) {
									PRINT lang_get( 'none' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['hide_status'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'none' ) || ( is_blank( $t_current ) ) ) {
											$t_none_found = true;
										} else {
											$t_this_string = get_enum_element( 'status', $t_current );
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
							?>
			</td>
			<td class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_build'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_build'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
			<td colspan="2" class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['show_version'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['show_version'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
			<td colspan="2" class="small-caption" valign="top">
							<?php
								$t_output = '';
								$t_any_found = false;
								if ( count( $t_filter['fixed_in_version'] ) == 0 ) {
									PRINT lang_get( 'any' );
								} else {
									$t_first_flag = true;
									foreach( $t_filter['fixed_in_version'] as $t_current ) {
										$t_this_string = '';
										if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
											$t_any_found = true;
										} else {
											$t_this_string = $t_current;
										}
										if ( $t_first_flag != true ) {
											$t_output = $t_output . '<br>';
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
		</tr>

		<tr <?php PRINT "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'per_page'; ?>"><?php PRINT lang_get( 'show' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'view_state'; ?>"><?php PRINT lang_get( 'view_status' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top">
				<a href="<?php PRINT $t_filters_url . 'highlight_changed'; ?>"><?php PRINT lang_get( 'changed' ) ?>:</a>
			</td>
			<td class="small-caption" valign="top" colspan="4">
				<a href="<?php PRINT $t_filters_url . 'do_filter_by_date'; ?>"><?php PRINT lang_get( 'use_date_filters' ) ?>:</a>
			</td>
			<?php 
				if ( $t_custom_cols > $t_filter_cols ) {
					echo '<td colspan="' . ($t_custom_cols - $t_filter_cols) . '">&nbsp;</td>';
				}
			?>
		</tr>
		<tr class="row-1">
			<td class="small-caption" valign="top">
				<?php PRINT $t_filter['per_page']; ?>
			</td>
			<td class="small-caption" valign="top">
				<?php
				if ( VS_PUBLIC == $t_filter['view_state'] ) {
					PRINT lang_get( 'public' );
				} else if ( VS_PRIVATE == $t_filter['view_state'] ) {
					PRINT lang_get( 'private' );
				} else {
					PRINT lang_get( 'any' );
				}
				?>
			</td>
			<td class="small-caption" valign="top">
				<?php PRINT $t_filter['highlight_changed']; ?>
			</td>
			<td class="small-caption" valign="top" colspan="4">
							<?php
							if ( 'on' == $t_filter['do_filter_by_date'] ) {
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
			<?php 
				if ( $t_custom_cols > $t_filter_cols ) {
					echo '<td colspan="' . ($t_custom_cols - $t_filter_cols) . '">&nbsp;</td>';
				}
			?>
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
						$t_fields .= '<a href="' . $t_filters_url . 'custom_field_' . $t_accessible_custom_fields_ids[$i] . '[]">';
						$t_fields .= string_display( lang_get_defaulted( $t_accessible_custom_fields_names[$i] ) );
						$t_fields .= '</a> </td> ';
					}
					$t_output = '';
					$t_any_found = false;

					$t_values .= '<td class="small-caption" valign="top"> ' ; 
					if ( !isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] ) ) {
						$t_values .= lang_get( 'any' );
					} else {
						$t_first_flag = true;
						foreach( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] as $t_current ) {
							$t_this_string = '';
							if ( ( $t_current == 'any' ) || ( is_blank( $t_current ) ) ) {
								$t_any_found = true;
							} else {
								$t_this_string = $t_current;
							}

							if ( $t_first_flag != true ) {
								$t_output = $t_output . '<br>';
							} else {
								$t_first_flag = false;
							}

							$t_output = $t_output . $t_this_string;
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
							$t_fields .= '<td colspan="' . ($t_filter_cols - $t_per_row ) . '">&nbsp</td> ';
							$t_values .= '<td colspan="' . ($t_filter_cols - $t_per_row) . '">&nbsp</td> ';
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
						$t_fields .= '<td colspan="' . ($t_per_row - $t_col_idx) . '">&nbsp</td> ';
						$t_values .= '<td colspan="' . ($t_per_row - $t_col_idx) . '">&nbsp</td> ';
					}

					if ( $t_filter_cols > $t_per_row ) {
						$t_fields .= '<td colspan="' . ($t_filter_cols - $t_per_row ) . '">&nbsp</td> ';
						$t_values .= '<td colspan="' . ($t_filter_cols - $t_per_row) . '">&nbsp</td> ';
					}

					$t_fields .= '</tr>' . "\n";
					$t_values .= '</tr>' . "\n";

					echo $t_fields;
					echo $t_values; 
				}
			}
		}
		} // expanded
		?>

		<tr>
			<td colspan="3">
				<?php
					collapse_icon( 'filter' );
					echo lang_get( 'search' );
				?>: 
				<input type="text" size="16" name="search" value="<?php PRINT htmlspecialchars( $t_filter['search'] ); ?>" />

				<input type="submit" name="filter" class="button" value="<?php PRINT lang_get( 'search' ) ?>" />
			</td>
			</form>

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
					<input type="submit" name="switch_to_query_button" class="button" value="<?php PRINT lang_get( 'use_query' ) ?>" />
					</form>
					<form method="post" name="open_queries" action="query_view_page.php">
					<input type="submit" name="switch_to_query_button" class="button" value="<?php PRINT lang_get( 'open_queries' ) ?>" />
					</form>
				<?php
			} else {
				?>
					<form method="get" name="reset_query" action="view_all_set.php">
					<input type="hidden" name="type" value="3" />
					<input type="hidden" name="source_query_id" value="-1" />
					<input type="submit" name="reset_query_button" class="button" value="<?php PRINT lang_get( 'reset_query' ) ?>" />
					</form>
				<?php
			}

			if ( access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) {
			?>
					<form method="post" name="save_query" action="query_store_page.php">
					<input type="submit" name="save_query_button" class="button" value="<?php PRINT lang_get( 'save_query' ) ?>" />
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
	function filter_db_get_filter( $p_filter_id ) {
		global $g_cache_filter_db_filters;
		$t_filters_table = config_get( 'mantis_filters_table' );
		$c_filter_id = db_prepare_int( $p_filter_id );

		if ( isset( $g_cache_filter_db_filters[$p_filter_id] ) ) {
			return $g_cache_filter_db_filters[$p_filter_id];
		}

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

			# check that the user has access to non current filters
			if ( ( ALL_PROJECTS <= $row['project_id'] ) && ( !is_blank( $row['name'] ) ) && ( !access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) ) {
				return null;
			}

			$g_cache_filter_db_filters[$p_filter_id] = $row['filter_string'];
			return $row['filter_string'];
		}

		return null;
	}

	function filter_db_get_project_current( $p_project_id ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$c_project_id 	= db_prepare_int( $p_project_id );
		$c_project_id 	= $c_project_id * -1;
		$t_user_id 		= auth_get_current_user_id();

		# we store current filters for each project with a special project index
		$query = "SELECT *
				  FROM $t_filters_table
				  WHERE user_id='$t_user_id'
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

	function filter_db_get_available_queries( ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$t_overall_query_arr = array();
		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

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
					AND filter_string!=''
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
		if ( !isset( $p_filter_arr['_view_type'] ) ) {
			$p_filter_arr['_view_type'] = gpc_get_string( 'view_type', 'simple' );
		}
		if ( !isset( $p_filter_arr['per_page'] ) ) {
			$p_filter_arr['per_page'] = gpc_get_int( 'per_page', config_get( 'default_limit_view' ) );
		}
		if ( !isset( $p_filter_arr['highlight_changed'] ) ) {
			$p_filter_arr['highlight_changed'] = config_get( 'default_show_changed' );
		}
		if ( !isset( $p_filter_arr['sort'] ) ) {
			$p_filter_arr['sort'] = "last_updated";
		}
		if ( !isset( $p_filter_arr['dir'] ) ) {
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
			$p_filter_arr['and_not_assigned'] = gpc_get_bool( 'and_not_assigned' );
		}
		if ( !isset( $p_filter_arr['do_filter_by_date'] ) ) {
			$p_filter_arr['do_filter_by_date'] = gpc_get_bool( 'do_filter_by_date' );
		}
		if ( !isset( $p_filter_arr['view_state'] ) ) {
			$p_filter_arr['view_state'] = gpc_get_int( 'view_state', '' );
		}

		$t_custom_fields 		= custom_field_get_ids();
		$f_custom_fields_data 	= array();
		if ( is_array( $t_custom_fields ) && ( sizeof( $t_custom_fields ) > 0 ) ) {
			foreach( $t_custom_fields as $t_cfid ) {
				if ( is_array( gpc_get( 'custom_field_' . $t_cfid, null ) ) ) {
					$f_custom_fields_data[$t_cfid] = gpc_get_string_array( 'custom_field_' . $t_cfid, 'any' );
				} else {
					$f_custom_fields_data[$t_cfid] = gpc_get_string( 'custom_field_' . $t_cfid, 'any' );
					$f_custom_fields_data[$t_cfid] = array( $f_custom_fields_data[$t_cfid] );
				}
			}
		}

		$t_multi_select_list = array( 'show_category' => 'string',
									  'show_severity' => 'int',
									  'show_status' => 'int',
									  'reporter_id' => 'int',
									  'handler_id' => 'string',
									  'show_resolution' => 'int',
									  'show_build' => 'string',
									  'show_version' => 'string',
									  'hide_status' => 'int',
									  'fixed_in_version' => 'string',
									  'user_monitor' => 'int' );
		foreach( $t_multi_select_list as $t_multi_field_name => $t_multi_field_type ) {
			if ( !isset( $p_filter_arr[$t_multi_field_name] ) ) {
				if ( 'hide_status' == $t_multi_field_name ) {
					$p_filter_arr[$t_multi_field_name] = array( config_get( 'hide_status_default' ) );
				} else if ( 'custom_fields' == $t_multi_field_name ) {
					$p_filter_arr[$t_multi_field_name] = array( $f_custom_fields_data );
				} else {
					$p_filter_arr[$t_multi_field_name] = array( "any" );
				}
			} else {
				if ( !is_array( $p_filter_arr[$t_multi_field_name] ) ) {
					$p_filter_arr[$t_multi_field_name] = array( $p_filter_arr[$t_multi_field_name] );
				}
				$t_checked_array = array();
				foreach ( $p_filter_arr[$t_multi_field_name] as $t_filter_value ) {
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
					$p_filter_arr['custom_fields'][$t_cfid] = array( "any" );
				} else {
					if ( !is_array( $p_filter_arr['custom_fields'][$t_cfid] ) ) {
						$p_filter_arr['custom_fields'][$t_cfid] = array( $p_filter_arr['custom_fields'][$t_cfid] );
					}
					$t_checked_array = array();
					foreach ( $p_filter_arr['custom_fields'][$t_cfid] as $t_filter_value ) {
						$t_checked_array[] = db_prepare_string( $t_filter_value );
					}
					$p_filter_arr['custom_fields'][$t_cfid] = $t_checked_array;
				}
			}
		}
		# all of our filter values are now guaranteed to be there, and correct.

		return $p_filter_arr;
	}
?>
