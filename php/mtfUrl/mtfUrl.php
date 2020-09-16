<?php
class mtfUrl{
	private $_root;
	
	public function __construct($key='')
    {	
		$_root=str_replace('\\','/',dirname(__file__)).'/';
	}
	function isUrl( $url ) {
		if ( !trim( $url ) )
			return false;

		if ( strlen( $url ) < 10 )
			return false;

		$pattern = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iuS';

		$result = preg_match( $pattern, $url );
		$result = (bool) $result;

		return $result;
	}
	public function string2url($_s){
		$_urls=array();
		$_a=preg_split('/(?<!=)http(?!s%3|%3)/',str_replace('%3D','==========',preg_replace("/<(.*?)>/", ' ',$_s)));
		//'%3D'=>'==========' 适用 http://g.click.taobao.com/q?ak=12032034&pid=mm_15952905_4134881_13422903&unid=798048&rf=http%3A%2F%2Fwww.zuanke8.com%2Fthread-4508974-1-1.html&rd=2&et=31714212&pgid=cba1b86d7d3950146cb16e5aa3669d09&ct=url%3Dhttps%253A%252F%252Ffavorite.taobao.com%252Fadd_collection.htm%253Fitemtype%253D0%2526id%253D33930249&v=1.2&ttype=1&cm=961%2C950%3B1572%2C567%3B888%2C895%3B965%2C1008%3B1420%2C568%3B1543%2C568%3B939%2C977%3B945%2C1069%3B1462%2C583%3B812%2C812&ck=&cw=14  
		
		foreach ($_a as $_k => $_u) {
			$_u='http'.$_u;
			$_ar=explode(' ',$_u);
			$_u=$_ar[0];
			unset($_ar);
			$_u=trim(preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~","\\0",$_u));

			//换行符
			$_ar=explode("\r",$_u);
			$_u=$_ar[0];
			$_ar=explode("\n",$_u);
			$_u=trim(trim($_ar[0]),' ');//网址后，空格回车中的空格
			
			//$_ar=explode('#',$_u);
			//$_ar[0]=str_replace(' ','',str_replace('&nbsp;','',$_ar[0]));
			
			if($this->isUrl($_u)){
				$_urls[]=str_replace('==========','%3D',$_u);
			}
		}
		return $_urls;
	}
	
	public function url2string($_u,$_r=1,$_q=array()){
		if($_r){
			$_u=$this->getRedirectUrl($_u);
		}
		$_a=parse_url($_u);
		$host=$_a['host'];
		$_ar=explode('.',$host);
		if(count($_ar)<=2){
			$domain=ucfirst($_ar[0]);	
		}else{
			$name=$_ar[0]==='www'?'':ucfirst($_ar[0]);
			$domain=ucfirst($_ar[1]);
		}
		$_ar=explode('-',$name);
		$name=$_ar[0];
		
		$path=$_a['path'];
		$_ar=explode('/',$path);
		$_ar=array_reverse($_ar);
		$_ar[0]=substr(strpos($_ar[0],'.')?stristr($_ar[0],'.',true):$_ar[0],0,6);
		
		$_bn=ucfirst($_ar[0]);
		
		if(!$name){
			$name=$_bn;
		}
		if($name===$_bn){
			array_splice($_ar,0,1);	
		}
		
		array_pop($_ar);
		$_ar[0]=ucfirst($_ar[0]);
		$path=implode('-',$_ar);
		$fragment=$_a['fragment'];
		$query=$_a['query'];
		parse_str($query,$_a);
		$_ar=array();
		foreach($_a as $_k=>$_v){
			if($_q){
				if(isset($_q[$_k])){
					$_ar[]=$_q[$_k].':'.mb_substr(urldecode($_v),0,20);
				}	
			}else{
				$_ar[]=$_k.':'.mb_substr(urldecode($_v),0,20);
			}
		}
		$query=implode(',',$_ar);
		return $name.' '.$path.' '.$domain.' '.$query.($fragment?'#'.$fragment:'');
	}
	
	public function getRawUrl($_u) {
		$_html='';
		$_maxredirs=0;
		$_post='';
		
		if(stripos($_u,'dwz.cn')!==FALSE){//国外需使用ssl
			$_u=str_replace('http://','https://',$_u);
		}elseif(stripos($_u,'t.im')!==FALSE){
			$_post=array('m'=>1);
		}
		
		$_ch = curl_init($_u);
		//curl_setopt($_ch, CURLOPT_ENCODING, 'gzip');
		curl_setopt($_ch, CURLOPT_HEADER, TRUE); //输出header
		curl_setopt($_ch, CURLOPT_NOBODY, FALSE);//必须输出body（例如dwz.cn）
		curl_setopt($_ch, CURLOPT_RETURNTRANSFER, TRUE);// 禁止自动输出内容
		curl_setopt($_ch, CURLOPT_FOLLOWLOCATION, $_maxredirs?TRUE:FALSE);// 自动跳转 
		curl_setopt($_ch, CURLOPT_MAXREDIRS,$_maxredirs);
		curl_setopt($_ch, CURLOPT_AUTOREFERER, TRUE);// 跳转时自动设置来源地址
		curl_setopt($_ch, CURLOPT_URL, $_u);// 设置URL
		curl_setopt($_ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($_ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($_ch, CURLOPT_REFERER, '');//设置来源
		curl_setopt($_ch, CURLOPT_TIMEOUT, 2);//2秒超时
		curl_setopt($_ch,CURLOPT_USERAGENT,'Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12A365 Safari/600.1.4');//（适应url.163.com）
		if($_post){
			curl_setopt($_ch, CURLOPT_POST, 1);
			curl_setopt($_ch, CURLOPT_POSTFIELDS, $_post);
		}
		$_html = curl_exec($_ch);
		preg_match_all('/^Location:(.*)$/mi', $_html, $_matches);
		curl_close($_ch);
		$_raw=!empty($_matches[1]) ? trim($_matches[1][0]) : '';
		
		if(!$_raw){
			if(stripos($_u,'t.im')!==FALSE){
				include_once($this->_root.'../../mtf/QueryList/autoload.php');
				$_data=QL\QueryList::Query($_html,array(
				'url' => array('.panel-body>p>a','text')
				))->data;
				$_raw=$_data[0]['url'];
			}
		}
		
		return $_raw;
	}
}
?>