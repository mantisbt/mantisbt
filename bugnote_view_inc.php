<?php
	# Mantis - a php based bugtracking system
	# Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	# Copyright (C) 2002 - 2004  Mantis Team   - mantisbt-dev@lists.sourceforge.net
	# This program is distributed under the terms and conditions of the GPL
	# See the files README and LICENSE for details

	# --------------------------------------------------------
	# $Id: bugnote_view_inc.php,v 1.30 2005-05-16 12:56:05 vboctor Exp $
	# --------------------------------------------------------
?>
<?php
	# This include file prints out the list of bugnotes attached to the bug
	# $f_bug_id must be set and be set to the bug id
?>
<?php
	$t_core_path = config_get( 'core_path' );

	require_once( $t_core_path.'current_user_api.php' );
?>
<?php
	# grab the user id currently logged in
	$t_user_id = auth_get_current_user_id();

	if ( !access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) ) {
		$t_restriction = 'AND view_state=' . VS_PUBLIC;
	} else {
		$t_restriction = '';
	}

	$t_bugnote_table		= config_get( 'mantis_bugnote_table' );
	$t_bugnote_text_table	= config_get( 'mantis_bugnote_text_table' );
	$t_bugnote_order		= current_user_get_pref( 'bugnote_order' );

	# get the bugnote data
	$query = "SELECT *
			FROM $t_bugnote_table
			WHERE bug_id='$f_bug_id' $t_restriction
			ORDER BY date_submitted $t_bugnote_order";
	$result = db_query( $query );
	$num_notes = db_num_rows( $result );
?>

<?php # Bugnotes BEGIN ?>
<a name="bugnotes" id="bugnotes" /><br />

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<div id="bugnotes_closed" style="display: none;">
<table class="width100" cellspacing="1">
<tr>
	<td class="form-title" colspan="2">
		<a href="" onclick="ToggleDiv( 'bugnotes', g_div_bugnotes ); return false;"
		><img border="0" src="images/plus.png" alt="+" /></a>
		<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
</table>
</div>
<?php } ?>

<div id="bugnotes_open">
<table class="width100" cellspacing="1">
<?php
	# no bugnotes
	if ( 0 == $num_notes ) {
?>
<tr>
	<td class="center" colspan="2">
		<?php echo lang_get( 'no_bugnotes_msg' ) ?>
	</td>
</tr>
<?php } else { # print bugnotes ?>
<tr>
	<td class="form-title" colspan="2">
<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
		<a href="" onclick="ToggleDiv( 'bugnotes', g_div_bugnotes ); return false;"
		><img border="0" src="images/minus.png" alt="-" /></a>
<?php } ?>
		<?php echo lang_get( 'bug_notes_title' ) ?>
	</td>
</tr>
<?php
	for ( $i=0; $i < $num_notes; $i++ ) {
		# prefix all bugnote data with v3_
		$row = db_fetch_array( $result );
		extract( $row, EXTR_PREFIX_ALL, 'v3' );
		if ( db_unixtimestamp( $v3_date_submitted ) != db_unixtimestamp( $v3_last_modified ) )
			$t_bugnote_modified = true;
		else
			$t_bugnote_modified = false;

		$v3_date_submitted = date( config_get( 'normal_date_format' ), ( db_unixtimestamp( $v3_date_submitted ) ) );
		$v3_last_modified = date( config_get( 'normal_date_format' ), ( db_unixtimestamp( $v3_last_modified ) ) );

		# grab the bugnote text and id and prefix with v3_
		$query = "SELECT note
				FROM $t_bugnote_text_table
				WHERE id='$v3_bugnote_text_id'";
		$result2 = db_query( $query );
		$row = db_fetch_array( $result2 );

		$v3_note = $row['note'];
		$v3_note = string_display_links( $v3_note );
		$t_bugnote_id_formatted = bugnote_format_id( $v3_id );

		if ( VS_PRIVATE == $v3_view_state ) {
			$t_bugnote_css		= 'bugnote-private';
			$t_bugnote_note_css	= 'bugnote-note-private';
		} else {
			$t_bugnote_css		= 'bugnote-public';
			$t_bugnote_note_css	= 'bugnote-note-public';
		}
?>
<tr class="bugnote" name="<?php echo $v3_id ?>" id="<?php echo $v3_id ?>">
        <td class="<?php echo $t_bugnote_css ?>">
		<span class="small">(<?php echo $t_bugnote_id_formatted ?>)</span><br />
		<?php
			echo print_user( $v3_reporter_id );
		?>
		<?php if ( VS_PRIVATE == $v3_view_state ) { ?>
		<span class="small">[ <?php echo lang_get( 'private' ) ?> ]</span>
		<?php } ?>
		<br />
		<span class="small"><?php echo $v3_date_submitted ?></span><br />
		<?php
		if ( true == $t_bugnote_modified ) {
			echo '<span class="small">'.lang_get( 'edited_on').' '.$v3_last_modified.'</span><br />';
		}
		?>
		<br /><span class="small">
		<?php
			# only admins and the bugnote creator can edit/delete this bugnote
			# bug must be open to be editable
			if ( !bug_is_readonly( $f_bug_id ) ) {
				if ( ( access_has_bug_level( config_get( 'manage_project_threshold' ), $f_bug_id ) ) ||
					( ( $v3_reporter_id == $t_user_id ) && ( ON == config_get( 'bugnote_allow_user_edit_delete' ) ) ) ) {
					print_button( 'bugnote_edit_page.php?bugnote_id='.$v3_id, lang_get( 'bugnote_edit_link' ) );
					print_button( 'bugnote_delete.php?bugnote_id='.$v3_id, lang_get( 'delete_link' ) );
					if ( access_has_bug_level( config_get( 'private_bugnote_threshold' ), $f_bug_id ) &&
						access_has_bug_level( config_get( 'change_view_status_threshold' ), $f_bug_id ) ) {
						if ( VS_PRIVATE == $v3_view_state ) {
							print_button('bugnote_set_view_state.php?private=0&amp;bugnote_id='.$v3_id, lang_get( 'make_public' ));
						} else {
							print_button('bugnote_set_view_state.php?private=1&amp;bugnote_id='.$v3_id, lang_get( 'make_private' ));
						}
					}
				}
			}
		?>
		</span>
	</td>
	<td class="<?php echo $t_bugnote_note_css ?>">
		<?php
			switch ( $v3_note_type ) {
				case REMINDER:
					echo '<div class="italic">' . lang_get( 'reminder_sent_to' ) . ': ';
					$v3_note_attr = substr( $v3_note_attr, 1, strlen( $v3_note_attr ) - 2 );
					$t_to = array();
					foreach ( explode( '|', $v3_note_attr ) as $t_recipient ) {
						$t_to[] = prepare_user_name( $t_recipient );
					}
					echo implode( ', ', $t_to ) . '</div><br />';
				default:
					echo $v3_note;
			}
		?>
	</td>
</tr>
<tr>
	<td class="spacer" colspan="2">&nbsp;</td>
</tr>
<?php
		} # end for loop
	} # end else
?>
</table>
</div>

<?php if ( ON == config_get( 'use_javascript' ) ) { ?>
<script type="text/JavaScript">
<!--
	SetDiv( "bugnotes", g_div_bugnotes );
// -->
</script>
<?php } ?>

<?php # Bugnotes END ?>
