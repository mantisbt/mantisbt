<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: filter_api.php,v 1.26 2004-04-08 03:31:37 prescience Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'current_user_api.php' );

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
	function filter_get_bug_rows( &$p_page_number, &$p_per_page, &$p_page_count, &$p_bug_count ) {
		$t_bug_table			= config_get( 'mantis_bug_table' );
		$t_bug_text_table		= config_get( 'mantis_bug_text_table' );
		$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
		$t_custom_field_string_table	= config_get( 'mantis_custom_field_string_table' );
		$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
		$t_project_table		= config_get( 'mantis_project_table' );
		$t_limit_reporters		= config_get( 'limit_reporters' );
		$t_report_bug_threshold		= config_get( 'report_bug_threshold' );

		$t_filter = current_user_get_bug_filter();

		if ( false === $t_filter ) {
			return false; # signify a need to create a cookie
			#@@@ error instead?
		}

		$t_project_id	= helper_get_current_project();
		$t_user_id		= auth_get_current_user_id();

		$t_where_clauses = array( "$t_project_table.enabled = 1", "$t_project_table.id = $t_bug_table.project_id" );
		$t_select_clauses = array( "$t_bug_table.*" );
		$t_join_clauses = array();

		if ( ALL_PROJECTS == $t_project_id ) {
			if ( ! current_user_is_administrator() ) {
				$t_projects = current_user_get_accessible_projects();

				if ( 0 == sizeof( $t_projects ) ) {
					return array();  # no accessible projects, return an empty array
				} else {
					$t_clauses = array();

					#@@@ use project_id IN (1,2,3,4) syntax if we can
					for ( $i=0 ; $i < sizeof( $t_projects ) ; $i++) {
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
		if ( ! access_has_project_level( config_get( 'private_bug_threshold' ) ) ) {
			$t_public = VS_PUBLIC;
			array_push( $t_where_clauses, "($t_bug_table.view_state='$t_public' OR $t_bug_table.reporter_id='$t_user_id')" );
		}

		# reporter
		if ( 'any' != $t_filter['reporter_id'] ) {
			$c_reporter_id = db_prepare_int( $t_filter['reporter_id'] );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# limit reporter
		if ( ( ON === $t_limit_reporters ) && ( current_user_get_access_level() <= $t_report_bug_threshold ) ) {
			$c_reporter_id = db_prepare_int( auth_get_current_user_id() );
			array_push( $t_where_clauses, "($t_bug_table.reporter_id='$c_reporter_id')" );
		}

		# handler
		if ( 'none' == $t_filter['handler_id'] ) {
			array_push( $t_where_clauses, "$t_bug_table.handler_id=0" );
		} else if ( 'any' != $t_filter['handler_id'] ) {
			$c_handler_id = db_prepare_int( $t_filter['handler_id'] );
			if ( 'on' != $t_filter['and_not_assigned'] ) {
				array_push( $t_where_clauses, "($t_bug_table.handler_id='$c_handler_id')" );
			} else {
				array_push( $t_where_clauses, "(($t_bug_table.handler_id='$c_handler_id') OR ($t_bug_table.handler_id=0))" );
			}
		}

		# hide closed
		if ( ( 'on' == $t_filter['hide_closed'] ) && ( CLOSED != $t_filter['show_status'] ) ) {
			$t_closed = CLOSED;
			array_push( $t_where_clauses, "($t_bug_table.status<>'$t_closed')" );
		}

		# hide resolved
		if ( ( 'on' == $t_filter['hide_resolved'] ) && ( RESOLVED != $t_filter['show_status'] ) ) {
			$t_resolved = RESOLVED;
			array_push( $t_where_clauses, "($t_bug_table.status<>'$t_resolved')" );
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

		# resolution
		if ( 'any' != $t_filter['show_resolution'] ) {
			$c_show_resolution = db_prepare_string( $t_filter['show_resolution'] );
			array_push( $t_where_clauses, "($t_bug_table.resolution='$c_show_resolution')" );
		}

		# product build
		if ( 'any' != $t_filter['show_build'] ) {
			$c_show_build = db_prepare_string( $t_filter['show_build'] );
			array_push( $t_where_clauses, "($t_bug_table.build='$c_show_build')" );
		}

		# product version
		if ( 'any' != $t_filter['show_version'] ) {
			$c_show_version = db_prepare_string( $t_filter['show_version'] );
			array_push( $t_where_clauses, "($t_bug_table.version='$c_show_version')" );
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

		if( ON == config_get( 'filter_by_custom_fields' ) ) {
			# custom field filtering
			$t_custom_fields = custom_field_get_ids();
			$t_first_time = true;
			$t_custom_where_clause = "";

			foreach( $t_custom_fields as $t_cfid ) {
				# Ignore all custom filters that are not set, or that are set to "" or "any"
				if ( isset( $t_filter['custom_fields'][$t_cfid] ) &&
					( 'any' != strtolower( $t_filter['custom_fields'][$t_cfid] ) ) &&
					( "" != trim( $t_filter['custom_fields'][$t_cfid] ) ) ) {

					if( $t_first_time ) {
						$t_first_time = false;
						$t_custom_where_clause = '(';
					} else {
						$t_custom_where_clause .= ' AND ';
					}

					$t_table_name = $t_custom_field_string_table . '_' . $t_cfid;
					array_push( $t_join_clauses, "LEFT JOIN $t_custom_field_string_table as $t_table_name ON $t_table_name.bug_id = $t_bug_table.id" );
					$t_custom_where_clause .= "(  $t_table_name.field_id = $t_cfid AND $t_table_name.value = '";
					$t_custom_where_clause .= db_prepare_string( trim( $t_filter['custom_fields'][$t_cfid] ) )  . "' )";
				}
			}
			if( $t_custom_where_clause != "" ) {
				array_push( $t_where_clauses, $t_custom_where_clause . ')' );
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
							 OR ($t_bug_table.id LIKE '%$c_search%')
							 OR ($t_bugnote_text_table.note LIKE '%$c_search%'))" );
			array_push( $t_where_clauses, "($t_bug_text_table.id = $t_bug_table.bug_text_id)" );

			$t_from_clauses = array( $t_bug_text_table, $t_project_table );

			array_push( $t_join_clauses, ",($t_bug_table LEFT JOIN $t_bugnote_table ON $t_bugnote_table.bug_id = $t_bug_table.id)" );

			array_push( $t_join_clauses, "LEFT JOIN $t_bugnote_text_table ON $t_bugnote_text_table.id = $t_bugnote_table.bugnote_text_id" );
		} else {
			$t_from_clauses = array( $t_bug_table, $t_project_table );
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

		if ( 'DESC' == $t_filter['dir'] ) {
			$c_dir = 'DESC';
		} else {
			$c_dir = 'ASC';
		}

		$query2 .= " ORDER BY $c_sort $c_dir";

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
	# Will print the filter selection area for both the bug list view screen, as well
	# as the bug list print screen. This function was an attempt to make it easier to
	# add new filters and rearrange them on screen for both pages.
	function filter_draw_selection_area( $p_page_number, $p_for_screen = true )
	{
		$t_filter = current_user_get_bug_filter();
		$t_project_id = helper_get_current_project();

		$t_sort = $t_filter['sort'];
		$t_dir = $t_filter['dir'];

		$t_tdclass = "small-caption";
		$t_trclass = "row-category2";
		$t_action  = "view_all_set.php?f=3";

		if ( $p_for_screen == false ) {
			$t_tdclass = "print";
			$t_trclass = "";
			$t_action  = "view_all_set.php";
		}
?>
		<br />
		<form method="post" name="filters" action="<?php echo $t_action; ?>">
		<input type="hidden" name="type" value="1" />
		<?php
			if ( $p_for_screen == false ) {
				print '<input type="hidden" name="print" value="1" />';
				print '<input type="hidden" name="offset" value="0" />';
			}
		?>
		<input type="hidden" name="sort" value="<?php echo $t_sort ?>" />
		<input type="hidden" name="dir" value="<?php echo $t_dir ?>" />
		<input type="hidden" name="page_number" value="<?php echo $p_page_number ?>" />
		<table class="width100" cellspacing="0">

		<?php
			$t_filter_cols = 8;
			$t_custom_cols = 1;
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

				if ( sizeof( $t_accessible_custom_fields_ids ) > 0 ) {
					$t_per_row = config_get( 'filter_custom_fields_per_row' );
					$t_num_custom_rows = ceil( sizeof( $t_accessible_custom_fields_ids ) / $t_per_row );
				}
			}

			$t_filters_url = config_get( 'path' ) . 'view_filters_page.php?for_screen=' . $p_for_screen . '&amp;target_field=';
		?>

		<input type="hidden" name="reporter_id" value="<?php echo $t_filter['reporter_id'] ?>">
		<input type="hidden" name="handler_id" value="<?php echo $t_filter['handler_id'] ?>">
		<input type="hidden" name="show_category" value="<?php echo $t_filter['show_category'] ?>">
		<input type="hidden" name="show_severity" value="<?php echo $t_filter['show_severity'] ?>">
		<input type="hidden" name="show_status" value="<?php echo $t_filter['show_status'] ?>">
		<input type="hidden" name="per_page" value="<?php echo $t_filter['per_page'] ?>">
		<input type="hidden" name="highlight_changed" value="<?php echo $t_filter['highlight_changed'] ?>">
		<input type="hidden" name="hide_resolved" value="<?php echo $t_filter['hide_resolved'] ?>">
		<input type="hidden" name="hide_closed" value="<?php echo $t_filter['hide_closed'] ?>">
		<input type="hidden" name="and_not_assigned" value="<?php echo $t_filter['and_not_assigned'] ?>">
		<input type="hidden" name="show_build" value="<?php echo $t_filter['show_build'] ?>">
		<input type="hidden" name="show_resolution" value="<?php echo $t_filter['show_resolution'] ?>">
		<input type="hidden" name="show_version" value="<?php echo $t_filter['show_version'] ?>">
		<input type="hidden" name="start_month" value="<?php echo $t_filter['start_month'] ?>">
		<input type="hidden" name="start_day" value="<?php echo $t_filter['start_day'] ?>">
		<input type="hidden" name="start_year" value="<?php echo $t_filter['start_year'] ?>">
		<input type="hidden" name="end_month" value="<?php echo $t_filter['end_month'] ?>">
		<input type="hidden" name="end_day" value="<?php echo $t_filter['end_day'] ?>">
		<input type="hidden" name="end_year" value="<?php echo $t_filter['end_year'] ?>">
		<input type="hidden" name="do_filter_by_date" value="<?php echo $t_filter['do_filter_by_date'] ?>">
		<?php
			for ( $i = 0; $i < sizeof( $t_accessible_custom_fields_ids ); $i++ ) {
				print '<input type="hidden" name="custom_field_';
				print $t_accessible_custom_fields_ids[$i];
				print '" value="';
				if ( ! isset( $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] ) ) {
					$t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]] = 'any';
				}
				print $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$i]];
				print "\">\n";
			}
		?>
		<tr <?php echo "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'reporter_id'; ?>"><?php echo lang_get( 'reporter' ) ?>:</a>
				<?php
					if ( $t_filter['reporter_id'] == 0 ) {
						echo lang_get( 'any' );
					} else {
						echo user_get_field( $t_filter['reporter_id'], 'username' );
					}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'handler_id'; ?>"><?php echo lang_get( 'assigned_to' ) ?>:</a>
				<?php
					if ( $t_filter['handler_id'] == 0 ) {
						echo lang_get( 'any' );
					} else {
						echo user_get_field( $t_filter['handler_id'], 'username' );
					}
					if ( 'on' == $t_filter['and_not_assigned'] ) {
						echo ' (' . lang_get( 'or_unassigned' ) . ')';
					}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 2 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'show_category'; ?>"><?php echo lang_get( 'category' ) ?>:</a>
				<?php
					if ( $t_filter['show_category'] == '' ) {
						echo lang_get( 'any' );
					} else {
						echo $t_filter['show_category'];
					}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'show_severity'; ?>"><?php echo lang_get( 'severity' ) ?>:</a>
				<?php
					if ( ( $t_filter['show_severity'] == 'any' ) || ( $t_filter['show_severity'] == '' ) ) {
						echo lang_get( 'any' );
					} else {
						print get_enum_element( 'severity', $t_filter['show_severity'] );
					}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'show_status'; ?>"><?php echo lang_get( 'status' ) ?>:</a>
				<?php
					if ( ( $t_filter['show_status'] == 'any' ) || ( $t_filter['show_status'] == '' ) ) {
						echo lang_get( 'any' );
					} else {
						print get_enum_element( 'status', $t_filter['show_status'] );
					}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'show_resolution'; ?>"><?php echo lang_get( 'resolution' ) ?>:</a>
				<?php
					if ( ( $t_filter['show_resolution'] == 'any' ) || ( $t_filter['show_resolution'] == '' ) ) {
						echo lang_get( 'any' );
					} else {
						print get_enum_element( 'resolution', $t_filter['show_resolution'] );
					}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'hide_resolved'; ?>"><?php echo lang_get( 'hide_resolved' ) ?>:</a>
				<?php
					if ( 'on' == $t_filter['hide_resolved'] ) {
						echo lang_get( 'yes' );
					} else {
						echo lang_get( 'no' );
					}
				?>
			</td>
		</tr>

		<tr <?php echo "class=\"" . $t_trclass . "\""; ?>>
			<td class="small-caption" colspan="<?php echo ( 2 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'do_filter_by_date'; ?>"><?php echo lang_get( 'use_date_filters' ) ?>:</a>
				<?php
				if ( 'on' == $t_filter['do_filter_by_date'] ) {
					$t_chars = preg_split( '//', config_get( 'short_date_format' ), -1, PREG_SPLIT_NO_EMPTY );
					$t_time = mktime( 0, 0, 0, $t_filter['start_month'], $t_filter['start_day'], $t_filter['start_year'] );
					foreach( $t_chars as $t_char ) {
						if ( strcasecmp( $t_char, "M" ) == 0 ) {
							print ' ';
							print date( 'F', $t_time );
						}
						if ( strcasecmp( $t_char, "D" ) == 0 ) {
							print ' ';
							print date( 'd', $t_time );
						}
						if ( strcasecmp( $t_char, "Y" ) == 0 ) {
							print ' ';
							print date( 'Y', $t_time );
						}
					}

					echo ' - ';

					$t_time = mktime( 0, 0, 0, $t_filter['end_month'], $t_filter['end_day'], $t_filter['end_year'] );
					foreach( $t_chars as $t_char ) {
						if ( strcasecmp( $t_char, "M" ) == 0 ) {
							print ' ';
							print date( 'F', $t_time );
						}
						if ( strcasecmp( $t_char, "D" ) == 0 ) {
							print ' ';
							print date( 'd', $t_time );
						}
						if ( strcasecmp( $t_char, "Y" ) == 0 ) {
							print ' ';
							print date( 'Y', $t_time );
						}
					}
				} else {
					echo lang_get( 'no' );
				}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'show_build'; ?>"><?php echo lang_get( 'product_build' ) ?>:</a>
				<?php
				if ( 'any' == $t_filter['show_build'] ) {
					echo lang_get( 'any' );
				} else {
					echo $t_filter['show_build'];
				}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'show_version'; ?>"><?php echo lang_get( 'product_version' ) ?>:</a>
				<?php
				if ( 'any' == $t_filter['show_version'] ) {
					echo lang_get( 'any' );
				} else {
					echo $t_filter['show_version'];
				}
				?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'per_page'; ?>"><?php echo lang_get( 'show' ) ?>:</a>
				<?php echo $t_filter['per_page']; ?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'highlight_changed'; ?>"><?php echo lang_get( 'changed' ) ?>:</a>
				<?php echo $t_filter['highlight_changed']; ?>
			</td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>"></td>
			<td class="small-caption" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<a href="<?php print $t_filters_url . 'hide_closed'; ?>"><?php echo lang_get( 'hide_closed' ) ?>:</a>
				<?php
					if ( 'on' == $t_filter['hide_closed'] ) {
						echo lang_get( 'yes' );
					} else {
						echo lang_get( 'no' );
					}
				?>
			</td>
		</tr>
		<?php
		if ( ON == config_get( 'filter_by_custom_fields' ) ) {
		?>
			<?php # -- Custom Field Searching -- ?>
			<?php
			if ( sizeof( $t_accessible_custom_fields_ids ) > 0 ) {
				$t_per_row = config_get( 'filter_custom_fields_per_row' );
				$t_num_rows = ceil( sizeof( $t_accessible_custom_fields_ids ) / $t_per_row );
				$t_base = 0;

				for ( $i = 0; $i < $t_num_rows; $i++ ) {
					?>
					<tr <?php echo "class=\"" . $t_trclass . "\""; ?>>
					<?php
					for( $j = 0; $j < $t_per_row; $j++ ) {
						echo '<td class="small-caption" colspan="' . ( 1 * $t_filter_cols ) . '">';
						if ( isset( $t_accessible_custom_fields_names[$t_base + $j] ) ) {
							echo "<a href=\"" . $t_filters_url . 'custom_field_' . $t_accessible_custom_fields_ids[$t_base + $j] . "\">";
							echo $t_accessible_custom_fields_names[$t_base + $j] . ":</a> ";
							echo $t_filter['custom_fields'][$t_accessible_custom_fields_ids[$t_base + $j]];
						} else {
							echo '&nbsp;';
						}
						echo '</td>';
					}
					?>
					</tr>
					<?php
					$t_base += $t_per_row;
				}
			}
		}
		?>

		<tr>
			<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>"><?php echo lang_get( 'search' ) ?>: <input type="text" size="16" name="search" value="<?php echo $t_filter['search']; ?>" /></td>
			<!-- SUBMIT button -->
			<td class="left" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
				<input type="submit" name="filter" value="<?php echo lang_get( 'search' ) ?>" />
			</td>
			</form>
			<td colspan="<?php echo ( 2 * $t_custom_cols ); ?>"></td>

			<?php
			$t_stored_queries_arr = array();
			$t_stored_queries_arr = filter_db_get_available_queries();

			if ( sizeof( $t_stored_queries_arr ) > 0 ) {
				?>
				<td class="right" colspan="<?php echo ( 3 * $t_custom_cols ); ?>">
					<form method="get" name="list_queries" action="view_all_set.php">
					<input type="hidden" name="type" value="3" />
					<select name="source_query_id">
					<option value="-1"><?php echo '[' . lang_get( 'reset_query' ) . ']' ?></option>
					<option value="-1"></option>
					<?php
					foreach( $t_stored_queries_arr as $t_query_id => $t_query_name ) {
						print '<option value="' . $t_query_id . '">' . $t_query_name . '</option>';
					}
					?>
					</select>
					<input type="submit" name="switch_to_query_button" value="<?php echo lang_get( 'use_query' ) ?>" />
					</form>
					<form method="post" name="open_queries" action="query_view_page.php">
					<input type="submit" name="switch_to_query_button" value="<?php echo lang_get( 'open_queries' ) ?>" />
					</form>
				</td>
				<?php
			} else {
				?>
				<td class="right" colspan="<?php echo ( 3 * $t_custom_cols ); ?>">
					<form method="get" name="reset_query" action="view_all_set.php">
					<input type="hidden" name="type" value="3" />
					<input type="hidden" name="source_query_id" value="-1" />
					<input type="submit" name="reset_query_button" value="<?php echo lang_get( 'reset_query' ) ?>" />
					</form>
				</td>
				<?php
			}

			if ( access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) {
			?>
				<td class="left" colspan="<?php echo ( 1 * $t_custom_cols ); ?>">
					<form method="post" name="save_query" action="query_store_page.php">
					<input type="submit" name="save_query_button" value="<?php echo lang_get( 'save_query' ) ?>" />
					</form>
				</td>
			<?php
			} else {
			?>
				<td colspan="<?php echo ( 1 * $t_custom_cols ); ?>">&nbsp;</td>
			<?php
			}
			?>

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
		if ( ( -1 != $c_project_id ) && ( ! access_has_project_level( config_get( 'stored_query_create_threshold' ) ) ) ) {
			return -1;
		}

		# ensure that we're not making this filter public if we're not allowed
		if ( ! access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
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
			if ( ( -1 != $row['project_id'] ) && ( ! access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) ) {
				return null;
			}

			$g_cache_filter_db_filters[$p_filter_id] = $row['filter_string'];
			return $row['filter_string'];
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

		if ( ! filter_db_can_delete_filter( $c_filter_id ) ) {
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

	function filter_db_get_available_queries( ) {
		$t_filters_table = config_get( 'mantis_filters_table' );
		$t_overall_query_arr = array();
		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		# If the user doesn't have access rights to stored queries, just return
		if ( ! access_has_project_level( config_get( 'stored_query_use_threshold' ) ) ) {
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
?>
