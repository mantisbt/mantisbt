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

$t_plugins = new ListPluginsForDisplay();
?>

<div class="col-md-12 col-xs-12">

<?php
# Installed plugins section
if( $t_plugins->countInstalled() ) {
?>

<div class="space-10"></div>

<a id="installed"></a>
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
									<?php $t_plugins->printInstalled(); ?>
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

# Invalid plugins section
if( $t_plugins->countInvalid() ) {
?>
	<div class="space-10"></div>
	<a id="invalid"></a>
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<?php print_icon( 'fa-exclamation-triangle', 'ace-icon' ); ?>
				<?php echo lang_get( 'plugins_missing' ) ?>
			</h4>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<?php
			printf(
				lang_get( 'plugins_missing_description' ),
				string_attribute( config_get_global( 'plugin_path' ) )
			);
			?>
		</div>
		<div class="widget-body">
			<div class="widget-main no-padding">
				<div class="table-responsive">
					<table class="table table-striped table-bordered table-condensed table-hover">
						<colgroup>
							<col>
							<col>
							<col style="width:10%">
						</colgroup>
						<thead>
						<tr>
							<th><?php echo lang_get( 'plugin' ) ?></th>
							<th><?php echo lang_get( 'plugin_problem_description' ) ?></th>
							<th><?php echo lang_get( 'plugin_actions' ) ?></th>
						</tr>
						</thead>
						<tbody>
							<?php $t_plugins->printInvalid(); ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php
} # End Invalid plugins section

# Available plugins section
if( $t_plugins->countAvailable() ) {
?>

<a id="available"></a>
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
						<?php $t_plugins->printAvailable(); ?>
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
 * Class ListPluginsForDisplay
 *
 * Collects all Plugins and dispatches them in 3 categories: available,
 * installed and invalid. Provides functions to print each sub-list in the
 * appropriate format, depending on the category.
 */
class ListPluginsForDisplay {
	/**
	 * @var AvailablePlugin[] List of available plugins (i.e. valid and not installed)
	 */
	protected $available = array();

	/**
	 * @var InstalledPlugin[] List of installed (registered) plugins
	 */
	protected $installed = array();

	/**
	 * @var InvalidPluginForDisplay[] List of invalid plugins
	 */
	protected $invalid = array();

	/**
	 * PluginsListForDisplay constructor.
	 */
	public function __construct() {
		# Get list of all available and installed plugins, sorted by name
		$t_plugins = plugin_find_all();
		uasort( $t_plugins,
			function ( $p_p1, $p_p2 ) {
				return strcasecmp( $p_p1->name, $p_p2->name );
			}
		);

		foreach( $t_plugins as $t_basename => $t_plugin ) {
			if( !$t_plugin->isValid() ) {
				$this->invalid[$t_basename] = new InvalidPluginForDisplay( $t_plugin );
			} elseif( plugin_is_registered( $t_basename ) ) {
				$this->installed[$t_basename] = new InstalledPlugin( $t_plugin );
			} else {
				$this->available[$t_basename] = new AvailablePlugin( $t_plugin );
			}
		}
	}

	/**
	 * @return int Number of available plugins
	 */
	public function countAvailable() {
		return count( $this->available );
	}

	/**
	 * @return int Number of installed plugins
	 */
	public function countInstalled() {
		return count( $this->installed );
	}

	/**
	 * @return int Number of invalid plugins
	 */
	public function countInvalid() {
		return count( $this->invalid );
	}

	/**
	 * Prints the list of available plugins
	 */
	public function printAvailable() {
		foreach( $this->available as $t_plugin ) {
			$t_plugin->render();
		}
	}

	/**
	 * Prints the list of installed plugins
	 */
	public function printInstalled() {
		foreach( $this->installed as $t_plugin ) {
			$t_plugin->render();
		}
	}

	/**
	 * Print the list of invalid plugins
	 */
	public function printInvalid() {
		foreach( $this->invalid as $t_plugin ) {
			$t_plugin->render();
		}
	}
}

abstract class PluginForDisplay {
	protected $basename = '';
	protected $name = '';
	protected $description = '';

	public function __construct( MantisPlugin $p_plugin ) {
		$this->basename = $p_plugin->basename;
		$this->name = $p_plugin->name;
		if( $p_plugin->version ) {
			$this->name .= ' ' . $p_plugin->version;
		}
	}

	public function render() {
		echo "<tr>\n";
		$this->renderColumns();
		echo "</tr>\n";
	}

	protected function renderColumns() {
		echo "<td>$this->name</td>\n";
		echo "<td>$this->description</td>\n";
	}
}

class InvalidPluginForDisplay extends PluginForDisplay {
	protected $can_remove = false;

	public function __construct( MantisPlugin $p_plugin ) {
		parent::__construct( $p_plugin );

		# Descriptions from InvalidPlugin classes are trusted input
		$this->description = $p_plugin->description;
		if( $p_plugin->status_message ) {
			$this->description .= '<br>' . $p_plugin->status_message;
		}

		$this->can_remove = $p_plugin->removable;
	}

	protected function renderColumns() {
		parent::renderColumns();

		# Actions
		echo '<td class="center">';
		if( $this->can_remove ) {
			print_link_button(
				'manage_plugin_uninstall.php?name=' . $this->basename
				. form_security_param( 'manage_plugin_uninstall'
				),
				lang_get( 'remove_link' ),
				'btn-xs'
			);
		} else {
			echo lang_get( 'plugin_manual_fix' );
		}
		echo '</td>', "\n";
	}
}

class AvailablePlugin extends PluginForDisplay {
	protected $dependencies = array();
	protected $upgrade_needed = false;
	protected $can_install = false;

	public function __construct( MantisPlugin $p_plugin ) {
		parent::__construct( $p_plugin );

		$t_plugin_name = $p_plugin->name . ' ' . $p_plugin->version;

		# Plugin Author
		$t_author = $p_plugin->author;
		if( !empty( $t_author ) ) {
			if( is_array( $t_author ) ) {
				$t_author = implode( ', ', $t_author );
			}
			if( !is_blank( $p_plugin->contact ) ) {
				$t_subject = lang_get( 'plugin' ) . ' - ' . $t_plugin_name;
				$t_author = '<br>'
					. sprintf( lang_get( 'plugin_author' ),
						prepare_email_link( $p_plugin->contact, $t_author, $t_subject )
					);
			} else {
				$t_author = '<br>' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
			}
		}

		# Plugin Website URL
		$t_url = $p_plugin->url;
		if( !is_blank( $t_url ) ) {
			$t_url = '<br>'
				. lang_get( 'plugin_url' )
				. lang_get( 'word_separator' )
				. '<a href="' . $t_url . '">' . $t_url . '</a>';
		}

		# Description
		$this->description = string_display_line_links( $p_plugin->description )
			. '<span class="small">' . $t_author . $t_url . '</span>';

		# Dependencies
		if( is_array( $p_plugin->requires ) ) {
			$_all_plugins = plugin_find_all();
			foreach( $p_plugin->requires as $t_required_basename => $t_version ) {
				$this->can_install = false;

				switch( plugin_dependency( $t_required_basename, $t_version ) ) {
					case 1:
						$t_upgrade_needed = plugin_is_registered( $t_required_basename )
							&& plugin_needs_upgrade( plugin_get( $t_required_basename ) );
						if( $t_upgrade_needed ) {
							$t_class = 'dependency_upgrade';
							$t_tooltip = lang_get( 'plugin_key_upgrade' );
						} else {
							$t_class = 'dependency_met';
							$t_tooltip = lang_get( 'plugin_key_met' );
							$this->can_install = true;
						}
						break;
					case -1:
						$t_class = 'dependency_dated';
						$t_tooltip = lang_get( 'plugin_key_dated' );
						break;
					case 0:
					default:
						$t_class = 'dependency_unmet';
						$t_tooltip = lang_get( 'plugin_key_unmet' );
						break;
				}

				$t_dependency_name = array_key_exists( $t_required_basename, $_all_plugins )
					? $_all_plugins[$t_required_basename]->name
					: $t_required_basename;
				$t_dependency_name .= ' ' . $t_version;

				$this->dependencies[] = sprintf( '<span class="%s" title="%s">%s</span>',
					$t_class,
					$t_tooltip,
					string_display_line( $t_dependency_name )
				);
			}
		} else {
			$this->dependencies[] = '<span class="dependency_met">'
				. lang_get( 'plugin_no_depends' )
				. '</span>';
		}

		$this->upgrade_needed = plugin_needs_upgrade( $p_plugin );
	}

	protected function renderColumns() {
		parent::renderColumns();

		# Dependencies
		echo '<td>', implode( '<br>', $this->dependencies ), '</td>', "\n";

		# Actions
		# Only displayed if current object is of AvailablePlugin class
		if( get_class( $this ) == 'AvailablePlugin' ) {
			echo '<td class="center">';
			if( $this->can_install ) {
				print_small_button(
					'manage_plugin_install.php?name=' . $this->basename
					. form_security_param( 'manage_plugin_install' ),
					lang_get( 'plugin_install' )
				);
			}
			echo '</td>', "\n";
		}
	}
}

class InstalledPlugin extends AvailablePlugin {
	protected $priority;
	protected $protected;

	public function __construct( MantisPlugin $p_plugin ) {
		parent::__construct( $p_plugin );

		# Plugin name / page
		# If plugin is installed and has a config page, we create a link to it
		if( !is_blank( $p_plugin->page ) ) {
			$this->name = '<a href="'
				. string_attribute( plugin_page( $p_plugin->page, false, $p_plugin->basename ) )
				. '">'
				. string_display_line( $this->name )
				. '</a>';
		}

		$this->priority = plugin_priority( $p_plugin->basename );
		$this->protected =  plugin_protected( $p_plugin->basename );
	}

	protected function renderColumns() {
		parent::renderColumns();

		if( $this->basename == 'MantisCore' ) {
			echo "<td>&nbsp;</td>\n<td>&nbsp;</td>\n";
		} else {
			echo '<input type="hidden" name="change_', $this->basename, '" value="1"/>';

			# Priority
			echo '<td class="center">',
				'<select name="priority_' . $this->basename . '" class="input-sm">';
			print_plugin_priority_list( $this->priority );
			echo '</select>', '</td>', "\n";

			# Protected
			echo '<td class="center">';
			echo '<label>',
				'<input type="checkbox" class="ace" name="protected_' . $this->basename . '"';
			check_checked( $this->protected );
			echo ' />',
				'<span class="lbl"></span>',
				'</label>';
			echo '</td>', "\n";
		}

		# Actions
		echo '<td class="center">';
		if( $this->upgrade_needed ) {
			print_link_button(
				'manage_plugin_upgrade.php?name=' . $this->basename
				. form_security_param( 'manage_plugin_upgrade' ),
				lang_get( 'plugin_upgrade' ),
				'btn-xs'
			);
		}
		if( !$this->protected ) {
			print_link_button(
				'manage_plugin_uninstall.php?name=' . $this->basename
				. form_security_param( 'manage_plugin_uninstall' ),
				lang_get( 'plugin_uninstall' ),
				'btn-xs'
			);
		}
		echo '</td>', "\n";
	}
}
