<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Timer API
	###########################################################################
	# --------------------
	# --- BC Timer -------
	# --------------------
	# Charles Killian, modified by Kenzaburo Ito

	# set to ON if you want to see debug level timings
	$g_debug_timer = ON;

	# USAGE: set #debug_timer to ON and just call mark_time( 'token name' );
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

		function BC_Timer() {
			$this->atime   = array();
			$this->atime[] = array( "START", $this->get_microtime() );
		}

		# get_microtime function taken from Everett Michaud on Zend.com
		function get_microtime(){
			$tmp = split( ' ', microtime() );
			$rt = $tmp[0] + $tmp[1];
			return $rt;
		}

		# create the time entry
		function mark_time( $p_marker_name ) {
			$this->atime[] = array( $p_marker_name, $this->get_microtime() );
		}

		# print out the timings.  If not in debug then just print out the total time.
		function print_times() {
			global $g_debug_timer;

			$t_total_time = 0;

			# store end time
			$this->atime[] = array( "END", $this->get_microtime() );

			# if debug then display debug timings
			if ( ON == $g_debug_timer ) {
				for ($i=0;$i+1<count($this->atime);$i++) {
					$time = $this->atime[$i+1][1]-$this->atime[$i][1];
					#echo number_format( $time, 5 )." = ".$this->atime[$i][0]." : ".$this->atime[$i+1][0]."<br />";
					PRINT '<span class="italic">Time: '.number_format( $time, 6 ).' seconds for '.$this->atime[$i][0].' -to- '.$this->atime[$i+1][0].'</span><br />';
				}
			}

			# display total time
			$end = count( $this->atime )-1;
			$time = $this->atime[$end][1]-$this->atime[0][1];
			PRINT '<span class="italic">Time: '.number_format( $time, 6 ).' seconds.</span><br />';
		}
	}
	# --------------------
?>