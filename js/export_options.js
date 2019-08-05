/*
# Mantis - a php based bugtracking system

# Copyright 2019 MantisBT Team   - mantisbt-dev@lists.sourceforge.net

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

function export_options_update_form() {
	var provider_id = $('#div_export_options select#input_provider').find(":selected").val();
	var project_id = $('#div_export_options select#input_project_id').val();
	var container = $('#div_export_plugin_options');
	// If there's a saved content, use it, otherwise request it to the server.
	if( export_plugin_options_form_cache.hasOwnProperty(provider_id) ) {
		var old_id = container.data('provider_id');
		// save current content. clone() preserves the inputs contents
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
				// save current content. clone() preserves the inputs contents
				export_plugin_options_form_cache[old_id] = container.clone();
				export_plugin_options_form_cache[provider_id] = new_html;
				container.html(new_html);
				container.data('provider_id',provider_id);
		});
	}
}

// Stores the html associated for each option, to request the ajax only once,
// and preserve input changes done by the user
var export_plugin_options_form_cache = new Object();

$(document).ready( function() {
	$('#div_export_options select#input_provider').change( export_options_update_form );
	// Run the first time, after form creation:
	export_options_update_form();
});