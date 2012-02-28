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
 * Excel API
 * @package CoreAPI
 * @subpackage ExcelAPI
 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 */

/**
 * A method that returns the header for an Excel Xml file.
 *
 * @param string $p_worksheet_title  The worksheet title.
 * @param array $p_styles An optional array of ExcelStyle entries . Parent entries must be placed before child entries
 * @returns the header Xml.
 */
function excel_get_header( $p_worksheet_title, $p_styles = array() ) {
	$p_worksheet_title = preg_replace( '/[\/:*?"<>|]/', '', $p_worksheet_title );
	return "<?xml version=\"1.0\" encoding=\"UTF-8\"?><?mso-application progid=\"Excel.Sheet\"?>
 <Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n ". excel_get_styles( $p_styles ). "<Worksheet ss:Name=\"" . urlencode( $p_worksheet_title ) . "\">\n<Table>\n<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
}

/**
 * Returns an XML string containing the <tt>ss:Styles</tt> entry, possibly empty
 *
 * @param array $p_styles an array of ExcelStyle entries
 * @return void|string
 */
function excel_get_styles( $p_styles ) {

    if ( count ( $p_styles ) == 0 ) {
        return;
    }

    $t_styles_string = '<ss:Styles>';

    foreach ( $p_styles as $t_style ) {
        $t_styles_string .= $t_style->asXml();
    }
    $t_styles_string .= '</ss:Styles>';

    return $t_styles_string;
}

/**
 * A method that returns the footer for an Excel Xml file.
 * @returns the footer xml.
 */
function excel_get_footer() {
	return "</Table>\n</Worksheet></Workbook>\n";
}

/**
 * Generates a cell XML for a column title.
 * @returns The cell xml.
 */
function excel_format_column_title( $p_column_title ) {
	return '<Cell><Data ss:Type="String">' . $p_column_title . '</Data></Cell>';
}

/**
 * Generates the xml for the start of an Excel row.
 *
 * @param string $p_style_id The optional style id
 * @returns The Row tag.
 */
function excel_get_start_row( $p_style_id = '') {
    if ( $p_style_id != '' ) {
        return '<Row ss:StyleID="' . $p_style_id . '">';
    } else {
	    return '<Row>';
    }
}

/**
 * Generates the xml for the end of an Excel row.
 * @returns The Row end tag.
 */
function excel_get_end_row() {
	return '</Row>';
}

/**
 * Gets an Xml Row that contains all column titles
 * @param string $p_style_id The optional style id.
 * @returns The xml row.
 */
function excel_get_titles_row( $p_style_id = '') {
	$t_columns = excel_get_columns();
	$t_ret = excel_get_start_row( $p_style_id );

	foreach( $t_columns as $t_column ) {
		$t_custom_field = column_get_custom_field_name( $t_column );
		if( $t_custom_field !== null ) {
			$t_ret .= excel_format_column_title( lang_get_defaulted( $t_custom_field ) );
		} else {
			$t_column_title = column_get_title( $t_column );
			$t_ret .= excel_format_column_title( $t_column_title );
		}
	}

	$t_ret .= '</Row>';

	return $t_ret;
}

/**
 * Gets the download file name for the Excel export.  If 'All Projects' selected, default to <username>,
 * otherwise default to <projectname>.
* @returns file name without extension
*/
function excel_get_default_filename() {
	$t_current_project_id = helper_get_current_project();

	if( ALL_PROJECTS == $t_current_project_id ) {
		$t_filename = user_get_name( auth_get_current_user_id() );
	} else {
		$t_filename = project_get_field( $t_current_project_id, 'name' );
	}

	return $t_filename;
}

/**
 * Escapes the specified column value and includes it in a Cell Xml.
 * @param $p_value The value
 * @returns The Cell Xml.
 */
function excel_prepare_string( $p_value ) {
	$t_type = is_numeric( $p_value ) ? 'Number' : 'String';

	$t_value = str_replace( array ( '&', "\n", '<', '>'), array ( '&amp;', '&#10;', '&lt;', '&gt;' ),  $p_value );

	return excel_get_cell( $t_value,  $t_type );
}

