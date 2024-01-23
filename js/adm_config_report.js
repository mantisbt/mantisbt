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

$(document).ready( function() {
	"use strict";

	/**
	 * Display the complex element and load its contents if necessary.
	 *
	 * @param container The .adm_config_expand div to process
	 */
	function show_element(container) {
		container.find('.expand_hide').show();
		container.find('.expand_show').hide();
		// Load contents if not already done and display
		var content = container.find('.expand_content');
		if (content[0].childElementCount === 0) {
			var req_data = {
				'config_id': container.data('config_id'),
				'project_id': container.data('project_id'),
				'user_id': container.data('user_id')
			};
			content.load('api/rest/index.php/internal/config_display',req_data);
		}
		content.show();
	}

	$('.adm_config_expand .expand_hide').hide().removeClass('hidden');
	$('.adm_config_expand .expand_hide .toggle')
			.click(function(event) {
				event.preventDefault();
				var container = $(this).closest('.adm_config_expand');
				container.find('.expand_hide').hide();
				container.find('.expand_show').show();
				container.find('.expand_content').hide();
			});
	$('.adm_config_expand .expand_show .toggle')
			.click(function(event) {
				event.preventDefault();
				show_element($(this).closest('.adm_config_expand'));
			});
	$('.expand_all')
		.click(function() {
			// Load contents and display all hidden elements on the page
			$('.expand_show:visible').each(function(){
				show_element($(this).parent());
			});
		});
});