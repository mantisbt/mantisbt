<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( lang_get( 'plugin_format_title' ) );

print_manage_menu( );

?>

<br />
<form action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_format_config_edit' ) ?>
<table align="center" class="width50" cellspacing="1">

<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'plugin_format_title' ) . ': ' . lang_get( 'plugin_format_config' )?>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category" width="60%">
		<?php echo lang_get( 'plugin_format_process_text' )?>
		<br /><span class="small"><?php echo lang_get( 'plugin_format_process_text_warning_notice' )?></span>
	</td>
	<td class="center" width="20%">
		<label><input type="radio" name="process_text" value="1" <?php echo( ON == plugin_config_get( 'process_text' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_enabled' )?></label>
	</td>
	<td class="center" width="20%">
		<label><input type="radio" name="process_text" value="0" <?php echo( OFF == plugin_config_get( 'process_text' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_disabled' )?></label>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo lang_get( 'plugin_format_process_urls' )?>
	</td>
	<td class="center">
		<label><input type="radio" name="process_urls" value="1" <?php echo( ON == plugin_config_get( 'process_urls' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_enabled' )?></label>
	</td>
	<td class="center">
		<label><input type="radio" name="process_urls" value="0" <?php echo( OFF == plugin_config_get( 'process_urls' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_disabled' )?></label>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo lang_get( 'plugin_format_process_buglinks' )?>
	</td>
	<td class="center">
		<label><input type="radio" name="process_buglinks" value="1" <?php echo( ON == plugin_config_get( 'process_buglinks' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_enabled' )?></label>
	</td>
	<td class="center">
		<label><input type="radio" name="process_buglinks" value="0" <?php echo( OFF == plugin_config_get( 'process_buglinks' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_disabled' )?></label>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo lang_get( 'plugin_format_process_vcslinks' )?>
	</td>
	<td class="center">
		<label><input type="radio" name="process_vcslinks" value="1" <?php echo( ON == plugin_config_get( 'process_vcslinks' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_enabled' )?></label>
	</td>
	<td class="center">
		<label><input type="radio" name="process_vcslinks" value="0" <?php echo( OFF == plugin_config_get( 'process_vcslinks' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'plugin_format_disabled' )?></label>
	</td>
</tr>

<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" />
	</td>
</tr>

</table>
</form>

<?php
html_page_bottom();
