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
 * Handle JavaScript required for the fileupload widget
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 */

# Prevent output of HTML in the content if errors occur
define( 'DISABLE_INLINE_ERROR_REPORTING', true );

require_once( 'core.php' );
require_api( 'config_api.php' );


# Send correct MIME Content-Type header for JavaScript content.
# See http://www.rfc-editor.org/rfc/rfc4329.txt for details on why application/javascript is the correct MIME type.
header( 'Content-Type: application/javascript; charset=UTF-8' );


# Don't let Internet Explorer second-guess our content-type, as per
# http://blogs.msdn.com/b/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
header( 'X-Content-Type-Options: nosniff' );


# WARNING: DO NOT EXPOSE SENSITIVE CONFIGURATION VALUES!
#
# All configuration values below are publicly available to visitors of the bug
# tracker regardless of whether they're authenticated. Server paths should not
# be exposed. It is OK to expose paths that the user sees directly (short
# paths) but you do need to be careful in your selections. Consider servers
# using URL rewriting engines to mask/convert user-visible paths to paths that
# should only be known internally to the server.
?>
Dropzone.autoDiscover = false;
function enableDropzone( classPrefix, autoUpload ) {
	try {
		var zone = new Dropzone( "." + classPrefix + "-form", {
			forceFallback: <?php echo config_get( 'dropzone_enabled' ) ? 'false' : 'true' ?>,
			paramName: "ufile",
			autoProcessQueue: autoUpload,
			clickable: '.' + classPrefix,
			previewsContainer: '#' + classPrefix + '-previews-box',
			uploadMultiple: true,
			parallelUploads: 100,
			maxFilesize: <?php echo ceil( config_get( 'max_file_size' ) / (1000 * 1024) ) ?>,
			addRemoveLinks: !autoUpload,
			acceptedFiles: '<?php echo config_get( 'allowed_files' ) ?>',
			previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-details\">\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n    <div class=\"dz-size\" data-dz-size></div>\n    <img data-dz-thumbnail />\n  </div>\n  <div class=\"progress progress-small progress-striped active\"><div class=\"progress-bar progress-bar-success\" data-dz-uploadprogress></div></div>\n  <div class=\"dz-success-mark\"><span></span></div>\n  <div class=\"dz-error-mark\"><span></span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n</div>",
			dictDefaultMessage: "<?php echo lang_get( 'dropzone_default_message' ) ?>",
			dictFallbackMessage: "<?php echo lang_get( 'dropzone_fallback_message' ) ?>",
			dictFallbackText: "<?php echo lang_get( 'dropzone_fallback_text' ) ?>",
			dictFileTooBig: "<?php echo lang_get( 'dropzone_file_too_big' ) ?>",
			dictInvalidFileType: "<?php echo lang_get( 'dropzone_invalid_file_type' ) ?>",
			dictResponseError: "<?php echo lang_get( 'dropzone_response_error' ) ?>",
			dictCancelUpload: "<?php echo lang_get( 'dropzone_cancel_upload' ) ?>",
			dictCancelUploadConfirmation: "<?php echo lang_get( 'dropzone_cancel_upload_confirmation' ) ?>",
			dictRemoveFile: "<?php echo lang_get( 'dropzone_remove_file' ) ?>",
			dictRemoveFileConfirmation: "<?php echo lang_get( 'dropzone_remove_file_confirmation' ) ?>",
			dictMaxFilesExceeded: "<?php echo lang_get( 'dropzone_max_files_exceeded' ) ?>",

			init: function () {
				var dropzone = this;
				$( "input[type=submit]" ).on( "click", function (e) {
					if( dropzone.getQueuedFiles().length ) {
						e.preventDefault();
						e.stopPropagation();
						dropzone.processQueue();
					}
				});
				this.on( "successmultiple", function( files, response ) {
					document.open();
					document.write( response );
					document.close();
				});
			},
			fallback: function() {
				if( $( "." + classPrefix ).length ) {
					$( "." + classPrefix ).hide();
				}
			}
		});
	} catch (e) {
		alert( '<?php echo lang_get( 'dropzone_not_supported' ) ?>' );
	}
}

$(document).ready( function() {
	if( $( ".dropzone-form" ).length ) {
		enableDropzone( "dropzone", false );
	}
	if( $( ".auto-dropzone-form" ).length ) {
		enableDropzone( "auto-dropzone", true );
	}
});