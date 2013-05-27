<?php

/**
 * Copyright (c) 06-May-2013 Dieter Raber <me@dieterraber.net>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to 
 * deal in the Software without restriction, including without limitation the 
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or 
 * sell copies of the Software, and to permit persons to whom the Software is 
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Gettext
 *
 * @author Dieter Raber <me@dieterraber.net>
 */
namespace Jig\I18n;
use Jig\Exception\JigException;

class Gettext {
  
  /**
   * Initialises gettext while at the same time circumventing Apache's gettext cache
   * 
   * @param string $locale in the format en_GB
   * @param string $i18nDir path to the message files, expects $locale/messages/domain.mo to be present
   * @param string $domain optional, default is 'generic'
   * @throws JigException
   */
  public static function init($locale, $i18nDir, $domain='generic') {
    
    
    $i18nFile = current(glob($i18nDir . '/messages/' . $locale . '/LC_MESSAGES/' . $domain . '*.mo'));
    if(!$i18nFile) {
      throw new JigException ('No .mo file found for the locale ' . $locale . ' on the domain ' . $domain);
    }
    $i18nFile = basename($i18nFile);
    $domain = substr($i18nFile, 0, strrpos($i18nFile, '.')); 
    
    putenv('LC_ALL=' . $locale);
    setlocale(LC_ALL, $locale);
    bindtextdomain($domain, $i18nDir . '/messages');
    textdomain($domain); 
  }
}

// this includes the function __() if not present
// the inclusion is done to get the function in the global namespace
include __DIR__ . '/function.translate.php';