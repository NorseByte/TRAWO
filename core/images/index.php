<?php 
    // Set image header
	header("Content-Type: image/png");
	$sign = imagecreatefrompng("../an.png");

	// Transparent
	imagealphablending($sign, false);
	imagesavealpha($sign, true);
	
	// Display the image
	imagepng($sign);
    imagedestroy($sign);
?>