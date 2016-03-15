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
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'custom_field_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );

auth_reauthenticate();

access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

html_page_top( lang_get( 'manage_custom_field_link' ) );

print_manage_menu( 'manage_custom_field_page.php' );
?>

<div class="table-container">
	<h2><?php echo lang_get( 'custom_fields_setup' ) ?></h2>
	<table>
		<thead>
			<tr>
				<th class="category"><?php echo lang_get( 'custom_field_name' ) ?></th>
				<th class="category"><?php echo lang_get( 'custom_field_project_count' ) ?></th>
				<th class="category"><?php echo lang_get( 'custom_field_type' ) ?></th>
				<th class="category"><?php echo lang_get( 'custom_field_possible_values' ) ?></th>
				<th class="category"><?php echo lang_get( 'custom_field_default_value' ) ?></th>
			</tr>
		</thead>
		<tbody><?php
		$t_custom_fields = custom_field_get_ids();
		foreach( $t_custom_fields as $t_field_id ) {
			$t_desc = custom_field_get_definition( $t_field_id ); ?>
			<tr>
				<td>
					<a href="manage_custom_field_edit_page.php?field_id=<?php echo $t_field_id ?>"><?php echo custom_field_get_display_name( $t_desc['name'] ) ?></a>
				</td>
				<td><?php echo count( custom_field_get_project_ids( $t_field_id ) ) ?></td>
				<td><?php echo get_enum_element( 'custom_field_type', $t_desc['type'] ) ?></td>
				<?php
				# workaround to enforce line break displaying custom field values
				# @todo replace by CSS after we don't support any longer browsers without CSS3 support
				?>
				<td><?php echo str_replace( '|', ' | ', string_display( $t_desc['possible_values'] ) ) ?></td>
				<td><?php echo string_display( $t_desc['default_value'] ) ?></td>
			</tr><?php
		} # Create Form END ?>
		</tbody>
	</table>
	<form method="post" action="manage_custom_field_create.php">
		<fieldset>
			<?php echo form_security_field( 'manage_custom_field_create' ); ?>
			<input type="text" name="name" size="32" maxlength="64" />
			<input type="submit" class="button" value="<?php echo lang_get( 'add_custom_field_button' ) ?>" />
		</fieldset>
	</form>
</div><?php


html_page_bottom();
