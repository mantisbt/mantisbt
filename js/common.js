/*
# Mantis - a php based bugtracking system

# Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright 2002 MantisBT Team   - mantisbt-dev@lists.sourceforge.net

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

/*
 * Collapsible element functions
 */
var g_collapse_clear = 1;

// global code to determine how to set visibility
var a = navigator.userAgent.indexOf("MSIE");
var style_display;

if (a!= -1) {
	style_display = 'block';
} else {
	style_display = 'table-row';
}
style_display = 'block';

$(document).ready( function() {
    $('.collapse-open').show();
    $('.collapse-closed').hide();
    $('.collapse-link').click( function(event) {
        event.preventDefault();
        var id = $(this).attr('id');
        var t_pos = id.indexOf('_closed_link' );
        if( t_pos == -1 ) {
            t_pos = id.indexOf('_open_link' );
        }
        var t_div = id.substring(0, t_pos );
        ToggleDiv( t_div );
    });

    $('.widget-box').on('shown.ace.widget' , function(event) {
       var t_id = $(this).attr('id');
       var t_cookie = GetCookie( "collapse_settings" );
        if ( 1 == g_collapse_clear ) {
            t_cookie = "";
            g_collapse_clear = 0;
        }
        t_cookie = t_cookie.replace("|" + t_id + ":1", '' );
        t_cookie = t_cookie + "|" + t_id + ":0";
        SetCookie( "collapse_settings", t_cookie );
	});

    $('.widget-box').on('hidden.ace.widget' , function(event) {
        var t_id = $(this).attr('id');
        var t_cookie = GetCookie( "collapse_settings" );
        if ( 1 == g_collapse_clear ) {
            t_cookie = "";
            g_collapse_clear = 0;
        }
        t_cookie = t_cookie.replace( "|" + t_id + ":0", '' );
        t_cookie = t_cookie + "|" + t_id + ":1";
        SetCookie( "collapse_settings", t_cookie );
    });

    $('#sidebar.sidebar-toggle').on('click', function (event) {
        var t_id = $(this).attr('id');
        var t_cookie = GetCookie("collapse_settings");
        if (1 == g_collapse_clear) {
            t_cookie = "";
            g_collapse_clear = 0;
        }
        if( $(this).parent().hasClass( "menu-min" ) ) {
            t_cookie = t_cookie.replace("|" + t_id + ":1", '');
            t_cookie = t_cookie + "|" + t_id + ":0";
        } else {
            t_cookie = t_cookie.replace("|" + t_id + ":0", '');
            t_cookie = t_cookie + "|" + t_id + ":1";
        }
        SetCookie("collapse_settings", t_cookie);
    });

    $('input[type=text].autocomplete').autocomplete({
		source: function(request, callback) {
			var fieldName = $(this).attr('element').attr('id');
			var postData = {};
			postData['entrypoint']= fieldName + '_get_with_prefix';
			postData[fieldName] = request.term;
			$.getJSON('xmlhttprequest.php', postData, function(data) {
				var results = [];
				$.each(data, function(i, value) {
					var item = {};
					item.label = $('<div/>').text(value).html();
					item.value = value;
					results.push(item);
				});
				callback(results);
			});
		}
	});

	$('a.dynamic-filter-expander').click(function(event) {
		event.preventDefault();
		var fieldID = $(this).attr('id');
		var targetID = fieldID + '_target';
		var viewType = $('#filters_form_open input[name=view_type]').val();
		$('#' + targetID).html('<span class="dynamic-filter-loading">' + translations['loading'] + "</span>");
		$.ajax({
			url: 'return_dynamic_filters.php',
			data: 'view_type=' + viewType + '&filter_target=' + fieldID,
			cache: false,
			context: $('#' + targetID),
			success: function(html) {
				$(this).html(html);
			},
			error: function(obj,status,error) {
				$(this).html('<span class="error-msg">' + status + ': ' + error + '</span>');
			}
		});
	});

	$('input.autofocus:first, select.autofocus:first, textarea.autofocus:first').focus();

	var checkAllSelectors = '';
	$(':checkbox.check_all').each(function() {
		var baseFieldName = $(this).attr('name').replace(/_all$/, '');
		if (checkAllSelectors.length > 0) {
			checkAllSelectors += ', ';
		}
		checkAllSelectors += ':checkbox[name="' + baseFieldName + '[]"]';
	});
	if (checkAllSelectors.length > 0) {
		$(checkAllSelectors).click(function() {
			var fieldName = $(this).attr('name').replace(/\[\]/g, '');
			var checkedCount = $(this).closest('form').find(':checkbox[name="' + fieldName + '[]"]:checked').length;
			var totalCount = $(this).closest('form').find(':checkbox[name="' + fieldName + '[]"]').length;
			var allSelected = checkedCount == totalCount;
			$(this).closest('form').find(':checkbox[name=' + fieldName + '_all]').prop('checked', allSelected);
		});
		$(':checkbox.check_all').click(function() {
			var baseFieldName = $(this).attr('name').replace(/_all$/, '');
			$(this).closest('form').find(':checkbox[name="' + baseFieldName + '[]"]').prop('checked', $(this).is(':checked'));
		});
	}

	var stopwatch = {
		timerID: null,
		elapsedTime: 0,
		tick: function() {
			this.elapsedTime += 1000;
			var seconds = Math.floor(this.elapsedTime / 1000) % 60;
			var minutes = Math.floor(this.elapsedTime / 60000) % 60;
			var hours = Math.floor(this.elapsedTime / 3600000) % 60;
			if (seconds < 10) {
				seconds = '0' + seconds;
			}
			if (minutes < 10) {
				minutes = '0' + minutes;
			}
			if (hours < 10) {
				hours = '0' + hours;
			}
			$('input[type=text].stopwatch_time').val(hours + ':' + minutes + ':' + seconds);
			this.start();
		},
		reset: function() {
			this.stop();
			this.elapsedTime = 0;
			$('input[type=text].stopwatch_time').val('');
		},
		start: function() {
			this.stop();
			var self = this;
			this.timerID = window.setTimeout(function() {
				self.tick();
			}, 1000);
		},
		stop: function() {
			if (typeof this.timerID == 'number') {
				window.clearTimeout(this.timerID);
				delete this.timerID;
			}
		}
	};
	$('input[type=button].stopwatch_toggle').click(function() {
		if (stopwatch.elapsedTime == 0) {
			stopwatch.stop();
			stopwatch.start();
			$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_stop']);
		} else if (typeof stopwatch.timerID == 'number') {
			stopwatch.stop();
			$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_start']);
		} else {
			stopwatch.start();
			$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_stop']);
		}
	});
	$('input[type=button].stopwatch_reset').click(function() {
		stopwatch.reset();
		$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_start']);
	});

	$('input[type=text].datetime').each(function(index, element) {
		$(this).after('&nbsp;<i class="fa fa-calendar fa-lg datetime" id="' + element.id + '_datetime_button' + '"></i>');
		Calendar.setup({
			inputField: element.id,
			timeFormat: 24,
			showsTime: true,
			ifFormat: config['calendar_js_date_format'],
			button: element.id + '_datetime_button'
		});
	});

	if( $( ".dropzone-form" ).length ) {
		enableDropzone( "dropzone", false );
	}
	if( $( ".auto-dropzone-form" ).length ) {
		enableDropzone( "auto-dropzone", true );
	}

	$('.bug-jump').find('[name=bug_id]').focus( function() {
		var bug_label = $('.bug-jump-form').find('[name=bug_label]').val();
		if( $(this).val() == bug_label ) {
			$(this).val('');
			$(this).removeClass('field-default');
		}
	});
	$('.bug-jump').find('[name=bug_id]').blur( function() {
		var bug_label = $('.bug-jump-form').find('[name=bug_label]').val();
		if( $(this).val() == '' ) {
			$(this).val(bug_label);
			$(this).addClass('field-default');
		}
	});
	$('[name=source_query_id]').change( function() {
		$(this).parent().submit();
	});

	/* Project selector: auto-switch on select */
	$('#form-set-project-id').change( function() {
		$('#form-set-project').submit();
	});
	$('#project-selector').children('.button').hide();

	setBugLabel();

	$(document).on('click', 'input[type=checkbox]#use_date_filters', function() {
		if (!$(this).is(':checked')) {
			$('div.filter-box select[name=start_year]').prop('disabled', true);
			$('div.filter-box select[name=start_month]').prop('disabled', true);
			$('div.filter-box select[name=start_day]').prop('disabled', true);
			$('div.filter-box select[name=end_year]').prop('disabled', true);
			$('div.filter-box select[name=end_month]').prop('disabled', true);
			$('div.filter-box select[name=end_day]').prop('disabled', true);
		} else {
			$('div.filter-box select[name=start_year]').prop('disabled', false);
			$('div.filter-box select[name=start_month]').prop('disabled', false);
			$('div.filter-box select[name=start_day]').prop('disabled', false);
			$('div.filter-box select[name=end_year]').prop('disabled', false);
			$('div.filter-box select[name=end_month]').prop('disabled', false);
			$('div.filter-box select[name=end_day]').prop('disabled', false);
		}
	});

	/* Handle custom field of date type */
	$(document).on('change', 'select[name^=custom_field_][name$=_control]', function() {
		var table = $(this).closest('table');
		switch(this.value) {
			case '2': // between
				$(table).find("select[name*=_start_year]").prop('disabled', false);
				$(table).find("select[name*=_start_month]").prop('disabled', false);
				$(table).find("select[name*=_start_day]").prop('disabled', false);
				$(table).find("select[name*=_end_year]").prop('disabled', false);
				$(table).find("select[name*=_end_month]").prop('disabled', false);
				$(table).find("select[name*=_end_day]").prop('disabled', false);
				break;

			case '3': // on or before
			case '4': // before
			case '5': // on
			case '6': // after
			case '7': // on or after
				$(table).find("select[name*=_start_year]").prop('disabled', false);
				$(table).find("select[name*=_start_month]").prop('disabled', false);
				$(table).find("select[name*=_start_day]").prop('disabled', false);
				$(table).find("select[name*=_end_year]").prop('disabled', true);
				$(table).find("select[name*=_end_month]").prop('disabled', true);
				$(table).find("select[name*=_end_day]").prop('disabled', true);
				break;

			case '0': // any
			case '1': // none
			default:
				$(table).find("select[name*=_start_year]").prop('disabled', true);
				$(table).find("select[name*=_start_month]").prop('disabled', true);
				$(table).find("select[name*=_start_day]").prop('disabled', true);
				$(table).find("select[name*=_end_year]").prop('disabled', true);
				$(table).find("select[name*=_end_month]").prop('disabled', true);
				$(table).find("select[name*=_end_day]").prop('disabled', true);
				break;
		}
	});

	/* For Period.php bundled with the core MantisGraph plugin */
	$('#dates > input[type=image].datetime').hide();
	$('#period_menu > select#interval').change(function() {
		if ($(this).val() == 10) {
			$('#dates > input[type=text].datetime').prop('disabled', false);
			$('#dates > input[type=image].datetime').show();
		} else {
			$('#dates > input[type=text].datetime').prop('disabled', true);
			$('#dates > input[type=image].datetime').hide();
		}
	});

	$(document).on('change', '#tag_select', function() {
		var tagSeparator = $('#tag_separator').val();
		var currentTagString = $('#tag_string').val();
		var newTagOptionID = $(this).val();
		var newTag = $('#tag_select option[value=' + newTagOptionID + ']').text();
		if (currentTagString.indexOf(newTag) == -1) {
			if (currentTagString.length > 0) {
				$('#tag_string').val(currentTagString + tagSeparator + newTag);
			} else {
				$('#tag_string').val(newTag);
			}
		}
		$(this).val(0);
	});

	$('a.click-url').bind("click", function() {
		$(this).attr("href", $(this).attr("url"));
	});

	$('input[name=private].ace').bind("click", function() {
		if ($(this).is(":checked")){
			$('textarea[name=bugnote_text]').addClass("bugnote-private");
			$('tr[id=bugnote-attach-files]').hide();
		} else {
			$('textarea[name=bugnote_text]').removeClass("bugnote-private");
			$('tr[id=bugnote-attach-files]').show();
		}
	});
});

