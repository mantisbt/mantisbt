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

	$f_name			= gpc_get_string( 'f_name' );

	$t_names_array = explode( '|', $f_name );
	$t_duplicate = false;

	$t_count_added = 0;
	foreach ( $t_names_array as $t_name ) {
		$t_name = trim( $t_name );
		if ( is_blank( $t_name ) ) {
			continue;
		}

		if ( custom_field_is_name_unique( $t_name ) ) {
			$t_count_added++;
			custom_field_create( $t_name );
		} else {
			$t_duplicate = true;
		}
	}

	$t_redirect_url = 'manage_custom_field_page.php';
?>
<?php print_page_top1() ?>
<?php
		print_meta_redirect( $t_redirect_url );
?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $t_duplicate ) {		# DUPLICATE
		echo $MANTIS_ERROR[ERROR_CUSTOM_FIELD_NAME_NOT_UNIQUE] . '<br />';
	} else if ( 0 == $t_count_added ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
