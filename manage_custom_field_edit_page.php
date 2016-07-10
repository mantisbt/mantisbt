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

layout_page_header();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_custom_field_page.php' );

$t_definition = custom_field_get_definition( $f_field_id );
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>

<div id="manage-custom-field-update-div" class="form-container">
<form id="manage-custom-field-update-form" method="post" action="manage_custom_field_update.php">
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-flask"></i>
		<?php echo lang_get( 'edit_custom_field_title' ) ?>
	</h4>
</div>

<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">

<fieldset>
			<?php echo form_security_field( 'manage_custom_field_update' ); ?>
			<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
			<input type="hidden" name="return" value="<?php echo $f_return ?>" />
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_name' ) ?>
		</td>
		<td>
			<input type="text" id="custom-field-name" name="name" class="input-sm" size="32" maxlength="64" value="<?php echo string_attribute( $t_definition['name'] ) ?>" />
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_type' ) ?>
		</td>
		<td>
			<select id="custom-field-type" name="type" class="input-sm">
				<?php print_enum_string_option_list( 'custom_field_type', (int)$t_definition['type'] ) ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_possible_values' ) ?>
		</td>
		<td>
			<input type="text" id="custom-field-possible-values" name="possible_values" class="input-sm" size="80%" value="<?php echo string_attribute( $t_definition['possible_values'] ) ?>" />
            <small><?php echo sprintf( lang_get( 'separate_list_items_by' ), '|' ) ?></small>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_default_value' ) ?>
		</td>
		<td>
			<div class="input">
				<input type="text" id="custom-field-default-value" name="default_value" class="input-sm" size="32" maxlength="255" value="<?php echo string_attribute( $t_definition['default_value'] ) ?>" />
			</div>
			<div class="textarea">
				<textarea disabled="disabled" id="custom-field-default-value-textarea" name="default_value" class="form-control" cols="80" rows="10"><?php echo string_attribute( $t_definition['default_value'] ) ?></textarea>
			</div>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_valid_regexp' ) ?>
		</td>
		<td>
			<input type="text" id="custom-field-valid-regexp" name="valid_regexp" class="input-sm" size="32" maxlength="255" value="<?php echo string_attribute( $t_definition['valid_regexp'] ) ?>" />
		</td>
	</tr>

	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_access_level_r' ) ?>
		</td>
		<td>
			<select id="custom-field-access-level-r" name="access_level_r" class="input-sm">
				<?php print_enum_string_option_list( 'access_levels', (int)$t_definition['access_level_r'] ) ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_access_level_rw' ) ?>
		</td>
		<td>
			<select id="custom-field-access-level-rw" name="access_level_rw" class="input-sm">
				<?php print_enum_string_option_list( 'access_levels', (int)$t_definition['access_level_rw'] ) ?>
			</select>
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
    <tr>
        <td class="category">
            <?php echo lang_get( 'custom_field_length_min' ) ?>
        </td>
        <td>
            <input type="text" id="custom-field-length-min" name="length_min" class="input-sm" size="32" maxlength="64" value="<?php echo $t_definition['length_min'] ?>" />
        </td>
    </tr>
    <tr>
        <td class="category">
            <?php echo lang_get( 'custom_field_length_max' ) ?>
        </td>
        <td>
            <input type="text" id="custom-field-length-max" name="length_max" class="input-sm" size="32" maxlength="64" value="<?php echo $t_definition['length_max'] ?>" />
        </td>
    </tr>
    <tr>
        <td class="category">
            <?php echo lang_get( 'custom_field_filter_by' ) ?>
        </td>
        <td>
            <label>
                <input type="checkbox" class="ace" id="custom-field-filter-by" name="filter_by" value="1"
                    <?php
                    if( $t_definition['filter_by'] ) {
                        echo 'checked="checked"';
                    }
                    ?> />
                <span class="lbl"></span>
            </label>
        </td>
    </tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_display_report' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-display-report" name="display_report" value="1" <?php check_checked( (bool)$t_definition['display_report'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_display_update' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-display-update" name="display_update" value="1" <?php check_checked( (bool)$t_definition['display_update'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_display_resolved' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-display-resolved" name="display_resolved" value="1" <?php check_checked( (bool)$t_definition['display_resolved'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_display_closed' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-display-closed" name="display_closed" value="1" <?php check_checked( (bool)$t_definition['display_closed'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_require_report' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-require-report" name="require_report" value="1" <?php check_checked( (bool)$t_definition['require_report'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_require_update' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-require-update" name="require_update" value="1" <?php check_checked( (bool)$t_definition['require_update'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_require_resolved' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-require-resolved" name="require_resolved" value="1" <?php check_checked( (bool)$t_definition['require_resolved'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
	<tr>
		<td class="category">
			<?php echo lang_get( 'custom_field_require_closed' ) ?>
		</td>
		<td>
			<label>
				<input type="checkbox" class="ace" id="custom-field-require-closed" name="require_closed" value="1" <?php check_checked( (bool)$t_definition['require_closed'] ) ?> />
				<span class="lbl"></span>
			</label>
		</td>
	</tr>
</fieldset>
</table>
</div>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'update_custom_field_button' ) ?>" />
</div>
</div>
</div>
</form>
</div>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form method="post" action="manage_custom_field_delete.php" class="pull-right">
		<fieldset>
			<?php echo form_security_field( 'manage_custom_field_delete' ); ?>
			<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
			<input type="hidden" name="return" value="<?php echo string_attribute( $f_return ) ?>" />
			<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" value="<?php echo lang_get( 'delete_custom_field_button' ) ?>" />
		</fieldset>
	</form>
</div>

<?php /** @todo There is access checking in the ADD action page and at the top of this file.
           * We may need to add extra checks to exclude projects from the list that the user
		   * can't link/unlink fields from/to. */
?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-link"></i>
			<?php echo lang_get( 'link_custom_field_to_project_title' ) ?>
		</h4>
	</div>

	<div class="widget-body">
		<div class="widget-main no-padding">
			<div class="table-responsive">
			<table class="table table-bordered table-condensed table-striped">
			<form id="manage-custom-field-link-form" method="post" action="manage_custom_field_proj_add.php">
			<div class="form-container" id="manage-custom-field-link-div">
				<tr id="custom-field-link-project">
				<td class="category">
					<?php echo lang_get( 'linked_projects_label' ) ?>
				</td>
				<td>
					<?php print_custom_field_projects_list( $f_field_id ); ?>
				</td>
				</tr>

				<input type="hidden" name="field_id" value="<?php echo $f_field_id ?>" />
				<?php echo form_security_field( 'manage_custom_field_proj_add' ); ?>

				<tr>
					<td class="category">
						<?php echo lang_get( 'projects_title_label' ) ?>
					</td>
					<td>
						<select id="custom-field-project-id" name="project_id[]" class="input-sm" multiple="multiple" size="5">
							<?php print_project_option_list( null, false ); ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="category">
						<?php echo lang_get( 'custom_field_sequence_label' ) ?>
					</td>
					<td>
						<input type="text" id="custom-field-sequence" name="sequence" class="input-sm" value="0" />
					</td>
				</tr>
				<tr>
					<td colspan="2" class="no-padding">
					<div class="widget-toolbox padding-8 clearfix">
						<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'link_custom_field_to_project_button' ) ?>" />
					</div>
					</td>
				</tr>
				</form>
			</table>
			</div>
		</div>
    </div>
    </div>
</div><?php

layout_page_end();
