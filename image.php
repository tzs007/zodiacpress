<?php // This file creates the chart image
if ( isset( $_GET['zpl'] ) ) {
	$longitudes_raw = unserialize( $_GET['zpl'] );
	for ( $n = 0; $n <= 16; $n++ ) {
		$longitudes[] = zpcd_sanitize_data( $longitudes_raw[ $n ] );
	}
}
if ( isset( $_GET['zpc'] ) ) {
	$cusps_raw = unserialize( $_GET['zpc'] );
	for ( $c = 1; $c <= 12; $c++ ) {
		$cusps[ $c ] = zpcd_sanitize_data( $cusps_raw[ $c ] );
	}
}
if ( isset( $_GET['zps'] ) ) {
	$speed_raw = unserialize( $_GET['zps'] );
	for ( $s = 0; $s <= 12; $s++ ) {
		$speed[ $s ] = zpcd_sanitize_data( $speed_raw[ $s ] );
	}
}
if ( isset( $_GET['zpu'] ) ) {
	$unknown_time = zpcd_sanitize_data( unserialize( $_GET['zpu'] ) );
}
if ( isset( $_GET['zpi'] ) ) {
	$i18n_raw = unserialize( $_GET['zpi'] );

	foreach ( $i18n_raw as $key => $str ) {
		$i18n[ $key ] = zpcd_sanitize_data( $str );
	}
}

if ( isset( $_GET['zpcustom'] ) ) {
	$customizer_raw = unserialize( $_GET['zpcustom'] );

	foreach ( $customizer_raw as $key => $value ) {
		$customizer[ $key ] = zpcd_sanitize_data( $value );
	}
}

header ('Content-Type: image/png');

$dir = rtrim( dirname( __FILE__ ), '/\\' ) . '/assets/fonts/';// @test now

$first_house = $cusps[1];
// create the blank image
$overall_size = 660;
$im = @imagecreatetruecolor( $overall_size, $overall_size ) or die( 'Cannot initialize new GD image stream' );

// Define colors
$black = imagecolorallocate($im, 0, 0, 0);
$white = imagecolorallocate($im, 255, 255, 255);
$red = imagecolorallocate($im, 207,0,15);
$blue = imagecolorallocate($im, 65,105,225);
$green = imagecolorallocate($im, 0,215,23);
$orange = imagecolorallocate($im, 245, 171, 53);
$bright_red = imagecolorallocate($im, 255, 0, 0);
$bright_blue = imagecolorallocate($im, 0, 0, 255);
$bright_green = imagecolorallocate($im, 0, 224, 0);
$blues_blue = imagecolorallocate($im, 31,141,186);
$gray = imagecolorallocate($im,233,233,233);
$off_white = imagecolorallocate($im,248,248,248);

// Convert customizer hex colors to rbg values
if ( ! empty( $customizer ) ) {
	foreach ( $customizer as $k => $hex ) {
		$customizer_rgb[ $k ] = zpcd_hex2rgb( $hex );
	}
}

/************************************************************
*
* @test
*
************************************************************/
// $debug3 = '';
// if(isset( $customizer_rgb['outer_bg_color'] ) ) {

// 	$debug3 = print_r( $customizer_rgb['outer_bg_color'], true );
// }
// if(isset( $cusps ) ) {

// 	$debug3 = print_r( $cusps, true );
// }






// ------------------------------------------

// Set default colors and get customizer colors

$outer_bg_color = isset( $customizer_rgb['outer_bg_color'] ) ?
				imagecolorallocate($im,$customizer_rgb['outer_bg_color'][0], $customizer_rgb['outer_bg_color'][1],$customizer_rgb['outer_bg_color'][2]) :
				$gray;

$signs_wheel_color = isset( $customizer_rgb['signs_wheel_color'] ) ?
				imagecolorallocate($im,$customizer_rgb['signs_wheel_color'][0], $customizer_rgb['signs_wheel_color'][1],$customizer_rgb['signs_wheel_color'][2]) :
				$white;

$signs_divider_color = $black;
$signs_border_color = $black;
$wheel_bg_color = $off_white;
$houses_border_color = $black;
$houses_divider_color = $black;
$angles_arrow_color = $black;
$planet_glyph_color = $black;
$house_number_color = $black;
$angle_degree_color = $bright_blue;
$inner_wheel_color = $white;
$inner_wheel_border_color = $black;
$hard_aspect_color = $bright_red;
$soft_aspect_color = $blues_blue;
$minor_aspect_color = $bright_green;
$fire_sign_color = $red;
$earth_sign_color = $green;
$air_sign_color = $orange;
$water_sign_color = $blue;

// ------------------------------------------

$diameter = 500; // diameter of houses circle
$outer_diameter = 600; // diameter of signs circle
$outer_diameter_distance = ($outer_diameter - $diameter) / 2; // distance between outer-outer diameter and diameter
$inner_diameter_offset = 90; // diameter of inner circle

