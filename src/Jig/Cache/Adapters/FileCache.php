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
 * FileCache. Uses JSON files as storage
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
      self::delete($cacheFilePath);
    };
    
    return self::unserialize(file_get_contents($cacheFilePath), $expirationCallback);
  }

  /**
   * Writes a resource to the cache, paths will ge created if applicable
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
  
  /**
   * Delete a cache file. This can also be used to delete a file before its
   * actual expiration.
   * 
   * @param type $cacheFilePath
   */
  public static function delete($cacheFilePath) {
    if(is_file($cacheFilePath)) {
      unlink($cacheFilePath);
    }
  }
}
