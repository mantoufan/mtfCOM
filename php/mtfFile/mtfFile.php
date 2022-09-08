<?php 
include_once __DIR__ . '/../mtfUnit/mtfUnit.php';
include_once __DIR__ . '/../mtfGuid/mtfGuid.php';
include_once __DIR__ . '/../mtfUrl/mtfUrl.php';
include_once __DIR__ . '/../mtfCrypt/mtfCrypt.php';
include_once __DIR__ . '/../mtfSub/mtfSub.php';
include_once __DIR__ . '/../mtfKey/mtfKey.php';
include_once __DIR__ . '/../mtfColor/mtfColor.php';
include_once __DIR__ . '/../LanguageDetector/api.php';
include_once __DIR__ . '/../mtfProxyCurl/mtfProxyCurl.php';
include_once __DIR__ . '/../mtfBBcode/mtfBBcode.php';
include_once __DIR__ . '/../mtfRand/mtfRand.php';
include_once __DIR__ . '/../mtfCode/mtfCode.php';
include_once __DIR__ . '/../mtfZH/mtfZH.php';
include_once __DIR__ . '/../mtfKeyword/mtfKeyword.php';
include_once __DIR__ . '/../mtfFileConf/mtfFileConf.php';

class mtfFile{
	/** 配置：与 mtfFileConf 一致：开始 */
	public $maxTime;// 注意配置 Web 服务器的超时时间
	private $root = '';
	public $dir = array();
	public $conf = array();
	public $db = array();
	/** 配置：结束 */
	private $mtfUnit;
	private $mtfMysql;
	private $mtfQueue;
	public $mtfGuid;
	private $mtfUrl;
	private $mtfAttr;
	private $mtfCrypt;
	private $mtfRelate;
	private $mtfBBcode;
	private $mtfRand;
	public $mtfSub;
	public $mtfKey;
	public $mtfApiLanguageDetector;
	public $mtfCode;
	public $mtfZH;
	public $mtfKeyword;
	public $mtfProxyCurl;
	private $mtfColor;
	private $__config = array('w' => '', 'h' => '', 'nl' => 0, 'default' => '', 'ext' => '');
	private $_cache = array();
	private $_var = array();
	public function __construct($config = array()) {	
		if (empty($config['root'])) return;
		ini_set('memory_limit', '512M');
		$this->mtfUnit = new mtfUnit();
		$this->mtfGuid = new mtfGuid();
		$this->mtfUrl = new mtfUrl();
		$this->mtfCrypt = new mtfCrypt();
		$this->mtfSub = new mtfSub();
		$this->mtfKey = new mtfKey();
		$this->mtfColor = new mtfColor();
		$this->mtfApiLanguageDetector = new mtfApiLanguageDetector();
		$this->mtfProxyCurl = new mtfProxyCurl();
		$this->mtfBBcode = new mtfBBcode();
		$this->mtfRand = new mtfRand();
		$this->mtfCode = new mtfCode();
		$this->mtfZH = new mtfZH();
		$this->mtfKeyword = new mtfKeyword();
		$mtfFileConf = new mtfFileConf();
		$this->maxTime = $mtfFileConf->maxTime;
		$this->dir = $mtfFileConf->dir;
		$this->conf = $mtfFileConf->conf;
		$this->db = $mtfFileConf->db;

		$this->conf = array_merge(array(
			'ext2type' => json_decode(file_get_contents(__DIR__ . '/' . 'json/ext2type.json'), true),
			'minetype2ext' => json_decode(file_get_contents(__DIR__ . '/' . 'json/minetype2ext.json'), true),
		), $this->conf);
		
		$this->root = $config['root'];
		if (is_dir($this->root) === false) mkdir($this->root);
		foreach ($this->dir as $_k => $_v) {
			$this->dir[$_k] = $this->root . '/' . $_v . '/';
			if (is_dir($this->dir[$_k]) === false) mkdir($this->dir[$_k]);
		}
	}
	
	public function config($_d_p) {
		$_d = array('p' => $_d_p, 'c' => array());
		$_c = strstr(substr(strchr($_d['p'], '_c_'), 3), '.', true);
		if ($_c) {
			$_ar = explode('_', $_c);
			$_k = '';
			foreach($_ar as $v){
				if($_k === ''){
					$_k = $v;
				}else{
					$_d['c'][$_k] = $v;
					$_k = '';	
				}
			}
		}
		return $_d['c'];
	}
	public function e2dir($_e) {
		if (in_array($_e, $this->conf['oss']['ext'])) return $this->dir['oss'];
		return $this->dir['file'];
	}
	public function n2dir($_n) {
		return substr($_n,0,6).'/';
	}
	
	public function n2date($_n) {
		return substr($_n,0,4).'-'.substr($_n,4,2).'-'.substr($_n,6,2).' '.substr($_n,8,2).':'.substr($_n,10,2).':'.substr($_n,12,2);
	}
	
	public function en($_id)
	{
		return $_id?$_id.'p'.md5($_id.'madfan'):'';
	}
	
	public function de($_id)
	{
		$_a=explode('p',$_id);
		return $_a[0];
	}
	
	private function _txt($_n,$_t,$_a,$_c='')
	{
		$_p=$this->dir[$_t].$this->n2dir($_n).$_n.'.txt';
		if($_a==='w'){
			if(is_array($_c)){
				$c=$this->mtfUnit->JsonEncodeCN($_c);
			}
			return file_put_contents($_p,$_c);
		}elseif($_a==='r'){
			return file_get_contents($_p);
		}elseif($_a==='p'){
			return $_p;
		}
		return false;
	}
	
	private function _log($_n,$_a,$_c='')
	{
		return $this->_txt($_n,'log',$_a,$_c);
	}
	
	private function _info($_f_p)
	{
		$_f=$this->pathInfo($_f_p);
		
		$_f['i']=array('mimetype'=>@$_f['i']['mimetype'],'duration'=>'','bitrate'=>'','filesize'=>'','filesizekb'=>'','filesizemb'=>'');
		if(file_exists($_f['p'])){
			if($_f['t']==='image'){
				$_i=getimagesize($_f['p']);
				$_f['i']['width']=$_i[0];
				$_f['i']['height']=$_i[1];
				$_f['i']['mime']=$_i['mime'];
			}elseif($_f['t']==='video'||$_f['t']==='audio'){
				$_l=shell_exec('ffprobe "'.$_f['p'].'" 2>&1');
				preg_match("/Duration: (\S+),/", $_l, $_ar);
				$_f['i']['duration']=$this->mtfUnit->time2ms($_ar[1]);
				preg_match("/bitrate: (\S+)/", $_l, $_ar);
				$_f['i']['bitrate']=$_ar[1];
			}
			$_f['i']['filesize']=filesize($_f['p']);
			$_f['i']['filesizekb']=$this->mtfUnit->subStr(round($_f['i']['filesize']/1024,2),5).'KB';
			$_f['i']['filesizemb']=$this->mtfUnit->subStr(round($_f['i']['filesize']/1048576,2),5).'MB';
		}
		return $_f;
	}
	
	public function pathInfo($_p='')
	{
		$_o=array('p'=>'','e'=>'','n'=>'','bn'=>'','t'=>'','d'=>'','i'=>array('mimetype'=>''),'h'=>'');
		if($_p){
			$_a=explode('?',$_p);
			$_p=$_a[0];
			$_o['p']=$_p;
			$_i=pathinfo($_o['p']);
			$_o['e']=strtolower(@$_i['extension']);
			$_o['n']=$_i['basename'];
			$_o['bn']=$this->mtfUnit->rtrim($_i['basename'],'.'.@$_i['extension']);
			$_o['id']=$this->mtfUnit->rtrim($_o['bn'],strstr($_o['bn'],'_c_'));
			$_o['id']=($_o['id']?$_o['id']:$_o['bn']);
			
			settype($_o['id'],'string');
			
			$_o['d']=$_i['dirname'];
			$_o['mid']=substr($_o['id'],0,6);
			
			
			$_o['t']=@$this->conf['ext2type'][strtolower($_o['e'])];//iphone的MOV是大写的	
		}
		return $_o;	
	}
	
	public function trueExt($_p){
		$_i=pathinfo($_p);
		$_ext=strtolower(@$_i['extension']);
		$_i= new finfo(FILEINFO_MIME_TYPE);
		if(file_exists($_p)){
			$_mimetype=$_i->file($_p);
			if($_mimetype){
				$_e=$this->conf['minetype2ext'][$_mimetype];
				if($_e==='*'||(strpos($_e,$_ext)) !== false){
					
				}else{
					return explode(',',$_e)[0];
				}
			}
		}
		return false;
	}
	
	private function _404(){
		http_response_code(404);
		exit;
	}
	
	public function convert($_f_p, $_d_p, $queue = 0, $_force = '0')
	{ 
		if (file_exists($_d_p) === true && filesize($_d_p) > 0) return true;
		$_f = $this->_info($_f_p);
		$_d = $this->pathInfo($_d_p);
		$_d['c'] = $this->config($_d['p']);
		
		if($_f['t']==='image'){
			$_c=@$_d['c'];
			include_once __DIR__ . '/../Grafika/autoload.php';
			$editor = Grafika\Grafika::createEditor();
			
			if(@$_c['p']){
				$_p=explode(',',$_c['p']);
				$_f['p']=$this->mtfRand->get('img',$_p[0],$_p[1]);
				$_f=$this->_info($_f['p']);
				unset($_p);
			}
			
			if(file_exists($_f['p'])){
				$editor->open($image, $_f['p']);
				$_w=$image->getWidth();
				$_h=$image->getHeight();
			}else{
				$_w=300;
				$_h=300;
				$image=Grafika\Grafika::createBlankImage($_w,$_h);
			}
			
			//限制可选宽度和高度
			if(isset($_c['w']) && intval($_c['w']) !== $_w && in_array($_c['w'], $this->conf['convert']['image']['widths']) === false){
				$this->_404();
			}
			if(isset($_c['h']) && intval($_c['h']) !== $_h && in_array($_c['h'], $this->conf['convert']['image']['heights']) === false){
				$this->_404();
			}
			
			//剪裁
			if(@$_c['ace']){
				$_a=$this->_av2url($_c['ace'],$_f['id']);
				if(@$_a['csw']){
					$editor->resizeExactWidth($image, $_a['csw']);
					$editor->crop($image, $_a['cw'], $_a['ch'], 'top-left', $_a['cx'], $_a['cy']);
					
					//非GD处理模式BUG修复：强制改变裁剪后图片的宽和高，为后续处理铺垫
					$editor->resizeExact($image, $_a['cw'], $_a['ch']);
					$_c['c']='';//让裁剪模式失效
				}
			}
			//文本
			if(@$_c['txt']){//第一位为文本，第二位为位置，第三位为字号，第四位为颜色
				$_font=explode(',',$_c['txt']);
				if(!@$_font[0]){
					$_s='';
				}else{
					$_s=$_font[0];
				}
				if(!@$_font[1]){
					$_font[1]='center';
				}
				if(!@$_font[2]){
					$_font[2]=12;
				}
				if(!@$_font[3]){
					$_font[3]='FFFFFF';
				}
				$_lh=$_font[2];//字高
				$_l=$this->mtfUnit->strLen($_s)*8.5/12*$_lh;//字长
				$_co='#'.$_font[3];//颜色
				switch($_font[1]){
					case 'top-left':
						$_x=0;
						$_y=0;
					break;
					case 'top-center':
						$_x=($_w-$_l)/2;
						$_y=0;
					break;
					case 'top-right':
						$_x=$_w-$_l;
						$_y=0;
					break;
					case 'center-left':
						$_x=0;
						$_y=($_h-$_lh)/2;
					break;
					case 'center':
						$_x=($_w-$_l)/2;
						$_y=($_h-$_lh)/2;
					break;
					case 'center-right':
						$_x=$_w-$_l;
						$_y=($_h-$_lh)/2;
					break;
					case 'bottom-left':
						$_x=0;
						$_y=$_h-$_lh;
					break;
					case 'bottom-center':
						$_x=($_w-$_l)/2;
						$_y=$_h-$_lh;
					break;
					case 'bottom-right':
						$_x=$_w-$_l;
						$_y=$_h-$_lh;
					break;
				}
				$editor->text($image, $_s, $_lh, $_x, $_y, new Grafika\Color($_co));
				unset($_font,$_l,$_lh,$_s,$_co);
			}
			
			//w 宽度 h 高度 nl 不要放大 c裁剪
			if(@$_c['c']){
				if($_c['c']==='s'){
					$editor->crop($image, $_c['w'], $_c['h'], 'smart');	
				}elseif($_c['c']==='f'){
					if($_f['e']==='gif'){
						$editor->resizeExact($image, floor($_c['h']/$_f['i']['height']*$_f['i']['width']), $_c['h']);
					}else{
						$editor->resizeFill($image, $_c['w'], $_c['h']);
					}
				}
			}else{
				if(@$_c['w']){
					if(@$_c['nl']&&$_f['i']['width']<$_c['w']){
					}else{
						if($_f['e']==='gif'){
							$editor->resizeExact( $image, $_c['w'], floor($_c['w']/$_f['i']['width']*$_f['i']['height']) );
						}else{
							$editor->resizeExactWidth($image, $_c['w']);
						}
					}
				}elseif(@$_c['h']){
					if(@$_c['nl']&&$_f['i']['height']<$_c['h']){
					}else{
						$editor->resizeExactHeight($image, $_c['h']);
					}
				}
			}

			$editor->save($image, $_d['p']);

			//首次预览时，对预览图进行校正
			$this->_get_ture_orientation_img($_d['p']);
			return true;
		} elseif ($_f['t'] === 'video' || $_f['t'] === 'audio') {
			$_l = $this->_log($_d['n'], 'p');
			if($_f['i']['bitrate'] > $_d['c']['b']){
				$_c_b = $_d['c']['b'];
			} else {
				if ($_force !== '1') return false;
				$_c_b = $_f['i']['bitrate'];
			}
			if ($_f['t'] === 'video') {
				$_h = popen('ffmpeg -hwaccel qsv -i "' . $_f['p'] . '" -y -threads 2 -preset faster -ac 2 -b:a 384k -vf "scale=' . $_d['c']['w'] . ':-2" -b ' . $_c_b . 'k -bufsize ' . $_c_b . 'k -crf 28 -bf 2 -r 24 -g 12 -coder 1 -movflags +faststart -tune zerolatency -x264opts opencl "' . $_d['p'] . '" 1>"' . $_l . '" 2>&1', 'r');
			}else{
				$_h = popen('ffmpeg -hwaccel qsv -i "' . $_f['p'] . '" -y -threads 2 -preset faster -ac 2 -b:a ' . $_c_b . 'k "' . $_d['p'] . '" 1>"' . $_l . '" 2>&1', 'r');	
			}
			while (true) {
				sleep(2);
				$_log = $this->_log($_d['n'], 'r');
				if (!$_log) continue;
				preg_match("/video:(\d+)kB/", $_log, $_ar);
				$_v['videoSize'] = $_ar[1];
				preg_match("/audio:(\d+)kB/", $_log, $_ar);
				$_v['audioSize'] = $_ar[1];
				preg_match("/Input[\S\s]*?,\s(\d+)x(\d+)\s/", $_log, $_ar);
				$_v['swidth'] = $_ar[1];
				$_v['sheight'] = $_ar[2];
				preg_match("/Output[\S\s]*?,\s(\d+)x(\d+)\s/", $_log, $_ar);
				$_v['width'] = $_ar[1];
				$_v['height'] = $_ar[2];
				if(empty($_v['videoSize']) === false || empty($_v['audioSize']) === false){
					$_filesizekb = 0;
					if (empty($_v['videoSize']) === false) $_filesizekb += $_v['videoSize'];
					if (empty($_v['audioSize']) === false) $_filesizekb += $_v['audioSize'];
					$_f['i']['filesizekb'] = $_filesizekb . 'KB';
					return array(
						'原始比特率' => $_f['i']['bitrate'],
						'比特率' => $_d['c']['b'],
						'时长' => $_f['i']['duration'],
						'大小' => $_f['i']['filesizekb'],
						'原始宽度' => $_v['swidth'],
						'原始高度' => $_v['sheight'],
						'宽度' => $_v['width'],
						'高度' => $_v['height']
					);
				}else{
					preg_match_all("/time=(\S+)/", $_log, $_ar);
					if (empty($_ar[1])) {
						if ($queue) $this->mtfQueue->urlError($queue);	
						return false;	
					}
					$_v['time'] = $this->mtfUnit->time2ms(end($_ar[1]));
					if ($queue) $this->mtfQueue->urlUpdate($queue, round($_v['time'] / $_f['i']['duration'] * 100, 2) . '%');
				}
			}
		} elseif ($_f['t'] === 'doc') {
			$_h = exec('LibreOfficePortable --headless -convert-to ' . $_d['e'] . ' "' . $_f['p'] . '" -outdir "' . $_d['d'] . '"');
			//目标文件名与源文件一样
			return true;
		}
	}
	
	public function config2Url($_config=array())
	{
		$u='';
		if($_config){
			$u='_c';
			foreach($_config as $_k => $_v){
				if($_k==='default'){
					continue;
				}elseif($_v){
					$u.='_'.$_k.'_'.$_v;
				}
			}
		}
		return $u;
	}
	
	private function _set_people_cache($_sql,$_uid){
		
		$_r=$this->mtfMysql->sql('s',$this->db['table'],'i,a,k,nz,nz0,nz1,nfol,nto,nfol1,nmsg1,ip','WHERE '.$_sql);
		foreach($_r as $_k=>$_v)
		{
			$_i=$_v['i'];
			$this->_cache['list'][$_i]=array('a'=>$this->mtfAttr->parseA($_v['a'],'|'),'k'=>$this->mtfAttr->parseA($_v['k']),'o'=>$_v['i']);
			$_dn=$this->_dn($_i,1);//绑定域名
			if($_dn){
				$this->_cache['list'][$_i]['dn']=$_dn;
			}
			unset($_v['i']);unset($_v['a']);unset($_v['k']);
			
			if($_uid===$_i){
				// $_v['self']=1;
				$this->_cache['list'][$_i]['count']=array_filter($_v);//过滤为0的数字
			}else{
				unset($_v['nz1']);unset($_v['nfol1']);unset($_v['nmsg1']);
			}
		}
	}
	
	private function _av2url($_av,$_key=''){//头像数据转Url x.jpg_csw_cw_ch_cx_cy
		if($_key){
			$_a=explode('_',$this->mtfCrypt->de($_av,$_key));
			$_r=array();
			$_csw=1;
		}else{
			$_a=explode('_',$_av);
			$_b=explode('.',$_a[0]);
			$_id=$_b[0];
			$_ace=array();
			if($_a[1]){
				array_shift($_a);
				$_ace=array('ace'=>$this->mtfCrypt->en(implode('_',$_a),$_id));
				$_csw=1;
			}else{
				$_csw=0;
			}
			$_r=array('avi'=>$_b[0],'ave'=>$_b[1])+$_ace;
		}
		return ($_csw?array('csw'=>$_a[0],'cw'=>$_a[1],'ch'=>$_a[2],'cx'=>$_a[3],'cy'=>$_a[4]):array())+$_r;
	}
	
	private function _waifu2url($_av,$_key=''){//waifu2x放大图片 x.jpg_s_n
		if($_key){
			$_a=explode('_',$this->mtfCrypt->de($_av,$_key));
		}else{
			$_a=explode('_',$_av);
			$_b=explode('.',$_a[0]);
			$_id=$_b[0];
			
			$_waifu=$this->mtfCrypt->en(implode('_',$_a),$_id);
			
		}
		return @$_waifu?$_waifu:array('s'=>$_a[1],'n'=>$_a[2]);
	}
	
	private function _get_people($_f_id='',$_tpl='card',$_uid='',$_nm=''){
		if(@$_f_id){
			$__a=array();
			//如果有缓存，优先读取缓存
			if(!@$this->_cache['list'][$_f_id]){
				$this->_set_people_cache('i='.$_f_id,$_uid);
			}
			$_cache=@$this->_cache['list'][$_f_id];
			if(@$_cache['k']){
				$_key=$_cache['k'];
				$_title=@$_key['标题'][0];
				$_des=preg_replace('/：(\d+)/',':$1',strtr(@$_key['描述'][0],array('：//'=>'://')));;
				unset($_key['标题']);
				unset($_key['描述']);
			}
			if(@$_cache['a']){
				$_attr=$_cache['a'];
				
				if(@$_attr['头像'][0]){
					$_a=$this->_av2url(@$_attr['头像'][0]);
					$__a['avi']=$_a['avi'];
					$__a['ave']=$_a['ave'];
					$__a['ace']=$_a['ace'];
				}else{
					$__a['avn']=$this->_uname($_f_id);//根据名称生成的唯一标识符
				}
				
				if(@$_attr['实名'][0]==='通过'){
					if(@$_title===@$_attr['名称'][0]){
						$__a['real']=1;
					}
				}
			}
			if(@$_cache['dn']){
				$__a['dn']=$_cache['dn'];
			}
			if(@$_title){
				$__a['title']=$_title;
			}
			if($_tpl==='card'){
				if(@$_des){
					$_a=$this->mtfBBcode->parse($_des,array('type'=>'tdk'));
					$__a['des']=@$_a['s'];
					if(@$_a['b']){
						$__a['b']=$_a['b'];
					}
				}
				if(empty($_key) === false){
					$_tag = $this->mtfAttr->parseK($_key);
					if($_tag){
						$__a['tag']=explode(',',str_replace('标签:','',implode(',',$_tag)));
					}
				}
				if(@$_cache['count']){//统计信息
					$__a['count']=$_cache['count'];
					if(@$this->_cache['dashen'][$_f_id]){
						$__a['count']['nz_p']=$this->_cache['dashen'][$_f_id];
					}
				}
				if(@$this->_cache['nmsg1'][$_f_id]){
					$__a['nmsg1']=$this->_cache['nmsg1'][$_f_id];
				}
			}	
			$_data=array('i'=>$_f_id)+$__a;
		}else{
			$_data['i']=0;
			$_data['title']=@$_nm?$_nm:$this->_mtflang2span('匿名');//匿名
			if(@$this->_cache['nmsg1'][$_data['i']]){
				$_data['nmsg1']=$this->_cache['nmsg1'][$_data['i']];
			}
			$_data['avn']=$this->_uname($_data['title']);//根据名称生成的唯一标识符
		}
		
		return $_data;
	}
	
	private function _get_data($_v,$_attr){
		$_uid = $this->uid2id($_SERVER['HTTP_UID']);
		$_d = array();
		$_t = $this->conf['ext2type'][$_v['e']];
		if($_t==='people'){
			$_i=$_v['i'];
			if($_uid){
				if(in_array($_uid,@$_attr['粉丝'])){//当前用户是否为作者的粉丝
					$_d['fol']=1;
				}
			}
		}else{
			if(@$_attr['关注可见'][0]){
				$_d['xf']=1;
			}
			if(@$_attr['禁止回复'][0]){
				$_d['br']=1;
			}
			if(@$_attr['作者可见'][0]){
				$_var['reply_author']=1;
			}
			if($_t==='video' || $_t==='audio'){
				if(@$_attr['字幕']){
					$_a=$this->_getSub($_attr['字幕']);
					$_d['cap']=$_a['cap'];
				}
			}
			$_i=$_v['i'];
			if($_v['o']){
				$_d['o']=$_v['o'];
			}
		}
		$_d['r']=$this->mtfRight($_uid,$_i);
		
		if(@$_v['nz']){
			$_d['zan']=$_v['nz'];
		}
		if(@$_v['nrel']){
			$_d['rel']=$_v['nrel'];
		}
		if(@$_v['t1']){
			$_d['tag']['t1']=$_v['t1'];
		}
		/*
		if(@$_v['k']){
			$_d['tag']=$this->mtfAttr->parseA($_v['k']);
		}
		*/
		//获取弹幕（最近5条） 包含最近评论
		if(!in_array($_v['e'],array('people','mtftag')) && $_v['r']){
			$__d=$this->mtfQueueList(array('r'=>$_v['r'],'tpl'=>'dm','dm'=>'1','page'=>'1_100','order'=>'i DESC','reply_author'=>@$_var['reply_author']));
			if($__d){
				$_d['dm']=$__d;
			}
		}
		
		//获取红包
		if(@$this->_var['task']){//如果是 完成任务送红包
			if($_v['o'] && !in_array($_v['o'],@$this->_var['task']['u'])){//匿名用户和已经领过的用户 不能领取红包
				$_d['task']=array('i'=>$this->_var['task']['i'],'o'=>$_v['o']);
			}
		}
		
		if($_v['w']){
			$_d['w']=json_decode($_v['w'],true);
			
			if($_uid===$_v['o']){
				if($_d['w']['t']){//如果是 完成任务送红包
					$this->_var['task']=array('i'=>$_v['i'],'u'=>$_d['w']['t'][0]['u']);
				}
			}
			
			
		}
		//获取重复
		if(@$_attr['主人'][0]){
			$_d['pok']=$this->_get_people($_attr['主人'][0],'info',$_uid);
		}
		
		//如果有赞
		if($this->_cache['zan'][$_v['i']]){
			$_d['zp']=$this->_cache['zan'][$_v['i']];
		}
		
		return $_d;
	}
	
