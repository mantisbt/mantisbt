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
 * @package MantisBT
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

access_ensure_global_level( config_get( 'view_configuration_threshold' ) );

$t_read_write_access = access_has_global_level( config_get('set_configuration_threshold' ) );

html_page_top( lang_get( 'configuration_report' ) );

print_manage_menu( 'adm_config_report.php' );
print_manage_config_menu( 'adm_config_report.php' );

function get_config_type( $p_type ) {
	switch( $p_type ) {
		case CONFIG_TYPE_INT:
			return "integer";
		case CONFIG_TYPE_FLOAT:
			return "float";
		case CONFIG_TYPE_COMPLEX:
			return "complex";
		case CONFIG_TYPE_STRING:
		default:
			return "string";
	}
}

function print_config_value_as_string( $p_type, $p_value ) {
	$t_corrupted = false;

	switch( $p_type ) {
		case CONFIG_TYPE_FLOAT:
			$t_value = (float)$p_value;
			echo $t_value;
			return;
		case CONFIG_TYPE_INT:
			$t_value = (integer)$p_value;
			echo $t_value;
			return;
		case CONFIG_TYPE_STRING:
			$t_value = config_eval( $p_value );
			echo string_nl2br( string_html_specialchars( $t_value ) );
			return;
		case CONFIG_TYPE_COMPLEX:
			$t_value = @unserialize( $p_value );
			if ( $t_value === false ) {
				$t_corrupted = true;
			}
			break;
		default:
			$t_value = config_eval( $p_value );
			break;
	}

	echo '<pre>';

	if ( $t_corrupted ) {
		echo lang_get( 'configuration_corrupted' );
	} else {
		if ( function_exists( 'var_export' ) ) {
			var_export( $t_value );
		} else {
			print_r( $t_value );
		}
	}

	echo '</pre>';
}

$t_config_table = db_get_table( 'config' );
$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table ORDER BY user_id, project_id, config_id";
$result = db_query_bound( $query );
?>
<div id="adm-config-div" class="table-container">
	<h2><?php echo lang_get( 'database_configuration' ) ?></h2>
	<table cellspacing="1" cellpadding="5" border="1">
		<tr class="row-category">
			<th class="center"><?php echo lang_get( 'username' ) ?></th>
			<th class="center"><?php echo lang_get( 'project_name' ) ?></th>
			<th><?php echo lang_get( 'configuration_option' ) ?></th>
			<th class="center"><?php echo lang_get( 'configuration_option_type' ) ?></th>
			<th class="center"><?php echo lang_get( 'configuration_option_value' ) ?></th>
			<th class="center"><?php echo lang_get( 'access_level' ) ?></th>
			<?php if ( $t_read_write_access ): ?>
			<th class="center"><?php echo lang_get( 'actions' ) ?></th>
			<?php endif; ?>
		</tr><?php
		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' ); ?>
		<tr <?php echo helper_alternate_class() ?>>
			<td class="center">
				<?php echo ($v_user_id == 0) ? lang_get( 'all_users' ) : string_display_line( user_get_name( $v_user_id ) ) ?>
			</td>
			<td class="center"><?php echo string_display_line( project_get_name( $v_project_id, false ) ) ?></td>
			<td><?php echo string_display_line( $v_config_id ) ?></td>
			<td class="center"><?php echo string_display_line( get_config_type( $v_type ) ) ?></td>
			<td class="left"><?php print_config_value_as_string( $v_type, $v_value ) ?></td>
			<td class="center"><?php echo get_enum_element( 'access_levels', $v_access_reqd ) ?></td>
			<?php if ( $t_read_write_access ): ?>
			<td class="center">
				<?php
					if ( config_can_delete( $v_config_id ) ) {
						print_button( "adm_config_delete.php?user_id=$v_user_id&project_id=$v_project_id&config_option=$v_config_id", lang_get( 'delete_link' ) );
					} else {
						echo '&#160;';
					}
				?>
			</td>
			<?php endif; ?>
		</tr><?php
		} # end for loop ?>
	</table>
</div><?php

if ( $t_read_write_access ) { ?>
<div class="form-container">
	<form method="post" action="adm_config_set.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'set_configuration_option' ) ?></span></legend>
			<?php echo form_security_field( 'adm_config_set' ) ?>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-user-id"><span><?php echo lang_get( 'username' ) ?></span></label>
				<span class="select">
					<select id="config-user-id" name="user_id">
						<option value="0" selected="selected"><?php echo lang_get( 'all_users' ); ?></option>
						<?php print_user_option_list( 0 ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-project-id"><span><?php echo lang_get( 'project_name' ) ?></span></label>
				<span class="select">
					<select id="config-project-id" name="project_id">
						<option value="0" selected="selected"><?php echo lang_get( 'all_projects' ); ?></option>
						<?php print_project_option_list( ALL_PROJECTS, false ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-option"><span><?php echo lang_get( 'configuration_option' ) ?></span></label>
				<span class="input"><input type="text" id="config-option" name="config_option" value="" size="64" maxlength="64" /></span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-type"><span><?php echo lang_get( 'configuration_option_type' ) ?></span></label>
				<span class="select">
					<select id="config-type" name="type">
						<option value="default" selected="selected">default</option>
						<option value="string">string</option>
						<option value="integer">integer</option>
						<option value="complex">complex</option>
					</select>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-value"><span><?php echo lang_get( 'configuration_option_value' ) ?></span></label>
				<span class="textarea"><textarea id="config-value" name="value" cols="80" rows="10"></textarea></span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" name="config_set" class="button" value="<?php echo lang_get( 'set_configuration_option' ) ?>" /></span>
		</fieldset>
	</form>
</div><?php
} # end user can change config

html_page_bottom();