/**
 * Returns an <tt>Cell</tt> as an XML string
 *
 * <p>All the parameters are assumed to be valid and escaped, as this function performs no
 * escaping of its own.</p>
 *
 * @param string $p_value
 * @param string $p_type
 * @param array $p_attributes An array where the keys are attribute names and values attribute
 * values for the <tt>Cell</tt> object
 * @return string
 */
function excel_get_cell( $p_value, $p_type, $p_attributes = array() ) {
    $t_ret = "<Cell ";

    foreach ( $p_attributes as $t_attribute_name => $t_attribute_value ) {
        $t_ret .= $t_attribute_name. '="' . $t_attribute_value . '" ';
    }

    $t_ret .= ">";

    $t_ret .= "<Data ss:Type=\"$p_type\">" . $p_value . "</Data></Cell>\n";

    return $t_ret;
}

/**
 * Gets the columns to be included in the Excel Xml export.
 * @returns column names.
 */
function excel_get_columns() {
	$t_columns = helper_get_columns_to_view( COLUMNS_TARGET_EXCEL_PAGE );
	return $t_columns;
}

#
# Formatting Functions
#
# Names for formatting functions are excel_format_*, where * corresponds to the
# field name as return get excel_get_columns() and by the filter api.
#
/**
 * Gets the formatted bug id value.
 * @param object $p_bug the bug
 * @returns The bug id prefixed with 0s.
 */
function excel_format_id( $p_bug ) {
	return excel_prepare_string( bug_format_id( $p_bug->id ) );
}

/**
 * Gets the formatted project id value.
 * @param object $p_bug the bug
 * @returns The project name.
 */
function excel_format_project_id( $p_bug ) {
	return excel_prepare_string( project_get_name( $p_bug->project_id ) );
}

/**
 * Gets the formatted reporter id value.
 * @param object $p_bug the bug
 * @returns The reporter user name.
 */
function excel_format_reporter_id( $p_bug ) {
	return excel_prepare_string( user_get_name( $p_bug->reporter_id ) );
}

/**
 * Gets the formatted number of bug notes.
 * @param object $p_bug the bug
 * @returns The number of bug notes.
 */
function excel_format_bugnotes_count( $p_bug ) {
	return excel_prepare_string( $p_bug->bugnotes_count );
}

/**
 * Gets the formatted handler id.
 * @param object $p_bug the bug
 * @returns The handler user name or empty string.
 */
function excel_format_handler_id( $p_bug ) {
	if( $p_bug->handler_id > 0 ) {
		return excel_prepare_string( user_get_name( $p_bug->handler_id ) );
	} else {
		return excel_prepare_string( '' );
	}
}

/**
 * Gets the formatted priority.
 * @param object $p_bug the bug
 * @returns the priority text.
 */
