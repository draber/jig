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
   * Encode an array into base64 format
   * 
   * @param string $resource
   * @param array $options
   */
  public function fromString($resource, array $options = []) {
    $options = array_merge(
      [
      'data_uri'  => false,
      'mime_type' => ''
      ], $options
    );
    //return json_encode($resource, $options['options']);
  }

  /**
   * Encode binary data into .base64 format
   * 
   * @param string $data
   * @param array $user_options
   * @throws JigException
   */
  public static function encode($data, array $user_options = []) {
    $options = self::mergeArgs(self::$encode_options, $user_options);
    $base_64 = base64_encode($data);

    // return raw
    if (!$options['data-uri']) {
      return $base_64;
    }

    // return as data ruri
    if (!$options['mime-type']) {
      $tmp                  = tmpfile();
      fwrite($tmp, $data);
      fseek($tmp, 0);
      $file_info            = new finfo(FILEINFO_MIME);
      $options['mime-type'] = $file_info -> file($tmp);
      fclose($tmp);
    }
    return 'data:' . $options['mime-type'] . ';base64,' . $base_64;
  }

  /**
   * Decode a base64 string to a (binary) string
   * 
   * @param string $resource
   * @param array $user_options
   * @return string
   */
  public function toString($resource, array $options = []) {
    $options = array_merge(
      [
      'strict' => true
      ], $options
    );
    $resource = self::getRealResource($resource, $options);
    if (0 === strpos($resource, 'data:')) {
      $resource = substr($resource, strpos($resource, 'base64,') + 7);
    }
    return base64_decode($resource, $options['strict']);
  }

}