<?php
/*---------------------------------------------------------------------------------
*	Ultimate Portal
*	Version 0.3
*	Project manager: vicram10
*	Copyright 2011
*	Powered by SMFSimple.com
**********************************************************************************/
require('../SSI.php');
global $db_prefix, $context, $scripturl, $txt, $settings, $smcFunc;
global $user_info, $ultimateportalSettings, $boarddir,$boardurl;

if (empty($_GET['url']))
	return;
	
$image = $smcFunc['db_escape_string']($_GET['url']);
//Extract Filename and Extension
$image_path = parse_url($image);
$img_parts = pathinfo($image_path['path']); 
$filename = $img_parts['filename'];
$img_ext = $img_parts['extension'];
//End Extract
//Image extension is valid?
$ext_valid = false;
//Image extension? any image extension (jpg, png, gif, bmp), convert to JPG 
$newwidth = 324; //new width - nuevo ancho 
$newheight = 465; //new height - nuevo alto				
if ($img_ext == 'gif')
{
	$ext_valid = true;
	$i=imagecreatefromgif($image);
	//resize the image
	$width = imagesx($i); //original width - ancho original
	$height = imagesy($i); //original height - alto original
	$im_destiny = imagecreatetruecolor($newwidth, $newheight);
	//Now Resize - Library GD RULES :P
	imagecopyresampled($im_destiny, $i, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);	
}
if ($img_ext == 'jpg' || $img_ext == 'jpeg')
{
	$ext_valid = true;		
	$i=imagecreatefromjpeg($image);
	//resize the image
	$width = imagesx($i); //original width - ancho original
	$height = imagesy($i); //original height - alto original
	$im_destiny = imagecreatetruecolor($newwidth, $newheight);
	//Now Resize - Library GD RULES :P
	imagecopyresampled($im_destiny, $i, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);		
}	
if ($img_ext == 'png')
{
	$ext_valid = true;		
	$i=imagecreatefrompng($image);
	//resize the image
	$width = imagesx($i); //original width - ancho original
	$height = imagesy($i); //original height - alto original
	$im_destiny = imagecreatetruecolor($newwidth, $newheight);
	//Now Resize - Library GD RULES :P
	imagecopyresampled($im_destiny, $i, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);		
}
//Extension valid?
if ($ext_valid === false)
	fatal_lang_error('ultport_no_extension_image',false);
if ($ultimateportalSettings['user_posts_cover_view'] == 'advanced')
{
	//image copy in BOX DVD image
	$box = imagecreatefrompng($settings['default_theme_url'] . '/images/ultimate-portal/up-box.png');
	imagecopymerge($box, $im_destiny, 114/*horizontal position*/, 17/*vertical position*/, 0, 0, $newwidth, $newheight, 75);		
	//Watermark?
	if (!empty($ultimateportalSettings['ultimate_portal_cover_watermark']))
	{
		// create colors - default is "black"
		$color = imagecolorallocate($box, 0, 0, 0);
		//WaterMark?
		$watermark = $ultimateportalSettings['ultimate_portal_cover_watermark'];
		//Fonts TTF
		$fonts = $boarddir . '/Themes/default/fonts/Forgottb.ttf';
		//Write text
		imagettftext($box, 12/*size letter*/, 90/*sense*/, 100 /*horizontal direction*/ , 480 /*vertical direction*/ , $color, $fonts, $watermark);
	}
	//PNG Transparency
	imagealphablending($box, true);
	imagesavealpha($box, true);			
	//Ok, Now view the Cover
	header('content-type: image/png');		
	imagepng($box);	
	imagedestroy($i);
	imagedestroy($box);
}
if ($ultimateportalSettings['user_posts_cover_view'] == 'normal')
{
	//PNG Transparency
	imagealphablending($im_destiny, true);
	imagesavealpha($im_destiny, true);			
	//Ok, Now view the Cover
	header('content-type: image/png');		
	imagepng($im_destiny);							
	imagedestroy($i);
}
?>