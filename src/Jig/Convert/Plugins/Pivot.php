<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 06-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Convert\Plugins;
use Jig\Utils\ArrayUtils;

/**
 * Pivot an array
 * Just a wrapper for ArrayUtils::pivot() to allow the usage with the converter
 */
class Pivot {

  /**
   * Pivot an array
   * 
   * @param array $resource
   * @return array
   */
  public function fromArray(array $resource) {
    return ArrayUtils::pivot($resource);
  }
}
