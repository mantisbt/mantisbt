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
 * Edit Project Page
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
 * @uses current_user_api.php
 * @uses custom_field_api.php
 * @uses date_api.php
 * @uses event_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses project_hierarchy_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'date_api.php' );
require_api( 'event_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'project_hierarchy_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

auth_reauthenticate();

$f_project_id = gpc_get_int( 'project_id' );
$f_show_global_users = gpc_get_bool( 'show_global_users' );

project_ensure_exists( $f_project_id );
$g_project_override = $f_project_id;
access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

$t_row = project_get_row( $f_project_id );

$t_can_manage_users = access_has_project_level( config_get( 'project_user_threshold' ), $f_project_id );

require_js( 'manage_proj_edit_page.js' );

layout_page_header( project_get_field( $f_project_id, 'name' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_proj_edit_page.php' );
?>

<!-- PROJECT PROPERTIES -->
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div id="manage-proj-update-div" class="form-container">
	<form id="manage-proj-update-form" method="post" action="manage_proj_update.php">
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<?php print_icon( 'fa-puzzle-piece', 'ace-icon' ); ?>
	<?php echo lang_get('edit_project_title') ?>
</h4>
</div>

<div class="widget-body">
<div class="widget-main no-padding">
	<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_update' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<tr>
				<td class="category">
					<label for="project-name">
						<span class="required">*</span>
						<?php echo lang_get( 'project_name' ) ?>
					</label>
				</td>
				<td>
					<input type="text" id="project-name" name="name" required
						   class="input-sm" size="60" maxlength="128"
						   value="<?php echo string_attribute( $t_row['name'] ) ?>"
					/>
				</td>
			</tr>
			<tr>
				<td class="category">
					<label for="project-status">
						<?php echo lang_get( 'status' ) ?>
					</label>
				</td>
				<td>
					<select id="project-status" name="status" class="input-sm">
						<?php print_enum_string_option_list( 'project_status', (int)$t_row['status'] ) ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="category">
					<label for="project-enabled">
						<?php echo lang_get( 'enabled' ) ?>
					</label>
				</td>
				<td>
					<input type="checkbox" id="project-enabled" name="enabled" class="ace"
						<?php check_checked( (int)$t_row['enabled'], ON ); ?>
					/>
					<span class="lbl"></span>
				</td>
			</tr>
			<tr>
				<td class="category">
					<label for="project-inherit-global">
						<?php echo lang_get( 'inherit_global' ) ?>
					</label>
				</td>
				<td>
					<input type="checkbox" id="project-inherit-global" name="inherit_global" class="ace"
						<?php check_checked( (int)$t_row['inherit_global'], ON ); ?>
					/>
					<span class="lbl"></span>
				</td>
			</tr>

			<tr>
				<td class="category">
					<label for="project-view-state">
						<?php echo lang_get( 'view_status' ) ?>
					</label>
				</td>
				<td>
					<select id="project-view-state" name="view_state" class="input-sm">
						<?php print_enum_string_option_list( 'project_view_state', (int)$t_row['view_state']) ?>
					</select>
				</td>
			</tr>
			<?php
			$g_project_override = $f_project_id;
			if( file_is_uploading_enabled() && DATABASE !== config_get( 'file_upload_method' ) ) {
				$t_file_path = $t_row['file_path'];
				# Don't reveal the absolute path to non-administrators for security reasons
				if( is_blank( $t_file_path ) && current_user_is_administrator() ) {
					$t_file_path = config_get_global( 'absolute_path_default_upload_folder' );
				}
				?>
				<tr>
					<td class="category">
						<label for="project-file-path">
							<?php echo lang_get( 'upload_file_path' ) ?>
						</label>
					</td>
					<td>
						<input type="text" id="project-file-path" name="file_path"
							   class="input-sm" size="60" maxlength="250"
							   value="<?php echo string_attribute( $t_file_path ) ?>"
						/>
					</td>
				</tr><?php
			} ?>
			<tr>
				<td class="category">
					<label for="project-description">
						<?php echo lang_get( 'description' ) ?>
					</label>
				</td>
				<td>
					<?php # Newline after opening textarea tag is intentional, see #25839 ?>
					<textarea class="form-control" id="project-description" name="description" cols="70" rows="5">
<?php echo string_textarea( $t_row['description'] ) ?>
</textarea>
				</td>
			</tr>

			<?php event_signal( 'EVENT_MANAGE_PROJECT_UPDATE_FORM', array( $f_project_id ) ); ?>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
			<input type="submit" class="btn btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'update_project_button' ) ?>"
			/>
		</div>
	</div>
	</form>
</div>
</div>

<!-- PROJECT DELETE -->
<div class="col-md-12 col-xs-12">
<?php
# You must have global permissions to delete projects
if( access_has_global_level ( config_get( 'delete_project_threshold' ) ) ) { ?>
<div id="project-delete-div" class="form-container">
	<form id="project-delete-form" method="post" action="manage_proj_delete.php" class="pull-right">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_delete' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<input type="submit" class="btn btn-primary btn-sm btn-white btn-round"
				   value="<?php echo lang_get( 'delete_project_button' ) ?>"
			/>
		</fieldset>
	</form>
</div>
<?php } ?>
</div>

<?php
if ( config_get_global( 'subprojects_enabled') == ON ) {
?>
<!-- SUBPROJECTS -->
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div id="manage-project-update-subprojects-div" class="form-container">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-share-alt', 'ace-icon' ); ?>
					<?php echo lang_get( 'subprojects' ); ?>
				</h4>
			</div>
			<div class="widget-toolbox padding-8 clearfix">
		<?php
		# Check the user's global access level before allowing project creation
		if( access_has_global_level ( config_get( 'create_project_threshold' ) ) ) {
			print_form_button(
				'manage_proj_create_page.php?parent_id=' . $f_project_id,
				lang_get( 'create_new_subproject_link' ),
				null,
				null,
				'btn btn-sm btn-primary btn-white btn-round'
			);
		} ?>
	</div>
		<form id="manage-project-subproject-add-form" method="post" action="manage_proj_subproj_add.php" class="form-inline">
			<div class="widget-body">
			<div class="widget-main">
			<fieldset>
				<?php echo form_security_field( 'manage_proj_subproj_add' ) ?>
				<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
				<!--suppress HtmlFormInputWithoutLabel -->
				<select name="subproject_id" class="input-sm" required>
					<option selected disabled value="">
						<?php echo '[', lang_get( 'select_project_button' ), ']' ?>
					</option>
<?php
	$t_all_subprojects = project_hierarchy_get_subprojects( $f_project_id, true );
	$t_all_subprojects[] = $f_project_id;
	$t_manage_access = config_get( 'manage_project_threshold' );
	$t_projects = project_get_all_rows();
	$t_projects = multi_sort( $t_projects, 'name' );
	foreach ( $t_projects as $t_project ) {
		if( in_array( $t_project['id'], $t_all_subprojects ) ||
			in_array( $f_project_id, project_hierarchy_get_all_subprojects( $t_project['id'] ) ) ||
			!access_has_project_level( $t_manage_access, $t_project['id'] )
		) {
			continue;
		}
?>
					<option value="<?php echo $t_project['id'] ?>">
						<?php echo string_attribute( $t_project['name'] ) ?>
					</option>
<?php
	} # End looping over projects
?>
				</select>
				<input type="submit" class="btn btn-sm btn-primary btn-white btn-round"
					   value="<?php echo lang_get( 'add_subproject' ); ?>"
				/>
			</fieldset>
			</div>
			</div>
		</form>
		</div>
	</div>
</div>

<?php
	$t_subproject_ids = current_user_get_accessible_subprojects( $f_project_id, true );
	if( array() != $t_subproject_ids ) { ?>
	<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form id="manage-project-update-subprojects-form" action="manage_proj_update_children.php" method="post">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-share-alt', 'ace-icon' ); ?>
				<?php echo lang_get( 'subprojects' ); ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_update_children' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-condensed">
				<thead>
					<tr>
						<th><?php echo lang_get( 'name' ) ?></th>
						<th><?php echo lang_get( 'status' ) ?></th>
						<th><?php echo lang_get( 'enabled' ) ?></th>
						<th><?php echo lang_get( 'inherit' ) ?></th>
						<th><?php echo lang_get( 'view_status' ) ?></th>
						<th><?php echo lang_get( 'description' ) ?></th>
						<th colspan="2"><?php echo lang_get( 'actions' ) ?></th>
					</tr>
				</thead>
				<tbody>
<?php
		foreach ( $t_subproject_ids as $t_subproject_id ) {
			$t_subproject = project_get_row( $t_subproject_id );
			$t_inherit_parent = project_hierarchy_inherit_parent( $t_subproject_id, $f_project_id, true ); ?>
					<tr>
						<td>
							<a href="manage_proj_edit_page.php?project_id=<?php echo $t_subproject['id'] ?>">
								<?php echo string_display_line( $t_subproject['name'] ) ?>
							</a>
						</td>
						<td class="center">
							<?php echo get_enum_element( 'project_status', $t_subproject['status'] ) ?>
						</td>
						<td class="center">
							<?php echo trans_bool( $t_subproject['enabled'] ) ?>
						</td>
						<td class="center">
						<label>
							<input type="checkbox" class="ace" name="inherit_child_<?php echo $t_subproject_id ?>"
								<?php echo ( $t_inherit_parent ? 'checked="checked"' : '' ) ?>  />
								<span class="lbl"></span>
						</label>
						</td>
						<td class="center">
							<?php echo get_enum_element( 'project_view_state', $t_subproject['view_state'] ) ?>
						</td>
						<td>
							<?php echo string_display_links( $t_subproject['description'] ) ?>
						</td>
						<td class="center">
							<div class="inline">
<?php
	print_link_button(
		'manage_proj_edit_page.php?project_id=' . $t_subproject['id'],
		lang_get( 'edit' ), 'btn-xs'
	);
	print_link_button(
		"manage_proj_subproj_delete.php?project_id=$f_project_id&subproject_id="
		. $t_subproject['id']
		. form_security_param( 'manage_proj_subproj_delete' ),
		lang_get( 'unlink_link' ), 'btn-xs'
	);
?>
							</div>
						</td>
					</tr>
<?php
		} # End of foreach loop over subprojects
?>
				</tbody>
			</table>
		</div>
		</fieldset>
		</div>
		</div>
			<div class="widget-toolbox padding-8 clearfix">
				<input type="submit" class="btn btn-primary btn-white btn-round"
					   value="<?php echo lang_get( 'update_subproject_inheritance' ) ?>"
				/>
			</div>
		</div>
	</form>
</div>
<?php
		# End of subprojects listing / update form
	} else {
		# If there are no subprojects, clear floats to h2 overlap on div border
?>
		<br />
<?php }

	} # are sub-projects enabled?
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div id="categories" class="form-container">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-sitemap', 'ace-icon' ); ?>
				<?php echo lang_get( 'categories' ); ?>
			</h4>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
	<form id="manage-project-category-copy-form" method="post" action="manage_proj_cat_copy.php" class="form-inline">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_cat_copy' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<!--suppress HtmlFormInputWithoutLabel -->
			<select name="other_project_id" class="input-sm" required>
				<option selected disabled value="">
					<?php echo '[', lang_get( 'select_project_button' ), ']' ?>
				</option>
				<?php print_project_option_list( null, false, $f_project_id ); ?>
			</select>
			<input type="submit" name="copy_from" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'copy_categories_from' ) ?>"
			/>
			<input type="submit" name="copy_to" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'copy_categories_to' ) ?>"
			/>
		</fieldset>
	</form>
	</div>
		<div class="widget-body">
			<div class="widget-main no-padding">
<?php
	$t_categories = category_get_all_rows( $f_project_id );
	if( count( $t_categories ) > 0 ) {
?>
	<div class="table-responsive">
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th><?php echo lang_get( 'category' ) ?></th>
				<th><?php echo lang_get( 'assign_to' ) ?></th>
				<th colspan="2" class="center"><?php echo lang_get( 'actions' ) ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach ( $t_categories as $t_category ) {
			$t_id = $t_category['id'];
			$t_inherited = ( $t_category['project_id'] != $f_project_id );
?>
			<tr>
				<td><?php echo string_display_line( category_full_name( $t_id, $t_inherited, $f_project_id ) )  ?></td>
				<td><?php echo prepare_user_name( $t_category['user_id'] ) ?></td>
				<td class="center">
					<div class="inline">
<?php
			if( !$t_inherited ) {
				$t_id = urlencode( $t_id );
				$t_project_id = urlencode( $f_project_id );
				echo '<div class="pull-left">';
				print_form_button(
					'manage_proj_cat_edit_page.php?id=' . $t_id . '&project_id=' . $t_project_id,
					lang_get( 'edit' ),
					null,
					null,
					'btn btn-xs btn-primary btn-white btn-round'
				);
				echo '</div>';
			}
			if( !$t_inherited ) {
				echo '<div class="pull-left">';
				print_form_button(
					'manage_proj_cat_delete.php?id=' . $t_id . '&project_id=' . $t_project_id,
					lang_get( 'delete' ),
					null,
					null,
					'btn btn-xs btn-primary btn-white btn-round'
				);
				echo '</div>';
			}
?>
					</div>
				</td>
			</tr>
<?php
		} # end for loop
?>
		</tbody>
	</table>
	</div>
<?php
	}
?>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<form id="project-add-category-form" method="post" action="manage_proj_cat_add.php" class="form-inline">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_cat_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<!--suppress HtmlFormInputWithoutLabel -->
			<input type="text" name="name" required size="32" maxlength="128" class="input-sm" />
			<input type="submit" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'add_category_button' ) ?>"
			/>
			<input type="submit" name="add_and_edit_category"
				   class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'add_and_edit_category_button' ) ?>"
			/>
		</fieldset>
	</form>
