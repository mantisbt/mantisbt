<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: view.php,v 1.3 2005-02-12 20:01:08 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'string_api.php' );
?>
<?php
	// Copy 'id' parameter into 'bug_id' so it is found by the simple/advanced view page.
	$_GET['bug_id'] = gpc_get_int( 'id' );

	include string_get_bug_view_page();
?>
