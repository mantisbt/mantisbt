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

namespace Mantis\plugins\MantisCoreFormatting\tests;

use Generator;
use MantisMarkdown;
use PHPUnit\Framework\TestCase;
use TypeError;

require_once( dirname( __DIR__, 3 ) . '/tests/TestConfig.php' );

# MantisBT Core API
require_mantis_core();

/**
 * Test for the MantisMarkdown parser
 *
 * @package Tests
 * @subpackage MantisMarkdown
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 *
 * @covers MantisMarkdown
 */
class MarkdownTest extends TestCase {

	private ?MantisMarkdown $parser = null;

	protected function setUp(): void {
		$this->parser = new MantisMarkdown( ON, ON );
	}

	/**
	 * The "getInstance" method returns the same instance.
	 */
	public function testGetInstanceReturnsTheSameInstance(): void {
		$t_instance_1 = MantisMarkdown::getInstance();
		$t_instance_2 = MantisMarkdown::getInstance();

		$this->assertSame( $t_instance_1, $t_instance_2 );
	}

  /**
   * The default value for "process_urls", "process_buglinks" and
   * "process_prlinks" is the value of the constant "OFF" (0).
   */
	public function testIfDefaultConfigurationValueIsOff(): void {
		$t_parser = new MantisMarkdown();

		$this->assertSame( OFF, $t_parser->getConfigProcessUrls() );
		$this->assertSame( OFF, $t_parser->getConfigProcessBugLinks() );
		$this->assertSame( OFF, $t_parser->getConfigProcessPrLinks() );
	}

	/**
	 * The instance can be configured via constructor parameters and the
	 * values from the arguments are correctly applied to their switches.
	 */
	public function testCanConfigure(): void {
		$t_parser = new MantisMarkdown( ON );
		$this->assertSame( ON, $t_parser->getConfigProcessUrls() );
		$this->assertSame( OFF, $t_parser->getConfigProcessBugLinks() );
		$this->assertSame( OFF, $t_parser->getConfigProcessPrLinks() );

		$t_parser = new MantisMarkdown( OFF, ON );
		$this->assertSame( OFF, $t_parser->getConfigProcessUrls() );
		$this->assertSame( ON, $t_parser->getConfigProcessBugLinks() );
		$this->assertSame( OFF, $t_parser->getConfigProcessPrLinks() );

		$t_parser = new MantisMarkdown( ON, ON );
		$this->assertSame( ON, $t_parser->getConfigProcessUrls() );
		$this->assertSame( ON, $t_parser->getConfigProcessBugLinks() );
		$this->assertSame( OFF, $t_parser->getConfigProcessPrLinks() );

		$t_parser = new MantisMarkdown( ON, OFF, ON );
		$this->assertSame( ON, $t_parser->getConfigProcessUrls() );
		$this->assertSame( OFF, $t_parser->getConfigProcessBugLinks() );
		$this->assertSame( ON, $t_parser->getConfigProcessPrLinks() );

		$t_parser = new MantisMarkdown( OFF, ON, OFF );
		$this->assertSame( OFF, $t_parser->getConfigProcessUrls() );
		$this->assertSame( ON, $t_parser->getConfigProcessBugLinks() );
		$this->assertSame( OFF, $t_parser->getConfigProcessPrLinks() );
	}

  /**
   * The configuration value for "process_urls", "process_buglinks" and
   * "process_prlinks" must be the value of the constant "ON" (1) or "OFF" (0).
   * Its "OFF" (0) otherwise.
   */
	public function testCanNotConfigureInvalidValues(): void {
		$t_expected = OFF;

		foreach( [-1, 2, 3] as $t_value ) {
			$t_parser = new MantisMarkdown( $t_value, $t_value );
			$this->assertSame( $t_expected, $t_parser->getConfigProcessUrls() );
			$this->assertSame( $t_expected, $t_parser->getConfigProcessBugLinks() );
			$this->assertSame( $t_expected, $t_parser->getConfigProcessPrLinks() );
		}
	}

