<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 04-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Utils;

/**
 * StringUtils
 */
class StringUtils {

  /**
   * Figures out whether a string is binary or not
   * 
   * @param string $string
   * @return bool
   */
  public static function isBinary($string) {
    $string = str_replace(array("\n", "\r", "\t"), '', $string);
    return !ctype_print($string);
  }

  /**
   * Remove the byte order mark from a string if applicable
   * 
   * @param string $string
   * @return string
   */
  public static function removeBom($string) {
    if (substr($string, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
      $string = substr($string, 3);
    }
    return $string;
  }
  
  /**
   * Trim and remove the byte order mark from a string if applicable
   * 
   * @param string $string
   * @return string
   */
  public static function trim($string, $charList='') {
    if (substr($string, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
      $string = substr($string, 3);
    }
    return $charList ? trim($string, $charList) : trim($string);
  }

  /**
   * makes a teaser out of text by character count, stripping off all HTML
   *
   * @param string $string original text
   * @param int $charCount number of characters to extract
   * @param string $append the string to append, ' …' by default
   * @return string $string
   */
  public static function teaserByCharCount($text, $charCount = 150, $append = '&nbsp;…') {
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = self::avoidFrenchLineBreak($text);

    $append       = strip_tags($append);
    $append       = html_entity_decode($append, ENT_QUOTES, 'UTF-8');
    $appendStrlen = mb_strlen($append);
    if (mb_strlen($text) <= ($charCount + $appendStrlen)) {
      return $text;
    }

    // for comparison generate a string without special characters and cut it to the right length
    $plainText = str_replace(
      array(
      '?', '\'', '.', '/', '&', ')', '(', '[', ']', '_', ',', ':', '-', '!', '"', '`', '°', '%', '{', '}', '#', '’', ';', '!', '…', '€'
      ), 'x', $text);
    $plainText = self::removeSpecChars($plainText, ' ');
    if (strlen($plainText) >= $charCount + 5) {
      $plainText = substr($plainText, 0, $charCount + 5);
      $plainText = substr($plainText, 0, strrpos($plainText, ' '));
      $plainText = trim(rtrim($plainText, '-,.!?:;'));
    }

    $word_count = str_word_count($plainText);

    $realTextArr = preg_split('~[\s]+~', $text);
    $text        = implode(' ', array_slice($realTextArr, 0, $word_count));

    return nl2br(trim(rtrim($text, '-,.!?:;'))) . $append;
  }

  /**
   * In French ortography some marks are preceeded by a space which can lead to unwanted line breaks
   * 
   * @param string $string
   * @return string
   */
  public static function avoidFrenchLineBreak($string, $repl = '&nbsp;') {
    // first character in expression is thin space U+2009
    return preg_replace('~(\x{2009}| )([:!?;…€])~u', $repl . '$2', $string);
  }

  /**
   * This function removes all special characters from a string. They are replaced by $repl,
   * multiple $repl are replaced by just one, $repl is also trimmed from the beginning and the end
   * of the string.
   *
   * @param string $string the original text
   * @param string $repl the replacement, - by default
   * @param bool $lower return string in lower case, true by default
   * @return string $string the modified string
   */
  public static function removeSpecChars($string, $repl = '-', $lower = true) {
    $specChars = array(
      'Á'     => 'A', 'Â'     => 'A', 'Ã'     => 'A', 'Ä'     => 'Ae', 'Å'     => 'A', 'Æ'     => 'A', 'Ç'     => 'C',
      'È'     => 'E', 'É'     => 'E', 'Ê'     => 'E', 'Ë'     => 'E', 'Ì'     => 'I', 'Í'     => 'I', 'Î'     => 'I',
      'Ï'     => 'I', 'Ð'     => 'E', 'Ñ'     => 'N', 'Ò'     => 'O', 'Ó'     => 'O', 'Ô'     => 'O', 'Õ'     => 'O',
      'Ö'     => 'Oe', 'Ø'     => 'O', 'Ù'     => 'U', 'Ú'     => 'U', 'Û'     => 'U', 'Ü'     => 'Ue', 'Ý'     => 'Y',
      'Þ'     => 'T', 'ß'     => 'ss', 'à'     => 'a', 'á'     => 'a', 'â'     => 'a', 'ã'     => 'a', 'ä'     => 'ae',
      'å'     => 'a', 'æ'     => 'ae', 'ç'     => 'c', 'è'     => 'e', 'é'     => 'e', 'ê'     => 'e', 'ë'     => 'e',
      'ì'     => 'i', 'í'     => 'i', 'î'     => 'i', 'ï'     => 'i', 'ð'     => 'e', 'ñ'     => 'n', 'ò'     => 'o',
      'ó'     => 'o', 'ô'     => 'o', 'õ'     => 'o', 'ö'     => 'oe', 'ø'     => 'o', 'ù'     => 'u', 'ú'     => 'u',
      'û'     => 'u', 'ü'     => 'ue', 'ý'     => 'y', 'þ'     => 't', 'ÿ'     => 'y', '_'     => $repl
    );
    $string = strtr($string, $specChars);
    $string = trim(preg_replace('~\W+~u', $repl, $string), $repl);
    return $lower ? strtolower($string) : $string;
  }

  /**
   * Convert a string with spaces or underscores to camelCase
   * 
   * @param string $string
   * @return string
   */
  public static function camelize($string, $firstToUpper=false) {
    $string = 'x' . strtolower(trim($string));
    $string = ucwords(preg_replace('/[\s_]+/', ' ', $string));
    $string = substr(str_replace(' ', '', $string), 1);
    return $firstToUpper ? ucfirst($string) : $string;
  }

  /**
   * Returns the given camelCasedWord as an underscored_word.
   * This is borrowed from the CakePHP framework
   *
   * @param string $camelCasedWord Camel-cased word to be "underscorized"
   * @return string Underscore-syntaxed version of the $camelCasedWord
   */
  public static function underscorize($camelCasedWord) {
    return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
  }  
  
  /**
   * Cast string to the right format
   * 
   * @param string $value
   * @return string
   */
  public static function typecast($value) {
    switch (true) {
      case $value === 'true' || $value === 'TRUE':
        return true;

      case $value === 'false' || $value === 'FALSE':
        return false;

      case $value === 'null' || $value === 'NULL':
        return null;

      case is_numeric($value):
        return strpos($value, '.') ? (float) $value : (int) $value;
    }

    return $value;
  }
  
  /**
   * Quote a value
   * 
   * @param string $value
   * @param array $options
   * @return string
   */
  public static function quote($value, array $options=[]) {
    $options = array_merge(
      [
      'enclosure' => '"',
      'escape'    => '\\'
      ], $options
    );
    if (!is_numeric($value) && !is_bool($value)  && !is_null($value) && !in_array(strtolower($value), ['true', 'false', 'null'])) {
      $value = str_replace($options['enclosure'], $options['escape'] . $options['enclosure'], $value);
      return $options['enclosure'] . $value . $options['enclosure'];
    }
    switch(true){
      case is_null($value):
        return 'null';
        
      case false === $value:
        return 'false';
        
      case true === $value:
        return 'true';
        
      default:
        return $value;
    }
      
  }
  
  /**
   * Encodes text randomly to html entities of different styles
   * This code comes from Symfony 1.4
   * 
   * @param string $text
   * @return string
   */
  protected static function encodeText($text){
    $encoded_text = '';  
    for ($i = 0; $i < strlen($text); $i++) {
      $char = $text{$i};
      $r = rand(0, 100);
  
      # roughly 10% raw, 45% hex, 45% dec
      # '@' *must* be encoded. I insist.
      if ($r > 90 && $char != '@') {
        $encoded_text .= $char;
      }
      else if ($r < 45) {
        $encoded_text .= '&#x'.dechex(ord($char)).';';
      }
      else  {
        $encoded_text .= '&#'.ord($char).';';
      }
    }  
    return $encoded_text;
  }

}
