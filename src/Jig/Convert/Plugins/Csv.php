<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 03-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Convert\Plugins;

use Jig\Convert\PluginAbstract;
use Jig\Convert\Interfaces\PluginArrayInterface;
use Jig\Utils\StringUtils;
use Jig\Utils\ArrayUtils;
use Jig\Exception\JigException;

/**
 * CSV conversion
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
class Csv extends PluginAbstract implements PluginArrayInterface {

  /**
   * {@inheritdoc} 
   * 
   * Target format: CSV
   * 
   * <code>
   * $options = [
   *  'delimiter' => ',',    // typically ,|;|\t
   *  'enclosure' => '"',    // typically "|'
   *  'escape'    => '\\',   // how the above is escaped
   *  'pivot'     => false   // pivot resulting array
   *  ];
   * </code>
   * @example url://path/to/example.php description
   * 
   * @param mixed $resource
   * @param array $options
   * @return array
   */
  public function toArray($resource, array $options = []) {
    $options = array_merge(
      [
      'delimiter' => ',',
      'enclosure' => '"',
      'escape'    => '\\',
      'pivot'     => false
      ], $options
    );
    $resource   = explode("\n", trim(parent::getRealResource($resource, $options)));
    $retval     = [];
    foreach ($resource as $line) {
      $line   = trim($line);
      $values = str_getcsv($line, $options['delimiter'], $options['enclosure'], $options['escape']);
      $retval[] = ArrayUtils::typecast($values);
    }
    return $options['pivot'] ? ArrayUtils::pivot($retval) : $retval;
  }

  /**
   * {@inheritdoc} 
   * 
   * Source format: CSV
   * 
   * <code>
   * $options = [
   *  'delimiter' => ',',    // typically ,|;|\t
   *  'enclosure' => '"',    // typically "|'
   *  'escape'    => '\\',   // how the above is to be escaped
   *  'pivot'     => false   // pivot input array
   *  ];
   * </code>
   * 
   * @param mixed $resource
   * @param array $options
   */
  public function fromArray($resource, array $options = []) {
    $options = array_merge(
      [
      'delimiter' => ',',
      'enclosure' => '"',
      'escape'    => '\\',
      'pivot'     => false
      ], $options
    );
    $resource   = parent::getRealResource($resource, $options);
    if($options['pivot']){
      $resource = ArrayUtils::pivot($resource);
    }
    $dataDepth = self::verifyDataDepthLimit($resource, 2);
    if ($dataDepth === 1) {
      $resource = [$resource];
    }
    else if ($dataDepth > 2) {
      $msg = '$resource must have one or two levels';
      if($options['pivot']){
        $msg .= ' after it has been pivoted';
      }
      throw new JigException($msg);
    }
    $csv = '';
    foreach ($resource as $valueArr) {
      $line = '';
      foreach ($valueArr as $value) {
        $line .= StringUtils::quote($value, $options) . $options['delimiter'];
      }
      $csv .= rtrim($line, $options['delimiter']) . PHP_EOL;
    }
    return $csv;
  }

}