$dist_from_diameter_i = 40;	// distance inner planet glyph is from circumference of wheel
$dist_from_diameter_line_i = 12;// distance of line to inner planet glyph
$dist_from_diameter_dms_i = 60;	// distance of dms from circumference of wheel for inner planet
$dist_from_diameter_o = 58;	// distance outer planet glyph is from circumference of wheel
$dist_from_diameter_line_o = 28;// distance of line to outer planet glyph
$dist_from_diameter_dms_o = 78; // distance of dms circumference of wheel - for outer planet

$radius = $diameter / 2; // radius of houses circle
$radius_outer = $outer_diameter / 2;// radius of signs circle
$radius_inner = ( $diameter - ($inner_diameter_offset * 2) ) / 2;
$center_pt = $overall_size / 2; // center of circle

$last_planet_num = 16;
$num_planets = $last_planet_num + 1; // add 1 for sorting functions

// glyphs used for planets - HamburgSymbols.ttf - Sun, Moon - Pluto
$pl_glyph[0] = 81;
$pl_glyph[1] = 87;
$pl_glyph[2] = 69;
$pl_glyph[3] = 82;
$pl_glyph[4] = 84;
$pl_glyph[5] = 89;
$pl_glyph[6] = 85;
$pl_glyph[7] = 73;
$pl_glyph[8] = 79;
$pl_glyph[9] = 80;
$pl_glyph[10] = 77; // chiron
$pl_glyph[11] = 96;
$pl_glyph[12] = 141;
$pl_glyph[13] = 60; //Part of Fortune
$pl_glyph[14] = 109; //vertex
$pl_glyph[15] = 90; //Ascendant
$pl_glyph[16] = 88; //Midheaven

// glyphs used for planets - HamburgSymbols.ttf - Aries - Pisces
$sign_glyph[1] = 97;
$sign_glyph[2] = 115;
$sign_glyph[3] = 100;
$sign_glyph[4] = 102;
$sign_glyph[5] = 103;
$sign_glyph[6] = 104;
$sign_glyph[7] = 106;
$sign_glyph[8] = 107;
$sign_glyph[9] = 108;
$sign_glyph[10] = 122;
$sign_glyph[11] = 120;
$sign_glyph[12] = 99;

// ------------------------------------------

// create colored rectangle on blank image
imagefilledrectangle( $im, 0, 0, $overall_size, $overall_size, $white );

// draw the outer border of the chartwheel
imagefilledellipse( $im, $center_pt, $center_pt, $overall_size - 1, $overall_size - 1, $outer_bg_color );// make it smaller to fit antialiased border

// antialias border to overall outer
zpcd_antialiased_ellipse($im, $center_pt, $center_pt, $center_pt - 0.5, $center_pt - 0.5, $outer_bg_color);

// draw the signs circle of the chartwheel
imagefilledellipse($im, $center_pt, $center_pt, $outer_diameter, $outer_diameter, $signs_wheel_color);

zpcd_antialiased_ellipse($im, $center_pt, $center_pt, $radius_outer, $radius_outer, $signs_border_color);

// draw the houses circle of the chartwheel
imagefilledellipse($im, $center_pt, $center_pt, $diameter, $diameter, $wheel_bg_color);
zpcd_antialiased_ellipse($im, $center_pt, $center_pt, $radius, $radius, $houses_border_color);

// draw the inner circle of the chartwheel
imagefilledellipse($im, $center_pt, $center_pt, $diameter - ($inner_diameter_offset * 2), $diameter - ($inner_diameter_offset * 2), $inner_wheel_color);
zpcd_antialiased_ellipse($im, $center_pt, $center_pt, $radius_inner, $radius_inner, $inner_wheel_border_color);

// ------------------------------------------

// draw the dividing lines between the signs
$offset_from_start_of_sign = $first_house - (floor($first_house / 30) * 30);

for ($i = $offset_from_start_of_sign; $i <= $offset_from_start_of_sign + 330; $i = $i + 30) {
	$x1 = -$radius * cos(deg2rad($i));
	$y1 = -$radius * sin(deg2rad($i));

	$x2 = -($radius + $outer_diameter_distance) * cos(deg2rad($i));
	$y2 = -($radius + $outer_diameter_distance) * sin(deg2rad($i));

	zpcd_image_smooth_line( $im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $signs_divider_color );

}

// ------------------------------------------

