<?php
class mtfTcn{
	public $appkey='3271760578';
	public $w3key='5d890122d046967d9db9b495@68257acd1186a78a0701421463eb8234';
	public function __construct()
    {
		define('SINA_APPKEY', $this->appkey);
		define('w3_APPKEY', $this->w3key);
	}
	private function _curl($url, $header = null, $post = null)
    {
		//初始化curl，当然，你也可以用fsockopen代替  
		$ch = curl_init();  
		//设置网址  
		curl_setopt($ch, CURLOPT_URL, $url);  
		//附加Head内容  
		if ($header !== null) {
		  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		}  
		//是否输出返回头信息  
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		//将curl_exec的结果返回  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		//设置超时时间 3秒
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		
		//加速curl
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 600);//DNS缓存时间，改为10分钟

		if ($post !== null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		
		//执行  
		$result = curl_exec($ch);  
		//关闭curl回话  
		curl_close($ch);  
		return $result;  
	}
	//简单处理下url，sina对于没有协议(http://)开头的和不规范的地址会返回错误  
	private function _filterUrl($url) {  
		$url = trim($url); 
		$prev_ar = explode('//', $url);
		$url = trim(preg_replace('/^(http[s]?\:)?\/\//', '', $url));  
		if ($url == ''){
			return FALSE;
		}	  
		else
		{
			if(substr($prev_ar[0], 4, 1) === 's') {
				$prev_ar[0] = 'https';
			} else {
				$prev_ar[0] = 'http';
			}
			return urlencode($prev_ar[0] . '://' . $url);  
		}	
	}  
	//根据长网址获取短网址  
	public function short($long_url) {
		$url = 'https://api.xiaomark.com/v1/link/create'; // 小马短链接 
		$result = $this->_curl($url, array(  
			'content-type: application/json'
		), json_encode(array(
			'apikey' => '689a5b4bfa3a492e1874c0ad5e171b75', // 自己去申请 key ，每天 50 条，不用用我的 ^_^
			'origin_url' => $long_url
		)));
    $json = json_decode($result);
		if ($json->code !== 0) return FALSE;
		return $json->data->link->url;
	}  
	//根据短网址获取长网址，此函数重用了不少sinaShortenUrl中的代码，以方便你阅读对比，你可以自行合并两个函数  
	public function expand($short_url) {  
		//拼接请求地址，此地址你可以在官方的文档中查看到  
		$url = 'http://api.t.sina.com.cn/short_url/expand.json?source=' . SINA_APPKEY .'&url_short=' . $short_url;  
		//获取请求结果  
		$result = $this->_curl($url);  
		//下面这行注释用于调试，你可以把注释去掉看看从sina返回的信息是什么东西  
		//print_r($result);exit();  
		//解析json  
		$json = json_decode($result);  
		//异常情况返回false  
		if (isset($json->error) || !isset($json[0]->url_long) || $json[0]->url_long =='')  
			return false;  
		else  
			return $json[0]->url_long;  
	}  
}
?>