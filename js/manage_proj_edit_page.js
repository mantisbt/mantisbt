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

var userList;

function acldelete_setup( div ) {
	var jcheckboxs = $(div).find('input.user_access_delete');
	var show_or_hide_select =
		function(){
			var jdivacl = $(div).closest('tr').find('div.editable_access_level');
			if( this.checked ) {
				jdivacl.hide()
			} else {
				jdivacl.show()
			}
		};
	jcheckboxs.change(show_or_hide_select);
	jcheckboxs.each(show_or_hide_select);
}

function acledit_setup( div ) {
	var jdiv = $(div);
	// add listeners to edit links
	jdiv.find('span.unchanged a.edit_link').click( function(e){
		e.preventDefault();
		show_input( $(this).closest('div.editable_access_level') );
	});
	// add events to manage changes in selection
	jdiv.find('select.user_access_level')
		.on( 'blur change', function(e){
			try_hide_input( $(this).closest('div.editable_access_level') );
			var x1 = this.value;
			var x2 = $(this).closest('.key-access');
			var x3 = $(this).closest('.key-access').data('textvalue');
			var selection = $(this).find('option:selected').text();
			$(this).closest('.key-access').data('textvalue', selection);
			var x4 = $(this).closest('.key-access').data('textvalue');
		})
		;
	try_hide_input( div );
}

function show_input( div ) {
	var jdiv = $(div);
	jdiv.find('span.changed_to').show();
	jdiv.find('select.user_access_level').show().focus();
	jdiv.find('span.unchanged').hide();
}

function try_hide_input( div ) {
	var jdiv = $(div);
	var jselect = jdiv.find('select.user_access_level');
	if( jselect.is(":hidden") ) {
		return;
	}
	var current_val = jselect.val();
	var original_val = jselect.data('original_val');
	if( current_val == original_val ) {
		jselect.hide();
		jdiv.find('span.changed_to').hide();
		var textonly = jdiv.find('span.unchanged');
		textonly.removeClass('hidden');
		textonly.show();
	}
};


$(document).ready( function() {
	if( !$('#manage-project-users-list .listjs-table').length ) {
		return;
	}

	$('#manage-project-users-form-toolbox').removeClass('hidden');

	var per_page = $('#input-per-page').val();
	var userList_options = {  valueNames: [ { name: 'key-name', attr: 'data-sortvalue' }, 'key-email', { name: 'key-access', attr: 'data-sortvalue' } ]
		, page: per_page,
			pagination: {
			  innerWindow: 2,
			  left: 1,
			  right: 1,
			  paginationClass: "pagination",
			  }
	};
	userList = new List('manage-project-users-list', userList_options);
	userList.on( 'updated', function(){
		$('div.editable_access_level').each( function(){ acledit_setup(this); } );
		$('div.editable_user_delete').each( function(){ acldelete_setup(this); } );
	});

	$('#input-per-page').change( function(e) {
		if( $.isNumeric( this.value ) && this.value > 0 ) {
			userList.page = this.value;
			userList.update();
		}
	});

	$('div.editable_access_level').each( function(){ acledit_setup(this); } );
	$('div.editable_user_delete').each( function(){ acldelete_setup(this); } );

	$('#manage-project-users-form').submit( function(e){
		var items = userList.items;

		// Build an array of all inputs in list
		// including those hidden under pagination
		var acl_values = new Object();
		var delete_ids = new Array();
		$.each(items, function(){
			var jselect = $(this.elm).find('select.user_access_level');
			var current_val = jselect.val();
			var original_val = jselect.data('original_val');
			if( current_val != original_val ) {
				var user_id = jselect.data('user_id');
				acl_values[user_id] = current_val;
			}
			var jcheck = $(this.elm).find('input.user_access_delete:checkbox:checked');
			if( jcheck.length ) {
				delete_ids.push( jcheck.val() );
			}
		});

		var json_submit = new Object();
		json_submit['user_access_level'] = acl_values;
		json_submit['user_access_delete'] = delete_ids;
		console.log(JSON.stringify(json_submit));

		$('<input />').attr('type', 'hidden')
			.attr('name', 'json_submit')
          .attr('value', JSON.stringify(json_submit) )
          .appendTo('#manage-project-users-form');
	});

	var btn_remove_all = $('#manage-project-users-form input[name=btn-remove-all]');
	var btn_undo_remove_all = $('#manage-project-users-form button[name=btn-undo-remove-all]');
	// remove hidden class and use jquery functionality.
	btn_undo_remove_all.hide().removeClass('hidden');

	btn_remove_all.click(function(e){
		e.preventDefault();
		var items = userList.items;
		// Update al checkboxes in list
		// including those hidden under pagination
		$.each(items, function(){
			var jcheck = $(this.elm).find('input.user_access_delete:checkbox');
			if( jcheck.length ) {
				// save previous state
				jcheck.data('prev_state', jcheck.prop('checked') );
				jcheck.prop('checked', true).trigger('change');
			}
		});
		$(this).hide();
		btn_undo_remove_all.show();
	});

	btn_undo_remove_all.click(function(e){
		e.preventDefault();
		var items = userList.items;
		// Update al checkboxes in list
		// including those hidden under pagination
		$.each(items, function(){
			var jcheck = $(this.elm).find('input.user_access_delete:checkbox');
			if( jcheck.length ) {
				// restore previous state
				var prev = jcheck.data('prev_state');
				if( undefined !== prev ) {
					jcheck.prop('checked', prev).trigger('change');
				}
			}
		});
		$(this).hide();
		btn_remove_all.show();
	});

});
