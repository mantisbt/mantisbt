<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: ajax_api.php,v 1.1 2006-05-16 23:59:28 vboctor Exp $
	# --------------------------------------------------------

	$t_core_dir = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

	require_once( $t_core_dir . 'bug_api.php' );

	### Ajax API ###

	function ajax_click_to_edit( $p_initial_string, $p_element_id_prefix, $p_query_string ) {
		$t_element_id_target = $p_element_id_prefix . '_target';
		$t_element_id_edit = $p_element_id_prefix . '_edit';
		$t_edit = lang_get( 'edit_link' );

		$t_return  = '<a id="' . $t_element_id_target . '">' . $p_initial_string . '</a> ';
		$t_return .= '<a id="' . $t_element_id_edit . '" onclick="';
		$t_return .= "AjaxLoad('$t_element_id_target', '$p_query_string', '$t_element_id_edit' )";
		$t_return .= '"><small>[' . $t_edit . ']</small></a>';
		
		return $t_return;
	}
?>