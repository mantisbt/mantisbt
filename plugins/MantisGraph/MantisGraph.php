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
	 * ChartJS colorschemes plugin
	 * @see https://nagix.github.io/chartjs-plugin-colorschemes/
	 */
	const CHARTJS_COLORSCHEMES_VERSION = '0.3.0';
	const CHARTJS_COLORSCHEMES_HASH = 'sha256-x/VBrVFabQZyhV1G+oKCy98gkqf/pW5aVAhaBRxJ/x4=';

	/**
	 * A method that populates the plugin information and minimum requirements.
	 * @return void
	 */
	function register() {
		$this->name = lang_get( 'plugin_graph_title' );
		$this->description = lang_get( 'plugin_graph_description' );
		$this->page = '';

		$this->version = MANTIS_VERSION;
		$this->requires = array(
			'MantisCore' => '2.0.0',
		);

		$this->author = 'MantisBT Team';
		$this->contact = 'mantisbt-dev@lists.sourceforge.net';
		$this->url = 'http://www.mantisbt.org';
	}

	/**
	 * Plugin initialization
	 * @return void
	 */
	function init() {
		plugin_require_api( 'core/graph_api.php' );
		plugin_require_api( 'core/Period.php' );
		require_api( 'summary_api.php' );
	}

	/**
	 * Default plugin configuration.
	 * @return array
	 */
	function config() {
		return array();
	}

	/**
	 * plugin hooks
	 * @return array
	 */
	function hooks() {
		$t_hooks = array(
			'EVENT_REST_API_ROUTES' => 'routes',
			'EVENT_LAYOUT_RESOURCES' => 'resources',
			'EVENT_CORE_HEADERS' => 'csp_headers',
			'EVENT_SUBMENU_SUMMARY' => 'summary_submenu',
			'EVENT_MENU_FILTER' => 'graph_filter_menu'
		);
		return $t_hooks;
	}

	/**
	 * Add the RESTful routes that are handled by this plugin.
	 *
	 * @param $p_event_name The event name
	 * @param $p_event_args The event arguments
	 * @return void
	 */
	function routes( $p_event_name, $p_event_args ) {
		$t_app = $p_event_args['app'];
		$t_app->group( plugin_route_group(), function() use ( $t_app ) {
			$t_app->get( '/reporters', function( $req, $res, $args ) {
				if( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
					$t_report_associative = create_reporter_summary();
					$t_report = array();

					foreach( $t_report_associative as $t_name => $t_count ) {
						$t_report[] = array( "name" => $t_name, "count" => $t_count );
					}

					return $res->withStatus( HTTP_STATUS_SUCCESS )->withJson( $t_report );
				}

				return $res->withStatus( HTTP_STATUS_FORBIDDEN );
			} );
		} );
	}

	/**
	 * Add Content-Security-Policy directives that are needed to load scripts for CDN.
	 * @return void
	 */
	function csp_headers() {
		if( config_get_global( 'cdn_enabled' ) == ON ) {
			http_csp_add( 'script-src', 'https://cdnjs.cloudflare.com' );
			http_csp_add( 'script-src', 'https://cdn.jsdelivr.net' );
		}
	}

	/**
	 * generate graph filter menu
	 * @return array
	 */
	function graph_filter_menu() {
		if( access_has_project_level( config_get( 'view_summary_threshold' ) ) ) {
			return array( '<a class="btn btn-sm btn-primary btn-white btn-round" href="' .
				plugin_page( 'issues_trend_page.php' ) . '">' . plugin_lang_get( 'issue_trends_link' ) . '</a>', );
		} else {
			return '';
		}
	}

	/**
	 * Include javascript files for chart.js
	 * @return void
	 */
	function resources() {
		if( current( explode( '/', gpc_get_string( 'page', '' ) ) ) === $this->basename ) {
			if( config_get_global( 'cdn_enabled' ) == ON ) {
				html_javascript_cdn_link('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/' . CHARTJS_VERSION . '/Chart.min.js', CHARTJS_HASH);
				html_javascript_cdn_link('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/' . CHARTJS_VERSION . '/Chart.bundle.min.js', CHARTJSBUNDLE_HASH);
				html_javascript_cdn_link('https://cdn.jsdelivr.net/npm/chartjs-plugin-colorschemes@' . self::CHARTJS_COLORSCHEMES_VERSION . '/dist/chartjs-plugin-colorschemes.min.js', self::CHARTJS_COLORSCHEMES_HASH );
			} else {
				echo '<script type="text/javascript" src="' . plugin_file('Chart-' . CHARTJS_VERSION . '.min.js') . '"></script>';
				echo '<script type="text/javascript" src="' . plugin_file('Chart.bundle-' . CHARTJS_VERSION . '.min.js') . '"></script>';
				echo '<script type="text/javascript" src="' . plugin_file( 'chartjs-plugin-colorschemes.min.js' ) . '"></script>';
			}
			echo '<script type="text/javascript" src="' . plugin_file("MantisGraph.js") . '"></script>';
		}
	}

	/**
	 * generate summary submenu
	 * @return array
	 */
	function summary_submenu() {
		$t_filter = summary_get_filter();
		$t_filter_param = filter_get_temporary_key_param( $t_filter );
		$t_param_page = explode( '/', gpc_get_string( 'page', '' ) );
		$t_plugin_page_current = end( $t_param_page );

		# Core should implement the automatic highlighting of active links, but that requires
		# changing the format of the menu and submenu events.
		# For now, it's responsability of the plugin to detect and render each link properly.
		$t_menu_items = array();
		$t_menu_items[] = array( 'icon' => 'fa-table', 'title' => plugin_lang_get( 'synthesis_link' ),
			'url' => helper_url_combine( helper_mantis_url( 'summary_page.php' ), $t_filter_param ) );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => lang_get( 'by_developer' ),
			'url' => helper_url_combine( plugin_page( 'developer_graph.php' ), $t_filter_param ),
			'plugin_page' => 'developer_graph.php' );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => lang_get( 'by_reporter' ),
			'url' => helper_url_combine( plugin_page( 'reporter_graph.php' ), $t_filter_param ),
			'plugin_page' => 'reporter_graph.php' );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => plugin_lang_get( 'status_link' ),
			'url' => helper_url_combine( plugin_page( 'status_graph.php' ), $t_filter_param ),
			'plugin_page' => 'status_graph.php' );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => plugin_lang_get( 'resolution_link' ),
			'url' => helper_url_combine( plugin_page( 'resolution_graph.php' ), $t_filter_param ),
			'plugin_page' => 'resolution_graph.php' );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => plugin_lang_get( 'priority_link' ),
			'url' => helper_url_combine( plugin_page( 'priority_graph.php' ), $t_filter_param ),
			'plugin_page' => 'priority_graph.php' );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => plugin_lang_get( 'severity_link' ),
			'url' => helper_url_combine( plugin_page( 'severity_graph.php' ), $t_filter_param ),
			'plugin_page' => 'severity_graph.php' );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => plugin_lang_get( 'category_link' ),
			'url' => helper_url_combine( plugin_page( 'category_graph.php' ), $t_filter_param ),
			'plugin_page' => 'category_graph.php' );

		$t_menu_items[] = array( 'icon' => 'fa-bar-chart', 'title' => plugin_lang_get( 'issue_trends_link' ),
			'url' => helper_url_combine( plugin_page( 'issues_trend_graph.php' ), $t_filter_param ),
			'plugin_page' => 'issues_trend_graph.php' );

		$t_html_links = array();
		foreach( $t_menu_items as $t_item ) {
			$t_class_active = '';
			if( isset( $t_item['plugin_page'] ) )  {
				if( $t_item['plugin_page'] == $t_plugin_page_current ) {
					$t_class_active = ' active';
				}
			} else {
				if( 'summary_page.php' == basename( $_SERVER['SCRIPT_NAME'] ) ) {
					$t_class_active = ' active';
				}
			}
			$t_html = '<a class="btn btn-sm btn-primary btn-white' . $t_class_active . '" href="' . $t_item['url'] . '">';
			$t_html .= ' <i class="fa ' . $t_item['icon'] . '"></i> ';
			$t_html .= $t_item['title'] . '</a>';
			$t_html_links[] = $t_html;
		}
		return $t_html_links;
	}
}
