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
require_api( 'layout_api.php' );
require_css( 'status_config.php' );

auth_ensure_user_authenticated();

$t_current_user_id = auth_get_current_user_id();

# Improve performance by caching category data in one pass
category_get_all_rows( helper_get_current_project() );

compress_enable();

# don't index my view page
html_robots_noindex();

layout_page_header_begin( lang_get( 'my_view_link' ) );

if( current_user_get_pref( 'refresh_delay' ) > 0 ) {
	html_meta_redirect( 'my_view_page.php?refresh=true', current_user_get_pref( 'refresh_delay' ) * 60 );
}

layout_page_header_end();

layout_page_begin( __FILE__ );

$f_page_number		= gpc_get_int( 'page_number', 1 );

$t_per_page = config_get( 'my_view_bug_count' );
$t_bug_count = null;
$t_page_count = null;

$t_boxes = config_get( 'my_view_boxes' );
asort( $t_boxes );
reset( $t_boxes );
#print_r ($t_boxes);

$t_project_id = helper_get_current_project();
$t_timeline_view_threshold_access = access_has_project_level( config_get( 'timeline_view_threshold' ) );
$t_timeline_view_class = ( $t_timeline_view_threshold_access ) ? "col-md-7" : "col-md-6";
?>
<div class="col-xs-12 <?php echo $t_timeline_view_class ?>">

<?php
$t_number_of_boxes = count ( $t_boxes );
$t_boxes_position = config_get( 'my_view_boxes_fixed_position' );
$t_counter = 0;
$t_two_columns_applied = false;

define( 'MY_VIEW_INC_ALLOW', true );

while (list ($t_box_title, $t_box_display) = each ($t_boxes)) {
		# don't display bugs that are set as 0
	if ($t_box_display == 0) {
		$t_number_of_boxes = $t_number_of_boxes - 1;
	}
		# don't display "Assigned to Me" bugs to users that bugs can't be assigned to
	else if(
		$t_box_title == 'assigned'
		&&  ( current_user_is_anonymous()
			|| !access_has_project_level( config_get( 'handle_bug_threshold' ), $t_project_id, $t_current_user_id )
		)
	) {
		$t_number_of_boxes = $t_number_of_boxes - 1;
	}
		# don't display "Monitored by Me" bugs to users that can't monitor bugs
	else if( $t_box_title == 'monitored' && ( current_user_is_anonymous() OR !access_has_project_level( config_get( 'monitor_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
		$t_number_of_boxes = $t_number_of_boxes - 1;
	}
		# don't display "Reported by Me" bugs to users that can't report bugs
	else if( in_array( $t_box_title, array( 'reported', 'feedback', 'verify' ) ) &&
		( current_user_is_anonymous() OR !access_has_project_level( config_get( 'report_bug_threshold' ), $t_project_id, $t_current_user_id ) ) ) {
		$t_number_of_boxes = $t_number_of_boxes - 1;
			}

			# display the box
	else {
		if( !$t_timeline_view_threshold_access ) {
			if ($t_counter >= $t_number_of_boxes / 2 && !$t_two_columns_applied) {
				echo '</div>';
				echo '<div class="col-xs-12 col-md-6">';
				$t_two_columns_applied = true;
			} elseif ($t_counter >= $t_number_of_boxes && $t_two_columns_applied) {
				echo '</div>';
			} else {
				include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'my_view_inc.php' );
				echo '<div class="space-10"></div>';
			}
			$t_counter++;
		} else {
			include( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'my_view_inc.php' );
			echo '<div class="space-10"></div>';
		}
	}
}
?>
</div>

<?php if( $t_timeline_view_threshold_access ) { ?>
<div class="col-md-5 col-xs-12">
	<?php include( $g_core_path . 'timeline_inc.php' ); ?>
	<div class="space-10"></div>
</div>
<?php }
layout_page_end();
