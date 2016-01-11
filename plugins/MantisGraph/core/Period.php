<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Class for actions dealing with date periods
 *
 * This class encapsulates all actions dealing with time intervals. It handles data
 * storage, and retrieval, as well as formatting and access.
 *
 * @copyright Logical Outcome Ltd. 2005 - 2007
 * @author Glenn Henshaw <thraxisp@logicaloutcome.ca>
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage classes
 */
class Period {
	/**
	 * start date
	 * @var string
	 */
	private $start = '';

	/**
	 * end date
	 * @var string
	 */
	private $end = '';

	/**
	 * Constructor
	 */
	function __construct() {
		$this->start = '';

		# default to today
		$this->end = '';

		# default to today
	}

	/**
	 * set dates for a week
	 *
	 * @param string  $p_when  Date string to expand to a week (Sun to Sat).
	 * @param integer $p_weeks Number of weeks.
	 * @return void
	 */
	function a_week( $p_when, $p_weeks = 1 ) {
		list( $t_year, $t_month, $t_day ) = explode( '-', $p_when );
		$t_now = getdate( mktime( 0, 0, 0, $t_month, $t_day, $t_year ) );
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, $t_month, $t_day - $t_now['wday'] + ( $p_weeks * 7 ) - 1, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, $t_day - $t_now['wday'], $t_year ) );
	}

	/**
	 * set dates for this week
	 * @return void
	 */
	function this_week() {
		$this->a_week( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for last week
	 *
	 * @param integer $p_weeks Number of weeks.
	 * @return void
	 */
	function last_week( $p_weeks = 1 ) {
		$this->a_week( date( 'Y-m-d', strtotime( '-' . $p_weeks . ' week' ) ), $p_weeks );
	}

	/**
	 * set dates for this week to date
	 *
	 * @return void
	 */
	function week_to_date() {
		$this->this_week();
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
	}

	/**
	 * set dates for a month
	 *
	 * @param string $p_when Date string to expand to a month.
	 * @return void
	 */
	function a_month( $p_when ) {
		list( $t_year, $t_month, $t_day ) = explode( '-', $p_when );
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, $t_month + 1, 0, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
 	 * set dates for this month
 	 *
	 * @return void
	 */
	function this_month() {
		$this->a_month( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for last month
	 *
	 * @return void
	 */
	function last_month() {
		$this->a_month( date( 'Y-m-d', strtotime( '-1 month' ) ) );
	}

	/**
	 * set dates for this month to date
	 *
	 * @return void
	 */
	function month_to_date() {
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
		list( $t_year, $t_month, $t_day ) = explode( '-', $this->end );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
	 * set dates for a quarter
	 *
	 * @param string $p_when Date string to expand to a quarter.
	 * @return void
	 */
	function a_quarter( $p_when ) {
		list( $t_year, $t_month, $t_day ) = explode( '-', $p_when );
		$t_month = ( (int)(( $t_month - 1 ) / 3 ) * 3 ) + 1;
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, $t_month + 3, 0, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
	 * set dates for this quarter
	 *
	 * @return void
	 */
	function this_quarter() {
		$this->a_quarter( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for last month
	 *
	 * @return void
	 */
	function last_quarter() {
		$this->a_quarter( date( 'Y-m-d', strtotime( '-3 months' ) ) );
	}

	/**
	 * set dates for this quarter to date
	 *
	 * @return void
	 */
	function quarter_to_date() {
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
		list( $t_year, $t_month, $t_day ) = explode( '-', $this->end );
		$t_month = ( (int)(( $t_month - 1 ) / 3 ) * 3 ) + 1;
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, $t_month, 1, $t_year ) );
	}

	/**
	 * set dates for a year
	 *
	 * @param string $p_when Date string to expand to a year.
	 * @return void
	 */
	function a_year( $p_when ) {
		list( $t_year, $t_month, $t_day ) = explode( '-', $p_when );
		$this->end = strftime( '%Y-%m-%d 23:59:59', mktime( 0, 0, 0, 12, 31, $t_year ) );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, 1, 1, $t_year ) );
	}

	/**
	 * set dates for this year
	 *
	 * @return void
	 */
	function this_year() {
		$this->a_year( date( 'Y-m-d' ) );
	}

	/**
	 * set dates for current year, ending today
	 *
	 * @return void
	 */
	function year_to_date() {
		$this->end = date( 'Y-m-d' ) . ' 23:59:59';
		list( $t_year, $t_month, $t_day ) = explode( '-', $this->end );
		$this->start = strftime( '%Y-%m-%d 00:00:00', mktime( 0, 0, 0, 1, 1, $t_year ) );
	}

	/**
	 * set dates for last year
	 *
	 * @return void
	 */
	function last_year() {
		$this->a_year( date( 'Y-m-d', strtotime( '-1 year' ) ) );
	}

	/**
	 * get start date in unix timestamp format
	 *
	 * @return integer
	 */
	function get_start_timestamp() {
		return strtotime( $this->start );
	}

	/**
	 * get end date in unix timestamp format
	 *
	 * @return integer
	 */
	function get_end_timestamp() {
		return strtotime( $this->end );
	}

	/**
	 * get formatted start date
	 *
	 * @return string
	 */
	function get_start_formatted() {
		return( $this->start == '' ? '' : strftime( '%Y-%m-%d', $this->get_start_timestamp() ) );
	}

	/**
	 * get formatted end date
	 *
	 * @return string
	 */
	function get_end_formatted() {
		return( $this->end == '' ? '' : strftime( '%Y-%m-%d', $this->get_end_timestamp() ) );
	}

	/**
	 * get number of days in interval
     * @return integer
	 */
	function get_elapsed_days() {
		return( $this->get_end_timestamp() - $this->get_start_timestamp() ) / ( 24 * 60 * 60 );
	}

	/**
	 * print a period selector
	 *
	 * @param string $p_control_name Value representing the name of the html control on the web page.
     * @return string
	 */
	function period_selector( $p_control_name ) {
		$t_periods = array(
			0 => plugin_lang_get( 'period_none' ),
			7 => plugin_lang_get( 'period_this_week' ),
			8 => plugin_lang_get( 'period_last_week' ),
			9 => plugin_lang_get( 'period_two_weeks' ),
			1 => plugin_lang_get( 'period_this_month' ),
			2 => plugin_lang_get( 'period_last_month' ),
			3 => plugin_lang_get( 'period_this_quarter' ),
			4 => plugin_lang_get( 'period_last_quarter' ),
			5 => plugin_lang_get( 'period_year_to_date' ),
			6 => plugin_lang_get( 'period_last_year' ),
			10 => plugin_lang_get( 'period_select' ),
		);
		$t_default = gpc_get_int( $p_control_name, 0 );
		$t_formatted_start = $this->get_start_formatted();
		$t_formatted_end = $this->get_end_formatted();
		$t_ret = '<div id="period_menu">';
		$t_ret .= get_dropdown( $t_periods, $p_control_name, $t_default, false, false );
		$t_ret .= "</div>\n";
		$t_ret .= "<div id=\"dates\">\n";
		$t_ret .= '<label for="start_date">' . lang_get( 'from_date' ) . '</label><input type="text" id="start_date" name="start_date" size="20" value="' . $t_formatted_start . '" class="datetime" disabled="disabled" />' . "<br />\n";
		$t_ret .= '<label for="end_date">' . lang_get( 'to_date' ) . '</label><input type="text" id="end_date" name="end_date" size="20" value="' . $t_formatted_end . '" class="datetime" disabled="disabled" />' . "\n";
		$t_ret .= "</div>\n";
		return $t_ret;
	}

	/**
	 * set date based on period selector
	 *
	 * @param string $p_control_name Value representing the name of the html control on the web page.
	 * @param string $p_start_field  Name representing the name of the starting field on the date selector i.e. start_date.
	 * @param string $p_end_field    Name representing the name of the ending field on the date selector i.e. end_date.
	 * @return void
	 */
	function set_period_from_selector( $p_control_name, $p_start_field = 'start_date', $p_end_field = 'end_date' ) {
		$t_default = gpc_get_int( $p_control_name, 0 );
		switch( $t_default ) {
			case 1:
				$this->month_to_date();
				break;
			case 2:
				$this->last_month();
				break;
			case 3:
				$this->quarter_to_date();
				break;
			case 4:
				$this->last_quarter();
				break;
			case 5:
				$this->year_to_date();
				break;
			case 6:
				$this->last_year();
				break;
			case 7:
				$this->week_to_date();
				break;
			case 8:
				$this->last_week();
				break;
			case 9:
				$this->last_week( 2 );
				break;
			case 10:
				$t_today = date( 'Y-m-d' );
				if( $p_start_field != '' ) {
					$this->start = gpc_get_string( $p_start_field, '' ) . ' 00:00:00';
					if( $this->start == '' ) {
						$this->start = $t_today . ' 00:00:00';
					}
				}
				if( $p_end_field != '' ) {
					$this->end = gpc_get_string( $p_end_field, '' ) . ' 23:59:59';
					if( $this->end == '' ) {
						$this->end = $t_today . ' 23:59:59';
					}
				}
				break;
			default:
		}
	}
}
