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
 * Test cases for markdown handling within mantis
 *
 * @package    Tests
 * @subpackage String
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

# Includes
require_once( dirname( dirname( __FILE__ ) ) . '../../../tests/TestConfig.php' );
require_once( dirname( dirname( __FILE__ ) ) . '/core/MantisMarkdown.php' );

# MantisBT Core API
require_mantis_core();

/**
 * Mantis markdown handling test cases
 * @package    Tests
 * @subpackage Markdown
 * @copyright Copyright 2016  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

class MantisMarkdownTest extends PHPUnit_Framework_TestCase {

	/**
	 * Mantis Text Process
	 *
	 */
	private function textProcess ( $p_string ) {
		$t_string = $p_string;

		$t_string = string_strip_hrefs( $t_string );
		$t_string = string_html_specialchars( $t_string );
		$t_string = string_restore_valid_html_tags( $t_string, true );

		return $t_string;
	}
	/**
	 * Test If string starts with hash character followed by letters
	 * @return void
	 */
	public function testHashLetters() {
		$this->assertEquals( '<h1>hello</h1>', MantisMarkdown::convert_text( '# hello' ) );
		$this->assertEquals( '<h1>hello</h1>', MantisMarkdown::convert_text( '#hello' ) );
	}

	/**
	 * Test If string starts with hash character followed by number and letters
	 * @return void
	 */
	public function testHashNumberAny() {
		$this->assertEquals( '<h1>1abcd</h1>', MantisMarkdown::convert_text( '# 1abcd' ) );
		$this->assertEquals( '<h1>1abcd</h1>', MantisMarkdown::convert_text( '#1abcd' ) );
	}

	/**
	 * Test If string starts with hash character followed by letters and numbers
	 * @return void
	 */
	public function testHashLettersAny() {
		$this->assertEquals( '<h1>abcd1234</h1>', MantisMarkdown::convert_text( '# abcd1234' ) );
		$this->assertEquals( '<h1>abcd1234</h1>', MantisMarkdown::convert_text( '#abcd1234' ) );
	}

	/**
	 * Test If string starts with hash character followed by numbers
	 * since the class overrides the default Markdown parsing on Header
	 * then the methods should return the standard text.
	 * @return void
	 */
	public function testHashNumbers() {
		$this->assertEquals( '<p>#1</p>', MantisMarkdown::convert_text( '#1' ) );
	}

	/**
	* Test if table class attribute is defined
	 * @return void
	 */
	public function testTableClassDefined() {
		$markdown_table = <<<EOD
| _header_ 1   | header 2     |
| ------------ | ------------ |
| _cell_ 1.1   | ~~cell~~ 1.2 |
| `|` 2.1      | \| 2.2       |
| `\|` 2.1     | [link](/)    |
EOD;

		$markdown_table_output = <<<EOD
<table class="table table-nonfluid">
<thead>
<tr>
<th><em>header</em> 1</th>
<th>header 2</th>
</tr>
</thead>
<tbody>
<tr>
<td><em>cell</em> 1.1</td>
<td><del>cell</del> 1.2</td>
</tr>
<tr>
<td><code>|</code> 2.1</td>
<td>| 2.2</td>
</tr>
<tr>
<td><code>\|</code> 2.1</td>
<td><a href="/">link</a></td>
</tr>
</tbody>
</table>
EOD;

		$this->assertEquals( $markdown_table_output, MantisMarkdown::convert_text( $markdown_table ) );
	}

	/**
	 * Test the quote markdown if style attribute is defined
	 * @return void
	 */
	public function testQuoteStyleAttribute() {
		$markdown_quote = <<<EOD
> quote

indented:
	> quote

no space after `>`:
>quote
EOD;

		$markdown_quote_output = <<<EOD
<blockquote style="padding:0.13em 1em;color:#777;border-left:0.25em solid #C0C0C0;font-size:13px;">
<p>quote</p>
</blockquote>
<p>indented:</p>
<blockquote style="padding:0.13em 1em;color:#777;border-left:0.25em solid #C0C0C0;font-size:13px;">
<p>quote</p>
</blockquote>
<p>no space after <code>&gt;</code>:</p>
<blockquote style="padding:0.13em 1em;color:#777;border-left:0.25em solid #C0C0C0;font-size:13px;">
<p>quote</p>
</blockquote>
EOD;
		$this->assertEquals( $markdown_quote_output, MantisMarkdown::convert_text( $markdown_quote ) );
	}

	/**
	 * Test the quote markdown with text processing
	 * @return void
	 */
	public function testQuoteWithTextProcess() {

$markdown_quote_output = <<<EOD
<blockquote style="padding:0.13em 1em;color:#777;border-left:0.25em solid #C0C0C0;font-size:13px;">
<p>This is a quote</p>
</blockquote>
EOD;
		$t_string = $this->textProcess ( "> This is a quote" );

		$t_string = str_replace( "&gt;", ">", $t_string );

		$this->assertEquals( $markdown_quote_output, MantisMarkdown::convert_text( $t_string ) );
	}

	/**
	 * Test '<' signs within the code block tag (backticks) with text processing
	 * @return void
	 */
	public function testLessThanSignWithTextProcess (){

$markdown = <<<EOD
LT sign within backticks: `<`
LT sign within code block
```
<
```
EOD;

$markdown_output = <<<EOD
<p>LT sign within backticks: <code>&lt;</code><br />
LT sign within code block</p>
<pre><code>&lt;</code></pre>
EOD;
		$t_string = $this->textProcess ( $markdown );
		
		$t_string = MantisMarkdown::convert_text( $t_string );

		# markdown is unable to process '<' signs within the code block tag (backticks)
		# markdown convert '<' signs into '&amp;lt;' after the text proccessing process
		# therefore we need restore the entity back into its original name '&lt;'
		$t_string = str_replace( '&amp;lt;', '&lt;', $t_string );

		$this->assertEquals( $markdown_output,  $t_string );
	}
}








