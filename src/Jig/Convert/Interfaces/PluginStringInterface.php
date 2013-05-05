<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 01-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Convert\Interfaces;

/**
 * PluginStringInterface
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
interface PluginStringInterface {

  /**
   * Converts the source format to a string
   * 
   * @param mixed $resource
   * @param array $options
   */
  public function toString($resource, array $options = []);

  /**
   * Converts a string to the target format
   * 
   * @param string $resource
   * @param array $options
   */
  public function fromString($resource, array $options = []);
}
