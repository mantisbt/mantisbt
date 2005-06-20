<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
//+----------------------------------------------------------------------+
//| WAMP (XP-SP1/1.3.27/4.0.12/4.3.2)                                    |
//+----------------------------------------------------------------------+
//| Copyright (c) 1992-2003 Michael Wimmer                               |
//+----------------------------------------------------------------------+
//| I don't have the time to read through all the licences to find out   |
//| what the exactly say. But it's simple. It's free for non commercial  |
//| projects, but as soon as you make money with it, i want my share :-) |
//| (License : Free for non-commercial use)                              |
//+----------------------------------------------------------------------+
//| Authors: Michael Wimmer <flaimo@gmx.net>                             |
//+----------------------------------------------------------------------+
//
// $Id: class.RSSBuilder.inc.php,v 1.4 2005-06-20 15:13:41 vboctor Exp $

/**
* @package RSSBuilder
* @category FLP
*/
/**
* Abstract class for getting ini preferences
*
* Tested with WAMP (XP-SP1/1.3.27/4.0.12/4.3.2)
* Last change: 2003-06-26
*
* @desc Abstract class for the RSS classes
* @access protected
* @author Michael Wimmer <flaimo 'at' gmx 'dot' net>
* @copyright Michael Wimmer
* @link http://www.flaimo.com/
* @global array $GLOBALS['_TICKER_ini_settings']
* @abstract
* @package RSSBuilder
* @category FLP
* @version 1.002
*/
class RSSBase {

	/*-----------------------*/
	/* C O N S T R U C T O R */
	/*-----------------------*/

	/**
	* Constructor
	*
	* @desc Constructor
	* @return void
	* @access private
	*/
	function RSSBase() {
	} // end constructor

} // end class RSSBase

//---------------------------------------------------------------------------

/**
* Class for creating a RSS file
*
* Tested with WAMP (XP-SP1/1.3.27/4.0.12/4.3.2)
* Last change: 2003-06-26
*
* @desc Class for creating a RSS file
* @access public
* @author Michael Wimmer <flaimo@gmx.net>
* @copyright Michael Wimmer
* @link http://www.flaimo.com/
* @example rss_sample_script.php Sample script
* @package RSSBuilder
* @category FLP
* @version 1.002
*/
class RSSBuilder extends RSSBase {

	/*-------------------*/
	/* V A R I A B L E S */
	/*-------------------*/

	/**#@+
	* @access private
	* @var string
	*/
	/**
	* encoding of the XML file
	*
	* @desc encoding of the XML file
	*/
	var $encoding;

	/**
	* URL where the RSS document will be made available
	*
	* @desc URL where the RSS document will be made available
	*/
	var $about;

	/**
	* title of the rss stream
	*
	* @desc title of the rss stream
	*/
	var $title;

	/**
	* description of the rss stream
	*
	* @desc description of the rss stream
	*/
	var $description;

	/**
	* publisher of the rss stream (person, an organization, or a service)
	*
	* @desc publisher of the rss stream
	*/
	var $publisher;

	/**
	* creator of the rss stream (person, an organization, or a service)
	*
	* @desc creator of the rss stream
	*/
	var $creator;

	/**
	* creation date of the file (format: 2003-05-29T00:03:07+0200)
	*
	* @desc creation date of the file (format: 2003-05-29T00:03:07+0200)
	*/
	var $date;

	/**
	* iso format language
	*
	* @desc iso format language
	*/
	var $language;

	/**
	* copyrights for the rss stream
	*
	* @desc copyrights for the rss stream
	*/
	var $rights;

	/**
	* URL to an small image
	*
	* @desc URL to an small image
	*/
	var $image_link;

	/**
	* spatial location, temporal period or jurisdiction
	*
	* spatial location (a place name or geographic coordinates), temporal
	* period (a period label, date, or date range) or jurisdiction (such as a
	* named administrative entity)
	*
	* @desc spatial location, temporal period or jurisdiction
	*/
	var $coverage;

	/**
	* person, an organization, or a service
	*
	* @desc person, an organization, or a service
	*/
	var $contributor;

	/**
	* 'hourly' | 'daily' | 'weekly' | 'monthly' | 'yearly'
	*
	* @desc 'hourly' | 'daily' | 'weekly' | 'monthly' | 'yearly'
	*/
	var $period;

	/**
	* date (format: 2003-05-29T00:03:07+0200)
	*
	* Defines a base date to be used in concert with updatePeriod and
	* updateFrequency to calculate the publishing schedule.
	*
	* @desc base date to calculate from (format: 2003-05-29T00:03:07+0200)
	*/
	var $base;

	/**
	* category (rss 2.0)
	*
	* @desc category (rss 2.0)
	* @since 1.001 - 2003-05-30
	*/
	var $category;

	/**
	* compiled outputstring
	*
	* @desc compiled outputstring
	*/
	var $output;
	/**#@-*/

	/**#@+
	* @access private
	*/
	/**
	* every X hours/days/weeks/...
	*
	* @desc every X hours/days/weeks/...
	* @var int
	*/
	var $frequency;

	/**
	* caching time in minutes (rss 2.0)
	*
	* @desc caching time in minutes (rss 2.0)
	* @var int
	* @since 1.001 - 2003-05-30
	*/
	var $cache;

	/**
	* array wich all the rss items
	*
	* @desc array wich all the rss items
	* @var array
	*/
	var $items = array();

	/**
	* use DC data
	*
	* @desc use DC data
	* @var boolean
	*/
	var $use_dc_data = FALSE;

	/**
	* use SY data
	*
	* @desc use SY data
	* @var boolean
	*/
	var $use_sy_data = FALSE;
	/**#@-*/

