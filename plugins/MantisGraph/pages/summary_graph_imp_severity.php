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
 * This page displays "improved" charts on severities : bars, 3Dpie and a mix severities per status
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( 'core.php' );

plugin_require_api( 'core/graph_api.php' );

access_ensure_project_level( config_get( 'view_summary_threshold' ) );

layout_page_header();

layout_page_begin( 'summary_page.php' );

print_summary_menu( 'summary_page.php' );
echo '<br />';
print_summary_submenu();
$t_width = plugin_config_get( 'window_width' );
$t_graph_width = (int)( ( $t_width - 50 ) * 0.6 );

# gather the data for the graphs
$t_metrics = enum_bug_group( lang_get( 'severity_enum_string' ), 'severity' );
$t_token = token_set( TOKEN_GRAPH, json_encode( $t_metrics ) );
?>

    <div class="col-md-12 col-xs-12">
        <div class="space-10"></div>

        <div class="widget-box widget-color-blue2">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <i class="ace-icon fa fa-bar-chart-o"></i>
                    <?php echo plugin_lang_get( 'graph_imp_severity_title' ) ?>
                </h4>
            </div>

            <div class="widget-body">
                <div class="widget-main no-padding">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <td class="center">
                                    <img src="<?php echo plugin_page( 'summary_graph_byseverity.php' )?>&amp;width=<?php echo $t_graph_width?>" alt="" />
                                </td>
                            </tr>
                            <tr>
                                <td class="center">
                                    <img src="<?php echo plugin_page( 'summary_graph_byseverity_pct.php' )?>&amp;width=<?php echo $t_graph_width?>" alt="" />
                                </td>
                            </tr>
                            <tr>
                                <td class="center">
                                    <img src="<?php echo plugin_page( 'summary_graph_byseverity_mix.php' )?>&amp;width=<?php echo $t_graph_width?>" alt="" />
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
layout_page_end();
