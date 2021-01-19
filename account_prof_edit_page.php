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
 * This page allows the user to edit his/her profile
 * Changes get POSTed to account_prof_update.php
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
 * @uses gpc_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses profile_api.php
 * @uses string_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'current_user_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'profile_api.php' );
require_api( 'string_api.php' );

if( !config_get( 'enable_profiles' ) ) {
	trigger_error( ERROR_ACCESS_DENIED, ERROR );
}

auth_ensure_user_authenticated();

current_user_ensure_unprotected();

$f_profile_id	= gpc_get_int( 'profile_id' );
$f_redirect_page = gpc_get_string( 'redirect', 'account_prof_menu_page.php' );

/** @noinspection PhpUnhandledExceptionInspection */
$t_global_profile = profile_is_global( $f_profile_id );
if( $t_global_profile ) {
	access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
}
$t_row = profile_get_row( $f_profile_id );

/**
 * @var $v_id
 * @var $v_user_id
 * @var $v_platform
 * @var $v_os
 * @var $v_os_build
 * @var $v_description
 */
extract( $t_row, EXTR_PREFIX_ALL, 'v' );

layout_page_header();

if( $t_global_profile ) {
	layout_page_begin( 'manage_overview_page.php' );
	print_manage_menu( 'manage_prof_menu_page.php' );
} else {
	layout_page_begin();
}

# Edit Profile Form BEGIN
?>

<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>
	<form method="post" action="account_prof_update.php">
		<?php  echo form_security_field( 'account_prof_update' )?>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="profile_id" value="<?php echo $v_id ?>" />
		<input type="hidden" name="redirect" value="<?php echo string_attribute( $f_redirect_page ) ?>" />

		<div class="widget-box widget-color-blue2">

			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-user', 'ace-icon' ); ?>
					<?php echo lang_get( 'edit_profile' ) ?>
				</h4>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-condensed">
							<tr>
								<th class="category">
									<label for="platform">
										<span class="required">*</span>
										<?php echo lang_get( 'platform' ) ?>
									</label>
								</th>
								<td>
									<input id="platform" name="platform" type="text" required
										   class="input-sm" size="32" maxlength="32"
										   value="<?php echo string_attribute( $v_platform ) ?>"
									/>
								</td>
							</tr>
							<tr>
								<th class="category">
									<label for="os">
										<span class="required">*</span>
										<?php echo lang_get( 'os' ) ?>
									</label>
								</th>
								<td>
									<input id="os" name="os" type="text" required
										   class="input-sm" size="32" maxlength="32"
										   value="<?php echo string_attribute( $v_os ) ?>"
									/>
								</td>
							</tr>
							<tr>
								<th class="category">
									<label for="os_build">
										<span class="required">*</span>
										<?php echo lang_get( 'os_build' ) ?>
									</label>
								</th>
								<td>
									<input id="os_build" name="os_build" type="text" required
										   class="input-sm" size="16" maxlength="16"
										   value="<?php echo string_attribute( $v_os_build ) ?>"
									/>
								</td>
							</tr>
							<tr>
								<th class="category">
									<label for="description">
										<?php echo lang_get( 'profile_description' ) ?>
									</label>
								</th>
								<td>
									<?php # Newline after opening textarea tag is intentional, see #25839 ?>
									<textarea id="description" name="description"
											  class="form-control"
											  cols="60" rows="8">
<?php echo string_textarea( $v_description ) ?>
</textarea>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="widget-toolbox padding-8 clearfix">
					<span class="required pull-right">
						*
						<?php echo lang_get( 'required' ) ?>
					</span>
					<button class="btn btn-primary btn-white btn-round">
						<?php echo lang_get( 'update_profile' ) ?>
					</button>
				</div>
			</div>
		</div>
	</form>
</div>

<?php
layout_page_end();
