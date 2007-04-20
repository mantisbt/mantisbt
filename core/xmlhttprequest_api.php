<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: xmlhttprequest_api.php,v 1.2 2007-04-20 08:28:23 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'bug_api.php' );
	require_once( $t_core_dir . 'profile_api.php' );
	require_once( $t_core_dir . 'logging_api.php' );
	require_once( $t_core_dir . 'projax_api.php' );

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

	# ---------------
	# Echos a serialized list of platforms starting with the prefix specified in the $_POST
	function xmlhttprequest_platform_get_with_prefix() {
		$f_platform = gpc_get_string( 'platform' );

		$t_unique_entries = profile_get_field_all_for_user( 'platform' );
		$t_matching_entries = projax_array_filter_by_prefix( $t_unique_entries, $f_platform );

		echo projax_array_serialize_for_autocomplete( $t_matching_entries );
	}

	# ---------------
	# Echos a serialized list of OSes starting with the prefix specified in the $_POST
	function xmlhttprequest_os_get_with_prefix() {
		$f_os = gpc_get_string( 'os' );

		$t_unique_entries = profile_get_field_all_for_user( 'os' );
		$t_matching_entries = projax_array_filter_by_prefix( $t_unique_entries, $f_os );

		echo projax_array_serialize_for_autocomplete( $t_matching_entries );
	}

	# ---------------
	# Echos a serialized list of OS Versions starting with the prefix specified in the $_POST
	function xmlhttprequest_os_build_get_with_prefix() {
		$f_os_build = gpc_get_string( 'os_build' );

		$t_unique_entries = profile_get_field_all_for_user( 'os_build' );
		$t_matching_entries = projax_array_filter_by_prefix( $t_unique_entries, $f_os_build );

		echo projax_array_serialize_for_autocomplete( $t_matching_entries );
	}
?>
