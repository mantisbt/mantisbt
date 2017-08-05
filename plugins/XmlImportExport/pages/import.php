<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Import XML Issues Page
 */

access_ensure_project_level( plugin_config_get( 'import_threshold' ) );

auth_reauthenticate( );

layout_page_header( plugin_lang_get( 'import' ) );

layout_page_begin( 'manage_overview_page.php' );

$t_this_page = plugin_page( 'import' ); # FIXME with plugins this does not work...
print_manage_menu( $t_this_page );

$t_max_file_size = (int)min(
	ini_get_number( 'upload_max_filesize' ),
	ini_get_number( 'post_max_size' ),
	config_get( 'max_file_size' )
);

# We need a project to import into
$t_project_id = helper_get_current_project( );
if( ALL_PROJECTS == $t_project_id ) {
	print_header_redirect( 'login_select_proj_page.php?ref=' . $t_this_page );
}

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" >

<form id="file_upload" method="post" enctype="multipart/form-data" action="<?php echo plugin_page( 'import_action' )?>">
<?php echo form_security_field( 'plugin_xml_import_action' ) ?>

<input type="hidden" name="project_id" value="<?php echo $t_project_id;?>" />

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
<i class="ace-icon fa fa-upload"></i>
<?php
	printf(
		plugin_lang_get( 'importing_in_project' ),
		string_display( project_get_field( $t_project_id, 'name' ) )
	);
?>
</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
	<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">

<tr>
	<th class="category" width="25%">
		<?php echo lang_get( 'select_file' )?><br />
		<?php echo '<span class="small">(' . lang_get( 'max_file_size_label' ) . ' ' . number_format( $t_max_file_size / 1000 ) . 'k)</span>'?>
	</th>
	<td width="85%">
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size?>" />
		<input type="hidden" name="step" value="1" />
		<input name="file" type="file" size="40" />
	</td>
</tr>
<tr>
	<td class="bold" colspan="2">
<?php
	echo plugin_lang_get( 'import_options' );
?>
	</td>
</tr>

<tr>
	<th class="category" width="25%">
		<?php echo plugin_lang_get( 'cross_references' );?>
	</th>
	<td>
		<?php echo plugin_lang_get( 'default_strategy' );?>
		<select class="input-sm" name="strategy">
			<option value="renumber" title="<?php echo plugin_lang_get( 'renumber_desc' );?>">
			<?php echo plugin_lang_get( 'renumber' );?></option>
			<option value="link" title="<?php echo plugin_lang_get( 'link_desc' );?>">
			<?php echo plugin_lang_get( 'link' );?></option>
			<option value="disable" title="<?php echo plugin_lang_get( 'disable_desc' );?>">
			<?php echo plugin_lang_get( 'disable' );?></option>
		</select>
		<br><br>

		<?php echo plugin_lang_get( 'fallback' );?>
		<select class="input-sm" name="fallback">
			<option value="link" title="<?php echo plugin_lang_get( 'link_desc' );?>">
			<?php echo plugin_lang_get( 'link' );?></option>
			<option value="disable" title="<?php echo plugin_lang_get( 'disable_desc' );?>">
			<?php echo plugin_lang_get( 'disable' );?></option>
		</select>
	</td>
</tr>

<tr>
	<th class="category" width="25%">
		<?php echo lang_get( 'categories' );?>
	</th>
	<td>
		<label for="keepcategory">
		<input type="checkbox" class="ace" checked="checked" id="keepcategory" name="keepcategory" />
		<span class="lbl"> <?php echo plugin_lang_get( 'keep_same_category' );?> </span>
		</label>
		<br><br>

		<?php echo plugin_lang_get( 'fallback_category' );?>
		<select class="input-sm" name="defaultcategory">
			<?php print_category_option_list( );?>
		</select>
	</td>
</tr>
</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'upload_file_button' )?>" />
</div>
</div>
</div>
</form>
</div>
</div>

<?php
layout_page_end();
