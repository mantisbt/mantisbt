<?php

function email_template_bug_message( $issue_data,$top_line ) {

	// we move all data into an array called $variables
	$variables = array();
	$variables['top_line']		= $top_line;
	$variables['project']		= $issue_data['email_project'];
	$variables['category']		= $issue_data['email_category'];
	$variables['bug_id']		= $issue_data['email_bug'];
	$variables['reporter']		= $issue_data['email_reporter'];
	$variables['hander']		= $issue_data['email_handler'];
	$variables['severity']		= get_enum_element( 'severity', $issue_data['email_severity'], auth_get_current_user_id(), $issue_data['project_id'] );
	$variables['status']		= get_enum_element( 'status', $issue_data['email_status'], auth_get_current_user_id(), $issue_data['project_id'] );
	$variables['priority']		= get_enum_element( 'priority', $issue_data['email_priority'], auth_get_current_user_id(),$issue_data['project_id'] );
	$variables['repro']			= get_enum_element( 'reproducibility', $issue_data['email_reproducibility'], auth_get_current_user_id(), $issue_data['project_id'] );
	$variables['submitted']		= date( config_get( 'normal_date_format' ), $issue_data['email_date_submitted'] );
	$variables['modified']		= date( config_get( 'normal_date_format' ), $issue_data['email_last_modified'] );
	$variables['summary']		= $issue_data['email_summary'];
	$variables['description']	= $issue_data['email_description'];
	$variables['tags']			= $issue_data['email_tag'];
	$variables['info']			= $issue_data['email_additional_information'];
	$variables['steps']			= $issue_data['email_steps_to_reproduce'];
	$variables['due_date']		= $issue_data['email_due_date'];
	$variables['url']			= $issue_data['email_bug_view_url'];

## process custom fields, looping through the array
	$custom_fields = $issue_data['custom_fields'];
	if ( !empty( $custom_fields ) ) {
		$cfdata = '<table><tr><b><td width="40%">Field</td><td width="60%">Value</td></b></tr>';
		foreach( $issue_data['custom_fields'] as $t_custom_field_name => $t_custom_field_data ) {
			$cfdata .= '<tr><td>'.utf8_str_pad( lang_get_defaulted( $t_custom_field_name, null ) . ': ', $t_email_padding_length, ' ', STR_PAD_RIGHT ).'</td>';
			$cfdata .= '<td>'.string_custom_field_value_for_email( $t_custom_field_data['value'], $t_custom_field_data['type'] ).'</td></tr>';
		}
		$cfdata .='</table>';
	} else {
		$cfdata = "Not Available";
	}
	$variables['cfdata'] = $cfdata ;

## process the notes, looping through the array
	$bugnotes = $issue_data['bugnotes'];
	if (count( $bugnotes ) > 0 ) {
		$notedata = '<table><tr><b><td width="15%">Date</td><td width="10%">Username</td><td width="75%">Note</td></b></tr>';
		foreach( $bugnotes as $t_note ) {
			$noteDate =  date( config_get( 'normal_date_format' ), $t_note->date_submitted );
			$notereporter = user_get_username($t_note->reporter_id);
			$notedata .= '<tr><td>'.$noteDate.'</td><td>'.$notereporter.'</td><td>'.$t_note->note.'</td></tr>';
		}
		$notedata .='</table>';
	} else {
		$notedata ='Not Available';
	}
	$variables['notedata'] = trim($notedata, " \t\n\r") ;

## process the relationships, looping through the array
	$relations = $issue_data['relations'];
	if ( !empty($relations  ) {
		$reldata = $relations;
	} else {
		$reldata = "Not Available";
	}
	$variables['reldata'] = $reldata ;

## process the history, looping through the array
	$history = $issue_data['history'];
	if( count( $history)  > 0 ) {
		$hisdata = '<table><tr><b><td width="15%">Date</td><td width="10%">Username</td><td width="25%">What</td><td width="50%">Change</td></b></tr>';
		foreach( $history as $t_his ) {
			$hisrecords++;
			$hisDate = date( config_get( 'normal_date_format' ), $t_his['date'] );
			$t_localized_item = history_localize_item( $t_his['field'], $t_his['type'], $t_his['old_value'], $t_his['new_value'], false );
			$hisdata .= '<tr><td>'.$hisDate . '</td><td>' . utf8_str_pad( $t_his['username'], 15 ). '</td><td>' . utf8_str_pad( $t_localized_item['note'], 25 ) . '</td><td>'. utf8_str_pad( $t_localized_item['change'], 20 ) . '</td></tr>';
			$lastdatestamp = $t_his['date'] ;
		}
		$hisdata .='</table>';	
	} else {
		$lastdatestamp = 0;
		$hisdata = 'Not Available';
	}
	$variables['hisdata'] = trim($hisdata, " \t\n\r") ;

## process the history, finding the last entries based upon $lastdastestamp
	$newissue = false ;
	If ($lastdatestamp<>0 ){
		$history = $issue_data['history'];
		$lasthisdata = '<table><tr><b><td width="15%">Date</td><td width="10%">Username</td><td width="25%">What</td><td width="50%">Change</td></b></tr>';
		foreach( $history as $t_his ) {
			if ( $t_his['date'] == $lastdatestamp ){
				$hisDate = date( config_get( 'normal_date_format' ), $t_his['date'] );
				$t_localized_item = history_localize_item( $t_his['field'], $t_his['type'], $t_his['old_value'], $t_his['new_value'], false );
				$lasthisdata .= '<tr><td>'.$hisDate . '</td><td>' . utf8_str_pad( $t_his['username'], 15 ). '</td><td>' . utf8_str_pad( $t_localized_item['note'], 25 ) . '</td><td>'. utf8_str_pad( $t_localized_item['change'], 20 ) . '</td></tr>';
				if ($t_his['type'] == 1) {
					$newissue = true;
				}
			}
		}
		$lasthisdata .='</table>';	
	} else {
		$lasthisdata = 'Not Available';
	}
	$variables['lasthisdata'] = trim($lasthisdata, " \t\n\r") ;

## next 2 are temp entries
$variables['reference'] = $_SERVER['HTTP_REFERER'];
$variables['mail_data'] = print_r($issue_data, true);
##

	// Load the template
	
	// first check if this is a new issue
	if ( $newissue ){
		$template_definition = config_get( 'newbug_mailtemplate' );
	} else {
		$template_definition = config_get( 'bug_mailtemplate' );
	}
	$template = file_get_contents(x$template_definitionx);
	
	// next assign the values to the template
	foreach( $variables as $key => $value ) {
		$template = str_replace( '{{ '.$key.' }}', $value, $template );
	}
	return $template;

}

function email_template_bugnote( $p_bugnote, $p_project_id, $p_show_time_tracking, $p_horizontal_separator, $top_line , $p_date_format = null ) {

	$t_date_format			= ( $p_date_format === null ) ? config_get( 'normal_date_format' ) : $p_date_format;

	$t_last_modified		= date( $t_date_format, $p_bugnote->last_modified );
	$t_formatted_bugnote_id = bugnote_format_id( $p_bugnote->id );
	$t_bugnote_link			= string_process_bugnote_link( config_get( 'bugnote_link_tag' ) . $p_bugnote->id, false, false, true );

	if( $p_show_time_tracking && $p_bugnote->time_tracking > 0 ) {
		$t_time_tracking = ' ' . lang_get( 'time_tracking' ) . ' ' . db_minutes_to_hhmm( $p_bugnote->time_tracking ) . "\n";
	} else {
		$t_time_tracking = '';
	}

	if( user_exists( $p_bugnote->reporter_id ) ) {
		$t_access_level = access_get_project_level( $p_project_id, $p_bugnote->reporter_id );
		$t_access_level_string = ' ( ' . access_level_get_string( $t_access_level ) . ' )';
	} else {
		$t_access_level_string = '';
	}

	// we move all data into an array called $variables
	$variables = array();
	$variables['top_line']	= $top_line;
	$variables['note_id']	= $t_formatted_bugnote_id;
	$reporter				= user_get_name( $p_bugnote->reporter_id );
	$variables['reporter']	= $reporter;
	$variables['access']	= $t_access_level_string;
	$variables['modified']	= $t_last_modified;
	$variables['private']	=  $t_private;
	$variables['timetrack'] = $t_time_tracking;
	$variables['url']		= $t_bugnote_link;
	$note					= $p_bugnote->note;
	$variables['note']		= $note;

	// fetch additional data
	$t_bug_id =  $p_bugnote->bug_id;

	$variables['bug_id']	= $t_bug_id;
	$variables['bug_url']	= string_get_bug_view_url_with_fqdn( $t_bug_id );
	$t_query        		= "SELECT summary, C.name AS category_name, P.name AS project_name FROM {bug} B , {category} C, {project} P WHERE B.category_id = C.id AND B.project_id = P.id and B.id = $t_bug_id";
	$t_result       		= db_query($t_query);
	$row_1 					= db_fetch_array($t_result);
	$t_summary      		= $row_1['summary'];
	$t_category     		= $row_1['category_name'];
	$t_project      		= $row_1['project_name'];
	$variables['summary']	= $t_summary;
	$variables['category']	= $t_category;
	$variables['project']	= $t_project;
	
	// Load the template
	$template_definition = config_get( 'note_mailtemplate' );
	$template = file_get_contents( $template_definition );
	
	// next assign the values to the template
	foreach( $variables as $key => $value ) {
		$template = str_replace( '{{ '.$key.' }}', $value, $template  );
	}
	return $template;
}