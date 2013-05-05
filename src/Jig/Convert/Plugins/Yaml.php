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
use Symfony\Component\Yaml\Yaml as sfYaml;

/**
 * YAML plugin.
 * 
 * Wrapper for the Symfony YAML class to use it with Jig
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
class Yaml extends PluginAbstract implements PluginArrayInterface {

  /**
   * {@inheritdoc} 
   * 
   * Target format: YAML
   * 
   * <code>
   * $options = [
   *  'inline' => 10, // level where you switch to inline YAML
   *  ];
   * </code>
   * 
   * @param mixed $resource
   * @param array $options
   * 
   */
  public function fromArray($resource, array $options = []) {
    $options = array_merge(
      [
      'inline' => 10
      ], $options
    );
    return sfYaml::dump(parent::getRealResource($resource, $options), $options['inline']);
  }
  

  /**
   * {@inheritdoc} 
   * 
   * Source format: YAML
   * 
   * @param mixed $resource
   * @param array $options
   * @return array
   */
  public function toArray($resource, array $options = []) {
    return sfYaml::parse(parent::getRealResource($resource, $options));
  }

}