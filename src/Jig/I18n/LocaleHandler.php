<?php

namespace Jig\I18n;

class LocaleHandler {
  
  /**
   * Configuration defaults
   * 
   * @var array 
   */
  private $settings = array(
      'available' => array('fr', 'en', 'de'),
      'default' => 'fr',
      'key' => 'locale',
      'precedence' => array('get', 'post', 'cookie', 'session'),
      'cookie-path' => '/',
      'save-locale' => true,
      'force-locale' => null
  );
  
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
      $this -> settings['available-locale'][$key] = $this -> harmonize($value);
      $this -> settings['available-lang'][$key] = substr($value, 0, 2);
    }
    $this -> settings['default'] = $this -> harmonize($this -> settings['default']);
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
  public function getLocale() {
    return $this -> locale;
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
  public function getDefaultLocale() {
    return $this -> settings['default'];
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
          $lookup = $_POST;
          break;
        case 'session':
          $lookup = !empty($_SESSION) ? $_SESSION : array();
          break;
      }
      if(isset($lookup) && !empty($lookup[$this -> settings['key']])
              && (in_array($lookup[$this -> settings['key']], $this -> settings['available-locale'])
              || in_array($lookup[$this -> settings['key']], $this -> settings['available-lang']))
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