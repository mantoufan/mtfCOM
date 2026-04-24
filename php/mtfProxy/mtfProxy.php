<?php
class mtfProxy
{
    private $_root;
    private $RandUA;
    public $dir = array(
        'data' => 'data',
    );
    private $_count = 'ips_count.php';
    public $cacheTime = 1;

    public function __construct()
    {
        $this->_root = str_replace('\\', '/', dirname(__FILE__)) . '/';
        include $this->_root . '../RandUA/RandUA.php';
        $this->RandUA = new RandUA();
    }

    public function getRandIP()
    {
        static $arr_1 = ["218", "218", "66", "66", "218", "218", "60", "60", "202", "204", "66", "66", "66", "59", "61", "60", "222", "221", "66", "59", "60", "60", "66", "218", "218", "62", "63", "64", "66", "66", "122", "211"];
        static $arr_1_last = null;
        if ($arr_1_last === null) {
            $arr_1_last = count($arr_1) - 1;
        }
        $ip2id = round(rand(600000, 2550000) / 10000);
        $ip3id = round(rand(600000, 2550000) / 10000);
        $ip4id = round(rand(600000, 2550000) / 10000);
        $ip1id = $arr_1[mt_rand(0, $arr_1_last)];
        return $ip1id . '.' . $ip2id . '.' . $ip3id . '.' . $ip4id;
    }

    public function curl($_url, $_arv = array())
    {
        $_arv = array_merge(['timeout' => '3', 'ip' => '', 'referer' => '', 'out' => '', 'exec' => '1', 'post' => '', 'header' => '', 'fakeip' => '', 'ua' => ''], $_arv);
        $_ch = curl_init();

        $opts = [
            CURLOPT_URL            => $_url,
            CURLOPT_ENCODING       => 'gzip',
            CURLOPT_TIMEOUT        => $_arv['timeout'],
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS      => 2,
            CURLOPT_REFERER        => $_arv['referer'],
            CURLOPT_USERAGENT      => $_arv['ua'] === 'm' ? $this->RandUA->getMobile() : $this->RandUA->get(),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
        ];

        if ($_arv['out'] === 'header') {
            $opts[CURLOPT_HEADER] = true;
            $opts[CURLOPT_NOBODY] = true;
        } elseif ($_arv['out'] === 'body') {
            $opts[CURLOPT_HEADER] = false;
            $opts[CURLOPT_NOBODY] = false;
        } elseif ($_arv['out'] === 'all') {
            $opts[CURLOPT_HEADER] = true;
            $opts[CURLOPT_NOBODY] = false;
        }

        curl_setopt_array($_ch, $opts);

        $_header = [];
        if ($_arv['fakeip']) {
            $_ip = $this->getRandIP();
            $_header[] = 'X-FORWARDED-FOR:' . $_ip;
            $_header[] = 'CLIENT-IP:' . $_ip;
        }
        if ($_arv['header']) {
            $_header = array_merge($_header, $_arv['header']);
        } else {
            $_header[] = 'Accept:*/*';
            $_header[] = 'Accept-Language:zh-CN,zh;q=0.8';
            $_header[] = 'Accept-Encoding:gzip,deflate,sdch';
        }
        curl_setopt($_ch, CURLOPT_HTTPHEADER, $_header);

        if ($_arv['ip']) {
            curl_setopt($_ch, CURLOPT_PROXY, $_arv['ip']);
        }
        if ($_arv['post']) {
            curl_setopt($_ch, CURLOPT_POST, 1);
            curl_setopt($_ch, CURLOPT_POSTFIELDS, $_arv['post']);
        }

        return $_arv['exec'] ? curl_exec($_ch) : $_ch;
    }

    private function _get_ips($_arv)
    {
        $_arv = array_merge(['timeout' => '3', 'url' => '', 'regular' => '', 'page' => ['min' => 1, 'max' => 1], 'fakeip' => '1', 'referer' => ''], $_arv);
        $ips = [];
        for ($i = $_arv['page']['min']; $i <= $_arv['page']['max']; ++$i) {
            $_u = str_replace('{page}', $i, $_arv['url']);
            $_h = $this->curl($_u, ['timeout' => $_arv['timeout'], 'fakeip' => $_arv['fakeip'], 'out' => 'body', 'referer' => $_arv['referer']]);
            preg_match_all($_arv['regular'], $_h, $_m);
            $_ips   = $_m[1];
            $_ports = $_m[2];
            if ($_ips && $_ports) {
                foreach ($_ips as $_key => $_ip) {
                    $ips[] = $_ip . ':' . $_ports[$_key];
                }
            }
        }
        return $ips;
    }

