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
 * ParsedownExtension class
 * @copyright Copyright 2016 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 * @package MantisBT
 * @subpackage parsedown
 */


/**
 * ParsedownExtension Extension class
 *
 * Extending Parsedown library to meet the MantisBT needs
 *
 * @package MantisBT
 * @subpackage parsedown
 *
 * @uses Parsedown
 */

require_once( dirname( dirname( __FILE__ ) ) . '/parsedown/Parsedown.php' );

class ParsedownExtension extends Parsedown
{
    /**
     * Disables Header elements
     *
     * @param string $line The Markdown syntax to parse
     * @access protected
     * @return void
     */
    protected function blockHeader($line){
        return;
    }

    /**
     * Disables of setting the Header elements.
     *
     * @param string $line The Markdown syntax to parse
     * @param array $block
     * @access protected
     * @return void
     */
    protected function blockSetextHeader($line, array $block = NULL){
        return;
    }    
}