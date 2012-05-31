<?php
# MantisBT - a php based bugtracking system

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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'create_project_threshold' ) );

	html_page_top();?>
	
		<div class="page-header">
		<h1><?php
			if ( null !== $f_parent_id ) {
				echo lang_get( 'add_subproject_title' );
			} else {
				echo lang_get( 'add_project_title' );
			}
		?>

</h1>
	</div>
	<?php

	print_manage_menu( 'manage_proj_create_page.php' );

	$f_parent_id = gpc_get( 'parent_id', null );
?>
<div class="span10">
<form method="post" action="manage_proj_create.php">
<?php
	echo form_security_field( 'manage_proj_create' );
	if ( null !== $f_parent_id ) {
		$f_parent_id = (int) $f_parent_id;
?>
<input type="hidden" name="parent_id" value="<?php echo $f_parent_id ?>">
<?php } ?>

		
		<label><span class="required">*</span><?php echo lang_get( 'project_name' )?></label>
		<input class="span12" type="text" name="name" size="64" maxlength="128" />
		<label><?php echo lang_get( 'description' ) ?></label>
		<textarea name="description" class="span12" rows="5"></textarea>

		<label><?php echo lang_get( 'status' ) ?></label>
		<select name="status"><?php print_enum_string_option_list( 'project_status' ) ?></select>
		<label><?php echo lang_get( 'view_status' ) ?></label>
		<select name="view_state"><?php print_enum_string_option_list( 'view_state' ) ?></select>

	<?php

	if ( config_get( 'allow_file_upload' ) ) {
		$t_default_upload_path = '';
		# Don't reveal the absolute path to non-administrators for security reasons
		if ( current_user_is_administrator() ) {
			$t_default_upload_path = config_get( 'absolute_path_default_upload_folder' );
		}
	?>

				<label><?php echo lang_get( 'upload_file_path' ) ?></label>
				<input type="text" name="file_path" size="70" maxlength="250" value="<?php echo $t_default_upload_path ?>" />
		<?php
	}
?>
		<label class="checkbox"><?php echo lang_get( 'inherit_global' ) ?>
		<input type="checkbox" name="inherit_global" checked="checked" />
		</label>
	<?php if ( !is_null( $f_parent_id ) ) { ?>

		<label class="checkbox"><?php echo lang_get( 'inherit_parent' ) ?>
		<input type="checkbox" name="inherit_parent" checked="checked" />
		</label>
	<?php } ?>
				<br />
	
<?php event_signal( 'EVENT_MANAGE_PROJECT_CREATE_FORM' ) ?>

		<input type="submit" class="btn btn-primary" value="<?php echo lang_get( 'add_project_button' ) ?>" />
</form>
</div></div>

<?php
	html_page_bottom();
