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

	/**
	 * Manage the navbar project menu initializacion and events
	 * for focus and key presses.
	 */

	/* initialize the list */
	var projects_list_options = {
		valueNames: [ 'project-link' ]
	};
	var listprojects = new List('projects-list', projects_list_options);
	if( listprojects.items.length <= 10 ) {
		$('#projects-list .projects-searchbox').hide();
	}

	/**
	 * Events to manage focus when displaying the dropdown.
	 * - Focus on the active item to position the scrollable list on that item.
	 * - If there is no items to show, focus on the search box.
	 */
	$(document).on('shown.bs.dropdown', '#dropdown_projects_menu', function() {
		var li_active = $(this).find('.dropdown-menu li.active a');
		if( li_active.length ) {
			li_active.focus();
		} else {
			$('#projects-list .search').focus();
		}
	});

	/**
	 * When pressing a key in the search box, targeted at navigating the list,
	 * switch focus to the list.
	 * When pressing Escape, close the dropdown.
	 */
	$('#projects-list .search').keydown( function(event){
		switch (event.key) {
			case 'ArrowDown':
			case 'ArrowUp':
			case 'Down':
			case 'Up':
			case 'PageDown':
			case 'PageUp':
				var list = $('#projects-list .list');
				if( list.find('li.active').length ) {
					list.find('li.active a').focus();
				} else {
					list.find('li a').first().focus();
				}
				break;
			case 'Escape':
				$('#dropdown_projects_menu').removeClass('open');
		}
	});

	/**
	 * When pressing a key in the list, which is not targeted at navigating the list,
	 * for example, typing a string, toggle focus to the search box.
	 */
	$('#projects-list .list').keydown( function(event){
		switch (event.key) {
			case 'Enter':
			case 'ArrowDown':
			case 'ArrowUp':
			case 'Down':
			case 'Up':
			case 'PageDown':
			case 'PageUp':
			case 'Home':
			case 'End':
				return;
		}
		$('#projects-list .search').focus();
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

    $('#sidebar-btn.sidebar-toggle').on('click', function (event) {
		var t_cookie;
		var t_sidebar = $(this).closest('.sidebar');
		var t_id = t_sidebar.attr('id');

		if (1 == g_collapse_clear) {
			t_cookie = "";
			g_collapse_clear = 0;
		} else {
			// Get collapse state and remove the old value
			t_cookie = GetCookie("collapse_settings");
			t_cookie = t_cookie.replace(new RegExp('\\|' + t_id + ':.'), '');
		}

		// Save the new collapse state
		var t_value = !t_sidebar.hasClass("menu-min") | 0;
		t_cookie += '|' + t_id + ':' + t_value;

		SetCookie("collapse_settings", t_cookie);
    });

    $('input[type=text].typeahead').each(function() {
        var $this = $(this);
		$(this).typeahead({
			minLength: 1,
			highlight: true
		}, {
			source: function (query, undefined, callback) {
				var params = {};
				params['field'] = $this[0].id;
				params['prefix'] = query;
				$.getJSON('api/rest/index.php/internal/autocomplete', params, function (data) {
					var results = [];
					$.each(data, function (i, value) {
						results.push(value);
					});
	 				callback(results);
				});
			}
		});
	});

	$('a.dynamic-filter-expander').click(function(event) {
		event.preventDefault();
		var fieldID = $(this).attr('id');
		var filter_id = $(this).data('filter_id');
		var filter_tmp_id = $(this).data('filter');
		var targetID = fieldID + '_target';
		var viewType = $('#filters_form_open input[name=view_type]').val();
		$('#' + targetID).html('<span class="dynamic-filter-loading">' + translations['loading'] + "</span>");
		var params = 'view_type=' + viewType + '&filter_target=' + fieldID;
		if( undefined !== filter_id ) {
			params += '&filter_id=' + filter_id;
		}
		if( undefined !== filter_tmp_id ) {
			params += '&filter=' + filter_tmp_id;
		}
		$.ajax({
			url: 'return_dynamic_filters.php',
			data: params,
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

	/**
	 * Prepare a table where the checkboxes range selection has been applied
	 * Save row and column index in each cell for easier iteration.
	 * This assumes no rowspan or colspan is used in the area where the ckecboxes are rendered.
	 */
	$('table.checkbox-range-selection').each(function(){
		$(this).find('tr').each( function(row_index){
			$(this).children('th, td').each(function(col_index){
				$(this).data('col_index', col_index);
				$(this).data('row_index', row_index);
			});
		});
	});

	/**
	 * Enable range selection for checkboxes, inside a container having class "checkbox-range-selection"
	 * Assumes the bootstrap/ace styled checkboxes:
	 *		<label>
	 *			<input type="checkbox" class="ace">
	 *			<span class="lbl"></span>
	 *		</label>
	 */
	$('.checkbox-range-selection').on('click', 'label', function (e) {
		if( $(this).children('input:checkbox').length == 0 ) {
			return;
		}
		var jcontainer = $(this).closest('.checkbox-range-selection');
		var last_clicked = jcontainer.data('checkbox-range-last-clicked');
		if (!last_clicked) {
			last_clicked = this;
		}
		if (e.shiftKey) {
			// Because shift-click is triggered in a label/span, some browsers
			// will activate a text selection. Remove text selection.
			window.getSelection().removeAllRanges();
			var cb_label_list = jcontainer.find('label').has('input:checkbox');
			// The actual input hasn't been changed yet, so we want to set the
			// opposite value for all the checkboxes
			var clicked_current_st = $(this).find('input:checkbox').first().prop('checked');
			// The currently clicked one is also modified, becasue shift-click is not
			// recognised correctly by the framework. See #25215
			if( jcontainer.is('table') ) {
				// Special case for a table container:
				// we traverse the table cells for a rectangular area
				var cell_1 = $(this).closest('th, td');
				var row_1 = cell_1.data('row_index');
				var col_1 = cell_1.data('col_index');
				var cell_2 = $(last_clicked).closest('th, td');
				var row_2 = cell_2.data('row_index');
				var col_2 = cell_2.data('col_index');
				var row_start = Math.min(row_1, row_2);
				var row_end = Math.max(row_1, row_2);
				var col_start = Math.min(col_1, col_2);
				var col_end = Math.max(col_1, col_2);
				for (i = 0 ; i <= cb_label_list.length ; i++) {
					var it_td = $(cb_label_list[i]).closest('th, td');
					var it_row = it_td.data('row_index');
					var it_col = it_td.data('col_index');
					if(    row_start <= it_row && it_row <= row_end
						&& col_start <= it_col && it_col <= col_end ) {
						$(cb_label_list[i]).find('input:checkbox').prop('checked', !clicked_current_st);
					}
				}
			} else {
				// General case: we traverse the items by their relative index
				var start = cb_label_list.index(this);
				var end = cb_label_list.index(last_clicked);
				var index_start = Math.min(start, end);
				var index_end = Math.max(start, end);
				for (i = index_start ; i <= index_end ; i++) {
					$(cb_label_list[i]).find('input:checkbox').prop('checked', !clicked_current_st);
				}
			}
		}
		jcontainer.data('checkbox-range-last-clicked', this);
	});

	var stopwatch = {
		timerID: 0,
		startTime: null,
		zeroTime: moment('0', 's'),
		tick: function() {
			var elapsedDiff = moment().diff(this.startTime),
				elapsedTime = this.zeroTime.clone().add(elapsedDiff);

			$('input[type=text].stopwatch_time').val(elapsedTime.format('HH:mm:ss'));
		},
		reset: function() {
			this.stop();
			$('input[type=text].stopwatch_time').val('');
		},
		start: function() {
			var self = this,
				timeFormat = '',
				stoppedTime = $('input[type=text].stopwatch_time').val();

			this.stop();

			if (stoppedTime) {
				switch (stoppedTime.split(':').length) {
					case 1:
						timeFormat = 'ss';
						break;

					case 2:
						timeFormat = 'mm:ss';
						break;

					default:
						timeFormat = 'HH:mm:ss';
				}

				this.startTime = moment().add(this.zeroTime.clone().diff(moment(stoppedTime, timeFormat)));
			} else {
				this.startTime = moment();
			}

			this.timerID = window.setInterval(function() {
				self.tick();
			}, 1000);

			$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_stop']);
		},
		stop: function() {
			if (this.timerID) {
				window.clearInterval(this.timerID);
				this.timerID = 0;
			}

			$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_start']);
		}
	};

	$('input[type=button].stopwatch_toggle').click(function() {
		if (!stopwatch.timerID) {
			stopwatch.start();
		} else {
			stopwatch.stop();
		}
	});

	$('input[type=button].stopwatch_reset').click(function() {
		stopwatch.reset();
	});

	$('input[type=text].datetimepicker').each(function(index, element) {
		$(this).datetimepicker({
			locale: $(this).data('picker-locale'),
			format: $(this).data('picker-format'),
			useCurrent: false,
			icons: {
				time: 'fa fa-clock-o',
				date: 'fa fa-calendar',
				up: 'fa fa-chevron-up',
				down: 'fa fa-chevron-down',
				previous: 'fa fa-chevron-left',
				next: 'fa fa-chevron-right',
				today: 'fa fa-arrows ',
				clear: 'fa fa-trash',
				close: 'fa fa-times'
			}
		}).next().on(ace.click_event, function() {
			$(this).prev().focus();
		});
	});

	$( 'form .dropzone' ).each(function(){
		var classPrefix = 'dropzone';
		var autoUpload = $(this).hasClass('auto-dropzone');
		var zoneObj = enableDropzone( classPrefix, autoUpload );
		if( zoneObj ) {
			/* Attach image paste handler to report-bug & add-note forms */
			$( '#bugnoteadd, #report_bug_form' ).bind( 'paste', function( event ) {
				var items = ( event.clipboardData || event.originalEvent.clipboardData ).items;
				for( index in items ) {
					var item = items[index];
					if( item.kind === 'file' ) {
						zoneObj.addFile( item.getAsFile() )
					}
				}
			});
		}
	});

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
		$(this.form).submit();
	});

	/* Project selector: auto-switch on select */
	$('#form-set-project-id').change( function() {
		$('#form-set-project').submit();
	});
	$('#project-selector').children('.button').hide();

	setBugLabel();

	/* Handle standard filter date fields */
	$(document).on('change', '.js_switch_date_inputs_trigger', function() {
		$(this).closest('table')
				.find('select')
				.prop('disabled', !$(this).prop('checked'));
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
		var tagArray = currentTagString.split(tagSeparator);
		if (tagArray.indexOf(newTag) == -1) {
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

	/**
	 * Manage visiblity on hover trigger objects
	 */
	if( $('.visible-on-hover-toggle').length ) {
		$('.visible-on-hover-toggle').hover(
			function(e){ // handlerIn
				$(e.currentTarget).find('.visible-on-hover').removeClass('invisible');
			},
			function(e){ // handlerOut
				$(e.currentTarget).find('.visible-on-hover').addClass('invisible');
			}
		);
		$('.visible-on-hover').addClass('invisible');
	}

	/**
	 * Enhance tables with sortable columns using list.js
	 */
	$('.table-responsive.sortable').each(function(){
		var jtable = $(this).find('table').first();
		var ths = jtable.find('thead > tr > th');
		if( !ths.length ) {
			// exit if there is no headers
			return;
		}
		var th_count = ths.length

		var trs = jtable.find('tbody > tr');
		if( trs.length > 1000 ) {
			// don't run on big tables to avoid perfomance issues in client side
			return;
		}

		var options_valuenames = [];
		var exclude_index = [];
		ths.each(function(index){
			if( $(this).hasClass('no-sort') ) {
				// if the column says no sorting, save this index for later checks and skip
				exclude_index.push(index);
				return;
			}
			// wrap the contents into a crafted div
			var new_div = $('<div />').addClass('sort')
					.attr('data-sort','sortkey_'+index)
					.attr('role','button')
					.html($(this).html());
			$(this).html(new_div);

			options_valuenames.push( { name:'sortkey_'+index, attr:'data-sortval' } );
		});
		trs.each(function(){
			var tds = $(this).children('td');
			if( tds.length != th_count ) {
				// exit if different number of cells than headers, possibly colspan, etc
				return;
			}
			tds.each(function(index){
				if( exclude_index.indexOf(index) >= 0 ) {
					// if this column was marked as no-sorting, skip.
					return;
				}
				$(this).addClass( 'sortkey_'+index ).attr( 'data-sortval', $(this).text() );
			});
		});
		jtable.find('tbody').addClass('list');

		var listoptions = { valueNames: options_valuenames };
		var listobject =  new List( this, listoptions );
		$(this).data('listobject',listobject).data('listoptions',listoptions).addClass('listjs-table');
	});

	/**
	 * Change status color box's color when a different status is selected.
	 * To achieve that we need to store the current value in a data attribute,
	 * to compute the class name to remove in the change event.
	 */
	var statusColor = $('#status');
	// Store current value
	statusColor.data('prev', statusColor.val());
	statusColor.change(function () {
		function getColorClassName (statusCode) {
			return  'status-' + statusCode + '-fg';
		}

		var me = $(this);
		me.siblings('i')
			.removeClass(getColorClassName(me.data('prev')))
			.addClass(getColorClassName(me.val()));
		me.data('prev', me.val());
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
	var zone_class =  '.' + classPrefix;
	var zone = $( zone_class );
	var form = zone.closest('form');
	var max_filesize_bytes = zone.data('max-filesize-bytes');
	var max_filseize_mb = Math.ceil( max_filesize_bytes / ( 1024*1024) );
	var options = {
		forceFallback: zone.data('force-fallback'),
		paramName: "ufile",
		autoProcessQueue: autoUpload,
		clickable: zone_class,
		previewsContainer: '#' + classPrefix + '-previews-box',
		uploadMultiple: true,
		parallelUploads: 100,
		maxFilesize: max_filseize_mb,
		timeout: 0,
		addRemoveLinks: !autoUpload,
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
			/**
			 * 'addedfiles' is undocumented but works similar to 'addedfile'
			 * It's triggered once after a multiple file addition, and receives
			 * an array with the added files.
			 */
			this.on("addedfiles", function (files) {
				var error_found = false;
				var text_files = '';
				for (var i = 0; i < files.length; i++) {
					if( files[i].size > max_filesize_bytes ) {
						error_found = true;
						var size_mb = files[i].size / ( 1024*1024 );
						var dec = size_mb < 0.01 ? 3 : 2;
						text_files = text_files + '"' + files[i].name + '" (' + size_mb.toFixed(dec) + ' MiB)\n';
						this.removeFile( files[i] );
					}
				}
				if( error_found ) {
					var max_mb = max_filesize_bytes / ( 1024*1024 );
					var max_mb_dec = max_mb < 0.01 ? 3 : 2;
					var text = zone.data( 'dropzone_multiple_files_too_big' );
					text = text.replace( '{{files}}', '\n' + text_files + '\n' );
					text = text.replace( '{{maxFilesize}}', max_mb.toFixed(max_mb_dec) );
					alert( text );
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
