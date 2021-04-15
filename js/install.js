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
'use strict';

/**
 * PrefixInput object
 * @param input
 * @constructor
 */
function PrefixInput (inputId) {
	this.input = $('#' + inputId);
	this.button = $('#btn_' + inputId);

	/** Corresponding reset button */
	this.resetButton = function () { return this.button; };
	this.enableButton = function () { this.button.removeAttr('disabled');};
	this.disableButton = function () { this.button.attr('disabled', true);};

	/** Default value (data attribute) */
	this.getDefault = function () { return this.input.data('defval'); };
	this.setDefault = function (value) {
		this.input.data('defval', value);
		if (this.isValueDefault()) {
			this.disableButton();
		}
	};

	this.getValue = function () { return this.input.val(); };
	this.isValueDefault = function () { return this.getValue() === this.getDefault(); };

	/**
	 *
	 * @param value
	 */
	this.setValue = function (value) {
		this.input.val(value);
		if (this.isValueDefault()) {
			this.disableButton();
		}
	};

	/**
	 * Reset the input's value to default
	 * Set focus to the input, select the whole text and disable the reset button.
	 */
	this.resetValue = function () {
		this.input.val(this.getDefault());
		this.input.focus()[0].setSelectionRange(0, this.getValue().length);
		this.disableButton();
	};
}

var reset_buttons = $('button.reset-prefix');

/**
 * Initialize all input's default values and disable the reset buttons
 */
var inputs = $('input.table-prefix').each(function () {
	var input = new PrefixInput(this.id);
	input.setDefault(input.getValue());
	input.disableButton();
});

/**
 * On Change event for database type selection list
 * Preset prefix, plugin prefix and suffix fields when changing db type
 */
$('#db_type').change(
	function () {
		var db;
		if ($(this).val() === 'oci8') {
			db = 'oci8';
			$('#oracle_size_warning').show();
		} else {
			db = 'other';
			$('#oracle_size_warning').hide();
		}

		// Loop over the selected DB's default values for each pre/suffix
		$('#default_' + db + ' span').each(
			function () {
				var input = new PrefixInput(this.className);

				// Only change the value if not changed from default
				if (input.isValueDefault()) {
					input.setValue(this.textContent);
				}
				input.setDefault(this.textContent);
			}
		);

		update_sample_table_names();
	}
);

/**
 * Process changes to prefix/suffix inputs
 */
inputs.on('input', function () {
	var input = new PrefixInput(this.id);

	// Enable / disable the Reset button as appropriate
	if(input.isValueDefault()) {
		input.disableButton();
	} else {
		input.enableButton();
	}

	update_sample_table_names();
});

/**
 * Buttons to reset the prefix/suffix to the current default value
 */
reset_buttons.click(function () {
	var input = new PrefixInput($(this).prev('input.table-prefix')[0].id);
	input.resetValue();
	update_sample_table_names();
});

update_sample_table_names();

/**
 * Populate sample table names based on given prefix/suffix
 */
function update_sample_table_names() {
	var prefix = $('#db_table_prefix').val().trim();
	if(prefix && prefix.substr(-1) !== '_') {
		prefix += '_';
	}
	var suffix = $('#db_table_suffix').val().trim();
	if(suffix && suffix.substr(0,1) !== '_') {
		suffix = '_' + suffix;
	}
	var plugin = $('#db_table_plugin_prefix').val().trim();
	if(plugin && plugin.substr(-1) !== '_') {
		plugin += '_';
	}

	$('#db_table_prefix_sample').val(prefix + '<CORE TABLE>' + suffix);
	$('#db_table_plugin_prefix_sample').val(prefix + plugin + '<PLUGIN>_<TABLE>' + suffix);
}

});
