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


html_css_link( 'dropzone.css' );
html_javascript_link( 'dropzone.min.js');
?>

<!-- The template to display files available for download -->
<script type="text/javascript">

	function enableDropzone( classPrefix, autoUpload ) {
		try {
			var zone = new Dropzone( "." + classPrefix + "-form", {
				forceFallback: <?php echo config_get( 'use_file_dropzone' ) ? 'false' : 'true' ?>,
				paramName: "ufile",
				autoProcessQueue: autoUpload,
				clickable: '.' + classPrefix,
				previewsContainer: '#' + classPrefix + '-previews-box',
				uploadMultiple: true,
				parallelUploads: 100,
				maxFilesize: <?php echo ceil( ( config_get( 'max_file_size' ) / (1000 * 1024) ) ) ?>,
				acceptedFiles: '<?php echo config_get( 'allowed_files' )  ?>',
				addRemoveLinks: !autoUpload,
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
			alert( 'Dropzone.js does not support older browsers!' );
		}
	}


	$(document).ready( function() {
		Dropzone.autoDiscover = false;

		if( $( ".dropzone-form" ).length ) {
			enableDropzone( "dropzone", false );
		}
		if( $( ".auto-dropzone-form" ).length ) {
			enableDropzone( "auto-dropzone", true );
		}
	});

</script>
