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

use BugData;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * PHPUnit tests for Sitemap
 */
final class SitemapTest extends MantisCoreBase {

	private const MAX_ISSUES = 50;
	private const MAX_NEWS = 50;

	private static $libxml_use_errors;
	private static $news_enabled;
	private static $report_bug_threshold;
	private static $view_changelog_threshold;
	private static $roadmap_view_threshold;
	private static $view_summary_threshold;
	private static $enable_project_documentation;
	private static $manage_project_threshold;
	private static $time_tracking_enabled;
	private static $time_tracking_reporting_threshold;
	private static $main_menu_custom_options;
	private static $antispam_max_event_count;
	private static $sidebar_urls = [];
	private static $issues = [];
	private static $news = [];

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$libxml_use_errors = libxml_use_internal_errors( true );

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
		$g_plugin_cache['SitemapPlugin'] = new class {
			/**
			 * EVENT_MENU_MAIN_FRONT hook
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
		};
		event_hook( 'EVENT_MENU_MAIN_FRONT', 'hookEVENT_MENU_MAIN_FRONT', 'SitemapPlugin' );
		event_hook( 'EVENT_MENU_MAIN', 'hookEVENT_MENU_MAIN', 'SitemapPlugin' );

		self::$sidebar_urls = [
			'main_page.php',					# if news_enabled = ON
			'my_view_page.php',					# always
			'view_all_bug_page.php',			# always
			string_get_bug_report_url(),		# if report_bug_threshold
			'changelog_page.php',				# if view_changelog_threshold
			'roadmap_page.php',					# if roadmap_view_threshold
			'summary_page.php',					# if view_summary_threshold
			'proj_doc_page.php',				# if enable_project_documentation = ON
			'wiki.php?',						# if wiki_enable = ON + wiki_engine
			layout_manage_menu_link(),			# if manage_project_threshold
			'billing_page.php',					# if time_tracking_enabled = ON + time_tracking_reporting_threshold
			'main_menu_custom_options.php',		# by main_menu_custom_options
		];

		# Disable issue creation limit
		self::$antispam_max_event_count = config_get( 'antispam_max_event_count' );
		config_set( 'antispam_max_event_count', 0 );

		# Create test issues
		for( $t_index = 0; $t_index < self::MAX_ISSUES; ++ $t_index ) {
			$t_issue_data = new BugData();
			$t_issue_data->project_id = 1;
			$t_issue_data->summary = 'Test issue ' . $t_index;
			$t_issue_data->description = 'Test issue for Sitemap tests';
			self::$issues [] = $t_issue_data->create();
		}
		
		# Create test news
		for( $t_index = 0; $t_index < self::MAX_NEWS; ++ $t_index ) {
			self::$news [] = news_create( ALL_PROJECTS, 0, VS_PUBLIC, 0, 'Test news ' . $t_index, 'Test news for Sitemap tests' );
		}
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();

		global $g_project_override;
		$g_project_override = null;

		# un-Hack plugin items
		global $g_plugin_cache;
		unset( $g_plugin_cache['SitemapPlugin'] );
		global $g_event_cache;
		unset( $g_event_cache['EVENT_MENU_MAIN_FRONT']['callbacks']['SitemapPlugin'] );
		unset( $g_event_cache['EVENT_MENU_MAIN']['callbacks']['SitemapPlugin'] );

		# Clear test news
		foreach( self::$news as $t_news ) {
			news_delete( $t_news );
		}
		self::$news = [];

		# Clear test issues
		foreach( self::$issues as $t_issue ) {
			bug_delete( $t_issue );
		}
		self::$issues = [];

		config_set( 'news_enabled', self::$news_enabled );
		config_set( 'report_bug_threshold', self::$report_bug_threshold );
		config_set( 'view_changelog_threshold', self::$view_changelog_threshold );
		config_set( 'roadmap_view_threshold', self::$roadmap_view_threshold );
		config_set( 'view_summary_threshold', self::$view_summary_threshold );
		config_set( 'enable_project_documentation', self::$enable_project_documentation );
		config_set( 'manage_project_threshold', self::$manage_project_threshold );
		config_set( 'time_tracking_enabled', self::$time_tracking_enabled );
		config_set( 'time_tracking_reporting_threshold', self::$time_tracking_reporting_threshold );
		config_set( 'antispam_max_event_count', self::$antispam_max_event_count );
		config_set( 'main_menu_custom_options', self::$main_menu_custom_options );

		libxml_clear_errors();
		libxml_use_internal_errors( self::$libxml_use_errors );
	}

	/**
	 * Generate Sitemap
	 *
	 * @param array $p_headers Optional HTTP headers
	 * @return Response HTTP response
	 */
	private function get_sitemap( array $p_headers = [] ): Response {
		return ( new Client( [
			'allow_redirects' => false,
			'http_errors' => false,
			'headers' => $p_headers,
		] ) )->get( config_get_global( 'path' ). 'sitemap.php' );
	}

	/**
	 * Get Sitemap URLs
	 *
	 * @param Response $p_response HTTP response
	 * @return array Sitemap URLs
	 */
	private function parse_sitemap_urls( Response $p_response ): array {
		$t_sitemap = $p_response->getBody()->getContents();
		$this->assertEquals( HTTP_STATUS_SUCCESS, $p_response->getStatusCode(), "The wrong HTTP response. Response body:\n$t_sitemap" );
		$this->assertNotEmpty( $t_sitemap, 'Empty HTTP response' );

		# Check Sitemap XML format
		$t_xml = new DOMDocument();
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
		$t_xpath = new DOMXPath( $t_xml );
		$t_xpath->registerNamespace( 's', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
		return array_map( fn( $p_node ) => trim( $p_node->nodeValue ),
			iterator_to_array( $t_xpath->query('//s:url/s:loc') )
		);
	}

	/**
	 * Tests layout_get_sidebar_items()
	 *
	 * @return void
	 */
	public function testGetSidebarItems(): void {
		$t_sidebar_items = layout_get_sidebar_items();

		# Include "plugin" items
		$t_urls = self::$sidebar_urls;
		$t_urls [] = 'EVENT_MENU_MAIN_FRONT.php';
		$t_urls [] = 'EVENT_MENU_MAIN.php';

		# Check URLs
		foreach( $t_urls as $t_url ) {
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
	public function testSitemap(): void {
		$t_sitemap = $this->get_sitemap();
		$t_urls = $this->parse_sitemap_urls( $t_sitemap );

		# Check URLs
		$t_path = config_get_global( 'path' );
		$t_home_page = $t_path . config_get_global( 'default_home_page' );
		$this->assertContains( $t_home_page, $t_urls, "Missed Home URL: '$t_home_page'" );
		foreach( self::$sidebar_urls as $t_test_url ) {
			$t_url = $t_path . $t_test_url;
			$this->assertCount( 1, array_filter( $t_urls,
				fn( $p_item ) => str_starts_with( $p_item, $t_url ) ), "Missed Sidebar URL: '$t_url'"
			);
		}
		foreach( self::$issues as $t_issue ) {
			$t_url = $t_path . string_get_bug_view_url( $t_issue );
			$this->assertContains( $t_url, $t_urls, "Missed Issue URL: '$t_url'" );
		}
		foreach( self::$news as $t_news ) {
			$t_url = $t_path . 'news_view_page.php?news_id=' . $t_news;
			$this->assertContains( $t_url, $t_urls, "Missed News URL: '$t_url'" );
		}
	}

	/**
	 * Tests unmodified Sitemap
	 *
	 * @return void
	 */
	public function testSitemapIfModifiedSince(): void {
		$t_sitemap = $this->get_sitemap( [ 'If-Modified-Since' => gmdate( 'D, d M Y H:i:s \G\M\T', time() ) ] );

		$this->assertEquals( HTTP_STATUS_NOT_MODIFIED, $t_sitemap->getStatusCode(), 'The wrong HTTP response' );
		$this->assertEmpty( $t_sitemap->getBody()->getContents() );
	}

	/**
	 * Tests Sitemap with a disabled anonymous user
	 *
	 * @return void
	 */
	public function testSitemapWithDisabledAnonymousUser(): void {
		$t_anonymous = user_get_id_by_name( auth_anonymous_account() );
		user_set_fields( $t_anonymous, [ 'enabled' => false, 'protected' => true ] );
		$t_sitemap = $this->get_sitemap();
		user_set_fields( $t_anonymous, [ 'enabled' => true, 'protected' => true ] );

		$this->assertEquals( HTTP_STATUS_NOT_FOUND, $t_sitemap->getStatusCode(), 'The wrong HTTP response' );
		$this->assertEmpty( $t_sitemap->getBody()->getContents() );
	}

	/**
	 * Tests Sitemap with denied view_bug_threshold
	 *
	 * @return void
	 */
	public function testSitemapWithViewIssuesThreshold(): void {
		config_set( 'view_bug_threshold', NOBODY );
		$t_sitemap = $this->get_sitemap();
		config_set( 'view_bug_threshold', ANYBODY );

		# Check URLs
		$t_urls = $this->parse_sitemap_urls( $t_sitemap );
		$t_path = config_get_global( 'path' );
		foreach( self::$issues as $t_issue ) {
			$t_url = $t_path . string_get_bug_view_url( $t_issue );
			$this->assertNotContains( $t_url, $t_urls, "Extra Issue URL: '$t_url'" );
		}
	}

	/**
	 * Tests Sitemap with denied limit_view_unless_threshold
	 *
	 * @return void
	 */
	public function testSitemapWithLimitViewUnlessThreshold(): void {
		config_set( 'limit_view_unless_threshold', NOBODY );
		$t_sitemap = $this->get_sitemap();
		config_set( 'limit_view_unless_threshold', ANYBODY );

		# Check URLs
		$t_urls = $this->parse_sitemap_urls( $t_sitemap );
		$t_path = config_get_global( 'path' );
		foreach( self::$issues as $t_issue ) {
			$t_url = $t_path . string_get_bug_view_url( $t_issue );
			$this->assertNotContains( $t_url, $t_urls, "Extra Issue URL: '$t_url'" );
		}
	}

	/**
	 * Tests Sitemap with disabled news
	 *
	 * @return void
	 */
	public function testSitemapWithDisabledNews(): void {
		config_set( 'news_enabled', OFF );
		$t_sitemap = $this->get_sitemap();
		config_set( 'news_enabled', ON );

		# Check URLs
		$t_urls = $this->parse_sitemap_urls( $t_sitemap );
		$t_path = config_get_global( 'path' );
		foreach( self::$news as $t_news ) {
			$t_url = $t_path . 'news_view_page.php?news_id=' . $t_news;
			$this->assertNotContains( $t_url, $t_urls, "Extra News URL: '$t_url'" );
		}
	}
}
