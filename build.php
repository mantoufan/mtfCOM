<?php
$g_dom='.';
$g_usr='madfan';
$g_psd='';
$g_root=str_replace('\\','/',dirname(__file__)).'/';
$j=@$_POST['j'];$usr=@$_POST['usr'];
if($j){
	$j=base64_decode($j);
	$j=json_decode($j,true); 
	$n=uniqid();
	$f=$n.'.zip';
	fopen($f,'w');
	$tmp=array();
	$tmp[]=$f;
	function loadPHPJson($k,$v1,$zip){
		if(file_exists($k.'/'.$v1.'/mtf.json')){
			$j2=file_get_contents($k.'/'.$v1.'/mtf.json');
			$a2=json_decode($j2,true);
			foreach($a2 as $k2=>$v2){
				foreach($v2 as $k3=>$v3){
					if($k2==='php'){
						if(strstr($v3,'.')){
							$zip->addFile($k2.'/'.$v3, 'API/mtf/'.$k2.'/'.$v3);
						}else{
							addFileToZip($k2.'/'.$v3, $zip, 'API/mtf');	
							loadPHPJson($k2,$v3,$zip);
						}
					}elseif($k2=='json'){
						$c[$k2][$v3]=json_decode(file_get_contents($k2.'/'.$v3.'.'.$k2),true);
					}
				}
			}
		}
		addFileToZip($k.'/'.$v1, $zip, 'API/mtf');
	}
	
	if($j){
		$zip = new ZipArchive();
		if ($zip->open($f, ZipArchive::OVERWRITE) === TRUE) {
			foreach($j as $k=>$v){
				foreach($v as $k1=>$v1){
					if($k==='php'){
						if(strstr($v1,'.')){
							$zip->addFile($k.'/'.$v1, 'API/mtf/'.$k.'/'.$v1);
						}else{
							loadPHPJson($k,$v1,$zip);
						}
					}elseif($k==='js'||$k==='css'){
						$c[$k].=($c[$k]?"\n":'').file_get_contents($k.'/'.$v1.'.'.$k);	
					}elseif($k==='tpl'){
						$c[$k][$v1]=file_get_contents($k.'/'.$v1.'.'.$k);
					}elseif($k==='lang'||$k==='json'){
						include_once('php/mtfLang/mtfLang.php');
						$l=new mtfLang($v1.'/'.$k);
						if(@$c[$k]){
							$c[$k]=@array_merge($c[$k],$l->lang);
						}else{
							$c[$k]=$l->lang;	
						}
					}elseif($k=='mod'){
						foreach($v1 as $k2=>$v2){
							if(is_dir($k.'/'.$k1.'/'.$v2)){
								addFileToZip($k.'/'.$k1.'/'.$v2, $zip, 'UI/mtf', $k.'/'.$k1);	
							}else{
								$ext=substr(strrchr($v2,'.'),1);
								if(substr($ext,-1)=='*'){
									$v2=rtrim($v2,'*');	
								}
								if($ext=='js'||$ext=='css'){
									$c[$ext].=file_get_contents($k.'/'.$k1.'/'.$v2);	
								}else{
									$zip->addFile($k.'/'.$k1.'/'.$v2, 'UI/mtf/'.$v2);	
								}
							}
						}
					}
				}
			}
			if($usr){
				$usr=base64_decode($usr);
				$usr=json_decode($usr,true);
				foreach($usr as $k=>$v){
					foreach($v as $k1=>$v1){
						if($k==='js'||$k==='css'){
							$c[$k].=($c[$k]?"\n":'').$v1;
						}elseif($k==='tpl'){
							$c[$k][stristr($k1,'.',true)]=$v1;
						}elseif($k==='lang'||$k==='json'){
							$k1=stristr($k1,'.',true);
							if($c[$k][$k1]){
								$c[$k][$k1]=@array_merge($c[$k][$k1],json_decode($v1,true));
							}else{
								$c[$k][$k1]=json_decode($v1,true);
							}
						}elseif($k==='html'){
							if($v1){	
								$html=trim(preg_replace(array("/> *([^ ]*) *</","/<!--[^!]*-->/","'/\*[^*]*\*/'","/\r\n/","/\n/","/\t/",'/>[ ]+</'),array(">\\1<",'','','','','','><'),$v1));	
								if($k1==='app'){
									$c['tpl'][$k1]=$html;	
								}else{
									file_put_contents($n.'.'.$k,$html);
									$tmp[]=$n.'.'.$k;
									$zip->addFile($n.'.html', 'UI/'.$k1.'.'.$k);
								}
							}
						}elseif($k==='php'){
							if($v1){		
								file_put_contents($n.$k1.'.'.$k,$v1);
								$tmp[]=$n.$k1.'.php';
								if($k1==='api'){
									$zip->addFile($n.$k1.'.'.$k, 'API/index'.'.'.$k);
								}elseif($k1==='index'){
									$zip->addFile($n.$k1.'.'.$k, 'UI/'.$k1.'.'.$k);
								}else{
									$zip->addFile($n.$k1.'.'.$k, 'API/'.$k1.'.'.$k);
								}
							}
						}elseif($k==='htaccess'){
							if($v1){
								file_put_contents($n.$k1.'.'.$k,$v1);
								$tmp[]=$n.$k1.'.'.$k;
								if($k1==='api'){
									$zip->addFile($n.$k1.'.'.$k, 'API/.'.$k);
								}else{
									$zip->addFile($n.$k1.'.'.$k, 'UI/.'.$k);
								}
							}
						}elseif($k==='crossdomain'){
							if($v1){
								file_put_contents($n.$k.'.xml',$v1);
								$tmp[]=$n.$k.'.xml';
								if($k1==='api'){
									$zip->addFile($n.$k.'.xml', 'API/'.$k.'.xml');
								}else{
									$zip->addFile($n.$k.'.xml', 'UI/'.$k.'.xml');
								}
							}
						}
					}
				}
			}
			if($c['tpl']){
				$js='var TPL='.json_encode($c['tpl'],true).';';
				$php='$TPL='.var_export($c['tpl'],true).';';	
			}
			if($c['lang']){
				$js.='var LANG='.json_encode($c['lang'],true).';';
				$php.='$LANG='.var_export($c['lang'],true).';';	
			}
			if($c['json']){
				$js.='var CONF='.json_encode($c['json'],true).';';
				$php.='$CONF='.var_export($c['json'],true).';';	
			}
			$js=($js?$js."\n":'').@$c['js'];
			$css=@$c['css'];
			if($js){
				//include('php/JavaScriptPacker/JavaScriptPacker.php');
				//$myPacker = new JavaScriptPacker($js);
				//$js= $myPacker->pack();
				file_put_contents($n.'.js',$js);
				exec($g_root.'mod/UglifyJS3/node_modules/.bin/uglifyjs.cmd '.$g_root.$n.'.js -o '.$g_root.$n.'.min.js -m -c --ie8');
				$tmp[]=$n.'.js';
				$tmp[]=$n.'.min.js';
				$zip->addFile($n.'.min.js', 'UI/mtf/j.js');
			}
			if($css){
				file_put_contents($n.'.css',str_replace('; ', ';', (preg_replace(array("/> *([^ ]*) *</","/<!--[^!]*-->/","'/\*[^*]*\*/'","/\r\n/","/\n/","/\t/",'/>[ ]+</'),array(">\\1<",'','','','','','><'),$css))));
				$tmp[]=$n.'.css';	
				$zip->addFile($n.'.css', 'UI/mtf/c.css');
			}
			if($php){
				file_put_contents($n.'.MTF.php','<?php '.$php.' ?>');
				$tmp[]=$n.'.MTF.php';
				$zip->addFile($n.'.MTF.php', 'API/mtf/php/MTF.php');		
			}
		}
		$zip->close();
	}
	echo file_get_contents($f);
	if($tmp){
		foreach($tmp as $k=>$v){
			unlink($v);
		}
	}
}

function addFileToZip($path, $zip, $des='', $rep='') {
	$handler = opendir($path); //打开当前文件夹由$path指定。
	/*
	循环的读取文件夹下的所有文件和文件夹
	其中$filename = readdir($handler)是每次循环的时候将读取的文件名赋值给$filename，
	为了不陷于死循环，所以还要让$filename !== false。
	一定要用!==，因为如果某个文件名如果叫'0'，或者某些被系统认为是代表false，用!=就会停止循环
	*/
	while (($filename = readdir($handler)) !== false) {
		if ($filename != '.' && $filename != '..') {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
			if (is_dir($path.'/'.$filename)) {// 如果读取的某个对象是文件夹，则递归
				$zip->addEmptyDir($des.'/'.$path.'/'.$filename);//空文件夹
				addFileToZip($path.'/'.$filename, $zip, $des);
			} else { //将文件加入zip对象
				$zip->addFile($path.'/'.$filename, $des?($des.'/'.str_replace($rep,'',$path).'/'.$filename):'');
			}
		}
	}
	@closedir($path);
}


?>