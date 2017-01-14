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
 * Manage filter edit page
 *
 * @package MantisBT
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

layout_page_header( lang_get('manage_filter_edit_page_title' ) );

layout_page_begin( 'manage_filter_edit_page.php' );

$f_filter_id = gpc_get_int( 'filter_id', null );
if( null === $f_filter_id ) {
	error_parameters( 'FILTER_ID' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

$t_filter_string = filter_db_get_filter( $f_filter_id );
if( !$t_filter_string ) {
	access_denied();
} else {
	$t_filter = filter_deserialize( $t_filter_string );
	$t_filter['_source_query_id'] = $f_filter_id;
	filter_cache_row( $f_filter_id );
}

$f_view_type = gpc_get_string( 'view_type', $t_filter['_view_type'] );
$t_filter['_view_type'] = $f_view_type;
$t_filter = filter_ensure_valid_filter( $t_filter );

$t_action = 'manage_filter_edit_update.php';
$t_current_project_id = helper_get_current_project();
$t_filter_project_id = filter_get_field( $f_filter_id, 'project_id' );

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<form method="post" name="filters" id="filters_form_open" action="<?php echo $t_action; ?>">
	<input type="hidden" name="filter_id" value="<?php echo $f_filter_id ?>" >
	<input type="hidden" name="view_type" value="<?php echo $t_filter['_view_type'] ?>" >
	<?php echo form_security_field( 'manage_filter_edit_update' ) ?>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-filter"></i>
				<?php echo lang_get('edit_filter') ?>
			</h4>

			<div class="widget-toolbar">
				<div class="widget-menu">
					<a href="#" data-action="settings" data-toggle="dropdown">
						<i class="ace-icon fa fa-bars bigger-125"></i>
					</a>
					<ul class="dropdown-menu dropdown-menu-right dropdown-yellow dropdown-caret dropdown-closer">
						<li>
						<?php
							$f_switch_view_link = 'manage_filter_edit_page.php?filter_id=' . $f_filter_id . '&view_type=';
							if( ( SIMPLE_ONLY != config_get( 'view_filters' ) ) && ( ADVANCED_ONLY != config_get( 'view_filters' ) ) ) {
								if( 'advanced' == $t_filter['_view_type'] ) {
									echo '<a href="' . $f_switch_view_link, 'simple"><i class="ace-icon fa fa-toggle-off"></i>&#160;&#160;' . lang_get('simple_filters') . '</a>';
								} else {
									echo '<a href="' . $f_switch_view_link, 'advanced"><i class="ace-icon fa fa-toggle-on"></i>&#160;&#160;' . lang_get('advanced_filters') . '</a>';
								}
							}
						?>
						</li>
					</ul>
				</div>
			</div>

		</div>

		<div class="widget-body">
			<div class="widget-main no-padding">

				<div class="widget-toolbox padding-8 clearfix">
					<div class="btn-toolbar pull-left">
						<div class="form-inline">
							<label>
								<?php echo lang_get( 'query_name' ) ?>&nbsp;
								<input type="text" size="16" name="filter_name" maxlength="64" value="<?php echo filter_get_field( $f_filter_id, 'name' ) ?>">
							</label>
						</div>
					</div>
				</div>

				<div class="table-responsive">
					<?php
					$t_for_screen = true;
					$t_static = gpc_get_bool( 'static', false );
					filter_form_draw_inputs( $t_filter, $t_for_screen, $t_static );
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
				</div>

				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-striped">
						<?php
						if( access_has_project_level( config_get( 'stored_query_create_shared_threshold' ) ) ) {
						?>
						<tr>
							<td class="category">
								<?php echo lang_get( 'filter_access' ) ?>:
							</td>
							<td>
								<label class="inline">
									<input type="checkbox" class="ace input-sm" name="is_public" value="1" <?php check_checked( true == filter_get_field( $f_filter_id, 'is_public' ) ) ?>>
								<span class="lbl"> <?php echo lang_get( 'public' ) ?></span>
								</label>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="category">
								<?php echo lang_get( 'filter_visibility' ) ?>:
							</td>
							<td>
								<label class="inline">
									<input type="radio" class="ace input-sm" name="project_id" value="<?php echo ALL_PROJECTS ?>" <?php check_checked( ALL_PROJECTS == $t_filter_project_id ) ?>>
									<span class="lbl"> <?php echo lang_get( 'all_projects' ) ?></span>
								</label>
								<br>
								<?php if( ALL_PROJECTS != $t_filter_project_id ) { ?>
								<label>
									<input type="radio" class="ace input-sm" name="project_id" value="<?php echo $t_filter_project_id ?>" <?php check_checked( ALL_PROJECTS != $t_filter_project_id ) ?>>
									<span class="lbl"> <?php echo lang_get( 'stored_project' ) . ' (' . project_get_name( $t_filter_project_id ) . ')' ?></span>
								</label>
								<br>
								<?php } ?>
								<?php if( $t_filter_project_id != $t_current_project_id ) { ?>
								<label>
									<input type="radio" class="ace input-sm" name="project_id" value="<?php echo $t_current_project_id ?>">
									<span class="lbl"> <?php echo lang_get( 'current_project' ) . ' (' . project_get_name( $t_current_project_id ) . ')' ?></span>
								</label>
								<?php } ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_filter' ) ?>" />
		</div>
	</div>
	</form>
</div>

<?php
layout_page_end();
