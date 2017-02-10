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
 * View Filters Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses compress_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'compress_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'version_api.php' );

auth_ensure_user_authenticated();

compress_enable();

layout_page_header();

layout_page_begin();

$f_filter_id = gpc_get( 'filter_id', null );
if( null === $f_filter_id ) {
	$t_filter = current_user_get_bug_filter();
	$t_named_filter = false;
} else {
	$c_filter_id = (int)$f_filter_id;
	$t_filter_string = filter_db_get_filter( $c_filter_id );
	if( !$t_filter_string ) {
		access_denied();
	} else {
		$t_filter = filter_deserialize( $t_filter_string );
		$t_named_filter = true;
		filter_cache_row( $c_filter_id );
	}
}

$f_for_screen = gpc_get_bool( 'for_screen', true );

$t_action  = 'view_all_set.php?f=3';

if( $f_for_screen == false ) {
	$t_action  = 'view_all_set.php';
}

$f_static = gpc_get_bool( 'static', false );

$f_view_type = gpc_get_string( 'view_type', $t_filter['_view_type'] );
$t_filter['_view_type'] = $f_view_type;
$t_filter = filter_ensure_valid_filter( $t_filter );

?>
<div class="space-10"></div>
<div class="col-md-12 col-xs-12">

	<form method="post" name="filters" id="filters_form_open" class="form-control" action="<?php echo $t_action; ?>">

	<?php # CSRF protection not required here - form does not result in modifications ?>
	<input type="hidden" name="type" value="1" />
	<input type="hidden" name="view_type" value="<?php echo $f_view_type; ?>" />
	<?php
		if( $f_for_screen == false ) {
			print '<input type="hidden" name="print" value="1" />';
			print '<input type="hidden" name="offset" value="0" />';
		}
	?>

		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-filter"></i>
					<?php echo lang_get('filters') ?>
				</h4>

				<div class="widget-toolbar">
					<div class="widget-menu">
						<a href="#" data-action="settings" data-toggle="dropdown">
							<i class="ace-icon fa fa-bars bigger-125"></i>
						</a>
						<ul class="dropdown-menu dropdown-menu-right dropdown-yellow dropdown-caret dropdown-closer">
							<?php
								$t_url = 'view_filters_page.php?static=' . (int)$f_static;
								if( $t_named_filter ) {
									$t_url .= '&filter_id=' . $f_filter_id;
								}
								$t_url .= '&view_type=';
								filter_print_view_type_toggle( $t_url, $t_filter['_view_type'] );
							?>
						</ul>
					</div>
				</div>

			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">

					<div class="table-responsive">
							<?php
							filter_form_draw_inputs( $t_filter, $f_for_screen, $f_static );
							?>
					</div>

					<div class="widget-toolbox padding-8 clearfix">
						<div class="btn-toolbar pull-left">
							<div class="form-inline">
								<label><?php echo lang_get( 'search' ) ?>&nbsp;
									<input type="text" id="filter-search-txt" class="input-sm" size="16"
										   name="<?php echo FILTER_PROPERTY_SEARCH ?>"
										   value="<?php echo string_attribute( $t_filter[FILTER_PROPERTY_SEARCH] ) ?>">
								</label>
							</div>
						</div>
						<div class="btn-toolbar pull-right">
							<div class="form-inline">
								<input type="submit" class="btn btn-primary btn-sm btn-white btn-round no-float" name="filter" value="<?php echo lang_get( 'use_query' )?>" />
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</form>
</div>
<?php
layout_page_end();
