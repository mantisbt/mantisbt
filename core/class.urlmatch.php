<?php
// Class mantisLink
//
// 09-18-2004 Bastian Pfennigschmidt for Mantis Bugtracker
class mantisLink extends urlmatch
{
	function mantisLink()
	{
		parent::urlmatch();
		$this->charLimit = 50;
		$this->breakTxt = '...';
		$this->addProperty("target=\"_blank\"");
	}
	
	function match($string, $screenTxt="")
	{
		$urls = $this->_getUrls($string, $screenTxt);
		
		while (list($key, $value) = each($urls))
		{
			$string = str_replace(
					$value["url"],
					"<a href=\"".$value["url_prepend"].$value["url"]."\">".$this->_screenURL($value["url"])."</a> ".
					$value["prepend"].$value["url_prepend"].$value["url"]."\">".$this->_screenURL($value["url"],$screenTxt)."</a>",
					$string
					);	
		}
		
		return $string;
	}
}

/*
class: urlmatch
released: 08-19-2002
revised: 11-21-2002
revised: 09-18-2004 Bastian Pfennigschmidt
author: kumar (kumar@chicagomodular.com)

****description: 

Looks for any URLs in a string of text and matches each one with a customizable <a> tag.  Convenient for parsing user input where users are not expected to write HTML.  The class finds URLs starting with 'http|https|ftp|www', ignores HTML tags, supports the addition of <a> properties (such as defining a CSS class), and supports a character limit for the displayed link text.  I have tested it with many possibilities but please don't hesitate to report any bugs to my above email address.  To-Do: matching email addresses, if the need arises.

****revision 09-18-2004:	Bastian Pfennigschmidt

Better trim functions
Move link output into additional methode to make overwrite possible
Accept @ in URL

****revision 11-21-2002:

Small fix to detect tabs and PC/MS-DOS line breaks.

****revision 10-14-2002:

Fixed many bugs relating to detecting punctuation and characters within the URL or trailing the URL, matching multiple URLs (as in a list), URLs appearing within or around HTML tags or as HTML source... there are many more URL-in-text scenarios supported now.  You may notice from the code, however, there are many preg checks to ensure the flexibility of the class.  If speed is a concern of yours, you may want to comment out the code that looks for HTML source (href="") or code that detects and deletes multiple http/www URLs.  Also, PHP 4.1.0 is now required but if anyone needs this for lesser versions, please email me. 


****requirements:

PHP >= 4.1.0 (array_key_exists(), foreach())

license: The GNU General Public License (GPL)
http://www.opensource.org/licenses/gpl-license.html

*/

class urlmatch
{
	// @public: -- edit here or set values in your script or child class:
	
	var $charLimit = 50; // num of chars to limit for the screen-displayed URL
	var $breakTxt = "..."; // text to show at the end of a break, if the char limit is reached
	var $startFromLeft;	// this is for compatibilty only - please use $breakPosition instead
	var $breakPosition = "smart"; // in the event your screen URL is longer than the character limit :
							   // left: add the break at the left, shows the directory side [right side] of the URL
							   // right: add the break at the right, shows the domain side of the URL [left side]
							   // smart: add the break at a smart position, shows beginning and end of the URL
	var $debug = FALSE; // set this to TRUE to display internal matching procedure
	
	// @private: -- do not edit:
	
	var $tags = array();
	var $matchUrl;
	var $matchHtmlSrc;
	var $matchOpeningPunctuation;
	var $matchClosingPunctuation;
		
	function urlmatch()
	// constructor
	{
		$this->matchHtmlSrc = "/^href=\"?.*/i";
		$this->matchOpeningPunctuation = "/^('|\").*/";
		$this->matchClosingPunctuation = "/(\?|\(|\)|\"|'|\.|,|;|!)$/";
		
		$this->matchUrl = implode("",array(
		"/^'?\"?" // possible opening punctuation
		,"(www|ftp:\/\/|http:\/\/|https:\/\/)" // match opening of a URL, possibly in parenthesis, quotes, etc.
		,"(.*@){0,1}"
		,"([\.[:alnum:]_-]){0,4}" // match 4 possible subdomains
		,"([[:alnum:]_-]+\.)" // match the domain
		,"([[:alnum:]_-]\.?)" // match possible top level sub domain
		,"([[:alpha:]]){0,3}" // match final top level domain
		,".*$" // match possible directories or query string, anything
		,"/i")); // perl modifiers: case-insensitive
	}
	
