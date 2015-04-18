<?php
# Copyright (c) 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top();
//print_manage_menu();
?>

<br />
<form action="<?php echo plugin_page( 'config' ) ?>" method="post">
<?php echo form_security_field( 'plugin_XmlImportExport_config' ) ?>
<table class="width60" align="center">

<tr>
<td class="form-title" colspan="2"><?php echo plugin_lang_get("config_title") ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'import_threshold' ) ?></td>
<td><select name="import_threshold"><?php
	print_enum_string_option_list(
		'access_levels',
		plugin_config_get( 'import_threshold' )
	);
	?></select></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo plugin_lang_get( 'export_threshold' ) ?></td>
<td><select name="export_threshold"><?php
	print_enum_string_option_list(
		'access_levels',
		plugin_config_get( 'export_threshold' )
	);
	?></select></td>
</tr>

<tr>
<td class="center" colspan="2"><input type="submit" value="<?php echo plugin_lang_get("action_update") ?>"/></td>
</tr>

</table>
</form>

<?php
html_page_bottom();
