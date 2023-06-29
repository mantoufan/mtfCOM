<?php
include('../mtfApp/buildConfig.php'); // Path of build Configuration file in another private repo named mtfApp
$g_dom='.';
$g_usr='madfan';
$g_psd='';
$g_root=str_replace('\\','/',dirname(__file__)).'/';
$j=@$_POST['j'];$usr=@$_POST['usr'];$id=@$_POST['id'];
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
						@$c[$k].=(@$c[$k]?"\n":'').file_get_contents($k.'/'.$v1.'.'.$k);	
					}elseif($k==='tpl'){
						$c[$k][$v1]=htmlMinify(file_get_contents($k.'/'.$v1.'.html'));
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
							@$c[$k].=(@$c[$k]?"\n":'').$v1;
						}elseif($k==='tpl'){
							$c[$k][stristr($k1,'.',true)]=htmlMinify($v1);
						}elseif($k==='lang'||$k==='json'){
							$k1=stristr($k1,'.',true);
							if(@$c[$k][$k1]){
								$c[$k][$k1]=@array_merge($c[$k][$k1],json_decode($v1,true));
							}else{
								$c[$k][$k1]=json_decode($v1,true);
							}
						}elseif($k==='html'){
							if($v1){	
								$html=htmlMinify($v1);	
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
								file_put_contents($n.$k1.'.'.$k,strtr($v1,$ENV));
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
			if(@$c['tpl']){
				$js='var TPL='.json_encode($c['tpl'],true).';';
				$php='$TPL='.var_export($c['tpl'],true).';';	
			}
			if(@$c['lang']){
				$js.='var LANG='.json_encode($c['lang'],true).';';
				$php.='$LANG='.var_export($c['lang'],true).';';	
			}
			if(@$c['json']){
				$js.='var CONF='.json_encode($c['json'],true).';';
				$php.='$CONF='.var_export($c['json'],true).';';	
			}
			$js=(@$js?$js."\n":'').@$c['js'];
			$css=@$c['css'];
			if($js){
				file_put_contents($n.'.js',$js);
				// exec($g_root.'lsrunase.exe /user:Administrator /password:'.$AKEY.' /domain: /command:"'.$g_root.'mod/UglifyJS3/node_modules/.bin/uglifyjs.cmd '.$g_root.$n.'.js -o '.$g_root.$n.'.min.js -m -c --ie8" /runpath:c:');
        exec($g_root.'mod/UglifyJS3/node_modules/.bin/uglifyjs '.$g_root.$n.'.js -o '.$g_root.$n.'.min.js -m -c --ie8 --no-annotations');
				while(1) {
					if (file_exists($n.'.min.js')) break;
					usleep(500);
				}
				// copy($n.'.js', $n.'.min.js');
				$tmp[]=$n.'.js';
				$tmp[]=$n.'.min.js';
				$zip->addFile($n.'.min.js', 'UI/mtf/j.js');
			}
			if($css){
				file_put_contents($n.'.css',$css);
				// exec($g_root.'lsrunase.exe /user:Administrator /password:'.$AKEY.' /domain: /command:"'.$g_root.'mod/CleanCss/node_modules/.bin/cleancss.cmd '.$g_root.$n.'.css -o '.$g_root.$n.'.min.css" /runpath:c:');
       	exec($g_root.'mod/CleanCss/node_modules/.bin/cleancss '.$g_root.$n.'.css -o '.$g_root.$n.'.min.css');
				while(1) {
					if (file_exists($n.'.min.css')) break;
					usleep(500);
				}
				rename($n.'.min.css', $n.'.css');
				$tmp[]=$n.'.css';	
				$zip->addFile($n.'.css', 'UI/mtf/c.css');
			}
			if(@$php){
				file_put_contents($n.'.MTF.php','<?php '.$php.' ?>');
				$tmp[]=$n.'.MTF.php';
				$zip->addFile($n.'.MTF.php', 'API/mtf/php/MTF.php');		
			}
		}
		$zip->close();
	}
	// echo file_get_contents($f);
	$servers = $ID2DEPLOY[$id];
	foreach ($servers as $server) {
		$url = @$DEPLOY[$server]['url'];
		if (!$url) {
			t($id . ' ' . 'url not found');
			exit;
		}
		t($id . ' ' . $url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL , $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'id' => $id,
			'upload' => new CURLFile($f)
		));
		$h = curl_exec($ch);
		curl_close($ch);
		t($h);
	}
	if($tmp){
		foreach($tmp as $k=>$v){
			unlink($v);
		}
	}
}
function addFileToZip($path, $zip, $des='', $rep='') {
	$handler = opendir($path);
	while (($filename = readdir($handler)) !== false) {
		if ($filename != '.' && $filename != '..') {
			if (is_dir($path . '/' . $filename)) {
				$zip->addEmptyDir($des . '/' . str_replace($rep, '', $path) . '/' . $filename);
				addFileToZip($path . '/' . $filename, $zip, $des, $rep);
			} else {
				$zip->addFile($path . '/' . $filename, $des ? ($des . '/' . str_replace($rep, '', $path) . '/' . $filename) : '');
			}
		}
	}
	closedir($handler);
}
function t($t) {
	echo $t."\r\n";
}
function htmlMinify($h) {
	return trim(preg_replace(array("/> *([^ ]*) *</","/<!--[^!]*-->/","'/\*[^*]*\*/'","/\r\n/","/\n/","/\t/",'/>[ ]+</'),array(">\\1<",'','','','','','><'), $h));
}
?>