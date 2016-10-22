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
 * This include file prints out the fileupload widget
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 */
?>
	data-force-fallback="<?php echo config_get( 'dropzone_enabled' ) ? 'false' : 'true' ?>"
	data-max-filesize="<?php echo ceil( config_get( 'max_file_size' ) / (1000 * 1024) ) ?>"
	data-accepted-files="<?php echo config_get( 'allowed_files' ) ?>"
	data-default-message="<?php echo htmlspecialchars( lang_get( 'dropzone_default_message' ) ) ?>"
	data-fallback-message="<?php echo htmlspecialchars( lang_get( 'dropzone_fallback_message' ) ) ?>"
	data-fallback-text="<?php echo htmlspecialchars( lang_get( 'dropzone_fallback_text' ) ) ?>"
	data-file-too-big="<?php echo htmlspecialchars( lang_get( 'dropzone_file_too_big' ) ) ?>"
	data-invalid-file-type="<?php echo htmlspecialchars( lang_get( 'dropzone_invalid_file_type' ) ) ?>"
	data-response-error="<?php echo htmlspecialchars( lang_get( 'dropzone_response_error' ) ) ?>"
	data-cancel-upload="<?php echo htmlspecialchars( lang_get( 'dropzone_cancel_upload' ) ) ?>"
	data-cancel-upload-confirmation="<?php echo htmlspecialchars( lang_get( 'dropzone_cancel_upload_confirmation' ) ) ?>"
	data-remove-file="<?php echo htmlspecialchars( lang_get( 'dropzone_remove_file' ) ) ?>"
	data-remove-file-confirmation="<?php echo htmlspecialchars( lang_get( 'dropzone_remove_file_confirmation' ) ) ?>"
	data-max-files-exceeded="<?php echo htmlspecialchars( lang_get( 'dropzone_max_files_exceeded' ) ) ?>"
	data-dropzone-not-supported="<?php echo htmlspecialchars( lang_get( 'dropzone_not_supported' ) ) ?>"


