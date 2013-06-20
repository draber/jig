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

/**
 * Generates the locale from either _GET, _POST, _COOKIE or _SESSION.
 * Language in this context is defined as in ISO 639-1, countries as
 * in ISO 3166-1. It can in-/output the locales in the formats en and en_GB.
 * If no country is found in the data submitted the default country as found
 * in self::getDefaultCountry is used
 * 
 * @see self::getDefaultCountry
 */
class LocaleHandler {
  
  /**
   * <code>
   * // Configuration defaults
   * $settings = [
   *  'available' => ['fr', 'en', 'de'], // could be also something like [en_GB...]
   *  'default' => 'fr',                 // same here
   *  'key' => 'locale',                 // the key used in _COOKIE etc.
   *  'precedence' => ['get', 'post', 'cookie', 'session'],
   *  'cookie-path' => '/',              // for which path to set the cookie
   *  'save-locale' => true,             // save the loacle in a cookie
   *  'force-locale' => null             // forces the locale to be the value of this key
   * ]
   * </code>
   * 
   * @var array 
   */
  private $settings = [
      'available' => ['fr', 'en', 'de'],
      'default' => 'fr',
      'key' => 'locale',
      'precedence' => ['get', 'post', 'cookie', 'session'],
      'cookie-path' => '/',
      'save-locale' => true,
      'force-locale' => null
  ];
  
  /**
   * Locale in the form fr_FR
   * 
   * @var string 
   */
  private $locale;
  
  /**
   * Locale in the form fr
   * 
   * @var string 
   */
  private $language;
  
  /**
   * Locale in the form FR
   * 
   * @var string 
   */
  private $country;
  
  /**
   * Constructor
   */
  public function __construct(array $settings = array()) {
    $this -> settings = array_merge($this -> settings, $settings);
    foreach($this -> settings['available'] as $key => $value) {
      $this -> settings['available-locales'][$key] = $this -> harmonize($value);
      $this -> settings['available-langs'][$key] = substr($value, 0, 2);
    }
    // @TODO speedy delete, does this cause any trouble?
    //$this -> settings['default'] = $this -> harmonize($this -> settings['default']);
    unset($this -> settings['available']);
    $this -> locale = !empty($this -> settings['force-locale']) 
                    ? $this -> harmonize($this -> settings['force-locale'])
                    : $this -> buildLocale();
    $this -> language = substr($this -> locale, 0, 2);
    $this -> country = strlen($this -> locale) === 5 ? strtoupper(substr($this -> locale, -2))
              : $this -> getDefaultCountry($this -> language);
    if($this -> settings['save-locale']) {
      $this -> saveLocale();
    }    
  }


  /**
   * Assigns a default country in case none has been suppied to the class
   * 
   * @param string $lang
   * @return string
   */
  protected function getDefaultCountry($lang) {
    $defaults = [
        'af' => 'ZA',
        'ar' => 'SA',
        'be' => 'BY',
        'bg' => 'BG',
        'br' => 'FR',
        'bs' => 'BA',
        'ca' => 'ES',
        'cs' => 'CZ',
        'cy' => 'GB',
        'da' => 'DK',
        'de' => 'DE',
        'el' => 'GR',
        'en' => 'GB',
        'es' => 'ES',
        'et' => 'EE',
        'eu' => 'ES',
        'fa' => 'IR',
        'fi' => 'FI',
        'fo' => 'FO',
        'fr' => 'FR',
        'ga' => 'IE',
        'gl' => 'ES',
        'gv' => 'GB',
        'he' => 'IL',
        'hi' => 'IN',
        'hr' => 'HR',
        'hu' => 'HU',
        'id' => 'ID',
        'is' => 'IS',
        'it' => 'IT',
        'iw' => 'IL',
        'ja' => 'JP',
        'ka' => 'GE',
        'kl' => 'GL',
        'ko' => 'KR',
        'kw' => 'GB',
        'lb' => 'LU',
        'lt' => 'LT',
        'lv' => 'LV',
        'mi' => 'NZ',
        'mk' => 'MK',
        'mr' => 'IN',
        'ms' => 'MY',
        'mt' => 'MT',
        'nl' => 'NL',
        'nn' => 'NO',
        'no' => 'NO',
        'oc' => 'FR',
        'pl' => 'PL',
        'pt' => 'PT',
        'ro' => 'RO',
        'ru' => 'RU',
        'se' => 'NO',
        'sk' => 'SK',
        'sl' => 'SI',
        'sq' => 'AL',
        'sv' => 'SE',
        'ta' => 'IN',
        'te' => 'IN',
        'tg' => 'TJ',
        'th' => 'TH',
        'tl' => 'PH',
        'tr' => 'TR',
        'uk' => 'UA',
        'ur' => 'PK',
        'uz' => 'UZ',
        'vi' => 'VN',
        'wa' => 'BE',
        'yi' => 'US',
        'zh' => 'CN'
    ];
    return isset($defaults[$lang]) ? $defaults[$lang] : '';
  }
  
  /**
   * Get the locale in the form fr_FR
   * 
   * @return string 
   */
  public function getLocale($asUrlFragment=false) {
    return  $asUrlFragment ? str_replace('_', '-', strtolower($this -> locale)) : $this -> locale;
  }

  
  /**
   * Get the locale in the form fr
   * 
   * @return string 
   */
  public function getLanguage() {
    return $this -> language;
  }

  
  /**
   * Get the locale in the form FR
   * 
   * @return string 
   */
  public function getCountry() {
    return $this -> country;
  }

  
  /**
   * Get the default locale in its original format
   * 
   * @return string 
   */
  public function getDefaultLocale($asUrlFragment=false) {
    return $asUrlFragment ? str_replace('_', '-', strtolower($this -> settings['default'])) : $this -> settings['default'];
  } 
  
  
  /**
   * Get the locale in the form fr_FR
   * 
   * @return string 
   */
  public function getAvailableLocales() {
    return $this -> settings['available-locales'];
  }

  
  /**
   * Generate the locale from _SESSION or any of _REQUEST
   * 
   * @return string 
   */
  protected function buildLocale() {
    foreach($this -> settings['precedence'] as $key) {
      switch($key) {
        case 'get':
          $lookup = $_GET;
          break;
        case 'cookie':
          $lookup = $_COOKIE;
          break;
        case 'post':
        case 'put':
        case 'delete':
          $lookup = $_POST;
          break;
        case 'session':
          $lookup = !empty($_SESSION) ? $_SESSION : array();
          break;
      }
      if(isset($lookup) && !empty($lookup[$this -> settings['key']])
              && (in_array($lookup[$this -> settings['key']], $this -> settings['available-locales'])
              || in_array($lookup[$this -> settings['key']], $this -> settings['available-langs']))
      ) {
        return $this -> harmonize($lookup[$this -> settings['key']]);
      }
    }
    return $this -> settings['default'];
  }

  
  /**
   * Converts locale from any format to the format fr_FR
   * 
   * @return string 
   */
  protected function harmonize($value) {
    $value = strtolower($value);
    return strlen($value) === 2 ? trim($value . '_' . $this -> getDefaultCountry($value), '_')
              : substr($value, 0, 2) . '_' . strtoupper(substr($value, -2));
  }

  
  
  /**
   * Save the locale in a coookie
   */
  protected function saveLocale() {
    $locale = $this -> locale;
    $_SESSION[$this -> settings['key']] = $locale;
    setcookie($this -> settings['key'], $locale, strtotime('+1 month'), $this -> settings['cookie-path']);
  }

}