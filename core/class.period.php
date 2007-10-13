<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

# --------------------------------------------------------
# $Id: class.period.php,v 1.1.2.1 2007-10-13 22:35:16 giallu Exp $
# --------------------------------------------------------
/**
* Class for actions dealing with date periods
*
* This class encapsulates all actions dealing with time intervals. It handles data 
* storage, and retrieval, as well as formatting and access.
*
* @copyright Logical Outcome Ltd. 2005 - 2007
* @author Glenn Henshaw <thraxisp@logicaloutcome.ca>
*
* @version $Id: class.period.php,v 1.1.2.1 2007-10-13 22:35:16 giallu Exp $
*/
class Period {
/**
* @var string start date
*/
	var		$start = '';
/**
* @var string end date
*/
    var		$end = '';
    
//******* End vars *********************************************

/**
* Constructor
*/
function Period() {	
	$this->start = ''; // default to today
	$this->end = ''; // default to today
}

/**
* return a matching SQL clause
*
* create an SQL clause that matches the set date.
* 
* @return string SQL clause
*/
function get_sql_clause () {
	if ( '' == $this->start ) {
		if ( '' == $this->end ) {
			$t_clause = '';
		} else {
			$t_clause = " =< '" . $this->end . "'";
		}
	} else {
		if ( '' == $this->end ) {
			$t_clause = " >= '" . $this->start . "'";
		} else {
			$t_clause = " BETWEEN '" . $this->start . "' AND '" . $this->end . "'";
		}
	}
	return $t_clause;
}

/**
* return a matching text clause
*
* create an text clause that matches the set date.
* 
* @return string SQL clause
*/
function get_text_clause () {
	if ( '' == $this->start ) {
		if ( '' == $this->end ) {
			$t_clause = lang_get( 'all_dates' );
		} else {
			list( $t_end_date, $t_end_time ) = split( ' ', $this->end );
			$t_clause = lang_get( 'before' ) . ' ' . $t_end_date;
		}
	} else {
		list( $t_start_date, $t_start_time ) = split( ' ', $this->start );
		if ( '' == $this->end ) {
			$t_clause = lang_get( 'after' ) . ' ' . $t_start_date;
		} else {
			list( $t_end_date, $t_end_time ) = split( ' ', $this->end );
			$t_clause = lang_get( 'from_date' ) . ' ' . $t_start_date . ' ' . lang_get( 'to_date' ) . ' ' . $t_end_date;
		}
	}
	return $t_clause;
}

/**
* set dates for a week
*
* @param string date string to expand to a week (Sun to Sat)
*/
function a_week ( $p_when, $p_weeks = 1 ) {
	list( $t_year, $t_month, $t_day ) = split( "-", $p_when );
    $t_now = getdate( mktime( 0,0,0,$t_month, $t_day, $t_year ) );
	$this->end = strftime( "%Y-%m-%d 23:59:59", 
	    mktime( 0,0,0, $t_month, $t_day - $t_now['wday'] + ( $p_weeks * 7 ) - 1, $t_year ) );
	$this->start = strftime( "%Y-%m-%d 00:00:00", mktime( 0,0,0,$t_month, $t_day - $t_now['wday'], $t_year ) );
}

/**
* set dates for this week
*
*/
function this_week ( ) {
	$this->a_week( date( 'Y-m-d' ) );
}

/**
* set dates for last week
*
*/
function last_week ( $p_weeks = 1) {
	$this->a_week( date( 'Y-m-d', strtotime( '-'.$p_weeks.' week' ) ), $p_weeks );
}

/**
* set dates for this week to date
*
*/
function week_to_date ( ) {
    $this->this_week( );
	$this->end = date( 'Y-m-d' ) . ' 23:59:59';
}

/**
* set dates for a month
*
* @param string date string to expand to a month
*/
function a_month ( $p_when ) {
	list( $t_year, $t_month, $t_day ) = split( "-", $p_when );
	$this->end = strftime( "%Y-%m-%d 23:59:59", mktime( 0,0,0, $t_month+1, 0, $t_year ) );
	$this->start = strftime( "%Y-%m-%d 00:00:00", mktime( 0,0,0,$t_month, 1, $t_year ) );
}

/**
* set dates for this month
*
*/
function this_month ( ) {
	$this->a_month( date( 'Y-m-d' ) );
}

/**
* set dates for last month
*
*/
function last_month ( ) {
	$this->a_month( date( 'Y-m-d', strtotime( '-1 month' ) ) );
}

/**
* set dates for this month to date
*
*/
function month_to_date ( ) {
	$this->end = date( 'Y-m-d' ) . ' 23:59:59';
	list( $t_year, $t_month, $t_day ) = split( "-", $this->end );
	$this->start = strftime( "%Y-%m-%d 00:00:00", mktime( 0,0,0,$t_month, 1, $t_year ) );
}

/**
* set dates for a quarter
*
* @param string date string to expand to a quarter
*/
function a_quarter ( $p_when ) {
	list( $t_year, $t_month, $t_day ) = split( "-", $p_when );
	$t_month = ( (int)( ( $t_month - 1 ) / 3 ) * 3 ) + 1; 
	$this->end = strftime( "%Y-%m-%d 23:59:59", mktime( 0,0,0, $t_month+3, 0, $t_year ) );
	$this->start = strftime( "%Y-%m-%d 00:00:00", mktime( 0,0,0,$t_month, 1, $t_year ) );
}

/**
* set dates for this quarter
*
*/
function this_quarter ( ) {
	$this->a_quarter( date( 'Y-m-d' ) );
}

/**
* set dates for last month
*
*/
function last_quarter ( ) {
	$this->a_quarter( date( 'Y-m-d', strtotime( '-3 months' ) ) );
}

/**
* set dates for this quarter to date
*
*/
function quarter_to_date ( ) {
	$this->end = date( 'Y-m-d' ) . ' 23:59:59';
	list( $t_year, $t_month, $t_day ) = split( "-", $this->end );
	$t_month = ( (int)( ( $t_month - 1 ) / 3 ) * 3 ) + 1; 
	$this->start = strftime( "%Y-%m-%d 00:00:00", mktime( 0,0,0,$t_month, 1, $t_year ) );
}

/**
* set dates for a year
*
* @param string date string to expand to a year
*/
function a_year ( $p_when ) {
	list( $t_year, $t_month, $t_day ) = split( "-", $p_when );
	$this->end = strftime( "%Y-%m-%d 23:59:59", mktime( 0,0,0, 12, 31, $t_year ) );
	$this->start = strftime( "%Y-%m-%d 00:00:00", mktime( 0,0,0, 1, 1, $t_year ) );
}

/**
* set dates for this year
*
*/
function this_year ( ) {
	$this->a_year( date( 'Y-m-d' ) );
}

/**
* set dates for current year, ending today
*
*/
function year_to_date ( ) {
	$this->end = date( 'Y-m-d' ) . ' 23:59:59';
	list( $t_year, $t_month, $t_day ) = split( "-", $this->end );
	$this->start = strftime( "%Y-%m-%d 00:00:00", mktime( 0,0,0, 1, 1, $t_year ) );
}

/**
* set dates for last year
*
*/
function last_year ( ) {
	$this->a_year( date( 'Y-m-d', strtotime( '-1 year' ) ) );
}

/**
* get start date in unix timestamp format
*
*/
function get_start_timestamp ( ) {
	return strtotime( $this->start );
}

/**
* get end date in unix timestamp format
*
*/
function get_end_timestamp ( ) {
	return strtotime( $this->end );
}

/**
* get formatted start date
*
*/
function get_start_formatted ( ) {
	return ($this->start == '' ? '' : strftime( '%Y-%m-%d', $this->get_start_timestamp()) );
}

/**
* get formatted end date
*
*/
function get_end_formatted ( ) {
	return ($this->end == '' ? '' : strftime( '%Y-%m-%d', $this->get_end_timestamp()) );
}

/**
* get number of days in interval
*
*/
function get_elapsed_days ( ) {
	return ( $this->get_end_timestamp() - $this->get_start_timestamp() ) / (24 * 60 * 60);
}

/**
* print a period selector
*
*/
function period_selector ( $p_control_name ) {
	$t_periods = array(
		0 => lang_get( 'period_none' ),
		7 => lang_get( 'period_this_week' ),
		8 => lang_get( 'period_last_week' ),
		9 => lang_get( 'period_two_weeks' ),
		1 => lang_get( 'period_this_month' ),
		2 => lang_get( 'period_last_month' ),
		3 => lang_get( 'period_this_quarter' ),
		4 => lang_get( 'period_last_quarter' ),
		5 => lang_get( 'period_year_to_date' ),
		6 => lang_get( 'period_last_year' ),
		10 => lang_get( 'period_select' )
	);
	$t_default = gpc_get_int( $p_control_name, 0 );
	$t_formatted_start = $this->get_start_formatted();
	$t_formatted_end = $this->get_end_formatted();
	$t_ret = '<div id="period_menu">';
	$t_ret .= get_dropdown( $t_periods, $p_control_name, $t_default, false, false, 'setDisplay(\'dates\', document.getElementById(\''.$p_control_name.'\').value == 10)');
    $t_ret .= '</div><div id="dates">'.
        lang_get('from_date').'&nbsp;'.
        '<input type="text" id="start_date" name="start_date" size="10" value="'.$t_formatted_start.'" />'.
	    '<img src="images/calendar-img.gif" id="f_trigger_s" style="cursor: pointer; border: 1px solid red;" '.
	        ' title="Date selector" onmouseover="this.style.background=\'red\';"'.
	        ' onmouseout="this.style.background=\'white\'" />'."\n".
	    '<br />'.
        lang_get('to_date').'&nbsp;&nbsp;&nbsp;&nbsp;'.
        '<input type="text" id="end_date" name="end_date" size="10" value="'.$t_formatted_end.'" />'.
	    '<img src="images/calendar-img.gif" id="f_trigger_e" style="cursor: pointer; border: 1px solid red;" '.
	        ' title="Date selector" onmouseover="this.style.background=\'red\';"'.
	        ' onmouseout="this.style.background=\'white\'" />'."\n".
	    '<script type="text/javascript"> 
			<!--
			Calendar.setup({ inputField : "start_date", ifFormat : "%Y-%m-%d", button : "f_trigger_s", 
			    align : "cR", singleClick : false,  showTime : false }); 
    		Calendar.setup({ inputField : "end_date", ifFormat : "%Y-%m-%d", button : "f_trigger_e", 
    			    align : "cR", singleClick : false,  showTime : false }); 
    		var t = document.getElementById(\''.$p_control_name.'\').value;
    		setDisplay(\''.$p_control_name.'\',true);
    		setDisplay(\'dates\', document.getElementById(\''.$p_control_name.'\').value == 10);
    			//-->
    		</script>'."\n".
    		'</div>';
    return $t_ret;

}

/**
* set date based on period selector
*
*/
function set_period_from_selector ( $p_control_name, $p_start_field='start_date', $p_end_field='end_date' ) {
	$t_default = gpc_get_int( $p_control_name, 0 );
	switch ( $t_default ) {
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
            if ($p_start_field != '') {
                $this->start = gpc_get_string($p_start_field, '') . ' 00:00:00';
                if ($this->start == '') {
                    $this->start = $t_today . ' 00:00:00';             
                }
            }
            if ($p_end_field != '') {
                $this->end = gpc_get_string($p_end_field, '') . ' 23:59:59';
                if ($this->end == '') {
                    $this->end = $t_today . ' 23:59:59';                  
                }
            }
            break;
		default:
	}			
}

} // end class

?>