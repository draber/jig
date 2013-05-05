<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 03-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Cache\Adapters;

use Jig\Cache\CacheAbstract;
use Jig\Cache\Interfaces\CacheInterface;

/**
 * FileCache
 */
class FileCache extends CacheAbstract implements CacheInterface {

  /**
   * Reads a resource from the cache
   * 
   * @param string $cacheFilePath
   */
  public static function read($cacheFilePath) {
    if (!is_readable($cacheFilePath)) {
      return false;
    }

    $expirationCallback = function() use ($cacheFilePath){
      unlink($cacheFilePath);
    };
    
    return self::unserialize(file_get_contents($cacheFilePath), $expirationCallback);
  }

  /**
   * Write a resource to the a file
   * 
   * @param type $cacheFilePath
   * @param mixed $data
   * @param mixed $duration can be a number of seconds or any argument accepted by strtotime()
   */
  public static function write($cacheFilePath, $data, $duration = 0) {
    $data = self::serialize($data, $duration);
    $directory = dirname($cacheFilePath);
    if (!is_dir($directory)) {
      mkdir($directory, 0777, true);
    }
    return file_put_contents($cacheFilePath, $data);
  }

}