function setBugLabel() {
	var bug_label = $('.bug-jump-form').find('[name=bug_label]').val();
	var field = $('.bug-jump').find('[name=bug_id]');
	if( field.val() == '' ) {
		field.val(bug_label);
		field.addClass('field-default');
	}
}

/*
 * String manipulation
 */
function Trim( p_string ) {
	if (typeof p_string != "string") {
		return p_string;
	}

	var t_string = p_string;
	var t_ch = '';

	// Trim beginning spaces

	t_ch = t_string.substring( 0, 1 );
	while ( t_ch == " " ) {
		t_string = t_string.substring( 1, t_string.length );
		t_ch = t_string.substring( 0, 1 );
	}

	// Trim trailing spaces

	t_ch = t_string.substring( t_string.length-1, t_string.length );
	while ( t_ch == " " ) {
		t_string = t_string.substring( 0, t_string.length-1 );
		t_ch = t_string.substring( t_string.length-1, t_string.length );
	}

	return t_string;
}

/*
 * Cookie functions
 */
function GetCookie( p_cookie ) {
	var t_cookie_name = "MANTIS_" + p_cookie;
	var t_cookies = document.cookie;

	t_cookies = t_cookies.split( ";" );

	var i = 0;
	while( i < t_cookies.length ) {
		var t_cookie = t_cookies[ i ];

		t_cookie = t_cookie.split( "=" );

		if ( Trim( t_cookie[ 0 ] ) == t_cookie_name ) {
			return( t_cookie[ 1 ] );
		}
		i++;
	}

	return -1;
}

