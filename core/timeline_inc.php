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

require_once( 'core.php' );
require_api( 'timeline_api.php' );

define( 'MAX_EVENTS', 50 );

$f_days = gpc_get_int( 'days', 0 );
$f_all = gpc_get_int( 'all', 0 );
$t_max_events = $f_all ? 0 : MAX_EVENTS + 1;

$t_end_time = time() - ( $f_days * SECONDS_PER_DAY );
$t_start_time = $t_end_time - ( 7 * SECONDS_PER_DAY );
$t_events = timeline_events( $t_start_time, $t_end_time, $t_max_events );

$t_collapse_block = is_collapsed( 'timeline' );
$t_block_css = $t_collapse_block ? 'collapsed' : '';
$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
?>

<div id="timeline" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-clock-o"></i>
			<?php echo lang_get( 'timeline_title' ) ?>
		</h4>
		<div class="widget-toolbar">
			<a data-action="collapse" href="#">
				<i class="1 ace-icon fa <?php echo $t_block_icon ?> bigger-125"></i>
			</a>
		</div>
	</div>

	<div class="widget-body">
		<div class="widget-toolbox">
			<div class="btn-toolbar">
<?php
				$t_short_date_format = config_get( 'short_date_format' );
				echo '&#160;&#160;';
				echo '<span class="label label-grey"> ' . date( $t_short_date_format, $t_start_time ) . ' </span>';
				echo  ' .. ';
				echo '<span class="label label-grey"> ' . date( $t_short_date_format, $t_end_time ) . ' </span>';
				echo '&#160;&#160;';

				echo '<div class="btn-group">';
				echo ' <a class="btn btn-primary btn-xs btn-white btn-round" href="my_view_page.php?days=' .
					( $f_days + 7 ) . '">' . lang_get( 'prev' ) . '</a>';

				$t_next_days = ( $f_days - 7 ) > 0 ? $f_days - 7 : 0;

				if( $t_next_days != $f_days ) {
					echo ' <a class="btn btn-primary btn-xs btn-white btn-round" href="my_view_page.php?days=' .
						$t_next_days . '">' . lang_get( 'next' ) . '</a>';
				}
				echo '</div>';
?>
			</div>
		</div>

		<div class="widget-main no-padding">
			<div class="profile-feed">
			</div>
		</div>

<?php
	if( !$f_all && count( $t_events ) > MAX_EVENTS ) {
		$t_events = array_slice( $t_events, 0, MAX_EVENTS );
		timeline_print_events( $t_events );
		echo '<div class="widget-toolbox">';
		echo '<div class="btn-toolbar">';
		echo '<a class="btn btn-primary btn-sm btn-white btn-round" href="my_view_page.php?days='
			. $f_days . '&amp;all=1">' . lang_get( 'timeline_more' ) . '</a>';
		echo '</div>';
		echo '</div>';
	} else {
		timeline_print_events( $t_events );
	}
?>

	</div>
</div>
