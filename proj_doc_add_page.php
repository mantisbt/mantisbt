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
 * Add documentation to project
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'utility_api.php' );

# Check if project documentation feature is enabled.
if( OFF == config_get( 'enable_project_documentation' ) ||
	!file_is_uploading_enabled() ||
	!file_allow_project_upload() ) {
	access_denied();
}

access_ensure_project_level( config_get( 'upload_project_file_threshold' ) );

$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );

html_page_top();
?>

<br />
<div>
<form method="post" enctype="multipart/form-data" action="proj_doc_add.php">
<?php echo form_security_field( 'proj_doc_add' ) ?>
<table class="width75" cellspacing="1">
<tr>
	<td class="form-title">
		<?php echo lang_get( 'upload_file_title' ) ?>
	</td>
	<td class="right">
		<?php print_doc_menu( 'proj_doc_add_page.php' ) ?>
	</td>
</tr>
<tr class="row-1">
	<th class="category" width="25%">
		<label class="required"><span><?php echo lang_get( 'title' ) ?></span></label>
	</th>
	<td width="75%">
		<input type="text" name="title" size="70" maxlength="250" />
	</td>
</tr>
<tr class="row-2">
	<th class="category">
		<?php echo lang_get( 'description' ) ?>
	</th>
	<td>
		<textarea name="description" cols="60" rows="7"></textarea>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<label class="required"><span><?php echo lang_get( 'select_file' ) ?></span></label>
		<br />
		<?php print_max_filesize( $t_max_file_size ); ?>
	</td>
	<td>
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
		<input name="file" type="file" size="70" />
	</td>
</tr>
<tr>
	<td class="left">
		<span class="required"> * <?php echo lang_get( 'required' ) ?></span>
	</td>
	<td class="center">
		<input type="submit" class="button" value="<?php echo lang_get( 'upload_file_button' ) ?>" />
	</td>
</tr>
</table>
</form>
</div>

<?php
html_page_bottom();
