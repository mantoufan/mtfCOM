<?php
class mtfGuid
{
	public $mtfCrypt;
	private $_root;
	public $day=3;//有效期
	
	public function __construct()
    {	
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		include_once($_root.'../mtfCrypt/mtfCrypt.php');
		$this->mtfCrypt=new mtfCrypt();
	}
	public function get(){
		if(@$_COOKIE['PHPSESSID']){
			return @$_COOKIE['PHPSESSID'];	
		}else{
			return md5(@$_SERVER['HTTP_USER_AGENT'].substr($_SERVER['REQUEST_TIME'],0,7));	
		}
	}
	public function ip(){
		$list = explode(',',@$_SERVER['HTTP_X_FORWARDED_FOR']);
		return $list[0]?$list[0]:$_SERVER["REMOTE_ADDR"];
	}
	private function ua2num(){
		$_ua = '';
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$_ua = stristr(@$_SERVER['HTTP_USER_AGENT'], 'NetType', true);
			$_ua = $_ua ? $_ua : @$_SERVER['HTTP_USER_AGENT'];
		}
		preg_match_all('|(\d+)|', $_ua, $_r);
		return substr(array_sum($_r[0]), 0, 10);
	}
	public function enUid($_i){
		return $this->mtfCrypt->en($_i.'|'.$this->ua2num().'|'.date('Y-m-d H:i:s').'|'.date('Y-m-d H:i:s'));
	}
	public function deUid($_uid){
		$_a=explode('|',$this->mtfCrypt->de($_uid));
		$_i=$_a[0];
		$_ua2num=@$_a[1];
		$_data_create=@$_a[2];
		$_data_last=@$_a[3];
		if($_ua2num!==$this->ua2num()){
			$_statue='wrong-ua';	
		}else{
			$diff=date_diff(date_create($_data_create),date_create());
			$_diff=$diff->format("%a");
			if($_diff>$this->day){
				$_statue='out-time';		
			}else{
				$diff=date_diff(date_create($_data_last),date_create());	
				$_diff=$diff->format("%a");
				if($_diff>=1){
					$_statue='need-update';
					$_uid=$this->mtfCrypt->en($_i.'|'.$this->ua2num().'|'.$_data_create.'|'.date('Y-m-d H:i:s'));	
				}else{
					$_statue='success';	
				}
			}
		}
		return array('i'=>$_i,'statue'=>$_statue,'uid'=>@$_uid);
	}
}
?>