<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 03-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Cache;

use Jig\Utils\StringUtils;

/**
 * CacheAbstract
 */
class CacheAbstract {

  
  /**
   * Unserialize cache data and delete the record on expiration
   * @param string $data
   * @param function $expirationCallback
   * @return string
   */
  protected static function unserialize($data, $expirationCallback=null) {

    $data = json_decode($data, true);
    if ($data['base64']) {
      $data['content'] = base64_decode($data['content']);
    }
    if($expirationCallback && $data['expires'] < time()){
      $expirationCallback();
    }
    return $data['content'];
  }

  /**
   * Serialize data and duration
   * 
   * @param mixed $data
   * @param mixed $duration can be a number of seconds or any argument accepted by strtotime()
   */
  protected static function serialize($data, $duration = 0) {
    switch (true) {
      case !$duration;
        $duration = strtotime('+1 week');
        break;

      case !is_numeric($duration);
        $duration = strtotime($duration);
        break;

      case is_numeric($duration);
        $duration = intval($duration);
        break;

      default:
        throw new JigException('Invalid duration ' . $duration . ', caching not possible');
    }
    $base64 = false;
    if (StringUtils::isBinary($data)) {
      $data   = base64_encode($data);
      $base64 = true;
    }

    $data = array(
      'expires' => $duration,
      'content' => $data,
      'base64'  => $base64
    );
    return json_encode($data);
  }

}