</div>
</div>
</div>
</div>


<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div id="project-versions-div" class="form-container">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-share-alt', 'ace-icon' ); ?>
				<?php echo lang_get( 'versions' ); ?>
			</h4>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
	<form id="manage-project-version-copy-form" method="post" action="manage_proj_ver_copy.php" class="form-inline">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_ver_copy' ) ?>
			<input type="hidden" class="form-control input-sm" name="project_id" value="<?php echo $f_project_id ?>" />
			<!--suppress HtmlFormInputWithoutLabel -->
			<select name="other_project_id" class="input-sm" required>
				<option selected disabled value=""><?php echo '[', lang_get( 'select_project_button' ), ']' ?></option>
				<?php print_project_option_list( null, false, $f_project_id ); ?>
			</select>
			<input type="submit" name="copy_from" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'copy_versions_from' ) ?>"
			/>
			<input type="submit" name="copy_to" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'copy_versions_to' ) ?>"
			/>
		</fieldset>
	</form>
	</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
	<?php
	$t_versions = version_get_all_rows( $f_project_id, VERSION_ALL, true );
	if( count( $t_versions ) > 0 ) { ?>
	<div class="table-responsive">
		<table id="versions" class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th><?php echo lang_get( 'version' ) ?></th>
				<th><?php echo lang_get( 'released' ) ?></th>
				<th><?php echo lang_get( 'obsolete' ) ?></th>
				<th><?php echo lang_get( 'timestamp' ) ?></th>
				<th><?php echo lang_get( 'actions' ) ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach ( $t_versions as $t_version ) {
			$t_inherited = $t_version['project_id'] != $f_project_id;
			$t_name = version_full_name( $t_version['id'], $t_inherited, $f_project_id );
			$t_released = $t_version['released'];
			$t_obsolete = $t_version['obsolete'];
			if( !date_is_null( $t_version['date_order'] ) ) {
				$t_date_formatted = date( config_get( 'complete_date_format' ), $t_version['date_order'] );
			} else {
				$t_date_formatted = ' ';
			} ?>

			<tr>
				<td><?php echo string_display_line( $t_name ) ?></td>
				<td class="center"><?php echo trans_bool( $t_released ) ?></td>
				<td class="center"><?php echo trans_bool( $t_obsolete ) ?></td>
				<td class="center"><?php echo $t_date_formatted ?></td>
				<td class="center">
					<div class="inline">
					<?php
					$t_version_id = version_get_id( $t_name, $f_project_id );
					if( !$t_inherited ) {
						echo '<div class="pull-left">';
						print_form_button(
							'manage_proj_ver_edit_page.php?version_id=' . $t_version_id,
							lang_get( 'edit' )
						);
						echo '</div>';

						echo '<div class="pull-left">';
						print_form_button(
							'manage_proj_ver_delete.php?version_id=' . $t_version_id,
							lang_get( 'delete' )
						);
						echo '</div>';
					} ?>
					</div>
				</td>
			</tr>
<?php
		} # end for loop
?>
		</tbody>
	</table>
	</div>
<?php
	}
