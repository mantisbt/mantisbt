<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_add.php,v 1.46 2004-10-08 19:57:46 thraxisp Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'file_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	# Check if project documentation feature is enabled.
	if ( ! file_is_uploading_enabled() ) {
		access_denied();
	}

	if ( ! file_allow_project_upload() ) {
		access_denied();
	}
	
	access_ensure_project_level( config_get( 'upload_project_file_threshold' ) );

	# @@@@ (thraxisp) this needs a filter for project_id == ALL_PROJECTS
	#  it fails later when it tries to find the 'filepath' to store the document
	#  see #4664

	$f_title		= gpc_get_string( 'title' );
	if ( is_blank( $f_title ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_description	= gpc_get_string( 'description' );
	$f_file		= gpc_get_file( 'file' );

	if ( !is_uploaded_file( $f_file['tmp_name'] ) || 0 == $f_file['size'] ) {
		trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
	}

	file_add( 0, $f_file['tmp_name'], $f_file['name'], $f_file['type'], 'project', $f_title, $f_description );

	$t_redirect_url = 'proj_doc_page.php';
?>
<?php html_page_top1() ?>
<?php html_meta_redirect( $t_redirect_url, $g_wait_time ); ?>
<?php html_page_top2() ?>

<br />
<div align="center">
<?php
	print lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
