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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'Period.php' );
	require_once( 'graph_api.php' );

	access_ensure_project_level( config_get( 'view_summary_threshold' ) );

	$f_width = gpc_get_int( 'width', 600 );
	$t_ar = plugin_config_get( 'bar_aspect' );
	$t_interval = new Period();
	$t_interval->set_period_from_selector( 'interval' );
	$f_show_as_table = gpc_get_bool( 'show_table', FALSE );
	$f_summary = gpc_get_bool( 'summary', FALSE );

	$t_interval_days = $t_interval->get_elapsed_days();
	if ( $t_interval_days <= 14 ) {
	    $t_incr = 60 * 60; // less than 14 days, use hourly
	} else if ( $t_interval_days <= 92 ) {
	    $t_incr = 24 * 60 * 60; // less than three month, use daily
	} else {
	    $t_incr = 7 * 24 * 60 * 60; // otherwise weekly
	}

	$f_page_number = 1;

	$t_per_page = -1;
	$t_bug_count = null;
	$t_page_count = 0;

	$t_filter = current_user_get_bug_filter();
    $t_filter['_view_type']	= 'advanced';
    $t_filter['show_status'] = array(META_FILTER_ANY);
	$t_filter['sort'] = '';
	$rows = filter_get_bug_rows( $f_page_number, $t_per_page, $t_page_count, $t_bug_count, $t_filter, null, null, true );
	if ( count($rows) == 0 ) {
		// no data to graph
		exit();
	}

	$t_bug_table			= db_get_table( 'mantis_bug_table' );
	$t_bug_hist_table			= db_get_table( 'mantis_bug_history_table' );

	$t_marker = array();
	$t_data = array();
	$t_ptr = 0;
	$t_end = $t_interval->get_end_timestamp();
	$t_start = $t_interval->get_start_timestamp();

    $t_resolved = config_get( 'bug_resolved_status_threshold' );
    $t_closed = config_get( 'bug_closed_status_threshold' );

    $t_bug = array();
    $t_bug_cat = array(); // save categoties or bugs to look up resolved ones.
    $t_category = array();

	// walk through all issues and grab their category for 'now'
	$t_marker[$t_ptr] = time();
	$t_data[$t_ptr] = array();
	foreach ($rows as $t_row) {
	    // the following function can treat the resolved parameter as an array to match
        $t_cat = category_get_name( $t_row->category_id );
        if ($t_cat == '')
            $t_cat = 'none';
	    if ( !access_compare_level( $t_row->status, $t_resolved ) ) {
	        if (in_array($t_cat, $t_category)) {
                $t_data[$t_ptr][$t_cat] ++;
            } else {
                $t_data[$t_ptr][$t_cat] = 1;
                $t_category[] = $t_cat;
            }
        }
        $t_bug[] = $t_row->id;
        $t_bug_cat[$t_row->id] = $t_cat;
	}

    // get the history for these bugs over the interval required to offset the data
    // type = 0 and field=status are status changes
    // type = 1 are new bugs
    $t_select = 'SELECT bug_id, type, field_name, old_value, new_value, date_modified FROM '.$t_bug_hist_table.
        ' WHERE bug_id in ('.implode(',', $t_bug).') and '.
            '( (type='.NORMAL_TYPE.' and field_name=\'category\') or '.
                '(type='.NORMAL_TYPE.' and field_name=\'status\') or type='.NEW_BUG.' ) and '.
                'date_modified >= \''. $t_start .'\''.
            ' order by date_modified DESC';
    $t_result = db_query( $t_select );
	$row = db_fetch_array( $t_result );

	for ($t_now = time() - $t_incr; $t_now >= $t_start; $t_now -= $t_incr) {
	    // walk through the data points and use the data retrieved to update counts
	    while( ( $row !== false ) && ( $row['date_modified'] >= $t_now ) ) {
	        switch ($row['type']) {
    	        case 0: // updated bug
    	            if ($row['field_name'] == 'category') {
	                    $t_cat = $row['new_value'];
            	        if ($t_cat == '')
            	            $t_cat = 'none';
            	        if (in_array($t_cat, $t_category)) {
                            $t_data[$t_ptr][$t_cat] --;
                        } else {
                            $t_data[$t_ptr][$t_cat] = 0;
                            $t_category[] = $t_cat;
                        }
	                    $t_cat = $row['old_value'];
            	        if ($t_cat == '')
            	            $t_cat = 'none';
            	        if (in_array($t_cat, $t_category)) {
                            $t_data[$t_ptr][$t_cat] ++;
                        } else {
                            $t_data[$t_ptr][$t_cat] = 1;
                            $t_category[] = $t_cat;
                        }
                        // change the category associated with the bug to match in case the bug was
                        //  created during the scan
                        $t_bug_cat[$row['bug_id']] = $t_cat;
                    } else { // change of status access_compare_level( $t_row['status'], $t_resolved )
                        if ( access_compare_level( $row['new_value'], $t_resolved ) &&
                                !access_compare_level( $row['old_value'], $t_resolved ) ) {
                            // transition from open to closed
                            $t_cat = $t_bug_cat[$row['bug_id']];
            	            if ($t_cat == '')
            	                $t_cat = 'none';
            	            if (in_array($t_cat, $t_category)) {
                                $t_data[$t_ptr][$t_cat] ++;
                            } else {
                                $t_data[$t_ptr][$t_cat] = 1;
                                $t_category[] = $t_cat;
                            }
                        }
                    }
                    break;
    	        case 1: // new bug
                    $t_cat = $t_bug_cat[$row['bug_id']];
    	            if ($t_cat == '')
    	                $t_cat = 'none';
    	            if (in_array($t_cat, $t_category)) {
                        $t_data[$t_ptr][$t_cat] --;
                    } else {
                        $t_data[$t_ptr][$t_cat] = 0;
                        $t_category[] = $t_cat;
                    }
                    break;
            }
        	$row = db_fetch_array( $t_result );
        }

	    if ($t_now <= $t_end) {
    	    $t_marker[$t_ptr] = $t_now;
	        $t_ptr++;
	        foreach ( $t_category as $t_cat ) {
	            $t_data[$t_ptr][$t_cat] = $t_data[$t_ptr-1][$t_cat];
            }
        }
	}
    $t_bin_count = $t_ptr;
