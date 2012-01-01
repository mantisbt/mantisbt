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

html_page_top( plugin_lang_get( 'import' ) );

$t_this_page = plugin_page('import'); //FIXME with plugins this does not work...
print_manage_menu( $t_this_page );

$t_max_file_size = (int)min(
	ini_get_number( 'upload_max_filesize' ),
	ini_get_number( 'post_max_size' ),
	config_get( 'max_file_size' )
);

// We need a project to import into
$t_project_id = helper_get_current_project( );
if( ALL_PROJECTS == $t_project_id ) {
	print_header_redirect( "login_select_proj_page.php?ref=$t_this_page" );
}

?>

<div class="center">
<form name="file_upload" method="post" enctype="multipart/form-data" action="<?php echo plugin_page( 'import_action' )?>">
<?php echo form_security_field( 'plugin_xml_import_action' ) ?>

<input type="hidden" name="project_id" value="<?php echo $t_project_id;?>" />

<table class="width100">
<tr>
	<td class="form-title" colspan="2">
<?php
	echo plugin_lang_get( 'importing_in_project' ) . ' ' . string_display( project_get_field( $t_project_id, 'name' ) );
?>
	</td>
</tr>
<tr class="row-1">
	<td class="category" width="25%">
		<?php echo lang_get( 'select_file' )?><br />
		<?php echo '<span class="small">(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size / 1000 ) . 'k)</span>'?>
	</td>
	<td width="85%">
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size?>" />
		<input type="hidden" name="step" value="1" />
		<input name="file" type="file" size="40" />
	</td>
</tr>
<tr>
	<td class="form-title" colspan="2">
<?php
	echo plugin_lang_get( 'import_options' );
?>
	</td>
</tr>

<tr class="row-2">
	<td class="category" width="25%">
	<?php echo plugin_lang_get( 'cross_references' );?>
	</td>
	<td>
	<p><?php echo plugin_lang_get( 'default_strategy' );?>
	<select name="strategy">
	<option value="renumber" title="<?php echo plugin_lang_get( 'renumber_desc' );?>">
	<?php echo plugin_lang_get( 'renumber' );?></option>
	<option value="link" title="<?php echo plugin_lang_get( 'link_desc' );?>">
	<?php echo plugin_lang_get( 'link' );?></option>
	<option value="disable" title="<?php echo plugin_lang_get( 'disable_desc' );?>">
	<?php echo plugin_lang_get( 'disable' );?></option>
	</select>
	</p>
	<p><?php echo plugin_lang_get( 'fallback' );?>
	<select name="fallback">
	<option value="link" title="<?php echo plugin_lang_get( 'link_desc' );?>">
	<?php echo plugin_lang_get( 'link' );?></option>
	<option value="disable" title="<?php echo plugin_lang_get( 'disable_desc' );?>">
	<?php echo plugin_lang_get( 'disable' );?></option>
	</select>
	</p>

	</td>
</tr>

<tr class="row-2">
	<td class="category" width="25%"><?php echo lang_get( 'categories' );?></td>
	<td>
	<p><label for="keepcategory"><?php echo plugin_lang_get( 'keep_same_category' );?></label>
	<input type="checkbox" checked=checked id="keepcategory" name="keepcategory" /></p>

	<p><?php echo plugin_lang_get( 'fallback_category' );?>
	<select name="defaultcategory">
<?php print_category_option_list( );?>
	</select>
	</p>

	</td>
</tr>


<tr>
	<td colspan="2" class="center">
		<input type="submit" class="button" value="<?php echo lang_get( 'upload_file_button' )?>" />
	</td>
</tr>
</table>
</form>

</div>
<?php
html_page_bottom();