?>
	</div>
	</div>
	<div class="widget-toolbox padding-8 clearfix">
	<form id="manage-project-add-version-form" method="post" action="manage_proj_ver_add.php" class="form-inline">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_ver_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<!--suppress HtmlFormInputWithoutLabel -->
			<input type="text" class="input-sm" required name="version" size="32" maxlength="64" />
			<input type="submit" name="add_version" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'add_version_button' ) ?>"
			/>
			<input type="submit" name="add_and_edit_version" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'add_and_edit_version_button' ) ?>"
			/>
		</fieldset>
	</form>
	</div>
	</div>
</div>
</div>

<?php
# You need either global permissions or project-specific permissions to link
#  custom fields
$t_custom_field_count = count( custom_field_get_ids() );
if( access_has_project_level( config_get( 'custom_field_link_threshold' ), $f_project_id ) &&
	( $t_custom_field_count > 0 ) ) {
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div id="customfields" class="form-container">
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-flask', 'ace-icon' ); ?>
			<?php echo lang_get( 'custom_fields_setup' ); ?>
		</h4>
	</div>
	<div class="widget-toolbox padding-8 clearfix">
	<form id="manage-project-custom-field-copy-form" method="post" action="manage_proj_custom_field_copy.php" class="form-inline">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_custom_field_copy' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<!--suppress HtmlFormInputWithoutLabel -->
			<select name="other_project_id" class="input-sm" required>
				<option selected disabled value=""><?php echo '[', lang_get( 'select_project_button' ), ']' ?></option>
				<?php print_project_option_list( null, false, $f_project_id ); ?>
			</select>
			<input type="submit" name="copy_from" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'copy_from' ) ?>"
			/>
			<input type="submit" name="copy_to" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'copy_to' ) ?>"
			/>
		</fieldset>
	</form>
	</div>
