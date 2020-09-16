<?php
class mtfZH{
	public function convert($str, $lan) {
		require_once( dirname(__FILE__) . '/ZhConversion.php');
		switch($lan){
			case 'zh-hans'://简体中文
				 $str=strtr($str, $zh2Hans);
			break;
			case 'zh-hant'://繁體中文
				 $str=strtr($str, $zh2Hant);
			break;
			case 'zh-cn'://大陆简体
				$str=strtr(strtr($str, $zh2CN), $zh2Hans);
			break;
			case 'zh-hk'://港澳繁體
				$str=strtr(strtr($str, $zh2HK), $zh2Hant);
			break;
			case 'zh-sg'://马新简体
				$str=strtr(strtr($str, $zh2SG), $zh2Hans);
			break;
			case 'zh-tw'://台灣正體
				$str=strtr(strtr($str, $zh2TW), $zh2Hant);
			break;
		}
		return $str;
	}
}
?>