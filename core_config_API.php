<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Revision: 1.1 $
	# $Author: jfitzell $
	# $Date: 2002-08-24 09:16:54 $
	#
	# $Id: core_config_API.php,v 1.1 2002-08-24 09:16:54 jfitzell Exp $
	# --------------------------------------------------------

	###########################################################################
	# Configuration API
	###########################################################################

	# ------------------
	# Retrieves the value of a config option
	#  This function will return one of (in order of preference):
	#    1. The user-defined value (if set)
	#    2. The default value (if known)
	#    3. The value passed as the second parameter of the function
	function config_get( $p_option, $p_default=null ) {

		# ------ global variable implementation ------
		# this function implements getting configuration
		#  from our current global variable scheme. This
		#  interface should remain constant but we could
		#  call out to other functions or replace this code
		#  to use a DB or some other method

		if ( isset( $GLOBALS['g_'.$p_option] ) ) {
			return $GLOBALS['g_'.$p_option];
		} else {
			# @@@ trigerring either a NOTICE or a WARNING would be nice here
			#      if no $p_default was supplied (the common case)
			return $p_default;
		}
	}

?>