// If time is known, draw house cusps, Asc, Desc, MC lines, arrows
if ( empty( $unknown_time ) ) {

	// draw the lines for the house cusps
	for ( $i = 1; $i <= 12; $i = $i + 1 ) {
		$angle = $first_house - $cusps[ $i ];
		$x1 = -$radius * cos( deg2rad( $angle ) );
		$y1 = -$radius * sin( deg2rad( $angle ) );

		$x2 = -( $radius - $inner_diameter_offset ) * cos( deg2rad( $angle ) );
		$y2 = -( $radius - $inner_diameter_offset ) * sin( deg2rad( $angle ) );

		zpcd_image_smooth_line( $im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $houses_divider_color );

		// display the house cusp numbers
		zpcd_house_number_coordinates( $i, -$angle, $radius - $inner_diameter_offset, $xy );
		imagettftext( $im, 10, 0, $xy[0] + $center_pt, $xy[1] + $center_pt, $house_number_color, $dir . 'HamburgSymbols.ttf', $i );
	}

	// ------------------------------------------

	//draw the line for the Ascendant
	$angle = $first_house - $longitudes[15];
	$x1 = -($radius - 10 ) * cos( deg2rad( $angle ) );
	$y1 = -($radius - 10 ) * sin( deg2rad( $angle ) );
	$x2 = -( $radius - $inner_diameter_offset + 1 ) * cos( deg2rad( $angle ) );
	$y2 = -( $radius - $inner_diameter_offset + 1 ) * sin( deg2rad( $angle ) );
	zpcd_draw_thick_line($im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $angles_arrow_color);

	//draw the arrow for the Ascendant
	$dist_asc_first_house = ( $angle < 0 ) ? ( $angle + 360 ) : $angle;
	$valuea = 90 - $dist_asc_first_house;
	$angle1 = 65 - $valuea;
	$angle2 = 65 + $valuea;
	$x2 = $x1 + (20 * cos(deg2rad($angle1)));
	$y2 = $y1 + (20 * sin(deg2rad($angle1)));
	zpcd_draw_thick_line( $im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $angles_arrow_color );
	$x2 = $x1 - (20 * cos(deg2rad($angle2)));
	$y2 = $y1 + (20 * sin(deg2rad($angle2)));
	zpcd_draw_thick_line( $im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $angles_arrow_color );

	// draw the thick line for the Descendant
	$x1 = -( $radius - $inner_diameter_offset + 1 ) * cos( deg2rad( $angle - 180 ) );
	$y1 = -( $radius - $inner_diameter_offset + 1 ) * sin( deg2rad( $angle - 180 ) );

	$x2 = -( $radius - 2 ) * cos( deg2rad( $angle - 180 ) );
	$y2 = -( $radius - 2 ) * sin( deg2rad( $angle - 180 ) );

	zpcd_draw_thick_line( $im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $angles_arrow_color );

	// ------------------------------------------

	// draw the thick line for the MC
	$angle = $first_house - $longitudes[16];
	$dist_mc_first_house = $angle;
	if ( $dist_mc_first_house < 0 ) {
		$dist_mc_first_house = $dist_mc_first_house + 360;
	}
	$valueb = 90 - $dist_mc_first_house;
	$angle1 = 65 - $valueb;
	$angle2 = 65 + $valueb;
	$x1 = -($radius - $inner_diameter_offset + 1) * cos(deg2rad($angle));
	$y1 = -($radius - $inner_diameter_offset + 1) * sin(deg2rad($angle));
	$x2 = -($radius - 10) * cos(deg2rad($angle));
	$y2 = -($radius - 10) * sin(deg2rad($angle));
	zpcd_draw_thick_line($im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $angles_arrow_color);

	// draw the arrow for the MC
	$x1 = $x2 + (20 * cos(deg2rad($angle1)));
	$y1 = $y2 + (20 * sin(deg2rad($angle1)));
	zpcd_draw_thick_line($im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $angles_arrow_color);
	$x1 = $x2 - (20 * cos(deg2rad($angle2)));
	$y1 = $y2 + (20 * sin(deg2rad($angle2)));
	zpcd_draw_thick_line($im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $angles_arrow_color);
}

// ------------------------------------------

// draw the spokes of the wheel
$spoke_length = 9;
$minor_spoke_length = 4;
$cnt = 0;
for ($i = $offset_from_start_of_sign; $i <= $offset_from_start_of_sign + 359; $i = $i + 1) {
	$x1 = -( $radius - 0.5 ) * cos(deg2rad($i));
	$y1 = -( $radius - 0.5 ) * sin(deg2rad($i));

	if ($cnt % 5 == 0) {
		$x2 = -($radius - $spoke_length) * cos(deg2rad($i));
		$y2 = -($radius - $spoke_length) * sin(deg2rad($i));
	} else {
		$x2 = -($radius - $minor_spoke_length) * cos(deg2rad($i));
		$y2 = -($radius - $minor_spoke_length) * sin(deg2rad($i));
	}

	$cnt = $cnt + 1;
	zpcd_image_smooth_line( $im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $houses_divider_color );
}

// ------------------------------------------

// put signs around chartwheel
$cw_sign_glyph = 14;
$ch_sign_glyph = 12;
$gap_sign_glyph = -20;

