<?php

/*
  This file is part of ADIOS Framework.

  This file is published under the terms of the license described
  in the license.md file which is located in the root folder of
  ADIOS Framework package.
*/

namespace ADIOS\Core;

/**
 * Various handy helper functions
 */
class HelperFunctions {
  var $loadUrlError = '';
  
  /**
   * Minifies HTML
   *
   * @param  string $html Input HTML
   * @return string Minified HTML
   */
  public static function minifyHtml($html) {
    $search = [
      '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
      '/[^\S ]+\</s',     // strip whitespaces before tags, except space
      '/(\s)+/s',         // shorten multiple whitespace sequences
      '/<!--(.|\s)*?-->/' // Remove HTML comments
    ];

    $replace = ['>', '<', '\\1', ''];

    return preg_replace($search, $replace, $html);
  }
  
  /**
   * Load content of remote URL using PHP's CURL library.
   *
   * @param  string $url URL to be loaded
   * @param  array $post Array of POST values to be posted to the request
   * @return string Loaded content of remote URL
   */
  public static function loadUrl($url, $post = []) {

    self::$loadUrlError = '';

    if (is_callable('curl_init')) {
      $ch = curl_init();

      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POST, count($post));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_BUFFERSIZE, 1024 * 1024 * 1024 * 10);
      curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_MAX_TLSv1_3);
      curl_setopt($ch, CURLOPT_TIMEOUT, 1000);

      $html = curl_exec($ch);
      self::$loadUrlError = curl_error($ch);

      curl_close($ch);
    } else {
      $error = 'CURL is not available';
    }

    return '' == $error ? $html : false;
  }
  
  /**
   * Removes special characters from string
   *
   * @param  string $string Original string
   * @return string String with removed special characters
   */
  public static function rmspecialchars($string) {
    $from = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '{', '}', '[', ']', ':', '|', ';', "'", '\\', ',', '.', '/', '<', '>', '?'];
    foreach ($from as $char) {
      $string = str_replace($char, '', $string);
    }

    return $string;
  }

  /**
   * Removes punctuation characters from string
   *
   * @param  string $string Original string
   * @return string String with removed punctuation characters
   */
  public static function rmdiacritic($string) {
    $from = ['ŕ', 'ě', 'š', 'č', 'ř', 'š', 'ž', 'ť', 'ď', 'ľ', 'ĺ', 'ý', 'á', 'í', 'ä', 'é', 'ú', 'ü', 'ö', 'ô', 'ó', 'ň', 'Ě', 'Š', 'Č', 'Ř', 'Š', 'Ť', 'Ď', 'Ľ', 'Ĺ', 'Ž', 'Ý', 'Á', 'Í', 'É', 'Ú', 'Ü', 'Ó', 'Ó', 'Ň'];
    $to = ['r', 'e', 's', 'c', 'r', 's', 'z', 't', 'd', 'l', 'l', 'y', 'a', 'i', 'a', 'e', 'u', 'u', 'o', 'o', 'o', 'n', 'E', 'S', 'C', 'R', 'S', 'T', 'D', 'L', 'L', 'Z', 'Y', 'A', 'I', 'E', 'U', 'U', 'O', 'O', 'N'];

    return str_replace($from, $to, $string);
  }
  
  /**
   * Convert string with to URL-compatible string
   *
   * @param  string $string Original string
   * @param  bool $replaceSlashes If TRUE, slashes are replaced with hyphenation
   * @return string URL-compatible string
   */
  public static function str2url($string, $replaceSlashes = true) {
    if ($replaceSlashes) {
      $string = str_replace('/', '-', $string);
    }

    $string = preg_replace('/ |^(a-z0-9)/', '-', strtolower(self::rmspecialchars(self::rmdiacritic($string))));

    $string = preg_replace('/[^(\x20-\x7F)]*/', '', $string);
    $string = preg_replace('/[^(\-a-z0-9)]*/', '', $string);
    $string = trim($string, '-');

    while (strpos($string, '--')) {
      $string = str_replace('--', '-', $string);
    }

    return $string;
  }
  
  /**
   * Generates random password
   *
   * @return string Generated random password
   */
  public static function randomPassword() {
    $alphabet = [
      '0' => 'abcdefghijklmnopqrstuvwxyz',
      '1' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
      '2' => '1234567890',
      '3' => '_!*$^',
    ];

    $patterns = [
      '12231110130112',
      '1211132101122310',
      '1122312130113010',
      '131213011220',
    ];

    $pattern = $patterns[rand(0, count($patterns) - 1)];

    $pass = "";
    for ($i = 0; $i < strlen($pattern) - 1; $i++) {
      $charset = $alphabet[$pattern[$i]];
      $pass .= $charset[rand(0, strlen($charset) - 1)];
    }

    return $pass;
  }

}