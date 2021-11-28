<?php
class mtfProxy{
	private $_root;
	private $RandUA;
	public $dir=array(
		'data'=>'data'
	);
	private $_count='ips_count.php';
	public $cacheTime=1;
	
	public function __construct()
    {	
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		include($this->_root.'../RandUA/RandUA.php');
		$this->RandUA=new RandUA();
	}
	public function getRandIP()
	{
		$ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
		$ip3id= round(rand(600000, 2550000) / 10000);
		$ip4id= round(rand(600000, 2550000) / 10000);
		//下面是第二种方法，在以下数据中随机抽取
		$arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
		$randarr= mt_rand(0,count($arr_1)-1);
		$ip1id = $arr_1[$randarr];
		return $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
	}
	
	public function curl($_url, $_arv=array()) {
		$_arv=array_merge(array('timeout'=>'3','ip'=>'','referer'=>'','out'=>'','exec'=>'1','post'=>'','header'=>'','fakeip'=>'','ua'=>''),$_arv);
		$_ch = curl_init();
		curl_setopt($_ch, CURLOPT_URL, $_url);
		if($_arv['out']==='header'){
			curl_setopt($_ch, CURLOPT_HEADER, TRUE);
			curl_setopt($_ch, CURLOPT_NOBODY, TRUE);
		}elseif($_arv['out']==='body'){
			curl_setopt($_ch, CURLOPT_HEADER, FALSE);
			curl_setopt($_ch, CURLOPT_NOBODY, FALSE);
		}elseif($_arv['out']==='all'){
			curl_setopt($_ch, CURLOPT_HEADER, TRUE);
			curl_setopt($_ch, CURLOPT_NOBODY, FALSE);
		}
		curl_setopt($_ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($_ch, CURLOPT_TIMEOUT, $_arv['timeout']);
		curl_setopt($_ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($_ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($_ch, CURLOPT_MAXREDIRS,2);
		curl_setopt($_ch, CURLOPT_REFERER, $_arv['referer']);
		curl_setopt($_ch, CURLOPT_USERAGENT, $_arv['ua']=='m'?$this->RandUA->getMobile():$this->RandUA->get());
		curl_setopt($_ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($_ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		@curl_setopt($_ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		
		$_header=array();
		if($_arv['fakeip']){
			$_ip=$this->getRandIP();
			$_header[]='X-FORWARDED-FOR:'.$_ip;
			$_header[]='CLIENT-IP:'.$_ip;
		}
		if($_arv['header']){
			$_header=array_merge($_header,$_arv['header']);
		}else{
			$_header[]='Accept:*/*';
			$_header[]='Accept-Language:zh-CN,zh;q=0.8';
			$_header[]='Accept-Encoding:gzip,deflate,sdch';
		}
		
		curl_setopt($_ch, CURLOPT_HTTPHEADER, $_header);
		
		if($_arv['ip']){
			curl_setopt($_ch, CURLOPT_PROXY, $_arv['ip']);
		}
		if($_arv['post']){
			curl_setopt($_ch, CURLOPT_POST, 1);
			curl_setopt($_ch, CURLOPT_POSTFIELDS, $_arv['post']);
		}
		if($_arv['exec']){
			return curl_exec($_ch);
		}else{
			return $_ch;
		}
	}
	private function _get_ips($_arv) {
		$_arv=array_merge(array('timeout'=>5, 'url'=>'','regular'=>'','page'=>array('min'=>1,'max'=>1),'timeout'=>'3','fakeip'=>'1','referer'=>''),$_arv);
		$ips = array();
		for ($i = $_arv['page']['min']; $i <= $_arv['page']['max']; ++$i) {
			$_u = str_replace('{page}',$i,$_arv['url']);
			$_h = $this->curl($_u,array('timeout'=>$_arv['timeout'],'fakeip'=>$_arv['fakeip'],'out'=>'body','referer'=>$_arv['referer']));
			preg_match_all($_arv['regular'], $_h, $_m);
			$_ips = $_m[1];
			$_ports = $_m[2];
			if ($_ips && $_ports) {
				foreach ($_ips as $_key => $_ip) {
					array_push($ips, $_ip.':'.$_ports[$_key]);
				}
			}
		}
		return $ips;
	}
	
	private function _get_hidemyass(){//0可用
		//https://github.com/mkbodanu4/hidemyass-proxies-scraper-php
		//来源：http://proxylist.hidemyass.com/
		include($this->_root.'hidemyass.class.php');
		$_list = new ProxyList(array(array('a[]', 3)));//高匿名3-4级
		$_hidemyass=array();
		$_ar=$_list->get();
		if($_ar){
			foreach($_ar as $_k=>$_v){
				if(is_numeric($_v['port'])){
					$_hidemyass[]=$_v['ip'].':'.$_v['port'];
				}
			}
		}
		return $_hidemyass;
	}
	
	private $regular=array(
		// 'FreeProxyList-All'=>array(// 45:300 2020年8月12日 服务器上打不开
		// 	'url'=>'http://free-proxy-list.net',
		// 	'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/'
		// ),
		// 'FreeProxyList-Anonymous'=>array(// 9:- 2020年8月12日 服务器上打不开
		// 	'url'=>'http://free-proxy-list.net/anonymous-proxy.html',
		// 	'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>.*?<td>anonymous<\/td>/'
		// ),
		// 'FreeProxyList-SSL'=>array(// 14:99 2020年8月12日 服务器上打不开
		// 	'url'=>'https://www.sslproxies.org/',
		// 	'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/'
		// ),
		// 'Xroxy-All'=>array(// 8:24 2020年8月12日 服务器上打不开
		// 	'url'=>'http://www.xroxy.com/proxyrss.xml',
		// 	'regular'=>'/<prx:ip>((?:\d+\.){3}\d+)<\/prx:ip>[^<]*<prx:port>(\d+)<\/prx:port>/'
		// ),
		// 'Xroxy-Anonymous'=>array(// 3:- 2020年8月12日 服务器上打不开
		// 	'url'=>'http://www.xroxy.com/proxyrss.xml',
		// 	'regular'=>'/<prx:ip>((?:\d+\.){3}\d+)<\/prx:ip>[^<]*<prx:port>(\d+)<\/prx:port>[^<]*<prx:type>Anonymous<\/prx:type>/'
		// ),
		'Hidester-All'=>array(//48:786
			'url'=>'https://hidester.com/proxydata/php/data.php?mykey=csv&gproxy=2',
			'regular'=>'/{"IP":"((?:\d+\.){3}\d+)","PORT":(\d+),"latest_check".*?http/',
			'timeout'=>5,
			'referer'=>'https://hidester.com/'
		),
		'Hidester-Anonymous'=>array(// 21:117 2020年8月12日打不开
			'url'=>'https://hidester.com/proxydata/php/data.php?mykey=csv&gproxy=2',
			'regular'=>'/{"IP":"((?:\d+\.){3}\d+)","PORT":(\d+),"latest_check".*?Anonymous.*?http/',
			'timeout'=>5,
			'referer'=>'https://hidester.com/'
		),
		'KuaiDaiLi-Transparent'=>array(// 2:13
			'url'=>'http://www.kuaidaili.com/free/intr/1/',
			'regular'=>'/<td data-title=\"IP\">((?:\d+\.){3}\d+)<\/td>[^<]*<td data-title=\"PORT\">(\d+)<\/td>/'
		),
		'KuaiDaiLi-Anonymous'=>array(// 2:14
			'url'=>'http://www.kuaidaili.com/free/inha/1/',
			'regular'=>'/<td data-title=\"IP\">((?:\d+\.){3}\d+)<\/td>[^<]*<td data-title=\"PORT\">(\d+)<\/td>/'
		),
		// 'PAY-KuaiDaiLi-ALL'=>array(//0可用 17可访问 999总数
		// 	'url'=>'http://dev.kuaidaili.com/api/getproxy/?orderid=918972498655926&num=999&b_pcchrome=1&b_pcie=1&b_pcff=1&protocol=1&method=2&an_an=1&an_ha=1&sep=1',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/'
		// ),
		// 'PAY-XiCiDaiLi-ALL'=>array(//0可用 17可访问 3401总数
		// 	'url'=>'http://tvp.daxiangdaili.com/ip/?tid=555471942291896&num=5000&sortby=time',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'timeout'=>6
		// ),
		// 'PAY-XiCiDaiLi-Anonymous'=>array(//0可用 17可访问 2000总数
		// 	'url'=>'http://tvp.daxiangdaili.com/ip/?tid=555471942291896&num=5000&category=2&sortby=time',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'timeout'=>6
		// ),
		// 'PAY-XiCiDaiLi-SSL'=>array(//0可用 17可访问 2000总数
		// 	'url'=>'http://tvp.daxiangdaili.com/ip/?tid=555471942291896&num=5000&sortby=time&protocol=https',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'timeout'=>6
		// ),
		// 'XiCiDaiLi-All'=>array(//0可用 17可访问 99总数 Block IP
		// 	'url'=>'http://www.xicidaili.com/wt/{page}',
		// 	'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
		// 	'fakeip'=>0,
		// 	'page'=>array(
		// 		'min'=>1,
		// 		'max'=>1
		// 	)
		// ),
		// 'XiCiDaiLi-Anonymous'=>array(//0可用 14可访问 99总数 Block IP
		// 	'url'=>'http://www.xicidaili.com/nn/{page}',
		// 	'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
		// 	'fakeip'=>0,
		// 	'page'=>array(
		// 		'min'=>1,
		// 		'max'=>1
		// 	)
		// ),
		// 'XiCiDaiLi-SSL'=>array(//99总数 Block IP
		// 	'url'=>'http://www.xicidaili.com/wn/{page}',
		// 	'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
		// 	'fakeip'=>0,
		// 	'page'=>array(
		// 		'min'=>1,
		// 		'max'=>1
		// 	)
		// ),
		'IPAdress'=>array(// 17:50
			'url'=>'https://www.ip-adress.com/proxy_list/',
			'regular'=>'/>((?:\d+\.){3}\d+)<\/a>:(\d+)/'
		),
		'ProxyListPlus'=>array(// 0:100
			'url'=>'https://list.proxylistplus.com/Fresh-HTTP-Proxy-List-{page}',
			'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
			'page'=>array(
				'min'=>1,
				'max'=>3
			)
		),
		// 'PAY-ZHANDAYE-All'=>array(//2000总数
		// 	'url'=>'http://api.zdaye.com/?api=201705241200122138&rtype=1&pw=1991&ct=3000',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'fakeip'=>0
		// ),
		// 'PAY-ZHANDAYE-SSL'=>array(//1423总数
		// 	'url'=>'http://api.zdaye.com/?api=201705241200122138&https=1&rtype=1&pw=1991&ct=3000',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'fakeip'=>0
		// ),
		// 'PAY-ZHANDAYE-Anonymous'=>array(//2000总数
		// 	'url'=>'http://api.zdaye.com/?api=201705241200122138&dengji=3&rtype=1&pw=1991&ct=3000',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'fakeip'=>0
		// ),
		// '66IP-ALL'=>array(//2300总数-500错误，国内能打开
		// 	'url'=>'http://www.66ip.cn/mo.php?sxb=&tqsl=5000',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'fakeip'=>0
		// ),
		// 'PAY-DATA5U-ALL'=>array(//1657总数
		// 	'url'=>'http://api.ip.data5u.com/api/get.shtml?order=c242e4b75a74c0148df8a22d34c7abf9&num=10000&carrier=0&protocol=0&an1=1&an2=2&an3=3&sp1=1&sp2=2&sp3=3&sort=1&system=1&distinct=0&rettype=1&seprator=%0D%0A',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'fakeip'=>0
		// ),
		// 'PAY-DATA5U-Anonymous'=>array(//911总数
		// 	'url'=>'http://api.ip.data5u.com/api/get.shtml?order=c242e4b75a74c0148df8a22d34c7abf9&num=10000&carrier=0&protocol=0&an1=1&an2=2&sp1=1&sp2=2&sp3=3&sort=1&system=1&distinct=0&rettype=1&seprator=%0D%0A',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'fakeip'=>0
		// ),
		// 'PAY-DATA5U-SSL'=>array(//168总数
		// 	'url'=>'http://api.ip.data5u.com/api/get.shtml?order=c242e4b75a74c0148df8a22d34c7abf9&num=10000&carrier=0&protocol=2&an1=1&an2=2&an3=3&sp1=1&sp2=2&sp3=3&sort=1&system=1&distinct=0&rettype=1&seprator=%0D%0A',
		// 	'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
		// 	'fakeip'=>0
		// ),
		'CN-PROXY-ALL'=>array(// 65:70
			'url'=>'http://cn-proxy.com/',
			'regular'=>'/<td>((?:\d+\.){3}\d+)<\/td>[^<]*<td>(\d+)<\/td>/',
			'fakeip'=>0
		),
		'XILADAILI-Transparent'=>array(// 26:100
			'url'=>'http://www.xiladaili.com/http/{page}/',
			'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
			'fakeip'=>0,
			'page'=>array(
				'min'=>1,
				'max'=>2
			)
		),
		'XILADAILI-SSL'=>array(// 34:100
			'url'=>'http://www.xiladaili.com/https/{page}/',
			'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
			'fakeip'=>0,
			'page'=>array(
				'min'=>1,
				'max'=>2
			)
		),
		'XILADAILI-Anonymous'=>array(// 34:100
			'url'=>'http://www.xiladaili.com/gaoni/{page}/',
			'regular'=>'/((?:\d+\.){3}\d+):(\d+)/',
			'fakeip'=>0,
			'page'=>array(
				'min'=>1,
				'max'=>2
			)
		),
		'PAY-XILADAILI-ALL'=>array(// 34:100
			'url'=>'http://www.xiladaili.com/api/?uuid=94ca9df6b6b0463ca061545f36cb0a02&num=5000&place=中国&protocol=0&sortby=2&repeat=1&format=4&position=1',
			'regular'=>'/((?:\d+\.){3}\d+):(\d+)/'
		),
		'PAY-XILADAILI-Anonymous'=>array(// 34:100
			'url'=>'http://www.xiladaili.com/api/?uuid=94ca9df6b6b0463ca061545f36cb0a02&num=5000&place=中国&category=1&protocol=0&sortby=2&repeat=1&format=4&position=1',
			'regular'=>'/((?:\d+\.){3}\d+):(\d+)/'
		),
		'PAY-XILADAILI-SSL'=>array(// 34:100
			'url'=>'http://www.xiladaili.com/api/?uuid=94ca9df6b6b0463ca061545f36cb0a02&num=5000&place=中国&protocol=2&sortby=2&repeat=1&format=4&position=1',
			'regular'=>'/((?:\d+\.){3}\d+):(\d+)/'
		)
	);
	
	public function update($_iptype='all',$_arv=array()){
		$_arv=array_merge(array('check'=>false),$_arv);
		ignore_user_abort(TRUE); 
		set_time_limit(120);
		$_data=$this->dir['data'].'ips_'.$_iptype.'.php';
		$_lock=$this->dir['data'].'ips_'.$_iptype.'.lock';
		file_put_contents($_lock,"1",LOCK_EX);
		if(@$_arv['remote']){
			$ips=$this->_get_ips(array('url'=>$_arv['remote'].'&ip_type='.($_iptype==='key'?'all':$_iptype),'regular'=>'/((?:\d+\.){3}\d+):(\d+)/'));
			if($iptype==='key'){
				$_arv['check']=true;
			}
		}else{
			switch($_iptype){
				case 'all'://全部
					$ips=array_merge(
						// $this->_get_ips($this->regular['FreeProxyList-All']),
						// $this->_get_ips($this->regular['Xroxy-All']),
						$this->_get_ips($this->regular['IPAdress']),
						$this->_get_ips($this->regular['ProxyListPlus']),
						$this->_get_ips($this->regular['Hidester-All']),
						$this->_get_ips($this->regular['KuaiDaiLi-Anonymous']),
						$this->_get_ips($this->regular['KuaiDaiLi-Transparent']),
						$this->_get_ips($this->regular['CN-PROXY-ALL']),
						$this->_get_ips($this->regular['XILADAILI-Transparent']),
						$this->_get_ips($this->regular['PAY-XILADAILI-ALL'])
					);
					break;
				case 'anonymous'://匿名
					$ips=array_merge(
						// $this->_get_ips($this->regular['FreeProxyList-Anonymous']),
						// $this->_get_ips($this->regular['Xroxy-Anonymous']),
						$this->_get_ips($this->regular['Hidester-Anonymous']),
						$this->_get_ips($this->regular['KuaiDaiLi-Anonymous']),
						$this->_get_ips($this->regular['XILADAILI-Anonymous']),
						$this->_get_ips($this->regular['PAY-XILADAILI-Anonymous'])
					);
					break;           
				case 'key'://可用（兼容老版本）
				case 'available'://可用
					$ips=array_merge(
						// $this->_get_ips($this->regular['FreeProxyList-All']),
						// $this->_get_ips($this->regular['Xroxy-All']),
						$this->_get_ips($this->regular['IPAdress']),
						$this->_get_ips($this->regular['ProxyListPlus']),
						$this->_get_ips($this->regular['Hidester-All']),
						$this->_get_ips($this->regular['CN-PROXY-ALL']),
						$this->_get_ips($this->regular['XILADAILI-Transparent']),
						$this->_get_ips($this->regular['PAY-XILADAILI-ALL'])
					);
					$_arv['check']=true;
					break;
				case 'ssl'://安全
					$ips=array_merge(
						// $this->_get_ips($this->regular['FreeProxyList-SSL'])
						$this->_get_ips($this->regular['XILADAILI-SSL']),
						$this->_get_ips($this->regular['PAY-XILADAILI-SSL'])
					);
					break;
				case 'Hidemyass-Anonymous':
					$ips=$this->_get_hidemyass();
				default:
					$ips=$this->_get_ips($this->regular[$_iptype]);
				;
			};
			$ips=array_unique($ips);
		}
		
		if($_arv['check']){
			$ips=$this->check($ips);
		}
		$ips=array_values($ips);
		end($ips);
		$_count=key($ips);
		if($_count>10){
			@include($this->_count);
			@$ips_count[$_iptype]=$_count;
			file_put_contents($this->dir['data'] . $this->_count,"<?php\n\$ips_count=".var_export(@$ips_count, true)."\n?>",LOCK_EX);
			file_put_contents($_data,"<?php\n\$ips=".var_export($ips, true)."\n?>",LOCK_EX);
		}
		unlink($_lock);	
		return $ips;
		
	}
	
	public function get($_iptype='all',$_arv=array()){
		$_arv=array_merge(array('html'=>false,'check'=>false),$_arv);
		$this->dir['data'] = $this->dir['data'].'/';
		if(!is_dir($this->dir['data'])){
			mkdir($this->dir['data']);	
		}
		$this->_count=$this->dir['data'].$this->_count;
		
		$_data=$this->dir['data'].'ips_'.$_iptype.'.php';
		if(file_exists($_data) && time()-filemtime($_data)<$this->cacheTime){
			include($_data);	
		}else{
			$ips=$this->update($_iptype,array('check'=>$_arv['check'],'remote'=>@$_arv['remote']));
			if(count($ips)<10){
				include($_data);	
			}
		}
		return $_arv['html']?'<pre>'.implode("\r\n",$ips).'</pre>':$ips;
	}
	
	public function check($_ips,$_arv=array('url'=>'http://www.baidu.com','html'=>'www.baidu.com/img/sug_bd.png')){
		$mh=curl_multi_init();
		$handles=array();
		
		foreach($_ips as $_k=>$_ip){
			$handles[$_k]=$this->curl($_arv['url'],array('ip'=>$_ip,'timeout'=>6,'out'=>'body','exec'=>0));
			curl_multi_add_handle($mh,$handles[$_k]);
		}
		$running=null;
		do {
			curl_multi_exec($mh,$running);
			curl_multi_select($mh);//优化CPU占用
		} while ($running > 0);
			foreach($_ips as $_k=>$_ip){
				$_h=curl_multi_getcontent($handles[$_k]);
				if($_h){
					if(stristr($_h,$_arv['html'])){
						$ips[]=$_ip;
					}
				}
				curl_close($handles[$_k]);
				curl_multi_remove_handle($mh,$handles[$_k]); 
			}
		curl_multi_close($mh);
		return $ips;
	}
	
}

?>