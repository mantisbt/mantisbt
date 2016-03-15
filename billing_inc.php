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
 * @uses database_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses lang_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

if( !defined( 'BILLING_INC_ALLOW' ) ) {
	return;
}

require_api( 'bugnote_api.php' );
require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

?>
<a id="bugnotestats"></a><br />
<?php
collapse_open( 'bugnotestats' );

$t_today = date( 'd:m:Y' );
$t_date_submitted = isset( $t_bug ) ? date( 'd:m:Y', $t_bug->date_submitted ) : $t_today;

$t_bugnote_stats_from_def = $t_date_submitted;
$t_bugnote_stats_from_def_ar = explode( ':', $t_bugnote_stats_from_def );
$t_bugnote_stats_from_def_d = $t_bugnote_stats_from_def_ar[0];
$t_bugnote_stats_from_def_m = $t_bugnote_stats_from_def_ar[1];
$t_bugnote_stats_from_def_y = $t_bugnote_stats_from_def_ar[2];

$t_bugnote_stats_from_d = gpc_get_int( 'start_day', $t_bugnote_stats_from_def_d );
$t_bugnote_stats_from_m = gpc_get_int( 'start_month', $t_bugnote_stats_from_def_m );
$t_bugnote_stats_from_y = gpc_get_int( 'start_year', $t_bugnote_stats_from_def_y );

$t_bugnote_stats_to_def = $t_today;
$t_bugnote_stats_to_def_ar = explode( ':', $t_bugnote_stats_to_def );
$t_bugnote_stats_to_def_d = $t_bugnote_stats_to_def_ar[0];
$t_bugnote_stats_to_def_m = $t_bugnote_stats_to_def_ar[1];
$t_bugnote_stats_to_def_y = $t_bugnote_stats_to_def_ar[2];

$t_bugnote_stats_to_d = gpc_get_int( 'end_day', $t_bugnote_stats_to_def_d );
$t_bugnote_stats_to_m = gpc_get_int( 'end_month', $t_bugnote_stats_to_def_m );
$t_bugnote_stats_to_y = gpc_get_int( 'end_year', $t_bugnote_stats_to_def_y );

$f_get_bugnote_stats_button = gpc_get_string( 'get_bugnote_stats_button', '' );

# Retrieve the cost as a string and convert to floating point
$f_bugnote_cost = floatval( gpc_get_string( 'bugnote_cost', '' ) );

$f_project_id = helper_get_current_project();

if( ON == config_get( 'time_tracking_with_billing' ) ) {
	$t_cost_col = true;
} else {
	$t_cost_col = false;
}

# Time tracking date range input form
# CSRF protection not required here - form does not result in modifications
?>

<form method="post" action="">
	<input type="hidden" name="id" value="<?php echo isset( $f_bug_id ) ? $f_bug_id : 0 ?>" />
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
					$t_filter[FILTER_PROPERTY_FILTER_BY_DATE] = 'on';
					$t_filter[FILTER_PROPERTY_START_DAY] = $t_bugnote_stats_from_d;
					$t_filter[FILTER_PROPERTY_START_MONTH] = $t_bugnote_stats_from_m;
					$t_filter[FILTER_PROPERTY_START_YEAR] = $t_bugnote_stats_from_y;
					$t_filter[FILTER_PROPERTY_END_DAY] = $t_bugnote_stats_to_d;
					$t_filter[FILTER_PROPERTY_END_MONTH] = $t_bugnote_stats_to_m;
					$t_filter[FILTER_PROPERTY_END_YEAR] = $t_bugnote_stats_to_y;
					filter_init( $t_filter );
					print_filter_do_filter_by_date( true );
				?>
			</td>
		</tr>
<?php
	if( $t_cost_col ) {
?>
		<tr class="row-1">
			<td>
				<?php echo lang_get( 'time_tracking_cost_per_hour_label' ) ?>
				<input type="text" name="bugnote_cost" value="<?php echo $f_bugnote_cost ?>" />
			</td>
		</tr>
<?php
	}
?>
		<tr>
			<td class="center" colspan="2">
				<input type="submit" class="button"
					name="get_bugnote_stats_button"
					value="<?php echo lang_get( 'time_tracking_get_info_button' ) ?>"
				/>
			</td>
		</tr>
	</table>
</form>

