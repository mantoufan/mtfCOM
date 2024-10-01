<?php
class mtfKey
{
	public $psd=5;//>=3
	public $al=array('zz');
	public $timeDiffer=1800;//30分钟，适应长篇漫画上传
	public $ask=array('1'=>'0+1=?','2'=>'1+1=?','3'=>'3+0=?');
	public $alpha=['B', 'U', 'Z', 'P'];
	public $error='';
	public $errorCode='';
	public $domain=array(
		'allow'=>array('mtf.im'),
		'redirect'=>'http://mtf.yzhan.cyou'
	);
	public $keyTimes=35;//key能使用多少次，用户不刷新页面的情况下
	public $jsEncodeTimes=3;//js加密多少次
	public $dir='../../../dir';//存放log和黑名单的路径
	public $mtfGuid;
	public $mtfUrl;
	public $mtfTpl;
	private $_root;
	public $key;
	public $fid;//从mtfKey中获得指纹fid
	public $answer;
	public $default='dm126';//默认密钥
	public $app=array('XMLHttpRequest','qiuqiu.dm126.madfan','com.tencent.mobileqq');//APP白名单
	public $ips=array();//IP黑名单
	public $urlLib=array(
		'safe'=>array(
					'mtf.im'
				),
		'lure'=>array(
				),
		'risk'=>array(
				)
	);//网址安全云库
	
