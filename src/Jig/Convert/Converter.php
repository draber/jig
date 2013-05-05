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

use Jig\Exception\JigException;

/**
 * Convert cannot do much by itself, it rather uses the plugins in a smart fashion.
 * This way the plugins can be kept pretty simple and don't need ugrades whenever
 * new formats are introduced
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
class Converter {

  /**
   * This variable stores the conversion result and is currently used by the save method only
   *
   * @var mixed
   */
  protected $conversionResult = null;
  
  /**
   * This array basically allows to set all options in the constructor
   * 
   * @static array $options 
   * @see PluginAbstract::getRealResource()
   * @see AnyPlugin::AnyPublicConversionMethod()
   */
  protected static $options = [];

  /**
   * Set options globally. All options can be overridden by any function at any time.
   * 
   * @param array $options
   * @see PluginAbstract::getRealResource
   */
  public function __construct(array $options = []) {
    self::$options = array_merge(self::$options, $options);
  }

  /**
   * Each plugin should at least have two types of methods: 
   * - one two convert to (toArray, toString or similar)
   * - one to convert from (fromArray, fromString or similar)
   * 
   * When the desired conversion method exists it will be used directly, or if not
   * __call will try to construct it via an intermediate format.
   * 
   * @param string $method
   * @param array $options
   * @return mixed
   * @throws JigException
   */
  public function __call($method, array $args) {
    
    if(empty($args)){
      throw new JigException('Converter::' . $method . '() expects $resource [, array $options] as argument(s)');
    }

    $params = self::parseMethod($method);
    

    // Under the assumption you called Convert::FooToBar(resource, [a,b,c])
    $resource      = array_shift($args);                                         // 'resource'
    if($args){                                                                   // remaining args would be options
      self::$options = array_merge(self::$options, array_shift($args));
    }
    $sourcePlugin  = PluginFactory::build($params['SourceFormat'], self::$options); // Instance of Foo
    $convMethod    = $params['Operator'] . $params['TargetFormat'];                 // toBar
    
    // 1. Plugin Foo has a method toBar()
    if (method_exists($sourcePlugin, $convMethod)) {
      $this -> conversionResult = call_user_func([$sourcePlugin, $convMethod], $resource, self::$options);
      return $this -> conversionResult;
    }

    // 2. Foo cannot convert directly, try to find a common format
    $targetPlugin = PluginFactory::build($params['TargetFormat'], self::$options);      // Instance of Bar    
    $bridge       = self::getBridge($sourcePlugin, $targetPlugin, $params['Operator']); // something like (to|from)Array

    if (!$bridge) {
      throw new JigException('Converter::' . $method . '(): cannot be constructed, no intermediate format found');
    }
    
    // build bridge resource with Foo's 'to' method and final result with Bar's 'from' method 
    $bridgeResource = call_user_func([$sourcePlugin, $params['Operator'] . $bridge], $resource, self::$options);
    $this -> conversionResult = call_user_func([$targetPlugin, self::getOppositeOperator($params['Operator']) . $bridge], $bridgeResource, self::$options);
    return $this -> conversionResult;
  }

  /**
   * Get available to to|from methods of a plugin
   * 
   * @param object $plugin
   * @param string $operator to|from
   * @return array
   */
  protected static function getMethods($plugin, $operator) {
    $methods = array_filter(
      get_class_methods($plugin), function($method) use ($operator) {
        return 0 === strpos($method, $operator);
      }
    );
    array_walk(
      $methods, function(&$method) use ($operator) {
        $method = substr($method, strlen($operator));
      }
    );
    return $methods;
  }

  /**
   * Get first match between $sourcePlugin's 'to' methods and targetPlugins's 'from' methods
   * resp. inverse
   *  
   * @param object $sourcePlugin
   * @param object $targetPlugin
   * @param string $operator to|from
   * @return mixed
   */
  protected static function getBridge($sourcePlugin, $targetPlugin, $operator) {
    $matches = array_intersect(
      self::getMethods($sourcePlugin, $operator), self::getMethods($targetPlugin, self::getOppositeOperator($operator))
    );
    return $matches ? array_shift($matches) : false;
  }

  /**
   * Get operator for inverse operation
   * @param string $operator
   * @return string
   */
  protected static function getOppositeOperator($operator) {
    return $operator === 'to' ? 'from' : 'to';
  }

  /**
   * Parse the method for parameters and re-order them if applicable. 
   * 
   * @param string $method
   * @return array
   * @throws JigException
   */
  protected static function parseMethod($method) {
    $result = [];
    preg_match('~(?<SourceFormat>[A-Z][a-z0-9_]*)(?<Operator>To|From)(?<TargetFormat>[A-Z][a-z0-9_]*)~', $method, $result);
    if (count($result) !== 7) {
      throw new JigException('Converter::' . $method . '(): Expected format is Converter -> FooToBar([args])');
    }

    $result['Operator'] = strtolower($result['Operator']);

    $pluginNs = __NAMESPACE__ . '\\Plugins\\';
    if (!class_exists($pluginNs . $result['SourceFormat']) && class_exists($pluginNs . $result['TargetFormat'])) {
      $targetFormat           = $result['TargetFormat'];
      $result['TargetFormat'] = $result['SourceFormat'];
      $result['SourceFormat'] = $targetFormat;
      $result['Operator']     = self::getOppositeOperator($result['Operator']);
    }
    $result['method']       = $result['SourceFormat'] . $result['Operator'] . $result['TargetFormat'];

    return $result;
  }

  public function save($path) {
    if (!$this -> conversionResult) {
      return false;
    }
    $dir = dirname($path);
    if (!is_dir($dir)) {
      mkdir($dir, 0777, true);
    }
    file_put_contents(
      $path, (!is_string($this -> conversionResult) ? serialize($this -> conversionResult) : $this -> conversionResult)
    );
  }

}
