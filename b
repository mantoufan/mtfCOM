<?php
  include('../mtfApp/buildConfig.php'); // Path of build Configuration file in another private repo named mtfApp
  $id=isset($params['i']) ? $params['i'] : (isset($argv[1]) ? $argv[1] : '');
  if (empty($id)) exit('id can not be empty');
  $DIR = isset($ID2DIR[$id]) ? '../' . $ID2DIR[$id] : '';
  if (empty($DIR)) exit('dir can not be empty');
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
  
  $_POST=array('id'=>$id,'j'=>base64_encode($j),'usr'=>base64_encode(json_encode($usr)));
  include('build.php');
  function getDir($src){
    global $DIR;
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
    global $DIR;
    if(file_exists($DIR.'/'.$src)){
      return file_get_contents($DIR.'/'.$src);
    }
    return '';
  }
?>