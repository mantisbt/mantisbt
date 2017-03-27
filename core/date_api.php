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
 * Date API
 *
 * @package CoreAPI
 * @subpackage DateAPI
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses user_pref_api.php
 * @uses utility_api.php
 */

require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'user_pref_api.php' );
require_api( 'utility_api.php' );

$g_cache_timezone = array();

/**
 * checks if date is null
 * @param integer $p_date Date.
 * @return boolean
 * @access public
 */
function date_is_null( $p_date ) {
	return $p_date == date_get_null();
}

/**
 * gets null date
 * @return integer
 * @access public
 */
function date_get_null() {
	return 1;
}

/**
 * gets Unix timestamp from date string
 * @param string $p_date A valid date/time string (see http://php.net/manual/en/datetime.formats.php)
 * @return false|int a timestamp on success, null date when $p_date is blank or false on failure.
 * @access public
 */
function date_strtotime( $p_date ) {
	if( is_blank( $p_date ) ) {
		return date_get_null();
	}
	return strtotime( $p_date );
}

/**
 * set new timezone
 * @param string $p_timezone PHP timezone to set.
 * @return void
 * @access public
 */
function date_set_timezone( $p_timezone ) {
	global $g_cache_timezone;

	array_push( $g_cache_timezone, date_default_timezone_get() );

	if( !date_default_timezone_set( $p_timezone ) ) {
		# unable to set timezone
		trigger_error( ERROR_UPDATING_TIMEZONE, WARNING );
	}
}

/**
 * restore previous timezone
 * @return void
 * @access public
 */
function date_restore_timezone() {
	global $g_cache_timezone;

	$t_timezone = array_pop( $g_cache_timezone );

	if( $t_timezone === null ) {
		return;
	}

	if( !date_default_timezone_set( $t_timezone ) ) {
		# unable to set timezone
		trigger_error( ERROR_UPDATING_TIMEZONE, WARNING );
	}
}

/**
 * Print html option tags for month in a select list - in user's language
 * @param integer $p_month Integer representing month of the year.
 * @return void
 * @access public
 */
function print_month_option_list( $p_month = 0 ) {
	for( $i = 1;$i <= 12;$i++ ) {
		$t_month_name = date( 'F', mktime( 0, 0, 0, $i, 1, 2000 ) );
		if( $i == $p_month ) {
			echo '<option value="' . $i . '" selected="selected">' . lang_get( 'month_' . strtolower( $t_month_name ) ) . '</option>';
		} else {
			echo '<option value="' . $i . '">' . lang_get( 'month_' . strtolower( $t_month_name ) ) . '</option>';
		}
	}
}

/**
 * Print numeric month html option tags for select list
 *
 * @param integer $p_month Integer representing month of the year.
 * @return void
 * @access public
 */
function print_numeric_month_option_list( $p_month = 0 ) {
	for( $i = 1;$i <= 12;$i++ ) {
		if( $i == $p_month ) {
			echo '<option value="' . $i . '" selected="selected">' . $i . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $i . '</option>';
		}
	}
}

/**
 * Print html option tags for Day of month in a select list
 * @param integer $p_day Integer representing day of the month.
 * @return void
 * @access public
 */
function print_day_option_list( $p_day = 0 ) {
	for( $i = 1;$i <= 31;$i++ ) {
		if( $i == $p_day ) {
			echo '<option value="' . $i . '" selected="selected">' . $i . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $i . '</option>';
		}
	}
}

/**
 * Print html option tags for year since 1999 in a select list
 * @todo deprecate this for year_range
 * @param integer $p_year Integer representing year.
 * @return void
 * @access public
 */
function print_year_option_list( $p_year = 0 ) {
	$t_current_year = date( 'Y' );

	for( $i = $t_current_year;$i > 1999;$i-- ) {
		if( $i == $p_year ) {
			echo '<option value="' . $i . '" selected="selected">' . $i . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $i . '</option>';
		}
	}
}

/**
 * Print html option tags for year in a select list
 * @param integer $p_year  Current Year.
 * @param integer $p_start First Year to display.
 * @param integer $p_end   Last Year to display.
 * @return void
 * @access public
 */