	function addProperty($strProperty)
	//
	// @public: call this to add any tag properties for the <a> tag
	//
	// @param: string $strProperty (i.e. "target=\"_blank\"")
	//
	{
		$this->tags[] = $strProperty;
	}
	
	function match($string,$screenTxt="")
	//
	// @public: Creates the link for output  
	//
	// @param: string $url (the matched URL passed from match() )
	// @param: string $prepend (prepend of url passed from match() )
	// @param: string $url_prepend (the url prepend of url passed from match() )
	//
	{
		$urls = $this->_getUrls($string, $screenTxt);
		
		while (list($key, $value) = each($urls))
		{
			$string = str_replace(
					$value["url"],
					$value["prepend"].$value["url_prepend"].$value["url"]."\">".$this->_screenURL($value["url"],$screenTxt)."</a>",
					$string
					);	
		}
		
		return $string;
	}
	
	function _getUrls($string,$screenTxt="")
	//
	// @private: call this to return the final string with all urls matched
	//
	// @param: string $string (entire string which may contain URLs)
	// @param: string $screenTxt (text, if any, which should be displayed for the matched URL)
	//
	{			
		// init token:
		$token = " \t\r\n<>()"; // split by any characters we know will not be in a URL
		$piece = strtok($string,$token); 
		$storedURLsToMatch = array();
		$htmlSrcUrlsToDelete = array();
		$urls = array();
		if($this->debug)
		{ 
			$i=1; 
			echo "string: ",htmlspecialchars($string),"<br />\n"; 
		} // END if($this->debug)
		
		// look through the string one piece at a time, pices being separated by the token above:
		while(!($piece === FALSE))
		{
			// init:
			$url_prepend="";
			if($this->debug)
			{
				echo "strtok $i: '",htmlspecialchars($piece),"'<br />\n"; 
				$i++; 
			} // END if($this->debug)
			
			if(preg_match($this->matchHtmlSrc,$piece))
			// if we have found a url already coded by means of href="http://etc...", delete that url from the match queue:
			{
				$piece = substr($piece,6,strlen($piece)-7); // get rid of href=" and trailing "
				if(preg_match($this->matchUrl,$piece)) 
				{
					$htmlSrcUrlsToDelete[] = $piece;
					if($this->debug){ 
						echo str_repeat("&nbsp;",4),"detected already coded url: '",htmlspecialchars($piece),"'<br />\n"; }
				} // end if(preg_match($this->matchUrl,$piece)) 
			}  // end if(preg_match($this->matchHtmlSrc,$piece))
			
			if(preg_match($this->matchUrl,$piece,$theMatch))
			// if we have matched a URL:
			{	
				// init:
				$new_url = $theMatch[0];	
				$sameURLwithoutProtocol = "";
				
				// deal with www urls, and prepare for logging url, below:	
				if(strtolower($theMatch[1]) == "www") 
					$url_prepend .= "http://";
				else 
					$sameURLwithoutProtocol = str_replace($theMatch[1],"",$theMatch[0]);
				
				// find out if we matched some punctuation text which needs to be put back in place:
				if(preg_match($this->matchOpeningPunctuation,$theMatch[0],$openingPuncuationMatch))
				{
					$new_url = substr($new_url,strlen($openingPuncuationMatch[1]));
					if($this->debug)
						echo str_repeat("&nbsp;",4),"cleaning up url: '",htmlspecialchars($new_url),"'<br />\n";
					unset($openingPuncuationMatch);
				} // END if(preg_match($this->matchOpeningPunctuation,$theMatch[0],$openingPuncuationMatch))
				
				if(preg_match($this->matchClosingPunctuation,$new_url,$cleanup)) 
				// if the URL ends in unnecessary punctuation, such as a "?" or ")":
				{
					$new_url = substr($new_url,0,-strlen($cleanup[1]));
					if($this->debug){ 
						echo str_repeat("&nbsp;",4),"cleaning up url: '",htmlspecialchars($new_url),"'<br />\n"; }
				} // END if(preg_match($this->matchClosingPunctuation,$new_url,$cleanup))
				
				// log URLs for replacement, if not already logged:
				if(	!array_key_exists($new_url,$storedURLsToMatch) or 
						!array_key_exists($sameURLwithoutProtocol,$storedURLsToMatch) )
				{
					$storedURLsToMatch[$new_url] = $url_prepend;
				} // END if(!array_key_exists($new_url,$storedURLsToMatch) or ...
			} // END if(preg_match($this->matchUrl,$piece,$theMatch))
			$piece = strtok($token);
		} // end while(!($piece === FALSE))
				
		// define the shared prefix for each matched URL:
		$prepend = "<a ";
		for($i=0, $max=count($this->tags); $i<$max; $i++)
		{
			$prepend .= $this->tags[$i]." ";
		} // END for($i=0, $max=count($this->tags); $i<$max; $i++)
		$prepend .= "href=\"";
		
		foreach($htmlSrcUrlsToDelete as $alreadyCodedUrl)
		// delete any urls from the toMatch array which already exist as html src:
		{
			if(array_key_exists($alreadyCodedUrl,$storedURLsToMatch))
			{
				unset($storedURLsToMatch[$alreadyCodedUrl]);
				if($this->debug)
					echo str_repeat("&nbsp;",4),"deleting url: '$alreadyCodedUrl'<br />\n"; 
			} // END if(array_key_exists($alreadyCodedUrl,$storedURLsToMatch))
		} // END foreach($htmlSrcUrlsToDelete as $alreadyCodedUrl)
		
		foreach($storedURLsToMatch as $theMatchedURL => $url_prepend)
		// match each url found in the above search with an html tag:
		{
			$doTheReplace = TRUE;
			if(strtolower(substr($theMatchedURL,0,3)) == "www")
			// if no protocol, avoid replacing the same URL twice:
			{
				if(
					array_key_exists("http://".$theMatchedURL,$storedURLsToMatch) or
					array_key_exists("https://".$theMatchedURL,$storedURLsToMatch) or
					array_key_exists("ftp://".$theMatchedURL,$storedURLsToMatch))
					
					$doTheReplace = FALSE; 	// we can't process the www url in this text when 
											// there are duplicates containing http:// .. sorry.
			} // END if(strtolower(substr($theMatchedURL,0,3)) == "www")
			if($doTheReplace)
			{
				$urls[] = array("url" => $theMatchedURL, "prepend" => $prepend, "url_prepend" => $url_prepend); 
				
				if($this->debug)
					echo str_repeat("&nbsp;",4),"matching url: '$theMatchedURL'<br />\n";
			} // END if($doTheReplace)
		} // END foreach($storedURLsToMatch as $theMatchedURL => $url_prepend)
		
		// finish:
		if($this->debug) echo "\n<br />";
		return $urls;
	} // END function getUrls()
	
