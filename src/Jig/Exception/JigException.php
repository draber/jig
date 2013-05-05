<?php
/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 01-Mar-2013 Dieter Raber <me@dieterraber.net> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Jig\Exception;

class JigException extends \Exception {

  /**
   * Prefixes the message with __METHOD__ of the callee (if not given)
   * 
   * @param string $message
   * @param int $code
   * @param \Exception $previous
   */
  public function __construct($message = null, $code = 0, \Exception $previous = null) {
    $trace   = debug_backtrace();
    $message = trim($message);
    if (!empty($trace[1]) && !preg_match('~^[\w]+\:\:[\w]+~', $message)) {
      $message = $trace[1]['class'] . '::' . $trace[1]['function'] . '(): ' . $message;
    }
    parent::__construct($message, $code, $previous);
  }

}