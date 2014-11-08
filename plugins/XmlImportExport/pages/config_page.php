<?php
# Copyright (c) 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top();
//print_manage_menu();
?>

<br />
<div class="form-container">
<form action="<?php echo plugin_page( 'config' ) ?>" method="post">
<fieldset>
	<legend>
		<?php echo plugin_lang_get( 'config_title' ) ?>
	</legend>

	<?php echo form_security_field( 'plugin_XmlImportExport_config' ) ?>

	<!-- Import Access Level  -->
	<div class="field-container">
		<label for="import_threshold">
			<span><?php echo plugin_lang_get( 'import_threshold' ) ?></span>
		</label>
		<span class="select">
			<select id="import_threshold" name="import_threshold"><?php
				print_enum_string_option_list(
					'access_levels',
					plugin_config_get( 'import_threshold' )
				);
			?></select>
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Export Access Level  -->
	<div class="field-container">
		<label for="export_threshold">
			<span><?php echo plugin_lang_get( 'export_threshold' ) ?></span>
		</label>
		<span class="select">
			<select id="export_threshold" name="export_threshold"><?php
				print_enum_string_option_list(
					'access_levels',
					plugin_config_get( 'export_threshold' )
				);
			?></select>
		</span>
		<span class="label-style"></span>
	</div>

	<!-- Update button -->
	<div class="submit-button">
		<input type="submit" value="<?php echo plugin_lang_get( 'action_update' ) ?>"/>
	</div>

</fieldset>
</form>
</div>

<?php
html_page_bottom();