	public function __construct($key='')
    {	
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		include_once($_root.'../mtfGuid/mtfGuid.php');
		$this->mtfGuid=new mtfGuid();
		include_once($_root.'../mtfUrl/mtfUrl.php');
		$this->mtfUrl=new mtfUrl();
		include_once($_root.'../mtfTpl/mtfTpl.php');
		$this->mtfTpl=new mtfTpl();
		$this->key=$key;
	}
	private function _psd($s=''){
		$s=trim($s);
		if(empty($s)){return '';}
		$r='';$l=strlen($s);
		for($i=0;$i<$l;$i++){
			if(is_numeric($s[$i])){
				$r.=$s[$i];
			}else{
				break;	
			}
		}
		return $r;
	}
	private function _xor($s,$k)
	{
		$t = '';
		$l = strlen($s);
		$kl = strlen($k);
		for($i=0;$i<$l;$i++)
		{   
		 $j = $i%$kl;
		 $t .= $s[$i] ^ $k[$j];
		}
		return $t; 
	}
	private function _enc($s){
		$e='';
		$l=strlen($s);
		for ($i = 0; $i < $l; $i++) {
			if (in_array($s[$i], $this->alpha)) {
				$e.=array_search($s[$i], $this->alpha);
			} else {
				$e.=$s[$i];
			}
		}
		return $e;
	}
	private function _key_session($key){
		$key=substr($key,0,15);//只保留15位，避免暴露到SSID中
		@session_write_close();
		@session_id($key);
		@session_start();
	}
	private function _wlog($_f,$error=''){
		$_f=dirname(__FILE__).'/'.$_f;
		$_i=pathinfo($_f);
		$_d=$_i['dirname'];
		if(!is_dir($_d)){
			@mkdir($_d);
		}
		$_c=date('m-d H:i:s',time())."\n".json_encode(array('error'=>$error,'key'=>$this->key,'REMOTE_ADDR'=>@$_SERVER['REMOTE_ADDR'],'REMOTE_PORT'=>@$_SERVER['REMOTE_PORT'],'HTTP_X_FORWARDED_FOR'=>$this->mtfGuid->ip(),'SERVER_ADDR'=>@$_SERVER['SERVER_ADDR'],'SERVER_PORT'=>@$_SERVER['SERVER_PORT'],'QUERY_STRING'=>@$_SERVER['QUERY_STRING'],'HTTP_REFERER'=>@$_SERVER['HTTP_REFERER'],'HTTP_USER_AGENT'=>@$_SERVER['HTTP_USER_AGENT'],'HTTP_COOKIE'=>@$_SERVER['HTTP_COOKIE'],'HTTP_X_REQUESTED_WITH'=>@$_SERVER['HTTP_X_REQUESTED_WITH']))."\n\n";	
		
		@file_put_contents($_f, $_c,FILE_APPEND);
	}
	private function _error($error,$errorCode=''){
		$this->error=$error;
		$this->errorCode=$errorCode;
		$this->_wlog($this->dir.'/'.$error.'.txt',$errorCode);
	}
	public function ban($fid,$domain){
		$banList=array();
		$_r='';
		$_f=dirname(__FILE__).'/'.$this->dir.'/banList.'.$domain.'.php';
		@include($_f);
		if(@$banList[$fid]){
			unset($banList[$fid]);
			$_r=FALSE;
		}else{
			$banList[$fid]=date('Y-m-d H:i:s',strtotime('+2 day'));//封禁两天
			$_r=TRUE;
		}
		foreach($banList as $k=>$v){
			if(strtotime($v)<=time()){
				unset($banList[$k]);
			}
		}

		@file_put_contents($_f, "<?php\n\$banList=".var_export(@$banList, true)."\n?>");
		return $_r;
	}
	public function get($fid='')
	{
		$a=time();
		$b=rand(1000000000,5666666666);
		$key=($a+$b+$this->psd).$b;
		$al=$this->al;
		for($i = 0; $i < $this->psd;$i++) {
			array_push($al,'a');
		}
		$k=implode('',$al);
		
		$this->_key_session($key);
		
		$_ip=$this->mtfGuid->ip();
		//IP黑名单拦截
		if(in_array($_ip, $this->ips)) exit;
		
		$_SESSION['IP']=$_ip;
		$_SESSION['UA']=$_SERVER['HTTP_USER_AGENT'];
		$_SESSION['FID']=$fid;
		$_SESSION['LIVE']=1;
		@session_write_close();
		
		$key=$this->psd.$this->_xor($key,$k);
		return $key;
	}
	public function getJS($fid='')
	{
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$k=$this->get($fid);
		$k=$this->_enc($k);
		$js='function h(p, a){for(var i = 0;i<a.length;i++){if(a[i]==p){return true;}}return false;}if(h(location.hostname,'.json_encode($this->domain['allow']).')){mtfKey.key=d(\''.$k.'\');function d(s){var a='.json_encode($this->alpha).',r=\'\';for(var i=0;i<s.length;i++){r+=i>0&&!isNaN(s[i])?a[s[i]]:s[i];}return r;}}';//不要跳转，避免其他地方使用失效
		include_once($_root.'../JavaScriptPacker/JavaScriptPacker.php');
		for ($i=0; $i<=$this->jsEncodeTimes; $i++) {
			$myPacker = new JavaScriptPacker($js,36, false, false);
			$js=$myPacker->pack();
		}
		return $js;
	}
	private function deKey(){
		$key=$this->key;
		$this->psd=$this->_psd($key);
		$key=ltrim($key,$this->psd);
		$al=$this->al;
		for($i = 0; $i < $this->psd;$i++) {
			array_push($al,'a');
		}
		$k=implode('',$al);
		return $key=$this->_xor($key,$k);
	}
	public function check($_domain) {
    if($this->key){
			$_ip=$this->mtfGuid->ip();
			
			$key=$this->deKey();
			if($key===$this->default){
				$this->_error('check-default-key');
				return false;
			}
			
			if(is_numeric($key)){
				$a=substr($key,0,10);
				$b=substr($key,10,10);
				//$c=substr($key,20)-floor(0.0008*$b+568*$this->psd);
				$clientTime=$a-$this->psd-$b;
				
				$this->_key_session($key);
				@$_SESSION['T']++;

				//IP黑名单拦截
				if(in_array($_ip, $this->ips)) exit;

				$banList=array();
				$_f=dirname(__FILE__).'/'.$this->dir.'/banList.'.$_domain.'.php';
				@include($_f);

				if(!@$_SESSION['LIVE']){
					$this->_error('check-wrong-live');
					@session_write_close();
				}elseif(@$_SESSION['FID'] && $banList && strtotime(@$banList[$_SESSION['FID']])>time()){
					$this->_error('check-ban',$_SESSION['FID']);
					@session_write_close();
				}elseif($this->app && @$_SERVER['HTTP_X_REQUESTED_WITH'] && !in_array($_SERVER['HTTP_X_REQUESTED_WITH'],$this->app)){//同域 Ajax XMLHttpRequest
					$this->_error('check-wrong-app',$_SERVER['HTTP_X_REQUESTED_WITH']);
					@session_write_close();
				}elseif($_SESSION['IP']!==$_ip){
					$this->_error('check-wrong-ip',$_ip.'|'.$_SESSION['IP']);
					@session_write_close();	
				}elseif($_SESSION['UA']!==$_SERVER['HTTP_USER_AGENT']){
					$this->_error('check-wrong-ua',$_SERVER['HTTP_USER_AGENT'].'|'.$_SESSION['UA']);
					@session_write_close();
				}elseif(abs(time()-$clientTime)>=$this->timeDiffer){
					$this->_error('check-over-time',time().'|'.$clientTime.'|'.abs(time()-$clientTime));
					@session_write_close();
				}elseif(@$_SESSION['T']>$this->keyTimes){
					$this->_error('check-wrong-times',$_SESSION['T'].'|'.$this->keyTimes);
					@session_write_close();
				}elseif(!empty($_SERVER['HTTP_VIA']) && stripos($_SERVER['HTTP_VIA'], 'Baidu-CDN-Node') === FALSE && stripos($_SERVER['HTTP_VIA'], 'cache') === FALSE && stripos($_SERVER['HTTP_VIA'], 'yunjiasu') === FALSE){//排除百度CDN与阿里CDN的节点
					$this->_error('check-wrong-via',$_SERVER['REMOTE_ADDR'].'|'.$_SERVER['HTTP_VIA'].'|'.$_SERVER['HTTP_X_FORWARDED_FOR']);
					@session_write_close();
				}else{
					$this->fid=$_SESSION['FID'];
					@session_write_close();
					return TRUE;	
				}
				//echo date('m-d H:i:s',$clientTime);	
			}else{
				$this->_error('check-wrong-key',$key);
			}
		}else{
			$this->_error('check-empty-key');	
		}
		return FALSE;
    }
	public function captcha()
	{
		if($this->key){
			$key=$this->deKey();
			$this->_key_session($key);
			
			if(@$_SESSION['answer']){	
				if(@$_SESSION['nocaptcha']){
					@$_SESSION['nocaptcha']++;
					if(@$_SESSION['nocaptcha']>$this->keyTimes){
						$this->_error('captcha-over-time',$_SESSION['nocaptcha']);
						$_SESSION['answer']='';	
						$_SESSION['nocaptcha']='';
					}
					
					$this->fid=$_SESSION['FID'];
					
					@session_write_close();
					
					return TRUE;	
				}elseif($_SESSION['answer']==$this->answer){
					$this->_error('captcha-answer-right',@$_SESSION['answer'].'|'.$this->answer);
					$_SESSION['nocaptcha']=1;
					
					$this->fid=$_SESSION['FID'];
					
					@session_write_close();
					return TRUE;	
				}else{
					$this->_error('captcha-answer-wrong',@$_SESSION['answer'].'|'.$this->answer);
					$_SESSION['answer']='';
					@session_write_close();
					return FALSE;
				}
			}else{
				$a=array_rand($this->ask,1);
				$_SESSION['answer']=$a;
				$this->_error('captcha-ask',$a);	
				@session_write_close();
				return $this->ask[$a];
			}
		}
		return FALSE;
	}
	private function _op(){
		$_op=array('+','*','*');
		return $_op[array_rand($_op,1)];
	}
	private function __sp(){
		$_sp=array(' ','　','`');
		return $_sp[array_rand($_sp,1)];
	}
	private function _sp(){
		$_l=rand(0,2);$_s='';
		for($_i=0; $_i<$_l; $_i++) {
			$_s.=$this->__sp();
		}
		return $_s;
	}
	public function asks()
	{
		$formula='';
		$_l=rand(2,3);//大于2
		
		$_re=array(
			'*'=>$this->_sp().'X'.$this->_sp(),
			'*'=>$this->_sp().'x'.$this->_sp(),
			'*'=>$this->_sp().'Ｘ'.$this->_sp(),
			'*'=>$this->_sp().'×'.$this->_sp(),
			'+'=>$this->_sp().'＋'.$this->_sp(),
			'0'=>'o',
			'0'=>'O',
			'1'=>'l',
			'1'=>'|',
			'1'=>'I',
		);
		
		
		for($_i=0; $_i<$_l; $_i++) {
		  $formula.=rand(0,9).$this->_op();
		}
		$formula=substr($formula,0,-1);
		return array(eval('return '.$formula.';')=>strtr($formula,$_re).' = ?');
	}
	public function clean($_dir,$_out,$_max){//清理垃圾：要清理的文件夹，过期时间，最大文件数
		$_ar=glob($_dir.'/*.*',GLOB_BRACE);
		$_l=count($_ar);
		if($_l>$_max){
			$_del=1;
		}else{
			$_del=0;
		}
		foreach($_ar as $_v){
			if(time()-filemtime($_v)>$_out||$_del===1){
				@unlink($_v);
			}
		}
	}
	
