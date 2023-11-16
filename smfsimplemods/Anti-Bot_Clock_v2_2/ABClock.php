<?php
/********************************************************************************
* ABClock.php v2.0												*
* By Karl Benson & .LORD. (read changelog)								*
*********************************************************************************
This program is distributed in the hope that it is and will be useful, but
WITHOUT ANY WARRANTIES; without even any implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE.
********************************************************************************/
if (!defined('SMF'))
	die('Hacking attempt...');

function GenerateClock()
{
	global $settings, $modSettings;

	// Attempt to stop from dying... (not that it should)
	@set_time_limit(0);
	@ini_set('memory_limit', '112M');

	// Must have received the session variable
	if (empty($_SESSION['abclock']) || !is_array($_SESSION['abclock'])) {
		unset($_SESSION['abclock']);		// Unset as its unvalid
		header('HTTP/1.1 400 Bad Request');
		die();
	}

	##### 	Create the Clock	 #####

	// Rotation? Get our time *30 (less some shift hour hand depending on minutes)
	$set['hour'] = 360 - ($_SESSION['abclock']['hour'] * 30) - ($_SESSION['abclock']['minu'] * 2.5);
	$set['minute'] = 360 - ($_SESSION['abclock']['minu'] * 30);
	unset($_SESSION['abclock']['hour'], $_SESSION['abclock']['minu']);

	// Face selection
	$modSettings['abclock_f'] = empty($modSettings['abclock_f']) ? mt_rand(1,6) : $modSettings['abclock_f'];
	// Hands selection (Use same type for hour and minute hands)
	$modSettings['abclock_h'] = empty($modSettings['abclock_h']) ? mt_rand(1,5) : $modSettings['abclock_h'];
	// Special
	if (6 <= $modSettings['abclock_f']) $modSettings['abclock_h'] = $modSettings['abclock_f'] = 6;

	// -- Load The Face --
	$face = $settings['default_theme_dir'] . '/images/clocks/face' . $modSettings['abclock_f'] . '.png';
	$face = imagecreatefrompng($face);
	$face_size = array ('x' => imageSX($face), 'y' => imageSY($face));

	$hands = array('minute', 'hour');
	foreach ($hands as $value)
	{
		// -- Load The Hand --
		$hand = $settings['default_theme_dir'] . '/images/clocks/' . $value . $modSettings['abclock_h'] . '.png';
		$hand = imagecreatefrompng($hand);

		// Rotate Hand if necessary
		if(!empty($set[$value]))
			$hand = imagerotate($hand, $set[$value], -1);

		//Get the sizes of both pix
		$hand_size = array ('x' => imageSX($hand), 'y' => imageSY($hand));

		$dest = array ( 'x' => ($face_size['x'] - $hand_size['x']) >> 1,
						'y' => ($face_size['y'] - $hand_size['y']) >> 1);

		// Merge the face with hand
		imagecopy($face, $hand, $dest['x'], $dest['y'], 0, 0, $hand_size['x'], $hand_size['y']);
		imagedestroy($hand);
	}

	//Creating Noise
	$color = imagecolorallocate($face, 0, 0, 0); // negro
	imagesetthickness($face, 2);
	$mid = array ('x' => $face_size['x'] >> 1, 'y' => $face_size['y'] >> 1);

	for ($i = 1; $i <= $modSettings['abclock_n']; $i++)
		for ($x = 0; $x <= 1; $x++)
		{
			$radio = mt_rand(0.2 * $mid['x'], 0.6 * $mid['x']);
			$angle = deg2rad(mt_rand(0, 359));
			$tri = array (cos($angle), sin($angle));
			$coorx = array($mid['x']+($x ? 4 : $radio)*$tri[0], $mid['x']+($x ? $radio : 0.7*$mid['x'])*$tri[0]);
			$coory = array($mid['y']+($x ? 4 : $radio)*$tri[1], $mid['y']+($x ? $radio : 0.7*$mid['y'])*$tri[1]);
			imageline($face, (int)$coorx[0], (int)$coory[0], (int)$coorx[1], (int)$coory[1], $color);
		}

	// -- Load The Base --
	$modSettings['abclock_b'] = empty($modSettings['abclock_b']) || $modSettings['abclock_b'] > 5 ? mt_rand(1,5) : $modSettings['abclock_b'];
	$base = $settings['default_theme_dir'] . '/images/clocks/base' . $modSettings['abclock_b'] . '.png';
	$base = imagecreatefrompng($base);
	imageSaveAlpha($base, true);

	// Merge FACE & CRISTAL with BASE
	for ($i = 1; $i >= 0; $i--)
	{
		$modSettings['abclock_r'] = empty($modSettings['abclock_r'])
			? 0 : mt_rand(0, $modSettings['abclock_r'] << 1) - $modSettings['abclock_r'];

		// Rotate (Face/Cristal) if necessary
		if (($ajus = !empty($modSettings['abclock_r'])) || !$i) {
			$face = imagerotate($face, $modSettings['abclock_r'], -1);
			$face_size = array ('x' => imageSX($face), 'y' => imageSY($face));
		}

		// Merge the (Face/Cristal) with Base
		imagecopy($base, $face, 0, 0,
			$ajus + ($face_size['x'] - imageSX($base)) >> 1, $ajus + ($face_size['y'] - imageSY($base)) >> 1,
			$face_size['x'], $face_size['y']);
		imagedestroy($face);

		// -- Load The Cristal --	// Cute Effect & Extra Noise
		if ($i) {
			$modSettings['abclock_r'] = 30;
			$face = $settings['default_theme_dir'] . '/images/clocks/cristal.png';
			$face = imagecreatefrompng($face);
		}
	}

	// Random Special Effects (PHP5 only)
	if (!empty($modSettings['abclock_e']) && @version_compare(PHP_VERSION, '5.0.0') !== -1) {
		$effects = array(IMG_FILTER_NEGATE, IMG_FILTER_EDGEDETECT, IMG_FILTER_EMBOSS);
		imagefilter($base, $effects[mt_rand(0, 2)]);
	}

	// Headers - don't cache this
	header('Cache-Control: no-cache');
	header('Pragma: nocache');
	header('Content-type: image/png');

	// Send out the image
	imagepng($base);
	// Destroy the images
	imagedestroy($base);
	die();
}

?>