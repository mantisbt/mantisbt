// Controlls the reload of project names and descriptions.
// used by manage_proj_edit_page.php
$(document).ready(function() {
  $('#project-language').on('change', function() {
    var language = $( '#project-language option:selected' ).val();
    var field_id = $( '#project-id' ).val();
    
    $.ajax({
      url: "manage_proj_edit_language_switch.php",
      type: 'GET',
      data: {
        lang: language,
        id: field_id
      },
      contentType: 'application/json; charset=utf-8',
      success: function(response) {
        $('#project-name').val( response['name'] );
        $('#project-description').val( response['description'] );
      },
      error: function(response) {
        console.error(response.responseText);
      }
    });
  });
  $('#project-language').trigger('change');
});