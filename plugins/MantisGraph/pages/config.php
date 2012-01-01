<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'title' ) );

print_manage_menu( );

$t_current_font_selected = array(
	'arial' => false,
	'verdana' => false,
	'trebuchet' => false,
	'verasans' => false,
	'times' => false,
	'georgia' => false,
	'veraserif' => false,
	'courier' => false,
	'veramono' => false,
);

$t_current_font = plugin_config_get( 'font' );
if ( isset( $t_current_font_selected[$t_current_font] ) ) {
	$t_current_font_selected[$t_current_font] = true;
} else {
	$t_current_font_selected['arial'] = true;
}

/**
 * Prints checked="checked" to the end of a HTML <option> tag if the supplied
 * font name matches the current font configuration value.
 * @param string The name of the font to check
 * @return string Either checked="checked" for a match or otherwise an empty string
 */
function print_font_checked( $p_font_name ) {
	global $t_current_font_selected;

	if ( isset( $t_current_font_selected[$p_font_name] ) ) {
		if ( $t_current_font_selected[$p_font_name] ) {
			return ' checked="checked"';
		}
	}

	return '';
}

?>

<br />
<form action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_graph_config_edit' ) ?>
<table align="center" class="width75" cellspacing="1">

<tr>
	<td class="form-title" colspan="3">
		<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'library' )?>
	</td>
	<td class="center">
		<label><input type="radio" name="eczlibrary" value="1" <?php echo( ON == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/><?php echo plugin_lang_get('bundled')?></label>
	</td>
	<td class="center">
		<label><input type="radio" name="eczlibrary" value="0" <?php echo( OFF == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/>JpGraph</label>
	</td>
</tr>

<tr class="spacer"><td></td></tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'window_width' )?>
	</td>
	<td class="center" colspan="2">
		<input type="text" name="window_width" value="<?php echo plugin_config_get( 'window_width' )?>" />
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'bar_aspect' )?>
	</td>
	<td class="center" colspan="2">
		<input type="text" name="bar_aspect" value="<?php echo plugin_config_get( 'bar_aspect' )?>" />
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'summary_graphs_per_row' )?>
	</td>
	<td class="center" colspan="2">
		<input type="text" name="summary_graphs_per_row" value="<?php echo plugin_config_get( 'summary_graphs_per_row' )?>" />
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'font' )?>
	</td>
	<td style="vertical-align: top">
		Sans-serif:<br />
		<label><input type="radio" name="font" value="arial"<?php echo print_font_checked( 'arial' )?>/>Arial</label><br />
		<label><input type="radio" name="font" value="verdana"<?php echo print_font_checked( 'verdana' )?>/>Verdana</label><br />
		<label><input type="radio" name="font" value="trebuchet"<?php echo print_font_checked( 'trebuchet' )?>/>Trebuchet</label><br />
		<label><input type="radio" name="font" value="verasans"<?php echo print_font_checked( 'verasans' )?>/>Vera Sans</label>
	</td>
	<td style="vertical-align: top">
		Serif:<br />
		<label><input type="radio" name="font" value="times"<?php echo print_font_checked( 'times' )?>/>Times</label><br />
		<label><input type="radio" name="font" value="georgia"<?php echo print_font_checked( 'georgia' )?>/>Georgia</label><br />
		<label><input type="radio" name="font" value="veraserif"<?php echo print_font_checked( 'veraserif' )?>/>Vera Serif</label><br />
		<br />Monospace:<br />
		<label><input type="radio" name="font" value="courier"<?php echo print_font_checked( 'courier' )?>/>Courier</label><br />
		<label><input type="radio" name="font" value="veramono"<?php echo print_font_checked( 'veramono' )?>/>Vera Mono</label>
	</td>
</tr>

<tr class="spacer"><td></td></tr>

<?php if ( current_user_is_administrator() ) {?>
<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'jpgraph_path' )?>
		<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_path_default' )?></span>
	</td>
	<td class="center" colspan="2">
		<input type="text" name="jpgraph_path" value="<?php echo plugin_config_get( 'jpgraph_path' )?>" />
	</td>
</tr>
<?php } ?>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'jpgraph_antialias' )?>
		<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_antialias_info' )?></span>
	</td>
	<td class="center">
		<label><input type="radio" name="jpgraph_antialias" value="1" <?php echo( ON == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/><?php echo plugin_lang_get('enabled')?></label>
	</td>
	<td class="center">
		<label><input type="radio" name="jpgraph_antialias" value="0" <?php echo( OFF == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/><?php echo plugin_lang_get('disabled')?></label>
	</td>
</tr>

<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" />
	</td>
</tr>

</table>
<form>

<?php
html_page_bottom();
