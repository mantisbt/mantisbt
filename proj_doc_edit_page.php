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

$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );

html_page_top();
?>

<br />
<div>
<form method="post" enctype="multipart/form-data" action="proj_doc_update.php">
<?php echo form_security_field( 'proj_doc_update' ) ?>
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<input type="hidden" name="file_id" value="<?php echo $t_file_id ?>" />
		<?php echo lang_get( 'upload_file_title' ) ?>
	</td>
	<td class="right">
		<?php print_doc_menu() ?>
	</td>
</tr>
<tr class="row-1">
	<th class="category" width="20%">
		<label class="required"><span><?php echo lang_get( 'title' ) ?></span></label>
	</th>
	<td width="80%">
		<input type="text" name="title" size="70" maxlength="250" value="<?php echo $v_title ?>" />
	</td>
</tr>
<tr class="row-2">
	<th class="category">
		<?php echo lang_get( 'description' ) ?>
	</th>
	<td>
		<textarea name="description" cols="60" rows="7"><?php echo $v_description ?></textarea>
	</td>
</tr>
<tr class="row-1">
	<th class="category">
		<?php echo lang_get( 'filename' ) ?>
	</th>
	<td>
		<?php
			$t_href = '<a href="file_download.php?file_id='.$v_id.'&amp;type=doc">';
			echo $t_href;
			print_file_icon( $v_filename );
			echo '</a>&#160;' . $t_href . file_get_display_name( $v_filename ) . '</a>';
		?>
	</td>
</tr>
<tr class="row-2">
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
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td>
		<input type="submit" class="button" value="<?php echo lang_get( 'file_update_button' ) ?>" />
	</td>
</tr>
</table>
</form>

<br />
	<form method="post" action="proj_doc_delete.php">
	<?php echo form_security_field( 'proj_doc_delete' ) ?>
	<input type="hidden" name="file_id" value="<?php echo $t_file_id ?>" />
	<input type="hidden" name="title" value="<?php echo $v_title ?>" />
	<input type="submit" class="button" value="<?php echo lang_get( 'file_delete_button' ) ?>" />
	</form>
</div>

<?php
	html_page_bottom();
