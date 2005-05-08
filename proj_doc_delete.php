<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: proj_doc_delete.php,v 1.25 2005-05-08 20:42:08 marcelloscata Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	# Check if project documentation feature is enabled.
	if ( OFF == config_get( 'enable_project_documentation' ) ) {
		access_denied();
	}

	$f_file_id = gpc_get_int( 'file_id' );
	$f_title = gpc_get_string( 'title', '' );

	$t_project_id = file_get_field( $f_file_id, 'project_id', 'project' );

	access_ensure_project_level( config_get( 'upload_project_file_threshold' ), $t_project_id );

	# Confirm with the user
	helper_ensure_confirmed( lang_get( 'confirm_file_delete_msg' ) .
		'<br/>' . lang_get( 'filename' ) . ': ' . $f_title,
		lang_get( 'file_delete_button' ) );

	file_delete( $f_file_id, 'project' );

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
