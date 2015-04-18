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
	 * @copyright Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	access_ensure_project_level( config_get( 'view_configuration_threshold' ) );

	$t_read_write_access = access_has_global_level( config_get('set_configuration_threshold' ) );

	html_page_top( lang_get( 'configuration_report' ) );

	print_manage_menu( 'adm_config_report.php' );
	print_manage_config_menu( 'adm_config_report.php' );

	$t_config_types = array(
		CONFIG_TYPE_DEFAULT => 'default',
		CONFIG_TYPE_INT     => 'integer',
		CONFIG_TYPE_FLOAT   => 'float',
		CONFIG_TYPE_COMPLEX => 'complex',
		CONFIG_TYPE_STRING  => 'string',
	);

	function get_config_type( $p_type ) {
		global $t_config_types;

		if( array_key_exists( $p_type, $t_config_types ) ) {
			return $t_config_types[$p_type];
		} else {
			return $t_config_types[CONFIG_TYPE_DEFAULT];
		}
	}

	function print_config_value_as_string( $p_type, $p_value, $p_for_display = true ) {
		$t_corrupted = false;

		switch( $p_type ) {
			case CONFIG_TYPE_DEFAULT:
				return;
			case CONFIG_TYPE_FLOAT:
				echo (float)$p_value;
				return;
			case CONFIG_TYPE_INT:
				echo (integer)$p_value;
				return;
			case CONFIG_TYPE_STRING:
				$t_value = string_nl2br( string_html_specialchars( config_eval( $p_value ) ) );
				if( $p_for_display ) {
					$t_value = "'$t_value'";
				}
				echo $t_value;
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

		if( $t_corrupted ) {
			$t_output = $p_for_display ? lang_get( 'configuration_corrupted' ) : '';
		} else {
			$t_output = var_export( $t_value, true );
		}

		if( $p_for_display ) {
			echo '<pre>' . string_attribute( $t_output ) . '</pre>';
		} else {
			echo $t_output;
		}
	}

	function print_option_list_from_array( $p_array, $p_filter_value ) {
		foreach( $p_array as $t_key => $t_value ) {
			echo "<option value='$t_key'";
			check_selected( $p_filter_value, $t_key );
			echo '>' . string_attribute( $t_value ) . "</option>\n";
		}
	}


	# Get filter values
	$t_filter_save          = gpc_get_bool( 'save' );
	$t_filter_default       = gpc_get_bool( 'default_filter_button', false );
	$t_filter_reset         = gpc_get_bool( 'reset_filter_button', false );
	if( $t_filter_default ) {
		$t_filter_user_value    = ALL_USERS;
		$t_filter_project_value = ALL_PROJECTS;
		$t_filter_config_value  = META_FILTER_NONE;
	} else if( $t_filter_reset ) {
		$t_filter_user_value    = META_FILTER_NONE;
		$t_filter_project_value = META_FILTER_NONE;
		$t_filter_config_value  = META_FILTER_NONE;
	} else {
		$t_filter_user_value    = gpc_get_int( 'filter_user_id', ALL_USERS );
		$t_filter_project_value = gpc_get_int( 'filter_project_id', ALL_PROJECTS );
		$t_filter_config_value  = gpc_get_string( 'filter_config_id', META_FILTER_NONE );
	}

	# Manage filter's persistency through cookie
	$t_cookie_name = config_get( 'manage_config_cookie' );
	if( $t_filter_save ) {
		# Save user's filter to the cookie
		$t_cookie_string = implode(
			':',
			array(
				$t_filter_user_value,
				$t_filter_project_value,
				$t_filter_config_value,
			)
		);
		gpc_set_cookie( $t_cookie_name, $t_cookie_string, true );
	} else {
		# Retrieve the filter from the cookie if it exists
		$t_cookie_string = gpc_get_cookie( $t_cookie_name, null );

		if( null !== $t_cookie_string ) {
			$t_cookie_contents = explode( ':', $t_cookie_string );

			$t_filter_user_value    = $t_cookie_contents[0];
			$t_filter_project_value = $t_cookie_contents[1];
			$t_filter_config_value  = $t_cookie_contents[2];

			if( $t_filter_project_value != META_FILTER_NONE && !project_exists( $t_filter_project_value ) ) {
				$t_filter_project_value = ALL_PROJECTS;
			}

			if(    $t_filter_config_value != META_FILTER_NONE
			   && !is_blank( $t_filter_config_value )
			   && @config_get_global( $t_filter_config_value ) === null
			) {
				$t_filter_config_value = META_FILTER_NONE;
			}
		}
	}

	# Get config edit values
	$t_edit_user_id         = gpc_get_int( 'user_id', $t_filter_user_value == META_FILTER_NONE ? ALL_USERS : $t_filter_user_value );
	$t_edit_project_id      = gpc_get_int( 'project_id', $t_filter_project_value == META_FILTER_NONE ? ALL_PROJECTS : $t_filter_project_value );
	$t_edit_option          = gpc_get_string( 'config_option', $t_filter_config_value == META_FILTER_NONE ? '' : $t_filter_config_value );
	$t_edit_type            = gpc_get_string( 'type', CONFIG_TYPE_DEFAULT );
	$t_edit_value           = gpc_get_string( 'value', '' );

	# Apply filters
	$t_config_table  = db_get_table( 'mantis_config_table' );
	$t_project_table = db_get_table( 'mantis_project_table' );

	# Get users in db having specific configs
	$query = "SELECT DISTINCT user_id
		FROM $t_config_table
		WHERE user_id <> " . db_param() ;
	$t_result = db_query_bound( $query, array( ALL_USERS ) );
	if( $t_filter_user_value != META_FILTER_NONE && $t_filter_user_value != ALL_USERS ) {
		# Make sure the filter value exists in the list
		$t_users_list[$t_filter_user_value] = user_get_name( $t_filter_user_value );
	} else {
		$t_users_list = array();
	}
	while ( $row = db_fetch_array( $t_result ) ) {
		$t_user_id = $row['user_id'];
		$t_users_list[$t_user_id] = user_get_name( $t_user_id );
	}
	asort( $t_users_list );
	# Prepend '[any]' and 'All Users' to the list
	$t_users_list = array(
			META_FILTER_NONE => '[' . lang_get( 'any' ) . ']',
			ALL_USERS        => lang_get( 'all_users' ),
		)
		+ $t_users_list;

	# Get projects in db with specific configs
	$query = "SELECT DISTINCT project_id, pt.name as project_name
		FROM $t_config_table as ct
		JOIN $t_project_table as pt ON pt.id = ct.project_id
		WHERE project_id!=0
		ORDER BY project_name";
	$t_result = db_query_bound( $query );
	$t_projects_list[META_FILTER_NONE] = '[' . lang_get( 'any' ) . ']';
	$t_projects_list[ALL_PROJECTS] = lang_get( 'all_projects' );
	while ( $row = db_fetch_array( $t_result ) ) {
		extract( $row, EXTR_PREFIX_ALL, 'v' );
		$t_projects_list[$v_project_id] = $v_project_name;
	}

	# Get config list used in db
	$query = "SELECT DISTINCT config_id
		FROM $t_config_table
		ORDER BY config_id";
	$t_result = db_query_bound( $query );
	$t_configs_list[META_FILTER_NONE] = '[' . lang_get( 'any' ) . ']';
	if( $t_filter_config_value != META_FILTER_NONE ) {
		# Make sure the filter value exists in the list
		$t_configs_list[$t_filter_config_value] = $t_filter_config_value;
	}
	while ( $row = db_fetch_array( $t_result ) ) {
		extract( $row, EXTR_PREFIX_ALL, 'v' );
		$t_configs_list[$v_config_id] = $v_config_id;
	}

	# Build filter's where clause
	$t_where = '';
	$t_param = array();
	if( $t_filter_user_value != META_FILTER_NONE ) {
		$t_where .= " AND user_id = " . db_param();
		$t_param[] = $t_filter_user_value;
	}
	if( $t_filter_project_value != META_FILTER_NONE ) {
		$t_where .= " AND project_id = " . db_param();
		$t_param[] = $t_filter_project_value;
	}
	if( $t_filter_config_value != META_FILTER_NONE ) {
		$t_where .= " AND config_id = " . db_param();
		$t_param[] = $t_filter_config_value;
	}
	if( $t_where != '' ) {
		$t_where = " WHERE 1=1 " . $t_where;
	}

	$query = "SELECT config_id, user_id, project_id, type, value, access_reqd
		FROM $t_config_table
		$t_where
		ORDER BY user_id, project_id, config_id ";
	$result = db_query_bound( $query, $t_param );
?>

<br />

<!-- FILTER FORM -->
<div align="center">

<form id="filter_form" method="post">
<?php # CSRF protection not required here - form does not result in modifications ?>
	<input type="hidden" name="save" value="1" />

<table class="width100" cellspacing="1">
	<!-- Title -->
	<tr>
		<td class="form-title" colspan="7">
			<?php echo lang_get( 'filters' ) ?>
		</td>
	</tr>

	<tr class="row-category2">
		<td class="small-caption">
			<?php echo lang_get( 'username' ); ?>: <br />
		</td>
		<td class="small-caption">
			<?php echo lang_get( 'project_name' ); ?>: <br />
		</td>
		<td class="small-caption">
			<?php echo lang_get( 'configuration_option' ); ?>: <br />
		</td>
	</tr>

	<tr class="row-1">
		<td>
			<select name="filter_user_id">
			<?php
			print_option_list_from_array( $t_users_list, $t_filter_user_value );
			?>
			</select>
		</td>
		<td>
			<select name="filter_project_id">
			<?php
			print_option_list_from_array( $t_projects_list, $t_filter_project_value );
			?>
			</select>
		</td>
		<td>
			<select name="filter_config_id">
			<?php
			print_option_list_from_array( $t_configs_list, $t_filter_config_value );
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="3">
			<input name="apply_filter_button" type="submit" class="button-small" value="<?php echo lang_get( 'filter_button' )?>" />
			<input name="default_filter_button" type="submit" class="button-small" value="<?php echo lang_get( 'default_filter' )?>" />
			<input name="reset_filter_button" type="submit" class="button-small" value="<?php echo lang_get( 'reset_query' )?>" />
		</td>
	</tr>
</table>
</form>

<br />

<!-- CONFIGURATIONS LIST -->
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
			<?php if( $t_read_write_access ) { ?>
			<td class="center">
				<?php echo lang_get( 'actions' ) ?>
			</td>
			<?php } ?>
		</tr>

<?php
	# Pre-generate a form security token to avoid performance issues when the
	# db contains a large number of configurations
	$t_form_security_token = form_security_token( 'adm_config_delete' );

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
<?php
		if( $t_read_write_access ) {
?>
			<td class="center">
<?php
			if( config_can_delete( $v_config_id ) ) {
				# Update button (will populate edit form at page bottom)
				print_button(
					'#config_set_form',
					lang_get( 'edit_link' ),
					array(
						'user_id'           => $v_user_id,
						'project_id'        => $v_project_id,
						'config_option'     => $v_config_id,
						'type'              => $v_type,
						'value'             => $v_value,
					),
					OFF
				);

				# Delete button
				print_button(
					'adm_config_delete.php',
					lang_get( 'delete_link' ),
					array(
						'user_id'       => $v_user_id,
						'project_id'    => $v_project_id,
						'config_option' => $v_config_id,
					),
					$t_form_security_token
				);
			} else {
				echo '&#160;';
			}
?>
			</td>
<?php
		} # end if config_can_delete
?>
		</tr>
<?php
	} # end while loop
