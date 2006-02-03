<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: summary_api.php,v 1.42.6.1 2006-01-08 14:55:50 thraxisp Exp $
	# --------------------------------------------------------

	### Summary printing API ###

	# --------------------
	function summary_helper_print_row( $p_label, $p_open, $p_resolved, $p_closed, $p_total ) {
		printf( '<tr %s>', helper_alternate_class() );
		printf( '<td width="50%%">%s</td>', string_display( $p_label ) );
		printf( '<td width="12%%" class="right">%s</td>', $p_open );
		printf( '<td width="12%%" class="right">%s</td>', $p_resolved );
		printf( '<td width="12%%" class="right">%s</td>', $p_closed );
		printf( '<td width="12%%" class="right">%s</td>', $p_total );
		print( '</tr>' );
	}
	# --------------------
	# Used in summary reports
	# Given the enum string this function prints out the summary
	# for each enum setting
	# The enum field name is passed in through $p_enum
	function summary_print_by_enum( $p_enum_string, $p_enum ) {
		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		$t_project_filter = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $t_project_filter ) {
			return;
		}
		
		$t_filter_prefix = config_get( 'bug_count_hyperlink_prefix' );
		$t_arr = explode_enum_string( $p_enum_string );
		$enum_count = count( $t_arr );

		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_status_query = ( 'status' == $p_enum ) ? '' : ' ,status ';		  
		$query = "SELECT COUNT(id) as count, $p_enum $t_status_query 
				FROM $t_mantis_bug_table
				WHERE $t_project_filter
				GROUP BY $p_enum $t_status_query 
				ORDER BY $p_enum $t_status_query";
		$result = db_query( $query );

		$t_last_value = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = config_get( 'bug_resolved_status_threshold' );
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			if ( ( $row[$p_enum] != $t_last_value ) &&
				( -1 != $t_last_value ) ) {
				# Build up the hyperlinks to bug views
				$t_bug_link = '';
				switch ( $p_enum ) {
					case 'status':
						$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_status=' . $t_last_value;
						break;
					case 'severity':
						$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_severity=' . $t_last_value;
						break;
					case 'resolution':
						$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_resolution=' . $t_last_value;
						break;
					case 'priority':
						$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_priority=' . $t_last_value;
						break;
				}

				if ( !is_blank( $t_bug_link ) ) {
					if ( 0 < $t_bugs_open ) {
						$t_bugs_open = $t_bug_link . '&amp;hide_status=' . RESOLVED . '">' . $t_bugs_open . '</a>';
					} else {
						if ( ( 'status' == $p_enum ) && ( $t_last_value >= $t_resolved_val ) ) {
							$t_bugs_open = '-';
						}
					}
					if ( 0 < $t_bugs_resolved ) {
						$t_bugs_resolved = $t_bug_link . '&amp;show_status=' . RESOLVED . '&amp;hide_status=' . CLOSED . '">' . $t_bugs_resolved . '</a>';
					} else {
						if ( ( 'status' == $p_enum ) && ( ( $t_last_value < $t_resolved_val ) || ( $t_last_value >= $t_closed_val ) ) ) {
							$t_bugs_resolved = '-';
						}
					}
					if ( 0 < $t_bugs_closed ) {
						$t_bugs_closed = $t_bug_link . '&amp;show_status=' . CLOSED . '&amp;hide_status=">' . $t_bugs_closed . '</a>';
					} else {
						if ( ( 'status' == $p_enum ) && ( $t_last_value < $t_closed_val ) ){
							$t_bugs_closed = '-';
						}
					}
					if ( 0 < $t_bugs_total ) {
						$t_bugs_total = $t_bug_link . '&amp;hide_status=">' . $t_bugs_total . '</a>';
					}
				}

				summary_helper_print_row( get_enum_element( $p_enum, $t_last_value), $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

				$t_bugs_open		= 0;
				$t_bugs_resolved	= 0;
				$t_bugs_closed		= 0;
				$t_bugs_total		= 0;
			}

			$t_bugs_total += $row['count'];
			if ( $t_closed_val <= $row['status'] ) {
				$t_bugs_closed += $row['count'];
			} else if ( $t_resolved_val <= $row['status'] ) {
				$t_bugs_resolved += $row['count'];
			} else {
				$t_bugs_open += $row['count'];
			}
			$t_last_value = $row[$p_enum];
		}

		if ( 0 < $t_bugs_total ) {
			# Build up the hyperlinks to bug views
			$t_bug_link = '';
			switch ( $p_enum ) {
				case 'status':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_status=' . $t_last_value;
					break;
				case 'severity':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_severity=' . $t_last_value;
					break;
				case 'resolution':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_resolution=' . $t_last_value;
					break;
				case 'priority':
					$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;show_priority=' . $t_last_value;
					break;
			}

			if ( !is_blank( $t_bug_link ) ) {
				if ( 0 < $t_bugs_open ) {
					$t_bugs_open = $t_bug_link . '&amp;hide_status=' . RESOLVED . '">' . $t_bugs_open . '</a>';
				} else {
					if ( ( 'status' == $p_enum ) && ( $t_last_value >= $t_resolved_val ) ) {
						$t_bugs_open = '-';
					}
				}
				if ( 0 < $t_bugs_resolved ) {
					$t_bugs_resolved = $t_bug_link . '&amp;show_status=' . RESOLVED . '&amp;hide_status=' . CLOSED . '">' . $t_bugs_resolved . '</a>';
					} else {
						if ( ( 'status' == $p_enum ) && ( ( $t_last_value < $t_resolved_val ) || ( $t_last_value >= $t_closed_val ) ) ) {
							$t_bugs_resolved = '-';
					}
				}
				if ( 0 < $t_bugs_closed ) {
					$t_bugs_closed = $t_bug_link . '&amp;show_status=' . CLOSED . '&amp;hide_status=">' . $t_bugs_closed . '</a>';
					} else {
						if ( ( 'status' == $p_enum ) && ( $t_last_value < $t_closed_val ) ){
							$t_bugs_closed = '-';
						}
				}
				if ( 0 < $t_bugs_total ) {
					$t_bugs_total = $t_bug_link . '&amp;hide_status=">' . $t_bugs_total . '</a>';
				}
			}

			summary_helper_print_row( get_enum_element( $p_enum, $t_last_value), $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
		}
	}
	# --------------------
	# prints the bugs submitted in the last X days (default is 1 day) for the
	# current project
	function summary_bug_count_by_date( $p_time_length=1 ) {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );

		$c_time_length = (int)$p_time_length;

		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		$specific_where = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $specific_where ) {
			return;
		}

		$query = "SELECT COUNT(*)
				FROM $t_mantis_bug_table
				WHERE ".db_helper_compare_days(db_now(),"date_submitted","<= '$c_time_length'")." AND $specific_where";
		$result = db_query( $query );
		return db_result( $result, 0 );
	}
	# --------------------
	# This function shows the number of bugs submitted in the last X days
	# An array of integers representing days is passed in
	function summary_print_by_date( $p_date_array ) {
		$arr_count = count( $p_date_array );
		for ($i=0;$i<$arr_count;$i++) {
			$t_enum_count = summary_bug_count_by_date( $p_date_array[$i] );

			$t_start_date = mktime( 0, 0, 0, date( 'm' ), ( date( 'd' ) - $p_date_array[$i] ), date( 'Y' ) );
			$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;do_filter_by_date=on&amp;start_year=' . date( 'Y', $t_start_date ) . '&amp;start_month=' . date( 'm', $t_start_date ) . '&amp;start_day=' . date( 'd', $t_start_date ) . '&amp;hide_status=">';

			printf( '<tr %s>', helper_alternate_class() );
			printf( '<td width="50%%">%s</td>', $p_date_array[$i] );
			if ( 0 < $t_enum_count ) {
				printf( '<td class="right">%s</td>', $t_bug_link . $t_enum_count . '</a>' );
			} else {
				printf( '<td class="right">%s</td>', $t_enum_count );
			}
			print( '</tr>' );
		} # end for
	}
	# --------------------
	# print bug counts by assigned to each developer
	function summary_print_by_developer() {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );

		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		$specific_where = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $specific_where ) {
			return;
		}

		$query = "SELECT COUNT(id) as count, handler_id, status
				FROM $t_mantis_bug_table
				WHERE handler_id>0 AND $specific_where
				GROUP BY handler_id, status
				ORDER BY handler_id, status";
		$result = db_query( $query );

		$t_last_handler = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( ($v_handler_id != $t_last_handler) && (-1 != $t_last_handler) ) {
				$t_user = user_get_name( $t_last_handler );

				$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;handler_id=' . $t_last_handler;
				if ( 0 < $t_bugs_open ) {
					$t_bugs_open = $t_bug_link . '&amp;hide_status=' . RESOLVED . '">' . $t_bugs_open . '</a>';
				}
				if ( 0 < $t_bugs_resolved ) {
					$t_bugs_resolved = $t_bug_link . '&amp;show_status=' . RESOLVED . '&amp;hide_status=' . CLOSED .'">' . $t_bugs_resolved . '</a>';
				}
				if ( 0 < $t_bugs_closed ) {
					$t_bugs_closed = $t_bug_link . '&amp;show_status=' . CLOSED . '&amp;hide_status=">' . $t_bugs_closed . '</a>';
				}
				if ( 0 < $t_bugs_total ) {
					$t_bugs_total = $t_bug_link . '&amp;hide_status=">' . $t_bugs_total . '</a>';
				}

				summary_helper_print_row( $t_user, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

				$t_bugs_open = 0;
				$t_bugs_resolved = 0;
				$t_bugs_closed = 0;
				$t_bugs_total = 0;
			}

			$t_bugs_total += $v_count;
			if ( $t_closed_val <= $row['status'] ) {
				$t_bugs_closed += $v_count;
			} else if ( $t_resolved_val <= $row['status'] ) {
				$t_bugs_resolved += $v_count;
			} else {
				$t_bugs_open += $v_count;
			}
			$t_last_handler = $v_handler_id;
		}

		if ( 0 < $t_bugs_total ) {
			$t_user = user_get_name( $t_last_handler );

			$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;handler_id=' . $t_last_handler;
			if ( 0 < $t_bugs_open ) {
				$t_bugs_open = $t_bug_link . '&amp;hide_status=' . RESOLVED . '">' . $t_bugs_open . '</a>';
			}
			if ( 0 < $t_bugs_resolved ) {
				$t_bugs_resolved = $t_bug_link . '&amp;show_status=' . RESOLVED . '&amp;hide_status=' . CLOSED . '">' . $t_bugs_resolved . '</a>';
			}
			if ( 0 < $t_bugs_closed ) {
				$t_bugs_closed = $t_bug_link . '&amp;show_status=' . CLOSED . '&amp;hide_status=">' . $t_bugs_closed . '</a>';
			}
			if ( 0 < $t_bugs_total ) {
				$t_bugs_total = $t_bug_link . '&amp;hide_status=">' . $t_bugs_total . '</a>';
			}

			summary_helper_print_row( $t_user, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
		}
	}
	# --------------------
	# print bug counts by reporter id
	function summary_print_by_reporter() {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );
		$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		$specific_where = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $specific_where ) {
			return;
		}

		$query = "SELECT reporter_id, COUNT(*) as num
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY reporter_id
				ORDER BY num DESC";
		$result = db_query( $query, $t_reporter_summary_limit );

		while ( $row = db_fetch_array( $result ) ) {
			$v_reporter_id = $row['reporter_id'];
			$query = "SELECT COUNT(id) as count, status FROM $t_mantis_bug_table
					WHERE reporter_id=$v_reporter_id
					AND $specific_where
					GROUP BY status
					ORDER BY status";
			$result2 = db_query( $query );

			$last_reporter = -1;
			$t_bugs_open = 0;
			$t_bugs_resolved = 0;
			$t_bugs_closed = 0;
			$t_bugs_total = 0;

			$t_resolved_val = RESOLVED;
			$t_closed_val = CLOSED;

			while ( $row2 = db_fetch_array( $result2 ) ) {
                $t_bugs_total += $row2['count'];
                if ( $t_closed_val <= $row2['status'] ) {
                    $t_bugs_closed += $row2['count'];
                } else if ( $t_resolved_val <= $row2['status'] ) {
                    $t_bugs_resolved += $row2['count'];
                } else {
                    $t_bugs_open += $row2['count'];
                }
			}

			if ( 0 < $t_bugs_total ) {
				$t_user = user_get_name( $v_reporter_id );

				$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;reporter_id=' . $v_reporter_id;
				if ( 0 < $t_bugs_open ) {
					$t_bugs_open = $t_bug_link . '&amp;hide_status=' . RESOLVED . '">' . $t_bugs_open . '</a>';
				}
				if ( 0 < $t_bugs_resolved ) {
					$t_bugs_resolved = $t_bug_link . '&amp;show_status=' . RESOLVED . '&amp;hide_status=' . CLOSED . '">' . $t_bugs_resolved . '</a>';
				}
				if ( 0 < $t_bugs_closed ) {
					$t_bugs_closed = $t_bug_link . '&amp;show_status=' . CLOSED . '&amp;hide_status=">' . $t_bugs_closed . '</a>';
				}
				if ( 0 < $t_bugs_total ) {
					$t_bugs_total = $t_bug_link . '&amp;hide_status=">' . $t_bugs_total . '</a>';
				}

				summary_helper_print_row( $t_user, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
			}
		}
	}
	# --------------------
	# print a bug count per category
	function summary_print_by_category() {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_project_table = config_get( 'mantis_project_table' );
		$t_summary_category_include_project = config_get( 'summary_category_include_project' );

		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		$specific_where = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $specific_where ) {
			return;
		}
		$t_project_query = ( ON == $t_summary_category_include_project ) ? 'project_id, ' : '';

		$query = "SELECT COUNT(id) as count, $t_project_query category, status
				FROM $t_mantis_bug_table
				WHERE category>'' AND $specific_where
				GROUP BY $t_project_query category, status
				ORDER BY $t_project_query category, status";

		$result = db_query( $query );

		$last_category = -1;
		$last_project = -1;
		$t_bugs_open = 0;
		$t_bugs_resolved = 0;
		$t_bugs_closed = 0;
		$t_bugs_total = 0;

		$t_resolved_val = RESOLVED;
		$t_closed_val = CLOSED;

		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );

			if ( ( $v_category != $last_category ) && ( $last_category != -1 ) ) {
				$label = $last_category;
				if ( ( ON == $t_summary_category_include_project ) && ( ALL_PROJECTS == $t_project_id ) ) {
					$label = sprintf( '[%s] %s', project_get_name( $last_project ), $label );
				}

				$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;show_category=' . $last_category;
				if ( 0 < $t_bugs_open ) {
					$t_bugs_open = $t_bug_link . '&amp;hide_status=' . RESOLVED . '">' . $t_bugs_open . '</a>';
				}
				if ( 0 < $t_bugs_resolved ) {
					$t_bugs_resolved = $t_bug_link . '&amp;show_status=' . RESOLVED . '&amp;hide_status=' . CLOSED . '">' . $t_bugs_resolved . '</a>';
				}
				if ( 0 < $t_bugs_closed ) {
					$t_bugs_closed = $t_bug_link . '&amp;show_status=' . CLOSED . '&amp;hide_status=">' . $t_bugs_closed . '</a>';
				}
				if ( 0 < $t_bugs_total ) {
					$t_bugs_total = $t_bug_link . '&amp;hide_status=">' . $t_bugs_total . '</a>';
				}

				summary_helper_print_row( $label, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

				$t_bugs_open = 0;
				$t_bugs_resolved = 0;
				$t_bugs_closed = 0;
				$t_bugs_total = 0;
			}

            $t_bugs_total += $row['count'];
            if ( $t_closed_val <= $row['status'] ) {
                $t_bugs_closed += $row['count'];
            } else if ( $t_resolved_val <= $row['status'] ) {
                $t_bugs_resolved += $row['count'];
            } else {
                $t_bugs_open += $row['count'];
            }

			$last_category = $v_category;
			if ( ( ON == $t_summary_category_include_project ) && ( ALL_PROJECTS == $t_project_id ) ) {
                $last_project = $v_project_id;
            }
		}

		if ( 0 < $t_bugs_total ) {
			$label = $last_category;
			if ( ( ON == $t_summary_category_include_project ) && ( ALL_PROJECTS == $t_project_id ) ) {
				$label = sprintf( '[%s] %s', project_get_name( $last_project ), $label );
			}

			$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;show_category=' . $last_category;
			if ( !is_blank( $t_bug_link ) ) {
				if ( 0 < $t_bugs_open ) {
					$t_bugs_open = $t_bug_link . '&amp;hide_status=' . RESOLVED . '">' . $t_bugs_open . '</a>';
				}
				if ( 0 < $t_bugs_resolved ) {
					$t_bugs_resolved = $t_bug_link . '&amp;show_status=' . RESOLVED . '&amp;hide_status=' . CLOSED . '">' . $t_bugs_resolved . '</a>';
				}
				if ( 0 < $t_bugs_closed ) {
					$t_bugs_closed = $t_bug_link . '&amp;show_status=' . CLOSED . '&amp;hide_status=">' . $t_bugs_closed . '</a>';
				}
				if ( 0 < $t_bugs_total ) {
					$t_bugs_total = $t_bug_link . '&amp;hide_status=">' . $t_bugs_total . '</a>';
				}
			}

			summary_helper_print_row( $label, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );
		}
	}
	# --------------------
	# print bug counts by project
	function summary_print_by_project( $p_projects = null, $p_level = 0, $p_cache = null ) {
		$t_mantis_bug_table 	= config_get( 'mantis_bug_table' );
		$t_mantis_project_table = config_get( 'mantis_project_table' );

		$t_project_id = helper_get_current_project();

		if ( null == $p_projects ) {
			if ( ALL_PROJECTS == $t_project_id ) {
				$p_projects = current_user_get_accessible_projects();
			} else {
				$p_projects = Array( $t_project_id );
			}
		}

		# Retrieve statistics one time to improve performance.
		if ( null === $p_cache ) {
			$query = "SELECT project_id, status, COUNT( status ) AS count
					FROM $t_mantis_bug_table
					GROUP BY project_id, status";

			$result = db_query( $query );
			$p_cache = Array();

			$t_resolved_val = RESOLVED;
			$t_closed_val = CLOSED;

			while ( $row = db_fetch_array( $result ) ) {
				extract( $row, EXTR_PREFIX_ALL, 'v' );
                if ( $t_closed_val <= $v_status ) {
                    if ( isset( $p_cache[ $v_project_id ][ 'closed'   ] ) ) {
                        $p_cache[ $v_project_id ][ 'closed'   ]  += $v_count;
                    } else {
                        $p_cache[ $v_project_id ][ 'closed'   ]  = $v_count;
                    }
                } else if ( $t_resolved_val <= $v_status ) {
                    if ( isset( $p_cache[ $v_project_id ][ 'resolved' ] ) ) {
                        $p_cache[ $v_project_id ][ 'resolved' ]  += $v_count;
                    } else {
                        $p_cache[ $v_project_id ][ 'resolved' ]  = $v_count;
                    }
                } else {
                    if ( isset( $p_cache[ $v_project_id ][ 'open'     ] ) ) {
                        $p_cache[ $v_project_id ][ 'open'     ]  += $v_count;
                    } else {
                        $p_cache[ $v_project_id ][ 'open'     ]  = $v_count;
                    }
                }
			}
		}

		foreach ( $p_projects as $t_project ) {
			$t_name = str_repeat( "» ", $p_level ) . project_get_name( $t_project );

			$t_pdata = isset( $p_cache[ $t_project ] ) ? $p_cache[ $t_project ]
			             : array( 'open' => 0, 'resolved' => 0, 'closed' => 0 );

			$t_bugs_open     = isset( $t_pdata['open'] ) ? $t_pdata['open'] : 0;
			$t_bugs_resolved = isset( $t_pdata['resolved'] ) ? $t_pdata['resolved'] : 0;
			$t_bugs_closed   = isset( $t_pdata['closed'] ) ? $t_pdata['closed'] : 0;
			$t_bugs_total    = $t_bugs_open + $t_bugs_resolved + $t_bugs_closed;

			summary_helper_print_row( $t_name, $t_bugs_open, $t_bugs_resolved, $t_bugs_closed, $t_bugs_total );

			$t_subprojects = current_user_get_accessible_subprojects( $t_project );

			if ( count( $t_subprojects ) > 0 ) {
				summary_print_by_project( $t_subprojects, $p_level + 1, $p_cache );
			}
		}
	}
	# --------------------
	# Print developer / resolution report
	function summary_print_developer_resolution( $p_resolution_enum_string ) {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );

		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		# Organise an array of resolution values to be used later
		$t_res_arr = explode_enum_string( $p_resolution_enum_string );
		$enum_res_count = count( $t_res_arr );
		$c_res_s = array();
		for ( $i = 0; $i < $enum_res_count; $i++ ) {
			$t_res_s = explode_enum_arr( $t_res_arr[$i] );
			$c_res_s[$i] = db_prepare_string( $t_res_s[0] );
		}

		$specific_where = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $specific_where ) {
			return;
		}

		$specific_where .= ' AND handler_id > 0';
		# Get all of the bugs and split them up into an array
		$query = "SELECT COUNT(id) as count, handler_id, resolution
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY handler_id, resolution
				ORDER BY handler_id, resolution";
		$result = db_query( $query );

		$t_handler_res_arr = array();
		$t_arr = db_fetch_array( $result );
		while ( $t_arr ) {
			if ( !isset( $t_handler_res_arr[$t_arr['handler_id']] ) ) {
				$t_handler_res_arr[$t_arr['handler_id']] = array();
				$t_handler_res_arr[$t_arr['handler_id']]['total'] = 0;
			}
			if ( !isset( $t_handler_res_arr[$t_arr['handler_id']][$t_arr['resolution']] ) ) {
				$t_handler_res_arr[$t_arr['handler_id']][$t_arr['resolution']] = 0;
			}
			$t_handler_res_arr[$t_arr['handler_id']][$t_arr['resolution']] += $t_arr['count'];
			$t_handler_res_arr[$t_arr['handler_id']]['total'] += $t_arr['count'];

			$t_arr = db_fetch_array( $result );
		}

        $t_filter_prefix = config_get( 'bug_count_hyperlink_prefix' );
		$t_row_count = 0;
		# We now have a multi dimensional array of users and resolutions, with the value of each resolution for each user
		foreach( $t_handler_res_arr as $t_handler_id => $t_arr2 ) {
			# Only print developers who have had at least one bug assigned to them. This helps
			# prevent divide by zeroes, showing developers not on this project, and showing
			# users that aren't actually developers...

			if ( $t_arr2['total'] > 0 ) {
				PRINT '<tr align="center" ' . helper_alternate_class( $t_row_count ) . '>';
				$t_row_count++;
				PRINT '<td>';
				PRINT user_get_name( $t_handler_id );
				PRINT '</td>';

				# We need to track the percentage of bugs that are considered fix, as well as
				# those that aren't considered bugs to begin with (when looking at %age)
				$t_bugs_fixed = 0;
				$t_bugs_notbugs = 0;
				for ( $j = 0; $j < $enum_res_count; $j++ ) {
					$res_bug_count = 0;

					if ( isset( $t_arr2[$c_res_s[$j]] ) ) {
						$res_bug_count = $t_arr2[$c_res_s[$j]];
					}

					PRINT '<td>';
					if ( 0 < $res_bug_count ) {
						$t_bug_link = '<a class="subtle" href="' . $t_filter_prefix . '&amp;handler_id=' . $t_handler_id;
						$t_bug_link = $t_bug_link . '&amp;show_resolution=' .  $c_res_s[$j] . '">';
						PRINT $t_bug_link . $res_bug_count . '</a>';
					} else {
						PRINT $res_bug_count;
					}
					PRINT '</td>';

					# These resolutions are considered fixed
					if ( FIXED == $c_res_s[$j] ) {
						$t_bugs_fixed += $res_bug_count;
					}
					# These are not counted as bugs
					else if ( (WONT_FIX == $c_res_s[$j] )  ||
							  (SUSPENDED == $c_res_s[$j] ) ||
							  (DUPLICATE == $c_res_s[$j] ) ||
							  (NOT_A_BUG == $c_res_s[$j] ) ) {
						$t_bugs_notbugs += $res_bug_count;
					}
				}

				$t_percent_fixed = 0;
				if ( ( $t_arr2['total'] - $t_bugs_notbugs ) > 0 ) {
					$t_percent_fixed = ( $t_bugs_fixed / ( $t_arr2['total'] - $t_bugs_notbugs ) );
				}
				PRINT '<td>';
				printf( '% 1.0f%%', ( $t_percent_fixed * 100 ) );
				PRINT '</td>';
			}
		}
	}
	# --------------------
	# Print reporter / resolution report
	function summary_print_reporter_resolution( $p_resolution_enum_string ) {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );
		$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		# Organise an array of resolution values to be used later
		$t_res_arr = explode_enum_string( $p_resolution_enum_string );
		$enum_res_count = count( $t_res_arr );
		$c_res_s = array();
		for ( $i = 0; $i < $enum_res_count; $i++ ) {
			$t_res_s = explode_enum_arr( $t_res_arr[$i] );
			$c_res_s[$i] = db_prepare_string( $t_res_s[0] );
		}

		# Checking if it's a per project statistic or all projects
		$specific_where = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $specific_where ) {
			return;
		}

		# Get all of the bugs and split them up into an array
		$query = "SELECT COUNT(id) as count, reporter_id, resolution
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY reporter_id, resolution";
		$result = db_query( $query );

		$t_reporter_res_arr = array();
		$t_reporter_bugcount_arr = array();
		$t_arr = db_fetch_array( $result );
		while ( $t_arr ) {
			if ( !isset( $t_reporter_res_arr[$t_arr['reporter_id']] ) ) {
				$t_reporter_res_arr[$t_arr['reporter_id']] = array();
				$t_reporter_bugcount_arr[$t_arr['reporter_id']] = 0;
			}
			if ( !isset( $t_reporter_res_arr[$t_arr['reporter_id']][$t_arr['resolution']] ) ) {
				$t_reporter_res_arr[$t_arr['reporter_id']][$t_arr['resolution']] = 0;
			}
			$t_reporter_res_arr[$t_arr['reporter_id']][$t_arr['resolution']] += $t_arr['count'];
			$t_reporter_bugcount_arr[$t_arr['reporter_id']] += $t_arr['count'];

			$t_arr = db_fetch_array( $result );
		}

		# Sort our total bug count array so that the reporters with the highest number of bugs are listed first,
		arsort( $t_reporter_bugcount_arr );

		$t_row_count = 0;
		# We now have a multi dimensional array of users and resolutions, with the value of each resolution for each user
		foreach( $t_reporter_bugcount_arr as $t_reporter_id => $t_total_user_bugs ) {
			# Limit the number of reporters listed
			if ( $t_row_count > $t_reporter_summary_limit ) {
				break;
			}

			# Only print reporters who have reported at least one bug. This helps
			# prevent divide by zeroes, showing reporters not on this project, and showing
			# users that aren't actually reporters...
			if ( $t_total_user_bugs > 0 ) {
				$t_arr2 = $t_reporter_res_arr[$t_reporter_id];

				PRINT '<tr align="center" ' . helper_alternate_class( $t_row_count ) . '>';
				$t_row_count++;
				PRINT '<td>';
				PRINT user_get_name( $t_reporter_id );
				PRINT '</td>';

				# We need to track the percentage of bugs that are considered fix, as well as
				# those that aren't considered bugs to begin with (when looking at %age)
				$t_bugs_fixed = 0;
				$t_bugs_notbugs = 0;
				for ( $j = 0; $j < $enum_res_count; $j++ ) {
					$res_bug_count = 0;

					if ( isset( $t_arr2[$c_res_s[$j]] ) ) {
						$res_bug_count = $t_arr2[$c_res_s[$j]];
					}

					PRINT '<td>';
					if ( 0 < $res_bug_count ) {
						$t_bug_link = '<a class="subtle" href="' . config_get( 'bug_count_hyperlink_prefix' ) . '&amp;reporter_id=' . $t_reporter_id;
						$t_bug_link = $t_bug_link . '&amp;show_resolution=' .  $c_res_s[$j] . '">';
						PRINT $t_bug_link . $res_bug_count . '</a>';
					} else {
						PRINT $res_bug_count;
					}
					PRINT '</td>';

					# These resolutions are considered fixed
					if ( FIXED == $c_res_s[$j] ) {
						$t_bugs_fixed += $res_bug_count;
					}
					# These are not counted as bugs
					else if ( (UNABLE_TO_DUPLICATE == $c_res_s[$j] ) ||
							  (DUPLICATE == $c_res_s[$j] ) ||
							  (NOT_A_BUG == $c_res_s[$j] ) ) {
						$t_bugs_notbugs += $res_bug_count;
					}
				}

				$t_percent_errors = 0;
				if ( $t_total_user_bugs > 0 ) {
					$t_percent_errors = ( $t_bugs_notbugs / $t_total_user_bugs );
				}
				PRINT '<td>';
				printf( '% 1.0f%%', ( $t_percent_errors * 100 ) );
				PRINT '</td>';
				PRINT '</tr>';
			}
		}
	}	# --------------------
	# Print reporter effectiveness report
	function summary_print_reporter_effectiveness( $p_severity_enum_string, $p_resolution_enum_string ) {
		$t_mantis_bug_table = config_get( 'mantis_bug_table' );
		$t_mantis_user_table = config_get( 'mantis_user_table' );
		$t_reporter_summary_limit = config_get( 'reporter_summary_limit' );

		$t_project_id = helper_get_current_project();
		$t_user_id = auth_get_current_user_id();

		# These are our overall "values" for severities and non-bug results
		$t_severity_multiplier[FEATURE] = 1;
		$t_severity_multiplier[TRIVIAL] = 2;
		$t_severity_multiplier[TEXT] = 3;
		$t_severity_multiplier[TWEAK] = 2;
		$t_severity_multiplier[MINOR] = 5;
		$t_severity_multiplier[MAJOR] = 8;
		$t_severity_multiplier[CRASH] = 8;
		$t_severity_multiplier[BLOCK] = 10;
		$t_severity_multiplier['average'] = 5;

		$t_notbug_multiplier[UNABLE_TO_DUPLICATE] = 2;
		$t_notbug_multiplier[DUPLICATE] = 3;
		$t_notbug_multiplier[NOT_A_BUG] = 5;

		$t_sev_arr = explode_enum_string( $p_severity_enum_string );
		$enum_sev_count = count( $t_sev_arr );
		$c_sev_s = array();
		for ( $i = 0; $i < $enum_sev_count; $i++ ) {
			$t_sev_s = explode_enum_arr( $t_sev_arr[$i] );
			$c_sev_s[$i] = db_prepare_string( $t_sev_s[0] );
		}

		$t_res_arr = explode_enum_string( $p_resolution_enum_string );
		$enum_res_count = count( $t_res_arr );
		$c_res_s = array();
		for ( $i = 0; $i < $enum_res_count; $i++ ) {
			$t_res_s = explode_enum_arr( $t_res_arr[$i] );
			$c_res_s[$i] = db_prepare_string( $t_res_s[0] );
		}

		# Checking if it's a per project statistic or all projects
		$specific_where = helper_project_specific_where( $t_project_id );
		if ( ' 1<>1' == $specific_where ) {
			return;
		}

		# Get all of the bugs and split them up into an array
		$query = "SELECT COUNT(id) as count, reporter_id, resolution, severity
				FROM $t_mantis_bug_table
				WHERE $specific_where
				GROUP BY reporter_id, resolution, severity";
		$result = db_query( $query );

		$t_reporter_ressev_arr = array();
		$t_reporter_bugcount_arr = array();
		$t_arr = db_fetch_array( $result );
		while ( $t_arr ) {
			if ( !isset( $t_reporter_ressev_arr[$t_arr['reporter_id']] ) ) {
				$t_reporter_ressev_arr[$t_arr['reporter_id']] = array();
				$t_reporter_bugcount_arr[$t_arr['reporter_id']] = 0;
			}
			if ( !isset( $t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']] ) ) {
				$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']] = array();
				$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']]['total'] = 0;
			}
			if ( !isset( $t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']][$t_arr['resolution']] ) ) {
				$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']][$t_arr['resolution']] = 0;
			}
			$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']][$t_arr['resolution']] += $t_arr['count'];
			$t_reporter_ressev_arr[$t_arr['reporter_id']][$t_arr['severity']]['total'] += $t_arr['count'];
			$t_reporter_bugcount_arr[$t_arr['reporter_id']] += $t_arr['count'];

			$t_arr = db_fetch_array( $result );
		}

		# Sort our total bug count array so that the reporters with the highest number of bugs are listed first,
		arsort( $t_reporter_bugcount_arr );

		$t_row_count = 0;
		# We now have a multi dimensional array of users, resolutions and severities, with the
		# value of each resolution and severity for each user
		foreach( $t_reporter_bugcount_arr as $t_reporter_id => $t_total_user_bugs ) {
			# Limit the number of reporters listed
			if ( $t_row_count > $t_reporter_summary_limit ) {
				break;
			}

			# Only print reporters who have reported at least one bug. This helps
			# prevent divide by zeroes, showing reporters not on this project, and showing
			# users that aren't actually reporters...
			if ( $t_total_user_bugs > 0 ) {
				$t_arr2 = $t_reporter_ressev_arr[$t_reporter_id];

				PRINT '<tr ' . helper_alternate_class( $t_row_count ) . '>';
				$t_row_count++;
				PRINT '<td>';
				PRINT user_get_name( $t_reporter_id );
				PRINT '</td>';

				$t_total_severity = 0;
				$t_total_errors = 0;
				for ( $j = 0; $j < $enum_sev_count; $j++ ) {
					if ( !isset( $t_arr2[$c_sev_s[$j]] ) ) {
						continue;
					}

					$sev_bug_count = $t_arr2[$c_sev_s[$j]]['total'];
					$t_sev_mult = $t_severity_multiplier['average'];
					if ( $t_severity_multiplier[$c_sev_s[$j]] ) {
						$t_sev_mult = $t_severity_multiplier[$c_sev_s[$j]];
					}

					if ( $sev_bug_count > 0 ) {
						$t_total_severity += ( $sev_bug_count * $t_sev_mult );
					}

					# Calculate the "error value" of bugs reported
					$t_notbug_res_arr = array( UNABLE_TO_DUPLICATE, DUPLICATE, NOT_A_BUG );

					foreach ( $t_notbug_res_arr as $t_notbug_res ) {
						if ( isset( $t_arr2[$c_sev_s[$j]][$t_notbug_res] ) ) {
							$t_notbug_mult = 1;
							if ( $t_notbug_multiplier[$t_notbug_res] ) {
								$t_notbug_mult = $t_notbug_multiplier[$t_notbug_res];
							}

							$t_total_errors += ( $t_sev_mult * $t_notbug_mult );
						}
					}
				}
				PRINT '<td>';
				PRINT $t_total_severity;
				PRINT '</td>';
				PRINT '<td>';
				PRINT $t_total_errors;
				PRINT '</td>';
				PRINT '<td>';
				PRINT ( $t_total_severity - $t_total_errors );
				PRINT '</td>';
				PRINT '</tr>';
			}
		}
	}
?>
