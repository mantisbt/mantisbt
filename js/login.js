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
	// Captcha refresh
	$('#captcha-image, #captcha-refresh li a').click( function(e) {
		var img = $('#captcha-image img');
		var captcha = img.attr('src');
		img.attr('src', captcha.split('?', 1) + '?' + Math.random());
		$('#captcha-field').focus();
		e.preventDefault();
	});
});
