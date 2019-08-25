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
 * Edit Project Documentation
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses database_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'database_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

# Check if project documentation feature is enabled.
if( OFF == config_get( 'enable_project_documentation' ) ||
	!file_is_uploading_enabled() ||
	!file_allow_project_upload() ) {
	access_denied();
}

$t_file_id = gpc_get_int( 'file_id' );

$t_project_id = file_get_field( $t_file_id, 'project_id', 'project' );

access_ensure_project_level( config_get( 'upload_project_file_threshold' ), $t_project_id );

$t_query = 'SELECT * FROM {project_file} WHERE id=' . db_param();
$t_result = db_query( $t_query, array( $t_file_id ) );
$t_row = db_fetch_array( $t_result );
extract( $t_row, EXTR_PREFIX_ALL, 'v' );

$v_title = string_attribute( $v_title );
$v_description = string_textarea( $v_description );

$t_max_file_size = file_get_max_file_size();

layout_page_header();

layout_page_begin( 'proj_doc_page.php' );

print_doc_menu();

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">
<form method="post" enctype="multipart/form-data" action="proj_doc_update.php">
<?php echo form_security_field( 'proj_doc_update' ) ?>
<input type="hidden" name="file_id" value="<?php echo $t_file_id ?>"/>
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-edit"></i>
			<?php echo lang_get('upload_file_title') ?>
		</h4>
	</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
<tr>
	<th class="category" width="20%">
		<span class="required">*</span> <?php echo lang_get( 'title' ) ?>
	</th>
	<td width="80%">
		<input type="text" name="title" class="input-sm" size="70" maxlength="250" value="<?php echo $v_title ?>" required />
	</td>
</tr>
<tr>
	<th class="category">
		<?php echo lang_get( 'description' ) ?>
	</th>
	<td>
		<?php # Newline after opening textarea tag is intentional, see #25839 ?>
		<textarea class="form-control" name="description" cols="60" rows="7">
<?php echo $v_description ?>
</textarea>
	</td>
</tr>
<tr>
	<th class="category">
		<?php echo lang_get( 'filename' ) ?>
	</th>
	<td>
		<?php
			$t_href = '<a href="file_download.php?file_id='.$v_id.'&amp;type=doc">';
			echo $t_href;
			print_file_icon( $v_filename );
			echo '</a>&#160;' . $t_href . string_html_specialchars( file_get_display_name( $v_filename ) ) . '</a>';
		?>
	</td>
</tr>
<tr>
	<td class="category">
		<?php echo lang_get( 'select_file' ); ?>
		<br />
		<?php print_max_filesize( $t_max_file_size ); ?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
		<input name="file" type="file" size="70" />
	</td>
<tr>
</table>
</div>
</div>
	<div class="widget-toolbox padding-8 clearfix">
		<span class="required pull-right"> * <?php echo lang_get('required') ?></span>
		<input type="submit" class="btn btn-primary btn-white btn-round"
			value="<?php echo lang_get('file_update_button') ?>"/>
	</div>
</div>
</div>
</form>
</div>

	<div class="space-10"></div>
	<form method="post" action="proj_doc_delete.php">
	<?php echo form_security_field( 'proj_doc_delete' ) ?>
	<input type="hidden" name="file_id" value="<?php echo $t_file_id ?>" />
	<input type="hidden" name="title" value="<?php echo $v_title ?>" />
	<input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="<?php echo lang_get( 'file_delete_button' ) ?>" />
	</form>
</div>

<?php
layout_page_end();
