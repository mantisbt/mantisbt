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

	if ( empty( $f_field_id ) || empty( $f_project_id ) ) {
		print_mantis_error( ERROR_EMPTY_FIELD );
	}

	$result = custom_field_remove( $f_field_id, $f_project_id );

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
	} else {						# FAILURE
		print_sql_error( $query );
	}

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>
