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
 * This page allows users to add a new profile which is POSTed to
 * account_prof_add.php
 *
 * Users can also manage their profiles
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses current_user_api.php
 * @uses form_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses profile_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'profile_api.php' );

if( !config_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

if( isset( $g_global_profiles ) ) {
	$g_global_profiles = true;
} else {
	$g_global_profiles = false;
}

auth_ensure_user_authenticated();
current_user_ensure_unprotected();

$t_manage_global_profile_threshold = config_get( 'manage_global_profile_threshold' );
$t_can_manage_global_profile = access_has_global_level( $t_manage_global_profile_threshold );
if( $g_global_profiles ) {
	access_ensure_global_level( $t_manage_global_profile_threshold );
} else {
	access_ensure_global_level( config_get( 'add_profile_threshold' ) );
}

layout_page_header( lang_get( 'manage_profiles_link' ) );

if( $g_global_profiles ) {
	layout_page_begin( 'manage_overview_page.php' );
	print_manage_menu( 'manage_prof_menu_page.php' );
} else {
	layout_page_begin();
}

if( $g_global_profiles ) {
	$t_user_id = ALL_USERS;
} else {
	$t_user_id = auth_get_current_user_id();
	print_account_menu( 'account_prof_menu_page.php' );
}
?>

<div class="col-md-12 col-xs-12">

<?php
	# Profiles list BEGIN
	$t_profiles = profile_get_all_for_user( $t_user_id );
	if( $t_profiles ) {
?>
<div class="space-10"></div>
<div id="categories" class="form-container">
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-file-o', 'ace-icon' ); ?>
				<?php echo lang_get( 'manage_profiles_link' ) ?>
			</h4>
		</div>
		<div class="widget-body">
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th><?php echo lang_get( 'platform' ) ?></th>
								<th><?php echo lang_get( 'os' ) ?></th>
								<th><?php echo lang_get( 'os_build' ) ?></th>
<?php
			if( !$g_global_profiles ) {
?>
								<th><?php echo lang_get( 'global_profile' ) ?></th>
								<th><?php echo lang_get( 'default_profile' ) ?></th>
<?php
			}
?>
								<th class="center"><?php echo lang_get( 'actions' ) ?></th>
							</tr>
						</thead>

						<tbody>
<?php
			$t_security_token = form_security_token( 'account_prof_update' );
			$t_default_profile = current_user_get_pref( 'default_profile' );

			foreach( $t_profiles as $t_profile ) {
				/**
				 * @var $v_id
				 * @var $v_user_id
				 * @var $v_platform
				 * @var $v_os
				 * @var $v_os_build
				 */
				extract( $t_profile, EXTR_PREFIX_ALL, 'v' );
				$t_is_global_profile = $v_user_id == ALL_USERS;
				$t_is_default_profile = $t_default_profile == $v_id
?>
							<tr>
								<td><?php echo string_display_line( $v_platform ); ?></td>
								<td><?php echo string_display_line( $v_os ); ?></td>
								<td><?php echo string_display_line( $v_os_build );  ?></td>
<?php
				if( !$g_global_profiles ) {
?>
								<td class="center">
									<?php if( $t_is_global_profile ) { ?>
									<?php print_icon( 'fa-check', 'ace-icon fa-lg' ); ?>
									<?php } ?>
								</td>
								<td class="center">
									<?php if( $t_is_default_profile ) { ?>
									<?php print_icon( 'fa-check', 'ace-icon fa-lg' ); ?>
									<?php } ?>
								</td>
<?php
				}
?>
								<td class="center">
									<div class="btn-group inline">
<?php
				# Common POST parameters for action buttons
				$t_param = array(
					'profile_id' => $v_id,
					'redirect' => basename( $_SERVER["SCRIPT_FILENAME"] ),
				);

				# Print the Edit and Delete buttons for local profiles, or
				# if user can manage global ones.
				if( !$t_is_global_profile || $t_can_manage_global_profile ) {
					echo '<div class="pull-left">';
					print_form_button(
						'account_prof_edit_page.php',
						lang_get( 'edit' ),
						$t_param
					);
					echo '</div>';

					echo '<div class="pull-left">';
					print_form_button(
						'account_prof_update.php',
						lang_get( 'delete' ),
						array_merge( $t_param, array( 'action' => 'delete' ) ),
						$t_security_token
					);
					echo '</div>';
				}

				# Make / Clear Default button
				if( !$g_global_profiles ) {
					echo '<div class="pull-left">';
					if( $t_is_default_profile ) {
						$t_param['profile_id'] = 0;
					}
					print_form_button(
						'account_prof_update.php',
						lang_get( $t_is_default_profile ? 'clear_default' : 'make_default' ),
						array_merge( $t_param, array( 'action' => 'make_default' ) ),
						$t_security_token
					);
					echo '</div>';
				}


				echo '<div class="pull-left">';
				echo '</div>';
?>
									</div>
								</td>
							</tr>
<?php
			} # end foreach profile
?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
	} # end if profiles
	# Profiles list END

	# Add Profile Form BEGIN
?>

<div class="space-10"></div>
<div id="account-profile-div" class="form-container">
	<form id="account-profile-form" method="post" action="account_prof_update.php">
		<fieldset>
			<?php  echo form_security_field( 'account_prof_update' )?>
			<input type="hidden" name="action" value="add" />
			<input type="hidden" name="user_id" value="<?php echo $t_user_id ?>" />

			<div class="widget-box widget-color-blue2">

				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<?php print_icon( 'fa-file-o', 'ace-icon' ); ?>
						<?php echo lang_get( 'add_profile' ) ?>
					</h4>
				</div>

				<div class="widget-body">
					<div class="widget-main no-padding">
						<div class="table-responsive">
							<table class="table table-bordered table-condensed table-striped">
								<tr>
									<td class="category">
										<span class="required">*</span>
										<label for="platform">
											<?php echo lang_get( 'platform' ) ?>
										</label>
									</td>
									<td>
										<input type="text" class="input-sm"
											   id="platform" name="platform"
											   size="32" maxlength="32" required />
									</td>
								</tr>
								<tr>
									<td class="category">
										<span class="required">*</span>
										<label for="os">
											<?php echo lang_get( 'os' ) ?>
										</label>
									</td>
									<td>
										<input type="text" class="input-sm"
											   id="os" name="os"
											   size="32" maxlength="32" required />
									</td>
								</tr>
								<tr>
									<td class="category">
										<span class="required">*</span>
										<label for="os_build">
											<?php echo lang_get( 'os_build' ) ?>
										</label>
									</td>
									<td>
										<input type="text" class="input-sm"
											   id="os_build" name="os_build"
											   size="16" maxlength="16" required />
									</td>
								</tr>
								<tr>
									<td class="category">
										<label for="description">
											<?php echo lang_get( 'profile_description' ) ?>
										</label>
									</td>
									<td>
									<textarea  id="description" name="description"
											   class="form-control"
											   cols="80" rows="8"></textarea>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>

				<div class="widget-toolbox padding-8 clearfix">
				<span class="required pull-right">
					* <?php echo lang_get( 'required' ); ?>
				</span>
					<button class="btn btn-primary btn-white btn-round">
						<?php echo lang_get('add_profile'); ?>
					</button>
				</div>
			</div>
		</fieldset>
	</form>
</div>

</div>
<?php
layout_page_end();
