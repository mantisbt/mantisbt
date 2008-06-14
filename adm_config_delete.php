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
	# $Id: adm_config_delete.php,v 1.1.2.1 2007-10-13 22:32:24 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	# helper_ensure_post();

	$f_user_id = gpc_get_int( 'user_id' );
	$f_project_id = gpc_get_int( 'project_id' );
	$f_config_option = gpc_get_string( 'config_option' );

	if ( $f_project_id == ALL_PROJECTS ) {
		access_ensure_global_level( config_get( 'set_configuration_threshold' ) );
	} else {
		access_ensure_project_level( config_get( 'set_configuration_threshold' ), $f_project_id );
	}

	# make sure that configuration option specified can be stored in the database
	if ( !config_can_set_in_database( $f_config_option ) ) {
		error_parameters( $f_config_option );
		trigger_error( ERROR_CONFIG_OPT_CANT_BE_SET_IN_DB, ERROR );
	}

	helper_ensure_confirmed( lang_get( 'delete_config_sure_msg' ), lang_get( 'delete_link' ) );

	config_delete( $f_config_option, $f_user_id, $f_project_id );

	print_successful_redirect( 'adm_config_report.php' );
?>