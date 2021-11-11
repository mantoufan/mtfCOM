<?php
class mtfTpl{
	public function tip($_a){
		return '<!doctype html>
		<html>
		<head>
		<title>MTF</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">'.
		(!empty($_a['bu'])?'<meta http-equiv="refresh" content="'.$_a['r'].';url='.$_a['bu'].'" />':'').
		'<title>'.$_a['t'].'</title>
		<style>
		body{text-align:center;margin:2em 0;background:#F7F7F7;font-size:1.5em;}
		.bt{margin:.5em 0;display:block;padding:1em 0;background:#60efdb;color:#000000;}
		.a{border-radius: 50%;width:100px;height:100px;}.d{font-size:80%;}
		</style>
		</head>
		<body>'.
		(!empty($_a['a'])?'<img class="a" src="'.$_a['a'].'" />':'').
		(!empty($_a['t'])?'<div>'.$_a['t'].'</div>':'').
		(!empty($_a['d'])?'<div class="d">'.$_a['d'].'</div>':'').
		(!empty($_a['bt'])?'<a class="bt" href='.urldecode($_a['bu']).' style="background:'.$_a['bc'].'">'.$_a['bt'].'</a>':'').
		(!empty($_a['bt1'])?'<a class="bt" href='.urldecode($_a['bu1']).' style="background:'.$_a['bc1'].'">'.$_a['bt1'].'</a>':'').
		'</body>
		</html>';
	}
	public function frame($_domain){
		function htmlencode($html){
			$code=bin2hex($html); 
			$spilt=chunk_split($code, 2, '%');
			$totallen=strlen($spilt);
			$sublen=$totallen-1;
			$fianlop=substr($spilt, '0', $sublen);
			return "document.write(unescape('%$fianlop'));";
		}
		return '<!doctype html><html><head><title>MTFP2P</title><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"><meta name="applicable-device" content="pc,mobile"><meta name="MobileOptimized" content="width"><meta name="HandheldFriendly" content="true"><meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="x5-fullscreen" content="true"><meta name="full-screen" content="yes"><link rel="shortcut icon" href="//'.@$_SERVER['HTTP_HOST'].'/mtf/php/mtfTpl/s/p/favicon.ico" /><style>body{background:#F7F7F7;}*{margin:0;padding:0;}.loading img{width:100%;margin:5em auto;max-width:320px;display:block;}#a{display:block;width:100%;position:absolute;top:0;}html,body{overflow:hidden;}#d,.none{display:none}</style></head><body><div class="loading"><img src="//'.@$_SERVER['HTTP_HOST'].'/mtf/php/mtfTpl/s/p/logo.gif" alt="加载中·Loading" /></div><script>'.htmlencode('<iframe id="a" frameborder="0" scrolling="no" allowfullscreen="true"></iframe><div id="d">'.$_domain.'</div>').'function I(i){return document.getElementById(i);}var a=I("a"),b=location.href.replace(location.protocol+"//"+location.host,I("d").innerHTML).replace("?","&if=1?")+"&if=1";function wr(){a.style.height=(window.innerHeight||document.documentElement.clientHeight||document.body.clientHeight)+"px"}window.onresize=function(){wr();},wr();if((navigator.userAgent.match("shareh")||navigator.standalone)&&top.location===self.location){setTimeout(function(){top.location.replace(b);},350);}else{a.src=b;};</script>';
	}
}
?>