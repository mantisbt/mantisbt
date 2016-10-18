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
 * Markdown API
 *
 * @package CoreAPI
 * @subpackage MarkdownAPI
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses ParsedownExtension
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
     * Disables Header elements
     *
     * @param string $line The Markdown syntax to parse
     * @access protected
     * @return void if markdown starts with # symbol | string html representation generated from markdown.
     */
    protected function blockHeader($line){
        $block = parent::blockHeader($line);

        # check if string start with # symbol
        # if string starts with # symbol then should not be treated as header
        if (preg_match_all('/^(#\w+)/', $line['text'])) {
            return;
        } 
        
        return $block;
    }

    /**
     * Disables of setting the Header elements.
     *
     * @param string $line The Markdown syntax to parse
     * @param array $block
     * @access protected
     * @return void if markdown starts with # symbol | string html representation generated from markdown.
     */
    protected function blockSetextHeader($line, array $block = NULL){
        
        $block = parent::blockSetextHeader($line, $block);
        
        # check if string start with # symbol
        # if string starts with # symbol then should not be treated as header
        if (preg_match_all('/^(#\w+)/', $line['text'])) {
            return;
        } 
        
        return $block;
    }    
}