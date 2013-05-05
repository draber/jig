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
 * Invoke a converter  plugin
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
class PluginFactory {

  /**
   * Create an object instance of the plugin
   * 
   * @param string $pluginName
   * @param array $options
   * @return object \nsPlugin instance of the plugin
   * @throws JigException
   */
  public static function build($pluginName, array $options = []) {
    $nsPlugin = __NAMESPACE__ . '\\Plugins\\' . $pluginName;
    if (class_exists($nsPlugin)) {
      return new $nsPlugin($options);
    }
    throw new JigException('Invalid plugin ' . $pluginName);
  }

}
