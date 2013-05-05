<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 01-Mar-2013 Dieter Raber <me@dieterraber.net> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Convert\Plugins;

use Jig\Convert\PluginAbstract;
use Jig\Utils\StringUtils;
use Jig\Utils\ArrayUtils;
use Jig\Convert\Interfaces\PluginArrayInterface;

/**
 * Ini plugin.
 * 
 * Handle Ini files/strings
 */
class Ini extends PluginAbstract implements PluginArrayInterface {

  /**
   * Encode an array into INI format
   * 
   * <code>
   * // Default $options
   * $options = [
   *   'default_section'  => 'settings',
   *   'allow_fake_first' => true
   * ]
   * </code>
   * 
   * @param mixed $resource
   * @param array $options
   */
  public function fromArray(array $resource, array $options = []) {
    $options = array_merge(
      [
      'default_section'  => 'settings',
      'allow_fake_first' => true
      ], $options
    );

    $quoteArgs = [
      'enclosure' => '"',
      'escape'    => '\\'
    ];
    
    // fake a section in case the array is not deep enough
    if(count($resource) && $options['allow_fake_first'] && !is_array(current($resource))){
      $resource = [$options['default_section'] => $resource];
    }

    $ini = '';
    foreach ($resource as $l1Key => $l1Val) {
      if (is_array($l1Val)) {
        $ini .= "\n" . '[' . $l1Key . ']' . "\n";
        foreach ($l1Val as $l2Key => $l2Val) {
          if (is_array($l2Val)) {
            foreach ($l2Val as $l3Key => $l3Val) {
              if (is_array($l3Val)) {
                throw new JigException('$resource must not have more than three levels');
              }
              $ini .= $l2Key . '[' . $l3Key . '] = ' . StringUtils::quote($l3Val, $quoteArgs) . "\n";
            }
          }
          else {
            $ini .= $l2Key . ' = ' . StringUtils::quote($l2Val, $quoteArgs) . "\n";
          }
        }
      }
      else {
        $ini .= $l1Key . ' = ' . StringUtils::quote($l1Val, $quoteArgs) . "\n";
      }
    }
    return trim($ini);
  }

  /**
   * Decode a INI string to an array
   * 
   * @param mixed $resource
   * @param array $options
   * @return array
   */
  public function toArray($resource, array $options = []) {
    $options = array_merge(
      [
      'process_sections' => true,
      'scanner_mode'     => INI_SCANNER_RAW
      ], $options
    );
    return ArrayUtils::typecast(parse_ini_string(self::getRealResource($resource, $options), $options['process_sections'], $options['scanner_mode']));
  }

}