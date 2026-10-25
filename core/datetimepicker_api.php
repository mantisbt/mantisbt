<?php
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

/**
 * DateTimePicker API
 *
 * @package CoreAPI
 * @subpackage DateTimePickerAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses icon_api.php
 * @uses lang_api.php
 * @uses string_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'icon_api.php' );
require_api( 'lang_api.php' );
require_api( 'string_api.php' );

# Include Moment.js and Bootstrap DateTimePicker.js
if( config_get_global( 'cdn_enabled' ) == ON ) {
	require_css( [ 'https://cdnjs.cloudflare.com/ajax/libs/eonasdan-bootstrap-datetimepicker/' . DATETIME_PICKER_VERSION . '/css/bootstrap-datetimepicker.min.css', DATETIME_PICKER_HASH_CSS ] );
	require_js( [ 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/' . MOMENT_VERSION . '/moment-with-locales.min.js', MOMENT_HASH ] );
	require_js( [ 'https://cdnjs.cloudflare.com/ajax/libs/eonasdan-bootstrap-datetimepicker/' . DATETIME_PICKER_VERSION . '/js/bootstrap-datetimepicker.min.js', DATETIME_PICKER_HASH_JS ] );
} else {
	require_css( 'bootstrap-datetimepicker-' . DATETIME_PICKER_VERSION . '.min.css' );
	require_js( 'moment-with-locales-' . MOMENT_VERSION . '.min.js' );
	require_js( 'bootstrap-datetimepicker-' . DATETIME_PICKER_VERSION . '.min.js' );
}
require_js( 'datetimepicker-proxy.js' );

/**
 * Get a form element with DateTimePicker.
 * @param string $p_date   Date to display.
 * @param string $p_id     Id of element.
 * @param string $p_format Time format, by default, the value from the $g_datetime_picker_format is used.
 * @return string Element to output.
 */
function datetimepicker_get_field(string $p_date, string $p_id, string $p_format = '') {
	return '<input type="text" id="' . $p_id . '" name="' . $p_id . '"'
		. ' class="datetimepicker input-sm" size="20" maxlength="20"'
		. ' data-picker-locale="' . string_html_specialchars( lang_get_current_datetime_locale() ) . '"'
		. ' data-picker-format="' . string_html_specialchars( $p_format ?: config_get( 'datetime_picker_format' ) ) . '"'
		. ' ' . helper_get_tab_index()
		. ' value="' . string_html_specialchars( $p_date ) . '">'
		. icon_get( 'fa-calendar', 'fa-xlg datetimepicker' );
}

/**
 * Populate form element with DateTimePicker.
 * @param string $p_date   Date to display.
 * @param string $p_id     Id of element.
 * @param string $p_format Time format, by default, the value from the $g_datetime_picker_format is used.
 * @return void
 */
function datetimepicker_print(string $p_date, string $p_id, string $p_format = '') {
	echo datetimepicker_get_field( $p_date, $p_id, $p_format );
}