<?php
	$t_custom_fields = custom_field_get_linked_ids( $f_project_id );
	$t_linked_count = count( $t_custom_fields );
	if( $t_linked_count > 0 ) { ?>
	<div class="widget-body">
		<div class="widget-main no-padding">
			<div class="table-responsive">
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th><?php echo lang_get( 'custom_field' ) ?></th>
				<th><?php echo lang_get( 'custom_field_sequence' ) ?></th>
				<th><?php echo lang_get( 'actions' ); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
		foreach( $t_custom_fields as $t_field_id ) {
			$t_desc = custom_field_get_definition( $t_field_id ); ?>
			<tr>
				<td><?php echo '<a href="manage_custom_field_edit_page.php?field_id=' . $t_field_id . '">' .
						custom_field_get_display_name( $t_desc['name'] ) . '</a>' ?></td>
				<td class="center">
					<form method="post" action="manage_proj_custom_field_update.php" class="form-inline">
						<fieldset>
							<?php echo form_security_field( 'manage_proj_custom_field_update' ) ?>
							<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
							<input type="hidden" name="field_id" value="<?php echo $t_field_id ?>" />
							<!--suppress HtmlFormInputWithoutLabel -->
							<input type="text" class="input-sm" name="sequence" size="2"
								   value="<?php echo custom_field_get_sequence( $t_field_id, $f_project_id ) ?>"
							/>
							<input type="submit" class="btn btn-sm btn-primary btn-white btn-round"
								   value="<?php echo lang_get( 'update' ) ?>"
							/>
						</fieldset>
					</form>
				</td>
				<td class="center"><?php
					# You need global permissions to edit custom field defs
					print_form_button(
						"manage_proj_custom_field_remove.php?field_id=$t_field_id&project_id=$f_project_id",
						lang_get( 'remove_link' )
					); ?>
				</td>
			</tr>
<?php
		} # end for loop
?>
		</tbody>
	</table>
	</div>
	</div>
	</div>
<?php
	}
	if( $t_custom_field_count > $t_linked_count ) { ?>
	<div class="widget-toolbox padding-8 clearfix">
	<form method="post" action="manage_proj_custom_field_add_existing.php" class="form-inline">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_custom_field_add_existing' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<!--suppress HtmlFormInputWithoutLabel -->
			<select name="field_id" class="input-sm">
				<?php
					$t_cf_defs = array();
					foreach( custom_field_get_ids() as $t_cfid ) {
						$t_cf_defs[] = custom_field_get_definition( $t_cfid );
					}
					$t_custom_fields = multi_sort( $t_cf_defs, 'name' );

					foreach( $t_custom_fields as $t_field )
					{
						if( !custom_field_is_linked( $t_field['id'], $f_project_id ) ) {
							echo '<option value="', $t_field['id'], '">',
								string_attribute( lang_get_defaulted( $t_field['name'] ) ),
								'</option>' ;
						}
					}
				?>
			</select>
			<input type="submit" class="btn btn-sm btn-primary btn-white btn-round"
				   value="<?php echo lang_get( 'add_existing_custom_field' ) ?>"
			/>
		</fieldset>
	</form>
</div><?php
	} ?>
