<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002         Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the README and LICENSE files for details

	# --------------------------------------------------------
	# $Id: bug_view_page.php,v 1.10 2002-11-27 02:45:20 jfitzell Exp $
	# --------------------------------------------------------
?>
<?php require_once( 'core.php' ) ?>
<?php login_cookie_check() ?>
<?php
	$f_bug_id	= gpc_get_int( 'f_bug_id' );
	$f_history	= gpc_get_bool( 'f_history' );

	if ( ADVANCED_ONLY == config_get( 'show_view' ) ) {
		print_header_redirect ( 'bug_view_advanced_page.php?f_bug_id=' . $f_bug_id );
	}

	project_access_check( $f_bug_id );

	# if bug is private, make sure user can view private bugs
	access_bug_check( $f_bug_id );

	$t_bug = bug_prepare_display( bug_get( $f_bug_id, true ) );

	compress_start();
?>
<?php print_page_top1() ?>
<?php print_page_top2() ?>

<br />
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'viewing_bug_simple_details_title' ) ?>
		<span class="small"><?php print_bracket_link( "#bugnotes", lang_get( 'jump_to_bugnotes' ) ) ?></span>
	</td>
	<td class="right" colspan="3">
<?php if ( BOTH == config_get( 'show_view' ) ) { ?>
		<span class="small"><?php print_bracket_link( 'bug_view_advanced_page.php?f_bug_id=' . $f_bug_id, lang_get( 'view_advanced_link' ) )?></span>
<?php }?>
	<span class="small"><?php print_bracket_link( 'bug_view_page.php?f_bug_id=' . $f_bug_id . '&amp;f_history=1#history', lang_get( 'bug_history' ) ) ?></span>
	<span class="small"><?php print_bracket_link( 'print_bug_page.php?f_bug_id=' . $f_bug_id, lang_get( 'print' ) ) ?></span>
	</td>
</tr>
<tr class="row-category">
	<td width="16%">
		<?php echo lang_get( 'id' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'category' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'severity' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'reproducibility' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'date_submitted' ) ?>
	</td>
	<td width="16%">
		<?php echo lang_get( 'last_update' ) ?>
	</td>
</tr>
<tr class="row-2">
	<td>
		<?php echo bug_format_id( $f_bug_id ) ?>
	</td>
	<td>
		<?php echo $t_bug->category ?>
	</td>
	<td>
		<?php echo get_enum_element( 'severity', $t_bug->severity ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'reproducibility', $t_bug->reproducibility ) ?>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->date_submitted ) ?>
	</td>
	<td>
		<?php print_date( config_get( 'normal_date_format' ), $t_bug->last_updated ) ?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'reporter' ) ?>
	</td>
	<td>
		<?php print_user_with_subject( $t_bug->reporter_id, $f_bug_id ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'view_status' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'project_view_state', $t_bug->view_state ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'assigned_to' ) ?>
	</td>
	<td colspan="5">
		<?php print_user_with_subject( $t_bug->handler_id, $f_bug_id ) ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'priority' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'priority', $t_bug->priority ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'resolution' ) ?>
	</td>
	<td>
		<?php echo get_enum_element( 'resolution', $t_bug->resolution ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'status' ) ?>
	</td>
	<td bgcolor="<?php echo get_status_color( $t_bug->status ) ?>">
		<?php echo get_enum_element( 'status', $t_bug->status ) ?>
	</td>
	<td class="category">
		<?php echo lang_get( 'duplicate_id' ) ?>
	</td>
	<td>
		<?php print_duplicate_id( $t_bug->duplicate_id ) ?>
	</td>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td class="spacer" colspan="6">
		&nbsp;
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'summary' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->summary ?>
	</td>
</tr>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'description' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->description ?>
	</td>
</tr>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'additional_information' ) ?>
	</td>
	<td colspan="5">
		<?php echo $t_bug->additional_information ?>
	</td>
