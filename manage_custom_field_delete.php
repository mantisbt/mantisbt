<?php
# Mantis - a php based bugtracking system

# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
# Copyright (C) 2002 - 2007  Mantis Team   - mantisbt-dev@lists.sourceforge.net

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

	# --------------------------------------------------------
	# $Id: manage_custom_field_delete.php,v 1.17.2.1 2007-10-13 22:33:27 giallu Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );

	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'custom_field_api.php' );

	form_security_validate('manage_custom_field_delete');

	auth_reauthenticate();
	access_ensure_global_level( config_get( 'manage_custom_fields_threshold' ) );

	$f_field_id	= gpc_get_int( 'field_id' );
	$f_return = strip_tags( gpc_get_string( 'return', 'manage_custom_field_page.php' ) );

	$t_definition = custom_field_get_definition( $f_field_id );

	if( 0 < count( custom_field_get_project_ids( $f_field_id ) ) ) {
		helper_ensure_confirmed( lang_get( 'confirm_used_custom_field_deletion' ) .
			'<br/>' . lang_get( 'custom_field' ) . ': ' . string_attribute( $t_definition['name'] ),
			lang_get( 'field_delete_button' ) );
	} else {
		helper_ensure_confirmed( lang_get( 'confirm_custom_field_deletion' ) .
			'<br/>' . lang_get( 'custom_field' ) . ': ' . string_attribute( $t_definition['name'] ),
			lang_get( 'field_delete_button' ) );
	}

	custom_field_destroy( $f_field_id );

	form_security_purge('manage_custom_field_delete');

	html_page_top1();
	html_meta_redirect( $f_return );
	html_page_top2();
?>

<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $f_return, lang_get( 'proceed' ) );
?>
</div>

<?php html_page_bottom1( __FILE__ ) ?>
