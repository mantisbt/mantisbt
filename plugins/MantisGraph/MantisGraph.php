<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );

class MantisGraphPlugin extends MantisPlugin  {

	/**
	 *  A method that populates the plugin information and minimum requirements.
	 */
	function register( ) {
		$this->name = lang_get( 'plugin_graph_title' );
		$this->description = lang_get( 'plugin_graph_description' );
		$this->page = 'config';

		$this->version = '1.0';
		$this->requires = array(
			'MantisCore' => '1.2.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Default plugin configuration.
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
	
	function init() {
		//mantisgraph_autoload();
		spl_autoload_register( array( 'MantisGraphPlugin', 'autoload' ) );
		
		$t_path = config_get_global('plugin_path' ). plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;

		set_include_path(get_include_path() . PATH_SEPARATOR . $t_path);
	}
	
	public static function autoload( $className ) {
		if (class_exists( 'ezcBase' ) ) {
			ezcBase::autoload( $className );
		}
	}
	
	function hooks( ) {
		$hooks = array(
			'EVENT_MENU_SUMMARY' => 'summary_menu',
			'EVENT_SUBMENU_SUMMARY' => 'summary_submenu',
			'EVENT_MENU_FILTER' => 'graph_filter_menu',
		);
		return $hooks;
	}

	function summary_menu( ) {
		return array( '<a href="' . plugin_page( 'summary_jpgraph_page' ) . '">' . plugin_lang_get( 'menu_advanced_summary' ) . '</a>', );
	}	

	function graph_filter_menu( ) {
		return array( '<a href="' . plugin_page( 'bug_graph_page.php' ) . '">' . plugin_lang_get( 'graph_bug_page_link' ) . '</a>', );
	}		
	
	function summary_submenu( ) {
		$t_icon_path = config_get( 'icon_path' );
		return array( '<a href="' . helper_mantis_url( 'summary_page.php' ) . '"><img src="' . $t_icon_path . 'synthese.gif" border="0" alt="" />' . plugin_lang_get( 'synthesis_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_status.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" border="0" alt="" />' . plugin_lang_get( 'status_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_priority.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" border="0" alt="" />' . plugin_lang_get( 'priority_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_severity.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" border="0" alt="" />' . plugin_lang_get( 'severity_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_category.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" border="0" alt="" />' . plugin_lang_get( 'category_link' ) . '</a>',
			'<a href="' . plugin_page( 'summary_graph_imp_resolution.php' ) . '"><img src="' . $t_icon_path . 'synthgraph.gif" border="0" alt="" />' . plugin_lang_get( 'resolution_link' ) . '</a>',
 		);
	}	

}

function mantisgraph_autoload() {
}