	private function isSafe($_u){
		foreach($this->urlLib as $k=>$_v){
			foreach($_v as $_k=>$v){
				if(stripos($_u,$v)!==FALSE){
					return $k;
				}
			}
		}
		return 'unknown';
	}
	
	public function go($_u){
		$_raw=$this->mtfUrl->getRawUrl($_u);
		$_t=$this->isSafe($_raw?$_raw:$_u);
		
		$_a['t']='Tip';
		$_a['d']='The link may has risk<script>function back(){  
    if ((navigator.userAgent.indexOf(\'MSIE\') >= 0) && (navigator.userAgent.indexOf(\'Opera\') < 0)){ // IE  
        if(history.length > 0){  
            window.history.go( -1 );  
        }else{  
            window.opener=null;window.close();  
        }  
    }else{ //非IE浏览器  
        if (navigator.userAgent.indexOf(\'Firefox\') >= 0 ||  
            navigator.userAgent.indexOf(\'Opera\') >= 0 ||  
            navigator.userAgent.indexOf(\'Safari\') >= 0 ||  
            navigator.userAgent.indexOf(\'Chrome\') >= 0 ||  
            navigator.userAgent.indexOf(\'WebKit\') >= 0){  
  
            if(window.history.length > 1){  
                window.history.go( -1 );  
            }else{  
                window.opener=null;window.close();  
            }  
        }else{ //未知的浏览器  
            window.history.go( -1 );  
        }  
    }  
}</script>';
		$_a['bt1']='Back';
		$_a['bu1']='javascript:back();';
		$_a['bc1']='gray';
		switch($_t){
			case 'safe'://信任链接
				$_a['r']=0;
				$_a['bu']=$_u;
			break;
			case 'lure'://诱导链接
				$_a['bt']='Continue';
				$_a['bu']=$_u;
				$_a['bc']='green';	
			break;
			case 'risk'://风险链接
				$_a['d']=str_replace('may ','',$_a['d']);
			break;
			default://未知链接
				$_a['r']=0;
				$_a['bu']=$_u;
			break;
		}
		echo $this->mtfTpl->tip($_a);
	}
}
?>