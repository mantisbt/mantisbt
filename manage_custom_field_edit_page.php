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
 * Custom Field Configuration
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
 * @uses custom_field_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );

auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

$f_field_id	= gpc_get_int( 'field_id' );
$f_return	= strip_tags( gpc_get_string( 'return', 'manage_custom_field_page.php' ) );

custom_field_ensure_exists( $f_field_id );

require_js( 'manage_custom_field_edit_page.js' );

html_page_top();

print_manage_menu( 'manage_custom_field_edit_page.php' );

$t_definition = custom_field_get_definition( $f_field_id );
?>

<div id="manage-custom-field-update-div" class="form-container">
	<form id="manage-custom-field-update-form" method="post" action="manage_custom_field_update.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'edit_custom_field_title' ) ?></span></legend>
			<?php echo form_security_field( 'manage_custom_field_update' ); ?>
			<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
			<input type="hidden" name="return" value="<?php echo string_attribute( $f_return ); ?>" />
			<div class="field-container">
				<label for="custom-field-name"><span><?php echo lang_get( 'custom_field_name' ) ?></span></label>
				<span class="input"><input type="text" id="custom-field-name" name="name" size="32" maxlength="64" value="<?php echo string_attribute( $t_definition['name'] ) ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-type"><span><?php echo lang_get( 'custom_field_type' ) ?></span></label>
				<span class="select">
					<select id="custom-field-type" name="type">
						<?php print_enum_string_option_list( 'custom_field_type', (int)$t_definition['type'] ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-possible-values"><span><?php echo lang_get( 'custom_field_possible_values' ) ?></span></label>
				<span class="input"><input type="text" id="custom-field-possible-values" name="possible_values" size="100" value="<?php echo string_attribute( $t_definition['possible_values'] ) ?>" />
					<?php echo sprintf( lang_get( 'separate_list_items_by' ), '|' ) ?>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-default-value"><span><?php echo lang_get( 'custom_field_default_value' ) ?></span></label>
				<span class="input">
					<input type="text" id="custom-field-default-value" name="default_value" size="32" maxlength="255" value="<?php echo string_attribute( $t_definition['default_value'] ) ?>" />
				</span>
				<span class="textarea">
					<textarea disabled="disabled" id="custom-field-default-value-textarea" name="default_value" cols="80" rows="10"><?php echo string_attribute( $t_definition['default_value'] ) ?></textarea>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-valid-regexp"><span><?php echo lang_get( 'custom_field_valid_regexp' ) ?></span></label>
				<span class="input"><input type="text" id="custom-field-valid-regexp" name="valid_regexp" size="32" maxlength="255" value="<?php echo string_attribute( $t_definition['valid_regexp'] ) ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-access-level-r"><span><?php echo lang_get( 'custom_field_access_level_r' ) ?></span></label>
				<span class="select">
					<select id="custom-field-access-level-r" name="access_level_r">
						<?php print_enum_string_option_list( 'access_levels', (int)$t_definition['access_level_r'] ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-access-level-rw"><span><?php echo lang_get( 'custom_field_access_level_rw' ) ?></span></label>
				<span class="select">
					<select id="custom-field-access-level-rw" name="access_level_rw">
						<?php print_enum_string_option_list( 'access_levels', (int)$t_definition['access_level_rw'] ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-length-min"><span><?php echo lang_get( 'custom_field_length_min' ) ?></span></label>
				<span class="input"><input type="text" id="custom-field-length-min" name="length_min" size="32" maxlength="64" value="<?php echo $t_definition['length_min'] ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-length-max"><span><?php echo lang_get( 'custom_field_length_max' ) ?></span></label>
				<span class="input"><input type="text" id="custom-field-length-max" name="length_max" size="32" maxlength="64" value="<?php echo $t_definition['length_max'] ?>" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-filter-by"><span><?php echo lang_get( 'custom_field_filter_by' ) ?></span></label>
				<span class="checkbox">
					<input type="checkbox" id="custom-field-filter-by" name="filter_by"
					<?php
						if( $t_definition['filter_by'] ) {
							echo 'checked="checked"';
						}
					?> />
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-display-report"><span><?php echo lang_get( 'custom_field_display_report' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-display-report" name="display_report" value="1" <?php check_checked( (bool)$t_definition['display_report'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-display-update"><span><?php echo lang_get( 'custom_field_display_update' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-display-update" name="display_update" value="1" <?php check_checked( (bool)$t_definition['display_update'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-display-resolved"><span><?php echo lang_get( 'custom_field_display_resolved' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-display-resolved" name="display_resolved" value="1" <?php check_checked( (bool)$t_definition['display_resolved'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-display-closed"><span><?php echo lang_get( 'custom_field_display_closed' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-display-closed" name="display_closed" value="1" <?php check_checked( (bool)$t_definition['display_closed'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-require-report"><span><?php echo lang_get( 'custom_field_require_report' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-require-report" name="require_report" value="1" <?php check_checked( (bool)$t_definition['require_report'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-require-update"><span><?php echo lang_get( 'custom_field_require_update' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-require-update" name="require_update" value="1" <?php check_checked( (bool)$t_definition['require_update'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-require-resolved"><span><?php echo lang_get( 'custom_field_require_resolved' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-require-resolved" name="require_resolved" value="1" <?php check_checked( (bool)$t_definition['require_resolved'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-require-closed"><span><?php echo lang_get( 'custom_field_require_closed' ) ?></span></label>
				<span class="checkbox"><input type="checkbox" id="custom-field-require-closed" name="require_closed" value="1" <?php check_checked( (bool)$t_definition['require_closed'] ) ?> /></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'update_custom_field_button' ) ?>" /></span>
		</fieldset>
	</form>
</div>

<br />

<div class="form-container center">
	<form method="post" action="manage_custom_field_delete.php" class="action-button">
		<fieldset>
			<?php echo form_security_field( 'manage_custom_field_delete' ); ?>
			<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
			<input type="hidden" name="return" value="<?php echo string_attribute( $f_return ) ?>" />
			<input type="submit" class="button" value="<?php echo lang_get( 'delete_custom_field_button' ) ?>" />
		</fieldset>
	</form>
</div>

<?php /** @todo There is access checking in the ADD action page and at the top of this file.
           * We may need to add extra checks to exclude projects from the list that the user
		   * can't link/unlink fields from/to. */
?>
<div id="manage-custom-field-link-div" class="form-container">
	<form id="manage-custom-field-link-form" method="post" action="manage_custom_field_proj_add.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'link_custom_field_to_project_title' ) ?></span></legend>

			<div id="custom-field-link-project" class="field-container">
				<span class="display-label">
					<span><?php echo lang_get( 'linked_projects_label' ) ?></span>
				</span>
				<div class="display-value">
					<?php print_custom_field_projects_list( $f_field_id ) ?>
				</div>
				<span class="label-style"></span>
			</div>

			<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
			<?php echo form_security_field( 'manage_custom_field_proj_add' ); ?>
			<div class="field-container">
				<label for="custom-field-project-id"><span><?php echo lang_get( 'projects_title_label' ) ?></span></label>
				<span class="select">
					<select id="custom-field-project-id" name="project_id[]" multiple="multiple" size="5">
						<?php print_project_option_list( null, false ); ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label for="custom-field-sequence"><span><?php echo lang_get( 'custom_field_sequence_label' ) ?></span></label>
				<span class="input"><input type="text" id="custom-field-sequence" name="sequence" value="0" /></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'link_custom_field_to_project_button' ) ?>" /></span>
		</fieldset>
	</form>
</div><?php

html_page_bottom();
