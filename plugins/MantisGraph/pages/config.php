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

layout_page_header( plugin_lang_get( 'title' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_plugin_page.php' );

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

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" >
<form id="graph-config-form" action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_graph_config_edit' ) ?>

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
<h4 class="widget-title lighter">
	<i class="ace-icon fa fa-bar-chart-o"></i>
	<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
	<div class="table-responsive">
<table class="table table-bordered table-condensed table-striped">
<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'library' )?>
	</th>
	<td class="center">
		<label>
			<input type="radio" class="ace" name="eczlibrary" value="1" <?php echo( ON == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/>
		<span class="lbl"> <?php echo plugin_lang_get('bundled')?> <span>
		</label>
	</td>
	<td class="center">
		<label>
			<input type="radio" class="ace" name="eczlibrary" value="0" <?php echo( OFF == plugin_config_get( 'eczlibrary' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl"> JpGraph </span>
		</label>
	</td>
</tr>

			

<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'window_width' )?>
	</th>
	<td class="center" colspan="2">
		<input type="text" name="window_width" class="input-sm" value="<?php echo plugin_config_get( 'window_width' )?>" />
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'bar_aspect' )?>
	</th>
	<td class="center" colspan="2">
		<input type="text" name="bar_aspect" class="input-sm" value="<?php echo plugin_config_get( 'bar_aspect' )?>" />
	</td>
</tr>

<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'summary_graphs_per_row' )?>
	</th>
	<td class="center" colspan="2">
		<input type="text" name="summary_graphs_per_row" class="input-sm" value="<?php echo plugin_config_get( 'summary_graphs_per_row' )?>" />
	</td>
</tr>
	
<tr>
	<th class="category width-40">
		<?php echo plugin_lang_get( 'font' )?>
	</th>
	<td style="vertical-align: top">
		Sans-serif:<br />
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="arial"<?php echo print_font_checked( 'arial' )?>/>
				<span class="lbl"> Arial </span>
			</label></div>
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="verdana"<?php echo print_font_checked( 'verdana' )?>/>
				<span class="lbl"> Verdana </span>
			</label></div>
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="trebuchet"<?php echo print_font_checked( 'trebuchet' )?>/>
				<span class="lbl"> Trebuchet </span>
			</label></div>
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="verasans"<?php echo print_font_checked( 'verasans' )?>/>
				<span class="lbl"> Vera Sans </span>
			</label></div>
	</td>
	<td style="vertical-align: top">
		Serif:<br />
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="times"<?php echo print_font_checked( 'times' )?>/>
				<span class="lbl">  Times </span>
			</label></div>
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="georgia"<?php echo print_font_checked( 'georgia' )?>/>
				<span class="lbl"> Georgia </span>
			</label></div>
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="veraserif"<?php echo print_font_checked( 'veraserif' )?>/>
				<span class="lbl"> Vera Serif </span>
			</label></div><br />
		<br />Monospace:<br />
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="courier"<?php echo print_font_checked( 'courier' )?>/>
				<span class="lbl"> Courier </span>
			</label></div>
		<div class="radio"><label>
				<input type="radio" class="ace" name="font" value="veramono"<?php echo print_font_checked( 'veramono' )?>/>
				<span class="lbl"> Vera Mono </span>
			</label></div>
	</td>
</tr>

<tr class="spacer"><td></td></tr>

<?php if( current_user_is_administrator() ) {?>
<tr>
	<td class="category width-40">
		<?php echo plugin_lang_get( 'jpgraph_path' )?>
		<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_path_default' )?></span>
	</td>
	<td class="center" colspan="2">
		<input type="text" name="jpgraph_path" class="input-sm" value="<?php echo plugin_config_get( 'jpgraph_path' )?>" />
	</td>
</tr>
<?php } ?>

<tr>
	<td class="category width-40">
		<?php echo plugin_lang_get( 'jpgraph_antialias' )?>
		<br /><span class="small"><?php echo plugin_lang_get( 'jpgraph_antialias_info' )?></span>
	</td>
	<td class="center">
		<label>
			<input type="radio" class="ace" name="jpgraph_antialias" value="1" <?php echo( ON == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl"> <?php echo plugin_lang_get('enabled')?> </span>
		</label>
	</td>
	<td class="center">
		<label>
			<input type="radio" class="ace" name="jpgraph_antialias" value="0" <?php echo( OFF == plugin_config_get( 'jpgraph_antialias' ) ) ? 'checked="checked" ' : ''?>/>
			<span class="lbl"> <?php echo plugin_lang_get('disabled')?> </span>
		</label>
	</td>
</tr>

</table>
</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'change_configuration' )?>" />
</div>
</div>
</div>
</form>
</div>
</div>

<?php
layout_page_end();
