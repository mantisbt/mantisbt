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

html_page_top();

print_manage_menu( 'manage_proj_create_page.php' );

$f_parent_id = gpc_get( 'parent_id', null );

if( project_table_empty() ) {
	echo '<br />';
	echo '<div id="create-first-project" class="important-msg">';
	echo '<ul>';
	echo '<li>' . lang_get( 'create_first_project' ) . '</li>';
	echo '</ul>';
	echo '</div>';
}
?>

<div id="manage-project-create-div" class="form-container">
	<form method="post" id="manage-project-create-form" action="manage_proj_create.php">
		<fieldset class="required"><?php
			echo form_security_field( 'manage_proj_create' );
			if( null !== $f_parent_id ) {
				$f_parent_id = (int)$f_parent_id; ?>
				<input type="hidden" name="parent_id" value="<?php echo $f_parent_id ?>" /><?php
			} ?>
			<legend><span><?php
			if( null !== $f_parent_id ) {
				echo lang_get( 'add_subproject_title' );
			} else {
				echo lang_get( 'add_project_title' );
			} ?></span></legend>

			<div class="field-container">
				<label for="project-name" class="required"><span><?php echo lang_get( 'project_name' )?></span></label>
				<span class="input"><input type="text" id="project-name" name="name" size="60" maxlength="128" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="project-status"><span><?php echo lang_get( 'status' ) ?></span></label>
				<span class="select">
					<select id="project-status" name="status">
						<?php print_enum_string_option_list( 'project_status' ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="project-inherit-global"><span><?php echo lang_get( 'inherit_global' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="project-inherit-global" name="inherit_global" checked="checked" /></span>
				<span class="label-style"></span>
			</div>
			<?php if( !is_null( $f_parent_id ) ) { ?>
			<div class="field-container">
				<label for="project-inherit-parent"><span><?php echo lang_get( 'inherit_parent' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="project-inherit-parent" name="inherit_parent" checked="checked" /></span>
				<span class="label-style"></span>
			</div><?php
			} ?>

			<div class="field-container">
				<label for="project-view-state"><span><?php echo lang_get( 'view_status' ) ?></span></label>
				<span class="select">
					<select id="project-view-state" name="view_state">
						<?php print_enum_string_option_list( 'view_state', config_get( 'default_project_view_status', null, ALL_USERS, ALL_PROJECTS ) ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<?php

			$g_project_override = ALL_PROJECTS;
			if( file_is_uploading_enabled() && DATABASE !== config_get( 'file_upload_method' ) ) {
				$t_file_path = '';
				# Don't reveal the absolute path to non-administrators for security reasons
				if( current_user_is_administrator() ) {
					$t_file_path = config_get( 'absolute_path_default_upload_folder' );
				}
				?>
				<div class="field-container">
					<label for="project-file-path"><span><?php echo lang_get( 'upload_file_path' ) ?></span></label>
					<span class="input"><input type="text" id="project-file-path" name="file_path" size="60" maxlength="250" value="<?php echo $t_file_path ?>" /></span>
					<span class="label-style"></span>
				</div><?php
			} ?>
			<div class="field-container">
				<label for="project-description"><span><?php echo lang_get( 'description' ) ?></span></label>
				<span class="textarea"><textarea id="project-description" name="description" cols="70" rows="5"></textarea></span>
				<span class="label-style"></span>
			</div>

			<?php event_signal( 'EVENT_MANAGE_PROJECT_CREATE_FORM' ) ?>

			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'add_project_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<?php
html_page_bottom();
