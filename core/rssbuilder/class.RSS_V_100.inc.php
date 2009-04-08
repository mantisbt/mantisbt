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
class RSS_V_100 extends RSS_V_abstract {

	function __construct(RSSBuilder &$rssdata) {
		parent::__construct($rssdata);
	} // end constructor

	protected function generateXML() {
		parent::generateXML();
		$root = $this->xml->createElement('rdf:RDF');
		$this->xml->appendChild($root);
		$root->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$root->setAttribute('xmlns:sy', 'http://purl.org/rss/1.0/modules/syndication/');
		$root->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
		$channel = $this->xml->createElement('channel');
		$root->appendChild($channel);

		if ($this->rssdata->getTitle() != FALSE) {
			$title = $this->xml->createElement('title');
			$title->appendChild($this->xml->createTextNode($this->rssdata->getTitle()));
			$channel->appendChild($title);
		} // end if

		if ($this->rssdata->getAbout() != FALSE) {
			$about = $this->xml->createTextNode($this->rssdata->getAbout());
			$channel->setAttribute('rdf:about', $this->rssdata->getAbout());

			$link = $this->xml->createElement('link');
			$link->appendChild($about->cloneNode());
			$channel->appendChild($link);
		} // end if

		if ($this->rssdata->getDescription() != FALSE) {
			$description = $this->xml->createElement('description');
			$description->appendChild($this->xml->createCDATASection($this->rssdata->getDescription()));
			$channel->appendChild($description);
		} // end if

		if ($this->rssdata->getDCPublisher() != FALSE) {
			$publisher = $this->xml->createElement('dc:publisher');
			$publisher->appendChild($this->xml->createTextNode($this->rssdata->getDCPublisher()));
			$channel->appendChild($publisher);
		} // end if

		if ($this->rssdata->getDCCreator() != FALSE) {
			$creator = $this->xml->createElement('dc:creator');
			$creator->appendChild($this->xml->createTextNode($this->rssdata->getDCCreator()));
			$channel->appendChild($creator);
		} // end if

		if ($this->rssdata->getDCDate() != FALSE) {
			$date = $this->xml->createTextNode(date('r', $this->rssdata->getDCDate()));
			$pub_date = $this->xml->createElement('dc:date');
			$pub_date->appendChild($date);
			$channel->appendChild($pub_date);
		} // end if

		if ($this->rssdata->getDCLanguage() != FALSE) {
			$language = $this->xml->createElement('dc:language');
			$language->appendChild($this->xml->createTextNode($this->rssdata->getDCLanguage()));
			$channel->appendChild($language);
		} // end if

		if ($this->rssdata->getDCRights() != FALSE) {
			$copyright = $this->xml->createElement('dc:rights');
			$copyright->appendChild($this->xml->createTextNode($this->rssdata->getDCRights()));
			$channel->appendChild($copyright);
		} // end if

		if ($this->rssdata->getDCCoverage() != FALSE) {
			$coverage = $this->xml->createElement('dc:coverage');
			$coverage->appendChild($this->xml->createTextNode($this->rssdata->getDCCoverage()));
			$channel->appendChild($coverage);
		} // end if

		if ($this->rssdata->getDCContributor() != FALSE) {
			$contributor = $this->xml->createElement('dc:contributor');
			$contributor->appendChild($this->xml->createTextNode($this->rssdata->getDCContributor()));
			$channel->appendChild($contributor);
		} // end if

		if ($this->rssdata->getSYPeriod() != FALSE) {
			$period = $this->xml->createElement('sy:updatePeriod');
			$period->appendChild($this->xml->createTextNode($this->rssdata->getSYPeriod()));
			$channel->appendChild($period);
		} // end if

		if ($this->rssdata->getSYFrequency() != FALSE) {
			$frequency = $this->xml->createElement('sy:updateFrequency');
			$frequency->appendChild($this->xml->createTextNode($this->rssdata->getSYFrequency()));
			$channel->appendChild($frequency);
		} // end if

		if ($this->rssdata->getSYBase() != FALSE) {
			$basedate = $this->xml->createTextNode(date('r', $this->rssdata->getSYBase()));
			$base = $this->xml->createElement('sy:updateBase');
			$base->appendChild($basedate);
			$channel->appendChild($base);
		} // end if

		if ($this->rssdata->getImageLink() != FALSE) {
			$image = $this->xml->createElement('image');
			$channel->appendChild($image);
			$image->setAttribute('rdf:about', $this->rssdata->getImageLink());
			$image->appendChild($title->cloneNode(TRUE));
			$url = $this->xml->createElement('url');
			$url->appendChild($this->xml->createTextNode($this->rssdata->getImageLink()));
			$image->appendChild($url);
			$image->appendChild($link->cloneNode(TRUE));
			$image->appendChild($description->cloneNode(TRUE));
		} // end if

		$items = $this->xml->createElement('items');
		$channel->appendChild($items);
		$sequence = $this->xml->createElement('rdf:Seq');
		$items->appendChild($sequence);

		foreach ($this->rssdata->getRSSItemList() as $id => $rss_item) {
			$li = '$li_' . $id;
			$$li = $this->xml->createElement('rdf:li');
			$$li->setAttribute('resource', $rss_item->getLink());
			$sequence->appendChild($$li);
		} // end foreach

		foreach ($this->rssdata->getRSSItemList() as $id => $rss_item) {
			$item = '$item_' . $id;
			$$item = $this->xml->createElement('item');
			$$item->setAttribute(' rdf:about', $rss_item->getLink());
			$root->appendChild($$item);

			$item_title = '$item_title_' . $id;
			$$item_title = $this->xml->createElement('title');
			$$item_title->appendChild($this->xml->createTextNode($rss_item->getTitle()));
			$$item->appendChild($$item_title);

			$item_author = '$item_author_' . $id;
			$$item_author = $this->xml->createElement('author');
			$$item_author->appendChild($this->xml->createTextNode($rss_item->getAuthor()));
			$$item->appendChild($$item_author);

			$item_link = '$item_link_' . $id;
			$$item_link = $this->xml->createElement('link');
			$$item_link->appendChild($this->xml->createTextNode($rss_item->getLink()));
			$$item->appendChild($$item_link);

			$item_desc = '$item_desc_' . $id;
			$$item_desc = $this->xml->createElement('description');
			$$item_desc->appendChild($this->xml->createCDATASection($rss_item->getDescription()));
			$$item->appendChild($$item_desc);

			$item_sub = '$item_sub_' . $id;
			$$item_sub = $this->xml->createElement('dc:subject');
			$$item_sub->appendChild($this->xml->createTextNode($rss_item->getSubject()));
			$$item->appendChild($$item_sub);

			$item_date = '$item_date_' . $id;
			$date_string = $this->xml->createTextNode(date('r', $rss_item->getItemDate()));
			$$item_date = $this->xml->createElement('dc:date');
			$$item_date->appendChild($date_string);
			$$item->appendChild($$item_date);

			$item_pic = '$item_pic_' . $id;
			$$item_pic = $this->xml->createElement('dc:image');
			$$item_pic->appendChild($this->xml->createTextNode($rss_item->getImage()));
			$$item->appendChild($$item_pic);
		} // end foreach
	} // function
} // end class
?>