function excel_format_priority( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'priority', $p_bug->priority, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the formatted severity.
 * @param object $p_bug the bug
 * @returns the severity text.
 */
function excel_format_severity( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'severity', $p_bug->severity, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the formatted reproducibility.
 * @param object $p_bug the bug
 * @returns the reproducibility text.
 */
function excel_format_reproducibility( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'reproducibility', $p_bug->reproducibility, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the formatted view state,
 * @param object $p_bug the bug
 * @returns The view state
 */
function excel_format_view_state( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'view_state', $p_bug->view_state, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the formatted projection.
 * @param object $p_bug the bug
 * @returns the projection text.
 */
function excel_format_projection( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'projection', $p_bug->projection, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the formatted eta.
 * @param object $p_bug the bug
 * @returns the eta text.
 */
function excel_format_eta( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'eta', $p_bug->eta, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the status field.
 * @param object $p_bug the bug
 * @returns the formatted status.
 */
function excel_format_status( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'status', $p_bug->status, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the resolution field.
 * @param object $p_bug the bug
 * @returns the formatted resolution.
 */
function excel_format_resolution( $p_bug ) {
	return excel_prepare_string( get_enum_element( 'resolution', $p_bug->resolution, auth_get_current_user_id(), $p_bug->project_id ) );
}

/**
 * Gets the formatted version.
 * @param object $p_bug the bug
 * @returns the product version.
 */
function excel_format_version( $p_bug ) {
	return excel_prepare_string( $p_bug->version );
}

/**
 * Gets the formatted fixed in version.
 * @param object $p_bug the bug
 * @returns the fixed in version.
 */
function excel_format_fixed_in_version( $p_bug ) {
	return excel_prepare_string( $p_bug->fixed_in_version );
}

/**
 * Gets the formatted target version.
 * @param object $p_bug the bug
 * @returns the target version.
 */
function excel_format_target_version( $p_bug ) {
	return excel_prepare_string( $p_bug->target_version );
}

/**
 * Gets the formatted category.
 * @param object $p_bug the bug
 * @returns the category.
 */
function excel_format_category_id( $p_bug ) {
	return excel_prepare_string( category_full_name( $p_bug->category_id, false ) );
}

/**
 * Gets the formatted operating system.
 * @param object $p_bug the bug
 * @returns the operating system.
 */
function excel_format_os( $p_bug ) {
	return excel_prepare_string( $p_bug->os );
}

/**
 * Gets the formatted operating system build (version).
 * @param object $p_bug the bug
 * @returns the operating system build (version)
 */
function excel_format_os_build( $p_bug ) {
	return excel_prepare_string( $p_bug->os_build );
}

/**
 * Gets the formatted product build,
 * @param object $p_bug the bug
 * @returns the product build.
 */
function excel_format_build( $p_bug ) {
	return excel_prepare_string( $p_bug->build );
}

/**
 * Gets the formatted platform,
 * @param object $p_bug the bug
 * @returns the platform.
 */
function excel_format_platform( $p_bug ) {
	return excel_prepare_string( $p_bug->platform );
}

/**
 * Gets the formatted date submitted.
 * @param object $p_bug the bug
 * @returns the date submitted in short date format.
 */
function excel_format_date_submitted( $p_bug ) {
	return excel_prepare_string( date( config_get( 'short_date_format' ), $p_bug->date_submitted ) );
}

/**
 * Gets the formatted date last updated.
 * @param object $p_bug the bug
 * @returns the date last updated in short date format.
 */
function excel_format_last_updated( $p_bug ) {
	return excel_prepare_string( date( config_get( 'short_date_format' ), $p_bug->last_updated ) );
}

/**
 * Gets the summary field.
 * @param object $p_bug the bug
 * @returns the formatted summary.
 */
function excel_format_summary( $p_bug ) {
	return excel_prepare_string( $p_bug->summary );
}

/**
 * Gets the formatted selection.
 * @param object $p_bug the bug
 * @returns An formatted empty string.
 */
function excel_format_selection( $p_bug ) {
	return excel_prepare_string( '' );
}

/**
 * Gets the formatted description field.
 * @param object $p_bug the bug
 * @returns The formatted description (multi-line).
 */
function excel_format_description( $p_bug ) {
	return excel_prepare_string( $p_bug->description );
}

/**
 * Gets the formatted 'steps to reproduce' field.
 * @param object $p_bug the bug
 * @returns The formatted steps to reproduce (multi-line).
 */
function excel_format_steps_to_reproduce( $p_bug ) {
	return excel_prepare_string( $p_bug->steps_to_reproduce );
}

/**
 * Gets the formatted 'additional information' field.
 * @param object $p_bug the bug
 * @returns The formatted additional information (multi-line).
 */
function excel_format_additional_information( $p_bug ) {
	return excel_prepare_string( $p_bug->additional_information );
}

/**
 * Gets the formatted value for the specified issue id, project and custom field.
 * @param $p_issue_id The issue id.
 * @param $p_project_id The project id.
 * @param $p_custom_field The custom field name (without 'custom_' prefix).
 * @returns The custom field value.
 */
function excel_format_custom_field( $p_issue_id, $p_project_id, $p_custom_field ) {
	$t_field_id = custom_field_get_id_from_name( $p_custom_field );

	if( $t_field_id === false ) {
		return excel_prepare_string( '@' . $p_custom_field . '@' );
	}

	if( custom_field_is_linked( $t_field_id, $p_project_id ) ) {
		$t_def = custom_field_get_definition( $t_field_id );
		return excel_prepare_string( string_custom_field_value( $t_def, $t_field_id, $p_issue_id ) );
	}

	// field is not linked to project
	return excel_prepare_string( '' );
}

/**
 * Gets the formatted value for the specified plugin column value.
 * @param $p_custom_field The plugin column name.
 * @param $p_bug The bug to print the column for (needed for the display function of the plugin column).
 * @returns The plugin column value.
 */
function excel_format_plugin_column_value( $p_column, $p_bug ) {
	$t_plugin_columns = columns_get_plugin_columns();

	if ( !isset( $t_plugin_columns[$p_column] ) ) {
		return excel_prepare_string( '' );
	} else {
		$t_column_object = $t_plugin_columns[ $p_column ];
		ob_start();
		$t_column_object->display( $p_bug, COLUMNS_TARGET_EXCEL_PAGE );
		$t_value = ob_get_clean();
		return excel_prepare_string( $t_value );
	}
}

/**
 * Gets the formatted due date.
 * @param object $p_bug the bug
 * @returns The formatted due date.
 */
function excel_format_due_date( $p_bug ) {
	return excel_prepare_string( date( config_get( 'short_date_format' ), $p_bug->due_date ) );
}

/**
 * Gets the sponsorship total for an issue
 * @param object $p_bug the bug
 * @return string
 * @access public
 */
function excel_format_sponsorship_total( $p_bug ) {
	return excel_prepare_string( $p_bug->sponsorship_total );
}

/**
 * The <tt>ExcelStyle</tt> class is able to render style information
 *
 * <p>For more information regarding the values taken by the parameters of this class' methods
 * please see <a href="http://msdn.microsoft.com/en-us/library/aa140066(v=office.10).aspx#odc_xmlss_ss:style">
 * the ss:Style documentation</a>.</p>
 *
 */
class ExcelStyle {

    private $id;
    private $parent_id;

    private $interior;
    private $font;
    private $border;
    private $alignment;

    /**
     * @param string $p_id The unique style id
     * @param string $p_parent_id The parent style id
     */
    function __construct( $p_id , $p_parent_id  = '') {

        $this->id = $p_id;
        $this->parent_id = $p_parent_id;
    }

    function getId() {

        return $this->id;
    }

    /**
     * @param string $p_color the color in #rrggbb format or a named color
     * @param string $p_pattern
     */
    function setBackgroundColor( $p_color, $p_pattern = 'Solid' ) {

        if ( ! isset ( $this->interior ) ) {
            $this->interior = new Interior();
        }

        $this->interior->color = $p_color;
        $this->interior->pattern = $p_pattern;
    }

    /**
     *
     * @param int $p_bold 1 for bold, 0 for not bold
     * @param string $p_color the color in #rrggbb format or a named color
     * @param string $p_name the name of the font
     * @param int $p_italic 1 for italic, 0 for not italic
     */

    function setFont( $p_bold, $p_color = '', $p_name = '', $p_italic = -1 ) {

        if ( ! isset ( $this->font ) ) {
            $this->font = new Font();
        }

        if ( $p_bold != -1 ) {
            $this->font->bold = $p_bold;
        }
        if ( $p_color != '' ) {
            $this->font->color = $p_color;
        }
        if ( $p_name != '' ) {
            $this->font->fontName = $p_name;
        }
        if ( $p_italic != -1 ) {
            $this->font->italic = $p_italic;
        }
    }


    /**
     * Sets the border values for the style
     *
     * <p>The values are set for the following positions: Left, Top, Right, Bottom. There is no
     * support for setting individual values.</p>
     *
     * @param string $p_color the color in #rrggbb format or a named color
     * @param string $p_line_style None, Continuous, Dash, Dot, DashDot, DashDotDot, SlantDashDot, or Double
     * @param string $p_weight Thickness in points
     */
    function setBorder( $p_color, $p_line_style = 'Continuous', $p_weight = 1) {

        if ( ! isset ( $this->border ) ) {
            $this->border = new Border();
        }

        if ( $p_color != '' ) {
            $this->border->color = $p_color;
        }

        if ( $p_line_style != '' ) {
            $this->border->lineStyle = $p_line_style;
        }

        if ( $p_weight != -1 ) {
            $this->border->weight = $p_weight;
        }
    }

    /**
     * Sets the aligment for the style
     *
     * @param int $p_wrap_text 1 to wrap, 0 to not wrap
     * @param string $p_horizontal Automatic, Left, Center, Right, Fill, Justify, CenterAcrossSelection, Distributed, and JustifyDistributed
     * @param string $p_vertical Automatic, Top, Bottom, Center, Justify, Distributed, and JustifyDistributed
     */
    function setAlignment( $p_wrap_text, $p_horizontal = '', $p_vertical = '') {

        if ( ! isset ( $this->alignment ) ) {
            $this->alignment = new Alignment();
        }

        if ( $p_wrap_text != '' ) {
            $this->alignment->wrapText = $p_wrap_text;
        }

        if ( $p_horizontal != '' ) {
            $this->alignment->horizontal = $p_horizontal;
        }

        if ( $p_vertical != '' ) {
            $this->alignment->vertical = $p_vertical;
        }

    }

    function asXml() {

        $xml = '<ss:Style ss:ID="' . $this->id.'" ss:Name="'.$this->id.'" ';
        if ( $this->parent_id != '' ) {
            $xml .= 'ss:Parent="' . $this->parent_id .'" ';
        }
        $xml .= '>';
        if ( $this->interior ) {
            $xml .= $this->interior->asXml();
        }
        if ( $this->font ) {
            $xml .= $this->font->asXml();
        }
        if ( $this->border ) {
            $xml .= $this->border->asXml();
        }
        if ( $this->alignment ) {
            $xml .= $this->alignment->asXml();
        }
        $xml .= '</ss:Style>'."\n";

        return $xml;
    }
}

class Interior {

    public $color;
    public $pattern;

    function asXml() {

        $xml = '<ss:Interior ';

        if ( $this->color ) {
           $xml .= 'ss:Color="' . $this->color .'" ss:Pattern="'. $this->pattern . '" ';
        }

        $xml .= '/>';

        return $xml;
    }
}

class Font {

    public $bold;
    public $color;
    public $fontName;
    public $italic;

    function asXml() {

        $xml = '<ss:Font ';

        if ( $this->bold ) {
            $xml .= 'ss:Bold="' . $this->bold .'" ';
        }

        if ( $this->color ) {
            $xml .= 'ss:Color="' . $this->color .'" ';
        }

        if ( $this->fontName) {
            $xml .= 'ss:FontName="' . $this->fontName .'" ';
        }

        if ( $this->italic ) {
            $xml .= 'ss:Italic="' . $this->italic .'" ';
        }

        $xml .= '/>';

        return $xml;
    }
}

class Border {

    private $positions = array('Left', 'Top', 'Right', 'Bottom');

    public $color;
    public $lineStyle;
    public $weight;

    function asXml() {

        $xml = '<ss:Borders>';

        foreach ( $this->positions as $p_position ) {

            $xml.= '<ss:Border ss:Position="' . $p_position .'" ';

            if ( $this->lineStyle ) {
                $xml .= 'ss:LineStyle="' . $this->lineStyle .'" ';
            }

            if ( $this->color ) {
                $xml .= 'ss:Color="' . $this->color .'" ';
            }

            if ( $this->weight) {
                $xml .= 'ss:Weight="' . $this->weight .'" ';
            }

            $xml.= '/>';
        }

        $xml .= '</ss:Borders>';

        return $xml;
    }
}

class Alignment {

    public $wrapText;
    public $horizontal;
    public $vertical;

    function asXml() {

        $xml = '<ss:Alignment ';

        if ( $this->wrapText ) {
            $xml .= 'ss:WrapText="' . $this->wrapText.'" ';
        }

        if ( $this->horizontal ) {
            $xml .= 'ss:Horizontal="' . $this->horizontal.'" ';
        }

        if ( $this->vertical ) {
            $xml .= 'ss:Vertical="' . $this->vertical.'" ';
        }

        $xml .= '/>';

        return $xml;
    }
}
