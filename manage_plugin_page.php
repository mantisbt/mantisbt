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
 * Plugin Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses plugin_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'plugin_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( lang_get( 'manage_plugin_link' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

$t_plugins = plugin_find_all();
uasort( $t_plugins,
	function ( $p_p1, $p_p2 ) {
		return strcasecmp( $p_p1->name, $p_p2->name );
	}
);

$t_plugins_installed = array();
$t_plugins_available = array();

foreach( $t_plugins as $t_basename => $t_plugin ) {
	if( plugin_is_registered( $t_basename ) ) {
		$t_plugins_installed[$t_basename] = $t_plugin;
	} else {
		$t_plugins_available[$t_basename] = $t_plugin;
	}
}

if( 0 < count( $t_plugins_installed ) ) {
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">

	<form action="manage_plugin_update.php" method="post">
		<fieldset>
		<?php echo form_security_field( 'manage_plugin_update' ) ?>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-cubes"></i>
				<?php echo lang_get('plugins_installed') ?>
		</h4>
	</div>
<div class="widget-body">
<div class="widget-main no-padding">
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed table-hover">

			<colgroup>
				<col style="width:20%" />
				<col style="width:35%" />
				<col style="width:20%" />
				<col style="width:7%" />
				<col style="width:8%" />
				<col style="width:10%" />
			</colgroup>
			<thead>
				<!-- Info -->
				<tr>
					<th><?php echo lang_get( 'plugin' ) ?></th>
					<th><?php echo lang_get( 'plugin_description' ) ?></th>
					<th><?php echo lang_get( 'plugin_depends' ) ?></th>
					<th><?php echo lang_get( 'plugin_priority' ) ?></th>
					<th><?php echo lang_get( 'plugin_protected' ) ?></th>
					<th><?php echo lang_get( 'plugin_actions' ) ?></th>
				</tr>
			</thead>

			<tbody>
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
	if( !is_blank( $t_page ) ) {
		$t_name = '<a href="' . string_attribute( plugin_page( $t_page, false, $t_basename ) ) . '">' . $t_name . '</a>';
	}

	if( !empty( $t_author ) ) {
		if( is_array( $t_author ) ) {
			$t_author = implode( ', ', $t_author );
		}
		if( !is_blank( $t_contact ) ) {
			$t_author = '<br />' . sprintf( lang_get( 'plugin_author' ),
					prepare_email_link( $t_contact, $t_author )
				);
		} else {
			$t_author = '<br />' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
		}
	}

	if( !is_blank( $t_url ) ) {
		$t_url = '<br />' . lang_get( 'plugin_url' ) . lang_get( 'word_separator' ) . '<a href="' . $t_url . '">' . $t_url . '</a>';
	}

	$t_upgrade = plugin_needs_upgrade( $t_plugin );

	if( is_array( $t_requires ) ) {
		foreach( $t_requires as $t_plugin => $t_version ) {
			$t_dependency = plugin_dependency( $t_plugin, $t_version );
			if( 1 == $t_dependency ) {
				if( is_blank( $t_upgrade ) ) {
					$t_depends[] = '<span class="small dependency_met">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else {
					$t_depends[] = '<span class="small dependency_upgrade">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				}
			} else if( -1 == $t_dependency ) {
				$t_depends[] = '<span class="small dependency_dated">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
			} else {
				$t_depends[] = '<span class="small dependency_unmet">'.string_display_line( $t_plugin.' '.$t_version ).'</span>';
			}
		}
	}

	if( 0 < count( $t_depends ) ) {
		$t_depends = implode( '<br>', $t_depends );
	} else {
		$t_depends = '<span class="small dependency_met">' . lang_get( 'plugin_no_depends' ) . '</span>';
	}

	echo '<tr>';
	echo '<td class="small center">',$t_name,'<input type="hidden" name="change_',$t_basename,'" value="1"/></td>';
	echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
	echo '<td class="small center">',$t_depends,'</td>';
	if( 'MantisCore' == $t_basename ) {
		echo '<td>&#160;</td><td>&#160;</td>';
	} else {
		echo '<td class="center">',
			'<select name="priority_' . $t_basename . '"',
				' class="input-sm">',
				print_plugin_priority_list( $t_priority ),
			'</select>','</td>';
		echo '<td class="center">',
		'<label>',
			'<input type="checkbox" class="ace" name="protected_' . $t_basename . '"',
				check_checked( $t_protected ), ' />',
		'<span class="lbl"></span>',
		'</label>',
			'</select>','</td>';
	}
	echo '<td class="center">';
	if( $t_upgrade ) {
		print_link_button(
			'manage_plugin_upgrade.php?name=' . $t_basename . form_security_param( 'manage_plugin_upgrade' ),
			lang_get( 'plugin_upgrade' ), 'btn-xs' );
	}
	if( !$t_protected ) {
		print_link_button(
			'manage_plugin_uninstall.php?name=' . $t_basename . form_security_param( 'manage_plugin_uninstall' ),
			lang_get( 'plugin_uninstall' ),  'btn-xs' );
	}
	echo '</td></tr>';
} ?>
			</tbody>
		</table>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="<?php echo lang_get('plugin_update') ?>"/>
		</div>
	</div>
</div>
</div>
</form>
</div>
<?php
}

