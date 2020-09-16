<?php 
class mtfKeyword{
	private $_root;
	public function __construct($__root='')
    {	
		$this->_root=str_replace('\\','/',dirname(__file__)).'/';
	}
	public function get($word)
	{
		$ar=array();
		$do='';
		$domain='';
		include('data/words.php');
		if($wDY[$_SERVER['SERVER_NAME']]){
			$_ar=$wDY[$_SERVER['SERVER_NAME']];
			if(isset($_ar['f'])){
				$ar=$_ar['f']($word);
				if($ar!==$word){
					$do='301';
					$domain=$_SERVER['SERVER_NAME'];
					$word=$ar;
				}
			}
			if(isset($_ar['301'])){
				foreach($_ar['301'] as $_k=>$_v){
					if(isset($_v[$word])){
						$do='301';
						$domain=$_k;
						$ar=$_v[$word];
					}
				}
			}
		}
		if(!$do){
			ini_set('memory_limit', '256M');
			// 加入头文件
			include($this->_root.'../JieBa/autoload.php');
			Fukuball\Jieba\Jieba::init(array('dict'=>'small'));
			$a=Fukuball\Jieba\Jieba::cut($word);
			$ar=array();
			foreach($a as $k=>$v){
				if($wSame[$v]){
					$ar=array_merge($ar,explode(' ',$wSame[$v]));
				}else{
					$ar[]=$v;
				}
			}
		}
		return array('do'=>$do,'word'=>$ar,'domain'=>$domain);
	}
}
?>