	/*-----------------------*/
	/* C O N S T R U C T O R */
	/*-----------------------*/

	/**#@+
	* @return void
	*/
	/**
	* Constructor
	*
	* @desc Constructor
	* @param string $encoding encoding of the xml file
	* @param string $about URL where the RSS document will be made available
	* @param string $title
	* @param string $description
	* @param string $image_link  URL
	* @uses setEncoding()
	* @uses setAbout()
	* @uses setTitle()
	* @uses setDescription()
	* @uses setImageLink()
	* @uses setCategory()
	* @uses etCache()
	* @access private
	*/
	function RSSBuilder($encoding = '',
						$about = '',
						$title = '',
						$description = '',
						$image_link = '',
						$category = '',
						$cache = '') {
		$this->setEncoding($encoding);
		$this->setAbout($about);
		$this->setTitle($title);
		$this->setDescription($description);
		$this->setImageLink($image_link);
		$this->setCategory($category);
		$this->setCache($cache);
	} // end constructor

	/*-------------------*/
	/* F U N C T I O N S */
	/*-------------------*/

	/**
	* add additional DC data
	*
	* @desc add additional DC data
	* @param string $publisher person, an organization, or a service
	* @param string $creator person, an organization, or a service
	* @param string $date  format: 2003-05-29T00:03:07+0200
	* @param string $language  iso-format
	* @param string $rights  copyright information
	* @param string $coverage  spatial location (a place name or geographic coordinates), temporal period (a period label, date, or date range) or jurisdiction (such as a named administrative entity)
	* @param string $contributor  person, an organization, or a service
	* @uses setPublisher()
	* @uses setCreator()
	* @uses setDate()
	* @uses setLanguage()
	* @uses setRights()
	* @uses setCoverage()
	* @uses setContributor()
	* @access public
	*/
	function addDCdata($publisher = '',
						$creator = '',
						$date = '',
						$language = '',
						$rights = '',
						$coverage = '',
						$contributor = '') {
		$this->setPublisher($publisher);
		$this->setCreator($creator);
		$this->setDate($date);
		$this->setLanguage($language);
		$this->setRights($rights);
		$this->setCoverage($coverage);
		$this->setContributor($contributor);
		$this->use_dc_data = (boolean) TRUE;
	} // end function

	/**
	* add additional SY data
	*
	* @desc add additional DC data
	* @param string $period  'hourly' | 'daily' | 'weekly' | 'monthly' | 'yearly'
	* @param int $frequency  every x hours/days/weeks/...
	* @param string $base  format: 2003-05-29T00:03:07+0200
	* @uses setPeriod()
	* @uses setFrequency()
	* @uses setBase()
	* @access public
	*/
	function addSYdata($period = '', $frequency = '', $base = '') {
		$this->setPeriod($period);
		$this->setFrequency($frequency);
		$this->setBase($base);
		$this->use_sy_data = (boolean) TRUE;
	} // end function
	/**#@-*/

	/**#@+
	* @return void
	* @access private
	*/
	/**
	* Sets $encoding variable
	*
	* @desc Sets $encoding variable
	* @param string $encoding  encoding of the xml file
	* @see $encoding
	*/
	function setEncoding($encoding = '') {
		if (!isset($this->encoding)) {
			$this->encoding = (string) ((strlen(trim($encoding)) > 0) ? trim($encoding) : 'UTF-8');
		} // end if
	} // end function

	/**
	* Sets $about variable
	*
	* @desc Sets $about variable
	* @param string $about
	* @see $about
	*/
	function setAbout($about = '') {
		if (!isset($this->about) && strlen(trim($about)) > 0) {
			$this->about = (string) trim($about);
		} // end if
	} // end function

	/**
	* Sets $title variable
	*
	* @desc Sets $title variable
	* @param string $title
	* @see $title
	*/
	function setTitle($title = '') {
		if (!isset($this->title) && strlen(trim($title)) > 0) {
			$this->title = (string) trim($title);
		} // end if
	} // end function

	/**
	* Sets $description variable
	*
	* @desc Sets $description variable
	* @param string $description
	* @see $description
	*/
	function setDescription($description = '') {
		if (!isset($this->description) && strlen(trim($description)) > 0) {
			$this->description = (string) trim($description);
		} // end if
	} // end function

	/**
	* Sets $publisher variable
	*
	* @desc Sets $publisher variable
	* @param string $publisher
	* @see $publisher
	*/
	function setPublisher($publisher = '') {
		if (!isset($this->publisher) && strlen(trim($publisher)) > 0) {
			$this->publisher = (string) trim($publisher);
		} // end if
	} // end function

	/**
	* Sets $creator variable
	*
	* @desc Sets $creator variable
	* @param string $creator
	* @see $creator
	*/
	function setCreator($creator = '') {
		if (!isset($this->creator) && strlen(trim($creator)) > 0) {
			$this->creator = (string) trim($creator);
		} // end if
	} // end function

	/**
	* Sets $date variable
	*
	* @desc Sets $date variable
	* @param string $date  format: 2003-05-29T00:03:07+0200
	* @see $date
	*/
	function setDate($date = '') {
		if (!isset($this->date) && strlen(trim($date)) > 0) {
			$this->date = (string) trim($date);
		} // end if
	} // end function

	/**
	* Sets $language variable
	*
	* @desc Sets $language variable
	* @param string $language
	* @see $language
	* @uses isValidLanguageCode()
	*/
	function setLanguage($language = '') {
		if (!isset($this->language) && $this->isValidLanguageCode($language) === TRUE) {
			$this->language = (string) trim($language);
		} // end if
	} // end function

