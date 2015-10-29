<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Mantis Graph plugin
 */
class MantisGraphPlugin extends MantisPlugin  {

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = lang_get( 'plugin_graph_title' );
		$this->description = lang_get( 'plugin_graph_description' );
		$this->page = 'config';

		$this->version = '1.3.0';
		$this->requires = array(
			'MantisCore' => '1.3.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	function config() {
		return array(
			'eczlibrary' => ON,

			'window_width' => 800,
			'bar_aspect' => 0.9,
			'summary_graphs_per_row' => 2,
			'font' => 'arial',

			'jpgraph_path' => '',
			'jpgraph_antialias' => ON,
		);
	}

	/**
	 * init function
	 * @return void
	 */
	function init() {
		spl_autoload_register( array( 'MantisGraphPlugin', 'autoload' ) );
	}

	/**
	 * class auto loader
	 * @param string $p_class Class name to autoload.
	 * @return void
	 */
	public static function autoload( $p_class ) {
		if( class_exists( 'ezcBase' ) ) {
			ezcBase::autoload( $p_class );
		}
	}

	/**
	 * plugin hooks
	 * @return array
	 */
	function hooks() {
		$t_hooks = array(
			'EVENT_MENU_SUMMARY' => 'summary_menu',
			'EVENT_SUBMENU_SUMMARY' => 'summary_submenu',
			'EVENT_MENU_FILTER' => 'graph_filter_menu'
		);
		return $t_hooks;
	}

	/**
	 * generate summary menu
	 * @return array
	 */
	function summary_menu() {
		return array( '<a href="' . plugin_page( 'summary_jpgraph_page' ) . '">' . plugin_lang_get( 'menu_advanced_summary' ) . '</a>', );
	}

	/**
	 * generate graph filter menu
	 * @return array
	 */
	function graph_filter_menu() {
		if( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
			return array( '<a href="' . plugin_page( 'bug_graph_page.php' ) . '">' . plugin_lang_get( 'graph_bug_page_link' ) . '</a>', );
		} else {
			return '';
		}
	}

	/**
	 * generate summary submenu
	 * @return array
	 */
	function summary_submenu() {
		$t_icon_path = config_get( 'icon_path' );
		return array( '<a href="' . helper_mantis_url( 'summary_page.php' ) . '"><img src="' . $t_icon_path . 'synthese.gif" alt="" />' . plugin_lang_get( 'synthesis_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_status.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" alt="" />' . plugin_lang_get( 'status_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_priority.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" alt="" />' . plugin_lang_get( 'priority_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_severity.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" alt="" />' . plugin_lang_get( 'severity_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_category.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" alt="" />' . plugin_lang_get( 'category_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_resolution.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" alt="" />' . plugin_lang_get( 'resolution_link' ) . '</a>',
		);
	}
}
