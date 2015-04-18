<?php
require_once 'class.RSSBase.inc.php';
/**
* Class for creating an RSS-feed
* @author Michael Wimmer <flaimo@gmail.com>
* @category flaimo-php
* @copyright Copyright © 2002-2008, Michael Wimmer
* @license GNU General Public License v3
* @link http://code.google.com/p/flaimo-php/
* @package RSS
* @version 2.2.1
*/
class RSSItemList extends ObjectList {
	
	function __construct() {
		parent::__construct(0,100);
	} // end constructor
	
	public function addRSSItem(RSSItem &$item) {
		parent::addObject($item);
	} // end function
	
} // end class
?>
