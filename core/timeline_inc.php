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

# Variables that are defined in parent script:
#
# $g_timeline_filter	Filter array to be used to get timeline event
#						If undefined, it's initialized as null.
# $g_timeline_user		User id to limit timeline scope.
#						If undefined, it's initialized as null.
#

if( !isset( $g_timeline_filter ) ) {
	$g_timeline_filter = null;
}
if( !isset( $g_timeline_user ) ) {
	$g_timeline_user = null;
}

$f_days = gpc_get_int( 'days', 0 );
$f_all = gpc_get_int( 'all', 0 );
$t_max_events = $f_all ? 0 : MAX_EVENTS + 1;

$t_end_time = time() - ( $f_days * SECONDS_PER_DAY );
$t_start_time = $t_end_time - ( 7 * SECONDS_PER_DAY );
$t_events = timeline_events( $t_start_time, $t_end_time, $t_max_events, $g_timeline_filter, $g_timeline_user );

$t_collapse_block = is_collapsed( 'timeline' );
$t_block_css = $t_collapse_block ? 'collapsed' : '';
$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

$t_url_page = string_sanitize_url( basename( $_SERVER['SCRIPT_NAME'] ) );
# Timeline shows shows next/prev buttons that reload the page with new timeline parameters
# we must preserve parent script query parameters
$t_url_params = array();
if( !empty( $_GET ) ) {
	# Sanitize request values to avoid xss
	foreach( $_GET as $t_key => $t_value ) {
		$t_url_params[$t_key] = htmlspecialchars( $t_value );
	}
}
# clear timeline own parameters, which will be added later as needed
unset( $t_url_params['days'] );
unset( $t_url_params['all'] );
?>

<div id="timeline" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-clock-o', 'ace-icon' ); ?>
			<?php echo lang_get( 'timeline_title' ) ?>
		</h4>
		<div class="widget-toolbar">
			<a data-action="collapse" href="#">
				<?php print_icon( $t_block_icon, '1 ace-icon bigger-125' ); ?>
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
				$t_url_params['days'] = $f_days + 7;
				$t_href = $t_url_page . '?' . http_build_query( $t_url_params );
				echo ' <a class="btn btn-primary btn-xs btn-white btn-round" href="' . $t_href . '">' . lang_get( 'prev' ) . '</a>';

				$t_next_days = max( $f_days - 7, 0 );

				if( $t_next_days != $f_days ) {
					$t_url_params['days'] = $t_next_days;
					$t_href = $t_url_page . '?' . http_build_query( $t_url_params );
					echo ' <a class="btn btn-primary btn-xs btn-white btn-round" href="' . $t_href . '">' . lang_get( 'next' ) . '</a>';
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
		$t_url_params['days'] = $f_days;
		$t_url_params['all'] = 1;
		$t_href = $t_url_page . '?' . http_build_query( $t_url_params );
		echo '<a class="btn btn-primary btn-sm btn-white btn-round" href="' . $t_href . '">' . lang_get( 'timeline_more' ) . '</a>';
		echo '</div>';
		echo '</div>';
	} else {
		timeline_print_events( $t_events );
	}
?>

	</div>
</div>
