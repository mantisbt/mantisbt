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
	    $t_incr = 24 * 60 * 60; // less than three months, use daily
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

	if( $t_end == false || $t_start == false ) {
		return;
	}
	// grab all status levels
	$t_status_arr  = MantisEnum::getAssocArrayIndexedByValues( config_get( 'status_enum_string' ) );
	$t_status_labels  = MantisEnum::getAssocArrayIndexedByValues( lang_get( 'status_enum_string' ) );
	$t_default_bug_status = config_get( 'bug_submit_status' );

    $t_bug = array();
    $t_view_status = array();

	// walk through all issues and grab their status for 'now'
	$t_marker[$t_ptr] = time();
	foreach ($rows as $t_row) {
	    if ( isset( $t_data[$t_ptr][$t_row->status] ) ) {
            $t_data[$t_ptr][$t_row->status] ++;
	    } else {
            $t_data[$t_ptr][$t_row->status] = 1;
            $t_view_status[$t_row->status] =
                isset($t_status_arr[$t_row->status]) ? $t_status_arr[$t_row->status] : '@'.$t_row->status.'@';
        }
        $t_bug[] = $t_row->id;
	}

    // get the history for these bugs over the interval required to offset the data
    // type = 0 and field=status are status changes
    // type = 1 are new bugs
    $t_select = 'SELECT bug_id, type, old_value, new_value, date_modified FROM '.$t_bug_hist_table.
        ' WHERE bug_id in ('.implode(',', $t_bug).
        ') and ( (type='.NORMAL_TYPE.' and field_name=\'status\')
            or type='.NEW_BUG.' ) and date_modified >= \''. $t_start .'\''.
        ' order by date_modified DESC';
    $t_result = db_query( $t_select );
	$t_row = db_fetch_array( $t_result );

	for ($t_now = time() - $t_incr; $t_now >= $t_start; $t_now -= $t_incr) {
	    // walk through the data points and use the data retrieved to update counts
	    while( ( $t_row !== false ) && ( $t_row['date_modified'] >= $t_now ) ) {
	        switch ($t_row['type']) {
    	        case 0: // updated bug
        	        if ( isset( $t_data[$t_ptr][$t_row['new_value']] ) ) {
                        if ( $t_data[$t_ptr][$t_row['new_value']] > 0 )
                            $t_data[$t_ptr][$t_row['new_value']] --;
        	        } else {
                        $t_data[$t_ptr][$t_row['new_value']] = 0;
                        $t_view_status[$t_row['new_value']] =
                            isset($t_status_arr[$t_row['new_value']]) ? $t_status_arr[$t_row['new_value']] : '@'.$t_row['new_value'].'@';
                    }
        	        if ( isset( $t_data[$t_ptr][$t_row['old_value']] ) ) {
                        $t_data[$t_ptr][$t_row['old_value']] ++;
        	        } else {
                        $t_data[$t_ptr][$t_row['old_value']] = 1;
                        $t_view_status[$t_row['old_value']] =
                            isset($t_status_arr[$t_row['old_value']]) ? $t_status_arr[$t_row['old_value']] : '@'.$t_row['old_value'].'@';
                    }
                    break;
				case 1: // new bug
					if ( isset( $t_data[$t_ptr][$t_default_bug_status] ) ) {
						if ( $t_data[$t_ptr][$t_default_bug_status] > 0 )
                            $t_data[$t_ptr][$t_default_bug_status] --;
    	            } else {
                        $t_data[$t_ptr][$t_default_bug_status] = 0;
                        $t_view_status[$t_default_bug_status] =
                            isset( $t_status_arr[$t_default_bug_status] ) ? $t_status_arr[$t_default_bug_status] : '@' . $t_default_bug_status . '@';
                    }
                    break;
            }
        	$t_row = db_fetch_array( $t_result );
        }

	    if ($t_now <= $t_end) {
	        $t_ptr++;
    	    $t_marker[$t_ptr] = $t_now;
	        foreach ( $t_view_status as $t_status => $t_label ) {
	            $t_data[$t_ptr][$t_status] = $t_data[$t_ptr-1][$t_status];
            }
        }
	}

    ksort($t_view_status);
    /* @todo - these should probably be separate strings, but in the summary page context,
        the string is used as the title for all columns */
    $t_label_string = lang_get('orct'); //use the (open/resolved/closed/total) label
    $t_label_strings = explode('/', utf8_substr($t_label_string, 1, strlen($t_label_string)-2));

    // add headers for table
    if ($f_show_as_table) {
		$t_date_format = config_get( 'short_date_format' );
        html_begin();
        html_head_begin();
        html_css();
        html_content_type();
        html_title( lang_get( 'by_status' ) );
    	html_head_end();
    	html_body_begin();
	    echo '<table class="width100"><tr><td></td>';
        if ($f_summary) {
            echo '<th>' . $t_label_strings[0] . '</th>';
            echo '<th>' . $t_label_strings[1] . '</th>';
            echo '<th>' . $t_label_strings[2] . '</th>';
        } else {
            foreach ( $t_view_status as $t_status => $t_label ) {
                echo '<th>'.$t_label.' ('.$t_status.')</th>';
            }
        }
        echo '</tr>';
    }

	$t_resolved = config_get( 'bug_resolved_status_threshold' );
	$t_closed = config_get( 'bug_closed_status_threshold' );
	$t_bin_count = $t_ptr;
	$t_labels = array();
	$i = 0;
    if ($f_summary) {

        $t_labels[++$i] = $t_label_strings[0];
        $t_labels[++$i] = $t_label_strings[1];
        $t_labels[++$i] = $t_label_strings[2];
    } else {
        foreach ( $t_view_status as $t_status => $t_label ) {
            $t_labels[++$i] = isset($t_status_labels[$t_status]) ? $t_status_labels[$t_status] : lang_get_defaulted($t_label);
        }
    }
    $t_label_count = $i;

	// reverse the array and consolidate the data, if necessary
	$t_metrics = array();
	for ($t_ptr=0; $t_ptr<$t_bin_count; $t_ptr++) {
	    $t = $t_bin_count - $t_ptr;
	    $t_metrics[0][$t_ptr] = $t_marker[$t];
	    if ($f_summary) {
	        $t_metrics[1][$t_ptr] = 0;
	        $t_metrics[2][$t_ptr] = 0;
	        $t_metrics[3][$t_ptr] = 0;
            foreach ( $t_view_status as $t_status => $t_label ) {
                if ( isset( $t_data[$t][$t_status] ) ) {
                    if ( $t_status < $t_resolved )
        	            $t_metrics[1][$t_ptr] += $t_data[$t][$t_status];
                    else if ( $t_status < $t_closed )
        	            $t_metrics[2][$t_ptr] += $t_data[$t][$t_status];
                    else
        	            $t_metrics[3][$t_ptr] += $t_data[$t][$t_status];
        	    }
            }
        } else {
            $i = 0;
            foreach ( $t_view_status as $t_status => $t_label ) {
                if ( isset( $t_data[$t][$t_status] ) )
        	        $t_metrics[++$i][$t_ptr] = $t_data[$t][$t_status];
        	    else
        	        $t_metrics[++$i][$t_ptr] = 0;
            }
        }
        if ( $f_show_as_table ) {
            echo '<tr class="row-'.($t_ptr%2+1).'"><td>'.$t_ptr.' ('. date( $t_date_format, $t_metrics[0][$t_ptr] ) .')'.'</td>';
            for ( $i=1; $i<=$t_label_count; $i++ ) {
                echo '<td>'.$t_metrics[$i][$t_ptr].'</td>';
            }
            echo '</tr>';
        }

	}
    if ($f_show_as_table) {
        echo '</table>';
    	html_body_end();
    	html_end();
    } else {
	    graph_bydate( $t_metrics, $t_labels, lang_get( 'by_status' ), $f_width, $f_width * $t_ar );
	}
