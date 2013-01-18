<?php
# MantisBT - A PHP based bugtracking system

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
 * @copyright Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 */

/**
 * MantisBT Core API's
 */
require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

access_ensure_global_level( config_get( 'view_configuration_threshold' ) );

$t_read_write_access = access_has_global_level( config_get('set_configuration_threshold' ) );

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
	echo '<p id="adm-config-value">';
			echo string_nl2br( string_html_specialchars( "'$t_value'" ) );
	echo '</p>';
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

	echo '<pre id="adm-config-value">';

	if ( $t_corrupted ) {
	} else {
		if ( function_exists( 'var_export' ) ) {
			var_export( $t_value );
		} else {
			print_r( $t_value );
		}
	}

	echo '</pre>';
}

function print_option_list_from_array( $p_array, $p_filter_value ) {
	foreach( $p_array as $t_key => $t_value ) {
		echo "<option value='$t_key'";
		check_selected( (string)$p_filter_value, (string)$t_key );
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

		if( $t_filter_project_value != META_FILTER_NONE && project_exists( $t_filter_project_value ) ) {
			$t_filter_project_value = ALL_PROJECTS;
		}
	}
}

# Apply filters
$t_config_table  = db_get_table( 'config' );
$t_project_table = db_get_table( 'project' );

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
if( $t_filter_user_value != META_FILTER_NONE ) {
	$t_where .= " AND user_id = $t_filter_user_value ";
}
if( $t_filter_project_value != META_FILTER_NONE ) {
	$t_where .= " AND project_id = $t_filter_project_value ";
}
if( $t_filter_config_value != META_FILTER_NONE ) {
	$t_where .= " AND config_id = '$t_filter_config_value' ";
}
if( $t_where != '' ) {
	$t_where = " WHERE 1=1 " . $t_where;
}

$query = "SELECT config_id, user_id, project_id, type, value, access_reqd
	FROM $t_config_table
	$t_where
	ORDER BY user_id, project_id, config_id ";
$result = db_query_bound( $query );
?>

<!-- FILTER FORM -->
<div id="config-filter-div" class="table-container">

	<form name="filter_form" method="post">
	<?php # CSRF protection not required here - form does not result in modifications ?>
		<input type="hidden" name="save" value="1" />

	<table cellspacing="1">
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
</div>

<div>
<div id="adm-config-div" class="table-container" style="display: table">
	<h2><?php echo lang_get( 'database_configuration' ) ?></h2>
<!--	<table cellspacing="1" cellpadding="5" border="1"> -->
	<table cellspacing="1" width="100%">
		<tr class="row-category">
			<th><?php echo lang_get( 'username' ) ?></th>
			<th><?php echo lang_get( 'project_name' ) ?></th>
			<th><?php echo lang_get( 'configuration_option' ) ?></th>
			<th><?php echo lang_get( 'configuration_option_type' ) ?></th>
			<th><?php echo lang_get( 'configuration_option_value' ) ?></th>
			<th><?php echo lang_get( 'access_level' ) ?></th>
			<?php if ( $t_read_write_access ): ?>
			<th><?php echo lang_get( 'actions' ) ?></th>
			<?php endif; ?>
		</tr><?php
		while ( $row = db_fetch_array( $result ) ) {
			extract( $row, EXTR_PREFIX_ALL, 'v' ); ?>
		<tr <?php echo helper_alternate_class() ?> width="100%">
			<td >
				<?php echo ($v_user_id == 0) ? lang_get( 'all_users' ) : string_display_line( user_get_name( $v_user_id ) ) ?>
			</td>
			<td ><?php echo string_display_line( project_get_name( $v_project_id, false ) ) ?></td>
			<td><?php echo string_display_line( $v_config_id ) ?></td>
			<td ><?php echo string_display_line( get_config_type( $v_type ) ) ?></td>
			<td style="overflow-x:auto;"><?php print_config_value_as_string( $v_type, $v_value ) ?></td>
			<td ><?php echo get_enum_element( 'access_levels', $v_access_reqd ) ?></td>
			<?php if ( $t_read_write_access ): ?>
			<td >
				<?php
					if( config_can_delete( $v_config_id ) ) {
						print_button( "adm_config_delete.php?user_id=$v_user_id&project_id=$v_project_id&config_option=$v_config_id", lang_get( 'delete_link' ) );
					} else {
						echo '&#160;';
					}
				?>
			</td>
			<?php endif; ?>
		</tr><?php
		} # end for loop ?>
	</table>
</div>
</div>
<?php
# Only display the edit form if user is authorized to change configuration
if ( $t_read_write_access ) { ?>
<div id="config-edit-div" class="form-container">
	<form method="post" action="adm_config_set.php">
		<fieldset>
			<legend><span><?php echo lang_get( 'set_configuration_option' ) ?></span></legend>
			<?php echo form_security_field( 'adm_config_set' ) ?>

			<!-- Username -->
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-user-id"><span><?php echo lang_get( 'username' ) ?></span></label>
				<span class="select">
					<select id="config-user-id" name="user_id">
						<option value="0" selected="selected"><?php echo lang_get( 'all_users' ); ?></option>
						<?php print_user_option_list( 0 ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>

			<!-- Project -->
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-project-id"><span><?php echo lang_get( 'project_name' ) ?></span></label>
				<span class="select">
					<select id="config-project-id" name="project_id">
						<option value="0" selected="selected"><?php echo lang_get( 'all_projects' ); ?></option>
						<?php print_project_option_list( ALL_PROJECTS, false ) ?>
					</select>
				</span>
				<span class="label-style"></span>
			</div>

			<!-- Config option name -->
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-option"><span><?php echo lang_get( 'configuration_option' ) ?></span></label>
				<span class="input"><input type="text" id="config-option" name="config_option" value="" size="64" maxlength="64" /></span>
				<span class="label-style"></span>
			</div>

			<!-- Option type -->
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-type"><span><?php echo lang_get( 'configuration_option_type' ) ?></span></label>
				<span class="select">
					<select id="config-type" name="type">
						<option value="default" selected="selected">default</option>
						<option value="string">string</option>
						<option value="integer">integer</option>
						<option value="complex">complex</option>
					</select>
				</span>
				<span class="label-style"></span>
			</div>

			<!-- Option Value -->
			<div class="field-container <?php echo helper_alternate_class_no_attribute(); ?>">
				<label for="config-value"><span><?php echo lang_get( 'configuration_option_value' ) ?></span></label>
				<span class="textarea">
					<textarea id="config-value" name="value" cols="80" rows="10"></textarea>
				</span>
				<span class="label-style"></span>
			</div>

			<!-- Submit button -->
			<span class="submit-button"><input type="submit" name="config_set" class="button" value="<?php echo lang_get( 'set_configuration_option' ) ?>" /></span>
		</fieldset>
	</form>
</div><?php
} # end user can change config

html_page_bottom();
