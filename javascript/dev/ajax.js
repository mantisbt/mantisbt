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

var processURI = './xmlhttprequest.php';
var liveReq = false;

// on !IE we only have to initialize it once
if (window.XMLHttpRequest) {
	liveReq = new XMLHttpRequest();
}

/**
 * Build the XMLHttpRequest and send it
 */
function AjaxLoad( targetElementId, queryString, elementIdToHide ) {
	if (liveReq && liveReq.readyState < 4) {
		liveReq.abort();
	}

	if (window.XMLHttpRequest) {
		// branch for IE/Windows ActiveX version
	} else if (window.ActiveXObject) {
		liveReq = new ActiveXObject("Microsoft.XMLHTTP");
	}

	name = this.id;
	liveReq.onreadystatechange = function() { liveReqProcessReqChange( targetElementId, elementIdToHide ); };
	liveReq.open("GET", processURI + "?" + queryString);

	// show "Loading..." while waiting
	document.getElementById( targetElementId ).innerHTML = loading_lang;

	liveReq.send(null);

	return false;
}

/**
 * Processes the results of the XMLHttpRequest
 */
function liveReqProcessReqChange( targetElementId, elementIdToHide ) {
	if (liveReq.readyState == 4) {
		document.getElementById(targetElementId).innerHTML = liveReq.responseText;
		document.getElementById(elementIdToHide).innerHTML = '';
	}
}
