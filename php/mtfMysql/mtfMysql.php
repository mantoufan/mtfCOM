<?php
class mtfMysql{
	public $db=array(
					'host'=>'',
					'user'=>'',
					'password'=>'',
					'database'=>'',
					'table'=>'',
					'install'=>''
				);
	private $_link;
	public function __construct($db)
    {
		$this->db=$db;
		if(@$db['install']){
			$_l='mtfMysql.'.$db['table'].'.lock';
			if(!file_exists($_l)){
				$db['install']=str_replace('`&talbe`','`'.$db['table'].'`',$db['install']);
				$this->sql('r',$db['install']);
				file_put_contents($_l,date('YmdHis',time()));
			}
		}
	}
	public function sql($action,$table,$values='',$if='',$debug='')
	{
		if(!$this->_link){
			$this->_link = @mysqli_connect(   
							  $this->db['host'], 
							  $this->db['user'],
							  $this->db['password'], 
							  $this->db['database']
						  );          
			if (!$this->_link) {   
				//printf('%s', mysqli_connect_error());
				return false;   
			}else{
				mysqli_set_charset ($this->_link,'utf8mb4');	
			}
		}
		if($action==='s1'||$action==='s'){
			$sql_str='SELECT '.$values.' FROM '.$table.' '.$if;
		}
		elseif($action==='d'){
			$sql_str='DELETE FROM '.$table.' '.$if;
		}
		elseif($action==='i'||$action==='iu'){
			$kv=$this->_key_value($values);
			$sql_str='INSERT INTO '.$table.' ('.implode(',',$kv[0]).') VALUES ('.implode(',',$kv[1]).')';
			if($action==='iu'){
				$sql_str.=' ON DUPLICATE KEY ';
			}
		}
		if($action==='u'||$action==='iu'){
			$set_str=array();
			$kv=$this->_key_value($values);

			foreach ($kv[0] as $k=>$v){
				if(stripos($kv[1][$k],'///')!=FALSE){
					$set_str[]=$v.'='.str_replace('///','',$values[$v]);//或trim($kv[1][$k],'\'')	
				}elseif($values[$v]==='NULL'){
					$set_str[]=$v.'=NULL';
				}else{
					$set_str[]=$v.'='.$kv[1][$k];
				}
			}
			$set_str=implode(',',$set_str);
			if($action==='iu'){
				@$sql_str.='UPDATE '.$set_str;
			}else{
				@$sql_str.='UPDATE '.$table.' SET '.$set_str.' '.$if;
			}
		}
		elseif($action==='r'){
			$sql_str=$table;
		}
		if($debug){
			echo $sql_str.'\n';
		}
		$result = mysqli_query($this->_link, $sql_str);
		if(($action=='s1'||$action=='s')&&$result){
			//$num_results = mysqli_num_rows($result);
			$rows=array();
			while($row = mysqli_fetch_assoc($result)){
				if($action=='s1')
				return $row;
				else
				$rows[]=$row;
			} 
			$r=$rows;
			mysqli_free_result($result);
		}else{
			$r=$result;
		}
		//mysqli_close($this->_link);
		return $r;
	}
	private function _key_value($post)
	{
		$keys=array();
		$values=array();
  	 	foreach($post as $k=>$v)
   		{
	   		$keys[]=$k;
			$values[]='\''.addslashes($v).'\'';
   		}
   		return array($keys,$values);
	}
}
?>