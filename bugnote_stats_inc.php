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
 * This include file prints out the bug bugnote_stats
 * $f_bug_id must already be defined
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses bugnote_api.php
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses utility_api.php
 */

if( !defined( 'BUGNOTE_STATS_INC_ALLOW' ) ) {
	return;
}

require_api( 'bugnote_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'utility_api.php' );

if( OFF == config_get( 'time_tracking_enabled' ) ) {
	return;
}
?>

<?php

$t_bugnote_stats_from_def = date( 'd:m:Y', $t_bug->date_submitted );
$t_bugnote_stats_from_def_ar = explode( ':', $t_bugnote_stats_from_def );
$t_bugnote_stats_from_def_d = $t_bugnote_stats_from_def_ar[0];
$t_bugnote_stats_from_def_m = $t_bugnote_stats_from_def_ar[1];
$t_bugnote_stats_from_def_y = $t_bugnote_stats_from_def_ar[2];

$t_bugnote_stats_from_d = gpc_get_string( FILTER_PROPERTY_DATE_SUBMITTED_START_DAY, $t_bugnote_stats_from_def_d );
$t_bugnote_stats_from_m = gpc_get_string( FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH, $t_bugnote_stats_from_def_m );
$t_bugnote_stats_from_y = gpc_get_string( FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR, $t_bugnote_stats_from_def_y );

$t_bugnote_stats_to_def = date( 'd:m:Y' );
$t_bugnote_stats_to_def_ar = explode( ':', $t_bugnote_stats_to_def );
$t_bugnote_stats_to_def_d = $t_bugnote_stats_to_def_ar[0];
$t_bugnote_stats_to_def_m = $t_bugnote_stats_to_def_ar[1];
$t_bugnote_stats_to_def_y = $t_bugnote_stats_to_def_ar[2];

$t_bugnote_stats_to_d = gpc_get_string( FILTER_PROPERTY_DATE_SUBMITTED_END_DAY, $t_bugnote_stats_to_def_d );
$t_bugnote_stats_to_m = gpc_get_string( FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH, $t_bugnote_stats_to_def_m );
$t_bugnote_stats_to_y = gpc_get_string( FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR, $t_bugnote_stats_to_def_y );

$f_get_bugnote_stats_button = gpc_get_string( 'get_bugnote_stats_button', '' );

$t_collapse_block = is_collapsed( 'bugnotestats' );
$t_block_css = $t_collapse_block ? 'collapsed' : '';
$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

# Time tracking date range input form
# CSRF protection not required here - form does not result in modifications
?>
<div class="col-md-12 col-xs-12 noprint">
	<a id="bugnotestats"></a>
	<div class="space-10"></div>
	<div id="bugnotestats" class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">

		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-clock-o"></i>
				<?php echo lang_get( 'time_tracking' ) ?>
			</h4>
			<div class="widget-toolbar">
				<a data-action="collapse" href="#">
					<i class="1 ace-icon <?php echo $t_block_icon ?> fa bigger-125"></i>
				</a>
			</div>
		</div>

		<form method="post" action="#bugnotestats">
			<div class="widget-body">
				<div class="widget-main">
					<input type="hidden" name="id" value="<?php echo $f_bug_id ?>" />
					<table class="width100" cellspacing="0">
						<tr>
							<td class="form-title" colspan="4"><?php
								collapse_icon( 'bugnotestats' );
								echo lang_get( 'time_tracking' ); ?>
							</td>
						</tr>
						<tr class="row-2">
							<td class="category" width="25%">
								<?php
									$t_filter = array();
									$t_filter[FILTER_PROPERTY_FILTER_BY_DATE_SUBMITTED] = 'on';
									$t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_DAY] = $t_bugnote_stats_from_d;
									$t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_MONTH] = $t_bugnote_stats_from_m;
									$t_filter[FILTER_PROPERTY_DATE_SUBMITTED_START_YEAR] = $t_bugnote_stats_from_y;
									$t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_DAY] = $t_bugnote_stats_to_d;
									$t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_MONTH] = $t_bugnote_stats_to_m;
									$t_filter[FILTER_PROPERTY_DATE_SUBMITTED_END_YEAR] = $t_bugnote_stats_to_y;
									filter_init( $t_filter );
									print_filter_do_filter_by_date( true );
								?>
							</td>
						</tr>
						<tr>
							<td class="center" colspan="2">
								<input type="submit" class="button"
									name="get_bugnote_stats_button"
									value="<?php echo lang_get( 'time_tracking_get_info_button' ) ?>" />
							</td>
						</tr>
					</table>


<?php
	# Print time tracking information if requested
	if( !is_blank( $f_get_bugnote_stats_button ) ) {
		# Retrieve time tracking information
		$t_from = $t_bugnote_stats_from_y . '-' . $t_bugnote_stats_from_m . '-' . $t_bugnote_stats_from_d;
		$t_to = $t_bugnote_stats_to_y . '-' . $t_bugnote_stats_to_m . '-' . $t_bugnote_stats_to_d;
		$t_bugnote_stats = bugnote_stats_get_events_array( $f_bug_id, $t_from, $t_to );

		# Sort the array by user/real name
		if( ON == config_get( 'show_realname' ) ) {
			$t_name_field = 'realname';
		} else {
			$t_name_field = 'username';
		}
		$t_sort_name = array();
		foreach ( $t_bugnote_stats as $t_key => $t_item ) {
			$t_sort_name[$t_key] = $t_item[$t_name_field];
		}
		array_multisort( $t_sort_name, $t_bugnote_stats );
		unset( $t_sort_name );
?>

					<div class="space-10"></div>
					<div class="table-responsive">
						<table class="table table-bordered table-condensed table-striped">
							<tr>
								<td class="small-caption align-left">
									<?php echo lang_get( $t_name_field ) ?>
								</td>
								<td class="small-caption align-left">
									<?php echo lang_get( 'time_tracking' ) ?>
								</td>
							</tr>
						<?php
								# Loop on all time tracking entries
								$t_sum_in_minutes = 0;
								foreach ( $t_bugnote_stats as $t_item ) {
									$t_sum_in_minutes += $t_item['sum_time_tracking'];
									$t_item['sum_time_tracking'] = db_minutes_to_hhmm( $t_item['sum_time_tracking'] );
						?>
							<tr>
								<td class="small-caption">
									<?php echo string_display_line( $t_item[$t_name_field] ) ?>
								</td>
								<td class="small-caption">
									<?php echo $t_item['sum_time_tracking'] ?>
								</td>
							</tr>
						<?php
								} # end for loop
						?>
							<tr>
								<td class="small-caption">
									<?php echo lang_get( 'total_time' ) ?>
								</td>
								<td class="small-caption">
									<?php echo db_minutes_to_hhmm( $t_sum_in_minutes ) ?>
								</td>
							</tr>
						</table>
					</div>
<?php
	} # end if
?>
				</div>
			</div>
		</form>
	</div>
</div>

