<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_file_add.php,v 1.35 2002-12-29 10:26:07 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	# Add file to a bug and then view the bug
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id	= gpc_get_int( 'bug_id' );
	$f_file		= gpc_get_file( 'file' );
	
	if ( ! file_allow_bug_upload( $f_bug_id ) ) {
		access_denied();
	}

	project_access_check( $f_bug_id );
	check_access( config_get( 'upload_bug_file_threshold' ) );

	if ( !is_uploaded_file( $f_file['tmp_name'] ) || 0 == $f_file['size'] ) {
		trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
	}

	$t_upload_method	= config_get( 'file_upload_method' );

	if ( DISK == $t_upload_method || FTP == $t_upload_method )  {
		$t_project_id	= bug_get_field( $f_bug_id, 'project_id' );
		$t_file_path	= project_get_field( $t_project_id, 'file_path' );

		if ( !file_exists( $t_file_path ) ) {
			trigger_error( ERROR_NO_DIRECTORY, ERROR );
		}
	}

	file_add( $f_bug_id, $f_file['tmp_name'], $f_file['name'], $f_file['type'] );

	# Determine which view page to redirect back to.
	$t_redirect_url = string_get_bug_view_url( $f_bug_id );

	print_page_top1();
	print_meta_redirect( $t_redirect_url );
	print_page_top2();
?>
<br />
<div align="center">
<?php
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
