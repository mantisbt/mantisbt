/*
 * Mantis - a php based bugtracking system
 * Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * This program is distributed under the terms and conditions of the GPL
 * See the README and LICENSE files for details
 *
 * --------------------------------------------------------
 * $Id: addLoadEvent.js,v 1.2 2005-02-12 20:03:50 jlatour Exp $
 * --------------------------------------------------------
 */
/*
Care Of:
		Simon Willison
		http://simon.incutio.com/archive/2004/05/26/addLoadEvent
		Thnx Dude!

******** USEAGE ********************************
addLoadEvent(nameOfSomeFunctionToRunOnPageLoad);
addLoadEvent(function() {
  // more code to run on page load
});
*/
function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}