function print_year_range_option_list( $p_year = 0, $p_start = 0, $p_end = 0 ) {
	$t_current = date( 'Y' );
	$t_forward_years = config_get( 'forward_year_count' );

	$t_start_year = $p_start;
	if( $t_start_year == 0 ) {
		$t_backward_years = config_get( 'backward_year_count' );
		$t_start_year = $t_current - $t_backward_years;
	}

	if( ( $p_year < $t_start_year ) && ( $p_year != 0 ) ) {
		$t_start_year = $p_year;
	}

	$t_end_year = $p_end;
	if( $t_end_year == 0 ) {
		$t_end_year = $t_current + $t_forward_years;
	}
	if( $p_year > $t_end_year ) {
		$t_end_year = $p_year + $t_forward_years;
	}

	for( $i = $t_start_year;$i <= $t_end_year;$i++ ) {
		if( $i == $p_year ) {
			echo '<option value="' . $i . '" selected="selected">' . $i . '</option>';
		} else {
			echo '<option value="' . $i . '">' . $i . '</option>';
		}
	}
}

/**
 * Print <select> tag for selecting a date
 * @param string  $p_name            Name for html select field attribute.
 * @param string  $p_format          Date format e.g. YmD.
 * @param integer $p_date            Integer timestamp representing date.
 * @param boolean $p_default_disable Whether date selector is disabled.
 * @param boolean $p_allow_blank     Whether blank/null date is allowed.
 * @param integer $p_year_start      First year to display in drop down.
 * @param integer $p_year_end        Last year to display in drop down.
 * @param string  $p_input_css       CSS classes to use with input fields
 * @return void
 * @access public
 */
function print_date_selection_set( $p_name, $p_format, $p_date = 0, $p_default_disable = false, $p_allow_blank = false, $p_year_start = 0, $p_year_end = 0, $p_input_css = "input-sm" ) {
	if( $p_date != 0 ) {
		$t_date = date( $p_format, $p_date );
	} else {
		$t_date = '';
	}

	$t_disable = '';
	if( $p_default_disable == true ) {
		$t_disable = ' readonly="readonly"';
	}

 	echo '<input ' . helper_get_tab_index() . ' type="text" name="' . $p_name . '_date" ' .
		' class="datetimepicker ' . $p_input_css . '" ' . $t_disable .
		' data-picker-locale="' . lang_get_current_datetime_locale() . '"' .
		' data-picker-format="' . convert_date_format_to_momentjs( $p_format ) . '"' .
		' size="16" maxlength="20" value="' . $t_date . '" />';
	echo '<i class="fa fa-calendar fa-xlg datetimepicker"></i>';
}


/**
 * Converts php date format string to moment.js date format.
 * This function is used primarily with datetime picker widget.
 * @param string  $p_php_format  Php date format string: http://php.net/manual/en/function.date.php
 * @return string in moment.js format: http://momentjs.com/docs/#/displaying/format/
 * @access public
 */
function convert_date_format_to_momentjs( $p_php_format )
{
    $t_replacements = array(
        'd' => 'DD',
        'D' => 'ddd',
        'j' => 'D',
        'l' => 'dddd',
        'N' => 'E',
        'S' => 'o',
        'w' => 'e',
        'z' => 'DDD',
        'W' => 'W',
        'F' => 'MMMM',
        'm' => 'MM',
        'M' => 'MMM',
        'n' => 'M',
        't' => '', // no equivalent
        'L' => '', // no equivalent
        'o' => 'YYYY',
        'Y' => 'YYYY',
        'y' => 'YY',
        'a' => 'a',
        'A' => 'A',
        'B' => '', // no equivalent
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => 'SSS',
        'e' => 'zz', // deprecated since version 1.6.0 of moment.js
        'I' => '', // no equivalent
        'O' => '', // no equivalent
        'P' => '', // no equivalent
        'T' => '', // no equivalent
        'Z' => '', // no equivalent
        'c' => '', // no equivalent
        'r' => '', // no equivalent
        'U' => 'X',
	);

    return strtr( $p_php_format, $t_replacements );
}