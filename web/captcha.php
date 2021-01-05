<?php
if (isset($_SERVER["HTTP_ORIGIN"]))
{
    header("Access-Control-Allow-Origin: ". $_SERVER["HTTP_ORIGIN"]);
    header("Access-Control-Allow-Credentials: true");
}
session_start();

$width = 128;
$height = 32;
$length = 4;
$size = min($width / $length, $height);

$image = imagecreatetruecolor($width, $height);
$bgcolor = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $bgcolor);

$text = "0123456789";
$font = realpath("captcha.ttf");

$captcha = "";
for ($i = 0; $i < $length; ++$i)
{
    $x = $i * $width / $length + rand(0, 100) / 100.0 * ($width / $length - $size);
    $y = $size + rand(0, 100) / 100.0 * ($height - $size);
    $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    $char = substr($text, rand(0, strlen($text) - 1), 1);
    imagettftext($image, $size, 0, $x, $y, $color, $font, $char);
    $captcha .= $char;
}
$_SESSION["captcha"] = strtolower($captcha);

imagepng($image);
?>
