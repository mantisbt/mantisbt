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
	# charles_killian
	class BC_Timer {
		var $stime;
		var $etime;

		# get_microtime function taken from Everett Michaud on Zend.com
		function get_microtime(){
			$tmp = split( ' ', microtime() );
			$rt = $tmp[0] + $tmp[1];
			return $rt;
		}

		function start_time(){
			$this->stime = $this->get_microtime();
		}

		function end_time(){
			$this->etime = $this->get_microtime();
		}

		function elapsed_time(){
			return ($this->etime - $this->stime);
		}
	}
	# --------------------
?>