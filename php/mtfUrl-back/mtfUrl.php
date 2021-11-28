<?php
class mtfUrl{
	private $root='ZONE';
	public $dir=array(
					'dat'=>'dat',
					'img'=>'img'
				);
	private $mtfUnit;
	public $Phantomjs='/bin/Win32/Phantomjs/phantomjs.exe';
	private $js='js.js';
	
	public function __construct()
    {
		$_root=str_replace('\\','/',dirname(__file__)).'/';
		$this->_root=$_root;
		
		$_root=$_root.'../../../../'.$this->root.'/';
		if(!is_dir($_root)){
			mkdir($_root);	
		}
		
		foreach($this->dir as $_k=>$_v){
			$this->dir[$_k]=$_root.$_v.'/';
			if(!is_dir($this->dir[$_k])){
				mkdir($this->dir[$_k]);
			}
		}
		
		$this->Phantomjs=$this->_root.$this->Phantomjs;
		$this->js=$this->_root.$this->js;
		
		include_once($this->_root.'../mtfUnit/mtfUnit.php');
		$this->mtfUnit=new mtfUnit();
	}
	
	private function _id($_u)
	{
		return md5($_u);	
	}
	
	private function _dat($_d,$_a='r')
	{
		if($_a==='r')
		{			
			return json_decode(file_get_contents($_d),true);		
		}elseif(is_array($_a)){
			file_put_contents($_d,$this->mtfUnit->JsonEncodeCN($_a));
		}
	}
	
	public function string2url($_s){
		$_urls=array();
		$_a=preg_split('/(?<!=)http/',$_s);
		foreach ($_a as $_k => $_u) {
			$_u='http'.$_u;
			$_ar=explode(' ',$_u);
			$_u=$_ar[0];
			unset($_ar);
			$_u=preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~","\\0",$_u);
			$_ar=explode('#',$_u);
			if(filter_var($_ar[0], FILTER_VALIDATE_URL)){
				$_urls[]=$_u;
			}
		}
		return $_urls;
	}
	
	public function get($_u,$_arv=array('t'=>'','d'=>'','p'=>''))
	{
		$_a=$_arv;
		$_i=$this->_id($_u);
		$_d=$this->dir['dat'].$_i.'.dat';
		$_l=$this->dir['dat'].$_i.'.lock';
		if(file_exists($_d))
		{	
			$_a=array_merge($this->_dat($_d),array_filter($_arv));	
		}else{
			if(!file_exists($this->js))
			{
				$js='
				var system=require("system"),fs = require("fs"),page,id=system.args[1],url=system.args[2],murl=null,mid=null,mr=0;
				var renderPage=function (u,id) {
						page=require("webpage").create();
						page.settings.userAgent="Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3";
						page.viewportSize={width:128,height:568};
						page.clipRect={top:0,left:0,width:128,height:128};
						page.zoomFactor=0.4;
						page.onNavigationRequested=function(url, type, willNavigate, main) {
							if (main && url!=murl) {
								murl=url;
								mid=id;
								page.close();
								mr=1;
								setTimeout("renderPage(murl,mid)",200);
							}else{
								mr=0;	
							}
						};
						page.open(u, function (status) {
							if(status){
								page.render("'.$this->dir['img'].'"+id+".jpg",{format:"jpg",quality:"25"});
								var j=page.evaluate(function () {
									var meta = document.getElementsByTagName("meta"),d;
									for(i in meta){
										if(typeof meta[i].name!="undefined"&&meta[i].name.toLowerCase()=="description"){
											d=meta[i].content;
											break;
										}
									}
									return {t:document.title,d:d,p:""};
								});
								if(mr===0){
									if(j){
										fs.write("'.$this->dir['dat'].'"+id+".dat", JSON.stringify(j), "w");
										fs.remove("'.$this->dir['dat'].'"+id+".lock");
									}
									phantom.exit();
								}
							}
						});
				}
				renderPage(url,id);';
				file_put_contents($this->js,$js);
			}
			if($_arv['t']&&$_arv['d']&&$_arv['p']){
				//do nothing
			}else{
				if(!file_exists($_l))
				{
					$this->_dat($_l,array(1));
					pclose(popen($this->Phantomjs.' '.$this->js.' '.$_i.' '.$_u,'r'));
				}
			}
			
		}
		return $_a;
	}
	
	private function getPic($_p)
	{
		$_p=$this->dir['img'].$_p;
		if(file_exists($_p))
		{
			$_d=$_p;
			$_i=getimagesize($_d);
			header('content-type:'.$_i['mime']);
			echo file_get_contents($_d);
		}else{
			$_d=$this->dir['img'].$_d;
			$im=imagecreatetruecolor(300, 300);
 			$white=imagecolorallocate($im, 255, 255, 255);
			$grey=imagecolorallocate($im, 128, 128, 128);
			$black=imagecolorallocate($im, 0, 0, 0);
			imagefilledrectangle($im, 0, 0, 300, 300, $white);
			$text = "^_^";
			$font = 'arial.ttf';
			imagettftext($im, 100, 0, 20, 50, $black, $font, $text);
			header("Content-type: image/jpg");
			echo imagejpeg($im);
			imagedestroy($im);
		}
	}
	
	public function getJS($_u,$_arv=array('t'=>'','d'=>'','p'=>''))
	{
		$_a=$this->get($_u,$_arv);
		$_i=$this->_id($_u);
		return 'document.writeln(\'<a href="'.$_u.'" target="_blank">'.((!$_a['t']&&!$_a['d']&&!$_a['p'])?$_u:'<img src="'.($_a['p']?$_a['p']:'//'.$_SERVER['HTTP_HOST'].'/'.$_i.'.jpg').'" align="left" width="80px" height="80px" /><font size="3">'.($_a['t']?$_a['t']:'').'</font><br><font size="2">'.($_a['d']?mb_substr($_a['d'],0,36):'').'</font>').'</a>\')';
	}
	
	public function RESTful()
	{
		$_u=str_replace(':/','://',@$_GET['u']);
		$_t=@$_GET['t'];
		$_d=@$_GET['d'];
		$_p=str_replace(':/','://',@$_GET['p']);
		$_i=pathinfo(@$_SERVER['QUERY_STRING']);
		$_e=$_i['extension'];
		if(!$_u&&($_e==='jpg'||$_e==='png'||$_e==='gif')){
			$this->getPic($_SERVER['QUERY_STRING']);	
		}else{
			if($_u){
				echo $this->getJS($_u,array('t'=>$_t,'d'=>$_d,'p'=>$_p));
			}
		}
	}
}
?>