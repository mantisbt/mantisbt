<?php include( "core.php" ); ?>
<html>
<head>
<?php include('css_inc.php'); ?>
<style type="text/css">
input.css	{ font-family: courier new, courier, fixed; font-size: 8pt; }
td.css		{ font-size: 8pt; padding: 2px; }
th.css		{ font-size: 10pt; letter-spacing: 0.1em; background-color: #ddddf0; border-bottom: solid 1px #000000; }
</style>
</head>
<body>

<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
<tr>
	<th colspan="2">
		CSS Generation Tool
	</th>
</tr>
<tr>
	<td valign="top" style="border: solid 1px #000000; padding: 0px">
		<form method="post" action="index.php">
		<table border="0" cellspacing="0" cellpadding="4">
		<tr>
			<th class="css" colspan="2">Colors</th>
		</tr>
		<tr>
			<td class="css">Background Color</td>
			<td bgcolor="<?php echo $g_background_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_background_color" size="8" value="<?php echo $g_background_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Required Color</td>
			<td bgcolor="<?php echo $g_required_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_required_color" size="8" value="<?php echo $g_required_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Table Border Color</td>
			<td bgcolor="<?php echo $g_table_border_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_table_border_color" size="8" value="<?php echo $g_table_border_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Category Title Color</td>
			<td bgcolor="<?php echo $g_category_title_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_category_title_color" size="8" value="<?php echo $g_category_title_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Primary Color 1</td>
			<td bgcolor="<?php echo $g_primary_color1 ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_primary_color1" size="8" value="<?php echo $g_primary_color1; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Primary Color 2</td>
			<td bgcolor="<?php echo $g_primary_color2 ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_primary_color2" size="8" value="<?php echo $g_primary_color2; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Form Title Color</td>
			<td bgcolor="<?php echo $g_form_title_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_form_title_color" size="8" value="<?php echo $g_form_title_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Spacer Color</td>
			<td bgcolor="<?php echo $g_spacer_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_spacer_color" size="8" value="<?php echo $g_spacer_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Menu Color</td>
			<td bgcolor="<?php echo $g_menu_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_menu_color" size="8" value="<?php echo $g_menu_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>

		<!-- =========== -->

		<tr>
			<th class="css" colspan="2">Status Colors</th>
		</tr>
		<tr>
			<td class="css">New Color</td>
			<td bgcolor="<?php echo $g_new_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_new_color" size="8" value="<?php echo $g_new_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Feedback Color</td>
			<td bgcolor="<?php echo $g_feedback_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_feedback_color" size="8" value="<?php echo $g_feedback_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Acknowledged Color</td>
			<td bgcolor="<?php echo $g_acknowledged_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_acknowledged_color" size="8" value="<?php echo $g_acknowledged_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Confirmed Color</td>
			<td bgcolor="<?php echo $g_confirmed_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_confirmed_color" size="8" value="<?php echo $g_confirmed_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Assigned Color</td>
			<td bgcolor="<?php echo $g_assigned_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_assigned_color" size="8" value="<?php echo $g_assigned_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Resolved Color</td>
			<td bgcolor="<?php echo $g_resolved_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_resolved_color" size="8" value="<?php echo $g_resolved_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Closed Color</td>
			<td bgcolor="<?php echo $g_closed_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_closed_color" size="8" value="<?php echo $g_closed_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>

		<!-- =========== -->

		<tr>
			<th class="css" colspan="2">Fonts</th>
		</tr>
		<tr>
			<td class="css">Font Family</td>
			<td class="css">
				&nbsp;&nbsp;<input class="css" type="text" name="g_fonts" size="8" value="<?php echo $g_fonts; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Small Font Size</td>
			<td class="css">
				&nbsp;&nbsp;<input class="css" type="text" name="g_font_small" size="8" value="<?php echo $g_font_small; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Normal Font Size</td>
			<td class="css">
				&nbsp;&nbsp;<input class="css" type="text" name="g_font_normal" size="8" value="<?php echo $g_font_normal; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Large Font Size</td>
			<td class="css">
				&nbsp;&nbsp;<input class="css" type="text" name="g_font_large" size="8" value="<?php echo $g_font_large; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td class="css">Font Color</td>
			<td bgcolor="<?php echo $g_font_color ?>">
				&nbsp;&nbsp;<input class="css" type="text" name="g_font_color" size="8" value="<?php echo $g_font_color; ?>" />&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td colspan="2" nowrap>
				<input class="css" type="submit" name="f_preview" value="Preview CSS" />
				&nbsp;&nbsp;
				<input class="css" type="submit" name="f_preview" value="Reset" />
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<a href="css_view.php" >View CSS</a>&nbsp;&nbsp;|&nbsp;&nbsp;
				<a href="css_download.php" >Save to File</a>
			</td>
		</tr>
		</table>
		</form>
	</td>
	<td valign="top">

		<?php include('view_inc.php'); ?>

	</td>
</tr>
</table>
</body>
</html>