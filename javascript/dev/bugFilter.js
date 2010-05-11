var begin_form = '';
var form_fields = new Array();
var serialized_form_fields = new Array();
$j(document).ready(function(){
	var i = 0;
	$j('[name=filters_open]').find('input').each(function() {
		var formname = $j(this).parent('form').attr('name');
		if( formname != 'list_queries_open' && formname != 'open_queries' && formname != 'save_query' ) {
			// serialize the field and add it to an array

			if( $j.inArray($j(this).attr('name'),form_fields) == -1 ) {
				form_fields[i] = $j(this).attr('name');
				i++;
			}
		}
	});
	$j.each( form_fields, function (index, value) {
		serialized_form_fields[value] = $j('[name=filters_open]').find('[name='+value+']').serialize();
	});

	/* Change the action for managing stored queries */
	$j('[name=open_queries]').attr('action', 'plugin.php?page=StoredQuery/manage');
	$j('[name=save_query]').attr('action', 'plugin.php?page=StoredQuery/edit');

	$j('[name=save_query]').submit(function( event ) {
		/* Stop submitting this and submit the filter form to this action with the stored query id */
		event.preventDefault();
		$j('#filters_form_open').attr('action', $j(this).attr('action'));

		/* Add the source query id */
		var source_query_id = $j('[name=source_query_id] option:selected').val();
		$j('#filters_form_open').prepend( '<input type="hidden" name="source_query_id" value="' + source_query_id +' " />' );
		// submit the filter changes to the save query page
		$j('#filters_form_open').submit();
	});

	/* Set up events to modify the form css to show when a stored query has been modified */
	begin_form = $j('[name=filters_open]').serialize();

	$j('[:input').live("change", function() {
		filter_highlight_changes($j(this));
	});
	$j(':checkbox').live("click", function() {
		filter_highlight_changes($j(this));
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
	var changed_form = $j('[name=filters_open]').serialize();
	if( begin_form == changed_form ) {
		filter_clean_all();
	}
}

function filter_named_filter_clean() {
	/* be sure it's clean whether it's stored filter or not */
	var selected_text = $j('[name=source_query_id] option:selected').html();
	if( selected_text.charAt(0) == '*' ) {
		$j('[name=source_query_id]').removeClass('tainted');
		var reset_text = selected_text.substring(2,selected_text.length);
		$j('[name=source_query_id] option:selected').html(reset_text);
	}
}

function filter_named_filter_dirty() {
	var stored_query_id = $j('[name=source_query_id]').val();
	if( stored_query_id == -1 ) {
		/* Only make it dirty if it's a stored filter */
		return;
	}
	/* stored query in filter is tainted */
	var selected_text = $j('[name=source_query_id] option:selected').html();
	if( selected_text.charAt(0) != '*' ) {
		$j('[name=source_query_id] option:selected').prepend('* ');
		$j('[name=source_query_id]').addClass('tainted');
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
	$j('.tainted').each(function() {
		$j(this).removeClass('tainted');
	});
}
