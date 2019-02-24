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
 * Project Page
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );

auth_reauthenticate();

$f_sort	= gpc_get_string( 'sort', 'name' );
$f_dir	= gpc_get_string( 'dir', 'ASC' );

if( 'ASC' == $f_dir ) {
	$t_direction = ASCENDING;
} else {
	$t_direction = DESCENDING;
}

layout_page_header( lang_get( 'manage_projects_link' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_proj_page.php' );

# Project Menu Form BEGIN
?>

<div class="col-md-12 col-xs-12">
    <div class="space-10"></div>
	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-puzzle-piece"></i>
			<?php echo lang_get( 'projects_title' ) ?>
		</h4>
	</div>
	<div class="widget-body">
	<div class="widget-main no-padding">
	<div class="widget-toolbox padding-8 clearfix">
		<?php
		# Check the user's global access level before allowing project creation
		if( access_has_global_level ( config_get( 'create_project_threshold' ) ) ) {
			print_form_button( 'manage_proj_create_page.php', lang_get( 'create_new_project_link' ), null, null, 'btn btn-primary btn-white btn-round' );
		} ?>
	</div>
	<div class="table-responsive">
	<table class="table table-striped table-bordered table-condensed table-hover">
		<thead>
			<tr>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'name' ), 'name', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'name' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'status' ), 'status', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'status' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'enabled' ), 'enabled', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'enabled' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'view_status' ), 'view_state', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'view_state' ); ?>
				</th>
				<th><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'description' ), 'description', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'description' ); ?>
				</th>
			</tr>
		</thead>

		<tbody>
<?php
		# sorting by name is done inside the graph to mantain the hierarchy visualization
		# otherwise, a plain sort of the result will be made afterwards
		$t_sorted_hierarchy = $f_sort == 'name';
		if( $t_sorted_hierarchy ) {
			$t_graph_sort = ( $t_direction == DESCENDING ) ? ProjectGraph::SORT_NAME_DESC : ProjectGraph::SORT_NAME_ASC;
		} else {
			$t_graph_sort = null;
		}

		$t_graph = new ProjectGraph( array(
			'for_user' => auth_get_current_user_id(),
			'show_disabled' => true,
			'filter_threshold' => 'manage_project_threshold',
			'sort' => $t_graph_sort,
		) );
		# If the graph is sorted by name, the hierarchy can present duplicates for projects
		# that have multiple parents. When sorting by other fields, the hierarchy is not
		# explicitly displayed, so we don't want duplicated projects.
		$t_traverse_options = array(
			'duplicates' => $t_sorted_hierarchy,
			'include_all_projects' => false
			);
		$t_project_list = $t_graph->traverse( $t_traverse_options );

		foreach( $t_project_list as &$t_item ) {
			$t_item += project_get_row( $t_item['id'] );
		}
		unset( $t_item );

		if( !$t_sorted_hierarchy ) {
			$t_project_list = multi_sort( $t_project_list, $f_sort, $t_direction);
		}

		foreach( $t_project_list as $t_project ) {
			$t_padding = $t_sorted_hierarchy ? str_repeat( "&raquo; ", $t_project['level'] - 1 ) : '';
			?>
			<tr>
				<td>
					<a href="manage_proj_edit_page.php?project_id=<?php echo $t_project['id'] ?>"><?php echo $t_padding, string_display_line( $t_project['name'] ) ?></a>
				</td>
				<td><?php echo get_enum_element( 'project_status', $t_project['status'] ) ?></td>
				<td class="center"><?php echo trans_bool( $t_project['enabled'] ) ?></td>
				<td><?php echo get_enum_element( 'project_view_state', $t_project['view_state'] ) ?></td>
				<td><?php echo string_display_links( $t_project['description'] ) ?></td>
			</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>
	</div>
	</div>
	</div>

	<div class="space-10"></div>

	<div id="categories" class="form-container">

	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-sitemap"></i>
			<?php echo lang_get( 'global_categories' ) ?>
		</h4>
	</div>
	<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed table-hover">
<?php
		$t_categories = category_get_all_rows( ALL_PROJECTS );
		$t_can_update_global_cat = access_has_global_level( config_get( 'manage_site_threshold' ) );

		if( count( $t_categories ) > 0 ) {
?>
		<thead>
			<tr>
				<td><?php echo lang_get( 'category' ) ?></td>
				<td><?php echo lang_get( 'assign_to' ) ?></td>
				<?php if( $t_can_update_global_cat ) { ?>
				<td class="center"><?php echo lang_get( 'actions' ) ?></td>
				<?php } ?>
			</tr>
		</thead>

		<tbody>
<?php
			foreach( $t_categories as $t_category ) {
				$t_id = $t_category['id'];
?>
			<tr>
				<td><?php echo string_display_line( category_full_name( $t_id, false ) )  ?></td>
				<td><?php echo prepare_user_name( $t_category['user_id'] ) ?></td>
				<?php if( $t_can_update_global_cat ) { ?>
				<td class="center">
<?php
					$t_id = urlencode( $t_id );
					$t_project_id = urlencode( ALL_PROJECTS );
					echo '<div class="btn-group inline">';
					echo '<div class="pull-left">';
					print_form_button( "manage_proj_cat_edit_page.php?id=$t_id&project_id=$t_project_id", lang_get( 'edit_link' ) );
					echo '</div>';
					echo '<div class="pull-left">';
					print_form_button( "manage_proj_cat_delete.php?id=$t_id&project_id=$t_project_id", lang_get( 'delete_link' ) );
					echo '</div>';
?>
				</td>
			<?php } ?>
			</tr>
<?php
			} # end for loop
?>
		</tbody>
<?php
		} # end if
?>
	</table>
	</div>
	</div>

<?php if( $t_can_update_global_cat ) { ?>
	<form method="post" action="manage_proj_cat_add.php" class="form-inline">
		<div class="widget-toolbox padding-8 clearfix">
			<?php echo form_security_field( 'manage_proj_cat_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo ALL_PROJECTS ?>" />
			<input type="text" name="name" class="input-sm" size="32" maxlength="128" />
			<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add_category_button' ) ?>" />
			<input type="submit" name="add_and_edit_category" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'add_and_edit_category_button' ) ?>" />
		</div>
	</form>
<?php } ?>
</div>
</div>
</div>
<?php
echo '</div>';
layout_page_end();
