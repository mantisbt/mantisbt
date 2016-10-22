<?php
	require_once '../core.php';

	header("Content-Type: application/javascript");
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

