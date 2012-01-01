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
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( lang_get( 'manage_plugin_link' ) );

print_manage_menu( 'manage_plugin_page.php' );

function plugin_sort( $p1, $p2 ) {
	return strcasecmp( $p1->name, $p2->name );
}

$t_plugins = plugin_find_all();
uasort( $t_plugins, 'plugin_sort' );

global $g_plugin_cache;

$t_plugins_installed = array();
$t_plugins_available = array();

foreach( $t_plugins as $t_basename => $t_plugin ) {
	if ( isset( $g_plugin_cache[$t_basename] ) ) {
		$t_plugins_installed[$t_basename] = $t_plugin;
	} else {
		$t_plugins_available[$t_basename] = $t_plugin;
	}
}

if ( 0 < count( $t_plugins_installed ) ) { 
?>
<br />
<form action="manage_plugin_update.php" method="post">
<?php echo form_security_field( 'manage_plugin_update' ) ?>
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="7">
		<?php echo lang_get( 'plugins_installed' ) ?>

	</td>
</tr>

<!-- Info -->
<tr class="row-category">
	<td width="20%"><?php echo lang_get( 'plugin' ) ?></td>
	<td width="35%"><?php echo lang_get( 'plugin_description' ) ?></td>
	<td width="20%"><?php echo lang_get( 'plugin_depends' ) ?></td>
	<td width="7%"><?php echo lang_get( 'plugin_priority' ) ?></td>
	<td width="8%"><?php echo lang_get( 'plugin_protected' ) ?></td>
	<td width="10%"><?php echo lang_get( 'plugin_actions' ) ?></td>
</tr>

<?php
foreach ( $t_plugins_installed as $t_basename => $t_plugin ) {
	$t_description = string_display_line_links( $t_plugin->description );
	$t_author = $t_plugin->author;
	$t_contact = $t_plugin->contact;
	$t_page = $t_plugin->page;
	$t_url = $t_plugin->url;
	$t_requires = $t_plugin->requires;
	$t_depends = array();
	$t_priority = plugin_priority( $t_basename );
	$t_protected = plugin_protected( $t_basename );

	$t_name = string_display_line( $t_plugin->name.' '.$t_plugin->version );
	if ( !is_blank( $t_page ) ) {
		$t_name = '<a href="' . string_attribute( plugin_page( $t_page, false, $t_basename ) ) . '">' . $t_name . '</a>';
	}

	if ( !is_blank( $t_author ) ) {
		if ( is_array( $t_author ) ) {
			$t_author = implode( $t_author, ', ' );
		}
		if ( !is_blank( $t_contact ) ) {
			$t_author = '<br />' . sprintf( lang_get( 'plugin_author' ),
				'<a href="mailto:' . string_attribute( $t_contact ) . '">' . string_display_line( $t_author ) . '</a>' );
		} else {
			$t_author = '<br />' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
		}
	}

	if ( !is_blank( $t_url ) ) {
		$t_url = '<br />' . lang_get( 'plugin_url' ) . lang_get( 'word_separator' ) . "<a href=\"$t_url\">$t_url</a>";
	}

	$t_upgrade = plugin_needs_upgrade( $t_plugin );
	$t_uninstall = ( 'MantisCore' != $t_basename && !$t_protected );

	if ( is_array( $t_requires ) ) {
		foreach( $t_requires as $t_plugin => $t_version ) {
			$t_dependency = plugin_dependency( $t_plugin, $t_version );
			if ( 1 == $t_dependency ) {
				if ( is_blank( $t_upgrade ) ) {
					$t_depends[] = '<span class="small dependency_met">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else {
					$t_depends[] = '<span class="small dependency_upgrade">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				}
			} else if ( -1 == $t_dependency ) {
				$t_depends[] = '<span class="small dependency_dated">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
			} else {
				$t_depends[] = '<span class="small dependency_unmet">'.string_display_line( $t_plugin.' '.$t_version ).'</span>';
			}
		}
	}

	if ( 0 < count( $t_depends ) ) {
		$t_depends = implode( $t_depends, '<br />' );
	} else {
		$t_depends = '<span class="small dependency_met">' . lang_get( 'plugin_no_depends' ) . '</span>';
	}

	echo '<tr ',helper_alternate_class(),'>';
	echo '<td class="small center">',$t_name,'<input type="hidden" name="change_',$t_basename,'" value="1"/></td>';
	echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
	echo '<td class="small center">',$t_depends,'</td>';
	if ( 'MantisCore' != $t_basename ) {
		echo '<td class="center">','<select name="priority_',$t_basename,'">',print_plugin_priority_list( $t_priority ),'</select>','</td>';
		echo '<td class="center">','<input type="checkbox" name="protected_',$t_basename,'" '.( $t_protected ? 'checked="checked" ' : '').'/>','</td>';
	} else {
		echo '<td>&#160;</td><td>&#160;</td>';
	}
	echo '<td class="center">';
	if ( $t_upgrade ) { print_bracket_link( 'manage_plugin_upgrade.php?name=' . $t_basename . form_security_param( 'manage_plugin_upgrade' ), lang_get( 'plugin_upgrade' ) ); }
	if ( $t_uninstall ) { print_bracket_link( 'manage_plugin_uninstall.php?name=' . $t_basename . form_security_param( 'manage_plugin_uninstall' ), lang_get( 'plugin_uninstall' ) ); }
	echo '</td></tr>';
} ?>

<tr>
	<td colspan="3"></td>
	<td colspan="2" class="center"><input type="submit" value="<?php echo lang_get( 'plugin_update' ) ?>"/></td>
	<td></td>
</tr>

</table>
</form>
<?php 
}

if ( 0 < count( $t_plugins_available ) ) { 
?>
<br />
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="7">
		<?php echo lang_get( 'plugins_available' ) ?>

	</td>
</tr>

<!-- Info -->
<tr class="row-category">
	<td width="25%"><?php echo lang_get( 'plugin' ) ?></td>
	<td width="45%"><?php echo lang_get( 'plugin_description' ) ?></td>
	<td width="20%"><?php echo lang_get( 'plugin_depends' ) ?></td>
	<td width="10%"><?php echo lang_get( 'plugin_actions' ) ?></td>
</tr>

<?php
foreach ( $t_plugins_available as $t_basename => $t_plugin ) {
	$t_description = string_display_line_links( $t_plugin->description );
	$t_author = $t_plugin->author;
	$t_contact = $t_plugin->contact;
	$t_url = $t_plugin->url ;
	$t_requires = $t_plugin->requires;
	$t_depends = array();

	$t_name = string_display_line( $t_plugin->name.' '.$t_plugin->version );

	if ( !is_blank( $t_author ) ) {
		if ( is_array( $t_author ) ) {
			$t_author = implode( $t_author, ', ' );
		}
		if ( !is_blank( $t_contact ) ) {
			$t_author = '<br />' . sprintf( lang_get( 'plugin_author' ),
				'<a href="mailto:' . string_display_line( $t_contact ) . '">' . string_display_line( $t_author ) . '</a>' );
		} else {
			$t_author = '<br />' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
		}
	}

	if ( !is_blank( $t_url ) ) {
		$t_url = '<br />' . lang_get( 'plugin_url' ) . lang_get( 'word_separator' ) . "<a href=\"$t_url\">$t_url</a>";
	}

	$t_ready = true;
	if ( is_array( $t_requires ) ) {
		foreach( $t_requires as $t_plugin => $t_version ) {
			$t_dependency = plugin_dependency( $t_plugin, $t_version );
			if ( 1 == $t_dependency ) {
				$t_depends[] = '<span class="small dependency_met">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
			} else if ( -1 == $t_dependency ) {
				$t_ready = false;
				$t_depends[] = '<span class="small dependency_dated">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
			} else {
				$t_ready = false;
				$t_depends[] = '<span class="small dependency_unmet">'.string_display_line( $t_plugin.' '.$t_version ).'</span>';
			}
		}
	}

	if ( 0 < count( $t_depends ) ) {
		$t_depends = implode( $t_depends, '<br />' );
	} else {
		$t_depends = '<span class="small dependency_met">' . lang_get( 'plugin_no_depends' ) . '</span>';
	}

	echo '<tr ',helper_alternate_class(),'>';
	echo '<td class="small center">',$t_name,'</td>';
	echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
	echo '<td class="center">',$t_depends,'</td>';
	echo '<td class="center">';
	if ( $t_ready ) { print_bracket_link( 'manage_plugin_install.php?name=' . $t_basename . form_security_param( 'manage_plugin_install' ), lang_get( 'plugin_install' ) ); }
	echo '</td></tr>';
} ?>

</table>
<?php } ?>

<br /><?php echo lang_get( 'plugin_key' ) ?>:
<span class='dependency_met'><?php echo lang_get( 'plugin_key_met' ) ?></span>,
<span class='dependency_unmet'><?php echo lang_get( 'plugin_key_unmet' ) ?></span>,
<span class='dependency_dated'><?php echo lang_get( 'plugin_key_dated' ) ?></span>,
<span class='dependency_upgrade'><?php echo lang_get( 'plugin_key_upgrade' ) ?></span>.

<?php
html_page_bottom();

