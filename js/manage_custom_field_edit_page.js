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

// Handle switching between textarea custom field type and other types
$(document).ready(function() {
  $('#custom-field-type').on('change', function() {
  if($(this).val() == 10) {  // 10: CUSTOM_FIELD_TYPE_TEXTAREA
    $('#custom-field-default-value').closest('.input').hide();
    $('#custom-field-default-value').attr('disabled', 'disabled');
    $('#custom-field-default-value-textarea').closest('.textarea').show();
    $('#custom-field-default-value-textarea').removeAttr('disabled');
  } else {
    $('#custom-field-default-value-textarea').closest('.textarea').hide();
    $('#custom-field-default-value-textarea').attr('disabled', 'disabled');
    $('#custom-field-default-value').closest('.input').show();
    $('#custom-field-default-value').removeAttr('disabled');
  }
  });
  $('#custom-field-type').trigger('change');
  
  
  
  $('#custom-field-language').on('change', function() {
    var language = $( '#custom-field-language option:selected' ).val();
    var field_id = $( '#custom-field-id' ).val();
    
    $.ajax({
      url: "manage_custom_field_edit_language_switch.php",
      type: 'GET',
      data: {
        lang: language,
        id: field_id
      },
      contentType: 'application/json; charset=utf-8',
      success: function(response) {
        $('#custom-field-name').val( response['name'] );
        $('#custom-field-possible-values').val( response['possible_values'] );
        $('#custom-field-default-value').val( response['default_value'] );
      },
      error: function(response) {
        console.error(response.responseText);
      }
    });
  });
  $('#custom-field-language').trigger('change');
});