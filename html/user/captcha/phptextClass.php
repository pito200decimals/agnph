<?php
/*phptext class, version 1.0
created by www.w3schools.in (Gautam kumar)
April 26, 2014
*/

/* Somewhat modified by Cyn */
class phptextClass
{
	public function phpcaptcha($backgroundColor,$imgWidth,$imgHeight,$noiceLines=0,$noiceDots=0)
	{
		/* Settings */
		$text=$this->random();
		$font = './font/monofont.ttf';/* font */
		$fontSize = $imgHeight * 0.75;
		
		$im = imagecreatetruecolor($imgWidth, $imgHeight);				
        $colors = array();
        for ($i = 0; $i < 20*strlen($text); $i++) {
            $rgb = array(mt_rand(64, 191), mt_rand(64, 191), mt_rand(64, 191));
            $rgb[mt_rand(0, 2)] = mt_rand(0, 31);
            $rgb[mt_rand(0, 2)] = mt_rand(223, 255);
            if ($i >= strlen($text)) {
                $rgb = array(mt_rand(64, 191), mt_rand(64, 191), mt_rand(64, 191));
                //$rgb = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            }
            $colors[] = imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]);
        }
		$textColor = $colors[0];
		
		$backgroundColor = $this->hexToRGB($backgroundColor);
		$backgroundColor = imagecolorallocate($im, $backgroundColor['r'],$backgroundColor['g'],$backgroundColor['b']);
				
		/* generating lines randomly in background of image */
		if($noiceLines>0){
            for( $i=0; $i<$noiceLines; $i++ ) {				
                imageline($im,
                    mt_rand(0,$imgWidth), mt_rand(0,$imgHeight),
                    mt_rand(0,$imgWidth), mt_rand(0,$imgHeight),
                    $colors[mt_rand(strlen($text), sizeof($colors) - 1)]);
            }
        }
				
		if($noiceDots>0){/* generating the dots randomly in background */
            for( $i=0; $i<$noiceDots; $i++ ) {
                imagefilledellipse($im,
                    mt_rand(0,$imgWidth),mt_rand(0,$imgHeight),
                    mt_rand(1, $imgHeight/2), mt_rand(1, $imgHeight/2),
                    $colors[mt_rand(strlen($text), sizeof($colors) - 1)]);
            }
        }
		
		imagefill($im,0,0,$backgroundColor);	
		list($x, $y) = $this->ImageTTFCenter($im, $text, $font, $fontSize);
        for ($i = 0; $i < strlen($text); $i++) {
            $x = $imgWidth / (strlen($text) + 2) * (1 + $i + mt_rand(-10, 10) / 50.0);
            $y2 = $y + $imgHeight / 10 * (mt_rand(-100, 100)/100.0);
            imagettftext($im, $fontSize, 0, (int)$x, (int)$y2, $colors[$i], $font, substr($text, $i, 1));		
        }

		header('Content-Type: image/jpeg');/* defining the image type to be shown in browser widow */
		imagejpeg($im,NULL,90);/* Showing image */
		imagedestroy($im);/* Destroying image instance */
		if(isset($_SESSION)){
			$_SESSION['captcha_code'] = $text;/* set random text in session for captcha validation*/
		}
	}
	
	/*for random string*/
	protected function random($characters=6,$letters = '23456789bcdfghjkmnpqrstvwxyz'){
		$str='';
		for ($i=0; $i<$characters; $i++) { 
			$str .= substr($letters, mt_rand(0, strlen($letters)-1), 1);
		}
		return $str;
	}	
	
	/*function to convert hex value to rgb array*/
	protected function hexToRGB($colour)
	{
			if ( $colour[0] == '#' ) {
					$colour = substr( $colour, 1 );
			}
			if ( strlen( $colour ) == 6 ) {
					list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
			} elseif ( strlen( $colour ) == 3 ) {
					list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
			} else {
					return false;
			}
			$r = hexdec( $r );
			$g = hexdec( $g );
			$b = hexdec( $b );
			return array( 'r' => $r, 'g' => $g, 'b' => $b );
	}		
		
	/*function to get center position on image*/
	protected function ImageTTFCenter($image, $text, $font, $size, $angle = 8) 
	{
		$xi = imagesx($image);
		$yi = imagesy($image);
		$box = imagettfbbox($size, $angle, $font, $text);
		$xr = abs(max($box[2], $box[4]));
		$yr = abs(max($box[5], $box[7]));
		$x = intval(($xi - $xr) / 2);
		$y = intval(($yi + $yr) / 2);
		return array($x, $y);	
	}
}
?>