	/**
	* Sets $rights variable
	*
	* @desc Sets $rights variable
	* @param string $rights
	* @see $rights
	*/
	function setRights($rights = '') {
		if (!isset($this->rights) && strlen(trim($rights)) > 0) {
			$this->rights = (string) trim($rights);
		} // end if
	} // end function

	/**
	* Sets $coverage variable
	*
	* @desc Sets $coverage variable
	* @param string $coverage
	* @see $coverage
	*/
	function setCoverage($coverage = '') {
		if (!isset($this->coverage) && strlen(trim($coverage)) > 0) {
			$this->coverage = (string) trim($coverage);
		} // end if
	} // end function


	/**
	* Sets $contributor variable
	*
	* @desc Sets $contributor variable
	* @param string $contributor
	* @see $contributor
	*/
	function setContributor($contributor = '') {
		if (!isset($this->contributor) && strlen(trim($contributor)) > 0) {
			$this->contributor = (string) trim($contributor);
		} // end if
	} // end function

	/**
	* Sets $image_link variable
	*
	* @desc Sets $image_link variable
	* @param string $image_link
	* @see $image_link
	*/
	function setImageLink($image_link = '') {
		if (!isset($this->image_link) && strlen(trim($image_link)) > 0) {
			$this->image_link = (string) trim($image_link);
		} // end if
	} // end function

	/**
	* Sets $period variable
	*
	* @desc Sets $period variable
	* @param string $period  'hourly' | 'daily' | 'weekly' | 'monthly' | 'yearly'
	* @see $period
	*/
	function setPeriod($period = '') {
		if (!isset($this->period) && strlen(trim($period)) > 0) {
			switch ($period) {
				case 'hourly':
				case 'daily':
				case 'weekly':
				case 'monthly':
				case 'yearly':
					$this->period = (string) trim($period);
					break;
				default:
					$this->period = (string) '';
					break;
			} // end switch
		} // end if
	} // end function

	/**
	* Sets $frequency variable
	*
	* @desc Sets $frequency variable
	* @param int $frequency
	* @see $frequency
	*/
	function setFrequency($frequency = '') {
		if (!isset($this->frequency) && strlen(trim($frequency)) > 0) {
			$this->frequency = (int) $frequency;
		} // end if
	} // end function

	/**
	* Sets $base variable
	*
	* @desc Sets $base variable
	* @param string $base
	* @see $base
	*/
	function setBase($base = '') {
		if (!isset($this->base) && strlen(trim($base)) > 0) {
			$this->base = (string) trim($base);
		} // end if
	} // end function

	/**
	* Sets $category variable
	*
	* @desc Sets $category variable
	* @param string $category
	* @see $category
	* @since 1.001 - 2003-05-30
	*/
	function setCategory($category = '') {
		if (strlen(trim($category)) > 0) {
			$this->category = (string) trim($category);
		} // end if
	} // end function

	/**
	* Sets $cache variable
	*
	* @desc Sets $cache variable
	* @param int $cache
	* @see $cache
	* @since 1.001 - 2003-05-30
	*/
	function setCache($cache = '') {
		if (strlen(trim($cache)) > 0) {
			$this->cache = (int) $cache;
		} // end if
	} // end function
	/**#@-*/

	/**#@+
	* @access public
	*/
	/**
	* Checks if a given string is a valid iso-language-code
	*
	* @desc Checks if a given string is a valid iso-language-code
	* @param string $code  String that should validated
	* @return boolean $isvalid  If string is valid or not
	* @static
	*/
	function isValidLanguageCode($code = '') {
		return (boolean) ((preg_match('(^([a-zA-Z]{2})$)',$code) > 0) ? TRUE : FALSE);
	} // end function

	/**
	* Returns $encoding variable
	*
	* @desc Returns $encoding variable
	* @return string $encoding
	* @see $image_link
	*/
	function getEncoding() {
		return (string) $this->encoding;
	} // end function

	/**
	* Returns $about variable
	*
	* @desc Returns $about variable
	* @return string $about
	* @see $about
	*/
	function getAbout() {
		return (string) $this->about;
	} // end function

	/**
	* Returns $title variable
	*
	* @desc Returns $title variable
	* @return string $title
	* @see $title
	*/
	function getTitle() {
		return (string) $this->title;
	} // end function

	/**
	* Returns $description variable
	*
	* @desc Returns $description variable
	* @return string $description
	* @see $description
	*/
	function getDescription() {
		return (string) $this->description;
	} // end function

	/**
	* Returns $publisher variable
	*
	* @desc Returns $publisher variable
	* @return string $publisher
	* @see $publisher
	*/
	function getPublisher() {
		return (string) $this->publisher;
	} // end function

	/**
	* Returns $creator variable
	*
	* @desc Returns $creator variable
	* @return string $creator
	* @see $creator
	*/
	function getCreator() {
		return (string) $this->creator;
	} // end function

	/**
	* Returns $date variable
	*
	* @desc Returns $date variable
	* @return string $date
	* @see $date
	*/
	function getDate() {
		return (string) $this->date;
	} // end function

	/**
	* Returns $language variable
	*
	* @desc Returns $language variable
	* @return string $language
	* @see $language
	*/
	function getLanguage() {
		return (string) $this->language;
	} // end function

	/**
	* Returns $rights variable
	*
	* @desc Returns $rights variable
	* @return string $rights
	* @see $rights
	*/
	function getRights() {
		return (string) $this->rights;
	} // end function

	/**
	* Returns $coverage variable
	*
	* @desc Returns $coverage variable
	* @return string $coverage
	* @see $coverage
	*/
	function getCoverage() {
		return (string) $this->coverage;
	} // end function

