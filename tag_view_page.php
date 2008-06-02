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
	# $Id: tag_view_page.php,v 1.1.2.2 2007-10-18 15:33:23 nuclear_eclipse Exp $
	# --------------------------------------------------------

	require_once( 'core.php' );
	
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path . 'tag_api.php' );

	access_ensure_global_level( config_get( 'tag_view_threshold' ) );
	compress_enable();

	$f_tag_id = gpc_get_int( 'tag_id' );
	$t_tag_row = tag_get( $f_tag_id );

	$t_name = string_display_line( $t_tag_row['name'] );
	$t_description = string_display( $t_tag_row['description'] );

	html_page_top1( sprintf( lang_get( 'tag_details' ), $t_tag_row['name'] ) );
	html_page_top2();
?>

<br/>
<table class="width100" cellspacing="1">

<!-- Title -->
<tr>
	<td class="form-title" colspan="2">
		<?php echo sprintf( lang_get( 'tag_details' ), $t_tag_row['name'] ) ?>

	</td>
	<td class="right" colspan="3">
		<?php print_bracket_link( 'search.php?hide_status_id=90&amp;tag_string='.urlencode($t_tag_row['name']), sprintf( lang_get( 'tag_filter_default' ), tag_stats_attached( $f_tag_id ) ) ); ?>
	</td>
</tr>

<!-- Info -->
<tr class="row-category">
	<td width="15%"><?php echo lang_get( 'tag_id' ) ?></td>
	<td width="25%"><?php echo lang_get( 'tag_name' ) ?></td>
	<td width="20%"><?php echo lang_get( 'tag_creator' ) ?></td>
	<td width="20%"><?php echo lang_get( 'tag_created' ) ?></td>
	<td width="20%"><?php echo lang_get( 'tag_updated' ) ?></td>
</tr>

<tr <?php echo helper_alternate_class() ?>>
	<td><?php echo $t_tag_row['id'] ?></td>
	<td><?php echo $t_name ?></td>
	<td><?php echo user_get_name($t_tag_row['user_id']) ?></td>
	<td><?php echo print_date( config_get( 'normal_date_format' ), db_unixtimestamp( $t_tag_row['date_created'] ) ) ?> </td>
	<td><?php echo print_date( config_get( 'normal_date_format' ), db_unixtimestamp( $t_tag_row['date_updated'] ) ) ?> </td>
</tr>

<!-- spacer -->
<tr class="spacer">
	<td colspan="5"></td>
</tr>

<!-- Description -->
<tr <?php echo helper_alternate_class() ?>>
	<td class="category"><?php echo lang_get( 'tag_description' ) ?></td>
	<td colspan="4"><?php echo $t_description ?></td>
</tr>

<!-- Statistics -->
<?php
	$t_tags_related = tag_stats_related( $f_tag_id );
	if ( count( $t_tags_related ) ) { 
		echo '<tr ',helper_alternate_class(),'>';
		echo '<td class="category" rowspan="',count( $t_tags_related ),'">',lang_get( 'tag_related' ),'</td>';
		
		$i = 0;
		foreach( $t_tags_related as $t_tag ) {
			$t_name = string_display_line( $t_tag['name'] );
			$t_description = string_display_line( $t_tag['description'] );
			$t_count = $t_tag['count'];

			echo ( $i > 0 ? '<tr '.helper_alternate_class().'>' : '' );
			echo "<td><a href='tag_view_page.php?tag_id=$t_tag[id]' title='$t_description'>$t_name</a></td>\n";
			echo '<td colspan="3">';
			print_bracket_link( 'search.php?hide_status_id=90&amp;tag_string='.urlencode("+$t_tag_row[name]".config_get('tag_separator')."+$t_name"), sprintf( lang_get( 'tag_related_issues' ), $t_tag['count'] ) );
			echo '</a></td></tr>';
			
			$i++;
		}
	}
?>

<!-- Buttons -->
<tr>
	<td colspan="5">
<?php
	$t_can_edit = access_has_global_level( config_get( 'tag_edit_threshold' ) );
	$t_can_edit_own = $t_can_edit || auth_get_current_user_id() == tag_get_field( $f_tag_id, 'user_id' )
		&& access_has_global_level( config_get( 'tag_edit_own_threshold' ) );

	if ( $t_can_edit_own ) { ?>
		<form action="tag_update_page.php" method="post">
			<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>" />
			<input type="submit" class="button" value="<?php echo lang_get( 'tag_update_button' ) ?>" />
		</form>
<?php } if ( $t_can_edit ) { ?>
		<form action="tag_delete.php" method="post">
			<?php echo form_security_field( 'tag_delete' ) ?>
			<input type="hidden" name="tag_id" value="<?php echo $f_tag_id ?>" />
			<input type="submit" class="button" value="<?php echo lang_get( 'tag_delete_button' ) ?>" />
		</form>
<?php } ?>

	</td>
</tr>

</table>
<?php
	html_page_bottom1( __FILE__ );
?>