    private function _get_hidemyass()
    { //0可用
        include $this->_root . 'hidemyass.class.php';
        $_list     = new ProxyList(array(array('a[]', 3))); //高匿名3-4级
        $_hidemyass = [];
        $_ar = $_list->get();
        if ($_ar) {
            foreach ($_ar as $_v) {
                if (is_numeric($_v['port'])) {
                    $_hidemyass[] = $_v['ip'] . ':' . $_v['port'];
                }
            }
        }
        return $_hidemyass;
    }

    private $regular = array(
        'FreeProxyList-All' => array(
            'url'     => 'http://free-proxy-list.net',
            'regular' => '/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
        ),
        'FreeProxyList-Anonymous' => array(
            'url'     => 'http://free-proxy-list.net/anonymous-proxy.html',
            'regular' => '/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
        ),
        'FreeProxyList-SSL' => array(
            'url'     => 'https://www.sslproxies.org/',
            'regular' => '/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
        ),
        'Xroxy-Transparent' => array(
            'url'     => 'http://www.xroxy.com/proxyrss.xml',
            'regular' => '/<prx:ip>((?:\d+\.){3}\d+)<\/prx:ip>[^<]*<prx:port>(\d+)<\/prx:port>[^<]*<prx:type>Transparent<\/prx:type>/',
        ),
        'Xroxy-Anonymous' => array(
            'url'     => 'http://www.xroxy.com/proxyrss.xml',
            'regular' => '/<prx:ip>((?:\d+\.){3}\d+)<\/prx:ip>[^<]*<prx:port>(\d+)<\/prx:port>[^<]*<prx:type>Anonymous<\/prx:type>/',
        ),
        'Hidester-All' => array(
            'url'     => 'https://hidester.com/proxydata/php/data.php?mykey=csv&gproxy=2',
            'regular' => '/{"IP":"((?:\d+\.){3}\d+)","PORT":(\d+),"latest_check".*?http/',
            'timeout' => 5,
            'referer' => 'https://hidester.com/',
        ),
        'Hidester-Anonymous' => array(
            'url'     => 'https://hidester.com/proxydata/php/data.php?mykey=csv&gproxy=2',
            'regular' => '/{"IP":"((?:\d+\.){3}\d+)","PORT":(\d+),"latest_check".*?Anonymous.*?http/',
            'timeout' => 5,
            'referer' => 'https://hidester.com/',
        ),
        'KuaiDaiLi-Transparent' => array(
            'url'     => 'http://www.kuaidaili.com/free/intr/1/',
            'regular' => '/<td data-title=\"IP\">((?:\d+\.){3}\d+)<\/td>[^<]*<td data-title=\"PORT\">(\d+)<\/td>/',
        ),
        'KuaiDaiLi-Anonymous' => array(
            'url'     => 'http://www.kuaidaili.com/free/inha/1/',
            'regular' => '/<td data-title=\"IP\">((?:\d+\.){3}\d+)<\/td>[^<]*<td data-title=\"PORT\">(\d+)<\/td>/',
        ),
        'IPAdress' => array(
            'url'     => 'https://www.ip-adress.com/proxy_list/',
            'regular' => '/>((?:\d+\.){3}\d+)<\/a>:(\d+)/',
        ),
        'ProxyListPlus' => array(
            'url'     => 'https://list.proxylistplus.com/Fresh-HTTP-Proxy-List-{page}',
            'regular' => '/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
            'page'    => ['min' => 1, 'max' => 3],
        ),
    );