	/**
	* Returns $contributor variable
	*
	* @desc Returns $contributor variable
	* @return string $contributor
	* @see $contributor
	*/
	function getContributor() {
		return (string) $this->contributor;
	} // end function

	/**
	* Returns $image_link variable
	*
	* @desc Returns $image_link variable
	* @return string $image_link
	* @see $image_link
	*/
	function getImageLink() {
		return (string) $this->image_link;
	} // end function

	/**
	* Returns $period variable
	*
	* @desc Returns $period variable
	* @return string $period
	* @see $period
	*/
	function getPeriod() {
		return (string) $this->period;
	} // end function

	/**
	* Returns $frequency variable
	*
	* @desc Returns $frequency variable
	* @return string $frequency
	* @see $frequency
	*/
	function getFrequency() {
		return (int) $this->frequency;
	} // end function

	/**
	* Returns $base variable
	*
	* @desc Returns $base variable
	* @return string $base
	* @see $base
	*/
	function getBase() {
		return (string) $this->base;
	} // end function

	/**
	* Returns $category variable
	*
	* @desc Returns $category variable
	* @return string $category
	* @see $category
	* @since 1.001 - 2003-05-30
	*/
	function getCategory() {
		return (string) $this->category;
	} // end function

	/**
	* Returns $cache variable
	*
	* @desc Returns $cache variable
	* @return int $cache
	* @see $cache
	* @since 1.001 - 2003-05-30
	*/
	function getCache() {
		return (int) $this->cache;
	} // end function

	/**
	* Adds another rss item to the object
	*
	* @desc Adds another rss item to the object
	* @param string $about  URL
	* @param string $title
	* @param string $link  URL
	* @param string $description (optional)
	* @param string $subject  some sort of category (optional dc value - only shows up if DC data has been set before)
	* @param string $date  format: 2003-05-29T00:03:07+0200 (optional dc value - only shows up if DC data has been set before)
	* @param string $author  some sort of category author of item
	* @param string $comments  url to comment page rss 2.0 value
	* @param string $image  optional mod_im value for dispaying a different pic for every item
	* @return void
	* @see $items
	* @uses RSSItem
	*/
	function addItem($about = '',
					$title = '',
					$link = '',
					$description = '',
					$subject = '',
					$date = '',
					$author = '',
					$comments = '',
					$image = '') {

		$item = new RSSItem($about,
							$title,
							$link,
							$description,
							$subject,
							$date,
							$author,
							$comments,
							$image);
		$this->items[] = $item;
	} // end function

	/**
	* Deletes a rss item from the array
	*
	* @desc Deletes a rss item from the array
	* @param int $id  id of the element in the $items array
	* @return boolean true if item was deleted
	* @see $items
	*/
	function deleteItem($id = -1) {
		if (array_key_exists($id, $this->items)) {
			unset($this->items[$id]);
			return (boolean) TRUE;
		} else {
			return (boolean) FALSE;
		} // end if
	} // end function

	/**
	* Returns an array with all the keys of the $items array
	*
	* @desc Returns an array with all the keys of the $items array
	* @return array array with all the keys of the $items array
	* @see $items
	*/
	function getItemList() {
		return (array) array_keys($this->items);
	} // end function

	/**
	* Returns the $items array
	*
	* @desc Returns the $items array
	* @return array $items
	*/
	function getItems() {
		return (array) $this->items;
	} // end function

	/**
	* Returns a single rss item by ID
	*
	* @desc Returns a single rss item by ID
	* @param int $id  id of the element in the $items array
	* @return mixed RSSItem or FALSE
	* @see RSSItem
	*/
	function getItem($id = -1) {
		if (array_key_exists($id, $this->items)) {
			return (object) $this->items[$id];
		} else {
			return (boolean) FALSE;
		} // end if
	} // end function
	/**#@-*/

	/**#@+
	* @return void
	* @access private
	*/
	/**
	* creates the output based on the 0.91 rss version
	*
	* @desc creates the output based on the 0.91 rss version
	* @see $output
	*/
	function createOutputV090() {
		// not implemented
		$this->createOutputV100();
	} // end function

	/**
	* creates the output based on the 0.91 rss version
	*
	* @desc creates the output based on the 0.91 rss version
	* @see $output
	* @since 1.001 - 2003-05-30
	*/
	function createOutputV091() {
		$this->output  = (string) '<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">' . "\n";
		$this->output .= (string) '<rss version="0.91">' . "\n";
		$this->output .= (string) '<channel>' . "\n";

		if (strlen($this->rights) > 0) {
			$this->output .= (string) '<copyright>' . $this->rights . '</copyright>' . "\n";
		} // end if

		if (strlen($this->date) > 0) {
			$this->output .= (string) '<pubDate>' .$this->date . '</pubDate>' . "\n";
			$this->output .= (string) '<lastBuildDate>' .$this->date . '</lastBuildDate>' . "\n";
		} // end if

		if (strlen($this->about) > 0) {
			$this->output .= (string) '<docs>' . $this->about . '</docs>' . "\n";
		} // end if

		if (strlen($this->description) > 0) {
			$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
		} // end if

		if (strlen($this->about) > 0) {
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
		} // end if

		if (strlen($this->title) > 0) {
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
		} // end if

		if (strlen($this->image_link) > 0) {
			$this->output .= (string) '<image>' . "\n";
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
			$this->output .= (string) '<url>' . $this->image_link . '</url>' . "\n";
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
			if (strlen($this->description) > 0) {
				$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
			} // end if
			$this->output .= (string) '</image>' . "\n";
		} // end if

		if (strlen($this->publisher) > 0) {
			$this->output .= (string) '<managingEditor>' . $this->publisher . '</managingEditor>' . "\n";
		} // end if

		if (strlen($this->creator) > 0) {
			$this->output .= (string) '<webMaster>' . $this->creator . '</webMaster>' . "\n";
		} // end if

		if (strlen($this->language) > 0) {
			$this->output .= (string) '<language>' . $this->language . '</language>' . "\n";
		} // end if

		if (count($this->getItemList()) > 0) {
			foreach ($this->getItemList() as $id) {
				$item =& $this->items[$id];

				if (strlen($item->getTitle()) > 0 && strlen($item->getLink()) > 0) {
					$this->output .= (string) '<item>' . "\n";
					$this->output .= (string) '<title>' . $item->getTitle() . '</title>' . "\n";
					$this->output .= (string) '<link>' . $item->getLink() . '</link>' . "\n";
					if (strlen($item->getDescription()) > 0) {
						$this->output .= (string) '<description>' . $item->getDescription() . '</description>' . "\n";
					} // end if
					$this->output .= (string) '</item>' . "\n";
				} // end if
			} // end foreach
		} // end if

		$this->output .= (string) '</channel>' . "\n";
		$this->output .= (string) '</rss>' . "\n";
	} // end function

