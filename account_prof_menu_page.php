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

if( $g_global_profiles ) {
	access_ensure_global_level( config_get( 'manage_global_profile_threshold' ) );
} else {
	access_ensure_global_level( config_get( 'add_profile_threshold' ) );
}

layout_page_header( lang_get( 'manage_profiles_link' ) );

layout_page_begin( 'manage_overview_page.php' );

if( $g_global_profiles ) {
	print_manage_menu('manage_prof_menu_page.php');
}

if( $g_global_profiles ) {
	$t_user_id = ALL_USERS;
} else {
	$t_user_id = auth_get_current_user_id();
    print_account_menu( 'account_prof_menu_page.php' );
}

# Add Profile Form BEGIN
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div id="account-profile-div" class="form-container">
	<form id="account-profile-form" method="post" action="account_prof_update.php">
		<fieldset>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-file-o"></i>
		<?php echo lang_get( 'add_profile_title' ) ?>
	</h4>
</div>

<div class="widget-body">
	<div class="widget-main no-padding">
		<div class="table-responsive">
			<table class="table table-bordered table-condensed table-striped">
				<fieldset>
			<?php  echo form_security_field( 'profile_update' )?>
			<input type="hidden" name="action" value="add" />
			<input type="hidden" name="user_id" value="<?php echo $t_user_id ?>" />
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'platform' ) ?>
				</td>
				<td>
					<input id="platform" type="text" name="platform" class="input-sm" size="32" maxlength="32" />
				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'os' ) ?>
				</td>
				<td>
					<input id="os" type="text" name="os" class="input-sm" size="32" maxlength="32" />
				</td>
			</tr>
			<tr>
				<td class="category">
					<span class="required">*</span> <?php echo lang_get( 'os_version' ) ?>
				</td>
				<td>
					<input id="os-version" type="text" name="os_build" class="input-sm" size="16" maxlength="16" />
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'additional_description' ) ?>
				</td>
				<td>
					<textarea class="form-control" id="description" name="description" cols="80" rows="8"></textarea>
				</td>
			</tr>
		</fieldset>
	</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
				<span class="required pull-right"> * <?php echo lang_get( 'required' ); ?></span>
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get('add_profile_button') ?>"/>
</div>
</div>
</div>
	</form>
</div>
<?php
	# Add Profile Form END
	# Edit or Delete Profile Form BEGIN

	$t_profiles = profile_get_all_for_user( $t_user_id );
	if( $t_profiles ) {
?>

<div class="space-10"></div>

<div id="account-profile-update-div" class="form-container">
	<form id="account-profile-update-form" method="post" action="account_prof_update.php">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<i class="ace-icon fa fa-file-o"></i>
					<?php echo lang_get( 'edit_or_delete_profiles_title' ) ?>
				</h4>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					<div class="table-responsive">
						<table class="table table-bordered table-condensed table-striped">
		<fieldset>
			<?php  echo form_security_field( 'profile_update' )?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'edit_profile' ) ?>
				</td>
				<td>
					<label>
						<input id="action-edit" type="radio" class="ace" name="action" value="edit" />
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
<?php
	if( !$g_global_profiles ) {
?>
				<tr>
					<td class="category">
						<?php echo lang_get( 'make_default' ) ?>
					</td>
					<td>
						<label>
							<input id="action-default" type="radio" class="ace" name="action" value="make_default" />
							<span class="lbl"></span>
						</label>
					</td>
				</tr>
<?php
	}
?>
			<tr>
				<td class="category">
					<?php echo lang_get( 'delete_profile' ) ?>
				</td>
				<td>
					<label>
						<input id="action-delete" type="radio" class="ace" name="action" value="delete" />
						<span class="lbl"></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="category">
					<?php echo lang_get( 'select_profile' ) ?>
				</td>
				<td>
					<select id="select-profile" name="profile_id" class="input-sm">
						<?php print_profile_option_list( $t_user_id, '', $t_profiles ) ?>
					</select>
				</td>
			</tr>
		</fieldset>
		</table>
		</div>
		</div>
			<div class="widget-toolbox padding-8 clearfix">
				<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'submit_button' ) ?>" />
			</div>
		</div>
	</div>
	</form>
</div>
<?php
} # Edit or Delete Profile Form END
echo '</div>';
layout_page_end();
