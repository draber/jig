<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 01-Mar-2013 Dieter Raber <me@dieterraber.net> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Convert;

use Jig\Cache\Adapters\FileCache;
use Jig\Utils\FsUtils;
use Jig\Exception\JigException;

/**
 * PluginBase is the abstract base class for all plugins
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
abstract class PluginAbstract {

  /**
   * Reads data from a file or URL data can be parsed as PHP
   * 
   * <code>
   * // possible resource examples
   * $resource = 'http://path/to/resource'; // URL that points to the resource
   * $resource = 'path/to/file';            // File that contains the resource
   * $resource = 'some string';             // string in the expected format
   * $resource = ['some' => 'array'];       // array
   * $resource = ?                          // some other format as long there is a suitable plugin
   * 
   * // In the cases of URLs, files and strings the resource can also be be parsed, see options below
   * 
   * $options = [
   *   'parse_file'      => true,     // accept files as parameter
   *   'parse_php'       => false,    // evaluate PHP code in a file, means also to
   *                                  // parse code treated with serialize() to an array.
   *                                  // Code serialized with json_encode() are always allowed
   *                                  // NOTE: Use this with only when you are sure about the source!
   *   'expected_format' => null,     // string|array by default computed from the name of the calling function 
   *                                  // triggered within the restrictions above
   *   'parse_url'       => true,     // accept URLs as parameter
   *   'use_cache'       => true,     // URL resources are cached for a short while by default to avoid HTTP requests
   *   'cache_dir'       => null,     // default: rtrim(sys_get_temp_dir(), '/') . '/PHP_Jig/Converter/' . session_id()
   * ];
   * 
   * // $options will be merged with those in the constructor and those sent to the function
   * </code>
   * 
   * @param mixed $resource
   * @param array $options
   * @return mixed
   * @todo garbage collection for cached resources
   */
  protected static function getRealResource($resource, $options = []) {
    $options = array_merge(
      [
      'parse_php'       => false,
      'parse_file'      => true,
      'parse_url'       => true,
      'use_cache'       => true,
      'expected_format' => null,
      'cache_dir'       => rtrim(sys_get_temp_dir(), '/') . '/PHP_Jig/Converter/' . session_id()
      ], 
      $options
    );
    
    // compute expected format
    if(is_null($options['expected_format'])){
      $matches = [];
      
      preg_match('~^from(?<expected_format>[\w]+)$~', strtolower(debug_backtrace()[1]['function']), $matches);
      if(empty($matches['expected_format']) || !in_array($matches['expected_format'], ['string', 'array'])){
        $options['expected_format'] = 'string';
      }
      else {
        $options['expected_format'] = $matches['expected_format'];
      }
    }
    
    // - Resource cannot be a file or a URL or they are both disallowed
    if (!is_string($resource) || (!$options['parse_url'] && !$options['parse_file'])) {
      return $resource;
    }

    $cacheFile = $options['cache_dir'] . '/' . session_id() . '/' . md5($resource);

    // - Resource might be cached
    if ($options['use_cache'] && is_readable($cacheFile)) {
      return FileCache::read($cacheFile);
    }

    // - Resource might be an URL
    if ($options['parse_url'] && false === strpos($resource, "\n") && false !== strpos($resource, '://')) {
      $client   = new \Guzzle\Http\Client($resource);
      $request  = $client -> get();
      $response = $request -> send();
      $resource = (string) $response -> getBody();
      if ($options['expected_format'] === 'array') {
        $resource = self::unserializeResource($resource, $options['parse_php']);
      }
      if ($options['use_cache']) {
        FileCache::write($cacheFile, $resource, '+1minute');
      }
      return $resource;
    }

    // - Resource might be a file
    if ($options['parse_file'] && false === strpos($resource, "\n") && is_readable($resource)) {
      if ($options['parse_php']) {
        ob_start();
        require $resource;
        $resource = ob_get_contents();
        ob_end_flush();
      }
      $resource = file_get_contents($resource);
      return $options['expected_format'] === 'array' ? self::unserializeResource($resource) : $resource;
    }

    // - Resource is neither file nor URL
    return $resource;
  }

  /**
   * Tries to unserialize a string that is either encoded with json_encode or serialize,
   * the latter only when php parsing is allowed. This restriction is due to the warning
   * in the unserialize documentation: "Do not pass untrusted user input to unserialize(). 
   * Unserialization can result in code being loaded and executed due to object 
   * instantiation and autoloading, and a malicious user may be able to exploit this. 
   * Use a safe, standard data interchange format such as JSON (via json_decode() 
   * and json_encode()) if you need to pass serialized data to the user."
   * 
   * @param string $resource
   * @param bool $parse_php
   * @return array
   * @throws JigException
   */
  protected function unserializeResource($resource, $parse_php = false) {
    $resource = trim($resource);
    if (0 === strpos($resource, '{') || 0 === strpos($resource, '[')) {
      try {
        return json_decode($resource);
      }
      catch (Exception $e) {
        throw new JigException('$resource is not a json encoded array');
      }
    }
    else if ($parse_php && preg_match('~^a:[\d]+:~', $resource)) {
      try {
        return unserialize($resource);
      }
      catch (Exception $e) {
        throw new JigException('$resource is not a serialzed array');
      }
    }
    return $resource;
  }

  /**
   * Some formats accept as limited depth of levels in an array, e.g. csv can only have two dimensions
   * 
   * @param array $resource
   * @param int $level=1
   * @return int
   */
  protected static function verifyDataDepthLimit(array $resource, $maxLevel, $level = 1) {
    if ($level > $maxLevel) {
      return $maxLevel + 1;
    }
    $nextLevel    = $level + 1;
    $highestLevel = $level;
    foreach ($resource as $value) {
      if (is_array($value)) {
        $subLevel     = self::verifyDataDepthLimit($value, $maxLevel, $nextLevel);
        $highestLevel = max($subLevel, $highestLevel);
      }
    }
    return $highestLevel;
  }

  /**
   * Some of the functions need a file as a resource for various reasons (e.g. to check the mime type).
   * If the resource is some type of variable instead this function will create a temporary file.   * 
   * 
   * @param string $fileName
   * @param string $data
   */
  protected static function tempStore($fileName, $data) {
    $filePath = rtrim(sys_get_temp_dir(), '/') . '/PHP_Jig/Converter/' . session_id();
    FsUtils::mkDir($filePath);
    file_put_contents($filePath . '/' . $fileName, $data);
    return $filePath . '/' . $fileName;
  }

}
