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
 * Mantis Configuration. View, edit, update a configuration option.
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api
 * @uses config_api
 * @uses constant_inc.php
 * @uses error_api
 * @uses form_api
 * @uses gpc_api
 * @uses helper_api
 * @uses lang_api
 * @uses layout_api
 * @uses print_api
 * @uses string_api
 * @uses user_api
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'error_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'lang_api.php' );
require_api( 'layout_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );

access_ensure_global_level( config_get( 'view_configuration_threshold' ) );
$t_has_write_access = access_has_global_level( config_get( 'set_configuration_threshold' ) );

layout_page_header( lang_get( 'configuration_report' ) );
layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( PAGE_CONFIG_DEFAULT );
print_manage_config_menu( 'adm_config_report.php' );

# Get request values
$f_edit_user_id         = gpc_get_int( 'user_id', ALL_USERS );
$f_edit_project_id      = gpc_get_int( 'project_id', ALL_PROJECTS );
$f_edit_option          = gpc_get_string( 'config_option', null );
$f_edit_action          = gpc_get_string( 'action', MANAGE_CONFIG_ACTION_VIEW );

# Ensure we exclusively use one of the defined, valid actions (XSS protection)
$t_valid_actions = array(
	MANAGE_CONFIG_ACTION_CREATE,
	MANAGE_CONFIG_ACTION_CLONE,
	MANAGE_CONFIG_ACTION_EDIT,
	MANAGE_CONFIG_ACTION_VIEW
);
$t_edit_action = in_array( $f_edit_action, $t_valid_actions )
	? $f_edit_action
	: MANAGE_CONFIG_ACTION_CREATE;

# if not creating a new option, the option name is required
if( MANAGE_CONFIG_ACTION_CREATE != $t_edit_action && null == $f_edit_option ) {
	error_parameters( 'config_option' );
	trigger_error( ERROR_EMPTY_FIELD, ERROR );
}

# see if the user can modify configuration options
$t_modify = MANAGE_CONFIG_ACTION_VIEW != $t_edit_action
		&& $t_has_write_access
		&& config_can_delete( $f_edit_option );

# if can't modify, switch antion to "view"
if( !$t_modify ) {
	$t_edit_action = MANAGE_CONFIG_ACTION_VIEW;
}

$t_action_label = lang_get( 'set_configuration_option_action_' . $t_edit_action );

if( MANAGE_CONFIG_ACTION_CREATE != $t_edit_action ) {
	# retrieve existing config data from database for this option
	$t_query = new DbQuery( 'SELECT * FROM {config} WHERE config_id = :config AND user_id = :user AND project_id = :project' );
	$t_query->bind_values(  array(
			'config' => $f_edit_option,
			'user' => $f_edit_user_id,
			'project' => $f_edit_project_id
		) );
	$t_config_row = $t_query->fetch();

	if( !$t_config_row ) {
		# this error will be triggered if the exact config combination does not exist in database
		error_parameters( $f_edit_option );
		trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, ERROR );
	}
	$t_option_user_id = (int)$t_config_row['user_id'];
	$t_option_project_id = (int)$t_config_row['project_id'];
	$t_option_id = $t_config_row['config_id'];
	$t_option_type = $t_config_row['type'];
	$t_option_value = $t_config_row['value'];
} else {
	# action is MANAGE_CONFIG_ACTION_CREATE
	# prepare new or default values
	$t_option_user_id = $f_edit_user_id;
	$t_option_project_id = $f_edit_project_id;
	$t_option_id = $f_edit_option;
	$t_option_type = CONFIG_TYPE_DEFAULT;
	$t_option_value = '';

	if( null != $t_option_id ) {
		# if an option has been provided,
		# make sure that configuration option specified is a valid one.
		$t_not_found_value = '***CONFIG OPTION NOT FOUND***';
		if( config_get( $t_option_id, $t_not_found_value ) === $t_not_found_value ) {
			error_parameters( $t_option_id );
			trigger_error( ERROR_CONFIG_OPT_NOT_FOUND, ERROR );
		}
	}
}

