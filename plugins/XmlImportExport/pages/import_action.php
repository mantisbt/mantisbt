<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2014  MantisBT Team - mantisbt-dev@lists.sourceforge.net
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

$t_plugin_path = config_get( 'plugin_path' );
require_once( $t_plugin_path . 'XmlImportExport' . DIRECTORY_SEPARATOR . 'ImportXml.php' );

form_security_validate( 'plugin_xml_import_action' );

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

form_security_purge( 'plugin_xml_import_action' );

html_page_top( plugin_lang_get( 'import' ) );

print_manage_menu( 'manage_import_issues_page.php' );

echo "<pre>\n";
$importer->import( );
echo "</pre>\n";

html_page_bottom();
