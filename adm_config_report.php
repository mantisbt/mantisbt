<?php
# MantisBT - a php based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

	/**
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	access_ensure_project_level( config_get( 'view_configuration_threshold' ) );

	html_page_top( lang_get( 'configuration_report' ) );

	print_manage_menu( 'adm_config_report.php' );
	print_manage_config_menu( 'adm_config_report.php' );

	function get_config_type( $p_type ) {
		switch( $p_type ) {
			case CONFIG_TYPE_INT:
				return "integer";
			case CONFIG_TYPE_FLOAT:
				return "float";
			case CONFIG_TYPE_COMPLEX:
				return "complex";
			case CONFIG_TYPE_STRING:
			default:
				return "string";
		}
	}

	function print_config_value_as_string( $p_type, $p_value ) {
		$t_corrupted = false;

		switch( $p_type ) {
			case CONFIG_TYPE_FLOAT:
				$t_value = (float)$p_value;
				echo $t_value;
				return;
			case CONFIG_TYPE_INT:
				$t_value = (integer)$p_value;
				echo $t_value;
				return;
			case CONFIG_TYPE_STRING:
				$t_value = config_eval( $p_value );
				echo string_nl2br( string_html_specialchars( "'$t_value'" ) );
				return;
			case CONFIG_TYPE_COMPLEX:
				$t_value = @unserialize( $p_value );
				if ( $t_value === false ) {
					$t_corrupted = true;
				}
				break;
			default:
				$t_value = config_eval( $p_value );
				break;
		}

		echo '<pre>';

		if ( $t_corrupted ) {
			echo lang_get( 'configuration_corrupted' );
		} else {
			if ( function_exists( 'var_export' ) ) {
				var_export( $t_value );
			} else {
				print_r( $t_value );
			}
		}

		echo '</pre>';
	}

	$t_config_table = db_get_table( 'mantis_config_table' );

	$t_user_table = db_get_table( 'mantis_user_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );
		# Get users in db with specific configs
		$query = "SELECT DISTINCT user_id, ut.realname as username, ut.username as idrh
			  FROM $t_config_table as ct
			  JOIN $t_user_table as ut
			  	ON ut.id=ct.user_id
			  ORDER BY username";
		$t_result = db_query_bound($query);
		$t_users_list = array();
		$t_users_list[-1] = '<i>'.lang_get("none_filter").'</i>';
		$t_users_list[ALL_USERS] = lang_get("all_users");
		while ( $row = db_fetch_array( $t_result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			$t_users_list[$v_user_id] = "$v_username ($v_idrh)";
		}

		# Get projects in db with specific configs
		$query = "SELECT DISTINCT project_id, pt.name as project_name
			  FROM $t_config_table as ct
			  JOIN $t_project_table as pt
			  	ON pt.id=ct.project_id
			  WHERE project_id!=0
			  ORDER BY project_name";
		$t_result = db_query_bound($query);
		$t_projects_list = array();
		$t_projects_list[-1] = '<i>'.lang_get("none_filter").'</i>';
		$t_projects_list[ALL_PROJECTS] = lang_get("all_projects");
		while ( $row = db_fetch_array( $t_result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			$t_projects_list[$v_project_id] = $v_project_name;
		}

		# Get config listused in db
		$query = "SELECT DISTINCT config_id
			  FROM $t_config_table
			  ORDER BY config_id";
		$t_result = db_query_bound($query);
		$t_configs_list = array();
		$t_configs_list[-1] = '<i>'.lang_get("none_filter").'</i>';
		while ( $row = db_fetch_array( $t_result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' );
			$t_configs_list[$v_config_id] = $v_config_id;
		}

	function print_option_list_from_array($array,$filter_value){
			foreach ($array as $key => $value){
				if($filter_value == $key){
				$selected = " selected='selected' "	;
				}else{
					$selected ="";
				}
				echo "<option value='$key' $selected>$value</option>\n";
			}

		}

	$t_filter_user_value 	= gpc_get_int('filter_user_id',-1);
	$t_filter_project_value = gpc_get_int('filter_project_id',-1);
	$t_filter_config_value 	= gpc_get_string('filter_config_id',-1);	

	$where = '';
	if($t_filter_user_value != -1){
		$where .= " AND user_id=$t_filter_user_value ";
	}
	if($t_filter_project_value != -1){
		$where .= " AND project_id=$t_filter_project_value ";
	}
	if($t_filter_config_value != -1){
		$where .= " AND config_id='$t_filter_config_value' ";
	}
	if($where!=''){
		$where = " WHERE 1=1 ".$where;
	}

	$query = "SELECT config_id, user_id, project_id, type, value, access_reqd FROM $t_config_table $where ORDER BY user_id, project_id, config_id ";
	$result = db_query_bound( $query );
?>

<br />
<div align="center">
<form action="" name="form_filter">
<table class="width100" cellspacing="1">
	<!-- Title -->
	<tr>
		<td class="form-title" colspan="7">
			<?php echo lang_get( 'filters' ) ?>
		</td>
	</tr>

	<tr class="row-category" style="width:30%;">
		<td class="center">
			<?php echo lang_get( 'username' );?> : <br />
			<select name="filter_user_id" style="width:200px;">
			<?php
			print_option_list_from_array($t_users_list, $t_filter_user_value);
			?>
			</select>
		</td>
		<td class="center" style="width:30%;">
			<?php echo lang_get( 'project_name' );?> : <br />
			<select name="filter_project_id" style="width:200px;">
			<?php
			print_option_list_from_array($t_projects_list, $t_filter_project_value);
			?>
			</select>
		</td>
		<td class="center" style="width:40%;">
			<?php echo lang_get( 'configuration_option' );?> : <br />
			<select name="filter_config_id" style="width:200px;">
			<?php
			print_option_list_from_array($t_configs_list, $t_filter_config_value);
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<input type="submit" value="<?php echo lang_get('filter_button');?>"/>
		</td>
	</tr>
</table>
</form>

<br />

<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="7">
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
				<?php echo ($v_user_id == 0) ? lang_get( 'all_users' ) : string_display_line( user_get_name( $v_user_id ) ) ?>
			</td>
			<td class="center">
				<?php echo string_display_line( project_get_name( $v_project_id, false ) ) ?>
			</td>
			<td>
				<?php echo string_display_line( $v_config_id ) ?>
			</td>
			<td class="center">
				<?php echo string_display_line( get_config_type( $v_type ) ) ?>
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
						print_button( "adm_config_delete.php?user_id=$v_user_id&project_id=$v_project_id&config_option=$v_config_id", lang_get( 'delete_link' ) );
					} else {
						echo '&#160;';
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
<form method="post" action="adm_config_set.php">
<?php echo form_security_field( 'adm_config_set' ) ?>
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'set_configuration_option' ) ?>
	</td>
</tr>
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
			<?php print_project_option_list( ALL_PROJECTS, false ) ?>
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
</table>
</form>
<?php
	} # end user can change config
?>
</div>
<?php
	html_page_bottom();
