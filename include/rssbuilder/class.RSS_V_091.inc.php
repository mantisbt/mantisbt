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
class RSS_V_091 extends RSS_V_abstract {
	
	function __construct(RSSBuilder &$rssdata) {
		parent::__construct($rssdata);
	} // end constructor

	protected function generateXML() {
		parent::generateXML();
		$root = $this->xml->createElement('rss');
		$this->xml->appendChild($root);
		$root->setAttribute('version', '0.91');
	
		$channel = $this->xml->createElement('channel');
		$root->appendChild($channel);		

		if ($this->rssdata->getDCRights() != FALSE) {
			$copyright = $this->xml->createElement('copyright');
			$copyright->appendChild($this->xml->createTextNode($this->rssdata->getDCRights()));
			$channel->appendChild($copyright);			
		} // end if
		
		if ($this->rssdata->getDCDate() != FALSE) {
			$date = $this->xml->createTextNode(date('r', $this->rssdata->getDCDate()));
			$pub_date = $this->xml->createElement('pubDate');
			$lb_date = $this->xml->createElement('lastBuildDate');
			$pub_date->appendChild($date);
			$lb_date->appendChild($date->cloneNode());
			$channel->appendChild($pub_date);
			$channel->appendChild($lb_date);			
		} // end if
		
		if ($this->rssdata->getAbout() != FALSE) {
			$docs = $this->xml->createElement('docs');
			$link = $this->xml->createElement('link');
			$about_text = $this->xml->createTextNode($this->rssdata->getAbout());
			$docs->appendChild($about_text);
			$link->appendChild($about_text->cloneNode());
			$channel->appendChild($docs);	
			$channel->appendChild($link);
		} // end if

		if ($this->rssdata->getDescription() != FALSE) {
			$description = $this->xml->createElement('description');
			$description->appendChild($this->xml->createTextNode($this->rssdata->getDescription()));
			$channel->appendChild($description);			
		} // end if

		if ($this->rssdata->getTitle() != FALSE) {
			$title = $this->xml->createElement('title');
			$title->appendChild($this->xml->createTextNode($this->rssdata->getTitle()));
			$channel->appendChild($title);			
		} // end if
				
		if ($this->rssdata->getImageLink() != FALSE) {
			$image = $this->xml->createElement('image');
			$channel->appendChild($image);	
			$image->appendChild($title->cloneNode(TRUE));	
			$url = $this->xml->createElement('url');
			$url->appendChild($this->xml->createTextNode($this->rssdata->getImageLink()));
			$image->appendChild($url);	
			$image->appendChild($link->cloneNode(TRUE));	
			$image->appendChild($description->cloneNode(TRUE));	
		} // end if

		if ($this->rssdata->getDCPublisher() != FALSE) {
			$managingEditor = $this->xml->createElement('managingEditor');
			$managingEditor->appendChild($this->xml->createTextNode($this->rssdata->getDCPublisher()));
			$channel->appendChild($managingEditor);			
		} // end if

		if ($this->rssdata->getDCCreator() != FALSE) {
			$webMaster = $this->xml->createElement('webMaster');
			$webMaster->appendChild($this->xml->createTextNode($this->rssdata->getDCCreator()));
			$channel->appendChild($webMaster);			
		} // end if

		if ($this->rssdata->getDCLanguage() != FALSE) {
			$language = $this->xml->createElement('language');
			$language->appendChild($this->xml->createTextNode($this->rssdata->getDCLanguage()));
			$channel->appendChild($language);			
		} // end if
		
		foreach ($this->rssdata->getRSSItemList() as $id => $rss_item) {
			$item = '$item_' . $id;
			$$item = $this->xml->createElement('item');
			$channel->appendChild($$item);
			
			$item_title = '$item_title_' . $id;
			$$item_title = $this->xml->createElement('title');
			$$item_title->appendChild($this->xml->createTextNode($rss_item->getTitle()));
			$$item->appendChild($$item_title);
			
			$item_link = '$item_link_' . $id;
			$$item_link = $this->xml->createElement('link');
			$$item_link->appendChild($this->xml->createTextNode($rss_item->getLink()));
			$$item->appendChild($$item_link);			
			
			$item_desc = '$item_desc_' . $id;
			$$item_desc = $this->xml->createElement('description');
			$$item_desc->appendChild($this->xml->createTextNode($rss_item->getDescription()));
			$$item->appendChild($$item_desc);	
		} // end foreach
	} // function	
} // end class
?>
