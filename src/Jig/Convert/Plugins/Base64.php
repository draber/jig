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
use Jig\Convert\Interfaces\PluginStringInterface;

/**
 * base64 plugin.
 * 
 * Handle base64 transcoding
 */
class Base64 extends PluginAbstract implements PluginStringInterface {

  /**
   * Encode a string into base64 format
   * 
   * @param string $resource
   * @param array $options
   */
  public function fromString($resource, array $options = []) {
    $resource = self::getRealResource($resource, $options);
    return base64_encode($resource);
  }

  /**
   * Decode a base64 string to a (binary) string
   * 
   * @param string $resource
   * @param array $options
   * @return string
   */
  public function toString($resource, array $options = []) {
    $options = array_merge(
      [
      'strict' => true
      ], $options
    );
    $resource = self::getRealResource($resource, $options);
    return base64_decode($resource, $options['strict']);
  }

}