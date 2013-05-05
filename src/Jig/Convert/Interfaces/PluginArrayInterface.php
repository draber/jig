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
 * Interface for plugins that handle array-ish formats such as YAML, INI or CSV
 * 
 * @author Dieter Raber <me@dieterraber.net>
 */
interface PluginArrayInterface {

  /**
   * Converts the source or resources that represent that represent the source to an array. 
   * For a reference on accepted formats for $resource as well as on $options see 
   * {@see Jig\Convert\PluginAbstract::getRealResource()}. $options submitted to this function 
   * will be passed through to getRealResource(), meaning that you can override them here, too
   * 
   * @param mixed $resource
   * @param array $options
   * @see Jig\Convert\PluginAbstract::getRealResource()
   */
  public function toArray($resource, array $options = []);

  /**
   * Converts an array or resources that represent an array in a serialized format
   * (either with serialize() or json_encode()) to the target format. For a reference 
   * on accepted formats for $resource as well as on $options see {@see Jig\Convert\PluginAbstract::getRealResource()}. 
   * $options submitted to this function will be passed through to getRealResource(), meaning that you can override
   * them here, too
   * 
   * @param mixed $resource
   * @param array $options
   * @see Jig\Convert\PluginAbstract::getRealResource()
   */
  public function fromArray($resource, array $options = []);
}
