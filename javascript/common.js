/*
 * Mantis - a php based bugtracking system
 * Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
 * This program is distributed under the terms and conditions of the GPL
 * See the README and LICENSE files for details
 *
 * --------------------------------------------------------
 * $Id: common.js,v 1.7 2005-05-10 17:56:40 thraxisp Exp $
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

var g_div_history		= 0x0001;
var g_div_bugnotes		= 0x0002;
var g_div_bugnote_add	= 0x0004;
var g_div_upload_form	= 0x0010;
var g_div_monitoring	= 0x0020;
var g_div_sponsorship	= 0x0040;
var g_div_relationships	= 0x0080;
var g_div_filter        = 0x0100;

function GetViewSettings() {
	var t_cookie = GetCookie( "VIEW_SETTINGS" );

	if ( -1 == t_cookie ) {
		t_cookie = 0xffff;
	} else {
		t_cookie = parseInt( t_cookie );
	}

	return t_cookie;
}

function SetDiv( p_div, p_cookie_bit ) {
	var t_view_settings = GetViewSettings();

	if( t_view_settings & p_cookie_bit ) {
		document.getElementById( p_div + "_open" ).style.display = "";
		document.getElementById( p_div + "_closed" ).style.display = "none";
	} else {
		document.getElementById( p_div + "_open" ).style.display = "none";
		document.getElementById( p_div + "_closed" ).style.display = "";
	}
}

function ToggleDiv( p_div, p_cookie_bit ) {
	var t_view_settings = GetViewSettings();

	t_view_settings ^= p_cookie_bit;
	SetCookie( "VIEW_SETTINGS", t_view_settings );

	SetDiv( p_div, p_cookie_bit );
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
