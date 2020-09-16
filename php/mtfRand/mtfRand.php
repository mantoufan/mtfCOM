<?php
class mtfRand{
	public function uid2p($_uid){
		$_m=md5($_uid);
		$_max='99999999999999999999999999999999';
		$_num=preg_replace('/\D/s', '', $_m);
		$_l=32-strlen($_num);
		for($_i=0;$_i<$_l;$_i++){
			$_num.=0;
		}
		return $_num/$_max;
	}
	public function get($_type,$_class='',$_uid=''){//分类,独立标识
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		if($_type==='img'){
			if(!$_class){
				$_dir=glob($_root.$_type.'/*', GLOB_ONLYDIR);
				$_class=array_rand($_dir);
			}
			$_dir=glob($_root.$_type.'/'.$_class.'/*.{gif,jpg,png}',GLOB_BRACE);
			$_l=count($_dir);
			return $_p=$_dir[round($this->uid2p($_uid)*$_l)];  
		}
	}
}
?>