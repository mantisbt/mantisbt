<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: date_api.php,v 1.7 2005-02-26 01:00:39 vboctor Exp $
	# --------------------------------------------------------

	### Date API ###

	# --------------------
	# prints the date given the formating string
	function print_date( $p_format, $p_date ) {
		PRINT date( $p_format, $p_date );
	}
	# --------------------
	function print_month_option_list( $p_month = 0 ) {
		for ($i=1; $i<=12; $i++) {
			$month_name = date( 'F', mktime(0,0,0,$i,1,2000) );
			if ( $i == $p_month ) {
				PRINT "<option value=\"$i\" selected=\"selected\">$month_name</option>";
			} else {
				PRINT "<option value=\"$i\">$month_name</option>";
			}
		}
	}
	# --------------------
	function print_numeric_month_option_list( $p_month = 0 ) {
		for ($i=1; $i<=12; $i++) {
			if ($i == $p_month) {
				PRINT "<option value=\"$i\" selected=\"selected\"> $i </option>" ;
			} else {
				PRINT "<option value=\"$i\"> $i </option>" ;
			}
		}
	}

	# --------------------
	function print_day_option_list( $p_day = 0 ) {
		for ($i=1; $i<=31; $i++) {
			if ( $i == $p_day ) {
				PRINT "<option value=\"$i\" selected=\"selected\"> $i </option>";
			} else {
				PRINT "<option value=\"$i\"> $i </option>";
			}
		}
	}
	# --------------------
	function print_year_option_list( $p_year = 0 ) {
		$current_year = date( "Y" );

		for ($i=$current_year; $i>1999; $i--) {
			if ( $i == $p_year ) {
				PRINT "<option value=\"$i\" selected=\"selected\"> $i </option>";
			} else {
				PRINT "<option value=\"$i\"> $i </option>";
			}
		}
	}
	# --------------------
	function print_year_range_option_list( $p_year = 0, $p_start = 0, $p_end = 0) {
		$t_current = date( "Y" ) ;
		$t_forward_years = config_get( 'forward_year_count' ) ;

		$t_start_year = $p_start ;
		if ($t_start_year == 0) {
			$t_start_year = $t_current ;
		}
		if ( ( $p_year < $t_start_year ) && ( $p_year != 0 ) ) {
			$t_start_year = $p_year ;
		}

		$t_end_year = $p_end ;
		if ($t_end_year == 0) {
			$t_end_year = $t_current + $t_forward_years ;
		}
		if ($p_year > $t_end_year) {
			$t_end_year = $p_year + $t_forward_years ;
		}

		for ($i=$t_start_year; $i <= $t_end_year; $i++) {
			if ($i == $p_year) {
				PRINT "<option value=\"$i\" selected=\"selected\"> $i </option>" ;
			} else {
				PRINT "<option value=\"$i\"> $i </option>" ;
			}
		}
	}
	# --------------------

	function print_date_selection_set( $p_name, $p_format, $p_date=0, $p_default_disable=false, $p_allow_blank=false, $p_year_start=0, $p_year_end=0) {
		$t_chars = preg_split('//', $p_format, -1, PREG_SPLIT_NO_EMPTY) ;
		if ( $p_date != 0 ) {
			$t_date = preg_split('/-/', date( 'Y-m-d', $p_date), -1, PREG_SPLIT_NO_EMPTY) ;
		} else {
			$t_date = array( 0, 0, 0 );
		}

		$t_disable = '' ;
		if ( $p_default_disable == true ) {
			$t_disable = 'disabled' ;
		}
		$t_blank_line = '' ;
		if ( $p_allow_blank == true ) {
			$t_blank_line = "<option value=\"0\"></option>" ;
		}

		foreach( $t_chars as $t_char ) {
			if (strcmp( $t_char, "M") == 0) {
				echo "<select name=\"" . $p_name . "_month\" $t_disable>" ;
				echo $t_blank_line ;
				print_month_option_list( $t_date[1] ) ;
				echo "</select>\n" ;
			}
			if (strcmp( $t_char, "m") == 0) {
				echo "<select name=\"" . $p_name . "_month\" $t_disable>" ;
				echo $t_blank_line ;
				print_numeric_month_option_list( $t_date[1] ) ;
				echo "</select>\n" ;
			}
			if (strcasecmp( $t_char, "D") == 0) {
				echo "<select name=\"" . $p_name . "_day\" $t_disable>" ;
				echo $t_blank_line ;
				print_day_option_list( $t_date[2] ) ;
				echo "</select>\n" ;
			}
			if (strcasecmp( $t_char, "Y") == 0) {
				echo "<select name=\"" . $p_name . "_year\" $t_disable>" ;
				echo $t_blank_line ;
				print_year_range_option_list( $t_date[0], $p_year_start, $p_year_end ) ;
				echo "</select>\n" ;
			}
		}
	}

?>
