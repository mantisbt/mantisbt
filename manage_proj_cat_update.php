<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_cat_update.php,v 1.32 2005-02-12 20:01:06 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'category_api.php' );
?>
<?php
	$f_project_id		= gpc_get_int( 'project_id' );
	$f_category			= gpc_get_string( 'category' );
	$f_new_category		= gpc_get_string( 'new_category' );
	$f_assigned_to		= gpc_get_int( 'assigned_to', 0 );

	access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );

	if ( is_blank( $f_new_category ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	$f_category		= trim( $f_category );
	$f_new_category	= trim( $f_new_category );

	# check for duplicate
	if ( strtolower( $f_category ) == strtolower( $f_new_category ) ||
		 category_is_unique( $f_project_id, $f_new_category ) ) {
		category_update( $f_project_id, $f_category, $f_new_category, $f_assigned_to );
	} else {
		trigger_error( ERROR_CATEGORY_DUPLICATE, ERROR );
	}

	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
?>
<?php
	html_page_top1();

	html_meta_redirect( $t_redirect_url );

	html_page_top2();
?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
