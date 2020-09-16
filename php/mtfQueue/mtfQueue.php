<?php
class mtfQueue{
	public $max=2;
	public $url='';
	private $_start='开始';
	private $_stop='停止';
	private $_error='错误';
	private $_out='超时';
	
	public $db=array(
					'host'=>'',
					'user'=>'',
					'password'=>'',
					'database'=>'',
					'table'=>'mtfqueue',
					'install'=>"CREATE TABLE IF NOT EXISTS `mtfqueue` (
							  `i` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT '任务ID',
							  `d` text NOT NULL COMMENT '数据（json）',
							  `u` varchar(250) NOT NULL COMMENT '处理URL',
							  `g` varchar(50) NOT NULL COMMENT '任务所属队列名称',
							  `s` varchar(25) NOT NULL COMMENT '状态',
							  `t` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '时间',
							  `t0` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
							  `o` mediumint(3) NOT NULL DEFAULT '180' COMMENT '超时（秒数）',
							  `m` varchar(32) DEFAULT NULL COMMENT '任务md5（唯一）',
							  PRIMARY KEY (`i`)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COMMENT='队列' AUTO_INCREMENT=1 ;"
				);
	public $name='mtfqueue';
					
	private $mtfMysql='';
	private $mtfUnit='';
	private $_root='';
	private $_libPath='';
	
	public function __construct($db='')
    {
		set_time_limit(0);
		$this->_root=dirname(__file__).'/';
		if($db){
			if(is_array($db)){
				$this->db=array_merge($this->db,$db);
				include_once($this->_root.'../mtfMysql/mtfMysql.php');
				$this->mtfMysql=new mtfMysql($this->db);
			}else{
				$this->url=$db;	
			}
		}
		include_once($this->_root.'../mtfUnit/mtfUnit.php');
		$this->mtfUnit=new mtfUnit();
		$this->_libPath=$this->_root.'../../../'.$this->name.'.php';
	}
	
	public function add($d,$g='默认',$u='',$_o='')
	{
		$_d = $this->mtfUnit->JsonEncodeCN($d);
		$_m = md5($_d);
		$this->mtfMysql->sql('i',$this->db['table'],array('d'=>$_d,'m'=>$_m,'u'=>$u,'g'=>$g,'s'=>$this->_start)+($_o?array('o'=>$_o):array()));
		$this->refresh();
	}
	public function urlAdd($d,$g='默认',$u='',$_o='')
	{
		$this->_url_send($this->url,'add',$d,$u,$g,'','',$_o);
	}
	
	public function refresh()
	{
		$_n=$this->name;
		$_ar=$this->mtfMysql->sql('s',$this->db['table'],'g,count(g) as num','GROUP BY g');
		$$_n=array();
		
		if($_ar){
			foreach($_ar as $_k => $_v){
				$$_n[$_v['g']]=$_v['num'];
			}
		}
		file_put_contents($this->_libPath,"<?php\n\$".$_n."=".var_export($$_n, true).";\n?>");
	}
	
	public function runAll()
	{
		$_n=$this->name;
		@include($this->_libPath);
		if(@$$_n){
			foreach($$_n as $g=>$v){
				if($v>=1){
					$this->run($g);	
				}
			}
		}
	}
	
	public function run($g='默认')
	{
		$_n=$this->name;
		@include($this->_libPath);
		if(@$$_n[$g]>=1){
			$_ar=$this->mtfMysql->sql('s',$this->db['table'],'i,d,u,g,s,t,t0,o','WHERE `g`=\''.$g.'\' AND `s`!=\''.$this->_stop.'\' AND `s`!=\''.$this->_error.'\' AND `s`!=\''.$this->_out.'\' ORDER BY t ASC LIMIT 0,'.$this->max);
			if($_ar){
				foreach($_ar AS $_k => $_v){
					if($_v['s']===$this->_start){
						$this->_url_send($_v['u'],'work',json_decode($_v['d'],true),'',$_v['g'],$_v['s'],$_v['i']);
					}else{//超过10分钟标记为超时
						if((time()-strtotime($_v['t0']))>$_v['o']){
							$this->out($_v['i']);
						}
					}
				}
				
			}
		}
	}
	public function urlRun($g='默认',$s='进行中')
	{
		$this->_url_send($this->url,'run','','',$g,$s);
	}
	
	public function remove($i)
	{
		$this->mtfMysql->sql('d',$this->db['table'],'','WHERE `i`='.$i);
	}
	public function urlRemove($i)
	{
		$this->_url_send($this->url,'remove','','','','',$i);
	}
	
