<?php
/**
 * MantisBT - A PHP based bugtracking system
 *
 * MantisBT is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MantisBT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 */

/**
 * Process XML Import
 */

$t_plugin_path = config_get_global( 'plugin_path' );
require_once $t_plugin_path . 'XmlImportExport/ImportXml.php';

form_security_validate( 'plugin_xml_import_action' );

auth_reauthenticate();

$f_file = gpc_get_file( 'file', -1 );
$f_strategy = gpc_get_string( 'strategy' );
$f_fallback = gpc_get_string( 'fallback' );
$f_project = gpc_get_int( 'project_id' ); # not used, but ensures a project is selected
$f_keepcategory = gpc_get_bool( 'keepcategory' );
$f_defaultcategory = gpc_get_int( 'defaultcategory' );

file_ensure_uploaded( $f_file );

$t_importer = new ImportXML( $f_file, $f_strategy, $f_fallback, $f_keepcategory, $f_defaultcategory );

form_security_purge( 'plugin_xml_import_action' );

layout_page_header( plugin_lang_get( 'import' ) );

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'manage_import_issues_page.php' );

?>

<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<pre>

<?php
$t_importer->import();
?>

</pre>
</div>

<?php
layout_page_end();
