<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.

# --------------------------------------------------------
# $Id$
# --------------------------------------------------------

require_once( 'core.php' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top1( lang_get( 'manage_plugin_link' ) );
html_page_top2();

print_manage_menu( 'manage_plugin_page.php' );

$t_plugins = plugin_find_all();
$t_plugins_installed = plugin_get_installed();

$t_plugins_available = array();
foreach( $t_plugins as $t_basename => $t_info ) {
	if ( !isset( $t_plugins_installed[$t_basename] ) ) {
		$t_plugins_available[$t_basename] = $t_info;
	}
}

?>

<?php if ( 0 < count( $t_plugins_installed ) ) { ?>
<br/>
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
	<td width="50%"><?php echo lang_get( 'plugin_description' ) ?></td>
	<td width="20%"><?php echo lang_get( 'plugin_depends' ) ?></td>
	<td width="10%"><?php echo lang_get( 'plugin_actions' ) ?></td>
</tr>

<?php 
foreach ( $t_plugins_installed as $t_basename => $t_enabled ) {
	$t_description = string_display_line_links( $t_plugins[$t_basename]['description'] );
	$t_author = $t_plugins[$t_basename]['author'];
	$t_contact = $t_plugins[$t_basename]['contact'];
	$t_page = $t_plugins[$t_basename]['page'] ;
	$t_url = $t_plugins[$t_basename]['url'] ;
	$t_requires = $t_plugins[$t_basename]['requires'];
	$t_depends = array();

	$t_name = string_display_line( $t_plugins[$t_basename]['name'].' '.$t_plugins[$t_basename]['version'] );
	if ( !is_blank( $t_page ) ) {
		$t_name = '<a href="' . string_attribute( plugin_page( $t_page, $t_basename ) ) . '">' . $t_name . '</a>';
	}

	if ( !is_blank( $t_author ) ) {
		if ( is_array( $t_author ) ) {
			$t_author = implode( $t_author, ', ' );
		}
		if ( !is_blank( $t_contact ) ) {
			$t_author = '<br/>' . sprintf( lang_get( 'plugin_author' ), 
				'<a href="mailto:' . string_attribute( $t_contact ) . '">' . string_display_line( $t_author ) . '</a>' );
		} else {
			$t_author = '<br/>' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
		}
	}

	if ( !is_blank( $t_url ) ) {
		$t_url = '<br/>' . lang_get( 'plugin_url' ) . "<a href=\"$t_url\">$t_url</a>";
	}

	$t_upgrade = '';
	if ( plugin_needs_upgrade( $t_basename ) ) {
		$t_upgrade = '<form action="manage_plugin_upgrade.php?name='.$t_basename.'" method="post">'.
			'<input type="submit" value="'.lang_get( 'plugin_upgrade' ).'"></form>';
	}

	$t_uninstall = '';
	if ( 'mantis' != $t_basename ) {
		$t_uninstall = '<form action="manage_plugin_uninstall.php?name='.$t_basename.'" method="post">'.
			'<input type="submit" value="'.lang_get( 'plugin_uninstall' ).'"></form>';
	}

	if ( is_array( $t_requires ) ) {
		foreach( $t_requires as $t_plugin => $t_version ) {
			$t_dependency = plugin_dependency( $t_plugin, $t_version );
			if ( 1 == $t_dependency ) {
				if ( is_blank( $t_upgrade ) ) {
					$t_depends[] = '<span class="dependency_met">'.string_display_line( $t_plugins[$t_plugin]['name'].' '.$t_version ).'</span>';
				} else {
					$t_depends[] = '<span class="dependency_upgrade">'.string_display_line( $t_plugins[$t_plugin]['name'].' '.$t_version ).'</span>';
				}
			} elseif ( -1 == $t_dependency ) {
				$t_depends[] = '<span class="dependency_dated">'.string_display_line( $t_plugins[$t_plugin]['name'].' '.$t_version ).'</span>';
			} else {
				$t_depends[] = '<span class="dependency_unmet">'.string_display_line( $t_plugin.' '.$t_version ).'</span>';
			}
		}
	}

	if ( 0 < count( $t_depends ) ) {
		$t_depends = implode( $t_depends, '<br/>' );
	} else {
		$t_depends = '<span class="dependency_met">' . lang_get( 'plugin_no_depends' ) . '</span>';
	}

	echo '<tr ',helper_alternate_class(),'>';
	echo '<td class="center">',$t_name,'</td>';
	echo '<td>',$t_description,$t_author,$t_url,'</td>';
	echo '<td class="center">',$t_depends,'</td>';
	echo '<td class="center">',$t_upgrade,$t_uninstall,'</td>';
	echo '</tr>';
} ?>

</table>
<?php } ?>

<?php if ( 0 < count( $t_plugins_available ) ) { ?>
<br/>
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="7">
		<?php echo lang_get( 'plugins_available' ) ?>

	</td>
</tr>

<!-- Info -->
<tr class="row-category">
	<td width="20%"><?php echo lang_get( 'plugin' ) ?></td>
	<td width="50%"><?php echo lang_get( 'plugin_description' ) ?></td>
	<td width="20%"><?php echo lang_get( 'plugin_depends' ) ?></td>
	<td width="10%"><?php echo lang_get( 'plugin_actions' ) ?></td>
</tr>

<?php 
foreach ( $t_plugins_available as $t_basename => $t_info ) {
	$t_description = string_display_line_links( $t_info['description'] );
	$t_author = $t_info['author'];
	$t_contact = $t_info['contact'];
	$t_url = $t_info['url'] ;
	$t_requires = $t_info['requires'];
	$t_depends = array();

	$t_name = string_display_line( $t_info['name'].' '.$t_info['version'] );

	if ( !is_blank( $t_author ) ) {
		if ( is_array( $t_author ) ) {
			$t_author = implode( $t_author, ', ' );
		}
		if ( !is_blank( $t_contact ) ) {
			$t_author = '<br/>' . sprintf( lang_get( 'plugin_author' ), 
				'<a href="mailto:' . string_display_line( $t_contact ) . '">' . string_display_line( $t_author ) . '</a>' );
		} else {
			$t_author = '<br/>' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
		}
	}

	if ( !is_blank( $t_url ) ) {
		$t_url = '<br/>' . lang_get( 'plugin_url' ) . "<a href=\"$t_url\">$t_url</a>";
	}

	$t_ready = true;
	if ( is_array( $t_requires ) ) {
		foreach( $t_requires as $t_plugin => $t_version ) {
			$t_dependency = plugin_dependency( $t_plugin, $t_version );
			if ( 1 == $t_dependency ) {
				$t_depends[] = '<span class="dependency_met">'.string_display_line( $t_plugins[$t_plugin]['name'].' '.$t_version ).'</font>';
			} elseif ( -1 == $t_dependency ) {
				$t_ready = false;
				$t_depends[] = '<span class="dependency_dated">'.string_display_line( $t_plugins[$t_plugin]['name'].' '.$t_version ).'</font>';
			} else {
				$t_ready = false;
				$t_depends[] = '<span class="dependency_unmet">'.string_display_line( $t_plugin.' '.$t_version ).'</font>';
			}
		}
	}

	if ( 0 < count( $t_depends ) ) {
		$t_depends = implode( $t_depends, '<br/>' );
	} else {
		$t_depends = '<span class="dependency_met">' . lang_get( 'plugin_no_depends' ) . '</font>';
	}

	$t_install = '';
	if ( $t_ready ) {
		$t_install = '<form action="manage_plugin_install.php?name='.$t_basename.'" method="post">'.
			'<input type="submit" value="'.lang_get( 'plugin_install' ).'"></form>';
	}

	echo '<tr ',helper_alternate_class(),'>';
	echo '<td class="center">',$t_name,'</td>';
	echo '<td>',$t_description,$t_author,$t_url,'</td>';
	echo '<td class="center">',$t_depends,'</td>';
	echo '<td class="center">',$t_install,'</td>';
	echo '</tr>';
} ?>

</table>
<?php } ?>

<br/><?php echo lang_get( 'plugin_key' ) ?>:
<span class='dependency_met'><?php echo lang_get( 'plugin_key_met' ) ?></span>,
<span class='dependency_unmet'><?php echo lang_get( 'plugin_key_unmet' ) ?></span>,
<span class='dependency_dated'><?php echo lang_get( 'plugin_key_dated' ) ?></span>,
<span class='dependency_upgrade'><?php echo lang_get( 'plugin_key_upgrade' ) ?></span>.

<?php
html_page_bottom1();

