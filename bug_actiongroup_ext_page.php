<?php
# MantisBT - a php based bugtracking system

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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2010  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	require_once( 'bug_group_action_api.php' );

	auth_ensure_user_authenticated();

	$f_action = gpc_get_string( 'action' );
	$f_bug_arr = gpc_get_int_array( 'bug_arr', array() );

	# redirect to view issues if nothing is selected
	if ( is_blank( $f_action ) || ( 0 == count( $f_bug_arr ) ) ) {
		print_header_redirect( 'view_all_bug_page.php' );
	}

  # redirect to view issues page if action doesn't have ext_* prefix.
  # This should only occur if this page is called directly.
	$t_external_action_prefix = 'EXT_';
	if ( strpos( $f_action, $t_external_action_prefix ) !== 0 ) {
		print_header_redirect( 'view_all_bug_page.php' );
  }

	$t_external_action = utf8_strtolower( utf8_substr( $f_action, utf8_strlen( $t_external_action_prefix ) ) );
	$t_form_fields_page = 'bug_actiongroup_' . $t_external_action . '_inc.php';
	$t_form_name = 'bug_actiongroup_' . $t_external_action;

	bug_group_action_print_top();
?>

	<br />

	<div align="center">
	<form method="post" action="bug_actiongroup_ext.php">
<?php echo form_security_field( $t_form_name ); ?>
		<input type="hidden" name="action" value="<?php echo string_attribute( $t_external_action ) ?>" />
<table class="width75" cellspacing="1">
	<?php
		bug_group_action_print_title( $t_external_action );
		bug_group_action_print_hidden_fields( $f_bug_arr );
		bug_group_action_print_action_fields( $t_external_action );
	?>
</table>
	</form>
	</div>

	<br />

<?php
	bug_group_action_print_bug_list( $f_bug_arr );
	bug_group_action_print_bottom();
