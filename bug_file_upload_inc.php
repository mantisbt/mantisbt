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
 * This include file prints out the bug file upload form
 * It POSTs to bug_file_add.php
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses collapse_api.php
 * @uses config_api.php
 * @uses file_api.php
 * @uses form_api.php
 * @uses lang_api.php
 * @uses utility_api.php
 */

if( !defined( 'BUG_FILE_UPLOAD_INC_ALLOW' ) ) {
	return;
}

require_api( 'collapse_api.php' );
require_api( 'config_api.php' );
require_api( 'file_api.php' );
require_api( 'form_api.php' );
require_api( 'lang_api.php' );
require_api( 'utility_api.php' );

# check if we can allow the upload... bail out if we can't
if( !file_allow_bug_upload( $f_bug_id ) ) {
	return false;
}

$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
?>
<div class="col-md-6 col-xs-12">
	<div class="space-10"></div>
<?php
	$t_collapse_block = is_collapsed( 'upload_form' );
	$t_block_css = $t_collapse_block ? 'collapsed' : '';
	$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';
	$t_file_upload_max_num = max( 1, config_get( 'file_upload_max_num' ) );
?>
<form id="upload_form" method="post" enctype="multipart/form-data" action="bug_file_add.php" class="form-inline auto-dropzone-form">
<?php echo form_security_field( 'bug_file_add' ) ?>

<div class="widget-box widget-color-blue2 <?php echo $t_block_css ?>">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-upload"></i>
			<?php echo lang_get( $t_file_upload_max_num == 1 ? 'upload_file' : 'upload_files' ) ?>
		</h4>
		<div class="widget-toolbar">
			<a data-action="collapse" class="collapse-link" href="#">
				<i class="1 ace-icon <?php echo $t_block_icon ?> fa bigger-125"></i>
			</a>
		</div>
	</div>

	<div class="widget-body">
		<div class="widget-main no-padding">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed table-striped">
<tr>
	<td class="category" width="15%">
		<?php echo lang_get( $t_file_upload_max_num == 1 ? 'select_file' : 'select_files' ) ?>
		<br />
		<?php print_max_filesize( $t_max_file_size ); ?>
	</td>
	<td width="85%">
		<input type="hidden" name="bug_id" value="<?php echo $f_bug_id ?>" />
		<input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
		<div class="auto-dropzone center">
			<div class="space-10"></div>
			<i class="upload-icon ace-icon fa fa-cloud-upload blue fa-2x"></i>&#160;&#160;
			<span class="bigger-170 lighter grey"><?php echo lang_get( 'dropzone_default_message' ) ?></span>
			<div class="space-8"></div>
			<div id="auto-dropzone-previews-box" class="dropzone-previews dz-max-files-reached"></div>
			</div>
		<div class="fallback">
<?php
	# Display multiple file upload fields
	for( $i = 0; $i < $t_file_upload_max_num; $i++ ) {
?>
		<input id="ufile[]" name="ufile[]" type="file" size="50" />
<?php
		if( $t_file_upload_max_num > 1 ) {
			echo '<br />';
		}
	}
?>
	<br/>
		<input type="submit" class="btn btn-primary btn-sm btn-white btn-round"
			value="<?php echo lang_get( $t_file_upload_max_num == 1 ? 'upload_file_button' : 'upload_files_button' ) ?>"
		/>
	</div>
</td>
</tr>
</table>
</div>
</div>
</div>
</div>
</form>
</div>
<?php
include_once( dirname( __FILE__ ) . '/fileupload_inc.php' );
