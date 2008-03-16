<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

	# --------------------------------------------------------
	# $Id: adm_config_report.php,v 1.9.2.1 2007-10-13 22:32:25 giallu Exp $
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

	function print_config_value_as_string( $p_type, $p_value ) {
		switch( $p_type ) {
			case CONFIG_TYPE_INT:
				$t_value = (integer)$p_value;
				echo $t_value;
				return;
			case CONFIG_TYPE_STRING:
				$t_value = config_eval( $p_value );
				echo string_nl2br( string_html_specialchars( "'$t_value'" ) );
				return;
			case CONFIG_TYPE_COMPLEX:
				$t_value = unserialize( $p_value );
				break;
			default:
				$t_value = config_eval( $p_value );
				break;
		}
		
		echo '<pre>';
		if ( function_exists( 'var_export' ) ) {
			var_export( $t_value );
		} else {
			print_r( $t_value );
		}
		echo '</pre>';
	}

	$t_config_table = config_get_global( 'mantis_config_table' );
	$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table ORDER BY user_id, project_id, config_id";
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
			<td class="center">
				<?php echo lang_get( 'username' ) ?>
			</td>
			<td class="center">
				<?php echo lang_get( 'project_name' ) ?>
			</td>
			<td>
				<?php echo lang_get( 'configuration_option' ) ?>
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
			<td class="center">
				<?php echo lang_get( 'actions' ) ?>
			</td>
		</tr>
<?php
	while ( $row = db_fetch_array( $result ) ) {
		extract( $row, EXTR_PREFIX_ALL, 'v' );

?>
<!-- Repeated Info Rows -->
		<tr <?php echo helper_alternate_class() ?> valign="top">
			<td class="center">
				<?php echo ($v_user_id == 0) ? lang_get( 'all_users' ) : user_get_name( $v_user_id ) ?>
			</td>
			<td class="center">
				<?php echo project_get_name( $v_project_id ) ?>
			</td>
			<td>
				<?php echo string_display( $v_config_id ) ?>
			</td>
			<td class="center">
				<?php echo string_display( get_config_type( $v_type ) ) ?>
			</td>
			<td class="left">
				<?php print_config_value_as_string( $v_type, $v_value ) ?>
			</td>
			<td class="center">
				<?php echo get_enum_element( 'access_levels', $v_access_reqd ) ?>
			</td>
			<td class="center">
				<?php
					if ( config_can_delete( $v_config_id ) ) {
						print_button( 'adm_config_delete.php?user_id=' . $v_user_id . '&amp;project_id=' . $v_project_id . '&amp;config_option=' . $v_config_id, lang_get( 'delete_link' ) );
					} else {
						echo '&nbsp;';
					}
				?>
			</td>
		</tr>
<?php
	} # end for loop
?>
</table>
<?php
    if ( access_has_global_level( config_get('set_configuration_threshold' ) ) ) {
?>
<br />
<!-- Config Set Form -->
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'set_configuration_option' ) ?>
	</td>
</tr>
		<form method="post" action="adm_config_set.php">
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td>
		<select name="user_id">
			<option value="0" selected="selected"><?php echo lang_get( 'all_users' ); ?></option>
			<?php print_user_option_list( auth_get_current_user_id() ) ?>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'project_name' ) ?>
	</td>
	<td>
		<select name="project_id">
			<option value="0" selected="selected"><?php echo lang_get( 'all_projects' ); ?></option>
			<?php print_project_option_list( ALL_PROJECTS, false ) ?>" />
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'configuration_option' ) ?>
	</td>
	<td>
			<input type="text" name="config_option" value="" size="64" maxlength="64" />
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'configuration_option_type' ) ?>
	</td>
	<td>
		<select name="type">
			<option value="default" selected="selected">default</option>
			<option value="string">string</option>
			<option value="integer">integer</option>
			<option value="complex">complex</option>
		</select>
	</td>
</tr>
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'configuration_option_value' ) ?>
	</td>
	<td>
			<textarea name="value" cols="80" rows="10"></textarea>
	</td>
</tr>
<tr>
	<td colspan="2">
			<input type="submit" name="config_set" class="button" value="<?php echo lang_get( 'set_configuration_option' ) ?>" />
	</td>
</tr>
		</form>
</table>
<?php
	} # end user can change config
?>
</div>
<?php
	html_page_bottom1( __FILE__ );
?>