	/**
	 * The Parser class implements the CommonMark specifications for headers,
	 * as Parsedown does not.
	 *
	 * @see https://spec.commonmark.org/0.31.2/#example-62
	 * @see https://parsedown.org/demo
	 *
	 * @dataProvider provideHeaders
	 */
	public function testCommonMarkImplementationOfHeaders( string $p_sample, string $p_expected ): void {
		$this->assertSame( $p_expected, $this->parser->text( $p_sample ) );
	}

	/**
	 * The switch `process_urls` should only affect "unmarked" email addresses.
	 * Email addresses noted within Markdown tags, should always be converted
	 * to links.
	 *
	 * - "<user@example.com>" - always converted to a link.
	 * - "user@example.com" - only converted to a link if "process_urls = ON".
	 *
	 * @dataProvider provideEmails
	 */
	public function testProcessEmails( string $p_sample, int $p_config, string $p_expected ): void {
		$t_parser = new MantisMarkdown( $p_config );
		$this->assertSame( $p_expected, $t_parser->text( $p_sample ) );
	}

	/**
	 * The configuration of `process_urls` should only affect "unmarked" URLs.
	 *
	 * This tests for the presence of the string `<a href=…` to avoid conflicts
	 * with the attributes of the link.
	 *
	 * URLs noted within Markdown tags, should always be converted to links.
	 *
	 * - "<https://example.com>" - always converted to a link.
	 * - "[Text](https://example.com)" - always converted to a link.
	 * - "https://example.com" - only converted to a link if "process_urls = ON".
	 *
	 * @todo take care of input of HTML links.
	 *
	 * @dataProvider provideUrls
	 */
	public function testProcessUrls( string $p_sample, int $p_config, string $p_needle, bool $p_contains ): void {
		$t_parser = new MantisMarkdown( $p_config );

		if( $p_contains ) {
			$this->assertStringContainsString( $p_needle, $t_parser->text( $p_sample ) );
		} else {
			$this->assertStringNotContainsString( $p_needle, $t_parser->text( $p_sample ) );
		}
	}

	/**
	 * Inline code is replaced by its hash value.
	 */
	public function testInlineCodeIsReplacedByIsHashValue(): void {
		$t_code = 'const foo = "bar"';
		$t_hash = $this->parser->hash( $t_code );
		$t_markdown = 'lorem `' . $t_code . '` ipsum';
		$t_expected = '<p>lorem <code>' . $t_hash . '</code> ipsum</p>';

		$this->assertSame( $t_expected, $this->parser->text( $t_markdown ) );
		$this->assertArrayHasKey( $t_hash, $this->parser->getCodeblocks() );
		$this->assertSame( $t_code, $this->parser->getCodeblocks()[$t_hash] );
	}

	/**
	 * Inline code is restored when HTML markup is finalized.
	 */
	public function testInlineCodeIsRestored(): void {
		$t_code = '<div style="padding: 2rem">@administrator is mentioned</div>';
		$t_code_output = $this->parser->disarmCode( $t_code );
		$t_markdown = 'lorem `' . $t_code . '` ipsum';
		$t_expected = '<p>lorem <code>' . $t_code_output . '</code> ipsum</p>';

		$this->assertSame( $t_expected, $this->parser->convert( $t_markdown, true ) );
	}

	/**
	 * A code block is replaced by its hash value.
	 */
	public function testCodeBlockIsReplacedByItsHashValue(): void {
		$t_code = <<<EOD
 const theTruth = () => {
    fetch('https://api.chucknorris.io/jokes/random')
        .then((response) => response.json())
        .then((json) => {
            console.log(json.value);
        })
}
theTruth();
EOD;

		$t_input = <<<EOD
lorem ipsum
```
$t_code
```
dolor sit amet
EOD;

		$t_hash = $this->parser->hash( $t_code );
		$t_expected = <<<EOD
<p>lorem ipsum</p>
<pre><code>$t_hash</code></pre>
<p>dolor sit amet</p>
EOD;

		$this->assertSame( $t_expected, $this->parser->text( $t_input ) );
		$this->assertArrayHasKey( $t_hash, $this->parser->getCodeblocks() );
		$this->assertSame( $t_code, $this->parser->getCodeblocks()[$t_hash] );
	}

