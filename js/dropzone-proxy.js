/*
# Mantis - a php based bugtracking system

# Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright 2026 MantisBT Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.
 */

/* jshint esversion: 8 */
/* globals Dropzone, $ */

Dropzone.autoDiscover = false;

$(function() {
	'use strict';

	$('form .dropzone').each( function() {
		const zoneObj = enableDropzone( 'dropzone', $(this).hasClass('auto-dropzone') );
		if( zoneObj ) {
			/* Attach file paste handler to parent form */
			$(this).closest( 'form' ).on( 'paste', function( event ) {
				const items = ( event.clipboardData || event.originalEvent.clipboardData ).items;
				for( const index in items ) {
					const item = items[index];
					if( item.kind === 'file' ) {
						zoneObj.addFile( item.getAsFile() )
					}
				}
			});
		}
	});

	// Dropzone handler
	function enableDropzone( classPrefix, autoUpload ) {
		var zone_class =  '.' + classPrefix;
		var zone = $( zone_class );
		var form = zone.closest('form');
		var max_filesize_bytes = zone.data('max-filesize-bytes');
		var max_filesize_mb = Math.ceil( max_filesize_bytes / ( 1024*1024) );
		var max_filename_length = zone.data( 'max-filename-length' );
		var options = {
			forceFallback: zone.data('force-fallback'),
			paramName: "ufile",
			autoProcessQueue: autoUpload,
			clickable: zone_class,
			previewsContainer: '#' + classPrefix + '-previews-box',
			uploadMultiple: true,
			parallelUploads: 100,
			maxFilesize: max_filesize_mb,
			timeout: 0,
			addRemoveLinks: false,
			acceptedFiles: zone.data('accepted-files'),
			thumbnailWidth: 150,
			thumbnailMethod: 'contain',
			dictDefaultMessage: zone.data('default-message'),
			dictFallbackMessage: zone.data('fallback-message'),
			dictFallbackText: zone.data('fallback-text'),
			dictFileTooBig: zone.data('file-too-big'),
			dictInvalidFileType: zone.data('invalid-file-type'),
			dictResponseError: zone.data('response-error'),
			dictCancelUpload: zone.data('cancel-upload'),
			dictCancelUploadConfirmation: zone.data('cancel-upload-confirmation'),
			dictRemoveFile: zone.data('remove-file'),
			dictRemoveFileConfirmation: zone.data('remove-file-confirmation'),
			dictMaxFilesExceeded: zone.data('max-files-exceeded'),

			init: function () {
				var dropzone = this;
				var form = $( this.options.clickable ).closest('form');
				form.on('submit', function (e) {
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
				this.on( "complete", function( file ) {
					// Set progress bar as inactive
					let progressbar = file.previewElement.querySelector('.progress');
					progressbar.classList.remove('active');
				});
				/**
				 * 'addedfiles' is undocumented but works similar to 'addedfile'
				 * It's triggered once after a multiple file addition, and receives
				 * an array with the added files.
				 */
				this.on("addedfiles", function (files) {
					var bullet = '-\u00A0';
					var error_file_too_big = '';
					var error_filename_too_long = '';
					for (var i = 0; i < files.length; i++) {
						if( files[i].size > max_filesize_bytes ) {
							var size_mb = files[i].size / ( 1024*1024 );
							var dec = size_mb < 0.01 ? 3 : 2;
							error_file_too_big += bullet + '"' + files[i].name + '" (' + size_mb.toFixed(dec) + ' MiB)\n';
							this.removeFile( files[i] );
						} else if( files[i].name.length > 250 ) {
							error_filename_too_long += bullet + '"' + files[i].name + '" (' + files[i].name.length + ')\n';
							// this.removeFile( files[i] );
						}
					}

					var text = '';
					var error_message = '';
					if( error_file_too_big ) {
						var max_mb = max_filesize_bytes / ( 1024*1024 );
						var max_mb_dec = max_mb < 0.01 ? 3 : 2;
						text = zone.data( 'dropzone_multiple_files_too_big' ) + "\n";
						text = text.replace( '{{files}}', '\n' + error_file_too_big );
						text = text.replace( '{{maxFilesize}}', max_mb.toFixed(max_mb_dec) );
						error_message += text;
					}

					if( error_filename_too_long ) {
						text = zone.data( 'dropzone_multiple_filenames_too_long' ) + "\n";
						text = text.replace( '{{maxFilenameLength}}', max_filename_length );
						text = text.replace( '{{files}}', '\n' + error_filename_too_long );
						error_message += text;
					}
					if( error_message ) {
						alert(error_message);
					}
				});
			},
			fallback: function() {
				if( $( "." + classPrefix ).length ) {
					$( "." + classPrefix ).hide();
				}
			}
		};
		var preview_template = document.getElementById('dropzone-preview-template');
		if( preview_template ) {
			options.previewTemplate = preview_template.innerHTML;
		}

		var zone_object = null;
		try {
			zone_object = new Dropzone( form[0], options );
		} catch (e) {
			alert( zone.data('dropzone-not-supported') );
		}

		return zone_object;
	}
});
