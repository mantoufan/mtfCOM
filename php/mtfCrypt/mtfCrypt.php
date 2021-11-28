<?php
class mtfCrypt{
	public $key='madfan';
	
	private function _keyED($txt, $encrypt_key) {
		$encrypt_key = md5($encrypt_key);
		$ctr = 0;
		$tmp = "";
		for ($i = 0; $i < strlen($txt); $i++) {
			if ($ctr == strlen($encrypt_key)) $ctr = 0;
			$tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
			$ctr++;
		}
		return $tmp;
	}
	public function encrypt($txt, $key) {
		$encrypt_key = md5($key);
		$ctr = 0;
		$tmp = "";
		for ($i = 0; $i < strlen($txt); $i++) {
			if ($ctr == strlen($encrypt_key)) $ctr = 0;
			$tmp.= substr($encrypt_key,$ctr,1) . (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
			$ctr++;
		}
		return $this->_keyED($tmp, $key);
	}
	public function decrypt($txt, $key) {
		$txt = $this->_keyED($txt, $key);
		$tmp = "";
		for ($i = 0; $i < strlen($txt); $i++) {
			$md5 = substr($txt, $i, 1);
			$i++;
			$tmp.= (substr($txt,$i,1) ^ $md5);
		}
		return $tmp;
	}
	
	private function base_encode($str) {
        $src  = array("/","+","=");
        $dist = array("-a","-b","-c");
        $old  = base64_encode($str);
        $new  = str_replace($src,$dist,$old);
        return $new;
	}
	
	private function base_decode($str) {
        $src = array("-a","-b","-c");
        $dist  = array("/","+","=");
        $old  = str_replace($src,$dist,$str);
        $new = base64_decode($old);
        return $new;
	}

	//解密
	public function de($str,$key=''){
		return $this->decrypt($this->base_decode($str),$key?$key:$this->key);
	}
	//加密
	public function en($str,$key=''){
		return $this->base_encode($this->encrypt($str,$key?$key:$this->key));
	}		
	
}
?>