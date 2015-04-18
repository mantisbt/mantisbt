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
#
# --------------------------------------------------------
# $Id$
# --------------------------------------------------------

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

$t_filename = "exported_issues.xml";

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

$writer = new XMLWriter;

$writer->openURI( 'php://output' );
$writer->setIndent( true );
$writer->setIndentString( '    ' );

$writer->startDocument( '1.0', 'UTF-8' );
$writer->startElement( 'mantis' );
$writer->writeAttribute( 'version', $t_version );
$writer->writeAttribute( 'urlbase', $t_url );
$writer->writeAttribute( 'issuelink', $t_bug_link );
$writer->writeAttribute( 'notelink', $t_bugnote_link );
$writer->writeAttribute( 'format', '1' );

# Ignored fields, these will be skipped
$t_ignore = array(
	'_stats',
	'bug_text_id',
);

/* properties that we want to export are 'protected' */
$t_columns = array_keys( getClassProperties('BugData', 'protected') );

# export the rows
foreach( $t_result as $t_row ) {

	$writer->startElement( 'issue' );

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

				$writer->startElement( $t_element_name );
				$writer->writeAttribute( 'id', $t_value );
				$writer->text( $t_element_data );
				$writer->endElement( );
				break;

			case 'category_id':

				// id for categories were introduced in 1.2
				$t_element_name = 'category';
				$t_element_data = category_get_name( $t_value );

				$writer->startElement( $t_element_name );
				$writer->writeAttribute( 'id', $t_value );
				$writer->text( $t_element_data );
				$writer->endElement( );
				break;

			case 'project_id':
				$t_element_name = 'project';
				$t_element_data = project_get_name( $t_value );

				$writer->startElement( $t_element_name );
				$writer->writeAttribute( 'id', $t_value );
				$writer->text( $t_element_data );
				$writer->endElement( );

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

				$writer->startElement( $t_element );
				$writer->writeAttribute( 'id', $t_value );
				$writer->text( $t_element_data );
				$writer->endElement( );
				break;

			default:
				$writer->writeElement( $t_element, $t_value );
		}
	}
	$writer->endElement(); # issue

	// Save memory by clearing cache
	//bug_clear_cache();
	//bug_text_clear_cache();
}

$writer->endElement(); # mantis
$writer->endDocument( );
