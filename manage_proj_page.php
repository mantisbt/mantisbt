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

html_page_top( lang_get( 'manage_projects_link' ) );

print_manage_menu( 'manage_proj_page.php' );

# Project Menu Form BEGIN
?>
<div class="form-container">
	<h2><?php echo lang_get( 'projects_title' ); ?></h2><?php

	# Check the user's global access level before allowing project creation
	if( access_has_global_level( config_get( 'create_project_threshold' ) ) ) {
		print_button( 'manage_proj_create_page.php', lang_get( 'create_new_project_link' ) );
	} ?>

	<table>
		<thead>
			<tr class="row-category">
				<td><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'name' ), 'name', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'name' ); ?>
				</td>
				<td><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'status' ), 'status', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'status' ); ?>
				</td>
				<td><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'enabled' ), 'enabled', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'enabled' ); ?>
				</td>
				<td><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'view_status' ), 'view_state', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'view_state' ); ?>
				</td>
				<td><?php
					print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'description' ), 'description', $t_direction, $f_sort );
					print_sort_icon( $t_direction, $f_sort, 'description' ); ?>
				</td>
			</tr>
		</thead>

		<tbody>
<?php
		$t_manage_project_threshold = config_get( 'manage_project_threshold' );
		$t_projects = user_get_accessible_projects( auth_get_current_user_id(), true );
		$t_full_projects = array();
		foreach ( $t_projects as $t_project_id ) {
			$t_full_projects[] = project_get_row( $t_project_id );
		}
		$t_projects = multi_sort( $t_full_projects, $f_sort, $t_direction );
		$t_stack = array( $t_projects );

		while( 0 < count( $t_stack ) ) {
			$t_projects = array_shift( $t_stack );

			if( 0 == count( $t_projects ) ) {
				continue;
			}

			$t_project = array_shift( $t_projects );
			$t_project_id = $t_project['id'];
			$t_level      = count( $t_stack );

			# only print row if user has project management privileges
			if( access_has_project_level( $t_manage_project_threshold, $t_project_id, auth_get_current_user_id() ) ) { ?>
			<tr>
				<td>
					<a href="manage_proj_edit_page.php?project_id=<?php echo $t_project['id'] ?>"><?php echo str_repeat( '&raquo; ', $t_level ) . string_display( $t_project['name'] ) ?></a>
				</td>
				<td><?php echo get_enum_element( 'project_status', $t_project['status'] ) ?></td>
				<td><?php echo trans_bool( $t_project['enabled'] ) ?></td>
				<td><?php echo get_enum_element( 'project_view_state', $t_project['view_state'] ) ?></td>
				<td><?php echo string_display_links( $t_project['description'] ) ?></td>
			</tr><?php
			}
			$t_subprojects = project_hierarchy_get_subprojects( $t_project_id, true );

			if( 0 < count( $t_projects ) || 0 < count( $t_subprojects ) ) {
				array_unshift( $t_stack, $t_projects );
			}

			if( 0 < count( $t_subprojects ) ) {
				$t_full_projects = array();
				foreach ( $t_subprojects as $t_project_id ) {
					$t_full_projects[] = project_get_row( $t_project_id );
				}
				$t_subprojects = multi_sort( $t_full_projects, $f_sort, $t_direction );
				array_unshift( $t_stack, $t_subprojects );
			}
		} ?>
		</tbody>
	</table>
</div>

<div id="categories" class="form-container">
	<h2><?php echo lang_get( 'global_categories' ) ?></h2>

	<table>
<?php
		$t_categories = category_get_all_rows( ALL_PROJECTS );
		$t_can_update_global_cat = access_has_global_level( config_get( 'manage_site_threshold' ) );

		if( count( $t_categories ) > 0 ) {
?>
		<thead>
			<tr class="row-category">
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
				<td><?php echo string_display( category_full_name( $t_id, false ) )  ?></td>
				<td><?php echo prepare_user_name( $t_category['user_id'] ) ?></td>
				<?php if( $t_can_update_global_cat ) { ?>
				<td class="center">
<?php
					$t_id = urlencode( $t_id );
					$t_project_id = urlencode( ALL_PROJECTS );

					print_button( 'manage_proj_cat_edit_page.php?id=' . $t_id . '&project_id=' . $t_project_id, lang_get( 'edit_link' ) );
					echo '&#160;';
					print_button( 'manage_proj_cat_delete.php?id=' . $t_id . '&project_id=' . $t_project_id, lang_get( 'delete_link' ) );
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

<?php if( $t_can_update_global_cat ) { ?>
	<form method="post" action="manage_proj_cat_add.php">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_cat_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo ALL_PROJECTS ?>" />
			<input type="text" name="name" size="32" maxlength="128" />
			<input type="submit" name="add_category" class="button" value="<?php echo lang_get( 'add_category_button' ) ?>" />
			<input type="submit" name="add_and_edit_category" class="button" value="<?php echo lang_get( 'add_and_edit_category_button' ) ?>" />
		</fieldset>
	</form>
<?php } ?>
</div>

<?php
html_page_bottom();
