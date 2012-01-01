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
 * String manipulation
 */
function Trim( p_string ) {
	if (typeof p_string != "string") {
		return p_string;
	}

	var t_string = p_string;
	var t_ch = '';

	// Trim beginning spaces

	t_ch = t_string.substring( 0, 1 );
	while ( t_ch == " " ) {
		t_string = t_string.substring( 1, t_string.length );
		t_ch = t_string.substring( 0, 1 );
	}

	// Trim trailing spaces

	t_ch = t_string.substring( t_string.length-1, t_string.length );
	while ( t_ch == " " ) {
		t_string = t_string.substring( 0, t_string.length-1 );
		t_ch = t_string.substring( t_string.length-1, t_string.length );
	}

	return t_string;
}

/*
 * Cookie functions
 */
function GetCookie( p_cookie ) {
	var t_cookie_name = "MANTIS_" + p_cookie;
	var t_cookies = document.cookie;

	t_cookies = t_cookies.split( ";" );

	var i = 0;
	while( i < t_cookies.length ) {
		var t_cookie = t_cookies[ i ];

		t_cookie = t_cookie.split( "=" );

		if ( Trim( t_cookie[ 0 ] ) == t_cookie_name ) {
			return( t_cookie[ 1 ] );
		}
		i++;
	}

	return -1;
}

function SetCookie( p_cookie, p_value ) {
	var t_cookie_name = "MANTIS_" + p_cookie;
	var t_expires = new Date();

	t_expires.setTime( t_expires.getTime() + (365 * 24 * 60 * 60 * 1000));

	document.cookie = t_cookie_name + "=" + p_value + "; expires=" + t_expires.toUTCString() + ";";
}

/*
 * Collapsible element functions
 */
var g_collapse_clear = 1;

function ToggleDiv( p_div ) {
	t_open_div = document.getElementById( p_div + "_open" );
	t_closed_div = document.getElementById( p_div + "_closed" );

	t_cookie = GetCookie( "collapse_settings" );
	if ( 1 == g_collapse_clear ) {
		t_cookie = "";
		g_collapse_clear = 0;
	}

	if ( t_open_div.className == "hidden" ) {
		t_open_div.className = "";
		t_closed_div.className = "hidden";
		t_cookie = t_cookie + "|" + p_div + ",1";
	} else {
		t_closed_div.className = "";
		t_open_div.className = "hidden";
		t_cookie = t_cookie + "|" + p_div + ",0";
	}

	SetCookie( "collapse_settings", t_cookie );
}

/* Check checkboxes */
function checkall( p_formname, p_state) {
	var t_elements = (eval("document." + p_formname + ".elements"));

	for (var i = 0; i < t_elements.length; i++) {
		if(t_elements[i].type == 'checkbox') {
			t_elements[i].checked = p_state;
		}
	}
}

// global code to determine how to set visibility
var a = navigator.userAgent.indexOf("MSIE");
var style_display;

if (a!= -1) {
	style_display = 'block';
} else {
	style_display = 'table-row';
}
style_display = 'block';

function setDisplay(idTag, state)
{
	if(!document.getElementById(idTag)) alert('SetDisplay(): id '+idTag+' is empty');
	// change display visibility
	if ( state != 0 ) {
		document.getElementById(idTag).style.display = style_display;
	} else {
		document.getElementById(idTag).style.display = 'none';
	}
}

function toggleDisplay(idTag)
{
	setDisplay( idTag, (document.getElementById(idTag).style.display == 'none')?1:0 );
}

/* Append a tag name to the tag input box, with repect for tag separators, etc */
function tag_string_append( p_string ) {
	t_tag_separator = document.getElementById('tag_separator').value;
	t_tag_string = document.getElementById('tag_string');
	t_tag_select = document.getElementById('tag_select');

	if ( Trim( p_string ) == '' ) { return; }

	if ( t_tag_string.value != '' ) {
		t_tag_string.value = t_tag_string.value + t_tag_separator + p_string;
	} else {
		t_tag_string.value = t_tag_string.value + p_string;
	}
	t_tag_select.selectedIndex=0;
}
