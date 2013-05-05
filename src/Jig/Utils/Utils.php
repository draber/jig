<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 03-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Utils;

/**
 * Collection of basic utitlity functions
 */
class Utils {



  /**
   * Create a random password
   *
   * @param array $settings
   * @setting bool length, number of characters, default 8
   * @setting bool upper, whether or not to include upper case characters, default true
   * @setting bool lower, whether or not to include lower case characters, default true
   * @setting bool number, whether or not to include numbers, default true
   * @setting bool spec, whether or not to include special characters (!$*@-+), default false
   * @return string $password
   */
  public static function createPassword($settings = array()) {
    $defaults = array('length' => 8,
      'upper'  => true,
      'lower'  => true,
      'number' => true,
      'spec'   => false);

    $settings   = array_merge($defaults, $settings);
    $characters = array('upper'     => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
      'lower'     => 'abcdefghijklmnopqrstuvwxyz',
      'number'    => '0123456789',
      'spec'      => '!$*@-+');
    $password   = '';
    $availChars = '';
    foreach ($characters as $key => $value) {
      if ($settings[$key]) {
        $availChars .= $value;
      }
    }
    $avail_length = strlen($availChars);
    for ($i = 0; $i < $settings['length']; $i++) {
      $password .= $availChars{rand(0, $avail_length - 1)};
    }
    return $password;
  }

  /**
   * Retrieve php ini settings in bytes 
   * Refer to http://www.php.net/manual/en/function.ini-get.php
   * for more information
   *
   * @param string $key, the key in the php ini file
   * @return int $value, the value in bytes
   */
  public static function iniGetBytes($key) {
    $value = ini_get(trim($key));
    $last  = strtolower($value[strlen($value) - 1]);
    switch ($last) {
      // The 'g' modifier is available since PHP 5.1.0
      case 'g':
        $value *= 1024;
      case 'm':
        $value *= 1024;
      case 'k':
        $value *= 1024;
    }
    return $value;
  }



  /**
   * Convert an object to an array
   *
   * @param object $object, the object to convert
   * @return array
   */
  public static function objectToArray($object) {
    if (!is_object($object) && !is_array($object)) {
      return $object;
    }
    if (is_object($object)) {
      $object = get_object_vars($object);
    }
    return array_map(array(self, 'objectToArray'), $object);
  }



  public static function dateToTimestamp($date, $format = false) {
    if (!$format) {
      $format = 'd/m/Y';
    }

    $dataArr[] = strtok($date, ' /.:-');
    $dataArr[] = strtok(' /.:-');
    $dataArr[] = strtok(' /.:-');

    foreach ($dataArr as $part) {
      if (!is_numeric($part)) {
        return false;
      }
    }

    $formatArr[strtok($format, ' /.:-')] = $dataArr[0];
    $formatArr[strtok(' /.:-')]          = $dataArr[1];
    $formatArr[strtok(' /.:-')]          = $dataArr[2];

    if (isset($formatArr['j'])) {
      $formatArr['d'] = $formatArr['j'];
      unset($formatArr['j']);
    }
    $formatArr['d'] = str_pad($formatArr['d'], 2, '0', STR_PAD_LEFT);

    if (isset($formatArr['n'])) {
      $formatArr['m'] = $formatArr['n'];
      unset($formatArr['n']);
    }
    $formatArr['m'] = str_pad($formatArr['m'], 2, '0', STR_PAD_LEFT);

    if (isset($formatArr['y'])) {
      $formatArr['Y'] = $formatArr['y'];
      unset($formatArr['y']);
    }
    if ($formatArr['Y'] < 100) {
      if ($formatArr['Y'] <= date('y')) {
        $formatArr['Y'] = $formatArr['Y'] + 2000;
      }
      else {
        $formatArr['Y'] = $formatArr['Y'] + 1900;
      }
    }
    // some validation
    if ($formatArr['Y'] % 4 == 0) {
      if ($formatArr['Y'] % 100 != 0) {
        $leapYear = true;
      }
      else {
        if ($formatArr['Y'] % 400 == 0) {
          $leapYear = true;
        }
        else {
          $leapYear = false;
        }
      }
    }
    else {
      $leapYear    = false;
    }
    $shortMonths = array(2         => $leapYear ? 29 : 28,
      4         => 30,
      6         => 30,
      9         => 30,
      11        => 30);
    $max_days = isset($shortMonths[$formatArr['m']]) ? $shortMonths[$formatArr['m']] : 31;

    if ($formatArr['m'] > 12 || $formatArr['m'] < 1 || $formatArr['d'] > $max_days || $formatArr['d'] < 1) {
      return false;
    }
    return mktime(0, 0, 0, $formatArr['m'], $formatArr['d'], $formatArr['Y']);
  }

}
