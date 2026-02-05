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
		const id_data = src_script.data('id');
		if(!id_data) throw new Error('Missing "id" data for SVG');
		const id = $('#' + id_data);
		if(!id.length) throw new Error('Missing placeholder tag for SVG');
		const source_data = src_script.data('source');
		if(!source_data) throw new Error('Missing "source" data for SVG');

		const instance = await Viz.instance(); // Viz.js library object
		const svg = await instance.renderSVGElement(source_data);
		id.append(svg);

		id.append($('<a class="btn-hover"><i class="fa fa-download"></i></a>').on('click', function() {
			const serializer = new XMLSerializer();
			let svg_str = serializer.serializeToString(svg);
			if(!svg_str.startsWith('<?xml')) {
				svg_str = '<?xml version="1.0" encoding="utf-8"?>\n' + svg_str;
			}
			const url = URL.createObjectURL(new Blob([svg_str], { type: 'image/svg+xml;charset=utf-8' }));
			const a = document.createElement('a');
			a.href = url;
			a.download = 'image.svg';
			a.target = '_blank';
			document.body.appendChild(a);
			a.click();
			setTimeout(() => {
				document.body.removeChild(a);
				URL.revokeObjectURL(url);
			}, 1000);
		}));
	}
	render().catch(err => console.error(err));
});
