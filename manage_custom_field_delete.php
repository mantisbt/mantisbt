<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_custom_field_delete.php,v 1.15 2004-07-10 00:29:14 int2str Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );
	
	require_once( $t_core_path.'custom_field_api.php' );
?>
<?php
	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

	$f_field_id	= gpc_get_int( 'field_id' );
	$f_return	= strip_tags( gpc_get_string( 'return', 'manage_custom_field_page.php' ) );

	if( 0 < count( custom_field_get_project_ids( $f_field_id ) ) ) {
		helper_ensure_confirmed( lang_get( 'confirm_used_custom_field_deletion' ),
								 lang_get( 'field_delete_button' ) );
	} else {
		helper_ensure_confirmed( lang_get( 'confirm_custom_field_deletion' ),
								 lang_get( 'field_delete_button' ) );
	}

	custom_field_destroy( $f_field_id );

	html_page_top1();
	html_meta_redirect( $f_return );
	html_page_top2();
?>

<br />

<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';

	print_bracket_link( $f_return, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
