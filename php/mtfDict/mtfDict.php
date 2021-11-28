<?php
class mtfDict{
	public $db=array(
					'host'=>'',
					'user'=>'',
					'password'=>'',
					'database'=>'',
					'table'=>'mtfdict',
					'install'=>"CREATE TABLE IF NOT EXISTS `mtfdict` (
								  `i` int(10) NOT NULL AUTO_INCREMENT COMMENT '序号',
								  `s` varchar(200) NOT NULL COMMENT '简体字',
								  `t` varchar(200) NOT NULL COMMENT '繁体字',
								  `p` text NOT NULL COMMENT '拼音',
								  `e` text NOT NULL COMMENT '英语',
								  PRIMARY KEY (`i`),
								  KEY `s` (`s`),
								  KEY `t` (`t`)
								) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COMMENT='词典' AUTO_INCREMENT=1"
				);
	private $_root;
				
	public function __construct($db)
    {
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		$this->db=array_merge($this->db,$db);
		$_l='mtfMysql.'.$this->db['table'].'.lock';
		include($this->_root.'../mtfMysql/mtfMysql.php');
		if(!file_exists($_l)){
			set_time_limit(0);
			$this->mtfMysql=new mtfMysql($this->db);
			include('CcCedict/config.php');
			include('CcCedict/autoload.php');
			$filePath=$this->_root.'/CcCedict/cedict_ts.u8';
			$parser=new CcCedict\Parser();
			$parser->setFilePath($filePath);
			$parser->setOptions([
				CcCedict\Entry::F_SIMPLIFIED,
				CcCedict\Entry::F_TRADITIONAL,
				CcCedict\Entry::F_PINYIN_DIACRITIC,
				CcCedict\Entry::F_ENGLISH
			]);
			$parser->setBlockSize(500);
			$parser->setStartLine(0);
			$parser->setNumberOfBlocks(INF);
			foreach ($parser->parse() as $_k) {
				foreach ($_k['parsedLines'] as $_v) {
					$this->mtfMysql->sql('i',$this->db['table'],array('s'=>$_v['simplified'],'t'=>$_v['traditional'],'p'=>$_v['pinyinDiacritic'],'e'=>$_v['english']));
				}
			}
		}else{
			$this->mtfMysql=new mtfMysql($this->db);	
		}
	}
	public function getBaidu($_n)
	{
		include($this->_root.'../QueryList/autoload.php');
		$_r = QL\QueryList::run('Request',array(
				'target' => 'http://hanyu.baidu.com/zici/s',
				'referrer'=>'http://hanyu.baidu.com/zici/',
				'method' => 'GET',
				'params' => ['wd' => $_n],
				'user_agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0',
				'timeout' =>'3'
			))->setQuery(
				array(
					'pinyin'=>array('.header-info b','html','',function($c){return $c;}),
					'bushou'=>array('#radical span','html','',function($c){return $c;}),
					'bihua'=>array('#stroke_count span','html','',function($c){return $c;}),
					'fayin'=>array('.header-info a','url','',function($c){return $c;}),
					'fanyi'=>array('#fanyi-wrapper dt','html','',function($c){return $c;}),
					'shiyi_jiben'=>array('#basicmean-wrapper dd','html','',function($c){return $c;}),
					'shiyi_xiangxi'=>array('#detailmean-wrapper dd','html','',function($c){return $c;}),
					'shiyi_baike'=>array('#baike-wrapper p','html','-a',function($c){return $c;}),
					'yingwen'=>array('#fanyi-wrapper dt','html','',function($c){return $c;})
				)
			)->data;
		$_r_bd=array();
		if($_r){
			foreach($_r as $_k => $_v){
				if(@$_v['pinyin']){
					$_r_bd[$_v['pinyin']]=$_v;
				}
			}
			return $_r_bd;
		}
	}
	
	public function get($_n)
	{
		if(preg_match("/^[a-zA-Z\s]+$/",$_n)){//英语
			$_r=$this->mtfMysql->sql('s',$this->db['table'],'s,t,p,e','WHERE e = \''.$_n.'\' OR e LIKE \''.$_n.'/%\'');
		}else{
			$_r=$this->mtfMysql->sql('s',$this->db['table'],'s,t,p,e','WHERE s = \''.$_n.'\' OR t = \''.$_n.'\'');
			$_r_bd=$this->getBaidu($_n);
		}
		if(@$_r){
			foreach($_r as $_k => $_v){
				$_r[$_k]['relate']=$this->relate($_v['s'],$_v['p']);
				if($_r[$_k]['relate']){
					$_r[$_k]['relates']=1;	
				}
				$_a=explode('/',$_v['e']);
				foreach($_a as $_k2 => $_v2){
					if(stristr($_v2,'see ')||stristr($_v2,'variant ')||stristr($_v2,'pun ')){
						$_ar=explode('|',$_v['e']);
						$_b=$_ar[0];
						$_ar=explode('[',$_b);
						$_b=$_ar[0];
						$_ar=explode(' ',$_b);	
						$_b=end($_ar);
						if($_b){
						$_r2=$this->mtfMysql->sql('s',$this->db['table'],'e','WHERE s = \''.$_b.'\' OR t = \''.$_b.'\'');
							if($_r2[0]['e']){
								$_a[$_k2]=$_r2[0]['e'];
							}
						}
					}
				}
				$_r[$_k]['e']=implode('/',$_a);
				if(@$_r_bd[$_v['p']]){
					$_r_p=$_r_bd[$_v['p']];
					$_r[$_k]['b']=@$_r_p['bushou'];
					$_r[$_k]['h']=@$_r_p['bihua'];
					$_r[$_k]['j']=@$_r_p['shiyi_jiben'].@$_r_p['shiyi_xiangxi'].@$_r_p['shiyi_baike'];
				}
			}
			return $_r;
		}elseif($_r_bd){
			$_r=array();$_i=0;
			foreach($_r_bd as $_k => $_v){
				$_r[$_i]['n']=$_n;
				$_r[$_i]['p']=$_v['pinyin'];
				$_r[$_i]['e']=@$_v['fanyi'];
				$_r[$_i]['b']=@$_v['bushou'];
				$_r[$_i]['h']=@$_v['bihua'];
				$_r[$_i]['j']=@$_v['shiyi_jiben'].@$_v['shiyi_xiangxi'].@$_v['shiyi_baike'];
				$_i++;
			}
			return $_r;
		}else{
			return false;	
		}
	}
	
	public function relate($_n,$_p)
	{
		$_n=mb_substr($_n, 0, 1, 'utf-8');
		$_r=$this->mtfMysql->sql('s',$this->db['table'],'s,t,p,e','WHERE (s LIKE \''.$_n.'%\' OR t LIKE \''.$_n.'%\') AND p LIKE \''.$_p.'%\'');
		if(@$_r){
			return $_r;
		}else{
			return false;	
		}
	}
}
?>