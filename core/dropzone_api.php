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
 * Dropzone API
 *
 * @package CoreAPI
 * @subpackage DropzoneAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses file_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses string_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'file_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );

# Include Dropzone.js
if( config_get_global( 'cdn_enabled' ) == ON ) {
	require_css( [ 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/' . DROPZONE_VERSION . '/min/dropzone.min.css' ] );
	require_js( [ 'https://cdnjs.cloudflare.com/ajax/libs/dropzone/' . DROPZONE_VERSION . '/min/dropzone.min.js', DROPZONE_HASH ] );
} else {
	require_css( 'dropzone-' . DROPZONE_VERSION . '.min.css' );
	require_js( 'dropzone-' . DROPZONE_VERSION . '.min.js' );
}
require_js( 'dropzone-proxy.js' );

/**
 * Populate form element with dropzone data attributes
 * @return void
 */
function dropzone_print_form_data() {
	echo 'data-force-fallback="' . ( config_get( 'dropzone_enabled' ) ? 'false' : 'true' ) . '"' . "\n";
	echo "\t" . 'data-max-filesize-bytes="'. file_get_max_file_size() . '"' . "\n";
	echo "\t" . 'data-max-filename-length="'. DB_FIELD_SIZE_FILENAME . '"' . "\n";
	$t_allowed_files = config_get( 'allowed_files' );
	if ( !empty ( $t_allowed_files ) ) {
		$t_allowed_files = '.' . implode ( ',.', explode ( ',', $t_allowed_files ) );
	}
	echo "\t" . 'data-accepted-files="' . $t_allowed_files . '"' . "\n";
	echo "\t" . 'data-default-message="' . string_html_specialchars( lang_get( 'dropzone_default_message' ) ) . '"' . "\n";
	echo "\t" . 'data-fallback-message="' . string_html_specialchars( lang_get( 'dropzone_fallback_message' ) ) . '"' . "\n";
	echo "\t" . 'data-fallback-text="' . string_html_specialchars( lang_get( 'dropzone_fallback_text' ) ) . '"' . "\n";
	echo "\t" . 'data-file-too-big="' . string_html_specialchars( lang_get( 'dropzone_file_too_big' ) ) . '"' . "\n";
	echo "\t" . 'data-invalid-file-type="' . string_html_specialchars( lang_get( 'dropzone_invalid_file_type' ) ) . '"' . "\n";
	echo "\t" . 'data-response-error="' . string_html_specialchars( lang_get( 'dropzone_response_error' ) ) . '"' . "\n";
	echo "\t" . 'data-cancel-upload="' . string_html_specialchars( lang_get( 'dropzone_cancel_upload' ) ) . '"' . "\n";
	echo "\t" . 'data-cancel-upload-confirmation="' . string_html_specialchars( lang_get( 'dropzone_cancel_upload_confirmation' ) ) . '"' . "\n";
	echo "\t" . 'data-remove-file=""' . "\n";
	echo "\t" . 'data-remove-file-confirmation="' . string_html_specialchars( lang_get( 'dropzone_remove_file_confirmation' ) ) . '"' . "\n";
	echo "\t" . 'data-max-files-exceeded="' . string_html_specialchars( lang_get( 'dropzone_max_files_exceeded' ) ) . '"' . "\n";
	echo "\t" . 'data-dropzone-not-supported="' . string_html_specialchars( lang_get( 'dropzone_not_supported' ) ) . '"';
	echo "\t" . 'data-dropzone_multiple_files_too_big="' . string_html_specialchars( lang_get( 'dropzone_multiple_files_too_big' ) ) . '"';
	echo "\t" . 'data-dropzone_multiple_filenames_too_long="' . string_html_specialchars( lang_get( 'dropzone_multiple_filenames_too_long' ) ) . '"';
}

/**
 * Populate a hidden div where its inner html will be used as preview template
 * for dropzone attached files
 * @return void
 */
function dropzone_print_template() {
	?>
	<div id="dropzone-preview-template" class="hidden">
		<div class="dz-preview dz-file-preview">
			<div class="dz-filename"><span data-dz-name></span></div>
			<img src="data:image/png;base64," alt="" data-dz-thumbnail>
			<div class="dz-error-message">
				<div class="dz-error-mark"><span><?php print_icon('fa-times-circle') ?></span></div>
				<span data-dz-errormessage></span>
			</div>
			<div class="dz-size" data-dz-size></div>
			<div class="progress progress-small progress-striped active">
				<div class="progress-bar progress-bar-success" data-dz-uploadprogress></div>
			</div>
			<a class="btn btn-primary btn-white btn-round btn-xs" data-dz-remove>
				<?php echo string_html_specialchars( lang_get( 'dropzone_remove_file' ) ) ?>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Populate form element with dropzone
 * @return void
 */
function dropzone_print() {
	dropzone_print_template();
	?>
	<input type="hidden" name="max_file_size" value="<?php echo file_get_max_file_size() ?>">
	<div class="dropzone center" <?php dropzone_print_form_data() ?>>
		<?php print_icon( 'fa-cloud-upload', 'upload-icon ace-icon blue fa-3x' ) ?>
		<br>
		<span class="bigger-150 grey"><?php echo string_html_specialchars( lang_get( 'dropzone_default_message' ) ) ?></span>
		<div id="dropzone-previews-box" class="dropzone-previews dz-max-files-reached"></div>
	</div>
	<div class="fallback">
		<div class="dz-message" data-dz-message></div>
		<input <?php echo helper_get_tab_index() ?> id="ufile[]" name="ufile[]" type="file" size="60">
	</div>
	<?php
}
