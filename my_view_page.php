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

<div class="row-fluid">
<?php html_status_legend(); ?>
<div class="tabbable tabs-left">
        <ul class="nav nav-tabs">
          <li class="active"><a data-toggle="tab" href="#l1">Pendientes</a></li>
          <li class=""><a data-toggle="tab" href="#l2">No Asignadas</a></li>
          <li class=""><a data-toggle="tab" href="#l3">Reportadas por mí</a></li>
          <li class=""><a data-toggle="tab" href="#l4">Resueltas</a></li>
          <li class=""><a data-toggle="tab" href="#l5">Modificadas Recientemente</a></li>
          <li class=""><a data-toggle="tab" href="#l6">Monitorizadas por mí</a></li>


        </ul>
        <div class="tab-content">

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
				# for even box number start new row and column
					($t_counter == 1)? $actived=" active": $actived="";
					echo "<div id='l".$t_counter."' class='tab-pane".$actived."'>";
					include 'my_view_inc.php';
					echo "</div>";
			}
		}

?>

</div>
</div>

<?php
	html_page_bottom();