	/**
	 * A code block is restored when HTML markup is finalized.
	 */
	public function testCodeBlockIsRestored(): void {
		$t_code = <<<EOD
 const theTruth = () => {
    fetch('https://api.chucknorris.io/jokes/random')
        .then((response) => response.json())
        .then((json) => {
            console.log(json.value);
        })
}
theTruth();
EOD;

		$t_markdown = <<<EOD
lorem ipsum
```
$t_code
```
dolor sit amet
EOD;

		$t_code_output = $this->parser->disarmCode( $t_code );
		$t_expected = <<<EOD
<p>lorem ipsum</p>
<pre><code>$t_code_output</code></pre>
<p>dolor sit amet</p>
EOD;
		$this->assertSame( $t_expected, $this->parser->convert( $t_markdown, true ) );
	}

	/**
	 * Tables have the wanted CSS class.
	 *
	 * @todo get the class from the parser "->getTableClass();"
	 */
	public function testTableClassIsApplied(): void {
		$t_table = <<<EOD
| header |
| ---    |
| cell   |
EOD;

		$this->assertStringContainsString( 'class="table table-nonfluid"', $this->parser->text( $t_table ) );
	}

	/**
	 * The "convert()" method uses the corresponding method from Parsedown depending on
	 * the boolean "p_multiline" argument. Either "text" or "line".
	 *
	 * - "false" => Parsdown::linie(); Plain line, no surrounding tags.
	 * - "true" => Parsdown::text(); A paragraph is expected.
	 */
	public function testConvertToLineOrParagraph(): void {
		$t_sample = 'lorem ipsum dolor';

		$this->assertSame( $t_sample, $this->parser->convert( $t_sample ) );
		$this->assertSame( '<p>' . $t_sample . '</p>', $this->parser->convert( $t_sample, true ) );
	}

	public static function provideHeaders(): Generator {
		# Valid headers
		yield  'valid: # foo' => ['# foo', '<h1>foo</h1>'];
		yield  'valid: ## foo' => ['## foo', '<h2>foo</h2>'];
		yield  'valid: ### foo' => ['### foo', '<h3>foo</h3>'];
		yield  'valid: #### foo' => ['#### foo', '<h4>foo</h4>'];
		yield  'valid: ##### foo' => ['##### foo', '<h5>foo</h5>'];
		yield  'valid: ###### foo' => ['###### foo', '<h6>foo</h6>'];

		# Valid headers with tab after "#"
		yield  'valid: #{tab}foo' => ["#\tfoo", '<h1>foo</h1>'];
		yield  'valid: ##{tab}foo' => ["##\tfoo", '<h2>foo</h2>'];
		yield  'valid: ###{tab}foo' => ["###\tfoo", '<h3>foo</h3>'];
		yield  'valid: ####{tab}foo' => ["####\tfoo", '<h4>foo</h4>'];
		yield  'valid: #####{tab}foo' => ["#####\tfoo", '<h5>foo</h5>'];
		yield  'valid: ######{tab}foo' => ["######\tfoo", '<h6>foo</h6>'];

		# Leading spaces {0,3}
		yield  'valid: \s{1}# foo' => [' # foo', '<h1>foo</h1>'];
		yield  'valid: \s{2}## foo' => ['  ## foo', '<h2>foo</h2>'];
		yield  'valid: \s{3}### foo' => ['   ### foo', '<h3>foo</h3>'];
		yield  'valid: \s{1}#{tab}foo' => [" #\tfoo", '<h1>foo</h1>'];
		yield  'valid: \s{2}##{tab}foo' => ["  ##\tfoo", '<h2>foo</h2>'];
		yield  'valid: \s{3}###{tab}foo' => ["   ###\tfoo", '<h3>foo</h3>'];

		# Invalid headers
		yield  'invalid: ####### foo' => ['####### foo', '<p>####### foo</p>'];
		yield  'invalid: #foo' => ['#foo', '<p>#foo</p>'];
		yield  'invalid: #123' => ['#123', '<p>#123</p>'];
	}

