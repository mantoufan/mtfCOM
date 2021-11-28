<?php
class mtfApiLanguageDetector{
	//判斷字串是否是繁體
	/*
	public function containTraditional($_s)
	{
		$_l = mb_strlen($_s, 'utf-8');

		// gbk 包含 big5 內的字元，所以不能用 gbk
		return ($_l != mb_strlen(iconv('UTF-8', 'gb2312//IGNORE', $_s), 'gb2312')) ? true : false;
	}
	*/
	public function detect($_s){
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		include_once('autoload.php');
		$LanguageDetector=LanguageDetector\Detect::initByPath($_root.'data/datafile.php');
		$_lang=$LanguageDetector->detect($_s);
		if(is_array($_lang)){
			$_lang=@$_lang[0]['lang'];
		}
		//简/繁校正
		/*
		$_a=explode('-',$_lang);
		if($_a[0]==='zh'){
			$_lang=$this->containTraditional($_s)?'zh-tw':'zh-cn';
		}
		*/
		return $_lang;
	}

}
?>