for ( $i = 1; $i <= 12; $i++ ) {
	$angle_to_use = deg2rad((($i - 1) * 30) + 15 - $first_house);

	$center_pos_x = -$cw_sign_glyph / 2;
	$center_pos_y = $ch_sign_glyph / 2;

	$offset_pos_x = $center_pos_x * cos($angle_to_use);
	$offset_pos_y = $center_pos_y * sin($angle_to_use);

	$x1 = $center_pos_x + $offset_pos_x + ((-$radius + $gap_sign_glyph) * cos($angle_to_use));
	$y1 = $center_pos_y + $offset_pos_y + (($radius - $gap_sign_glyph) * sin($angle_to_use));

	if ($i == 1 || $i == 5 || $i == 9) {
		$clr_to_use = $fire_sign_color;
	} elseif ($i == 2 || $i == 6 || $i == 10) {
		$clr_to_use = $earth_sign_color;
	} elseif ($i == 3 || $i == 7 || $i == 11) {
		$clr_to_use = $air_sign_color;
	} elseif ($i == 4 || $i == 8 || $i == 12) {
		$clr_to_use = $water_sign_color;
	}

	zpcd_draw_bold_text($im, 16, 0, $x1 + $center_pt, $y1 + $center_pt, $clr_to_use, $dir . 'HamburgSymbols.ttf', chr( $sign_glyph[ $i ] ), 1 );

}

// ------------------------------------------

// put planets in chartwheel
	
// sort longitudes in descending order from 360 down to 0
zpcd_sort_planets_by_descending_longitude( $num_planets, $longitudes, $sort, $sort_pos );

$flag = false;

for ($i = $num_planets - 1; $i >= 0; $i--) {
	// $sort holds longitudes in descending order from 360 down to 0
	// $sort_pos holds the planet number corresponding to that longitude
	$angle_to_use = deg2rad($sort[ $i ] - $first_house);



	// Asc and MC go outside the circle
	if ( in_array( $sort_pos[ $i ], array( 15, 16 ) ) ) {
		$glyph_radius = $radius_outer;
		$dms_radius = $radius_outer;
		$degree_color = $angle_degree_color;
	} else {

		$degree_color = $bright_blue; // @todo this will be a setting in customizer

		// draw line from planet to circumference
		if ( false == $flag ) {
			$glyph_radius = $radius - $dist_from_diameter_i;
			$dms_radius = $radius - $dist_from_diameter_dms_i;
			$x1 = (-$radius + $dist_from_diameter_line_i) * cos($angle_to_use);
			$y1 = ($radius - $dist_from_diameter_line_i) * sin($angle_to_use);
			$x2 = (-$radius + 6) * cos($angle_to_use);
			$y2 = ($radius - 6) * sin($angle_to_use);
		} else {
			$glyph_radius = $radius - $dist_from_diameter_o;
			$dms_radius = $radius - $dist_from_diameter_dms_o;
			$x1 = (-$radius + $dist_from_diameter_line_o) * cos($angle_to_use);
			$y1 = ($radius - $dist_from_diameter_line_o) * sin($angle_to_use);
			$x2 = (-$radius + 6) * cos($angle_to_use);
			$y2 = ($radius - 6) * sin($angle_to_use);
		}
		zpcd_image_smooth_line( $im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $degree_color );
		$flag = ! $flag;
	}

	zpcd_planet_glyph_coordinates( $angle_to_use, $glyph_radius, $i, $sort_pos[ $i ], $xy, $sort );

	// Use text instead of glyph for Vertex
	if ( 14 == $sort_pos[ $i ] ) {
		imagestring( $im, 5, $xy[0] + $center_pt, $xy[1] + $center_pt - 12, 'Vx', $planet_glyph_color );// Subtract from the y because imagestring y = the top left rather than lower left point
	} else {

		// Draw planets, but not Asc or MC if time is unknown
		if ( ! ( ! empty( $unknown_time ) && in_array( $sort_pos[ $i ], array( 15, 16 ) ) ) ) {
			imagettftext( $im, 16, 0, $xy[0] + $center_pt, $xy[1] + $center_pt, $planet_glyph_color, $dir . 'HamburgSymbols.ttf', chr( $pl_glyph[ $sort_pos[ $i ] ] ) );
		}
	}

	// Display degrees
	// Use this again to get coordinates to display the degrees
	zpcd_planet_glyph_coordinates( $angle_to_use, $dms_radius, $i, $sort_pos[ $i ], $xy_dms, $sort, 9 );
	$sign_num = floor( $longitudes[ $sort_pos[ $i ] ] / 30 );
	$pos_in_sign = $longitudes[ $sort_pos[ $i ] ] - ( $sign_num * 30 );
	$degrees = floor( $pos_in_sign );

	// Check for retrograde, but not for POF, Vertex, Asc, or MC
	if ( ! in_array( $sort_pos[ $i ], array( 13, 14, 15, 16 ) ) && $speed[ $sort_pos[ $i ] ] < 0 ) {
		$degrees = "$degrees >";
	}

	// Display degrees, but not for Asc or MC if time is unknown
	if ( ! ( ! empty( $unknown_time ) && in_array( $sort_pos[ $i ], array( 15, 16 ) ) ) ) {
		imagettftext( $im, 8, 0, $xy_dms[0] + $center_pt, $xy_dms[1] + $center_pt, $degree_color, $dir . 'HamburgSymbols.ttf', $degrees );
	}

}