    public function update($_iptype = 'all', $_arv = array())
    {
        $_arv  = array_merge(['check' => false], $_arv);
        ignore_user_abort(true);
        set_time_limit(120);
        $_data = $this->dir['data'] . 'ips_' . $_iptype . '.php';
        $_lock = $this->dir['data'] . 'ips_' . $_iptype . '.lock';
        file_put_contents($_lock, '1', LOCK_EX);

        if (!empty($_arv['remote'])) {
            $ips = $this->_get_ips(['url' => $_arv['remote'] . '&ip_type=' . ($_iptype === 'key' ? 'all' : $_iptype), 'regular' => '/((?:\d+\.){3}\d+):(\d+)/']);
            if ($_iptype === 'key') {
                $_arv['check'] = true;
            }
        } else {
            switch ($_iptype) {
                case 'all':
                    $ips = array_merge(
                        $this->_get_ips($this->regular['FreeProxyList-All']),
                        $this->_get_ips($this->regular['Xroxy-Transparent']),
                        $this->_get_ips($this->regular['IPAdress']),
                        $this->_get_ips($this->regular['ProxyListPlus']),
                        $this->_get_ips($this->regular['Hidester-All']),
                        $this->_get_ips($this->regular['KuaiDaiLi-Anonymous']),
                        $this->_get_ips($this->regular['KuaiDaiLi-Transparent'])
                    );
                    break;
                case 'anonymous':
                    $ips = array_merge(
                        $this->_get_ips($this->regular['FreeProxyList-Anonymous']),
                        $this->_get_ips($this->regular['Xroxy-Anonymous']),
                        $this->_get_ips($this->regular['Hidester-Anonymous']),
                        $this->_get_ips($this->regular['KuaiDaiLi-Anonymous'])
                    );
                    break;
                case 'key':
                case 'available':
                    $ips = array_merge(
                        $this->_get_ips($this->regular['FreeProxyList-All']),
                        $this->_get_ips($this->regular['Xroxy-Transparent']),
                        $this->_get_ips($this->regular['Xroxy-Anonymous']),
                        $this->_get_ips($this->regular['IPAdress']),
                        $this->_get_ips($this->regular['ProxyListPlus']),
                        $this->_get_ips($this->regular['Hidester-All'])
                    );
                    $_arv['check'] = true;
                    break;
                case 'ssl':
                    $ips = $this->_get_ips($this->regular['FreeProxyList-SSL']);
                    break;
                case 'Hidemyass-Anonymous':
                    $ips = $this->_get_hidemyass();
                    break;
                default:
                    $ips = $this->_get_ips($this->regular[$_iptype]);
            }
            $ips = array_unique($ips);
        }

        if ($_arv['check']) {
            $ips = $this->check($ips);
        }
        $ips    = array_values($ips);
        $_count = count($ips) - 1;
        if ($_count > 10) {
            $ips_count = [];
            if (is_file($this->_count)) {
                include $this->_count;
            }
            $ips_count[$_iptype] = $_count;
            file_put_contents($this->dir['data'] . $this->_count, "<?php\n\$ips_count=" . var_export($ips_count, true) . "\n?>", LOCK_EX);
            file_put_contents($_data, "<?php\n\$ips=" . var_export($ips, true) . "\n?>", LOCK_EX);
        }
        unlink($_lock);
        return $ips;
    }

    public function get($_iptype = 'all', $_arv = array())
    {
        $_arv             = array_merge(['html' => false, 'check' => false], $_arv);
        $this->dir['data'] = $this->dir['data'] . '/';
        if (!is_dir($this->dir['data'])) {
            mkdir($this->dir['data']);
        }
        $this->_count = $this->dir['data'] . $this->_count;

        $_data = $this->dir['data'] . 'ips_' . $_iptype . '.php';
        if (file_exists($_data) && time() - filemtime($_data) < $this->cacheTime) {
            include $_data;
        } else {
            $ips = $this->update($_iptype, ['check' => $_arv['check'], 'remote' => $_arv['remote'] ?? null]);
            if (count($ips) < 10) {
                include $_data;
            }
        }
        return $_arv['html'] ? '<pre>' . implode("\r\n", $ips) . '</pre>' : $ips;
    }

    public function check($_ips, $_arv = array('url' => 'http://www.baidu.com', 'html' => 'www.baidu.com/img/sug_bd.png'))
    {
        $mh      = curl_multi_init();
        $handles = [];

        foreach ($_ips as $_k => $_ip) {
            $handles[$_k] = $this->curl($_arv['url'], ['ip' => $_ip, 'timeout' => 6, 'out' => 'body', 'exec' => 0]);
            curl_multi_add_handle($mh, $handles[$_k]);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        $ips = [];
        foreach ($_ips as $_k => $_ip) {
            $_h = curl_multi_getcontent($handles[$_k]);
            if ($_h && stristr($_h, $_arv['html'])) {
                $ips[] = $_ip;
            }
            curl_close($handles[$_k]);
            curl_multi_remove_handle($mh, $handles[$_k]);
        }
        curl_multi_close($mh);
        return $ips;
    }
}
