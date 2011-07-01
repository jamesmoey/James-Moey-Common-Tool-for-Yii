<?php
class MiscHelper {
  public static function getUrlStatusCode($url) {
    $ch = curl_init(str_replace(" ", "%20", $url));
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, false);
    $data = curl_exec($ch);
    curl_close($ch);
    if ($data === false) {
      return 500;
    } else {
      preg_match("/HTTP\/1\.[1|0]\s(\d{3})/",$data,$matches);
      return (int) $matches[1];
    }
  }
}