?>
</table>


<?php
	# Only display the edit form if user is authorized to change configuration
if( $t_read_write_access ) {
?>
<br />

<!-- Config Set Form -->

<form id="config_set_form" method="post" action="adm_config_set.php">
<?php echo form_security_field( 'adm_config_set' ) ?>
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<?php echo lang_get( 'set_configuration_option' ) ?>
	</td>
</tr>

<!-- Username -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'username' ) ?>
	</td>
	<td>
		<select name="user_id">
			<option value="<?php echo ALL_USERS; ?>"
				<?php check_selected( $t_edit_user_id, ALL_USERS ) ?>>
				<?php echo lang_get( 'all_users' ); ?>
			</option>
			<?php print_user_option_list( $t_edit_user_id ) ?>
		</select>
	</td>
</tr>

<!-- Project -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'project_name' ) ?>
	</td>
	<td>
		<select name="project_id">
			<option value="<?php echo ALL_PROJECTS; ?>"
				<?php check_selected( $t_edit_project_id, ALL_PROJECTS ); ?>>
				<?php echo lang_get( 'all_projects' ); ?>
			</option>
			<?php print_project_option_list( $t_edit_project_id, false ) ?>
		</select>
	</td>
</tr>

<!-- Config option name -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'configuration_option' ) ?>
	</td>
	<td>
		<input type="text" name="config_option"
			value="<?php echo string_attribute( $t_edit_option ); ?>"
			size="64" maxlength="64" />
	</td>
</tr>

<!-- Option type -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'configuration_option_type' ) ?>
	</td>
	<td>
		<select name="type">
			<?php print_option_list_from_array( $t_config_types, $t_edit_type ); ?>
		</select>
	</td>
</tr>

<!-- Option Value -->
<tr <?php echo helper_alternate_class() ?> valign="top">
	<td>
		<?php echo lang_get( 'configuration_option_value' ) ?>
	</td>
	<td>
		<textarea name="value" cols="80" rows="10"><?php
			echo print_config_value_as_string( $t_edit_type, $t_edit_value, false );
		?></textarea>
	</td>
</tr>

<!-- Submit button -->
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
