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

echo '<div class="timeline">';

$t_heading = lang_get( 'timeline_title' );

echo '<div class="heading">' . $t_heading . '</div>';

$t_short_date_format = config_get( 'short_date_format' );

$t_next_days = ( $f_days - 7 ) > 0 ? $f_days - 7 : 0;
$t_prev_link = ' [<a href="my_view_page.php?days=' . ( $f_days + 7 ) . '">' . lang_get( 'prev' ) . '</a>]';

if( $t_next_days != $f_days ) {
	$t_next_link = ' [<a href="my_view_page.php?days=' . $t_next_days . '">' . lang_get( 'next' ) . '</a>]';
} else {
	$t_next_link = '';
}

echo '<div class="date-range">' . date( $t_short_date_format, $t_start_time ) . ' .. ' . date( $t_short_date_format, $t_end_time ) . $t_prev_link . $t_next_link . '</div>';

if( !$f_all && count( $t_events ) > MAX_EVENTS ) {
	$t_events = array_slice( $t_events, 0, MAX_EVENTS );
	timeline_print_events( $t_events );
	echo '<p>' . $t_prev_link = ' [ <a href="my_view_page.php?days=' . $f_days . '&amp;all=1">' . lang_get( 'timeline_more' ) . '</a> ]</p>';
} else {
	timeline_print_events( $t_events );
}

echo '</div>';
