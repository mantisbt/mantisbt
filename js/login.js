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

/* jshint esversion: 6 */

$(function() {
	/* globals SecurimageAudio */
	let captcha_image_audioObj = new SecurimageAudio({
		audioElement: 'captcha_image_audio',
		controlsElement: 'captcha_image_audio_controls'
	});

	$('.captcha_play_button').on('click', function () {
		$('#captcha-field').trigger('focus');
	});

	// Captcha refresh
	$('#captcha-image, #captcha-refresh').on ('click', function(e) {
		captcha_image_audioObj.refresh();
		let img = $('#captcha-image img');
		let captcha = img.attr('src');
		img.attr('src', captcha.split('?', 1) + '?' + Math.random());
		$('#captcha-field').trigger('focus');
		e.preventDefault();
	});
});