	function _screenURL($theScreenURL,$screenTxt="")
	//
	// @private: logic for the displayed link text.  
	// checks length of display text and will truncate to the string limit if necessary.
	//
	// @param: string $theScreenURL (the matched URL passed from match() )
	// @param: string $screenTxt ($screenTxt if passed from match() )
	//
	{
		// For compatibility:
		if ($this->startFromLeft)
			$this->breakPosition = "right";
		
		$output = (!empty($screenTxt)) ? $screenTxt: $theScreenURL;
		if(strlen($output) > $this->charLimit)
		{
			switch($this->breakPosition)
			{
				case "left":
					$output = $this->breakTxt . substr($output,-$this->charLimit);
				break;
				
				case "right":
					$output = substr($output,0,$this->charLimit) . $this->breakTxt;
				break;
				
				case "smart":
				default:
				   $lengthToKeep = $this->charLimit - strlen($this->breakTxt);
				   $modulo = $lengthToKeep % 2;
				   $start = 0;
				   $end = 0;
				   if ($modulo == 0) {
					   $start = $lengthToKeep / 2;
					   $end   = $start;
				   } else {
					   $start = intval($lengthToKeep / 2);
					   $end   = $start + 1;
				   }
				   $output = substr($output, 0, $start) .$this->breakTxt. substr($output, -$end);
				break;
			}
				
		} // END if(strlen($output) > $this->charLimit)
		return $output;
	} // END function screenURL($theScreenURL,$screenTxt="")
	
} // END class urlmatch
?>
