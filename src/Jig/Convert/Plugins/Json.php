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
use Jig\Convert\Interfaces\PluginArrayInterface;

/**
 * JSON conversion
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
class Json extends PluginAbstract implements PluginArrayInterface {

  /**
   * {@inheritdoc} 
   * 
   * Target format: JSON
   * 
   * <code>
   * $options = [
   *  'json_options' => 0, // Bitmask consisting of constants below. 
   *  ];
   * </code>
   * - JSON_HEX_TAG (integer): All < and > are converted to \u003C and \u003E.
   * - JSON_HEX_AMP (integer): All &s are converted to \u0026. 
   * - JSON_HEX_APOS (integer): All ' are converted to \u0027. 
   * - JSON_HEX_QUOT (integer): All " are converted to \u0022. 
   * - JSON_FORCE_OBJECT (integer): Outputs an object rather than an array when a non-associative array is used. 
   *   Especially useful when the recipient of the output is expecting an object and the array is empty.
   * - JSON_NUMERIC_CHECK (integer): Encodes numeric strings as numbers. 
   * - JSON_BIGINT_AS_STRING (integer): Encodes large integers as their original string value.
   * - JSON_PRETTY_PRINT (integer): Use whitespace in returned data to format it.
   * - JSON_UNESCAPED_SLASHES (integer): Don't escape /.
   * - JSON_UNESCAPED_UNICODE (integer): Encode multibyte Unicode characters literally (default is to escape as \uXXXX).
   * 
   * See {@link http://www.php.net/manual/en/function.json-encode.php} for updates
   * 
   * @link http://www.php.net/manual/en/function.json-encode.php
   * @param mixed $resource
   * @param array $options
   * @return string
   */
  public function fromArray($resource, array $options = []) {
    $options = array_merge(
      [
      'json_options' => 0
      ], $options
    );
    return json_encode(parent::getRealResource($resource, $options), $options['json_options']);
  }

  /**
   * Decode a JSON string to an array
   * 
   * @param mixed $resource
   * @param array $options
   * @return array
   */
  public function toArray($resource, array $options = []) {
    $options['assoc'] = false;
    return $this -> toMixed($resource, $options);
  }

  /**
   * Decode a JSON string to an object
   * 
   * @param mixed $resource
   * @param array $options
   * @return array
   */
  public function toObject($resource, array $options = []) {
    $options['assoc'] = true;
    return $this -> toMixed($resource, $options);
  }
  
  /**
   * Avoids duplicate code for toArray and toObject
   * 
   * @param mixed $resource
   * @param array $options
   * @return mixed
   */
  protected function toMixed($resource, array $options = []) {
    $options = array_merge(
      [
      'depth'   => 512,
      'options' => 0
      ], $options
    );
    return json_decode(parent::getRealResource($resource, $options), $options['assoc'], $options['depth'], $options['options']);
  }

}