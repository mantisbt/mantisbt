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
?>

	<div class="page-header">
		<h1><?php echo lang_get( 'site_information' ) ?></h1>
	</div>

<?php
	print_manage_menu();
?>

<div class="span9">
<ul>
	<li><?php echo lang_get( 'mantis_version' ) .": ";?><?php echo MANTIS_VERSION, ( $t_version_suffix ? " $t_version_suffix" : '' ) ?></li>
	<li><?php echo lang_get( 'schema_version' ) .": "; ?><?php echo config_get( 'database_version' ) ?></li>

<?php
	$t_is_admin = current_user_is_administrator(); 
	if ( $t_is_admin ) {
?>
	<li><?php echo lang_get( 'site_path' ) .": ";?><?php echo config_get( 'absolute_path' ) ?></li>
	<li><?php echo lang_get( 'core_path' ) .": ";?><?php echo config_get( 'core_path' ) ?></li>
	<li><?php echo lang_get( 'plugin_path' ) .": ";?><?php echo config_get( 'plugin_path' ) ?></li>

<?php
}
event_signal( 'EVENT_MANAGE_OVERVIEW_INFO', array( $t_is_admin ) ) 
?>
</ul>
</div>
</div>
<?php
html_page_bottom();