	/**
	* creates the output based on the 1.0 rss version
	*
	* @desc creates the output based on the 1.0 rss version
	* @see $output
	*/
	function createOutputV100() {
		$this->output  = (string) '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:im="http://purl.org/rss/1.0/item-images/" ';

		if ($this->use_dc_data === TRUE) {
			$this->output .= (string) 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
		} // end if

		if ($this->use_sy_data === TRUE) {
			$this->output .= (string) 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" ';
		} // end if

		$this->output .= (string) 'xmlns="http://purl.org/rss/1.0/">' . "\n";

		if (strlen($this->about) > 0) {
			$this->output .= (string) '<channel rdf:about="' . $this->about . '">' . "\n";
		} else {
			$this->output .= (string) '<channel>' . "\n";
		} // end if

		if (strlen($this->title) > 0) {
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
		} // end if

		if (strlen($this->about) > 0) {
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
		} // end if

		if (strlen($this->description) > 0) {
			$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
		} // end if

		// additional dc data
		if (strlen($this->publisher) > 0) {
			$this->output .= (string) '<dc:publisher>' . $this->publisher . '</dc:publisher>' . "\n";
		} // end if

		if (strlen($this->creator) > 0) {
			$this->output .= (string) '<dc:creator>' . $this->creator . '</dc:creator>' . "\n";
		} // end if

		if (strlen($this->date) > 0) {
			$this->output .= (string) '<dc:date>' .$this->date . '</dc:date>' . "\n";
		} // end if

		if (strlen($this->language) > 0) {
			$this->output .= (string) '<dc:language>' . $this->language . '</dc:language>' . "\n";
		} // end if

		if (strlen($this->rights) > 0) {
			$this->output .= (string) '<dc:rights>' . $this->rights . '</dc:rights>' . "\n";
		} // end if

		if (strlen($this->coverage) > 0) {
			$this->output .= (string) '<dc:coverage>' . $this->coverage . '</dc:coverage>' . "\n";
		} // end if

		if (strlen($this->contributor) > 0) {
			$this->output .= (string) '<dc:contributor>' . $this->contributor . '</dc:contributor>' . "\n";
		} // end if

		// additional SY data
		if (strlen($this->period) > 0) {
			$this->output .= (string) '<sy:updatePeriod>' . $this->period . '</sy:updatePeriod>' . "\n";
		} // end if

		if (strlen($this->frequency) > 0) {
			$this->output .= (string) '<sy:updateFrequency>' . $this->frequency . '</sy:updateFrequency>' . "\n";
		} // end if

		if (strlen($this->base) > 0) {
			$this->output .= (string) '<sy:updateBase>' . $this->base . '</sy:updateBase>' . "\n";
		} // end if

		if (strlen($this->image_link) > 0) {
			$this->output .= (string) '<image rdf:about="' . $this->image_link . '">' . "\n";
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
			$this->output .= (string) '<url>' . $this->image_link . '</url>' . "\n";
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
			if (strlen($this->description) > 0) {
				$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
			} // end if
			$this->output .= (string) '</image>' . "\n";
		} // end if

		if (count($this->getItemList()) > 0) {
			$this->output .= (string) '<items><rdf:Seq>' . "\n";
			foreach ($this->getItemList() as $id) {
				$item =& $this->items[$id];
				if (strlen($item->getAbout()) > 0) {
					$this->output .= (string) ' <rdf:li resource="' . $item->getAbout() . '" />' . "\n";
				} // end if
			} // end foreach
			$this->output .= (string) '</rdf:Seq></items>' . "\n";
		} // end if
		$this->output .= (string) '</channel>' . "\n";

		if (count($this->getItemList()) > 0) {
			foreach ($this->getItemList() as $id) {
				$item =& $this->items[$id];

				if (strlen($item->getTitle()) > 0 && strlen($item->getLink()) > 0) {
					if (strlen($item->getAbout()) > 0) {
						$this->output .= (string) '<item rdf:about="' . $item->getAbout() . '">' . "\n";
					} else {
						$this->output .= (string) '<item>' . "\n";
					} // end if

					$this->output .= (string) '<title>' . $item->getTitle() . '</title>' . "\n";
					$this->output .= (string) '<link>' . $item->getLink() . '</link>' . "\n";

					if (strlen($item->getDescription()) > 0) {
						$this->output .= (string) '<description>' . $item->getDescription() . '</description>' . "\n";
					} // end if

					if ($this->use_dc_data === TRUE && strlen($item->getSubject()) > 0) {
						$this->output .= (string) '<dc:subject>' . $item->getSubject() . '</dc:subject>' . "\n";
					} // end if

					if ($this->use_dc_data === TRUE && strlen($item->getDate()) > 0) {
						$this->output .= (string) '<dc:date>' . $item->getDate() . '</dc:date>' . "\n";
					} // end if

					if (strlen($item->getImage()) > 0) {
						$this->output .= (string) '<im:image>' . $item->getImage() . '</im:image>' . "\n";
					} // end if

					$this->output .= (string) '</item>' . "\n";
				} // end if
			} // end foreach
		} // end if

		$this->output .= (string) '</rdf:RDF>';
	} // end function

