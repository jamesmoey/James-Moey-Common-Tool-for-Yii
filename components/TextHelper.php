<?php
class TextHelper 
{

  public static $randomTextBoundary = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789";

  public static function generateRandomText($length = 8) {
    $string = '';
    for ($i = 0; $i < $length; $i++) {
      $string .= self::$randomTextBoundary[rand(0,strlen(self::$randomTextBoundary)-1)];
    }
    return $string;
  }
}