</div>
</div>
</div><?php
}

event_signal( 'EVENT_MANAGE_PROJECT_PAGE', array( $f_project_id ) );
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="alert alert-info">
		<div class="center bigger-110">
	<?php
	print_icon( 'fa-info-circle' );
	echo ' ';
	if( VS_PUBLIC == project_get_field( $f_project_id, 'view_state' ) ) {
		echo lang_get( 'public_project_msg' );
	} else {
		echo lang_get( 'private_project_msg' );
	} ?>
	</div>
	</div>
</div>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div id="manage-project-users-div" class="form-container">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-users', 'ace-icon' ); ?>
					<?php echo lang_get( 'manage_accounts_title' ); ?>
				</h4>
			</div>
			<div class="widget-body" id="manage-project-users-list">

				<div class="widget-toolbox padding-8 clearfix">
					<form id="manage-project-users-copy-form" method="post" action="manage_proj_user_copy.php" class="form-inline">
						<fieldset>
							<?php echo form_security_field( 'manage_proj_user_copy' ) ?>
							<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
							<!--suppress HtmlFormInputWithoutLabel -->
							<select name="other_project_id" class="input-sm" required>
								<option selected disabled value="">
									<?php echo '[', lang_get( 'select_project_button' ), ']' ?>
								</option>
								<?php print_project_option_list( null, false, $f_project_id ); ?>
							</select>
							<span class=form-inline">
								<input type="submit" name="copy_from" class="btn btn-sm btn-primary btn-white btn-round"
									   value="<?php echo lang_get( 'copy_users_from' ) ?>"
								/>
								<input type="submit" name="copy_to" class="btn btn-sm btn-primary btn-white btn-round"
									   value="<?php echo lang_get( 'copy_users_to' ) ?>"
								/>
							</span>
						</fieldset>
					</form>
				</div>
	<?php
	$t_users = project_get_all_user_rows( $f_project_id, ANYBODY, $f_show_global_users );
	$t_users_count = count( $t_users );

	if( $t_users_count > 0 ) {

		$t_user_ids = array();
		$t_sort = array();
		foreach ( $t_users as $t_ix => $t_user ) {
			$t_user_display_name = user_get_name_from_row( $t_user );
			$t_users[$t_ix]['display_name'] = $t_user_display_name;
			$t_user_ids[] = $t_user['id'];
			$t_sort[] = $t_user_display_name;
		}

		user_cache_array_rows( $t_user_ids );
		array_multisort( $t_sort, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $t_users );

		?>
				<div id="manage-project-users-form-toolbox" class="hidden widget-toolbox padding-8 clearfix">
					<div class="btn-toolbar">
						<div class="widget-toolbar no-border pull-left">
							<!--suppress HtmlFormInputWithoutLabel -->
							<input type="text" class="search input-sm" placeholder="<?php echo lang_get( 'filter_button' ) ?>" />
						</div>
						<div class="widget-toolbar pull-left">
							<label>
								<?php echo lang_get( 'show' ) ?>
								<input id="input-per-page" type="text" min="5" size="2" class="input-sm"
									   value="<?php echo config_get( 'default_limit_view' ) ?>"
								/>
							</label>
							<?php echo '(', lang_get( 'total' ), ': ',
								$t_users_count, ' ', lang_get( 'users_link' ), ')'
							?>
						</div>
						<div class="btn-group pull-right">
							<ul class="pagination small no-margin"></ul>
						</div>
					</div>
				</div>

				<div class="widget-main no-padding" >
					<form id="manage-project-users-form" method="post" action="manage_proj_user_update.php">
						<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
						<?php echo form_security_field( 'manage_proj_user_update' ) ?>
						<div class="table-responsive listjs-table">
							<table class="table table-striped table-bordered table-condensed">
								<thead>
									<tr>
										<th>
											<div class="sort" role="button" data-sort="key-name">
												<?php echo lang_get( 'username' ) ?>
											</div>
										</th>
										<th>
											<div class="sort" role="button" data-sort="key-email">
												<?php echo lang_get( 'email' ) ?>
											</div>
										</th>
										<th class="col-md-4">
											<div class="sort" role="button" data-sort="key-access">
												<?php echo lang_get( 'access_level' ) ?>
											</div>
										</th>
										<th>
											<?php echo lang_get( 'remove_link' ) ?>
										</th>
									</tr>
								</thead>
								<tbody class="list">
<?php
		# If including global users, fetch here all local user to later distinguish them
		$t_local_users = array();
		if( $f_show_global_users ) {
			$t_local_users = project_get_all_user_rows( $f_project_id, ANYBODY, false );
		}

		foreach( $t_users as $t_user ) {
			$t_username =  $t_user['display_name'];
			$t_can_manage_this_user = $t_can_manage_users
					&& access_has_project_level( $t_user['access_level'], $f_project_id )
					&& ( !$f_show_global_users || isset( $t_local_users[$t_user['id']]) );
?>
		<tr>
			<td class="key-name" data-sortvalue="<?php echo $t_username ?>">
				<a href="manage_user_edit_page.php?user_id=<?php echo $t_user['id'] ?>">
				<?php echo prepare_user_name( $t_user['id'], false ); ?>
				</a>
			</td>
			<td class="key-email">
			<?php
				$t_email = user_get_email( $t_user['id'] );
				print_email_link( $t_email, $t_email );
			?>
			</td>
			<?php
			$t_current_level_string = get_enum_element( 'access_levels', $t_user['access_level'] );
			?>
			<td class="key-access" data-sortvalue="<?php echo $t_current_level_string ?>">
				<?php
				if( $t_can_manage_this_user ) {
					echo '<div class="editable_access_level">';
					echo $t_current_level_string . '<span class="hidden unchanged">';
					echo ' <a href="#" class="edit_link">[' . lang_get( 'edit' ) . ']</a>';
					echo '</span>';
					$t_arrow = layout_is_rtl() ? 'fa-long-arrow-left' : 'fa-long-arrow-right';
					echo ' <span class="changed_to">';
					print_icon( $t_arrow, 'fa-lg' );
					echo '</span>';
					echo '<select name="user_access_level[' . $t_user['id'] . ']" class="input-xs user_access_level"'
							. ' data-original_val="' . $t_user['access_level'] . '" data-user_id="' . $t_user['id'] . '">';
					# only access levels that are less than or equal current user access level for current project
					print_project_access_levels_option_list( (int)$t_user['access_level'], $f_project_id );
					echo '</select>';
					echo '</div>';
				} else {
					echo $t_current_level_string;
				}
				?>
			</td>
			<td class="center">
				<?php
				# You need global or project-specific permissions to remove users
				#  from this project
				if( $t_can_manage_this_user ) {
					?>
					<div class="checkbox no-padding no-margin editable_user_delete">
						<label>
							<input type="checkbox" name="user_access_delete[]"
								   class="ace user_access_delete"
								   value="<?php echo $t_user['id'] ?>"
							/>
							<span class="lbl"></span>
						</label>
					</div>
					<?php
				}
				?>
			</td>
		</tr>
		<?php
		}  # end for
		?>
								</tbody>
							</table>
						</div>

						<div class="widget-toolbox padding-8 clearfix">
							<div class="form-inline pull-left">
								<input type="submit" name="submit-apply"
									   class="btn btn-primary btn-white btn-round"
									   value="<?php echo lang_get( 'apply_changes' ) ?>"
								/>
							</div>
							<div class="form-inline pull-right">
								<?php echo form_security_field( 'manage_proj_user_remove' ) ?>
								<input type="submit" name="btn-remove-all"
									   class="btn btn-primary btn-white btn-round btn-xs"
									   formaction="manage_proj_user_remove.php"
									   value="<?php echo lang_get( 'remove_all_link' ) ?>"
								/>
								<button name="btn-undo-remove-all" class="hidden btn btn-primary btn-white btn-round btn-xs">
									<?php echo lang_get( 'undo' ). ': ', lang_get( 'remove_all_link' ) ?>
								</button>
							</div>
						</div>
					</form>
				</div>
<?php
	} // end if user count > 0
