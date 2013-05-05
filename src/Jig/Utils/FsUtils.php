<?php

/**
 * This file is part of the Jig package.
 * 
 * Copyright (c) 04-Mar-2013 Dieter Raber <me@dieterraber.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jig\Utils;

/**
 * FsUtils
 */
class FsUtils {
  
  /**
   * Returns the extension of a given file
   *
   * @param  string $filePath
   * @return string
   */
  public static function getFileExtension($filePath) {
    return strtolower(substr(strrchr($filePath, '.'), 1));
  }

  /**
   * Removes the extension of a given file name/path.
   * This is always based on the basename of the path
   *
   * @param  string $filePath
   * @return string
   */
  public static function removeFileExtension($filePath) {
    $filePath = basename($filePath);
    return substr($filePath, 0, strrpos($filePath, '.'));
  }
  
  /**
   * Build a directory if it doesn't exist already
   * 
   * @param string $directory
   * @param int $perms
   */
  public static function mkDir($directory, $perms=0777){
    if (!is_dir($directory)) {
      mkdir($directory, $perms, true);
    }
  }
}
