<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: manage_proj_page.php,v 1.11 2004-07-20 15:51:50 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'icon_api.php' );
?>
<?php auth_ensure_user_authenticated() ?>
<?php
	$f_sort	= gpc_get_string( 'sort', 'name' );
	$f_dir	= gpc_get_string( 'dir', 'ASC' );

	if ( 'ASC' == $f_dir ) {
		$t_direction = ASC;
	} else {
		$t_direction = DESC;
	}

?>
<?php html_page_top1( lang_get( 'manage_projects_link' ) ) ?>
<?php html_page_top2() ?>

<?php print_manage_menu( 'manage_proj_page.php' ) ?>

<?php # Project Menu Form BEGIN ?>
<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="5">
		<?php echo lang_get( 'projects_title' ) ?>
		<?php
		# Check the user's global access level before allowing project creation
		if ( access_has_global_level ( config_get( 'create_project_threshold' ) ) ) {
			print_bracket_link( 'manage_proj_create_page.php', lang_get( 'create_new_project_link' ) );
		}
		?>
	</td>
</tr>
<tr class="row-category">
	<td width="20%">
		<?php print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'name' ), 'name', $t_direction, $f_sort ) ?>
		<?php print_sort_icon( $t_direction, $f_sort, 'name' ) ?>
	</td>
	<td width="10%">
		<?php print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'status' ), 'status', $t_direction, $f_sort ) ?>
		<?php print_sort_icon( $t_direction, $f_sort, 'status' ) ?>
	</td>
	<td width="10%">
		<?php print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'enabled' ), 'enabled', $t_direction, $f_sort ) ?>
		<?php print_sort_icon( $t_direction, $f_sort, 'enabled' ) ?>
	</td>
	<td width="10%">
		<?php print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'view_status' ), 'view_state', $t_direction, $f_sort ) ?>
		<?php print_sort_icon( $t_direction, $f_sort, 'view_state' ) ?>
	</td>
	<td width="40%">
		<?php print_manage_project_sort_link( 'manage_proj_page.php', lang_get( 'description' ), 'description', $t_direction, $f_sort ) ?>
		<?php print_sort_icon( $t_direction, $f_sort, 'description' ) ?>
	</td>
</tr>
<?php
	$t_projects = project_get_all_rows();

	$t_projects = multi_sort( $t_projects, $f_sort, $t_direction );

	foreach ( $t_projects as $t_project ) {
		extract( $t_project, EXTR_PREFIX_ALL, 'v' );

        if ( !access_has_project_level ( config_get( 'manage_project_threshold' ), $v_id ) ) {
		  continue;
		}

?>
<tr <?php echo helper_alternate_class() ?>>
	<td>
		<a href="manage_proj_edit_page.php?project_id=<?php echo $t_project['id'] ?>"><?php echo string_display( $t_project['name'] ) ?></a>
	</td>
	<td>
		<?php echo get_enum_element( 'project_status', $t_project['status'] ) ?>
	</td>
	<td>
		<?php echo trans_bool( $t_project['enabled'] ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $t_project['view_state'] ) ?>
	</td>
	<td>
		<?php echo string_display_links( $t_project['description'] ) ?>
	</td>
</tr>
<?php
	} # End of foreach loop over projects
?>
</table>

<?php html_page_bottom1( __FILE__ ) ?>
