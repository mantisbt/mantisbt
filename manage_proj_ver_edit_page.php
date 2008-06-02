<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: manage_proj_ver_edit_page.php,v 1.31.2.1 2007-10-13 22:33:48 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	auth_reauthenticate();

	$f_version_id = gpc_get_int( 'version_id' );

	$t_version = version_get( $f_version_id );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $t_version->project_id );

	html_page_top1();
	html_page_top2();

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
		<input type="text" name="date_order" size="32" value="<?php echo string_attribute( date( 'Y-m-d H:i:s', $t_version->date_order ) ) ?>" />
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
<tr>
	<td>
		&nbsp;
	</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'update_version_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<br />

<div class="border-center">
	<form method="post" action="manage_proj_ver_delete.php">
	<input type="hidden" name="version_id" value="<?php echo string_attribute( $t_version->id ) ?>" />
	<input type="submit" class="button" value="<?php echo lang_get( 'delete_version_button' ) ?>" />
	</form>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
