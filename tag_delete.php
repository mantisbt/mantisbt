<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: tag_delete.php,v 1.1 2007-08-24 19:04:39 nuclear_eclipse Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'tag_api.php' );

	access_ensure_global_level( config_get( 'tag_edit_threshold' ) );

	$f_tag_id = gpc_get_int( 'tag_id' );
	$t_tag_row = tag_get( $f_tag_id );

	helper_ensure_confirmed( lang_get( 'tag_delete_message' ), lang_get( 'tag_delete_button' ) );

	tag_delete( $f_tag_id );
	
	print_successful_redirect( config_get( 'default_home_page' ) );
