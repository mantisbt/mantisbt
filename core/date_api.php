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
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 */

require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );

/**
 * Keeps track of whether the external files required for jscalendar to work
 * have already been included in the output sent to the client. jscalendar
 * will not work correctly if it is included multiple times on the same page.
 * @global bool $g_jscalendar_included_already
 */
$g_calendar_already_imported = false;

/**
 * checks if date is null
 * @param int $p_date
 * @return bool
 * @access public
 */
function date_is_null( $p_date ) {
	return $p_date == date_get_null();
}

/**
 * gets null date
 * @return int
 * @access public
 */
function date_get_null() {
	return 1;
}

$g_cache_timezone = array();

/**
 * set new timezone
 * @return null
 * @access public
 */
function date_set_timezone( $p_timezone ) {
	global $g_cache_timezone;

	array_push( $g_cache_timezone, date_default_timezone_get() );

	if( !date_default_timezone_set( $p_timezone ) ) {
		// unable to set timezone
		trigger_error( ERROR_UPDATING_TIMEZONE, WARNING );
	}
}

/**
 * restore previous timezone
 * @return null
 * @access public
 */
function date_restore_timezone( ) {
	global $g_cache_timezone;

	$t_timezone = array_pop( $g_cache_timezone );

	if( $t_timezone === null ) {
		return;
	}

	if( !date_default_timezone_set( $t_timezone ) ) {
		// unable to set timezone
		trigger_error( ERROR_UPDATING_TIMEZONE, WARNING );
	}
}

/**
 *
 * @param int $p_month
 * @return null
 * @access public
 */
function print_month_option_list( $p_month = 0 ) {
	for( $i = 1;$i <= 12;$i++ ) {
		$month_name = date( 'F', mktime( 0, 0, 0, $i, 1, 2000 ) );
		if( $i == $p_month ) {
			echo "<option value=\"$i\" selected=\"selected\">" . lang_get( 'month_' . utf8_strtolower($month_name)) . "</option>";
		} else {
			echo "<option value=\"$i\">" . lang_get( 'month_' . utf8_strtolower($month_name)) . "</option>";
		}
	}
}

/**
 *
 *
 * @param int $p_month
 * @return null
 * @access public
 */
function print_numeric_month_option_list( $p_month = 0 ) {
	for( $i = 1;$i <= 12;$i++ ) {
		if( $i == $p_month ) {
			echo "<option value=\"$i\" selected=\"selected\">$i</option>";
		} else {
			echo "<option value=\"$i\">$i</option>";
		}
	}
}

/**
 *
 * @param int $p_day
 * @return null
 * @access public
 */
function print_day_option_list( $p_day = 0 ) {
	for( $i = 1;$i <= 31;$i++ ) {
		if( $i == $p_day ) {
			echo "<option value=\"$i\" selected=\"selected\">$i</option>";
		} else {
			echo "<option value=\"$i\">$i</option>";
		}
	}
}

/**
 *
 * @param int $p_year
 * @return null
 * @access public
 */
function print_year_option_list( $p_year = 0 ) {
	$current_year = date( "Y" );

	for( $i = $current_year;$i > 1999;$i-- ) {
		if( $i == $p_year ) {
			echo "<option value=\"$i\" selected=\"selected\">$i</option>";
		} else {
			echo "<option value=\"$i\">$i</option>";
		}
	}
}

/**
 *
 * @param int $p_year
 * @param int $p_start
 * @param int $p_end
 * @return null
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

	if(( $p_year < $t_start_year ) && ( $p_year != 0 ) ) {
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
			echo "<option value=\"$i\" selected=\"selected\">$i</option>";
		} else {
			echo "<option value=\"$i\">$i</option>";
		}
	}
}

/**
 *
 * @param string $p_name
 * @param string $p_format
 * @param int $p_date
 * @param bool $p_default_disable
 * @param bool $p_allow_blank
 * @param int $p_year_start
 * @param int $p_year_end
 * @return null
 * @access public
 */
function print_date_selection_set( $p_name, $p_format, $p_date = 0, $p_default_disable = false, $p_allow_blank = false, $p_year_start = 0, $p_year_end = 0 ) {
	$t_chars = preg_split( '//', $p_format, -1, PREG_SPLIT_NO_EMPTY );
	if( $p_date != 0 ) {
		$t_date = preg_split( '/-/', date( 'Y-m-d', $p_date ), -1, PREG_SPLIT_NO_EMPTY );
	} else {
		$t_date = array(
			0,
			0,
			0,
		);
	}

	$t_disable = '';
	if( $p_default_disable == true ) {
		$t_disable = ' disabled="disabled"';
	}
	$t_blank_line = '';
	if( $p_allow_blank == true ) {
		$t_blank_line = "<option value=\"0\"></option>";
	}

	foreach( $t_chars as $t_char ) {
		if( strcmp( $t_char, "M" ) == 0 ) {
			echo "<select ", helper_get_tab_index(), " name=\"" . $p_name . "_month\"$t_disable>";
			echo $t_blank_line;
			print_month_option_list( $t_date[1] );
			echo "</select>\n";
		}
		if( strcmp( $t_char, "m" ) == 0 ) {
			echo "<select ", helper_get_tab_index(), " name=\"" . $p_name . "_month\"$t_disable>";
			echo $t_blank_line;
			print_numeric_month_option_list( $t_date[1] );
			echo "</select>\n";
		}
		if( strcasecmp( $t_char, "D" ) == 0 ) {
			echo "<select ", helper_get_tab_index(), " name=\"" . $p_name . "_day\"$t_disable>";
			echo $t_blank_line;
			print_day_option_list( $t_date[2] );
			echo "</select>\n";
		}
		if( strcasecmp( $t_char, "Y" ) == 0 ) {
			echo "<select ", helper_get_tab_index(), " name=\"" . $p_name . "_year\"$t_disable>";
			echo $t_blank_line;
			print_year_range_option_list( $t_date[0], $p_year_start, $p_year_end );
			echo "</select>\n";
		}
	}
}
