
function export_options_update_form() {
	var provider_id = $('#div_export_options select#input_provider').find(":selected").val();
	var project_id = $('#div_export_options select#input_project_id').val();
	var container = $('#div_export_plugin_options');
	if( export_plugin_options_form_cache.hasOwnProperty(provider_id) ) {
		var old_id = container.data('provider_id');
		export_plugin_options_form_cache[old_id] = container.clone();
		container.empty();
		container.append( export_plugin_options_form_cache[provider_id] );
		container.data('provider_id',provider_id);
	} else {
		var req_data = {
			'provider_id': provider_id,
			'project_id': project_id
		};
		$.get( 'api/rest/internal/export_plugin_options', req_data,
			function(new_html){
				var old_id = container.data('provider_id');
				export_plugin_options_form_cache[old_id] = container.clone();
				export_plugin_options_form_cache[provider_id] = new_html;
				container.html(new_html);
				container.data('provider_id',provider_id);
		});
	}
}

var export_plugin_options_form_cache = new Object();

$(document).ready( function() {
	$('#div_export_options select#input_provider').change( export_options_update_form );
	export_options_update_form();
});