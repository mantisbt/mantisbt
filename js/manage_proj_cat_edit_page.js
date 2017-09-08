// Controlls the reload of category names.
// used by manage_proj_cat_edit_page.php
$(document).ready(function() {
  $('#proj-category-language').on('change', function() {
    var language = $( '#proj-category-language option:selected' ).val();
    var proj_id = $( '#proj-category-project-id' ).val();
    var field_id = $( '#proj-category-category-id' ).val();
    
    $.ajax({
      url: "manage_proj_cat_edit_language_switch.php",
      type: 'GET',
      data: {
        lang: language,
        id: field_id
      },
      contentType: 'application/json; charset=utf-8',
      success: function(response) {
        $('#proj-category-name').val( response['name'] );
      },
      error: function(response) {
        console.error(response.responseText);
      }
    });
  });
  $('#proj-category-language').trigger('change');
});