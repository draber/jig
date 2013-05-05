<?php
/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 01-Mar-2013 Dieter Raber <me@dieterraber.net> 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Transcode\Plugin;

use Jig\Transcode\TranscodeInterface;
use Jig\Transcode\TranscodeAbstract;
use Jig\Exception\JigException;

class Uri extends TranscodeAbstract implements TranscodeInterface {

  protected static $encode_options = [
    'raw' => false,
    'query-fragment-only' => true
  ];
  
  /**
   *
   * @var array 
   */
  protected static $decode_options = [
    'raw' => false,
    'once' => false,
    'last-only' => true
  ];

  /**
   * Encode URI
   * 
   * @param string $uri
   * @param array $user_options
   * @throws JigException
   */
  public static function encode($uri, array $user_options = []) {
    $options    = self::mergeArgs(self::$encode_options, $user_options);
    $fn = $options['raw'] ? 'rawurlencode' : 'urlencode';
    if(!$options['query-fragment-only']){
      return $fn($uri);
    }
    // encode query and fragment only
    
    /*
     * @todo
     * if(isurl) : regexp
     * if(isarray): tostring
     * if(!strpos(?): alterntiver regex
     */
    return preg_replace_callback(
      '~\?(?<query>[^#]*)(#(?<fragment>.*))?$~', 
      function($matches) use ($fn) {
      	$retval = '';
        if(!empty($matches['query'])){ 
        	parse_str($matches['query'], $params);
        	foreach($params as $key => $value){
        		$params[$key] = $key . '=' . $fn($value);
        	}       	
        	$retval .= '?' . implode('&', $params);
        }
        if(!empty($matches['fragment'])){
        	$retval .= '#' . $fn($matches['fragment']);
        }
        return $retval;
      }, 
      $uri
    );
  }


  /**
   * Decode URI
   * 
   * @param string $uri
   * @param array $user_options
   * @return mixed
   */
  public static function decode($uri, array $user_options = []) {
    $options = self::mergeArgs(self::$decode_options, $user_options);
    $fn = $options['raw'] ? 'rawurldecode' : 'urldecode';
    if($options['once']){
      return $fn($uri);
    }
    // return all steps
    $ret_arr = [];
    while(preg_match('~%[0-9a-fA-F]{2}~', $uri)){
      $uri = $fn($uri);
      $ret_arr[] = $uri;
    }
    return $options['last-only'] ? array_pop($ret_arr) : $ret_arr;
  }
}