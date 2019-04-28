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

# Get list of all available and installed plugins, sorted by name
$t_plugins = plugin_find_all();
uasort( $t_plugins,
	function ( $p_p1, $p_p2 ) {
		return strcasecmp( $p_p1->name, $p_p2->name );
	}
);
?>

<div class="col-md-12 col-xs-12">

<?php
# Installed plugins section
$t_plugins_installed = prepare_for_display( true );
if( 0 < count( $t_plugins_installed ) ) {
?>

<div class="space-10"></div>

<div class="form-container">
	<form action="manage_plugin_update.php" method="post">
		<div class="widget-box widget-color-blue2">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php print_icon( 'fa-cubes', 'ace-icon' ); ?>
					<?php echo lang_get('plugins_installed') ?>
				</h4>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					<fieldset>
						<?php echo form_security_field( 'manage_plugin_update' ) ?>

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
	foreach( $t_plugins_installed as $t_basename => $t_data ) {
		echo '<tr>';
		echo '<td>',
			$t_data['plugin'],
			'<input type="hidden" name="change_', $t_basename, '" value="1"/></td>';
		echo '<td>', $t_data['description'], '</td>';
		echo '<td class="center">', $t_data['dependencies'], '</td>';

		if( $t_basename == 'MantisCore' ) {
			echo '<td>&nbsp;</td><td>&nbsp;</td>';
		} else {
			echo '<td class="center">',
				'<select name="priority_' . $t_basename . '" class="input-sm">';
			print_plugin_priority_list( $t_data['priority'] );
			echo '</select>', '</td>';
			echo '<td class="center">',
				'<label>',
				'<input type="checkbox" class="ace" name="protected_' . $t_basename . '"';
			check_checked( $t_data['protected'] );
			echo ' />',
				'<span class="lbl"></span>',
				'</label>',
				'</select>','</td>';
		}

		# Actions
		echo '<td class="center">';
		if( $t_data['upgrade'] ) {
			print_link_button(
				'manage_plugin_upgrade.php?name=' . $t_basename . form_security_param( 'manage_plugin_upgrade' ),
				lang_get( 'plugin_upgrade' ), 'btn-xs' );
		}
		if( !$t_data['protected'] ) {
			print_link_button(
				'manage_plugin_uninstall.php?name=' . $t_basename . form_security_param( 'manage_plugin_uninstall' ),
				lang_get( 'plugin_uninstall' ),  'btn-xs' );
		}
		echo '</td>';
		echo "</tr>\n";
	}
?>
								</tbody>
						</table>
					</div>

					<div class="widget-toolbox padding-8 clearfix">
						<input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="<?php echo lang_get( 'update' ) ?>"/>
					</div>
				</fieldset>
			</div>
		</div>
	</div>
	</form>
</div>
<?php
} # End Installed plugins section

# Available plugins section
$t_plugins_available = prepare_for_display( false );
if( 0 < count( $t_plugins_available ) ) {
?>

<div class="space-10"></div>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<?php print_icon( 'fa-cube', 'ace-icon' ); ?>
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
						<tr>
							<th><?php echo lang_get( 'plugin' ) ?></th>
							<th><?php echo lang_get( 'plugin_description' ) ?></th>
							<th><?php echo lang_get( 'plugin_depends' ) ?></th>
							<th><?php echo lang_get( 'plugin_actions' ) ?></th>
						</tr>
					</thead>

					<tbody>
<?php
foreach( $t_plugins_available as $t_basename => $t_data ) {
	echo '<tr>';
	echo '<td>',
	$t_data['plugin'],
	'<input type="hidden" name="change_', $t_basename, '" value="1"/></td>';
	echo '<td>', $t_data['description'], '</td>';
	echo '<td class="center">', $t_data['dependencies'], '</td>';

	# Actions
	echo '<td class="center">';
	if( $t_data['install'] ) {
		print_small_button(
			'manage_plugin_install.php?name=' . $t_basename . form_security_param( 'manage_plugin_install' ),
			lang_get( 'plugin_install' ) );
	}
	echo '</td>';

	echo "</tr>\n";
}
?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php
} # End Available plugins section

