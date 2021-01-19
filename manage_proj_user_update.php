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
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses layout_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses user_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'layout_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'user_api.php' );

form_security_validate( 'manage_proj_user_update' );
auth_reauthenticate();

$f_project_id = gpc_get_int( 'project_id' );
$f_confirmed = gpc_get_bool( '_confirmed' );

project_ensure_exists( $f_project_id );
access_ensure_project_level( config_get( 'manage_project_threshold' ), $f_project_id );
access_ensure_project_level( config_get( 'project_user_threshold' ), $f_project_id );

# If the form was managed by javascript, we recive relevant data in a json array
# so we can ignore the standard inputs
$f_form_json_updates = gpc_get_string( 'json_submit', null );
if( $f_form_json_updates ) {
	$t_form_updates = json_decode( $f_form_json_updates, true );
	$f_form_updated_acls = $t_form_updates['user_access_level'];
	$f_form_deleted_ids = $t_form_updates['user_access_delete'];
} else {
	$f_form_updated_acls = gpc_get_int_array( 'user_access_level', array() );
	$f_form_deleted_ids = gpc_get_int_array( 'user_access_delete', array() );
}

# Build and validate the set of changes

$t_users_to_update = array();
$t_users_to_delete = array();

$t_all_local_users = project_get_all_user_rows( $f_project_id, ANYBODY, false );

foreach( $f_form_deleted_ids as $t_id ) {
	if( !isset( $t_all_local_users[$t_id] ) ) {
		# a user id that is not explicitly assigned to the project
		continue;
	}
	$t_user = $t_all_local_users[$t_id];
	$t_can_manage_this_user = access_has_project_level( $t_user['access_level'], $f_project_id );
	if( !$t_can_manage_this_user ) {
		# this user can't be modified
		continue;
	}
	$t_users_to_delete[$t_id] = $t_id;
}

$t_current_user_access_level = access_get_project_level( $f_project_id );
$t_enum_values = MantisEnum::getValues( config_get( 'access_levels_enum_string' ) );

foreach( $f_form_updated_acls as $t_id => $t_value ) {
	if( isset( $t_users_to_delete[$t_id] ) ) {
		# this user is marked for deletion, ignore it
		continue;
	}
	if( !isset( $t_all_local_users[$t_id] ) ) {
		# a user id that is not explicitly assigned to the project
		continue;
	}
	$t_user = $t_all_local_users[$t_id];
	if( $t_value == $t_user['access_level'] ) {
		# the value is not changed, ignore it
		continue;
	}
	$t_can_manage_this_user = access_has_project_level( $t_user['access_level'], $f_project_id );
	if( !$t_can_manage_this_user ) {
		# this user can't be modified
		continue;
	}

	if( $t_value > $t_current_user_access_level ) {
		# can't assign a higer level that the one current use has
		continue;
	}
	if( !in_array( $t_value, $t_enum_values ) ) {
		# the submitted access level is not valid
		continue;
	}

	$t_users_to_update[$t_id] = $t_value;
}

