<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	###########################################################################
	# Date API
	###########################################################################
	# --------------------
	# prints the date given the formating string
	function print_date( $p_format, $p_date ) {
		echo date( $p_format, $p_date );
	}
	# --------------------
?>