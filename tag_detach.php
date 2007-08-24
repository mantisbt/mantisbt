<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: tag_detach.php,v 1.1 2007-08-24 19:04:39 nuclear_eclipse Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'tag_api.php' );

	$f_tag_id = gpc_get_int( 'tag_id' );
	$f_bug_id = gpc_get_int( 'bug_id' );

	$t_tag_row = tag_get( $f_tag_id );
	$t_tag_bug_row = tag_bug_get_row( $f_tag_id, $f_bug_id );

	if ( ! ( access_has_global_level( config_get( 'tag_detach_threshold' ) ) 
		|| ( auth_get_current_user_id() == $t_tag_bug_row['user_id'] )
			&& access_has_global_level( config_get( 'tag_detach_own_threshold' ) ) ) ) 
	{
		access_denied();
	}

	tag_bug_detach( $f_tag_id, $f_bug_id );
	
	print_successful_redirect_to_bug( $f_bug_id );