// ------------------------------------------

// draw in the aspect lines
	for ($i = 0; $i <= $last_planet_num; $i++)
	{
		for ($j = 0; $j <= $last_planet_num; $j++)
		{
			$q = 0;
			$da = Abs( $longitudes[ $sort_pos[ $i ] ] - $longitudes[ $sort_pos[ $j ] ] );

			if ($da > 180)
			{
				$da = 360 - $da;
			}

			// set orb - 8 if Sun or Moon, 6 if not Sun or Moon
			if ($sort_pos[ $i ] == 0 || $sort_pos[ $i ] == 1 || $sort_pos[$j] == 0 || $sort_pos[$j] == 1)
			{
				$orb = 8;
			}
			else
			{
				$orb = 6;
			}

			// is there an aspect within orb?
			if ($da <= $orb)
			{
				$q = 1;
			}
			elseif (($da <= (60 + $orb)) And ($da >= (60 - $orb)))
			{
				$q = 6;
			}
			elseif (($da <= (90 + $orb)) And ($da >= (90 - $orb)))
			{
				$q = 4;
			}
			elseif (($da <= (120 + $orb)) And ($da >= (120 - $orb)))
			{
				$q = 3;
			}
			elseif (($da <= (150 + $orb)) And ($da >= (150 - $orb)))
			{
				$q = 5;
			}
			elseif ($da >= (180 - $orb))
			{
				$q = 2;
			}

			if ($q > 0)
			{
				if ($q == 1 || $q == 3 || $q == 6)
				{
					$aspect_color = $soft_aspect_color;
				}
				elseif ($q == 4 || $q == 2)
				{
					$aspect_color = $hard_aspect_color;
				}
				elseif ($q == 5)
				{
					$aspect_color = $minor_aspect_color;
				}

				if ($q != 1)
				{
					//non-conjunctions
					$x1 = (-$radius + $inner_diameter_offset) * cos(deg2rad($sort[ $i ] - $first_house));
					$y1 = ($radius - $inner_diameter_offset) * sin(deg2rad($sort[ $i ] - $first_house));
					$x2 = (-$radius + $inner_diameter_offset) * cos(deg2rad($sort[$j] - $first_house));
					$y2 = ($radius - $inner_diameter_offset) * sin(deg2rad($sort[$j] - $first_house));

					// dashed line for inconjunct
					if ($q == 5) {
						$style = array_merge(array_fill(0, 8, $aspect_color), array_fill(0, 8, $inner_wheel_color));
						imagesetstyle($im, $style);
						imageline($im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, IMG_COLOR_STYLED);	
					} else {
						zpcd_image_smooth_line($im, $x1 + $center_pt, $y1 + $center_pt, $x2 + $center_pt, $y2 + $center_pt, $aspect_color);	
					}

					
				}
			}

		}
	}

// If time is unknown, print note that this is a hypothetical birth time of noon
if ( ! empty( $unknown_time ) ) {

	$noon = isset( $i18n['time'] ) ? $i18n['time'] : 'Time: 12:00pm';
	$hypothetical = isset( $i18n['hypothetical'] ) ? $i18n['hypothetical'] : '';

	imagestring( $im, 2, 8, 16, $noon, $black );
	imagestring( $im, 2, 8, 32, $hypothetical, $black );
}

	// @todo remove unused...
	// imagestring( $im, 2, 8, 32, 'Tropical Zodiac', $black );
	// imagestring( $im, 2, 8, 48, 'Placidus', $black );

	
	// imagestring( $im, 2, 8, 8, $debug3, $black );// @test now


	imagepng( $im );
	imagedestroy( $im );

/************************************************************
*

@todo now get orb from settings for aspect lines to be drawn only if within orb.
Are settings even accessible in here??? must send them in the image src url

*
************************************************************/

/**
 * Strip all tags and whitespace
 */
function zpcd_sanitize_data( $data ) {
	$data = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $data );
	$data = strip_tags($data);
	$data = preg_replace('/[\r\n\t ]+/', ' ', $data);
 	return trim( $data );
}

