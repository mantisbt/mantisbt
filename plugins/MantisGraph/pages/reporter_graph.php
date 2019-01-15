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
 * This page displays "improved" charts on priorities : bars, 3Dpie and a mix priorities per status
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
print_summary_menu( 'summary_page.php', $t_filter );
print_summary_submenu();
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-bar-chart-o"></i>
				<?php echo plugin_lang_get( 'graph_reporter_title' ) ?>
			</h4>
		</div>

        <div class="col-md-12 col-xs-12" style="padding: 20px;">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <i class="ace-icon fa fa-bar-chart"></i>
                    <?php echo plugin_lang_get('graph_topreporter_fixed') ?>
                </h4>
            </div>
<?php
            $t_metrics = create_reporter_summary( $t_filter );
            graph_bar( $t_metrics, 2 /*wfactor*/ );
?>
        </div>
    </div>
</div>

<?php
layout_page_end();
