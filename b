<?php
  $ROOT = '../mtfApp/';
  $params = getopt('i:');
	$DIR = isset($params['i']) ? $ROOT.$params['i'] : (isset($argv[1]) ? $ROOT.$argv[1] : __DIR__);
  $id=basename($DIR);
	$url='http://127.os120.com/build.php';
	$j=file_get_contents($DIR.'/USR/mtf.json');$usr=array(); 
	
	$usr['tpl']=getDir('USR/tpl');
	$usr['lang']=getDir('USR/lang');
	$usr['json']=getDir('USR/json');
	$usr['js']['j']=getFile('USR/j.js');
	$usr['css']['c']=getFile('USR/c.css');
	$usr['html']['app']=getFile('USR/app.html');
	$usr['php']['index']=getFile('USR/index.php');
	$usr['php']['api']=getFile('USR/api.php');
	$usr['htaccess'][]=getFile('USR/.htaccess');
	$usr['htaccess']['api']=getFile('USR/api.htaccess');
	$usr['crossdomain']['']=getFile('USR/crossdomain.xml');
	$usr['crossdomain']['api']=getFile('USR/api-crossdomain.xml');
	
	$post_data=array('id'=>$id,'j'=>base64_encode($j),'usr'=>base64_encode(json_encode($usr)));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	$h=curl_exec($ch);
	curl_close($ch);
	echo $h;
	function getDir($src){
		$fL=glob($DIR.'/'.$src.'/*');$l=count($fL);$a=array();
		if($l>0){
			for ($i=0; $i<$l; $i++) { 
				$n=$fL[$i];
				$a[substr(strrchr($n,'/'),1)]=file_get_contents($n); 
			}  
		}
		return $a;
	}
	function getFile($src){
		if(file_exists($DIR.'/'.$src)){
			return file_get_contents($DIR.'/'.$src);
		}
		return '';
	}
?>