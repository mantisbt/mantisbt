$.datepicker.regional['en'] = {
	dateFormat: config['calendar_jq_date_format'],
	firstDay: 0,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};
$.datepicker.setDefaults($.datepicker.regional['en']);

$.timepicker.regional['en'] = {
	timeFormat: config['calendar_jq_time_format'],
	isRTL: false
};
$.timepicker.setDefaults($.timepicker.regional['en']);