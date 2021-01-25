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
 * Mantis Administration Section
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );
require_once( 'schema.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

layout_page_header( 'MantisBT Administration' );

layout_admin_page_begin();

print_admin_menu_bar( 'index.php' );

/**
 * Print Check result - information only
 *
 * @param string $p_description Description.
 * @param string $p_value       Information.
 * @return void
 */
function print_info_row( $p_description, $p_value ) {
	echo '<tr>';
	echo '<th class="category">' . $p_description . '</th>';
	echo '<td>' . $p_value . '</td>';
	echo '</tr>';
}
?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<?php print_icon( 'fa-download', 'ace-icon' ); ?>
		<?php echo lang_get( 'install_information' ) ?>
	</h4>
</div>

<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
	<table class="table table-bordered table-condensed">
<?php
	if( ON == config_get_global( 'show_version' ) ) {
		$t_version_suffix = config_get_global( 'version_suffix' );
	} else {
		$t_version_suffix = '';
	}
	print_info_row( lang_get( 'mantis_version' ), MANTIS_VERSION . $t_version_suffix );
	print_info_row( lang_get( 'php_version' ), phpversion() );
?>
		<tr>
			<td class="bold" width="30%" colspan="2">
				<?php echo lang_get( 'database_information' ) ?>
			</td>
		</tr>
<?php
	print_info_row( lang_get( 'schema_version' ), config_get( 'database_version', 0,  ALL_USERS, ALL_PROJECTS ) );
	print_info_row( lang_get( 'adodb_version' ), $g_db->Version() );
?>
		<tr>
			<td class="bold" width="30%" colspan="2">
				<?php echo lang_get( 'path_information' ) ?>
			</td>
		</tr>
<?php
	print_info_row( lang_get( 'site_path' ), config_get_global( 'absolute_path' ) );
	print_info_row( lang_get( 'core_path' ), config_get_global( 'core_path' ) );
	print_info_row( lang_get( 'plugin_path' ), config_get_global( 'plugin_path' ) );
?>
	</table>
</div>
</div>
</div>
</div>
</div>

<?php
layout_admin_page_end();
