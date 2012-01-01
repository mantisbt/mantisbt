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
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	auth_reauthenticate();
	access_ensure_global_level( config_get( 'manage_site_threshold' ) );

	$t_version_suffix = config_get_global( 'version_suffix' );

	html_page_top( lang_get( 'manage_link' ) );

	print_manage_menu();
?>

<br />
<table class="width75" align="center" cellspacing="1">

<tr>
<td class="form-title" width="30%"><?php echo lang_get( 'site_information' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo lang_get( 'mantis_version' ) ?></td>
<td><?php echo MANTIS_VERSION, ( $t_version_suffix ? " $t_version_suffix" : '' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo lang_get( 'schema_version' ) ?></td>
<td><?php echo config_get( 'database_version' ) ?></td>
</tr>

<tr class="spacer">
<td></td>
</tr>

<?php
	$t_is_admin = current_user_is_administrator(); 
	if ( $t_is_admin ) {
?>
<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo lang_get( 'site_path' ) ?></td>
<td><?php echo config_get( 'absolute_path' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo lang_get( 'core_path' ) ?></td>
<td><?php echo config_get( 'core_path' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
<td class="category"><?php echo lang_get( 'plugin_path' ) ?></td>
<td><?php echo config_get( 'plugin_path' ) ?></td>
</tr>

<tr class="spacer">
<td></td>
</tr>
<?php
}

event_signal( 'EVENT_MANAGE_OVERVIEW_INFO', array( $t_is_admin ) ) 
?>

</table>

<?php
html_page_bottom();

