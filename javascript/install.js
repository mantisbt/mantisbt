/*
# Mantis - a php based bugtracking system

# Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright 2013 MantisBT Team   - mantisbt-dev@lists.sourceforge.net

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

/**
 * On Change event for database type selection list
 * Preset prefix, plugin prefix and suffix fields when changing db type
 */
$('#db_type').change(
	function () {
		var db;
		if ($(this).val() == 'oci8') {
			db = 'oci8';
		} else {
			db = 'other';
		}

		$('#default_' + db + ' span').each(
			function (i, el) {
				var target = $('#' + $(el).attr('name'));
				var oldVal = target.data('defval');
				// Only change the value if not changed from default
				if (typeof oldVal === 'undefined' || oldVal == target.val()) {
					target.val($(el).text());
				}
				// Store default value
				target.data('defval', $(el).text());
			}
		);
	}
).change();

});