function zpcd_sort_planets_by_descending_longitude($num_planets, $longitude, &$sort, &$sort_pos) {
// load all $longitude() into sort() and keep track of the planet numbers in $sort_pos()
	for ($i = 0; $i <= $num_planets - 1; $i++)
	{
		$sort[ $i ] = $longitude[ $i ];
		$sort_pos[ $i ] = $i;
	}

// do the actual sort
	for ($i = 0; $i <= $num_planets - 2; $i++)
	{
		for ($j = $i + 1; $j <= $num_planets - 1; $j++)
		{
			if ($sort[$j] > $sort[ $i ])
			{
				$temp = $sort[ $i ];
				$temp1 = $sort_pos[ $i ];

				$sort[ $i ] = $sort[$j];
				$sort_pos[ $i ] = $sort_pos[$j];

				$sort[$j] = $temp;
				$sort_pos[$j] = $temp1;
			}
		}
	}
}

/**
 * Check if 2 planet longitudes are too close together
 */
function zpcd_is_planet_too_close( $long_1, $long_2 ) {
	$distance = $long_1 - $long_2;

	if ( $distance < 0 ) {
		$distance += 360;
	}

	return (  $distance <= 3 ) ? true : false;

}

/**
 * Calculate the coordinates on the wheel where a planet glyph should be displayed
 */
function zpcd_planet_glyph_coordinates($angle_to_use, $radii, $i, $sort_pos, &$xy, $sorted_longitudes = false, $glyph_height = 16) {
	$glyph_width = 16;
	$gap_pl_glyph = -10;
	// take into account the width and height of the glyph, defined below
	// get distance we need to shift the glyph so that the absolute middle of the glyph is the start point
	$center_pos_x = -$glyph_width / 2;
	$center_pos_y = $glyph_height / 2;
	// get the offset we have to move the center point to in order to be properly placed
	$offset_pos_x = $center_pos_x * cos( $angle_to_use );
	$offset_pos_y = $center_pos_y * sin( $angle_to_use );

	// If longitude is within 3 degrees of previous, move it over
	// iF there is a previous one to compare it to
	if ( isset( $sorted_longitudes[ $i + 1 ] ) ) {
		if ( zpcd_is_planet_too_close( $sorted_longitudes[ $i ], $sorted_longitudes[ $i + 1 ] ) ) {

			// Yes, too close
			$angle_to_use += deg2rad(3);
			
			// Was the previous also too close to its previous planet?
			if ( isset( $sorted_longitudes[ $i + 2 ] ) ) {
				if ( zpcd_is_planet_too_close( $sorted_longitudes[ $i + 1 ], $sorted_longitudes[ $i + 2 ] ) ) {

					// Yes, too close
					$angle_to_use += deg2rad(6);
			
					// was the previous-previous also too close to its previous planet?
					if ( isset( $sorted_longitudes[ $i + 3 ] ) ) {
						if ( zpcd_is_planet_too_close( $sorted_longitudes[ $i + 2 ], $sorted_longitudes[ $i + 3 ] ) ) {
							$angle_to_use += deg2rad(9);
						}
					}
				}
			}
		}
	}

	// For Asc and MC, display the degrees off to the side a bit
	if ( 9 == $glyph_height ) { // Make sure we're dealing with degrees, not glyphs
		// position the Asc degrees
		if ( 15 == $sort_pos ) {
			$radii -= 5;
			$angle_to_use -= deg2rad(3);
		}
		// position the MC degrees
		if ( 16 == $sort_pos ) {
			$radii -= 2;
			$angle_to_use -= deg2rad(4);
		}		
	}

	// now get the final X, Y coordinates
	$xy[0] = $center_pos_x + $offset_pos_x + ((-$radii + $gap_pl_glyph) * cos($angle_to_use));
	$xy[1] = $center_pos_y + $offset_pos_y + (($radii - $gap_pl_glyph) * sin($angle_to_use));
	return ($xy);
}

