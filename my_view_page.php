<?php
# MantisBT - A PHP based bugtracking system

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
 * My View Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'user_api.php' );
require_css( 'status_config.php' );

auth_ensure_user_authenticated();

$t_current_user_id = auth_get_current_user_id();

# Improve performance by caching category data in one pass
category_get_all_rows( helper_get_current_project() );

compress_enable();

# don't index my view page
html_robots_noindex();

html_page_top1( lang_get( 'my_view_link' ) );

if( current_user_get_pref( 'refresh_delay' ) > 0 ) {
	html_meta_redirect( 'my_view_page.php?refresh=true', current_user_get_pref( 'refresh_delay' ) * 60 );
}

html_page_top2();

print_recently_visited();

$f_page_number		= gpc_get_int( 'page_number', 1 );

$t_per_page = config_get( 'my_view_bug_count' );
$t_bug_count = null;
$t_page_count = null;

$t_boxes = config_get( 'my_view_boxes' );
asort( $t_boxes );
reset( $t_boxes );
#print_r ($t_boxes);

$t_project_id = helper_get_current_project();
?>

<div>
<?php html_status_legend( STATUS_LEGEND_POSITION_TOP ); ?>

<div>
<?php include( $g_core_path . 'timeline_inc.php' ); ?>

<div class="myview_boxes_area">

<table class="hide" cellspacing="3" cellpadding="0">
<?php
$t_number_of_boxes = count( $t_boxes );
$t_boxes_position = config_get( 'my_view_boxes_fixed_position' );
$t_counter = 0;

define( 'MY_VIEW_INC_ALLOW', true );

while( list( $t_box_title, $t_box_display ) = each( $t_boxes ) ) {
	if( $t_box_display == 0 ) {
		# don't display bugs that are set as 0
		$t_number_of_boxes = $t_number_of_boxes - 1;
	} else if( $t_box_title == 'assigned' && ( current_user_is_anonymous()
		|| !access_has_project_level( config_get( 'handle_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
		# don't display "Assigned to Me" bugs to users that bugs can't be assigned to
		$t_number_of_boxes = $t_number_of_boxes - 1;
	} else if( $t_box_title == 'monitored' && ( current_user_is_anonymous() or !access_has_project_level( config_get( 'monitor_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
		# don't display "Monitored by Me" bugs to users that can't monitor bugs
		$t_number_of_boxes = $t_number_of_boxes - 1;
	} else if( in_array( $t_box_title, array( 'reported', 'feedback', 'verify' ) ) &&
		( current_user_is_anonymous() or !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
		# don't display "Reported by Me" bugs to users that can't report bugs
		$t_number_of_boxes = $t_number_of_boxes - 1;
	} else {
		# display the box
		$t_counter++;

		# check the style of displaying boxes - fixed (ie. each box in a separate table cell) or not
		if( ON == $t_boxes_position ) {
			if( 1 == $t_counter%2 ) {
				# for even box number start new row and column
				echo '<tr><td class="myview-left-col">';
				include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'my_view_inc.php' );
				echo '</td></tr>';
			} else if( 0 == $t_counter%2 ) {
				# for odd box number only start new column
				echo '<tr><td class="myview-right-col">';
				include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'my_view_inc.php' );
				echo '</td></tr>';
			}
		} else if( OFF == $t_boxes_position ) {
			# start new table row and column for first box
			if( 1 == $t_counter ) {
				echo '<tr><td class="myview-left-col">';
			}

			# start new table column for the second half of boxes
			if( $t_counter == ceil( $t_number_of_boxes / 2 ) + 1 ) {
				echo '<td class="myview-right-col">';
			}

			# display the required box
			include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'my_view_inc.php' );
			echo '<br />';

			# close the first column for first half of boxes
			if( $t_counter == ceil( $t_number_of_boxes / 2 ) ) {
				echo '</td>';
			}
		}
	}
}

# Close the box groups depending on the layout mode and whether an empty cell
# is required to pad the number of cells in the last row to the full width of
# the table.
if( ON == $t_boxes_position && $t_counter == $t_number_of_boxes && 1 == $t_counter%2 ) {
	echo '<td class="myview-right-col"></td></tr>';
} else if( OFF == $t_boxes_position && $t_counter == $t_number_of_boxes ) {
	echo '</td></tr>';
}
?>

</table>
</div>

<?php
html_status_legend( STATUS_LEGEND_POSITION_BOTTOM );

html_page_bottom();