	//w 缩略图宽度 n 上传文件名
	public function getPreView($_f_p, $_config = array(), $_arv = array())
	{
		$_arv2 = array('n' => '', 'url' => '');
		$_config = array_merge($this->__config, $_config);
		$_arv = array_merge($_arv2, $_arv);
		$_f = $this->_info($_f_p);
		$_d = $this->pathInfo();
		$_config['ext'] = $_f['e'];
		$_d['p'] = $_f['d'] . '/' . $_f['id'] . $this->config2Url($_config) . '.';
		
		if($_f['t']==='image'){
			$_d['p'].=$_f['e'];
			//校正图像方向（上传时不再校正，避免影响md5值）
		}elseif($_f['t']==='video'){
			$_d['p'].=$this->conf['preview'][$_f['t']]['ext'];
			
			if(file_exists($_d['p']) === false){
				if($_f['i']['duration']){
					$_ss=floor($_f['i']['duration']/1000/3);
				}else{
					$_ss=0;
				}
				$_t=7;
				$_r=1;
				exec('ffmpeg -ss '.$_ss.' -t '.$_t.' -i "'.$_f['p'].'" -r '.$_r.' -vf "scale='.$_config['w'].':-1,format=yuv420p" -f image2 "'.$_f['d'].'/'.$_f['bn'].'-%03d.jpg"');
				exec('ffmpeg -i "'.$_f['d'].'/'.$_f['bn'].'-%03d.jpg" -filter_complex scale=120:-1,tile=3x3 "'.$_f['d'].'/'.$_f['bn'].'.jpg"');
				exec('ffmpeg -f image2 -framerate 5 -i "'.$_f['d'].'/'.$_f['bn'].'-%03d.jpg" "'.$_f['d'].'/'.$_f['bn'].'.'.$this->conf['preview'][$_f['t']]['ext'].'"');
				for ($i=1; $i<=($_t+2); $i++) {
					$j=($this->mtfUnit->strLen($i)===1?'00'.$i:'0'.$i);
					@unlink($_f['d'].'/'.$_f['bn'].'-'.$j.'.jpg');
				}	
			}
		}elseif($_f['t']==='audio'){
			$_d['p'].=$this->conf['preview'][$_f['t']]['ext'];
			
			if(file_exists($_d['p']) === false){
				exec('ffmpeg -i "'.$_f['p'].'" -filter_complex "compand,showwavespic=s=640x50:colors=#666666" -frames:v 1 "'.$_f['d'].'/'.$_f['bn'].'.'.$this->conf['preview'][$_f['t']]['ext'].'"');//compand，扩大音频高度，充满画布，让声音较小的音频也能获取指纹
			}
		}elseif($_f['t']==='people'){
			$_attr=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_f['id'],0,'|');
			if(@$_attr['a']['头像'][0]){
				$_a=$this->_av2url($_attr['a']['头像'][0]);
				$_config['ace']=$_a['ace'];
				$_d['p']=$this->dir['file'].$this->n2dir($_a['avi']).$_a['avi'].$this->config2Url($_config).'.'.$_a['ave'];
			}else{
				$_f=$this->pathInfo($_config['default']);
				$_d['p']=$this->dir['file'].$this->n2dir($_f['id']).$_f['id'].$this->config2Url($_config).'.'.$_f['e'];	
			}
		} else {
			$_d['p'].='jpg';
			
			if(file_exists($_d['p']) === false){
				include_once __DIR__ . '/../Grafika/autoload.php';
				$editor = Grafika\Grafika::createEditor();
				$image = Grafika\Grafika::createBlankImage(600,60);
				$editor->draw($image, Grafika\Grafika::createDrawingObject('Rectangle', 600, 60, array(0, 0), 0, null, '#333333'));
				$editor->draw($image, Grafika\Grafika::createDrawingObject('Rectangle', 320, 40, array(140, 10), 0, null, '#FFFFFF'));
				$editor->text($image, $_f['e'], 24, 70-$this->mtfUnit->strLen($_f['e'])*8.5, 16, new Grafika\Color('#FFFFFF'));
				$_fs = $_f['i']['filesizemb'];
				if ($_fs === '0MB') $_fs=$_f['i']['filesizekb'];
				$editor->text($image, $_fs, 24, 530-$this->mtfUnit->strLen($_fs)*8.5, 16, new Grafika\Color('#FFFFFF'));
				$_arv['n']=$this->mtfUnit->subStr($_arv['n'], 18,'..');
				$editor->text($image, $_arv['n'], 24, 300-$this->mtfUnit->strLen($_arv['n'])*8.5, 16, new Grafika\Color('#333333'), __DIR__ . '/../Grafika/fonts/wenquanyidengkuanzhenghei.ttf');
				$editor->save($image, $_f['d'].'/'.$_f['bn'].'.jpg');
			}
		}
		if ($_arv['url']) {
			return implode('/', array_slice(explode('/', explode($this->root, $_d['p'])[1]), 3));
		}else{
			return $_d['p'];
		}
	}
	
	private function _mtflang2span($_s){
		return '<span class="m-lang">'.$_s.'</span>';
	}
	
	private function _k2ch($_k,$_ch){
		switch($_k){
			case 'index':
				$_t=$this->conf['name'];
			break;
			case 'msg':
				$_t='消息';
			break;
			case 'fol':
				$_t='粉丝';
			break;
			case 'to':
				$_t='关注';
			break;
			case 'zan':
				$_t=$_ch;
			break;
			case 'rank':
				$_t='大神';
			break;
			case 'weal':
				$_t='赚 ♥';
			break;
			case 'swap':
				$_t='花 ♥';
			break;
			case 'my':
				$_t='我的 帖子';
			break;
			case 'block':
				$_t='检查 公告';
			break;
			default:
				$_t=$_k;
		}
		return $_t;
	}
	public function siteMap($_host = '', $_index = ''){
		mkdir('cache');
		$_var_sitemap = 'cache/' . str_replace('.', '-', $_host) . '-sitemap';
		if($_index === 'update'){
			set_time_limit($this->maxTime);
			$_dn_id = array_flip($this->conf['dn'])[$_host];
			$_sql = $_dn_id ? 'AND o=\'' . $_dn_id . '\'' : '';
			$_hi = '<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			$__d = 10000;
			$_r = $this->mtfMysql->sql('s', $this->db['table'], 'i,k,t0', 'WHERE e=\'mtfdat\' AND ar =\'\' AND aw =\'\' AND p<999999999 ' . $_sql . ' ORDER BY t0 DESC'); // 排除文章内的回复
			$_l_t_ar = array();
			$_l = 0;
			$_h = '';
			if($_r){
				$_l_r = count($_r);
				foreach($_r as $_k => $_v){
					$_i = intval($_k / $__d);
					if($_i === $_l){
						if($_h){
							$_h .= '</urlset>';
							file_put_contents($_var_sitemap . $_i . '.xml', $_h);
							$_hi .= '<sitemap><loc>https://' . $_host . '/sitemap' . $_i . '.xml</loc><lastmod>' . $_l_t_ar[$_i - 1] . '</lastmod></sitemap>';
						}
						$_h = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';
						$_l++;
					}
					$_h .= '<url>';
					$_h .= '<loc>https://' . $_host . '/' . $_v['i'] . '</loc>';
					$_l_t = (new DateTime($_v['t0']))->format('c');
					if (isset($_l_t_ar[$_i]) === false) $_l_t_ar[$_i] = $_l_t;
					$_h.='<lastmod>' . $_l_t . '</lastmod>';
					$_p = $this->dir['file'] . $this->n2dir($_v['i']) . $_v['i'] . '.mtfdat';
					$_d = $this->getContent($_p, 'sitemap');
					if(empty($_d['img']) === false){
						foreach($_d['img'] as $_v2){
							$_h .= '<image:image><image:loc>' . $_v2['loc'] . '</image:loc></image:image>';
						}
					}
					if(empty($_d['video']) === false){
						$_key = $this->mtfAttr->parseA($_v['k'], '|');
						foreach ($_d['video'] as $_v2) {
							$_h .= '<video:video>';
							$_h .= '<video:thumbnail_loc>' . $_v2['thumbnail_loc'] . '</video:thumbnail_loc>';
							$_h .= '<video:title><![CDATA[' . (
								$_v2['title'] ? $_v2['title'] : (
									empty($_key['标题']) ? '' : $_key['标题'][0]
								)) . ']]></video:title>';
							$_h .= '<video:description><![CDATA[' . (
								$_v2['description'] ? $_v2['description'] : (
									empty($_key['描述']) ? $_v2['loc_description'] : $_key['描述'][0]
								)) . ']]></video:description>';
							$_h .= '<video:content_loc>' . $_v2['content_loc'] . '</video:content_loc>';
							if ($_v2['duration']) $_h.='<video:duration>' . intval($_v2['duration'] / 1000) . '</video:duration>';
							$_h .= '<video:publication_date>' . $_v2['publication_date'] . '</video:publication_date>';
							$_h .= '</video:video>';
						}
					}
					$_h .= '</url>';
					
					if($_k === $_l_r - 1){
						if ($_h === '') continue;
						$_i++;
						$_h .= '</urlset>';
						file_put_contents($_var_sitemap . $_i . '.xml', $_h);
						$_hi .= '<sitemap><loc>https://' . $_host . '/sitemap' . $_i . '.xml</loc><lastmod>' . $_l_t_ar[$_i - 1] . '</lastmod></sitemap>';
					}
				}
				$_hi .= '</sitemapindex>';
				file_put_contents($_var_sitemap . '.xml', $_hi);
			}
		} else {
			if (time() - filemtime($_var_sitemap.'.xml') > 3600) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->conf['domain']['dat'] . '/sitemap/' . $_host . '/update/');
				curl_setopt($ch, CURLOPT_TIMEOUT, 1);
				curl_exec($ch);
			}
			return file_get_contents($_var_sitemap . $_index . '.xml');
		}
	}
	//类型：view 预览 down 下载
	public function getContent($_f_p, $_t='view', $_arv = array(), $_config = array()) {
		$_arv2 = array('mtfdat' => array('pattern' => "[\d+]{6,18}"));
		$_config = array_merge($this->__config, $_config);
		$_arv = array_merge($_arv2, $_arv);
		$_f = $this->pathInfo($_f_p);
		$_f['des'] = '';
		$_d = $this->pathInfo();
		$_img = array();
		
		if($_f['t']==='mtfdat'){  
			
			switch($_t){
				case 'view':
					$_c=file_get_contents($_f['p']);
					return $_c;
				case 'mustache':
					$_f['p']=$this->fileOrTmp($_f['n']);
					$_c=file_get_contents($_f['p']);
					
					//先进行BBcode的CC代码解析，避免其中的图片被解析
					if(!@$_arv['preview']){
						$_uid=$this->uid2id($_SERVER['HTTP_UID']);
						$_c=$this->mtfBBcode->parse($_c,array('uid'=>$_uid,'id'=>$_f['id']));
					}
					
					$_exts=implode('|',array_keys($this->conf['ext2type']));
					preg_match_all("/(".$_arv['mtfdat']['pattern']."\.(".$_exts."))/", $_c, $_m);
					$_ar=array('tpl'=>'');
					foreach($_m[0] as $_k => $_v){
						$_d=$this->pathInfo($_v);
						if(empty($_ar[$_d['id']])){
							$_ar['sub'][$_d['id']]=array();
						}
						if($_d['e']==='url'){
							$__a=array('u'=>$this->getContent($this->fileOrTmp($_d['n']),'view'));
							if(@$_arv['preview']){
								$_ar['sub'][$_d['id']]['preview']['url']=$__a;
							}else{
								$_ar['sub'][$_d['id']]['url']=$__a;
							}
						}elseif($_d['e']==='mtfdat'){
							if(@$_arv['preview']){
								$_v='';
							}else{
								$_p=$this->dir['file'].$this->n2dir($_d['id']).$_d['id'].'.'.$_d['e'];
								if($_c===$_d['id'].'.'.$_d['e']){//直接引用文章（显示内容）
									return $this->getContent($_p,'mustache');
								}else{//引用多篇文章（列表模版）
									$_ar['sub'][$_d['id']]=$this->getContent($_p,'list',array('mtfdat'=>$this->conf['list'],'load'=>$_d['id'],'pi'=>1));
								}
							}
						}else{
							if(@$_arv['preview']){
								$_t=$this->conf['ext2type'][$_d['e']];
								
								if($_t==='video'||$_t==='audio'){
									
								}
								$__a=array('ext'=>$_d['e'])+$this->_isDownGifExt($_d['e']);
								
								$_ar['sub'][$_d['id']]['preview']['img']=$__a;
							}else{
								$_t=$this->conf['ext2type'][$_d['e']];
								
								if($_t==='video'||$_t==='audio'){
									$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a,k','WHERE i="'.$_d['id'].'"',0,'|');
									$_attr=$_r['a'];
									$_key=$_r['k'];
									
									$_ar['sub'][$_d['id']][$_t]=$this->_getMedia($_d['id'],$_t,$_attr,$_key);
									
								} elseif ($_t === 'rom') {
									$_ar['sub'][$_d['id']][$_t] = array('e' => $_d['e']);
								}else{
									//建立文章中图片（含文件）缓存
									$_img[]=$_d['id'];
									
								}
								
							}
						}
						
						$_c=str_replace($_v,'{{#'.$_d['id'].'}}{{>base}}{{/'.$_d['id'].'}}',$_c);
					}
					
					if($_img){
						$_is=implode(',',$_img);
						$_r=$this->mtfMysql->sql('s',$this->db['table'],'a,k,e,i,url,ch,cs,cv','WHERE i IN ('.$_is.') ORDER BY FIELD(i,'.$_is.')');
						if($_r){
							$this->_cache['img'][$_f['id']]=array();
							foreach($_r as $_k=>$_v){
								$__a=$this->_isDownGifExt($_v['e'],'',$_v['i']);
								$_key=$this->mtfAttr->parseA($_v['k'],'|');
								$_attr=$this->mtfAttr->parseA($_v['a'],'|');
								if(empty($_key['标题']) === false){
									$__a['alt'] = $this->mtfUnit->clearEmoji($_key['标题'][0]);
									$_tag = self::FilterTag($_key, $this->conf['tag']['filter'] + array('共享许可'));
									if(empty($_tag) === false) $__a['k'] = $this->mtfAttr->parseK($_tag);
								}
								if($_v['url']){
									$__a['u']=$_v['url'];
								}
								if($_k < 2){// 懒加载
									$__a['l'] = 1;
								}
								if($_v['e']==='gif'){//动画
									$__a['g']=1;
								}
								list($__a['width'], $__a['height']) = $this->_getWH($_attr['宽度'][0], $_attr['高度'][0]);
								if (is_numeric($_v['ch']) && is_numeric($_v['cs']) && is_numeric($_v['cv'])) {
									$_color = $this->mtfColor->hsv2rgb(array('h'=>$_v['ch'],'s'=>$_v['cs'],'v'=>$_v['cv']));
									$__a['cr'] = $_color['r'];
									$__a['cg'] = $_color['g'];
									$__a['cb'] = $_color['b'];
								}
								$_ar['sub'][$_v['i']]['img']=$__a;
								
								$this->_cache['img'][$_f['id']][]=array('i'=>$_v['i'],'e'=>$_v['e']);
							}
						}
					}
					
					$_ar['tpl']=$_c;
					return $_ar;
				case 'data':
				case 'sitemap':
					$_ar = array();
					if (file_exists($_f['p']) === false) $_f['p'] = str_replace($this->dir['file'], $this->dir['tmp'], $_f['p']);
					if (file_exists($_f['p']) === false) return $_ar;
					$_h = $this->mtfBBcode->parse(strip_tags(preg_replace('/s+([ $])/', '', file_get_contents($_f['p'])), '<p>'), array('type'=>'list'));
					$_c = $_h['s'];
					if (empty($_h['bb']) === false) $_ar['bb'] = $_h['bb'];
					if (empty($_h['bb_html']) === false) $_ar['bb_html'] = $_h['bb_html'];
					if (empty($_c)) return $_ar;
					$_exts = implode('|', array_keys($this->conf['ext2type']));
					preg_match_all("/(".$_arv['mtfdat']['pattern']."\.(".$_exts."))/", $_c, $_m);
					$_gif = 0;
					$_video_ids = array();
					foreach($_m[0] as $_k => $_v){
						$_d = $this->pathInfo($_v);
						if ($_d['e'] === 'url'){	
							$_ar['url'][] = array('i' => $_d['id'], 'u' => $this->getContent($this->fileOrTmp($_d['n']), 'view'));
						} elseif ($_d['e'] === 'mtfdat'){
							if($_c === $_d['id'] . '.' . $_d['e']){//直接引用文章（显示内容）
								$_p = $this->dir['file'] . $this->n2dir($_d['id']) . $_d['id'] . '.' . $_d['e'];
								return $this->getContent($_p, 'data');
							}
						} elseif ($_d['t']==='audio'){//语音直接显示
							$_b = $this->conf['convert']['audio'][0];
							$_ar['audio'][] = array(
								'i' => $_d['id'],'source' => array('b' => $_b['b'], 'w' => $_b['w'], 'ext' => $_b['ext']
							));	
						} else {
							$_a = $this->_isDownGifExt($_d['e']);
							if($_t === 'sitemap'){
								if(isset($_a['video']) && $_a['video'] === 1){
									$_video_ids []= $_d['id'];
								}else{
									$_ar['img'] []= array(
										'loc' => 'https://'.$this->conf['domain']['cdn'] . '/' . $_d['id'] . '_c_w_1280.' . $_d['e']
									);
								}
							}else{
								if ($_a['e'] === 'gif') {
									if ($_gif === 0 && empty($_ar['img'])) { // 如果已经有非 gif 图，优先展示。如果没有，只展示一张 gif 图
										$_ar['img'] []= array('i' => $_d['id']) + $_a;
										$_gif = 1;
									}
								} elseif ($_gif === 0) $_ar['img'] []= array('i' => $_d['id']) + $_a;
							}
						}
						$_c = str_replace($_v, '', $_c);
					}
					$_ar['i'] = $_f['id'];
					$_ar['des'] = $this->_get_des($_c);
					if ($_t === 'sitemap' && count($_video_ids) > 0) {
						$_ar['video'] = array();
						$_video_ids_str = implode(',', $_video_ids);
						$_r = $this->mtfMysql->sql('s', $this->db['table'], 'i,e,a,k,t','WHERE i IN(' . $_video_ids_str . ') ORDER BY FIELD(i, ' . $_video_ids_str . ')');
						foreach ($_r as $_v) {
							$_attr = $this->mtfAttr->parseA($_v['a'], '|');
							if (isset($_attr['比特率'])) {
								$_b = end($_attr['比特率']);
								$_w = $this->conf['convert']['video'][count($_attr['比特率']) - 1]['w'];
							} else {
								$_b = $this->conf['convert']['video'][0]['b'];
								$_w = $this->conf['convert']['video'][0]['w'];
							}
							$_key = $this->mtfAttr->parseA($_v['k'], '|');
							$_ar['video'][] = array(
								'content_loc' => 'https://' . $this->conf['domain']['cdn'] . '/' . $_v['i'] . '_c_b_' . $_b . '_w_' . $_w . '.' . $_v['e'],
								'thumbnail_loc' => 'https://' . $this->conf['domain']['cdn'] . '/' . $_d['id'] . '_c_h_150_ext_gif.webp',
								'duration' => empty($_attr['时长']) ? null : end($_attr['时长']),
								'title' => empty($_key['标题']) ? null : $_key['标题'][0],
								'description' => empty($_key['描述']) ? null : $_key['描述'][0],
								'loc_description' => $_ar['des'],
								'publication_date' => (new DateTime($_v['t']))->format('c'),
								'bitrate' => $_b
							);
						}
					}
					return $_ar;
				case 'list':
					if(empty($_arv['load']) === false){
						$_r = $this->mtfMysql->sql('s1', $this->db['table'], 'k,o,nm', 'WHERE i=' . $_arv['load']);
						$_arv['o'] = $_r['o'];
						if(empty($_r['k']) === false){
							$_key = $this->mtfAttr->parseA($_r['k'], '|');
							if (empty($_key['标题']) === false) {
								$_arv['title'] = $_key['标题'][0];
								unset($_key['标题']);
							}
							if (empty($_key['描述']) === false) {
								$_arv['des'] = $_key['描述'][0];
								unset($_key['描述']);
							}
							foreach($_key as $_kv){
								foreach($_kv as $_v){
									$_arv['tag'] = array($_v);
									break 2;
								}
							}
						}
					}
					$_ar = array();
					if(empty($_arv['pi']) === false) {
						$_uid = $this->uid2id($_SERVER['HTTP_UID']);
						$_ar['list']['pi'] = $this->_get_people($_arv['o'], 'info', $_uid, $_arv['nm']);
					}
					if (empty($_arv['title']) === false) $_ar['list']['n'] = $_arv['title'];
					
					$_dn = $this->_dn(empty($_arv['domain']) === false ? $_arv['domain'] : $_arv['o']); // 绑定域名
					if ($_dn) $_ar['list']['dn'] = explode('/', $_dn)[0];

					$_a = $this->getContent($_f['p'], 'data');
					
					//如果有描述，优先提取描述
					if (empty($_arv['des']) === false) $_ar['list']['t'] = $_arv['des'];
					elseif (empty($_a['des']) === false) $_ar['list']['t'] = $_a['des'];
					
					if (empty($_a['bb']) === false) $_ar['list']['n'] = implode('', $_a['bb']) . $_ar['list']['n'];
					if (empty($_a['bb_html']) === false) $_ar['list']['bb'] = implode('', $_a['bb_html']);
					if (empty($_arv['tag']) === false) $_ar['list']['k'] = $_arv['tag'];

					if(empty($_a['img']) === false){
						$_a['img'] = array_slice($_a['img'], 0, $this->conf['list']['max_p_length']);
						$_ar['list']['p']=$_a['img'];
						$img_count = count($_a['img']);
						if ($img_count > 1) {
							$_ar['list']['ps'] = $img_count;
							$_ar['list']['wh'] = 900 / $_ar['list']['ps']; // 300
						} else {
							$_r = $this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i=\''.$_a['img'][0]['i'].'\'',0,'|');
							if ($_a['img'][0]['e'] === 'gif') $_ar['list']['p'][0]['g'] = 1;
							list($_ar['list']['p'][0]['width'], $_ar['list']['p'][0]['height']) = $this->_getWH(isset($_r['a']['宽度']) ? $_r['a']['宽度'][0] : null, isset($_r['a']['高度']) ? $_r['a']['高度'][0] : null);
							list($_ar['list']['p'][0]['width'], $_ar['list']['p'][0]['height']) = $this->_getWHfromE($_ar['list']['p'][0]['width'], $_ar['list']['p'][0]['height'], $_a['img'][0]['e']);
						}
					}
					if (empty($_arv['dm']) === false) $_ar['list']['dm'] = 1;
					elseif (empty($_a['audio']) === false) $_ar['list']['audio'] = $_a['audio'];
					if(empty($_a['url']) === false) $_ar['list']['url'] = $_a['url'];
					
					$_ar['list']['type']='mtfdat';
					return $_ar;
				case 'down':
					$this->down($_f['p']);
					break; 
			}
		}elseif($_f['t']==='image'){
			$_d['n']=$_f['n'];

			// 真实图片转换
			$_f['c']=$this->config($_f['p']);
			$_e=$_f['e'];
			if (in_array(strtolower($_f['c']['ext']),array('jpg','jpeg','png','gif','webp'))) {
				$_e=$_f['c']['ext'];
			}

			$_f['p']=$_f['d'].'/'.$_f['id'].'.'.$_e;
			$_d['p']=$this->dir['cache'].$this->n2dir($_d['n']).$_d['n'];
			
			if(rand(1,100)<=$this->conf['cache']['p']){//清理缓存
				$this->mtfKey->clean($this->dir['cache'].$this->n2dir($_d['n']),$this->conf['cache']['out'],$this->conf['cache']['max']);
				$this->mtfKey->clean($this->dir['tmp'].$this->n2dir($_d['n']),$this->conf['cache']['out'],$this->conf['cache']['max']);
			}
			if(!$this->convert($_f['p'], $_d['p'])){
				return false;	
			}
			$this->down($_d['p'], $_t);
		} elseif ($_f['t'] === 'video' || $_f['t'] === 'audio' || $_f['t'] === 'doc' || $_f['t'] === 'rom') {
			$this->down($_d['p'], $_t);
		} elseif ($_f['t']==='txt' || $_f['t']==='sub') {
			switch($_t){
				case 'view':
					return file_get_contents($_f['p']);
				case 'down':
					$this->down($_f['p']);
			}
		} elseif($_f['t']==='zip') {
			switch($_t){
				case 'view':
					header('Content-type:text/html;charset=utf-8');
					if($_f['e']==='7z'){
						return 403;//不支持预览
					}else{
						include_once __DIR__ . '/../UnifiedArchive/autoload.php';
						$archive=new wapmorgan\UnifiedArchive\UnifiedArchive();
						$archive->open($_f['n']);	
						return implode("\n",$archive->getFileNames());
					}
				case 'down':
					$this->down($_f['p']);
			}
		} elseif($_f['t']==='bt') {
			switch($_t){
				case 'view':
					include_once __DIR__ .'/../Torrent/Torrent.php';
					$_torrent=new Torrent($_f['p']);
					$_ar=$_torrent->info['files'];
					$_files=array();
					foreach($_ar as $_k=>$_v){
						$_p=$_v['path.utf-8']?$_v['path.utf-8']:$_v['path'];
						$_l=count($_p);
						if($_l==1){
							$_files[$_p[0]]='';
						}else{
							$_files[$_p[0]][]=$_p[1];
							if($_l>=3){
								for($_i=$_l-2;$_i>=0;$_i--){
									$_files[$_p[$_i]][]=$_files[$_p[$_i+1]];
								}
							}
						}
					}
					break;
				case 'down':
					$this->down($_f['p']);
			}
		} elseif ($_f['t'] === 'url') {
			switch ($_t) {
				case 'view':
					$_c = file_get_contents($_f['p']);
					$_a = explode('BASEURL=', $_c);
					$_c= empty($_a[1]) === false ? $_a[1] : $_a[0];
					return explode('[', $_c)[0];
				case 'down':
					$this->down($_f['p']);
			}
		}
	}
	
	public function down($_f_p, $_type = 'down'){
		header('Content-Type: ' . mime_content_type($_f_p));
		if ($_type === 'down') header('Content-Disposition: attachment');
		header('cache-control: max-age=31536000');
		//kangle 虚拟主机，配置zoneUp 的别名，路径
		// kangle
		//header('X-Accel-Redirect:');
		// nginx
		header('X-Accel-Redirect: ' . substr(strrchr($this->root, '/'), 1) . explode($this->root, $_f_p)[1]);
		// apache
		//header('X-Sendfile:');
		exit;
	}
	
	public function hash($_f_p) {
		$_f=$this->pathInfo($_f_p);
		include_once __DIR__ . '/../Grafika/autoload.php';
		$_editor=Grafika\Grafika::createEditor();
		if($_f['t']==='image'){
			$_editor->open($_image, $_f['p']);  
		}elseif($_f['t']==='video'){
			$_editor->open($_image, $_f['d'].'/'.$_f['id'].'.jpg');
		}elseif($_f['t']==='audio'){
			$_editor->open($_image, $_f['d'].'/'.$_f['id'].'.'.$this->conf['preview'][$_f['t']]['ext']);
		}else{
			if($_f['t']==='txt'||$_f['t']==='sub'||$_f['t']==='mtfdat'||$_f['t']==='url'){
				$_s=$this->mtfUnit->removeSpaceTabs(file_get_contents($_f['p']));
				$_s=str_replace("\r",'',str_replace("\n",'',$_s));
				$_l=$this->mtfUnit->strLen($_s);
				$_l=ceil(sqrt($_l));//四入五入
				$_image=Grafika\Grafika::createBlankImage(12*$_l,16*$_l);//16兼容纯字母，字母高度>数字高度>汉字高度
				for ($_i=0; $_i<=$_l; $_i++) {
					$_editor->text($_image, mb_substr($_s,$_i*$_l,$_l), 12, 0, $_i*16, new Grafika\Color('#FFFFFF'), __DIR__ .'/../Grafika/fonts/wenquanyidengkuanzhenghei.ttf');
				}
				//$_editor->save($_image, $_f['bn'].'.jpg');
			}else{
				//其他格式不支持相似度hash
			}
		}
		if (empty($_image) === false) {
			$_hash = new Grafika\Gd\ImageHash\DifferenceHash();
			return $_hash->hash($_image, $_editor);
		}
		return '';
	}

	public function tags($_f_p)
	{
		$_f=$this->pathInfo($_f_p);
		
		$_attr=array();
		
		if($_f['t']==='image'){
			$exif = @exif_read_data($_f['p'], 'IFD0');//不支持GIF
			if($exif){
				foreach($exif as $_k=>$_v){
					switch ($_k)
					{
						case 'Make':
							$_attr['相机品牌']=$exif['Make'];
							break;
						case 'Model':
							$_attr['相机型号']=$exif['Model'];
							break;
						case 'ExposureTime':
							$_attr['快门']=$exif['ExposureTime'];
							break;
						case 'FNumber':
							$_attr['光圈']=$exif['FNumber'];
							break;
						case 'FocalLength':
							$_attr['焦距']=$exif['FocalLength'];
							break;
						case 'ISOSpeedRatings':
							$_attr['感光度']=$exif['ISOSpeedRatings'];
							break;
						case 'UndefinedTag:0xA434':
							$_attr['镜头']=$exif['UndefinedTag:0xA434'];
							break;
						default:;
					}	
				}
			}
		}
		return $_attr;
	}
	
	private function _get_ture_orientation_img($_f_p)
	{
		$_i = getimagesize($_f_p);
		if (function_exists('exif_read_data') === false || in_array($_i['mime'], array('image/png', 'image/jpeg', 'image/pjpeg')) === false) return false;
		$_img = imagecreatefromstring(file_get_contents($_f_p));
		$_exif = exif_read_data($_f_p);
		if(empty($_exif['Orientation']) === false) {//只旋转照片，不旋转透明png，png经过处理，会变为半透明（黑色背景）
			switch($_exif['Orientation']) {
				case 8:
					$_img = imagerotate($_img, 90, 0);
					break;
				case 3:
					$_img = imagerotate($_img, 180, 0);
					break;
				case 6:
					$_img = imagerotate($_img, -90, 0);
			}
			switch($_i['mime']){
				case 'image/png':
					imagepng($_img,$_f_p);
					break;          
				case 'image/jpeg':
				case 'image/pjpeg':
					imagejpeg($_img,$_f_p);
			}
		}
	}
	
	public function ext($_extConf = array('db' => array(), 'queue' => array(), 'mail' => array())) {
		if(empty($_extConf['db']) === false){
			$this->db = array_merge($this->db,$_extConf['db']);
			include_once __DIR__.'/../mtfMysql/mtfMysql.php';
			$this->mtfMysql = new mtfMysql($this->db);
			include_once __DIR__ . '/../mtfAttr/mtfAttr.php';
			$this->mtfAttr = new mtfAttr($this->db);
			include_once __DIR__ . '/../mtfRelate/mtfRelate.php';
			$this->mtfRelate = new mtfRelate($this->db);
		}
		if(empty($_extConf['que']) === false){
			include_once __DIR__ . '/../mtfQueue/mtfQueue.php';
			$this->mtfQueue = new mtfQueue($_extConf['que']);
			$this->mtfQueue->RESTful();
		}
		if(empty($_extConf['mail']) === false){
			$this->conf['mail'] = $_extConf['mail'];
		}
		if(empty($_extConf['time']) === false){
			$this->conf['time'] = $_extConf['time'];
		}
	}
	
	public function getName() { // 文件名固定为 年月日时分秒（微妙）（18+4=22）的情况
		return date('YmdHis', time()).sprintf("%04d", explode(' ', microtime())[0] * 10000);
	}
	
	public function uid2id($_server_uid){
		if($_server_uid){
			$_ar=$this->mtfGuid->deUid($_server_uid);
			$_statue=$_ar['statue'];
			$_i=$_ar['i'];
			$_uid=$_ar['uid'];
			if($_statue==='success'){
				return $_i;
			}elseif($_statue==='need-update'){
				$_ip=$this->mtfGuid->ip();
				$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('IP'=>$_ip)),'WHERE i='.$_i,0,'|');
				$this->mtfMysql->sql('u',$this->db['table'],array('t0'=>date('Y-m-d H:i:s')),'WHERE i='.$_i);
				$this->error('uid','login-update',array('uid'=>$_uid));
			}else{
				$this->error('uid','login-out');
			}
		}else{
			return false;
		}
		
	}
	//是否是管理
	public function isAdmin($_uid){
		return in_array($_uid,$this->conf['uid']['admin']);
	}
	//权限 [管理，自己，授权，好友，关注，粉丝，会员，游客]
	public function mtfRight($_uid,$_object){
		if(!@$_uid){//游客
			return '游客';
		}elseif($this->isAdmin($_uid)){
			return '管理';
		}else{
			if($_object){
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,e,o,msg,aw','WHERE i='.$_object);
				if(@$_r['e']){
					$_e=$_r['e'];
					
					if($_e==='people'){
						$_o=$_object;
					}else{
						$_o=$_r['o'];	
					}
					
					if($_uid===$_o){//如果是自己的接收的消息
						return '自己';	
					}elseif(@$_r['aw'] && in_array($_uid,explode(',',$_r['aw']))){
						return '授权';
					}else{
						if(@$this->_cache['list'][$_o]){
							$_r=$this->_cache['list'][$_o];
						}else{
							$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_o,0,'|');
						}
						
						if(@$_r['a']['关注']){
							if(in_array($_uid,$_r['a']['关注'])){
								$_n1='关注';
							}
						}
						if(@$_r['a']['粉丝']){
							if(in_array($_uid,$_r['a']['粉丝'])){
								$_n2='粉丝';
							}
						}
						
						if(@$_n1 && @$_n2){
							return '好友';	
						}elseif(@$_n1){
							return $_n1;	
						}elseif(@$_n2){
							return $_n2;	
						}else{
							return '会员';	
						}
					}
				}else{
					return '会员';	
				}
			}else{
				return '会员';	
			}
		}
	}
	public function hasRight($_uid,$_object,$_need,$_disallow=array(),$_tip=1){
		$_rights=array('游客','会员','粉丝','关注','好友','授权','自己','管理');
		$_l_need=array_search($_need,$_rights);
		if($_l_need){
			$_right=$this->mtfRight($_uid,$_object);
			if(in_array($_right,$_disallow)){
				return false;
			}else{
				$_l_self=array_search($_right,$_rights);
				if($_l_self>=$_l_need){
					return true;	
				}else{
					if($_l_need>=1){
						if($_tip){
							$this->error('uid','login-need',array('preventRetry'=>true));
						}
					}
					return false;	
				}
			}
		}else{
			return false;		
		}
	}
	
	public function error($errortype,$error,$arv=array()){
		exit(json_encode(array('errortype'=>$errortype,'error'=>$error)+$arv));
	}
	
	private function _verify($_data,$_n,$_r=''){
		foreach($_n as $_k=>$_v){
			$d=trim(@$_data[$_v]);
			if((!$d && !$_r[$_k]) || ($d && @$_r[$_k] && preg_match($_r[$_k],$d))){
				$this->error('io','input-error',array('n'=>@$_v,'r'=>@$_r[$_k]));
			}
		}
	}
	
	private function _sql_i_msg($_g,$_s,$_f,$_tt,$_v,$_n,$_ip,$_fid,$_once='',$_vv='',$_ignore=array()){//忽略参数，作为判断是否唯一标准
		if($_once){
			$_md5=md5($_g.$_s.(@in_array('tt',$_ignore)?'':$_tt).(@in_array('v',$_ignore)?'':$_v).$_vv);//大分类，小分类，作用人，作用对象（如文章，图片），附加变量
			if($_once===1){
				$_sql='';
			}else{
				$_date=date('Y-m-d H:i:s', time()-$_once);
				$_sql=' AND t>\''.$_date.'\'';
			}
			$_sql_or=array();
			if($_f){
				$_sql_or[]='f='.$_f;
			}
			if($_ip){
				$_sql_or[]='ip=\''.$_ip.'\'';
			}
			if($_fid){
				$_sql_or[]='fid=\''.$_fid.'\'';
			}
			if($_sql_or){
				$_sql_or=' AND ('.implode(' OR ',$_sql_or).')';
			}else{
				$_sql_or='';
			}
			$_r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'i','WHERE h=\''.$_md5.'\''.$_sql.$_sql_or);
			if(@$_r['i']){
				return false;
			}
		}
		$this->mtfMysql->sql('i',$this->db['table_msg'],array('g'=>$_g,'s'=>$_s,'f'=>$_f,'tt'=>$_tt,'v'=>$_v,'n'=>$_n,'ip'=>$_ip,'fid'=>$_fid,'h'=>$_md5,'vv'=>$_vv));
		return true;
	}
	
	private function _uname($_t){
		return substr(md5($_t),0,10);
	}
	
	//积分转账
	public function fen($_t,$_s,$_f,$_tt,$_v,$_n,$_ip,$_fid,$_once='',$_vv='',$_ignore=array()){//忽略参数，作为判断是否唯一标准
		if(!$_n){
			return false;
		}
		switch($_t){
			case 'zan':
				
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'nz0','WHERE i='.$_tt);
				$_nz0=floor($_r['nz0']);
				if($_nz0+$_n>=0){
					if($this->_sql_i_msg(0,$_s,$_f,$_tt,$_v,$_n,$_ip,$_fid,$_once,$_vv,$_ignore)){
						if($_n>0){
							$this->mtfMysql->sql('u',$this->db['table'],array('nz0'=>'///nz0+'.$_n,'nz'=>'///nz+'.$_n,'nz1'=>'///nz1+'.$_n),'WHERE i='.$_tt);
						}else{
							$this->mtfMysql->sql('u',$this->db['table'],array('nz0'=>'///nz0-'.abs($_n)),'WHERE i='.$_tt);
						}
						if(!in_array($_s,array('点 ♥','点击 红包','关注 红包','任务 红包','原图 ♥','扣除 ♥','被捉 ♥','逃跑 ♥'))){//点♥不扣来源
							if($_f){//反过来，减少/增加 来源
								$_n=-$_n;
								if($_n>0){
									$this->mtfMysql->sql('u',$this->db['table'],array('nz0'=>'///nz0+'.$_n,'nz'=>'///nz+'.$_n,'nz1'=>'///nz1+'.$_n),'WHERE i='.$_f);
								}else{
									$this->mtfMysql->sql('u',$this->db['table'],array('nz0'=>'///nz0-'.abs($_n)),'WHERE i='.$_f);
								}
								$this->_sql_i_msg(0,$_s,$_tt,$_f,$_v,$_n,$_ip,$_fid,'',$_vv);//反作用时，不再重复验证 $_once
							}
						}
						return true;
					}else{
						return false;
					}
				}else{
					$this->error('uid','lack-nz0');
				}
				
				break;	
			default:
				break;
		}
		
		return true;
	}
	
	private function _get_raw($_i,$_raw){
		$_attr=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_i,0,'|');
		$_p=(@$_attr['a']['原图价']?$_attr['a']['原图价'][0]:$_raw['p']);
		$_p1=ceil($_p*$_raw['r']);
		return array('p'=>$_p,'p1'=>$_p1);
	}
	
	public function mtfQueueApi($_object,$_action,$_data)
	{	
		if($_object==='file')
		{
			if($_action==='add')
			{
				return $this->mtfQueueAdd($_data);
			}
			else if($_action==='list')
			{
				return $this->mtfQueueList($_data);
			}
			else if($_action==='data')
			{
				return $this->mtfQueueList($_data);
			}
			else if($_action==='hm')
			{
				$_hm=@$_data[0]['hm'];
				$_type=@$_data[0]['type'];
				if($_type==='hash'){
					$_type='h';
				}
				if($_hm){
					if($_type==='hm'){
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,e','WHERE '.$_type.'=\''.$_hm.'\' LIMIT 0,1');
					}elseif($_type==='h'){
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,k,e,BIT_COUNT(CAST( CONV( h, 2, 10 ) AS UNSIGNED ) ^ CAST( 0b'.$_hm.' AS UNSIGNED )) as hd','WHERE h!=\'\' ORDER BY hd ASC LIMIT 0,1');
						if($_r['hd']>2||!$_r['k']){//先判断分类，只对人像/猫等，采用汉明距离找到相似图片，直接显示，其它分类，仍推荐采用md5判断，例如红包/白色底图片容易分不清
							$_r['i']='';
						}
					}
					
					if(@$_r['i']){
						return array($_hm=>$_r['i'].'.'.$_r['e']);
					}else{
						return array($_hm=>false);
					}
				}else{
					return array($_hm=>false);
				}
			}
		}
		elseif($_object==='people')
		{
			if($_action==='add')
			{
				$this->_verify($_data,array('psd','name','phone','qq','mail','safe','phone','safe'),array('','','','^[0-9]*$','^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$','','^[0-9]{6}$','^[0-9]{6}$'));
				
				$_ip=$this->mtfGuid->ip();
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'count(*) as t','WHERE FIND_IN_SET("IP:'.$_ip.'", a) AND t>"'.date("Y-m-d").'"');
				if(@$_r['t']>$this->conf['uid']['limit']){//限制每天单IP注册人数不超过指定人数
					$this->error('uid','login-limit-num');
				}
			
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'max(i) AS i','WHERE e="people"');
				if(@$_r['i'])
				{
					$_i=$_r['i'];
					
				}else{
					$_i=$this->conf['uid']['init'];	
				}
				while($_i){
					$_i+=1;
					if($this->mtfUnit->isNumGood($_i))
					{
						continue;	
					}else{
						break;	
					}
				}
				$this->mtfMysql->sql('i',$this->db['table'],array('i'=>$_i,'e'=>'people','a'=>'IP:'.$_ip.',密码:'.md5(md5($_data['psd'])).',名称:'.$_data['name'].',手机:'.$_data['phone'].',安全码:'.$_data['safe'].(@$_data['qq']?',QQ:'.$_data['qq']:'').(@$_data['mail']?',邮箱:'.$_data['mail']:'')));//注册后，增加一条未知消息
				return array('i'=>$_i);
			}
			elseif($_action==='login')
			{
				$_i=@$_data['i'];
				$_password=@$_data['psd'];
				
				if($_i && $_password){
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,a,fol','WHERE i='.$_i);
					if(@$_r['i']){
						$__r=$this->mtfAttr->parseA($_r['a'],'|');
						if(@$__r['密码'][0]){
							if(md5($_password) === $__r['密码'][0]){
								$_i=$_r['i'];
								$_uid=$this->mtfGuid->enUid($_i);
								if(@$_data['i_e']){
									$_a=explode(',',$_data['i_e']);
									foreach($_a as $_k=>$_v){
										$_a[$_k]=$this->mtfCrypt->de($_v);	
									}
									$this->mtfMysql->sql('u',$this->db['table'],array('o'=>$_i),'WHERE i IN ('.implode(',',$_a).')');//认领匿名用户所发的帖子
								}
								return array('uid'=>$_uid,'i'=>$_i,'f'=>$_r['fol']);	
							}	
						}	
					}
					$this->error('io','input-error');
				}
			}
			elseif($_action==='uid2i')
			{
				$_i=$this->uid2id(@$_data[0]['uid']);
				if($_i){
					return array('i'=>$_i);
				}else{
					$this->error('uid','login-empty');
				}
			}
		}
		elseif($_object==='bt')
		{
			$_i=@$_data['id'];
			$_uid=$this->uid2id(@$_SERVER['HTTP_UID']);
			
			if($_i && !is_numeric($_i)){//如果非数字，标签
				if($_action!=='zan' && $_action!=='zan2' && $_action!=='p2p')
				{
					if($this->isAdmin($_uid)){
						return false;
					}
				}
			}
			
			if($_action==='zan')
			{
				$_ip=$this->mtfGuid->ip();
				$_fid=$_data['fid'];
				
				if($_i){
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o,nz','WHERE i='.$_i);
					if($_r['e']==='people'){
						$_o=$_i;
					}elseif($_r['e']){
						$_o=$_r['o'];
					}
					$_zan=1;
					
					//对于人/作者：每一天，同一IP / FID / UID，对任意一作者，只增加一个♥
					if($_r['e']==='people'){
						if($this->fen('zan','点 ♥',$_uid,$_o,'',$_zan,$_ip,$_fid,$this->conf['time']['zan']['people']*86400,'',array('tt','v'))){//1代表任意作者，0代表同一作者
							
						}else{
							$this->error('bt','zan-already',array('i'=>$_i));
						}
					}else{//对于文章：mei每一天，同一IP / FID / UID，对同一投稿，只增加一个♥
						if($this->_sql_i_msg(3,'点 ♥',$_uid,$_o,$_i,$_zan,$_ip,$_fid,$this->conf['time']['zan']['people']*86400)){//点♥（对象为非人）：不计入会员♥的列表
							$this->mtfMysql->sql('u',$this->db['table'],array('nz'=>'///nz+'.$_zan),'WHERE i='.$_i);
							//给作者增加♥：每一天，同一IP / FID / UID，对任意一作者，只增加一个♥
							$this->fen('zan','点 ♥',$_uid,$_o,$_i,$_zan,$_ip,$_fid,$this->conf['time']['zan']['item']*86400,'',array('tt','v'));//1代表任意作者，0代表同一作者
							//给父文章增加赞
							if(@$_data['i']){
								if(is_numeric($_data['i']) && $_i!==$_data['i'] && $_o!==$_data['i']){//作者不重复增加
									$this->mtfMysql->sql('u',$this->db['table'],array('nz'=>'///nz+'.$_zan),'WHERE i='.$_data['i']);
								}
							}
						}else{
							$this->error('bt','zan-already',array('i'=>$_i));
						}
					}
					
					return array('zan'=>$_r['nz']+1);
				}
			}
			elseif($_action==='raw')//原图
			{
				$_raw=array(
					'r'=>0.6,//兑换比率
					'p'=>5//默认价格
				);
				$_step=$_data['step'];
				$_sql='WHERE t>\''.date('Y-m-d H:i:s',strtotime("-".$this->conf['time']['raw']." day")).'\' AND g=0 AND f=\'\' AND tt='.$_uid.' AND v=\''.$_i.'\' AND s=\'原图 ♥\'';
				if($_step==='1'){
					//30天内
					$__r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'i',$_sql);
					if($__r['i']){
						return array('status'=>false);
					}else{
						return array('status'=>true)+$this->_get_raw($_i,$_raw);
					}
				}else if($_step==='3'){//获取赞
					return array('status'=>true)+$this->_get_raw($_i,$_raw);
				}else if($_step==='4'){//作者设置赞
					if($this->hasRight($_uid,$_i,'自己','',1)){
						if(is_numeric(@$_data['p'])){
							$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('原图价'=>array($_data['p']))),'WHERE i='.$_i);
						}
						return array('status'=>true);
					}else{
						return array('status'=>false);
					}
				}else{
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o,a','WHERE i='.$_i);
					if(@$_r['e']){
							if($_uid===$_r['o']){//不扣除♥
							}elseif($this->hasRight($_uid,$_i,'会员','',1)){
								$_p=$this->_get_raw($_i,$_raw);
								
								$__r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'i',$_sql);
								if($__r['i']){
									
								}else{
									$_ip=$this->mtfGuid->ip();
									$_fid=$_data['fid'];
									$this->fen('zan','原图 ♥','',$_uid,$_i,'-'.$_p['p'],$_ip,$_fid);
									if(@$_r['o']){
										$_o=$_r['o'];
										$this->fen('zan','原图 ♥',$_uid,$_o,$_i,$_p['p1'],$_ip,$_fid);
									}
								}
							}
							return array('u'=>$this->conf['domain']['cdn'].'/'.$_i.'.'.$_r['e'],/*'waifu'=>$this->conf['domain']['cdn'].'/'.$_i.'_c_waifu_'.$this->_waifu2url($_i.'_2_2').'.'.$_r['e'],*/'status'=>true);
					}else{
						return array('status'=>false);
					}
				}
			}
			elseif($_action==='pok')//捕捉
			{
				$_pok=array(
					'z'=>1,//加价幅度
					'u'=>0.8,//逃跑
					't'=>7//逃跑锁定天数
				);
				$_step=$_data['step'];
				if($this->hasRight($_uid,$_i,'会员')){
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'a,nz,o','WHERE i='.$_i);
					$_attr=$this->mtfAttr->parseA($_r['a'],'|');
					$_p=@$_attr['主人'][0];
					$_zan=floor(@$_r['nz'])+$_pok['z'];
					$_uzan=floor($_zan*$_pok['u']);
					
					if(@$_r['o']){
						$__r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'t','WHERE f='.$_r['o'].' AND s=\'逃跑 ♥\' ORDER BY i DESC LIMIT 1');
						if($__r['t']){
							if((time()-strtotime($__r['t']))/86400<$_pok['t']){
								return array('status'=>false,'no'=>date('Y-m-d H:i:s',strtotime($__r['t'].'+'.$_pok['t'].' day')));
							}
						}
					}
					
					
					$__p=array();
					
					if($_p){
						$__p=$this->_get_people($_p,'info',$_uid);
						//上一主人的点♥数量
						$__r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'n','WHERE v='.$_i.' AND tt='.$_p.' AND s=\'捕捉 ♥\' ORDER BY i DESC LIMIT 1');
						$_pzan=floor(abs($__r['n'])*$_pok['u']);
					}else{
						$_pzan=0;
					}
					if($_p){
						$_s=array_search($_uid,array('主人'=>$_p,'作者'=>@$_r['o']));
					}else{
						$_s='';
					}
					if($_step==='1'){
						return array('p'=>$__p,'zan'=>$_zan,'u'=>$_pok['u'],'uzan'=>$_uzan,'s'=>$_s,'pzan'=>$_pzan,'t'=>$_pok['t']);
					}else{
						$_ip=$this->mtfGuid->ip();
						$_fid=$_data['fid'];
						
						if($_step==='2'){//捕捉
							if($_uid===$_p){
								return array('status'=>false);
							}else{
								if($this->fen('zan','捕捉 ♥',$_p,$_uid,$_i,-$_zan,$_ip,$_fid)){
									$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('主人'=>array($_uid))),'WHERE i='.$_i);
									$this->mtfMysql->sql('u',$this->db['table'],array('nz'=>'///nz+'.$_pok['z']),'WHERE i='.$_i);//给文章增加❤
									if($_r['o']){//每更换主人一次，给作者增加❤
										$this->fen('zan','被捉 ♥',$_uid,$_r['o'],$_i,$_pok['z'],$_ip,$_fid,1);
									}
								}
								$__p=$this->_get_people($_uid,'info',$_uid);
							}
						}elseif($_step==='3'){//释放·逃跑
							if($_s==='主人'){
								$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('主人'=>array($_p))),'WHERE i='.$_i,0,'|');
								return array('status'=>true,'p'=>'');
							}elseif($_s==='作者'){
								if($this->fen('zan','逃跑 ♥',$_uid,$_p,$_i,$_pzan,$_ip,$_fid)){
									$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('主人'=>array($_p))),'WHERE i='.$_i,0,'|');
									return array('status'=>true,'p'=>'');
								}else{
									return array('status'=>false);
								}
							}else{
								return array('status'=>false);
							}
						}
						
						return array('status'=>true,'p'=>$__p,'zan'=>$_zan);
					}
				}else{
					return array('status'=>false);
				}
			}
			elseif($_action==='p2p')//分享
			{
				if(@$_data[0]['psd']==='madfan'){//绕过Key验证
					$_tt=@$_POST['uid'];
					if($_tt){
						$_s='分享 ♥';
						$_uid='';
						$_i=$_POST['i'];
						$_zan=1;
						$_ip=$_POST['ip'];
						$_fid='';
						return $this->fen('zan',$_s,$_uid,$_tt,$_i,$_zan,$_ip,$_fid);
					}
				}else{
					$_a='';
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'a,k,e,o','WHERE i='.$_i);
					if($_r['e']==='people'){
						$_o=$_i;
					}else{
						$_o=$_r['o'];
						$_key=$this->mtfAttr->parseA($_r['k'],'|');
						if($_key['标题'][0]){
							$_a=$_key['标题'][0];
						}
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'a,k','WHERE i='.$_o);
					}
					$_attr=$this->mtfAttr->parseA($_r['a'],'|');$_key=$this->mtfAttr->parseA($_r['k'],'|');
					
					if($_key['标题'][0]){
						$_q['t']=$_key['标题'][0];
					}else{
						$_q['t']=$_o;
					}
					$_q['t'].=($_a?':'.$_a:'');
					
					$_a=$this->_av2url(@$_attr['头像'][0]);
					
					$_q['a']='//'.$this->conf['domain']['cdn'].'/'.(@$_a['avi']?$_a['avi'].'_c_'.(@$_a['ace']?'ace_'.$_a['ace'].'_':'').'c_f_w_50_h_50.'.$_a['ave']:'201207010000000002_c_w_50_h_50_p_pokemongif,'.$this->_uname($_q['t']?$_q['t']:$_o).'.gif');
					
					if($_uid){//会员分享，可以给自己获得♥
						$_q['uid']=$_uid;
					}
					$_q['i']=$_i;
					$_q['r']=1;//自动跳转
					$_q['d']=@$_data['d'];
					
					$_dn_id=array_flip($this->conf['dn'])[$_SERVER['SERVER_NAME']];
					if($_dn_id){
						$_q['bu_true']=1;
					}
					
					$_q['bu']=@$_data['bu'];
					
					$_q['bc']=@$_data['bc'];
					$_q['bt']=@$_data['bt'];
					$_q['max']=5;
					$_q['return']='https://'.$this->conf['domain']['web'].'/api/bt/p2p/?psd=madfan';//绕过key验证
					
					$_h=$this->mtfProxyCurl->p2p($this->conf['domain']['p2p'],$_q);
					$_j=json_decode($_h,true);
					
					return array('u'=>@$_j['u'],'n'=>@$_j['n']);
				}
				
			}
			elseif($_action==='zan2')
			{
				
				$_status=false;
				$_zan=$_data['num'];
				$_reason=$_data['reason'];
				if($_zan>0){
					if($this->hasRight($_uid,$_i,'会员',array('自己'))){
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o','WHERE i='.$_i);
						if($_r['e']==='people'){
							$_o=$_i;
						}else{
							$_o=$_r['o'];
						}
						$this->fen('zan','送 ♥',$_o,$_uid,$_i,-$_zan,$_ip,$_fid,'',$_reason);
						if($_r['e']==='people'){
							
						}else{
							$this->mtfMysql->sql('u',$this->db['table'],array('nz'=>'///nz+'.$_zan),'WHERE i='.$_i);
						}
						$_status=true;
					}
				}else{
					if($this->isAdmin($_uid)){
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o','WHERE i='.$_i);
						if($_r['e']==='people'){
							$_o=$_i;
						}else{
							$_o=$_r['o'];
						}
						$this->fen('zan','扣除 ♥','',$_o,$_id,$_zan,$_ip,$_fid,'',$_reason);
						$_status=true;
					}
				}
				return array('status'=>$_status);
			}
			elseif($_action==='rel')
			{
				$_status=false;
				if($this->hasRight($_uid,$_i,'会员',array('自己'))){
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'p,r,nrel','WHERE i='.$_uid);
					if(@$_r['r']){
						$_a=explode(',',$_r['r']);
					}else{
						$_a=array();
					}
					
					$_r2=$this->mtfMysql->sql('s1',$this->db['table'],'nrel','WHERE i='.$_i);
					
					if(in_array($_i,$_a)){
						$this->mtfRelate->sql('d1',$this->db['table'],array('r'=>$_i),'WHERE i='.$_uid);
						
						$this->mtfMysql->sql('u',$this->db['table'],array('nrel'=>'///nrel-1'),'WHERE i='.$_i);
						$_status=false;
						$_r2['nrel']--;
					}else{
						$this->mtfRelate->sql('i1',$this->db['table'],array('r'=>$_i),'WHERE i='.$_uid);
						
						$this->mtfMysql->sql('u',$this->db['table'],array('nrel'=>'///nrel+1'),'WHERE i='.$_i);
						$_status=true;
						$_r2['nrel']++;
					}
				}
				return array('status'=>$_status,'rel'=>$_r2['nrel']);
			}
			elseif($_action==='tag')
			{
				$_step=$_data['step'];
				
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,a,k,o','WHERE i='.$_i);
				if($_step==='1'){
					if(!@$_r['e']){
						if(strlen($_i)>18 && $_uid){//标签
							$this->mtfMysql->sql('i',$this->db['table'],array('i'=>$_i,'o'=>$_uid,'e'=>'mtftag'));
						}
					}
				}
				if($this->hasRight($_uid,$_i,'自己')){
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,a,k,t1','WHERE i='.$_i);
					$_key=$this->mtfAttr->parseA($_r['k']);
					$_attr=$this->mtfAttr->parseA($_r['a']);
					$_a=$this->_av2url($_attr['头像'][0]);
					
					if($_step==='1'){
						return array('status'=>true,'tag'=>$_key,'t1'=>$_r['t1'])+$_a;
					}else{
						$__a=array();//头像
						
						if($_r['e']==='people'){//如果是人，检查昵称的唯一性
							if(@$_data['title']){
								if(is_numeric($_data['title'])){
									$this->error('uid','title-num');
								}elseif(preg_match("/\s/", $_data['title'])){
									$this->error('uid','title-space');
								}else{
									$__r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE i!='.$_i.' AND e=\'people\' AND FIND_IN_SET(\''.'标题:'.$_data['title'].'\', k)');
									if(@$__r['i']){
										$this->error('uid','title-unique');
									}
								}
							}
						}
						$_ar=array();
						$_ar['标题'] = @$_data['title'];
						$_ar['描述'] = @$_data['des'];
						$_tag=array();
						$_del=array();
						
						if(@$_data['tag']){
							$_tag=json_decode(str_replace(' ', '', $_data['tag']),true);
						}
						if($_r['e']==='people'){
							if($_key){
								foreach($_key as $__k=>$__v){
									if($_tag[$__k]){
										foreach($__v as $__v2){
											if(in_array($__v2,$_tag[$__k])){
												
											}else{
												$_del[]=$this->_ui(($__k==='标签'?$__v2:$__k.':'.$__v2),$_i);
											}	
										}
									}else{
										foreach($__v as $__v2){
											$_del[]=$this->_ui(($__k==='标签'?$__v2:$__k.':'.$__v2),$_i);
										}
									}
								}
								if($_del){
									$this->mtfMysql->sql('d',$this->db['table'],'','WHERE i IN ('.implode(',',$_del).')');
								}
							}
						}
						if ($_tag) $_ar += $_tag;
						
						$this->mtfAttr->sql('u0', $this->db['table'], array('k'=>$_ar), 'WHERE i=' . $_i);	
						
						$_new=$_data['av'];
						//头像
						if(@$_new && $_new!=='201207010000000001.jpg'){
							if(@$_a['avi']){
								$_old=$_a['avi'].'.'.$_a['ave'];
							}
							
							if($_new!==@$_old){
								$this->mtfQueueAdd(array('p'=>$_new,'quota'=>$_uid));
								if($_old){
									$this->mtfQueueDel(array('i'=>$_a['avi'],'id'=>$_uid));
								}	
							}
							
							$_data['cx']=abs($_data['cx']);
							$_data['cy']=abs($_data['cy']);
							$_data['cw']=abs($_data['cw']);
							$_data['ch']=abs($_data['ch']);
							if($_data['cx']===0 && $_data['cy']===0 && $_data['csw']===$_data['cw']){
						
							}elseif($_data['cw']&&$_data['ch']){
								$_new.='_'.$_data['csw'].'_'.$_data['cw'].'_'.$_data['ch'].'_'.$_data['cx'].'_'.$_data['cy'];
							}
							
							if($_new!==@$_attr['头像'][0]){
								$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('头像'=>array($_new))),'WHERE i='.$_i);
								$__a=$this->_av2url($_new);
							}
						}
							
						$this->mtfMysql->sql('u',$this->db['table'],array('t1'=>(@$_data['t1']?$_data['t1']:'NULL')),'WHERE i='.$_i);
						
						$_tag = array();
						if (empty($_data['title']) === false) $_tag['标题'] = array($_data['title']);
						if (empty($_data['des']) === false) $_tag['描述'] = array($_data['des']);
						if (empty($_data['list']) === false) {
							$_tag['标签'] = $this->_getTag($_ar);
							$_ar = $_tag;
						} else {
							$_ar = array_merge($_ar, $_tag);
						}
						if (empty($_data['t1']) === false) $__a['t1'] = $_data['t1'];
						return array('status' => true, 'tag' => $_ar) + $__a;
					}
				}
			}
			elseif($_action==='fl')
			{
				$_i=$_POST['id'];
				$_a=explode('/',$_POST['i']);
				$_id=$_a[0];
				$_ip=$this->mtfGuid->ip();
				$_fid=$_data['fid'];
				if($this->hasRight($_uid,$_i,'自己')){
					$_t=$_POST['type'];
					
					if($_id===$_i||!is_numeric($_id)){
						if($_t==='top' && $_id && !is_numeric($_id)){//标签
							$_dn_id=array_flip($this->conf['dn'])[$_SERVER['SERVER_NAME']];
							$_id=$this->_ui($_id,$_dn_id);
						}else{
							$_r=$this->mtfMysql->sql('s1',$this->db['table'],'p','WHERE i='.$_i);
							$_id=@$_r['p']?$_r['p']:$_uid;
						}
					}
					
					$_status=false;
					
					if($_t==='top'){
						if($this->hasRight($_uid,$_id,'自己')){
							$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,top','WHERE i='.$_id);
							if(@$_r['top']){
								$_a=explode(',',$_r['top']);
							}else{
								$_a=array();
							}
							if(in_array($_i,$_a)){
								$this->mtfRelate->sql('d1',$this->db['table'],array('top'=>$_i),'WHERE i='.$_id);
							}elseif(@$_r['i']){
								$this->mtfRelate->sql('i1',$this->db['table'],array('top'=>$_i),'WHERE i='.$_id);
								$_status=true;
							}
						}
						return array('status'=>$_status);
					}elseif($_t==='up'||$_t==='down'){
						if($this->hasRight($_uid,$_id,'自己')){
							$_a=array();
							$_r=$this->mtfMysql->sql('s1',$this->db['table'],'r','WHERE i='.$_id);
							if(@$_r['r']){
								if(strpos($_r['r'],$_i) !== false){
									$_r['r']=str_replace($_i,'',str_replace($_i.',','',str_replace(','.$_i,'',$_r['r'])));
									$_a=explode(',',$_r['r']);
									if($_t==='up'){
										$_a[]=$_i;
									}else{
										array_unshift($_a,$_i);
									}
									$this->mtfMysql->sql('u',$this->db['table'],array('r'=>implode(',',$_a)),'WHERE i='.$_id);
									$_status=true;
								}
							}
						}
						return array('status'=>$_status);
					}elseif($_t==='c'||$_t==='f'||$_t==='t'){
						$_v=$_POST['wealval'];
						$_n=$_POST['wealnum'];
						$_zan=$_v*$_n;
						
						if($_zan){
							$this->fen('zan','购买 红包',0,$_uid,$_id,-$_zan,$_ip,$_fid);
						
							if($_t==='f'){//关注送红包，对应人
								$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o','WHERE i='.$_i);
								if($_r['e']==='people'){
									$_o=$_i;
								}else{
									$_o=$_r['o'];
								}
							}else{
								$_o=$_i;
							}
							$_w=array();
							$_r=$this->mtfMysql->sql('s1',$this->db['table'],'w','WHERE i='.$_o);
							if(@$_r['w']){
								$_w=json_decode($_r['w'],true);
							}
							if(!@$_w[$_t]){
								$_w[$_t]=array();
							}
							
							$_w[$_t][]=array('v'=>$_v,'n'=>$_n);
							
							if($_t==='t'){
								$_worder=9;
							}else{
								$_worder='';
							}
							
							$this->mtfMysql->sql('u',$this->db['table'],array('w'=>json_encode($_w),'worder'=>$_worder),'WHERE i='.$_o);
						}
						
						return array('status'=>true);
					}
				}
			}
			elseif($_action==='pl')
			{
				$_i=$_POST['id'];
				
				if($this->hasRight($_uid,$_i,'自己')){
					$_t=$_POST['type'];
					$_ar=array();
					
					if($_t==='1'){//关注可见
						$_s='关注可见';
						$_xf=1;		
					}elseif($_t==='2'){//仅作者可见
						$_s='作者可见';
					}elseif($_t==='3'){//禁止回复
						$_s='禁止回复';
					}else{
						$_s='';
					}
					
					if($_s){
						$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i=\''.$_i.'\'',0,'|');
						if(@$_r['a'][$_s]){
							if($_t==='1'){
								$_xf=0;
							}
							$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array($_s=>'1')),'WHERE i='.$_i,0,'|');	
							$_status=false;
						}else{
							$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array($_s=>'1')),'WHERE i='.$_i,0,'|');
							$_status=true;
						}
						if($_t==='1'){
							$_ar=array('xf'=>@$_xf);
						}
					}
					return array('status'=>$_status)+@$_ar;
				}
			}
			elseif($_action==='av')
			{
				/*
				if($this->hasRight($_uid,$_i,'会员')){
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e','WHERE i='.$_i);
					if($_r['e']){
						$_e='';
						$_t=$this->conf['ext2type'][$_r['e']];
						if($_t==='image'){
							$_e=$_r['e'];	
						}else if($_t==='video'){
							$_e=$this->conf['preview'][$_t]['ext'];
						}
						if($_e){
							$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('头 像'=>$_i.'.'.$_e)),'WHERE i='.$_uid);
						}
					}
					return array('status'=>true,'id'=>$_uid,'av'=>$_i.'.'.$_e);
				}
				*/
			}
			elseif($_action==='fol')
			{
				if($this->hasRight($_uid,$_i,'会员')){
					
					$_ip=$this->mtfGuid->ip();
					$_fid=$_data['fid'];
					
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o,w','WHERE i='.$_i);
					if($_r['e']==='people'){
						$_o=$_i;	
					}else{
						$_o=$_r['o'];
					}
					
					if($_o===$_uid){
						$this->error('bt','fol-self');
					}else{
						$__r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'i','WHERE g=1 AND f='.$_uid.' AND tt='.$_o);
						if(@$__r['i']){
							$_status=false;
							$_r=$this->mtfMysql->sql('d',$this->db['table_msg'],'','WHERE i='.$__r['i']);
							
							$this->mtfMysql->sql('u',$this->db['table'],array('nto'=>'///nto-1'),'WHERE i='.$_uid);
							$this->mtfMysql->sql('u',$this->db['table'],array('nfol'=>'///nfol-1'),'WHERE i='.$_o);
							
							$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('关注'=>$_o)),'WHERE i='.$_uid,0,'|');
							$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('粉丝'=>$_uid)),'WHERE i='.$_o,0,'|');
							
							//收回红包
							$__r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'i,n,v','WHERE g=0 AND s=\'关注 红包\' AND f='.$_o.' AND tt='.$_uid.' ORDER BY i DESC LIMIT 1');
							if(@$__r['n']){
								$___r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'i,n,v','WHERE g=0 AND s=\'红包 退回\' AND f='.$_uid.' AND tt='.$_o.' AND i>'.$__r['i'].' LIMIT 1');
								if(!@$___r[i]){
									$_zan=$__r['n'];
									$this->fen('zan','红包 退回',$_uid,$_o,$__r['v'],$_zan,$_ip,$_fid);
									$_weal=array('t'=>'f','v'=>'-'.$_zan);
								}
							}
						}else{
							$_status=true;
							$this->_sql_i_msg(1,'关注',$_uid,$_o,$_i,'',$_ip,$_fid);
							$this->mtfMysql->sql('u',$this->db['table'],array('nto'=>'///nto+1'),'WHERE i='.$_uid);
							$this->mtfMysql->sql('u',$this->db['table'],array('nfol'=>'///nfol+1','nfol1'=>'///nfol1+1'),'WHERE i='.$_o);
							
							$this->mtfAttr->sql('i1',$this->db['table'],array('a'=>array('关注'=>$_o)),'WHERE i='.$_uid,0,'|');
							$this->mtfAttr->sql('i1',$this->db['table'],array('a'=>array('粉丝'=>$_uid)),'WHERE i='.$_o,0,'|');
							
							$_weal=$this->_weal('关注 红包',$_r['w'],$_o,$_i,$_uid,$_ip,$_fid);
						}
					}
				}
				$_r=array('status'=>$_status);
				if(@$_weal){
					$_r['weal']=$_weal;
				}
				return $_r;
			}
			elseif($_action==='cap')
			{
				if($this->hasRight($_uid,$_i,'会员')){
					$_cap=$_data['cap'];
					$_status=true;
					$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_i,0,'|');
					$_a=array();
					
					$_cap_same=array();
					
					if($_cap){
						$_cap=json_decode($_cap,true);
						foreach($_cap as $_k=>$_v)
						{
							$_a[]=$_v[1];
							$__a=explode('.',$_v[1]);
							$_cap[$_k][1]=$__a[0];
							$_cap_same[$__a[0]]=$_cap[$_k][0];
						}
					}
					if(@$_r['a']['字幕']){
						$_b=$_r['a']['字幕'];
					}else{
						$_b=array();
					}
					$_r=array_diff($_b, $_a);
					if($_r){
						$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('字幕'=>$_r)),'WHERE i='.$_i,0,'|');
						//删除
						if($_r){
							$this->mtfQueueDel(array('i'=>$_r,'id'=>$_i));	
						}
					}
					$_r=array_diff($_a, $_b);
					if($_r){
						$_r1=$this->mtfQueueAdd(array('p'=>$_r,'quota'=>$_i));
						$this->mtfAttr->sql('i1',$this->db['table'],array('a'=>array('字幕'=>$_r1['id'])),'WHERE i='.$_i,0,'|');
						if($_r1['strtr']){//与已有字幕重复
							foreach($_cap as $_k=>$_v)
							{
								if(@$_r1['strtr'][$_v[1]]){
									unset($_cap[$_k]);	
								}
							}
						}
					}
					$_r=array_intersect($_b, $_a);//相同字幕，修改语种标识
					if($_r){
						foreach($_r as $_k=>$_i)
						{
							$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('字幕语种'=>$_cap_same[$_i])),'WHERE i='.$_i);	
						}
					}
					return array('status'=>$_status,'cap'=>$_cap);
				}
			}
			elseif($_action==='edi')
			{
				if($this->hasRight($_uid,$_i,'自己')){
					$_p=$this->dir['file'].$this->n2dir($_i).$_i.'.'.'mtfdat';
					return $_a=$this->getContent($_p,'mustache',array('preview'=>1));
				}
			}
			elseif($_action==='del')
			{
				if($this->hasRight($_uid,$_i,'授权')){
					$this->mtfQueueDel(array('i'=>$_i,'id'=>@$_data['i'],'a'=>@$_data['a']));
					return array('status'=>true);
				}
			}
			elseif($_action==='set')
			{
				if(!$this->hasRight($_uid,$_i,'自己')){
					return array('status'=>false);
				}
				
				$_a=$_POST['a'];
				
				if($_a==='psd'){
					
					$this->_verify($_data,array('name','phone','safe','phone','safe'),array('','','','^[0-9]*$','^[0-9]{6}$'));
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,a','WHERE i='.$_i);
					if(@$_r['i']){
						$__r=$this->mtfAttr->parseA($_r['a'],'|');
						if($_data['name'] === $__r['名称'][0] && $_data['phone'] === $__r['手机'][0] && $_data['safe'] === $__r['安全码'][0]){
							if($_data['psd']){
								$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('密码'=>md5(md5($_data['psd'])))),'WHERE i='.$_i);	
							}
						}else{
							$this->error('io','input-error');
						}
					}
					
				}elseif($_a==='info'){
					if($_data['action']==='get'){
						$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_uid);
						if(@$_r['a']){
							return array(
								'qq'=>@$_r['a']['QQ'][0],
								'mail'=>@$_r['a']['邮箱'][0]
							);
						}
					}else{
						$this->_verify($_data,array('qq','mail'),array('^[0-9]*$','^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$'));
						$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('QQ'=>$_data['qq'],'邮箱'=>$_data['mail'])),'WHERE i='.$_i);
					}	
				}elseif($_a==='receipt'){
					
					if($_data['action']==='get'){
						$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_uid);
						if(@$_r['a']){
							return array(
								'receiptname'=>@$_r['a']['收货名称'][0],
								'receiptphone'=>@$_r['a']['收货手机'][0],
								'receiptaddress'=>@$_r['a']['收货地址'][0]
							);
						}
					}else{
						$this->_verify($_data,array('receiptname','receiptphone','receiptaddress','receiptphone'),array('','','','^[0-9]*$'));
						$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('收货名称'=>$_data['receiptname'],'收货手机'=>$_data['receiptphone'],'收货地址'=>$_data['receiptaddress'])),'WHERE i='.$_uid);	
					}
				
				}elseif($_a==='proof'){
					if($_data['action']==='get'){
						$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_uid);
						if(@$_r['a']){
							return array(
								'proof'=>@$_r['a']['证件'][0]?$this->en(@$_r['a']['证件'][0]):'',
								'name'=>@$_r['a']['名称'][0],
								'real'=>@$_r['a']['实名'][0]
							);
						}
					}else{
						$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_uid);
						if(@$_r['a']){
							$_old=@$_r['a']['证件'][0];
						}
						$_d=$this->pathInfo($_data['proof']);
						if($_d['id']){
							$_new=$this->de($_d['id']);
							if($_new!==@$_old){
								$this->mtfQueueAdd(array('p'=>$this->en($_new).'.jpg','quota'=>$_uid));
								if($_old){
									$this->mtfQueueDel(array('i'=>$this->en($_old),'id'=>$_uid));
								}	
							}
							$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('证件'=>$_new,'实名'=>'')),'WHERE i='.$_uid);
						}else{
							$this->error('io','input-error');
						}
					}
				}
				
			}
			elseif($_action==='at')
			{
				$_a=$_data['a'];
				$_i='';
				if(is_numeric($_a)){ 
					$_i=$_a;
				}else{
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE e=\'people\' AND FIND_IN_SET(\''.'标题:'.$_a.'\', k)');
					if(@$_r['i']){
						$_i=$_r['i'];
					}
				}
				if($_i){
					$_u=(((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://'). $_SERVER['SERVER_NAME'] .'/'. $_i;
					return array('status'=>true,'u'=>$_u);
				}
				return array('status'=>false);
			}
			elseif($_action==='admin')
			{
				if($this->isAdmin($_uid)){
					$_a=$_POST['a'];
					$_v=$_POST['v'];
					
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o,l,fid','WHERE i='.$_i);
					$_fid=$_r['fid'];
					
					if($_r['e']!=='people'){
						$_o=$_r['o'];
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'l','WHERE i='.$_o);
					}else{
						$_o=$_i;
					}
					
					if($_a==='lock'){
						if($_v==='1'||$_v==='2'){//锁定账户 强制实名
							
							if(!$_i){
								return array('status'=>false);
							}else{
								if(@$_r['l']===$_v){
									$this->mtfMysql->sql('u',$this->db['table'],array('l'=>0),'WHERE i='.$_o);
									return array('status'=>false);
								}else{
									$this->mtfMysql->sql('u',$this->db['table'],array('l'=>$_v),'WHERE i='.$_o);
									return array('status'=>true);
								}
							}
						}elseif($_v==='3'){//锁定来源
							if($this->mtfProxyCurl->ban($this->conf['key'],$_fid)){
								return array('status'=>true);
							}else{
								return array('status'=>false);
							}
						}elseif($_v==='4'){//删除账户
							$this->mtfQueueDel(array('i'=>$_o));
							return array('status'=>true);
						}
						
					}elseif($_a==='real'){
						
						if(!$_i){
							return array('status'=>false);
						}else{
							if($_v==='0'){
								$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_o);
								if(@$_r['a']){
									return array(
										'name'=>@$_r['a']['名称'][0]?@$_r['a']['名称'][0]:'',
										'proof'=>@$_r['a']['证件'][0]?$this->en(@$_r['a']['证件'][0]):''
									);
								}
							}elseif($_v==='1'){
								
								$_name=$_POST['name'];
								
								$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('实名'=>'通过','名称'=>$_name)),'WHERE i='.$_o);
								$this->mtfMysql->sql('u',$this->db['table'],array('l'=>0),'WHERE l=2');//如果是需实名状态，则恢复账户的正常状态
								return array('status'=>true);
							}elseif($_v==='2'){
								$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('实名'=>'不通过')),'WHERE i='.$_o);
								return array('status'=>true);
							}
						}
						
					}elseif($_a==='clear'){
						$_a=array();$_b=array();
						$_r=$this->mtfMysql->sql('s',$this->db['table_msg'],'v','WHERE s=\'消息\'');
						foreach($_r as $_k=>$_v){
							$_a[]=$_v['v'];
						}
						$__r=$this->mtfMysql->sql('s',$this->db['table'],'i','WHERE i IN('.implode(',',$_a).') AND ar!=\'\'');
						foreach($__r as $_k=>$_v){
							$_b[]=$_v['i'];
						}
						$this->mtfQueueDel(array('i'=>$_b));
						return array('status'=>true);
					}
				}
			}
			elseif($_action==='weal')
			{
				$_i=$_POST['id'];
				$_id=$_POST['i'];
				$_o=$_POST['o'];//收奖励的人
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'w,o,e','WHERE i='.$_id);
				if($_r['w']){
					$_ip=$this->mtfGuid->ip();
					$_fid=$_data['fid'];
					if($_r['e']==='people'){
						$_r['o']=$_id;
					}
					if($this->_weal('任务 红包',$_r['w'],$_r['o'],$_id,$_o,$_ip,$_fid,$_i)){
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'nz','WHERE i='.$_i);
						return array('zan'=>$_r['nz'],'status'=>true);
					}
				}
				return array('status'=>false);
			}
			elseif($_action==='mtfBBcode')
			{
				$_t=$_POST['type'];$_i=$_POST['i'];$_id=$_POST['id'];
				
				$_f_p=$this->dir['file'].$this->n2dir($_id).$_id.'.mtfdat';
				if(!file_exists($_f_p)){
					return array('status'=>false);
				}
				if(!$this->hasRight($_uid,$_id,'会员')){
					return array('status'=>false);
				}
				if($_t==='buy'||$_t==='key'){
					$_ip=$this->mtfGuid->ip();
					$_fid=$_data['fid'];
					
					$_c=file_get_contents($_f_p);
					
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,o','WHERE i='.$_id);
					
					if($_r['e']==='people'){
						$_o=$_id;
					}else{
						$_o=$_r['o'];
					}
						
					if($_t==='buy'){
						if($_o===$_uid){//自己买自己·不增减
						}else{
							$_zan=$this->mtfBBcode->add($_c,array('uid'=>$_uid,'id'=>$_id,'i'=>$_i,'step'=>1));
							$this->fen('zan','购买 内容',$_o,$_uid,$_id,-$_zan,$_ip,$_fid,1,$_i);
						}
					}elseif($_t==='key'){
						$_num=$_POST['num'];
						$_a=$this->mtfBBcode->add($_c,array('uid'=>$_uid,'id'=>$_id,'i'=>$_i,'step'=>1));
						if(!@$_num){
							exit;
						}else{
							$_n=$_a['num'];
							$_key=$_a['key'];
							if($_num>$_n){
								$this->error('uid','exceed-num');
							}elseif($_num>count($_key)){
								$this->error('uid','lack-stock');
							}else{
								$_zan=$_a['zan']*$_num;
							}
						}
						if($_o===$_uid){//自己买自己·不增减
						}else{
							$this->fen('zan','购买 卡密',$_o,$_uid,$_id,-$_zan,$_ip,$_fid,1,$_i);
						}
					}
					
					$_c_new=$this->mtfBBcode->add($_c,array('uid'=>$_uid,'id'=>$_id,'i'=>$_i,'n'=>$_num,'step'=>2));
					if($_c_new!==$_c){
						file_put_contents($_f_p,$_c_new);
					}
					//更新价格和销量
					$_bb=$this->mtfBBcode->parse($_c_new,array('type'=>'add'));
					$this->mtfMysql->sql('u',$this->db['table'],array('bz'=>@$_bb['av']['zan'],'bn'=>@$_bb['av']['num']),'WHERE i='.$_id);
					
					return array('status'=>true);
				}
			}
			elseif($_action==='mtfCC')
			{
				$_t=$_POST['type'];
				$_si=$_POST['si'];
				
				if($_t==='list'){
					if(!$this->hasRight($_uid,'','会员')){
						return array('status'=>false);
					}
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'j','WHERE i='.$_uid);
					if(@$_r['j']){
						return @$_r['j'];
					}
				}elseif($_t==='save'){
					if(!$this->hasRight($_uid,'','会员')){
						return array('status'=>false);
					}
					
					//$_si=$_POST['si'];
					unset($_POST['type']);
					unset($_POST['si']);
					
					if($_si){
						$_j=array();
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'j','WHERE i='.$_uid);
						if(@$_r['j']){
							$_j=json_decode($_r['j'],true);
						}
						$_POST['time']=date("Y-m-d H:i:s");
						$_j[$_si]=$_POST;
						$this->mtfMysql->sql('u',$this->db['table'],array('j'=>json_encode($_j)),'WHERE i='.$_uid);
						return array('status'=>true);
					}
				}elseif($_t==='result'){
					$_id=$_POST['id'];
					$_n=$_id.'.mtfdat';
					$_f_p=$this->fileOrTmp($_n);
					if($_f_p){
						$_c=file_get_contents($_f_p);
					
						$_i=$_POST['i'];
						$_ip=$this->mtfGuid->ip();
						$_c_new=$this->mtfBBcode->add($_c,array('uid'=>$_uid,'ii'=>$_i,'ip'=>$_ip,'si'=>$_si));
						if($_c_new!==$_c){
							file_put_contents($_f_p,$_c_new);
						}
						$_r=$this->mtfBBcode->add($_c_new,array('step'=>1,'si'=>$_si));
					}
					return $_r;
				}
			}
		}
	}
	
	public function mtfQueuePage($_total,$_per,$_page)
	{
		$_r=array();
		if($_per){
			$_prev='上页';
			$_next='下页';
			$_p=ceil(intval($_total)/$_per);
			
			if($_p>1)
			{
				$_r[1]=array('p'=>1,'s'=>1);
				$_start=$_page>1?$_page-1:1;$_end=$_page+1<$_p?$_page+1:$_p;
				for ($_j=$_start; $_j<=$_end; $_j++) {
					$_r[$_j]=array('p'=>$_j,'s'=>$_j);	
				}
				
				$_a=floor(($_start-1)/2)+1;
				$_r[$_a]=array('p'=>$_a,'s'=>$_a);
				$_a=$_end+floor(($_p-$_end)/2);
				$_r[$_a]=array('p'=>$_a,'s'=>$_a);
				
				$_r[$_p]=array('p'=>$_p,'s'=>$_p);
				if($_page<=$_p)
				{
					if($_page>1){
						$_r[$_p+1]=array('p'=>$_page-1,'s'=>$_prev,'l'=>1);	
					}
					if($_page<$_p)
					{
						$_r[$_p+2]=array('p'=>$_page+1,'s'=>$_next,'l'=>1);	
					}
				}
				$_r[$_page]=@array_merge(@$_r[$_page],array('c'=>1));
				$_r[1]['p']=0;//第一页不显示page={page}
			}
		}
		ksort($_r);
		return array_values($_r);
	}
	
	private function _string2ar($_html){
		$urls=array();$ids=array();
		$_arv=array('mtfdat'=>array('pattern'=>"[\d+]{6,18}"));
		$_exts=implode('|',array_keys($this->conf['ext2type']));
		preg_match_all("/(".$_arv['mtfdat']['pattern']."\.(".$_exts."))/", $_html, $_m);
		
		foreach($_m[0] as $_k=>$_v){
			$_d=$this->pathInfo($_v);
			if($_d['e']==='url'){	
				$urls[$_d['id']]=$this->getContent($this->dir['file'].$this->n2dir($_d['id']).$_d['id'].'.'.$_d['e'],'view');
			}else{
				$ids[$_d['id']]=array('',$_d['e']);	
			}
		}
		return array('urls'=>$urls,'ids'=>$ids);
	}
	
	private function _diff($_old_mtfdat='',$_new_html='')
	{
		$_old_url=array();$_old_id=array();
		$_new_url=array();$_new_id=array();
		$_add_url=array();$_add_id=array();
		$_del_url=array();$_del_id=array();
		$_sam_url=array();$_sam_id=array();
		
		if($_new_html){
			$_new_url=$this->mtfUrl->string2url($_new_html);
			preg_match_all('/<img.*?src="(.*?)".*?>/',$_new_html,$_m);
			foreach($_m[1] as $_k=>$_v){
				$_d=$this->pathInfo($_v);
				$_new_id[$_d['id']]=array('/<img[^<]+?'.$_d['id'].'[^<]+?>/',$_v);//[^<]+?避免连续<img><img>多匹配
			}
			$_r=$this->_string2ar($_new_html);//引用 .mtfdat的情况
			$_new_url+=$_r['urls'];
			$_new_id+=$_r['ids'];
		}
		
		if($_old_mtfdat){
			$_r=$this->_string2ar($_old_mtfdat);
			$_old_url=$_r['urls'];
			$_old_id=$_r['ids'];
		}
		
		//新增
		$_add_url=array_diff($_new_url,$_old_url);
		$_add_id=array_filter(array_diff_key($_new_id,$_old_id));//去除可能的空元素
		
		//不动
		$_sam_url=array_intersect($_old_url,$_new_url);//注意顺序，old_url应在new_url前，保留原有键名
		$_sam_id=array_intersect_key($_new_id,$_old_id);
		
		//删除
		$_del_url=array_diff($_old_url,$_new_url);//同上
		$_del_id=array_diff_key($_old_id,$_new_id);
		
		return array('add_url'=>$_add_url,'add_id'=>$_add_id,'del_url'=>$_del_url,'del_id'=>$_del_id,'sam_url'=>$_sam_url,'sam_id'=>$_sam_id);
	}
	
	//是否存在本地
	private function _islocal($_e){
		if($_e==='mtfdat'||$_e==='url'){
			return true;
		}else{
			return false;
		}
	}
	
	//获取hash和Hash_md5
	public function hashhm($_f,$_force_local=0){
		if($_force_local || $this->_islocal($_f['e'])){
			$_h=$this->hash($_f['p']);
			$_hm=md5_file($_f['p']);
		}else{
			$_h=@$this->_cache['hash'][$_f['n']]['hash'];
			$_hm=@$this->_cache['hash'][$_f['n']]['hm'];
		}
		return array('hash'=>$_h,'hm'=>$_hm);
	}
	
	private function _hash_cache($_f_ns){
		$this->_cache['hash']=$this->mtfProxyCurl->hash($this->conf['domain']['cdn'],implode(',',$_f_ns));
	}
	
	private function _hash_load($_ps,$_fc){
		//读入远程文件的hash到缓存
		$_a=array();
		foreach($_ps as $_k=>$_v){
			$_p=$_fc($_v);
			$_f=$this->pathInfo($_p);
			if($this->_islocal($_f['e'])){
			}else{
				$_a[]=$_f['n'];
			}
		}
		if($_a){
			$this->_hash_cache($_a);
		}
	}
	
	private function _uidLock($_uid=''){//校验账户状态
		if($_uid){
			$_r=$this->mtfMysql->sql('s1',$this->db['table'],'l','WHERE i='.$_uid);
			$_l=$_r['l'];
			switch($_l){
				case '1':
					$this->error('uid','be-block');
					break;
				case '2':
					$this->error('uid','real-need');
					break;	
				default:
					break;
			}
					
		}
	}
	public function getE($ext, $e) {
		return in_array(strtolower($ext), array('gif','jpg','png','jpeg')) ? $ext : $e;
	}
	
	public function mtfQueueAdd($_data=array())
	{
		
		
		$_f=array();
		$_ar=array();
		$_hash=array();
		$_uid=$this->uid2id(@$_SERVER['HTTP_UID']);
		$this->_uidLock($_uid);
		
		if(@$_data['p']){
			$_ps=$_data['p'];
			$__d=array('cdn'=>array(),'dat'=>array());
			$_id=array();
			$_strtr=array();
			if(!is_array($_ps)){
				$_ps=array($_ps);
			}
			$this->_hash_load($_ps,function($_v){return $_v;});
			
			foreach($_ps as $_k=>$_p){
				$_a=parse_url($_p);
				$_i=pathinfo($_p);
				$_f_p=$this->dir['tmp'].substr($this->n2dir($_i['basename']),0,-1).'/'.$_a['path'];
				
				$_f=$this->pathInfo($_f_p);
				$_f['c']=$this->config($_f_p);
				// 只处理图片
				$_f['e']=$this->getE($_f['c']['ext'], $_f['e']);
				unset($_f['d']);//避免与弹幕“d”字段冲突
				
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE i='.$_f['id']);
				if(@$_r['i']){//如果是已经存在的文件
					$this->mtfRelate->sql('i1',$this->db['table'],array('q'=>@$_data['quota']),'WHERE i='.$_r['i']);
				}else{
					$_h=$this->hashhm($_f);
					$_f['h']=$_h['hash'];
					$_f['hm']=$_h['hm'];
					
					if($_f['hm'] && @$_hash[$_f['hm']]){//避免$_f['hm']为空的情况
						$_f['id']=$_hash[$_f['hm']];
					}else{
						$_hash[$_f['hm']]=$_f['id'];
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE hm!=\'\' AND hm="'.$_f['hm'].'"');	
						if(@$_r['i']){
							$this->mtfRelate->sql('i1',$this->db['table'],array('q'=>@$_data['quota']),'WHERE i='.$_r['i']);
							$_strtr[$_f['id']]=$_r['i'];
							$_f['id']=$_r['i'];
						}else{
							$this->mtfMysql->sql('i',$this->db['table'],array('i'=>$this->de($_f['id']),'e'=>$_f['e'],'h'=>$_f['h'],'hm'=>$_f['hm'],'o'=>$_uid,'q'=>@$_data['quota']));
							if($this->_islocal($_f['e'])){
								$__d['dat'][]=$_f['id'].'.'.$_f['e'];
							}else{
								$__d['cdn'][]=$_f['id'].'.'.$_f['e'];
							}
						}
					}
				}
				$_id[]=$_f['id'];		
			}
			if($__d['dat']){
				$this->mtfQueue->urlAdd($__d['dat'],'移动',$this->conf['domain']['dat'],60);
			}
			if($__d['cdn']){
				$this->mtfQueue->urlAdd($__d['cdn'],'移动',$this->conf['domain']['up'],120);
			}
			return array('id'=>$_id,'strtr'=>$_strtr);
		}elseif($_data){
			$_old_mtfdat='';
			if(@$_data['ed_i']){
				$_mtfdata_id=$_data['ed_i'];
				if($this->hasRight($_uid,$_mtfdata_id,'授权')){
					$_old_mtfdat=$this->getContent($this->dir['file'].$this->n2dir($_mtfdata_id).$_mtfdata_id.'.mtfdat','view');
					
					if(@$_data['data']){//编辑
						$_mode='edit';
					}else{//删除
						$_mode='del';
					}
				}else{
					exit;	
				}
			}else{//添加
				
				if(!$_data['i']){
					//子域名
					$_dn_id=array_flip($this->conf['dn'])[$_SERVER['SERVER_NAME']];
					if($_dn_id){
						settype($_dn_id,'string');//需要转换类型，与uid对比
						$_data['i']=$_dn_id;
					}
				}
				
				$_mtfdata_id=$this->getName();
				if(empty($this->mtfUnit->clearSpace($_data['data'])))
				{
					$this->error('io','empty');
				}elseif(!$_uid){
					//匿名用户只能评论/回复
					if($_data['i']){
						if(is_numeric($_data['i'])){
							$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE i="'.$_data['i'].'"');
							if($_r['i']){
								$_nm=@$_data['nm'];
							}else{
								$this->error('uid','login-need');
							}
						}else{
							$this->error('uid','login-need');
						}
					}else{
						$this->error('uid','login-need');
					}
				}
				$_mode='add';
			}
			
			$_data['data']=strip_tags(str_replace('&nbsp;','',$_data['data']), "<p> <b> <br> <img> <div>");//先strip_tags再html_entity_decode
			$_data['data']=html_entity_decode($_data['data']);//&amp;->& &quot;->" '->' &lt;-> <  &gt;-> >
			$_bb=$this->mtfBBcode->parse($_data['data'],array('type'=>'add'));//先转BBcode，再转图片视频等
			$_data['data']=@$_bb['s'];
			
			$_diff=$this->_diff($_old_mtfdat, @$_bb['ss'] ? $_bb['ss'] : $_data['data']);
			if($_diff['add_url']){
				foreach($_diff['add_url'] as $_i=>$_url){
					$_f['id']=$this->getName();
					$_f['e']='url';
					
					$_f['n']=$_f['id'].'.'.$_f['e'];
					
					$_f['dir']=$this->dir['tmp'].$this->n2dir($_f['n']);
					if(!is_dir($_f['dir'])){
						mkdir($_f['dir']);
					}
					
					
					$_f['p']=$_f['dir'].$_f['n'];
					file_put_contents($_f['p'],$_url);
					$_h=$this->hashhm($_f);
					$_f['h']=$_h['hash'];
					$_f['hm']=$_h['hm'];
					
					if($_f['hm'] && @$_hash[$_f['hm']]){//避免$_f['h']为空的情况
						$_f['id']=$_hash[$_f['hm']];
					}else{
						$_hash[$_f['hm']]=$_f['id'];
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE hm!=\'\' AND hm="'.$_f['hm'].'"');//使用md5判断，避免相似
						if(@$_r['i']){
							$this->mtfRelate->sql('i1',$this->db['table'],array('q'=>$_mtfdata_id),'WHERE i='.$_r['i']);
							$_f['id']=$_r['i'];
							//权限处理：
							$_auth_id[]=$_r['i'];
						}else{
							//只有完全新增的文件：录入作者
							$_f['o']=$_uid;
							
							$_f['t']='url';
							$_d[]=$_f;
						}
					}
					//echo $_url;
					$_data['data']=str_replace($_url,$_f['id'].'.'.$_f['e'],$_data['data']);
					
				}
			}
			
			if($_diff['sam_url']){
				foreach($_diff['sam_url'] as $_i=>$_url){
					$_data['data']=str_replace($_url,$_i.'.url',$_data['data']);
					//$_list['url'][]=array('i'=>$_i,'u'=>$_url);
				}
			}
			
			$__d=array('del'=>array());//待远程删除的数据
			
			if($_diff['del_url']){
				foreach($_diff['del_url'] as $_i=>$_url){
					$__d['del'][]=$_i;
				}
			}
			if($_diff['add_id']){
				$this->_hash_load($_diff['add_id'],function($_v){return $_v[1];});
				foreach($_diff['add_id'] as $_i=>$_ar){
					
					$_a=parse_url($_ar[1]);
					$__i=pathinfo($_a['path']);
					$_f_p=$this->dir['tmp'].substr($this->n2dir($__i['basename']),0,-1).$_a['path'];
								
					$_f=$this->pathInfo($_f_p);
					$_f['c']=$this->config($_f_p);
					if(@$_f['c']['ext']){
						$_f['e']=$_f['c']['ext'];
					}
					
					unset($_f['d']);//避免与弹幕“d”字段冲突
					
					$skip=0;
					
					if(!is_numeric($_f['id'])){
						//引用
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,e','WHERE i='.$_i);
						if(@$_r['i']){//如果是已经存在的文件
							$this->mtfRelate->sql('i1',$this->db['table'],array('q'=>$_mtfdata_id),'WHERE i='.$_r['i']);
							$skip=1;
						}else{
							$_r=$this->mtfProxyCurl->down($this->conf['domain']['cdn'], $_ar[1]);
							//站外图片，直接过滤
							$_f['p']=$_r['p'];
							$_f['id']=$_r['id'];
							$_f['e']=$_r['e'];
							
							//Hash必须
							$_f['n']=$_f['id'].'.'.$_f['e'];
							$this->_hash_load(array($_f['p']),function($_v){return $_v;});
						}	
					}
					
					if(!$skip){
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE i='.$_f['id']);
						if(@$_r['i']){//如果是已经存在的文件
							$this->mtfRelate->sql('i1',$this->db['table'],array('q'=>$_mtfdata_id),'WHERE i='.$_r['i']);
							//权限处理：
							$_auth_id[]=$_r['i'];
						}else{
							//只有完全新增的文件：录入作者
							$_f['o']=$_uid;
							
							$_h=$this->hashhm($_f);
							$_f['h']=$_h['hash'];
							$_f['hm']=$_h['hm'];
							
							if($_f['hm'] && @$_hash[$_f['hm']]){//避免$_f['h']为空的情况
								$_f['id']=$_hash[$_f['hm']];
							}else{
								$_hash[$_f['hm']]=$_f['id'];
								unset($_r);
								$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE hm!=\'\' AND hm="'.$_f['hm'].'"');	
								if(@$_r['i']){
									$this->mtfRelate->sql('i1',$this->db['table'],array('q'=>$_mtfdata_id),'WHERE i='.$_r['i']);
									$_f['id']=$_r['i'];
								}else{
									$_f['t']=$this->conf['ext2type'][$_f['e']];
									$_d[]=$_f;
								}
							}
						}
					}	
					if($_ar[0]){
						$_data['data']=preg_replace($_ar[0],($_f['id']?$_f['id'].'.'.$_f['e']:''),$_data['data']);
					}
				}
			}
			
			if($_diff['sam_id']){
				foreach($_diff['sam_id'] as $_i=>$_ar){
					if($_ar[0]){
						$_f=$this->pathInfo($_ar[1]);
						$_f['c']=$this->config($_f['p']);//视频/音频
						$_data['data']=preg_replace($_ar[0],$_f['id'].'.'.(@$_f['c']['ext']?$_f['c']['ext']:$_f['e']),$_data['data']);
					}
				}
			}
			if($_diff['del_id']){
				foreach($_diff['del_id'] as $_i=>$_ar){
					if(@$_ar[1]==='mtfdat'){
						$this->mtfRelate->sql('d1',$this->db['table'],array('q'=>$_mtfdata_id),'WHERE i='.$_i);
					}else{
						$__d['del'][]=$_i;
					}
				}
			}
			
			if($__d['del']){
				$this->mtfQueueDel(array('i'=>$__d['del'],'id'=>$_mtfdata_id,'a'=>$_mode));
			}
			if($_mode==='add' || $_mode==='edit'){
				
				$_ip=$this->mtfGuid->ip();
				$_fid=$_data['fid'];
				$_i=$_data['i'];
				
				$_f=array();
				$_f['id']=$_mtfdata_id;
				$_f['t']=$_f['e']='mtfdat';
				$_f['n']=$_f['id'].'.'.$_f['e'];
				
				$_f['dir']=$this->dir['file'].$this->n2dir($_f['n']);//文档直接覆盖，不再调起 移动
				if (is_dir($_f['dir']) === false) mkdir($_f['dir']);
				$_f['p']=$_f['dir'].$_f['n'];
				
				
				$_ti=array();//发送消息通知（数据库）
				$_to=array();//发送消息通知（即时）
				$_mail=array();//发送邮件通知
				
				preg_match_all("/@(\d+)\b/", $_data['data'], $_m);
				foreach($_m[0] as $_k=>$_v){
					$__v=substr($_v,1);
					if($__v==='0'){//公告
						if($this->isAdmin($_uid)){
							$_ti[]=$__v;
						}
					}else{
						$_ti[]=$__v;
					}
					//$_data['data']=str_replace($_v,'',str_replace($_v.' ','',$_data['data']));
				}
				
				$_tags=array();//获取标签
				$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e,a,k,o','WHERE i=\''.$_i.'\'');//加引号，避免出现i=fol有结果的情况
				
				if($_r['e']==='people'){//如果是People
					$_ti[]=$_i;
					
					if($_uid===$_i){
						$_to[]=$_i;
					}else{//如果其他人在作者主页上发言，则为消息
						$_to[]='u'.$_i;
						$_f['msg']=1;
						$_mail[]=$_i;
					}
				}else{
					if($_r['e']==='mtftag'){//标签
						$_to[]=$_i=$_uid;
						
						$_key=$this->mtfAttr->parseA($_r['k']);
						if(@$_key['标题'][0]){
							$_tags[]=$_key['标题'][0];
						}
					}elseif($_r['e']){
						$_ti[]=$_r['o'];
						$_to[]=$_i;
					
						if($_r['o']===$_uid){
							
						}elseif($_r['o']){
							$_writer=$_r['o'];
							$_to[]='u'.$_r['o'];
							$_mail[]=$_r['o'];
						}
						
						if($_mode==='add'){//BBcode-回复可见
							
							$_attr=$this->mtfAttr->parseA($_r['a'],'|');//禁止回复
							if(@$_attr['禁止回复'][0]){
								$this->error('io','ban-reply');
							}
							
							$_f_p=$this->dir['file'].$this->n2dir($_i).$_i.'.mtfdat';
							if(!file_exists($_f_p)){
								$_f_p=$this->dir['tmp'].$this->n2dir($_i).$_i.'.mtfdat';
							}
							if(file_exists($_f_p)){
								$_c=file_get_contents($_f_p);
								$_c_new=$this->mtfBBcode->add($_c,array('uid'=>$_uid));
								if($_c_new!==$_c){
									file_put_contents($_f_p,$_c_new);
								}
							}
						}
						
					}else{//标签
						$_a=explode('/',$_i);
						if(is_numeric($_a[0]) && $_a[1]){
							$_to[]=$_a[0];
							$_tags[]=$_a[1];
						}elseif(!@$_a[1]){
							$_tags[]=$_a[0];
						}
						
						$_i=$_uid;
					}
				}
				
			
				$_ti=array_filter(array_unique($_ti));//匿名用户无未读消息
				if($_f['msg']===1){//如果是消息，添加权限
					$_ar=array();
					$_ar=$_ti;
					if($_uid){
						array_push($_ar,$_uid);
					}
					if(@$_writer){
						array_push($_ar,$_writer);
					}
					$_ar=array_unique($_ar);
					$_auth=implode(',',$_ar);
				}else{//非消息，同步到主页
					if($_mode==='add'){//编辑，不改变关系
						$this->mtfRelate->sql('i1',$this->db['table'],array('r'=>$_f['id']),'WHERE i='.$_i);
						$this->mtfMysql->sql('u',$this->db['table'],array('t0'=>date('Y-m-d H:i:s')),'WHERE i='.$_i);
					}	
				}
				
				if(@$_auth_id){//被引用文件的权限处理
					foreach($_auth_id as $__k=>$__v){
						if($_auth){
							$this->mtfRelate->sql('i1',$this->db['table'],array('ar'=>$_auth,'aw'=>$_auth),'WHERE i='.$__v);
						}else{
							$this->mtfMysql->sql('u',$this->db['table'],array('ar'=>''),'WHERE i='.$__v);
						}
					}
				}
				if($_ti){
					foreach($_ti as $_k=>$_o){
						if($_uid!==$_o){
							$_r=$this->_sql_i_msg(2,'消息',$_uid,$_o,$_f['id'],'',$_ip,$_fid,1,1);//未读消息
							if($_r){
								//获取 消息的最大值
								$_r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'max(i) as i','WHERE g=2');
								if(@$_r['i']){
									$_msg=$_r['i'];
								}
								if($_o==='0'){//公告
									$this->mtfMysql->sql('u',$this->db['table'],array('nmsg1'=>'///nmsg1+1'),'WHERE e=\'people\'');
								}else{
									$this->mtfMysql->sql('u',$this->db['table'],array('nmsg1'=>'///nmsg1+1'),'WHERE i='.$_o);
								}
							}
						}
						
					}
				}
				
			
				preg_match_all("/#([^#\s]+)#/", $_data['data'], $_m); // 话题改为与微博一致：需输入两个 # # 号 
				if(isset($_m[0])){
					foreach($_m[0] as $_k=>$_v){
						$_tag = trim($_v, '#');
						if (!in_array($_tag, $_tags)) $_tags[] = $_tag;
						$_data['data'] = str_replace($_v, '', $_data['data']);
					}	
				}
				$_f['parent'] = $_i;//获取文章父级
				
				if($_tags){
					$_f['k']=$_tags;
				}
				unset($_tags);
				
				$_data['data']=strtr($_data['data'],array('<div'=>'<p','</div>'=>'</p>'));
				
				file_put_contents($_f['p'],$_data['data']);
				$_h=$this->hashhm($_f);
				
				$_f['h']=$_h['hash'];
				$_f['hm']=$_h['hm'];
				
				//模式和变量
				$_f['m']=@$_data['m'];
				$_f['d']=@$_data['d'];
				$_f['v']=@$_data['v'];
				$_f['tt']=@$_data['tt'];
				
				//购买
				if(@$_bb['av']['zan']){//价格
					$_f['bz']=$_bb['av']['zan'];
					$_f['bn']=$_bb['av']['num'];
				}else{
					$_f['bz']=0;
					$_f['bn']=0;
				}
				
				//只有完全新增的文件：录入作者
				if($_mode==='add'){
					$_f['o']=$_uid;
				}
				
				$_d[]=$_f;
				
				if($_f['hm'] && @$_hash[$_f['hm']]){//避免$_f['h']为空的情况
					$_f['id']=$_hash[$_f['hm']];
				}else{
					$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i','WHERE hm!=\'\' AND hm="'.$_f['hm'].'"');
					if(@$_r['i']){//mtfdat
						//$_f['id']=$_r['i'];
						if($_mtfdata_id!==$_r['i']){//避免编辑状态，自己和自己相似，造成循环
							file_put_contents($_f['p'],$_r['i'].'.mtfdat');
							//相似文章，不记录 hash 和 md5_hash
							$_d[count($_d)-1]['h']='';
							$_d[count($_d)-1]['hm']='';
							$this->mtfRelate->sql('i1',$this->db['table'],array('q'=>$_f['id']),'WHERE i="'.$_r['i'].'"');
						}
					}
					if(@$_d){
						$__d=array('cdn'=>array(),'dat'=>array());
						foreach($_d as $_k=>$_v){//首次添加，记录父级
							
							$___d=array('t0'=>date('Y-m-d H:i:s'),'i'=>$this->de($_v['id']),'e'=>$_v['e'],'h'=>$_v['h'],'hm'=>$_v['hm'],'m'=>@$_v['m'],'d'=>@$_v['d'],'tt'=>@$_v['tt'],'v'=>@$_v['v'],'fid'=>$_fid,'ip'=>$_ip,'q'=>$_f['id']);	
							
							$___d+=(@$_v['parent']?(array('p'=>$_v['parent'])+(@$_writer?array('aw'=>$_writer):array())):array())+(@$_v['msg']?array('msg'=>$_v['msg']):array())+(@$_auth?array('ar'=>$_auth,'aw'=>$_auth):array())+(@$_nm?array('nm'=>$_nm):array())+(@$_v['o']?array('o'=>$_v['o']):array())+(@$_v['bz']||$_v['bz']===0?array('bz'=>$_v['bz'],'bn'=>@$_v['bn']):array());
							
							if($_k===count($_d)-1){
								$___d=array_merge($___d,array('q'=>''));
							}
							
							//新增修改时间
							$this->mtfMysql->sql('iu',$this->db['table'],$___d);
							if($this->_islocal($_v['e'])){
								if($_v['e']!=='mtfdat'){//文档直接覆盖，不再调起 移动
									$__d['dat'][]=$_v['id'].'.'.$_v['e'];
								}
							}else{
								$__d['cdn'][]=$_v['id'].'.'.$_v['e'];
							}
							
							if(@$_v['k']){
								$this->mtfAttr->sql('i1',$this->db['table'],array('k'=>array('标签'=>$_v['k'])),'WHERE i='.$this->de($_v['id']));
							}
						}
						
						if($__d['dat']){
							$this->mtfQueue->urlAdd($__d['dat'],'移动',$this->conf['domain']['dat'],60);
						}
						if($__d['cdn']){
							$this->mtfQueue->urlAdd($__d['cdn'],'移动',$this->conf['domain']['up'],120);
						}
					}
				}
			
			}
			
			if($_mode==='add'){
				$_data[$_f['id']]=$this->getContent($_f['p'],'list',array('pi'=>$_uid,'o'=>$_uid,'nm'=>$_nm));
				if(@$_msg){
					$_data[$_f['id']]['list']['msg']=$_msg;
				}
				$_data['to']=implode(',',$_to);
				$_data['uid']=$_uid;
				
				//发送邮件通知
				foreach($_mail as $_k=>$_v){
					$_r=$this->mtfAttr->sql('s1',$this->db['table'],'a,k','WHERE i='.$_v);
					if(@$_r['a']['邮件发送时间']){			
						if((time()-strtotime(str_replace('：',':',$_r['a']['邮件发送时间'][0])))/86400<$this->conf['time']['mail']){
							break;//两次邮件发送时间的间隔需要小于1天
						}
					}
					if(@$_r['a']['邮箱']){
						$_mailtoaddress=$_r['a']['邮箱'][0];
					}elseif(@$_r['a']['QQ']){
						$_mailtoaddress=@$_r['a']['QQ'][0].'@qq.com';
					}
					if(@$_r['k']['标题']){
						$_mailtoname=$_r['k']['标题'][0];
					}else{
						$_mailtoname=$_v;
					}
					
					$this->mtfAttr->sql('u1',$this->db['table'],array('a'=>array('邮件发送时间'=>date('Y-m-d h:i:s'))),'WHERE i='.$_v,0,'|');
					
					$this->mtfProxyCurl->email($this->conf['domain']['msg'],array('frommail'=>$this->conf['mail']['frommail'],'fromname'=>$this->conf['mail']['fromname'],'host'=>$this->conf['mail']['host'],'port'=>$this->conf['mail']['port'],'username'=>$this->conf['mail']['username'],'password'=>$this->conf['mail']['password'],'toaddress'=>$_mailtoaddress,'toname'=>$_mailtoname,'data'=>$_data['data']));
				}
				
			}elseif($_mode==='edit'){
				if($_data['ed_t']==='list'){//type为list的元素
					
					$_data[$_f['id']]=$this->getContent($_f['p'],'list',array('o'=>$_uid,'nm'=>$_nm,'load'=>$_f['id']));//读取标题和描述
					$_data['to']=implode(',',$_to);
					
				}else{
					$_data=$this->getContent($_f['p'],'mustache');
				}
			}
			
			return array('data'=>$_data,'mode'=>$_mode,'i_e'=>@$_f['id']?$this->mtfCrypt->en($_f['id']):'');
		}
	}
	
	public function fileOrTmp($_bn,$_q=''){
		$_d_dir=$this->dir['file'].$this->n2dir($_bn);
		if(!file_exists($_d_dir.$_bn)){
			$_d_dir=$this->dir['tmp'].$this->n2dir($_bn);
		}
		if(file_exists($_d_dir.$_bn)){
			return $_d_dir.($_q?$_q:$_bn);
		}else{
			return false;
		}
	}
	
	public function mtfQueueDel($_data=array())
	{
		
		$_is=$_data['i'];
		$_uid=$this->uid2id(@$_SERVER['HTTP_UID']);
		
		if(!is_array($_is)){
			$_is=array($_is);
		}
		
		$__d=array('cdn'=>array(),'dat'=>array(),'del'=>array());//待远程删除的数据，del:整合mtfQueueDel
		
		foreach($_is as $__k=>$_i){
			$__r=$this->mtfMysql->sql('s1',$this->db['table'],'i,a,e,o,q,h,hm,r','WHERE i="'.$this->de($_i).'"');
			
			if(!@$__r['e']){
				//如果已经被删除，直接跳过
				continue;
			}
			
			$_attr=$this->mtfAttr->parseA(@$__r['a'],'|');
			//关注与粉丝
			if(@$_attr['关注']){
				foreach($_attr['关注'] as $_k=>$_v){
					$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('粉丝'=>$_i)),'WHERE i='.$_v,0,'|');	
				}
			}
			if(@$_attr['粉丝']){
				foreach($_attr['粉丝'] as $_k=>$_v){
					$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('关注'=>$_i)),'WHERE i='.$_v,0,'|');	
				}
			}
			
			//字幕
			if(@$_attr['字幕']){
				foreach($_attr['字幕'] as $_k=>$_v){
					$__d['del'][]=$_v;
				}
			}
			
			//清理实名认证的图片
			if(@$_attr['证件']){
				foreach($_attr['证件'] as $_k=>$_v){
					$__d['del'][]=$_v;
				}
			}
			
			//关系：包含的内容（作者包含的文章，文章包含的回复）
			if($__r['r']){
				$_ra=explode(',',$__r['r']);
				foreach($_ra as $_k=>$_v){
					$__d['del'][]=$_v;
				}
			}
			
			$_t=@$this->conf['ext2type'][@$__r['e']];
			
			if(@$__r['q']){//如果文件被其他文件引用，能够引用其他文件的都是 mtfdat和视频/音频
				
				if($this->hasRight($_uid,$__r['i'],'授权','',0)){//如果是自己的文件
					
					if(strlen(@$_data['id'])<=18){//只有父级是文章时触发 头像上级是人
						if($_data['id']!==$__r['i']){
							$_r=str_replace($_data['id'],'',str_replace($_data['id'].',','',str_replace(','.$_data['id'],'',$__r['q'])));
							if($__r['e']==='mtfdat'){
								if($__r['i']!==$_data['id']){//删除的是引用到文章内的文章
									$this->mtfRelate->sql('d1',$this->db['table'],array('q'=>$_data['id']),'WHERE i='.$__r['i']);
									$__r['q']=$_data['id'];//删除对被删除文件的引用
									$_return=1;
								}
							}else{
								$_r=$this->mtfMysql->sql('s',$this->db['table'],'i','WHERE i IN('.$_r.') AND ( o='.$__r['o'].' OR e=\'people\' )');
								if(@$_r[0]){//如果自己的其它文章，还引用了这个文件，不删除这个文件
									$this->mtfRelate->sql('d1',$this->db['table'],array('q'=>$_data['id']),'WHERE i='.$__r['i']);
									$__r['q']=$_data['id'];//删除对被删除文件的引用
									$_return=1;
								}	
							}	
						}
					}
					
					$_q=explode(',',$__r['q']);
					//从所有引用被删除文件的文件中，删除对被删除文件的引用
					$_replace_id='';
					$_replace_q=array();
					
					foreach($_q as $_k=>$_v){
						if($_t==='mtfdat'){
							//区分 引用文件·全部引用（文件相似）
							$_f=$this->fileOrTmp($_v.'.'.$__r['e']);
							$_h=$this->getContent($_f,'view');
							if($_f){
								if($this->mtfUnit->clearSpace($_h)===$_i.'.'.$__r['e']){//全部引用（文件相似）
									if($_replace_id===''){
										copy($this->fileOrTmp($_i.'.'.$__r['e']),$_f);
										$_replace_id=$_v;
									}else{
										file_put_contents($_f,$_replace_id.'.'.$__r['e']);
										$_replace_q[]=$_v;
									}
								}else{//引用文件
									$_h=str_replace($_i.'.'.$__r['e'],'',$_h);
									file_put_contents($_f,$_h);
									if(!trim($_h)){//如果文章变为空，删除文章本身
										$__d['del'][]=$_v;
									}
								}
							}
						}elseif($_t==='sub'){
							//如果是字幕被一个音视频引用
							if(count($_q)===1){
								$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('字幕'=>$_i)),'WHERE i='.$_v,0,'|');
							}else{
								$this->mtfAttr->sql('d1',$this->db['table'],array('a'=>array('字幕'=>$_i)),'WHERE i='.$_i,0,'|');
								return false;
							}
						}else{
							$_r=$this->mtfMysql->sql('s1',$this->db['table'],'e','WHERE i="'.$_v.'"');
							$__t=$this->conf['ext2type'][$_r['e']];
							if($__t==='mtfdat'){//文章
								$_f=$this->fileOrTmp($_v.'.'.$_r['e']);
								$_h=$this->getContent($_f,'view');
								$_h=str_replace($__r['i'].'.'.$__r['e'],'',$_h);
								file_put_contents($_f,$_h);
								if(!trim($_h)){//如果文章变为空，删除文章本身
									if(@$_data['a']!=='edit'){//编辑模式下，删除原来图片，新增图片时，避免文章被删除
										$__d['del'][]=$_v;
									}
								}
							}
						}
					}
					
					if($_replace_id){
						$this->mtfMysql->sql('u',$this->db['table'],array('h'=>$__r['h'],'hm'=>$__r['hm'],'q'=>implode(',',$_replace_q)),'WHERE i='.$_replace_id);
					}
					
					if(@$_return===1){
						return false;
					}
					
				}else{//如果是其他人的文件
					$_id=$_data['id'];
					$this->mtfRelate->sql('d1',$this->db['table'],array('q'=>$_id),'WHERE i='.$_i);
					return false;
				}
				
			}else{//如果文件是引用的其他文件
				
				$_r=$this->mtfMysql->sql('s',$this->db['table'],'i','WHERE FIND_IN_SET('.$__r['i'].', q)');
				if($_r){
					foreach($_r as $__k=>$_v){
						$this->mtfRelate->sql('d1',$this->db['table'],array('q'=>$__r['i']),' WHERE i='.$_v['i']);
					}
				}
				if($this->hasRight($_uid,$__r['i'],'授权','',0)){//如果是自己的文件
					if(strlen(@$_data['id'])===18){//只有父级是文章时触发
						if($_t==='mtfdat'){
							if($_i!==$_data['id']){//删除的是引用到文章内的文章
								if(@$_data['a']!=='reply'){//排除对文章的回复
									return false;
								}
							}	
						}
					}
				}else{
					return false;
				}
				
			}
					
			//关系：被包含的内容
			$_r=$this->mtfMysql->sql('s',$this->db['table'],'i','WHERE FIND_IN_SET('.$_i.', r)');
			if($_r){
				foreach($_r as $__k=>$_v){
					$this->mtfRelate->sql('d1',$this->db['table'],array('r'=>$_i),' WHERE i='.$_v['i']);
				}
			}
			
			//置顶
			
			$_r=$this->mtfMysql->sql('s',$this->db['table'],'i','WHERE FIND_IN_SET('.$_i.', top)');
			if($_r){
				foreach($_r as $__k=>$_v){
					$this->mtfRelate->sql('d1',$this->db['table'],array('top'=>$_i),' WHERE i='.$_v['i']);
				}
			}
			
			/* 删除文件本体 */
			//删除不同比特率的文件
			if($_t==='video'||$_t==='audio'){
				$__c=array();
				foreach($this->conf['convert'] as $__k=>$__v){
					foreach($__v as $_k2=>$_v2){
						if($__k===$_t){
							$__c[$__k][$_v2['b']]=array('w'=>@$_v2['w'],'ext'=>$_v2['ext']);	
						}
					}
				}
				if(@$_attr['比特率']){
					foreach($_attr['比特率'] as $_k=>$_v){
						if(@$__c[$_t][$_v]){
							$_n=$_i.$this->config2Url(array('b'=>$_v,'w'=>$__c[$_t][$_v]['w'])).'.'.$__c[$_t][$_v]['ext'];
							$__d['cdn'][]=array('n'=>$_n);
							//删除日志
							$__d['cdn'][]=array('n'=>$_n.'.txt','dir'=>'log');
						}
					}
				}
			}
			
			//删除缩略图
			if(@$this->conf['preview'][$_t]['ext']){
				$__d['cdn'][]=array('n'=>$_i.'.'.$this->conf['preview'][$_t]['ext']);
				if($_t==='video'){
					$__d['cdn'][]=array('n'=>$_i.'.'.'jpg');
				}
			}
			
			//删除文档中文件
			if($_t==='mtfdat'){
				$this->mtfQueueAdd(array('ed_i'=>$_i));
			}
			
			//删除自身
			if($this->_islocal($__r['e'])){
				$_f=$this->fileOrTmp($_i.'.'.$__r['e']);
				@unlink($_f);
			}else{
				$__d['cdn'][]=array('n'=>$_i.'.'.$__r['e']);
				
			}
			$this->mtfMysql->sql('d',$this->db['table'],'','WHERE i="'.$this->de($_i).'"');
			
			if($_t==='mtfdat'){
				//消息
				//如果存在未读消息
				$_r=$this->mtfMysql->sql('s',$this->db['table_msg'],'tt','WHERE v="'.$this->de($_i).'" AND vv=1');
				foreach($_r as $_k=>$_v){
					$this->mtfMysql->sql('u',$this->db['table'],array('nmsg1'=>'///nmsg1-1'),'WHERE i='.$_v['tt']);
				}
				$this->mtfMysql->sql('d',$this->db['table_msg'],'','WHERE v="'.$this->de($_i).'"');
			}
		}
		
		if($__d['del']){//整合删除
			$this->mtfQueueDel(array('i'=>$__d['del']));
		}
		
		if($__d['cdn']){//删除远程文件
			$this->mtfQueue->urlAdd($__d['cdn'],'删除',$this->conf['domain']['up'],30);
		}
	}
	
	private function _jsonOrder($_rrr,$_i,$_rr){
		$_tmp=array($_i=>array());
		$_tmp[$_i]=$_rr;
		$_rrr[]=$_tmp;
		return $_rrr;
	}
	
	private function _weal($_s,$_w_json,$_o,$_v,$_uid,$_ip,$_fid,$_vv=''){
		if($_uid){
			if($_w_json){
				$_w=json_decode($_w_json,true);
				$_rec=0;
				if($_w){
					switch($_s){
						case '点击 红包':
							$_t='c';
							$_once=$this->conf['time']['weal']['click']*86400;//一天可以重复领取
						break;
						case '关注 红包':
							$_t='f';
							$_once=1;
						break;
						case '任务 红包':
							$_t='t';
							$_once=1;
							$_rec=1;//记录uid
						break;
						default:
							return false;
					}
					
					if(@$_w[$_t]){
						if(@$_w[$_t][0]['n']){
							if($this->fen('zan',$_s,$_o,$_uid,$_v,$_w[$_t][0]['v'],$_ip,$_fid,$_once)){
								
								if($_t==='t'){
									//给任务对应文章增加爱心♥
									$this->mtfMysql->sql('u',$this->db['table'],array('nz'=>'///nz+'.abs($_w[$_t][0]['v'])),'WHERE i='.$_vv);
								}
								
								$_w[$_t][0]['n']--;
								if($_rec){
									if(!@$_w[$_t][0]['u']){
										$_w[$_t][0]['u']=array();
									}
									$_w[$_t][0]['u'][]=$_uid;
									$_w[$_t][0]['u']=array_unique($_w[$_t][0]['u']);
								}
								
								$_weal=array('m'=>$_t,'n'=>$_w[$_t][0]['v'],'o'=>$_once,'v'=>$_v,'t'=>time());
								
								if($_w[$_t][0]['n']===0){
									array_shift($_w[$_t]);
								}
								if(count($_w[$_t])===0){
									unset($_w[$_t]);
								}
								
								if(count($_w)===0){
									$_w='';
								}else{
									$_w=json_encode($_w);
								}				
								$this->mtfMysql->sql('u',$this->db['table'],array('w'=>$_w),'WHERE i='.$_v);
								return $_weal;
							}
						}
					}
					
				}
			}
		}
							
	}
	
	private function _getSub($_sub){
		if($_sub){
			$_a=$_sub;
			$_b_cn=array();
			$_b_tw=array();
			$_b_other=array();
			
			$_c=array();
			$_txt='';
			
			$__r=$this->mtfMysql->sql('s',$this->db['table'],'i,a,e','WHERE i IN ('.implode(',',$_a).')');
			
			foreach($__r as $_k=>$_v){
				
				$__sub=$this->mtfAttr->parseA($_v['a'],'|');
				$__sub2=array(@$__sub['字幕语种'][0]?$__sub['字幕语种'][0]:'default',$_v['i'],$_v['e']);
				//将语种为zh-cn,zh-tw提前
				switch($__sub['字幕语种'][0]){
					case 'zh-cn':
						$_b_cn[]=$__sub2;
						break;
					case 'zh-tw':
						$_b_tw[]=$__sub2;
						break;
					default:
						$_b_other[]=$__sub2;
						break;
				}
				
				$_c[]=array($__sub['字幕语种'][0]?$__sub['字幕语种'][0]:'default',$_v['i']);
				unset($__sub);
				unset($__sub2);
			}
			
			
			if($_b_cn){
				$_ar=end($_b_cn);
				$_file=$_ar[1].'.'.$_ar[2];
				if(!$_b_tw){
					$_b_tw[]=array('繁',$_ar[1],$_ar[2],'zh-tw');
				}
			}elseif($_b_tw){
				$_ar=end($_b_tw);
				$_file=$_ar[1].'.'.$_ar[2];
				$_b_cn[]=array('简',$_ar[1],$_ar[2],'zh-cn');
			}
			
			if($_file){
				$_txt=$this->mtfZH->convert($this->mtfProxyCurl->sub($this->conf['domain']['cdn'],$_file,'txt'),'zh-cn');
				if($_v['e']==='lrc'){//如果是歌词，保留换行格式
					$_txt='<pre>'.$_txt.'</pre>';
				}
			}
			
			$_sub=array_merge($_b_cn,$_b_tw,$_b_other);
			
			unset($_b_cn);
			unset($_b_tw);
			unset($_b_other);
		}
		return array('sub'=>$_sub,'txt'=>$_txt,'cap'=>$_c);
	}
	
	private function _getMedia($_i,$_t,$_attr,$_key=array()){
		$__c=array();$_source=array();$_sub=array();
									
		foreach($this->conf['convert'] as $__k=>$__v){
			foreach($__v as $_k2=>$_v2){
				if($__k===$_t){
					$__c[$__k][$_v2['b']]=array('w'=>@$_v2['w'],'ext'=>$_v2['ext']);	
				}
			}
		}
		if(@$_attr['比特率']){
			foreach($_attr['比特率'] as $__k=>$__v){
				if(@$__c[$_t][$__v]){
					$_b=$__c[$_t][$__v];
					$_source[]=array('b'=>$__v,'w'=>$_b['w'],'ext'=>$_b['ext']);
				}
			}
		}else{
			$_b=$this->conf['convert'][$_t][0];
			$_source[]=array('b'=>$_b['b'],'w'=>$_b['w'],'ext'=>$_b['ext']);
		}
		
		$_txt='';
		if(@$_attr['字幕']){
			$_ar=$this->_getSub($_attr['字幕']);
			$_attr['字幕']=$_ar['sub'];
			$_txt=$_ar['txt'];
			unset($_ar);
			foreach($_attr['字幕'] as $__k=>$__ar){
				$_sub[]=array('label'=>$__ar[0],'i'=>$__ar[1],'ext'=>$__ar[2],'lan'=>$__ar[3]);
			}
		}
		
		if($_key){
			$_tag = self::FilterTag($_key, $this->conf['tag']['filter'] + array('共享许可'));
			if(empty($_tag) === false) $_tag = $this->mtfAttr->parseK($_tag);
		}
		return array('poster'=>array('i'=>$_i, 'e'=>'gif'),'source'=>$_source,'sub'=>$_sub)+($_txt?array('txt'=>$_txt):array())+($_tag?array('k'=>$_tag):array());
	}
	
	private function _isDownGifExt($_e,$_t='',$_i=''){
		$_d=array();
		$_ext=$_e;
		if(!$_t){
			$_t=$this->conf['ext2type'][$_e];
		}
		if($_t==='image'){
			
		}else{
			$_e=$this->conf['preview'][$_t]['ext'];
			if($_t==='video'){
				$_d+=array('video'=>1);
			}elseif($_t==='audio'){
				
			}else{
				$_d+=array('down'=> $_i ? array('e'=>$_ext) : 1);
			}
			
		}
		$_d+=array('e'=>$_e);
		if ($_e === 'gif') $_d['g'] = 1;
		return $_d;
	}

	private function _getWHfromE($sw, $sh, $e) {
		if (empty($sw) === false && empty($sh) === false) {
		  $sr = $sw / $sh;
		} else {
			$sr = null;
		}
		if ($e === 'gif') $sh = 150;
		else $sh = 300;
		return array($sr ? $sh * $sr | 0 : null, $sh);
	}

	private function _getWH($sw, $sh) {
		if (empty($sw) === false && empty($sh) === false) {
			$sr = $sw / $sh;
			$sw = $sw < 600 ? $sw : 1280;
			$sh = $sw / $sr;
			return array($sw, $sh | 0);
		}
		return array(1280, null);
	}
	static private function FindTag($tags, $filters) {
		foreach ($filters as $filter) {
			if (empty($tags[$filter]) === false) return $tags[$filter];
		}
		return null;
	}
	static private function FilterTag($tags, $filters) {
		$r = array();
		foreach ($filters as $filter) {
			if (empty($tags[$filter]) === false) $r[$filter] = $tags[$filter];
		}
		return $r;
	}
	static private function SortTag($tags, $filters) {
		$r = array();
		foreach ($filters as $filter) {
			if (empty($tags[$filter]) === false) {
				$r[$filter] = $tags[$filter];
				unset($tags[$filter]);
			}
		}
		return $r + $tags;
	} 
	private function _getTag($_key) {
		unset($_key['标题']);
		unset($_key['描述']);
		if (empty($_key)) return array();
		$_tag = array();
		if (empty($_key[0][0]) === false) $_tag []= $_key[0][0];
		$_t = self::FindTag($_key, $this->conf['tag']['filter']);
		if ($_t) $_tag []= $_t;
		return array_unique($_tag);
	}	
	
	private function _readMsg($_is=array(),$_uid){
		if($_uid){
			if(!is_array($_is)){
				$_is=array($_is);
			}
			$_r=$this->mtfMysql->sql('s',$this->db['table_msg'],'i','WHERE i IN ('.implode(',',$_is).') AND vv=\'1\'');//只标记未读消息，不重复标记，减少总未读消息数量，不重复减少
			if($_r){
				$_is=array();
				foreach($_r as $_k=>$_v){
					$_is[]=$_v['i'];
				}
				if($_is){
					$this->mtfMysql->sql('u',$this->db['table_msg'],array('vv'=>''),'WHERE i IN ('.implode(',',$_is).') AND tt='.$_uid);
					$this->mtfMysql->sql('u',$this->db['table'],array('nmsg1'=>'///nmsg1-'.count($_is)),'WHERE i='.$_uid);
				}
			}
		}
	}
	
	private function _ui($_k,$_o=''){
		return $this->mtfUnit->str2num($_k.($_o?'_'.$_o:''));
	}
	
	private function _dn($_i,$_t=''){//t=1,强制返回绑定域名
		$_h=$this->conf['dn'][$_i];
		if($_SERVER['SERVER_NAME']===$_h){
			return $_t?$_h:false;
		}elseif($_h||$_SERVER['SERVER_NAME']===$this->conf['domain']['web']){
			return $_h;
		}else{
			return $this->conf['domain']['web'].'/'.$_i;
		}
	}
	
	private function _dn_404($_i){
		$_dn=$this->_dn($_i);//绑定域名
		if($_dn && $_dn!==$_SERVER['SERVER_NAME'] && $_dn!==$_SERVER['SERVER_NAME'].'/'.$_i){
			return $_dn;
		}
		return false;
	}

	private function _get_des($_content) {
		return strip_tags(explode('</p>', str_replace('<p></p>','', 
		         $this->mtfUnit->subStr($this->mtfUnit->clearSpace($_content), $this->conf['list']['max_text_length'], '……')
	         ))[0]);
	}
	
	public function mtfQueueList($_dat = array()){
		if (empty($_dat)) return;
		$_rrr = array();
		$_dat_ar = array();
		if(empty($_dat[0]) === false) $_dat_ar = $_dat;
		else $_dat_ar[0] = $_dat;
		$_var['reply_author'] = $_dat['reply_author']; // 是否作者回复可见
		$_var['mom'] = 0; // 是否需要读取文章父级
		$_var['task'] = 0; // 是否为完成任务送红包
		$_var['web'] = 0; // 是否为个人主页
		$_uid = $this->uid2id($_SERVER['HTTP_UID']);
		$_dn_id = array_flip($this->conf['dn'])[$_SERVER['SERVER_NAME']];
		if($_dn_id){
			if($_dat_ar[0]['k'] === 'index'){
				$_dat_ar = array(
					array('i' => $_dn_id, 'tpl' => 'content'),
					array(
						'id' => $_dn_id,
						'order' => $_dat_ar[1]['order'] === 't0 DESC' ? $_dat_ar[1]['order'] : 'my',
						'tpl' => 'list',
						'page' => $_dat_ar[0]['page'], 'query' => '', 'pi' => 0, 'index' => 1
					)
				);//index=1，强制子域名绑定主页，用域名，而不是域名+数字访问
			} elseif ($_dat_ar[0]['k']) {
				$_a['id'] = $_dn_id;
				$_dat_ar[0] = array_merge($_a, $_dat_ar[0]); //注意顺序
				$_dat_ar[0]['pi'] = 0;
			}
		}
		$bhtml = ''; // 底部html
		$_tdk = array();
		foreach($_dat_ar as $_data){
			if (empty($_data)) continue;
			$_rr = array();
			$_sql = array();
			$_sql_or = array();
			$_sql_v = array();
			$_order = array();
			$_total = '';
			$_limit = '';
			$_page = '';
			$_per = '';
			$_query = '';
			$_tpl = 'list';
			$_people = 0;
			$_sql_default_v = 'i,a,e,nz,k,o,r,m,d,v,tt,w,t0,ar,nm,q,p,nrel,t0,t1,url,ch,cs,cv';
			$_sql_default_table = 'table';
			$_ch = '';
			$_artists = array();
			$_parents = array();
			
			foreach($_data as $_k=>$_v){
				switch($_k){
					case 'id':
						$_arr = array();
						$_r = $this->mtfMysql->sql('s1', $this->db['table'], 'e,r,top,k,o', 'WHERE i=' . $_v);
						
						if (empty($_data['msg']) || $_data['msg'] !== '1') {
							//如果是作者内的标签
							if (empty($_data['k']) === false) $_key = $this->mtfAttr->parseA($_r['k'], '|');
							$_arr = array();
							if(empty($_r['r']) === false){
								$_arr = array_reverse(explode(',', $_r['r']));
								$_sql_v []= 'i IN (' . $_r['r'] . ') AS isbox';
								if (empty($_data['k']) === false) $_sql_v []= 'o=' . $_v . ' AS isbox';
							}
							if(empty($_r['top']) === false){
								$_arr = array_merge(array_reverse(explode(',', $_r['top'])), $_arr);
								$_sql_v []= 'i IN (' . $_r['top'] . ') AS istop';
							}
						}
						
						
						if($_r['e']==='people' && (isset($_data['msg']) === false || $_data['msg'] !== '0')){
							$_var['web']=1;
							if($_uid){//在其他人的主页读取消息
								$_limit=50;
								$__r=$this->mtfMysql->sql('s',$this->db['table_msg'],'f,v,i,vv,tt','WHERE g=2 AND (( f='.$_v.' AND tt='.$_uid.' ) '.(@$_data['sub']?'':' OR ( f='.$_uid.' AND tt='.$_v.' )').') ORDER BY vv DESC,t DESC LIMIT 0,'.$_limit);
								$_a=array();
								$_b=array();
								$_msg=array();
								$_msg_v=array();
								foreach($__r as $__k => $__v){
									$_a[]=$__v['v'];
									if($__v['f']===$_uid){
										$_b[]=$__v['v'];
									}elseif($__v['vv'] && $__v['tt']===$_uid){
										$_msg[$__v['v']]=$__v['i'];
										$_msg_v[]=$__v['v'];
										array_pop($_a);
									}
								}
								
								if($_a){
									if(@$_data['msg']==='1'||(@$_data['order']!=='my'||@$_data['sub']===2)||$_v==='0'){//主页状态，也读 已读消息
										
											$_index=count($_arr)-1;
											if($_arr){
												$__a=reset($_a);
												foreach($_arr as $_k=>$_v){
													if($_v<$__a){
														$_index=$_k;
														break;
													}
												}
											}
											array_splice($_arr,$_index,0,$_a);
									}
									
								}
								if($_msg_v){
									$_arr=array_merge($_msg_v,$_arr);
								}
								if($_b){
									$_sql_v[]='i IN ('.implode(',',$_b).') AS isme';
								}
							}
						}
						
						if(!@$_data['k'] && @$_data['sub'] && $_data['page']){
							$_ar=explode('_',$_data['page']);
							$_arr=array_slice($_arr,$_ar[0]*$_ar[1]-1,$_ar[1]);
							unset($_ar);
						}
						$_msg_unread=array();
						
						if($_uid && (!@$_data['sub']||@$_data['sub']===2) && $_msg && $_arr){
							foreach($_arr as $_k=>$_v){
								if(in_array($_v,$_msg_v)){
									$_msg_unread[]=$_msg[$_v];
								}
							}
							if($_msg_unread){
								$this->_readMsg($_msg_unread,$_uid);
							}
						}
						
						
						if($_arr){
							if(!@$_data['order']||$_data['order']==='my'){
								if($_r['e']==='mtfdat' && $_r['o']){//默认提前作者评论
									$_order[]='o='.$_r['o'].' DESC';
								}
								$_order[]='t1 IS NULL OR t1 > \''.date('Y-m-d').'\' DESC';//前：未读消息，置顶，后：过期消息，其余：按时间
								
								$__r=implode(',',$_arr);
								$_order[]='field(i,'.$__r.')';
								unset($__r);
							}
							
							$_sql[]='(i IN ('.implode(',',$_arr).')'.(@$_data['k']?' OR o='.$_v:'').')';
							
						}else{
							$_sql[]='i=-1';//无结果
						}
						
						if($_data['order']==='my' && !@$_data['sub']){//sub动态页查看
							if($this->_dn_404($_v)){
									return array('301'=>'https://'.$this->_dn_404($_v));//无Key返回301
							}elseif($this->conf['dn'][$_v]){
								if($_data['index']!==1){
									return array('301'=>'https://'.$this->conf['dn'][$_v]);//无Key返回301
								}	
							}
						}
						
						//$_data['debug']=1;
						unset($_arr);
					
						break;
					case 'r':
						$_sql[]='i IN ('.$_v.')';
						break;
					case 'm':
						if($_v==='!'){
							$_sql[]='m!=\'\'';
						}
						break;
					case 'k':
						$_a=array();
						$_limit=50;//只最近50条消息
						switch($_v){
							case 'fol':
								$__r=$this->mtfMysql->sql('s',$this->db['table_msg'],'f','WHERE g=1 AND tt='.$_uid.' ORDER BY i DESC LIMIT 0,'.$_limit);
								foreach($__r as $__k => $__v){
									$_a[]=$__v['f'];
								}
								
								$_ai=implode(',',$_a);	
								$_sql[]='i IN ('.$_ai.')';
								$_order[]='field(i,'.$_ai.')';
								
								$this->mtfMysql->sql('u',$this->db['table'],array('nfol1'=>0),'WHERE i='.$_uid);
								
								break;
							case 'to':
								$__r=$this->mtfMysql->sql('s',$this->db['table_msg'],'tt','WHERE g=1 AND f='.$_uid.' ORDER BY i DESC LIMIT 0,'.$_limit);
								foreach($__r as $__k => $__v){
									$_a[]=$__v['tt'];
								}
								
								$_ai=implode(',',$_a);	
								$_sql[]='i IN ('.$_ai.')';
								$_order[]='field(i,'.$_ai.')';
								
								$this->mtfMysql->sql('u',$this->db['table'],array('nfol1'=>0),'WHERE i='.$_uid);
								break;
							case 'zan':
								
								$_sql_default_v='i,\'mtfzan\' AS e,s,f,tt,v,vv,t,n,ip';
								$_sql_default_table='table_msg';
								
								$_sql[]='g=0 AND tt='.$_uid;
								
								$this->mtfMysql->sql('u',$this->db['table'],array('nz1'=>0),'WHERE i='.$_uid);
								
								$_tpl='mtfzan';
								
								$_order[]='i DESC ';
								
								$__r=$this->mtfMysql->sql('s1',$this->db['table'],'nz,nz0','WHERE i='.$_uid);
								if(@$__r['nz']){
									$_ch='累计 ♥ ： '.$__r['nz'].'　 可用 ♥ ： '.$__r['nz0'];
								}
								
								break;
							case 'block':
								
								$_sql_default_v='i,\'mtfzan\' AS e,s,f,tt,v,vv,t,n';
								$_sql_default_table='table_msg';
								
								$_sql[]='g=0 AND n<0';
								
								$_tpl='mtfzan';
								
								$_order[]='i DESC ';
								
								break;
							case 'rec':
								//$_sql[]='i IN (100131,100133,100135,100136,100137,100138,100314,100315,100717,100749,104500)';
								$_sql[]='o IN (100131, 100132, 100133, 100134, 100315)';
								$_sql[]='p < 999999';
								break;
							case 'msg':	
							case 'index':
								$_a=array();
								$_p=array();
								$_od=array();//提前未读消息
								if(in_array($_v,array('index'))){
									$_index=1;
									$_data['sub']='1';
								}
								
								if($_uid){
									//读取消息
									
									$__r=$this->mtfMysql->sql('s',$this->db['table_msg'],'f,i,v,vv','WHERE g=2 AND tt IN ('.$_uid.',0) ORDER BY vv DESC, i DESC LIMIT 0,'.$_limit);
									$this->_cache['nmsg1']=array();
									foreach($__r as $__k => $__v){
										if(!$__v['f']){
											$__v['f']=0;
										}
										$_a[]=$__v['f'];
										if($__v['vv']){
											if(@$this->_cache['nmsg1'][$__v['f']]){
												$this->_cache['nmsg1'][$__v['f']]+=$__v['vv'];
											}else{
												$this->_cache['nmsg1'][$__v['f']]=$__v['vv'];
											}

											$this->_cache['nmsg1'][$__v['v']]=$__v['i'];
											$_od[]=$__v['f'];
										}
										
									}
									if($_od){
										
										$_order[]='field(i,'.implode(',',$_od).') DESC';
									}
									$_a=array_unique($_a);
									if(count($_a)>=1){//如果存在消息
										$_var['mom']=1;//读取父级消息
									}
									if($_index===1){
										//读取关注的人
										$__r=$this->mtfAttr->sql('s1',$this->db['table'],'a','WHERE i='.$_uid,0,'|');
										if(@$__r['a']['关注']){
											$_p=$__r['a']['关注'];
											$_order[]='i IN('.implode(',',$_p).') DESC';//提前关注的人
										}
									}
								}
								
								if($_index===1){
									//如果关注的人为空，读取最近加入的人，不包括匿名
									$__r=$this->mtfMysql->sql('s',$this->db['table'],'i','WHERE e=\'people\' AND i!=0 AND trim(r)!=\'\' ORDER BY '.($_p?'i IN ('.implode(',',$_p).') DESC,':'').'t0 DESC LIMIT 0,'.$_limit);//跳过没有发布任何文章，或者发布文章被删除的人
									$_p=array();
									foreach($__r as $__k => $__v){
										$_p[]=$__v['i'];
									};
									$_data['msg']='2';//读取动态和消息
								}else{
									$_data['msg']=$_data['sub']='1';//读取消息
								}	
									
								$_a=array_merge($_a,$_p);
								$_ai=implode(',',$_a);
								unset($_a);
								unset($_p);
								$_sql[]='i IN ('.$_ai.')';
								$_order[]= $_index === 1 ? 't0 DESC' : 'field(i,' . $_ai . ')';	
								break;
							case 'rank':
								$__r=$this->mtfMysql->sql('s',$this->db['table_msg'],'SUM(n) as n,tt','WHERE g=0 AND n>0 AND t>\''.date('Y-m-d H:i:s',strtotime('-'.$this->conf['time']['rank'].' day')).'\' GROUP BY tt ORDER BY n DESC');//1月内
								foreach($__r as $__k => $__v){
									if($__v['tt']){//过滤系统和匿名
										$_a[]=$__v['tt'];
										$this->_cache['dashen'][$__v['tt']]=$__v['n'];
									}
								}
								
								$_ai=implode(',',$_a);	
								$_sql[]='i IN ('.$_ai.')';
								$_order[]='field(i,'.$_ai.')';
								
								break;
							case 'weal':
								$_sql[]='w!=\'\'';
								$_order[]='worder ASC,t DESC';//顺序越大，任务排序靠后，9为任务红包，''点击和关注为空
								break;
							case 'swap':
								$_sql[]='bz!=\'\' AND p<999999999';//排除文章内的回复
								$_order[]='t1 IS NULL OR t1>\''.date('Y-m-d').'\' DESC,t0 DESC';//顺序越大，任务排序靠后，9为任务红包，''点击和关注为空
								break;
							case 'my':
								if($_uid){
									$_sql[]='o='.$_uid;
									$_sql[]='msg=\'\'';//非管理员排除消息
									$_sql[]='e=\'mtfdat\'';
									$_order[]='t0 DESC';//顺序越大，任务排序靠后，9为任务红包，''点击和关注为空
									$_data['pi']=0;//不显示人
								}
								break;
							default:
								$_sql[]='e!=\'mtftag\'';
								if($_v){
									$_v = substr($_v, 0, 100); // 只取前100个字符，避免过多消耗CPU
									$_dn_id=array_flip($this->conf['dn'])[$_SERVER['SERVER_NAME']];
									$_id=$this->_ui($_v,$_dn_id);
									$_r=$this->mtfMysql->sql('s1',$this->db['table'],'i,top','WHERE i='.$_id);
									if(@$_r['top']){
										$_sql_v[]='i IN ('.$_r['top'].') AS istop';
										$_order[]='field(i,'.$_r['top'].') DESC';
									}
									if(stristr($_v,':')){
										$_a=explode(' ',$_v);
										foreach($_a as $__k=>$__v){
											$__a=explode('|',$__v);
											if(count($__a)===1){
												$_sql[]='FIND_IN_SET(\''.$__v.'\', k)';
											}else{
												$_or=array();
												foreach($__a as $___v){
													$_or[]='FIND_IN_SET(\''.$___v.'\', k)';
												}
												$_sql[]='('.implode(' OR ',$_or).')';
											}
										}
									}else{
										$_a=$this->mtfKeyword->get($_v);
										if($_a['do']==='301'){
											return array('301'=>'https://'.$_a['domain'].'/'.$_a['word']);
										}else{
											$_v=str_replace(' | ','|',implode(' ',$_a['word']));
											$_a=explode(' ',$_v);
											foreach($_a as $__k=>$__v){
												$__a=explode('|',$__v);
												if(count($__a)===1){
													$_sql[]='k LIKE \'%'.$__v.'%\'';
												}else{
													$_or=array();
													foreach($__a as $___k=>$___v){
														$_or[]='k LIKE \'%'.$___v.'%\'';
													}
													$_sql[]='('.implode(' OR ',$_or).')';
												}
											}
										}
									}
								}
						}
						break;
					case 'f':
						if($_v){
							$_v=json_decode($_v,true);
							$_v=array_slice($_v,0,6);//只存前6个元素
							if($_v&&is_array($_v)){
								foreach($_v as $__k=>$__v){
									if(@$__v['k']&&@$__v['i']&&is_numeric($__v['i'])){
										$__v['i']=mb_substr($__v['i'],0,30);//只保留前30个数字
										$__v['k']=mb_substr($__v['k'],0,30);//只保留前30个数字
										$_v[$__k]['i']=$__v['i'];
										$_v[$__k]['k']=$__v['k'];
									}
									if(stristr($__v['k'],':')){
										$_s[]='FIND_IN_SET(\''.$__v['k'].'\', k)';
									}else{
										$_s[]='k LIKE \'%'.$__v['k'].'%\'';
									}
								}
								$_sql[]='(e=\'mtfdat\' AND ('.implode(' OR ',$_s).'))';
								if($_uid){
									$this->mtfMysql->sql('u',$this->db['table'],array('fol'=>$this->mtfUnit->JsonEncodeCN($_v)),'WHERE i='.$_uid);
								}
								
							}
							if(@$_data['fav']){
								$_var['fav']=1;
							}
							unset($_v);
						}
						break;
					case 'e':
						if(substr($_v,0,1)==='!'){
							$_sql[]='e!=\''.substr($_v,1).'\'';
						}else{
							$_sql[]='e=\''.$_v.'\'';
						}
						break;
					case 'o':
						if($_v){
							if(!$_uid || !$this->isAdmin($_uid)){//非管理员排除消息
								$_sql[]='msg=\'\'';
							}
							settype($_v,'int');
							if($_v>0){
								$_sql[]='o='.$_v;
							}else{
								$_sql[]='o!='.abs($_v);
							}
						}else{
							$_sql[]='o=\'\'';	
						}
						break;                                         
					case 'i':
						$_total=count(explode(',',$_v));
						if(substr($_v,0,1)==='!'){
							$_sql[]='i NOT IN ('.substr($_v,1).')';
						}else{
							$_sql[]='i IN ('.$_v.')';
						}
						break;
					case 'msg':
						if($_v){
							//未读消息
							$this->_readMsg(explode(',',$_v),$_uid);
						}
						break;
					case 'i_e':
						$_a=explode(',',$_v);
						foreach($_a as $_k=>$_v){
							$_a[$_k]=$this->mtfCrypt->de($_v);	
						}
						$_total=count($_a);
						$_sql[]='i IN ('.implode(',',$_a).')';
						break;
					case 'order':
						if($_v!=='my'){
							$_v=str_replace('i IN top DESC,',@$_top?'i IN ('.$_top.') DESC,':'',$_v);
							if($_v==='randbynz'){
								$_v='i>99999999 DESC,nz>'.floor(substr(time(),-3)/5).' DESC,t0 DESC';
							}
							$_order[]=str_replace('asc',' ASC',str_replace('desc',' DESC',$_v));
						}
						break;
					case 'page':
						$_a=explode('_',$_v);
						$_page=$_a[0];
						$_per=$_a[1];
						$_limit=' LIMIT '.($_page-1)*$_per.','.$_per;
						break;
					case 'query':
						$_query=$_v;
						break;
					case 'tpl':
						$_tpl=$_v;
						break;
					case 'color':
						$_query .= '&color='.$_v;
						$_hsv = $this->conf['hsv'][$_v];
						if(empty($_hsv) === false){
							$_l = count($_hsv);
							for($__i =0; $__i < $_l; $__i += 2){
								switch ($_i){
									case 0:
										$_c = 'ch';
									break;
									case 2:
										$_c='cs';
									break;
									case 4:
										$_c='cv';
									break;
								}
								$_min = $_hsv[$__i];
								$_max = $_hsv[$__i + 1];
								if (is_array($_min)){
									$_s = array();
									foreach($_min as $__k => $__v){
										$_s []= $_c . '>' . $_min[$__k] . ' AND ' . $_c . '<' . $_max[$__k];
									}
									$_sql []= '(' . implode(' OR ', $_s) . ')';
								}else{
									array_push($_sql, $_c . '>' . $_min, $_c . '<' . $_max);
								}
							}
						}
						break;
					case 'h':
						$_hash=$this->mtfProxyCurl->hash($this->conf['domain']['cdn'],$_v);
						if($_hash && $_hash[$_v]['hash']){
							$_sql_v[]='BIT_COUNT(CAST( CONV( h, 2, 10 ) AS UNSIGNED ) ^ CAST( 0b'.$_hash[$_v]['hash'].' AS UNSIGNED )) as hd';
							$_order[]='hd ASC';
							$_sql[]='h!=\'\'';
							$_sql[]='(e=\'jpg\' OR e=\'gif\' OR e=\'png\' OR e=\'bmp\')';
						}
						$_total=50;//只显示前50个结果
						break;
					case 'nz':
						$_sql[]='nz'.$_v;
						break;
					case 'cid'://当前ID
						//红包
						$_r=$this->mtfMysql->sql('s1',$this->db['table'],'w,o,e','WHERE i='.$_v);
						if($_r['w']){
							$_ip=$this->mtfGuid->ip();
							$_fid=$_data['fid'];
							if($_r['e']==='people'){
								$_r['o']=$_v;
							}
							$_weal=$this->_weal('点击 红包',$_r['w'],$_r['o'],$_v,$_uid,$_ip,$_fid);
						}
						break;
					case 't0':
						$_sql[]='t0'.$_v;
						break;
					case 't1'://有效期内文章
						if($_v==='1'){
							$_v=date('Y-m-d');
						}
						$_sql[]='(t1 is NULL OR t1>\''.$_v.'\')';
						break;
					case 'a':
						switch($_v){
							case 'pm':
								$_rr=array('n'=>'','n1'=>'','nz'=>'');
								$__r=$this->mtfMysql->sql('s1',$this->db['table'],'nz0,nz1,nfol1,nmsg1','WHERE i='.$_data['i']);
								if($__r){
									$_n=$__r['nz1']+$__r['nfol1'];
									$_rr['n']=$_n;
									$_rr['n1']=floor($__r['nmsg1']);
									$_rr['nz']=floor($__r['nz0']);
								}
								exit(json_encode($_rr));
							case 'sign':
								$_uid=$_data['uid'];$_step=$_data['step'];
								settype($_step,'int');
								$_uid=$this->uid2id($_data['uid']);
								if($_step===0){
									$_r=$this->mtfMysql->sql('s1',$this->db['table_msg'],'i','WHERE g=0 AND t>=\''.date('Y-m-d').'\' AND s=\'签到 ♥\' AND tt=\''.$_uid.'\'');
									if(@$_r['i']){
										$_sign=1;
									}else{
										$_sign=0;
									}
									exit(json_encode(array('step'=>$_step,'sign'=>$_sign)));
								}elseif($_step===1){
									$_r=$this->mtfMysql->sql('s',$this->db['table_msg'],'i,n','WHERE g=0 AND t>=\''.date('Y-m-d',strtotime("-3 day")).'\' AND s=\'签到 ♥\' AND tt=\''.$_uid.'\' ORDER BY i DESC');
									$_day=1;
									foreach($_r as $_k=>$_v){
										if($_v['n']==='6'){
											break;
										}
										$_day+=1;
									}
									if($_day==3){
										$_zan=6;
									}else{
										$_zan=2;
									}
									$_ip=$this->mtfGuid->ip();
									$this->fen('zan','签到 ♥','',$_uid,'',$_zan,$_ip,'',1,date('Ymd'));
									exit(json_encode(array('step'=>$_step,'day'=>$_day,'zan'=>$_zan,'i'=>$_uid)));
								}
							break;
						}
				}
			}
			
			$_orders = $_order ? ' ORDER BY '. implode(',', $_order) : '';
			
			$__sql = ($_sql || $_sql_or ? 'WHERE ' : '') . implode(' AND ',$_sql) . 
							 ($_sql_or ? ($_sql ? ' OR ' : '' ) . implode(' OR ', $_sql_or) : '');
			
			if ($__sql || $_sql_v) {// 计算汉明距离时，$__sql 为空，而 $_sql_v 存在
				if ($_total === '' && $_query !== 'no') {// no为不需要显示页码
					$__sql_v = 'count(1) as total';
					$_r = $this->mtfMysql->sql('s1', $this->db[$_sql_default_table], $__sql_v, $__sql); //算总数，不需要排序
					$_total = $_r['total'];
				}
				$__sql_v = $_sql_default_v . ($_sql_v ? ',' . implode(',', $_sql_v) : '');
				$_r = $this->mtfMysql->sql('s', $this->db[$_sql_default_table], $__sql_v, $__sql . $_orders . $_limit, $_data['debug']);
			}else{
				$_r = '';
			}
			
			if ($_r) {
				//获取TDK
				//非弹幕，非列表第一个，非人，非下级，非父级
				if(empty($_tdk) && empty($_dat['dm']) && empty($_dat['sub']) && empty($_dat['mom'])){
					if(empty($_data['k']) === false){
						if(empty($_data['id']) === false){
							$_tdk['people'] = $_data['id'];
							$_dn = $this->_dn($_tdk['people'], 1);//绑定域名
							if ($_dn) $_tdk['dn'] = $_dn;
							if (empty($_key['标题']) === false) {
							 $_dn_t = $_key['标题'][0];
							 unset($_key['标题'][0]);
							}
							$_tdk['t'] = $_data['k'] . '_' . $_dn_t;
							$_i = $_tag_i = $this->_ui($_data['k']);
						}else{
							$_tdk['t'] = $this->_k2ch($_data['k'], $_ch);
							if ($_tdk['t'] !== $_data['k']) {
								$_tdk['t'] = $_data['k'] === 'index' ? $_tdk['t'] : $this->_mtflang2span($_tdk['t']);
							}
							$_i = $_tag_i = $this->_ui($_data['k']);
						}
						$_tdk['tag'] = 1;
					} elseif (empty($_data['i']) === false) {
						$_tdk['t'] = $_i = $_data['i'];
						if(strlen($_data['i']) < 18){ //如果是会员首页
							$_tdk['none']='1';
						}else{
							$_tdk['z'] = $this->n2date($_i);
						}
					}
					if ($_tag_i) {//标签
						$__r = $this->mtfMysql->sql('s1', $this->db['table'], 'k', 'WHERE i=' . $_tag_i);
						if($__r){
							if(empty($_key)) $_key = array();
							elseif (empty($_key['描述']) === false) $_des = $_key['描述'][0];
							$_key = array_merge($_key, $this->mtfAttr->parseA($__r['k'], '|'));
							if (isset($_des)) $_key['描述'][0] = preg_replace('/.+/', $_key['描述'][0], $_des, 1);
						}
						unset($_tag_i);
					}else{
						$_tdk['t'] = $_data['i'];
						if (empty($_r[0]['k']) === false) $_key = $this->mtfAttr->parseA($_r[0]['k'],'|');
					}
					if(empty($_key) === false){
						if(empty($_key['标题']) === false){
							$_tdk['t'] = $_key['标题'][0];
							if (empty($_dn_t) === false) $_tdk['t'] .= '_' . $_dn_t;
						}
						if(empty($_key['描述']) === false){
							$_a = $this->mtfBBcode->parse(strtr($_key['描述'][0], array('：//'=>'://')), array('type'=>'tdk'));
							$_tdk['des'] = preg_replace('/^\n|\n$/', '', $_a['d']);
							if ($_a['d'] !== $_a['s']) $_tdk['d'] = str_replace($_a['d'], '', $_a['s']);
						}
						$_key2 = $_key;
						unset($_key2['标题']);
						unset($_key2['描述']);
						if(empty($_key2) === false){
							$_tag = $this->mtfAttr->parseK(self::SortTag($_key2, $this->conf['tag']['filter']));
							if ($_tag) $_tdk['k'] = explode(',', str_replace('标签:', '', implode(',', $_tag)));
						}
					}
					if(empty($_tdk) === false){
						$_tdk['type'] = $this->conf['ext2type'][$_r[0]['e']];
						if($_tpl === 'content' ) $this->_cache['tdk'][$_i] = $_tdk;
						$_rrr = $this->_jsonOrder($_rrr, $_i, array('tdk' => $_tdk));
					}
				}
				
				if($_tpl === 'data' || $_tpl === 'list' || $_tpl === 'dm' || $_tpl === 'mtfzan'){
					//建立作者和作者粉丝缓存，父级缓存
					$_artists_tmp=array();
					$_parents_tmp=array();
					$_artists_diff=array();
					$_parents_diff=array();
					$_as=array();
					
					
					if($_tpl === 'mtfzan'){
						foreach($_r as $_k=>$_v)
						{
							if($_v['f']){
								$_artists_tmp[]=$_v['f'];
							}
							if($_v['v'] && strlen($_v['v'])===18){
								$_parents_tmp[]=$_v['v'];
							}
						}
					}else{
						foreach($_r as $_k=>$_v)
						{
							if($_v['e']==='people'){
								$_artists_tmp[]=$_v['i'];
							}elseif($_v['o']){
								$_artists_tmp[]=$_v['o'];
							}
							
							if($_v['e']==='mtfdat'){
								$_as[]=$_v['i'];
							}
							
							if(@$_v['p'] && strlen($_v['p'])===18){//除去人，只保留文章
								$_parents_tmp[]=$_v['p'];
							}
						}
					}
					$_artists_tmp=array_unique($_artists_tmp);
					$_artists_diff=array_diff($_artists_tmp,$_artists);
					
					$_parents_tmp=array_unique($_parents_tmp);
					$_parents_diff=array_diff($_parents_tmp,$_parents);
					
					if($_artists_diff){
						$_artists=array_merge($_artists,$_artists_diff);
						//$this->_set_people_cache('i IN('.implode(',',$_artists_diff).')',$_uid);
						
					
					}
					//赞缓存
					$_zan_fs=array();
					
					if($_tpl === 'data'){
						
						if($_as){
							$__r=$this->mtfMysql->sql('s',$this->db['table_msg'],'f,n,v,vv','WHERE g=0 AND s=\'送 ♥\' AND n>0 AND  v IN ('.implode(',',$_as).') ORDER BY i DESC LIMIT 0,30');
							foreach($__r as $__k=>$__v)
							{
								$_zan_fs[]=$__v['f'];
								if(!$this->_cache['zan'][$__v['v']]){
									$this->_cache['zan'][$__v['v']]=array();
								}
								$this->_cache['zan'][$__v['v']][]=array('p'=>$this->_get_people($__v['f'],'info',$_uid),'n'=>$__v['n'],'vv'=>$__v['vv']);
							}
							unset($__r);
						}
						
					}
					
					if($_zan_fs){
						$_zan_fs=array_unique($_zan_fs);
						$_arts=array_merge($_zan_fs,$_artists_diff);
						unset($_zan_fs);
					}else{
						$_arts=$_artists_diff;
					}
					if($_arts){
						$this->_set_people_cache('i IN('.implode(',',$_arts).')',$_uid);
					}
					
					
					if($_tpl === 'list'||$_tpl === 'mtfzan'){
						if($_parents_diff){
							$_parents=array_merge($_parents,$_parents_diff);
							$__r=$this->mtfQueueList(array('mom'=>1,'i'=>implode(',',$_parents_diff),'e'=>'!people'));
							if(@$__r[0]){
								foreach($__r as $__k=>$__v)
								{
									foreach($__v as $_k=>$_v)
									{
										$this->_cache['list'][$_k]=$_v;
									}
								}
							}
						}
					}
				}
				foreach($_r as $_k=>$_v) {	
					if(!$_v['nrel'] && $_v['ar']){//无转发时，权限判定，如果无权限，直接略过
						$_auth=explode(',',$_v['ar']);
						if(!in_array($_uid,$_auth)&&!$this->isAdmin($_uid)){
							continue;
						}
					}
					$_attr=array();$_key=array();
					$_t=$this->conf['ext2type'][$_v['e']];
					if($_v['a']){
						$_attr=$this->mtfAttr->parseA($_v['a'],'|');
					}
					if($_v['k']){
						$_key=$this->mtfAttr->parseA($_v['k'],'|');
					}
					//区分非人物中的消息、文章
					if($_tpl === 'list' && $_t !== 'people'){
						if(@$_dat['sub']){
							$_class='sub';
						}elseif(@$_dat['mom']){
							$_class='';
							//$_rr[$_v['i']]['list']['mom']=1;//避免列表中音频与内容中的音频重复，播放列表自动切换
						}elseif($_var['web']===1){
							if(@$_v['istop']){
								$_class='top';
							}elseif(@$_v['isbox']){
								// $_class='box'; // mtf-box 已移除，默认没有样式
								if(!isset($_data['pi'])||@$_var['pi_isset']===1){//如果未定义，再定义，优先考虑自定义
									if(@$_data['id']===$_v['o']){//自己的文章，不显示pifno
										$_data['pi']=0;
									}else{
										$_data['pi']=1;
									}
									$_var['pi_isset']=1;
								}
							}else{
								$_class=@$_v['isme']?'rtl':'ltr';
							}
						}
						$_rr[$_v['i']]['list']['c']=$_class;
						unset($_class);
					}
					if($_t==='image'||$_t==='video'||$_t==='audio'||$_t==='zip'||$_t==='sub'||$_t==='doc'||$_t==='bt'||$_t==='txt'||$_t==='rom'){
						if($_tpl==='data'){
							$_rr[$_v['i']]=$this->_get_data($_v, $_attr);
						}elseif($_tpl==='list'){
							$_p = array('i'=>$_v['i']) + $this->_isDownGifExt($_v['e'], $_t);
							if (empty($_v['url']) === false) $_p['u'] = $_v['url'];
							list($_p['width'], $_p['height']) = $this->_getWH(isset($_attr['宽度']) ? $_attr['宽度'][0] : null, isset($_attr['高度']) ? $_attr['高度'][0] : null);
							list($_p['width'], $_p['height']) = $this->_getWHfromE($_p['width'], $_p['height'], $_v['e']);
							$_rr[$_v['i']]['list']['p'] []= $_p;
							$_rr[$_v['i']]['list']['type'] = $_t;
							
							//List强制排序
							$_rrr=$this->_jsonOrder($_rrr, $_v['i'], $_rr[$_v['i']]);
							unset($_rr[$_v['i']]);
						}else{
							$_h=array();
							if($_t==='image'){
								$_h['img']=$this->_isDownGifExt($_v['e'],$_t);
								if(@$_key['标题'][0]){
									$_h['img']['alt']=$this->mtfUnit->clearEmoji($_key['标题'][0]);
								}
								if($_v['url']){
									$_h['img']['u']=$_v['url'];
								}
								list($_h['img']['width'], $_h['img']['height']) = $this->_getWH($_attr['宽度'][0], $_attr['高度'][0]);
								if (is_numeric($_v['ch']) && is_numeric($_v['cs']) && is_numeric($_v['cv'])) {
									$_color = $this->mtfColor->hsv2rgb(array('h'=>$_v['ch'],'s'=>$_v['cs'],'v'=>$_v['cv']));
									$_h['img']['cr'] = $_color['r'];
									$_h['img']['cg'] = $_color['g'];
									$_h['img']['cb'] = $_color['b'];
								}
							}elseif($_t==='video'||$_t==='audio'){
								$_h[$_t]=$this->_getMedia($_v['i'],$_t,$_attr);
							}else{
								$_h['img']=$this->_isDownGifExt($_v['e'],$_t,$_v['i']);
							}
							$_rrr=$this->_jsonOrder($_rrr,$_v['i'],$_h);
							
							$_a=explode(',',$_v['q']);
							$_l=$this->mtfQueueList(array('mom'=>1,'i'=>$_a[0],'e'=>'!people'));
							
							if(@$_l[0][$_a[0]]){
								$_rrr=$this->_jsonOrder($_rrr,$_a[0],array('list'=>array('c'=>'mom','quota'=>$_l[0][$_a[0]]['list'])));//读取父级文章
							}
							unset($_a);
							unset($_l);
							
							$_rrr=$this->_jsonOrder($_rrr,$_v['i'],array('people'=>$this->_get_people($_v['o'],'card',$_uid,$_v['nm'])));//保持人在文章后的顺序
						}
					}elseif($_t==='url'){
						if($_tpl==='data'){
							$_rr[$_v['i']]=$this->_get_data($_v,$_attr);
						}elseif($_tpl==='list'){
							$_rr[$_v['i']]['list']['url'][]=array('i'=>$_v['i'],'u'=>$this->getContent($this->dir['file'].$this->n2dir($_v['i']).$_v['i'].'.'.$_v['e'],'view'));
							$_rr[$_v['i']]['list']['type']=$_t;
							
							//List强制排序
							$_rrr=$this->_jsonOrder($_rrr,$_v['i'],$_rr[$_v['i']]);
							unset($_rr[$_v['i']]);
						}else{
							$_u = $this->getContent($this->dir['file'].$this->n2dir($_v['i']).$_v['i'].'.'.$_v['e'], 'view');
							$_video = false;
							if (strrchr($_u, '.') === '.m3u8') {
								$_video = true;
							}
							$_rr[$_v['i']]['url'] = array('u'=>$_u, 'jump'=>$this->mtfCrypt->en($_u,substr(time(),0,7)), 'video'=>$_video);//直接打开URL跳转
						}
					} elseif ($_t === 'mtfdat') {
						$_p = $this->dir['file'] . $this->n2dir($_v['i']) . $_v['i'] . '.' . $_v['e'];
						if($_tpl === 'data') $_rr[$_v['i']] = $this->_get_data($_v, $_attr);
						elseif ($_tpl === 'list'){
							if(empty($_var['reply_author']) === false){
								$_rr[$_k] = array('tpl' => $this->_mtflang2span('作者 可见')); //加密内容链接
								unset($_rr[$_v['i']]);
							}else{		
								$_rr[$_v['i']] = array_merge_recursive($_rr[$_v['i']],
								  $this->getContent($_p, 'list', array(
										'pi' => $_data['pi'], 'o' => $_v['o'], 'nm' => $_v['nm'], 
										'title' => empty($_key['标题']) ? null: $_key['标题'][0],
										'des' => empty($_key['描述']) ? null: $_key['描述'][0],
										'tag' => $this->_getTag($_key), 
										'domain' => empty($_attr['域名']) ? null : $_attr['域名'][0]
									)
								));
								if(empty($_data['mom']) === false && empty($_v['p']) === false && strlen($_v['p']) === 18){ // 读取父级文章
									$_rr[$_v['i']]['list']['quota'] = $this->_cache['list'][$_v['p']]['list'];
								}
							}
							
							if (empty($_var['fav']) === false) $_rr[$_v['i']]['list']['fav'] = 1;
							$_rr[$_v['i']]['list']['t0'] = $_v['t0'];
							if (empty($this->_cache['nmsg1'][$_v['i']]) === false){
								$_rr[$_v['i']]['list']['msg'] = $this->_cache['nmsg1'][$_v['i']];
							}
							
							if(isset($_dat['mom']) && $_dat['mom'] > 1){
								if(empty($_v['r']) === false){
									$__r = explode(',', $_v['r']);
									$__i = array_search($_dat['mom'], $__r);
									$__n = count($__r);
									$_rr[$_v['i']]['list']['pn'] = array(
										'p' => $__i - 1 >= 0 ? $__r[$__i - 1] : null,
										'n' => $__i + 1 < $__n ? $__r[$__i + 1] : null
									);
								}
							}
							// 懒加载
							if ($_k < 2) $_rr[$_v['i']]['list']['l'] = 1;
							//List强制排序
							if (empty($_rr[$_v['i']]) === false) {
								$_rrr = $this->_jsonOrder($_rrr, $_v['i'], $_rr[$_v['i']]);
							}
							unset($_rr[$_v['i']]);
						}else{
							if(empty($_data['dm']) === false){// 与$_dat['dm']不同
								if(empty($_var['reply_author'])){
									$_dt = $_v['tt'] ? $_v['tt'] : ($_v['d'] ? $_v['d'] : -1);
									if ($_dt !== -1) { // 过滤没有 时间 和 位置 的弹幕	
										$_tmp = array(
											'm' => $_v['m'],
											'd' => array(
												$_v['i'] => $this->getContent($_p,'list', array(
														'o' => $_v['o'], 'dm' => 1, 
														'title' => empty($_key['标题']) ? null : $_key['标题'][0],
														'des'=> empty($_key['描述']) ? null : $_key['描述'][0]
													)
												)
											)
										);
										if ($_v['v']) $_tmp['v'] = $_v['v'];
										if ($_v['o']) $_tmp += array('o' => $_v['o'],'nm' => @$this->_cache['list'][$_v['o']]['k']['标题'][0]);
										else $tmp['nm'] = $_v['nm'];
										if (isset($_rrr[$_dt]) === false) $_rrr[$_dt] = array($_tmp);
										else $_rrr[$_dt] []= $_tmp;
									}
								}
							}else{
								if(isset($_attr['作者可见']) && $_attr['作者可见'][0]){
									if($_uid !== $_v['o']) $_var['reply_author'] = 1; // 回复仅作者可见
								}
								
								$_c = $this->getContent($_p, 'mustache');
								$_rrr = $this->_jsonOrder($_rrr, $_v['i'], $_c);
								if (empty($_rrr[0][$_i]['tdk']['des'])) {
									$_rrr[0][$_i]['tdk']['des'] = $this->_get_des(explode("\n", $_c['tpl'])[0]);
								}
								if (empty($_v['p']) === false && strlen($_v['p']) === 18) {
									$_l = $this->mtfQueueList(array('mom' => $_v['i'], 'i' => $_v['p'], 'e' => '!people'));
									if (empty($_l[0][$_v['p']]) === false) {
										$_rrr = $this->_jsonOrder($_rrr, $_v['p'], array('list'=>array('c' => 'mom', 'quota' => $_l[0][$_v['p']]['list']))); // 读取父级文章
									}
									unset($_l);
								}
								
								if ($_v['o']) {
									$_tags = array();
									foreach($_key2 as $__k=>$__v){
										if(in_array($__k, $this->conf['tag']['rec'])){
											foreach($__v as $___v){
												$_tags[] = $__k.':'.$___v;
											}
										}
									}
									if ($_tags) {
										$_relate = $this->mtfQueueList(array('id'=>$_v['o'],'k'=>implode('|',$_tags),'e'=>'mtfdat','msg'=>'0','page'=>'1_3','order'=>'i DESC','i'=>'!'.$_v['i'],'t0'=>'<\''.date('Y-m-d H:i:s',strtotime($_v['t0'].' +'.$this->conf['time']['author'].' day')).'\''));//在作者更新的文章中排除当前文章以及消息
										array_shift($_relate); // 删除tdk
									}
								}
								$_dn_o = isset($_attr['域名']) ? $_attr['域名'][0] : $_v['o'];	
								if ($this->_dn_404($_dn_o)) {
									$_dn_ar = explode('/', $this->_dn_404($_dn_o));
									return array('301'=>'https://' . $_dn_ar[0] . '/' . $_v['i']); // 无Key返回301
								}
								$_people = $this->_get_people($_v['o'], 'card', $_uid, $_v['nm']);
								if(empty($_people['title']) === false){
									$_rrr[0][$_i]['tdk']['t1'] = '_'.$_people['title'];
								}
								$_rrr = $this->_jsonOrder($_rrr, $_v['i'], array('people' => $_people));//保持人在文章后的顺序	
							}
						}
					}elseif($_t==='people'){
						if($_tpl==='data'){
							$_rr[$_v['i']]=$this->_get_data($_v,$_attr);
						}else{
							$_a=$this->_get_people($_v['i'],'card',$_uid,$_v['nm']);
							if(@$_data['sub']){
								if(@$_data['sub']==='1'){//与$_dat['sub']不同
									$_sub=$this->mtfQueueList(array('sub'=>2,'mom'=>$_var['mom'],'id'=>$_v['i'],'page'=>'1_1','order'=>'my','msg'=>@$_data['msg']));
								}
								//动态页面，去掉底部HTML和友情链接
								unset($_a['b']);
								unset($_a['count']);
								unset($_a['des']);
								unset($_a['tag']);
							}elseif(@$_dat_ar[1]['order']==='my' && @$_a['b']){//只有主页显示友情链接
								$_tmp = explode('_', $_dat_ar[1]['page']);
								if ($_tmp[0] === '1') $bhtml.=$_a['b'];
								unset($_a['b']);
								unset($_tmp);
							}
							
							$_rrr=$this->_jsonOrder($_rrr,$_v['i'],array('people'=>$_a+(@$_data['sub']?array('sub'=>1):array())+(@$_dat['sub']===2?array('subp'=>1):array())));//如果要显示子文章，则sub=1，不显示 ♥/粉丝/关注
							if(@$_sub[0]){//如果有子文章
								$_rrr=$this->_jsonOrder($_rrr,$_v['i'],array('sub'=>$_sub[0]));
								unset($_sub);
							}
						}
					}elseif($_t==='mtfzan'){
						//s,f,tt,v,vv,t
						if($_v['f']){
							$_f=$_v['f'];
							$_fn=$this->_cache['list'][$_f]['k']['标题'][0];
							if(!$_fn){
								$_fn=$_f;
							}
						}else{
							$_f='';
							$_fn=preg_replace('/(\d+)\.(\d+)\.(\d+)\.(\d+)/is',"$1.$2.*.*",$_v['ip']);
						}
						if($_v['v']){
							$_vn=strip_tags($this->_cache['list'][$_v['v']]['list']['n']);	
						}
						
						if(!$_vn){
							$_vn=$_v['v'];
						}
						
						$_rrr=$this->_jsonOrder($_rrr,$_v['i'],array('mtfzan'=>array('s'=>$_v['s'],'f'=>$_f,'fn'=>$_fn,'v'=>$_v['v'],'vn'=>$_vn,'vv'=>$_v['vv'],'t'=>$_v['t'],'n'=>$_v['n'])));
						
					}elseif($_t==='mtftag'){
						if($_tpl==='data'){
							$_rr[$_v['i']]=$this->_get_data($_v,$_attr);
						}else{
							$this->mtfAttr->parseA($_v['k']);
							$_rr[$_v['i']]['list']['type']=$_t;
						}
					}	
					unset($_key);//避免标题重复
				}
				
				if ($_rr) $_rrr[] = $_rr;
				
				//弹幕不显示页数
				if(empty($_dat['dm'])){
					$_pages = $this->mtfQueuePage($_total, $_per, $_page);
					if ($_pages) {
						if($_page > 1 && $_rrr[0]){
							foreach($_rrr[0] as $_k => $_v){
								if (isset($_rrr[0][$_k]['tdk'])) $_rrr[0][$_k]['tdk']['t'] .= '_' . $_page;
								break;
							}
						}
						$_rrr[] = array('page' => array('pages' => $_pages, 'query' => $_query));
					}
				}
			} else {
				if ($_data['people'] === '1') $_rrr[] = array(
					array('tpl' => $this->_mtflang2span('这是 你 的 网站 ， 网址 是') . '：<input value="' . $_SERVER['SERVER_NAME'] . '/' . $_data['id'] . '" /><br>' . $this->_mtflang2span('设分类 ： 点 昵称 按钮，点 标签，选 标签，输入 任意字符') . '<br>' . $this->_mtflang2span('加内容 ： 点 发帖') . '<br>' . $this->_mtflang2span('求关注 ： 点 推 按钮'))
				);
			}
		}
		if ($_tpl === 'data') {
			//未读消息
			if ($_uid) {
				$__r = $this->mtfMysql->sql('s1', $this->db['table'], 'nz0,nz1,nfol1,nmsg1', 'WHERE i='.$_uid);
				if ($__r) {
					$_n = $__r['nz1'] + $__r['nfol1'];
					$_rr['n'] = $_n;
					$_rr['n1'] = floor($__r['nmsg1']);
					$_rr['nz'] = floor($__r['nz0']);
				}
			}
			if (empty($_weal) === false) $_rr['weal']=$_weal;
			return $_rr;
		} else {
			if(empty($_relate) === false) $_rrr = array_merge($_rrr, $_relate);
			if ($bhtml) $_rrr []= array(array('tpl' => $bhtml));
			return $_rrr;
		}
	}
	
	public function mtfQueueStatus($_f_bn) {	
		$_s = json_decode($this->mtfQueue->urlStatus($_f_bn), true);
		return empty($_s) ? false : $_s['g'] . ' ' . $_s['s'];
	}
		
	public function idWaterMark($_d_p, $_w = 1280) {
		include_once __DIR__ . '/../Grafika/autoload.php';
		$_editor = Grafika\Grafika::createEditor();
		$_p1 = Grafika\Grafika::createImage($_d_p);
		$_p2 = Grafika\Grafika::createImage(__DIR__ . '/pic/IDWaterMark.png');
		$_editor->resizeExactWidth($_p1, $_w);
		$_editor->blend($_p1, $_p2, 'overlay', 0.5, 'center');
		$_editor->save($_p1, $_d_p);
	}
	
	public function mtfQueueWork($_i, $_data, $_g) {
		$_f = array();
		$_d = array();
		if($_g === '移动'){
			foreach($_data as $_f_p){
				$_f = $this->pathInfo($_f_p);
				$_f['d'] = $this->dir['tmp'] . $_f['mid'];
				$_f['p'] = $_f['d'] . '/' . $_f['id'] . '.' . $_f['e'];

				$_f['dir'] = $this->e2dir($_f['e']) . $_f['mid'] . '/';
				if (is_dir($_f['dir']) === false) mkdir($_f['dir']);
				$_d['p'] = $_f['dir'] . $_f['id'] . '.' . $_f['e'];

				$_f['dir_file'] = $this->dir['file'] . $_f['mid'] . '/';
				if (is_dir($_f['dir_file']) === false) mkdir($_f['dir_file']);
				$_d_file = $_f['dir_file'] . $_f['id'] . '.';

				if ($_f['t'] === 'video' || $_f['t'] === 'audio'){
					copy($_f['p'], $_d['p']); //复制：视频·音频
				} else {
					rename($_f['p'], $_d['p']); //移动
				}
				$_hsv = array();
				if ($_f['t'] === 'image'){
					$_c = $this->mtfColor->rec($_d['p']);
					$_hsv = array('ch' => intval($_c['h']), 'cv' => intval($_c['v']), 'cs' => intval($_c['s']));
					$qrcode = $this->mtfCode->deQRcode($_d['p']);//扫描二维码·链接
					if($qrcode && is_numeric($qrcode) === false){//过滤黑白漫画中的二维码·数字
						$this->mtfQueue->urlAdd(array(
							'class' => 'mtfMysql', 'sql' => array('u', array('url' => $qrcode), 'WHERE i=' . $_f['id'])
						), '数据', $this->conf['domain']['dat'], 60);
					}
					$_attr = array();
					list($_attr['宽度'], $_attr['高度']) = getimagesize($_d['p']);
					$this->mtfQueue->urlAdd(array(
						'class' => 'mtfAttr', 'sql' => array('i1', array('k' => $this->tags($_d['p']), 'a' => $_attr), 'WHERE i='.$_f['id'])
					), '数据', $this->conf['domain']['dat'], 60);
				} elseif ($_f['t'] === 'video' || $_f['t'] === 'audio') {
					if($_f['t'] === 'video'){
						rename($_f['d'] . '/' . $_f['id'] . '.jpg', $_d_file . 'jpg'); // 缩略图
						rename($_f['d'] . '/' . $_f['id'] . '.' . $this->conf['preview'][$_f['t']]['ext'], $_d_file . $this->conf['preview'][$_f['t']]['ext']);
						$_c = $this->mtfColor->rec($_d_file . 'jpg');
						$_hsv = array('ch' => intval($_c['h']), 'cv' => intval($_c['v']), 'cs' => intval($_c['s']));
					}else{
						rename($_f['d'] . '/' . $_f['id'] . '.' . $this->conf['preview'][$_f['t']]['ext'], $_d_file . $this->conf['preview'][$_f['t']]['ext']);
					}
					foreach ($this->conf['convert'][$_f['t']] as $__v) {
						$_d['convert'] = array('ext' => $__v['ext'], 'b' => $__v['b']);
						if (empty($__v['w']) === false) $_d['convert']['w'] = $__v['w'];
						if (empty($__v['force']) === false) $_d['convert']['force'] = $__v['force'];
						$_d['id'] = $_f['id'];
						$_d['t'] = $_f['t'];
						$this->mtfQueue->urlAdd(array_merge($_d, array('p' => $_f['p'])), '转码', $this->conf['domain']['up'], 600);
					}
				} elseif ($_f['t'] === 'doc') {
					rename($_f['d'] . '/' . $_f['id'] . '.' . $this->conf['preview'][$_f['t']]['ext'], $_d_file . $this->conf['preview'][$_f['t']]['ext']); // 缩略图
					foreach($this->conf['convert'][$_f['t']] as $__v){
						$_d['convert'] = array('ext' => $__v['ext']);
						$_d['id'] = $_f['id'];
						$_d['t'] = $_f['t'];
						$this->mtfQueue->urlAdd($_d, '转码', $this->conf['domain']['up'], 600);
					}
				} elseif ($_f['t'] === 'sub') {
					$_lang = $this->mtfApiLanguageDetector->detect($this->mtfSub->convert(file_get_contents($_d['p']), 'txt'));
					$this->mtfQueue->urlAdd(array(
						'class' => 'mtfAttr', 'sql' => array('u1', array('a' => array('字幕语种' => $_lang)
					), 'WHERE i=' . $_f['id']), 0, '|'), '数据', $this->conf['domain']['dat'], 60);
				}else{
					rename($_f['d'] . '/' . $_f['id'] . '.' . $this->conf['preview'][$_f['t']]['ext'], $_d_file . $this->conf['preview'][$_f['t']]['ext']); // 缩略图
				}
				// 重复将 hash 入库
				$_h = $this->hashhm(array('p' => $_d['p']), 1);
				$_hsv += array('h' => $_h['hash'], 'hm' => $_h['hm']);
				$this->mtfQueue->urlAdd(array(
					'class' => 'mtfMysql', 'sql' => array('u', $_hsv, 'WHERE i=' . $_f['id'])
				), '数据', $this->conf['domain']['dat'], 60);
			}
		} elseif ($_g === '转码') {
			$_f['p'] = $_data['p'];
			$_f['convert'] = $_data['convert'];
			$_f['n'] = $_f['id'] = $_data['id'];
			$_f['t'] = $_data['t'];
			$_convert = true;
			if ($_f['t'] === 'video') {
				$_f['n'] .= '_c_b_' . $_f['convert']['b'] . '_w_' . $_f['convert']['w'] . '.' . $_f['convert']['ext'];
			} elseif ($_f['t'] === 'audio') {
				$_f['n'] .= '_c_b_' . $_f['convert']['b'] . '.' . $_f['convert']['ext'];
			} elseif($_f['t'] === 'doc') {
				if ($_f['e'] === $_f['convert']['ext']) $_convert = false;
				else $_f['n'] .= '.' . $_f['convert']['ext'];	
			}
			$_d['p'] = $this->dir['tmp'] . $this->n2dir($_f['n']) . $_f['n'];
			$_d['n'] = $_f['n'];
			if ($_convert) {
				$_r = $this->convert($_f['p'], $_d['p'], $_i, $_f['convert']['force']);
				if ($_r) {
					rename($_d['p'], $this->e2dir($_f['convert']['ext']) . $this->n2dir($_f['n']) . $_f['n']); // 先转码到临时文件夹，再移动文件，触发同步，避免同步不完整
					if($_f['t'] === 'video' || $_f['t'] === 'audio'){
						$this->mtfQueue->urlAdd(array(
							'class' => 'mtfAttr', 'sql' => array('i1', array('a' => $_r), 'WHERE i=' . $_f['id'], 0, '|')
						), '数据', $this->conf['domain']['dat'], 60);
					}
				}
			}
		} elseif ($_g === '数据') {
			if (empty($_data['sql']) === false) {
				array_splice($_data['sql'], 1, 0, $this->db['table']);
				$_sql = $_data['sql'];
				$_class = $_data['class'];
				$_n = count($_sql);
				$this->$_class->sql($_sql[0], $_sql[1], $_sql[2], $_sql[3], $_n >= 5 ? $_sql[4] : null, $_n >= 6 ? $_sql[5] : null);
			}
		} elseif ($_g === '删除') {
			foreach ($_data as $_dat) {
				if ($_dat['dir']) {
					$_f = $this->dir[$_dat['dir']] . $this->n2dir($_dat['n']) . $_dat['n'];
				} else {
					$_e = substr(strrchr($_dat['n'], '.'), 1);
					$_f = $this->e2dir($_e) . $this->n2dir($_dat['n']) . $_dat['n'];
				}
				if (file_exists($_f)) unlink($_f);
			}
		}
		$this->mtfQueue->urlRemove($_i);
	}
}
?>