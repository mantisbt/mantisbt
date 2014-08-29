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
 * Check icon files
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

require_once( dirname( dirname( __FILE__ ) ) . '/core.php' );

access_ensure_global_level( config_get_global( 'admin_site_threshold' ) );

layout_page_header();

layout_admin_page_begin();

print_admin_menu_bar( 'test_icons.php' );

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-image"></i>
		Test Icons
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive">
	<table class="table table-bordered table-striped table-condensed table-hover">
<?php
foreach( $g_file_type_icons as $t_ext => $t_filename ) {
	$t_file_path = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'fileicons' . DIRECTORY_SEPARATOR . $t_filename;

	echo '<tr><td>';
	echo 'Testing icon for extension \'' . $t_ext . '\'... ' . $t_file_path . '... ';
	flush();

	if( file_exists( $t_file_path ) ) {
		echo '<span class="label label-success">OK</span>';
	} else {
		echo '<span class="label label-danger">NOT FOUND</span>';
	}
	echo '</td></tr>';
}
?>
	</table>
</div>
</div>
</div>
</div>
</div>

<?php
layout_admin_page_end();
