<?php
class mtfP2P{
	public $conf=array(
		'domain'=>array('dat'=>'')
	);
	public $db=array(
		'host'=>'',
		'user'=>'',
		'password'=>'',
		'database'=>'',
		'table'=>'mtfp2p',
		'install'=>"CREATE TABLE IF NOT EXISTS `mtfp2p` (
					  `i` mediumint(8) NOT NULL AUTO_INCREMENT COMMENT 'ID',
					  `u` varchar(250) NOT NULL COMMENT '网址',
					  `t` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '时间',
					  `n` tinyint(3) NOT NULL COMMENT '次数',
  					  `ip` varchar(250) NOT NULL DEFAULT '' COMMENT 'IP',
  					  `uid` varchar(250) NOT NULL DEFAULT '' COMMENT '唯一标识',
					  PRIMARY KEY (`i`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='P2P' AUTO_INCREMENT=1 ;"
	);
	private $mtfHTTP;
	private $mtfGuid;
	private $mtfMysql;
	private $mtfCrypt;
	private $mtfTpl;
	
	public function __construct($db='')
    {
		$_root=dirname(__file__).'/';
		
		include_once($_root.'../mtfHTTP/mtfHTTP.php');
		$this->mtfHTTP=new mtfHTTP();
		
		include_once($_root.'../mtfGuid/mtfGuid.php');
		$this->mtfGuid=new mtfGuid();
		
		include_once($_root.'../mtfCrypt/mtfCrypt.php');
		$this->mtfCrypt=new mtfCrypt();
		
		include_once($_root.'../mtfTpl/mtfTpl.php');
		$this->mtfTpl=new mtfTpl();
		
		if($db){
			$this->db=array_merge($this->db,$db);
			include_once($_root.'../mtfMysql/mtfMysql.php');
			$this->mtfMysql=new mtfMysql($this->db);
		}
	}

	public function p2pClick($u,$arv=array('getNum'=>''))
	{
		
		$a=parse_url($u);
		$h=$a['host'];
		$q=$a['query'];
		parse_str($q,$a);
		$q=$a['q'];
		if($q){
			parse_str(base64_decode($q),$a);	
		}
		
		$_max=$a['max']?$this->mtfCrypt->de($a['max']):5;
		if(!is_numeric($_max)){
			return FALSE;
		}
		$_return=$this->mtfCrypt->de($a['return']);
		$_uid=@$a['uid'];//唯一标识
		$_ip=$this->mtfGuid->ip();
		
		$_allow=TRUE;
		
		if($_uid){
			$_uid=$h.$_uid;
			$_sql='uid=\''.$_uid.'\'';
		}else{
			$_sql='u=\''.$u.'\'';
		}
		
		$__r=$this->mtfMysql->sql('s1',$this->db['table'],'i,n,ip','WHERE t>\''.date('Y-m-d').'\' AND '.$_sql.' ORDER BY n DESC LIMIT 0,1');
		
		if($arv['getNum']){
			if($__r===FALSE){//数据库繁忙，或数据库链接错误
				return 'unkown';
			}elseif(@$__r['n']){
				return @$__r['n'];
			}else{
				return 0;
			}
		}else{
			if($__r===FALSE){//数据库繁忙，或数据库链接错误
				$_allow=FALSE;
			}elseif(@$__r['n']){
				if($__r['n']>=$_max){
					$_allow=FALSE;
				}else{
					$_ips=explode(',',$__r['ip']);
					if(in_array($_ip,$_ips)){
						$_allow=FALSE;
					}else{
						$_ips[]=$_ip;
					}
				}
			}else{
				
			}
			if($_allow){
				if(@$__r['n']){
					$this->mtfMysql->sql('u',$this->db['table'],array('n'=>'///n+1','ip'=>implode(',',$_ips)),'WHERE i=\''.$__r['i'].'\'');
				}else{
					$this->mtfMysql->sql('i',$this->db['table'],array('n'=>1,'u'=>$u,'ip'=>$_ip,'uid'=>$_uid));
				}
				//注意先后顺序，先更新次数，后更新积分，避免增加积分的时间，导致更新次数延迟
				if($_return){
					$_r=$this->mtfHTTP->curl(array('u'=>$_return,'p'=>$a+array('ip'=>$_ip),'t'=>6));//延长时间，让分享赞及时加上
					if($_r==='error'){
						$_allow=FALSE;
					}
				}
			}

			return $this->mtfTpl->tip($a);
		}
	}
	
	public function p2pUrl($a,$domain=array())
	{
		if($a['vu']){
			$a['vu']=urlencode($a['vu']);
		}
		if($a['dau']){
			$a['dau']=urlencode($a['dau']);
		}
		if($a['diu']){
			$a['diu']=urlencode($a['diu']);
		}
		if($a['max']){
			$a['max']=$this->mtfCrypt->en($a['max']);
		}
		if($a['return']){
			$a['return']=$this->mtfCrypt->en($a['return']);
		}
		$d=$domain[array_rand($domain)];
		return $d.'?q='.base64_encode(http_build_query($a));
	}
	
	public function p2pFrame($domain){
		return $this->mtfTpl->frame($domain);
	}
}
?>