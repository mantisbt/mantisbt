<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	check_access( MANAGER );

	if ( empty( $f_caption ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}

	$t_captions_array = explode( '|', $f_caption );
	$t_count = count( $t_captions_array );
	$duplicate = false;

	foreach ( $t_captions_array as $t_caption ) {
		$t_caption = trim( $t_caption );
		if ( $t_caption == '') {
			continue;
		}

		if ( custom_field_is_caption_unique( $t_caption ) ) {
			$t_generated_id = custom_field_create( $t_caption );
			custom_field_bind( $t_generated_id, $f_project_id );
		} else {
			$duplicate = true;
		}
	}

	$t_redirect_url = 'manage_proj_edit_page.php?f_project_id='.$f_project_id;
?>
<?php print_page_top1() ?>
<?php
		print_meta_redirect( $t_redirect_url );
?>
<?php print_page_top2() ?>

<br />
<div align="center">
<?php
	if ( $duplicate ) {		# DUPLICATE
		echo $MANTIS_ERROR[ERROR_CUSTOM_FIELD_CAPTION_NOT_UNIQUE].'<br />';
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
