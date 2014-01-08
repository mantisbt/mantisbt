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
require_once( dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'core.php' );
require_once( 'schema.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

html_page_top( 'MantisBT Administration' );

function print_info_row( $p_description, $p_value ) {
	echo '<tr ' . helper_alternate_class() . '>';
	echo '<td class="category">' . $p_description . '</td>';
	echo '<td>' . $p_value . '</td>';
	echo '</tr>';
}

?>
<br />

<div align="center">
		<p>[ <a href="check.php">Check your installation</a> ]</p>
	<?php if ( count($upgrade) - 1 != config_get( 'database_version' ) ) { ?>
		<p>[ <a href="upgrade_warning.php"><b>Upgrade your installation</b></a> ]</p>
	<?php } ?>
		<p>[ <a href="system_utils.php">System Utilities</a> ]</p>
		<p>[ <a href="test_icons.php">Test Icons</a> ]</p>
		<p>[ <a href="test_langs.php">Test Langs</a> ]</p>
		<p>[ <a href="test_email.php">Test Email</a> ]</p>
		<p>[ <a href="email_queue.php">Email Queue</a> ]</p>
</div>

<table class="width75" align="center" cellspacing="1">
<tr>
<td class="form-title" width="30%" colspan="2"><?php echo lang_get( 'install_information' ) ?></td>
</tr>
<?php
   if( ON == config_get( 'show_version' ) ) {
		$t_version_suffix = config_get_global( 'version_suffix' );
	} else {
		$t_version_suffix = '';
	}
	print_info_row( lang_get( 'mantis_version' ), MANTIS_VERSION, $t_version_suffix );
	print_info_row( 'php_version', phpversion());
?>
<tr>
<td class="form-title" width="30%" colspan="2"><?php echo lang_get( 'database_information' ) ?></td>
</tr>
<?php
	print_info_row( lang_get( 'schema_version' ), config_get( 'database_version' ) );
	print_info_row( 'adodb_version', $g_db->Version() );
?>
<tr>
<td class="form-title" width="30%" colspan="2"><?php echo lang_get( 'path_information' ) ?></td>
</tr>
<?php
	print_info_row( lang_get( 'site_path' ), config_get( 'absolute_path' ) );
	print_info_row( lang_get( 'core_path' ), config_get( 'core_path' ) );
	print_info_row( lang_get( 'plugin_path' ), config_get( 'plugin_path' ) );
?>
</table>
<?php
	html_page_bottom();