function zpcd_house_number_coordinates( $num, $angle, $radii, &$xy ) {
	$char_width = ($num < 10) ? 10 : 16;
	$half_char_width = $char_width / 2;
	$char_height = 12;
	$half_char_height = $char_height / 2;

	//puts center of character right on circumference of circle
	$xpos0 = -$half_char_width;
	$ypos0 = $char_height;

	if ($num == 1) {
		$x_adj = -cos(deg2rad($angle)) * $char_width;
		$y_adj = sin(deg2rad($angle)) * $char_height;
	}
	elseif ($num == 2) {
		$x_adj = -cos(deg2rad($angle)) * $half_char_width;
		$y_adj = sin(deg2rad($angle)) * $char_height;
	}
	elseif ($num == 3) {
		$xpos0 = $half_char_width;
		$x_adj = -cos(deg2rad($angle)) * $half_char_width;
		$y_adj = sin(deg2rad($angle)) * $half_char_height;
	}
	elseif ($num == 4) {
		$xpos0 = $char_width;
		$x_adj = -cos(deg2rad($angle)) * $half_char_width;
		$y_adj = sin(deg2rad($angle)) * $half_char_height;
	}
	elseif ($num == 5) {
		$xpos0 = $char_width;
		$x_adj = -cos(deg2rad($angle)) * $half_char_width;
		$ypos0 = $half_char_height;
		$y_adj = sin(deg2rad($angle)) * $half_char_height;
	}
	elseif ($num == 6) {
		$xpos0 = $char_width;
		$x_adj = -cos(deg2rad($angle)) * $half_char_width;
		$ypos0 = -$half_char_height;
		$y_adj = sin(deg2rad($angle)) * $half_char_height;
	}
	elseif ($num == 7) {
		$x_adj = -cos(deg2rad($angle)) * $char_width;
		$ypos0 = -$half_char_height;
		$y_adj = -sin(deg2rad($angle)) * $half_char_height;
	}
	elseif ($num == 8) {
		$x_adj = -cos(deg2rad($angle)) * $char_width;
		$ypos0 = -$half_char_height;
		$y_adj = sin(deg2rad($angle)) * $half_char_height;
	}
	elseif ($num == 9) {
		$xpos0 = -$char_width;
		$x_adj = -cos(deg2rad($angle)) * $char_width;
		$ypos0 = -$half_char_height;
		$y_adj = sin(deg2rad($angle)) * $half_char_height;
	}
	elseif ($num == 10) {
		$xpos0 = -$char_width;
		$x_adj = -cos(deg2rad($angle)) * $char_width;
		$ypos0 = $half_char_height;
		$y_adj = sin(deg2rad($angle)) * $char_height;
	}
	elseif ($num == 11) {
		$xpos0 = -$char_width;
		$x_adj = -cos(deg2rad($angle)) * $char_width;
		$y_adj = sin(deg2rad($angle)) * $char_height;
	}
	elseif ($num == 12) {
		$x_adj = -cos(deg2rad($angle)) * $char_width;
		$y_adj = sin(deg2rad($angle)) * $half_char_height;
	}

	$xy[0] = $xpos0 + $x_adj - ($radii * cos(deg2rad($angle)));
	$xy[1] = $ypos0 + $y_adj + ( $radii * sin( deg2rad( $angle ) ) );
	return ($xy);
}
function zpcd_draw_bold_text($image, $size, $angle, $x_cord, $y_cord, $clr_to_use, $fontfile, $text, $boldness) {
	$_x = array(1, 0, 1, 0, -1, -1, 1, 0, -1);
	$_y = array(0, -1, -1, 0, 0, -1, 1, 1, 1);

	for ( $n = 0; $n <= $boldness; $n++ ) {
		ImageTTFText( $image, $size, $angle, $x_cord+$_x[$n], $y_cord+$_y[$n], $clr_to_use, $fontfile, $text );
	}
}
function zpcd_draw_thick_line( $image, $x1, $y1, $x2, $y2, $color, $boldness = 3 ) {
	$center = round($boldness/2);
	for($i=0;$i<$boldness;$i++) {
		$a = $center-$i;
		if($a<0){
			$a -= $a;
		}
		for($j=0;$j<$boldness;$j++) {
			$b = $center-$j;
			if($b<0){$b -= $b;}
			$c = sqrt($a*$a + $b*$b);
			if($c<=$boldness) {
				zpcd_image_smooth_line($image, $x1 +$i, $y1+$j, $x2 +$i, $y2+$j, $color);
			}
		}
	}
} 
function zpcd_image_smooth_line( $image, $x1, $y1, $x2, $y2, $color ) {
	$colors = imagecolorsforindex ( $image , $color );
	if ( $x1 == $x2 ) {
	imageline ( $image , $x1 , $y1 , $x2 , $y2 , $color ); // Vertical line
	} else {
	$m = ( $y2 - $y1 ) / ( $x2 - $x1 );
	$b = $y1 - $m * $x1;
	if ( abs ( $m ) <= 1 ) {
	$x = min ( $x1 , $x2 );
	$endx = max ( $x1 , $x2 );
	while ( $x <= $endx ) {
	$y = $m * $x + $b;
	$y == floor ( $y ) ? $ya = 1 : $ya = $y - floor ( $y );
	$yb = ceil ( $y ) - $y;
	$tempcolors = imagecolorsforindex ( $image , imagecolorat ( $image , $x , floor ( $y ) ) );
	$tempcolors['red'] = $tempcolors['red'] * $ya + $colors['red'] * $yb;
	$tempcolors['green'] = $tempcolors['green'] * $ya + $colors['green'] * $yb;
	$tempcolors['blue'] = $tempcolors['blue'] * $ya + $colors['blue'] * $yb;
	if ( imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) == -1 ) imagecolorallocate ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] );
	imagesetpixel ( $image , $x , floor ( $y ) , imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) );
	$tempcolors = imagecolorsforindex ( $image , imagecolorat ( $image , $x , ceil ( $y ) ) );
	$tempcolors['red'] = $tempcolors['red'] * $yb + $colors['red'] * $ya;
	$tempcolors['green'] = $tempcolors['green'] * $yb + $colors['green'] * $ya;
	$tempcolors['blue'] = $tempcolors['blue'] * $yb + $colors['blue'] * $ya;
	if ( imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) == -1 ) imagecolorallocate ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] );
	imagesetpixel ( $image , $x , ceil ( $y ) , imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) );
	$x ++;
	}
	} else {
	$y = min ( $y1 , $y2 );
	$endy = max ( $y1 , $y2 );
	while ( $y <= $endy ) {
	$x = ( $y - $b ) / $m;
	$x == floor ( $x ) ? $xa = 1 : $xa = $x - floor ( $x );
	$xb = ceil ( $x ) - $x;
	$tempcolors = imagecolorsforindex ( $image , imagecolorat ( $image , floor ( $x ) , $y ) );
	$tempcolors['red'] = $tempcolors['red'] * $xa + $colors['red'] * $xb;
	$tempcolors['green'] = $tempcolors['green'] * $xa + $colors['green'] * $xb;
	$tempcolors['blue'] = $tempcolors['blue'] * $xa + $colors['blue'] * $xb;
	if ( imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) == -1 ) imagecolorallocate ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] );
	imagesetpixel ( $image , floor ( $x ) , $y , imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) );
	$tempcolors = imagecolorsforindex ( $image , imagecolorat ( $image , ceil ( $x ) , $y ) );
	$tempcolors['red'] = $tempcolors['red'] * $xb + $colors['red'] * $xa;
	$tempcolors['green'] = $tempcolors['green'] * $xb + $colors['green'] * $xa;
	$tempcolors['blue'] = $tempcolors['blue'] * $xb + $colors['blue'] * $xa;
	if ( imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) == -1 ) imagecolorallocate ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] );
	imagesetpixel ( $image , ceil ( $x ) , $y , imagecolorexact ( $image , $tempcolors['red'] , $tempcolors['green'] , $tempcolors['blue'] ) );
	$y ++;
	}
	}
	}
}
function zpcd_setpixel4($img, $cx, $cy, $deltax, $deltay, $color) {
	imagesetpixel($img, $cx + $deltax, $cy + $deltay, $color);
	imagesetpixel($img, $cx - $deltax, $cy + $deltay, $color);
	imagesetpixel($img, $cx + $deltax, $cy - $deltay, $color);
	imagesetpixel($img, $cx - $deltax, $cy - $deltay, $color);
}
/**
 * Ellipse with antialiasing - based on Wu's algorithm
 */
