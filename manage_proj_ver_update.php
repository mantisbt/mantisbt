<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_ver_update.php,v 1.26 2003-02-15 10:25:17 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'version_api.php' );
?>
<?php
	$f_project_id	= gpc_get_int( 'project_id' );
	$f_version		= gpc_get_string( 'version' );
	$f_date_order	= gpc_get_string( 'date_order' );
	$f_new_version	= gpc_get_string( 'new_version' );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( is_blank( $f_new_version ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_version		= trim( $f_version );
	$f_new_version	= trim( $f_new_version );

	version_update( $f_project_id, $f_version, $f_new_version, $f_date_order );

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
?>
<?php
	print_page_top1();

	print_meta_redirect( $t_redirect_url );

	print_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
