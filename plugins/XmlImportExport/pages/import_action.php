<?php
# Mantis - a php based bugtracking system
# Copyright (C) 2002 - 2009  Mantis Team   - mantisbt-dev@lists.sourceforge.net
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

$t_plugin_path = config_get( 'plugin_path' );
require_once( $t_plugin_path . 'XmlImportExport/ImportXml.php' );

auth_reauthenticate( );

//var_dump( $_POST );
$f_file = gpc_get_file( 'file', -1 );
$f_strategy = gpc_get_string( 'strategy' );
$f_fallback = gpc_get_string( 'fallback' );
$f_project = gpc_get_int( 'project_id' ); // not used, but ensures a project is selected
$f_keepcategory = gpc_get_bool( 'keepcategory' );
$f_defaultcategory = gpc_get_int( 'defaultcategory' );

file_ensure_uploaded( $f_file );

$importer = new ImportXML( $f_file, $f_strategy, $f_fallback, $f_keepcategory, $f_defaultcategory );

html_page_top1( lang_get( 'manage_import_issues_link' ) );
html_page_top2( );

print_manage_menu( 'manage_import_issues_page.php' );

echo "<pre>\n";
$importer->import( );
echo "</pre>\n";

html_page_bottom1( __FILE__ );
