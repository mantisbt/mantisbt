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
	 * @package MantisBT
	 * @copyright Copyright (C) 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
	 * @copyright Copyright (C) 2002 - 2012  MantisBT Team - mantisbt-dev@lists.sourceforge.net
	 * @author Marcello Scata' <marcelloscata at users.sourceforge.net> ITALY
	 * @link http://www.mantisbt.org
	 */
	 /**
	  * MantisBT Core API's
	  */
	require_once( 'core.php' );

	$f_public_key = gpc_get_int( 'public_key' );

	$t_key = utf8_strtolower( utf8_substr( md5( config_get( 'password_confirm_hash_magic_string' ) . $f_public_key ), 1, 5) );
	$t_system_font_folder = get_font_path();
	$t_font_per_captcha = config_get( 'font_per_captcha' );

	$t_captcha_init = array(
		'TTF_folder'     => $t_system_font_folder,
		'TTF_RANGE'      => array( $t_font_per_captcha )
	);

	$captcha = new masc_captcha( $t_captcha_init );
	$captcha->make_captcha( $t_key );

#
# The class below was derived from
# http://www.phpclasses.org/browse/package/1163.html
#
# *** 3.0 Author
# Pascal Rehfeldt
# Pascal@Pascal-Rehfeldt.com
#
# http://www.phpclasses.org/browse.html/author/102754.html
#
#
# *** 3.1 License
# GNU General Public License (Version 2, June 1991)
#
# This program is free software; you can redistribute
# it and/or modify it under the terms of the GNU
# General Public License as published by the Free
# Software Foundation; either version 2 of the License,
# or (at your option) any later version.
#
# This program is distributed in the hope that it will
# be useful, but WITHOUT ANY WARRANTY; without even the
# implied warranty of MERCHANTABILITY or FITNESS FOR A
# PARTICULAR PURPOSE. See the GNU General Public License
# for more details.
#

	class masc_captcha
	{
		var $TTF_folder;
		var $TTF_RANGE  = array('ARIAL.TTF');
		var $chars		= 5;
		var $minsize	= 15;
		var $maxsize	= 15;
		var $maxrotation = 30;
		var $noise		= FALSE;
		var $websafecolors = TRUE;
		var $debug = FALSE;

		var $lx;				// width of picture
		var $ly;				// height of picture
		var $jpegquality = 80;	// image quality
		var $noisefactor = 9;	// this will multiplyed with number of chars
		var $nb_noise;			// number of background-noise-characters
		var $TTF_file;			// holds the current selected TrueTypeFont
		var $gd_version;		// holds the Version Number of GD-Library
		var $r;
		var $g;
		var $b;


			function masc_captcha( $config )
			{
				// Test for GD-Library(-Version)
				$this->gd_version = get_gd_version();
				if($this->gd_version == 0) die("There is no GD-Library-Support enabled. The Captcha-Class cannot be used!");
				if($this->debug) echo "\n<br />-Captcha-Debug: The available GD-Library has major version ".$this->gd_version;

				// extracts config array
				if(is_array($config))
				{
					if($this->debug) echo "\n<br />-Captcha-Debug: Extracts Config-Array in unsecure-mode!";
					foreach($config as $k=>$v) $this->$k = $v;
				}

				// check vars for maxtry, secretposition and min-max-size
				if($this->minsize > $this->maxsize)
				{
					$temp = $this->minsize;
					$this->minsize = $this->maxsize;
					$this->maxsize = $temp;
					if($this->debug) echo "<br />-Captcha-Debug: Arrghh! What do you think I mean with min and max? Switch minsize with maxsize.";
				}

				// check TrueTypeFonts
				if(is_array($this->TTF_RANGE))
				{
					if($this->debug) echo "\n<br />-Captcha-Debug: Check given TrueType-Array! (".count($this->TTF_RANGE).")";
					$temp = array();
					foreach($this->TTF_RANGE as $k=>$v)
					{
						if(is_readable($this->TTF_folder.$v)) $temp[] = $v;
					}
					$this->TTF_RANGE = $temp;
					if($this->debug) echo "\n<br />-Captcha-Debug: Valid TrueType-files: (".count($this->TTF_RANGE).")";
					//if(count($this->TTF_RANGE) < 1) die('No Truetypefont available for the CaptchaClass.');
				}
				else
				{
					if($this->debug) echo "\n<br />-Captcha-Debug: Check given TrueType-File! (".$this->TTF_RANGE.")";
					if(!is_readable($this->TTF_folder.$this->TTF_RANGE)) die('No Truetypefont available for the CaptchaClass.');
				}

				// select first TrueTypeFont
				$this->change_TTF();
				if($this->debug) echo "\n<br />-Captcha-Debug: Set current TrueType-File: (".$this->TTF_file.")";

				// get number of noise-chars for background if is enabled
				$this->nb_noise = $this->noise ? ($this->chars * $this->noisefactor) : 0;
				if($this->debug) echo "\n<br />-Captcha-Debug: Set number of noise characters to: (".$this->nb_noise.")";

				// set dimension of image
				$this->lx = ($this->chars + 1) * (int)(($this->maxsize + $this->minsize) / 1.5);
				$this->ly = (int)(2.4 * $this->maxsize);
				if($this->debug) echo "\n<br />-Captcha-Debug: Set image dimension to: (".$this->lx." x ".$this->ly.")";
			}

			function make_captcha( $private_key )
			{
				if($this->debug) echo "\n<br />-Captcha-Debug: Generate private key: ($private_key)";

				// create Image and set the apropriate function depending on GD-Version & websafecolor-value
				if($this->gd_version >= 2 && !$this->websafecolors)
				{
					$func1 = 'imagecreatetruecolor';
					$func2 = 'imagecolorallocate';
				}
				else
				{
					$func1 = 'imageCreate';
					$func2 = 'imagecolorclosest';
				}
				$image = $func1($this->lx,$this->ly);
				if($this->debug) echo "\n<br />-Captcha-Debug: Generate ImageStream with: ($func1())";
				if($this->debug) echo "\n<br />-Captcha-Debug: For colordefinitions we use: ($func2())";

				// Set Backgroundcolor
				$this->random_color(224, 255);
				$back =  @imagecolorallocate($image, $this->r, $this->g, $this->b);
				@ImageFilledRectangle($image,0,0,$this->lx,$this->ly,$back);
				if($this->debug) echo "\n<br />-Captcha-Debug: We allocate one color for Background: (".$this->r."-".$this->g."-".$this->b.")";

				// allocates the 216 websafe color palette to the image
				if($this->gd_version < 2 || $this->websafecolors) $this->makeWebsafeColors($image);

				// fill with noise or grid
				if($this->nb_noise > 0)
				{
					// random characters in background with random position, angle, color
					if($this->debug) echo "\n<br />-Captcha-Debug: Fill background with noise: (".$this->nb_noise.")";
					for($i=0; $i < $this->nb_noise; $i++)
					{
						srand((double)microtime()*1000000);
						$size	= intval(rand((int)($this->minsize / 2.3), (int)($this->maxsize / 1.7)));
						srand((double)microtime()*1000000);
						$angle	= intval(rand(0, 360));
						srand((double)microtime()*1000000);
						$x		= intval(rand(0, $this->lx));
						srand((double)microtime()*1000000);
						$y		= intval(rand(0, (int)($this->ly - ($size / 5))));
						$this->random_color(160, 224);
						$color	= $func2($image, $this->r, $this->g, $this->b);
						srand((double)microtime()*1000000);
						$text	= chr(intval(rand(45,250)));
						if(count ($this->TTF_RANGE)>0){
							@ImageTTFText($image, $size, $angle, $x, $y, $color, $this->change_TTF(), $text);
						} else {
							imagestring($image,5,$x,$y,$text,$color);
						}
					}
				}
				else
				{
					// generate grid
					if($this->debug) echo "\n<br />-Captcha-Debug: Fill background with x-gridlines: (".(int)($this->lx / (int)($this->minsize / 1.5)).")";
					for($i=0; $i < $this->lx; $i += (int)($this->minsize / 1.5))
					{
						$this->random_color(160, 224);
						$color	= $func2($image, $this->r, $this->g, $this->b);
						@imageline($image, $i, 0, $i, $this->ly, $color);
					}
					if($this->debug) echo "\n<br />-Captcha-Debug: Fill background with y-gridlines: (".(int)($this->ly / (int)(($this->minsize / 1.8))).")";
					for($i=0 ; $i < $this->ly; $i += (int)($this->minsize / 1.8))
					{
						$this->random_color(160, 224);
						$color	= $func2($image, $this->r, $this->g, $this->b);
						@imageline($image, 0, $i, $this->lx, $i, $color);
					}
				}

				// generate Text
				if($this->debug) echo "\n<br />-Captcha-Debug: Fill forground with chars and shadows: (".$this->chars.")";
				for($i=0, $x = intval(rand($this->minsize,$this->maxsize)); $i < $this->chars; $i++)
				{
					$text	= utf8_strtoupper(substr($private_key, $i, 1));
					srand((double)microtime()*1000000);
					$angle	= intval(rand(($this->maxrotation * -1), $this->maxrotation));
					srand((double)microtime()*1000000);
					$size	= intval(rand($this->minsize, $this->maxsize));
					srand((double)microtime()*1000000);
					$y		= intval(rand((int)($size * 1.5), (int)($this->ly - ($size / 7))));
					$this->random_color(0, 127);
					$color	=  $func2($image, $this->r, $this->g, $this->b);
					$this->random_color(0, 127);
					$shadow = $func2($image, $this->r + 127, $this->g + 127, $this->b + 127);
					if(count($this->TTF_RANGE) > 0){
						@ImageTTFText($image, $size, $angle, $x + (int)($size / 15), $y, $shadow, $this->change_TTF(), $text);
						@ImageTTFText($image, $size, $angle, $x, $y - (int)($size / 15), $color, $this->TTF_file, $text);
					} else {
						$t_font = rand(3,5);
						imagestring($image,$t_font,$x + (int)($size / 15),$y-20,$text,$color);
						imagestring($image,$t_font,$x,$y - (int)($size / 15)-20,$text,$color);
					}
					$x += (int)($size + ($this->minsize / 5));
				}
				header('Content-type: image/jpeg');
				@ImageJPEG($image, '', $this->jpegquality);
				@ImageDestroy($image);
				if($this->debug) echo "\n<br />-Captcha-Debug: Destroy Imagestream.";
			}

			/** @private **/
			function makeWebsafeColors(&$image)
			{
				for($r = 0; $r <= 255; $r += 51)
				{
					for($g = 0; $g <= 255; $g += 51)
					{
						for($b = 0; $b <= 255; $b += 51)
						{
							$color = imagecolorallocate($image, $r, $g, $b);
							//$a[$color] = array('r'=>$r,'g'=>$g,'b'=>$b);
						}
					}
				}
				if($this->debug) echo "\n<br />-Captcha-Debug: Allocate 216 websafe colors to image: (".imagecolorstotal($image).")";
			}

			function random_color($min,$max)
			{
				srand((double)microtime() * 1000000);
				$this->r = intval(rand($min,$max));
				srand((double)microtime() * 1000000);
				$this->g = intval(rand($min,$max));
				srand((double)microtime() * 1000000);
				$this->b = intval(rand($min,$max));
			}

			function change_TTF()
			{
				if(count($this->TTF_RANGE) > 0){
					if(is_array($this->TTF_RANGE))
					{
						srand((float)microtime() * 10000000);
						$key = array_rand($this->TTF_RANGE);
						$this->TTF_file = $this->TTF_folder.$this->TTF_RANGE[$key];
					}
					else
					{
						$this->TTF_file = $this->TTF_folder.$this->TTF_RANGE;
					}
					return $this->TTF_file;
				}
			}

	} // END CLASS masc_captcha