<?php
	if( !is_blank( $f_get_bugnote_stats_button ) ) {
		# Retrieve time tracking information
		$t_from = $t_bugnote_stats_from_y . '-' . $t_bugnote_stats_from_m . '-' . $t_bugnote_stats_from_d;
		$t_to = $t_bugnote_stats_to_y . '-' . $t_bugnote_stats_to_m . '-' . $t_bugnote_stats_to_d;
		$t_bugnote_stats = billing_get_summaries( $f_project_id, $t_from, $t_to, $f_bugnote_cost );

		# Sort the array by bug_id, user/real name
		if( ON == config_get( 'show_realname' ) ) {
			$t_name_field = 'realname';
		} else {
			$t_name_field = 'username';
		}

		if( is_blank( $f_bugnote_cost ) || ( (double)$f_bugnote_cost == 0 ) ) {
			$t_cost_col = false;
		}

		echo '<br />';

		$t_exports = array(
			'csv_export' => 'billing_export_to_csv.php',
			'excel_export' => 'billing_export_to_excel.php',
		);

		foreach( $t_exports as $t_export_label => $t_export_page ) {
			echo '[ <a href="' . $t_export_page . '?';
			echo 'from=' . $t_from . '&amp;to=' . $t_to;
			echo '&amp;cost=' . $f_bugnote_cost;
			echo '&amp;project_id=' . $f_project_id;
			echo '">' . lang_get( $t_export_label ) . '</a> ] ';
		}

		echo '<br />';

?>
<br />
<table class="width100" cellspacing="0">
	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo lang_get( $t_name_field ) ?>
		</td>
		<td class="small-caption bold">
			<?php echo lang_get( 'time_tracking' ) ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo lang_get( 'time_tracking_cost' ) ?>
		</td>
<?php	} ?>

	</tr>
<?php
		foreach ( $t_bugnote_stats['issues'] as $t_issue_id => $t_issue ) {
			$t_project_info = ( !isset( $f_bug_id ) && $f_project_id == ALL_PROJECTS ) ? '[' . project_get_name( $t_issue['project_id'] ) . ']' . lang_get( 'word_separator' ) : '';
			$t_link = sprintf( lang_get( 'label' ), string_get_bug_view_link( $t_issue_id ) ) . lang_get( 'word_separator' ) . $t_project_info . string_display( $t_issue['summary'] );
			echo '<tr class="row-category-history"><td colspan="4">' . $t_link . '</td></tr>';

			foreach( $t_issue['users'] as $t_username => $t_user_info ) {
?>
	<tr>
		<td class="small-caption">
			<?php echo $t_username ?>
		</td>
		<td class="small-caption">
			<?php echo db_minutes_to_hhmm( $t_user_info['minutes'] ) ?>
		</td>
<?php		if( $t_cost_col ) { ?>
		<td class="small-caption right">
			<?php echo string_attribute( number_format( $t_user_info['cost'], 2 ) ); ?>
		</td>
<?php		} ?>
	</tr>

<?php
			} # end of users within issues loop
		} # end for issues loop ?>

	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo lang_get( 'total_time' ); ?>
		</td>
		<td class="small-caption bold">
			<?php echo db_minutes_to_hhmm( $t_bugnote_stats['total']['minutes'] ); ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo string_attribute( number_format( $t_bugnote_stats['total']['cost'], 2 ) ); ?>
		</td>
<?php 	} ?>
	</tr>
</table>

<br />
<br />

<table class="width100" cellspacing="0">
	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo lang_get( $t_name_field ) ?>
		</td>
		<td class="small-caption bold">
			<?php echo lang_get( 'time_tracking' ) ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo lang_get( 'time_tracking_cost' ) ?>
		</td>
<?php	} ?>
	</tr>

<?php
	foreach ( $t_bugnote_stats['users'] as $t_username => $t_user_info ) {
?>
	<tr>
		<td class="small-caption">
			<?php echo $t_username; ?>
		</td>
		<td class="small-caption">
			<?php echo db_minutes_to_hhmm( $t_user_info['minutes'] ); ?>
		</td>
<?php		if( $t_cost_col ) { ?>
		<td class="small-caption right">
			<?php echo string_attribute( number_format( $t_user_info['cost'], 2 ) ); ?>
		</td>
<?php		} ?>
	</tr>
<?php	} ?>
	<tr class="row-category2">
		<td class="small-caption bold">
			<?php echo lang_get( 'total_time' ); ?>
		</td>
		<td class="small-caption bold">
			<?php echo db_minutes_to_hhmm( $t_bugnote_stats['total']['minutes'] ); ?>
		</td>
<?php	if( $t_cost_col ) { ?>
		<td class="small-caption bold right">
			<?php echo string_attribute( number_format( $t_bugnote_stats['total']['cost'], 2 ) ); ?>
		</td>
<?php	} ?>
	</tr>
</table>

<?php
	} # end if
	collapse_closed( 'bugnotestats' );
?>

<table class="width100" cellspacing="0">
	<tr>
		<td class="form-title" colspan="4"><?php
			collapse_icon( 'bugnotestats' );
			echo lang_get( 'time_tracking' ); ?>
		</td>
	</tr>
</table>

<?php
	collapse_end( 'bugnotestats' );
