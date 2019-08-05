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
 * @copyright Copyright 2019  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses current_user_api.php
 * @uses export_api.php
 * @uses form_api.php
 * @uses html_api.php
 * @uses layout_api.php
 */

use Mantis\Export\TableWriterFactory;

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'current_user_api.php' );
require_api( 'export_api.php' );
require_api( 'form_api.php' );
require_api( 'html_api.php' );
require_api( 'layout_api.php' );

auth_ensure_user_authenticated();
auth_reauthenticate();

#@TODO, protected user can view, but not change these configs
current_user_ensure_unprotected();

$t_user_id = auth_get_current_user_id();
$t_providers = TableWriterFactory::getProviders();
$t_default_provider_id = config_get( 'export_default_plugin', null, $t_user_id, ALL_PROJECTS );
$t_list = array();
foreach( $t_providers as $t_id => $t_provider ) {
	$t_row = array();
	$t_row['id'] = $t_provider->unique_id;
	$t_row['description'] = $t_provider->short_name . ' (.' . $t_provider->file_extension . ')';
	$t_row['provider_name'] = $t_provider->provider_name;
	$t_row['config_page'] = $t_provider->config_page_for_user;
	$t_list[] = $t_row;
}

/**
 * Helper function to print table rows for each item
 * @param array $p_row
 */
function print_export_row( array $p_row ) {
	echo'<tr>';
	echo '<td>', $p_row['description'], '</td>';
	echo '<td>', $p_row['provider_name'], '</td>';
	if( $p_row['config_page'] ) {
		$t_config_link = '<a href="' . $p_row['config_page'] . '">' . 'CONFIG' . '</a>';
	} else {
		$t_config_link = '';
	}
	echo '<td>', $t_config_link, '</td>';
	echo'</tr>';
}

layout_page_header( 'ACCOUNT_EXPORT' );
layout_page_begin();
print_account_menu( 'account_export_page.php' );

?>
<div class="col-md-12 col-xs-12">
	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-columns "></i>
				<?php echo 'DEFAULT_METHOD_CONFIG' ?>
			</h4>
		</div>
		<div id="default-export-div" class="form-container">
			<form method="post" action="account_export_update.php">
				<?php echo form_security_field( 'account_export_update' ) ?>
				<div class="widget-body">
					<div class="widget-main no-padding">
						<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
	<tbody>
		<tr>
			<td class="category">
				<?php echo 'DEFAULT METHOD' ?>
			</td>
			<td>
				<select id="input_default_provider" name="provider_id" class="input-sm" required>
					<option selected disabled value=""><?php echo '[', 'SELECT', ']' ?></option>
					<?php export_print_format_option_list( $t_default_provider_id ) ?>
				</select>
				<input type="hidden" name="action" value="DEFAULT" />
			</td>
		</tr>
	</tbody>
</table>
						</div>
					</div>
					<div class="widget-toolbox padding-8 clearfix">
						<input type="submit" class="btn btn-primary btn-sm btn-white btn-round" name="update_default_method" value="<?php echo 'UPDATE' ?>">
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="space-10"></div>

	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-columns "></i>
				<?php echo 'AVAILABLE_EXPORT_METHODS' ?>
			</h4>
		</div>
		<div class="widget-body">
			<div class="widget-main no-padding">
				<div class="table-responsive sortable listjs-table">
					<table class="table table-bordered table-hover table-condensed table-striped">
						<thead>
							<tr>
								<th><?php echo 'DESCRIPTION' ?></th>
								<th><?php echo 'PROVIDER' ?></th>
								<th><?php echo 'CONFIG' ?></th>
							<tr>
						</thead>
						<tbody>
							<?php
							foreach( $t_list as $t_row ) {
								print_export_row( $t_row );
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<?php

layout_page_end();