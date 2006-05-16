<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: xmlhttprequest_api.php,v 1.1 2006-05-16 23:59:28 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'bug_api.php' );

	### XmlHttpRequest API ###

	function xmlhttprequest_issue_reporter_combobox() {
		$f_bug_id = gpc_get_int( 'issue_id' );

		access_ensure_bug_level( config_get( 'update_bug_threshold' ), $f_bug_id );

		$t_reporter_id = bug_get_field( $f_bug_id, 'reporter_id' );
		$t_project_id = bug_get_field( $f_bug_id, 'project_id' );

		echo '<select name="reporter_id">';
		print_reporter_option_list( $t_reporter_id, $t_project_id );
		echo '</select>';
	}
?>