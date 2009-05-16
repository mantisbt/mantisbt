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
class RSS_V_200 extends RSS_V_abstract {

	function __construct(RSSBuilder &$rssdata) {
		parent::__construct($rssdata);
	} // end constructor

	protected function generateXML() {
		parent::generateXML();
		$root = $this->xml->createElement('rss');
		$root->setAttribute('version', '2.0');
		$root->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$root->setAttribute('xmlns:sy', 'http://purl.org/rss/1.0/modules/syndication/');
		$this->xml->appendChild($root);
		$channel = $this->xml->createElement('channel');
		$root->appendChild($channel);

		if ($this->rssdata->getDCRights() != FALSE) {
			$copyright = $this->xml->createElement('copyright');
			$copyright->appendChild($this->xml->createTextNode($this->rssdata->getDCRights()));
			$channel->appendChild($copyright);
		} // end if

		if ($this->rssdata->getDCDate() != FALSE) {
			$date = $this->xml->createTextNode(date('Y-m-d\TH:i:sO', $this->rssdata->getDCDate()));
			$pub_date = $this->xml->createElement('pubDate');
			$last_build = $this->xml->createElement('lastBuildDate');
			$pub_date->appendChild($date);
			$last_build->appendChild($date->cloneNode());
			$channel->appendChild($pub_date);
			$channel->appendChild($last_build);
		} // end if

		if ($this->rssdata->getAbout() != FALSE) {
			$about = $this->xml->createTextNode($this->rssdata->getAbout());
			$link = $this->xml->createElement('link');
			$docs = $this->xml->createElement('docs');
			$docs->appendChild($about);
			$link->appendChild($about->cloneNode());
			$channel->appendChild($docs);
			$channel->appendChild($link);
		} // end if

		if ($this->rssdata->getDescription() != FALSE) {
			$description = $this->xml->createElement('description');
			$description->appendChild($this->xml->createCDATASection($this->rssdata->getDescription()));
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
			$webmaster_string = $this->xml->createTextNode($this->rssdata->getDCCreator());
			$webMaster = $this->xml->createElement('webMaster');
			$generator = $this->xml->createElement('generator');
			$webMaster->appendChild($webmaster_string);
			$generator->appendChild($webmaster_string->cloneNode());
			$channel->appendChild($webMaster);
			$channel->appendChild($generator);
		} // end if

		if ($this->rssdata->getDCLanguage() != FALSE) {
			$language = $this->xml->createElement('language');
			$language->appendChild($this->xml->createTextNode($this->rssdata->getDCLanguage()));
			$channel->appendChild($language);
		} // end if

		if ($this->rssdata->getCategory() != FALSE) {
			$category = $this->xml->createElement('category');
			$category->appendChild($this->xml->createTextNode($this->rssdata->getCategory()));
			$channel->appendChild($category);
		} // end if

		if ($this->rssdata->getCache() != FALSE) {
			$cache = $this->xml->createElement('ttl');
			$cache->appendChild($this->xml->createTextNode($this->rssdata->getCache()));
			$channel->appendChild($cache);
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
			$date = $this->xml->createTextNode(date('Y-m-d\TH:i:sO', $this->rssdata->getDCDate()));
			$pub_date = $this->xml->createElement('dc:date');
			$pub_date->appendChild($date);
			$channel->appendChild($pub_date);
		} // end if

		if ($this->rssdata->getDCLanguage() != FALSE) {
			$language_dc = $this->xml->createElement('dc:language');
			$language_dc->appendChild($this->xml->createTextNode($this->rssdata->getDCLanguage()));
			$channel->appendChild($language_dc);
		} // end if

		if ($this->rssdata->getDCRights() != FALSE) {
			$copyright = $this->xml->createElement('dc:rights');
			$copyright->appendChild($this->xml->createTextNode($this->rssdata->getDCRights()));
			$channel->appendChild($copyright);
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

		foreach ($this->rssdata->getRSSItemList() as $id => $rss_item) {
			$item = '$item_' . $id;
			$$item = $this->xml->createElement('item');
			$channel->appendChild($$item);

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
			$$item_sub = $this->xml->createElement('category');
			$$item_sub->appendChild($this->xml->createTextNode($rss_item->getSubject()));
			$$item->appendChild($$item_sub);

			$item_date = '$item_date_' . $id;
			$item_date_string = '$item_date_string_' . $id;
			$$item_date_string = $this->xml->createTextNode(date('r', $rss_item->getItemDate()));
			$$item_date = $this->xml->createElement('pubDate');
			$$item_date->appendChild($$item_date_string);
			$$item->appendChild($$item_date);

			$item_guid = '$item_guid_' . $id;
			$$item_guid = $this->xml->createElement('guid');
			$$item_guid->appendChild($this->xml->createTextNode($rss_item->getLink()));
			$$item->appendChild($$item_guid);

			if ( $rss_item->getComments() != FALSE ) {
				$item_comments = '$item_comments_' . $id;
				$$item_comments = $this->xml->createElement('comments');
				$$item_comments->appendChild($this->xml->createTextNode($rss_item->getComments()));
				$$item->appendChild($$item_comments);			
			} // end if

		} // end foreach
	} // function
} // end class
?>
