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
	# $Id: logging_api.php,v 1.3.2.1 2007-10-13 22:35:34 giallu Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	
	$g_log_levels = array(
		LOG_EMAIL => 'mail',
		LOG_EMAIL_RECIPIENT => 'mail_recipient',
		LOG_FILTERING => 'filtering',
		LOG_AJAX => 'ajax'
	);

	###########################################################################
	# Logging api
	#  This is used to log system events other than bug related history
	###########################################################################
	
	function log_event( $p_level, $p_msg ) {
		global $g_log_levels;
	
		# check to see if logging is enabled
		$t_sys_log = config_get_global( 'log_level' );
		if ( 0 == ( $t_sys_log & $p_level ) ) {
			return;
		}
		
		$t_now = date( config_get( 'complete_date_format' ) );
		$t_level = $g_log_levels[$p_level];
		
		list( $t_destination, $t_modifiers) = split( ':', config_get_global( 'log_destination' ), 2 );
		switch ( $t_destination ) {
			case 'file':
				error_log( $t_now . ' ' . $t_level . ' ' . $p_msg . "\n", 3, $t_modifiers );
				break;
			default:
				break;
		}
	}
	
?>
