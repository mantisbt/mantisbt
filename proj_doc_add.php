<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_add.php,v 1.51 2005-07-17 14:10:59 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'file_api.php' );

	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}

	access_ensure_project_level( config_get( 'upload_project_file_threshold' ) );

	$f_title = gpc_get_string( 'title' );
	$f_description = gpc_get_string( 'description' );
	$f_file = gpc_get_file( 'file' );

	if ( is_blank( $f_title ) ) {
		error_parameters( lang_get( 'title' ) );
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

    $f_file_error =  ( isset( $f_file['error'] ) ) ? $f_file['error'] : 0;
	file_add( 0, $f_file['tmp_name'], $f_file['name'], $f_file['type'], 'project', $f_file_error, $f_title, $f_description );

	$t_redirect_url = 'proj_doc_page.php';

	html_page_top1();
	html_meta_redirect( $t_redirect_url );
	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ).'<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
