<?
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000, 2001  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	global	$g_primary_color_dark, $g_primary_color_light,
			$g_category_title_color2, $g_white_color,
			$g_category_title_color;
?>
<style type="text/css">
body 				{ background-color: #ffffff; font-family:Verdana, Arial; font-size: 10pt; margin: 0px; margin-top: 6px; margin-bottom: 6px; }
p 					{ font-family: Verdana, Arial, Helvetica; font-size: 10pt }
p.center			{ text-align: center }
address 			{ font-family: Verdana, Arial, Helvetica; font-size: 8pt }
span				{ font-family: Verdana, Arial, Helvetica; font-size: 10pt; }
table				{}
td 					{ font-family: Verdana, Arial, Helvetica; font-size: 10pt; padding: 4px; text-align: left }
a					{}

span.required 		{ font-size: 8pt; color: #aa0000 }
span.small 			{ font-size: 8pt }
span.pagetitle		{ font-size: 12pt; font-weight: bold; text-align: center }
span.bold			{ font-weight: bold; }

table.hide			{ width: 100%; border-color: #ffffff; }
table.width100		{ width: 100%; border-color: #000000; border-style: solid; border-width: 1px; }
table.width75		{ width: 75%;  border-color: #000000; border-style: solid; border-width: 1px; }
table.width60		{ width: 60%;  border-color: #000000; border-style: solid; border-width: 1px; }
table.width50		{ width: 50%;  border-color: #000000; border-style: solid; border-width: 1px; }

td.center			{ text-align: center; }
td.left				{ text-align: left; }
td.right			{ text-align: right; }
td.category			{ background-color: <? echo $g_category_title_color ?>; font-weight: bold; }
td.col-1			{ background-color: <? echo $g_primary_color_dark ?>; }
td.col-2			{ background-color: <? echo $g_primary_color_light ?>; }
td.form-title		{ background-color: <? echo $g_white_color ?>; font-weight: bold; }
td.nopad			{ padding: 0px; }
td.spacer			{ font-size: 1pt; line-height: 0.1; }
td.small-caption	{ font-size: 8pt }

tr.center			{ text-align: center; }
tr.row-1			{ background-color: <? echo $g_primary_color_dark ?>; }
tr.row-2			{ background-color: <? echo $g_primary_color_light ?>; }
tr.white			{ background-color: <? echo $g_white_color ?>; }
tr.row-category		{ background-color: <? echo $g_category_title_color ?>; font-weight: bold; }

/* Login Info */
td.login-info-left	{ width: 33%; padding: 0px; text-align: left; }
td.login-info-middle{ width: 33%; padding: 0px; text-align: center; }
td.login-info-right	{ width: 33%; padding: 0px; text-align: right; }
span.login-username	{ font-style: italic; }
span.login-time		{ font-size: 8pt; font-style: italic; }

/* Menu */
td.menu				{ background-color: <? echo $g_primary_color_dark ?>; text-align: center; }

/* Quick Summary */
td.quick-summary-left	{ width: 50%; text-align: left; }
td.quick-summary-right	{ width: 50%; text-align: right; }

/* News */
td.news-heading		{ background-color: <? echo $g_primary_color_dark ?>; text-align: left; border-bottom: 1px solid #000000; }
td.news-body		{ background-color: #ffffff; padding: 16px; }
span.news-headline	{ font-weight: bold; }
span.news-date		{ font-style: italic; font-size: 8pt; }
a.news-email		{ font-size: 8pt; }

</style>