	/**
	* creates the output based on the 2.0 rss draft
	*
	* @desc creates the output based on the 0.91 rss draft
	* @see $output
	* @since 1.001 - 2003-05-30
	*/
	function createOutputV200() {
		$this->output  = (string) '<rss version="2.0" xmlns:im="http://purl.org/rss/1.0/item-images/" ';

		if ($this->use_dc_data === TRUE) {
			$this->output .= (string) 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
		} // end if

		if ($this->use_sy_data === TRUE) {
			$this->output .= (string) 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" ';
		} // end if

		$this->output .= (string) '>' . "\n";

		$this->output .= (string) '<channel>' . "\n";

		if (strlen($this->rights) > 0) {
			$this->output .= (string) '<copyright>' . $this->rights . '</copyright>' . "\n";
		} // end if

		if (strlen($this->date) > 0) {
			$this->output .= (string) '<pubDate>' .$this->date . '</pubDate>' . "\n";
			$this->output .= (string) '<lastBuildDate>' .$this->date . '</lastBuildDate>' . "\n";
		} // end if

		if (strlen($this->about) > 0) {
			$this->output .= (string) '<docs>' . $this->about . '</docs>' . "\n";
		} // end if

		if (strlen($this->description) > 0) {
			$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
		} // end if

		if (strlen($this->about) > 0) {
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
		} // end if

		if (strlen($this->title) > 0) {
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
		} // end if

		if (strlen($this->image_link) > 0) {
			$this->output .= (string) '<image>' . "\n";
			$this->output .= (string) '<title>' . $this->title . '</title>' . "\n";
			$this->output .= (string) '<url>' . $this->image_link . '</url>' . "\n";
			$this->output .= (string) '<link>' . $this->about . '</link>' . "\n";
			if (strlen($this->description) > 0) {
				$this->output .= (string) '<description>' . $this->description . '</description>' . "\n";
			} // end if
			$this->output .= (string) '</image>' . "\n";
		} // end if

		if (strlen($this->publisher) > 0) {
			$this->output .= (string) '<managingEditor>' . $this->publisher . '</managingEditor>' . "\n";
		} // end if

		if (strlen($this->creator) > 0) {
			$this->output .= (string) '<webMaster>' . $this->creator . '</webMaster>' . "\n";
			$this->output .= (string) '<generator>' . $this->creator . '</generator>' . "\n";
		} // end if

		if (strlen($this->language) > 0) {
			$this->output .= (string) '<language>' . $this->language . '</language>' . "\n";
		} // end if

		if (strlen($this->category) > 0) {
			$this->output .= (string) '<category>' . $this->category . '</category>' . "\n";
		} // end if

		if (strlen($this->cache) > 0) {
			$this->output .= (string) '<ttl>' . $this->cache . '</ttl>' . "\n";
		} // end if


		// additional dc data
		if (strlen($this->publisher) > 0) {
			$this->output .= (string) '<dc:publisher>' . $this->publisher . '</dc:publisher>' . "\n";
		} // end if

		if (strlen($this->creator) > 0) {
			$this->output .= (string) '<dc:creator>' . $this->creator . '</dc:creator>' . "\n";
		} // end if

		if (strlen($this->date) > 0) {
			$this->output .= (string) '<dc:date>' .$this->date . '</dc:date>' . "\n";
		} // end if

		if (strlen($this->language) > 0) {
			$this->output .= (string) '<dc:language>' . $this->language . '</dc:language>' . "\n";
		} // end if

		if (strlen($this->rights) > 0) {
			$this->output .= (string) '<dc:rights>' . $this->rights . '</dc:rights>' . "\n";
		} // end if

		if (strlen($this->coverage) > 0) {
			$this->output .= (string) '<dc:coverage>' . $this->coverage . '</dc:coverage>' . "\n";
		} // end if

		if (strlen($this->contributor) > 0) {
			$this->output .= (string) '<dc:contributor>' . $this->contributor . '</dc:contributor>' . "\n";
		} // end if

		// additional SY data
		if (strlen($this->period) > 0) {
			$this->output .= (string) '<sy:updatePeriod>' . $this->period . '</sy:updatePeriod>' . "\n";
		} // end if

		if (strlen($this->frequency) > 0) {
			$this->output .= (string) '<sy:updateFrequency>' . $this->frequency . '</sy:updateFrequency>' . "\n";
		} // end if

		if (strlen($this->base) > 0) {
			$this->output .= (string) '<sy:updateBase>' . $this->base . '</sy:updateBase>' . "\n";
		} // end if

		if (count($this->getItemList()) > 0) {
			foreach ($this->getItemList() as $id) {
				$item =& $this->items[$id];

				if (strlen($item->getTitle()) > 0 && strlen($item->getLink()) > 0) {
					$this->output .= (string) '<item>' . "\n";
					$this->output .= (string) '<title>' . $item->getTitle() . '</title>' . "\n";
					$this->output .= (string) '<link>' . $item->getLink() . '</link>' . "\n";

					if (strlen($item->getDescription()) > 0) {
						$this->output .= (string) '<description>' . $item->getDescription() . '</description>' . "\n";
					} // end if

					if ($this->use_dc_data === TRUE && strlen($item->getSubject()) > 0) {
						$this->output .= (string) '<category>' . $item->getSubject() . '</category>' . "\n";
					} // end if

					if ($this->use_dc_data === TRUE && strlen($item->getDate()) > 0) {
						$this->output .= (string) '<pubDate>' . $item->getDate() . '</pubDate>' . "\n";
					} // end if

					if (strlen($item->getAbout()) > 0) {
						$this->output .= (string) '<guid>' . $item->getAbout() . '</guid>' . "\n";
					} // end if

					if (strlen($item->getAuthor()) > 0) {
						$this->output .= (string) '<author>' . $item->getAuthor() . '</author>' . "\n";
					} // end if

					if (strlen($item->getComments()) > 0) {
						$this->output .= (string) '<comments>' . $item->getComments() . '</comments>' . "\n";
					} // end if

					if (strlen($item->getImage()) > 0) {
						$this->output .= (string) '<im:image>' . $item->getImage() . '</im:image>' . "\n";
					} // end if
					$this->output .= (string) '</item>' . "\n";
				} // end if
			} // end foreach
		} // end if

		$this->output .= (string) '</channel>' . "\n";
		$this->output .= (string) '</rss>' . "\n";
	} // end function

