<?php
header("Content-type: image/JPG");
session_start();

function rand_captcha($len){

  $chars = "a0b1c2d3e4f5g6h7i8j9ABCDEFGHIJklmnopqrstuvwxyzKLMNOPQRSTUVWXYZ";
  $str = "";
  $size = strlen($chars);

  for($s1 = 0; $s1<$len;$s1++){
    $str .= $chars[rand(0, $size-1)];
  }

  return $str;
}

$captcha = rand_captcha(6);

$_SESSION['real_captcha'] = $captcha;

$arial = "../images/arial.ttf";
$image = imagecreatefromjpeg('../images/1.JPG');

$background = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
imagealphablending($img, false);
imagesavealpha($img, true);
$grey = imagecolorallocate($img, 127, 127, 127);
$transparent = imagecolorallocatealpha($img, 255, 255, 255, 127);
imagettftext($image,50, 0, 10, 60, $background, $arial, $captcha);

imagejpeg($image);
?>