?>
			</div>
			<div class="widget-toolbox padding-8 clearfix">
<?php
	# You need global or project-specific permissions to remove users
	#  from this project
	if( !$f_show_global_users ) {
		print_form_button(
			"manage_proj_edit_page.php?project_id=$f_project_id&show_global_users=true",
			lang_get( 'show_global_users' ),
			null,
			OFF,
			'btn btn-sm btn-primary btn-white btn-round'
		);
	} else {
		print_form_button(
			"manage_proj_edit_page.php?project_id=$f_project_id",
			lang_get( 'hide_global_users' ),
			null,
			OFF,
			'btn btn-sm btn-primary btn-white btn-round'
		);
	}
?>
			</div>
		</div>
	</div>
</div>

<?php
# We want to allow people with global permissions and people with high enough
#  permissions on the project we are editing
if( $t_can_manage_users ) {
$t_users = user_get_unassigned_by_project_id( $f_project_id );
if( count( $t_users ) > 0 ) { ?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<div class="form-container">
	<form id="manage-project-add-user-form" method="post" action="manage_proj_user_add.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-user', 'ace-icon' ); ?>
				<?php echo lang_get( 'add_user_title' ); ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php echo form_security_field( 'manage_proj_user_add' ) ?>
			<input type="hidden" name="project_id" value="<?php echo $f_project_id ?>" />
			<tr>
				<td class="category">
					<label for="project-add-users-username">
						<span class="required">*</span>
						<?php echo lang_get( 'username' ) ?>
					</label>
				</td>
				<td>
					<select id="project-add-users-username" name="user_id[]"
							class="input-sm" multiple="multiple" size="10" required>
<?php
						foreach( $t_users AS $t_user_id=>$t_display_name ) {
							echo '<option value="', $t_user_id, '">', string_attribute( $t_display_name ), '</option>';
						}
?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="category">
					<label for="project-add-users-access-level">
						<?php echo lang_get( 'access_level' ) ?>
					</label>
				</td>
				<td>
					<select id="project-add-users-access-level" name="access_level" class="input-sm">
<?php
						# only access levels that are less than or equal current user access level for current project
						print_project_access_levels_option_list(
							config_get( 'default_new_account_access_level' ),
							$f_project_id
						);
?>
					</select>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
			<div class="widget-toolbox padding-8 clearfix">
				<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
				<input type="submit" class="btn btn-primary btn-white btn-round"
					   value="<?php echo lang_get( 'add_user_button' ) ?>"
				/>
			</div>
		</div>
	</form>
	</div>
</div>
<?php
	}
}
?>

<?php
layout_page_end();
