<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: timer_api.php,v 1.8 2005-02-12 20:01:18 jlatour Exp $
	# --------------------------------------------------------

	### Timer API ###

	# --- BC Timer -------
	# Charles Killian, modified by Kenzaburo Ito

	# USAGE: set $g_debug_timer to ON and just call mark_time( 'token name' );
	# where 'token name' is descriptive of what is happening at that point

	# Normally you would mark_time() before and after a critical section of
	# code that you are timing.  Remember to test more than once since various
	# factors can affect actual runtime.

	# --------------------
	# You should use this function instead of the class function
	function mark_time( $p_marker_name ) {
		global $g_timer;

		$g_timer->mark_time( $p_marker_name );
	}
	# --------------------
	class BC_Timer {
		var $atime; # this is an array of ( string token => time ) array elements.
		# ---
		function BC_Timer() {
			$this->atime   = array();
			$this->atime[] = array( "START", $this->get_microtime() );
		}
		# ---
		# get_microtime function taken from Everett Michaud on Zend.com
		function get_microtime(){
			$tmp = split( ' ', microtime() );
			$rt = $tmp[0] + $tmp[1];
			return $rt;
		}
		# ---
		# create the time entry
		function mark_time( $p_marker_name ) {
			$this->atime[] = array( $p_marker_name, $this->get_microtime() );
		}
		# ---
		# print out the timings.  If not in debug then just print out the total time.
		function print_times() {
			global $g_debug_timer;

			# store end time
			$this->atime[] = array( "END", $this->get_microtime() );

			# calculate end time
			$timer_count = count( $this->atime );
			$total_time = $this->atime[$timer_count-1][1] - $this->atime[0][1];

			# if debug then display debug timings
			if ( ON == $g_debug_timer ) {
				for ($i=0; $i+1 < $timer_count; $i++) {
					$time = $this->atime[$i+1][1] - $this->atime[$i][1];
					$time_precent = $time / $total_time * 100;
					PRINT '<span class="italic">Time: '.number_format( $time, 6 ).' seconds ( '.number_format( $time_precent, 2 ).'% ) for '.$this->atime[$i][0].' -to- '.$this->atime[$i+1][0].'</span><br />';
				}
			}

			# display total time
			PRINT '<span class="italic">Time: '.number_format( $total_time, 6 ).' seconds.</span><br />';
		}
	}
	# --------------------
?>
