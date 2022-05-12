<?php
class mtfBBcode{
	public $className='mtfBB';
	public $classNameCC='mtfCC';
	public $classNameWeather='mtfBB-weather';
	public $classNameVideo='mtfBB-Video';
	public $classNameMenu='mtfBB-Menu';
	public $classNameMenuLine='mtfBB-Menu-Line';
	private $_lang='m-lang';
	private $_root;
	
	public function __construct()
    {	
		$this->_root = str_replace('\\','/',dirname(__file__)).'/';
	}
	private function _code2div($_s){
		return '<span class="'.$this->className.' '.$this->_lang.'">'.$_s.'</span>';
	}
	public function parse($_s,$_arv=array()){
		$_d=$_s;
		preg_match_all("/\[(.*?)\](.*?)\[\/\\1\]/s",$_s,$_ar);// /s.号匹配换行符
		$_bb=array();
		$_bb_html=array();
		
		$_av=array('zan'=>0,'num'=>0);
		
		foreach($_ar[1] as $_k=>$_v){
			$_a=$_ar[0][$_k];
			$_c=$_ar[2][$_k];
			switch ($_v)
			{
				case 'hide':
					switch (@$_arv['type'])
					{
						case 'list':
							$_bb[]=$this->_code2div('隐藏');
							$_s=str_replace($_a,'',$_s);
						break;
						case 'add':
							
						break;
						default:
							$_j=$this->_loadParam($_c);
							$_r=@$_j['dat']['reply'];
							if(in_array(@$_arv['uid'],$_r)){
								if(@$_j['参数原文']){
									$_c=str_replace($_j['参数原文'],'',$_c);
								}
								if(@$_j['数据原文']){
									$_c=str_replace($_j['数据原文'],'',$_c);
								}
								$_s=str_replace($_a,$_c,$_s);
							}else{
								$_s=str_replace($_a,$this->_code2div('隐藏 内容 需 <b> 登录 </b> 并 <b> 回复 </b> 可见'),$_s);
							}
						break;
					}
					break;
				case 'buy':
					switch (@$_arv['type'])
					{
						case 'list':
							$_j=$this->_loadParam($_c);
							$_z=@$_j['set']['zan'];
							$_bb[]=$this->_code2div($_z.'❤ 兑换');
							$_s=str_replace($_a,'',$_s);
						break;
						case 'add':
							$_j=$this->_loadParam($_c);
							$_z=@$_j['set']['zan'];
							$_r=@$_j['dat']['buy'];
							
							$_av['zan']+=$_z;
							$_av['num']+=$_r?count($_r):0;
						break;
						default:
							$_j=$this->_loadParam($_c);
							$_i=$_k;
							$_z=@$_j['set']['zan'];
							$_r=@$_j['dat']['buy'];
							if(in_array(@$_arv['uid'],$_r)){
								if(@$_j['参数原文']){
									$_c=str_replace($_j['参数原文'],'',$_c);
								}
								if(@$_j['数据原文']){
									$_c=str_replace($_j['数据原文'],'',$_c);
								}
								$_s=str_replace($_a,$_c,$_s);
							}else{
								$_s=str_replace($_a,$this->_code2div('隐藏 内容 需 <b> 兑换 </b> 可见').$this->_bt('兑换 <sup> 需 '.$_z.' ❤'.($_r?'　'.count($_r).' 人 已经 兑换':''),'buy',array('i'=>$_i,'id'=>$_arv['id'])),$_s);
							}
						break;
					}
					break;
				case 'key':
					switch (@$_arv['type'])
					{
						case 'list':
							$_j=$this->_loadParam($_c);
							$_key=$this->_key($_c,$_j);
							if(count($_key)===0){
								$_bb[]=$this->_code2div('已兑完');
							}else{
								$_z=@$_j['set']['zan'];
								$_bb[]=$this->_code2div($_z.'❤ 兑换');
							}
							
							$_s=str_replace($_a,'',$_s);
						break;
						case 'add':
							$_j=$this->_loadParam($_c);
							$_z=@$_j['set']['zan'];
							$_r=@$_j['dat']['buy'];
							
							$_key=$this->_key($_c,$_j);
							if(count($_key)===0){
								$_z=0;//剩余卡密为0，不再显示在 花♥ 列表
							}
							$_av['zan']+=$_z;
							$_av['num']+=$_r?count($_r):0;
						break;
						default:
							$_j=$this->_loadParam($_c);
							$_i=$_k;

							$_key=$this->_key($_c,$_j);

							$_z=@$_j['set']['zan'];
							$_n=@$_j['set']['num'];
							$_r=@$_j['dat']['buy'];
							
							$_c2='';
							
							if(@$_r[@$_arv['uid']]){
								$_c2=$this->_code2div('已经 兑换 :<p> '.implode(' </p><p> ',@$_r[@$_arv['uid']]).' </p>');
							}
							@$_c2.=$this->_code2div('1 卡密 需 '.$_z.' ❤ 　 每人 最多 兑换 '.$_n.' 卡密 ').$this->_bt('兑换 <sup> 剩余 '.count($_key).' 卡密'.($_r?'　'.count(array_keys($_r)).' 人 已经 兑换':''),'key',array('i'=>$_i,'id'=>$_arv['id']));
							$_s=str_replace($_a,$_c2,$_s);
						break;
					}
					break;
				case 'cc':
					switch (@$_arv['type'])
					{
						case 'list':
							$_bb[]=$this->_code2div('程序');
							$_s=str_replace($_a,'',$_s);
						break;
						case 'add':
							
						break;
						default:
							$c_c=$_c;
							$c_c=str_replace('&nbsp;',' ',$c_c);
							preg_match_all("/\<p\>(.*?)\<\/p\>/is",$c_c,$c_arr);
							$c_arr=$c_arr[0];
							@$c_c='';$c_ar=array();
							foreach($c_arr as $k1=>$v1){
								$v1=substr($v1,3,strlen($v1)-7);
								$c_a=explode(' ',$v1);
								if(@substr($c_a[1],0,1)=='^'){
									@$c_c.='<p></p>';
									$c_ar[]=base64_encode(implode(' ',@$c_a));
								}elseif(@$c_a[2]&&@$c_a[2]=='|'){
									@$c_c.='<p></p>';
									$c_ar[]=base64_encode(implode(' ',@$c_a));
								}else{
									@$c_c.='<p>'.@$c_a[0].' '.@$c_a[1].'</p>';
									$c_ar[]=base64_encode(@$c_a[2]?' '.@$c_a[2]:'');
								}
							}

							$_s=str_replace($_a,'<div id="'.$this->classNameCC.$_k.'" class="'.$this->classNameCC.'">'.@$c_c.'</div>'.'<script>mtfCC'.$_k.'JS=\''.base64_encode(json_encode(@$c_ar)).'\';</script>',$_s);
						break;
					}
					break;
				case 'weather':
					switch (@$_arv['type'])
					{
						case 'list':
							$_bb[]=$this->_code2div('天气');
							$_s=str_replace($_a,'',$_s);
						break;
						case 'add':
							
						break;
						default:
							$_s=str_replace($_a,'<div class=\''.$this->classNameWeather.'\' style="display:none">'.$_c.'</div>',$_s);
						break;
					}
					break;
				case 'video':
					switch (@$_arv['type'])
					{
						case 'add':
							$_s=str_replace($_c,preg_replace("/(http|https)\:\/\//",'',$_c),$_s);
						break;
						default:
							$_url='';
							if(stripos($_c,'v.youku.com')!==FALSE){
								$_id=reset(explode('.',end(explode('/id_',$_c))));
								$_url='https://player.youku.com/embed/'.$_id;
								//http://v.youku.com/v_show/id_XMzQ0NzY0NjgyNA==.html
								//https://player.youku.com/embed/XMzQ0NzY0NjgyNA==
							}elseif(stripos($_c,'v.qq.com')!==FALSE){
								$_id=end(explode('/',reset(explode('.htm',$_c))));
								$_url='https://v.qq.com/iframe/player.html?vid='.$_id.'&tiny=1&auto=0';
								//https://v.qq.com/x/cover/14rxevi1whx67f4/x060348j55k.html
								//https://v.qq.com/iframe/player.html?vid=x060348j55k&tiny=1&auto=0
							}elseif(stripos($_c,'huya.com')!==FALSE){
								$_id=end(explode('/',strip_tags(reset(explode('?',$_c)))));
								$_url='//liveshare.huya.com/iframe/'.$_id;
								
								//http://www.huya.com/xuanzi
							}
							if($_url){
								$_url='<div class="'.$this->classNameVideo.'"><iframe src="'.$_url.'" frameborder="0" allowfullscreen="true" width="100%" height="100%"></iframe></div>';
							}
							if($_arv['type']==='list'){
								$_bb_html[]=$_url;
								$_s=str_replace($_a,'',$_s);
							}else{
								$_s=str_replace($_a,$_url,$_s);
							}
						break;
					}
					break;
				case 'menu':
				case 'menuline':
					switch (@$_arv['type'])
					{
						case 'add':
							
						break;
						case 'tdk':
							$v_type = $_v;
						  $_d = str_replace($_a, '', $_d);
							$_h = array();
							$__ar=array_filter(explode("\n",str_replace("\r","\n",strip_tags($_c))));
							foreach($__ar as $__k=>$__v){
								$__a=explode(' ',$__v);
								$__h='';
								foreach($__a as $_k=>$_v){
									if($_k===0){
										$__h.='<div>'.$_v.':</div>';
									}else{
										$__h.='<a href="/'.$_v.'">'.$_v.'</a>';
									}	
								}
								$_h[]='<div>'.$__h.'</div>';
							}
							$_s = str_replace($_a,'<div class=\''.$this->classNameMenu.($v_type === 'menuline' ? ' '.$this->classNameMenuLine : '').'\'>'.implode('',$_h).'</div>',$_s);
						break;
					}
					break;
				case 'flink'://友情链接
					switch (@$_arv['type'])
					{
						case 'tdk':
							$_b = $_c;
							$_d = str_replace($_a, '', $_d);
							$_s = str_replace($_a, '', $_s);
						break;
					}
					break;
				case 'md'://Markdown语法
					switch (@$_arv['type'])
					{
						case 'add':
							$_ss = str_replace($_a, preg_replace('~(```[\s\S]*```)|([[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/])~', '', $_a), $_s);
						break;
						case 'list':
							$_s = str_replace($_a, '', $_s);
						break;
						default:
							include_once($this->_root.'../Parsedown/Parsedown.php');
							$Parsedown = new Parsedown();
							$_text = htmlspecialchars_decode(preg_replace('/<p.*?>|<\/p>/is','',preg_replace('/<p>(.*?)<\/p>/',"$1\n",str_replace('&nbsp;',' ', str_replace('<p><br></p>',"\n",$_c)))));
							$_text = preg_replace('/((```[\w+]+\s\[\]((?!```)[\s\S])*```[\r\n]*){2,})/', "[mtfCodeTab]\n$1\n[/mtfCodeTab]\n", $_text);
							$_s=str_replace($_a, strtr($Parsedown->text($_text), array('[mtfCodeTab]' => '<div class="mtf-code-tab">', '[/mtfCodeTab]' => '</div>')), $_s);
						break;
					}
					break;
				case 'html':
					switch (@$_arv['type'])
					{
						case 'tdk':
							$_d = str_replace($_a, '', $_d);
							$_c = preg_replace_callback('/style="(.*?)"/', function($matches){
								return str_replace('：', ':', $matches[0]);
							}, $_c);
							$_c = preg_replace_callback('/<style>(.*?)<\/style>/', function($matches){
								return str_replace('：', ':', $matches[0]);
							}, $_c);
							$_s = str_replace($_a, $_c, $_s);
						break;
					}
					break;
				default:
					break;
			}
		}
		if($_arv['type']==='list'){
			if(!$_bb && $_bb_html){
				$_bb[]=$this->_code2div('视频');
			}
			return array('bb'=>array_unique($_bb),'bb_html'=>$_bb_html,'s'=>$_s);
		}elseif($_arv['type']==='add'){
			return array('av'=>$_av,'s'=>$_s,'ss'=>@$_ss);
		}elseif($_arv['type']==='tdk'){
			return array('b'=>$_b,'d'=>$_d,'s'=>$_s);
		}else{
			return $_s;
		}
	}
	public function add($_s,$_arv=array()){
		preg_match_all("/\[(.*?)\](.*?)\[\/\\1\]/s",$_s,$_ar);// /s.号匹配换行符
		foreach($_ar[1] as $_k=>$_tag){
			$_a=$_ar[0][$_k];
			$_c=$_ar[2][$_k];
			switch ($_tag)
			{
				case 'hide':
					if(@$_arv['uid']){
						$_j=$this->_loadParam($_c);
						$_r=array();
						if(is_array(@$_j['dat']['reply'])){
							$_r=@$_j['dat']['reply'];
						}
						$_r[]=@$_arv['uid'];
						$_r=array_unique($_r);
						$_s=str_replace($_a,$this->_addParam($_c,array('dat'=>array('reply'=>$_r)),$_tag),$_s);
					}
					break;
				case 'buy':
						$_j=$this->_loadParam($_c);	
						$_id=$_k;
					if(floor(@$_arv['i'])===floor($_id)){
						if(@$_arv['step']===1){
							return $_z=@$_j['set']['zan'];
						}elseif(@$_arv['step']===2){
							if(@$_arv['uid']){	
								$_r=array();
								if(is_array(@$_j['dat']['buy'])){
									$_r=@$_j['dat']['buy'];
								}
								$_r[]=@$_arv['uid'];
								$_r=array_unique($_r);
								$_s=str_replace($_a,$this->_addParam($_c,array('set'=>$_j['set'],'dat'=>array('buy'=>$_r)),$_tag),$_s);
							}
						}
					}	
					break;
				case 'key':
						$_j=$this->_loadParam($_c);	
						$_id=$_k;
						$_key=$this->_key($_c,$_j);
					
						if(floor(@$_arv['i'])===floor($_id)){
							if(@$_arv['step']===1){
								$_r=@$_j['dat']['buy'];
								$_num=@$_j['set']['num'];
								if(@$_r[@$_arv['uid']]){
									$_num-=count($_r[@$_arv['uid']]);
								}
								return array('zan'=>@$_j['set']['zan'],'num'=>$_num,'key'=>$_key);
							}elseif(@$_arv['step']===2){
								if(@$_arv['uid']){			

									$_r=array();
									if(is_array(@$_j['dat']['buy'])){
										$_r=@$_j['dat']['buy'];
									}
									$_n=$_arv['n'];
									for($_i=0;$_i<$_n;$_i++){
										@$_r[$_arv['uid']][]=$_key[$_i];
										unset($_key[$_i]);
									}
									
									$_c2='<p>'.$_j['参数原文'].$_j['数据原文'].'</p><p>'.implode('</p><p>',$_key).'</p>';
									$_s=str_replace($_a,$this->_addParam($_c2,array('set'=>$_j['set'],'dat'=>array('buy'=>$_r)),$_tag),$_s);
								}
							}
						}
					break;
				case 'cc':
					if(isset($_arv['si'])&&$_k!=$_arv['si']){
						continue;
					}
					$_j=$this->_loadParam($_c);
					if(@$_arv['step']===1){
						$__r=array();$_t=0;
						$_r=@$_j['dat']['result'];
						foreach($_r as $__k=>$_v){
							$_n=count($_v);
							$__r[$__k]=$_n;
							$_t+=$_n;
						}
						foreach($__r as $__k=>$_v){
							$__r[$__k]=round($_v/$_t*100,2).'%　'.$_v.' 人';
						}
						ksort($__r);
						return $__r;
					}elseif(@$_arv['ii']){
						$_r=array();
						if(is_array(@$_j['dat']['result'])){
							$_r=@$_j['dat']['result'];
						}
						if($_r){
							$_uid_ar=array();
							foreach($_r as $__k=>$_v){
								$_uid_ar=array_merge($_uid_ar,$_v);
							}
						}
						$_uid=@$_arv['uid']?$_arv['uid']:$_arv['ip'];
						if(!in_array($_uid,$_uid_ar)){
							@$_r[$_arv['ii']][]=$_uid;
							$_r[$_arv['ii']]=array_unique($_r[$_arv['ii']]);
							$_s=str_replace($_a,$this->_addParam($_c,array('dat'=>array('result'=>$_r)),$_tag),$_s);
							
						}
					}
					break;
				default:
					break;
			}
		}
		return $_s;
	}
	private function _addParam($_c,$_arv=array(),$_tag){
		$_j=array();$__c=array();
		//获取参数
		preg_match_all("/\[(.*?)\](.*?)\[\/\\1\]/",$_c,$_ar);
		foreach($_ar[1] as $_k=>$_v){
			$__c[$_v]=$_ar[2][$_k];
		}
		
		$_c=$this->_editParam($_c,$__c,$_arv,'set');
		$_c=$this->_editParam($_c,$__c,$_arv,'dat');
		return '['.$_tag.']'.$_c.'[/'.$_tag.']';
	}
	private function _editParam($_c,$__c,$_arv,$_t){
		if(@$_arv[$_t]){
			if(@$__c[$_t]){
				$_c=str_replace('['.$_t.']'.$__c[$_t].'[/'.$_t.']','['.$_t.']'.json_encode($_arv[$_t]).'[/'.$_t.']',$_c);
			}else{
				$_c.='['.$_t.']'.json_encode($_arv[$_t]).'[/'.$_t.']';
			}
		}else{
			if(@$__c[$_t]){
				$_c=str_replace('['.$_t.']'.$__c[$_t].'[/'.$_t.']','',$_c);
			}
		}
		return $_c;
	}	
	private function _loadParam($_s){
		$_j=array();
		//获取参数
		preg_match_all("/\[(.*?)\](.*?)\[\/\\1\]/",$_s,$_ar);
		foreach($_ar[1] as $_k=>$_v){
			switch ($_v)
			{
				case 'set':
					$_j['set']=json_decode(strip_tags($_ar[2][$_k]),true);
					$_j['参数原文']=$_ar[0][$_k];
					break;  
				case 'dat':
					$_j['dat']=json_decode(strip_tags($_ar[2][$_k]),true);
					$_j['数据原文']=$_ar[0][$_k];
					break;
			}
		}
		return $_j;
	}
	private function _bt($_n,$_t,$_data){
		return '<a class="mtfBBcode-'.$_t.' '.$this->_lang.' m-button" data=\''.json_encode($_data).'\'>'.$_n.'</sup></a>';
	}
	private function _key($_c,$_j){
		if(@$_j['参数原文']){
			$_c=str_replace($_j['参数原文'],'',$_c);
		}
		if(@$_j['数据原文']){
			$_c=str_replace($_j['数据原文'],'',$_c);
		}
		//获取卡密
		$_key=array();
		$_a=explode('</p>',$_c);
		foreach($_a as $_k=>$_v){
			$_key[]=strip_tags($_v);
		}
		return array_values(array_filter($_key));
	}
}
?>