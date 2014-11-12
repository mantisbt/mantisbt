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
 * Export Issues in XML Format
 */

require_once( 'core.php' );

access_ensure_project_level( plugin_config_get( 'export_threshold' ) );

auth_ensure_user_authenticated( );
helper_begin_long_process( );

$t_page_number = 1;
$t_per_page = -1;
$t_bug_count = null;
$t_page_count = null;

$t_nl = "\n";

# Get bug rows according to the current filter
$t_result = filter_get_bug_rows( $t_page_number, $t_per_page, $t_page_count, $t_bug_count );
if( $t_result === false ) {
	$t_result = array( );
}

$t_filename = 'exported_issues.xml';

# Send headers to browser to activate mime loading
# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );

header( 'Content-Type: text/xml; name=' . $t_filename );
header( 'Content-Transfer-Encoding: BASE64;' );

# Added Quotes (") around file name.
header( 'Content-Disposition: attachment; filename="' . $t_filename . '"' );

$t_version = MANTIS_VERSION;
$t_url = config_get( 'path' );
$t_bug_link = config_get( 'bug_link_tag' );
$t_bugnote_link = config_get( 'bugnote_link_tag' );

$t_writer = new XMLWriter;

$t_writer->openURI( 'php://output' );
$t_writer->setIndent( true );
$t_writer->setIndentString( '    ' );

$t_writer->startDocument( '1.0', 'UTF-8' );
$t_writer->startElement( 'mantis' );
$t_writer->writeAttribute( 'version', $t_version );
$t_writer->writeAttribute( 'urlbase', $t_url );
$t_writer->writeAttribute( 'issuelink', $t_bug_link );
$t_writer->writeAttribute( 'notelink', $t_bugnote_link );
$t_writer->writeAttribute( 'format', '1' );

# Ignored fields, these will be skipped
$t_ignore = array(
	'_stats',
	'bug_text_id',
);

# properties that we want to export are 'protected'
$t_columns = array_keys( getClassProperties( 'BugData', 'protected' ) );

