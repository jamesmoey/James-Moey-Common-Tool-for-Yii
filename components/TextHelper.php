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

  /**
   * Convert Windows Encoding to UTF-8
   * @static
   * @param string|array $input
   */
  public static function encodeWindowEncodingToUtf8(&$input) {
    if (is_array($input)) {
      foreach ($input as $key=>&$value) {
        $input[$key] = self::encodeWindowEncodingToUtf8($value);
      }
    } else {
      $input = @iconv('windows-1256', 'UTF-8', $input);
    }
    return $input;
  }

  public static function decodeUtf8ToWindowEncoding(&$input) {
    if (is_array($input)) {
      foreach ($input as $key=>&$value) {
        $input[$key] = self::decodeUtf8ToWindowEncoding($value);
      }
    } else {
      $input = @iconv('UTF-8', 'windows-1256', $input);
    }
    return $input;
  }
}