function SetCookie( p_cookie, p_value ) {
	var t_cookie_name = "MANTIS_" + p_cookie;
	var t_expires = new Date();

	t_expires.setTime( t_expires.getTime() + (365 * 24 * 60 * 60 * 1000));

	document.cookie = t_cookie_name + "=" + p_value + "; expires=" + t_expires.toUTCString() + ";";
}

function ToggleDiv( p_div ) {
	var t_open_div = '#' + p_div + "_open";
	var t_closed_div = '#' + p_div + "_closed";

	var t_cookie = GetCookie( "collapse_settings" );
	if ( 1 == g_collapse_clear ) {
		t_cookie = "";
		g_collapse_clear = 0;
	}
	var t_open_display = $(t_open_div).css('display');
	$(t_open_div).toggle();

	if( $(t_closed_div).length ) {
		$(t_closed_div).toggle();
	}

	if ( t_open_display == "none" ) {
        t_cookie = t_cookie.replace( "|" + p_div + ":0", '' );
		t_cookie = t_cookie + "|" + p_div + ":1";
	} else {
        t_cookie = t_cookie.replace( "|" + p_div + ":1", '' );
		t_cookie = t_cookie + "|" + p_div + ":0";
	}

	SetCookie( "collapse_settings", t_cookie );
}

