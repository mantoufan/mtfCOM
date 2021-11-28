<?php
class mtfMysqlSysn{
	private $_root;
	private $_mtfMysql_from;
	private $_mtfMysql_to;
	private $_db_to;
	private $_tip;
	
	public function __construct($db_from,$db_to)
    {
		ini_set('date.timezone','Asia/Shanghai');
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		
		include_once($this->_root.'../mtfMysql/mtfMysql.php');
		$this->_mtfMysql_from=new mtfMysql($db_from);
		$this->_db_to=$db_to;
	}
	
	public function sysn($auto)
	{
		$_rs=$this->_mtfMysql_from->sql('r','SHOW TABLES');
		$_r=array();
		
		while($row = mysqli_fetch_assoc($_rs)){
			$_r[]=$row;
		} 
		if(@$_r[0]){
			$_t=array('s'=>array(),'f'=>array());
			foreach($_r as $_k=>$_v){
				$_dbname=$_v['Tables_in_'.$this->_db_to['database']];
				$__r=$this->_mtfMysql_from->sql('s',$_dbname,'*','WHERE 1');
				if(@$__r[0]){
					$this->_mtfMysql_to=new mtfMysql($this->_db_to);
					
					foreach($__r as $__k=>$__v){
						//不包含自动增长的队列
						if($auto){
							$_i=$__v[$auto];
							unset($__v[$auto]);
						}
						$_r=$this->_mtfMysql_to->sql('i',$_dbname,array_filter($__v));
						if($_r){
							$this->_tip='';
							$this->_mtfMysql_from->sql('d',$_dbname,array('i'=>$_i));
							@$_t['s'][$_dbname]++;
						}if($_r===false){
							if(!@$this->_tip){
								$this->_tip=date('Y-m-d H:i:s', time()).': '.'Link is lost, retry later ...';
								echo $this->_tip."\r\n";
							}
						}else{
							$this->_tip='';
							@$_t['f'][$_dbname]++;
						}
					}
					if(!@$this->_tip){
						echo date('Y-m-d H:i:s', time()).': '.$_dbname.' success '.@$_t['s'][$_dbname].' faild '.@$_t['f'][$_dbname]."\r\n";
					}
				}
			}
		}	
	}
	
	public function start($auto,$sleep=1)
	{
		set_time_limit(0);
		while (1) {
			$this->sysn($auto);
			sleep($sleep); //单位为秒
		}
	}
}
?>