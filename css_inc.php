<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	global	$g_background_color, $g_required_color,
			$g_table_border_color, $g_category_title_color,
			$g_primary_color1, $g_primary_color2,
			$g_form_title_color, $g_spacer_color,
			$g_menu_color, $g_fonts, $g_font_color,
			$g_font_small, $g_font_normal, $g_font_large;
?>
<style type="text/css">
body 				{ background-color: <?php echo $g_background_color ?>; color: <?php echo $g_font_color ?>; font-family: <?php echo $g_fonts ?>; font-size: <?php echo $g_font_normal ?>; margin-left: 4px; margin-right: 4px; margin-top: 6px; margin-bottom: 6px; }
p 					{ font-family: <?php echo $g_fonts ?>; font-size: <?php echo $g_font_normal ?> }
p.center			{ text-align: center }
address 			{ font-family: <?php echo $g_fonts ?>; font-size: <?php echo $g_font_small ?> }
span				{ font-family: <?php echo $g_fonts ?>; font-size: <?php echo $g_font_normal ?>; }
table				{ }
td 					{ font-family: <?php echo $g_fonts ?>; font-size: <?php echo $g_font_normal ?>; padding: 4px; text-align: left }
a					{}
a.small				{ font-size: <?php echo $g_font_small ?>; }

input.small			{ font-size: <?php echo $g_font_small ?>; }
select.small		{ font-size: <?php echo $g_font_small ?>; }

span.print			{ font-size: <?php echo $g_font_small ?>; }

span.required 		{ font-size: <?php echo $g_font_small ?>; color: <?php echo $g_required_color ?>; }
span.small 			{ font-size: <?php echo $g_font_small ?>; font-weight: normal; }
span.pagetitle		{ font-size: <?php echo $g_font_large ?>; font-weight: bold; text-align: center }
span.bold			{ font-weight: bold; }
span.italic			{ font-style: italic; }

table.hide			{ width: 100%; border-color: <?php echo $g_background_color ?>; }
table.width100		{ width: 100%; border-color: <?php echo $g_table_border_color ?>; border-style: solid; border-width: 1px; }
table.width75		{ width: 75%;  border-color: <?php echo $g_table_border_color ?>; border-style: solid; border-width: 1px; }
table.width60		{ width: 60%;  border-color: <?php echo $g_table_border_color ?>; border-style: solid; border-width: 1px; }
table.width50		{ width: 50%;  border-color: <?php echo $g_table_border_color ?>; border-style: solid; border-width: 1px; }

td.center			{ text-align: center; }
td.left				{ text-align: left; }
td.right			{ text-align: right; }
td.category			{ background-color: <?php echo $g_category_title_color ?>; color: <?php echo $g_font_color ?>; font-weight: bold; }
td.col-1			{ background-color: <?php echo $g_primary_color1 ?>; color: <?php echo $g_font_color ?>; }
td.col-2			{ background-color: <?php echo $g_primary_color2 ?>; color: <?php echo $g_font_color ?>; }
td.form-title		{ background-color: <?php echo $g_form_title_color ?>; color: <?php echo $g_font_color ?>; font-weight: bold; }
td.nopad			{ padding: 0px; }
td.spacer			{ background-color: <?php echo $g_spacer_color ?>; color: <?php echo $g_font_color ?>; font-size: 1pt; line-height: 0.1; }
td.small-caption	{ font-size: <?php echo $g_font_small ?>; }
td.print			{ font-size: <?php echo $g_font_small ?>; text-align: left; padding: 2px; }
td.print-category	{ font-size: <?php echo $g_font_small ?>; color: <?php echo $g_font_color ?>; font-weight: bold; text-align: right; padding: 2px; }
td.print-bottom		{ border-bottom: 1px solid #000000; }
td.print-spacer		{ background-color: <?php echo $g_spacer_color ?>; color: <?php echo $g_font_color ?>; font-size: 1pt; line-height: 0.1; padding: 0px;}

tr.center			{ text-align: center; }
tr.row-1			{ background-color: <?php echo $g_primary_color1 ?>; color: <?php echo $g_font_color ?>; }
tr.row-2			{ background-color: <?php echo $g_primary_color2 ?>; color: <?php echo $g_font_color ?>; }
tr.spacer			{ background-color: <?php echo $g_spacer_color ?>; color: <?php echo $g_font_color ?>; }
tr.row-category		{ background-color: <?php echo $g_category_title_color ?>; color: <?php echo $g_font_color ?>; font-weight: bold; }
tr.row-category2	{ background-color: <?php echo $g_category_title_color ?>; color: <?php echo $g_font_color ?>; }
tr.print-category	{ color: <?php echo $g_font_color ?>; font-weight: bold; }

td.login-info-left	{ width: 33%; padding: 0px; text-align: left; }
td.login-info-middle{ width: 33%; padding: 0px; text-align: center; }
td.login-info-right	{ width: 33%; padding: 0px; text-align: right; }
span.login-username	{ font-style: italic; }
span.login-time		{ font-size: <?php echo $g_font_small ?>; font-style: italic; }

td.menu				{ background-color: <?php echo $g_menu_color ?>; color: <?php echo $g_font_color ?>; text-align: center; width: 100%; padding: 1px; }

td.quick-summary-left	{ width: 50%; text-align: left; }
td.quick-summary-right	{ width: 50%; text-align: right; }

td.news-heading		{ background-color: <?php echo $g_primary_color1 ?>; color: <?php echo $g_font_color ?>; text-align: left; border-bottom: 1px solid <?php echo $g_table_border_color ?>; }
td.news-body		{ background-color: <?php echo $g_spacer_color ?>; color: <?php echo $g_font_color ?>; padding: 16px; }
span.news-headline	{ font-weight: bold; }
span.news-date		{ font-style: italic; font-size: <?php echo $g_font_small ?>; }
a.news-email		{ font-size: <?php echo $g_font_small ?>; }
</style>
<script language="JavaScript" type="text/javascript">
<!--
if(document.layers) {document.write('<style>td{padding:0px;}</style>')}
//-->
</script>
<noscript></noscript>