	/**
	* creates the output
	*
	* @desc creates the output
	* @uses createOutputV090()
	* @uses createOutputV091()
	* @uses createOutputV200()
	* @uses createOutputV100()
	*/
	function createOutput($version = '') {
		if (strlen(trim($version)) === 0) {
			$version = (string) '1.0';
		} // end if

		switch ($version) {
			case '0.9':
				$this->createOutputV090();
				break;
			case '0.91':
				$this->createOutputV091();
				break;
			case '2.00':
				$this->createOutputV200();
				break;
			case '1.0':
			default:
				$this->createOutputV100();
				break;
		} // end switch
	} // end function
	/**
	* echos the output
	*
	* use this function if you want to directly output the rss stream
	*
	* @desc echos the output
	* @return void
	* @access public
	* @uses createOutput()
	*/
	function outputRSS($version = '') {
		if (!isset($this->output)) {
			$this->createOutput($version);
		} // end if

		# Mantis: text/xml -> application/xml
		header ('content-type: application/xml');
		header('Content-Disposition: inline; filename=rss_' . str_replace(' ','',$this->title) . '.xml');
		$this->output = '<?xml version="1.0" encoding="' . $this->encoding . '"?>' . "\n" .
						'<!--  RSS generated by Flaimo.com RSS Builder [' .  date('Y-m-d H:i:s')  .']  --> ' . $this->output;
		echo $this->output;
	} // end function

	/**
	* returns the output
	*
	* use this function if you want to have the output stream as a string (for example to write it in a cache file)
	*
	* @desc returns the output
	*/
	function getRSSOutput($version = '') {
		if (!isset($this->output)) {
			$this->createOutput($version);
		} // end if
		return (string) '<?xml version="1.0" encoding="' . $this->encoding . '"?>' . "\n" .
						'<!--  RSS generated by Flaimo.com RSS Builder [' .  date('Y-m-d H:i:s')  .']  --> ' . $this->output;
	} // end function
	/**#@-*/
} // end class RSSBuilder

//---------------------------------------------------------------------------

/**
* single rss item object
*
* Tested with WAMP (XP-SP1/1.3.27/4.0.12/4.3.2)
* Last change: 2003-06-26
*
* @desc single rss item object
* @access private
* @author Michael Wimmer <flaimo@gmx.net>
* @copyright Michael Wimmer
* @link http://www.flaimo.com/
* @package RSSBuilder
* @category FLP
* @version 1.002
*/
class RSSItem extends RSSBase {

	/*-------------------*/
	/* V A R I A B L E S */
	/*-------------------*/

	/**#@+
	* @access private
	* @var string
	*/
	/**
	* URL
	*
	* @desc URL
	*/
	var $about;

	/**
	* headline
	*
	* @desc headline
	*/
	var $title;

	/**
	* URL to the full item
	*
	* @desc URL to the full item
	*/
	var $link;

	/**
	* optional description
	*
	* @desc optional description
	*/
	var $description;

	/**
	* optional subject (category)
	*
	* @desc optional subject (category)
	*/
	var $subject;

	/**
	* optional date
	*
	* @desc optional date
	*/
	var $date;

	/**
	* author of item
	*
	* @desc author of item
	* @since 1.001 - 2003-05-30
	*/
	var $author;

	/**
	* url to comments page (rss 2.0)
	*
	* @desc url to comments page (rss 2.0)
	* @since 1.001 - 2003-05-30
	*/
	var $comments;

	/**
	* imagelink for this item (mod_im only)
	*
	* @desc imagelink for this item (mod_im only)
	* @since 1.002 - 2003-06-26
	*/
	var $image;
	/**#@-*/

	/*-----------------------*/
	/* C O N S T R U C T O R */
	/*-----------------------*/

