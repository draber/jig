<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 07-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Convert\Plugins;

use Jig\Convert\PluginAbstract;
use Madflow\Markdown\Parser;

/**
 * Markdown. This does not implement any of the interfaces an cannot interact
 * with any of the other classes. It is in fact only a compatiblity layer for
 * Florian Reiss's Markdown class https://github.com/madflow/flow-markdown/
 * which in return is based on PHP Markdown by Michel Fortin http://michelf.com/ 
 */
class Markdown extends PluginAbstract {

  public function toHtml($resource, array $options = []) {
    $resource = parent::getRealResource($resource, $options);
    $parser = new Parser();
    return $parser -> transform($resource);
  }

}
