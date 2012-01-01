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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	/**
	 * requires ajax_api
	 */
	require_once( 'ajax_api.php' );
	/**
	 * requires tag_api
	 */
	require_once( 'tag_api.php' );

	compress_enable();

	$f_tag_id = gpc_get_int( 'tag_id' );
	$t_tag_row = tag_get( $f_tag_id );

	$t_name = string_display_line( $t_tag_row['name'] );
	$t_description = string_display( $t_tag_row['description'] );

	if ( !( access_has_global_level( config_get( 'tag_edit_threshold' ) )
		|| ( auth_get_current_user_id() == $t_tag_row['user_id'] )
			&& access_has_global_level( config_get( 'tag_edit_own_threshold' ) ) ) )
	{
		access_denied();
	}

	html_page_top( sprintf( lang_get( 'tag_update' ), $t_name ) );
?>

<br />
<form method="post" action="tag_update.php">
<?php echo form_security_field( 'tag_update' ) ?>
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<?php echo sprintf( lang_get( 'tag_update' ), $t_name ) ?>
		<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>"/>
	</td>
	<td class="right" colspan="3">
		<?php print_bracket_link( 'tag_view_page.php?tag_id='.$f_tag_id, lang_get( 'tag_update_return' ) ); ?>
	</td>
</tr>

<!-- Info -->
<tr class="row-category">
	<td width="15%"><?php echo lang_get( 'tag_id' ) ?></td>
	<td width="25%"><?php echo lang_get( 'tag_name' ) ?></td>
	<td width="20%"><?php echo lang_get( 'tag_creator' ) ?></td>
	<td width="20%"><?php echo lang_get( 'tag_created' ) ?></td>
	<td width="20%"><?php echo lang_get( 'tag_updated' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
	<td><?php echo $t_tag_row['id'] ?></td>
	<td><input type="text" <?php echo helper_get_tab_index() ?> name="name" value="<?php echo $t_name ?>"/></td>
	<td><?php
			if ( access_has_global_level( config_get( 'tag_edit_threshold' ) ) ) {
				if ( ON == config_get( 'use_javascript' ) ) {
					$t_username = prepare_user_name( $t_tag_row['user_id'] );
					echo ajax_click_to_edit( $t_username, 'user_id', 'entrypoint=user_combobox&user_id=' . $t_tag_row['user_id'] . '&access_level=' . config_get( 'tag_create_threshold' ) );
				} else {
					echo '<select ', helper_get_tab_index(), ' name="user_id">';
					print_user_option_list( $t_tag_row['user_id'], ALL_PROJECTS, config_get( 'tag_create_threshold' ) );
					echo '</select>';
				}
			} else {
				echo string_display_line( user_get_name($t_tag_row['user_id']) );
			}
		?></td>
	<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_created'] ) ?> </td>
	<td><?php echo date( config_get( 'normal_date_format' ), $t_tag_row['date_updated'] ) ?> </td>
</tr>

<!-- spacer -->
<tr class="spacer">
	<td colspan="5"></td>
</tr>

<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo lang_get( 'tag_description' ) ?></td>
	<td colspan="4">
		<textarea name="description" <?php echo helper_get_tab_index() ?> cols="80" rows="6"><?php echo string_textarea( $t_description ) ?></textarea>
	</td>
</tr>

<!-- Submit Button -->
<tr>
	<td class="center" colspan="6">
		<input <?php echo helper_get_tab_index() ?> type="submit" class="button" value="<?php echo lang_get( 'tag_update_button' ) ?>" />
	</td>
</tr>

</table>
</form>

<?php
	html_page_bottom();
