<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: tag_detach.php,v 1.3 2007-10-09 02:57:44 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'tag_api.php' );

	$f_tag_id = gpc_get_int( 'tag_id' );
	$f_bug_id = gpc_get_int( 'bug_id' );

	tag_bug_detach( $f_tag_id, $f_bug_id );
	
	print_successful_redirect_to_bug( $f_bug_id );
