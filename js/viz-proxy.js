/*
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 */

'use strict';
const current_script = document.currentScript;
$(function() {
	async function render() {
		const src_script = $(current_script);
		const id = $('#' + src_script.data('id'));
		if(!id.size()) throw new Error('Missing placeholder tag for SVG');
		const source = src_script.data('source');
		if(!source) throw new Error('Missing source data for SVG');
		const instance = await Viz.instance(); // Viz.js library object
		const svg = await instance.renderSVGElement(source);
		id.append(svg);
	}
	render().catch(err => console.error(err));
});