</tr>
<?php
	$t_show_attachments = ( ( $t_bug->reporter_id == auth_get_current_user_id() ) || access_level_check_greater_or_equal( config_get( 'view_attachments_threshold' ) ) );

	if ( $t_show_attachments ) {
?>
<tr class="row-2">
	<td class="category">
		<?php echo lang_get( 'attached_files' ) ?>
	</td>
	<td colspan="5">
		<?php file_list_attachments ( $f_bug_id ); ?>
	</td>
</tr>
<?php } ?>
<tr class="row-1">
	<td class="category">
		<?php echo lang_get( 'bug_relationships' ) ?>
	</td>
	<td colspan="5">
		<?php
			$result = relationship_fetch_all_src( $f_bug_id );
			$relationship_count = db_num_rows( $result );
			for ($i=0;$i<$relationship_count;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, 'v2' );

				$t_bug_link = string_get_bug_view_link( $t_bug->destination_bug_id );
				switch ( $t_bug->relationship_type ) {
				case BUG_DUPLICATE:	$t_description = str_replace( '%id', $t_bug_link, lang_get( 'duplicate_of' ) );
									break;
				case BUG_RELATED:	$t_description = str_replace( '%id', $t_bug_link, lang_get( 'related_to' ) );
									break;
				case BUG_DEPENDANT:	$t_description = str_replace( '%id', $t_bug_link, lang_get( 'dependant_on' ) );
									break;
				default:			$t_description = str_replace( '%id', $t_bug_link, lang_get( 'duplicate_of' ) );
				}

				PRINT "$t_description<br />";
			}
		?>
		<?php
			$result = relationship_fetch_all_dest( $f_bug_id );
			$relationship_count = db_num_rows( $result );
			for ($i=0;$i<$relationship_count;$i++) {
				$row = db_fetch_array( $result );
				extract( $row, EXTR_PREFIX_ALL, 'v2' );

				$t_bug_link = string_get_bug_view_link( $t_bug->source_bug_id );
				switch ( $t_bug->relationship_type ) {
				case BUG_DUPLICATE:	$t_description = str_replace( '%id', $t_bug_link, lang_get( 'has_duplicate' ) );
									break;
				case BUG_RELATED:	$t_description = str_replace( '%id', $t_bug_link, lang_get( 'related_to' ) );
									break;
				case BUG_DEPENDANT:	$t_description = str_replace( '%id', $t_bug_link, lang_get( 'blocks' ) );
									break;
				default:			$t_description = str_replace( '%id', $t_bug_link, lang_get( 'has_duplicate' ) );
				}

				PRINT "$t_description<br />";
			}
		?>
	</td>
</tr>
<tr align="center">
	<td colspan="6">
		<table width="100%">
			<tr align="center">
<?php
	# UPDATE button
	if ( $t_bug->status < RESOLVED ) {
		echo '<td class="center">';
		html_button_bug_update( $f_bug_id );
		echo '</td>';
	}

	# ASSIGN button
	if ( $t_bug->status < RESOLVED ) {
		echo '<td class="center">';
		html_button_bug_assign( $f_bug_id );
		echo '</td>';
	}

	# RESOLVE button
	if ( $t_bug->status < RESOLVED ) {
		echo '<td class="center">';
		html_button_bug_resolve( $f_bug_id );
		echo '</td>';
	}

	# REOPEN button
	if ( $t_bug->status >= RESOLVED ) {
		echo '<td class="center">';
		html_button_bug_reopen( $f_bug_id );
		echo '</td>';
	}

	# REOPEN button
	if ( $t_bug->status == RESOLVED ) {
		echo '<td class="center">';
		html_button_bug_close( $f_bug_id );
		echo '</td>';
	}

	# MONITOR/UNMONITOR button
	if ( user_is_monitoring_bug( auth_get_current_user_id(), $f_bug_id ) ) {
		echo '<td class="center">';
		html_button_bug_unmonitor( $f_bug_id );
		echo '</td>';
	} else {
		echo '<td class="center">';
		html_button_bug_monitor( $f_bug_id );
		echo '</td>';
	}	

	# DELETE button
	echo '<td class="center">';
	html_button_bug_delete( $f_bug_id );
	echo '</td>';
?>
			</tr>
		</table>
	</td>
</tr>
</table>

<?php
	if ( is_blank( $f_check ) && $t_show_attachments && $t_bug->status < RESOLVED && access_level_check_greater_or_equal( REPORTER ) ) {
		include( config_get( 'bug_file_upload_inc' ) );
	}

	include( config_get( 'bugnote_include_file' ) );

	if ( $f_history ) {
		include( config_get( 'history_include_file' ) );
	}
?>

<?php print_page_bot1( __FILE__ ) ?>
<?php compress_stop(); ?>
