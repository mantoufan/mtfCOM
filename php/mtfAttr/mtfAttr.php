<?php
class mtfAttr{
	private $_root;
	private $mtfMysql;
	
	public function __construct($db)
    {
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		
		include_once($this->_root.'../mtfMysql/mtfMysql.php');
		$this->mtfMysql=new mtfMysql($db);
	}
	
	private function _escape($_s){
		return strtr($_s,array(','=>'，',':'=>'：'));
	}
	
	private function _unescape($_s){
		return strtr($_s,array('，'=>',','：'=>':'));
	}
	
	// $_t='|'/''
	public function parseA($_a,$_t=''){
		$_ar=array();
		$_a=explode(',',$_a);
		if($_a){
			foreach($_a as $_k=>$_v){
				//$_k2=$this->_unescape(stristr($_v,':',true));
				$_k2=stristr($_v,':',true);
				if($_t==='|'){
					$_v2=substr(stristr($_v,':'),1);
					$_b=explode($_t,$_v2);
					
					foreach($_b as $_k3=>$_v3){
						if($_k2&&$_v3){
							//$_ar[$_k2][]=$this->_unescape($_v3);
							$_ar[$_k2][]=$_v3;
						}
					}
				}else{
					//$_v2=$this->_unescape(substr(stristr($_v,':'),1));
					$_v2=substr(stristr($_v,':'),1);
					
					if($_k2&&$_v2){
						$_ar[$_k2][]=$_v2;
					}
				}
			}
			return $_ar;
		}
	}
	
	public function parseK($_a){
		if($_a){
			$_ar=array();
			foreach($_a as $_k=>$__ar){
				foreach($__ar as $__k=>$_v){
					$_ar[]=$_k.':'.$_v;	
				}
			}
			return $_ar;
		}else{
			return '';	
		}
	}
	
	// $_t='|'/''
	public function sql($action,$table,$values='',$if='',$debug='',$_t='')
	{
		if($action==='s'||$action==='s1'){
			$_r=$this->mtfMysql->sql($action,$table,$values,$if,$debug);
			if($_r)
			{
				if($action==='s1'){
					$_r2[]=$_r;	
				}else{
					$_r2=$_r;
				}
			}
			$_rr=array();
			
			if(@$_r2){
				foreach ($_r2 as $_k=>$_v){
					foreach($_v as $_k2=>$_v2){
						$_ar=$this->parseA($_v2,$_t);
						$_rr[$_k][$_k2]=$_ar;
					}
				}
			}
			
			if($action==='s1'){
				if($_rr){
					$_rr=$_rr[0];
				}
			}
			return $_rr;		
		}
		elseif($action==='i1'||$action==='u0'||$action==='u1'||$action==='d1')
		{
			//u0 - 修改声明的护具，其余数据删除
			//u1 - 只修改声明的数据，其余数据不动
			$_rr=array();
			$_r=$this->sql('s1',$table,implode(',',array_keys($values)),$if,$debug,$_t);
			if($_r){
				foreach ($_r as $_k=>$_v){
					if($action==='u0'){
						$_r[$_k]=array();
					}
					foreach ($values as $_k2=>$_v2){
						if(!is_array($_v2))
						{
							$_ar=array();
							$ar=explode(',',$_v2);
							foreach($ar as $_k=>$_v)
							{
								$_ar[$_v]='';	
							}
							$_v2=$_ar;
						}
						foreach ($_v2 as $_k3=>$_v3){
							if($action==='d1'){
								if(@$_r[$_k2][$_k3]){
									if($_v3){
										if(!is_array($_v3)){
											$_v3=array($_v3);
										}
										foreach ($_v3 as $_k4=>$_v4){
											$_k5 = array_search($_v4,$_r[$_k2][$_k3]);
											if($_k5!==false){
												unset($_r[$_k2][$_k3][$_k5]);
											}
											if(empty($_r[$_k2][$_k3])){
												unset($_r[$_k2][$_k3]);	
											}
										}
									}else{
										unset($_r[$_k2][$_k3]);
									}
								}
							}elseif($action==='u0'||$action==='u1'||$action==='i1'){
								if($action==='u1'||!@$_r[$_k2][$_k3]){
									$_r[$_k2][$_k3]=array();
								}
								if(is_array($_v3)){
									$_r[$_k2][$_k3]=array_merge($_r[$_k2][$_k3],$_v3);
								}else{
									$_r[$_k2][$_k3][]=$_v3;	
								}
								$_r[$_k2][$_k3]=array_filter(array_unique($_r[$_k2][$_k3]));
							}
						
						}
					}
				}
			}else{
				if($action==='u1'||$action==='i1'){
					$_r=$values;
				}
			}
			if($_r){
				foreach ($_r as $_k=>$_v){
					$_rr[$_k]=array();
					foreach ($_v as $_k2=>$_v2){
						if(is_array($_v2)){
							if($_t==='|'){
								$_a=array();
								foreach ($_v2 as $_k3=>$_v3){
									$_a[]=$this->_escape($_v3);
								}
								$_rr[$_k][]=$this->_escape($_k2).':'.implode($_t,$_a);
							}else{
								foreach ($_v2 as $_k3=>$_v3){
									$_rr[$_k][]=$this->_escape($_k2).':'.$this->_escape($_v3);
								}
							}
						}else{
							$_rr[$_k][]=$this->_escape($_k2).':'.$this->_escape($_v2);
						}
					}
					$_rr[$_k]=implode(',',$_rr[$_k]);
				}
			}
			return $this->mtfMysql->sql('u',$table,$_rr,$if,$debug);
		}
	}
}
?>