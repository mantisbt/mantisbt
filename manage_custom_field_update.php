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

	$f_field_id		             = gpc_get_int(    'f_field_id' );
	$t_values['name']            = gpc_get_string( 'f_name' );
	$t_values['type']            = gpc_get_int(    'f_type' );
	$t_values['possible_values'] = gpc_get_string( 'f_possible_values' );
	$t_values['default_value']   = gpc_get_string( 'f_default_value' );
	$t_values['valid_regexp']    = gpc_get_string( 'f_valid_regexp' );
	$t_values['access_level_r']  = gpc_get_int(    'f_access_level_r' );
	$t_values['access_level_rw'] = gpc_get_int(    'f_access_level_rw' );
	$t_values['length_min']      = gpc_get_int(    'f_length_min' );
	$t_values['length_max']      = gpc_get_int(    'f_length_max' );
	$t_values['advanced']        = gpc_get_bool(   'f_advanced' );

	custom_field_update( $f_field_id, $t_values );

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
	echo lang_get( 'operation_successful' ).'<br />';

	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>

<?php print_page_bot1( __FILE__ ) ?>
