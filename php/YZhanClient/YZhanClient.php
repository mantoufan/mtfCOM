<?php
include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/../mtfGuid/mtfGuid.php';

use YZhanIP\Tool\IPTracer;

class YZhanClient {
  const GOOGLEBOTURL = 'https://api.os120.com/url/proxy?url=https://developers.google.com/static/search/apis/ipranges/googlebot.json&out_type=txt&des=yzhan';
  public $ip;
  public function __construct() {
    $this->ip = (new mtfGuid())->ip();
  }
  public function isGoogleBot() {
    return strpos($_SERVER['HTTP_USER_AGENT'], 'googlebot') !== false && IPTracer::IsInUrl($this->ip, array(self::GOOGLEBOTURL));
  }
}
?>