if( !$f_confirmed ) {
	# Display a confirmation page with a summary of changes

	$t_affected_users = array_merge( array_keys( $t_users_to_update ), array_keys( $t_users_to_delete ) );

	layout_page_header();
	layout_page_begin();
	?>
	<div class="col-md-12 col-xs-12">
		<div class="space-10"></div>

		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-bar-chart', 'ace-icon' ); ?>
					<?php echo lang_get( 'review_changes' ) ?>
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
	<?php
	if( empty( $t_affected_users ) ) {
		$t_message = lang_get( 'review_changes_empty' );
		?>
					<div class="alert alert-warning center">
						<p class="bigger-110"><?php echo $t_message ?></p>
						<div class="space-10"></div>
					</div>
		<?php
	} else {
		$t_message = lang_get( 'review_changes_confirmation' );
		$t_button_label = lang_get( 'yes' );
		?>
					<div class="alert alert-warning center">
						<p class="bigger-110"><?php echo $t_message ?></p>
						<div class="space-10"></div>

						<form method="post" class="center" action="">
							<input type="hidden" name="_confirmed" value="1" />
							<?php
							print_hidden_inputs( $_POST );
							print_hidden_inputs( $_GET );
							?>
							<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo $t_button_label ?>" />
						</form>

						<div class="space-10"></div>
					</div>
			<?php
	}
	?>
				</div>
			</div>
		</div>
	<?php

	user_cache_array_rows( $t_affected_users );

	# Table for users to be deleted
	if( !empty( $t_users_to_delete ) ) {
		# prepare usernames to sort the results
		$t_usernames_val = Array();
		foreach( $t_users_to_delete as $t_id ) {
			$t_usernames_val[] = user_get_name( $t_id );
		}
		$t_users_to_delete = array_values( $t_users_to_delete );
		array_multisort( $t_usernames_val, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $t_users_to_delete );
	?>
		<div class="space-10"></div>

		<div class="widget-box widget-color-grey">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-user-times', 'ace-icon' ); ?>
					<?php echo lang_get( 'remove_project_user_title' ) ?>
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive sortable">
						<table class="table table-striped table-bordered table-condensed">
							<thead>
								<tr>
									<th><?php echo lang_get( 'username' ) ?></th>
									<th><?php echo lang_get( 'old_value' ), ': ', lang_get( 'access_level' ) ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
								$fn_print_tr = function( $p_td1, $p_td2 ) {
									echo '<tr><td>' . $p_td1 . '</td><td>' . $p_td2 . '</td></tr>';
								};
								foreach( $t_users_to_delete as $t_id ) {
									$t_username = prepare_user_name( $t_id, false );
									$t_str_acl = get_enum_element( 'access_levels', $t_all_local_users[$t_id]['access_level'] );
									$fn_print_tr( $t_username, $t_str_acl );
								}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	# Table for users to be modified
	if( !empty( $t_users_to_update ) ) {
		# prepare usernames to sort the results
		$t_usernames_val = Array();
		foreach( $t_users_to_update as $t_id => $t_val ) {
			$t_usernames_val[] = user_get_name( $t_id );
		}
		$t_update_key = array_keys( $t_users_to_update );
		$t_update_val = array_values( $t_users_to_update );
		array_multisort( $t_usernames_val, SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $t_update_key, $t_update_val );
		$t_users_to_update = array_combine( $t_update_key, $t_update_val );
	?>
		<div class="space-10"></div>

		<div class="widget-box widget-color-grey">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-user', 'ace-icon' ); ?>
					<?php echo lang_get( 'modify_project_user_title' ) ?>
				</h4>
			</div>
			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive sortable">
						<table class="table table-striped table-bordered table-condensed">
							<thead>
								<tr>
									<th><?php echo lang_get( 'username' ) ?></th>
									<th><?php echo lang_get( 'old_value' ), ': ', lang_get( 'access_level' ) ?></th>
									<th><?php echo lang_get( 'new_value' ), ': ', lang_get( 'access_level' ) ?></th>
								</tr>
							</thead>
							<tbody>
							<?php
								$fn_print_tr = function( $p_td1, $p_td2, $p_td3 ) {
									echo '<tr><td>' . $p_td1 . '</td><td>' . $p_td2 . '</td><td>' . $p_td3 . '</td></tr>';
								};
								foreach( $t_users_to_update as $t_id => $t_new_acl ) {
									$t_username = prepare_user_name( $t_id, false );
									$t_old_acl = $t_all_local_users[$t_id]['access_level'];
									$t_str_new_acl = get_enum_element( 'access_levels', $t_new_acl );
									$t_str_old_acl = get_enum_element( 'access_levels', $t_old_acl );
									$fn_print_tr( $t_username, $t_str_old_acl, $t_str_new_acl );
								}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
	?>
	</div>
	<?php

	layout_page_end();

} else {
	# $f_confirmed is true
	# do the actual changes
	if( !empty( $t_users_to_update ) ) {
		project_add_users( $f_project_id, $t_users_to_update );
	}
	if( !empty( $t_users_to_delete ) ) {
		project_remove_users( $f_project_id, $t_users_to_delete );
	}

	form_security_purge( 'manage_proj_user_update' );
	$t_redirect_url = 'manage_proj_edit_page.php?project_id=' . $f_project_id;
	layout_page_header( null, $t_redirect_url );
	layout_page_begin( 'manage_overview_page.php' );
	html_operation_successful( $t_redirect_url );
	layout_page_end();

}