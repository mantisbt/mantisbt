<?php declare(strict_types=1);
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
 * Test cases for Sitemap
 *
 * @package    Tests
 * @subpackage Sitemap
 * @copyright Copyright 2026  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 */

namespace Mantis\tests\Mantis;

/**
 * "Plugin" to test some hooks
 */
class SitemapPlugin {
	/**
	 * EVENT_MENU_MAIN_FRONT hook
	 *
	 * @return array Menu items
	 */
	public static function hookEVENT_MENU_MAIN_FRONT(): array {
		return [
			[
				'url' => 'EVENT_MENU_MAIN_FRONT.php',
				'title' => 'main_link',
				'icon' => 'fa-bullhorn',
			],
		];
	}
	
	/**
	 * EVENT_MENU_MAIN hook
	 *
	 * @return array Menu items
	 */
	public static function hookEVENT_MENU_MAIN(): array {
		return [
			[
				'url' => 'EVENT_MENU_MAIN.php',
				'title' => 'main_link',
				'icon' => 'fa-bullhorn',
			],
		];
	}
}

/**
 * PHPUnit tests for Sitemap
 */
final class SitemapTest extends MantisCoreBase {

	private const MAX_ISSUES = 100;

	private static $libxml_use_errors;
	private static $path;
	private static $short_path;
	private static $news_enabled;
	private static $report_bug_threshold;
	private static $view_changelog_threshold;
	private static $roadmap_view_threshold;
	private static $view_summary_threshold;
	private static $enable_project_documentation;
	private static $wiki_enable;
	private static $manage_project_threshold;
	private static $time_tracking_enabled;
	private static $time_tracking_reporting_threshold;
	private static $main_menu_custom_options;
	private static $antispam_max_event_count;
	private static $test_urls = [];
	private static $issues = [];

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$libxml_use_errors = libxml_use_internal_errors( true );

		self::$path = config_get_global( 'path' );
		self::$short_path = config_get_global( 'short_path' );
		config_set_global( 'short_path', '/' );
		config_set_global( 'path', 'http://localhost/' );

		# Anonymous login
		self::login( true );
	
		global $g_project_override;
		$g_project_override = ALL_PROJECTS;

		# Permit all
		self::$news_enabled = config_get( 'news_enabled' );
		config_set( 'news_enabled', ON );
		self::$report_bug_threshold = config_get( 'report_bug_threshold' );
		config_set( 'report_bug_threshold', ANYBODY );
		self::$view_changelog_threshold = config_get( 'view_changelog_threshold' );
		config_set( 'view_changelog_threshold', ANYBODY );
		self::$roadmap_view_threshold = config_get( 'roadmap_view_threshold' );
		config_set( 'roadmap_view_threshold', ANYBODY );
		self::$view_summary_threshold = config_get( 'view_summary_threshold' );
		config_set( 'view_summary_threshold', ANYBODY );
		self::$enable_project_documentation = config_get( 'enable_project_documentation' );
		config_set( 'enable_project_documentation', ON );
		self::$wiki_enable = config_get_global( 'wiki_enable' );
		config_set_global( 'wiki_enable', ON );
		self::$manage_project_threshold = config_get( 'manage_project_threshold' );
		config_set( 'manage_project_threshold', ANYBODY );
		self::$time_tracking_enabled = config_get( 'time_tracking_enabled' );
		config_set( 'time_tracking_enabled', ON );
		self::$time_tracking_reporting_threshold = config_get( 'time_tracking_reporting_threshold' );
		config_set( 'time_tracking_reporting_threshold', ANYBODY );

		self::$main_menu_custom_options = config_get( 'main_menu_custom_options' );
		config_set( 'main_menu_custom_options', [
			[
				'url' => 'main_menu_custom_options.php',
				'title' => 'main_link',
				'icon' => 'fa-bullhorn',
			],
		] );

		# Hack plugin items
		global $g_plugin_cache;
		$g_plugin_cache['SitemapPlugin'] = new SitemapPlugin;
		event_hook( 'EVENT_MENU_MAIN_FRONT', 'hookEVENT_MENU_MAIN_FRONT', 'SitemapPlugin' );
		event_hook( 'EVENT_MENU_MAIN', 'hookEVENT_MENU_MAIN', 'SitemapPlugin' );

		self::$test_urls = [
			'main_page.php',					# if news_enabled = ON
			'my_view_page.php',					# always
			'view_all_bug_page.php',			# always
			string_get_bug_report_url(),		# if report_bug_threshold
			'changelog_page.php',				# if view_changelog_threshold
			'roadmap_page.php',					# if roadmap_view_threshold
			'summary_page.php',					# if view_summary_threshold
			'proj_doc_page.php',				# if enable_project_documentation = ON
			'wiki.php?type=project&amp;id=0',	# if wiki_enable = ON
			layout_manage_menu_link(),			# if manage_project_threshold
			'billing_page.php',					# if time_tracking_enabled = ON + time_tracking_reporting_threshold
			'EVENT_MENU_MAIN_FRONT.php',		# by SitemapPlugin::hookEVENT_MENU_MAIN_FRONT
			'EVENT_MENU_MAIN.php',				# by SitemapPlugin::hookEVENT_MENU_MAIN
			'main_menu_custom_options.php',		# by main_menu_custom_options
		];

