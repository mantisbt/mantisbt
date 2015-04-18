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
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	auth_reauthenticate();

	$f_version_id = gpc_get_int( 'version_id' );

	$t_version = version_get( $f_version_id );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_version->project_id );

	html_page_top();

	print_manage_menu( 'manage_proj_ver_edit_page.php' );
?>
<br />
<div align="center">
<form method="post" action="manage_proj_ver_update.php">
<?php echo form_security_field( 'manage_proj_ver_update' ) ?>
<input type="hidden" name="version_id" value="<?php echo string_attribute( $t_version->id ) ?>" />
<table class="width50" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'edit_project_version_title' ) ?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'version' ) ?>
	</td>
	<td>
		<input type="text" name="new_version" size="32" maxlength="64" value="<?php echo string_attribute( $t_version->version ) ?>" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'date_order' ) ?>
	</td>
	<td>
		<input type="text" id="date_order" name="date_order" size="32" value="<?php echo (date_is_null( $t_version->date_order ) ? '' : string_attribute( date( config_get( 'calendar_date_format' ), $t_version->date_order ) ) ) ?>" />
		<?php 
			date_print_calendar();
			date_finish_calendar( 'date_order', 'trigger');
		?>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td>
		<textarea name="description" cols="60" rows="5"><?php echo string_attribute( $t_version->description ) ?></textarea>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'released' ) ?>
	</td>
	<td>
		<input type="checkbox" name="released" <?php check_checked( $t_version->released, VERSION_RELEASED ); ?> />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?>>
	<td class="category">
		<?php echo lang_get( 'obsolete' ) ?>
	</td>
	<td>
		<input type="checkbox" name="obsolete" <?php check_checked( $t_version->obsolete, true ); ?> />
	</td>
</tr>
<?php event_signal( 'EVENT_MANAGE_VERSION_UPDATE_FORM', array( $t_version->id ) ); ?>
<tr>
	<td>
		&#160;
	</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_version_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border center">
	<form method="post" action="manage_proj_ver_delete.php">
	<?php echo form_security_field( 'manage_proj_ver_delete' ) ?>
	<input type="hidden" name="version_id" value="<?php echo string_attribute( $t_version->id ) ?>" />
	<input type="submit" class="button" value="<?php echo lang_get( 'delete_version_button' ) ?>" />
	</form>
</div>

<?php
	html_page_bottom();
