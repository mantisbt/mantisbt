<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: filter_api.php,v 1.5 2003-01-25 18:21:08 jlatour Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
	
	require_once( $t_core_dir . 'current_user_api.php' );

	###########################################################################
	# Filter API
	###########################################################################

	function filter_get_bug_rows( $p_page_number, $p_per_page = null, $p_page_count=null, $p_bug_count=null ) {
		$t_bug_table			= config_get( 'mantis_bug_table' );
		$t_bug_text_table		= config_get( 'mantis_bug_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_project_table		= config_get( 'mantis_project_table' );

		$t_filter = current_user_get_bug_filter();

		if ( false === $t_filter ) {
			return false; # signify a need to create a cookie
			#@@@ error instead?
		}

		$t_project_id	= helper_get_current_project();
		$t_user_id		= auth_get_current_user_id();

		$t_where_clauses = array( "$t_project_table.enabled = 1", "$t_project_table.id = $t_bug_table.project_id" );
		$t_select_clauses = array( "$t_bug_table.*" );
		$t_from_clauses = array( $t_bug_table, $t_project_table );
		$t_join_clauses = array();

		if ( 0 == $t_project_id ) { # all projects
			if ( ! current_user_is_administrator() ) {
				$t_projects = current_user_get_accessible_projects();

				if ( 0 == sizeof( $t_projects ) ) {
					return array();  # no accessible projects, return an empty array
				} else {
					$t_clauses = array();

					for ( $i=0 ; $i < sizeof( $t_projects ) ; $i++) {
						array_push( $t_clauses, "($t_bug_table.project_id='$t_projects[$i]')" );
					}

					array_push( $t_where_clauses, '('. implode( ' OR ', $t_clauses ) .')' );
				}
			}
		} else {
			check_access_to_project($t_project_id);

			array_push( $t_where_clauses, "($t_bug_table.project_id='$t_project_id')" );
		}

		# private bug selection
		if ( ! access_level_check_greater_or_equal( config_get( 'private_bug_threshold' ) ) ) {
			$t_public = PUBLIC;
			$t_private = PRIVATE;
			array_push( $t_where_clauses, "($t_bug_table.view_state='$t_public' OR ($t_bug_table.view_state='$t_private' AND $t_bug_table.reporter_id='$t_user_id'))" );
		}

		# reporter
		if ( 'any' != $t_filter['reporter_id'] ) {
			$c_reporter_id = db_prepare_int( $t_filter['reporter_id'] );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# handler
		if ( 'none' == $t_filter['handler_id'] ) {
			array_push( $t_where_clauses, "$t_bug_table.handler_id=0" );
		} else if ( 'any' != $t_filter['handler_id'] ) {
			$c_handler_id = db_prepare_int( $t_filter['handler_id'] );
			array_push( $t_where_clauses, "($t_bug_table.handler_id='$c_handler_id')" );
		}

		# hide closed
		if (( 'on' == $t_filter['hide_closed'] )&&( CLOSED != $t_filter['show_status'] )) {
			$t_closed = CLOSED;
			array_push( $t_where_clauses, "($t_bug_table.status<>'$t_closed')" );
		}

		# category
		if ( 'any' != $t_filter['show_category'] ) {
			$c_show_category = db_prepare_string( $t_filter['show_category'] );
			array_push( $t_where_clauses, "($t_bug_table.category='$c_show_category')" );
		}

		# severity
		if ( 'any' != $t_filter['show_severity'] ) {
			$c_show_severity = db_prepare_string( $t_filter['show_severity'] );
			array_push( $t_where_clauses, "($t_bug_table.severity='$c_show_severity')" );
		}

		# status
		if ( 'any' != $t_filter['show_status'] ) {
			$c_show_status = db_prepare_string( $t_filter['show_status'] );
			array_push( $t_where_clauses, "($t_bug_table.status='$c_show_status')" );
		}

		# Simple Text Search - Thnaks to Alan Knowles
		if ( !is_blank( $t_filter['search'] ) ) {
			$c_search = db_prepare_string( $t_filter['search'] );
			array_push( $t_where_clauses,
							"((summary LIKE '%$c_search%')
							 OR ($t_bug_text_table.description LIKE '%$c_search%')
							 OR ($t_bug_text_table.steps_to_reproduce LIKE '%$c_search%')
							 OR ($t_bug_text_table.additional_information LIKE '%$c_search%')
							 OR ($t_bug_table.id LIKE '%$c_search%')
							 OR ($t_bugnote_text_table.note LIKE '%$c_search%'))" );
			array_push( $t_where_clauses, "($t_bug_text_table.id = $t_bug_table.bug_text_id)" );

			array_push( $t_from_clauses, $t_bug_text_table );

			array_push( $t_join_clauses, "LEFT JOIN $t_bugnote_table ON $t_bugnote_table.bug_id = $t_bug_table.id" );

			array_push( $t_join_clauses, "LEFT JOIN $t_bugnote_text_table ON $t_bugnote_text_table.id = $t_bugnote_table.bugnote_text_id" );
		}

		$t_select	= implode( ', ', array_unique( $t_select_clauses ) );
		$t_from		= 'FROM ' . implode( ', ', array_unique( $t_from_clauses ) );
		$t_join		= implode( ' ', $t_join_clauses );
		if ( sizeof( $t_where_clauses ) > 0 ) {
			$t_where	= 'WHERE ' . implode( ' AND ', $t_where_clauses );
		} else {
			$t_where	= '';
		}

		# Get the total number of bugs that meet the criteria.
		$query = "SELECT COUNT( DISTINCT ( $t_bug_table.id ) ) as count $t_from $t_join $t_where";
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

		$query2  = "SELECT DISTINCT $t_select, UNIX_TIMESTAMP(last_updated) as last_updated
					$t_from
					$t_join
					$t_where";

		# Now add the rest of the criteria i.e. sorting, limit.
		$c_sort = db_prepare_string( $t_filter['sort'] );
		$c_dir	= db_prepare_string( $t_filter['dir'] );
		$query2 .= " ORDER BY '$c_sort' $c_dir";

		# Figure out the offset into the db query
		#
		# for example page number 1, per page 5:
		#     t_offset = 0
		# for example page number 2, per page 5:
		#     t_offset = 5
		$c_per_page = db_prepare_int( $p_per_page );
		$c_page_number = db_prepare_int( $p_page_number );
		$t_offset = ( ( $c_page_number - 1 ) * $c_per_page );

		$query2 .= " LIMIT $t_offset, $c_per_page";

		# perform query
		$result2 = db_query( $query2 );

		$row_count = db_num_rows( $result2 );

		$rows = array();

		for ( $i=0 ; $i < $row_count ; $i++ ) {
			array_push( $rows, db_fetch_array( $result2 ) );
		}

		return $rows;
	}

	# --------------------
	# return true if the filter cookie exists and is of the correct version,
	#  false otherwise
	function filter_is_cookie_valid() {
		$t_view_all_cookie = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );

		# check to see if the cookie does not exist
		if ( is_blank( $t_view_all_cookie ) ) {
			return false;
		}

		# check to see if new cookie is needed
		$t_setting_arr 			= explode( '#', $t_view_all_cookie );
		if ( $t_setting_arr[0] != config_get( 'cookie_version' ) ) {
			return false;
		}

		return true;
	}
?>