	/**#@+
	* @access private
	* @return void
	*/
	/**
	* Constructor
	*
	* @desc Constructor
	* @param string $about  URL
	* @param string $title
	* @param string $link  URL
	* @param string $description (optional)
	* @param string $subject  some sort of category (optional)
	* @param string $date  format: 2003-05-29T00:03:07+0200 (optional)
	* @param string $author  some sort of category author of item
	* @param string $comments  url to comment page rss 2.0 value
	* @param string $image  optional mod_im value for dispaying a different pic for every item
	* @uses setAbout()
	* @uses setTitle()
	* @uses setLink()
	* @uses setDescription()
	* @uses setSubject()
	* @uses setDate()
	* @uses setAuthor()
	* @uses setComments()
	* @uses setImage()
	*/
	function RSSItem($about = '',
					$title = '',
					$link = '',
					$description = '',
					$subject = '',
					$date = '',
					$author = '',
					$comments = '',
					$image = '') {
		$this->setAbout($about);
		$this->setTitle($title);
		$this->setLink($link);
		$this->setDescription($description);
		$this->setSubject($subject);
		$this->setDate($date);
		$this->setAuthor($author);
		$this->setComments($comments);
		$this->setImage($image);
	} // end constructor


	/**
	* Sets $about variable
	*
	* @desc Sets $about variable
	* @param string $about
	* @see $about
	*/
	function setAbout($about = '') {
		if (!isset($this->about) && strlen(trim($about)) > 0) {
			$this->about = (string) trim($about);
		} // end if
	} // end function

	/**
	* Sets $title variable
	*
	* @desc Sets $title variable
	* @param string $title
	* @see $title
	*/
	function setTitle($title = '') {
		if (!isset($this->title) && strlen(trim($title)) > 0) {
			$this->title = (string) trim($title);
		} // end if
	} // end function

	/**
	* Sets $link variable
	*
	* @desc Sets $link variable
	* @param string $link
	* @see $link
	*/
	function setLink($link = '') {
		if (!isset($this->link) && strlen(trim($link)) > 0) {
			$this->link = (string) trim($link);
		} // end if
	} // end function

	/**
	* Sets $description variable
	*
	* @desc Sets $description variable
	* @param string $description
	* @see $description
	*/
	function setDescription($description = '') {
		if (!isset($this->description) && strlen(trim($description)) > 0) {
			$this->description = (string) trim($description);
		} // end if
	} // end function

	/**
	* Sets $subject variable
	*
	* @desc Sets $subject variable
	* @param string $subject
	* @see $subject
	*/
	function setSubject($subject = '') {
		if (!isset($this->subject) && strlen(trim($subject)) > 0) {
			$this->subject = (string) trim($subject);
		} // end if
	} // end function

	/**
	* Sets $date variable
	*
	* @desc Sets $date variable
	* @param string $date
	* @see $date
	*/
	function setDate($date = '') {
		if (!isset($this->date) && strlen(trim($date)) > 0) {
			$this->date = (string) trim($date);
		} // end if
	} // end function

	/**
	* Sets $author variable
	*
	* @desc Sets $author variable
	* @param string $author
	* @see $author
	* @since 1.001 - 2003-05-30
	*/
	function setAuthor($author = '') {
		if (!isset($this->author) && strlen(trim($author)) > 0) {
			$this->author = (string) trim($author);
		} // end if
	} // end function

	/**
	* Sets $comments variable
	*
	* @desc Sets $comments variable
	* @param string $comments
	* @see $comments
	* @since 1.001 - 2003-05-30
	*/
	function setComments($comments = '') {
		if (!isset($this->comments) && strlen(trim($comments)) > 0) {
			$this->comments = (string) trim($comments);
		} // end if
	} // end function

	/**
	* Sets $image variable
	*
	* @desc Sets $image variable
	* @param string $image
	* @see $image
	* @since 1.002 - 2003-06-26
	*/
	function setImage($image = '') {
		if (!isset($this->image) && strlen(trim($image)) > 0) {
			$this->image = (string) trim($image);
		} // end if
	} // end function
	/**#@-*/

	/**#@+
	* @access public
	*/
	/**
	* Returns $about variable
	*
	* @desc Returns $about variable
	* @return string $about
	* @see $about
	*/
	function getAbout() {
		return (string) $this->about;
	} // end function

	/**
	* Returns $title variable
	*
	* @desc Returns $title variable
	* @return string $title
	* @see $title
	*/
	function getTitle() {
		return (string) $this->title;
	} // end function

	/**
	* Returns $link variable
	*
	* @desc Returns $link variable
	* @return string $link
	* @see $link
	*/
	function getLink() {
		return (string) $this->link;
	} // end function

	/**
	* Returns $description variable
	*
	* @desc Returns $description variable
	* @return string $description
	* @see $description
	*/
	function getDescription() {
		return (string) $this->description;
	} // end function

	/**
	* Returns $subject variable
	*
	* @desc Returns $subject variable
	* @return string $subject
	* @see $subject
	*/
	function getSubject() {
		return (string) $this->subject;
	} // end function

	/**
	* Returns $date variable
	*
	* @desc Returns $date variable
	* @return string $date
	* @see $date
	*/
	function getDate() {
		return (string) $this->date;
	} // end function

	/**
	* Returns $author variable
	*
	* @desc Returns $author variable
	* @return string $author
	* @see $author
	* @since 1.001 - 2003-05-30
	*/
	function getAuthor() {
		return (string) $this->author;
	} // end function

	/**
	* Returns $comments variable
	*
	* @desc Returns $comments variable
	* @return string $comments
	* @see $comments
	* @since 1.001 - 2003-05-30
	*/
	function getComments() {
		return (string) $this->comments;
	} // end function

	/**
	* Returns $image variable
	*
	* @desc Returns $image variable
	* @return string $image
	* @see $image
	* @since 1.002 - 2003-06-26
	*/
	function getImage() {
		return (string) $this->image;
	} // end function
	/**#@-*/
} // end class RSSItem
?>
