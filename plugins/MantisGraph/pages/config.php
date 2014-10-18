<?php
# MantisBT - A PHP based bugtracking system

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

/**
 * Edit Graph Plugin Configuration
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'title' ) );

print_manage_menu( );

$g_current_font_selected = array(
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
if( isset( $g_current_font_selected[$t_current_font] ) ) {
	$g_current_font_selected[$t_current_font] = true;
} else {
	$g_current_font_selected['arial'] = true;
}

/**
 * Prints checked="checked" to the end of a HTML <option> tag if the supplied
 * font name matches the current font configuration value.
 * @param string $p_font_name The name of the font to check.
 * @return string Either checked="checked" for a match or otherwise an empty string
 */
function print_font_checked( $p_font_name ) {
	global $g_current_font_selected;

	if( isset( $g_current_font_selected[$p_font_name] ) ) {
		if( $g_current_font_selected[$p_font_name] ) {
			return ' checked="checked"';
		}
	}

	return '';
}

?>

<div id="graph-config-div" class="form-container">
	<form id="graph-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
		<fieldset>
			<legend><span><?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?></span></legend>
			<?php echo form_security_field( 'plugin_graph_config_edit' ) ?>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'library' )?></span></label>
				<span class="radio">
					<input type="radio" id="ecz-library" name="eczlibrary" value="1" <?php echo( ON == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/>
					<label for="ecz-library"><?php echo plugin_lang_get( 'bundled' )?></label>
					<input type="radio" id="jpgraph-library" name="eczlibrary" value="0" <?php echo( OFF == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/>
					<label for="jpgraph-library">JpGraph</label>
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'window_width' )?></span></label>
				<span class="input">
					<input type="text" name="window_width" value="<?php echo plugin_config_get( 'window_width' )?>" />
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'bar_aspect' )?></span></label>
				<span class="input">
					<input type="text" name="bar_aspect" value="<?php echo plugin_config_get( 'bar_aspect' )?>" />
				</span>
				<span class="label-style"></span>
			</div>
			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'summary_graphs_per_row' )?></span></label>
				<span class="input">
					<input type="text" name="summary_graphs_per_row" value="<?php echo plugin_config_get( 'summary_graphs_per_row' )?>" />
				</span>
				<span class="label-style"></span>
			</div>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'font' )?></span></label>
				<span class="radio">
					Sans-serif:<br />
					<label><input type="radio" name="font" value="arial"<?php echo print_font_checked( 'arial' )?>/>Arial</label><br />
					<label><input type="radio" name="font" value="verdana"<?php echo print_font_checked( 'verdana' )?>/>Verdana</label><br />
					<label><input type="radio" name="font" value="trebuchet"<?php echo print_font_checked( 'trebuchet' )?>/>Trebuchet</label><br />
					<label><input type="radio" name="font" value="verasans"<?php echo print_font_checked( 'verasans' )?>/>Vera Sans</label>
					Serif:<br />
					<label><input type="radio" name="font" value="times"<?php echo print_font_checked( 'times' )?>/>Times</label><br />
					<label><input type="radio" name="font" value="georgia"<?php echo print_font_checked( 'georgia' )?>/>Georgia</label><br />
					<label><input type="radio" name="font" value="veraserif"<?php echo print_font_checked( 'veraserif' )?>/>Vera Serif</label><br />
					<br />Monospace:<br />
					<label><input type="radio" name="font" value="courier"<?php echo print_font_checked( 'courier' )?>/>Courier</label><br />
					<label><input type="radio" name="font" value="veramono"<?php echo print_font_checked( 'veramono' )?>/>Vera Mono</label>
				</span>
				<span class="label-style"></span>
			</div>

			<?php if( current_user_is_administrator() ) {?>
				<div class="field-container">
					<label><span><?php echo plugin_lang_get( 'jpgraph_path' )?>
					<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_path_default' )?></span>
					</span></label>
					<span class="input">
						<input type="text" name="jpgraph_path" value="<?php echo plugin_config_get( 'jpgraph_path' )?>" />
					</span>
					<span class="label-style"></span>
				</div>
			<?php } ?>

			<div class="field-container">
				<label><span><?php echo plugin_lang_get( 'jpgraph_antialias' )?>
				<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_antialias_info' )?></span>
				</span></label>
				<span class="radio">
					<label><input type="radio" name="jpgraph_antialias" value="1" <?php echo( ON == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/><?php echo plugin_lang_get( 'enabled' )?></label>
					<label><input type="radio" name="jpgraph_antialias" value="0" <?php echo( OFF == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/><?php echo plugin_lang_get( 'disabled' )?></label>
				</span>
				<span class="label-style"></span>
			</div>
			<span class="submit-button"><input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" /></span>
		</fieldset>
	</form>
</div>

<?php
html_page_bottom();
