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
 * @uses export_api.php
 * @uses form_api.php
 * @uses html_api.php
 * @uses layout_api.php
 */

use Mantis\Export\TableWriterFactory;

require_once( 'core.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'export_api.php' );
require_api( 'form_api.php' );
require_api( 'html_api.php' );
require_api( 'layout_api.php' );

auth_reauthenticate();

if( !export_can_manage_global_config() ) {
	access_denied();
}

layout_page_header( 'MANAGE_EXPORT_CONFIG' );
layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( PAGE_CONFIG_DEFAULT );
print_manage_config_menu( 'manage_config_export_page.php' );

$t_providers = TableWriterFactory::getAllProviders();
$t_export_config = config_get( 'export_plugins', array(), ALL_USERS, ALL_PROJECTS );

$t_default_provider_id = config_get( 'export_default_plugin', null, ALL_USERS, ALL_PROJECTS );

# classify the entries in: enabled, disabled, and missing
$t_config_rows = $t_export_config;
$t_list_enabled = array();
$t_list_disabled = array();
$t_list_missing = array();
foreach( $t_providers as $t_id => $t_provider ) {
	$t_row = array();
	$t_row['id'] = $t_provider->unique_id;
	$t_row['description'] = $t_provider->short_name . ' (.' . $t_provider->file_extension . ')';
	$t_row['provider_name'] = $t_provider->provider_name;
	$t_row['config_page'] = $t_provider->config_page_for_admin;
	if( isset( $t_config_rows[$t_id] ) ) {
		$t_config = $t_config_rows[$t_id];
		unset( $t_config_rows[$t_id] );
		$t_row['enabled'] = $t_config['enabled'];
	} else {
		# if does not exist in config, assume it's enabled by default
		$t_row['enabled'] = true;
	}
	if( $t_row['enabled'] ) {
		$t_list_enabled[] = $t_row;
	} else {
		$t_list_disabled[] = $t_row;
	}
}

# found in config, but not in plugins
if( !empty( $t_config_rows ) ) {
	foreach( $t_config_rows as $t_id => $t_config ) {
		$t_row = array();
		$t_row['id'] = $t_id;
		$t_row['description'] = 'PLUGIN_IS_NOT_INSTALLED';
		$t_row['provider_name'] = '';
		$t_row['config_page'] = '';
		$t_row['enabled'] = false;
		$t_list_missing[] = $t_row;
	}
}

/**
 * Helper unction to print a row
 * @param array $p_row	The row item  data
 * @param string $p_type	Which section is this included in
 */
function print_export_row( $p_row, $p_type ) {
	echo'<tr>';
	echo '<td>', $p_row['id'], '</td>';
	echo '<td>', $p_row['description'], '</td>';
	echo '<td>', $p_row['provider_name'], '</td>';
	if( $p_row['config_page'] ) {
		$t_config_link = '<a href="' . $p_row['config_page'] . '">' . 'CONFIG' . '</a>';
	} else {
		$t_config_link = '';
	}
	echo '<td>', $t_config_link, '</td>';
	echo '<td>';
	$t_submit_page = 'manage_config_export_set.php';
	$t_params = array( 'provider_id' => $p_row['id'] );
	switch( $p_type ) {
		case 'ENABLED':
			$t_params['action'] = 'DISABLE';
			print_form_button( $t_submit_page, 'DISABLE', $t_params);
			break;
		case 'DISABLED':
			$t_params['action'] = 'ENABLE';
			print_form_button( $t_submit_page, 'ENABLE', $t_params);
			break;
		case 'MISSING':
			$t_params['action'] = 'REMOVE';
			print_form_button( $t_submit_page, 'REMOVE', $t_params);
			break;
	}
	echo '</td>';
	echo'</tr>';
}

/**
 * Helper function to print a table for each section
 * @param array $p_list		A list of rows
 * @param string $p_label	The label for section hading
 * @param string $p_type	The section being rendered. 'ENABLED', 'DISABLED', or 'MISSING'
 * @return type
 */
function print_export_section( $p_list, $p_label, $p_type ) {
	if( empty( $p_list ) ) {
		return;
	}
	echo '<div><h4>', $p_label, '</h4></div>';
	echo '<div class="table-responsive sortable listjs-table">';
	echo '<table class="table table-bordered table-condensed table-striped">';
	echo '<colgroup>';
	echo '<col class="col-md-3">';
	echo '<col class="col-md-4">';
	echo '<col class="col-md-2">';
	echo '<col class="col-md-2">';
	echo '<col class="col-md-2">';
	echo '</colgroup>';
	echo '<thead>';
	echo '<tr>';
	echo '<th>', 'METHOD', '</th>';
	echo '<th>', 'DESCRIPTION', '</th>';
	echo '<th>', 'PROVIDER', '</th>';
	echo '<th class="no-sort">', 'CONFIG', '</th>';
	echo '<th class="no-sort">', 'ACTION', '</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';
	foreach( $p_list as $t_item ) {
		print_export_row( $t_item, $p_type );
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
}

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
			<form method="post" action="manage_config_export_set.php">
				<?php echo form_security_field( 'manage_config_export_set' ) ?>
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
				<?php echo 'EXPORT_METHODS_CONFIG' ?>
			</h4>
		</div>
		<div id="manage-export-div" class="form-container">
			<div class="widget-body">
				<div class="widget-main">
					<?php
					print_export_section( $t_list_enabled, 'label_ENABLED', 'ENABLED' );
					print_export_section( $t_list_disabled, 'label_DISABLED', 'DISABLED' );
					print_export_section( $t_list_missing, 'label_MISSING', 'MISSING' );
					?>
				</div>
			</div>
		</div>
	</div>
</div>


<?php
layout_page_end();
