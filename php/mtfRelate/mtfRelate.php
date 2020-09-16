<?php
class mtfRelate{
	private $_root;
	private $mtfMysql;
	
	public function __construct($db)
    {
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		
		include_once($this->_root.'../mtfMysql/mtfMysql.php');
		$this->mtfMysql=new mtfMysql($db);
	}
	
	private function _array_remove($ar,$v){
		foreach($ar as $_k=>$_v) {
			if($v == $_v){
				unset($ar[$_k]);
			}
		}
		return $ar;
	}
	
	public function sql($action,$table,$values='',$if='',$debug='')
	{
		if($action==='s1'){
			$_r=$this->mtfMysql->sql($action,$table,$values,$if,$debug);
			$_a=explode(',',$values);
			foreach ($_a as $_k=>$_v){
				if(@$_r[$_v]){
					$_r[$_v]=explode(',',$_r[$_v]);	
				}
			}
			return $_r;		
		}
		elseif($action==='i0'||$action==='i1'||$action==='u'||$action==='d1')
		{
			$_a=array();
			if($action==='i0'||$action==='i1'||$action==='d1'){
				$_r=$this->sql('s1',$table,implode(',',array_keys($values)),$if,$debug);
				if($_r){
					foreach ($values as $_k=>$_v){
						if($_v){
							if(@$_r[$_k]){
								$_a=$_r[$_k];
							}else{
								$_a=array();
							}
							if(!is_array($_v)){
								$_v=array($_v);
							}
							if($action==='i0'||$action==='i1'){	
								if($action==='i0'){
									$_a=array_merge($_v,$_a);	
								}else{
									$_a=array_merge($_a,$_v);
								}
								$values[$_k]=implode(',',array_unique($_a));
							}else{
								foreach ($_v as $_k2=>$_v2){
									$_a=$this->_array_remove($_a,$_v2);
								}
								$values[$_k]=implode(',',$_a);
							}
						}
					}
				}
			}
			return $this->mtfMysql->sql('u',$table,$values,$if,$debug);
		}
	}
}
?>