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

	require_once( 'custom_field_api.php' );

	auth_reauthenticate();

	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

	$f_field_id	= gpc_get_int( 'field_id' );
	$f_return	= strip_tags( gpc_get_string( 'return', 'manage_custom_field_page.php' ) );

	custom_field_ensure_exists( $f_field_id );

	html_page_top();

	print_manage_menu( 'manage_custom_field_edit_page.php' );

	$t_definition = custom_field_get_definition( $f_field_id );
?>
<br />
<div align="center">
<form method="post" action="manage_custom_field_update.php">
<?php echo form_security_field( 'manage_custom_field_update' ); ?>
	<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
	<input type="hidden" name="return" value="<?php echo $f_return ?>" />

	<table class="width50" cellspacing="1">
		<tr>
			<td class="form-title" colspan="2">
				<?php echo lang_get( 'edit_custom_field_title' ) ?>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_name' ) ?>
			</td>
			<td>
				<input type="text" name="name" size="32" maxlength="64" value="<?php echo string_attribute( $t_definition['name'] ) ?>" />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_type' ) ?>
			</td>
			<td>
				<select name="type">
					<?php print_enum_string_option_list( 'custom_field_type', $t_definition['type'] ) ?>
				</select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_possible_values' ) ?>
			</td>
			<td>
				<input type="text" name="possible_values" size="32" value="<?php echo string_attribute( $t_definition['possible_values'] ) ?>" />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_default_value' ) ?>
			</td>
			<td>
				<input type="text" name="default_value" size="32" maxlength="255" value="<?php echo string_attribute( $t_definition['default_value'] ) ?>" />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_valid_regexp' ) ?>
			</td>
			<td>
				<input type="text" name="valid_regexp" size="32" maxlength="255" value="<?php echo string_attribute( $t_definition['valid_regexp'] ) ?>" />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_access_level_r' ) ?>
			</td>
			<td>
				<select name="access_level_r">
					<?php print_enum_string_option_list( 'access_levels', $t_definition['access_level_r'] ) ?>
				</select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_access_level_rw' ) ?>
			</td>
			<td>
				<select name="access_level_rw">
					<?php print_enum_string_option_list( 'access_levels', $t_definition['access_level_rw'] ) ?>
				</select>
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_length_min' ) ?>
			</td>
			<td>
				<input type="text" name="length_min" size="32" maxlength="64" value="<?php echo $t_definition['length_min'] ?>" />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_length_max' ) ?>
			</td>
			<td>
				<input type="text" name="length_max" size="32" maxlength="64" value="<?php echo $t_definition['length_max'] ?>" />
			</td>
		</tr>
        <tr <?php echo helper_alternate_class() ?>>
            <td class="category">
                <?php echo lang_get( 'custom_field_filter_by' ) ?>
            </td>
            <td>
                <input type="checkbox" name="filter_by" <?php if ( $t_definition['filter_by'] ) { ?>checked="checked"<?php } ?>  />
            </td>
        </tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_display_report' ) ?>
			</td>
			<td>
				<input type="checkbox" name="display_report" value="1" <?php check_checked( $t_definition['display_report'] ) ?> />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_display_update' ) ?>
			</td>
			<td>
				<input type="checkbox" name="display_update" value="1" <?php check_checked( $t_definition['display_update'] ) ?> />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_display_resolved' ) ?>
			</td>
			<td>
				<input type="checkbox" name="display_resolved" value="1" <?php check_checked( $t_definition['display_resolved'] ) ?> />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_display_closed' ) ?>
			</td>
			<td>
				<input type="checkbox" name="display_closed" value="1" <?php check_checked( $t_definition['display_closed'] ) ?> />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_require_report' ) ?>
			</td>
			<td>
				<input type="checkbox" name="require_report" value="1" <?php check_checked( $t_definition['require_report'] ) ?> />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_require_update' ) ?>
			</td>
			<td>
				<input type="checkbox" name="require_update" value="1" <?php check_checked( $t_definition['require_update'] ) ?> />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_require_resolved' ) ?>
			</td>
			<td>
				<input type="checkbox" name="require_resolved" value="1" <?php check_checked( $t_definition['require_resolved'] ) ?> />
			</td>
		</tr>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="category">
				<?php echo lang_get( 'custom_field_require_closed' ) ?>
			</td>
			<td>
				<input type="checkbox" name="require_closed" value="1" <?php check_checked( $t_definition['require_closed'] ) ?> />
			</td>
		</tr>
		<tr>
			<td>&#160;</td>
			<td>
				<input type="submit" class="button" value="<?php echo lang_get( 'update_custom_field_button' ) ?>" />
			</td>
		</tr>
	</table>
</form>
</div>

<br />

<div class="border center">
	<form method="post" action="manage_custom_field_delete.php">
<?php echo form_security_field( 'manage_custom_field_delete' ); ?>
		<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
		<input type="hidden" name="return" value="<?php echo string_attribute( $f_return ) ?>" />
		<input type="submit" class="button" value="<?php echo lang_get( 'delete_custom_field_button' ) ?>" />
	</form>
</div>

<?php /** @todo There is access checking in the ADD action page and at the top of this file.
           * We may need to add extra checks to exclude projects from the list that the user
		   * can't link/unlink fields from/to. */
?>
<br />
<div align="center">
<form method="post" action="manage_custom_field_proj_add.php">
<?php echo form_security_field( 'manage_custom_field_proj_add' ); ?>
<table class="width75" cellspacing="1">
<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
		<?php echo lang_get( 'link_custom_field_to_project_title' ) ?>
	</td>
</tr>

<!-- Assigned Projects -->
<tr <?php echo helper_alternate_class( 1 ) ?> valign="top">
	<td class="category" width="30%">
		<?php echo lang_get( 'linked_projects' ) ?>:
	</td>
	<td width="70%">
		<?php print_custom_field_projects_list( $f_field_id ) ?>
	</td>
</tr>

<!-- Unassigend Project Selection -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td class="category">
		<?php echo lang_get( 'projects_title' ) ?>:
	</td>
	<td>
		<select name="project_id[]" multiple="multiple" size="5">
			<?php print_project_option_list( null, false ); ?>
		</select>
	</td>
</tr>

<!-- Sequence Number -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td class="category">
		<?php echo lang_get( 'custom_field_sequence' ) ?>:
	</td>
	<td>
		<input type="text" name="sequence" value="0" />
	</td>
</tr>

<!-- Submit Buttom -->
<tr>
	<td class="center" colspan="2">
		<input type="submit" class="button" value="<?php echo lang_get( 'link_custom_field_to_project_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
	html_page_bottom();