function zpcd_antialiased_ellipse( $img, $cx, $cy, $radius_x, $radius_y, $color ) {
	$radius_x2 = $radius_x * $radius_x;
	$radius_y2 = $radius_y * $radius_y;
	static $max_transparency = 0x7F; // 127
	// upper and lower halves
	$quarter = round($radius_x2 / sqrt($radius_x2 + $radius_y2));
	for ($x = 0; $x <= $quarter; $x++) {
		$y = $radius_y * sqrt(1-$x*$x/$radius_x2);
		$error = $y - floor($y);
		$transparency = round($error * $max_transparency);
		$alpha  = $color | ($transparency << 24);
		$alpha2 = $color | (($max_transparency - $transparency) << 24);
		zpcd_setpixel4($img, $cx, $cy, $x, floor($y),   $alpha);
		zpcd_setpixel4($img, $cx, $cy, $x, floor($y)+1, $alpha2);
	}
	// right and left halves
	$quarter = round($radius_y2 / sqrt($radius_x2 + $radius_y2));
	for ($y = 0; $y <= $quarter; $y++) {
		$x = $radius_x * sqrt(1-$y*$y/$radius_y2);
		$error = $x - floor($x);
		$transparency = round($error * $max_transparency);
		$alpha  = $color | ($transparency << 24);
		$alpha2 = $color | (($max_transparency - $transparency) << 24);
		zpcd_setpixel4($img, $cx, $cy, floor($x),   $y, $alpha);
		zpcd_setpixel4($img, $cx, $cy, floor($x)+1, $y, $alpha2);
	}
}
function zpcd_hex2rgb( $hex ) {
	$hex = ltrim( $hex, '#');
	if(strlen($hex) == 3) {
		$r = hexdec(substr($hex,0,1).substr($hex,0,1));
		$g = hexdec(substr($hex,1,1).substr($hex,1,1));
		$b = hexdec(substr($hex,2,1).substr($hex,2,1));
	} else {
		$r = hexdec(substr($hex,0,2));
		$g = hexdec(substr($hex,2,2));
		$b = hexdec(substr($hex,4,2));
	}
	return array($r, $g, $b); // returns an array with the rgb values
}
?>