if( 0 < count( $t_plugins_available ) ) {
?>

<div class="space-10"></div>
<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-cube"></i>
			<?php echo lang_get('plugins_available') ?>
		</h4>
	</div>

<div class="widget-body">
	<div class="widget-main no-padding">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-condensed table-hover">
		<colgroup>
			<col style="width:25%" />
			<col style="width:45%" />
			<col style="width:20%" />
			<col style="width:10%" />
		</colgroup>
		<thead>
			<!-- Info -->
			<tr class="row-category">
				<td><?php echo lang_get( 'plugin' ) ?></td>
				<td><?php echo lang_get( 'plugin_description' ) ?></td>
				<td><?php echo lang_get( 'plugin_depends' ) ?></td>
				<td><?php echo lang_get( 'plugin_actions' ) ?></td>
			</tr>
		</thead>

		<tbody>
<?php
	foreach ( $t_plugins_available as $t_basename => $t_plugin ) {
		$t_description = string_display_line_links( $t_plugin->description );
		$t_author = $t_plugin->author;
		$t_contact = $t_plugin->contact;
		$t_url = $t_plugin->url ;
		$t_requires = $t_plugin->requires;
		$t_depends = array();

		$t_name = string_display_line( $t_plugin->name.' '.$t_plugin->version );

		if( !empty( $t_author ) ) {
			if( is_array( $t_author ) ) {
				$t_author = implode( ', ', $t_author );
			}
			if( !is_blank( $t_contact ) ) {
				$t_author = '<br />' . sprintf( lang_get( 'plugin_author' ),
						prepare_email_link( $t_contact, $t_author )
					);
			} else {
				$t_author = '<br />' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
			}
		}

		if( !is_blank( $t_url ) ) {
			$t_url = '<br />' . lang_get( 'plugin_url' ) . lang_get( 'word_separator' ) . '<a href="' . $t_url . '">' . $t_url . '</a>';
		}

		$t_ready = true;
		if( is_array( $t_requires ) ) {
			foreach( $t_requires as $t_plugin => $t_version ) {
				$t_dependency = plugin_dependency( $t_plugin, $t_version );
				if( 1 == $t_dependency ) {
					$t_depends[] = '<span class="small dependency_met">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else if( -1 == $t_dependency ) {
					$t_ready = false;
					$t_depends[] = '<span class="small dependency_dated">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else {
					$t_ready = false;
					$t_depends[] = '<span class="small dependency_unmet">'.string_display_line( $t_plugin.' '.$t_version ).'</span>';
				}
			}
		}

		if( 0 < count( $t_depends ) ) {
			$t_depends = implode( '<br>', $t_depends );
		} else {
			$t_depends = '<span class="small dependency_met">' . lang_get( 'plugin_no_depends' ) . '</span>';
		}

		echo '<tr>';
		echo '<td class="small center">',$t_name,'</td>';
		echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
		echo '<td class="center">',$t_depends,'</td>';
		echo '<td class="center">';
		if( $t_ready ) {
			print_small_button(
				'manage_plugin_install.php?name=' . $t_basename . form_security_param( 'manage_plugin_install' ),
				lang_get( 'plugin_install' ) );
		}
		echo '</td></tr>';
	}
?>
		</tbody>
	</table>
	</div>
</div>
</div>
</div>

<?php
} # available plugins
?>
<div class="center">
	<div class="space-10"></div>
	<div class="well well-sm">
		<i class="ace-icon fa fa-key"></i>
	<?php echo lang_get('plugin_key_label') ?>
	<span class='dependency_met'><?php echo lang_get( 'plugin_key_met' ) ?></span>,
	<span class='dependency_unmet'><?php echo lang_get( 'plugin_key_unmet' ) ?></span>,
	<span class='dependency_dated'><?php echo lang_get( 'plugin_key_dated' ) ?></span>,
	<span class='dependency_upgrade'><?php echo lang_get( 'plugin_key_upgrade' ) ?></span>.
	</div>
</div>
<?php
echo '</div>';
layout_page_end();

