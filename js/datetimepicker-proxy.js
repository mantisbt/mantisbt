/*
# Mantis - a php based bugtracking system

# Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright 2026 MantisBT Team   - mantisbt-dev@lists.sourceforge.net

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

/* jshint esversion: 8 */
/* globals ace, moment, translations, $ */
$(function() {
	'use strict';

	var stopwatch = {
		timerID: 0,
		startTime: null,
		zeroTime: moment('0', 's'),
		tick: function() {
			var elapsedDiff = moment().diff(this.startTime),
				elapsedTime = this.zeroTime.clone().add(elapsedDiff);

			$('input[type=text].stopwatch_time').val(elapsedTime.format('HH:mm:ss'));
		},
		reset: function() {
			this.stop();
			$('input[type=text].stopwatch_time').val('');
		},
		start: function() {
			var self = this,
				timeFormat = '',
				stoppedTime = $('input[type=text].stopwatch_time').val();

			this.stop();

			if (stoppedTime) {
				switch (stoppedTime.split(':').length) {
					case 1:
						timeFormat = 'ss';
						break;

					case 2:
						timeFormat = 'mm:ss';
						break;

					default:
						timeFormat = 'HH:mm:ss';
				}

				this.startTime = moment().add(this.zeroTime.clone().diff(moment(stoppedTime, timeFormat)));
			} else {
				this.startTime = moment();
			}

			this.timerID = window.setInterval( function() {
				self.tick();
			}, 1000);

			$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_stop']);
		},
		stop: function() {
			if (this.timerID) {
				window.clearInterval(this.timerID);
				this.timerID = 0;
			}

			$('input[type=button].stopwatch_toggle').val(translations['time_tracking_stopwatch_start']);
		}
	};

	$('input[type=button].stopwatch_toggle').click( function() {
		if (!stopwatch.timerID) {
			stopwatch.start();
		} else {
			stopwatch.stop();
		}
	});

	$('input[type=button].stopwatch_reset').click( function() {
		stopwatch.reset();
	});

	$('input[type=text].datetimepicker').each( function(index, element) {
		$(this).datetimepicker({
			locale: $(this).data('picker-locale'),
			format: $(this).data('picker-format'),
			useCurrent: false,
			showTodayButton: true,
			icons: {
				time: 'fa fa-clock-o',
				date: 'fa fa-calendar',
				up: 'fa fa-chevron-up',
				down: 'fa fa-chevron-down',
				previous: 'fa fa-chevron-left',
				next: 'fa fa-chevron-right',
				today: 'fa fa-calendar-times-o',
				clear: 'fa fa-trash',
				close: 'fa fa-times'
			}
		}).next().on( ace.click_event, function() {
			$(this).prev().focus();
		});
	});
});
