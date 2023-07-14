<?php
class mtfUnit{
	//String
	public function subStr($_s,$_limit,$dot='')
	{
		$_l=$this->strLen($_s);
		if($_l>$_limit)
		{
			$_s=rtrim(mb_strcut($_s,0,$_limit),'.').$dot;
		}
		return $_s;
	}
	
	public function strLen($_s)
	{
		return (strlen($_s)+mb_strlen($_s))/2; 
	}
	
	//Json
	private function _ch_urlencode($data) {
		if (is_array($data) || is_object($data)) {
           foreach ($data as $k => $v) {
               if (is_scalar($v)) {
                   if (is_array($data)) {
                       $data[$k] = urlencode($v);
                   } else if (is_object($data)) {
                       $data->$k = urlencode($v);
                   }
               } else if (is_array($data)) {
                   $data[$k] = $this->_ch_urlencode($v); //递归调用该函数
               } else if (is_object($data)) {
                   $data->$k = $this->_ch_urlencode($v);
               }
           }
       }
       return $data;
	}
	
	public function JsonEncodeCN($_s){
		$_r = $this->_ch_urlencode($_s);
		$_r= json_encode($_r);
		return urldecode($_r);
   }
	
	//Date
	public function time2ms($_t){
		$_ar=explode(':',$_t);
		return ($_ar[2]+$_ar[1]*60+$_ar[0]*3600)*1000;
	}
	
	//Trim
	public function ltrim($_s,$_n,$right=0){
		if($right){
			return @substr($_s,strpos($_s,$_n)+1);
		}else{
			return substr($_s,0,strpos($_s,$_n));
		}
	}
	
	public function rtrim($_s,$_n,$right=0){
		if($right){
			return @substr($_s,strrpos($_s,(string)$_n)+1);
		}else{
			return substr($_s,0,strrpos($_s,(string)$_n));
		}
	}
	
	public function trim($_s,$_n){
		return $this->rtrim($this->ltrim($_s,$_n),$_n);
	}
	
	//去除所有空格、制表符
	public function removeSpaceTabs($_s){
		return strtr($_s,array(' '=>'', '　'=>'', '&nbsp;'=>'', '\t'=>'', '\n'=>'', '\r'=>''));
	}
	
	//检查数字是否优秀
	public function isNumGood($num){
		$ispretty=false;
		$ar=array("/^(0|13|15|18|168|400|800)[0-9]*$/i","/^\\d*(1688|2688|2088|2008|5188|10010|10001|666|888|668|686|688|866|868|886|999)\\d*$/i","/^\\d*(\\d)\\1{2,}\\d*$/i","/(?:(?:0(?=1)|1(?=2)|2(?=3)|3(?=4)|4(?=5)|5(?=6)|6(?=7)|7(?=8)|8(?=9)|9(?=0)){2,}|(?:0(?=9)|9(?=8)|8(?=7)|7(?=6)|6(?=5)|5(?=4)|4(?=3)|3(?=2)|2(?=1)|1(?=0)){2,})\\d/i","/^[0-9]*(518|918)$/i","/^\\d*(\\d)\\1(\\d)\\2\\d*$/i","/^\\d*(\\d)\\1\\1(\\d)\\2\\2\\d*$/i","/^(\\d)(\\d)(\\d)\\1\\2\\3$/i","/^(\\d)(\\d)\\2\\1\\2\\2$/i","/^(\\d)\\1(\\d)\\1\\1\\2$/i","/(19|20)[\d]{2}(1[0-2]|0?[1-9])(31|2[0-9]|1[0-9]|0?[0-9])/i");
		foreach ($ar as $k=>$v){
			if(preg_match($v,$num)){
				$ispretty=true;
				break;	
			}
		}
		return $ispretty;
	}
	
	//字符串生成唯一数字（21位）
	public function str2num($_s){
		return str_pad(base_convert(substr(md5($_s),8,-8), 16, 10),21,0,STR_PAD_RIGHT);
	}
	
	//清除匹配任何空白字符，包括空格、制表符、换页符等等 
	public function clearSpace($_s){
		return preg_replace("/^[\s]{2,}/", '', strtr($_s,array('&nbsp;'=>' ',' '=>' ','　　'=>' ')));//去除首行缩进的两个全角空格
	}
	
	//过滤Emoji
	public function clearEmoji($str)
	{
		$str = preg_replace_callback(
		'/./u',
		function (array $match) {
			return strlen($match[0]) >= 4 ? '' : $match[0];
		},
		$str);

		return $str;
	}
}
?>