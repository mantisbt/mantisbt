<?php
	# @@@@@@ NTOE that the status colors are NOT set in CSS.  This should be fixed through a redesign.

	error_reporting( E_ALL );

	# If p_var isset then do nothing.  Otherwise set it to be
	#  the provided value
	#
	function check_varset( &$p_var, $p_default_value ) {
		if ( !isset( $p_var ) ) {
			$p_var = $p_default_value;
		}
	}

	# --- color values ----------------
	#
	check_varset( $g_background_color, 		"#ffffff" );
	check_varset( $g_required_color, 		"#ffffff" );
	check_varset( $g_table_border_color, 	"#000000" );
	check_varset( $g_category_title_color, 	"#c8c8e8" );
	check_varset( $g_primary_color1, 		"#d8d8d8" );
	check_varset( $g_primary_color2, 		"#e8e8e8" );
	check_varset( $g_form_title_color, 		"#ffffff" );
	check_varset( $g_spacer_color, 			"#ffffff" );
	check_varset( $g_menu_color, 			"#e8e8e8" );

	# --- status color codes ----------
	#
	check_varset( $g_new_color, 		"#ffa0a0" );
	check_varset( $g_feedback_color, 	"#ff50a8" );
	check_varset( $g_acknowledged_color,"#ffd850" );
	check_varset( $g_confirmed_color, 	"#ffffb0" );
	check_varset( $g_assigned_color, 	"#c8c8ff" );
	check_varset( $g_resolved_color, 	"#cceedd" );
	check_varset( $g_closed_color, 		"#ffffff" );

	# --- fonts ----------
	#
	check_varset( $g_fonts, 		"Verdana, Arial, Helvetica, sans-serif" );
	check_varset( $g_font_small, 	"8pt" );
	check_varset( $g_font_normal, 	"10pt" );
	check_varset( $g_font_large, 	"12pt" );
	check_varset( $g_font_color, 	"#000000" );

	# --- fonts ----------
	#
	check_varset( $g_background_font_color, 	"#000000" );
	check_varset( $g_required_font_color, 		"#bb0000" );
	check_varset( $g_category_title_font_color, "#000000" );
	check_varset( $g_primary_font_color1, 		"#000000" );
	check_varset( $g_primary_font_color2, 		"#000000" );
	check_varset( $g_form_title_font_color, 	"#000000" );
	check_varset( $g_menu_font_color, 			"#e8e8e8" );

	# --- links ----------
	#
	check_varset( $g_active_color, 			"#ffffff" );
	check_varset( $g_active_font_color, 	"#ff0000" );
	check_varset( $g_unvisited_color, 		"#ffffff" );
	check_varset( $g_unvisited_font_color, 	"#0000ff" );
	check_varset( $g_visited_color, 		"#ffffff" );
	check_varset( $g_visited_font_color, 	"#800080" );

	# --- Input ----------
	#
	check_varset( $g_button_color, 			"#d8d8d8" );
	check_varset( $g_button_font_color, 	"#000000" );
	check_varset( $g_button_border_color, 	"#000000" );
	check_varset( $g_text_color, 			"#ffffff" );
	check_varset( $g_text_font_color, 		"#000000" );
	check_varset( $g_text_border_color, 	"#000000" );
	check_varset( $g_textarea_color, 		"#ffffff" );
	check_varset( $g_textarea_font_color, 	"#000000" );
	check_varset( $g_textarea_border_color, "#000000" );
	# --- ---
?>
