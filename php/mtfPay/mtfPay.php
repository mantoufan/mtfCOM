<?php
class mtfPay{
	private $_root;
	private $mtfHTTP;
	public function __construct()
    {	
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		include($_root.'../mtfHTTP/mtfHTTP.php');
		$this->mtfHTTP=new mtfHTTP();
	}
	public function sign($_arv){
		return md5(urldecode(http_build_query($_arv)));
	}
	public function form($_arv){
		$_h='<form method="post" action="'.$_arv['url'].'">';
		$autoSubmit=$_arv['autoSubmit'];
		unset($_arv['url']);
		unset($_arv['autoSubmit']);
		foreach($_arv as $_k=>$_v){
			$_h.='<input type="hidden" name="'.$_k.'" value="'.$_v.'"/>';
		}
		$_id='mtfPayBt'.rand(1,999999);
		$_h.='<div style="text-align:center"><input id="'.$_id.'" type="submit" value="支付" style="font-size:16px;padding:.5em 1em;background:#09c1f0;color:#FFFFFF;border:1px solid #09c1f0;border-radius:5px;" /></div>';
		$_h.='</form>';
		if($autoSubmit){
			$_h.='<script>document.getElementById("'.$_id.'").click();</script>';
		}
		return $_h;
	}
	public function refund($_arv){
		$_u=$_arv['url'];
		unset($_arv['url']);
		return $this->mtfHTTP->curl(array('u'=>$_arv['url'].'?'.http_build_query($_arv),'t'=>10));
	}
	public function notify(){
		$_u=$_arv['url'];$_n=$_arv['num'];
		unset($_arv['url']);unset($_arv['num']);
		for ($i=1; $x<=$_n; $x++) {
			$_r=$this->mtfHTTP->curl(array('u'=>$_arv['url'].'?'.http_build_query($_arv),'t'=>5));
			if($_r==='200'){
				return TRUE;
			}
		}
		return FALSE;
	}
}
?>