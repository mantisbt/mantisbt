<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_custom_field_create.php,v 1.7 2003-01-24 14:59:36 jlatour Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	require_once( $g_core_path . 'custom_field_api.php' );
?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_custom_fields' ) );

	$f_name	= gpc_get_string( 'name' );

	$f_name = trim( $f_name );
	if ( is_blank( $f_name ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if ( ! custom_field_is_name_unique( $f_name ) ) {
		trigger_error( ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE, ERROR );
	}

	$t_field_id = custom_field_create( $f_name );

	if ( ON == config_get( 'custom_field_edit_after_create' ) ) {
		$t_redirect_url = "manage_custom_field_edit_page.php?field_id=$t_field_id";
	} else {
		$t_redirect_url = 'manage_custom_field_page.php';
	}
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
