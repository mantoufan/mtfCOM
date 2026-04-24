<?php
class mtfHTTP {
  public function curl($_arv = array()) {
    $arv = array_merge(['u' => '', 't' => 6, 'p' => '', 'f' => '', 'h' => []], $_arv);
    $ch = curl_init();

    $opts = [
      CURLOPT_URL            => $arv['u'],
      CURLOPT_HEADER         => false,
      CURLOPT_NOBODY         => false,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => false,
      CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_ENCODING       => 'gzip',
    ];

    if ($arv['t'] < 1) {
      $opts[CURLOPT_NOSIGNAL]   = 1;
      $opts[CURLOPT_TIMEOUT_MS] = (int)($arv['t'] * 1000);
    } else {
      $opts[CURLOPT_TIMEOUT] = $arv['t'];
    }

    //兼容302CDN
    if ($arv['f']) {
      $opts[CURLOPT_MAXREDIRS]      = 2;
      $opts[CURLOPT_FOLLOWLOCATION] = true;
    }

    if (isset($_SERVER['HTTP_COOKIE'])) {
      $opts[CURLOPT_COOKIE] = $_SERVER['HTTP_COOKIE'];
    }

    if ($arv['p']) {
      $opts[CURLOPT_POST]       = 1;
      $opts[CURLOPT_POSTFIELDS] = http_build_query($arv['p']);
    }

    // Expect 减少一次 POST 预检，Accept-Encoding 请求 gzip 并解压
    $opts[CURLOPT_HTTPHEADER] = array_merge(
      ['Expect: ', 'Accept-Encoding:gzip', 'SERVER:' . json_encode($this->getFilteredServer())],
      $arv['h']
    );

    curl_setopt_array($ch, $opts);
    $_h = curl_exec($ch);
    return curl_errno($ch) ? 'error' : $_h;
  }

  public function getFilteredServer() {
    static $whitelist = ['HTTP' => 1, 'SERVER' => 1, 'REMOTE' => 1, 'REQUEST' => 1, 'QUERY' => 1, 'PHP' => 1];
    $server = [];
    foreach ($_SERVER as $k => $v) {
      $pos = strpos($k, '_');
      if ($pos !== false && isset($whitelist[substr($k, 0, $pos)])) {
        $server[$k] = $v;
      }
    }
    return $server;
  }

  // 保留旧拼写作为别名，向后兼容
  public function getFilteredSever() {
    return $this->getFilteredServer();
  }
}