	/**
	 * The samples may look a bit too verbose, and the marked URLs are already
	 * tested by the Parsedown tests. But they ensure that the Parsedown process is
	 * not affected in any way, no matter what the value of "process_urls" is.
	 */
	public static function provideEmails(): Generator {
		yield 'process_urls = ON; lorem <user@exmaple.com> ipsum' => [
			'lorem <user@exmaple.com> ipsum',
			ON,
			'<p>lorem <a href="mailto:user@exmaple.com">user@exmaple.com</a> ipsum</p>'
		];

		yield 'process_urls = ON; <user@exmaple.com>' => [
			'<user@exmaple.com>',
			ON,
			'<p><a href="mailto:user@exmaple.com">user@exmaple.com</a></p>'
		];

		yield 'process_urls = OFF; lorem <user@exmaple.com> ipsum' => [
			'lorem <user@exmaple.com> ipsum',
			OFF,
			'<p>lorem <a href="mailto:user@exmaple.com">user@exmaple.com</a> ipsum</p>'
		];

		yield 'process_urls = OFF; <user@exmaple.com>' => [
			'<user@exmaple.com>',
			OFF,
			'<p><a href="mailto:user@exmaple.com">user@exmaple.com</a></p>'
		];

		yield 'process_urls = ON; lorem user@exmaple.com ipsum' => [
			'lorem user@exmaple.com ipsum',
			ON,
			'<p>lorem <a href="mailto:user@exmaple.com">user@exmaple.com</a> ipsum</p>'
		];

		yield 'process_urls = ON; user@exmaple.com' => [
			'user@exmaple.com',
			ON,
			'<p><a href="mailto:user@exmaple.com">user@exmaple.com</a></p>'
		];

		yield 'process_urls = OFF; lorem user@exmaple.com ipsum' => [
			'lorem user@exmaple.com ipsum',
			OFF,
			'<p>lorem user@exmaple.com ipsum</p>'
		];

		yield 'process_urls = OFF; user@exmaple.com' => [
			'user@exmaple.com',
			OFF,
			'<p>user@exmaple.com</p>'
		];
	}

	/**
	 * The samples may look a bit too verbose, and the marked URLs are already
	 * tested by the Parsedown tests. But they ensure that the Parsedown process is
	 * not affected in any way, no matter what the value of "process_urls" is.
	 */
	public static function provideUrls(): Generator {
		yield 'process_urls = ON; lorem <https://exmaple.com> ipsum' => [
			'lorem <https://exmaple.com> ipsum',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = ON; <https://exmaple.com>' => [
			'<https://exmaple.com>',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = OFF; lorem <https://exmaple.com> ipsum' => [
			'lorem <https://exmaple.com> ipsum',
			OFF,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = OFF; <https://exmaple.com>' => [
			'<https://exmaple.com>',
			OFF,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = ON; lorem [link](https://exmaple.com) ipsum' => [
			'lorem [link](https://exmaple.com) ipsum',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = ON; [link](https://exmaple.com)' => [
			'[link](https://exmaple.com)',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = OFF; lorem [link](https://exmaple.com) ipsum' => [
			'lorem [link](https://exmaple.com) ipsum',
			OFF,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = OFF; [link](https://exmaple.com)' => [
			'[link](https://exmaple.com)',
			OFF,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = ON; lorem https://exmaple.com ipsum' => [
			'lorem https://exmaple.com ipsum',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = ON; https://exmaple.com' => [
			'https://exmaple.com',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = OFF; lorem https://exmaple.com ipsum' => [
			'lorem https://exmaple.com ipsum',
			OFF,
			'<a href="https://exmaple.com"',
			false
		];

		yield 'process_urls = OFF; https://exmaple.com' => [
			'https://exmaple.com',
			OFF,
			'<a href="https://exmaple.com"',
			false
		];
	}
}
