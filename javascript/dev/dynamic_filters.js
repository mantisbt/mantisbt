/*
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2012  MantisBT Team   - mantisbt-dev@lists.sourceforge.net

# Mantis is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# Mantis is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Mantis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * --------------------------------------------------------
 * $Id$
 * --------------------------------------------------------
 */
/*
// +----------------------------------------------------------------------+
// | Orginial Code Care Of:                                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 Bitflux GmbH                                      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the "License");      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an "AS IS" BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: Bitflux GmbH <devel@bitflux.ch>                              |
// |         http://blog.bitflux.ch/p1735.html                            |
// +----------------------------------------------------------------------+
//
//
// +----------------------------------------------------------------------+
// | Heavily Modified by Jeff Minard (07/09/04)                           |
// +----------------------------------------------------------------------+
// | Same stuff as above, yo!                                             |
// +----------------------------------------------------------------------+
// | Author: Jeff Minard <jeff-js@creatimation.net>                       |
// |         http://www.creatimation.net                                  |
// |         http://www.creatimation.net/journal/live-request             |
// +----------------------------------------------------------------------+
//
// +----------------------------------------------------------------------+
// | What is this nonsense?? (07/09/04)                                   |
// +----------------------------------------------------------------------+
// | This is a script that, by using XMLHttpRequest javascript objects    |
// | you can quickly add some very click live interactive feed back to    |
// | your pages that reuire server side interaction.                      |
// |                                                                      |
// | For instance, you use this to emulate a "live searching" feature     |
// | wherein users type in key phrases, and once they have stopped typing |
// | the script will automatically search and retrive *without* a page    |
// | reload.
// |                                                                      |
// | In another instance, I use this to product live comments by passing  |
// | the text to a Textile class that parses it to valid HTML. After      |
// | parsing, the html is returned and displayed on the page as the       |
// | user types.                                                          |
// +----------------------------------------------------------------------+
//
//
// +----------------------------------------------------------------------+
// | Modified by Lee O'Mara <lomara@omara.ca>                  12/08/2004 |
// +----------------------------------------------------------------------+
// | Hacked apart Jeff's script for use in Mantis.                        |
// |                                                                      |
// | This script gets filters from the server and displays them without   |
// | reloading the page.                                                  |
// |                                                                      |
// | The script tries to follow the notion of "unobtrusive javascript".   |
// | There are no event handlers in the HTML code. The events are added   |
// | on pageload(based on specific id names).                             |
// +----------------------------------------------------------------------+
*/

var processURI    = './return_dynamic_filters.php';
var liveReq = false;

/**
 * Build the XMLHttpRequest and send it
 */
function liveReqDoReq() {

	if (liveReq && liveReq.readyState < 4) {
		liveReq.abort();
	}

	if (window.XMLHttpRequest) {
		// branch for IE7, Firefox, Opera, etc.
		liveReq = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		// branch for IE5, IE6 via ActiveX
		liveReq = new ActiveXObject("Microsoft.XMLHTTP");
	}

	name = this.id;
	liveReq.onreadystatechange = function(){liveReqProcessReqChange(name);};
	t_view = document.getElementById('filters_form_open').elements['view_type'].value;
	liveReq.open("GET", processURI + "?view_type=" + t_view + "&filter_target=" + this.id);

	// show "Loading..." while waiting
	document.getElementById(this.id+'_target').innerHTML = string_loading;

	liveReq.send(null);

	return false;
}

/**
 * Processes the results of the XMLHttpRequest
 */
function liveReqProcessReqChange(name) {
	if (liveReq.readyState == 4) {
		document.getElementById(name+'_target').innerHTML = liveReq.responseText;
		replaceWithContent(name);
	}
}

/**
 * Strip the tag, leave the content.
 */
function replaceWithContent(id){
	tag = document.getElementById(id);
	if (!tag) return false;
	t_parent = tag.parentNode;
	if (!t_parent) return false;
	for(var i=0; i <tag.childNodes.length; i++){
		child = tag.childNodes[i].cloneNode(true);
		t_parent.insertBefore(child, tag);
	}
	t_parent.removeChild(tag);
}

/**
 * Initialise the filter links
 */
function labelInit(){
	// keep browsers that don't support DOM or
	// XMLHttpRequest from getting in trouble
	if (document.getElementById && 	(window.XMLHttpRequest || window.ActiveXObject)) {

		t_form = document.getElementById("filters_form_open");
		if (!t_form) return false;

		t_links = t_form.getElementsByTagName("a");
		if (!t_links) return false;

		for(var i=0; i < t_links.length; i++){
			var t_link = t_links[i];
			if (t_link.id.substring((t_link.id.length - 7), t_link.id.length) == "_filter"){
				// only attach event handler if a target is found
				if (document.getElementById(t_link.id+'_target')){
					// setup the event handler
					t_link.onclick = liveReqDoReq;
				} else {
					alert("missing target for:" +t_link.id);
				}
			}
		}
	}
}

addLoadEvent(labelInit);
