<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2003  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( config_get( 'manage_custom_fields' ) );

	$f_field_id		= gpc_get_int( 'field_id' );

	if( 0 == count( custom_field_get_project_ids( $f_field_id ) ) ) {
		$result = custom_field_destroy( $f_field_id );
	} else {
		$result = false;
	}

	$t_redirect_url = 'manage_custom_field_page.php';
?>
<?php print_page_top1() ?>
<?php
	if ( $result ) {
		print_meta_redirect( $t_redirect_url );
	}
?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $result ) {				# SUCCESS
		echo lang_get( 'operation_successful' ) . '<br />';
	} else {
		echo $MANTIS_ERROR[ERROR_CUSTOM_FIELD_IN_USE] . '<br />';
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>
