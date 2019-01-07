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
 * Generate custom graphs e.g. by status for given date range
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @uses core.php
 * @uses Period.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses plugin_api.php
 */

access_ensure_project_level( config_get( 'view_summary_threshold' ) );

$f_interval = gpc_get_int( 'interval', 0 );
$t_today = date( 'Y-m-d' );
$f_type = gpc_get_int( 'graph_type', 0 );
$f_show_as_table = gpc_get_bool( 'show_table', false );

layout_page_header_begin( plugin_lang_get( 'graph_page' ) );
$t_path = config_get_global( 'path' );
layout_page_header_end();

layout_page_begin();

$t_period = new Period();
$t_period->set_period_from_selector( 'interval' );
$t_types = array(
				1 => plugin_lang_get( 'status_link' ),
				2 => plugin_lang_get( 'category_link' ),
		   );
?>
<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
    <form id="graph_form" method="post" action="<?php echo plugin_page( 'issues_trend_page.php' ); ?>" class="form-inline">
        <div class="widget-box widget-color-blue2">
        <div class="widget-body">
        <div class="widget-main no-padding">
            <div class="table-responsive">
            <table class="table table-condensed">
                <tr>
                    <td class="center">
                    <div class="form-group">
						<?php echo get_dropdown( $t_types, 'graph_type', $f_type ); ?>
					</div>
                    </td>
                    <td class="center">
                    <div class="form-group">
						<?php echo $t_period->period_selector( 'interval' ); ?>
					</div>
                    </td>
                    <td class="center">
                    <div class="form-group">
						<input type="submit" class="btn btn-sm btn-primary btn-white btn-round" name="show" value="<?php echo lang_get( 'proceed' ); ?>"/>
					</div>
                    </td>
				</tr>
			</table>
            </div>
        </div>
        </div>
        </div>
    </form>
<?php
# build the graphs if both an interval and graph type are selected
if( ( 0 != $f_type ) && ( $f_interval > 0 ) ) {
	$f_start = $t_period->get_start_formatted();
	$f_end = $t_period->get_end_formatted();

	switch( $f_type ) {
		case 1:
			$t_page_to_include = 'issues_trend_bystatus_table.php';
			break;
		case 2:
			$t_page_to_include = 'issues_trend_bycategory_table.php';
			break;
		default:
			$t_page_to_include = '';
			break;
	}

	if( !is_blank( $t_page_to_include ) ) {
		include( config_get_global( 'plugin_path' ) . plugin_get_current() . '/pages/' . $t_page_to_include );
	}
}
?>

</div>
<?php
layout_page_end();