# export the rows
foreach( $t_result as $t_row ) {

	$t_writer->startElement( 'issue' );

	foreach( $t_columns as $t_element ) {
		$t_value = $t_row->$t_element;
		if( empty( $t_value ) ) {
			continue;
		}

		if( in_array( $t_element, $t_ignore ) ) {
			continue;
		}

		switch( $t_element ) {
			case 'reporter_id':
			case 'handler_id':
				$t_element_name = substr( $t_element, 0, - 3 );
				$t_element_data = user_get_name( $t_value );

				$t_writer->startElement( $t_element_name );
				$t_writer->writeAttribute( 'id', $t_value );
				$t_writer->text( $t_element_data );
				$t_writer->endElement( );
				break;

			case 'category_id':

				# id for categories were introduced in 1.2
				$t_element_name = 'category';
				$t_element_data = category_get_name( $t_value );

				$t_writer->startElement( $t_element_name );
				$t_writer->writeAttribute( 'id', $t_value );
				$t_writer->text( $t_element_data );
				$t_writer->endElement( );
				break;

			case 'project_id':
				$t_element_name = 'project';
				$t_element_data = project_get_name( $t_value );

				$t_writer->startElement( $t_element_name );
				$t_writer->writeAttribute( 'id', $t_value );
				$t_writer->text( $t_element_data );
				$t_writer->endElement( );

				break;

			case 'eta':
			case 'priority':
			case 'projection':
			case 'reproducibility':
			case 'resolution':
			case 'severity':
			case 'status':
			case 'view_state':
				$t_element_data = get_enum_element( $t_element, $t_value );

				$t_writer->startElement( $t_element );
				$t_writer->writeAttribute( 'id', $t_value );
				$t_writer->text( $t_element_data );
				$t_writer->endElement( );
				break;

			default:
				$t_writer->writeElement( $t_element, $t_value );
		}
	}

	# fetch and export custom fields
	$t_custom_fields = custom_field_get_all_linked_fields( $t_row->id );
	if( is_array( $t_custom_fields ) && count( $t_custom_fields ) > 0 ) {
		$t_writer->startElement( 'custom_fields' );
		foreach ( $t_custom_fields as $t_custom_field_name => $t_custom_field ) {
			$t_writer->startElement( 'custom_field' );
			# id
			$t_writer->writeElement( 'id', custom_field_get_id_from_name( $t_custom_field_name ) );
			# title
			$t_writer->writeElement( 'name', $t_custom_field_name );
			# filename
			$t_writer->writeElement( 'type', $t_custom_field['type'] );
			# filesize
			$t_writer->writeElement( 'value', $t_custom_field['value'] );
			# file_type
			$t_writer->writeElement( 'access_level_r', $t_custom_field['access_level_r'] );

			$t_writer->endElement(); # custom_field
		}
		$t_writer->endElement(); # custom_fields
	}

	# fetch and export bugnotes
	$t_bugnotes = bugnote_get_all_bugnotes( $t_row->id );
	if( is_array( $t_bugnotes ) && count( $t_bugnotes ) > 0 ) {
		$t_writer->startElement( 'bugnotes' );
		foreach ( $t_bugnotes as $t_bugnote ) {
			$t_writer->startElement( 'bugnote' );
			# id
			$t_writer->writeElement( 'id', $t_bugnote->id );
			# reporter
			$t_writer->startElement( 'reporter' );
			$t_writer->writeAttribute( 'id', $t_bugnote->reporter_id );
			$t_writer->text( user_get_name( $t_bugnote->reporter_id ) );
			$t_writer->endElement( );
			# bug note
			$t_writer->writeElement( 'note', $t_bugnote->note );
			# view state
			$t_writer->startElement( 'view_state' );
			$t_writer->writeAttribute( 'id', $t_bugnote->view_state );
			$t_writer->text( get_enum_element( 'view_state', $t_bugnote->view_state ) );
			$t_writer->endElement( );
			# date submitted
			$t_writer->writeElement( 'date_submitted', $t_bugnote->date_submitted );
			# last modified
			$t_writer->writeElement( 'last_modified', $t_bugnote->last_modified );
			# note type
			$t_writer->writeElement( 'note_type', $t_bugnote->note_type );
			# note attr
			$t_writer->writeElement( 'note_attr', $t_bugnote->note_attr );
			# time tracking
			$t_writer->writeElement( 'time_tracking', $t_bugnote->time_tracking );

			$t_writer->endElement(); # bugnote
		}
		$t_writer->endElement(); # bugnotes
	}

	# fetch and export attachments
	$t_attachments = bug_get_attachments( $t_row->id );
	if( is_array( $t_attachments ) && count( $t_attachments ) > 0 ) {
		$t_writer->startElement( 'attachments' );
		foreach ( $t_attachments as $t_attachment ) {
			$t_writer->startElement( 'attachment' );
			# id
			$t_writer->writeElement( 'id', $t_attachment['id'] );
			# title
			$t_writer->writeElement( 'title', $t_attachment['title'] );
			# filename
			$t_writer->writeElement( 'filename', $t_attachment['filename'] );
			# filesize
			$t_writer->writeElement( 'filesize', $t_attachment['filesize'] );
			# file_type
			$t_writer->writeElement( 'file_type', $t_attachment['file_type'] );
			# last added
			$t_writer->writeElement( 'date_added', $t_attachment['date_added'] );
			# content
			$t_content = file_get_content( $t_attachment['id'] );

			$t_writer->writeElement( 'content', base64_encode( $t_content['content'] ) );

			$t_writer->endElement(); # attachment
		}
		$t_writer->endElement(); # bugnotes
	}

	$t_writer->endElement(); # issue

	# Save memory by clearing cache
	# bug_clear_cache();
	# bug_text_clear_cache();
}

$t_writer->endElement(); # mantis
$t_writer->endDocument( );