?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div id="config-edit-div">
		<form id="config_set_form" method="post" action="<?php echo ( $t_modify? 'adm_config_set.php' : '' ) ?>">

			<!-- Title -->
			<div class="widget-box widget-color-blue2">
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<i class="ace-icon fa fa-sliders"></i>
						<?php echo $t_action_label; ?>
					</h4>
				</div>

				<div class="widget-body">
					<div class="widget-main no-padding">
						<div id="config-edit-div" class="form-container">
							<div class="table-responsive">

		<table class="table table-bordered table-condensed table-striped">
			<fieldset>
				<?php
					if( $t_modify ) {
						echo form_security_field( 'adm_config_set' );
					}
				?>

				<!-- Username -->
				<tr>
					<td class="category">
						<?php echo lang_get( 'username' ) ?>
					</td>
					<td>
						<?php
						if( $t_modify ) {
						?>
						<select id="config-user-id" name="user_id" class="input-sm">
							<option value="<?php echo ALL_USERS; ?>"
								<?php check_selected( $t_option_user_id, ALL_USERS ) ?>>
								<?php echo lang_get( 'all_users' ); ?>
							</option>
							<?php print_user_option_list( $t_option_user_id ) ?>
						</select>
						<input type="hidden" name="original_user_id" value="<?php echo $t_option_user_id; ?>" />
						<?php
						} else {
							$t_username = ALL_USERS == $t_option_user_id ? lang_get( 'all_users' ) : user_get_name( $t_option_user_id );
							echo string_display_line( $t_username );
						}
						?>
					</td>
				</tr>

				<!-- Project -->
				<tr>
					<td class="category">
						<?php echo lang_get( 'project_name' ) ?>
					</td>
					<td>
						<?php
						if( $t_modify ) {
						?>
						<select id="config-project-id" name="project_id" class="input-sm">
							<option value="<?php echo ALL_PROJECTS; ?>"
								<?php check_selected( $t_option_project_id, ALL_PROJECTS ); ?>>
								<?php echo lang_get( 'all_projects' ); ?>
							</option>
							<?php print_project_option_list( $t_option_project_id, false ) ?>
						</select>
						<input type="hidden" name="original_project_id" value="<?php echo $t_option_project_id; ?>" />
						<?php
						} else {
							echo string_display_line( project_get_name( $t_option_project_id ) );
						}
						?>
					</td>
				</tr>

				<!-- Config option name -->
				<tr>
					<td class="category">
						<?php echo lang_get( 'configuration_option' ) ?>
					</td>
					<td>
						<?php
						if( $t_modify ) {
						?>
						<input type="text" name="config_option" class="input-sm"
							   value="<?php echo string_display_line( $t_option_id ); ?>"
							   size="64" maxlength="64" />
						<input type="hidden" name="original_config_option" value="<?php echo string_display_line( $t_option_id ); ?>" />
						<?php
						} else {
							echo string_display_line( $t_option_id );
						}
						?>
					</td>
				</tr>

				<!-- Option type -->
				<tr>
					<td class="category">
						<?php echo lang_get( 'configuration_option_type' ) ?>
					</td>
					<td>
						<?php
						if( $t_modify ) {
						?>
						<select id="config-type" name="type" class="input-sm">
							<?php print_option_list_from_array( config_get_types(), $t_option_type ); ?>
						</select>
						<?php
						} else {
							echo string_display_line( config_get_type_string( $t_option_type ) );
						}
						?>
					</td>
				</tr>

				<!-- Option Value -->
				<tr>
					<td class="category">
						<?php echo lang_get( 'configuration_option_value' ) ?>
					</td>
					<td>
						<?php
						if( $t_modify ) {
						?>
						<textarea class="form-control" name="value" cols="80" rows="10"><?php
							echo config_get_value_as_string( $t_option_type, $t_option_value, false );
							?></textarea>
						<?php
						} else {
							echo config_get_value_as_string( $t_option_type, $t_option_value, true );
						}
						?>
					</td>
				</tr>
			</fieldset>
		</table>
							</div>

						</div>
						<div class="widget-toolbox padding-4 clearfix">
						<?php
						if( $t_modify ) {
						?>
							<input type="hidden" name="action" value="<?php echo $t_edit_action; ?>" />
							<input type="submit" name="config_set" class="btn btn-primary btn-white btn-round"
								value="<?php echo $t_action_label; ?>"/>
						<?php
						}
						?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php
layout_page_end();