	public function update($i,$s)
	{
		$this->mtfMysql->sql('u',$this->db['table'],array('s'=>$s),'WHERE `i`='.$i);
	}
	public function urlUpdate($i,$s)
	{
		$this->_url_send($this->url,'update','','','',$s,$i);
	}
		
	public function reset($i)
	{
		$this->update($i,$this->_start);
	}
	public function urlReset($i)
	{
		$this->_url_send($this->url,'update','','','',$this->_start,$i);
	}
	
	public function resetAll($g=''){
		$_ar=$this->mtfMysql->sql('s',$this->db['table'],'i,d,u,g,s,t,o','WHERE '.($g?'`g`=\''.$g.'\'':'1').' AND (`s`=\''.$this->_stop.'\' OR `s`=\''.$this->_out.'\') ORDER BY t ASC');
		if($_ar){
			foreach($_ar AS $_k => $_v){
				$this->reset($_v['i']);
			}
		}
	}
		
	public function stop($i)
	{
		$this->update($i,$this->_stop);
	}
	public function urlStop($i)
	{
		$this->_url_send($this->url,'update','','','',$this->_stop,$i);
	}
	
	public function error($i)
	{
		$this->update($i,$this->_error);
	}
	public function urlError($i)
	{
		$this->_url_send($this->url,'update','','','',$this->_error,$i);
	}
	
	public function out($i)
	{
		$this->update($i,$this->_out);
	}
	public function urlOut($i)
	{
		$this->_url_send($this->url,'update','','','',$this->_out,$i);
	}
	
	public function work($i,$d,$g)
	{
		call_user_func('mtfQueueWork', $i, $d, $g);
		exit;
		//override work
	}
	
	public function RESTful()
	{
		if($_POST){
			$_n=$this->name;
			$_a=@$_POST[$_n.'a'];
			if($_a){
				$_d=json_decode(@$_POST[$_n.'d'],true);
				$_u=@$_POST[$_n.'u'];
				$_g=@$_POST[$_n.'g'];
				$_s=@$_POST[$_n.'s'];
				$_i=@$_POST[$_n.'i'];
				$_o=@$_POST[$_n.'o'];
				switch($_a)
				{
					case 'add':
						return $this->add($_d,$_g,$_u,$_o);
						break;
					case 'run':
						return $this->run($_g,$_s);
						break;
					case 'work':
						$this->urlUpdate($_i,'进行中');
						return $this->work($_i,$_d,$_g);				
						break;
					case 'remove':
						return $this->remove($_i);
						break;
					case 'update':
						return $this->update($_i,$_s);
						break;
					case 'status':
						echo $this->mtfUnit->JsonEncodeCN($this->status($_d['key']));
						break;
					default:
						;
				}
			}
		}
	}
	
	private function _url_get(){
		return (@$_SERVER['HTTPS']==='on'?'https://':'http://').@$_SERVER['HTTP_HOST'].(@$_SERVER["SERVER_PORT"]==='80'?'':':'.@$_SERVER["SERVER_PORT"]).@$_SERVER['REQUEST_URI'];
	}
	private function _url_send($url,$a,$d=array(),$u='',$g='',$s='',$i='',$_o=''){
		$_n=$this->name;
		$_d=array($_n.'a'=>$a,$_n.'d'=>$this->mtfUnit->JsonEncodeCN($d),$_n.'u'=>$u?$u:$this->_url_get(),$_n.'g'=>$g,$_n.'s'=>$s,$_n.'i'=>$i,$_n.'o'=>$_o);
		$_ch=curl_init();
		curl_setopt($_ch, CURLOPT_URL, $url);
		curl_setopt($_ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($_ch, CURLOPT_POST, 1);
		curl_setopt($_ch, CURLOPT_POSTFIELDS, $_d);
		curl_setopt($_ch, CURLOPT_TIMEOUT, 3);
		
		//加速Curl
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		
		//加速POST，减少1秒延迟 Expect: 请求gzip，并解压
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //强制协议为1.1
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect: ","Accept-Encoding:gzip","SERVER: ".json_encode($_SERVER)));
		
		//开启GZIP解压，减少数据传输量
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
		
		$_h=curl_exec($_ch);
		curl_close($_ch);
		return $_h;
	}
	
	public function status($_key){
		if($_key){
			$_ar=$this->mtfMysql->sql('s1',$this->db['table'],'g,s','WHERE d LIKE \'%'.$_key.'%\'');
		}
		if($_ar['g']){
			return array('g'=>$_ar['g'],'s'=>$_ar['s']);
		}else{
			return false;	
		}
	}
	
	public function urlStatus($_key){
		$_n=$this->name;
		return $this->_url_send($this->url,'status',array('key'=>$_key));
	}
	
}
?>