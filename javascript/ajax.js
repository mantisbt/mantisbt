/*
 * Mantis - a php based bugtracking system
 * Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * Copyright (C) 2002 - 2006  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * This program is distributed under the terms and conditions of the GPL
 * See the README and LICENSE files for details
 *
 * --------------------------------------------------------
 * $Id: ajax.js,v 1.1 2006-05-16 23:59:29 vboctor Exp $
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
	document.getElementById( targetElementId ).innerHTML = "Loading...";

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