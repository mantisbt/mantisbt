<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: adm_config_report.php,v 1.1 2006-04-21 13:01:25 vboctor Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	access_ensure_project_level( config_get( 'view_configuration_threshold' ) );

	$t_core_path = config_get( 'core_path' );

	html_page_top1( lang_get( 'configuration_report' ) );
	html_page_top2();

	print_manage_menu( 'adm_config_report.php' );
	print_manage_config_menu( 'adm_config_report.php' );

	function get_config_type( $p_type ) {
		switch( $p_type ) {
			case CONFIG_TYPE_INT:
				return "integer";
			case CONFIG_TYPE_COMPLEX:
				return "complex";
			case CONFIG_TYPE_STRING:
			default:
				return "string";
		}
	}

	function get_config_value_as_string( $p_type, $p_value ) {
		switch( $p_type ) {
			case CONFIG_TYPE_INT:
				return (integer)$p_value;
			case CONFIG_TYPE_COMPLEX:
				return nl2br( var_dump( unserialize( $p_value ) ) );
			case CONFIG_TYPE_STRING:
			default:
				return config_eval( $p_value );
		}
	}

	$t_config_table = config_get_global( 'mantis_config_table' );
	$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table ORDER BY user_id, project_id";
	$result = db_query( $query );
?>
<br />
<div align="center">
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'database_configuration' ) ?>
	</td>
</tr>
		<tr class="row-category">
			<td>
				<?php echo lang_get( 'configuration_option' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'username' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'project_name' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'configuration_option_type' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'configuration_option_value' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'access_level' ) ?>
			</td>
		</tr>
<?php
	while ( $row = db_fetch_array( $result ) ) {
		extract( $row, EXTR_PREFIX_ALL, 'v' );

?>
<!-- Repeated Info Rows -->
		<tr <?php echo helper_alternate_class() ?>>
			<td>
				<?php echo string_display( $v_config_id ) ?>
			</td>
			<td class="center">
				<?php echo ($v_user_id == 0) ? lang_get( 'all_users' ) : user_get_name( $v_user_id ) ?>
			</td>
			<td class="center">
				<?php echo project_get_name( $v_project_id ) ?>
			</td>
			<td class="center">
				<?php echo string_display( get_config_type( $v_type ) ) ?>
			</td>
			<td class="left">
				<?php echo string_display( get_config_value_as_string( $v_type, $v_value ) ) ?>
			</td>
			<td class="center">
				<?php echo get_enum_element( 'access_levels', $v_access_reqd ) ?>
			</td>
		</tr>
<?php
	} # end for loop
?>
</table>
</div>
<?php
	html_page_bottom1( __FILE__ );
?>