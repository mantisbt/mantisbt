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

require_once( dirname( __FILE__, 2 ) . '/../../tests/TestConfig.php' );
require_once( dirname( __FILE__, 2 ) . '/core/MantisMarkdown.php' );

# MantisBT Core API
require_mantis_core();

/**
 * Mantis markdown handling test cases.
 *
 * @package Tests
 * @subpackage Markdown
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link https://www.mantisbt.org
 */
class MarkdownTest extends PHPUnit\Framework\TestCase {

	private ?MantisMarkdown $parser = null;

	protected function setUp(): void {
		$this->parser = new MantisMarkdown();
	}

	/**
	 * The configuration for "process_urls" can be changed during runtime.
	 */
	public function testCanSetConfigProcessUrls(): void {
		foreach ([OFF, ON, OFF] as $t_expected ) {
			$this->parser->setConfigProcessUrls( $t_expected );
			$this->assertSame( $t_expected, $this->parser->getConfigProcessUrls() );
		}
	}

	/**
	 * The configuration value for "process_urls" must be the value of the
	 * constant "ON" (1) or "OFF" (0),
	 */
	public function testCanNotSetInvalidValuesAsConfigProcessUrls(): void {
		$t_expected = OFF;
		$this->parser->setConfigProcessUrls( $t_expected );

		foreach ([-1, 2, 3] as $t_value ) {
			$this->parser->setConfigProcessUrls( $t_value );
			$this->assertSame( $t_expected, $this->parser->getConfigProcessUrls() );
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
	public function testCommonMarkImplementationOfHeaders(string $p_sample, string $p_expected): void {
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
	public function testProcessEmails(string $p_sample, int $p_config, string $p_expected): void {
		$this->parser->setConfigProcessUrls( $p_config );
		$this->assertSame( $p_expected, $this->parser->text( $p_sample));
	}

	/**
	 * The configuration of `process_urls` should only affect "unmarked" URLs.
	 *
	 * This tests for the presence of the string `<a href=…` to avoid conflicts
	 * with the attributes of the link.
	 *
	 * URLs noted within Markdown tags, should always be converted
	 * to links.
	 *
	 * - "<https://example.com>" - always converted to a link.
	 * - "[Text](https://example.com)" - always converted to a link.
	 * - "https://example.com" - only converted to a link if "process_urls = ON".
	 *
	 * @todo take care of input of HTML links?
	 *
	 * @dataProvider provideUrls
	 */
	public function testProcessUrls(string $p_sample, int $p_config, string $p_needle, bool $p_contains): void
	{
		$this->parser->setConfigProcessUrls($p_config);

		if( $p_contains ) {
			$this->assertStringContainsString( $p_needle, $this->parser->text( $p_sample ) );
		} else {
			$this->assertStringNotContainsString( $p_needle, $this->parser->text( $p_sample ) );
		}
	}

	/**
	 * Are attributes to an anchor are correctly added.
	 *
	 * @todo This test covers a core function, and therefore it
	 *       should move to the core tests.
	 *
	 * @todo is it okay changing the config with config_set
	 *
	 * @dataProvider provideAttributes
	 */
	public function testAddLinkAttributes( int $p_config, string $p_expected ) {
		config_set( 'html_make_links', $p_config );

		$this->assertStringContainsString( $p_expected, $this->parser->text( '<https://example.com>' ) );
	}

	/**
	 * Is inline code replaced by its hash value.
	 */
	public function testInlineCode(): void
	{
		$t_code = 'the mention of "@user" is untouched';
		$t_hash = $this->parser->hash( $t_code );

		$this->assertSame(
			'<p>lorem <code>' . $t_hash . '</code> @user ipsum</p>',
			 $this->parser->text( 'lorem `' . $t_code . '` @user ipsum' )
		);

		$this->assertArrayHasKey( $t_hash, $this->parser->getCodeblocks() );
		$this->assertSame( $t_code, $this->parser->getCodeblocks()[$t_hash] );
	}

	/**
	 * Is a code block replaced by its hash value.
	 *
	 * @todo find a solution for nicer looking samples.
	 *       String concatenations with PHP_EOL look too wild?
	 */
	public function testCodeBlock(): void
	{
		$t_code = <<<Code
 const theTruth = () => {
    fetch('https://api.chucknorris.io/jokes/random')
        .then((response) => response.json())
        .then((json) => {
            console.log(json.value);
        })
}
theTruth();
Code;

		$t_input = <<<Markdown
lorem ipsum
```
$t_code
```
dolor sit amet
Markdown;

		$t_hash = $this->parser->hash( $t_code );
		$t_expected = <<<HTML
<p>lorem ipsum</p>
<pre><code>$t_hash</code></pre>
<p>dolor sit amet</p>
HTML;

		$this->assertSame( $t_expected, $this->parser->text( $t_input ) );
		$this->assertArrayHasKey( $t_hash, $this->parser->getCodeblocks() );
		$this->assertSame( $t_code, $this->parser->getCodeblocks()[$t_hash] );
	}

	/**
	 * Test if css class is applied to a table.
	 */
	public function testTableClassIsApplied(): void
	{
		$t_table = <<<Markdown
| header |
| ---    |
| cell   |
Markdown;

		$this->assertTrue(false !== strpos (
			$this->parser->text( $t_table),
			'class="table table-nonfluid"'
		));
	}

	/**
	 * The "convert_line()" and "convert_text()" methods returns the
	 * finalized HTML markup.
	 *
	 * @note The samples do not cover "mentions" of "bugs", "bug notes" or "users",
	 *       as their processing is covered by core tests.
	 *
	 * - convert_line should not have p tags
	 * - convert_text should have p tags
	 * - code blocks should untouched
	 */
	public function testConvert(): void
	{
		$this->assertSame( 'I am <strong>strong</strong>', MantisMarkdown::convert_line('I am **strong**') );
		$this->assertSame( '<p>I am <strong>strong</strong></p>', MantisMarkdown::convert_text('I am **strong**') );
		$this->assertSame( '<p>I am <code>**strong**</code></p>', MantisMarkdown::convert_text('I am `**strong**`') );
	}

	public function provideHeaders(): Generator
	{
		# valid headers
		yield  'valid: # foo' => [ '# foo', '<h1>foo</h1>' ];
		yield  'valid: ## foo' => [ '## foo', '<h2>foo</h2>' ];
		yield  'valid: ### foo' => [ '### foo', '<h3>foo</h3>' ];
		yield  'valid: #### foo' => [ '#### foo', '<h4>foo</h4>' ];
		yield  'valid: ##### foo' => [ '##### foo', '<h5>foo</h5>' ];
		yield  'valid: ###### foo' => [ '###### foo', '<h6>foo</h6>' ];

		# invalid headers
		yield  'invalid: ####### foo' => [ '####### foo', '<p>####### foo</p>' ];
		yield  'invalid: #foo' => [ '#foo', '<p>#foo</p>' ];
		yield  'invalid: #123' => [ '#123', '<p>#123</p>' ];
	}

	public function provideEmails(): Generator
	{
		yield 'process_urls = ON; lorem <user@exmaple.com> ipsum' => [
			'lorem <user@exmaple.com> ipsum',
			ON,
			'<p>lorem <a href="mailto:user@exmaple.com">user@exmaple.com</a> ipsum</p>'
		];

		yield 'process_urls = OFF; lorem <user@exmaple.com> ipsum' => [
			'lorem <user@exmaple.com> ipsum',
			OFF,
			'<p>lorem <a href="mailto:user@exmaple.com">user@exmaple.com</a> ipsum</p>'
		];

		yield 'process_urls = ON; lorem user@exmaple.com ipsum' => [
			'lorem user@exmaple.com ipsum',
			ON,
			'<p>lorem <a href="mailto:user@exmaple.com">user@exmaple.com</a> ipsum</p>'
		];

		yield 'process_urls = OFF; lorem user@exmaple.com ipsum' => [
			'lorem user@exmaple.com ipsum',
			OFF,
			'<p>lorem user@exmaple.com ipsum</p>'
		];
	}

	public function provideUrls(): Generator
	{
		yield 'process_urls = ON; lorem <https://exmaple.com> ipsum' => [
			'lorem <https://exmaple.com> ipsum',
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

		yield 'process_urls = ON; lorem [link](https://exmaple.com) ipsum' => [
			'lorem [link](https://exmaple.com) ipsum',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = OFF; lorem [link](https://exmaple.com) ipsum' => [
			'lorem [link](https://exmaple.com) ipsum',
			ON,
			'<a href="https://exmaple.com"',
			true
		];

		yield 'process_urls = ON; lorem https://exmaple.com ipsum' => [
			'lorem https://exmaple.com ipsum',
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
	}

	public function provideAttributes(): Generator
	{
		yield 'LINKS_NEW_WINDOW' => [
			LINKS_NEW_WINDOW,
			'target="_blank"',
		];

		yield 'LINKS_NOOPENER' => [
			LINKS_NOOPENER,
			'rel="noopener"',
		];

		yield 'LINKS_NOREFERRER' => [
			LINKS_NOREFERRER,
			'rel="noreferrer"',
		];

		yield 'LINKS_NOOPENER | LINKS_NOREFERRER' => [
			LINKS_NOOPENER | LINKS_NOREFERRER,
			'rel="noreferrer"',
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOOPENER' => [
			LINKS_NEW_WINDOW | LINKS_NOOPENER,
			'target="_blank" rel="noopener"',
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOREFERRER' => [
			LINKS_NEW_WINDOW | LINKS_NOREFERRER,
			'target="_blank" rel="noreferrer"',
		];

		yield 'LINKS_NEW_WINDOW | LINKS_NOOPENER | LINKS_NOREFERRER' => [
			LINKS_NEW_WINDOW | LINKS_NOOPENER | LINKS_NOREFERRER,
			'target="_blank" rel="noreferrer"',
		];
	}
}
