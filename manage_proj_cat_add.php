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

	if ( is_blank( $f_category ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}

	$t_categories_array = explode( '|', $f_category );
	$t_count = count( $t_categories_array );
	$result = true;
	$duplicate = false;

	foreach ( $t_categories_array as $t_category ) {
		$t_category = trim( $t_category );
		if ( $t_category == '') {
			continue;
		}

		$t_duplicate = is_duplicate_category( $f_project_id, $t_category );
		if ( !$t_duplicate ) {
			$t_result = category_add( $f_project_id, $t_category );
			$result = $result && $t_result;
		} else {
			$duplicate = true;
			$result = false;
		}
	}

	$t_redirect_url = 'manage_proj_edit_page.php?f_project_id='.$f_project_id;
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
		echo lang_get( 'operation_successful' ).'<br />';
	} else if ( $duplicate ) {		# DUPLICATE
		echo $MANTIS_ERROR[ERROR_DUPLICATE_CATEGORY].'<br />';
	} else {						# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
