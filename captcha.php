<?php
// Remove session_start() from here since it's already started in faculty.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create an image
$image = imagecreatetruecolor(120, 40);

// Colors
$background = imagecolorallocate($image, 10, 58, 32); // Dark green background
$text_color = imagecolorallocate($image, 255, 255, 255); // White text

// Fill background
imagefilledrectangle($image, 0, 0, 120, 40, $background);

// Generate random string
$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$captcha_string = '';
for ($i = 0; $i < 6; $i++) {
    $captcha_string .= $characters[rand(0, strlen($characters) - 1)];
}

// Store the string in session
$_SESSION['captcha'] = $captcha_string;

// Add some lines for noise
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0,120), rand(0,40), rand(0,120), rand(0,40), $text_color);
}

// Add dots for noise
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($image, rand(0,120), rand(0,40), $text_color);
}

// Try to use TTF font if available
if (function_exists('imagettftext') && file_exists('./arial.ttf')) {
    imagettftext($image, 20, rand(-10, 10), 15, 30, $text_color, './arial.ttf', $captcha_string);
} else {
    // Fallback to basic text
    imagestring($image, 5, 20, 10, $captcha_string, $text_color);
}

// Prevent caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-type: image/png');

// Output the image
imagepng($image);
imagedestroy($image);
?>