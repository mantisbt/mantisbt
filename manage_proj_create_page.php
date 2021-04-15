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
 * Create a project
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses current_user_api.php
 * @uses event_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'current_user_api.php' );
require_api( 'event_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );

auth_reauthenticate();

access_ensure_global_level( config_get( 'create_project_threshold' ) );

layout_page_header();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_proj_page.php' );

$f_parent_id = gpc_get( 'parent_id', null );

?>

	<div class="col-md-12 col-xs-12">
		<div class="space-10"></div>

<?php if( project_table_empty() ) { ?>
	<div class="alert alert-sm alert-warning" role="alert">
		<?php print_icon( 'fa-warning', 'ace-icon fa-lg' ); ?>
		<?php echo lang_get( 'create_first_project' ) ?>
	</div>
<?php } ?>

	<div id="manage-project-create-div" class="form-container">
	<form method="post" id="manage-project-create-form" action="manage_proj_create.php">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-puzzle-piece', 'ace-icon' ); ?>
				<?php
				if( null !== $f_parent_id ) {
					echo lang_get( 'add_subproject_title' );
				} else {
					echo lang_get( 'add_project_title' );
				} ?>
			</h4>
		</div>
		<div class="widget-body">
		<div class="widget-main no-padding">
		<div class="table-responsive">
		<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php
			echo form_security_field( 'manage_proj_create' );
			if( null !== $f_parent_id ) {
				$f_parent_id = (int) $f_parent_id; ?>
				<input type="hidden" name="parent_id" value="<?php echo $f_parent_id ?>" /><?php
			} ?>

			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'project_name' ) ?>
				</td>
				<td>
					<input type="text" id="project-name" name="name" class="input-sm" size="60" maxlength="128" required />
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'status' ) ?>
				</td>
				<td>
					<select id="project-status" name="status" class="input-sm">
						<?php print_enum_string_option_list( 'project_status' ) ?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'inherit_global' ) ?>
				</td>
				<td>
					<label>
						<input type="checkbox" class="ace" id="project-inherit-global" name="inherit_global" checked="checked">
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<?php if( !is_null( $f_parent_id ) ) { ?>
				<tr>
					<td class="category">
						<?php echo lang_get( 'inherit_parent' ) ?>
					</td>
					<td>
						<label>
							<input type="checkbox" class="ace" id="project-inherit-parent" name="inherit_parent" checked="checked">
							<span class="lbl"></span>
						</label>
					</td>
				</tr>
			<?php
			} ?>

			<tr>
				<td class="category">
					<?php echo lang_get( 'view_status' ) ?>
				</td>
				<td>
					<select id="project-view-state" name="view_state" class="input-sm">
						<?php print_enum_string_option_list( 'project_view_state', config_get( 'default_project_view_status', null, ALL_USERS, ALL_PROJECTS ) ) ?>
					</select>
				</td>
			</tr>

			<?php

			$g_project_override = ALL_PROJECTS;
			if( file_is_uploading_enabled() && DATABASE !== config_get( 'file_upload_method' ) ) {
				$t_file_path = '';
				# Don't reveal the absolute path to non-administrators for security reasons
				if( current_user_is_administrator() ) {
					$t_file_path = config_get_global( 'absolute_path_default_upload_folder' );
				}
				?>
				<tr>
					<td class="category">
						<?php echo lang_get( 'upload_file_path' ) ?>
					</td>
					<td>
						<input type="text" id="project-file-path" name="file_path" class="input-sm" size="60" maxlength="250" value="<?php echo $t_file_path ?>" />
					</td>
				</tr>
			<?php
			} ?>

			<tr>
				<td class="category">
					<?php echo lang_get( 'description' ) ?>
				</td>
				<td>
					<textarea class="form-control" id="project-description" name="description" cols="70" rows="5"></textarea>
				</td>
			</tr>

			<?php event_signal( 'EVENT_MANAGE_PROJECT_CREATE_FORM' ) ?>
		</fieldset>
		</table>
		</div>
		</div>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<span class="required pull-right"> * <?php echo lang_get( 'required' ) ?></span>
			<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'add_project_button' ) ?>" />
		</div>
	</div>
	</div>
	</form>
</div>

<?php
layout_page_end();