// drop any categories that have no counts
//  These arise when bugs are opened and closed within the data intervals
    $t_count_cat = count( $t_category );
    for ( $t=0; $t<$t_count_cat; $t++ ) {
        $t_cat = $t_category[ $t ];
        $t_not_zero = false;
        for ($t_ptr=0; $t_ptr<$t_bin_count; $t_ptr++) {
            if ( isset( $t_data[$t_ptr][$t_cat] ) && ( $t_data[$t_ptr][$t_cat] > 0 ) ) {
                $t_not_zero = true;
                break;
            }
        }
        if ( !$t_not_zero ) {
            unset( $t_category[ $t ] );
		}
    }
// sort and display the results
    sort($t_category);
    if ($f_show_as_table) {
		$t_date_format = config_get( 'short_date_format' );
        html_begin();
        html_head_begin();
        html_css();
        html_content_type();
        html_title( lang_get( 'by_category' ) );
    	html_head_end();
    	html_body_begin();
	    echo '<table class="width100"><tr><td></td>';
        foreach ( $t_category as $t_cat ) {
            echo '<th>'.$t_cat.'</th>';
        }
        echo '</tr>';
	    for ($t_ptr=0; $t_ptr<$t_bin_count; $t_ptr++) {
            echo '<tr class="row-'.($t_ptr%2+1).'"><td>'.$t_ptr.' ('. date( $t_date_format, $t_marker[$t_ptr] ) .')'.'</td>';
            foreach ( $t_category as $t_cat ) {
                echo '<td>'.(isset($t_data[$t_ptr][$t_cat]) ? $t_data[$t_ptr][$t_cat] : 0).'</td>';
            }
            echo '</tr>';
        }
	    echo '</table>';
    	html_body_end();
    	html_end();
	} else {
	    // reverse the array and reorder the data, if necessary
	    $t_metrics = array();
	    for ($t_ptr=0; $t_ptr<$t_bin_count; $t_ptr++) {
	        $t = $t_bin_count - $t_ptr - 1;
	        $t_metrics[0][$t_ptr] = $t_marker[$t];
            $i = 0;
            foreach ( $t_category as $t_cat ) {
        	    $t_metrics[++$i][$t_ptr] = isset($t_data[$t][$t_cat]) ? $t_data[$t][$t_cat] : 0;
            }
	    }
	    array_unshift( $t_category, '' ); // add placeholder
	    graph_bydate( $t_metrics, $t_category, lang_get( 'by_category' ), $f_width, $f_width * $t_ar );
    }