		# Disable issue creation limit
		self::$antispam_max_event_count = config_get( 'antispam_max_event_count' );
		config_set( 'antispam_max_event_count', 0 );

		# Create test issues
		for( $t_index = 0; $t_index < self::MAX_ISSUES; ++ $t_index ) {
			$t_issue_data = new \BugData();
			$t_issue_data->project_id = 1;
			$t_issue_data->summary = 'Test issue ' . $t_index;
			$t_issue_data->description = 'Test issue for Sitemap tests';
			$t_issue_data->category_id = 1;
			self::$issues [] = $t_issue_data->create();
		}
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();

		global $g_project_override;
		$g_project_override = null;

		global $g_plugin_cache;
		unset( $g_plugin_cache['SitemapPlugin'] );
		global $g_event_cache;
		unset( $g_event_cache['EVENT_MENU_MAIN_FRONT']['callbacks']['SitemapPlugin'] );
		unset( $g_event_cache['EVENT_MENU_MAIN']['callbacks']['SitemapPlugin'] );

		foreach( self::$issues as $t_issue ) {
			bug_delete( $t_issue );
		}
		self::$issues = [];

		config_set_global( 'path', self::$path );
		config_set_global( 'short_path', self::$short_path );
		config_set( 'news_enabled', self::$news_enabled );
		config_set( 'report_bug_threshold', self::$report_bug_threshold );
		config_set( 'view_changelog_threshold', self::$view_changelog_threshold );
		config_set( 'roadmap_view_threshold', self::$roadmap_view_threshold );
		config_set( 'view_summary_threshold', self::$view_summary_threshold );
		config_set( 'enable_project_documentation', self::$enable_project_documentation );
		config_set_global( 'wiki_enable', self::$wiki_enable );
		config_set( 'manage_project_threshold', self::$manage_project_threshold );
		config_set( 'time_tracking_enabled', self::$time_tracking_enabled );
		config_set( 'time_tracking_reporting_threshold', self::$time_tracking_reporting_threshold );
		config_set( 'antispam_max_event_count', self::$antispam_max_event_count );
		config_set( 'main_menu_custom_options', self::$main_menu_custom_options );

		libxml_clear_errors();
		libxml_use_internal_errors( self::$libxml_use_errors );
	}

	/**
	 * Tests layout_get_sidebar_items()
	 *
	 * @return void
	 */
	public function testGetSidebarItems(): void {
		# Get Sidebar
		$t_sidebar_items = layout_get_sidebar_items();

		# Check URLs
		foreach( self::$test_urls as $t_url ) {
			$this->assertCount( 1, array_filter( $t_sidebar_items,
				fn( $p_item ) => str_starts_with( $p_item['url'] ?? '', $t_url ) ), "Missed Sidebar URL: '$t_url'"
			);
		}
	}

	/**
	 * Tests Sitemap generation
	 *
	 * @return void
	 */
	public function testGenerateSitemap(): void {
		# Generate Sitemap
		ob_start();
		require_once( dirname( dirname( __DIR__ )  ) . '/sitemap.php' );
		$t_sitemap = ob_get_contents();
		ob_end_clean();

		# Check Sitemap XML format
		$t_xml = new \DOMDocument();
		$t_xml->loadXML( $t_sitemap );
		$t_errors = libxml_get_errors();
		$this->assertEmpty( $t_errors, 'XML parsing errors: ' . implode( "\n",
			array_map( fn( $p_error ) => $p_error->message, $t_errors ) )
		);

		# Validate Sitemap XML schema
		$t_xml->schemaValidate( __DIR__ . '/sitemap.xsd' );
		$t_errors = libxml_get_errors();
		$this->assertEmpty( $t_errors, 'Schema validation errors: ' . implode( "\n",
			array_map( fn( $p_error ) => sprintf( '[Line %d] %s: %s',
				$p_error->line,
				$p_error->level === LIBXML_ERR_WARNING ? 'WARNING' : 'ERROR',
				trim( $p_error->message ) ), $t_errors
			) )
		);

		# Extract URLs
		$t_xpath = new \DOMXPath( $t_xml );
		$t_xpath->registerNamespace( 's', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
		$t_locs = array_map( fn( $p_node ) => trim( $p_node->nodeValue ),
			iterator_to_array( $t_xpath->query('//s:url/s:loc') )
		);
		$this->assertCount( count( self::$issues ) + count( layout_get_sidebar_items() ), $t_locs, 'The wrong number of URLs' );

		# Check URLs
		$t_path = config_get_global( 'path' );
		$t_default_home_page = config_get_global( 'default_home_page' );
		$this->assertContains( $t_path . $t_default_home_page, $t_locs, "Missed Home URL: '$t_default_home_page'" );
		foreach( self::$test_urls as $t_url ) {
			$this->assertContains( $t_path . $t_url, $t_locs , "Missed Sidebar URL: '$t_url'" );
		}
	}
}