function setDisplay(idTag, state)
{
	if(!document.getElementById(idTag)) alert('SetDisplay(): id '+idTag+' is empty');
	// change display visibility
	if ( state != 0 ) {
		document.getElementById(idTag).style.display = style_display;
	} else {
		document.getElementById(idTag).style.display = 'none';
	}
}

function toggleDisplay(idTag)
{
	setDisplay( idTag, (document.getElementById(idTag).style.display == 'none')?1:0 );
}

// Dropzone handler
Dropzone.autoDiscover = false;
function enableDropzone( classPrefix, autoUpload ) {
	try {
		var zone = new Dropzone( "." + classPrefix + "-form", {
			forceFallback: $.parseJSON(config['dropzone_force_fallback']),
			paramName: "ufile",
			autoProcessQueue: autoUpload,
			clickable: '.' + classPrefix,
			previewsContainer: '#' + classPrefix + '-previews-box',
			uploadMultiple: true,
			parallelUploads: 100,
			maxFilesize: config['max_file_size'],
			addRemoveLinks: !autoUpload,
			acceptedFiles: config['allowed_files'],
			dictDefaultMessage: translations['dropzone_default_message'],
			dictFallbackMessage: translations['dropzone_fallback_message'],
			dictFallbackText: translations['dropzone_fallback_text'],
			dictFileTooBig: translations['dropzone_file_too_big'],
			dictInvalidFileType: translations['dropzone_invalid_file_type'],
			dictResponseError: translations['dropzone_response_error'],
			dictCancelUpload: translations['dropzone_cancel_upload'],
			dictCancelUploadConfirmation: translations['dropzone_cancel_upload_confirmation'],
			dictRemoveFile: translations['dropzone_remove_file'],
			dictRemoveFileConfirmation: translations['dropzone_remove_file_confirmation'],
			dictMaxFilesExceeded: translations['dropzone_max_files_exceeded'],
			previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-details\">\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n    <div class=\"dz-size\" data-dz-size></div>\n    <img data-dz-thumbnail />\n  </div>\n  <div class=\"progress progress-small progress-striped active\"><div class=\"progress-bar progress-bar-success\" data-dz-uploadprogress></div></div>\n  <div class=\"dz-success-mark\"><span></span></div>\n  <div class=\"dz-error-mark\"><span></span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n</div>",

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
		alert( translations['dropzone_not_supported'] );
	}
}
