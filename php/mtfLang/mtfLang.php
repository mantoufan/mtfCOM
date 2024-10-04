<?php
class HttpAcceptLanguage {
  /**
   * Create a new HttpAcceptLanguage instance.
   *
   * @return array
   */
  public function __construct() {
    $languages = [];
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $httpAcceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
      $httpAcceptLanguages = explode(',', $httpAcceptLanguage);
    }
    if (empty($httpAcceptLanguages)) {
      return [];
    }
    foreach ($httpAcceptLanguages as $httpAcceptLanguage) {
      $language = [];
      $lang = trim($httpAcceptLanguage);
      if (stripos($httpAcceptLanguage, ';')) {
        $lang = trim(stristr($httpAcceptLanguage, ';', true));
      }
      $language['ISO639-1'] = $lang;
      if (stripos($lang, '-')) {
        $language['ISO639-1'] = stristr($lang, '-', true);
        $language['ISO3166-1'] = trim(mb_strtoupper(stristr($lang, '-')), '-');
        $language['RFC1766'] = $language['ISO639-1'] . '-' . $language['ISO3166-1'];
      }
      $language['q'] = 1;
      preg_match('/q=([0-9.]+)/', $httpAcceptLanguage, $q);
      if (!empty($q[1])) {
        $language['q'] = $q[1];
      }
      $languages[] = $language;
    }
    $this->languages = $languages;
    return $this->languages;
  }
  /**
   * Parse a language and return language like: 'fr' or 'fr-BE'.
   *
   * @param array $language
   *
   * @return string
   */
  public function parseLanguage($language) {
    if (isset($language['RFC1766'])) {
      return $language['RFC1766'];
    }
    return $language['ISO639-1'];
  }
  /**
   * Return the HttpAcceptLanguage informations.
   *
   * @return array
   */
  public function getRawLanguages() {
    if (isset($this->languages)) {
      return $this->languages;
    }
  }
  /**
   * Return all available languages.
   *
   * @return array
   */
  public function getLanguages() {
    if (isset($this->languages) && is_array($this->languages)) {
      $languages = [];
      foreach ($this->languages as $language) {
        $languages[] = $this->parseLanguage($language);
      }
      return $languages;
    }
  }
  /**
   * Return only the first available language.
   *
   * @return string
   */
  public function getLanguage() {
    if (isset($this->languages[0])) {
      $lang = $this->languages[0];
      return $this->parseLanguage($lang);
    }
  }
}
class mtfLang {
  public $lang;
  public $usrLang;
  public function __construct($_lang) {
    if (is_array($_lang)) {
      $_ar = $_lang;
    } else {
      $_f = glob($_lang . '/*');
      $_l = count($_f);
      $_ar = array();
      if ($_l > 0) {
        for ($_i = 0; $_i < $_l; $_i++) {
          $_n = $_f[$_i];
          $_lan = stristr(substr(strrchr($_n, '/'), 1), '.', true);
          if (@$_ar[$_lan]) {
            $_ar[$_lan] = @array_merge($_ar[$_lan], json_decode(file_get_contents($_n), true));
          } else {
            $_ar[$_lan] = json_decode(file_get_contents($_n), true);
          }
        }
      }
    }
    $this->lang = $_ar;
  }
  public function rec() {
    $_ls = new HttpAcceptLanguage();
    $_ls = $_ls->getLanguage();

    if (is_array($_ls)) {
      foreach ($_ls as $_k => $_v) {
        if ($this->lang[$v]) {
          $this->usrLang = $_v;
        }
      }
    } else {
      $this->usrLang = $_ls ? $_ls : 'en';
    }
    return $this->usrLang;
  }
  public function get($_s, $_ar = array()) {
    if ($this->usrLang) {
      $_usrLang = $this->usrLang;
    } else {
      $_usrLang = $this->rec();
    }
    $_lang = $this->lang;

    $_a = array();
    $s = '';
    if (is_array($_s)) {
      $_a = $_s;
    } else {
      $_a[] = $_s;
    }
    $_l = count($_a);
    foreach ($_a as $_k => $__s) {
      $s .= @$_lang[$_usrLang][$__s] ? $_lang[$_usrLang][$__s] : $__s;
      if ($_usrLang !== 'zh-CN' && $_usrLang !== 'zh-TW' && $_k < $_l - 1) { //非中文，添加空格分隔符
        $s .= ' ';
      }
    }

    return $this->getRaw($s, $_ar);
  }
  public function getRaw($_s, $_ar = array()) {
    if ($_ar) {
      foreach ($_ar as $_k => $_v) {
        $_s = str_replace('$' . $_k, $_v, $_s);
      }
    }
    return $_s;
  }
}
?>