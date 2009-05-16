<?php
require_once 'interface.RSS.inc.php';
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
class RSSBuilder extends RSSBase {

	protected $rss_data = array();
	protected $dc_data = array();
	protected $sy_data = array();
	protected $rss_itemlist;
	protected $filename;

	protected $versions = array('0.91' => '091', '1.0' => '100', '2.0' => '200');
	protected $version_objects = array();

	function __construct($encoding = 'ISO-8859-1',
						 $about = '',
						 $title = '',
						 $description = '',
						 $image_link = '',
						 $category = '',
						 $cache = 60) {
		parent::setVar($encoding, 'rss_data["encoding"]', 'string');
		parent::setVar($about, 'rss_data["about"]', 'string');
		parent::setVar($title, 'rss_data["title"]', 'string');
		parent::setVar($description, 'rss_data["description"]', 'string');
		parent::setVar($image_link, 'rss_data["image_link"]', 'string');
		parent::setVar($category, 'rss_data["category"]', 'string');
		parent::setVar($cache, 'rss_data["cache"]', 'int');
		$this->rss_itemlist = new RSSItemList();
		$this->filename = 'rss_' . str_replace(' ','_', $this->getTitle()) . '.xml';
	} // end constructor

	public function addRSSItem($about = '',
							   $title = '',
							   $link = '',
							   $description = '',
							   $subject = '',
							   $date = 0,
							   $author = '',
							   $comments = '',
							   $image = '') {
		$this->rss_itemlist->addRSSItem(new RSSItem($about, $title, $link,
										$description, $subject, $date, $author,
										$comments, $image));
	} // end function

	public function addDCdata($publisher = '',
							  $creator = '',
							  $date = 0,
							  $language = 'en',
							  $rights = '',
							  $coverage = '',
							  $contributor = '') {
		parent::setVar($publisher, 'dc_data["publisher"]', 'string');
		parent::setVar($creator, 'dc_data["creator"]', 'string');
		parent::setVar($date, 'dc_data["date"]', 'int');

		if (preg_match('(^([a-zA-Z]{2})$)',$language) > 0) {
			parent::setVar($language, 'dc_data["language"]', 'string');
		} // end if

		parent::setVar($rights, 'dc_data["rights"]', 'string');
		parent::setVar($coverage, 'dc_data["coverage"]', 'string');
		parent::setVar($contributor, 'dc_data["contributor"]', 'string');
	} // end function

	public function addSYdata($period = 'daily',
							  $frequency = 1,
							  $base = 0) {

		$periods = array('hourly','daily','weekly','monthly','yearly');
		if (in_array($period, $periods)) {
			parent::setVar($period, 'sy_data["period"]', 'string');
		} // end if

		parent::setVar($frequency, 'sy_data["frequency"]', 'int');
		parent::setVar($base, 'sy_data["base"]', 'int');
	} // end function

	public function getEncoding() {
		return parent::getVar('rss_data["encoding"]');
	} // end function

	public function getAbout() {
		return parent::getVar('rss_data["about"]');
	} // end function

	public function getTitle() {
		return parent::getVar('rss_data["title"]');
	} // end function

	public function getDescription() {
		return parent::getVar('rss_data["description"]');
	} // end function

	public function getImageLink() {
		return parent::getVar('rss_data["image_link"]');
	} // end function

	public function getCategory() {
		return parent::getVar('rss_data["category"]');
	} // end function

	public function getCache() {
		return parent::getVar('rss_data["cache"]');
	} // end function


	public function getRSSItemList() {
		return parent::getVar('rss_itemlist');
	} // end function


	public function getDCPublisher() {
		return parent::getVar('dc_data["publisher"]');
	} // end function

	public function getDCCreator() {
		return parent::getVar('dc_data["creator"]');
	} // end function

	public function getDCDate() {
		return parent::getVar('dc_data["date"]');
	} // end function

	public function getDCLanguage() {
		return parent::getVar('dc_data["language"]');
	} // end function

	public function getDCRights() {
		return parent::getVar('dc_data["rights"]');
	} // end function

	public function getDCCoverage() {
		return parent::getVar('dc_data["coverage"]');
	} // end function

	public function getDCContributor() {
		return parent::getVar('dc_data["contributor"]');
	} // end function


	public function getSYPeriod() {
		return parent::getVar('sy_data["period"]');
	} // end function

	public function getSYFrequency() {
		return parent::getVar('sy_data["frequency"]');
	} // end function

	public function getSYBase() {
		return parent::getVar('sy_data["base"]');
	} // end function

	public function getFilename() {
		return parent::getVar('filename');
	} // end function

	protected function setVersionObject($version = '0.91') {
		if (array_key_exists($version, $this->versions)) {
			$classname = 'RSS_V_' . $this->versions[$version];
			$this->version_objects[$version] = new $classname($this);
		} // end if
	} // end function

	protected function prepareRSSRequest($version = '0.91') {
		$this->filename = $this->versions[$version] . '_' . $this->filename;
		if (!isset($this->version_objects[$version])) {
			$this->setVersionObject($version);
		} // end if
	} // end function

	public function getRSSOutput($version = '0.91') {
		$this->prepareRSSRequest($version);
		return $this->version_objects[$version]->getRSSOutput();
	} // end function

	public function saveRSS($version = '0.91', $path = '') {
		$this->prepareRSSRequest($version);
		return $this->version_objects[$version]->saveRSS($path);
	} // end function

	public function outputRSS($version = '0.91') {
		$this->prepareRSSRequest($version);
		return $this->version_objects[$version]->outputRSS();
	} // end function
} // end class
?>
