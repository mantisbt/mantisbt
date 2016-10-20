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
 * MantisMarkdown class
 * @copyright Copyright 2016 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage parsedown
 */


/**
 * MantisMarkdown Extension class
 *
 * Extending Parsedown library to meet the MantisBT needs
 *
 * @package MantisBT
 * @subpackage parsedown
 *
 * @uses Parsedown
 */

require_once( dirname( dirname( __FILE__ ) ) . '/library/parsedown/Parsedown.php' );

class MantisMarkdown extends Parsedown
{

    /**
     * @var Custom table class $table_class
     */
    public $table_class = null;

    /**
     * Disables Header elements
     *
     * @param string $line The Markdown syntax to parse
     * @access protected
     * @return void if markdown starts with # symbol | string html representation generated from markdown.
     */
    protected function blockHeader($line) {
        $block = parent::blockHeader($line);

        # check if string start with # symbol
        # if string starts with # symbol then should not be treated as header
        if( preg_match_all('/^(#\w+)/', $line['text'], $matches) ) {
            return;
        } 
        
        return $block;
    }

    /**
     * Disables of setting the Header elements.
     *
     * @param string $line The Markdown syntax to parse
     * @param array $block A block-level element
     * @access protected
     * @return void if markdown starts with # symbol | string html representation generated from markdown.
     */
    protected function blockSetextHeader($line, array $block = NULL) {
        
        $block = parent::blockSetextHeader($line, $block);
        
        # check if string start with # symbol
        # if string starts with # symbol then should not be treated as header
        if( preg_match_all('/^(#\w+)/', $line['text'], $matches) ) {
            return;
        } 
        
        return $block;
    }

    /**
     * Add a class to table markedown elements
     * 
     * @param string $line The Markdown syntax to parse
     * @param array $block A block-level element
     * @param string $fn the function name to call (blockTable or blockTableContinue)
     * @access private
     * @return string html representation generated from markdown.
     */
    private function __doTable($line, $block, $fn) {

        if( $block = call_user_func('parent::' . $fn, $line, $block) ) {
        	$block['element']['attributes']['class'] = $this->table_class;
        }

        return $block;
    }

    /**
     * Override the blockTable structure by adding a class element
     *
     * @param string $line The Markdown syntax to parse
     * @param array $block A block-level element
     * @access protected
     * @return string html representation generated from markdown.
     */
    protected function blockTable($line, array $block = null) {
    	return $this->__doTable($line, $block, __FUNCTION__);
    }

    /**
     * * Override the blockTableContinue structure by adding a class element
     *
     * @param string $line The Markdown syntax to parse
     * @param array $block A block-level element
     * @access protected
     * @return string html representation generated from markdown.
     */
    protected function blockTableContinue($line, array $block) {
        return $this->__doTable($line, $block, __FUNCTION__);
    }
}

/**
 * Markdown API
 *
 * @package CoreAPI
 * @subpackage MarkdownAPI
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses MantisMarkdown
 */

$g_parsedown = null;

/**
 * Initialise the Parsedown library
 * We used the ParsedownExtension instead of the original library (Parsedown), its because we have
 * our own format settings (e.g If a line starts with # and issue id, the line is treated as a header 
 * instead of an issue reference and the # is omitted form the output)
 *
 * @return void
 */
function markdown_init() {
	global $g_parsedown;
	if ( $g_parsedown == null ) {
		$g_parsedown = new MantisMarkdown();
		# set the table class
		$g_parsedown->table_class = "table table-nonfluid";
	}
}

/**
 * Checked if markdown is enabled from config
 * @return boolean true enabled, false otherwise.
 */
function markdown_enabled() {
	return config_get( 'markdown_enabled' ) != OFF;
}

/**
 * Wrapped the parsedown->text as markdown_text
 *
 * ex: markdown_text('Hello _Parsedown_!'); 
 *
 * Output:
 * <p>Hello <em>Parsedown</em>!</p>
 * 
 * @link http://parsedown.org/tests for more samples
 *
 * @param string p_text The Markdown syntax to parse
 * @return string html representation generated from markdown
 */
function markdown_text( $p_text ) {
	markdown_init();

	global $g_parsedown;

	$t_text = $g_parsedown->text( $p_text );
	
	return $t_text;
}

/**
 * Wrapped the parsedown->line as markdown_line
 * Parse inline elements - instead of both block-level and inline elements
 *
 * ex: markdown_line('Hello _Parsedown_!'); 
 *
 * @link http://parsedown.org/tests for more samples
 *
 * Output:
 * Hello <em>Parsedown</em>!
 *
 * @param string p_text The Markdown syntax to parse
 * @return string html representation generated from markdown
 */
function markdown_line( $p_text ) {
	markdown_init();

	global $g_parsedown;

	$t_text =  $g_parsedown->line( $p_text );
	
	return $t_text;
}