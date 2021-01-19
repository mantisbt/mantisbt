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
 * View all bugs include file
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses category_api.php
 * @uses columns_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses filter_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

if( !defined( 'VIEW_ALL_INC_ALLOW' ) ) {
	return;
}

require_api( 'category_api.php' );
require_api( 'columns_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'event_api.php' );
require_api( 'filter_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

$t_filter = current_user_get_bug_filter();
filter_init( $t_filter );

list( $t_sort, ) = explode( ',', $g_filter['sort'] );
list( $t_dir, ) = explode( ',', $g_filter['dir'] );

$g_checkboxes_exist = false;

$t_current_project = helper_get_current_project();
# Improve performance by caching category data in one pass
if( $t_current_project > 0 ) {
	category_get_all_rows( $t_current_project );
}

$g_columns = helper_get_columns_to_view( COLUMNS_TARGET_VIEW_PAGE );

bug_cache_columns_data( $t_rows, $g_columns );

$t_filter_position = config_get( 'filter_position' );

# -- ====================== FILTER FORM ========================= --
if( ( $t_filter_position & FILTER_POSITION_TOP ) == FILTER_POSITION_TOP ) {
	filter_draw_selection_area();
}
# -- ====================== end of FILTER FORM ================== --


# -- ====================== BUG LIST ============================ --

?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<form id="bug_action" method="post" action="bug_actiongroup_page.php">
<?php # CSRF protection not required here - form does not result in modifications ?>
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<?php print_icon( 'fa-columns', 'ace-icon' ); ?>
		<?php echo lang_get( 'viewing_bugs_title' ) ?>
		<?php
			# -- Viewing range info --
			$v_start = 0;
			$v_end = 0;
			if (count($t_rows) > 0) {
				$v_start = $g_filter['per_page'] * ($f_page_number - 1) + 1;
				$v_end = $v_start + count($t_rows) - 1;
			}
			echo '<span class="badge"> ' . $v_start . ' - ' . $v_end . ' / ' . $t_bug_count . '</span>' ;
		?>
	</h4>
	</div>

	<div class="widget-body">

	<div class="widget-toolbox padding-8 clearfix">
		<div class="btn-toolbar">
			<div class="btn-group pull-left">
		<?php
			$t_filter_param = filter_get_temporary_key_param( $t_filter );
			if( empty( $t_filter_param ) ) {
				$t_summary_link = 'view_all_set.php?summary=1&temporary=y';
			} else {
				$t_filter_param = '?' . $t_filter_param;
				$t_summary_link = 'summary_page.php' . $t_filter_param;
			}
			# -- Print and Export links --
			print_small_button( 'print_all_bug_page.php' . $t_filter_param, lang_get( 'print_all_bug_page_link' ) );
			print_small_button( 'csv_export.php' . $t_filter_param, lang_get( 'csv_export' ) );
			print_small_button( 'excel_xml_export.php' . $t_filter_param, lang_get( 'excel_export' ) );
			if( access_has_project_level( config_get( 'view_summary_threshold' ), $t_current_project ) ) {
				print_small_button( $t_summary_link, lang_get( 'summary_link' ) );
			}

			$t_event_menu_options = $t_links = event_signal('EVENT_MENU_FILTER');

			foreach ($t_event_menu_options as $t_plugin => $t_plugin_menu_options) {
				foreach ($t_plugin_menu_options as $t_callback => $t_callback_menu_options) {
					if (!is_array($t_callback_menu_options)) {
						$t_callback_menu_options = array($t_callback_menu_options);
					}

					foreach ($t_callback_menu_options as $t_menu_option) {
						if ($t_menu_option) {
							echo $t_menu_option;
						}
					}
				}
			}
		?>
		</div>
		<div class="btn-group pull-right"><?php
			# -- Page number links --
			$t_tmp_filter_key = filter_get_temporary_key( $t_filter );
			print_page_links( 'view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number, $t_tmp_filter_key );
			?>
		</div>
	</div>
</div>

<div class="widget-main no-padding">
	<div class="table-responsive checkbox-range-selection">
	<table id="buglist" class="table table-bordered table-condensed table-hover table-striped">
	<thead>
<?php # -- Bug list column header row -- ?>
<tr class="buglist-headers">
<?php
	$t_title_function = 'print_column_title';
	$t_sort_properties = filter_get_visible_sort_properties_array( $t_filter, COLUMNS_TARGET_VIEW_PAGE );
	foreach( $g_columns as $t_column ) {
		helper_call_custom_function( $t_title_function, array( $t_column, COLUMNS_TARGET_VIEW_PAGE, $t_sort_properties ) );
	}
?>
</tr>

</thead><tbody>

<?php
/**
 * Output Bug Rows
 *
 * @param array $p_rows An array of bug objects.
 * @return void
 */
function write_bug_rows( array $p_rows ) {
	global $g_columns, $g_filter;

	$t_in_stickies = ( $g_filter && ( 'on' == $g_filter[FILTER_PROPERTY_STICKY] ) );

	# -- Loop over bug rows --

	$t_rows = count( $p_rows );
	for( $i=0; $i < $t_rows; $i++ ) {
		$t_row = $p_rows[$i];

		if( ( 0 == $t_row->sticky ) && ( 0 == $i ) ) {
			$t_in_stickies = false;
		}
		if( ( 0 == $t_row->sticky ) && $t_in_stickies ) {	# demarcate stickies, if any have been shown
?>
		   <tr>
				   <td colspan="<?php echo count( $g_columns ); ?>" bgcolor="#d3d3d3"></td>
		   </tr>
<?php
			$t_in_stickies = false;
		}

		echo '<tr>';

		$t_column_value_function = 'print_column_value';
		foreach( $g_columns as $t_column ) {
			helper_call_custom_function( $t_column_value_function, array( $t_column, $t_row ) );
		}
		echo '</tr>';
	}
}


write_bug_rows( $t_rows );
# -- ====================== end of BUG LIST ========================= --
?>

</tbody>
</table>
</div>

<div class="widget-toolbox padding-8 clearfix">
<?php
# -- ====================== MASS BUG MANIPULATION =================== --
# @@@ ideally buglist-footer would be in <tfoot>, but that's not possible due to global g_checkboxes_exist set via write_bug_rows()
?>
	<div class="form-inline pull-left">
<?php
		if( $g_checkboxes_exist ) {
			echo '<label class="inline">';
			echo '<input class="ace check_all input-sm" type="checkbox" id="bug_arr_all" name="bug_arr_all" value="all" />';
			echo '<span class="lbl padding-6">' . lang_get( 'select_all' ) . ' </span > ';
			echo '</label>';
		}
		if( $g_checkboxes_exist ) {
?>
			<select name="action" class="input-sm">
				<?php print_all_bug_action_option_list($t_unique_project_ids) ?>
			</select>
			<input type="submit" class="btn btn-primary btn-white btn-sm btn-round" value="<?php echo lang_get('ok'); ?>"/>
<?php
		} else {
			echo '&#160;';
		}
?>
			</div>
			<div class="btn-group pull-right">
				<?php
					$t_tmp_filter_key = filter_get_temporary_key( $t_filter );
					print_page_links('view_all_bug_page.php', 1, $t_page_count, (int)$f_page_number, $t_tmp_filter_key );
				?>
			</div>
<?php # -- ====================== end of MASS BUG MANIPULATION ========================= -- ?>
</div>

</div>
</div>
</div>
</form>
</div>
<?php

# -- ====================== FILTER FORM ========================= --
if( ( $t_filter_position & FILTER_POSITION_BOTTOM ) == FILTER_POSITION_BOTTOM ) {
	filter_draw_selection_area();
}
# -- ====================== end of FILTER FORM ================== --
