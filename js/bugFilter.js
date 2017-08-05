var begin_form = '';
var form_fields = new Array();
var serialized_form_fields = new Array();
$(document).ready(function(){
	$('#filter-toggle').on('click', function (event) {
		$('#filter-bar-queries').toggle();
		$('#filter-bar-search').toggle();
	});

	$('#filter-bar-search-txt').on('change', function (event) {
		var t_term = $('#filter-bar-search-txt').val();
		$('#filter-search-txt').val(t_term);
	});

	$('#filter-search-txt').on('change', function (event) {
		var t_term = $('#filter-search-txt').val();
		$('#filter-bar-search-txt').val(t_term);
	});

	$('#filter-bar-query-id').on('change', function(e) {
		var id = $(this).val();
		$('select[name=source_query_id]').val(id);
		$('#filter-queries-form').submit();
	});

	var i = 0;
	$('[name=filters_open]').find('input').each(function() {
		var formname = $(this).parent('form').attr('name');
		if( formname != 'list_queries_open' && formname != 'open_queries' && formname != 'save_query' ) {
			// serialize the field and add it to an array

			if( $.inArray($(this).attr('name'),form_fields) == -1 ) {
				if($(this).attr('name')) {
					form_fields[i] = $(this).attr('name');
					i++;
				}
			}
		}
	});
	$.each( form_fields, function (index, value) {
		var escaped_field_name = value.replace(/\[\]/g, '\\[\\]');
		serialized_form_fields[value] = $('[name=filters_open]').find('[name=' + escaped_field_name + ']').serialize();
	});

	/* Set up events to modify the form css to show when a stored query has been modified */
	begin_form = $('[name=filters_open]').serialize();

	$(document).on('change', ':input', function() {
		filter_highlight_changes($(this));
	});
	$(document).on('click', ':checkbox', function() {
		filter_highlight_changes($(this));
	});
});

function filter_toggle_field_changed(field) {
	var field_type = field.attr('type');
	var starting_value = serialized_form_fields[field.attr('name')];
	var current_value = field.serialize();

	// unchecked boxes start as undefined but if checked and then unchecked it
	// is no longer undefined so the comparison breaks.  Reset it to undefined.
	if( field_type=='checkbox' && current_value == '') {
		current_value = undefined;
	}
	if( current_value != starting_value ) {
		// field is changed
		filter_field_dirty(field);
	} else {
		// field is not changed
		filter_field_clean(field);
	}
}

function filter_highlight_changes(item) {
	filter_toggle_field_changed( item );

	/* Check if form is different that started with */
	var changed_form = $('[name=filters_open]').serialize();
	if( begin_form == changed_form ) {
		filter_clean_all();
	}
}

function filter_named_filter_clean() {
	/* be sure it's clean whether it's stored filter or not */
	var selected_text = $('[name=source_query_id] option:selected').html();
	if(selected_text && selected_text.charAt(0) == '*' ) {
		$('[name=source_query_id]').removeClass('tainted');
		var reset_text = selected_text.substring(2,selected_text.length);
		$('[name=source_query_id] option:selected').html(reset_text);
	}
}

function filter_named_filter_dirty() {
	var stored_query_id = $('[name=source_query_id]').val();
	if( stored_query_id == -1 ) {
		/* Only make it dirty if it's a stored filter */
		return;
	}
	/* stored query in filter is tainted */
	var selected_text = $('[name=source_query_id] option:selected').html();
	if( selected_text.charAt(0) != '*' ) {
		$('[name=source_query_id] option:selected').prepend('* ');
		$('[name=source_query_id]').addClass('tainted');
	}
}

function filter_field_clean( item ) {
	item.parent().removeClass('tainted');
}
function filter_field_dirty( item ) {
	if( !item.parent().hasClass('tainted') ) {
		filter_named_filter_dirty();
		item.parent().addClass('tainted');
	}
}

function filter_clean_all() {
	filter_named_filter_clean();
	$('.tainted').each(function() {
		$(this).removeClass('tainted');
	});
}
