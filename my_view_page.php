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

	require_once( 'compress_api.php' );
	require_once( 'filter_api.php' );
	require_once( 'last_visited_api.php' );

	auth_ensure_user_authenticated();

	$t_current_user_id = auth_get_current_user_id();

	# Improve performance by caching category data in one pass
	category_get_all_rows( helper_get_current_project() );

	compress_enable();

	# don't index my view page
	html_robots_noindex();

	html_page_top1( lang_get( 'my_view_link' ) );

	if ( current_user_get_pref( 'refresh_delay' ) > 0 ) {
		html_meta_redirect( 'my_view_page.php', current_user_get_pref( 'refresh_delay' )*60 );
	}

	html_page_top2();

	print_recently_visited();

	$f_page_number		= gpc_get_int( 'page_number', 1 );

	$t_per_page = config_get( 'my_view_bug_count' );
	$t_bug_count = null;
	$t_page_count = null;

	$t_boxes = config_get( 'my_view_boxes' );
	asort ($t_boxes);
	reset ($t_boxes);
	#print_r ($t_boxes);

	$t_project_id = helper_get_current_project();
?>

<div align="center">
<?php
	$t_status_legend_position = config_get( 'status_legend_position' );

	if ( $t_status_legend_position == STATUS_LEGEND_POSITION_TOP || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
		html_status_legend();
		echo '<br />';
	}
?>
<table class="hide" border="0" cellspacing="3" cellpadding="0">

<?php
	$t_number_of_boxes = count ( $t_boxes );
	$t_boxes_position = config_get( 'my_view_boxes_fixed_position' );
	$t_counter = 0;

	while (list ($t_box_title, $t_box_display) = each ($t_boxes)) {
		# don't display bugs that are set as 0
		if ($t_box_display == 0) {
			$t_number_of_boxes = $t_number_of_boxes - 1;
		}

		# don't display "Assigned to Me" bugs to users that bugs can't be assigned to
		else if ( $t_box_title == 'assigned' && ( current_user_is_anonymous() OR user_get_assigned_open_bug_count( $t_current_user_id, $t_project_id ) == 0 ) ) {
			$t_number_of_boxes = $t_number_of_boxes - 1;
		}

		# don't display "Monitored by Me" bugs to users that can't monitor bugs
		else if ( $t_box_title == 'monitored' && ( current_user_is_anonymous() OR !access_has_project_level( config_get( 'monitor_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
			$t_number_of_boxes = $t_number_of_boxes - 1;
		}

		# don't display "Reported by Me" bugs to users that can't report bugs
		else if ( in_array( $t_box_title, array( 'reported', 'feedback', 'verify' ) ) &&
				( current_user_is_anonymous() OR !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
			$t_number_of_boxes = $t_number_of_boxes - 1;
		}

		# display the box
		else {
			$t_counter++;

			# check the style of displaying boxes - fixed (ie. each box in a separate table cell) or not
			if ( ON == $t_boxes_position ) {
				# for even box number start new row and column
				if ( 1 == $t_counter%2 ) {
					echo '<tr><td valign="top" width="50%">';
					include 'my_view_inc.php';
					echo '</td>';
				}

				# for odd box number only start new column
				else if ( 0 == $t_counter%2 ) {
					echo '<td valign="top" width="50%">';
					include 'my_view_inc.php';
					echo '</td></tr>';
				}
			}
			else if ( OFF == $t_boxes_position ) {
				# start new table row and column for first box
				if ( 1 == $t_counter ) {
					echo '<tr><td valign="top" width="50%">';
				}

				# start new table column for the second half of boxes
				if ( $t_counter == ceil ($t_number_of_boxes/2) + 1 ) {
					echo '<td valign="top" width="50%">';
				}

				# display the required box
				include 'my_view_inc.php';
				echo '<br />';

				# close the first column for first half of boxes
				if ( $t_counter == ceil ($t_number_of_boxes/2) ) {
					echo '</td>';
				}
			}
		}
	}


	# Close the box groups depending on the layout mode and whether an empty cell
	# is required to pad the number of cells in the last row to the full width of
	# the table.
	if ( ON == $t_boxes_position && $t_counter == $t_number_of_boxes && 1 == $t_counter%2 ) {
		echo '<td valign="top" width="50%"></td></tr>';
	} else if ( OFF == $t_boxes_position && $t_counter == $t_number_of_boxes ) {
		echo '</td></tr>';
	}

?>

</table>
<?php
	if ( $t_status_legend_position == STATUS_LEGEND_POSITION_BOTTOM || $t_status_legend_position == STATUS_LEGEND_POSITION_BOTH ) {
		html_status_legend();
	}
?>
</div>

<?php
	html_page_bottom();