# Plugin dependencies color key
?>
<div class="center">
	<div class="space-10"></div>
	<div class="well well-sm">
		<?php print_icon( 'fa-key', 'ace-icon' ); ?>
		<?php echo lang_get('plugin_key_label') ?>
		<span class='dependency_met'><?php echo lang_get( 'plugin_key_met' ) ?></span>,
		<span class='dependency_unmet'><?php echo lang_get( 'plugin_key_unmet' ) ?></span>,
		<span class='dependency_dated'><?php echo lang_get( 'plugin_key_dated' ) ?></span>,
		<span class='dependency_upgrade'><?php echo lang_get( 'plugin_key_upgrade' ) ?></span>.
	</div>
</div>
</div>

<?php
layout_page_end();

/**
 * Prepare plugin information for display.
 *
 * @param bool $p_installed True for installed, False for available plugins
 *
 * @return array Data to display
 */
function prepare_for_display( $p_installed ) {
	global $t_plugins;

	$t_display_data = array();

	foreach( $t_plugins as $t_basename => $t_plugin) {
		if( $p_installed xor plugin_is_registered( $t_basename ) ) {
			continue;
		}

		$t_name = $t_plugin->name . ' ' . $t_plugin->version;
		$t_upgrade_needed = plugin_needs_upgrade( $t_plugin );

		# Plugin Author
		$t_author = $t_plugin->author;
		if( !empty( $t_author ) ) {
			if( is_array( $t_author ) ) {
				$t_author = implode( ', ', $t_author );
			}
			if( !is_blank( $t_plugin->contact ) ) {
				$t_subject = lang_get( 'plugin' ) . ' - ' . $t_name;
				$t_author = '<br />'
					. sprintf( lang_get( 'plugin_author' ),
						prepare_email_link( $t_plugin->contact, $t_author, $t_subject )
					);
			} else {
				$t_author = '<br />' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
			}
		}

		# Plugin name / page
		# If plugin is installed and has a config page, we create a link to it
		if( $p_installed && !is_blank( $t_plugin->page ) ) {
			$t_name = '<a href="'
				. string_attribute( plugin_page( $t_plugin->page, false, $t_basename ) )
				. '">'
				. string_display_line( $t_name )
				. '</a>';
		}

		# Plugin Website URL
		$t_url = $t_plugin->url;
		if( !is_blank( $t_url ) ) {
			$t_url = '<br />'
				. lang_get( 'plugin_url' )
				. lang_get( 'word_separator' )
				. '<a href="' . $t_url . '">' . $t_url . '</a>';
		}

		# Dependencies
		$t_can_install = true;
		if( is_array( $t_plugin->requires ) ) {
			$t_depends = array();
			foreach( $t_plugin->requires as $t_required_basename => $t_version ) {
				$t_dependency_name = array_key_exists( $t_required_basename, $t_plugins )
					? $t_plugins[$t_required_basename]->name
					: $t_required_basename;
				$t_dependency_name .= ' ' . $t_version;

				switch( plugin_dependency( $t_required_basename, $t_version ) ) {
					case 1:
						if( $t_upgrade_needed ) {
							$t_class = 'dependency_upgrade';
							$t_tooltip = lang_get( 'plugin_key_upgrade' );
						} else {
							$t_class = 'dependency_met';
							$t_tooltip = lang_get( 'plugin_key_met' );
						}
						break;
					case -1:
						$t_class = 'dependency_dated';
						$t_tooltip = lang_get( 'plugin_key_dated' );
						$t_can_install = false;
						break;
					case 0:
					default:
						$t_class = 'dependency_unmet';
						$t_tooltip = lang_get( 'plugin_key_unmet' );
						$t_can_install = false;
						break;
				}

				$t_depends[] = sprintf( '<span class="%s" title="%s">%s</span>',
					$t_class,
					$t_tooltip,
					string_display_line( $t_dependency_name )
				);
			}
			$t_depends = implode( '<br>', $t_depends );
		} else {
			$t_depends = '<span class="dependency_met">'
				. lang_get( 'plugin_no_depends' )
				. '</span>';
		}

		$t_display_data[$t_basename] = array(
			'plugin' => $t_name,
			'description' => string_display_line_links( $t_plugin->description )
				. '<span class="small">' . $t_author . $t_url . '</span>',
			'dependencies' => $t_depends,
			'upgrade' => $t_upgrade_needed,
			'install' => $t_can_install,
		);
		if( $p_installed ) {
			$t_display_data[$t_basename] += array(
				'priority' => plugin_priority( $t_basename ),
				'protected' =>  plugin_protected( $t_basename ),
			);
		}
	}
	return $t_display_data;
}
