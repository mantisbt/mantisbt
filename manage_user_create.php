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
	check_access( ADMINISTRATOR );

	$f_username			= gpc_get_string( 'f_username' );
	$f_password			= gpc_get_string( 'f_password' );
	$f_password_verify	= gpc_get_string( 'f_password_verify' );
	$f_email			= gpc_get_string( 'f_email' );
	$f_access_level		= gpc_get_string( 'f_access_level' );
	$f_protected		= gpc_get_bool( 'f_protected' );
	$f_enabled			= gpc_get_bool( 'f_enabled' );

	# check for empty username
	$f_username = trim( $f_username );
	if ( empty( $f_username ) ) {
		trigger_error( ERROR_EMPTY_FIELD, ERROR );
	}

	if ( $f_password != $f_password_verify ) {
		trigger_error( ERROR_USER_CREATE_PASSWORD_MISMATCH, ERROR );
	}

	user_create( $f_username, $f_password, $f_email, $f_access_level, $f_protected, $f_enabled )

?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<p>
<div align="center">
<?php
	$f_access_level = get_enum_element( 'access_levels', $f_access_level );
	echo "$s_created_user_part1 <span class=\"bold\">$f_username</span> $s_created_user_part2 <span class=\"bold\">$f_access_level</span><p>";

	print_bracket_link('manage_page.php', $s_proceed );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
