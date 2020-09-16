<?php
class mtfProxyCurl{
	private $_root;
	private $mtfHTTP;
	public function __construct()
    {	
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		include($this->_root.'../mtfHTTP/mtfHTTP.php');
		$this->mtfHTTP=new mtfHTTP();
	}
	public function verify($_conf_key){
		$_key=@$_POST['key']?$_POST['key']:@$_SERVER['HTTP_KEY'];
		$_answer=@$_REQUEST['answer'];
		$_j=$this->mtfHTTP->curl(array('u'=>$_conf_key['domain'].'?psd='.$_conf_key['psd'].'&key='.$_key.'&answer='.$_answer));
		
		$_a=json_decode($_j,true);
		
		if(@$_a['success']===TRUE){
			return $_a['fid'];
		}else{
			exit($_j);
		}
	}
	public function ban($_conf_key,$_fid){
		$_j=$this->mtfHTTP->curl(array('u'=>$_conf_key['domain'].'?psd='.$_conf_key['psd'].'&ban='.$_fid));
		
		$_a=json_decode($_j,true);
		
		if(@$_a['success']===TRUE){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	public function hasHm($_conf_domain_dat,$_hm,$_type='hm'){
		$_j=$this->mtfHTTP->curl(array('u'=>$_conf_domain_dat.'/api/file/hm/?hm='.$_hm.'&type='.$_type));
		$_a=json_decode($_j,true);
		
		if(@$_a[$_hm]){
			return $_a[$_hm];
		}else{
			return FALSE;
		}
	}
	public function hash($_conf_domain_cdn,$_n){
		$_j=$this->mtfHTTP->curl(array('u'=>$_conf_domain_cdn.'/api/?datatype=hash&outtype=json&n='.$_n,'t'=>3));
		$_a=json_decode($_j,true);

		return @$_a['hash'];
	}
	public function sub($_conf_domain_cdn,$_n,$_e){
		$_j=$this->mtfHTTP->curl(array('u'=>$_conf_domain_cdn.'/api/?datatype=sub&n='.$_n.'&e='.$_e,'t'=>3));
		return $_j;
	}
	public function down($_conf_domain_cdn,$_u){
		$_j=$this->mtfHTTP->curl(array('u'=>$_conf_domain_cdn.'/api/?datatype=down&outtype=json&r='.rand(1,999999).'&u='.urlencode($_u),'t'=>60));
		$_a=json_decode($_j,true);
		return $_a;
	}
	
	public function p2p($_url,$_post){
		$_u=$this->mtfHTTP->curl(array('u'=>$_url,'p'=>$_post,'t'=>20));//延长时间，让分享赞及时加上
		return $_u;
	}
	
	public function email($_conf_domain_msg,$_mail){
		return $this->mtfHTTP->curl(array('u'=>$_conf_domain_msg.'?a=mail&frommail='.$_mail['frommail'].'&fromname='.$_mail['fromname'].'&host='.$_mail['host'].'&port='.$_mail['port'].'&username='.$_mail['username'].'&password='.$_mail['password'].'&mailtoaddress='.$_mail['toaddress'].'&mailtoname='.$_mail['toname'].'&subject=【'.$_mail['fromname'].'】'.$_mail['toname'].'&text='.$_mail['toname'].'：'.$_mail['data'],'t'=>2));
	}
}
?>