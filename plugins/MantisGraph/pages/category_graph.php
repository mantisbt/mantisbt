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
 * This page displays "improved" charts on categories : categories on bars and 3Dpie
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

access_ensure_project_level( config_get( 'view_summary_threshold' ) );

layout_page_header();
layout_page_begin( 'summary_page.php' );

$t_filter = summary_get_filter();
print_summary_menu( 'developer_graph.php', $t_filter );

# Submenu
$t_mantisgraph = plugin_get();
$t_mantisgraph->print_submenu();

$t_metrics = create_category_summary( $t_filter );
array_multisort( $t_metrics, SORT_DESC, SORT_NUMERIC);

# Dynamically set width ratio between 1 and 0.25 based on number of categories
$t_wfactor = 1 - min( max( count( $t_metrics ), 25 ) - 25, 75 ) / 100;

# Set the maximum number of pie slices displayed and aggregate the rest into
# an "others" category if needed. The number of slices should not be higher
# than the number of available colors in the palette.
$t_num_slices = 20;
$t_pie_metrics = array_slice( $t_metrics, 0, $t_num_slices );
if( count( $t_metrics ) > $t_num_slices ) {
	$t_num_slices--;

	# Remove last element and replace it with "others"
	array_pop( $t_pie_metrics );
	$t_others = sprintf(
		plugin_lang_get( 'other_categories' ),
		count( $t_metrics ) - $t_num_slices
	);
	$t_pie_metrics[$t_others] = 0;

	# Sum remaining categories into "others" slice
	foreach( array_slice( $t_metrics, $t_num_slices ) as $t_value ) {
		$t_pie_metrics[$t_others] += $t_value;
	}
}
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-bar-chart-o', 'ace-icon' ); ?>
				<?php echo plugin_lang_get( 'graph_imp_category_title' ) ?>
			</h4>
		</div>

		<div class="col-md-6 col-xs-12">
			<?php graph_bar( $t_metrics, $t_wfactor, true ); ?>
		</div>

		<div class="col-md-6 col-xs-12">
			<?php graph_pie( $t_pie_metrics ); ?>
		</div>
	</div>
</div>

<?php
layout_page_end();
